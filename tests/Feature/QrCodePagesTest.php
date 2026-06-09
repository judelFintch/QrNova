<?php

namespace Tests\Feature;

use App\Livewire\QrCode\QrCodeGenerator;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QrCodePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_code_pages_require_authentication(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/qr-code/generator')->assertRedirect(route('login'));
        $this->get('/qr-codes')->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_qr_code_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertOk();
        $this->get('/qr-code/generator')->assertOk();
        $this->get('/qr-codes')->assertOk();
    }

    public function test_a_url_qr_code_can_be_generated_and_downloaded(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class)
            ->set('name', 'Site public')
            ->set('data.content', 'https://example.com')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('generatedId', 1);

        $qrCode = QrCode::firstOrFail();

        Storage::disk('public')->assertExists($qrCode->file_path);
        $this->get(route('qr-code.download', [$qrCode, 'format' => 'svg']))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');
        $this->get(route('qr-code.download', [$qrCode, 'format' => 'png']))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
        $this->get(route('qr-code.download', [$qrCode, 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
