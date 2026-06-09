<?php

namespace App\Livewire\QrCode;

use App\Models\QrCode;
use App\Services\QrCodeService;
use Livewire\Component;
use Livewire\WithPagination;

class QrCodeIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        QrCode::findOrFail($id)->delete();
        session()->flash('success', 'Le QR Code a été supprimé.');
    }

    public function render()
    {
        $qrCodes = QrCode::query()
            ->when($this->search, fn ($query) => $query->where(function ($query): void {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('content', 'like', '%'.$this->search.'%');
            }))
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->latest()
            ->paginate(9);

        return view('livewire.qr-code.qr-code-index', [
            'qrCodes' => $qrCodes,
            'types' => QrCodeService::TYPES,
        ])->layout('layouts.app', ['title' => 'Historique des QR Codes']);
    }
}
