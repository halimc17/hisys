<?
  defined('BASEPATH') OR exit('No direct script access allowed');
  
  class Dashboard_generator extends OWL_Controller {
    public $ochart, $toolsReport, $data_json;

    public function __construct() {
      parent::__construct();

      $this->load->model('Prc_ochartcollection', 'ochart');
      $this->load->model('Prc_tool_userdefinedreport', 'toolsReport');
    }

    function slave() {
      switch($this->get('switcher')) {
        case 'load':
          $fileJson = $this->ochart->get("WHERE isactive = '1'");
          $dataJson = [];
          foreach ($fileJson as $value) {
            $dataJson[] = json_decode(file_get_contents($value['src']), true);
          }
          $this->load($dataJson);
          // foreach ($dataJson['charts'] as $key => $value) {
          //   $dataJson['charts'][$key]['data']['columns'] = explode(',', $value['query']['kolomtampil']);

          //   $parameter = '';
          //   foreach ($value['query']['parameters'] as $k => $v) {
          //     $nilai = $v['value'];
          //     if ($k > 0) {
          //       $parameter .= ' AND ';
          //     }
              
          //     if ($v['type'] == 'Setup') {
          //       $cleanInput = str_replace('\"', '"', trim($v['value'], '"'));
          //       try {
          //         ob_start();
          //         $evalResult = null;
          //         eval('$evalResult = '.$cleanInput.';');
          //         ob_end_clean();
          //         $nilai = $evalResult;
          //       } catch (Exception $e) {
          //         // If evaluation fails, use the original input and optionally log the error
          //       }
          //     }

          //     if ($v['type'] != 'Number' && $v['operator'] != 'LIKE' && $v['operator'] != 'NOT LIKE') {
          //       $nilai = "'".$nilai."'";
          //       if ($v['operator'] == 'BETWEEN' || $v['operator'] == 'NOT BETWEEN') {
          //         $nilai = "'".$nilai."'";
          //       }
          //     }

          //     if ($v['operator'] == 'LIKE' || $v['operator'] == 'NOT LIKE') {
          //       $parameter .= $v['kolom']." ".$v['operator']." '%".$nilai."%'";
          //     } else if ($v['operator'] == 'BETWEEN' || $v['operator'] == 'NOT BETWEEN') {
          //       $parameter .= "(".$v['kolom']." ".$v['operator']." ".$nilai." AND ".$nilai.")";
          //       // array_splice($input, $i+1, 1);
          //     } else if ($v['operator'] == 'IS NULL' || $v['operator'] == 'IS NOT NULL') {
          //       $parameter .= $v['kolom']." ".$v['operator'];
          //     } else if ($v['operator'] == 'IN' || $v['operator'] == 'NOT IN') {
          //       $parameter .= $v['kolom']." ".$v['operator']." (".$nilai.")";
          //     } else {
          //       $parameter .= $v['kolom']." ".$v['operator']." ".$nilai;
          //     }
          //   }

          //   $query = str_replace('#PARAMETER#', 'WHERE '.$parameter, $value['query']['query']);

          //   $this->connectionDB($value['query']['dbname']);

          //   $data = $this->toolsReport->getData($query);

          //   $dataJson['charts'][$key]['data']['rows'] = $data;
          // }

          // $this->data_json = json_encode($dataJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
          // echo json_encode($dataJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

          // $this->view();
          $result = [
            'data' => $this->data_json,
            'view' => $this->view()
          ];

          echo json_encode($result);
        break;
      }
    }

    function load_test() {
        $fileJson = $this->ochart->get("WHERE isactive = '1'");
        $dataJson = json_decode(file_get_contents($fileJson[0]['src']), true);

        
        echo "<body id='display'>";
        echo "<script type='text/javascript'>";
        echo "var data = ".json_encode($dataJson, JSON_UNESCAPED_UNICODE).";";
        echo "</script>"; 
        echo "</body>";
        ?>
        <script type="text/javascript">
            (function () {
                // Your JavaScript code here
                var D=data;w=window;
                w.D=D;
                console.log(w.D);
                // You can now use w.D in your JavaScript code
                // For example:
                // console.log(w.D.charts); 
                //Running display.innerHTML 
            })();
        </script>
        <?php 
    }

    function load($dataJson) {
      foreach ($dataJson as $key => $value) {
        foreach ($value['charts'] as $keyChart => $valueChart) {
          $dataJson[$key]['charts'][$keyChart]['data']['columns'] = explode(',', $valueChart['query']['kolomtampil']);

          $parameter = '';
          foreach ($valueChart['query']['parameters'] as $k => $v) {
            $nilai = $v['value'];
            if ($k > 0) {
              $parameter .= ' AND ';
            }
            
            if ($v['type'] == 'Setup') {
              $cleanInput = str_replace('\"', '"', trim($v['value'], '"'));
              try {
                ob_start();
                $evalResult = null;
                eval('$evalResult = '.$cleanInput.';');
                ob_end_clean();
                $nilai = $evalResult;
              } catch (Exception $e) {
                // If evaluation fails, use the original input and optionally log the error
              }
            }

            if ($v['type'] != 'Number' && $v['operator'] != 'LIKE' && $v['operator'] != 'NOT LIKE') {
              $nilai = "'".$nilai."'";
              if ($v['operator'] == 'BETWEEN' || $v['operator'] == 'NOT BETWEEN') {
                $nilai = "'".$nilai."'";
              }
            }

            if ($v['operator'] == 'LIKE' || $v['operator'] == 'NOT LIKE') {
              $parameter .= $v['kolom']." ".$v['operator']." '%".$nilai."%'";
            } else if ($v['operator'] == 'BETWEEN' || $v['operator'] == 'NOT BETWEEN') {
              $parameter .= "(".$v['kolom']." ".$v['operator']." ".$nilai." AND ".$nilai.")";
              // array_splice($input, $i+1, 1);
            } else if ($v['operator'] == 'IS NULL' || $v['operator'] == 'IS NOT NULL') {
              $parameter .= $v['kolom']." ".$v['operator'];
            } else if ($v['operator'] == 'IN' || $v['operator'] == 'NOT IN') {
              $parameter .= $v['kolom']." ".$v['operator']." (".$nilai.")";
            } else {
              $parameter .= $v['kolom']." ".$v['operator']." ".$nilai;
            }
          }

          $query = str_replace('#PARAMETER#', 'WHERE '.$parameter, $valueChart['query']['query']);

          $this->connectionDB($valueChart['query']['dbname']);

          $data = $this->toolsReport->getData($query);

          $dataJson[$key]['charts'][$keyChart]['data']['rows'] = $data;
        }
      }

      $this->data_json = json_encode($dataJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
      
      return $this->data_json;
    }
    
    function view() {
      return "
        <div id=\"containerMenu\" class=\"d-flex flex-wrap gap-4\" style=\"padding: 25px;\">
        </div>
      ";
    }

    function connectionDB($dbname) {
      $db = get_config();
      $db = array_column($db, null, 'database');

      $dataDB = (object)$db[$dbname];
      $dataDB->name = $dataDB->database;
      $dataDB->dbname = $dataDB->database;
      $dataDB->dbserver = $dataDB->hostname;
      $this->load->database($dataDB);
    }
  }
