<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorConfig;
use Illuminate\Http\Request;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAWithQR;

class TwoFactorController extends Controller
{
    private function getGoogle2FA(): Google2FAWithQR
    {
        return new Google2FAWithQR();
    }

    /** Show QR setup page — generates a new secret if not already pending. */
    public function showSetup(Request $request)
    {
        $user = auth()->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.edit')->with('info', '2FA is already enabled.');
        }

        // Reuse an unconfirmed config or create a fresh secret
        $config = $user->twoFactorConfig;
        if (!$config || $config->isConfirmed()) {
            $secret = $this->getGoogle2FA()->generateSecretKey();
            $config = TwoFactorConfig::updateOrCreate(
                ['user_id' => $user->id],
                ['secret' => $secret, 'confirmed_at' => null, 'recovery_codes' => null]
            );
        }

        $qrCodeUrl = $this->getGoogle2FA()->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $config->secret
        );

        // Generate inline SVG QR via endroid/qr-code bundled with pragmarx
        $qrImage = $this->getGoogle2FA()->getQRCodeInline(
            config('app.name'),
            $user->email,
            $config->secret
        );

        return view('two-factor.setup', compact('qrImage', 'config'));
    }

    /** Confirm setup by verifying the first TOTP code. */
    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user   = auth()->user();
        $config = $user->twoFactorConfig;

        if (!$config || $config->isConfirmed()) {
            return redirect()->route('profile.edit');
        }

        $valid = $this->getGoogle2FA()->verifyKey($config->secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $config->update([
            'confirmed_at'   => now(),
            'recovery_codes' => $recoveryCodes,
        ]);

        $user->update(['two_factor_required_at' => now()]);

        activity()->causedBy($user)->log('enabled two-factor authentication');

        return redirect()->route('2fa.recovery-codes')->with('recovery_codes', $recoveryCodes);
    }

    /** Show recovery codes after enabling 2FA. */
    public function recoveryCodes()
    {
        $codes = session('recovery_codes');
        if (!$codes) {
            return redirect()->route('profile.edit');
        }
        return view('two-factor.recovery-codes', compact('codes'));
    }

    /** Disable 2FA (requires password confirmation). */
    public function disable(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = auth()->user();

        $user->twoFactorConfig?->delete();
        $user->update(['two_factor_required_at' => null]);

        activity()->causedBy($user)->log('disabled two-factor authentication');

        return redirect()->route('profile.edit')->with('success', '2FA has been disabled.');
    }

    /** Show TOTP challenge after login. */
    public function showChallenge()
    {
        if (!session('2fa_pending')) {
            return redirect()->route('dashboard');
        }

        return view('two-factor.challenge');
    }

    /** Verify TOTP code during login challenge. */
    public function challenge(Request $request)
    {
        $request->validate(['code' => ['required']]);

        $user   = auth()->user();
        $config = $user->twoFactorConfig;

        if (!$config) {
            return redirect()->route('dashboard');
        }

        $code = preg_replace('/\s+/', '', $request->code);

        // Try TOTP first, then recovery codes
        $valid = $this->getGoogle2FA()->verifyKey($config->secret, $code);

        if (!$valid) {
            $valid = $this->tryRecoveryCode($config, $code);
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $request->session()->forget('2fa_pending');
        $request->session()->put('2fa_passed', true);

        return redirect()->intended(route('dashboard'));
    }

    private function tryRecoveryCode(TwoFactorConfig $config, string $code): bool
    {
        $codes = $config->recovery_codes ?? [];
        $index = array_search($code, $codes, true);

        if ($index === false) {
            return false;
        }

        // Burn the used recovery code
        array_splice($codes, $index, 1);
        $config->update(['recovery_codes' => $codes]);

        return true;
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }
}
