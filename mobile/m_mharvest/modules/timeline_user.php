<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Timeline_user extends OWL_Controller{
    public function __construct(){
		parent::__construct();
        $this->load->lib("Date_queries","dateQueries");
        $this->load->model('Project_schedule','schedule');
        $this->load->model('Setup_datakaryawan','datakaryawan');
        echo '<link rel=stylesheet type=text/css href="'.$this->base_url().'assets/css/schedule.css?version=1">';
    }
    function slave(){
      $case = $this->get('switcher');
        switch($case){
            case 'form':
              break;
            default:
                echo $this->setup_schedule_user('2023-09-01','2023-10-01');
            break;
        }
    }
    
    function setup_schedule_user($dateFrom,$dateTo){
        $funcDate = $this->lib->dateQueries;
        $date = $funcDate->dateRange($dateFrom,$dateTo);
        $month = array_unique(array_column($date,'month'));
        //$this->print_r($date);

          $width = "100%";
          $height = "100%";
          $thisSchedule = '<div class="schedule-board">';
          $thisSchedule .= '<div class="calendar-row standard" id="calendarRow">';
          foreach($month as $vMonth){
          $thisSchedule .= '<div class="calendar-month">
                                <div class="calendar-month-label">'.$vMonth.'</div>
                                    <div class="calendar-week">';
            foreach($date as $day){
                if($day['month'] == $vMonth){
                    $dayOff = "";
                    if($day['is_holiday'] != 'false'){
                        $dayOff = "day-off";
                    }
                    $thisSchedule .= ' <div class="calendar-day '.$dayOff.'" week="'.$day['week'].'">'.$day['day'].'</div>';
                }else{
                    continue;
                }
            }
            $thisSchedule .= '</div> <!-- /calendar-week -->
                            </div>  <!-- /calendar-month -->';
        }
        $thisSchedule .=  '</div> <!-- /calendar-row -->';
        $thisSchedule .=  '<div class="schedule-body"><div class="schedule-users" id="scheduleUsers">';
        $where = "Where group_type = 'karyawan' and startdate >= '".$dateFrom."' and targetdate <= '".$dateTo."' order by value_type,startdate";
        $data = $this->schedule->selectData($where);
        if(count($data)>0){
        $karyawanid = array_unique(array_column($data,'value_type'));
        $datakaryawan = array();
        if(count($karyawanid)>0){
            $datakaryawan = $this->datakaryawan->selectOptDetail("Where karyawanid in ('".implode("','",$karyawanid)."')");
        }
        $listHeader = array();
        foreach($data as $item){
          $headerReady = true;
          if(!in_array($item['value_type'],$listHeader)){
            $headerReady = false;
            $listHeader[] = $item['value_type'];
          }
        if((count($listHeader) > 1 and !$headerReady)){  
          $thisSchedule .= ' </div> <!-- /schedule-user-child -->';
        } 
        if(!$headerReady){  
        $thisSchedule .= '<div class="schedule-user">
                            <div class="user-header">
                                <span class="user-pic"></span><!-- /user-pic -->
                                <span class="user-name">
                                    '.@$datakaryawan[$item['value_type']]['namakaryawan'].'<br />
                                </span>
                                <span class="userfunction-title">
                                    '.@$datakaryawan[$item['value_type']]['bagian'].'
                                </span>
                            </div> <!-- /user-header -->';

          }

          $thisSchedule .= '<div class="schedule-item">
                            <span class="schedule-item-title">['.$item['task'].'] '.$item['activity'].'</span> <!-- /schedule-item-title -->
                            <span class="schedule-due-date">'.date("m/d",strtotime($item['targetdate'])).'</span><!-- /schedule-due-date -->
                            </div> <!-- /schedule-item -->';
          }
          $thisSchedule .= ' </div> <!-- /schedule-user-child -->';
        }
      $thisSchedule .= '</div> <!-- /schedule-users -->

              <div class="schedule-matrix" id="scheduleMatrix">
                    <!-- frame Timeline -->';
            if(count($data)>0){
              $no = 0;
              $listHeader = array();
              foreach($data as $item){
                $headerReady = true;
                if(!in_array($item['value_type'],$listHeader)){
                  $headerReady = false;
                  $listHeader[] = $item['value_type'];
                }
                if((count($listHeader) > 1 and !$headerReady)){  
                  $thisSchedule .= ' </div> <!-- /user-allocation -->';
                } 
                 $h = 10;
                 $diff = $funcDate->diff_date($item['startdate'],$item['targetdate']);
                 $dayFrom = ((int)date('d',strtotime($item['startdate']))-1);//start Date = 0
                 $left = ($h+($dayFrom*71));
                 $diffDay = ((int)$diff->d);
                 $width = ($diffDay*71);
                 $color = (@$item['color_line']!="")?"border-color:".$item['color_line'].";":"";
                 if(!$headerReady){
  $thisSchedule .= '<div class="user-allocation">
                      <div class="user-allocation-header"></div> <!-- /user-allocation-header -->';
                 }
  $thisSchedule .= '<div class="user-allocation-item">
                          <div class="allocation-item-bar" style="'.$color.'left:'. $left.'px; width:'.$width.'px;">
                              <i class="fa fa-info-circle detail-icon" aria-hidden="true" onmouseover="showScheduleInfoBox(this);"></i>
                              <span class="estimation-label" title="Estimativa">'.$diff->d.' Days</span>
                              <span class="due-date-label" title="Target">'.date("m/d",strtotime($item['targetdate'])).'</span>
                          </div> <!-- /allocation-item-bar -->
                      </div> <!-- /user-alocation-item -->';
                  $no++;  
              }
              $thisSchedule .= ' </div> <!-- /user-allocation -->';
            }
             // frame Grid -->
              $thisSchedule .= '<div class="grid-overlay" id="gridOverlay">';
                      foreach($date as $day){
                        if($day['is_holiday'] != 'false'){
                            $thisSchedule .= '<div class="grid-day day-off"></div>';
                        }else{
                            $thisSchedule .= '<div class="grid-day"></div>';
                        }
                    }
 $thisSchedule .= '</div> <!-- /grid-overlay -->
              </div> <!-- /schedule-matrix -->
  
          </div> <!-- /schedule-body -->
      </div>
  
      <div class="scroll-anchor" id="scrollAnchor">
          &nbsp;
      </div>
  

      <script type="text/template" id="scheduleItemBox">
          <div class="schedule-box-detail">
              TITLE
          </div>
          <div class="schedule-box-title">TASK</div>
          <div class="schedule-box-commands">
              <a href="#">Detail</a>
              <a href="#" onclick="showActionBox(\'taskForwardBox\'); return false;">Comment</a>
              <a href="#">Edit Timeline</a>
          </div>
          <div class="action-box" data-id="taskForwardBox">
              <div class="buttons-bar">
                  <button onclick="closeActionBox()">Save</button>
                  <button onclick="closeActionBox()">Cancel</button>
              </div>
          </div>
          </script>';
          
        return $thisSchedule;
          //return '<iframe src="https://calendar.google.com/calendar/embed?src=rnd.owl2023%40gmail.com&ctz=Indian%2FChristmas" style="border: 0" width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no"></iframe>';
    }
    function options($SELF,$breadcrumb){
        $option = array();
        $option['master']       = '#bodymaster';
        $option['slave']        = $this->site_url().$this->uri->uri_string."_slave";
        $option['getpage']      = 'switcher';
        $option['type']         = '';
        $option['javascript']['src'] = array($this->base_url().'js/'.$SELF.'.js?version='.time().'');

        $d=array();
        $d['title'] = "Form Entry Data";
        $d['slave'] = "form";
        $d['text'] = "new";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['buatbaru'] = $d;

        $d=array();
        $d['title'] = "List Data";
        $d['text'] = "List Data";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['listdata'] = $d;

        $d=array();
        $d['title'] = "Filter";
        $d['slave'] = "Filter";
        $d['text'] = "Filter";
        $d['width'] = "300px";
        $d['show'] = true;
        $d['isEnable'] = false;
        $option['filter'] = $d;

        $option['breadcrumb']['title'] = $breadcrumb;

        $option['excel']['show']= false;
        $option['pdf']['show']= false;
        $option['csv']['show']= false;
        $option['fixHeader']['show']= false;
        $option['actions'] = array();
        $option['pathinfo']['site_url'] = $this->site_url();
        $option['pathinfo']['base_url'] = $this->base_url();
        $OPT =  json_encode($option);
        return $OPT;
    }

}

?>