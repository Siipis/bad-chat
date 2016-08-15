<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $admin = User::create([
                'name' => 'theQueen',
                'email' => 'amalia@varjohovi.net',
                'password' => bcrypt('shad4vamp00'),
                'role' => 'admin',
                'tier' => '0',
                'public_key' => uniqid(),
                'private_key' => bin2hex(random_bytes(15)),
            ]);

            $admin->activate();


            $mod = User::create([
                'name' => 'Infinitum',
                'email' => 'siipis@live.co.uk',
                'password' => bcrypt('shad4vamp00'),
                'role' => 'moderator',
                'public_key' => uniqid(),
                'private_key' => bin2hex(random_bytes(15)),
            ]);

            $mod->activate();

            $vouch = $admin->vouches()->create([
                'email' => 'siipis@live.co.uk'
            ]);

            $vouch->protegee()->associate($mod)->save();
        } catch (Exception $e) {
            die($e);
        }
    }
}
