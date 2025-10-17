<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Google2FA;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    /**
     * Show the two-factor authentication settings page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $qrCodeUrl = null;
        $secret = null;

        // If 2FA is not enabled, generate a new secret for setup
        if (!$user->hasEnabledTwoFactor()) {
            if (empty($user->google2fa_secret)) {
                $secret = Google2FA::generateSecretKey();
                $user->update(['google2fa_secret' => $secret]);
            } else {
                $secret = $user->google2fa_secret;
            }
            
            $qrCodeUrl = $this->generateQRCode($user->email, $secret);
        }

        return Inertia::render('Tenant/settings/TwoFactor', [
            'twoFactorEnabled' => $user->hasEnabledTwoFactor(),
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Enable two-factor authentication.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Generate a new secret if one doesn't exist
        if (empty($user->google2fa_secret)) {
            $secret = Google2FA::generateSecretKey();
            $user->update(['google2fa_secret' => $secret]);
        }

        return back()->with('status', 'Two-factor authentication setup initiated. Please scan the QR code.');
    }

    /**
     * Confirm and activate two-factor authentication.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (empty($user->google2fa_secret)) {
            throw ValidationException::withMessages([
                'code' => 'Two-factor authentication is not set up. Please refresh the page and try again.',
            ]);
        }

        $valid = Google2FA::verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

        $user->enableTwoFactor($user->google2fa_secret);

        return back()->with('status', 'Two-factor authentication has been enabled.');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (!$user->hasEnabledTwoFactor()) {
            throw ValidationException::withMessages([
                'code' => 'Two-factor authentication is not enabled.',
            ]);
        }

        $valid = Google2FA::verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

        $user->disableTwoFactor();

        // Clear any existing 2FA session verification
        $request->session()->forget('2fa_verified');

        return back()->with('status', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show the two-factor challenge page.
     */
    public function challenge(Request $request): Response
    {
        return Inertia::render('Tenant/auth/VerifyTwoFactor');
    }

    /**
     * Verify the two-factor authentication code during login.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (!$user || !$user->hasEnabledTwoFactor()) {
            return redirect()->route('tenant.login');
        }

        $valid = Google2FA::verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

        // Mark 2FA as verified in the session
        $request->session()->put('2fa_verified', true);

        return redirect()->intended(route('tenant.dashboard'));
    }

    /**
     * Generate QR code for two-factor authentication setup.
     */
    private function generateQRCode(string $email, string $secret): string
    {
        $qrCodeUrl = Google2FA::getQRCodeUrl(
            config('app.name'),
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        
        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($qrCodeUrl));
    }
}