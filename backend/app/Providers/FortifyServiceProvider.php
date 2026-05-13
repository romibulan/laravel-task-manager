<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedPasswordConfirmationResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse
        {

            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? response()->json(['success' => true, 'message' => "You have Successfully Registered"], 201)
                    : redirect(config('fortify.home'));
            }
        });

        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request)
            {

                $referer = request()->headers->get('referer');
                $originDomain = parse_url($referer, PHP_URL_HOST);
                Log::info("Login request from: " . $originDomain);
                return $request->wantsJson()
                    ? response()->json(['success' => true, 'message' => "Logged In successfully"], 200)
                    : redirect(config('fortify.home'));
            }
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? response()->json(['success' => true, 'message' => "Logged out successfully"], 200)
                    : redirect('/login');
            }
        });

        # password validation response on requesting /user/confirm-password that takes a password input. This route hits the store method of Laravel\Fortify\Http\Controllers\ConfirmablePasswordController.

        $this->app->instance(PasswordConfirmedResponse::class, new class implements PasswordConfirmedResponse
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? response()->json(['confirmed' => true], 200)
                    : back()->with('confirmed', true);
            }
        });

        $this->app->instance(FailedPasswordConfirmationResponse::class, new class implements FailedPasswordConfirmationResponse
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? response()->json(['confirmed' => false], 200)
                    : back()->with('confirmed', false);
            }
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::confirmPasswordsUsing(function (User $user, string $password) {
            return Hash::check($password, $user->password);
        });
    }
}
