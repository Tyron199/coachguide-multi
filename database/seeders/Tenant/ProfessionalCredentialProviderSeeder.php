<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\ProfessionalCredentialProvider;
use Illuminate\Database\Seeder;

class ProfessionalCredentialProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'ICF',
                'full_name' => 'International Coaching Federation',
                'website_url' => 'https://coachingfederation.org',
                'logo_url' => '/images/professional-credentials/icf.jpg',
                'display_order' => 1,
            ],
            [
                'name' => 'EMCC Global',
                'full_name' => 'European Mentoring and Coaching Council',
                'website_url' => 'https://www.emccglobal.org',
                'display_order' => 2,
                'logo_url' => '/images/professional-credentials/emcc.jpg',
            ],
            [
                'name' => 'COMENSA',
                'full_name' => 'Coaches and Mentors of South Africa',
                'website_url' => 'https://www.comensa.org.za',
                'display_order' => 3,
                'logo_url' => '/images/professional-credentials/comensa.png',
            ],
            [
                'name' => 'ANLP',
                'full_name' => 'Association for NLP',
                'website_url' => 'https://www.anlp.org',
                'display_order' => 4,
                'logo_url' => '/images/professional-credentials/anlp.jpg',
            ],
            [
                'name' => 'AC',
                'full_name' => 'Association for Coaching',
                'website_url' => 'https://www.associationforcoaching.com',
                'display_order' => 4,
                'logo_url' => '/images/professional-credentials/ac.png',
            ],
            [
                'name' => 'CCE',
                'full_name' => 'Center for Credentialing & Education',
                'website_url' => 'https://www.cce-global.org',
                'display_order' => 5,
            ],
            [
                'name' => 'WABC',
                'full_name' => 'Worldwide Association of Business Coaches',
                'website_url' => 'https://www.wabccoaches.com',
                'display_order' => 6,
            ],
            [
                'name' => 'IAC',
                'full_name' => 'International Association of Coaching',
                'website_url' => 'https://certifiedcoach.org',
                'display_order' => 7,
            ],
            [
                'name' => 'ICISF',
                'full_name' => 'International Coach and Supervisor Federation',
                'website_url' => 'https://www.icisf.net',
                'display_order' => 8,
            ],
            [
                'name' => 'NBHWC',
                'full_name' => 'National Board for Health & Wellness Coaching',
                'website_url' => 'https://nbhwc.org',
                'display_order' => 9,
            ],
            [
                'name' => 'IAPC&M',
                'full_name' => 'International Authority for Professional Coaching & Mentoring',
                'website_url' => 'https://coach-accreditation.services',
                'display_order' => 10,
            ],
            [
                'name' => 'ICR',
                'full_name' => 'International Coaches Register',
                'website_url' => 'https://www.internationalcoachesregister.com',
                'display_order' => 11,
            ],
            [
                'name' => 'APAC',
                'full_name' => 'Asia Pacific Alliance of Coaches',
                'website_url' => 'https://www.apacoaches.org',
                'display_order' => 12,
            ],
            [
                'name' => 'ANZCAL',
                'full_name' => 'Australian & New Zealand Coaching Alliance',
                'website_url' => 'https://www.anzcal.org',
                'display_order' => 13,
            ],
        ];

        foreach ($providers as $provider) {
            ProfessionalCredentialProvider::updateOrCreate(
                ['name' => $provider['name']],
                $provider
            );
        }
    }
}
