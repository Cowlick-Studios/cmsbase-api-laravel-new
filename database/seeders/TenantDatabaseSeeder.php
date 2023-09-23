<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      \App\Models\tenant\User::factory()->create([
        'name' => 'user',
        'email' => 'user@example.com',
      ]);

      \App\Models\tenant\User::factory(10)->create();
    }
}
