<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;

class CreateNewAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {newAdminName} {newAdminEmail} {newAdminPassword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new admin user in the system.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $user = new User;
      $user->name = $this->argument('newAdminName');
      $user->email = $this->argument('newAdminEmail');
      $user->password = bcrypt($this->argument('newAdminPassword'));
      $user->email_verified_at = now();
      $user->save();

      $this->info('Admin User ' . $this->argument('newAdminEmail') . ' created!');
      return;
    }
}
