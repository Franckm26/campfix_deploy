<?php

namespace Tests\Feature;

use App\Http\Middleware\DatabaseTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('transaction_test_records');
        Schema::create('transaction_test_records', function (Blueprint $table): void {
            $table->id();
            $table->string('value');
        });
    }

    public function test_successful_json_response_commits_even_when_session_has_error_flash(): void
    {
        $request = Request::create('/test-update', 'PUT');
        $request->setLaravelSession(app('session')->driver());
        $request->session()->flash('error', 'Unrelated message from an earlier workflow.');

        $response = (new DatabaseTransaction)->handle($request, function (): JsonResponse {
            DB::table('transaction_test_records')->insert(['value' => 'saved']);

            return response()->json(['success' => true]);
        });

        $this->assertTrue($response->getData(true)['success']);
        $this->assertDatabaseHas('transaction_test_records', ['value' => 'saved']);
    }
}
