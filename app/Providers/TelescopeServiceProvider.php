<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
        Telescope::night();

        // $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {

            if (isset($entry->content['name'])) {
                if ($entry->content['name'] === 'App\Events\CallActivityEvent') {
                    return false;
                }
                if (strpos($entry->content['name'], 'App\Jobs\GeneralJob') !== false) {
                }
            }
            if ($this->app->environment('local') || $this->app->environment('testing')) {
                return true;
            }

            // Get job info


            return $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag() ||
                $entry->type === 'job'; // Agregar esta línea
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
