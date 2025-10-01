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
        Schema::create('extracted_manifest_rows', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // To track the upload session
            $table->string('file_name'); // Original uploaded file name
            $table->integer('row_number'); // Row number in Excel file
            $table->string('row_type')->default('unknown'); // header, container, hbl, package, consignee
            
            // Raw data fields - storing exactly as extracted from Excel
            $table->json('raw_data'); // Complete row data as JSON
            $table->text('extracted_text')->nullable(); // Cleaned/concatenated text from the row
            
            // Identified data type fields
            $table->string('obl_number')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('shipper_info')->nullable();
            $table->string('consignee_info')->nullable();
            $table->string('container_number')->nullable();
            $table->string('container_type')->nullable();
            
            // HBL related fields
            $table->string('hbl_number')->nullable();
            $table->string('hbl_name')->nullable();
            $table->string('hbl_contact')->nullable();
            $table->string('hbl_address')->nullable();
            
            // Package information
            $table->string('package_type')->nullable();
            $table->string('package_quantity')->nullable();
            $table->string('package_weight')->nullable();
            $table->string('package_volume')->nullable();
            $table->string('package_description')->nullable();
            
            // Processing status
            $table->boolean('is_processed')->default(false);
            $table->string('processing_status')->default('pending'); // pending, processed, error, skipped
            $table->text('processing_notes')->nullable();
            $table->json('validation_errors')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['session_id', 'row_type']);
            $table->index(['session_id', 'is_processed']);
            $table->index(['obl_number']);
            $table->index(['container_number']);
            $table->index(['hbl_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracted_manifest_rows');
    }
};