<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = \App\Models\Event::class;

    public function definition()
    {
        return [
            'event_name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'image_url' => null, // حط الصورة لاحقاً بالسيدر
        ];
    }
}
