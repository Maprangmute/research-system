<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'make:user';
    protected $description = 'Create a new user manually';

    public function handle()
    {
        $user = User::create([
            'name' => 'วัฒนชัย',
            'email' => 'test@example.com',
            'password' => Hash::make('secret'),
            'prefix' => 'ดร.',
            'position' => 'นักวิจัย',
            'role' => 'admin',
            'profile_picture' => 'profiles/avatar.png',
        ]);

        $this->info("✅ User '{$user->name}' created successfully!");
    }
}
