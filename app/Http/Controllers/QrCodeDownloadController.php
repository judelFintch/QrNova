<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class QrCodeDownloadController extends Controller
{
    public const FORMATS = ['png', 'jpeg', 'svg', 'pdf', 'eps'];

    public const SIZES = [500, 1000, 2000, 4000];

    public function __invoke(Request $request, QrCode $qrCode, QrCodeService $service)
    {
        Gate::authorize('view', $qrCode);

        $validated = $request->validate([
            'format' => ['nullable', Rule::in(self::FORMATS)],
            'size' => ['nullable', Rule::in(self::SIZES)],
        ]);

        $format = $validated['format'] ?? $qrCode->format;
        $size = isset($validated['size']) ? (int) $validated['size'] : null;

        $mime = match ($format) {
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'eps' => 'application/postscript',
            'jpeg' => 'image/jpeg',
            default => 'image/png',
        };

        return response($service->downloadContents($qrCode, $format, $size), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="'.$service->filename($qrCode, $format).'"',
        ]);
    }
}
