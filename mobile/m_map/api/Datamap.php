<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Datamap
{
    public $pathIndex = "map/";
    private static $instance;
    function __construct()
    {
        $this->publish = load_class('Publishmap', $this->pathIndex);
    }

    function geojson()
    {
        $data = $this->publish->loadGeoJson();
        return $data;
    }
}
