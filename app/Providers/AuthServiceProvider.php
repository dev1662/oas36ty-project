<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Illuminate\Auth\Notifications\ResetPassword;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (! $this->app->routesAreCached()) {
            Passport::routes();
        }

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.url').'/reset-password?token='.$token;
            // return '/reset-password?token='.$token;

        });
        // ResetPassword::createUrlUsing(function ($user, string $token) {
        //     return config('app.url').'/invitation?token='.$token;
        //     // return config('app.url').'/invitation?token='.$token;

        // });
        // ResetPassword::createUrlUsing(function ($user, string $token) {
        //     return config('app.url').'/accept-invitation?token='.$token;
        //     // return config('app.url').'/invitation?token='.$token;

        // });

    }
}
