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
        Schema::create('container_unloading_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')
                ->constrained('containers')
                ->cascadeOnDelete();
            
            // Action type: 'unload' or 'reload'
            $table->enum('action', ['unload', 'reload']);
            
            // Level: 'package', 'hbl', 'mhbl'
            $table->enum('level', ['package', 'hbl', 'mhbl']);
            
            // Reference IDs based on level
            $table->foreignId('hbl_package_id')
                ->nullable()
                ->constrained('hbl_packages')
                ->cascadeOnDelete();
            
            $table->foreignId('hbl_id')
                ->nullable()
                ->constrained('hbl')
                ->cascadeOnDelete();
            
            $table->foreignId('mhbl_id')
                ->nullable()
                ->constrained('mhbls')
                ->cascadeOnDelete();
            
            // Metadata
            $table->string('hbl_number')->nullable();
            $table->string('mhbl_number')->nullable();
            $table->integer('package_count')->default(1);
            
            // Package details (for single package operations)
            $table->json('package_details')->nullable();
            
            // Batch details (for HBL/MHBL level operations)
            $table->json('packages_affected')->nullable();
            
            // User who performed the action
            $table->foreignId('performed_by')
                ->constrained('users')
                ->cascadeOnDelete();
            
            // Additional context
            $table->text('notes')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['container_id', 'action', 'created_at'], 'culog_container_action_date_idx');
            $table->index(['hbl_id', 'action'], 'culog_hbl_action_idx');
            $table->index(['mhbl_id', 'action'], 'culog_mhbl_action_idx');
            $table->index('performed_by', 'culog_performed_by_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_unloading_audit_logs');
    }
};
