<?
  defined('BASEPATH') OR exit('No direct script access allowed');

  class Chart_generator extends OWL_Controller {
    public $lib, $uri, $chart, $toolsReport, $toolsReportPar, $toolParam;

    public function __construct() {
      parent::__construct();

      $this->load->model('Prc_tool_chart', 'chart');
      $this->load->model('Prc_tool_php_encode', 'toolParam');
      $this->load->model('Prc_tool_userdefinedreport', 'toolsReport');
      $this->load->model('Prc_tool_userdefinedreport_par', 'toolsReportPar');
      
      $this->load->lib('Pagination', 'pagination');
    }

    function slave() {
      switch($this->get('switcher')) {
        case 'save':
          $dataInsert = array(
            'rnumber' => $this->get('rnumber'),
            'nama' => $this->get('title'),
            'kolomlabel' => $this->get('kolomLabel'),
            'kolomdata' => $this->get('kolomData'),
            'operation' => $this->get('operation'),
            'type' => $this->get('type'),
            'version' => '1.0',
            'status' => 0
          );

          $insertData = $this->chart->insertTable($dataInsert);
          if ($insertData) {
            echo json_encode([
              'status' => 'success',
              'message' => 'Chart successfully saved'
            ]);
          } else {
            echo json_encode([
              'status' => 'error',
              'message' => 'Failed to save chart'
            ]);
          }
        break;
        case 'parameter':
          $parameters = $this->toolsReportPar->get("WHERE rnumber = '".$this->get('rnumber')."'");

          echo json_encode($parameters);
        break;
        case 'new':
          $user = $_SESSION['standard']['username'];
          $listTable = $this->toolsReport->get("WHERE owner = '".$user."' AND status = 1", "ORDER BY rnumber");

          $this->loadNew($listTable);
        break;
        case 'load':
          $rnumber = $this->get('rnumber');
          $input = $this->post('parameters');
          foreach ($input as &$item) {
            $item = html_entity_decode($item);
          }

          $param = $this->toolsReportPar->get("WHERE rnumber = '".$rnumber."'");

          $parameter = '';
          for ($i = 0; $i < count($param); $i++) {
            if ($i > 0) {
              $parameter .= ' AND ';
            }

            // Evaluate PHP expressions for Setup parameters in a general way.
            if ($param[$i]['value'] == 'Setup') {
              // Check if input appears to be a PHP script (having parentheses or a $_SESSION variable)
              // Remove quotes that might have been added by the client
              $cleanInput = trim($input[$i], "'\"");

              // Evaluate the PHP expression
              try {
                // Start capturing any unwanted output (if needed)
                ob_start();
                $evalResult = null;
                eval('$evalResult = '.$cleanInput.';');
                ob_end_clean();
                $input[$i] = $evalResult;
              } catch (Exception $e) {
                // If evaluation fails, use the original input and optionally log the error
              }
            }

            if ($param[$i]['value'] != 'Number' && $param[$i]['operator'] != 'LIKE' && $param[$i]['operator'] != 'NOT LIKE') {
              $input[$i] = "'".$input[$i]."'";
              if ($param[$i]['operator'] == 'BETWEEN' || $param[$i]['operator'] == 'NOT BETWEEN') {
                $input[$i+1] = "'".$input[$i+1]."'";
              }
            }

            if ($param[$i]['operator'] == 'LIKE' || $param[$i]['operator'] == 'NOT LIKE') {
              $parameter .= $param[$i]['kolom']." ".$param[$i]['operator']." '%".$input[$i]."%'";
            } else if ($param[$i]['operator'] == 'BETWEEN' || $param[$i]['operator'] == 'NOT BETWEEN') {
              $parameter .= "(".$param[$i]['kolom']." ".$param[$i]['operator']." ".$input[$i]." AND ".$input[$i+1].")";
              array_splice($input, $i+1, 1);
            } else if ($param[$i]['operator'] == 'IS NULL' || $param[$i]['operator'] == 'IS NOT NULL') {
              $parameter .= $param[$i]['kolom']." ".$param[$i]['operator'];
            } else if ($param[$i]['operator'] == 'IN' || $param[$i]['operator'] == 'NOT IN') {
              $parameter .= $param[$i]['kolom']." ".$param[$i]['operator']." (".$input[$i].")";
            } else {
              $parameter .= $param[$i]['kolom']." ".$param[$i]['operator']." ".$input[$i];
            }
          }

          $qc = $this->toolsReport->getQueryColumn($rnumber);
          $query = str_replace('#PARAMETER#', 'WHERE '.$parameter, $qc[0]['query']);
          
          $this->connectionDB($qc[0]['dbname']);

          $data = $this->toolsReport->getData($query);
          $kolomTampil = explode(',', $qc[0]['kolomtampil']);

          if ($id = $this->get('id')) {
            echo json_encode([
              'query' => $query,
              'id' => $id,
              'rnumber' => $rnumber,
              'rows' => $data,
              'columns' => $kolomTampil
            ]);
            break;
          } else {
            echo json_encode([
              'rnumber' => $rnumber,
              'rows' => $data,
              'columns' => $kolomTampil
            ]);
            break;
          }
        break;
        case 'view':
          $this->LoadPreview($this->get('id'), $this->get('rnumber'));
        break;
        case 'publish':
          $this->chart->updateStatus(1, $this->get('id'));
        break;
        case 'unpublish':
          $this->chart->updateStatus(0, $this->get('id'));
        break;
        case 'delete':
          $this->chart->updateStatus(2, $this->get('id'));
        break;
        case 'data':
          echo json_encode($this->chart->get("WHERE id = '".$this->get('id')."'"));
        break;
        case 'getSetupParams':
          $params = $this->toolParam->get();
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
            $getData = $this->chart->get("WHERE status <> 2 AND rnumber IN (".implode(',', $rnumber).") ORDER BY id");
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
              $this->chart->listData(
                [$starting_limit, $table->per_page],
                "WHERE status <> 2 AND rnumber IN (".implode(',', $rnumber).") ORDER BY id"
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
            <th align='center'>Chart Title</th>
            <th align='center'>Chart Type</th>
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
          $action['view'] = "viewAction('&id={$value['id']}&rnumber={$value['rnumber']}', '{$value['nama']}');";

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
              <td align='left'>".$value['nama']."</td>
              <td align='center'>".$value['type']."</td>
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

    function loadNew($tableList) {
      $chartTypes = [
        "area" => [
          "filename" => "area-chart",
          "label" => "Area Chart"
        ],
        "bar" => [
          "filename" => "bar-chart",
          "label" => "Bar Chart"
        ],
        "card" => [
          "filename" => "card",
          "label" => "Card"
        ],
        "column" => [
          "filename" => "column-chart",
          "label" => "Column Chart"
        ],
        "doughnut" => [
          "filename" => "doughnut-chart",
          "label" => "Doughnut Chart"
        ],
        "line" => [
          "filename" => "line-chart",
          "label" => "Line Chart"
        ],
        "line-column" => [
          "filename" => "line-column-chart",
          "label" => "Line Column Chart"
        ],
        "line-stacked-column" => [
          "filename" => "line-stacked-column-chart",
          "label" => "Line Stacked Column Chart"
        ],
        "pie" => [
          "filename" => "pie-chart",
          "label" => "Pie Chart"
        ],
        "polar" => [
          "filename" => "polar-chart",
          "label" => "Polar Chart"
        ],
        "radar" => [
          "filename" => "radar-chart",
          "label" => "Radar Chart"
        ],
        "scatter" => [
          "filename" => "scatter-chart",
          "label" => "Scatter Chart"
        ],
        'stacked-area' => [
          'filename' => 'stacked-area-chart',
          'label' => 'Stacked Area Chart'
        ],
        'stacked-bar' => [
          'filename' => 'stacked-bar-chart',
          'label' => 'Stacked Bar Chart'
        ],
        'stacked-column' => [
          'filename' => 'stacked-column-chart',
          'label' => 'Stacked Column Chart'
        ],
        'table' => [
          'filename' => 'table',
          'label' => 'Table'
        ]
      ];

      ?>
        <link rel=stylesheet type=text/css href=<?= $this->base_template()."../ochart/assets/css/chartGenerator.css" ?>>
        
        <div class="body-canvas">
          <div class="d-flex h-100">
            <div class="w-75">
              <canvas id="myChart"></canvas>
            </div>
      
            <div class="w-25 bg-primary-subtle overflow-auto">
              <div class="accordion accordion-flush" id="accordionFlush">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button
                      id="btnSelectTable"
                      class="accordion-button"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#flush-collapseOne"
                      aria-expanded="true"
                      aria-controls="flush-collapseOne"
                    >
                      Select Table
                    </button>
                  </h2>
                  <div id="flush-collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionFlush">
                    <div class="accordion-body">
                      <? if (count($tableList) == 0) { ?>
                        <b>No data available</b>
                        <p>Please create a new table first in the Query Generator menu</p>
                      <? } ?>
                      <ul class="list-group">
                        <? foreach ($tableList as $key => $value) { ?>
                          <li class="form-check">
                            <input
                              class="form-check-input"
                              type="radio"
                              name="flexRadio"
                              id=<?= "flexRadio".$value['rnumber']; ?>
                              onclick="$.CGen.generateParameter(<?= htmlspecialchars(json_encode($value)); ?>);"
                            >
                            <label class="form-check-label" for=<?= "flexRadio".$value['rnumber']; ?>>
                              <?= $value['namalaporan']; ?>
                            </label>
                          </li>
                        <? } ?>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button
                      id="btnInputParam"
                      class="accordion-button collapsed"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#flush-collapseTwo"
                      aria-expanded="false"
                      aria-controls="flush-collapseTwo"
                    >
                      Input Paramater
                    </button>
                  </h2>
                  <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
                    <div class="accordion-body">
                      <p id='errorParam'>Select Table First</p>
                      <form
                        id="listParam"
                        class="lead-frame row"
                        method="POST"
                        action="<?= $this->site_url().$this->uri->uri_string.'_slave?switcher=load' ?>"
                        callback="callbackParam"
                        style="display: none;"
                      >
                        <div id="btnSubmit" class="col-12 u-margin-b-10">
                          <input class="btn btn-secondary btn-sm" type="submit" value="Submit">
                        </div>
                      </form>
                      <div class="clearfix"></div>
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                      Setup Chart
                    </button>
                  </h2>
                  <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
                    <div id="errorChart" class="accordion-body">Select table and input parameter first</div>
                    <div id='menu' class="accordion-body" style="display: none;">
                      <h3 class="p-3">Chart Types</h3>
                      <div class="d-flex flex-wrap">
                        <?php foreach ($chartTypes as $key => $value) { ?>
                          <button
                            id='btn<?= $key ?>'
                            type="button"
                            class="btn m-2"
                            data-bs-toggle = "button"
                            onclick="$.CGen.generateMenu('<?= $key ?>')"
                          >
                            <img src="<?= $this->base_template()."../ochart/assets/images/".$value['filename'] ?>.png" alt="<?= $value['label'] ?>" title="<?= $value['label'] ?>">
                          </button>
                        <?php } ?>
                      </div>
                      <hr>
                    </div>
                  </div>
                </div>
              </div>
            </div>
      
            <!-- Modal -->
            <div class="modal fade" id="saveModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="saveModalLabel">Save Chart</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <label for="recipient-name" class="col-form-label">Chart title:</label>
                    <input
                      type="text"
                      class="form-control"
                      id="recipient-name"
                      name="title"
                      onchange="$.CGen.title = this.value"
                      required
                    >
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button
                      type="button"
                      class="btn btn-success"
                      onclick="$.CGen.save()"
                      data-bs-dismiss="modal"
                    >
                      Save
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?
    }

    function LoadPreview($id, $rnumber) {
      ?>
        <div class="bootstrap-scope">
          <div class="container row">
            <div class="col-12">
              <div class="card shadow">
                <div class="card-header">
                  <form
                    id="paramForm"
                    method="post"
                    action="<?= $this->site_url().$this->uri->uri_string.'_slave?switcher=load&id='.$id.'&rnumber='.$rnumber ?>"
                    callback="callbackView"
                  >
                    <div id="listParam" class="mb-3"></div>
                    <input class="btn btn-sm btn-primary" type="submit" value="View Chart" style="font-size: 12px; float: right;">
                  </form>
                </div>
                <div class="card-body">
                  <canvas id="myChart"></canvas>
                  <div class="clearfix"></div>
              </div>
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
          'src' => $this->base_template().'../ochart/assets/js/tool_chartGenerator.js?v='.VERSION,
          'type' => 'text/javascript',
        ),
        array(
          'src' => $this->base_url().'js/'.$SELF.'.js?version='.VERSION,
          'type' => 'text/javascript'
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
      $d['title'] = "Create New Chart";
      $d['slave'] = "new";
      $d['text'] = "new";
      $d['width'] = "300px";
      $d['window'] = "center";
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
