<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pagination {
    protected $base_url		= '';
	protected $num_links = 2;
    public $total_rows = 0;
	public $per_page = 20;
	public $cur_page = 0;
    public $type_load = null;//'auto'/null
    public $total_pages = 0;
    protected $use_page_numbers = FALSE;
    protected $first_link = '<i class="fa fa-angle-double-left"></i>&nbsp;&nbsp;First';
	protected $next_link = 'Next&nbsp;&nbsp;<i class="fa fa-angle-right"></i>';
	protected $prev_link = '<i class="fa fa-angle-left"></i>&nbsp;&nbsp;Prev';
	protected $last_link = 'Last&nbsp;&nbsp;<i class="fa fa-angle-double-right"></i>';
	protected $uri_segment = 0;
    public $page_attr;
    public $id;
    public $TABLE = array('table'=>array('t'=>1,'att'=>array('class'=>'sortable data-table full-width','data-action'=>'true','data-print'=>'true')));
    public $THEAD;
    public $TBODY;
    public $TFOOT;
    public $PAGE;
    public $DOCUMENT;

    public function __construct(){
		$this->initialize();
	}
    function initialize(){
        $this->DOCUMENT = new DOMDocument('1.0', 'utf-8');
    }
    function loadTotalPages(){
        return $this->total_pages = ceil($this->total_rows/$this->per_page);
    }
    function loadAttributePgage(){
        $actived['last'] = 'true';
        $actived['next'] = 'true';
        $actived['first'] = 'true';
        $actived['prev'] = 'true';
        $page['last'] = $this->total_pages;
        $page['next'] = ($this->cur_page+1);
        $page['first'] = 1;
        $page['prev'] = ($this->cur_page-1);
        if($this->cur_page == $this->total_pages){
            $actived['last'] = 'false';
            $actived['next'] = 'false';
        }
        if($this->cur_page == 1){
            $actived['first'] = 'false';
            $actived['prev'] = 'false';
        }
        foreach($page as $key=>$v){
            $page_attr[$key] = sprintf('%s="%u"',$key,$v);
        }
        foreach($actived as $key=>$v){
            $page_attr[$key] .= ' '.sprintf('%s="%s"','actived',$v);
        }
        $this->page_attr = (object) $page_attr;
    }
    function page(){
        $this->loadAttributePgage();
        $select = '<div id="'.$this->ID_TABLE.'_pagination" class="pagination"><button name="first" '.$this->page_attr->first.'>'.$this->first_link.'</button><button name="prev" '.$this->page_attr->prev.'>'.$this->prev_link.'</button><select name="page">';
            if($this->total_pages >1){
                for($i=1; $i<=$this->total_pages; $i++){
                    $s = '';
                    if($this->cur_page == $i){
                        $s = 'selected';
                    }
                    $select .= '<option '.$s.' value="'.$i.'">'.$i.'</option>';
                }
            }else{
                $select .= '<option>All</option>';
            }
        $select .= '</select><button name="next" '.$this->page_attr->next.'>'.$this->next_link.'</button><button name="last" '.$this->page_attr->last.'>'.$this->last_link.'</button></div>';
        $this->PAGE=$this->convHtmlToArray($select);
    }
    function loadTable(){
        $this->loadTotalPages();
        $this->cur_page = ((int)$this->cur_page==0)?1:(int)$this->cur_page;
        if($this->cur_page > 1 and  $this->cur_page <= $this->total_pages and $this->type_load == 'auto'){
            $this->addBody();
        }else if($this->cur_page == 1 or $this->type_load == null){
            $this->ID_TABLE = $this->id; 
            $table = $this->TABLE;
            
            if($this->ID_TABLE != ""){
                $table = $this->appendAttArray($table,array('id'=>$this->ID_TABLE));
            }
            $this->page();
            $this->TABLE = $table;
            $this->head();
            $this->body();
            $this->footer();
        }
        
    }
    function appendChildArray(array $parent = array(),array $params = array()){
        return $this->appendArray($parent,$params,'c');
    }
    function appendAttArray(array $parent = array(),array $params = array()){
        return $this->appendArray($parent,$params,'att');
    }
    function appendArray(array $parent = array(),array $params = array(),$attDef=""){
        foreach($parent as $parentTag=>$att){
            if($attDef!= ""){
                if(empty($att[$attDef])){
                    $dataParent = array();
                }else{
                    $dataParent = $parent[$parentTag][$attDef];
                }
                if($attDef == 'c'){
                    $parent[$parentTag][$attDef] = array_merge($dataParent,$params);
                }else{
                    $parent[$parentTag][$attDef] = array_merge($dataParent,$params);
                }
                
            }
        }
        
        return $parent;
    }
    function head(){
        $theadHTML = array('thead'=>array('t'=>1,'att'=>array('class'=>'')));
        if(!empty($this->THEAD) and count($this->THEAD)>0){
            if(isset($this->THEAD['thead']) or isset($this->THEAD[0]['thead'])){
                $thead = $this->THEAD;
            }else{
                $thead = $this->appendChildArray($theadHTML,$this->THEAD);
            }
        }else{
            $thead = $theadHTML;
        }
        if(!$this->array_is_list($thead)){
            $thead[] = $thead;
        }
        $this->TABLE = $this->appendChildArray($this->TABLE,$thead);
    }
    function body(){
        $tbodyHTML =  array('tbody'=>array('t'=>1,'att'=>array('id'=>$this->ID_TABLE.'container')));
        if(!empty($this->TBODY) and count($this->TBODY)>0){
            if(isset($this->TBODY['tbody']) or isset($this->TBODY[0]['tbody'])){
                $tbody = $this->TBODY;
            }else{
                $tbody = $this->appendChildArray($tbodyHTML,$this->TBODY);
            }
        }else{
            $tbody =$tbodyHTML;
        }
        if(!$this->array_is_list($tbody)){
            $tbody[] = $tbody;
        }
        $this->TABLE = $this->appendChildArray($this->TABLE,$tbody);
    }
    function addBody(){
        if(!empty($this->TBODY) and count($this->TBODY)>0){
            $_addBody = $this->TBODY;
        }
        $this->TABLE = $_addBody;
    }
    function footer(array $params = array()){
        $tfootHTML = array('tfoot'=>array('t'=>1,'att'=>array()));
        if(!empty($this->TFOOT) and count($this->TFOOT)>0){
            if(isset($this->TFOOT['tfoot']) or isset($this->TFOOT[0]['tfoot'])){
                $tfoot = $this->TFOOT;
            }else{
                $tfoot = $this->appendChildArray($tfoot,$this->TFOOT);
            }
        }else{
            $tfoot = $tfootHTML;
        }
        if(!$this->array_is_list($tfoot)){
            $tfoot[] = $tfoot;
        }
        $this->TABLE = $this->appendChildArray($this->TABLE,$tfoot);
    }
    
    function loadHTML(){
        echo $this->DOCUMENT->saveHTML();
    }
    function getHTML(){
        $d = new \stdClass;
        $d->forPDF = $this->forPDF();
        $d->forPrint = $this->forPrint;
        return $d;
    }
    function forPDF(){
        $this->addBorder();
        $this->removePagination();
        return $this->DOCUMENT->saveHTML();
    }
    function forPrint(){
        return $this->DOCUMENT->saveHTML();
    }
    private function addBorder($num=1){
        $table = $this->DOCUMENT->getElementsByTagName('table');
        if($table->length > 0){
            foreach($table as $k=>$v){
                $v->setAttribute('border',$num);
                $v->setAttribute('style','border-spacing: 0px;');
            }
        }
    }
    private function removePagination(){
        $elem = $this->DOCUMENT->getElementsByTagName('div');
        if($elem->length > 0){
            foreach($elem as $k=>$v){
                foreach($v->attributes as $a=>$n){
                    if($n->name == 'class' and $n->value == 'pagination'){
                        $v->parentNode->removeChild($v);
                        break;
                    }
                }
                
            }
        }
    }
    function build(){
        $this->loadTable();
        $jsonHTML = array();
        if(count($this->TABLE) > 0){
            if($this->array_is_list($this->TABLE)){
                foreach($this->TABLE as $table){
                    $jsonHTML[] = $table;
                }
            }else{
                $jsonHTML[] = $this->TABLE;
            }
        }
        if(count($this->PAGE) > 0){
            if($this->array_is_list($this->PAGE)){
                foreach($this->PAGE as $page){
                    $jsonHTML[] = $page;
                }
            }else{
                $jsonHTML[] = $page;
            }
        }
        foreach($jsonHTML as $html){
            $build = $this->develop_element($html);
            if(count($build) > 0){
                foreach($build as $elem){
                    $this->DOCUMENT->appendChild($elem);
                }
            }
        }
    }
    function array_is_list(array $arr)
    {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
    function develop_element(array $params = array()){
        if(count($params) > 0){
            $element = array();
            foreach($params as $tagName=>$att){
                if(!empty($att['t']) and $att['t'] == 1){
                    $element[$tagName] = $this->create_Element($tagName);
                }else if(!empty($att['t']) and $att['t'] == 3){
                    $element[$tagName] = $this->create_text($att['v']);
                    continue;
                }
                if(is_array($att) and count($att) > 0){
                    foreach($att as $attKeys=>$attData){
                        if((is_array($attData) and count($attData) == 0) or $attData == ""){
                            continue;
                        }
                        switch($attKeys){
                            case "att":
                                if(!empty($att['t']) and $att['t'] == 1){
                                    foreach($attData as $attName=>$attValue){
                                        $element[$tagName]->appendChild($this->create_attribute($attName,$attValue));
                                    };
                                }
                            break;
                            case "c":
                                foreach($attData as $eleTagChild){
                                    $build = $this->develop_element($eleTagChild);
                                    if(is_array($build) and count($build) > 0){
                                        foreach($build as $keyTag => $elem){
                                            $element[$tagName]->appendChild($elem);
                                        }
                                    }
                                }
                            break;
                        }
                        
                    }
                }else{
                    
                }
                
            }
        }
        return $element;
    }
    function create_attribute($att,$val){
        $attribute = $this->DOCUMENT->createAttribute($att);
        $attribute->value = $val;
        return $attribute;
    }
    function create_text($val){
        $text = $this->DOCUMENT->createTextNode($val);
        return $text;
    }
    function create_Element($att){
        $element = $this->DOCUMENT->createElement($att);
        return $element;
    }
    function makeAdjustments(array $params = array(),$defEle){
        if(count($params)>0){
            foreach($params as $k=>$v){
               //belum bisa di dev
            };
        }
    }
    function convHtmlToArray($html){
        $document = new \DOMDocument('1.0', 'UTF-8');
        // echo html_entity_decode($html);
        // set error level
        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        // Restore error level
        libxml_use_internal_errors($internalErrors);
        $DOC = $document->childNodes[1]->childNodes[0];
        // echo "<pre>";
        // print_r($DOC);
        // echo "</pre>";
        $element = $this->getChild($DOC->childNodes);
        return $element;
    }
    // Type	                        nodeName	            nodeValue
    // 1	Element	                element name	        null
    // 2	Attr	                attribute name	        attribute value
    // 3	Text	                #text	                content of node
    // 4	CDATASection	        #cdata-section	        content of node
    // 5	EntityReference	e       ntity reference name	null
    // 6	Entity	                entity name	            null
    // 7	ProcessingInstruction	target	                content of node
    // 8	Comment	                #comment	            comment text
    // 9	Document	            #document	            null
    // 10	DocumentType	        doctype name	        null
    // 11 	DocumentFragment	    #document fragment	    null
    // 12	Notation	            notation name	        null
    function getChild($nodes){
        $e = array();
        if(!empty($nodes) and property_exists($nodes, "length") and $nodes->length > 0){
            foreach($nodes as $v){
                $elemFlag = false;
                if(property_exists($v,'nodeName')){
                    //getAttribute
                    if($v->nodeType == 1){
                        $nd = array();
                        $nd[$v->nodeName]['t'] = $v->nodeType;
                        if(property_exists($v,'attributes')){
                            foreach($v->attributes as $vAttr){
                                if($vAttr->nodeType == 2){
                                    $nd[$v->nodeName]['att'][$vAttr->name] = $vAttr->value;
                                }
                            }
                        }
                        $elemFlag = true;
                    }elseif($v->nodeType == 3){
                        if(trim($v->nodeValue) != ""){
                            $nd = array();
                            $nd[$v->nodeName]['t'] = $v->nodeType;
                            $nd[$v->nodeName]['v'] = $v->nodeValue;
                            $elemFlag = true;
                        }
                    }

                    //getChild
                    if($elemFlag and property_exists($v,'childNodes')){
                        $nd[$v->nodeName]['c'] = $this->getChild($v->childNodes);
                    }
                    if($elemFlag){
                        $e[] = $nd;
                    }
                }
                
            }
        }
        return $e;
   }
}