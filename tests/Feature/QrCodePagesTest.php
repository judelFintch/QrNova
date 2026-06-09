<?php

namespace Tests\Feature;

use App\Livewire\QrCode\QrCodeGenerator;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_an_existing_qr_code_can_be_modified_and_regenerated(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class)
            ->set('name', 'Ancien nom')
            ->set('data.content', 'https://old.example.com')
            ->call('generate')
            ->assertHasNoErrors();

        $qrCode = QrCode::firstOrFail();
        $oldPath = $qrCode->file_path;

        $this->get(route('qr-code.edit', $qrCode))
            ->assertOk()
            ->assertSee('Modifier le QR Code');

        Livewire::test(QrCodeGenerator::class, ['qrCode' => $qrCode])
            ->assertSet('name', 'Ancien nom')
            ->assertSet('data.content', 'https://old.example.com')
            ->set('name', 'Nouveau nom')
            ->set('data.content', 'https://new.example.com')
            ->set('format', 'svg')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('generatedId', $qrCode->id);

        $qrCode->refresh();

        $this->assertSame('Nouveau nom', $qrCode->name);
        $this->assertSame('https://new.example.com', $qrCode->content);
        $this->assertSame('svg', $qrCode->format);
        $this->assertSame(1, QrCode::count());
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($qrCode->file_path);
    }

    public function test_a_qr_code_with_logo_can_be_previewed_and_downloaded_as_svg(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class)
            ->set('name', 'QR avec logo')
            ->set('data.content', 'https://example.com')
            ->set('logo', UploadedFile::fake()->image('logo.png', 100, 100))
            ->call('preview')
            ->assertHasNoErrors()
            ->call('generate')
            ->assertHasNoErrors();

        $qrCode = QrCode::firstOrFail();

        $this->get(route('qr-code.show', $qrCode))->assertOk();
        $this->get(route('qr-code.download', [$qrCode, 'format' => 'svg']))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');
    }
}
