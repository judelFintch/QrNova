<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'is_active',
        'print_material',
        'print_copies',
        'campaign_start_at',
        'campaign_end_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'size' => 'integer',
            'margin' => 'integer',
            'is_active' => 'boolean',
            'print_copies' => 'integer',
            'campaign_start_at' => 'datetime',
            'campaign_end_at' => 'datetime',
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

    public function scans(): HasMany
    {
        return $this->hasMany(QrCodeScan::class);
    }

    public function isExpired(): bool
    {
        return $this->campaign_end_at !== null && $this->campaign_end_at->isPast();
    }

    public function isAccessible(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function getDisplayUrlAttribute(): ?string
    {
        return data_get($this->options, 'form_data.content') ?: $this->content;
    }
}
