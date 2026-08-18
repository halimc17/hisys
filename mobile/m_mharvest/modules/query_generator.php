<?
  defined('BASEPATH') OR exit('No direct script access allowed');

  class Query_generator extends OWL_Controller {
    public $lib, $uri, $toolsReport, $toolsReportPar, $toolParam;

    public function __construct() {
      parent::__construct();

      $this->load->model('Prc_tool_userdefinedreport', 'toolsReport');
      $this->load->model('Prc_tool_userdefinedreport_par', 'toolsReportPar');
      $this->load->model('Prc_tool_php_encode', 'toolParam');
    }

    function slave() {
      switch($this->get('switcher')) {
        case 'load':
          $rnumber = $this->get('rnumber');
          $input = $this->post('frmparam');
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
          for ($i = 0; $i < count($data); $i++) {
            $data[$i] = array_values($data[$i]);
          }
          $kolomTampil = explode(',', $qc[0]['kolomtampil']);

          echo json_encode([
            'rows' => $data,
            'columns' => $kolomTampil
          ]);
        break;
        case 'getDatabases':
          $db = get_db_config();
          $db = array_map(function($item) {
            return $item[ENVIRONMENT]['database'];
          }, $db);
          $databases = array_unique(array_values($db));

          echo json_encode($databases);
        break;
        case 'getTables':
          $db = $this->get('db');
          $this->connectionDB($db);
          $tables = $this->toolsReport->getTables($db);
          echo json_encode($tables);
        break;
        case 'getFields':
          $db = $this->get('db');
          $this->connectionDB($db);
          $tableName = $this->get('tablename');
          
          if ($tableName != '') {
            $fields = $this->toolsReport->getColumns($db, $tableName);

            echo json_encode($fields);
          }
        break;
        case 'new':
          $this->loadNew();
        break;
        case 'sendData':
          $params = $this->toolsReportPar->get("WHERE rnumber = '".$this->get('id')."'");
          
          echo json_encode($params);
        break;
        case 'view':
          $this->LoadView($this->get('id'));
        break;
        case 'publish':
          $this->toolsReport->updateStatus(1, $this->get('id'));
        break;
        case 'unpublish':
          $this->toolsReport->updateStatus(0, $this->get('id'));
        break;
        case 'delete':
          $this->toolsReport->updateStatus(2, $this->get('id'));
        break;
        case 'preview':
          $format = $this->get('format');
          $operator = $this->get('operator');
          $field = $this->get('field');
          $tableList = $this->get('table');
          $join = $this->get('join');
          $column = $this->get('kolomTampil');
          $selectColumn = $this->get('kolomSelect');
          $group = $this->get('grouping');
          $subtotal = $this->get('subtotal');
          $order = $this->get('order');
          $dbname = $this->get('dbname');
          $sort = $this->get('sort');

          $this->LoadPreview($format, $operator, $field, $tableList, $join, $column, $selectColumn, $group, $subtotal, $order, $dbname, $sort);
        break;
        case 'save':
          $title = $this->get('judul');
          $selectColumn = explode('|', html_entity_decode(urldecode($this->get('kolomSelect')), ENT_QUOTES));
          $tableList = explode(',', $this->get('table'));
          $join = explode(',', $this->get('join'));
          $dbname = $this->get('dbname');
          $group = explode('|', $this->get('grouping'));
          $column = explode('|', $this->get('kolomTampil'));
          $subtotal = explode('|', $this->get('subtotal'));
          $order = explode('|', $this->get('order'));
          $sort = $this->get('sort');
          $field = explode(',', $this->get('field'));
          $format = explode(',', $this->get('format'));
          $operator = explode(',', html_entity_decode($this->get('operator')));

          // get last rnumber
          $num = $this->toolsReport->getLastRnumber();
          $num++;

          // prepare select column
          $select = '';
          for ($i = 0; $i < count($selectColumn); $i++) {
            if ($i == 0) {
              $select .= $selectColumn[$i].' AS '.$column[$i];
            } else {
              $select .= ', '.$selectColumn[$i].' AS '.$column[$i];
            }
          }

          // prepare table
          $mainTable = $tableList[0];
          array_shift($tableList);

          $joinTable = '';
          if (count($tableList) > 0) {
            foreach ($tableList as $tbl) {
              $joinTable .= " LEFT JOIN ".$dbname.".".$tbl;
              $i = 0;
              foreach ($join as $j) {
                $tableName = explode('=', $j);
                $tabName = explode('.', $tableName[1]);
                if ($tbl == $tabName[0]) {
                  if ($i == 0) {
                    $joinTable .= " ON ".$j;
                    $i++;
                  } else {
                    $joinTable .= " AND ".$j;
                  }
                }
              }
            }
          }

          // prepare group
          $grouped = '';
          $ig = 0;
          for ($i = 0; $i < count($group); $i++) {
            if ($group[$i] == '1') {
              if ($ig == 0) {
                $grouped .= $column[$i];
                $ig++;
              } else {
                $grouped .= ', '.$column[$i];
              }
            }
          }

          if ($grouped != '') {
            $grouped = " GROUP BY ".$grouped;
          }

          // prepare subtotal
          if (in_array('1', $subtotal)) {
            $grouped .= ' WITH ROLLUP';
          }

          // prepare order
          $ordered = '';
          $io = 0;
          for ($i = 0; $i < count($order); $i++) {
            if ($order[$i] == '1') {
              if ($io == 0) {
                $ordered = $column[$i];
                $io++;
              } else {
                $ordered .= ', '.$column[$i];
              }
            }
          }

          if ($ordered != '') {
            $ordered = " ORDER BY ".$ordered;
          }

          if ($sort == 'DESC') {
            $ordered .= " DESC";
          } else if ($sort == 'ASC') {
            $ordered .= " ASC";
          }

          // prepare query
          $query = "SELECT ".$select." FROM ".$dbname.".".$mainTable.$joinTable." #PARAMETER#".$grouped.$ordered;
          // Hilangkan BOM/zero-width dan ubah semua whitespace "aneh" jadi spasi biasa
          $query = preg_replace('/\x{FEFF}|\x{200B}|\x{200C}|\x{200D}/u', '', $query); // zero-width
          $query = preg_replace('/\p{Z}+/u', ' ', $query); // semua separator Unicode → spasi biasa
          $query = trim($query);
          // (opsional) rapikan spasi berlebih
          $query = preg_replace('/\s+/', ' ', $query);
          
          $dataInsert = array(
            'rnumber' => $num,
            'namalaporan' => $title,
            'query' => $query,
            'dbname' => $dbname,
            'createdate' => date('Y-m-d'),
            'owner' => $_SESSION['standard']['username'],
            'status' => 0,
            'kolomtampil' => implode(',', $column),
            '`group`' => implode(',', $group),
            'subtotal' => implode(',', $subtotal),
            '`order`' => implode(',', $order),
            'sort' => $sort
          );

          $insertData = $this->toolsReport->insertTable($dataInsert);
          if ($insertData) {
            for ($i = 0; $i < count($field); $i++) {
              $dataInsertPar = array(
                'rnumber' => $num,
                'kolom' => $field[$i],
                'value' => $format[$i],
                'operator' => $operator[$i]
              );

              $insertDataPar = $this->toolsReportPar->insertTable($dataInsertPar);
              if (!$insertDataPar) {
                echo json_encode([
                  'status' => 'error',
                  'message' => 'Failed to save parameter'
                ]);
                break;
              }
            }
          } else {
            echo json_encode([
              'status' => 'error',
              'message' => 'Failed to save configuration'
            ]);
            break;
          }
          
          echo json_encode([
            'status' => 'success',
            'message' => 'Configuration saved successfully'
          ]);
        break;
        case 'getDataPreview':
          $input = $this->post('frmparam');
          $format = explode(',', $this->get('format'));
          $operator = explode(',', html_entity_decode($this->get('operator')));
          $field = explode(',', $this->get('field'));
          $tableList = explode(',', $this->get('table'));
          $join = explode(',', $this->get('join'));
          $column = explode('|', $this->get('kolomTampil'));
          $selectColumn = explode('|', html_entity_decode(urldecode($this->get('kolomSelect')), ENT_QUOTES));
          $group = explode('|', $this->get('grouping'));
          $subtotal = explode('|', $this->get('subtotal'));
          $order = explode('|', $this->get('order'));
          $dbname = $this->get('dbname');
          $sort = $this->get('sort');
          if (!empty($input)) {
            foreach ($input as &$item) {
              $item = html_entity_decode($item);
            }
          }

          // prepare select column
          $select = "";
          for ($i = 0; $i < count($selectColumn); $i++) {
            if ($i == 0) {
              $select .= $selectColumn[$i]." AS ".$column[$i];
            } else {
              $select .= ", ".$selectColumn[$i]." AS ".$column[$i];
            }
          }

          // prepare table
          $mainTable = $tableList[0];
          array_shift($tableList);

          $joinTable = '';
          if (count($tableList) > 0) {
            foreach ($tableList as $tbl) {
              $joinTable .= " LEFT JOIN ".$dbname.".".$tbl;
              $i = 0;
              foreach ($join as $j) {
                $tableName = explode('=', $j);
                $tabName = explode('.', $tableName[1]);
                if ($tbl == $tabName[0]) {
                  if ($i == 0) {
                    $joinTable .= " ON ".$j;
                    $i++;
                  } else {
                    $joinTable .= " AND ".$j;
                  }
                }
              }
            }
          }

          // prepare parameter
          $parameter = '';
          for ($i = 0; $i < count($field); $i++) {
            if ($field[$i] == '') {
              continue;
            }

            if ($i > 0) {
              $parameter .= ' AND ';
            }

            // Evaluate PHP expressions for Setup parameters in a general way.
            if ($format[$i] == 'Setup') {
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

            if ($format[$i] != 'Number' &&  $operator[$i] != 'LIKE' &&  $operator[$i] != 'NOT LIKE') {
              $input[$i] = "'".$input[$i]."'";
              if ( $operator[$i] == 'BETWEEN' ||  $operator[$i] == 'NOT BETWEEN') {
                $input[$i+1] = "'".$input[$i+1]."'";
              }
            }

            if ( $operator[$i] == 'LIKE' ||  $operator[$i] == 'NOT LIKE') {
              $parameter .= $field[$i]." ". $operator[$i]." '%".$input[$i]."%'";
            } else if ( $operator[$i] == 'BETWEEN' ||  $operator[$i] == 'NOT BETWEEN') {
              $parameter .= "(".$field[$i]." ". $operator[$i]." ".$input[$i]." AND ".$input[$i+1].")";
              array_splice($input, $i+1, 1);
            } else if ( $operator[$i] == 'IS NULL' ||  $operator[$i] == 'IS NOT NULL') {
              $parameter .= $field[$i]." ". $operator[$i];
            } else if ( $operator[$i] == 'IN' ||  $operator[$i] == 'NOT IN') {
              $parameter .= $field[$i]." ". $operator[$i]." (".$input[$i].")";
            } else {
              $parameter .= $field[$i]." ". $operator[$i]." ".$input[$i];
            }
          }

          if ($parameter != '') {
            $parameter = " WHERE ".$parameter;
          }

          // prepare group
          $grouped = '';
          $ig = 0;
          for ($i = 0; $i < count($group); $i++) {
            if ($group[$i] == '1') {
              if ($ig == 0) {
                $grouped .= $column[$i];
                $ig++;
              } else {
                $grouped .= ', '.$column[$i];
              }
            }
          }

          if ($grouped != '') {
            $grouped = " GROUP BY ".$grouped;
          }

          // prepare subtotal
          if (in_array('1', $subtotal)) {
            $grouped .= ' WITH ROLLUP';
          }

          // prepare order
          $ordered = '';
          $io = 0;
          for ($i = 0; $i < count($order); $i++) {
            if ($order[$i] == '1') {
              if ($io == 0) {
                $ordered = $column[$i];
                $io++;
              } else {
                $ordered .= ', '.$column[$i];
              }
            }
          }

          if ($ordered != '') {
            $ordered = " ORDER BY ".$ordered;
          }

          if ($sort == 'DESC') {
            $ordered .= " DESC";
          } else if ($sort == 'ASC') {
            $ordered .= " ASC";
          }

          // prepare query
          $query = "SELECT ".$select." FROM ".$dbname.".".$mainTable.$joinTable.$parameter.$grouped.$ordered;
          // Hilangkan BOM/zero-width dan ubah semua whitespace "aneh" jadi spasi biasa
          $query = preg_replace('/\x{FEFF}|\x{200B}|\x{200C}|\x{200D}/u', '', $query); // zero-width
          $query = preg_replace('/\p{Z}+/u', ' ', $query); // semua separator Unicode → spasi biasa
          $query = trim($query);
          // (opsional) rapikan spasi berlebih
          $query = preg_replace('/\s+/', ' ', $query);

          // get data
          $this->connectionDB($dbname);
          $data = $this->toolsReport->getData($query);
          for ($i = 0; $i < count($data); $i++) {
            $data[$i] = array_values($data[$i]);
          }

          echo json_encode([
            'query' => $query,
            'rows' => $data,
            'columns' => $column
          ]);
        break;
        case 'getSetupParams':
          $params = $this->toolParam->get();
          echo json_encode($params);
        break;
        case 'edit':
          $rnumber = $this->get('id');

          $data = $this->toolsReport->get("WHERE rnumber = '".$rnumber."'");
          $param = $this->toolsReportPar->get("WHERE rnumber = '".$rnumber."'");

          echo json_encode([
            'report' => $data,
            'parameters' => $param
          ]);
        break;
        default:
          try {
            //get request halaman
            $page = ((int)$this->get('page') == 0) ? 1 : (int)$this->get('page');
            // get user session
            $user = $_SESSION['standard']['username'];
            // get data from database
            $getData = $this->toolsReport->get("WHERE owner = '".$user."' AND status <> 2", "ORDER BY rnumber");
            // set table and pagination
            $this->load->lib('Pagination', 'pagination');
            $table = $this->lib->pagination;
            $table->id = 'tableReportList';
            if ($getData) {
              $table->total_rows = count($getData);
            } else {
              $table->total_rows = 0;
            }
            $table->per_page = 20;
            $table->cur_page = $page;
            $starting_limit = ($page - 1) * $table->per_page;
            $dataTable = $this->dataList(
              $this->toolsReport->listData(
                [$starting_limit, $table->per_page],
                "WHERE owner = '".$user."' AND status <> 2",
                "ORDER BY rnumber"
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

    function loadNew() {
      ?>
        <div class="bootstrap-scope">
          <div class="card mb-3">
            <div class="card-header">Step 1: Select Database and Tables</div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <label for="dbList" class="form-label">Database</label>
                  <div id="dbListContainer"></div>
                </div>
              </div>
              <hr>
              <div id="table-container">
                <div class="row mb-3 table-row" id="table-row-1">
                  <div class="col-md-4">
                    <label for="tableList1" class="form-label">From Table</label>
                    <div id="tableListContainer">
                      <select id="tableList1" class="form-select p-1 pt-0" style="font-size: 12px;" disabled>
                        <option value="">Select a database first</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label">
                      Fields <a onclick="$.QGen.showById('table1')" title='Maximize'>+</a> / <a onclick='$.QGen.hideById("table1")' title='Minimize'>-</a>
                    </label>
                    <div id="table1" class="p-2 border rounded bg-light field-container"></div>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-between">
                <button class="btn btn-secondary btn-sm" style="font-size: 12px;" id="btNew" onclick='$.QGen.addNewRow()' disabled>
                  <i class="fa fa-plus"></i> Add Join
                </button>
                <div>
                  <button class="btn btn-primary btn-sm" style="font-size: 12px;" id="btConfig" onclick='$.QGen.configureColumn()'>
                    <i class="fa fa-cogs"></i> Configure Columns & Parameters
                  </button>
                  <button class='btn btn-danger btn-sm' style="font-size: 12px;" id='btReset' onclick='reset()'>
                    <i class="fa fa-sync-alt"></i> Reset All
                  </button>
                </div>
              </div>
            </div>
          </div>
  
          <div class="card mb-3 w-100" id="columnControl" style="display:none; cursor:default; background-color: white !important; background: white !important; border: 1px solid #0000002d; box-shadow: 0 0 10px #fff;">
            <div class="card-header">Step 2: Build Your Report</div>
            <div class="card-body">
              <div style='width: 100%; cursor:default;' class="border-0">
                <div class="pb-3">
                  <div id="columnCollectorLabel" class="d-flex justify-content-between">
                    <label class="form-label fw-bold">Column Collector (Drag fields here)</label>
                  </div>
                  <div id='columnList' class='p-3 border rounded bg-light column-collector d-flex flex-wrap gap-1' ondrop='$.QGen.drop(event); $.QGen.generateParameter();' ondragover='$.QGen.allowDrop(event);'></div>
                </div>
                <div class="pb-3">
                  <label class="form-label fw-bold">Report Title</label>
                  <input type='text' id='judul' class="form-control" onkeypress='return tanpa_kutip(event);' placeholder='New Report Title' value=''>
                </div>
                <div id="funcOpr" class="row pb-3 d-flex gap-"></div>
                <div class="pb-3">
                  <label class="form-label fw-bold">Column Display Options</label>
                  <div id='caption' class='p-2 border rounded bg-light'></div>
                </div>
                <div class="pb-3">
                  <label class="form-label fw-bold">Parameters & Conditions (WHERE Clause)</label>
                  <div id='condition' class='p-2 border rounded bg-light'></div>
                </div>
                <div class="card-footer text-end bg-white">
                  <button class='btn btn-info btn-sm' style='font-size: 12px;' onclick='$.QGen.previewQuery();'>
                    <i class="fa fa-eye"></i> Preview
                  </button>
                  <button class='btn btn-danger btn-sm' style='font-size: 12px;' onclick='$.QGen.configureColumn();'>
                    <i class="fa fa-trash"></i> Reset
                  </button>
                  <button class='btn btn-success btn-sm' style='font-size: 12px;' onclick='$.QGen.saveConfig();'>
                    <i class="fa fa-save"></i> Save
                  </button>
                  <button class='btn btn-secondary btn-sm' style='font-size: 12px;' onclick='hideById("columnControl");'>
                    <i class="fa fa-times"></i> Close
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?
    }

    function dataList($data = array(), $no) {
      $thead = "
        <thead>
          <tr class='rowheader'>
            <th align='center'>No</th>
            <th align='center'>Report Title</th>
            <th align='center'>Database Name</th>
            <th align='center'>Created Date</th>
            <th align='center'>Created By</th>
            <th align='center'>Status</th>
          </tr>
        </thead>
      ";
      
      $table = "";
      $uriPublish = "?switcher=publish";
      $uriUnpublish = "?switcher=unpublish";
      $uriDelete = "?switcher=delete";
      // $uriEdit = "?switcher=edit";

      if (count($data) > 0) {
        foreach ($data as $key => $value) {
          $no++;
          $action = array();
          $action['view'] = "viewAction('&id={$value['rnumber']}', '{$value['namalaporan']}');";

          if ($value['status'] == '0') {
            $action['publish'] = "publishAction('{$uriPublish}&id={$value['rnumber']}');";
            // $action['edit'] = "editAction('{$uriEdit}&id={$value['rnumber']}');";
            $action['delete'] = "deleteAction('{$uriDelete}&id={$value['rnumber']}');";
          } else if ($value['status'] == '1') {
            $action['unpublish'] = "unpublishAction('{$uriUnpublish}&id={$value['rnumber']}');";
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
              <td align='left'>".$value['namalaporan']."</td>
              <td align='center'>".$value['dbname']."</td>
              <td align='center'>".$value['createdate']."</td>
              <td align='center'>".$value['owner']."</td>
              <td align='center'>".$currentStatusText."</td>
            </tr>
          ";
        }
      } else {
        $table .= "
          <tr>
            <td align='center' colspan='6'>No Data</td>
          </tr>
        ";
      }
      
      $result['head'] = $thead;
      $result['body'] = $table;

      return (object)$result;
    }

    function LoadView($rnumber = '') {
      ?>
        <div class="bootstrap-scope">
          <div class="container row">
            <div class="col-12">
              <div class="card shadow">
                <div class="card-header">
                  <form
                    id="paramForm"
                    method="post"
                    action="<?= $this->site_url().$this->uri->uri_string.'_slave?switcher=load&rnumber='.$rnumber ?>"
                    callback="callbackPreview"
                  >
                    <div id="listParam" class="mb-3"></div>
                    <input class="btn btn-sm btn-primary" type="submit" value="View Data" style="font-size: 12px; float: right;">
                  </form>
                </div>
                <div class="card-body">
                  <div id="reportOutput" class="table-responsive"></div>
              </div>
            </div>
          </div>
        </div>
      <?
    }

    function LoadPreview($format, $operator, $field, $table, $join, $column, $selectColumn, $group, $subtotal, $order, $dbname, $sort) {
      ?>
        <div class="bootstrap-scope">
          <div class="container row">
            <div class="col-12">
              <div class="card shadow">
                <div class="card-header">
                  <form
                    id="paramForm"
                    method="post"
                    action="<?= $this->site_url().$this->uri->uri_string.'_slave?switcher=getDataPreview&format='.$format.'&operator='.$operator.'&field='.$field.'&table='.$table.'&join='.$join.'&kolomTampil='.$column.'&kolomSelect='.$selectColumn.'&grouping='.$group.'&subtotal='.$subtotal.'&order='.$order.'&dbname='.$dbname.'&sort='.$sort ?>"
                    callback="callbackPreview"
                  >
                    <div id="listParam" class="mb-3"></div>
                    <input class="btn btn-sm btn-primary" type="submit" value="Preview Data" style="font-size: 12px; float: right;">
                  </form>
                </div>
                <div class="card-body">
                  <div id="reportOutput" class="table-responsive"></div>
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
      $option['master']     = '#bodymaster';
      $option['slave']      = $this->site_url().$this->uri->uri_string."_slave";
      $option['getpage']    = 'switcher';
      $option['type']       = '';
      $option['javascript'] = array(
        array(
          'src'=>$this->base_template().'../ochart/assets/js/tool_QueryGenerator.js?v='.VERSION,
          'type'=>'text/javascript',
        ),
        array(
          'src'=>$this->base_url().'js/'.$SELF.'.js?version='.VERSION,
          'type'=>'text/javascript',
        )
      );

      $d = array();
      $d['title'] = "Create New Query";
      $d['slave'] = "new";
      $d['text'] = "new";
      $d['width'] = "300px";
      $d['window'] = "center";
      $d['event']['click'] = "newAction";
      $d['show'] = TRUE;
      $d['isEnable'] = TRUE;
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

      $OPT =  json_encode($option);

      return $OPT;
    }
  }
