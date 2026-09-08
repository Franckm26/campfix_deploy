<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['email_address_sent_at', 'password_sent_at'] as $column) {
            if (! Schema::hasColumn('welcome_email_deliveries', $column)) {
                Schema::table('welcome_email_deliveries', function (Blueprint $table) use ($column): void {
                    $table->timestamp($column)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('welcome_email_deliveries', function (Blueprint $table): void {
            $table->dropColumn(['email_address_sent_at', 'password_sent_at']);
        });
    }
};
