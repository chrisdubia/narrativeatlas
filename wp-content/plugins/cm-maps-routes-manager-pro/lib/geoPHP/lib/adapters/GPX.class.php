<?php
/*
 * Copyright (c) Patrick Hayes
 *
 * This code is open-source and licenced under the Modified BSD License.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP Geometry/GPX encoder/decoder
 */
class GPX extends GeoAdapter
{
  private $namespace = FALSE;
  private $nss = ''; // Name-space string. eg 'georss:'

  /**
   * Read GPX string into geometry objects
   *
   * @param string $gpx A GPX string
   *
   * @return Geometry|GeometryCollection
   */
  public function read($gpx) {
    return $this->geomFromText($gpx);
  }

  /**
   * Serialize geometries into a GPX string.
   *
   * @param Geometry $geometry
   *
   * @return string The GPX string representation of the input geometries
   */
  public function write(Geometry $geometry, $namespace = FALSE) {
    if ($geometry->isEmpty()) return NULL;
    if ($namespace) {
      $this->namespace = $namespace;
      $this->nss = $namespace.':';    
    }
    //return '<'.$this->nss.'gpx creator="geoPHP" version="1.0">'.$this->geometryToGPX($geometry).'</'.$this->nss.'gpx>';
	return '<?xml version="1.0" encoding="UTF-8"?><'.$this->nss.'gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/WaypointExtension/v1 http://www8.garmin.com/xmlschemas/WaypointExtensionv1.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www8.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/ActivityExtension/v1 http://www8.garmin.com/xmlschemas/ActivityExtensionv1.xsd http://www.garmin.com/xmlschemas/AdventuresExtensions/v1 http://www8.garmin.com/xmlschemas/AdventuresExtensionv1.xsd http://www.garmin.com/xmlschemas/PressureExtension/v1 http://www.garmin.com/xmlschemas/PressureExtensionv1.xsd http://www.garmin.com/xmlschemas/TripExtensions/v1 http://www.garmin.com/xmlschemas/TripExtensionsv1.xsd http://www.garmin.com/xmlschemas/TripMetaDataExtensions/v1 http://www.garmin.com/xmlschemas/TripMetaDataExtensionsv1.xsd http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensions/v1 http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensionsv1.xsd http://www.garmin.com/xmlschemas/CreationTimeExtension/v1 http://www.garmin.com/xmlschemas/CreationTimeExtensionsv1.xsd http://www.garmin.com/xmlschemas/AccelerationExtension/v1 http://www.garmin.com/xmlschemas/AccelerationExtensionv1.xsd http://www.garmin.com/xmlschemas/PowerExtension/v1 http://www.garmin.com/xmlschemas/PowerExtensionv1.xsd http://www.garmin.com/xmlschemas/VideoExtension/v1 http://www.garmin.com/xmlschemas/VideoExtensionv1.xsd" xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:wptx1="http://www.garmin.com/xmlschemas/WaypointExtension/v1" xmlns:gpxtrx="http://www.garmin.com/xmlschemas/GpxExtensions/v3" xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1" xmlns:gpxx="http://www.garmin.com/xmlschemas/GpxExtensions/v3" xmlns:trp="http://www.garmin.com/xmlschemas/TripExtensions/v1" xmlns:adv="http://www.garmin.com/xmlschemas/AdventuresExtensions/v1" xmlns:prs="http://www.garmin.com/xmlschemas/PressureExtension/v1" xmlns:tmd="http://www.garmin.com/xmlschemas/TripMetaDataExtensions/v1" xmlns:vptm="http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensions/v1" xmlns:ctx="http://www.garmin.com/xmlschemas/CreationTimeExtension/v1" xmlns:gpxacc="http://www.garmin.com/xmlschemas/AccelerationExtension/v1" xmlns:gpxpx="http://www.garmin.com/xmlschemas/PowerExtension/v1" xmlns:vidx1="http://www.garmin.com/xmlschemas/VideoExtension/v1" creator="CM Maps Routes Manager Pro" version="1.1">'.$this->geometryToGPX($geometry).'</'.$this->nss.'gpx>';
  }
  
