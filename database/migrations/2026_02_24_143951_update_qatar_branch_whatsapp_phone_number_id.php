<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Qatar branch with existing WhatsApp Phone Number ID from .env
        DB::table('branches')
            ->where('country', 'Qatar')
            ->update(['whatsapp_phone_number_id' => '535208863012287']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove WhatsApp Phone Number ID from Qatar branch
        DB::table('branches')
            ->where('country', 'Qatar')
            ->update(['whatsapp_phone_number_id' => null]);
    }
};
