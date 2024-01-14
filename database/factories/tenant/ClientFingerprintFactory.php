<?php

namespace Database\Factories\tenant;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\tenant\ClientFingerprint>
 */
class ClientFingerprintFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {

    $otherCountryCode = ['US', 'US', 'US', 'US', 'US', 'CA', 'CA', 'CA', 'RU', 'GB', 'CN'];
    $countryCode = $otherCountryCode[array_rand($otherCountryCode)];

    return [
      'fingerprint' => fake()->md5(),
      'ip' => fake()->ipv4(),
      'user_agent' => fake()->userAgent(),
      'country_code' => $countryCode,
    ];
  }
}
