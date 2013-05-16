<?php

/**
 * Location
 * @package: Omeka
 */
class Location extends Omeka_Record_AbstractRecord
{
    public $item_id;
    public $latitude;
    public $longitude;
    public $zoom_level;
    public $map_type;

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
    
    public $address;
    
    protected function _validate()
    {
        if (empty($this->item_id)) {
            $this->addError('item_id', 'Location requires an item id.');
        }
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
                            );
        return $view_items;
    }
}