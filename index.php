<?php
include_once "class.search.php";

///Test Here
$latitude = "23.3838" ;
$longitude = "10.22" ;
$orderBy = "";
$limit = 3;

var_dump(Search::getNearbyHotels($latitude, $longitude, $orderBy, $limit));
?>
