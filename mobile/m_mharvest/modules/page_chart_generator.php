<?
  defined('BASEPATH') OR exit('No direct script access allowed');
  
  class Page_chart_generator extends OWL_Controller {
    public $lib, $uri, $chart, $chartht, $chartdt, $ochart, $toolParam, $toolsReport, $toolsReportPar;

    public function __construct() {
      parent::__construct();

      $this->load->model('Prc_tool_chart', 'chart');
      $this->load->model('Prc_page_chart_ht', 'chartht');
      $this->load->model('Prc_page_chart_dt', 'chartdt');
      $this->load->model('Prc_ochartcollection', 'ochart');
      $this->load->model('Prc_tool_php_encode', 'toolParam');
      $this->load->model('Prc_tool_userdefinedreport', 'toolsReport');
      $this->load->model('Prc_tool_userdefinedreport_par', 'toolsReportPar');

      $this->load->lib('Pagination', 'pagination');
    }

    function slave() {
      switch($this->get('switcher')) {
        case 'save':
          $title = $this->get('title');
          $data = json_decode(html_entity_decode($this->get('data'), ENT_QUOTES));

          $dataInsertHt = array(
            'title' => $title,
            'status' => 1
          );

          $insertDataHt = $this->chartht->insertTable($dataInsertHt);
          if ($insertDataHt) {
            $id = $this->chartht->get('WHERE title = "'.$title.'"')[0]['id'];

            foreach ($data as $dt) {
              $dataInsertDt = array(
                'parentid' => $id,
                'chartid' => $dt->id,
                'x' => $dt->x,
                'y' => $dt->y,
                'w' => $dt->w,
                'h' => $dt->h
              );

              $insertDataDt = $this->chartdt->insertTable($dataInsertDt);
              if (!$insertDataDt) {
                echo json_encode([
                  'status' => 'error',
                  'message' => 'Failed to save chart'
                ]);
                break;
              }
            }
          } else {
            echo json_encode([
              'status' => 'error',
              'message' => 'Failed to save header chart'
            ]);
            break;
          }

          // get id
          $id = $this->chartht->get('', 'MAX(id) AS id')[0]['id'];
          if (!$id) {
            echo json_encode([
              'status' => 'error',
              'message' => 'Failed to get chart id'
            ]);
            break;
          }

          echo json_encode([
            'status' => 'success',
            'message' => 'Success to save chart',
            'id' => $id
          ]);
        break;
        case 'parameter':
          $parameters = $this->toolsReportPar->get("WHERE rnumber = '".$this->get('rnumber')."'");

          echo json_encode($parameters);
        break;
        case 'new':
          $this->loadNew();
        break;
        case 'init':
          $user = $_SESSION['standard']['username'];
          $listTable = $this->toolsReport->get("WHERE owner = '".$user."' AND status = 1", "ORDER BY rnumber");
          $rnumber = implode(',', array_column($listTable, 'rnumber'));
          $listChart = $this->chart->get("WHERE rnumber IN (".$rnumber.") AND status = 1 ORDER BY id");

          echo json_encode([
            'listTable' => $listTable,
            'listChart' => $listChart
          ]);
        break;
        case 'load':
          $fileid = $this->get('id');

          $fileJson = $this->ochart->get("WHERE fileid = '{$fileid}'");
          $dataJson = json_decode(file_get_contents($fileJson[0]['src']), true);
          foreach ($dataJson['charts'] as $key => $value) {
            $dataJson['charts'][$key]['data']['columns'] = explode(',', $value['query']['kolomtampil']);

            $parameter = '';
            foreach ($value['query']['parameters'] as $k => $v) {
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
              } else if ($v['operator'] == 'IS NULL' || $v['operator'] == 'IS NOT NULL') {
                $parameter .= $v['kolom']." ".$v['operator'];
              } else if ($v['operator'] == 'IN' || $v['operator'] == 'NOT IN') {
                $parameter .= $v['kolom']." ".$v['operator']." (".$nilai.")";
              } else {
                $parameter .= $v['kolom']." ".$v['operator']." ".$nilai;
              }
            }

            $query = str_replace('#PARAMETER#', 'WHERE '.$parameter, $value['query']['query']);

            $this->connectionDB($value['query']['dbname']);

            $data = $this->toolsReport->getData($query);

            $dataJson['charts'][$key]['data']['rows'] = $data;
          }

          echo json_encode($dataJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;
        case 'view':
          $this->LoadPreview();
        break;
        case 'loadData':
          $rnumber = $this->get('rnumber');
          $chartid = $this->get('chartid');
          $parentid = $this->get('parentid');

          $chart = $this->chart->get("WHERE id IN (".$chartid.")");
          $chartdt = $this->chartdt->get("WHERE parentid = '".$parentid."'");

          echo json_encode([
            'rnumber' => $rnumber,
            'chartid' => $chartid,
            'chart' => $chart,
            'chartdt' => $chartdt
          ]);
        break;
        case 'publish':
          $this->chartht->updateStatus(1, $this->get('id'));
        break;
        case 'unpublish':
          $this->chartht->updateStatus(0, $this->get('id'));
        break;
        case 'delete':
          $this->chartht->updateStatus(2, $this->get('id'));
        break;
        case 'data':
          $rnumber = $this->get('rnumber');
          $parameter = html_entity_decode($this->get('parameter'), ENT_QUOTES);

          $qc = $this->toolsReport->getQueryColumn($rnumber);
          $query = str_replace('#PARAMETER#', 'WHERE '.$parameter, $qc[0]['query']);
          
          $this->connectionDB($qc[0]['dbname']);

          $data = $this->toolsReport->getData($query);
          $kolomTampil = explode(',', $qc[0]['kolomtampil']);
          
          echo json_encode([
            'rows' => $data,
            'columns' => $kolomTampil
          ]);
        break;
        case 'getSetupParams':
          $params = $this->toolParam->get();
          foreach ($params as $key => $value) {
            $cleanInput = trim($value['value'], "'\"");
            // Evaluate the PHP expression
            try {
              // Start capturing any unwanted output (if needed)
              ob_start();
              $evalResult = null;
              eval('$evalResult = '.$cleanInput.';');
              ob_end_clean();
              $params[$key]['nilai'] = $evalResult;
            } catch (Exception $e) {
              // If evaluation fails, use the original input and optionally log the error
            }
          }

          echo json_encode($params);
        break;
        default: 
          try {
            //get request halaman
            $page = ((int)$this->get('page') == 0) ? 1 : (int)$this->get('page');
            // get user session
            $user = $_SESSION['standard']['username'];
            // get data from database
            $rnumber = $this->toolsReport->getRnumber($user);
            $chartid = $this->chart->getId(implode(',', $rnumber));
            $parentid = $this->chartdt->getParentId(implode(',', $chartid));
            $getData = $this->chartht->get("WHERE status <> 2 AND id IN (".implode(',', $parentid).") ORDER BY id");
            // set table and pagination
            $table = $this->lib->pagination;
            $table->id = 'tableChartList';
            if ($getData) {
              $table->total_rows = count($getData);
            } else {
              $table->total_rows = 0;
            }
            $table->per_page = 20;
            $table->cur_page = $page;
            $starting_limit = ($page - 1) * $table->per_page;
            $dataTable = $this->dataList(
              $this->chartht->listData(
                [$starting_limit, $table->per_page],
                "WHERE status <> 2 AND id IN (".implode(',', $parentid).") ORDER BY id"
              ),
              $starting_limit
            );
            // create HTML to json
            $table->THEAD = $table->convHtmlToArray($dataTable->head);
            $table->TBODY = $table->convHtmlToArray($dataTable->body);
            // build HTML
            $table->build();
            // load HTML
            $table->loadHTML();
          } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
          }
        break;
      }
    }

    function dataList($data = array(), $no) {
      $thead = "
        <thead>
          <tr class='rowheader'>
            <th align='center'>No</th>
            <th align='center'>Page Chart Title</th>
            <th align='center'>Status</th>
          </tr>
        </thead>
      ";
      
      $table = "";
      $uriPublish = "?switcher=publish";
      $uriUnpublish = "?switcher=unpublish";
      $uriDelete = "?switcher=delete";

      if (count($data) > 0) {
        foreach ($data as $key => $value) {
          $no++;
          $action = array();
          $action['view'] = "viewAction('&id={$value['id']}', '{$value['title']}');";

          if ($value['status'] == '0') {
            $action['publish'] = "publishAction('{$uriPublish}&id={$value['id']}');";
            $action['delete'] = "deleteAction('{$uriDelete}&id={$value['id']}');";
          } else if ($value['status'] == '1') {
            $action['unpublish'] = "unpublishAction('{$uriUnpublish}&id={$value['id']}');";
          }

          $action = $this->toAtrr($action);

          if ($value['status'] == '0') {
            $currentStatusText = "Not Published";
            $style = "style='color: #e07979;'";
          } else if ($value['status'] == '1') {
            $currentStatusText = "Published";
            $style = "";
          }

          $table .= "
            <tr class='rowcontent' list-action ".$action." ".$style.">
              <td align='center'>".$no."</td>
              <td align='left'>".$value['title']."</td>
              <td align='center'>".$currentStatusText."</td>
            </tr>
          ";
        }
      } else {
        $table .= "
          <tr>
            <td align='center' colspan='5'>No Data</td>
          </tr>
        ";
      }
      
      $result['head'] = $thead;
      $result['body'] = $table;

      return (object)$result;
    }

    function loadNew() {
      ?>
        <link rel=stylesheet type=text/css href=<?= $this->base_template()."../ochart/assets/css/chart.css" ?>>
        
        <div class="row mx-0" style="min-height: calc(100vh - 65px);">
          <div class="col p-3 overflow-auto card shadow" id="dashboard" style="background-color: transparent;"></div>
          <div
            id="sidebar"
            class="col-1 py-3 px-0 d-flex flex-column gap-3 bg-primary-subtle"
            style="width: 40px; min-height: calc(100vh - 64px);"
          >
            <div id="headerSidebar" class="d-flex flex-column gap-3 w-100">
              <svg
                id="leftArrow"
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                fill="currentColor"
                class="bi bi-chevron-double-left mx-auto"
                viewBox="0 0 16 16"
                style="display: block; cursor: pointer"
                onclick="$.PChart.maximizeSidebar()"
              >
                <path
                  fill-rule="evenodd"
                  d="M8.354 1.646a.5.5 0 0 1 0 .708L2.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"
                />
                <path
                  fill-rule="evenodd"
                  d="M12.354 1.646a.5.5 0 0 1 0 .708L6.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"
                />
              </svg>
              <svg
                id="rightArrow"
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                fill="currentColor"
                class="bi bi-chevron-double-right mx-auto"
                viewBox="0 0 16 16"
                style="display: none; cursor: pointer"
                onclick="$.PChart.minimizeSidebar()"
              >
                <path
                  fill-rule="evenodd"
                  d="M3.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L9.293 8 3.646 2.354a.5.5 0 0 1 0-.708"
                />
                <path
                  fill-rule="evenodd"
                  d="M7.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L13.293 8 7.646 2.354a.5.5 0 0 1 0-.708"
                />
              </svg>
              <h5 id="titleSidebar" class="m-auto" style="writing-mode: vertical-lr;">
                Select Chart
              </h5>
            </div>
          </div>
        </div>
      <?
    }
    
    function LoadPreview() {
      ?>
        <link rel=stylesheet type=text/css href=<?= $this->base_template()."../ochart/assets/css/chart.css" ?>>

        <div class="row">
          <div class="col-12 u-margin-t-10">
            <div class="tabs-box panel-frame tabs_roles_0">
              <div id="containerChart" class="tabs-content chart-tab" style="text-align:left;"></div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      <?
    }

    function connectionDB($dbname) {
      $db = get_db_config();
      $db = array_map(function($item) {
        return $item[ENVIRONMENT];
      }, $db);
      $db = array_column($db, null, 'database');

      $dataDB = (object) $db[$dbname];
      $dataDB->name = $dataDB->database;
      $dataDB->dbname = $dataDB->database;
      $dataDB->dbserver = $dataDB->hostname;
      
      $this->load->database($dataDB);
    }

    function options($SELF, $breadcrumb) {
      $option = array();
      $option['master']  = '#bodymaster';
      $option['slave']   = $this->site_url().$this->uri->uri_string."_slave";
      $option['getpage'] = 'switcher';
      $option['type']    = '';
      $option['javascript'] = array(
        array(
          'src' => $this->base_url().'js/'.$SELF.'.js?version='.VERSION,
          'type' => 'text/javascript'
        ),
        array(
          'src' => $this->base_template().'../ochart/assets/js/tool_chart.js?v='.VERSION,
          'type' => 'text/javascript',
        ),
        array(
          'src' => $this->base_template().'../ochart/assets/js/tool_chartGenerator.js?v='.VERSION,
          'type' => 'text/javascript',
        ),
        array(
          'src' => $this->base_template().'../ochart/assets/js/bootstrap.bundle.min.js',
          'type' => 'text/javascript'
        ),
        array(
          'src' => $this->base_template().'../ochart/assets/js/chart.umd.min.js',
          'type' => 'text/javascript'
        )
      );

      $d = array();
      $d['title'] = "Create New Page of Chart";
      $d['slave'] = "new";
      $d['text'] = "new";
      $d['width'] = "300px";
      $d['window'] = "center";
      $d['event']['click'] = "newAction";
      $d['show'] = true;
      $d['isEnable'] = true;
      $option['buatbaru'] = $d;

      $d = array();
      $d['title'] = "List Data";
      $d['text'] = "List Data";
      $d['show'] = true;
      $d['isEnable'] = true;
      $option['listdata'] = $d;

      $d = array();
      $d['title'] = "filter";
      $d['slave'] = "filter";
      $d['text'] = "filter";
      $d['width'] = "300px";
      $d['show'] = false;
      $d['isEnable'] = false;
      $option['filter'] = $d;

      $option['breadcrumb']['title'] = $breadcrumb;
      $option['excel']['show'] = false;
      $option['pdf']['show'] = false;
      $option['csv']['show'] = false;
      $option['fixHeader']['show'] = false;
      $option['actions'] = array();
      $option['pathinfo']['site_url'] = $this->site_url();
      $option['pathinfo']['base_url'] = $this->base_url();

      $OPT = json_encode($option);

      return $OPT;
    }
  }
