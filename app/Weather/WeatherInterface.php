<?php

namespace App\Weather;

interface WeatherInterface
{
    public function __construct($url, $key, $version);
    public function setFilter($query);
    public function getRequestKey();
    public function setEndpoint($endpoint);
    public function get();
}
