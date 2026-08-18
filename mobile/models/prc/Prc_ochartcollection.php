<?
  defined('BASEPATH') or exit('No direct script access allowed');

  class  Prc_ochartcollection extends OWL_Model {
    public $result, $prepareDB;
    
    function __construct() {
      $d['table'] = array(
        "ochartcollection" => array(
          "id" => "bigint(20) NOT NULL AUTO_INCREMENT",
          "fileid" => "bigint(20) NOT NULL",
          "name" => "varchar(100) NOT NULL",
          "version" => "int(11) NOT NULL",
          "type" => "enum('chartjson', 'image') NOT NULL",
          "src" => "varchar(500) NOT NULL",
          "isactive" => "tinyint(1) DEFAULT 1",
          "createby" => "int(10) UNSIGNED ZEROFILL DEFAULT NULL",
          "createtime" => "datetime DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()",
          "updateby" => "int(10) UNSIGNED ZEROFILL DEFAULT NULL",
          "updatetime" => "datetime DEFAULT NULL",
          "Indexes" => array(
            "PRIMARY" => array("id"),
          ),
          "COLLATION" => array(
            'ENGINE' => "InnoDB DEFAULT",
            'CHARSET' => "utf8mb4",
            'COLLATE' => "utf8mb4_general_ci",
          )
        )
      );

      $this->prepareDB = $d;
      $this->init();
    }

    function init() {
      $result = false;
      foreach ($this->prepareDB['table'] as $tbl=>$column) {
        if (!$this->table_exists($tbl)) {
          $this->create_table($this->db->dbname, $tbl, $column);
        } else {
          $this->alter_table($this->db->dbname, $tbl, $column);
        }
      }

      return $result;
    }

    function selectTable($where = '') {
      $data = array();
      $q = "SELECT {$this->select()} FROM ".$this->db->dbname.".`ochartcollection` {$where}";
      $data = $this->query($q, 'ASSOC');

      return $data;
    }

    function get($where = '') {
      $data = array();
      if($dataRow = $this->selectTable($where) and $dataRow->rowCount() > 0) {
        $data = $this->fetch($dataRow);
      }

      return $data;
    }

    function insertTable($data = array()) {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['ochartcollection']);
      if (empty($data)) {
        $column = array_keys($columnTabel);
        foreach($column as $k=>$v) {
          if (!empty($this->post($k))) {
            $dataExec[$k] = $v;
          }
        }
      } else {
        $dataExec = $data;
      }

      return $this->insert($dataExec, $this->db->dbname.".`ochartcollection`");
    }

    function updateTable($data = array(), $where = '') {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['ochartcollection']);
      if (empty($data)) {
        $column = array_keys($columnTabel);
        foreach($column as $k=>$v) {
          if (!empty($this->post($k))) {
            $dataExec[$k] = $v;
          }
        }
      } else {
        $dataExec = $data;
      }

      return $this->update($dataExec, $this->db->dbname.".`ochartcollection`", $where);
    }

    function deleteTable($where = '') {
      return $this->delete($this->db->dbname.".`ochartcollection`", $where);
    }

    function deleteData($where = '') {
      $q = "DELETE FROM ".$this->db->dbname.".`ochartcollection` {$where}";
      $result = $this->query($q, 'ASSOC');
      if ($result) {
        $this->result = true;
      } else {
        $this->result = false;
      }

      return $this->result;
    }
    
    function listData($pageLimit, $where = '') {
      if (count($pageLimit) > 0) {
        $where .= " DESC LIMIT ".implode(",", $pageLimit);
      }
      
      $data = $this->get($where);
      
      return $data;
    }

    public function uploadJsonFile() {
      // Ambil ID dan parameter dari request
      $id = $_GET['id'];
      $parameter = json_decode(html_entity_decode($_GET['parameter']), ENT_QUOTES);
      $submenu = json_decode(html_entity_decode($_GET['submenu']), ENT_QUOTES);

      // Panggil fungsi untuk membuat atau generate isi file JSON
      $jsonContent = $this->generateJsonContent($id, $parameter, $submenu);

      // Periksa apakah hasil dari generate tidak kosong
      if (empty($jsonContent)) {
        $this->response['status'] = 400;
        $this->response['error'] = true;
        $this->response['message'] = "Failed! JSON content is empty.";

        return $this->response;
      }

      try {
        // Path untuk menyimpan file JSON
        $location = 'm_fileDocuments';
        $linkJson = 'chart_publish/dashboard/json/';
        $path = $location.'/'.$linkJson;

        // Buat folder jika belum ada
        if (!file_exists($path)) {
          mkdir($path, 0777, true);
        }

        // Periksa apakah folder dapat ditulis
        if (!is_writable($path)) {
          $this->response['status'] = 500;
          $this->response['error'] = true;
          $this->response['message'] = "Tidak memiliki izin untuk membuat atau mengunggah file JSON ke folder ".$path;
        } else {
          // Nama file JSON
          $fileName = $jsonContent['name'].".json";

          // Simpan isi JSON ke dalam file
          file_put_contents($path.$fileName, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

          // Periksa apakah file berhasil disimpan
          if (file_exists($path.$fileName)) {
            $dataInsert = [
              "fileid" => $id,
              "name" => $jsonContent['name'],
              "version" => 1,
              "type" => "chartjson",
              // "src" => $this->base_url($linkJson, $location).$fileName,
              "src" => $path.$fileName,
              "isactive" => 1,
              // "createby" => $this->user,
              "createtime" => date("Y-m-d H:i:s"),
              // "updateby" => $this->user,
              "updatetime" => date("Y-m-d H:i:s")
            ];

            $insertData = $this->insertTable($dataInsert);
            if (!$insertData) {
              $this->response['status'] = 500;
              $this->response['error'] = true;
              $this->response['message'] = "Failed! Gagal menyimpan data ke database.";
              $this->response['insertData'] = $insertData;
            } else {
              $this->response['status'] = 200;
              $this->response['error'] = false;
              $this->response['message'] = "File JSON berhasil disimpan di lokasi: ".$path.$fileName;
              $this->response['fileName'] = $fileName;
              $this->response['filePath'] = $this->base_url($linkJson, $location).$fileName;
              $this->response['tanggal'] = date("Y-m-d H:i:s");
              $this->response['jsonContent'] = $jsonContent;
            }
          } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! File JSON tidak mendapatkan akses, Location: ".$fileName;
          }
        }
      } catch (Exception $e) {
        $this->response['status'] = 409;
        $this->response['error'] = true;
        $this->response['message'] = "Failed! Save JSON file - (".$e->getMessage().")!!";
      }

      return $this->response;
    }

    private function generateJsonContent($id, $parameter, $submenu) {
      // Ambil data dari tabel page_chart_ht berdasarkan ID
      $queryHt = "SELECT * FROM ".$this->db->dbname.".page_chart_ht WHERE id = '".$id."'";
      $resultHt = $this->query($queryHt, 'ASSOC');
      $dataHt = $this->fetch($resultHt);

      // Buat struktur data JSON sesuai kebutuhan
      $jsonData = [
        "type" => "oChartCollection",
        "id" => $id,
        "name" => $dataHt[0]['title'],
        "status" => $dataHt[0]['status'],
        "menu" => [
          "name" => $submenu[0]['name'],
          "icon" => $submenu[1]['icon'],
          "color" => $submenu[2]['iconColor']
        ],
        "charts" => []
      ];
      
      // Ambil data dari tabel page_chart_dt
      $queryDt = "SELECT * FROM {$this->db->dbname}.page_chart_dt WHERE parentid = '{$id}'";
      $resultDt = $this->query($queryDt, 'ASSOC');
      $dataDt = $this->fetch($resultDt);
      foreach ($dataDt as $rowDt) {
        // Ambil data dari tabel tool_chart berdasarkan chartid
        $queryChart = "SELECT * FROM {$this->db->dbname}.tool_chart WHERE id = '{$rowDt['chartid']}'";
        $resultChart = $this->query($queryChart, 'ASSOC');
        $dataChart = $this->fetch($resultChart);

        // Ambil data dari tabel tool_userdefinedreport berdasarkan rnumber
        $query = "SELECT * FROM {$this->db->dbname}.tool_userdefinedreport WHERE rnumber = '{$dataChart[0]['rnumber']}'";
        $result = $this->query($query, 'ASSOC');
        $data = $this->fetch($result);

        $jsonData['charts'][] = [
          "chartid" => $rowDt['chartid'],
          "type" => $dataChart[0]['type'],
          "attributes" => [
            "x" => $rowDt['x'],
            "y" => $rowDt['y'],
            "w" => $rowDt['w'],
            "h" => $rowDt['h'],
          ],
          "format" => [
            "kolomlabel" => $dataChart[0]['kolomlabel'],
            "kolomdata" => $dataChart[0]['kolomdata'],
            "operation" => $dataChart[0]['operation']
          ],
          "query" => [
            "query" => $data[0]['query'],
            "dbname" => $data[0]['dbname'],
            "kolomtampil" => $data[0]['kolomtampil'],
            "group" => $data[0]['group'],
            "subtotal" => $data[0]['subtotal'],
            "order" => $data[0]['order'],
            "sort" => $data[0]['sort'],
            "parameters" => []
          ]
        ];

        // Ambil data parameter dari tabel tool_userdefinedreport_par berdasarkan rnumber
        $queryPar = "SELECT * FROM {$this->db->dbname}.tool_userdefinedreport_par WHERE rnumber = '{$dataChart[0]['rnumber']}'";
        $resultPar = $this->query($queryPar, 'ASSOC');
        $dataPar = $this->fetch($resultPar);
        foreach ($dataPar as $rowPar) {
          $param = $parameter[0][$rowPar['rnumber']."_".count($jsonData['charts'][count($jsonData['charts']) - 1]['query']['parameters'])];
          $jsonData['charts'][count($jsonData['charts']) - 1]['query']['parameters'][] = [
            "kolom" => $rowPar['kolom'],
            "type" => $rowPar['value'],
            "operator" => $rowPar['operator'],
            "value" => $param['type'] == 'Setup' ? $param['php'] : $param['isi']
          ];
        }
      }

      return $jsonData;
    }
  }
