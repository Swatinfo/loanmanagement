<?php

namespace Tests\Unit;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class FileUploadServiceTest extends TestCase
{
    public function test_rules_require_file_by_default(): void
    {
        $rules = FileUploadService::rules();

        $this->assertSame('required', $rules[0]);
        $this->assertContains('file', $rules);
    }

    public function test_rules_accept_nullable(): void
    {
        $rules = FileUploadService::rules(required: false);

        $this->assertSame('nullable', $rules[0]);
    }

    public function test_rules_whitelist_only_images_and_pdf(): void
    {
        $rules = FileUploadService::rules();

        $mimes = collect($rules)->first(fn ($r) => str_starts_with((string) $r, 'mimes:'));
        $mimetypes = collect($rules)->first(fn ($r) => str_starts_with((string) $r, 'mimetypes:'));

        $this->assertSame('mimes:pdf,jpg,jpeg,png,webp', $mimes);
        $this->assertStringContainsString('application/pdf', $mimetypes);
        $this->assertStringContainsString('image/jpeg', $mimetypes);
        $this->assertStringContainsString('image/png', $mimetypes);
        $this->assertStringContainsString('image/webp', $mimetypes);
    }

    public function test_rules_enforce_10mb_size_limit(): void
    {
        $rules = FileUploadService::rules();

        $this->assertContains('max:10240', $rules);
    }

    public function test_hashed_filename_strips_client_extension_outside_whitelist(): void
    {
        $file = UploadedFile::fake()->create('evil.php', 10);

        $name = FileUploadService::hashedFilename($file);

        $this->assertStringEndsWith('.bin', $name);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.bin$/', $name);
    }

    public function test_hashed_filename_keeps_allowed_extension(): void
    {
        $file = UploadedFile::fake()->create('invoice.pdf', 10);

        $name = FileUploadService::hashedFilename($file);

        $this->assertStringEndsWith('.pdf', $name);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.pdf$/', $name);
    }

    public function test_hashed_filename_is_unique_per_call(): void
    {
        $file = UploadedFile::fake()->create('a.pdf', 1);

        $this->assertNotSame(
            FileUploadService::hashedFilename($file),
            FileUploadService::hashedFilename($file),
        );
    }
}
