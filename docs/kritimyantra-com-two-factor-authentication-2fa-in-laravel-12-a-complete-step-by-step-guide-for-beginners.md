
[![Logo](https://kritimyantra.com/logo.png)
KritimYantra](/)
[Projects](https://kritimyantra.com/projects)
[Blogs](https://kritimyantra.com/blogs)
[Contact us](https://kritimyantra.com/contact)
[About us](https://kritimyantra.com/about)

[Sign in with Google](https://kritimyantra.com/auth/google)

[Projects](https://kritimyantra.com/projects)
[Blog](https://kritimyantra.com/blogs)
[Contact us](https://kritimyantra.com/contact)
[About](https://kritimyantra.com/about)
[Sign in with Google](https://kritimyantra.com/auth/google)

# Two-Factor Authentication (2FA) in Laravel 12 — A Complete Step-by-Step Guide for Beginners

![Author](https://kritimyantra.com/assets/img/logo.png)

Kritim Yantra

Jul 03, 2025

Two-Factor Authentication (2FA) in Laravel 12 — A Complete Step-by-Step Guide for Beginners

## 📢 Let’s Set the Scene

Imagine this:
You launch a Laravel app. It’s beautiful. Fast. Functional. But there’s one thing missing... **security**.

One day, a user sends you a message:

> “Hey! Someone logged into my account. Can you add 2FA?”

That’s your cue. Two-Factor Authentication (2FA) isn’t just a *nice-to-have*—it’s **essential** for protecting user accounts. If you’ve ever used a code from your phone to log in, you've used 2FA.

In this blog, you’ll learn how to **implement 2FA in Laravel 12** with a friendly, step-by-step tutorial — no experience needed.

---

## 🤔 What is 2FA and Why Does It Matter?

**2FA** adds a second layer of security to your app login.
Instead of just using a password (which can be stolen), users need a second “factor” — usually a **time-based code** from an app like **Google Authenticator** or **Authy**.

🛡️ With 2FA:

* Even if someone steals your password, they can't log in
* It protects your users and your reputation
* It’s becoming the **industry standard**

---

## 🧰 Tools We’ll Use

* **Laravel 12** (of course!)
* **Google Authenticator / Authy** (TOTP-based apps)
* **`pragmarx/google2fa-laravel`** package for generating codes

---

## 🛠️ Step 1: Setting Up Laravel 12

Start fresh (or use an existing app):

```
composer create-project laravel/laravel laravel-2fa
cd laravel-2fa
php artisan serve

```

Create auth scaffolding:

```
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run dev
php artisan migrate

```

Now you’ve got **login, registration, and dashboard** out of the box.

---

## 📦 Step 2: Install the 2FA Package

We’ll use [PragmaRX's Google2FA package](https://github.com/antonioribeiro/google2fa-laravel).

```
composer require pragmarx/google2fa-laravel

```

Now publish the config:

```
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"

```

This will create a `config/google2fa.php` file where you can customize settings if needed.

---

## 🧑‍💻 Step 3: Update the User Model

Add a new column to store the 2FA secret key.

### Create a Migration

```
php artisan make:migration add_google2fa_secret_to_users_table

```
### Update the Migration File:

```
Schema::table('users', function (Blueprint $table) {
    $table->text('google2fa_secret')->nullable();
});

```

Then run the migration:

```
php artisan migrate

```
### In `User.php`, make the field fillable:

```
protected $fillable = [
    'name', 'email', 'password', 'google2fa_secret',
];

```

---

## 📱 Step 4: Generate the 2FA Secret and QR Code

Let’s create a setup screen so users can enable 2FA using their authenticator app.

### Route Setup (web.php)

```
use App\Http\Controllers\TwoFactorController;

Route::middleware(['auth'])->group(function () {
    Route::get('/2fa', [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
});

```
### Create the Controller

```
php artisan make:controller TwoFactorController

```
### Inside `TwoFactorController.php`:

```
use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

public function show(Request $request)
{
    $user = $request->user();

    // Generate secret if it doesn't exist
    if (!$user->google2fa_secret) {
        $user->google2fa_secret = Google2FA::generateSecretKey();
        $user->save();
    }

    // Create QR Code URL
    $QR_Image = $this->generateQRCode($user->email, $user->google2fa_secret);

    return view('2fa.setup', [
        'QR_Image' => $QR_Image,
        'secret' => $user->google2fa_secret
    ]);
}

public function setup(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6',
    ]);

    $google2fa = app('pragmarx.google2fa');

    if ($google2fa->verifyKey(auth()->user()->google2fa_secret, $request->otp)) {
        return redirect('/dashboard')->with('success', '2FA enabled successfully.');
    }

    return back()->withErrors(['otp' => 'Invalid verification code.']);
}

private function generateQRCode($email, $secret)
{
    $QRContent = Google2FA::getQRCodeUrl(
        config('app.name'),
        $email,
        $secret
    );

    $renderer = new ImageRenderer(
        new RendererStyle(200),
        new SvgImageBackEnd()
    );

    $writer = new Writer($renderer);
    return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($QRContent));
}

```

---

## 🖼️ Create the 2FA Setup View

Create a file at: `resources/views/2fa/setup.blade.php`

```
<x-app-layout>
    <h2>🔐 Two-Factor Authentication Setup</h2>

    <p>Scan this QR code with Google Authenticator or Authy:</p>
    <div>
        <img src="{{ $QR_Image }}" />
    </div>

    <form method="POST" action="{{ route('2fa.setup') }}">
        @csrf
        <label>Enter the 6-digit code:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</x-app-layout>

```

---

## 🔐 Step 5: Protect Routes with 2FA

We’ll now ensure users enter a valid 2FA code after logging in.

### Middleware to Check 2FA

```
php artisan make:middleware Ensure2FAIsVerified

```

In `Ensure2FAIsVerified.php`:

```
public function handle($request, Closure $next)
{
    if ($request->user()->google2fa_secret && !$request->session()->get('2fa_verified')) {
        return redirect('/2fa/verify');
    }

    return $next($request);
}

```
### Register Middleware in `bootstrap/app.php`

Open your `bootstrap/app.php` and update the `withMiddleware()` call like this:

```
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your 2FA middleware alias
        $middleware->alias([
            '2fa' => \App\Http\Middleware\Ensure2FAIsVerified::class,
        ]);

        // Optionally, include it in the global or group stacks:
        // $middleware->prepend(\App\Http\Middleware\Ensure2FAIsVerified::class);
        // $middleware->appendToGroup('web', \App\Http\Middleware\Ensure2FAIsVerified::class);
    })
    ->create();

```

Then protect routes like this:

```
Route::middleware(['auth', '2fa'])->get('/dashboard', function () {
    return view('dashboard');
});

```

---

## 📲 Step 6: Create the 2FA Verification Page

Add a route and controller method:

```
Route::get('/2fa/verify', [TwoFactorController::class, 'verifyForm']);
Route::post('/2fa/verify', [TwoFactorController::class, 'verifyCode']);

```

In the controller:

```
public function verifyForm()
{
    return view('2fa.verify');
}

public function verifyCode(Request $request)
{
    $request->validate(['otp' => 'required|digits:6']);

    if (Google2FA::verifyKey(auth()->user()->google2fa_secret, $request->otp)) {
        $request->session()->put('2fa_verified', true);
        return redirect('/dashboard');
    }

    return back()->withErrors(['otp' => 'Invalid code.']);
}

```

Then create `resources/views/2fa/verify.blade.php`:

```
<x-app-layout>
    <h2>🔑 Verify 2FA</h2>

    <form method="POST" action="{{ url('/2fa/verify') }}">
        @csrf
        <label>Enter your 6-digit code:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</x-app-layout>

```

---

## 🧼 Extra: Disable 2FA (Optional Feature)

You could add a route/button to let users remove 2FA:

```
public function disable(Request $request)
{
    $request->user()->update(['google2fa_secret' => null]);
    $request->session()->forget('2fa_verified');

    return redirect('/dashboard')->with('success', '2FA disabled.');
}

```

---

## ✅ Wrapping Up: You’ve Built Secure Login with 2FA!

Let’s recap:

* 🔧 You installed Laravel 12 and a 2FA package
* 🔐 You generated a secret and QR code
* ✅ You verified the user’s code
* 🛡️ You protected your routes from unauthorized access

Your app now has **enterprise-grade security** 🎉

**Happy Coding, and Stay Secure! 🔐**

### Tags

[Laravel](https://kritimyantra.com/topics/laravel)

[Sign in with Google](https://kritimyantra.com/auth/google)
0 Likes

## Comments

No comments yet. Be the first to comment!

Please log in to post a comment:

[Sign in with Google](https://kritimyantra.com/auth/google)

## Related Posts

[Laravel 12 Service Container: A Beginner's Guide](https://kritimyantra.com/blogs/laravel-12-service-container-a-beginners-guide)

Web Development

[Laravel 12 Service Container: A Beginner's Guide](https://kritimyantra.com/blogs/laravel-12-service-container-a-beginners-guide)

Laravel

Php

![Kritim Yantra](https://kritimyantra.com/assets/img/logo.png)
Kritim Yantra

Mar 11, 2025

[Laravel 12: Understanding Error and Exception Handling with Examples](https://kritimyantra.com/blogs/laravel-12-error-handling-guide)

Web Development

[Laravel 12: Understanding Error and Exception Handling with Examples](https://kritimyantra.com/blogs/laravel-12-error-handling-guide)

Laravel

Php

![Kritim Yantra](https://kritimyantra.com/assets/img/logo.png)
Kritim Yantra

Mar 23, 2025

[Running PHP 8.4 in Docker with Local File Mounting](https://kritimyantra.com/blogs/running-php-84-in-docker-with-local-file-mounting)

Web Development

[Running PHP 8.4 in Docker with Local File Mounting](https://kritimyantra.com/blogs/running-php-84-in-docker-with-local-file-mounting)

Docker

![Kritim Yantra](https://kritimyantra.com/assets/img/logo.png)
Kritim Yantra

Apr 13, 2025

## KritimYantra

Premium software solutions for businesses. Custom features, deployment, and maintenance – all in one place.

### Quick Links

* [Home](https://kritimyantra.com)
* [About](https://kritimyantra.com/about)
* [FAQ](https://kritimyantra.com/faq)
* [Contact](https://kritimyantra.com/contact)
* [Disclaimer](https://kritimyantra.com/disclaimer)

### Legal

* [Privacy Policy](https://kritimyantra.com/privacy)
* [Refund Policy](https://kritimyantra.com/cancellation-refund-policy)
* [Terms & Conditions](https://kritimyantra.com/terms-and-conditions)

### Contact Us

* Email: support@kritimyantra.com
* Address: Mau, Uttar Pradesh, India 276402

© 2025 Kritimyantra, Inc. All rights reserved.

[Facebook](https://www.facebook.com/kritimyantra/)
[Instagram](https://www.instagram.com/kritimyantra/)
[X (Twitter)](https://x.com/kritimyantra)
[GitHub](https://github.com/kritimyantra)
[YouTube](https://youtube.com/%40kritimyantra)

