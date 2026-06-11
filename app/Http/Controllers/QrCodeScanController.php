<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\QrCodeScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrCodeScanController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $qrCode = QrCode::where('public_token', $token)
            ->where('type', 'url')
            ->firstOrFail();

        QrCodeScan::create([
            'qr_code_id' => $qrCode->id,
            'ip_hash' => hash('sha256', $request->ip()),
            'device' => $this->detectDevice($request->userAgent() ?? ''),
            'user_agent' => Str::limit($request->userAgent() ?? '', 500),
            'scanned_at' => now(),
        ]);

        if (! $qrCode->isAccessible()) {
            abort(404);
        }

        $destination = data_get($qrCode->options, 'form_data.content', $qrCode->content);

        return redirect()->away($destination);
    }

    private function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);

        if (str_contains($ua, 'ipad') || (str_contains($ua, 'android') && ! str_contains($ua, 'mobile'))) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'iphone') || str_contains($ua, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }
}
