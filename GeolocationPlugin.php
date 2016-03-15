<?php

define('GOOGLE_MAPS_API_VERSION', '3.x');
define('GEOLOCATION_MAX_LOCATIONS_PER_PAGE', 20000);
define('GEOLOCATION_DEFAULT_LOCATIONS_PER_PAGE', 50);
define('GEOLOCATION_PLUGIN_DIR', PLUGIN_DIR . '/Geolocation');

class GeolocationPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
            'install',
            'initialize',
            'uninstall',
            'config_form',
            'config',
            'define_acl',
            'define_routes',
            'after_save_item',
            'admin_items_show_sidebar',
            'public_items_show_sidebar_ultimate_top',
            'admin_items_search',
            'public_items_search',
            'items_browse_sql',
            'public_head',
            'admin_head',
            'contribution_type_form',
            'contribution_save_form',
            'annotation_type_form',
            'annotation_save_form',
            );
    
    protected $_filters = array(
            'admin_navigation_main',
            'response_contexts',
            'action_contexts',
            'admin_items_form_tabs',
            'public_navigation_items',
            'api_resources',
            'api_extend_items',
            'search_record_types'
            );
    
    public $_all_geo_fields = array("point_of_interest",
                                "route",
                                "street_number",
                                "sublocality",
                                "locality",
                                "administrative_area_level_3",
                                "administrative_area_level_2",
                                "administrative_area_level_1",
                                "natural_feature",
                                "establishment",
                                "postal_code",
                                "postal_code_prefix",
                                "country",
                                "continent",
                                "planetary_body");

    public function hookInitialize(){
        $key = get_option('geolocation_gmaps_key');// ? get_option('geolocation_gmaps_key') : 'AIzaSyD6zj4P4YxltcYJZsRVUvTqG_bT1nny30o';
        $lang = "nl";
        queue_js_url("https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&key=$key&language=$lang");
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    public function setUp(){
#        if(plugin_is_active('Contribution')) {
#            $this->_hooks[] = 'contribution_append_to_type_form';
#            $this->_hooks[] = 'contribution_save_form';
#        }
        parent::setUp();
    }
    
    /**
     * Add SimplePagesPage as a searchable type.
     */
    public function filterSearchRecordTypes($recordTypes)
    {
        $recordTypes['Location'] = __('Locations');
        return $recordTypes;
    }
    
    public function hookAdminHead($args)
    {
#        $key = get_option('geolocation_gmaps_key');// ? get_option('geolocation_gmaps_key') : 'AIzaSyD6zj4P4YxltcYJZsRVUvTqG_bT1nny30o';
#        $lang = "nl";
        $view = $args['view'];
        $view->addHelperPath(GEOLOCATION_PLUGIN_DIR . '/helpers', 'Geolocation_View_Helper_');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();        
        if ( ($module == 'geolocation' && $controller == 'map')
                    || ($module == 'contribution' && $controller == 'contribution' && $action == 'contribute' && get_option('geolocation_add_map_to_contribution_form') == '1')
                    || ($module == 'annotation' && $controller == 'annotation') //hooray!
                    || ($controller == 'items') )  {
            queue_css_file('geolocation-items-map');
            queue_css_file('geolocation-marker');
            queue_js_file('map');
        }
    }
        
    public function hookInstall()
    {
        $db = get_db();
        $sql = "
        CREATE TABLE IF NOT EXISTS $db->Location (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `item_id` BIGINT UNSIGNED NOT NULL ,
        `latitude` DOUBLE NOT NULL ,
        `longitude` DOUBLE NOT NULL ,
        `zoom_level` INT NOT NULL ,
        `map_type` VARCHAR( 255 ) NOT NULL ,
        
        `point_of_interest` VARCHAR( 255 ) NULL ,
        `route` VARCHAR( 255 ) NULL ,
        `street_number` VARCHAR( 255 ) NULL ,
        `sublocality` VARCHAR( 255 ) NULL ,
        `locality` VARCHAR( 255 ) NULL ,
        `administrative_area_level_1` VARCHAR( 255 ) NULL ,
        `administrative_area_level_2` VARCHAR( 255 ) NULL ,
        `administrative_area_level_3` VARCHAR( 255 ) NULL ,
        `natural_feature` VARCHAR( 255 ) NULL ,
        `establishment` VARCHAR( 255 ) NULL ,
        `postal_code` VARCHAR( 255 ) NULL ,
        `postal_code_prefix` VARCHAR( 255 ) NULL ,
        `country` VARCHAR( 255 ) NOT NULL ,
        `continent` VARCHAR( 255 ) NULL ,
        `planetary_body` VARCHAR( 255 ) NULL ,

        `address` TEXT NOT NULL ,
        INDEX (`item_id`)) ENGINE = MYISAM";
        $db->query($sql);
        
        set_option('geolocation_default_latitude', '38');
        set_option('geolocation_default_longitude', '-77');
        set_option('geolocation_default_zoom_level', '5');
        set_option('geolocation_per_page', GEOLOCATION_DEFAULT_LOCATIONS_PER_PAGE);
        set_option('geolocation_add_map_to_contribution_form', '1');   
        set_option('geolocation_use_metric_distances', '1'); 
        
        set_option('geolocation_gmaps_key', "AIzaSyD6zj4P4YxltcYJZsRVUvTqG_bT1nny30o");
        
        set_option('geolocation_public_search_fields', implode(",",$this->_all_geo_fields));
    }
    
    public function hookUninstall()
    {
        // Delete the plugin options
        delete_option('geolocation_default_latitude');
        delete_option('geolocation_default_longitude');
        delete_option('geolocation_default_zoom_level');
        delete_option('geolocation_per_page');
        delete_option('geolocation_add_map_to_contribution_form');
        delete_option('geolocation_use_metric_distances');
        // This is for older versions of Geolocation, which used to store a Google Map API key.
        delete_option('geolocation_gmaps_key');
        delete_option('geolocation_public_search_fields');
        
        // Drop the Location table
        $db = get_db();
        $db->query("DROP TABLE $db->Location");        
    }
    
    public function hookConfigForm()
    {
        #update the available search fields
        if (get_option("geolocation_public_search_fields") == ""){set_option("geolocation_public_search_fields", implode("\n", $this->_all_geo_fields));}
        include 'config_form.php';        
    }
    
    public function hookConfig($args)
    {
        // Use the form to set a bunch of default options in the db
        set_option('geolocation_default_latitude', $_POST['default_latitude']);
        set_option('geolocation_default_longitude', $_POST['default_longitude']);
        set_option('geolocation_default_zoom_level', $_POST['default_zoomlevel']);
        set_option('geolocation_item_map_width', $_POST['item_map_width']);
        set_option('geolocation_item_map_height', $_POST['item_map_height']);
        set_option('geolocation_gmaps_key', $_POST['geolocation_gmaps_key']);
        $perPage = (int)$_POST['per_page'];
        if ($perPage <= 0) {
            $perPage = GEOLOCATION_DEFAULT_LOCATIONS_PER_PAGE;
        } else if ($perPage > GEOLOCATION_MAX_LOCATIONS_PER_PAGE) {
            $perPage = GEOLOCATION_MAX_LOCATIONS_PER_PAGE;
        }
        set_option('geolocation_per_page', $perPage);
        set_option('geolocation_add_map_to_contribution_form', $_POST['geolocation_add_map_to_contribution_form']);
        set_option('geolocation_link_to_nav', $_POST['geolocation_link_to_nav']);        
        set_option('geolocation_public_search_fields', $_POST['geolocation_public_search_fields']);
        set_option('geolocation_use_metric_distances', $_POST['geolocation_use_metric_distances']); 
    }
    
    public function hookDefineAcl($args)
    {   
        $acl = $args['acl'];
        $acl->allow(null, 'Items', 'modifyPerPage');
        $acl->addResource('Locations');
        $acl->allow(null, 'Locations');
    }
    
    public function hookDefineRoutes($args)
    {
        $router = $args['router'];
        $mapRoute = new Zend_Controller_Router_Route('items/map/:page',
                        array('controller' => 'map',
                                'action'     => 'browse',
                                'module'     => 'geolocation',
                                'page'       => '1'),
                        array('page' => '\d+'));
        $router->addRoute('items_map', $mapRoute);
        
        // Trying to make the route look like a KML file so google will eat it.
        // @todo Include page parameter if this works.
        $kmlRoute = new Zend_Controller_Router_Route_Regex('geolocation/map\.kml',
                        array('controller' => 'map',
                                'action' => 'browse',
                                'module' => 'geolocation',
                                'output' => 'kml'));
        $router->addRoute('map_kml', $kmlRoute);        
    }
    
    public function hookAfterSaveItem($args)
    {
        if (!($post = $args['post'])) {
            return;
        }

        $item = $args['record'];
        if (!$item) {
            $item = $args['item'];
        }
        
        // If we don't have the geolocation form on the page, don't do anything!
        if (!$post['geolocation']) {
            return;
        }

        // Find the location objects for the item
        $locations = $this->_db->getTable('Location')->findLocationByItem($item, false);

        // NARRATION LOCATIONS
        $narrationGeolocationPost = $post['geolocation']['narration_location'];
        if (!empty($narrationGeolocationPost) &&
                (((string)$narrationGeolocationPost['latitude']) != '') &&
                (((string)$narrationGeolocationPost['longitude']) != '')) {
            if (!array_key_exists('narration_location', $locations)) {
                $locations['narration_location'] = new Location;
                $locations['narration_location']->item_id = $item->id;
            }
            $locations['narration_location']->setPostData($narrationGeolocationPost);
            $locations['narration_location']->save();
            // If the form is empty, then we want to delete whatever location is
            // currently stored
        } else {
            if (array_key_exists('narration_location', $locations)) {
                $locations['narration_location']->delete();
            }
        }

        // ACTION LOCATIONS (we can make this smaller)
        $actionGeolocationPost = $post['geolocation']['action_location'];
        if (!empty($actionGeolocationPost) &&
                (((string)$actionGeolocationPost['latitude']) != '') &&
                (((string)$actionGeolocationPost['longitude']) != '')) {
            if (!array_key_exists('action_location', $locations)) {
                $locations['action_location'] = new Location;
                $locations['action_location']->item_id = $item->id;
//                $actionLocation->item_id = $item->id;
            }
            $locations['action_location']->setPostData($actionGeolocationPost);
            $locations['action_location']->save();
            // If the form is empty, then we want to delete whatever location is
            // currently stored
        } else {
            if (array_key_exists('action_location', $locations)) {
                $locations['action_location']->delete();
            }
        }

    }

    public function _itemsShow($args){
        $view = $args['view'];
        $item = $args['item'];
        $location = $this->_db->getTable('Location')->findLocationByItem($item, false); //multiple items

        if ($location) {
            $html = "";
            $width = get_option('geolocation_item_map_width') ? get_option('geolocation_item_map_width') : '100%';
            $height = get_option('geolocation_item_map_height') ? get_option('geolocation_item_map_height') : '300px';            
            $html = '<div id="geolocation" class="element">';
            $html .= '  <h2 style="margin:0px">' . __("Locaties") . '</h2>';
            if (array_key_exists("narration_location", $location) && array_key_exists("action_location", $location)){ //assuming there are 2 for now!
                $narration_location = $location["narration_location"]->get_locationdata_for_public_viewing();
                $action_location = $location["action_location"]->get_locationdata_for_public_viewing();
                $html .= $view->itemGoogleMap($item, $width, $height);
                $html .= "  <div class=\"element-text\"><br>";
                $html .= "<b>" . __("Place of narration") . "</b><br>";
                foreach($narration_location as $loc_type => $loc_data){
                    if ($loc_data){
                        $uri = html_escape(url('/solr-search?q=&facet=' . $loc_type .':"' . $loc_data . '"'));
                        $html .= '<a href="' . $uri . '" style="padding:0px">' . html_escape($loc_data) . "</a>, ";
                    }
                }
                $html .= "<br><br><b>" . __("Place of Action") . "</b><br>";
                foreach($action_location as $loc_type => $loc_data){
                    if ($loc_data){
                        $uri = html_escape(url('/solr-search?q=&facet=action_' . $loc_type .':"' . $loc_data . '"'));
                        $html .= '<a href="' . $uri . '" style="padding:0px">' . html_escape($loc_data) . "</a>, ";
                    }
                }   
                $html .= "  </div>";
            }
            elseif (array_key_exists("narration_location", $location)){
                $narration_location = $location["narration_location"]->get_locationdata_for_public_viewing();
                $html .= $view->itemGoogleMap($item, $width, $height);
                $html .= "  <div class=\"element-text\"><br>";
                $html .= "<b>" . __("Place of narration") . "</b><br>";
                foreach($narration_location as $loc_type => $loc_data){
                    if ($loc_data){
                        $uri = html_escape(url('/solr-search?q=&facet=' . $loc_type .':"' . $loc_data . '"'));
                        $html .= '<a href="' . $uri . '" style="padding:0px">' . html_escape($loc_data) . "</a>, ";
                    }
                }
                $html .= "  </div>";
            }
            elseif (array_key_exists("action_location", $location)){
                $action_location = $location["action_location"]->get_locationdata_for_public_viewing();
                $html .= $view->itemGoogleMap($item, $width, $height);
                $html .= "  <div class=\"element-text\"><br>";
                $html .= "<b>" . __("Place of Action") . "</b><br>";
                foreach($action_location as $loc_type => $loc_data){
                    if ($loc_data){
                        $uri = html_escape(url('/solr-search?q=&facet=action_' . $loc_type .':"' . $loc_data . '"'));
                        $html .= '<a href="' . $uri . '" style="padding:0px">' . html_escape($loc_data) . "</a>, ";
                    }
                }
                $html .= "  </div>";
            }
            $html .= "</div>";
            echo $html;
        }
    }

    public function hookAdminItemsShowSidebar($args)
    {
        $this->_itemsShow($args);
    }
    
    public function hookPublicItemsShowSidebarUltimateTop($args)
    {
        $this->_itemsShow($args);
    }
    
    public function hookPublicHead($args){
#        $key = get_option('geolocation_gmaps_key');// ? get_option('geolocation_gmaps_key') : 'AIzaSyD6zj4P4YxltcYJZsRVUvTqG_bT1nny30o';
#        $lang = "nl";
        $view = $args['view'];
        $view->addHelperPath(GEOLOCATION_PLUGIN_DIR . '/helpers', 'Geolocation_View_Helper_');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if ( ($module == 'geolocation' && $controller == 'map')
                        || ($module == 'contribution' 
                            && $controller == 'contribution' 
                            && $action == 'contribute' 
                            && get_option('geolocation_add_map_to_contribution_form') == '1')
                         || ($controller == 'items') )  {
            queue_css_file('geolocation-items-map');
            queue_css_file('geolocation-marker');
#            queue_js_url("http://maps.google.com/maps/api/js?sensor=false");
#            queue_js_url("https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&key=$key&language=$lang");
            queue_js_file('map');
        }        
    }

    public function hookAdminItemsSearch($args)
    {
        // Get the request object
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $view = $args['view'];
        if ($request->getControllerName() == 'map' && $request->getActionName() == 'browse') {
            echo $view->partial('advanced-search-partial.php', array('searchFormId'=>'search', 'searchButtonId'=>'submit_search_advanced'));
        } else if ($request->getControllerName() == 'items' && $request->getActionName() == 'advanced-search') {
            echo $view->partial('advanced-search-partial.php', array('searchFormId'=>'advanced-search-form', 'searchButtonId'=>'submit_search_advanced'));
        }
    }
    
    public function hookPublicItemsSearch($args)
    {
        $view = $args['view'];                
        echo $view->partial('advanced-search-partial.php', array('searchFormId'=>'advanced-search-form', 'searchButtonId'=>'submit_search_advanced'));
    }
    
    public function hookItemsBrowseSql($args)
    {
        $db = $this->_db;
        $select = $args['select'];
        $alias = $this->_db->getTable('Location')->getTableAlias();
        
        $specific_geo_search = False;
        // go through all searchable fields
        foreach(explode("\n", get_option("geolocation_public_search_fields")) as $geo_field){
            $geo_field = trim($geo_field);
            if(isset($args['params']['geolocation-'.$geo_field])) {
                $search_value = trim($args['params']['geolocation-'.$geo_field]);
                if ( $search_value != ''){
                    //fire only once
                    if ($specific_geo_search == False){
                        $select->joinInner(array($alias => $db->Location), "$alias.item_id = items.id", array('latitude', 'longitude', 'address')); 
                    }
                        $field_type = trim($args['params']["geolocation-".$geo_field]);
                        $select->where($geo_field . ' LIKE "%' . $field_type . '%"');
                        $specific_geo_search = True;
                }
            }
        }
        
        //fires when a specific place is searched for
        if($specific_geo_search){ 
            $select->order('id');
        }
        //fire when map browsed and gelocation-address are not empty
        if (!empty($args['params']['only_map_items']) || !empty($args['params']['geolocation-address'])) {
            $select->joinInner(
                array($alias => $db->Location),
                "$alias.item_id = items.id",
                array()
            );
        }
        if (!empty($args['params']['geolocation-address'])) {
            // Get the address, latitude, longitude, and the radius from parameters
            $address = trim($args['params']['geolocation-address']);
            $lat = trim($args['params']['geolocation-latitude']);
            $lng = trim($args['params']['geolocation-longitude']);
            $radius = trim($args['params']['geolocation-radius']);
            // Limit items to those that exist within a geographic radius if an address and radius are provided
            if ($address != ''
                && is_numeric($lat)
                && is_numeric($lng)
                && is_numeric($radius)
            ) {
                // SELECT distance based upon haversine forumula
                if (get_option('geolocation_use_metric_distances')) {
                    $denominator = 111;
                    $earthRadius = 6371;
                } else {
                    $denominator = 69;
                    $earthRadius = 3959;
                }

                $radius = $db->quote($radius, Zend_Db::FLOAT_TYPE);
                $lat = $db->quote($lat, Zend_Db::FLOAT_TYPE);
                $lng = $db->quote($lng, Zend_Db::FLOAT_TYPE);

                $distanceFormula = new Zend_Db_Expr("($earthRadius*ACOS(COS(RADIANS($lat))*COS(RADIANS(locations.latitude))*COS(RADIANS($lng)-RADIANS(locations.longitude))+SIN(RADIANS($lat))*SIN(RADIANS(locations.latitude))))");

                $select->columns(array('distance' => $distanceFormula));

                // WHERE the distance is within radius miles/kilometers of the specified lat & long
                $select->where("(locations.latitude BETWEEN $lat - $radius / $denominator AND $lat + $radius / $denominator) AND (locations.longitude BETWEEN $lng - $radius / $denominator AND $lng + $radius / $denominator)");

                // Actually use distance calculation.
                //$select->having('distance < radius');

                //ORDER by the closest distances
                $select->order('distance');
            }
        }
    }
    
    public function filterAdminNavigationMain($navArray)
    {
        $navArray['Geolocation'] = array('label'=>'Vertelplaatsen', 'uri'=>url('geolocation/map/browse'));
        return $navArray;        
    }
    
    public function filterResponseContexts($contexts)
    {
        $contexts['kml'] = array('suffix'  => 'kml',
                'headers' => array('Content-Type' => 'text/xml'));
        return $contexts;        
    }
    
    public function filterActionContexts($contexts, $args)
    {
        $controller = $args['controller'];
        if ($controller instanceof Geolocation_MapController) {
            $contexts['browse'] = array('kml');
        }
        return $contexts;        
    }
    
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        // insert the map tab before the Miscellaneous tab
        $item = $args['item'];
        
        $tabs[__('Place of narration')] = $this->_mapForm($item, "narration_location");
        $tabs[__('Place of action')] = $this->_mapForm($item, "action_location");
        return $tabs;     
    }
    
    /**
     * Return HTML for a link to the same search visualized on a map
     *
     * @return string
     */
    function link_to_map_search()
    {
        $uri = 'geolocation/map/browse';
        $props = $uri . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        return $props;
    }

    /**
     * Return HTML for a link to the same search visualized on a map
     *
     * @return string
     */
    function link_to_list_search()
    {
        $uri = 'items/browse';
        $props = $uri . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        return $props;
    }

    
    public function filterPublicNavigationItems($navArray){
#        if (get_option('geolocation_see_results_on_map')) {
#        print "<pre>MAP SEARCH: " . $this->link_to_map_search()."</pre>";
        $navArray['Search results'] = array(
                                        'label'=>__('Resultaten lijst'),
                                        'uri' => url($this->link_to_list_search())
                                        );

        $navArray['Results on map'] = array(
                                        'label'=>__('Results on map'),
                                        'uri' => url($this->link_to_map_search())
                                        );
#        }
        if (get_option('geolocation_link_to_nav')) {
            $navArray['Browse Map'] = array(
                                            'label'=>__('Browse Map'),
                                            'uri' => url('items/map') 
                                            );
        }
        return $navArray;        
    }
    
    protected function _autocomplete_js_code(){
        return "
        <script type=\"text/javascript\">
            if (google){
                var options = {
            	    types: []
                };
            	var input = document.getElementById('geolocation-address');
            	var autocomplete = new google.maps.places.Autocomplete(input, options);

                jQuery(document).ready(function() {
            	    jQuery('#<?php echo $searchButtonId; ?>').click(function(event) {

            	        // Find the geolocation for the address
            	        var address = jQuery('#geolocation-address').val();
                        if (jQuery.trim(address).length > 0) {
                            var geocoder = new google.maps.Geocoder();	        
                            geocoder.geocode({'address': address}, function(results, status) {
                                // If the point was found, then put the marker on that spot
                        		if (status == google.maps.GeocoderStatus.OK) {
                        			var gLatLng = results[0].geometry.location;
                        	        // Set the latitude and longitude hidden inputs
                        	        jQuery('#geolocation-latitude').val(gLatLng.lat());
                        	        jQuery('#geolocation-longitude').val(gLatLng.lng());
                                    jQuery('#<?php echo $searchFormId; ?>').submit();
                        		} else {
                        		  	// If no point was found, give us an alert
                        		    alert('Error: \"' + address + '\" was not found!');
                        		}
                            });

                            event.stopImmediatePropagation();
                	        return false;
                        }                
            	    });
                });
            };
        </script>";
    }

    public function hookAnnotationTypeForm($args)
    {
        
        $annotationType = $args['type'];
        $item = $args['item'];
        echo $this->_mapForm($item,
                            "action_location",
                            __('Vind de ACTIELOCATIE voor ') . $annotationType->display_name . ':', 
                            true, 
                            null, 
                            true);
        echo "<hr>";
        echo $this->_mapForm($item,
                            "narration_location",
                            __('Vind de VERTELLOCATIE voor ') . $annotationType->display_name . ':', 
                            true, 
                            null, 
                            false);
    }

    public function hookAnnotationSaveForm($args)
    {
        _log($args['item']->id);
        $this->hookAfterSaveItem($args);
    }
    
    public function hookContributionTypeForm($args)
    {
       $contributionType = $args['type'];
       echo $this->_mapForm(null, 
                            "narration_location",
                            __('Vind de VERTELLOCATIE voor ') . $contributionType->display_name . ':', 
                            false, 
                            null, 
                            true);
    }

    public function hookContributionSaveForm($args)
    {
        _log($args['item']->id);
        $this->hookAfterSaveItem($args);
    }
    
    
    /**
     * Returns the form code for geographically searching for items
     * @param Item $item
     * @param int $width
     * @param int $height
     * @return string
     **/    
    protected function _mapForm($item, $location_type, $label = 'Find a Location by Address:', $confirmLocationChange = true,  $post = null, $input_fields_hide = false)
    {
        $input_type = ($input_fields_hide ? ' style="display:none;"' : '');
        $html = '';
        
        $center = $this->_getCenter();
        $center['show'] = false;
        
        //with multiple locations possibl
        $locations = $this->_db->getTable('Location')->findLocationByItem($item, false);
        
        if ($post === null) {
            $post = $_POST;
        }
        
        $usePost = !empty($post) && !empty($post['geolocation']);
        if ($usePost) {
            $lng  = (double) @$post['geolocation'][$location_type]['longitude'];
            $lat  = (double) @$post['geolocation'][$location_type]['latitude'];
            $zoom = (int) @$post['geolocation'][$location_type]['zoom_level'];
            $addr = @$post['geolocation'][$location_type]['address'];
	        $planetary_body = @$post['geolocation'][$location_type]['planetary_body'];
	        $continent = @$post['geolocation'][$location_type]['continent'];
	        $country = @$post['geolocation'][$location_type]['country'];
	        $administrative_area_level_1 = @$post['geolocation'][$location_type]['administrative_area_level_1'];
	        $administrative_area_level_2 = @$post['geolocation'][$location_type]['administrative_area_level_2'];
	        $locality = @$post['geolocation'][$location_type]['locality'];
	        $natural_feature = @$post['geolocation'][$location_type]['natural_feature'];
	        $sublocality = @$post['geolocation'][$location_type]['sublocality'];
	        $route = @$post['geolocation'][$location_type]['route'];
	        $point_of_interest = @$post['geolocation'][$location_type]['point_of_interest'];
	        $establishment = @$post['geolocation'][$location_type]['establishment'];
	        $street_number = @$post['geolocation'][$location_type]['street_number'];
	        $postal_code = @$post['geolocation'][$location_type]['postal_code'];
	        $postal_code_prefix = @$post['geolocation'][$location_type]['postal_code_prefix'];
        } else {
            $lng = $lat = $zoom = $addr = $planetary_body = $natural_feature = $continent = $country = $administrative_area_level_1 = $administrative_area_level_2 = $locality = $sublocality = $route = $point_of_interest = $establishment = $street_number = $postal_code = $postal_code_prefix = '';
            if ($locations) { //check if there is a location
                if (array_key_exists($location_type, $locations)) {
                    $location = $locations[$location_type];
                    $lng  = (double) $location['longitude'];
                    $lat  = (double) $location['latitude'];
                    $zoom = (int) $location['zoom_level'];
                    $addr = $location['address'];
    		        $planetary_body = $location['planetary_body'];
    		        $continent = $location['continent'];
    		        $country = $location['country'];
    		        $administrative_area_level_1 = $location['administrative_area_level_1'];
    		        $administrative_area_level_2 = $location['administrative_area_level_2'];
    		        $locality = $location['locality'];
    		        $natural_feature = $location['natural_feature'];
    		        $sublocality = $location['sublocality'];
    		        $route = $location['route'];
    		        $point_of_interest = $location['point_of_interest'];
    		        $establishment = $location['establishment'];
    		        $street_number = $location['street_number'];
    		        $postal_code = $location['postal_code'];
    		        $postal_code_prefix = $location['postal_code_prefix'];
                }
            }
        }
        
        $mapFormId = js_escape('omeka-map-form-' . $location_type);
        $name_root = "geolocation[$location_type]";
        $id_root = $location_type . "_";
        $class = $location_type;
        $coordinateClass = $location_type . "_coords";
        
        $html .= '<div class="field" >';
        $html .=     '<div id="location_form" class="five columns alpha">';
        $html .=         '<label>' . html_escape($label) . '</label>';
        $html .=     '</div>';
        $html .=     '<div class="five columns omega">';
        $html .=            '<input type="text" name="' . $name_root . '[address]" id="'. $id_root .'geolocation_address" value="' . $addr . '" class="textinput" onKeypress="resetAllAreas(\'' . $location_type . '\');"/>';
        $html .=            '<button type="button" style="margin-bottom: 18px; float:none;" name="geolocation_find_location_by_address" id="'. $id_root .'geolocation_find_location_by_address">' . __("Find") . '</button>';
        $html .=            '<button type="button" style="margin-bottom: 18px; float:none;" name="geolocation_empty" id="'. $id_root .'geolocation_empty" class="red button">' . __("Reset") . '</button>';
        $html .=     '</div>';
        $html .= '</div>';
        
        $html .= '<div id=' . $mapFormId . ' style="width: 100%; height: 300px"></div>';
        
        #site for auto filled geo location information:
        $html .=      '<div class="input-block" ' . $input_type . '>';
        $html .=         '<label>' . html_escape("Latitude") . '</label>';
        $html .=         '<input name="' . $name_root . '[latitude]" value="' . $lat . '" class="' . $coordinateClass . '"/>';
        $html .=         '<label>' . html_escape("Longitude") . '</label>';
        $html .=         '<input name="' . $name_root . '[longitude]" value="' . $lng . '" class="' . $coordinateClass . '"/>';
        $html .=         '<label>' . html_escape("Zoom") . '</label>';
        $html .=         '<input name="' . $name_root . '[zoom_level]" value="' . $zoom . '" class="' . $coordinateClass . '"  size="2"/>';
        $html .=         '<input name="' . $name_root . '[map_type]" value="Google Maps v' . GOOGLE_MAPS_API_VERSION . '" type="hidden"/><br>';
        $html .=         '<input name="' . $name_root . '[location_type]" value="' . $location_type . '" type="hidden"/><br>';
        $html .=     '</div>';

        $html .= '<div class="field" ' . $input_type . '>';
        $html .=     '<table id="location_form" class="geo" style="float:left;">';
        $html .=         '<tr><td>Address</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[route]" id="'. $id_root .'route" rows="1" size="20" value="'.$route.'" class="' . $class . '"/></td></tr>';
        $html .=         '<tr><td>Streetnumber</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[street_number]" id="'. $id_root .'street_number" rows="1" size="4" value="'.$street_number.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Postal code</td>';
    	$html .=         '<td><input type="text" name="' . $name_root . '[postal_code]" id="'. $id_root .'postal_code" rows="1" size="12" value="'.$postal_code.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Postal code prefix</td>';
    	$html .=         '<td><input type="text" name="' . $name_root . '[postal_code_prefix]" id="'. $id_root .'postal_code_prefix" rows="1" size="12" value="'.$postal_code_prefix.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Sublocality</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[sublocality]" id="'. $id_root .'sublocality" rows="1" size="28" value="'.$sublocality.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Place</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[locality]" id="'. $id_root .'locality" rows="1" size="28" value="'.$locality.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Natural feature</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[natural_feature]" id="'. $id_root .'natural_feature" rows="1" size="28" value="'.$natural_feature.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Establishment</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[establishment]" id="'. $id_root .'establishment" rows="1" size="28" value="'.$establishment.'" class="' . $class . '"/></td></tr>';	
    	$html .=         '<tr><td>County (adm2)</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[administrative_area_level_2]" id="'. $id_root .'administrative_area_level_2" rows="1" size="28" value="'.$administrative_area_level_2.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Province (adm1)</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[administrative_area_level_1]" id="'. $id_root .'administrative_area_level_1" rows="1" size="28" value="'.$administrative_area_level_1.'" class="' . $class . '"/></td></tr>';
    	$html .=         '<tr><td>Country</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[country]" id="'. $id_root .'country" rows="1" size="28" value="'.$country.'" class="' . $class . '"/></td><tr>';
    	$html .=         '<tr><td>Planetary body</td>';
        $html .=         '<td><input type="text" name="' . $name_root . '[planetary_body]" id="'. $id_root .'planetary_body" size="28" value="'.$planetary_body.'" class="' . $class . '"/></td></tr>';
        $html .=    '</table>';
        $html .= '</div>';
        
        $options = array();
        $options['form'] = array('id' => $location_type,
                                'posted' => $usePost);
        if ($locations or $usePost) {
            $options['point'] = array('latitude' => $lat,
                                        'longitude' => $lng,
                                        'zoomLevel' => $zoom);
        }
        
        $options['confirmLocationChange'] = $confirmLocationChange;
        $center = js_escape($center);
        $options = js_escape($options);
        $location_type = js_escape($location_type);
        
        $js = "
            var anOmekaMapForm = new OmekaMapForm($mapFormId , $center, $options, $location_type);
            jQuery(document).bind('omeka:tabselected', function () {
                anOmekaMapForm.resize();
            });                        
        ";
        
        $html .= "\n<script type='text/javascript'>" . $js . "</script>";
        return $html;
    }
    
    protected function _getCenter()
    {
        return array(
                'latitude'=>  (double) get_option('geolocation_default_latitude'),
                'longitude'=> (double) get_option('geolocation_default_longitude'),
                'zoomLevel'=> (double) get_option('geolocation_default_zoom_level'));        
    }
    
    /**
     * Register the geolocations API resource.
     * 
     * @param array $apiResources
     * @return array
     */
    public function filterApiResources($apiResources)
    {
        $apiResources['geolocations'] = array(
            'record_type' => 'Location',
            'actions' => array('get', 'index', 'post', 'put', 'delete'), 
        );
        return $apiResources;
    }
    
    /**
     * Add geolocations to item API representations.
     *
     * @param array $extend
     * @param array $args
     * @return array
     */
    public function filterApiExtendItems($extend, $args)
    {
        $item = $args['record'];
        $location = $this->_db->getTable('Location')->findBy(array('item_id' => $item->id));
        if (!$location) {
            return $extend;
        }
        $locationId = $location[0]['id'];
        $extend['geolocations'] = array(
            'id' => $locationId,
            'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl("/geolocations/{$locationId}"),
            'resource' => 'geolocations',
        );
        return $extend;
    }
}