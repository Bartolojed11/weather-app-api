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

    // For homepage of location
    // Display's default location and it's temperature
    public function index()
    {
        $default_cities = config('locations.default.cities');
        $icon_url = config('weather.icon.url');

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
            $data[$ndx]['temp'] = $weather['data']['main']['temp'] ?? '';
            $icon = $weather['data']['weather'][0]['icon'] ?? '';
            $data[$ndx]['icon'] = $icon == '' ? '' : $icon_url . $icon . '.png';
            $data[$ndx]['time'] = date('g:i a', $weather['data']['dt']);
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

    // For autocomplete api : Recheck if this is really needed
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
