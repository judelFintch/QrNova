<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type', 30);
            $table->longText('content');
            $table->json('options')->nullable();
            $table->string('file_path')->nullable();
            $table->string('format', 10)->default('png');
            $table->string('foreground_color', 7)->default('#000000');
            $table->string('background_color', 7)->default('#ffffff');
            $table->integer('size')->default(300);
            $table->integer('margin')->default(2);
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
