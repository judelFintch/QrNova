<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QrCodeDownloadController extends Controller
{
    public function __invoke(Request $request, QrCode $qrCode, QrCodeService $service)
    {
        $validated = $request->validate([
            'format' => ['nullable', Rule::in(['png', 'svg', 'pdf'])],
        ]);
        $format = $validated['format'] ?? $qrCode->format;
        $mime = match ($format) {
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            default => 'image/png',
        };

        return response($service->downloadContents($qrCode, $format), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="'.$service->filename($qrCode, $format).'"',
        ]);
    }
}
