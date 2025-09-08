<?php


return [
    'plans' => [
        'tier1' => [
            'name' => 'Basic',
            'monthly_price' => 'R235',
            'yearly_price' => 'R2538',
            'yearly_discount' => '10%',
            'description' => 'Essential coaching tools',
            'features' => [
                [
                    'name' => 'Coach Access',
                    'details' => ['Access to CoachGuide (standard look)']
                ],
                [
                    'name' => 'Tabs Included',
                    'details' => ['Clients', 'Sessions', 'Contracting', 'Coach Log only (no CPD access)']
                ],
                [
                    'name' => 'Reporting',
                    'details' => []
                ],
                [
                    'name' => 'Client Access',
                    'details' => []
                ],
                [
                    'name' => 'Corporate Access',
                    'details' => []
                ]
            ],
            'paddle_id_monthly' => env('PADDLE_TIER1_MONTHLY'),
            'paddle_id_monthly_trial' => env('PADDLE_TIER1_MONTHLY_TRIAL'),
            'paddle_id_yearly' => env('PADDLE_TIER1_YEARLY'),
            'paddle_id_yearly_trial' => env('PADDLE_TIER1_YEARLY_TRIAL'),
        ],
        'tier2' => [
            'name' => 'Professional',
            'monthly_price' => 'R465',
            'yearly_price' => 'R4743',
            'yearly_discount' => '15%',
            'description' => 'Advanced tools with client access and CPD tracking',
            'features' => [
                [
                    'name' => 'Coach Access',
                    'details' => ['Access to CoachGuide (whitelabel with own logo and look)', 'Company full profile']
                ],
                [
                    'name' => 'Tabs Included',
                    'details' => ['Clients', 'Sessions', 'Contracting', 'Coaching Tool Suite (limited)', 'Coach Log & CPD linked to Professional Bodies', 'Reflection & Supervision']
                ],
                [
                    'name' => 'Reporting',
                    'details' => ['Client reporting']
                ],
                [
                    'name' => 'Client Access',
                    'details' => ['Can give clients login access to view progress', 'Clients receive reminders on actions']
                ],
                [
                    'name' => 'Corporate Access',
                    'details' => []
                ]
            ],
            'paddle_id_monthly' => env('PADDLE_TIER2_MONTHLY'),
            'paddle_id_monthly_trial' => env('PADDLE_TIER2_MONTHLY_TRIAL'),
            'paddle_id_yearly' => env('PADDLE_TIER2_YEARLY'),
            'paddle_id_yearly_trial' => env('PADDLE_TIER2_YEARLY_TRIAL')
        ],
        'tier3' => [
            'name' => 'Enterprise',
            'monthly_price' => 'R795',
            'yearly_price' => 'R7632',
            'yearly_discount' => '20%',
            'description' => 'Complete coaching solution with advanced client and corporate features',
            'features' => [
                [
                    'name' => 'Coach Access',
                    'details' => ['Access to CoachGuide (whitelabel with own logo and look)', 'Company full profile', 'Client full reporting (to individuals & Corporate)']
                ],
                [
                    'name' => 'Tabs Included',
                    'details' => ['Clients', 'Sessions', 'Contracting', 'Coach Tool Suite (full suite)', 'Coach Log & CPD linked to Professional Bodies', 'Reflection & Supervision']
                ],
                [
                    'name' => 'Client Access',
                    'details' => ['Clients can log in and receive action reminders', 'Upload documents/photos/other accountability formats']
                ],
                [
                    'name' => 'Corporate Access',
                    'details' => ['Corporate HR can view staff coaching progress', 'Pull full report on all staff members']
                ]
            ],
            'paddle_id_monthly' => env('PADDLE_TIER3_MONTHLY'),
            'paddle_id_monthly_trial' => env('PADDLE_TIER3_MONTHLY_TRIAL'),
            'paddle_id_yearly' => env('PADDLE_TIER3_YEARLY'),
            'paddle_id_yearly_trial' => env('PADDLE_TIER3_YEARLY_TRIAL'),
        ],
    ],
];