  public function geomFromText($text) {
    // Change to lower-case and strip all CDATA
    $text = strtolower($text);
    $text = preg_replace('/<!\[cdata\[(.*?)\]\]>/s','',$text);
    
    // Load into DOMDocument
    $xmlobj = new DOMDocument();
    @$xmlobj->loadXML($text);
    if ($xmlobj === false) {
      throw new Exception("Invalid GPX: ". $text);
    }
    
    $this->xmlobj = $xmlobj;
    try {
      $geom = $this->geomFromXML();
    } catch(InvalidText $e) {
        throw new Exception("Cannot Read Geometry From GPX: ". $text);
    } catch(Exception $e) {
        throw $e;
    }

    return $geom;
  }
  
  protected function geomFromXML() {
    $geometries = array();
    $geometries = array_merge($geometries, $this->parseWaypoints());
    $geometries = array_merge($geometries, $this->parseTracks());
    $geometries = array_merge($geometries, $this->parseRoutes());
    
    if (empty($geometries)) {
      throw new Exception("Invalid / Empty GPX");
    }
    
    return geoPHP::geometryReduce($geometries); 
  }
  
  protected function childElements($xml, $nodename = '') {
    $children = array();
    foreach ($xml->childNodes as $child) {
      if ($child->nodeName == $nodename) {
        $children[] = $child;
      }
    }
    return $children;
  }
  
  protected function parseWaypoints() {
    $points = array();
    $wpt_elements = $this->xmlobj->getElementsByTagName('wpt');
    foreach ($wpt_elements as $wpt) {
      $lat = $wpt->attributes->getNamedItem("lat")->nodeValue;
      $lon = $wpt->attributes->getNamedItem("lon")->nodeValue;
      $points[] = new Point($lon, $lat);
    }
    return $points;
  }
  
  protected function parseTracks() {
    $lines = array();
    $trk_elements = $this->xmlobj->getElementsByTagName('trk');
    foreach ($trk_elements as $trk) {
      $components = array();
      foreach ($this->childElements($trk, 'trkseg') as $trkseg) {
        foreach ($this->childElements($trkseg, 'trkpt') as $trkpt) {
          $lat = $trkpt->attributes->getNamedItem("lat")->nodeValue;
          $lon = $trkpt->attributes->getNamedItem("lon")->nodeValue;
          
          // Added elevation support:
          $alt = NULL;
          $altNodes = $this->childElements($trkpt, 'ele');
          if (!empty($altNodes)) {
          	$altNodes = reset($altNodes);
          	$alt = floatval(trim($altNodes->nodeValue));
          }
          
          $components[] = new Point($lon, $lat, $alt);
        }
      }
      if ($components) {$lines[] = new LineString($components);}
    }
    return $lines;
  }
  
  protected function parseRoutes() {
    $lines = array();
    $rte_elements = $this->xmlobj->getElementsByTagName('rte');
    foreach ($rte_elements as $rte) {
      $components = array();
      foreach ($this->childElements($rte, 'rtept') as $rtept) {
        $lat = $rtept->attributes->getNamedItem("lat")->nodeValue;
        $lon = $rtept->attributes->getNamedItem("lon")->nodeValue;
        $components[] = new Point($lon, $lat);
      }
      $lines[] = new LineString($components);
    }
    return $lines;
  }
  
  protected function geometryToGPX($geom) {
    $type = strtolower($geom->getGeomType());
    switch ($type) {
      case 'point':
        return $this->pointToGPX($geom);
        break;
      case 'linestring':
        return $this->linestringToGPX($geom);
        break;
      case 'polygon':
      case 'multipoint':
      case 'multilinestring':
      case 'multipolygon':
      case 'geometrycollection':
        return $this->collectionToGPX($geom);
        break;
    }
  }
  
  private function pointToGPX($geom) {
	global $wpdb;
	$lat_post_id = $wpdb->get_row("SELECT post_id FROM $wpdb->postmeta WHERE (meta_key='_cmmrm_latitude' AND meta_value='".$geom->getY() ."')");
	$lon_post_id = $wpdb->get_row("SELECT post_id FROM $wpdb->postmeta WHERE (meta_key='_cmmrm_longitude' AND meta_value='".$geom->getX() ."')");
	if($lat_post_id->post_id == $lon_post_id->post_id)
	{
		
		$post = get_post($lat_post_id->post_id);
		return '<'.$this->nss.'wpt lat="'.$geom->getY().'" lon="'.$geom->getX().'"><name>'.$post->post_title.'</name></'.$this->nss.'wpt>';
	}
	else
	{
		return '<'.$this->nss.'wpt lat="'.$geom->getY().'" lon="'.$geom->getX().'"></'.$this->nss.'wpt>';
	}
  }
  
