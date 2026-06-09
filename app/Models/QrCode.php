<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class QrCode extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'content',
        'public_token',
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
            $photoPath = data_get($qrCode->options, 'form_data.photo_path');

            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
