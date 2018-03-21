<?php

use Illuminate\Database\Seeder;

class BouncerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Bouncer::allow('superadmin')->everything();
        \Bouncer::allow('admin')->everything();

        \Bouncer::allow('business')->to('use-cms');
        \Bouncer::allow('business')->to('use-mobile');

        \Bouncer::allow('user')->to('use-mobile');
    }
}
