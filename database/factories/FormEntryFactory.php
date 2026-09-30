<?php

namespace Database\Factories;

use App\Models\FormEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormEntry>
 */
class FormEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $project = fake()->catchPhrase();

        return [
            'demo' => 'project-kickoff',
            'title' => $project,
            'data' => [
                'project_name' => $project,
                'contact_email' => fake()->safeEmail(),
                'client_type' => 'personal',
                'priority' => 'medium',
                'stack' => ['Laravel', 'React'],
            ],
        ];
    }
}
