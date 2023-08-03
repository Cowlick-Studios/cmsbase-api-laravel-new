<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAuthenticationKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JWT keys for system authentication.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $keyPair = sodium_crypto_sign_keypair();

      echo (sodium_bin2base64($keyPair, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING) . "\n");

      return Command::SUCCESS;
    }
}
