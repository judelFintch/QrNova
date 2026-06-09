<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\View\View;

class ProgressiveQrCodeController extends Controller
{
    public function __invoke(string $token): View
    {
        $qrCode = QrCode::query()
            ->where('type', 'progressive')
            ->where('public_token', $token)
            ->firstOrFail();

        return view('pages.progressive-qr-code', [
            'qrCode' => $qrCode,
            'profile' => data_get($qrCode->options, 'form_data', []),
        ]);
    }
}
