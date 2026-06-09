<?php

namespace App\Livewire\QrCode;

use App\Services\QrCodeService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class QrCodeGenerator extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $type = 'url';

    public array $data = ['content' => 'https://example.com'];

    public string $foregroundColor = '#111827';

    public string $backgroundColor = '#ffffff';

    public int $size = 320;

    public int $margin = 10;

    public string $format = 'png';

    public string $errorCorrection = 'medium';

    public ?TemporaryUploadedFile $logo = null;

    public ?string $previewSvg = null;

    public ?int $generatedId = null;

    public function mount(QrCodeService $service): void
    {
        $this->refreshPreview($service);
    }

    public function updatedType(): void
    {
        $this->reset('data', 'logo', 'previewSvg', 'generatedId');
        $this->data = $this->type === 'url' ? ['content' => 'https://example.com'] : [];
        $this->resetValidation();
    }

    public function updated($property): void
    {
        if ($property !== 'previewSvg' && $property !== 'generatedId') {
            $this->generatedId = null;
        }

        if (in_array($property, ['foregroundColor', 'backgroundColor', 'size', 'margin', 'errorCorrection'], true)) {
            $this->refreshPreview(app(QrCodeService::class));
        }
    }

    public function preview(QrCodeService $service): void
    {
        $this->validate($this->rules(), $this->messages());
        $this->refreshPreview($service);
    }

    public function generate(QrCodeService $service): void
    {
        $validated = $this->validate($this->rules(), $this->messages());
        $logoPath = $this->logo?->store('qr-logos', 'public');
        $qrCode = $service->create($this->serviceAttributes($validated), $logoPath);

        $this->generatedId = $qrCode->id;
        $this->previewSvg = $service->downloadContents($qrCode, 'svg');
        session()->flash('success', 'Votre QR Code a été généré et sauvegardé.');
    }

    public function render()
    {
        return view('livewire.qr-code.qr-code-generator', [
            'types' => QrCodeService::TYPES,
        ])->layout('layouts.app', ['title' => 'Générateur de QR Code']);
    }

    private function refreshPreview(QrCodeService $service): void
    {
        try {
            $this->previewSvg = $service->preview($this->serviceAttributes(), $this->logo?->getRealPath());
        } catch (\Throwable) {
            $this->previewSvg = null;
        }
    }

    private function serviceAttributes(?array $validated = null): array
    {
        return [
            'name' => $validated['name'] ?? $this->name,
            'type' => $this->type,
            'data' => $this->data,
            'foreground_color' => $this->foregroundColor,
            'background_color' => $this->backgroundColor,
            'size' => $this->size,
            'margin' => $this->margin,
            'format' => $this->format,
            'error_correction' => $this->errorCorrection,
        ];
    }

    private function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(array_keys(QrCodeService::TYPES))],
            'foregroundColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'backgroundColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'size' => ['required', 'integer', 'min:180', 'max:1200'],
            'margin' => ['required', 'integer', 'min:0', 'max:50'],
            'format' => ['required', Rule::in(['png', 'svg', 'pdf'])],
            'errorCorrection' => ['required', Rule::in(['low', 'medium', 'quartile', 'high'])],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];

        return array_merge($rules, match ($this->type) {
            'url', 'social' => ['data.content' => ['required', 'url:http,https', 'max:2048']],
            'text' => ['data.content' => ['required', 'string', 'max:4000']],
            'whatsapp' => [
                'data.phone' => ['required', 'regex:/^\+?[0-9\s().-]{7,20}$/'],
                'data.message' => ['nullable', 'string', 'max:1000'],
            ],
            'email' => [
                'data.email' => ['required', 'email:rfc', 'max:255'],
                'data.subject' => ['nullable', 'string', 'max:255'],
                'data.message' => ['nullable', 'string', 'max:2000'],
            ],
            'phone' => ['data.phone' => ['required', 'regex:/^\+?[0-9\s().-]{7,20}$/']],
            'wifi' => [
                'data.ssid' => ['required', 'string', 'max:100'],
                'data.password' => ['nullable', 'string', 'max:255'],
                'data.security' => ['required', Rule::in(['WPA', 'WEP', 'nopass'])],
            ],
            'sms' => [
                'data.phone' => ['required', 'regex:/^\+?[0-9\s().-]{7,20}$/'],
                'data.message' => ['required', 'string', 'max:1000'],
            ],
            'vcard' => [
                'data.first_name' => ['required', 'string', 'max:100'],
                'data.last_name' => ['required', 'string', 'max:100'],
                'data.phone' => ['nullable', 'regex:/^\+?[0-9\s().-]{7,20}$/'],
                'data.email' => ['nullable', 'email:rfc', 'max:255'],
                'data.company' => ['nullable', 'string', 'max:150'],
                'data.job_title' => ['nullable', 'string', 'max:150'],
                'data.website' => ['nullable', 'url:http,https', 'max:2048'],
                'data.address' => ['nullable', 'string', 'max:500'],
            ],
            'location' => [
                'data.latitude' => ['required', 'numeric', 'between:-90,90'],
                'data.longitude' => ['required', 'numeric', 'between:-180,180'],
            ],
            'event' => [
                'data.title' => ['required', 'string', 'max:200'],
                'data.starts_at' => ['required', 'date'],
                'data.ends_at' => ['required', 'date', 'after:data.starts_at'],
                'data.location' => ['nullable', 'string', 'max:300'],
                'data.description' => ['nullable', 'string', 'max:1000'],
            ],
            default => [],
        });
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Donnez un nom à votre QR Code.',
            'data.content.required' => 'Ce contenu est obligatoire.',
            'data.content.url' => 'Saisissez une URL complète et valide.',
            'data.phone.regex' => 'Saisissez un numéro de téléphone valide.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
        ];
    }
}
