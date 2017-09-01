<?php
class AddressSearch extends myOracle{
	private $gpsX;
	private $gpsY;
	private $results = array();
	private $googleKey = '';
	private $adid = array();
	
	public function construct(){
		parent:construct();
	}
	//Get the address_id for the given address
	private function validateAddress($a){
		$sql = "select address_id from blackr.addSearch where upper(address1) like upper('%";
		if(is_array($a)){
			$sql .= $a['address1']."%')";
			if(isset($a['apt_unit'])){
				$sql .= " and upper(apt_unit) = upper('".$a['apt_unit']."')";
			}
			$sql .= " and upper(city) = upper('".$a['city']."') and zip = ".$a['zip'];
			
			$r = $this->runQuery($sql);
			$x = 0;
			while($row = oci_fetch_array($r)){
				$this->adid[$x] = $row[0];
				$x++;
			}
			//$row = oci_fetch_row($r);
			//$this->adid = $row[0];			
		}
	}
	
	//Pull the googleMap data for a given address
	private function getCoords($a){
		$googleMapURL = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
		$addString = $a['address1'].', '.$a['city'];
		$URL = $googleMapURL.$addString.'&key='.$this->googleKey;
		$results = file_get_contents($URL);
		/*
		Expected results format
		{
   "results" : [
      {
         "address_components" : [
            {
               "long_name" : "1600",
               "short_name" : "1600",
               "types" : [ "street_number" ]
            },
            {
               "long_name" : "Amphitheatre Pkwy",
               "short_name" : "Amphitheatre Pkwy",
               "types" : [ "route" ]
            },
            {
               "long_name" : "Mountain View",
               "short_name" : "Mountain View",
               "types" : [ "locality", "political" ]
            },
            {
               "long_name" : "Santa Clara County",
               "short_name" : "Santa Clara County",
               "types" : [ "administrative_area_level_2", "political" ]
            },
            {
               "long_name" : "California",
               "short_name" : "CA",
               "types" : [ "administrative_area_level_1", "political" ]
            },
            {
               "long_name" : "United States",
               "short_name" : "US",
               "types" : [ "country", "political" ]
            },
            {
               "long_name" : "94043",
               "short_name" : "94043",
               "types" : [ "postal_code" ]
            }
         ],
         "formatted_address" : "1600 Amphitheatre Parkway, Mountain View, CA 94043, USA",
         "geometry" : {
            "location" : {
               "lat" : 37.4224764,
               "lng" : -122.0842499
            },
            "location_type" : "ROOFTOP",
            "viewport" : {
               "northeast" : {
                  "lat" : 37.4238253802915,
                  "lng" : -122.0829009197085
               },
               "southwest" : {
                  "lat" : 37.4211274197085,
                  "lng" : -122.0855988802915
               }
            }
         },
         "place_id" : "ChIJ2eUgeAK6j4ARbn5u_wAGqWA",
         "types" : [ "street_address" ]
      }
   ],
   "status" : "OK"
}
		*/
		return json_decode($results);
	}
	
	//Validate the status of a given address_id	
	private function validateOrderable(){
		if(isset($this->adid)){
			$adid = $this->adid;
			$rec = array();
			array_walk($adid,function($a) use($adid,&$rec){
				$sql = 'select LANDUSE,AU_ADDRESS_STATUS,GIS_ADDRESS_STATUS,FOOTPRINT_ID from blackr.om_address_lookup_more_info where address_id = '.$a;
				$r = $this->runQuery($sql);
				$row = oci_fetch_array($r);	
				$row['ADID'] = $a;
				switch($row['LANDUSE']){
					case 'SMALL MDU':
                	case 'Multi Dwelling Unit':
                	case 'MULTI DWELLING UNIT':
                	case 'DUPLEX':
                	case 'Residential':
                	case 'RESIDENTIAL':
                	case 'Duplex':
                	case 'Other_Unknown':
                	case 'Small Mdu':
						$row['LANDUSE'] = 'RESIDENTIAL';
					break;
					default:
						$row['LANDUSE'] = 'BUSINESS';
					break;
				}
				switch($row['AU_ADDRESS_STATUS']){
					case 'DISCONNECTED':
						$row['HISTORY_OF_SERVICE'] = 1;
						$row['CURRENT_SERVICE'] = 0;
					break;
					case 'ACTIVE':
						$row['HISTORY_OF_SERVICE'] = 1;
						$row['CURRENT_SERVICE'] = 1;
					break;
					default:
						$row['HISTORY_OF_SERVICE'] = 0;
						$row['CURRENT_SERVICE'] = 0;
					break;
				}
				array_push($rec,$row);
				//print_r($rec);
			});
			//print_r($rec);
			$this->results = $rec;
		}else return false;
	}
	
	public function addressCheck($a){
		$a = json_decode($a,true);
		//print_r($a);
		$this->validateAddress($a);
		$this->validateOrderable();
		return $this->results;
	}
}
?>