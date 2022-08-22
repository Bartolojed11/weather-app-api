<?php

namespace App\Location;

use Illuminate\Support\Facades\Http;

class Location implements LocationInterface
{
    private $url = '';
    private $endpoint = '';
    private $key = '';
    private $version = '';
    private $filters = '';
    private $qry = '';

    private const ALLOWED_PARAMS = [
        'text',
        'type'
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
            'data' => $response->json()['features'] ?? [],
            'status' => $response->status(),
            'ok' => $response->ok(),
            'serverError' => $response->serverError(),
            'clientError' => $response->clientError(),
            'request_name' => $this->setRequestName()
        ];
    }

    /**
     * This will return the request name that was created on @setRequestName()
     *
     * @return String
     */
    public function getRequestKey() {
        return $this->setRequestName();
    }

    /**
     * This will create a request name and will be used as a cache name
     * for the api request result
     */
    private function setRequestName() {
        $key = isset($this->qry['text']) ? $this->qry['text'] . '_' : '';
        $key .= isset($this->qry['type']) ? $this->qry['type'] : '';

        return $key;
    }

    /**
     * Will set the filter needed for transforming it
     * to be sent to the api
     *
     * @param [Object] $query
     * @return object
     */
    public function setFilter($query) {
        $filters = $this->getValidFilters($query);
        $filters = $this->transformFilter($filters);
        $this->filters = $filters;
        $this->qry = $query;
        return $this;
    }

    /**
     * Add a filter to a certain country code
     * to be sent to the api
     *
     * @param [String] $country_code
     * @return object
     */
    public function filterByCountry($country_code = 'jp') {
        $this->filters = "$this->filters&filter=countrycode:$country_code";
        return $this;
    }

    /**
     * Will set the endpoint of the api
     *
     * @param [String] $endpoint
     * @return Object
     */
    public function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Will return the valid filters for this api
     *
     * @param [Object] $query
     * @return array
     */
    private function getValidFilters($query) {
        $filters = [];
        $ndx = 0;

        foreach($query as $key => $value) {
            if (in_array($key, self::ALLOWED_PARAMS)) {
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
    private function transformFilter($query) {
        $filter = '';

        foreach($query as $key => $value) {
            $filter .= "&$key=$value";
        }

        $filter = "$filter&apiKey=$this->key";
        $filter = trim($filter, '&');

        $filter = "?$filter";
        return trim($filter);
    }
}
