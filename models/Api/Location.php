<?php
/**
 * Omeka
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * @package Omeka\Record\Api
 */
class Api_Location extends Omeka_Record_Api_AbstractRecordAdapter
{
    /**
     * Get the REST representation of a location.
     * 
     * @param Location $record
     * @return array
     */
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
            'id' => $record->id, 
            'url' => $this->getResourceUrl("/geolocations/{$record->id}"), 
            
            "point_of_interest" => $record->point_of_interest, 
            "route" => $record->route,
            "street_number" => $record->street_number,
            "postal_code" => $record->postal_code,
            "postal_code_prefix" => $record->postal_code_prefix,
            "sublocality" => $record->sublocality,
            "locality" => $record->locality,
            "natural_feature" => $record->natural_feature,
            "establishment" => $record->establishment,
            "point_of_interest" => $record->point_of_interest,
            "administrative_area_level_3" => $record->administrative_area_level_3,
            "administrative_area_level_2" => $record->administrative_area_level_2,
            "administrative_area_level_1" => $record->administrative_area_level_1,
            "country" => $record->country,
            "continent" => $record->continent,
            "planetary_body" => $record->planetary_body,
                                
            'latitude' => $record->latitude, 
            'longitude' => $record->longitude, 
            'zoom_level' => $record->zoom_level, 
            'map_type' => $record->map_type, 
            'address' => $record->address, 
            'item' => array(
                'id' => $record->item_id, 
                'url' => $this->getResourceUrl("/items/{$record->item_id}"), 
                'resource' => 'items', 
            ),
            'location_type' => $record->location_type
        );
        return $representation;
    }
    
    /**
     * Set POST data to a location.
     * 
     * @param Location $record
     * @param mixed $data
     */
    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {
        if (isset($data->item->id)) {
            $record->item_id = $data->item->id;
        }
        
        if (isset($data->point_of_interest)) {
            $record->point_of_interest = $data->point_of_interest;
        }
        if (isset($data->route)) {
            $record->route = $data->route;
        }
        if (isset($data->street_number)) {
            $record->street_number = $data->street_number;
        }
        if (isset($data->postal_code)) {
            $record->postal_code = $data->postal_code;
        }
        if (isset($data->postal_code_prefix)) {
            $record->postal_code_prefix = $data->postal_code_prefix;
        }
        if (isset($data->sublocality)) {
            $record->sublocality = $data->sublocality;
        }
        if (isset($data->locality)) {
            $record->locality = $data->locality;
        }
        if (isset($data->natural_feature)) {
            $record->natural_feature = $data->natural_feature;
        }
        if (isset($data->establishment)) {
            $record->establishment = $data->establishment;
        }
        if (isset($data->point_of_interest)) {
            $record->point_of_interest = $data->point_of_interest;
        }
        if (isset($data->administrative_area_level_3)) {
            $record->administrative_area_level_3 = $data->administrative_area_level_3;
        }
        if (isset($data->administrative_area_level_2)) {
            $record->administrative_area_level_2 = $data->administrative_area_level_2;
        }
        if (isset($data->administrative_area_level_1)) {
            $record->administrative_area_level_1 = $data->administrative_area_level_1;
        }
        if (isset($data->country)) {
            $record->country = $data->country;
        }
        if (isset($data->continent)) {
            $record->continent = $data->continent;
        }
        if (isset($data->planetary_body)) {
            $record->planetary_body = $data->planetary_body;
        }
        
        if (isset($data->latitude)) {
            $record->latitude = $data->latitude;
        }
        if (isset($data->longitude)) {
            $record->longitude = $data->longitude;
        }
        if (isset($data->zoom_level)) {
            $record->zoom_level = $data->zoom_level;
        }
        if (isset($data->map_type)) {
            $record->map_type = $data->map_type;
        } else {
            $record->map_type = '';
        }
        if (isset($data->address)) {
            $record->address = $data->address;
        } else {
            $record->address = '';
        }
    }
    
    /**
     * Set PUT data to a location.
     * 
     * @param Location $record
     * @param mixed $data
     */
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {
        if (isset($data->point_of_interest)) {
            $record->point_of_interest = $data->point_of_interest;
        }
        if (isset($data->route)) {
            $record->route = $data->route;
        }
        if (isset($data->street_number)) {
            $record->street_number = $data->street_number;
        }
        if (isset($data->postal_code)) {
            $record->postal_code = $data->postal_code;
        }
        if (isset($data->postal_code_prefix)) {
            $record->postal_code_prefix = $data->postal_code_prefix;
        }
        if (isset($data->sublocality)) {
            $record->sublocality = $data->sublocality;
        }
        if (isset($data->locality)) {
            $record->locality = $data->locality;
        }
        if (isset($data->natural_feature)) {
            $record->natural_feature = $data->natural_feature;
        }
        if (isset($data->establishment)) {
            $record->establishment = $data->establishment;
        }
        if (isset($data->point_of_interest)) {
            $record->point_of_interest = $data->point_of_interest;
        }
        if (isset($data->administrative_area_level_3)) {
            $record->administrative_area_level_3 = $data->administrative_area_level_3;
        }
        if (isset($data->administrative_area_level_2)) {
            $record->administrative_area_level_2 = $data->administrative_area_level_2;
        }
        if (isset($data->administrative_area_level_1)) {
            $record->administrative_area_level_1 = $data->administrative_area_level_1;
        }
        if (isset($data->country)) {
            $record->country = $data->country;
        }
        if (isset($data->continent)) {
            $record->continent = $data->continent;
        }
        if (isset($data->planetary_body)) {
            $record->planetary_body = $data->planetary_body;
        }

        if (isset($data->latitude)) {
            $record->latitude = $data->latitude;
        }
        if (isset($data->longitude)) {
            $record->longitude = $data->longitude;
        }
        if (isset($data->zoom_level)) {
            $record->zoom_level = $data->zoom_level;
        }
        if (isset($data->map_type)) {
            $record->map_type = $data->map_type;
        } else {
            $record->map_type = '';
        }
        if (isset($data->location_type)) {
            $record->location_type = $data->location_type;
        } else {
            $record->location_type = '';
        }
        
        if (isset($data->address)) {
            $record->address = $data->address;
        } else {
            $record->address = '';
        }
    }
}