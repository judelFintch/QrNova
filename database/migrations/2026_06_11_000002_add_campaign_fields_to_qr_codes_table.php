<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('user_id');
            $table->string('print_material')->nullable()->after('is_active');
            $table->unsignedInteger('print_copies')->nullable()->after('print_material');
            $table->timestamp('campaign_start_at')->nullable()->after('print_copies');
            $table->timestamp('campaign_end_at')->nullable()->after('campaign_start_at');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'print_material', 'print_copies', 'campaign_start_at', 'campaign_end_at']);
        });
    }
};
