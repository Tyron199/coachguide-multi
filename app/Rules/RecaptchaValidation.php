<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip validation if reCAPTCHA is not configured
        if (empty(config('services.recaptcha.secret'))) {
            Log::warning('reCAPTCHA validation skipped - no secret key configured');
            return;
        }

        // Check if the reCAPTCHA response token is provided
        if (empty($value)) {
            $fail('Please complete the reCAPTCHA verification.');
            return;
        }

        try {
            // Verify the reCAPTCHA response with Google
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            // Check if verification was successful
            if (!$result['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'errors' => $result['error-codes'] ?? [],
                    'ip' => request()->ip(),
                ]);
                
                $fail('reCAPTCHA verification failed. Please try again.');
                return;
            }

            // Optional: Check score for reCAPTCHA v3 (if you're using v3)
            if (isset($result['score']) && $result['score'] < 0.5) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $result['score'],
                    'ip' => request()->ip(),
                ]);
                
                $fail('reCAPTCHA verification failed. Please try again.');
                return;
            }

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error: ' . $e->getMessage());
            $fail('reCAPTCHA verification failed. Please try again.');
        }
    }
}
