<?
defined('BASEPATH') or exit('No direct script access allowed');
class User_route extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mmap');
        $this->load->model('Privilege');
    }

    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'Menu':?>
            
            <div class="body-frame u-margin-10">
                <form>
                    <input type="checkbox" onchange="showCoordBox(this);"><span> Show Coordinates Distance<span>
                    <hr>

                <?php 
                
                $list_user = array(
                    $_SESSION['standard']['username']=>$_SESSION['empl']['name']
                );
                if($this->Privilege->imAdmin() or (strpos(strtolower(trim($_SESSION['standard']['username'])),'owl'))){
                    $list_user = array(
                        "ALL"=>"All"
                    );
                    $dataUser = $this->Mmap->listuser($_SESSION['standard']);
                    if(count($dataUser) > 0){
                        // print_r($dataUser);
                        foreach($dataUser as $v){
                            $list_user[$v['namauser']]=$v['namauser'];
                        }
                    }
                }
                ?>
                <div class="row">
                <div class="col-12 u-margin-b-10">
                        <label>Tanggal :</label>
                        <input id="usertanggal" class="myinput full-width" type="text" value="<?php echo date("Y-m-d"); ?>" readonly="readonly" onmousemove="setCalendar(this,'%Y-%m-%d')" autocomplete="off">
                    </div>
                    <div class="col-12 u-margin-b-10">
                        <label>User Locations :</label>
                        <select class="full-width" name="userlocations" onchange="getUserRoute(this.value);" search="true">
                            <option value="">Pilih User</option>";
                            <? foreach($list_user as $k=>$v){
                                echo "<option value=\"{$k}\">{$v}</option>";
                                }?>
                        </select>
                    </div>
                </div>
                </form>
            </div>
                <?php 
                
            break;
            default:
                include(VIEWPATH.'map.php');
            break;
        }
    }

    function options($SELF, $breadcrumb)
    {
        $option = array();
        $option['master']       = '#bodymaster';
        $option['slave']        = $this->site_url() . $this->uri->uri_string . "_slave";
        $option['getpage']      = 'switcher';
        $option['type']         = 'report';
        $option['javascript']['src'] = array($this->base_url() . 'js/' . $SELF . '.js?version=' . time() . '');

        $d = array();
        $d['title'] = "Form Entry Data";
        $d['slave'] = "form";
        $d['text'] = "new";
        $d['show'] = false;
        $d['isEnable'] = false;
        $option['buatbaru'] = $d;

        $d = array();
        $d['title'] = "List Data";
        $d['text'] = "List Data";
        $d['show'] = false;
        $d['isEnable'] = false;
        $option['listdata'] = $d;

        $d = array();
        $d['title'] = "Menu";
        $d['slave'] = "Menu";
        $d['text'] = "Menu";
        $d['width'] = "300px";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['filter'] = $d;

        $option['breadcrumb']['title'] = $breadcrumb;

        $option['excel']['show'] = false;
        $option['print']['show'] = true;
        $option['pdf']['show'] = true;
        $option['csv']['show'] = false;
        $option['fixHeader']['show'] = false;
        $option['actions'] = array();
        $option['pathinfo']['site_url'] = $this->site_url();
        $option['pathinfo']['base_url'] = $this->base_url();
        $OPT =  json_encode($option);
        return $OPT;
    }
}
