<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['admin', 'pustakawan', 'anggota']);
        return [
            'name'         => $name,
            'display_name' => ucfirst($name),
            'description'  => 'Role ' . $name,
        ];
    }
}