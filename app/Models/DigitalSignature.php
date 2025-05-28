<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        'metadata' => 'json'
    ];

    protected $dates = [
        'signed_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Override setAttribute untuk validasi UTF-8 pada metadata
     */
    public function setMetadataAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['metadata'] = null;
            return;
        }

        try {
            // Jika value adalah string, decode dulu
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

            // Bersihkan array dari karakter tidak valid UTF-8
            if (is_array($value)) {
                $value = $this->sanitizeArrayUtf8($value);
            } else {
                $value = $this->sanitizeStringUtf8($value);
            }

            // Validasi final sebelum encoding
            $value = $this->validateAndCleanData($value);

            // Encode dengan flag untuk menangani UTF-8
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_IGNORE);

            if ($encoded === false || json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Failed to encode metadata to JSON', [
                    'error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'data_preview' => $this->getDataPreview($value)
                ]);

                // Fallback: gunakan array kosong jika encoding gagal
                $this->attributes['metadata'] = json_encode([], JSON_UNESCAPED_UNICODE);
            } else {
                $this->attributes['metadata'] = $encoded;
            }

        } catch (\Exception $e) {
            Log::error('Exception while setting metadata attribute', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data_preview' => $this->getDataPreview($value)
            ]);

            // Fallback: set metadata sebagai array kosong
            $this->attributes['metadata'] = json_encode([], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Validasi dan bersihkan data secara menyeluruh
     */
    private function validateAndCleanData($data)
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleanKey = $this->sanitizeStringUtf8((string)$key);

                if (is_array($value)) {
                    $cleaned[$cleanKey] = $this->validateAndCleanData($value);
                } else {
                    $cleaned[$cleanKey] = $this->sanitizeStringUtf8($value);
                }
            }
            return $cleaned;
        }

        return $this->sanitizeStringUtf8($data);
    }

    /**
     * Sanitize array untuk menghilangkan karakter UTF-8 yang tidak valid
     */
    private function sanitizeArrayUtf8($array)
    {
        if (!is_array($array)) {
            return $this->sanitizeStringUtf8($array);
        }

        $sanitized = [];
        foreach ($array as $key => $value) {
            $cleanKey = $this->sanitizeStringUtf8((string)$key);

            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeArrayUtf8($value);
            } elseif (is_object($value)) {
                // Convert object to array first
                $sanitized[$cleanKey] = $this->sanitizeArrayUtf8((array)$value);
            } else {
                $sanitized[$cleanKey] = $this->sanitizeStringUtf8($value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize string untuk menghilangkan karakter UTF-8 yang tidak valid
     */
    private function sanitizeStringUtf8($input)
    {
        // Return as-is for non-string values
        if (!is_string($input)) {
            if (is_numeric($input) || is_bool($input) || is_null($input)) {
                return $input;
            }
            // Convert other types to string
            $input = (string)$input;
        }

        // Step 1: Remove null bytes and other problematic characters
        $cleaned = str_replace(["\0", "\x00"], '', $input);

        // Step 2: Fix encoding issues
        $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'UTF-8');

        // Step 3: Remove or replace control characters (except common ones like \n, \r, \t)
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);

        // Step 4: Remove high-bit characters that might cause issues
        $cleaned = preg_replace('/[\x80-\x9F]/', '', $cleaned);

        // Step 5: Ensure valid UTF-8
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            // Force UTF-8 conversion
            $cleaned = mb_convert_encoding($cleaned, 'UTF-8', mb_detect_encoding($cleaned, 'UTF-8, UTF-16, ISO-8859-1, ASCII', true));
        }

        // Step 6: Final validation
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            Log::warning('Unable to create valid UTF-8 string', ['original' => substr($input, 0, 100)]);
            return ''; // Return empty string if all else fails
        }

        return $cleaned ?: '';
    }

    /**
     * Get preview data untuk logging (tanpa expose sensitive info)
     */
    private function getDataPreview($data)
    {
        try {
            if (is_array($data)) {
                $preview = [];
                $count = 0;
                foreach ($data as $key => $value) {
                    if ($count >= 5) break;

                    if (is_string($value)) {
                        $preview[$key] = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                    } elseif (is_array($value)) {
                        $preview[$key] = '[Array with ' . count($value) . ' elements]';
                    } else {
                        $preview[$key] = '[' . gettype($value) . ']';
                    }
                    $count++;
                }
                return $preview;
            }

            if (is_string($data)) {
                return strlen($data) > 100 ? substr($data, 0, 100) . '...' : $data;
            }

            return '[' . gettype($data) . ']';
        } catch (\Exception $e) {
            return '[Preview generation failed]';
        }
    }

    /**
     * Override toJson method untuk handling error
     */
    public function toJson($options = 0)
    {
        try {
            return json_encode(
                $this->jsonSerialize(),
                $options | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE
            );
        } catch (\Exception $e) {
            Log::error('toJson failed for DigitalSignature', [
                'error' => $e->getMessage(),
                'model_id' => $this->id ?? 'new'
            ]);

            // Return minimal JSON on failure
            return json_encode(['error' => 'JSON serialization failed'], JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
    }

    /**
     * Override jsonSerialize untuk memastikan data clean
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        // Clean metadata sebelum serialization
        if (isset($data['metadata'])) {
            $data['metadata'] = $this->sanitizeArrayUtf8($data['metadata']);
        }

        return $data;
    }

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

    /**
     * Helper method untuk membersihkan metadata sebelum save
     */
    public static function cleanMetadataBeforeSave($metadata)
    {
        if (is_null($metadata)) {
            return null;
        }

        // Buat instance sementara untuk menggunakan method sanitize
        $tempInstance = new self();

        return $tempInstance->validateAndCleanData($metadata);
    }

    /**
     * Boot method untuk auto-clean metadata
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-clean metadata sebelum save
            if ($model->isDirty('metadata') && !is_null($model->metadata)) {
                $model->metadata = static::cleanMetadataBeforeSave($model->metadata);
            }
        });
    }
}
