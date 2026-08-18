<?
class MakeHTML {
    function options($data=array(),$value=""){
        $reuslt = "<option value=\"\" selected></options>";
        if(count($data) > 0){
            foreach($data as $v){
                $selected = "";
                if(!is_object($v)){
                    $v = (object)$v;
                }
                if(trim($v->value) == ""){
                    continue;
                }
                if($v->key == $value){
                    $selected = "selected";
                }
                $reuslt .= "<option value=\"".$v->key."\" ".$selected.">".$v->value."</options>";
            }
        }
        return $reuslt;
    }
}