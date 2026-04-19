<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Central file upload rules + filename sanitization.
 *
 * Whitelist is intentionally narrow: images and PDFs only. Reject office docs,
 * archives, scripts, and anything that could execute server-side if mis-served.
 */
class FileUploadService
{
    public const MAX_SIZE_KB = 10240; // 10 MB

    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Validator rules for a required single-file field. `mimes` checks the
     * extension; `mimetypes` double-checks the actual content type — both are
     * required because extension alone is spoofable.
     */
    public static function rules(bool $required = true): array
    {
        $base = [
            'file',
            'max:'.self::MAX_SIZE_KB,
            'mimes:'.implode(',', self::ALLOWED_EXTENSIONS),
            'mimetypes:'.implode(',', self::ALLOWED_MIME_TYPES),
        ];

        array_unshift($base, $required ? 'required' : 'nullable');

        return $base;
    }

    public static function messages(): array
    {
        return [
            'file.mimes' => 'Only PDF and image files (jpg, jpeg, png, webp) are allowed.',
            'file.mimetypes' => 'The file content does not match an allowed type (PDF or image).',
            'file.max' => 'File must be under '.(self::MAX_SIZE_KB / 1024).' MB.',
        ];
    }

    /**
     * Build a hashed, sanitized filename. Never reuse client-supplied names on
     * disk — they can contain traversal, control chars, or double extensions.
     * Returns "<hash>.<ext>" — the original name is still captured separately.
     */
    public static function hashedFilename(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $ext = 'bin';
        }

        return bin2hex(random_bytes(16)).'.'.$ext;
    }
}
