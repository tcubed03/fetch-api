<?php
class Search{
	public $hotelsByDistance = [];
	public $hotelsByPrice = [];

	const FETCH_HOTELS_URL = "https://s3.amazonaws.com/koisys-interviews/hotels.json";

	public function fetchHotels(){
		//Fetch array of hotels from API
		$hotelsInJson = file_get_contents(Search::FETCH_HOTELS_URL);
		$hotels = json_decode($hotelsInJson);
		return $hotels->message;
	}

	public function getHotelsByDistance($latitude, $longitude){
		//Fetch the array of hotels from API
		$hotelList = $this->fetchHotels();

		//Calculate the distance of the user from each hotel fetched
		foreach ($hotelList as $key => $hotelData) {
			
			$hotelLatitude = null;
			$hotelLongitude = null;

			/*
			Here we assume, that the fisrt coordinate is the latitude and the second longitude
			some hotel data have varying length of arrays hence index of latitude and longitude changes with length*/
			$latKey = count($hotelData)-3;
			$longKey = count($hotelData)-2;

			$hotelLatitude = (is_numeric($hotelData[$latKey])) ? $hotelData[$latKey] : null;
			$hotelLongitude = (is_numeric($hotelData[$longKey])) ? $hotelData[$longKey] : null;
			
			
		
			if($hotelLatitude != null || $hotelLongitude != null){

			   //convert to radians from degrees
			   $firstLong = deg2rad($longitude); 
	           $secondLong = deg2rad($hotelLongitude); 
	           $firstLat = deg2rad($latitude); 
	           $secondLat = deg2rad($hotelLatitude); 
	              
	           //Haversine Formula for calculating distance between coordinates
	           $changeInLong = $secondLong - $firstLong; 
	           $changeInLat = $secondLat - $firstLat; 
	              
	           $val = pow(sin($changeInLat/2),2)+cos($firstLat)*cos($secondLat)*pow(sin($changeInLong/2),2); 
	              
	           $res = 2 * asin(sqrt($val)); 
	              
	           $radius = 6372.795477598; 
	           $distInKm = $res*$radius;
	              
	           
	           $hotelsByDistance[$key] = $distInKm;
       		}
       		

        }
        //returns unsorted one-dimensional array with each distance from the user
        return $hotelsByDistance;

	}

	public static function getHotelsByPrice(){
		//fetch hotels from API
		$search = new Search();
		$hotelList = $search->fetchHotels();

		//create an array to hold all the prices of the hotels only with their corresponding keys
		foreach ($hotelList as $key => $hotelData) {
			
			$hotelsByPrice[$key] = $hotelData[count($hotelData)-1];
		}
		//returns unsorted one-dimensional array that contains the price for each hotel per night
		return $hotelsByPrice;

	}

	public static function getNearbyHotels($latitude, $longitude, $orderBy, $limit){
		//fetch hotels from API
		$search = new Search();
		$hotelList = $search->fetchHotels();
		//get unsorted array of price
		$listHotelsByPrice = $search->getHotelsByPrice();
		//get unsorted array of distances
		$listHotelsByDistance = $search->getHotelsByDistance($latitude, $longitude);

		//$nearByHotels array holds final result
		$nearByHotels = [];

		if(isset($orderBy) && $orderBy == "pricepernight"){
			//sort in ascending order
			asort($listHotelsByPrice);

			if(is_int($limit) && $limit > 0){

				//$i is our counter for the limit passed to this function
				$i = 0;
				foreach ($listHotelsByPrice as $key => $value) {
					//create new array to hold name, distance and price for each hotel sorted by price
					$nearByHotels[$i]["name"] = $hotelList[$key][0]."'";
					$nearByHotels[$i]["distance"] = $listHotelsByDistance[$key]." KM,";
					$nearByHotels[$i]["price"] = $value." EUR";
					$i++;
					
					if($i==$limit) {
						break;
					}
					
				}
				return $nearByHotels;
			}
			return "Invalid limit";
			
		}
		else{	
				//sort distance in ascending order
				asort($listHotelsByDistance);
				if(is_int($limit) && $limit > 0){
				$i = 0;
				foreach ($listHotelsByDistance as $key => $value) {
					//nearByHotels holds name, distance and price for each hotel sorted by price
					$nearByHotels[$i]["name"] = $hotelList[$key][0].",";
					$nearByHotels[$i]["distance"] = $value." KM,";
					$nearByHotels[$i]["price"] = $listHotelsByPrice[$key]." EUR" ;
					
					$i++;
					
					if($i==$limit) {
						break;
					}
					
				}
				return $nearByHotels;
			}
			return "Invalid limit";
			
			
			return ($nearByHotels);
		}
        
       
	}



	
}






?>