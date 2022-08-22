<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Weather\WeatherInterface;
use App\Location\LocationInterface;

use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    public $weatherProvider = '';
    public $locationProvider = '';
    private $cache_life = '';

    public function __construct(WeatherInterface $weatherInterface, LocationInterface $locationInterface)
    {
        $this->weatherProvider = $weatherInterface;
        $this->locationProvider = $locationInterface;
        $this->cache_life = config('cache.location_cache_life') ?? 3600;
    }

    /**
     * This will return the current weather forecast and locations
     * based on the listed location on the locations configuration
     *
     * @return json
     */
    public function index()
    {
        $default_cities = config('locations.default.cities');
        $icon_url = config('services.weather.icon_url');

        $data = [];
        $ndx = 0;

        for ($i = 0; $i < count($default_cities); $i++) {

            // Immidiately break loop to save api request
            if (Cache::has('default_locations_info')) break;

            $filter = [];
            $filter['text'] = $default_cities[$i];
            $filter['type'] = 'city';

            $location = $this->locationProvider
                ->setEndpoint('/geocode/autocomplete')
                ->setFilter($filter)
                ->filterByCountry()
                ->get();

            $long = $location['data'][0]['properties']['lon'] ?? '';
            $lat = $location['data'][0]['properties']['lat'] ?? '';

            if ($long === '' || $lat === '') continue;

            $filter = [];
            $filter['lon'] = $long;
            $filter['lat'] = $lat;


            $weather = $this->weatherProvider
                ->setEndpoint('/weather')
                ->setFilter($filter)
                ->get();


            $data[$ndx]['city'] = $default_cities[$i];
            $data[$ndx]['lon'] = $long;
            $data[$ndx]['lat'] = $lat;

            $temperature = $weather['data']['main']['temp'] ?? '';
            $temperature_symbol = $weather['temp_symbol'] ?? '';
            $temperature = $temperature . ' ' . $temperature_symbol;

            $data[$ndx]['temp'] = $temperature;
            $icon = $weather['data']['weather'][0]['icon'] ?? '';
            $data[$ndx]['icon'] = $icon == '' ? '' : $icon_url . $icon . '.png';
            $data[$ndx]['weather_description'] = $weather['data']['weather'][0]['description'] ?? '';
            $ndx++;
        }

        if (!empty($data)) {
            // Save info for an hour
            Cache::put('default_locations_info', $data, $seconds = $this->cache_life);
        } else {
            $data = Cache::get('default_locations_info');
        }

        return response()->json($data);
    }

    /**
     * This will return the location based on the passed parameters
     * This will also store the the result on cache to save api request
     *
     * @query string text, type
     * @param Request $request
     * @return json
     */
    public function searchLocation(Request $request)
    {
        $request_name = $this->locationProvider
            ->setFilter($request->query())
            ->getRequestKey();

        if (!Cache::has($request_name)) {

            $response = $this->locationProvider
                ->setEndpoint('/geocode/autocomplete')
                ->setFilter($request->query())
                ->filterByCountry()
                ->get();

            Cache::forever($request_name, $response);
        } else {
            $response = Cache::get($request_name);
        }

        return response()->json($response);
    }

    /**
     * This will get the weather forecast for a given longited and latitued
     * This will also store the the result on cache to save api request
     *
     * @query string lon, lat, units, cnt
     * @param Request $request
     * @return json
     */
    public function weatherForecast(Request $request)
    {
        $endpoint = '/forecast';

        $request_name = $this->weatherProvider
            ->setFilter($request->query())
            ->getRequestKey();

        $request_name = $request_name . '_' . $endpoint;

        if (!Cache::has($request_name)) {

            $response = $this->weatherProvider
            ->setEndpoint($endpoint)
            ->setFilter($request->query())
            ->get();

            Cache::put($request_name, $response, $this->cache_life);
        } else {
            $response = Cache::get($request_name);
        }

        return response()->json($response);
    }
}
