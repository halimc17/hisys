

<div class="menu_setup">
    <div class="title">My Account</div>

    <?php
    $this->load->lib("Chat","chat");
    $Funct = $this->lib->chat;
    $user_name = $_SESSION['standard']['username'];
    $user_name = ucwords(str_replace("."," ",$user_name));
    $userAgent = array(
        'Agent'=> $this->user_agent->agent,
        'is_mobile'=> json_encode($this->user_agent->is_mobile),
        'is_browser'=> json_encode($this->user_agent->is_browser),
        'platforms'=> implode(",",$this->user_agent->platforms),
        'platform'=> $this->user_agent->platform,
        'browsers'=> implode(",",$this->user_agent->browsers),
        'mobiles'=> implode(",",$this->user_agent->mobiles),
        'browser'=> $this->user_agent->browser,
        'mobile'=> $this->user_agent->mobile
    );
    // echo "<pre>";
    // var_dump($this->user_agent);
    // echo "</pre>";

    switch($this->get('page')){
        default: ?>
            <div class="row">
                <div class="col-12">
                    <div class="f-grid f-load">
                            <div class="custom-review f-grid-item f-block-width es-padding">
                                <div class="es-wrapper es-box es-online" tabindex="0">
                                <div class="es-background" ></div>  
                            <div class="es-user">
                            <div class="es-avatar img-75" style="background-color:<?php echo @$Funct->rand_color($_SESSION['standard']['username']);?>"><div><?php echo strtoupper(substr($_SESSION['standard']['username'],1,1)); ?></div></div>
                                <div class="es-username"><?php echo (empty($_SESSION['empl']['name']))?$user_name:$_SESSION['empl']['name']; ?></div>
                            <div class="es-location"><?php echo  $_SESSION['empl']['lokasitugas']; ?></div>
                            </div>
                            <div class="es-text review-text-263313e328a04ddb6f6eca39c2dfe04c" data-href="">
                                
                            </div>
                            <div class="es-meta">
                                <div id="" class="es-date"> Active </div>
                            </div>
                            </div>
                        </div>            
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <ul class="" >
            <!-- <li class="inactive"><a href="#">Profile</a></li>
            <li class="inactive"><a href="#">Full Name</a></li> -->
            <li><a>Change Password</a></li>
            <!-- <li><a onclick="javascript:sessionStorage.menuFormat='v1';">Format Menu</a></li> -->
            <li class="inactive"><a href="#">User Email</a></li>
            <?php 
            foreach($userAgent as $k=>$v){
                echo '<li><a>'.$k.' : '.$v.'</a></li>';
            }
            ?>
        </ul>

    <?php
        // echo "<pre>";
        // var_dump($this->user_agent);
        // echo "</pre>";
        

        break;
        case 'changepassword':

        break;

    }
    ?>
    <div class="clearfix"></div>
</div>
<!-- <div>
    <form method="POST">
        <iunput type="password" name="old_password" plasceholder="Old Password">
        <hr>
        <iunput type="password" name="new_password" plasceholder="New Password">
        <iunput type="password" name="confirm_password" plasceholder="Confirmed Password">

    </form>
</div> -->
<style>
   .menu_setup .title{
        background: white;
        position: sticky;
        top:0;
        left:0;
        right:0;
        text-align:left;
        z-index: 1;
        border-bottom: 1px solid #e5e2e2;
        width: inherit;
        font-size: xx-large;
        line-height: 70px;
        padding-left: 15px;
        color: #313131
    }
    .menu_setup{
        display: block;
        background: whitesmoke;
        width: 100%;
        height: inherit;
        height: -webkit-fill-available;
    }
    .menu_setup ul{
        background: whitesmoke;
        display: block;
        /* width: inherit; */
        /* height: -webkit-fill-available; */
        margin: 0px;
    }
    .menu_setup ul li{
        margin-top: 20px;
        position: relative;
    }
    .menu_setup ul li a{
        cursor:pointer;
    }
    .menu_setup ul li a:hover{
        color:#275370;
    }
    .menu_setup ul li::marker{
        content:'';
        font: normal normal normal 12px/1 FontAwesome;
        color:black;
    }
    .menu_setup ul li.inactive{
        display:none;
    }


.f-grid {
    background: transparent;
    max-width: 100%;
    margin: 0 auto;
}
.es-padding {
    padding-left: 5px !important;
    padding-right: 5px !important;
}
.es-meta i.offline{
    color:#ccc;
}
.es-meta i.offline:after{
    content:" Offline";
    font-family: "Arial",sans-serif,"Myriad Pro","Myriad Web","Tahoma";
    color:#cecece;
}
.es-meta i.online{
    color:#81e810;
}
.es-meta i.online:after{
    content:" Online";
    font-family: "Arial",sans-serif,"Myriad Pro","Myriad Web","Tahoma";
    color:#000;
}
.f-block-width {
    min-width: -webkit-fill-available;
    min-height: -webkit-fill-available;
}
@media (min-width: 385px) and (max-width: 767px)
.f-grid-sizer, .f-grid-item {
    width: 50%;
}
.f-grid-item {
    float: left;
}
#es-grid{
padding: 15px;
}
.es-box {
    text-align: center;
    /* height: 350px; */
    opacity:0.3;
}
.es-online {
    opacity:1 !important;
}
.es-wrapper {
    margin: 5px 0 !important;
}
.es-box {
    background-color: #FFFFFF;
    padding: 25px;
    width: 100%;
    border-radius: 8px;
    border: 1px solid #D6DAE4;
    overflow: hidden;
    position: relative;
}
.es-stars {
    margin-bottom: 15px;
}
.es-location {
    color: #828282;
    font-size: 12px;
    letter-spacing: 1.12px;
    margin-bottom: 15px;
}
.es-username {
    color: #000000;
    font-size: 18px;
    line-height: 25px;
    word-break: break-word;
    word-wrap: break-word;
    font-weight: 600;
    margin-bottom: 0px;
    min-height: 75px;
}
.es-avatar-img {
    border-radius: 50%;
    margin-bottom: 10px;
    object-fit: cover;
    position: relative;
}
.es-text {
    line-height: 1.5;
    word-break: break-word;
    word-wrap: break-word;
    margin-bottom: 20px;
    color: #000000;
}
.es-background{
 background-repeat: no-repeat;background-size:cover;background-blend-mode: inherit;
 width: 100%;
    height: 105px;
    position: absolute;
    top: 0px;
    left: 0px
}
.avatar-75 {
    width: 75px;
    height: 75px;
}
.es-avatar {
    position: relative;
    margin-left: calc(50% - 37.5px);
    border: 5px solid #fff;
}
.img-75 {
    width: 75px;
    height: 75px;
    font-size: 40px;
}
.es-avatar {
    border-radius: 50%;
    color: #fff;
    text-align: center;
    margin-bottom: 15px;
    object-fit: cover;
    background-position: 50%;
    background-size: cover;
}
.es-avatar div {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
</style>