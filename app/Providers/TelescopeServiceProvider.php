<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        // $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            // if ($this->app->environment('local') || $this->app->environment('testing')) {
            //     return true;
            // }

            return $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        // if ($this->app->environment('local') || $this->app->environment('testing')) {
        //     return;
        // }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        // Get IP
        Gate::define('viewTelescope', function (User $user) {
            if (env('APP_ENV') !== 'local') {

                $request = app('request');
                $ipWhitelist = env('IP_WHITELIST', '');
                $ipWhitelist = explode(',', $ipWhitelist);

                if (!in_array($request->ip(), $ipWhitelist)) {
                    return false;
                }
            }
            return true;
        });
    }
}
