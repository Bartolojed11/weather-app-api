<?php

namespace App\Weather;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenWeather implements WeatherInterface
{
    private $url = '';
    private $endpoint = '';
    private $key = '';
    private $version = '';
    private $filters = '';
    private $qry = '';

    private const FILTERS = [
        'lon',
        'lat',
        'units',
        'cnt'
    ];

    /**
     * $url: API URL
     * $key: API KEY
     * $version: API VERSION
     */
    public function __construct($url, $key, $version)
    {
        $this->url = $url;
        $this->key = $key;
        $this->version = $version;
    }

    /**
     * This will send a request to the third party api based on the
     * transformed params and endpoint
     *
     * @return array
     */
    public function get()
    {

        $response = Http::get($this->url . $this->version . $this->endpoint . $this->filters);

        return [
            'data' => $response->json(),
            'status' => $response->status(),
            'ok' => $response->ok(),
            'serverError' => $response->serverError(),
            'clientError' => $response->clientError(),
            'request_name' => $this->setRequestName(),
            'temp_symbol' => $this->getTempSymbol(),
            'icon_url' => config('services.weather.icon_url')
        ];
    }

    /**
     * Will set the filter needed for transforming it
     * to be sent to the api
     *
     * @param [Object] $query
     * @return object
     */
    public function setFilter($query)
    {
        $filters = $this->getValidFilters($query);
        $filters = $this->transformFilter($filters);
        $this->filters = $filters;
        $this->qry = $query;
        return $this;
    }

    /**
     * Return the used temperature symbol
     *
     * @return String
     */
    private function getTempSymbol()
    {
        $filter = $this->filters;

        if (strpos($filter, 'units=metric') === false) {
            return '°F';
        }

        return '°C';
    }

    /**
     * This will create a request name and will be used as a cache name
     * for the api request result
     */
    private function setRequestName()
    {
        $key = isset($this->qry['lon']) ? $this->qry['lon'] . '_' : '';
        $key .= isset($this->qry['lat']) ? $this->qry['lat'] : '';

        return $key;
    }

    /**
     * This will return the request name that was created on @setRequestName()
     *
     * @return String
     */
    public function getRequestKey()
    {
        return $this->setRequestName();
    }

    /**
     * Will set the endpoint of the api
     *
     * @param [String] $endpoint
     * @return Object
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Will return the valid filters for this api
     *
     * @param [Object] $query
     * @return array
     */
    private function getValidFilters($query)
    {
        $filters = [];
        $ndx = 0;

        foreach ($query as $key => $value) {
            if (in_array($key, self::FILTERS)) {
                $filters[$key] = $value;
            }
            $ndx++;
        }

        return $filters;
    }

    /**
     * This will transform the $query object into a string and parameters needed
     * for getting the api result
     *
     * @param [Object] $query
     * @return String
     */
    private function transformFilter($query)
    {
        $filter = '';

        foreach ($query as $key => $value) {
            $filter .= "&$key=$value";
        }

        // Add units if it's not on the query string
        // Metric as  celcius
        // Imperial as farenheight, also the default
        // Default to celcius since it is the most scientific fields measure temperature using the Celsius scale.
        // Source: https://education.nationalgeographic.org/resource/thermometer
        if (strpos($filter, 'units') === false) {
            $filter .= "&units=metric";
        }

        $filter = "$filter&appid=$this->key";
        $filter = trim($filter, '&');

        $filter = "?$filter";
        return trim($filter);
    }
}
