<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        $user = [
            'name' => 'admin',
            'user_name' => 'admin',
            'password' => bcrypt(12345678),
            'avatar' => 'noimage.png',
            'email' => 'admin@mail.com',
            'user_role' => 2
        ];
        User::create($user);
    }
}
