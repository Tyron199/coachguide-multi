<?php
    
namespace Database\Factories\Tenant;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $industries = [
            'Technology', 'Healthcare', 'Finance', 'Manufacturing', 'Retail', 'Education',
            'Construction', 'Real Estate', 'Consulting', 'Marketing', 'Legal Services',
            'Non-Profit', 'Government', 'Hospitality', 'Transportation'
        ];

        $companyName = fake()->company();
        
        return [
            'name' => $companyName,
            'address' => fake()->address(),
            'industry_sector' => fake()->randomElement($industries),
            'contact_person_name' => fake()->name(),
            'contact_person_position' => fake()->randomElement(['HR Manager', 'CEO', 'Operations Manager', 'Wellness Coordinator', 'Office Manager']),
            'contact_person_email' => fake()->companyEmail(),
            'contact_person_phone' => fake()->phoneNumber(),
            'billing_contact_name' => fake()->name(),
            'billing_contact_email' => fake()->companyEmail(),
            'billing_contact_phone' => fake()->phoneNumber(),
            'invoicing_notes' => fake()->optional(0.3)->paragraph(),
        ];
    }
}
