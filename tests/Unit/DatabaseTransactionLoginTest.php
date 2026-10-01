<?php

namespace Tests\Unit;

use App\Http\Middleware\DatabaseTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTransactionLoginTest extends TestCase
{
    public function test_invalid_login_redirect_commits_its_security_counter(): void
    {
        Schema::dropIfExists('login_transaction_probes');
        Schema::create('login_transaction_probes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('attempts')->default(0);
        });
        DB::table('login_transaction_probes')->insert(['attempts' => 0]);

        $request = Request::create('/login', 'POST');
        $request->setLaravelSession(app('session.store'));

        app(DatabaseTransaction::class)->handle($request, function () {
            DB::table('login_transaction_probes')->where('id', 1)->increment('attempts');

            return redirect('/')->with('error', 'Invalid email or password.');
        });

        $this->assertSame(1, (int) DB::table('login_transaction_probes')->value('attempts'));
    }
}
