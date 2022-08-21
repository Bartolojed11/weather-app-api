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
            'data' => $response->json()['features'] ?? [],
            'status' => $response->status(),
            'ok' => $response->ok(),
            'serverError' => $response->serverError(),
            'clientError' => $response->clientError(),
            'request_name' => $this->requestName()
        ];
    }

    public function getRequestKey() {
        return $this->requestName();
    }

    private function requestName() {
        $key = isset($this->qry['text']) ? $this->qry['text'] . '_' : '';
        $key .= isset($this->qry['type']) ? $this->qry['type'] : '';

        return $key;
    }

    public function setFilter($query) {
        $filters = $this->getValidFilters($query);
        $filters = $this->transformFilter($filters);
        $this->filters = $filters;
        $this->qry = $query;
        return $this;
    }

    public function filterByCountry($country_code = 'jp') {
        $this->filters = "$this->filters&filter=countrycode:$country_code";
        return $this;
    }

    public function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
        return $this;
    }

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
