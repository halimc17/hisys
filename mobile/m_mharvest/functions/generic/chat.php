<?
class Chat {
    function __construct(){
        $this->randColor = array();
        $this->caption_time = array();
    }
    function init(){
        echo "Initialize";
    }
    function diff_date($date1){
        $earlier = new DateTime($date1);
        $later = new DateTime();
        $diff = $later->diff($earlier);
        $result = date("F Y",strtotime($date1));
       switch($diff->d){
            case '0':
                $result ="Today";
            break;
            case '1':
                $result ="Yesterday";
            break;
       }
       switch($diff->m){
            case '1':
                $result = "Last month";
            break;
        }
        switch($diff->y){
            case '1':
                $result ="Last year";
            break;
        }
       return $result;
    }
    function caption_Query($nowTime,$lastTime){
        $result = false;
        $newCapion = $this->diff_date(date("Y-m-d",strtotime($nowTime)));
        if(!in_array($newCapion,array_values($this->caption_time))){
            $this->caption_time[$nowTime] = $newCapion;
            $result = true;
        }
        
        return $result;
    }
    function caption_show($nowTime,$lastTime){
        $result = false;
        if($DesDiff = $this->caption_Query($nowTime,$lastTime)){
            $result = $this->caption($this->caption_time[$nowTime],$nowTime);
        }
        return $result;
    }
    
    function chatbubbleuser($description,$createdate){
        return '<div class="talk-bubble userchat" datetime="'.$createdate.'"><div class="talktext"><p>'.$description.'</p></div>
        <i class="time">'.date("H:i A",strtotime($createdate)).'</i>
        </div>';
    }
    function caption($description,$createdate){
        return '<div class="chatcaption" datetime="'.$createdate.'"><i>'.$description.'</i></div>';
    }
    function notification($description,$createdate){
        return '<div class="chatnotification" datetime="'.$createdate.'"><i>'.$description.'</i></div>';
    }
    function chatbubble($description,$namakaryawan,$createdate){
        return '<div class="talk-bubble tri-right left-top" datetime="'.$createdate.'">
        <div class="chatname" style="color:'.$this->rand_color($namakaryawan).';">'.$namakaryawan.'</div>
        <div class="talktext"><p>'.$description.'</p></div>
        <i class="time">'.date("H:i A",strtotime($createdate)).'</i>
        </div>';
    }
    function rand_color($name) {
        $key = str_replace(" ","",$name);
        if(!isset($this->randColor[$key])){
            $this->randColor[$key] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        return $this->randColor[$key];
    }

    function load_chat($datachat=array(),$namakar=array(),$type='ASC'){
        $chat = false;
        if(count($datachat) > 0){
            $chat = array();
            $lastTime = '0000-00-00 00:00:00.000';
            if($type == 'DESC'){
                if($notifthisRow = $this->caption_show(current($datachat)['createdate'],$lastTime)){
                    $chat[] = $notifthisRow;
                }
            }
            foreach($datachat as $k=>$v){
                if($type == 'ASC'){
                    if($notifthisRow = $this->caption_show($v['createdate'],$lastTime)){
                        $chat[] = $notifthisRow;
                    }
                }
                
                switch($v['tipe']){
                    case 0:
                        if($v['isactive'] == 1){
                            if($v['createby'] == $_SESSION['standard']['userid']){
                                $chat[] = $this->chatbubbleuser($v['description'],$v['createdate']);
                            }else{
                                $chat[] = $this->chatbubble($v['description'],$namakar[$v['createby']],$v['createdate']);
                            }
                        }else{
                            if($v['createby'] == $_SESSION['standard']['userid']){
                                $chat[] = $this->chatbubbleuser("<font color='gray'><i class='fa fa-ban'></i>  <em>You delete this message</em></font>",$v['createdate']);
                            }else{
                                $chat[] = $this->chatbubble("<font color='gray'><i class='fa fa-ban'></i>  <em>This message was deleted</em></font>",$namakar[$v['createby']],$v['createdate']);
                            }
                        }
                    break;
                    case 1:
                        //Caption Chat
                        $chat[] = $this->caption($v['description'],$v['createdate']);
                    break;
                    case 2:
                        //Notification Chat
                        $chat[] = $this->notification($v['description'],$v['createdate']);
                    break;
                }
                $lastTime = $v['createdate']; 
                if($type == 'DESC'){
                    if(!empty($nextData=next($datachat))){
                        if($notifthisRow = $this->caption_show($nextData['createdate'],$lastTime)){
                            $chat[] = $notifthisRow;
                        }
                    }
                }
                
            }
        }
        return $chat;
    }

    function onlineUsers($id){
        $allSessions = [];
        // status 0 offline
        // status 1 online
        // status 2 online + typing
        // status 3 online + idle
        $sessionNames = scandir(session_save_path());
        foreach($sessionNames as $sessionName) {
            if(strpos($sessionName,"sess_") === false){
                continue;
            }else{
                $sessionName = str_replace("sess_","",$sessionName);
                if(strpos($sessionName,".") === false) { //This skips temp files that aren't sessions
                    @session_id($sessionName);
                    @session_start();
                    if(!empty($_SESSION['activity'][$id])){
                        $username = $_SESSION['standard']['username'];
                        $allSessions[$username]['name'] = $_SESSION['empl']['name'];
                        $allSessions[$username]['status'] = $_SESSION['activity'][$id];
                        $allSessions[$username]['end'] = $_SESSION['DIE'];
                    }
                    session_abort();
                }
            }
        } 
        return $allSessions;
    }
    function is_userhaslogin(){
        $allSessions = [];
        // status 0 offline
        // status 1 online
        // status 2 online + typing
        // status 3 online + idle
        $sessionNames = @scandir(session_save_path());
        if(is_array($sessionNames) and count($sessionNames)>0){
            foreach($sessionNames as $sessionName) {
                if(strpos($sessionName,"sess_") === false){
                    continue;
                }else{
                    $sessionName = str_replace("sess_","",$sessionName);
                    if(strpos($sessionName,".") === false) { //This skips temp files that aren't sessions
                        @session_id($sessionName);
                        @session_start();
                        if(!empty($_SESSION['standard']['userid'])){
                            $userid = $_SESSION['standard']['userid'];
                            $allSessions[$userid]['username'] = $_SESSION['standard']['username'];
                            $allSessions[$userid]['name'] = $_SESSION['empl']['name'];
                            $allSessions[$userid]['status'] = @$_SESSION['activity'];
                            $allSessions[$userid]['end'] = $_SESSION['DIE'];
                        }
                        session_abort();
                    }
                }
            } 
        }
        return $allSessions;
    }
}

?>