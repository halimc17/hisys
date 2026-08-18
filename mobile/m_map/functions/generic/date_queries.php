<?
class Date_queries {
    function is_holiday($strtotime,$format = 'Y-m-d'){
        $result = false;
        //is suturday and sunday
        if(date("N",$strtotime) == 6){
            $result = date($format,$strtotime);
        }else if(date("N",$strtotime) == 7){
            $result = date($format,$strtotime);
        }
        return $result;
    }
    function dateRange($dateFrom,$dateTo,$format = 'Y-m-d'){
        $step = '+1 day';
        $result = false;
        $dates = [];
        
        $current = strtotime($dateFrom);
        $last = strtotime($dateTo);
        while($current <= $last ) {
            //is suturday and sunday
            $info = [];
            if($this->is_holiday($current)){
                $info['year'] = date('Y',$current);
                $info['month'] = date('F',$current);
                $info['week'] = date('W',$current);
                $info['day'] = date('d',$current);
                $info['date'] = date($format,$current);
                $info['is_holiday'] = 'day-off';
            }else{
                $info['year'] = date('Y',$current);
                $info['month'] = date('F',$current);
                $info['week'] = date('W',$current);
                $info['day'] = date('d',$current);
                $info['date'] = date($format,$current);
                $info['is_holiday'] = 'false';
            }
            $dates[$current] = $info;
            // END //
            $current = strtotime( $step,$current);
        }
        if(count($dates) > 0){
            $result = $dates;
        }
        return $result;
    }
    function diff_date($date1,$date2){
        $result = false;
            $earlier = new DateTime($date1);
            $later = new DateTime($date2);
            $diff = $later->diff($earlier);
            $result = $diff;       
       return $result;
    }

}

?>