<?
  defined('BASEPATH') OR exit('No direct script access allowed');

  class Setup_parameter extends OWL_Controller {
    public $lib, $uri, $toolParam;

    public function __construct() {
      parent::__construct();
      
      $this->load->model('Prc_tool_php_encode', 'toolParam');
    }

    function slave() {
      switch($this->get('switcher')) {
        case 'new':
          $this->loadForm('new');
        break;
        case 'delete':
          $this->toolParam->deleteData("WHERE id = '".$this->get('id')."'");
        break;
        case 'edit':
          $data = $this->toolParam->get("WHERE id = '".$this->get('id')."'");
          $editData = (!empty($data) && is_array($data)) ? $data[0] : array();

          $this->loadForm('edit', $editData);
        break;
        case 'save':
          $name = $this->post('nama');
          $value = $this->post('nilai');
          if (empty($name) || empty($value)) {
            echo json_encode([
              'status' => 'error',
              'message' => 'Name and Value cannot be empty'
            ]);

            break;
          }
          
          $data = array(
            'name' => $name,
            'value' => html_entity_decode($value)
          );

          if ($this->get('type') == 'edit') {
            $id = $this->post('id');
            $updateData = $this->toolParam->updateTable($data, "id = '".$id."'");
            if (!$updateData) {
              echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update parameter'
              ]);

              break;
            }
          } else {
            $insertData = $this->toolParam->insertTable($data);
            if (!$insertData) {
              echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save parameter'
              ]);

              break;
            }
          }
          
          echo json_encode([
            'status' => 'success',
            'message' => 'Parameter saved successfully'
          ]);
        break;
        default:
          try {
            //get request halaman
            $page = ((int)$this->get('page') == 0) ? 1 : (int)$this->get('page');
            // get user session
            $user = $_SESSION['standard']['username'];
            // get data from database
            $getData = $this->toolParam->get("ORDER BY id DESC");
            // set table and pagination
            $this->load->lib('Pagination', 'pagination');
            $table = $this->lib->pagination;
            $table->id = 'tableParameter';
            if ($getData) {
              $table->total_rows = count($getData);
            } else {
              $table->total_rows = 0;
            }
            $table->per_page = 20;
            $table->cur_page = $page;
            $starting_limit = ($page - 1) * $table->per_page;
            $dataTable = $this->dataList(
              $this->toolParam->get("ORDER BY id DESC LIMIT {$starting_limit}, {$table->per_page}"),
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

    function loadForm($type, $data = array()) {
      ?>
        <div class="bootstrap-scope">
          <div class="container row">
            <div class="col-12">
              <div class="card shadow">
                <div class="card-body">
                  <form
                    id="nilaiForm"
                    method="POST"
                    action="<?= $this->site_url().$this->uri->uri_string.'_slave?switcher=save&type='.$type ?>"
                    callback="callAfterSubmit"
                  >
                    <div class="mb-3">
                      <label for="nama" class="form-label">Nama</label>
                      <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama" value="<?= isset($data['name']) ? $data['name'] : '' ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="nilai" class="form-label">Nilai</label>
                      <textarea class="form-control" placeholder="Masukkan nilai" id="nilai" name="nilai" onkeypress="return tanpa_kutip(event)" required><?= isset($data['value']) ? $data['value'] : '' ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                      <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                      <button type="reset" class="btn btn-secondary btn-sm">Reset</button>
                    </div>
                    <input type="hidden" name="id" value="<?= isset($data['id']) ? $data['id'] : '' ?>">
                  </form>
                  <div id="alertBox" class="alert mt-3 d-none"></div>
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
            <th align='center'>Name</th>
            <th align='center'>Value</th>
          </tr>
        </thead>
      ";
      
      $table = "";
      if (count($data) > 0) {
        foreach ($data as $key => $value) {
          $no++;

          $action = array();
          $action['delete'] = "deleteAction('?switcher=delete&id={$value['id']}');";
          $action['edit'] = "editAction('?switcher=edit&id={$value['id']}');";
          $action = $this->toAtrr($action);

          $table .= "
            <tr class='rowcontent' list-action ".$action.">
              <td align='center'>".$no."</td>
              <td align='center'>".$value['name']."</td>
              <td align='center'>".$value['value']."</td>
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

    function options($SELF, $breadcrumb) {
      $option = array();
      $option['master']     = '#bodymaster';
      $option['slave']      = $this->site_url().$this->uri->uri_string."_slave";
      $option['getpage']    = 'switcher';
      $option['type']       = '';
      $option['javascript'] = array(
        'src'=>$this->base_url().'js/'.$SELF.'.js?version='.VERSION,
        'type'=>'text/javascript',
      );

      $d = array();
      $d['title'] = "Create New Parameter";
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
