<?php

namespace App\Location;

interface LocationInterface
{
    public function get();
    public function __construct($url, $key, $version);
    public function getRequestKey();
    public function setFilter($query);
    public function filterByCountry($country_code = 'jp');
    public function setEndpoint($endpoint);
}
