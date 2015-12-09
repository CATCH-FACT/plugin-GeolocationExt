<?php

class Geolocation_View_Helper_ItemGoogleMap extends Zend_View_Helper_Abstract
{    

    public function itemGoogleMap($item = null, $width = '200px', $height = '200px', $hasBalloonForMarker = true, $markerHtmlClassName = 'geolocation_balloon')
    {
        $html = "<style type='text/css'>";
        $html .= "div.map-notification {
            width: $width;
            height: $height; 
        }";
        
        $divId = "item-map-{$item->id}";
        $html .= "
            #$divId {
                width: $width;
                height: $height;
            }";
        
        $html .= "</style>";
        
        $divId = "item-map-{$item->id}";
        $location = get_db()->getTable('Location')->findLocationByItem($item, false);
        // Only set the center of the map if this item actually has a location
        // associated with it
        if ($location) {
            if (array_key_exists("narration_location", $location) && array_key_exists("action_location", $location)){ //assuming there are 2 for now!

                $lats = array($location["narration_location"]->latitude, $location["action_location"]->latitude);
                $longs = array($location["narration_location"]->longitude, $location["action_location"]->longitude);
                
                $markers = array(   array($location["narration_location"]->latitude, $location["narration_location"]->longitude, "narration_location"), 
                                    array($location["action_location"]->latitude, $location["action_location"]->longitude, "action_location"));
                
                $center['latitude']     = min($lats) + ((max($lats) - min($lats)) / 2);
                $center['longitude']    = min($longs) + ((max($longs) - min($longs)) / 2);
                $center['zoomLevel']    = 6; //$location->zoom_level;
                $center['show']         = true;

                $options = array();

                $center = js_escape($center);
                $options = js_escape($options);
                $markers = js_escape($markers);
                
                $html .= '<div id="' . $divId . '" class="map panel"></div>';

                $js = "var " . Inflector::variablize($divId) . ";";
//                $js .= "OmekaMapSingle = new OmekaMapSingle(" . js_escape($divId) . ", $center, $options); ";

                $js .= "OmekaMapMultiple = new OmekaMapMultiple(" . js_escape($divId) . ", $center, $markers, $options); ";

                $html .= "<script type='text/javascript'>$js</script>";
                
            }
            elseif (array_key_exists("narration_location", $location)){
                $center['latitude']     = $location["narration_location"]->latitude;
                $center['longitude']    = $location["narration_location"]->longitude;
                $center['zoomLevel']    = $location["narration_location"]->zoom_level;
                $center['show']         = true;
                if ($hasBalloonForMarker) {
                    $titleLink = link_to_item(metadata($item, array('Dublin Core', 'Title'), array(), $item), array(), 'show', $item);
                    $thumbnailLink = !(item_image('thumbnail')) ? '' : link_to_item(item_image('thumbnail', array(), 0, $item), array(), 'show', $item);
                    $description = metadata($item, array('Dublin Core', 'Description'), array('snippet'=>150), $item);
                    //$center['markerHtml']   = geolocation_get_marker_html_for_item($item, $markerHtmlClassName);
                    $center['markerHtml'] = '<div class="' . $markerHtmlClassName . '"><p class="geolocation_marker_title">' . $titleLink . '</p>' . $thumbnailLink . '<p>' . $description . '</p></div>';
                }
                $options = array();
                $center = js_escape($center);
                $options = js_escape($options);
                $html .= '<div id="' . $divId . '" class="map panel"></div>';
            
                $js = "var " . Inflector::variablize($divId) . ";";
                $js .= "OmekaMapSingle = new OmekaMapSingle(" . js_escape($divId) . ", $center, $options); ";
                $html .= "<script type='text/javascript'>$js</script>";
            }
            elseif (array_key_exists("action_location", $location)){
                
                $lats = array($location["action_location"]->latitude);
                $longs = array($location["action_location"]->longitude);
                
                $markers = array(array($location["action_location"]->latitude, $location["action_location"]->longitude, "action_location"));
                                    
                $center['latitude']     = $location["action_location"]->latitude;
                $center['longitude']    = $location["action_location"]->longitude;
                $center['zoomLevel']    = $location["action_location"]->zoom_level;
                $center['show']         = true;
                if ($hasBalloonForMarker) {
                    $titleLink = link_to_item(metadata($item, array('Dublin Core', 'Title'), array(), $item), array(), 'show', $item);
                    $thumbnailLink = !(item_image('thumbnail')) ? '' : link_to_item(item_image('thumbnail', array(), 0, $item), array(), 'show', $item);
                    $description = metadata($item, array('Dublin Core', 'Description'), array('snippet'=>150), $item);
                    //$center['markerHtml']   = geolocation_get_marker_html_for_item($item, $markerHtmlClassName);
                    $center['markerHtml'] = '<div class="' . $markerHtmlClassName . '"><p class="geolocation_marker_title">' . $titleLink . '</p>' . $thumbnailLink . '<p>' . $description . '</p></div>';
                }
                $options = array();
                $center = js_escape($center);
                $options = js_escape($options);
                $markers = js_escape($markers);
                $html .= '<div id="' . $divId . '" class="map panel"></div>';
            
                $js = "var " . Inflector::variablize($divId) . ";";
//                $js .= "OmekaMapSingle = new OmekaMapSingle(" . js_escape($divId) . ", $center, $options); ";
                $js .= "OmekaMapMultiple = new OmekaMapMultiple(" . js_escape($divId) . ", $center, $markers, $options); ";
                $html .= "<script type='text/javascript'>$js</script>";
            }
        } else {
            $html .= '<p class="map-notification">This item has no location info associated with it.</p>';
        }
         return $html;   
    }
    
    public function itemGoogleMapOLD($item = null, $width = '200px', $height = '200px', $hasBalloonForMarker = true, $markerHtmlClassName = 'geolocation_balloon')
    {
        $html = "<style type='text/css'>";
        $html .= "div.map-notification {
            width: $width;
            height: $height; 
        }";
        
        $divId = "item-map-{$item->id}";
        $html .= "
            #$divId {
                width: $width;
                height: $height;
            }";
        
        $html .= "</style>";
        
        $divId = "item-map-{$item->id}";
        $location = get_db()->getTable('Location')->findLocationByItem($item, true);
        // Only set the center of the map if this item actually has a location
        // associated with it
        if ($location) {
            $center['latitude']     = $location->latitude;
            $center['longitude']    = $location->longitude;
            $center['zoomLevel']    = $location->zoom_level;
            $center['show']         = true;
            if ($hasBalloonForMarker) {
                $titleLink = link_to_item(metadata($item, array('Dublin Core', 'Title'), array(), $item), array(), 'show', $item);
                $thumbnailLink = !(item_image('thumbnail')) ? '' : link_to_item(item_image('thumbnail', array(), 0, $item), array(), 'show', $item);
                $description = metadata($item, array('Dublin Core', 'Description'), array('snippet'=>150), $item);
                //$center['markerHtml']   = geolocation_get_marker_html_for_item($item, $markerHtmlClassName);
                $center['markerHtml'] = '<div class="' . $markerHtmlClassName . '"><p class="geolocation_marker_title">' . $titleLink . '</p>' . $thumbnailLink . '<p>' . $description . '</p></div>';
            }
            $options = array();
            $center = js_escape($center);
            $options = js_escape($options);
            $html .= '<div id="' . $divId . '" class="map panel"></div>';
            
            $js = "var " . Inflector::variablize($divId) . ";";
            $js .= "OmekaMapSingle = new OmekaMapSingle(" . js_escape($divId) . ", $center, $options); ";
            $html .= "<script type='text/javascript'>$js</script>";
        } else {
            $html .= '<p class="map-notification">This item has no location info associated with it.</p>';
        }
         return $html;   
    }    
}