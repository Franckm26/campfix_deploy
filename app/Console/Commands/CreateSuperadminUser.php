<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSuperadminUser extends Command
{
    protected $signature   = 'superadmin:create {--email=} {--name=} {--password=}';
    protected $description = 'Create a superadmin user account';

    public function handle(): int
    {
        $email    = $this->option('email') ?? $this->ask('Email address');
        $name     = $this->option('name') ?? $this->ask('Full name');
        $password = $this->option('password') ?? $this->secret('Password');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists.");

            return self::FAILURE;
        }

        $user = User::create([
            'uuid'          => (string) Str::uuid(),
            'name'          => $name,
            'email'         => $email,
            'password'      => Hash::make($password),
            'role'          => 'superadmin',
            'is_admin'      => true,
            'is_superadmin' => true,
        ]);

        $this->info("Superadmin user '{$name}' created successfully.");
        $this->line("Email: {$email}");

        return self::SUCCESS;
    }
}
