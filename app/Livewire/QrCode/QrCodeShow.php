<?php

namespace App\Livewire\QrCode;

use App\Models\QrCode;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrCodeShow extends Component
{
    public QrCode $qrCode;

    public string $previewSvg = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $printMaterial = '';

    public ?int $printCopies = null;

    public string $campaignStartAt = '';

    public string $campaignEndAt = '';

    public bool $editingCampaign = false;

    public function updatedDateFrom(): void
    {
        $this->dispatch('chartDataUpdated', $this->chartData);
    }

    public function updatedDateTo(): void
    {
        $this->dispatch('chartDataUpdated', $this->chartData);
    }

    public function mount(QrCode $qrCode, QrCodeService $service): void
    {
        Gate::authorize('view', $qrCode);

        $this->qrCode = $qrCode;
        $this->previewSvg = $service->downloadContents($qrCode, 'svg');

        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        $this->printMaterial = $qrCode->print_material ?? '';
        $this->printCopies = $qrCode->print_copies;
        $this->campaignStartAt = $qrCode->campaign_start_at?->format('Y-m-d') ?? '';
        $this->campaignEndAt = $qrCode->campaign_end_at?->format('Y-m-d') ?? '';
    }

    public function toggleActive(): void
    {
        Gate::authorize('update', $this->qrCode);

        $this->qrCode->update(['is_active' => ! $this->qrCode->is_active]);
        $this->qrCode->refresh();
    }

    public function saveCampaign(): void
    {
        Gate::authorize('update', $this->qrCode);

        $this->validate([
            'printMaterial' => ['nullable', 'string', 'max:150'],
            'printCopies' => ['nullable', 'integer', 'min:1', 'max:9999999'],
            'campaignStartAt' => ['nullable', 'date'],
            'campaignEndAt' => ['nullable', 'date', 'after_or_equal:campaignStartAt'],
        ]);

        $this->qrCode->update([
            'print_material' => filled($this->printMaterial) ? $this->printMaterial : null,
            'print_copies' => $this->printCopies,
            'campaign_start_at' => filled($this->campaignStartAt) ? $this->campaignStartAt : null,
            'campaign_end_at' => filled($this->campaignEndAt) ? $this->campaignEndAt : null,
        ]);

        $this->qrCode->refresh();
        $this->editingCampaign = false;
    }

    public function resetScans(): void
    {
        Gate::authorize('update', $this->qrCode);

        $this->qrCode->scans()->delete();
        $this->qrCode->refresh();
    }

    public function exportCsv(): StreamedResponse
    {
        Gate::authorize('view', $this->qrCode);

        $scans = $this->qrCode->scans()
            ->whereBetween('scanned_at', [
                $this->dateFrom.' 00:00:00',
                $this->dateTo.' 23:59:59',
            ])
            ->orderBy('scanned_at')
            ->get();

        $filename = 'scans-'.($this->qrCode->name ?? $this->qrCode->id).'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($scans): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Heure', 'Appareil', 'User-Agent']);

            foreach ($scans as $scan) {
                fputcsv($out, [
                    $scan->scanned_at->format('d/m/Y'),
                    $scan->scanned_at->format('H:i:s'),
                    ucfirst($scan->device ?? '-'),
                    $scan->user_agent ?? '-',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    #[Computed]
    public function totalScans(): int
    {
        return $this->qrCode->scans()
            ->whereBetween('scanned_at', [
                $this->dateFrom.' 00:00:00',
                $this->dateTo.' 23:59:59',
            ])
            ->count();
    }

    #[Computed]
    public function uniqueScans(): int
    {
        return $this->qrCode->scans()
            ->whereBetween('scanned_at', [
                $this->dateFrom.' 00:00:00',
                $this->dateTo.' 23:59:59',
            ])
            ->distinct('ip_hash')
            ->count('ip_hash');
    }

    #[Computed]
    public function allTimeScans(): int
    {
        return $this->qrCode->scans()->count();
    }

    #[Computed]
    public function chartData(): array
    {
        $from = \Carbon\Carbon::parse($this->dateFrom)->startOfDay();
        $to = \Carbon\Carbon::parse($this->dateTo)->endOfDay();

        $scans = $this->qrCode->scans()
            ->whereBetween('scanned_at', [$from, $to])
            ->selectRaw('DATE(scanned_at) as day, COUNT(*) as total, COUNT(DISTINCT ip_hash) as unique_total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $totals = [];
        $uniques = [];

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $totals[] = $scans->get($key)?->total ?? 0;
            $uniques[] = $scans->get($key)?->unique_total ?? 0;
        }

        return compact('labels', 'totals', 'uniques');
    }

    #[Computed]
    public function deviceBreakdown(): array
    {
        return $this->qrCode->scans()
            ->whereBetween('scanned_at', [
                $this->dateFrom.' 00:00:00',
                $this->dateTo.' 23:59:59',
            ])
            ->selectRaw('device, COUNT(*) as count')
            ->groupBy('device')
            ->pluck('count', 'device')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.qr-code.qr-code-show')
            ->layout('layouts.app', ['title' => $this->qrCode->name ?: 'Détail du QR Code']);
    }
}
