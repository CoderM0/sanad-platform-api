<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $user =  User::create([
        //     'first_name' => 'Test Docotr',
        //     'last_name' => 'Test Docotr',
        //     'email' => 'doc@gmail.com',
        //     'password' => Hash::make('12345678'),
        //     'avatar' => 'somethig',
        //     'role' => 'doctor',
        // ]);
        // Doctor::create([
        //     'user_id' => $user->id,
        //     'achievements' => 'something',
        //     'specialization' => 'somthing else',
        //     'phone_number' => 'wow'
        // ]);
        $user =  User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'avatar' => 'somethig',
            'role' => 'admin',
        ]);
    }
}
