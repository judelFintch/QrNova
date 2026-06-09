<?php

namespace App\Livewire\QrCode;

use App\Models\QrCode;
use App\Services\QrCodeService;
use Livewire\Component;

class QrCodeShow extends Component
{
    public QrCode $qrCode;

    public string $previewSvg = '';

    public function mount(QrCode $qrCode, QrCodeService $service): void
    {
        $this->qrCode = $qrCode;
        $this->previewSvg = $service->downloadContents($qrCode, 'svg');
    }

    public function render()
    {
        return view('livewire.qr-code.qr-code-show')
            ->layout('layouts.app', ['title' => $this->qrCode->name ?: 'Détail du QR Code']);
    }
}
