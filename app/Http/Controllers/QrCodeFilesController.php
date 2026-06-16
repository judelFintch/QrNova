<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class QrCodeFilesController extends Controller
{
    public function __invoke(string $token): View
    {
        $qrCode = QrCode::where('public_token', $token)
            ->where('type', 'file')
            ->firstOrFail();

        if (! $qrCode->isAccessible()) {
            abort(404);
        }

        $files = collect(data_get($qrCode->options, 'form_data.uploaded_files', []))
            ->map(fn ($file) => [
                'name' => $file['name'] ?? basename($file['path']),
                'size' => $file['size'] ?? 0,
                'url' => Storage::disk('public')->exists($file['path'])
                    ? Storage::disk('public')->url($file['path'])
                    : null,
            ])
            ->filter(fn ($file) => $file['url'] !== null)
            ->values();

        return view('pages.qr-code-files', compact('qrCode', 'files'));
    }
}
