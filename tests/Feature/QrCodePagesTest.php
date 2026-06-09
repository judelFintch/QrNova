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

    public function test_a_progressive_qr_code_keeps_its_public_url_when_information_is_added_later(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class)
            ->set('name', 'Contact progressif')
            ->set('type', 'progressive')
            ->set('data.phone', '+243 999 000 111')
            ->call('generate')
            ->assertHasNoErrors();

        $qrCode = QrCode::firstOrFail();
        $publicUrl = $qrCode->content;

        $this->assertNotNull($qrCode->public_token);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->get($publicUrl)
            ->assertOk()
            ->assertSee('+243 999 000 111')
            ->assertSee('Partager le lien')
            ->assertSee($publicUrl)
            ->assertDontSee('Nouvelle adresse');

        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class, ['qrCode' => $qrCode])
            ->set('data.address', 'Nouvelle adresse')
            ->set('profilePhoto', UploadedFile::fake()->image('profil.jpg', 300, 300))
            ->call('generate')
            ->assertHasNoErrors();

        $qrCode->refresh();

        $this->assertSame($publicUrl, $qrCode->content);
        $this->assertSame(1, QrCode::count());
        Storage::disk('public')->assertExists(data_get($qrCode->options, 'form_data.photo_path'));

        $this->get($publicUrl)
            ->assertOk()
            ->assertSee('Nouvelle adresse');
    }

    public function test_custom_fields_can_be_added_and_removed_from_a_progressive_profile(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class)
            ->set('name', 'Profil personnalisable')
            ->set('type', 'progressive')
            ->set('data.phone', '+243 999 000 111')
            ->call('addProgressiveField')
            ->call('addProgressiveField')
            ->set('data.custom_fields.0.label', 'Champ à supprimer')
            ->set('data.custom_fields.0.value', 'Ancienne valeur')
            ->set('data.custom_fields.1.label', 'Matricule')
            ->set('data.custom_fields.1.value', 'QR-2026-001')
            ->call('removeProgressiveField', 0)
            ->assertSet('data.custom_fields.0.label', 'Matricule')
            ->call('generate')
            ->assertHasNoErrors();

        $qrCode = QrCode::firstOrFail();

        $this->assertSame([
            ['label' => 'Matricule', 'value' => 'QR-2026-001'],
        ], data_get($qrCode->options, 'form_data.custom_fields'));

        $this->get($qrCode->content)
            ->assertOk()
            ->assertSee('Matricule')
            ->assertSee('QR-2026-001')
            ->assertDontSee('Champ à supprimer');
    }

    public function test_initial_progressive_fields_can_be_removed_and_restored(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class)
            ->set('name', 'Profil initial')
            ->set('type', 'progressive')
            ->set('data.phone', '+243 999 000 111')
            ->set('data.address', 'Adresse à supprimer')
            ->set('profilePhoto', UploadedFile::fake()->image('profil.jpg', 300, 300))
            ->call('generate')
            ->assertHasNoErrors();

        $qrCode = QrCode::firstOrFail();
        $photoPath = data_get($qrCode->options, 'form_data.photo_path');

        Livewire::test(QrCodeGenerator::class, ['qrCode' => $qrCode])
            ->call('removeProgressiveInitialField', 'phone')
            ->call('removeProgressiveInitialField', 'address')
            ->call('removeProgressiveInitialField', 'photo_path')
            ->assertSet('data.phone', null)
            ->assertSet('data.address', null)
            ->assertSet('data.photo_path', null)
            ->call('restoreProgressiveInitialField', 'phone')
            ->assertSet('data.hidden_fields', ['address', 'photo_path'])
            ->call('generate')
            ->assertHasNoErrors();

        $qrCode->refresh();

        Storage::disk('public')->assertMissing($photoPath);
        $this->assertNull(data_get($qrCode->options, 'form_data.phone'));
        $this->assertNull(data_get($qrCode->options, 'form_data.address'));
        $this->get($qrCode->content)
            ->assertOk()
            ->assertDontSee('+243 999 000 111')
            ->assertDontSee('Adresse à supprimer');
    }

    public function test_progressive_qr_code_management_page_displays_share_actions(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(QrCodeGenerator::class)
            ->set('name', 'Profil à partager')
            ->set('type', 'progressive')
            ->set('data.phone', '+243 999 000 111')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSee('Partager le lien');

        $qrCode = QrCode::firstOrFail();

        $this->get(route('qr-code.index'))
            ->assertOk()
            ->assertSee('Partager');
        $this->get(route('qr-code.show', $qrCode))
            ->assertOk()
            ->assertSee('Partager le lien')
            ->assertSee('WhatsApp')
            ->assertSee($qrCode->content);
    }
}
