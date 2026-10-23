<?php

namespace Tests\Feature;

use App\Http\Controllers\ProfileController;
use App\Services\ImageUploadSecurity;
use App\Services\SecureFileUpload;
use App\Services\SupabaseStorage;
use App\Services\UserImportFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UploadSecurityTest extends TestCase
{
    private function png(string $name = 'photo.png'): UploadedFile
    {
        // A real 1x1 PNG; tests do not require the optional GD extension.
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lXcAAAAASUVORK5CYII='
        ))->mimeType('image/png');
    }

    public function test_real_images_are_stored_with_generated_safe_names(): void
    {
        Storage::fake('public');
        $path = (new SecureFileUpload())->validateAndStore($this->png(), 'concerns', 'concerns');
        $this->assertMatchesRegularExpression('~^concerns/[a-f0-9]{32}\.png$~', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_local_storage_rejects_php_disguised_as_an_image(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->createWithContent('photo.jpg', '<?php echo "payload";');
        $this->assertNull((new SecureFileUpload())->validateAndStore($file, 'concerns', 'concerns'));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_cloud_storage_rejects_executable_extension_even_with_real_image_content(): void
    {
        Http::fake();
        try {
            (new SupabaseStorage())->upload($this->png('photo.php'), 'concerns');
            $this->fail('Executable extension was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
        }
        Http::assertNothingSent();
    }

    public function test_cloud_upload_uses_the_detected_image_type(): void
    {
        config(['services.supabase.url' => 'https://example.supabase.co', 'services.supabase.key' => 'test']);
        Http::fake(['*' => Http::response([], 200)]);
        $url = (new SupabaseStorage())->upload($this->png('photo.JPG'), 'profile_pictures');
        $this->assertMatchesRegularExpression('~/profile_pictures/[a-f0-9]{32}\.png$~', $url);
    }

    public function test_profile_rejects_invalid_upload_before_storage_or_user_changes(): void
    {
        Http::fake();
        $request = Request::create('/profile/upload-picture', 'POST', [], [], [
            'profile_picture' => UploadedFile::fake()->createWithContent('photo.jpg', '<?php echo 1;'),
        ]);
        $this->expectException(ValidationException::class);
        app(ProfileController::class)->uploadProfilePicture($request);
    }

    public function test_oversized_image_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        ImageUploadSecurity::validate(UploadedFile::fake()->create('photo.png', 2049, 'image/png'));
    }

    public function test_standard_csv_remains_supported(): void
    {
        $rows = (new UserImportFile())->rows(UploadedFile::fake()->createWithContent(
            'users.csv', "email,name\nstudent@example.com,Student\n"
        ), false, false);
        $this->assertSame(['student@example.com', 'Student'], $rows[1]);
    }

    public function test_import_rejects_executable_extensions(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows(UploadedFile::fake()->createWithContent('users.php', "a,b\nc,d"), false, false);
    }

    public function test_import_rejects_binary_files_renamed_to_csv(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows(UploadedFile::fake()->createWithContent('users.csv', "a,b\n\x00binary"), false, false);
    }

    public function test_import_rejects_code_renamed_to_csv(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows(UploadedFile::fake()->createWithContent('users.csv', "<?php echo 1;\na,b"), false, false);
    }

    public function test_import_rejects_more_than_10000_rows(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows(UploadedFile::fake()->createWithContent('users.csv', str_repeat("a,b\n", 10001)), false, false);
    }

    public function test_import_rejects_excessive_columns(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows(UploadedFile::fake()->createWithContent('users.csv', str_repeat('a,', 101)."\na,b"), false, false);
    }

    public function test_import_rejects_oversized_files(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows(UploadedFile::fake()->create('users.csv', 2049, 'text/csv'), false, false);
    }

    public function test_import_rejects_invalid_workbook(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows(UploadedFile::fake()->createWithContent('users.xlsx', 'invalid workbook'), false, false);
    }

    private function workbook(array $extraEntries = []): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upload-test-');
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::OVERWRITE);
        $entries = [
            '[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/></Types>',
            '_rels/.rels' => '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Users" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:B2"/><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>email</t></is></c><c r="B1" t="inlineStr"><is><t>name</t></is></c></row><row r="2"><c r="A2" t="inlineStr"><is><t>student@example.com</t></is></c><c r="B2" t="inlineStr"><is><t>Student</t></is></c></row></sheetData></worksheet>',
        ];
        foreach (array_merge($entries, $extraEntries) as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        try {
            return UploadedFile::fake()->createWithContent('users.xlsx', file_get_contents($path));
        } finally {
            unlink($path);
        }
    }

    public function test_valid_xlsx_remains_supported(): void
    {
        $rows = (new UserImportFile())->rows($this->workbook(), false, false);
        $this->assertSame(['student@example.com', 'Student'], $rows[1]);
    }

    public function test_workbook_with_macros_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows($this->workbook(['xl/vbaProject.bin' => 'macro']), false, false);
    }

    public function test_compressed_workbook_exceeding_expansion_limit_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows($this->workbook(['xl/large.xml' => str_repeat('A', 21 * 1024 * 1024)]), false, false);
    }

    public function test_workbook_relationships_with_xml_entities_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new UserImportFile())->rows($this->workbook([
            'xl/_rels/extra.rels' => '<!DOCTYPE x [<!ENTITY test SYSTEM "file:///etc/passwd">]><x>&test;</x>',
        ]), false, false);
    }

    public function test_vercel_storage_routes_block_scripts_and_allow_images(): void
    {
        $routes = json_decode(file_get_contents(base_path('vercel.json')), true, 512, JSON_THROW_ON_ERROR)['routes'];
        foreach (['/storage/concerns/photo.png' => 200, '/storage/concerns/script.php' => 403,
            '/storage/concerns/script.PHP.jpg' => 403, '/storage/concerns/script.php/extra' => 403,
            '/storage/profile_pictures/picture.JPG' => 200, '/storage/page.html' => 403,
            '/storage/.user.ini' => 403, '/storage/payload.unknown' => 403] as $path => $expected) {
            foreach ($routes as $route) {
                $flags = ($route['caseSensitive'] ?? true) ? '' : 'i';
                if (preg_match('~^'.$route['src'].'$~'.$flags, $path)) {
                    $this->assertSame($expected, $route['status'] ?? 200, $path);
                    break;
                }
            }
        }
    }
}
