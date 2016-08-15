<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Channel;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::find(1);

        if ($admin instanceof User) {
            $admin->channels()->save(new Channel([
                'name' => '#public',
                'access' => 'public',
                'expires' => null,
                'isDefault' => true,
            ]));
        }
    }
}
