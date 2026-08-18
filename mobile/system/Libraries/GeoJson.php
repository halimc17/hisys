<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GeoJson {
    public $extention = '';
    public $Collenctions;
    public $styleData;
    public $style;
    public $Document;
    public $DocumentString;
    public $KmlReader;
    //"/Applications/XAMPP/xamppfiles/htdocs/palmaprima/mobile/system/../template/primary/assets/map/marker/marker_pokok.png"
    public $styleDefPoint = array(
        // "marker-symbol"=> "../template/primary/assets/map/marker/marker_pokok.png",
        "marker-name"=> 'circle'
    );
    public $styleDef = array(
            "stroke-width"=>2,
            "fill-color"=> "#ffffff",
            "fill-opacity"=>1,
            "stroke-color"=> "#567f9a",
            "stroke-opacity"=> 1.0,
        );
    /**
	 * Constructor
	 *
	 * Sets the User Agent and runs the compilation routine
	 *
	 * @return	void
	 */
    public function __construct(){
        $this->style = new stdClass;
	}
    function kmlFile($content){
        $result = null;
        $xmlDocument = simplexml_load_string($content);
        if ($xmlDocument->getName() !== 'kml') {
            throw new WrongDocumentFormat();
        }else{
            $this->Document = $xmlDocument;
            $this->DocumentString = $content;
            $result = $xmlDocument;
        }
        return $result;
    }
    public function init($file,$name)
    { 
        $content = $this->_load_file($file,$name);
        $this->_compile_data($content);
        return $this;
    }
    public function Json(){ 
        return json_encode($this->Collenctions);
    }
    public function array(){ 
        return $this->Collenctions;
    }
    public function document()
    { 
        return $this->Document;
    }
    public function style()
    { 
        return json_encode($this->style);
    }
    public function documentString()
    { 
        return $this->DocumentString;
    }
    protected function _compile_data($content = array()):void
    {
        /* geoJson File
        ================================

        Point : [lon, lat, alt]
        MultiPoint : [[lon, lat, alt],..]
        LineString : [[lon, lat, alt],..]
        MultiLineString : [[[lon, lat],..],..]
        Polygon : [[[lon, lat],[lon, lat]],..]
        MultiPolygon :[[[[lon,lat],[lon, lat]],..],..]
        
        let geoJson = {
            type : "FeatureCollection",
            features : new Array(),
            version : "",
            id : ""
        };

        let geoFeature = {
            type : "Feature",
            id : "ID",
            geometry : {},
            properties : {},
            title: "Example title",
        };
        let geoGeometry = {
            type : "Point",
            coordinates : new Array()
        };
        let geoProperties : {
            obj : "",
            obj2 : {},
        }
        */
        
        if(!empty($content) and count($content) > 0){
            foreach($content as $key=>$f){
                $xmlDocument = $this->kmlFile($f['content']);
                if(!empty($xmlDocument)){
                    $this->Collenctions = $this->Collection($xmlDocument);
                }
            }
        }
    }
    protected function parsePlacemark($placemark,$style): array {
        $geometry = $this->parseGeometry($placemark);
        if (isset($placemark->Point)){
            $style_id = "point";
            $properties = [
                'name' => (string)$placemark->name,
                'list-action' => array(),
                'description' => (string) $placemark->description,
                // Estrai ulteriori informazioni, come timestamp o ExtendedData se necessario
            ];
            foreach($this->styleDefPoint as $k=>$v){
                $properties[$k] = $v;
            }
            if($styleUrl = $placemark->styleUrl){
                $style_id = str_replace("#","",trim($styleUrl.PHP_EOL)); //str_replace('#','',$styleUrl);
                if(!in_array($style_id,(array)$this->styleData)){
                    $this->styleData[] = $style_id;
                }
                if(!empty($styleUrl)){
                    if(!empty($style->$style_id)){
                        $styleHit = $style->$style_id;
                        $properties["label-color"] = $styleHit["label-color"];
                        $properties["label-size"] = $styleHit["label-size"];
                    }
                }
                
            }
            $properties["style-name"] = $style_id;
        }else{
            $doc = new DOMDocument();
            $doc->loadHTML($placemark->description);
            $dataDes = array();
            $tables = $doc->getElementsByTagName('table');
            if(!empty($table = $tables[1])){
                foreach ($table->getElementsByTagName('tr') as $row) {
                    $td = $row->getElementsByTagName('td');
                    $dataDes[$td[0]->nodeValue] = $td[1]->nodeValue;
                }
            }
            // Attribute menambah List Actions in Attribute properties
            // 'list-action' => array('view'=>array('text'=>'View Map','execute'=>'testFunction','arguments'=>['masuk pak Aji'])),
            // 'description' => (string) $placemark->description,
            $properties = [
                'name' => (string)$placemark->name,
                'list-action' => array(),
                'description' => $dataDes,
                // Estrai ulteriori informazioni, come timestamp o ExtendedData se necessario
            ];
            foreach($this->styleDef as $k=>$v){
                $properties[$k] = $v;
            }
            if($styleUrl = $placemark->styleUrl){
                $style_id = str_replace("#","",trim($styleUrl.PHP_EOL)); //str_replace('#','',$styleUrl);
                if(!in_array($style_id,(array)$this->styleData)){
                    $this->styleData[] = $style_id;
                }
                $properties["style-name"] = $style_id ;
                if($placemark->type == "LineString" or $placemark->type == "MultiLineString") {
                    if(!empty($style->$style_id)){
                        $styleHit = $style->$style_id;
                        $properties["stroke-width"] = $styleHit["stroke-width"];
                        $properties["stroke-color"] = $styleHit["stroke-color"];
                    }
                }else{
                    if(!empty($style->$style_id)){
                        $styleHit = $style->$style_id;
                        $properties["stroke-width"] = $styleHit["stroke-width"];
                        $properties["fill-color"] = $styleHit["fill-color"];
                        $properties["stroke-color"] = $styleHit["stroke-color"];
                    }
                }
            }
        }
        return [
            'type' => 'Feature',
            'properties' => $properties,
            'geometry' => $geometry,
        ];
    }
    

    protected function parseLineStringCoordinates($lineString): array {
        $coordinatesList = explode(' ', trim((string) $lineString->coordinates));
        return array_map(function($coord) {
            $parts = explode(',', trim($coord));
            return array_map('floatval', $parts);
        }, $coordinatesList);
    }
    protected function parseLinearRingCoordinates($LinearRing): array {
        // return $LinearRing->coordinates;
        $coordinatesList = explode(' ', trim((string)$LinearRing[0]->coordinates));
        return array_map(function($coord) {
            $parts = explode(',', trim($coord));
            return array_map('floatval', $parts);
        }, $coordinatesList);
    }
    protected function Collection($content = array()){
        $result = new stdClass;
        $features = array();
        $this->style = new stdClass;
        $styleUrl = array();
        $features_temp = array();
        if(count($content->children())> 0){
            $styleUrl = $this->getChild($content,'Style');
            if(count($styleUrl)> 0){
                foreach($styleUrl as $styleV){
                    $idStyle = $styleV->attributes()->id;
                    $styleHit = $this->Properties($styleV);
                    $properties["name"] = trim($idStyle.PHP_EOL);
                    $properties["type"] = '';
                    $properties["stroke-width"] = (double)trim($styleHit->LineStyle->width.PHP_EOL);
                    $properties["stroke-color"] = "#".(string)trim($styleHit->LineStyle->color.PHP_EOL);
                    $properties["fill-color"] = "#".(string)trim($styleHit->PolyStyle->color.PHP_EOL);
                    $properties["fill-outline"] = (int)trim($styleHit->PolyStyle->outline.PHP_EOL);
                    $properties["label-color"] = "#".(string)trim($styleHit->LabelStyle->color.PHP_EOL);
                    $properties["label-size"] = (double)trim($styleHit->LabelStyle->scale.PHP_EOL);
                    // $properties["datareal"] = $styleHit;
                    $this->style->$idStyle = $properties;
                }
            }
            $result->Placemark = $this->getChild($content,'Placemark');
            if(count($result->Placemark)> 0){
                foreach($result->Placemark as $Placemark){
                    $Feature = $this->parsePlacemark($Placemark,$this->style);
                    if($Feature['geometry']['type'] == 'Point'){
                        $features_temp[] = $Feature;
                    }else{
                        $features[] = $Feature;
                    }
                    
                }
                if(count($features_temp)>0 and count($features_temp)>1){
                    $d['type'] = 'Feature';
                    foreach($features_temp as $k=>$v){
                        $d['properties'] = $v['properties'];
                        $d['geometry']['type'] = 'MultiPoint';
                        $d['geometry']['coordinates'][] = $v['geometry']['coordinates'];
                    }
                    $features[] = $d;
                }
            }
        }else{
            throw new WrongDocumentFormat();
        }
        return [
            'type' => 'FeatureCollection',
            'features' => $features,
            'name' => $this->Document->getName(),
            'styleUrl'=> $this->style,
            // 'styleData'=> $this->styleData
        ];
    }
   
    protected function parseGeometry($placemark): array {
        // Gestione Point
        if (isset($placemark->Point)) {
            $coordinates = explode(',', trim((string) $placemark->Point->coordinates));
            return [
                'type' => 'Point',
                'coordinates' => array_map('floatval', $coordinates)
            ];
        }
        
        // Gestione LineString
        if (isset($placemark->LineString)) {
            $coordinatesList = explode(' ', trim((string) $placemark->LineString->coordinates));
            $coordinates = array_map(function($coord) {
                $parts = explode(',', $coord);
                return array_map('floatval', $parts);
            }, $coordinatesList);

            return [
                'type' => 'LineString',
                'coordinates' => $coordinates
            ];
        }

        // Gestione MultiGeometry LineString / LinearRing
        if (isset($placemark->MultiGeometry)) {
            $name = trim($placemark->name.PHP_EOL);
            $nameType = "";
            $multiLineCoordinates[$name] = [];
            foreach ($placemark->MultiGeometry->children() as $childGeometry) {
                if ($childGeometry->getName() == 'LineString') {
                    $nameType = 'Multi'.$childGeometry->getName();
                    $lineCoordinates = $this->parseLineStringCoordinates($childGeometry);
                    if (!empty($lineCoordinates)) {
                        $multiLineCoordinates[$name][] = $lineCoordinates;
                    }
                }elseif($childGeometry->getName() == 'Polygon'){
                    $nameType = $childGeometry->getName();
                    $lineCoordinates = $this->parseLinearRingCoordinates($this->getChild($childGeometry,'LinearRing'));
                    if (!empty($lineCoordinates)) {
                        $multiLineCoordinates[$name][] = $lineCoordinates;
                    }
                }
            }
            return [
                'type' => $nameType,
                'coordinates' => $multiLineCoordinates[$name]
            ];
        }

        return [];
    }
    protected function getChild($children = array(),$namobject,$isMulti=array()){
        $b = null;
        if(count($children->children())> 0){
            foreach($children as $propV){
                if($namobject == $propV->getName()){
                    $b[] = $propV;
                }else{
                    if($namobject == 'MultiGeometry'){
                        $isMulti['MultiGeometry'] = true;
                    }
                    if($namobject == 'outerBoundaryIs'){
                        $isMulti['outerBoundaryIs'] = true;
                    }
                    if(!empty($res = $this->getChild($propV,$namobject,$isMulti))){
                        if(!empty($b)){
                            $b = array_merge($b,$res);
                        }else{
                            $b = $res;
                        }

                    }
                }
            }
        }
        return $b;
    }
    protected function Geometry($content = array()){
        $result = new stdClass;
        $result->type = "Feature";
        $result->coordinates = array();
        return $result;
    }
    protected function Properties($content){
        $result = new stdClass;
        if(count($content->attributes())> 0){
            foreach($content->attributes() as $prop=>$propV){
                $result->$prop = trim($propV.PHP_EOL);
            }
        }
        if(count($content->children())> 0){
            foreach($content->children() as $prop=>$propV){
                $result->$prop = $propV;
            }
        }
        return $result;
    }
    protected function _set_content($data=array(),$nameFile){
        $fileApproved = array('kml','json','svg');
        // $fileApproved = array('kml','json','svg','xsl');
        $result = null;
        if(isset($data['content'])){
            if(isset($data['name'])){
                $data['info'] = pathinfo($data['name']);
            }
            $data['fileupload'] = $nameFile;
            if(isset($data['info']) and in_array(strtolower($data['info']['extension']),$fileApproved)){
                if($data['info']['extension'] == 'kml'){
                    $content = fopen('data://text/plain,' .$data['content'],'r');
                }elseif($data['info']['extension'] == 'svg'){
                    $content = fopen($data['content'], "r");
                }else{
                    $content = fopen($data['content'], "r");
                }
                $fileContent = "";
                while (!feof($content)) {
                    $fileContent .= fgets($content);
                }
                fclose($content);
                $data['content'] = $fileContent;  
                $result = $data;
            }else{
                // $content = fopen($data['content'], "r");
            }
        }
        return $result;
    }
    protected function _load_file($file_tmpname,$nameFile){
        $file = Null;
        $info = pathinfo($nameFile);
        $fileApproved = array('kmz','zip');
        if(in_array(strtolower(@$info['extension']),$fileApproved)){
            $zip = zip_open($file_tmpname);
            $content = array();
            if($zip){
                $count = 0;
                while ($zip_entry = zip_read($zip)) {
                    $count++;
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $res = array();
                        $res['name'] = zip_entry_name($zip_entry);
                        $res['content'] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        $content[] = $res;
                    }
                }	
                zip_close($zip);
                $fps = array();
                foreach($content as $c){
                    if($res = $this->_set_content($c,$nameFile)){
                        $fps[] = $res;
                    }
                }
                if(count($fps) > 0){
                    $file = $fps;
                }
            }
        }else{
            // exit('ERROR : File tidak di izinkan');
            $res = array();
            $res['name'] = $nameFile;
            $res['content'] = $file_tmpname;
            if($res = $this->_set_content($res,$nameFile)){
                $fps[] = $res;
                $file = $fps;
            }
        }
        return $file;
    }
}