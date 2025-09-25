<?php

namespace App\Http\Requests\Tenant\Auth;

use App\Enums\Tenant\UserRegistrationStatus;
use App\Models\Tenant\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}\p{M}\'\-\.\s]+$/u', // Letters (including accented), apostrophes, hyphens, dots, spaces
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                //'email:rfc,dns', // More strict email validation
                'max:255',
            ],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults()
                    ->min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'token' => 'nullable|string',
            'timezone' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name.',
            'name.min' => 'Your name must be at least 2 characters long.',
            'name.regex' => 'Please enter a valid name. Names can only contain letters, spaces, hyphens, apostrophes, and dots.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.lowercase' => 'Email address must be in lowercase.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.mixed_case' => 'Password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one special character.',
            'password.uncompromised' => 'This password has been found in data breaches. Please choose a different password.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateUserInvitation($validator);
            $this->validateNameIsNotEmail($validator);
        });
    }

    /**
     * Validate that the user has a pending invitation.
     */
    protected function validateUserInvitation($validator): void
    {
        $normalizedEmail = strtolower(trim($this->input('email')));
        $existingUser = User::where('email', $normalizedEmail)->first();
        
        if (!$existingUser) {
            $validator->errors()->add('email', 'You need an invitation from your coach to register. Please contact your coach if you haven\'t received one.');
            return;
        }
        
        if ($existingUser->status === UserRegistrationStatus::ACCEPTED) {
            $validator->errors()->add('email', 'An account with this email already exists. Please try logging in instead.');
        }
    }

    /**
     * Validate that the name is not the same as the email.
     */
    protected function validateNameIsNotEmail($validator): void
    {
        $name = trim($this->input('name', ''));
        $email = trim($this->input('email', ''));
        
        if (!empty($name) && !empty($email) && strtolower($name) === strtolower($email)) {
            $validator->errors()->add('name', 'Your name cannot be the same as your email address. Please enter your actual name.');
        }
    }

    /**
     * Get the normalized email for database operations.
     */
    public function getNormalizedEmail(): string
    {
        return strtolower(trim($this->input('email')));
    }

    /**
     * Get the sanitized name.
     */
    public function getSanitizedName(): string
    {
        return trim($this->input('name'));
    }
}