  private function linestringToGPX($geom) {

	global $wpdb;
    $gpx = '<'.$this->nss.'trk>';
	$comp_counter = 0;
    foreach ($geom->getComponents() as $comp)
	{
		if($comp_counter == 0)
		{
			$rlat_post_id = $wpdb->get_row("SELECT post_id FROM $wpdb->postmeta WHERE (meta_key='cmmrm_approx_latitude' AND meta_value='".$comp->getY() ."')");
			$rlon_post_id = $wpdb->get_row("SELECT post_id FROM $wpdb->postmeta WHERE (meta_key='cmmrm_approx_longitude' AND meta_value='".$comp->getX() ."')");
			if($rlat_post_id->post_id == $rlon_post_id->post_id)
			{
				$rpost = get_post($rlat_post_id->post_id);
				$gpx .= '<name>'.$rpost->post_title.'</name>';
				$gpx .= '<extensions>';
				$gpx .= '<gpxx:TrackExtension>';
				$gpx .= '<gpxx:DisplayColor>Blue</gpxx:DisplayColor>';
				$gpx .= '</gpxx:TrackExtension>';
				$gpx .= '<gpxtrkx:TrackStatsExtension xmlns:gpxtrkx="http://www.garmin.com/xmlschemas/TrackStatsExtension/v1">';
				$gpx .= '<gpxtrkx:Distance>'.get_post_meta($rlat_post_id->post_id, '_cmmrm_distance', true).'</gpxtrkx:Distance>';
				$gpx .= '<gpxtrkx:TotalElapsedTime>'.get_post_meta($rlat_post_id->post_id, '_cmmrm_duration', true).'</gpxtrkx:TotalElapsedTime>';
				$gpx .= '<gpxtrkx:MovingTime>'.get_post_meta($rlat_post_id->post_id, '_cmmrm_avg_speed', true).'</gpxtrkx:MovingTime>';
				$gpx .= '<gpxtrkx:StoppedTime>0</gpxtrkx:StoppedTime>';
				$gpx .= '<gpxtrkx:MovingSpeed>0</gpxtrkx:MovingSpeed>';
				$gpx .= '<gpxtrkx:MaxSpeed>0</gpxtrkx:MaxSpeed>';
				$gpx .= '<gpxtrkx:MaxElevation>'.get_post_meta($rlat_post_id->post_id, '_cmmrm_max_elevation', true).'</gpxtrkx:MaxElevation>';
				$gpx .= '<gpxtrkx:MinElevation>'.get_post_meta($rlat_post_id->post_id, '_cmmrm_min_elevation', true).'</gpxtrkx:MinElevation>';
				$gpx .= '<gpxtrkx:Ascent>'.get_post_meta($rlat_post_id->post_id, '_cmmrm_elevation_gain', true).'</gpxtrkx:Ascent>';
				$gpx .= '<gpxtrkx:Descent>'.get_post_meta($rlat_post_id->post_id, '_cmmrm_elevation_descent', true).'</gpxtrkx:Descent>';
				$gpx .= '<gpxtrkx:AvgAscentRate>0</gpxtrkx:AvgAscentRate>';
				$gpx .= '<gpxtrkx:MaxAscentRate>0</gpxtrkx:MaxAscentRate>';
				$gpx .= '<gpxtrkx:AvgDescentRate>0</gpxtrkx:AvgDescentRate>';
				$gpx .= '<gpxtrkx:MaxDescentRate>0</gpxtrkx:MaxDescentRate>';
				$gpx .= '</gpxtrkx:TrackStatsExtension>';
				$gpx .= '</extensions>';
			}
			$gpx .= '<'.$this->nss.'trkseg>';
		}
		$gpx .= '<'.$this->nss.'trkpt lat="'.$comp->getY().'" lon="'.$comp->getX().'" />';
		$comp_counter++;
    }
    
    $gpx .= '</'.$this->nss.'trkseg>';
	$gpx .= '</'.$this->nss.'trk>';
    
    return $gpx;
  }
  
  public function collectionToGPX($geom) {
    $gpx = '';
    $components = $geom->getComponents();
    foreach ($geom->getComponents() as $comp) {
      $gpx .= $this->geometryToGPX($comp);
    }
    
    return $gpx;
  }

}
