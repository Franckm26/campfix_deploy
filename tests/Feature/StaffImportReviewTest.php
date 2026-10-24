<?php

namespace Tests\Feature;

use App\Http\Controllers\StaffImportController;
use App\Models\User;
use App\Models\WelcomeEmailDelivery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StaffImportReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropAllTables();
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid'); $table->string('name'); $table->string('email')->unique();
            $table->string('password'); $table->string('role'); $table->json('permissions')->nullable();
            $table->string('student_id')->nullable(); $table->string('department')->nullable(); $table->string('level')->nullable();
            foreach (['is_superadmin', 'is_admin', 'is_deleted', 'is_archived', 'force_password_change'] as $column) $table->boolean($column)->default(false);
            $table->unsignedBigInteger('archive_folder_id')->nullable();
            $table->unsignedInteger('failed_login_attempts')->default(0); $table->unsignedInteger('otp_attempts')->default(0); $table->timestamps();
        });
        Schema::create('user_archive_folders', function (Blueprint $table) { $table->id(); $table->string('name'); $table->unsignedInteger('user_count')->default(0); $table->timestamps(); });
        Schema::create('welcome_email_deliveries', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->text('encrypted_password');
            $table->string('status'); $table->unsignedInteger('attempts'); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable(); $table->unsignedBigInteger('concern_id')->nullable();
            foreach (['action', 'description', 'ip_address', 'user_agent', 'old_values', 'new_values', 'metadata'] as $column) $table->text($column)->nullable();
            $table->timestamps();
        });
        $this->actingAs(User::forceCreate(['name' => 'Administrator', 'email' => 'admin@example.com',
            'password' => 'unchanged', 'role' => 'superadmin', 'is_superadmin' => true]));
    }

    private function request(array $data, array $files = []): Request
    {
        $request = Request::create('/admin/users/import/staff', 'POST', $data, [], $files);
        $request->headers->set('Accept', 'application/json');
        $request->setLaravelSession(app('session.store'));
        $request->setUserResolver(fn () => auth()->user());
        return $request;
    }

    private function preview(string $csv = "Name,Email,Role\nJane Doe,jane@example.com,MIS\nJohn Doe,john@example.com,Maintenance\n", string $filename = 'staff.csv', string $format = 'masterlist'): array
    {
        return app(StaffImportController::class)->preview($this->request(['file_format' => $format],
            ['file' => UploadedFile::fake()->createWithContent($filename, $csv)]))->getData(true);
    }

    private function confirm(array $preview, ?array $rows = null)
    {
        $rows ??= array_map(fn ($row) => array_intersect_key($row, array_flip(['row_id', 'name', 'email', 'role'])), $preview['rows']);
        return app(StaffImportController::class)->confirm($this->request(['preview_token' => $preview['token'], 'rows' => $rows]));
    }

    public function test_upload_is_a_draft_without_creating_accounts_or_exposing_passwords(): void
    {
        $preview = $this->preview();
        $this->assertCount(2, $preview['rows']);
        $this->assertSame('mis', $preview['rows'][0]['role']);
        $this->assertSame('maintenance', $preview['rows'][1]['role']);
        $this->assertArrayNotHasKey('password', $preview['rows'][0]);
        $this->assertSame(1, User::count());
        $this->assertSame(0, WelcomeEmailDelivery::count());
    }

    public function test_edited_names_emails_and_roles_are_saved_with_default_permissions_and_queued_credentials(): void
    {
        $preview = $this->preview();
        $row = array_intersect_key($preview['rows'][0], array_flip(['row_id', 'name', 'email', 'role']));
        $row['name'] = 'Jane Edited'; $row['email'] = 'JANE.EDITED@example.com'; $row['role'] = 'building_admin';
        $response = $this->confirm($preview, [$row]);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(2, User::count());
        $user = User::where('email', 'jane.edited@example.com')->sole();
        $this->assertSame('Jane Edited', $user->name);
        $this->assertSame('building_admin', $user->role);
        $this->assertSame(User::defaultPermissions('building_admin'), $user->permissions);
        $this->assertTrue((bool) $user->force_password_change);
        $this->assertTrue(Hash::check(Crypt::decryptString(WelcomeEmailDelivery::sole()->encrypted_password), $user->password));
        $this->assertNull(session('staff_import_review'));
    }

    public function test_program_head_department_choices_are_available_and_selected_department_is_saved(): void
    {
        $preview = $this->preview();
        $this->assertSame(['GE', 'ICT', 'Business Management', 'THM'], $preview['departments']);
        $row = array_intersect_key($preview['rows'][0], array_flip(['row_id', 'name', 'email', 'role']));
        $row['role'] = 'program_head';
        $row['department'] = 'ICT';
        $response = $this->confirm($preview, [$row]);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $user = User::where('email', $row['email'])->sole();
        $this->assertSame('program_head', $user->role);
        $this->assertSame('ICT', $user->department);
        $this->assertSame(User::defaultPermissions('program_head'), $user->permissions);
    }

    public function test_program_head_requires_a_valid_department_before_any_accounts_are_created(): void
    {
        $preview = $this->preview();
        $rows = array_map(fn ($row) => array_intersect_key($row, array_flip(['row_id', 'name', 'email', 'role'])), $preview['rows']);
        $rows[1]['role'] = 'program_head';
        foreach ([null, '', 'Unknown department', ['ICT']] as $department) {
            $rows[1]['department'] = $department;
            try { $this->confirm($preview, $rows); $this->fail('Invalid Program Head department accepted.'); }
            catch (ValidationException $exception) { $this->assertArrayHasKey('rows.1.department', $exception->errors()); }
            $this->assertSame(1, User::count());
            $this->assertSame(0, WelcomeEmailDelivery::count());
        }
    }

    public function test_department_is_not_assigned_after_switching_to_another_staff_role(): void
    {
        $preview = $this->preview();
        $row = array_intersect_key($preview['rows'][0], array_flip(['row_id', 'name', 'email', 'role']));
        $row['department'] = 'THM';
        $response = $this->confirm($preview, [$row]);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertNull(User::where('email', $row['email'])->sole()->department);
    }

    public function test_staff_masterlist_generates_names_emails_and_position_roles_before_review(): void
    {
        $preview = $this->preview("NO\tLAST NAME\tFIRST NAME\tMIDDLE NAME\tOTHER\tEMPLOYEE\tSTAFF\n1\tDoe\tJane\tA\t\tNVS123\tSchool Administrator\n");
        $this->assertSame('Jane A Doe', $preview['rows'][0]['name']);
        $this->assertSame('jane.doe@novaliches.sti.edu.ph', $preview['rows'][0]['email']);
        $this->assertSame('school_admin', $preview['rows'][0]['role']);
        $this->confirm($preview);
        $this->assertSame('NVS123', User::where('email', 'jane.doe@novaliches.sti.edu.ph')->sole()->student_id);
    }

    public function test_existing_emails_are_flagged_and_can_be_corrected_in_review(): void
    {
        $preview = $this->preview("Name,Email,Role\nAnother Admin,admin@example.com,MIS\n");
        $this->assertNotEmpty($preview['rows'][0]['warning']);
        try { $this->confirm($preview); $this->fail('Duplicate email accepted.'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('rows.0.email', $exception->errors()); }
        $this->assertSame(1, User::count());
        $rows = [array_intersect_key($preview['rows'][0], array_flip(['row_id', 'name', 'email', 'role']))];
        $rows[0]['email'] = 'newadmin@example.com';
        $this->assertSame(200, $this->confirm($preview, $rows)->getStatusCode());
    }

    public function test_invalid_edits_unknown_rows_and_privileged_roles_create_no_accounts(): void
    {
        $preview = $this->preview();
        $row = array_intersect_key($preview['rows'][0], array_flip(['row_id', 'name', 'email', 'role']));
        foreach ([['email' => 'invalid'], ['name' => ' '], ['role' => 'superadmin'], ['row_id' => 'unknown']] as $changes) {
            try { $this->confirm($preview, [array_replace($row, $changes)]); $this->fail('Invalid row accepted.'); }
            catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        }
        $this->assertSame(1, User::count());
        $this->assertSame(0, WelcomeEmailDelivery::count());
    }

    public function test_duplicate_reviewed_emails_reject_the_entire_batch(): void
    {
        $preview = $this->preview();
        $rows = array_map(fn ($row) => array_intersect_key($row, array_flip(['row_id', 'name', 'email', 'role'])), $preview['rows']);
        $rows[1]['email'] = strtoupper($rows[0]['email']);
        try { $this->confirm($preview, $rows); $this->fail('Duplicate batch accepted.'); }
        catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        $this->assertSame(1, User::count());
    }

    public function test_a_consumed_or_expired_preview_cannot_be_imported(): void
    {
        $preview = $this->preview(); $this->confirm($preview);
        try { $this->confirm($preview); $this->fail('Preview replay accepted.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(422, $exception->getStatusCode()); }
        $this->assertSame(3, User::count());
        $preview = $this->preview(); session()->put('staff_import_review.expires_at', now()->subMinute()->timestamp);
        try { $this->confirm($preview); $this->fail('Expired preview accepted.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(422, $exception->getStatusCode()); }
    }

    public function test_executable_and_random_files_are_rejected_before_draft_or_accounts_are_created(): void
    {
        foreach ([['staff.php', '<?php echo 1;'], ['random.txt', "random words\nunrelated text\n"]] as [$filename, $content]) {
            try { $this->preview($content, $filename); $this->fail('Random upload accepted.'); }
            catch (ValidationException $exception) { $this->assertArrayHasKey('file', $exception->errors()); }
        }
        $this->assertSame(1, User::count());
        $this->assertNull(session('staff_import_review'));
    }

    public function test_users_without_account_creation_access_cannot_preview_staff(): void
    {
        auth()->user()->forceFill(['is_superadmin' => false, 'role' => 'student', 'permissions' => []])->save();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->preview();
    }

    public function test_a_different_administrator_cannot_confirm_another_users_draft(): void
    {
        $preview = $this->preview();
        $this->actingAs(User::forceCreate(['name' => 'Other Administrator', 'email' => 'other@example.com',
            'password' => 'unchanged', 'role' => 'superadmin', 'is_superadmin' => true]));
        try { $this->confirm($preview); $this->fail('Another administrator used the draft.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(422, $exception->getStatusCode()); }
        $this->assertSame(2, User::count());
        $this->assertSame(0, WelcomeEmailDelivery::count());
    }
}
