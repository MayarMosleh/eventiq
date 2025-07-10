<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null, // راح نربطها بعدين باليوزر (provider)
            'company_name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'company_image' => $this->faker->imageUrl(640, 480, 'business', true),
        ];
    }
}
