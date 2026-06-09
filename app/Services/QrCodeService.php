<?php

namespace App\Services;

use App\Models\QrCode as QrCodeModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class QrCodeService
{
    public const TYPES = [
        'url' => 'Lien web',
        'text' => 'Texte',
        'whatsapp' => 'WhatsApp',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'wifi' => 'Wi-Fi',
        'sms' => 'SMS',
        'vcard' => 'vCard',
        'location' => 'Localisation',
        'event' => 'Événement',
        'social' => 'Réseaux sociaux',
    ];

    public function buildContent(string $type, array $data): string
    {
        return match ($type) {
            'url', 'text' => trim((string) ($data['content'] ?? '')),
            'whatsapp' => 'https://wa.me/'.$this->digits($data['phone'] ?? '').'?text='.rawurlencode((string) ($data['message'] ?? '')),
            'email' => 'mailto:'.trim((string) ($data['email'] ?? '')).'?'.http_build_query([
                'subject' => $data['subject'] ?? '',
                'body' => $data['message'] ?? '',
            ], '', '&', PHP_QUERY_RFC3986),
            'phone' => 'tel:'.$this->phone($data['phone'] ?? ''),
            'wifi' => sprintf(
                'WIFI:T:%s;S:%s;P:%s;;',
                strtoupper((string) ($data['security'] ?? 'WPA')),
                $this->escapeWifi($data['ssid'] ?? ''),
                $this->escapeWifi($data['password'] ?? '')
            ),
            'sms' => 'SMSTO:'.$this->phone($data['phone'] ?? '').':'.($data['message'] ?? ''),
            'vcard' => $this->buildVcard($data),
            'location' => sprintf('geo:%s,%s?q=%s,%s', $data['latitude'], $data['longitude'], $data['latitude'], $data['longitude']),
            'event' => $this->buildEvent($data),
            'social' => trim((string) ($data['content'] ?? '')),
            default => throw new InvalidArgumentException('Type de QR Code non pris en charge.'),
        };
    }

    public function create(array $attributes, ?string $logoPath = null): QrCodeModel
    {
        $content = $this->buildContent($attributes['type'], $attributes['data']);

        $qrCode = QrCodeModel::create([
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'content' => $content,
            'options' => $this->options($attributes, $logoPath),
            'format' => $attributes['format'],
            'foreground_color' => $attributes['foreground_color'],
            'background_color' => $attributes['background_color'],
            'size' => $attributes['size'],
            'margin' => $attributes['margin'],
        ]);

        $path = $this->store($qrCode, $attributes['format']);
        $qrCode->update(['file_path' => $path]);

        return $qrCode->refresh();
    }

    public function update(QrCodeModel $qrCode, array $attributes, ?string $logoPath = null): QrCodeModel
    {
        $oldFilePath = $qrCode->file_path;
        $oldLogoPath = data_get($qrCode->options, 'logo_path');
        $logoPath ??= $oldLogoPath;

        $qrCode->update([
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'content' => $this->buildContent($attributes['type'], $attributes['data']),
            'options' => $this->options($attributes, $logoPath),
            'format' => $attributes['format'],
            'foreground_color' => $attributes['foreground_color'],
            'background_color' => $attributes['background_color'],
            'size' => $attributes['size'],
            'margin' => $attributes['margin'],
        ]);

        $path = $this->store($qrCode, $attributes['format']);
        $qrCode->update(['file_path' => $path]);

        if ($oldFilePath && $oldFilePath !== $path) {
            Storage::disk('public')->delete($oldFilePath);
        }

        if ($oldLogoPath && $oldLogoPath !== $logoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return $qrCode->refresh();
    }

    public function preview(array $attributes, ?string $absoluteLogoPath = null): string
    {
        $content = $this->buildContent($attributes['type'], $attributes['data']);

        return $this->build($content, $attributes, 'svg', $absoluteLogoPath);
    }

    public function downloadContents(QrCodeModel $qrCode, string $format): string
    {
        return $this->build($qrCode->content, $this->attributesFromModel($qrCode), $format, $this->logoAbsolutePath($qrCode));
    }

    public function filename(QrCodeModel $qrCode, string $format): string
    {
        return 'qr-code-'.Str::slug($qrCode->name ?: $qrCode->type).'.'.$format;
    }

    private function store(QrCodeModel $qrCode, string $format): string
    {
        $path = 'qr-codes/'.$qrCode->id.'-'.$this->filename($qrCode, $format);
        Storage::disk('public')->put($path, $this->downloadContents($qrCode, $format));

        return $path;
    }

    private function options(array $attributes, ?string $logoPath): array
    {
        return [
            'error_correction' => $attributes['error_correction'],
            'form_data' => $attributes['data'],
            'logo_path' => $logoPath,
        ];
    }

    private function build(string $content, array $attributes, string $format, ?string $logoPath): string
    {
        $writer = $format === 'png' ? new PngWriter : new SvgWriter;
        [$foregroundRed, $foregroundGreen, $foregroundBlue] = $this->hexToRgb($attributes['foreground_color']);
        [$backgroundRed, $backgroundGreen, $backgroundBlue] = $this->hexToRgb($attributes['background_color']);

        $result = (new Builder(
            writer: $writer,
            data: $content,
            errorCorrectionLevel: $this->errorCorrection($attributes['error_correction'] ?? 'medium'),
            size: (int) $attributes['size'],
            margin: (int) $attributes['margin'],
            foregroundColor: new Color($foregroundRed, $foregroundGreen, $foregroundBlue),
            backgroundColor: new Color($backgroundRed, $backgroundGreen, $backgroundBlue),
            logoPath: $logoPath ?? '',
            logoResizeToWidth: $logoPath ? (int) max(32, (int) $attributes['size'] / 5) : null,
            logoPunchoutBackground: (bool) $logoPath,
        ))->build();

        if ($format !== 'pdf') {
            return $result->getString();
        }

        $dompdf = new Dompdf(new Options(['isRemoteEnabled' => false]));
        $dompdf->loadHtml('<html><body style="margin:0;text-align:center"><img style="width:100%;height:auto" src="'.$result->getDataUri().'"></body></html>');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function attributesFromModel(QrCodeModel $qrCode): array
    {
        return [
            'foreground_color' => $qrCode->foreground_color,
            'background_color' => $qrCode->background_color,
            'size' => $qrCode->size,
            'margin' => $qrCode->margin,
            'error_correction' => data_get($qrCode->options, 'error_correction', 'medium'),
        ];
    }

    private function logoAbsolutePath(QrCodeModel $qrCode): ?string
    {
        $path = data_get($qrCode->options, 'logo_path');

        return $path && Storage::disk('public')->exists($path)
            ? Storage::disk('public')->path($path)
            : null;
    }

    private function errorCorrection(string $level): ErrorCorrectionLevel
    {
        return match ($level) {
            'low' => ErrorCorrectionLevel::Low,
            'quartile' => ErrorCorrectionLevel::Quartile,
            'high' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return array_map(hexdec(...), str_split($hex, 2));
    }

    private function phone(mixed $phone): string
    {
        return preg_replace('/[^\d+]/', '', (string) $phone) ?: '';
    }

    private function digits(mixed $phone): string
    {
        return preg_replace('/\D/', '', (string) $phone) ?: '';
    }

    private function escapeWifi(mixed $value): string
    {
        return str_replace(['\\', ';', ',', ':'], ['\\\\', '\\;', '\\,', '\\:'], (string) $value);
    }

    private function buildVcard(array $data): string
    {
        return implode("\n", array_filter([
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:'.($data['last_name'] ?? '').';'.($data['first_name'] ?? ''),
            'FN:'.trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
            filled($data['company'] ?? null) ? 'ORG:'.$data['company'] : null,
            filled($data['job_title'] ?? null) ? 'TITLE:'.$data['job_title'] : null,
            filled($data['phone'] ?? null) ? 'TEL:'.$this->phone($data['phone']) : null,
            filled($data['email'] ?? null) ? 'EMAIL:'.$data['email'] : null,
            filled($data['website'] ?? null) ? 'URL:'.$data['website'] : null,
            filled($data['address'] ?? null) ? 'ADR:;;'.$data['address'].';;;;' : null,
            'END:VCARD',
        ]));
    }

    private function buildEvent(array $data): string
    {
        $start = date('Ymd\THis', strtotime((string) $data['starts_at']));
        $end = date('Ymd\THis', strtotime((string) $data['ends_at']));

        return implode("\n", [
            'BEGIN:VEVENT',
            'SUMMARY:'.$data['title'],
            'DTSTART:'.$start,
            'DTEND:'.$end,
            'LOCATION:'.($data['location'] ?? ''),
            'DESCRIPTION:'.($data['description'] ?? ''),
            'END:VEVENT',
        ]);
    }
}
