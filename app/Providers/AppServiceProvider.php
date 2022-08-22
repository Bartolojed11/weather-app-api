<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Weather\WeatherInterface;
use App\Weather\OpenWeather;

use App\Location\LocationInterface;
use App\Location\Location;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(OpenWeather::class, function () {
            $config = config('services.weather');
            return new OpenWeather($config['url'], $config['key'], $config['version']);
        });

        $this->app->bind(Location::class, function () {
            $config = config('services.location');
            return new Location($config['url'], $config['key'], $config['version']);
        });

        $this->app->bind(LocationInterface::class, Location::class);
        $this->app->bind(WeatherInterface::class, OpenWeather::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
