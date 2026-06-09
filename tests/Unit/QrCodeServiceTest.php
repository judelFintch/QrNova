<?php

namespace Tests\Unit;

use App\Services\QrCodeService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QrCodeServiceTest extends TestCase
{
    private QrCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QrCodeService;
    }

    public function test_it_builds_supported_content_formats(): void
    {
        $this->assertSame('https://example.com', $this->service->buildContent('url', ['content' => ' https://example.com ']));
        $this->assertSame('https://wa.me/243999000111?text=Bonjour%20vous', $this->service->buildContent('whatsapp', ['phone' => '+243 999 000 111', 'message' => 'Bonjour vous']));
        $this->assertSame('SMSTO:+243999000111:Test', $this->service->buildContent('sms', ['phone' => '+243 999 000 111', 'message' => 'Test']));
    }

    public function test_it_rejects_an_unknown_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->buildContent('unknown', []);
    }
}
