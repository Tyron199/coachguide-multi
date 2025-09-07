<?php

namespace Database\Factories\Tenant;

use App\Enums\Tenant\CommunicationMethod;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $focusAreas = [
            'Weight Loss', 'Muscle Building', 'Cardiovascular Health', 'Flexibility', 'Nutrition',
            'Stress Management', 'Sleep Quality', 'Mental Health', 'Sports Performance', 'Injury Recovery'
        ];

        $medicalConditions = [
            'Diabetes', 'Hypertension', 'Asthma', 'Arthritis', 'Heart Disease', 'Back Pain',
            'Knee Problems', 'Shoulder Issues', 'Allergies', 'Anxiety', 'Depression'
        ];

        return [
            'address' => fake()->address(),
            'birthdate' => fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'medical_conditions' => fake()->boolean(30) ? fake()->randomElements($medicalConditions, fake()->numberBetween(1, 3)) : [],
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'preferred_method_of_communication' => fake()->randomElement(CommunicationMethod::cases()),
            'goal_summary' => fake()->paragraph(2),
            'objectives' => fake()->paragraph(3),
            'focus_areas' => fake()->randomElements($focusAreas, fake()->numberBetween(2, 4)),
        ];
    }

    /**
     * Create a profile for weight loss focused clients
     */
    public function weightLoss(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_summary' => 'Focused on sustainable weight loss through proper nutrition and regular exercise. Looking to develop healthy habits that can be maintained long-term.',
            'objectives' => 'Lose 20-30 pounds over the next 6 months through a combination of cardio, strength training, and nutritional guidance. Establish a consistent workout routine and learn meal planning strategies.',
            'focus_areas' => ['Weight Loss', 'Nutrition', 'Cardiovascular Health'],
        ]);
    }

    /**
     * Create a profile for fitness/muscle building focused clients
     */
    public function fitness(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_summary' => 'Building strength and muscle mass while improving overall fitness level. Interested in progressive overload training and sports performance.',
            'objectives' => 'Increase overall strength by 25% and gain 10-15 pounds of lean muscle mass. Learn proper form for compound movements and develop a structured training program.',
            'focus_areas' => ['Muscle Building', 'Sports Performance', 'Nutrition'],
        ]);
    }

    /**
     * Create a profile for wellness/health focused clients
     */
    public function wellness(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_summary' => 'Improving overall health and wellness through lifestyle changes. Focus on stress management, better sleep, and maintaining an active lifestyle.',
            'objectives' => 'Establish consistent sleep schedule, reduce stress levels through mindfulness and exercise, and maintain regular physical activity for long-term health benefits.',
            'focus_areas' => ['Stress Management', 'Sleep Quality', 'Mental Health', 'Flexibility'],
        ]);
    }
}
