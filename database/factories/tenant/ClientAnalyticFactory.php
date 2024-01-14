<?php

namespace Database\Factories\tenant;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\tenant\ClientAnalytic>
 */
class ClientAnalyticFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'date' => fake()->date(),
      'request_count' => fake()->numberBetween(0, 1000)
    ];
  }
}
