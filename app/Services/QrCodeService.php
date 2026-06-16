<?php

namespace App\Services;

use App\Models\QrCode as QrCodeModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\EpsWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class QrCodeService
{
    public const TYPES = [
        'url' => 'Lien web',
        'file' => 'Fichier',
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
        'progressive' => 'Profil progressif',
    ];

    public function buildContent(string $type, array $data): string
    {
        return match ($type) {
            'url', 'text' => trim((string) ($data['content'] ?? '')),
            'file' => filled($data['uploaded_file_path'] ?? null)
                ? Storage::disk('public')->url((string) $data['uploaded_file_path'])
                : 'https://example.com/fichier',
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
            'progressive' => (string) ($data['public_url'] ?? url('/p/apercu')),
            default => throw new InvalidArgumentException('Type de QR Code non pris en charge.'),
        };
    }

    public const TRACKABLE_TYPES = ['url', 'file', 'progressive'];

    public function create(array $attributes, ?string $logoPath = null): QrCodeModel
    {
        $publicToken = in_array($attributes['type'], self::TRACKABLE_TYPES) ? Str::random(40) : null;
        $content = $this->resolveContent($attributes['type'], $attributes['data'], $publicToken);

        $qrCode = QrCodeModel::create([
            'user_id' => Auth::id(),
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'content' => $content,
            'public_token' => $publicToken,
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
        $oldPhotoPath = data_get($qrCode->options, 'form_data.photo_path');
        $oldUploadedFilePath = data_get($qrCode->options, 'form_data.uploaded_file_path');
        $logoPath ??= $oldLogoPath;
        $publicToken = in_array($attributes['type'], self::TRACKABLE_TYPES)
            ? ($qrCode->public_token ?? Str::random(40))
            : null;

        $qrCode->update([
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'content' => $this->resolveContent($attributes['type'], $attributes['data'], $publicToken),
            'public_token' => $publicToken,
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

        $photoPath = data_get($attributes, 'data.photo_path');

        if ($oldPhotoPath && $oldPhotoPath !== $photoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        $uploadedFilePath = data_get($attributes, 'data.uploaded_file_path');

        if ($oldUploadedFilePath && $oldUploadedFilePath !== $uploadedFilePath) {
            Storage::disk('public')->delete($oldUploadedFilePath);
        }

        return $qrCode->refresh();
    }

    private function resolveContent(string $type, array $data, ?string $publicToken): string
    {
        if ($type === 'progressive') {
            return route('qr-code.progressive', $publicToken);
        }

        if (in_array($type, ['url', 'file'], true) && $publicToken) {
            return route('qr-code.scan', $publicToken);
        }

        return $this->buildContent($type, $data);
    }

    public function preview(array $attributes, ?string $absoluteLogoPath = null): string
    {
        $content = $this->buildContent($attributes['type'], $attributes['data']);

        return $this->build($content, $attributes, 'svg', $absoluteLogoPath);
    }

    public function downloadContents(QrCodeModel $qrCode, string $format, ?int $sizeOverride = null): string
    {
        $attributes = $this->attributesFromModel($qrCode, $sizeOverride);

        return $this->build($qrCode->content, $attributes, $format, $this->logoAbsolutePath($qrCode));
    }

    public function filename(QrCodeModel $qrCode, string $format): string
    {
        $ext = $format === 'jpeg' ? 'jpg' : $format;

        return 'qr-code-'.Str::slug($qrCode->name ?: $qrCode->type).'.'.$ext;
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
        [$foregroundRed, $foregroundGreen, $foregroundBlue] = $this->hexToRgb($attributes['foreground_color']);
        [$backgroundRed, $backgroundGreen, $backgroundBlue] = $this->hexToRgb($attributes['background_color']);

        $isRaster = in_array($format, ['png', 'jpeg']);
        $writer = match ($format) {
            'eps' => new EpsWriter,
            'svg' => new SvgWriter,
            default => new PngWriter,
        };

        $builder = new Builder(
            writer: $writer,
            data: $content,
            errorCorrectionLevel: $this->errorCorrection($attributes['error_correction'] ?? 'medium'),
            size: (int) $attributes['size'],
            margin: (int) $attributes['margin'],
            foregroundColor: new Color($foregroundRed, $foregroundGreen, $foregroundBlue),
            backgroundColor: new Color($backgroundRed, $backgroundGreen, $backgroundBlue),
            logoPath: $logoPath ?? '',
            logoResizeToWidth: $logoPath && $isRaster ? (int) max(32, (int) $attributes['size'] / 5) : null,
        );

        $result = $writer instanceof PngWriter
            ? $builder->build(logoPunchoutBackground: (bool) $logoPath)
            : $builder->build();

        if ($format === 'jpeg') {
            return $this->pngToJpeg($result->getString(), $backgroundRed, $backgroundGreen, $backgroundBlue);
        }

        if ($format !== 'pdf') {
            return $result->getString();
        }

        $dompdf = new Dompdf(new Options(['isRemoteEnabled' => false]));
        $dompdf->loadHtml('<html><body style="margin:0;text-align:center"><img style="width:100%;height:auto" src="'.$result->getDataUri().'"></body></html>');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function pngToJpeg(string $pngData, int $bgR, int $bgG, int $bgB): string
    {
        $src = imagecreatefromstring($pngData);
        $w = imagesx($src);
        $h = imagesy($src);
        $dst = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($dst, $bgR, $bgG, $bgB);
        imagefill($dst, 0, 0, $bg);
        imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
        ob_start();
        imagejpeg($dst, null, 95);
        $jpeg = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return $jpeg;
    }

    private function attributesFromModel(QrCodeModel $qrCode, ?int $sizeOverride = null): array
    {
        return [
            'foreground_color' => $qrCode->foreground_color,
            'background_color' => $qrCode->background_color,
            'size' => $sizeOverride ?? $qrCode->size,
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
