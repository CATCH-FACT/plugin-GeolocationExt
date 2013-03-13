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
        $public .= ($this->point_of_interest ? $this->point_of_interest . "<br>" : "");
        $public .= ($this->route ? $this->route . "<br>" : "");
        $public .= ($this->street_number ? $this->street_number . "<br>" : "");
        $public .= ($this->postal_code ? $this->postal_code . "<br>" : "");
        $public .= ($this->postal_code_prefix ? $this->postal_code_prefix . "<br>" : "");
        $public .= ($this->locality ? $this->locality . "<br>" : "");
        $public .= ($this->sublocality ? $this->sublocality . "<br>" : "");
        $public .= ($this->administrative_area_level_2 ? $this->administrative_area_level_2 . "<br>" : "");
        $public .= ($this->administrative_area_level_1 ? $this->administrative_area_level_1 . "<br>" : "");
        $public .= ($this->country ? $this->country . "<br>" : "");
        $public .= ($this->continent ? $this->continent . "<br>" : "");
        $public .= ($this->planetary_body ? $this->planetary_body . "<br>" : "");
        $public .= ($this->natural_feature ? $this->natural_feature . "<br>" : "");
        $public .= ($this->establishment ? $this->establishment . "<br>" : "");
        $public .= ($this->point_of_interest ? $this->point_of_interest . "<br>" : "");
        return $public;
    }
}