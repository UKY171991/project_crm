<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') !== 'local' || str_contains(config('app.url'), 'https')) {
            \URL::forceScheme('https');
        }

        if (\Schema::hasTable('settings')) {
            $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
            view()->share('systemSettings', $settings);

            // Dynamically set Mail Configuration
            if (isset($settings['mail_mailer'])) {
                config([
                    'mail.default' => $settings['mail_mailer'],
                    'mail.mailers.smtp.host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.encryption' => ($settings['mail_encryption'] == 'none') ? null : ($settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption')),
                    'mail.mailers.smtp.username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
                    'mail.mailers.smtp.password' => $settings['mail_password'] ?? config('mail.mailers.smtp.password'),
                    'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
                ]);
            }
        }
    }
}
