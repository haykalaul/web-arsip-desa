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
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->string('document_path');
            $table->string('original_filename');
            $table->string('signature_hash')->unique();
            $table->string('barcode_data');
            $table->string('barcode_path');
            $table->string('verification_url');
            $table->timestamp('signed_at');
            $table->unsignedBigInteger('signed_by');
            $table->string('document_type')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->json('metadata')->nullable(); // Store additional document info
            $table->timestamps();

            $table->foreign('signed_by')->references('id')->on('users');
            $table->index(['signature_hash', 'status']);
            $table->index('signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
