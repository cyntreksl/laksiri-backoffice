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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_contact_id')
                ->constrained('whatsapp_contacts')
                ->onDelete('cascade');
            $table->text('message');
            $table->enum('message_type', ['sent', 'received']);
            $table->string('message_id')->nullable(); // WhatsApp message ID from API
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'read', 'failed'])
                ->default('pending');
            $table->json('metadata')->nullable(); // For storing additional message data
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['whatsapp_contact_id', 'created_at']);
            $table->index(['message_type', 'is_read']);
            $table->index('message_id');
            $table->index('delivery_status');
            $table->index('sent_at');
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
