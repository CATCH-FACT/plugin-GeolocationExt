<?php

/**
 * Location
 * @package: Omeka
 */
class Location extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
    public $item_id;
    public $latitude;
    public $longitude;
    public $zoom_level;
    public $map_type;

    public $location_type;                  #possibility to add multiple locations per item

	public $point_of_interest;             #spot
	public $route;                         #straat
	public $sublocality;                   #stadsdeel
    public $locality;                      #plaats
    public $administrative_area_level_3;   #sub gemeente (niet veel gebruikt)
    public $administrative_area_level_2;   #gemeente
    public $administrative_area_level_1;   #povincie
    public $country;                       #land
    public $continent;                     
    public $planetary_body;

    public $natural_feature;
    public $establishment;
    public $street_number;
    public $postal_code;
    public $postal_code_prefix;
    
    public $address;                    #search value saved as well
    
    protected function _validate()
    {
        if (empty($this->item_id)) {
            $this->addError('item_id', 'Location requires an item ID.');
        }
/*        // An item must exist.
        if (!$this->getTable('Item')->exists($this->item_id)) {
            $this->addError('item_id', __('Location requires a valid item ID.'));
        }*/
    }
    
    public function get_locationdata_for_public_viewing(){
        $view_items = array("point_of_interest" => $this->point_of_interest, 
                            "route" => $this->route,
                            "street_number" => $this->street_number,
                            "postal_code" => $this->postal_code,
                            "postal_code_prefix" => $this->postal_code_prefix,
                            "sublocality" => $this->sublocality,
                            "locality" => $this->locality,
                            "natural_feature" => $this->natural_feature,
                            "establishment" => $this->establishment,
                            "point_of_interest" => $this->point_of_interest,
                            "administrative_area_level_3" => $this->administrative_area_level_3,
                            "administrative_area_level_2" => $this->administrative_area_level_2,
                            "administrative_area_level_1" => $this->administrative_area_level_1,
                            "country" => $this->country,
                            "continent" => $this->continent,
                            "planetary_body" => $this->planetary_body,
//                            "location_type" => $this->location_type,
                            );
        return $view_items;
    }
    
    public function get_locationdata_for_api(){
        $view_items = array("item_id" => $this->item_id, 
                            "latitude" => $this->latitude,
                            "longitude" => $this->longitude,
                            "zoom_level" => $this->zoom_level,
                            "map_type" => $this->map_type,
                            "address" => $this->address,
                            "route" => $this->route,
                            "street_number" => $this->street_number,
                            "postal_code" => $this->postal_code,
                            "postal_code_prefix" => $this->postal_code_prefix,
                            "sublocality" => $this->sublocality,
                            "locality" => $this->locality,
                            "natural_feature" => $this->natural_feature,
                            "establishment" => $this->establishment,
                            "point_of_interest" => $this->point_of_interest,
                            "administrative_area_level_3" => $this->administrative_area_level_3,
                            "administrative_area_level_2" => $this->administrative_area_level_2,
                            "administrative_area_level_1" => $this->administrative_area_level_1,
                            "country" => $this->country,
                            "continent" => $this->continent,
                            "planetary_body" => $this->planetary_body,
                            "location_type" => $this->location_type,
                            );
        return $view_items;
    }
    
    /**
     * Identify Location records as relating to the Locations ACL resource.
     * 
     * @return string
     */
    public function getResourceId()
    {
        return 'Locations';
    }
}