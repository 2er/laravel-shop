<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 通过 factory 方法生成 100 个用户并保存到数据库中
        factory(\App\Models\User::class, 10)->create();
    }
}
