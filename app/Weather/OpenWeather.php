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

    public function __construct($url, $key, $version)
    {
        $this->url = $url;
        $this->key = $key;
        $this->version = $version;
    }

    public function get()
    {

        $response = Http::get($this->url . $this->version . $this->endpoint . $this->filters);

        return [
            'data' => $response->json(),
            'status' => $response->status(),
            'ok' => $response->ok(),
            'serverError' => $response->serverError(),
            'clientError' => $response->clientError(),
            'request_name' => $this->requestName()
        ];
    }

    public function setFilter($query) {
        $filters = $this->getValidFilters($query);
        $filters = $this->transformFilter($filters);
        $this->filters = $filters;
        $this->qry = $query;
        return $this;
    }

    private function requestName() {
        $key = isset($this->qry['lon']) ? $this->qry['lon'] . '_' : '';
        $key .= isset($this->qry['lat']) ? $this->qry['lat'] : '';

        return $key;
    }

    public function getRequestKey() {
        return $this->requestName();
    }

    public function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
        return $this;
    }

    private function getValidFilters($query) {
        $filters = [];
        $ndx = 0;

        foreach($query as $key => $value) {
            if (in_array($key, self::FILTERS)) {
                $filters[$key] = $value;
            }
            $ndx++;
        }

        return $filters;
    }

    private function transformFilter($query) {
        $filter = '';

        foreach($query as $key => $value) {
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
