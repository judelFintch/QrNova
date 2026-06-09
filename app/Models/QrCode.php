<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QrCode extends Model
{
    protected $fillable = [
        'name',
        'type',
        'content',
        'options',
        'file_path',
        'format',
        'foreground_color',
        'background_color',
        'size',
        'margin',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'size' => 'integer',
            'margin' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (QrCode $qrCode): void {
            if ($qrCode->file_path) {
                Storage::disk('public')->delete($qrCode->file_path);
            }

            $logoPath = data_get($qrCode->options, 'logo_path');

            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
        });
    }
}
