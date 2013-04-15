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
        $public = "";
        $public .= ($this->point_of_interest ? "<p>" . $this->point_of_interest . "<p>" : "");
        $public .= ($this->route ? "<p>" . $this->route . "<p>" : "");
        $public .= ($this->street_number ? "<p>" . $this->street_number . "<p>" : "");
        $public .= ($this->postal_code ? "<p>" . $this->postal_code . "<p>" : "");
        $public .= ($this->postal_code_prefix ? "<p>" . $this->postal_code_prefix . "<p>" : "");
        $public .= ($this->locality ? "<p>" . $this->locality . "<p>" : "");
        $public .= ($this->sublocality ? "<p>" . $this->sublocality . "<p>" : "");
        $public .= ($this->administrative_area_level_2 ? "<p>" . $this->administrative_area_level_2 . "<p>" : "");
        $public .= ($this->administrative_area_level_1 ? "<p>" . $this->administrative_area_level_1 . "<p>" : "");
        $public .= ($this->country ? "<p>" . $this->country . "<p>" : "");
        $public .= ($this->continent ? "<p>" . $this->continent . "<p>" : "");
        $public .= ($this->planetary_body ? "<p>" . $this->planetary_body . "<p>" : "");
        $public .= ($this->natural_feature ? "<p>" . $this->natural_feature . "<p>" : "");
        $public .= ($this->establishment ? "<p>" . $this->establishment . "<p>" : "");
        $public .= ($this->point_of_interest ? "<p>" . $this->point_of_interest . "<p>" : "");
        return $public;
    }
}