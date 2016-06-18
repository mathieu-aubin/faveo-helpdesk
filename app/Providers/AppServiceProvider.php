<?php

namespace App\Providers;

use App\Model\Update\BarNotification;
use Illuminate\Support\ServiceProvider;
use View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Illuminate\Contracts\Auth\Registrar');
    }

    public function boot()
    {
        // Please note the different namespace
        // and please add a \ in front of your classes in the global namespace
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('example1', '* * * * *', function () {
                $this->index();

                return 'No';
            });

            \Cron::add('example2', '*/2 * * * *', function () {
                // Do some crazy things successfully every two minute
            });

            \Cron::add('disabled job', '0 * * * *', function () {
                // Do some crazy things successfully every hour
            }, false);
        });

        $this->composer();
    }

    public function composer()
    {
        View::composer('themes.default1.update.notification', function () {
            $notification = new BarNotification();
            $data = [
                'data' => $notification->get(),
            ];
            view()->share($data);
        });
    }
}
