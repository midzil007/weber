<?

class module_GoogleMaps
{		
	protected $base = 'maps.google.com';
  	
	function __construct() { 
		$this->db = Zend_Registry::getInstance()->db;
		$this->key = Zend_Registry::getInstance()->db->config->instance->mapKey;
		$this->baseUrl = "http://" . $this->base . "/maps/geo?output=xml" . "&key=" . $this->key; 		
	}
	
	function getInfoByLocation($location){
		$url = $this->baseUrl . '&q=' . urlencode($location);		
		return $this->getData($url); 		
	}
	
	function getData($url){		
		$xml = simplexml_load_file($url);		
		// pr($xml);
		$data = array();
		$status = $xml->Response->Status->code;
		if (strcmp($status, "200") == 0) { // Successful geocode
			$coordinates = $xml->Response->Placemark->Point->coordinates;
			
			$coordinatesSplit = split(",", $coordinates);					
			  
			$data['lat'] = (float) $coordinatesSplit[1];
			$data['lng'] = (float) $coordinatesSplit[0]; 
			$data['adress'] = (string) $xml->Response->Placemark->address;  
			$data['CountryNameCode'] = (string) $xml->Response->Placemark->AddressDetails->Country->CountryNameCode; 
			$data['CountryName'] = (string) $xml->Response->Placemark->AddressDetails->Country->CountryName;
			$data['box_north'] = (float) $xml->Response->Placemark->ExtendedData->LatLonBox['north']; 
			$data['box_south'] = (float) (float)$xml->Response->Placemark->ExtendedData->LatLonBox['south'];  
			$data['box_east'] = (float) $xml->Response->Placemark->ExtendedData->LatLonBox['east']; 
			$data['box_west'] = (float) $xml->Response->Placemark->ExtendedData->LatLonBox['west']; 
			
		} else if (strcmp($status, "620") == 0) { // sent geocodes too fast 
			usleep(100000);
			return $this->getData($url);
		} else { // failure to geocode
			
		}
 		return $data;  
	}
}

?>