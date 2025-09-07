<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Coaching Agreement</title>
    
    {{-- Template Schema Definition --}}
    {{-- 
    @template-schema
    {
        "template_name": "Package Coaching Agreement",
        "template_version": "1.0",
        "template_description": "Coaching agreement with upfront package pricing for multiple sessions",
        "categories": {
            "contract_dates": {
                "label": "Contract Period",
                "description": "Define the coaching engagement period",
                "fields": {
                    "start_date": {
                        "label": "Contract Start Date", 
                        "type": "date",
                        "required": true
                    },
                    "end_date": {
                        "label": "Contract End Date",
                        "type": "date", 
                        "required": true
                    }
                }
            },
            "session_details": {
                "label": "Session Configuration",
                "description": "Define your coaching session structure",
                "fields": {
                    "session_frequency": {
                        "label": "Session Frequency",
                        "type": "select",
                        "required": true,
                        "options": ["Weekly", "Bi-weekly", "Monthly"],
                        "default": "Weekly"
                    },
                    "session_duration": {
                        "label": "Session Duration (minutes)",
                        "type": "select",
                        "required": true,
                        "options": ["30", "45", "60", "90", "120"],
                        "default": 60
                    },
                    "session_format": {
                        "label": "Session Format",
                        "type": "select", 
                        "required": true,
                        "options": ["Online", "In-person",  "Hybrid"],
                        "default": "Online"
                    },
                    "session_location": {
                        "label": "Session Location",
                        "type": "text",
                        "required": false,
                        "default": "Online Platform"
                    },
                    "total_sessions": {
                        "label": "Total Number of Sessions",
                        "type": "number",
                        "required": true,
                        "min": 1,
                        "default": 12
                    }
                }
            },
            "financial_terms": {
                "label": "Package Pricing",
                "description": "Set your package deal pricing and payment terms",
                "fields": {
                    "package_fee": {
                        "label": "Total Package Fee",
                        "type": "currency",
                        "required": true,
                        "placeholder": "e.g., $1,800"
                    },
                    "payment_schedule": {
                        "label": "Payment Schedule",
                        "type": "select",
                        "required": true,
                    "options": [
                            "Payment upfront",
                            "Payment at the end of the month", 
                            "30 day payment terms",
                            "60 day payment terms"
                        ],
                        "default": "Payment upfront"
                    }
                }
            }
        }
    }
    @end-template-schema
    --}}
    
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .parties {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin: 20px 0;
        }
        .party {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
            display: table;
            table-layout: fixed;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
            box-sizing: border-box;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin: 20px 0 10px 0;
            height: 80px;
            position: relative;
        }
        .signature-image {
            max-width: 200px;
            max-height: 60px;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
        }
        ul {
            padding-left: 20px;
        }
        .variable {
            background-color: #f0f0f0;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .contract-logo {
            max-height: 80px;
            max-width: 300px;
            height: auto;
            width: auto;
        }
        .package-highlight {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        /* Mobile-first adjustments */
        @media (max-width: 640px) {
            body { max-width: 100%; padding: 24px 16px; font-size: 16px; line-height: 1.7; }
            .parties { display: block; }
            .party { display: block; width: 100%; margin-bottom: 12px; padding: 0; }
            .signature-section { display: block; }
            .signature-box { display: block; width: 100%; margin-bottom: 24px; padding: 0; }
            .signature-line { height: 64px; }
            .package-highlight { padding: 16px; margin: 16px 0; }
        }
        /* Avoid awkward page breaks in PDF */
        .section, .signature-section { page-break-inside: avoid; }
        @page { size: A4; margin: 25mm 20mm; }
        @media print {
            body { max-width: 100%; margin: 0; padding: 0; }
            .header { border-bottom: 1px solid #000; padding-bottom: 12px; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    @if(isset($logo) && $logo['dataUri'])
        <div class="logo-section">
            <img src="{{ $logo['dataUri'] }}" alt="Company Logo" class="contract-logo">
        </div>
    @endif
    
    <div class="header">
        <h1>Package Coaching Agreement</h1>
        <p><strong>Contract Date:</strong> <span class="variable">{{ $contract_date ?? now()->format('F j, Y') }}</span></p>
        <p><strong>Contract Period:</strong> <span class="variable">{{ $start_date }}</span> to <span class="variable">{{ $end_date }}</span></p> 
    </div>

    <div class="section">
        <div class="section-title">1. Parties Involved</div>
        <p>This agreement is made between:</p>
        <div class="parties">
            <div class="party">
                <strong>Coach:</strong><br>
                <span class="variable">{{ $coach->name }}</span><br>
                <span class="variable">{{ $coach->email }}</span><br>
                <span class="variable">{{ $coach->phone ?? 'Not provided' }}</span>
            </div>
            <div class="party">
                <strong>Client:</strong><br>
                <span class="variable">{{ $client->name }}</span><br>
                <span class="variable">{{ $client->email }}</span><br>
                <span class="variable">{{ $client->phone ?? 'Not provided' }}</span>
            </div>
        </div>
        <p>Both parties agree to enter into a coaching relationship designed to facilitate the development of personal, professional, or business goals.</p>
    </div> 

    <div class="section">
        <div class="section-title">2. Nature of Coaching</div>
        <p>Coaching is a partnership focused on thought-provoking and creative processes to inspire the client to maximize their potential. Coaching is not therapy, counseling, or consulting and does not substitute for them.</p>
    </div>

    <div class="section">
        <div class="section-title">3. Confidentiality</div>
        <p>All information shared between the coach and client will remain strictly confidential in accordance with Coaching Professional Bodies guidelines. Confidentiality will only be broken in cases where there is a legal obligation to disclose information, such as risk of harm to self or others.</p>
    </div>

    <div class="section">
        <div class="section-title">4. Coaching Package Details</div>
        <div class="package-highlight">
            <h3 style="margin-top: 0; color: #2563eb;">Your Coaching Package</h3>
            <ul>
                <li><strong>Total Sessions:</strong> <span class="variable">{{ $total_sessions }}</span> sessions</li>
                <li><strong>Session Duration:</strong> <span class="variable">{{ $session_duration }}</span> minutes per session</li>
                <li><strong>Session Frequency:</strong> <span class="variable">{{ $session_frequency }}</span></li>
                <li><strong>Session Format:</strong> <span class="variable">{{ $session_format }}</span></li>
                <li><strong>Session Location:</strong> <span class="variable">{{ $session_location }}</span></li>
            </ul>
        </div>
    </div>

    <div class="section">
        <div class="section-title">5. Package Pricing and Payment</div>
        <div class="package-highlight">
            <h3 style="margin-top: 0; color: #16a34a;">Investment Details</h3>
            <p><strong>Total Package Investment:</strong> <span class="variable" style="font-size: 1.2em; color: #16a34a;">{{ $package_fee }}</span></p>
            <p><strong>Per Session Value:</strong> ${{ number_format(floatval(str_replace(['$', ','], '', $package_fee)) / $total_sessions, 2) }}</p>
            <p><strong>Payment Schedule:</strong> <span class="variable">{{ $payment_schedule }}</span></p>
        </div>
        <p><strong>Package Benefits:</strong></p>
        <ul>
            <li>Commitment to your complete transformation journey</li>
            <li>Consistent progress tracking across all sessions</li>
            <li>Better value compared to individual session pricing</li>
            <li>Priority scheduling for your sessions</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">6. Session Scheduling and Cancellation</div>
        <p>Sessions will be scheduled in advance according to the agreed frequency. Clients must provide at least <strong>24 hours' notice</strong> to reschedule a session. Late cancellations or no-shows will still count toward your package allocation.</p>
        <p><strong>Package Validity:</strong> All sessions must be completed within the contract period specified above. Unused sessions after the end date will be forfeited unless otherwise agreed in writing.</p>
    </div>

    <div class="section">
        <div class="section-title">7. Responsibilities</div>
        <div style="margin-bottom: 15px;">
            <strong>Coach Responsibilities:</strong>
            <ul>
                <li>Maintain professional conduct as per Coaching Professional Bodies standards.</li>
                <li>Provide a safe, respectful, and confidential environment.</li>
                <li>Support the client's goals without judgment.</li>
                <li>Track progress across the entire package duration.</li>
            </ul>
        </div>
        <div>
            <strong>Client Responsibilities:</strong>
            <ul>
                <li>Show up on time and be prepared for each session.</li>
                <li>Take ownership of their actions and outcomes.</li>
                <li>Be open to reflection, challenge, and change.</li>
                <li>Complete payment according to the agreed schedule.</li>
            </ul>
        </div>
    </div>

    <div class="section">
        <div class="section-title">8. Code of Ethics</div>
        <p>The coach agrees to abide by the Code of Ethics and Conduct as outlined by the Coaching Professional Bodies.</p>
    </div>

    <div class="section">
        <div class="section-title">9. Refund Policy</div>
        <p>Refunds for unused sessions may be considered on a case-by-case basis within the first 30 days of the contract. After this period, all payments are non-refundable. Any refund calculations will be based on individual session rates, not the discounted package rate.</p>
    </div>

    <div class="section">
        <div class="section-title">10. Termination of Agreement</div>
        <p>Either party may terminate this agreement with written notice. In case of early termination, remaining sessions will be calculated at individual session rates, and any overpayment will be refunded accordingly.</p>
    </div>

    <div class="section">
        <div class="section-title">11. Limitation of Liability</div>
        <p>The coach will not be held liable for decisions made by the client or outcomes resulting from coaching sessions.</p>
    </div>

    <div class="section">
        <div class="section-title">12. Data Protection & Record Keeping</div>
        <p>Coaching notes and data will be kept securely and only for as long as necessary, in compliance with local data protection laws (e.g., POPIA, GDPR).</p>
    </div>

    <div class="section">
        <div class="section-title">13. Signatures</div>
        <div class="signature-section">
            @if(isset($coachSignature) && $coachSignature && $coachSignature->signature)
                <div class="signature-box">
                    <strong>Coach Signature:</strong>
                    <div class="signature-line">
                        <img src="data:image/png;base64,{{ $coachSignature->signature }}" alt="Coach Signature" class="signature-image">
                    </div>
                    <p><strong>Name:</strong> <span class="variable">{{ $coach->name }}</span></p>
                    <p><strong>Date:</strong> <span class="variable">{{ $coachSignature->created_at->format('F j, Y') }}</span></p>
                </div>
            @else
                <div class="signature-box">
                    <strong>Coach Signature:</strong>
                    <div class="signature-line"></div>
                    <p><strong>Name:</strong> <span class="variable">{{ $coach->name }}</span></p>
                    <p><strong>Date:</strong> _____________</p>
                </div>
            @endif
            
            @if(isset($clientSignature) && $clientSignature && $clientSignature->signature)
                <div class="signature-box">
                    <strong>Client Signature:</strong>
                    <div class="signature-line">
                        <img src="data:image/png;base64,{{ $clientSignature->signature }}" alt="Client Signature" class="signature-image">
                    </div>
                    <p><strong>Name:</strong> <span class="variable">{{ $client->name }}</span></p>
                    <p><strong>Date:</strong> <span class="variable">{{ $clientSignature->created_at->format('F j, Y') }}</span></p>
                </div>
            @else
                <div class="signature-box">
                    <strong>Client Signature:</strong>
                    <div class="signature-line"></div>
                    <p><strong>Name:</strong> <span class="variable">{{ $client->name }}</span></p>
                    <p><strong>Date:</strong> _____________</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
