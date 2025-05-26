<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DigitalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_name',
        'document_path',
        'original_filename',
        'signature_hash',
        'barcode_data',
        'barcode_path',
        'verification_url',
        'signed_at',
        'signed_by',
        'document_type',
        'description',
        'status',
        'metadata'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected $dates = [
        'signed_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user who signed the document
     */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Generate unique signature hash
     */
    public static function generateSignatureHash($documentName, $userId)
    {
        $timestamp = now()->timestamp;
        $randomString = Str::random(32);

        return hash('sha256', $documentName . $userId . $timestamp . $randomString);
    }

    /**
     * Generate barcode data
     */
    public function generateBarcodeData(): string
    {
        return url("/verify-signature/{$this->signature_hash}");
    }

    /**
     * Check if signature is valid
     */
    public function isValid(): bool
    {
        return $this->status === 'active' &&
            file_exists(storage_path('app/public/' . $this->document_path));
    }

    /**
     * Get document size in human readable format
     */
    public function getDocumentSizeAttribute(): string
    {
        if (file_exists(storage_path('app/public/' . $this->document_path))) {
            $bytes = filesize(storage_path('app/public/' . $this->document_path));
            $units = ['B', 'KB', 'MB', 'GB'];
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            return round($bytes / (1 << (10 * $pow)), 2) . ' ' . $units[$pow];
        }
        return 'Unknown';
    }

    /**
     * Get formatted signed date
     */
    public function getFormattedSignedDateAttribute(): string
    {
        return $this->signed_at->format('d F Y H:i:s');
    }
}
