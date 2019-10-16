<?php

use Illuminate\Database\Seeder;
use App\Eloquent\Admin\User;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User([
            'username' => 'admin',
            'password' => '123123'
        ]);

        $user->save();
    }
}
