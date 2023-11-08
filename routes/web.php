<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('auth/redirect', function () {
    return Socialite::driver('laravelpassport')->redirect();
});

Route::get('auth/callback', function () {
    $driver = Socialite::driver('laravelpassport');
    $scadaUser = $driver->user();

    $user = User::where('email', $scadaUser->getEmail())->first();
    if (!$user) {
        $user = User::create([
            'name' => $scadaUser->getName(),
            'email' => $scadaUser->getEmail(),
        ]);

        $user->oauthTokens()->create([
            'provider' => 'laravelpassport',
            'access_token' => encrypt($scadaUser->token),
            'refresh_token' => encrypt($scadaUser->refreshToken),
            'expires_at' => now()->addSeconds($scadaUser->expiresIn),
        ]);
    } else {
        $user->oauthTokens()->updateOrCreate([
            'provider' => 'laravelpassport',
        ], [
            'access_token' => encrypt($scadaUser->token),
            'refresh_token' => encrypt($scadaUser->refreshToken),
            'expires_at' => now()->addSeconds($scadaUser->expiresIn),
        ]);
    }

    Auth::login($user, true);

    return $user;
});

Route::get('refresh', function () {
    $user = Auth::user();
    if (!$user) {
        return redirect('auth/redirect');
    }

    $token = $user->oauthTokens()->where('provider', 'laravelpassport')->first();

    $driver = Socialite::driver('laravelpassport');
    $scadaUser = $driver->userFromToken(decrypt($token->access_token));

    $user->oauthTokens()->updateOrCreate([
        'provider' => 'laravelpassport',
    ], [
        'access_token' => encrypt($scadaUser->token),
        'refresh_token' => encrypt($scadaUser->refreshToken),
        'expires_at' => now()->addSeconds($scadaUser->expiresIn),
    ]);

    return $user;
});

Route::get('user', function() {
    return Auth::user()->oauthTokens;
});

Route::get('auth/logout', function () {
    Auth::logout();

    return redirect('/');
});
