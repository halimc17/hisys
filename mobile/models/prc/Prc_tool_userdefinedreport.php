<?
  defined('BASEPATH') or exit('No direct script access allowed');

  class  Prc_tool_userdefinedreport extends OWL_Model {
    public $result, $prepareDB;

    function __construct() {
      $d['table'] = array(
        "tool_userdefinedreport" => array(
          "rnumber"     => "int(10)",
          "namalaporan" => "varchar(150)",
          "query"       => "text",
          "dbname"      => "varchar(50)",
          "createdate"  => "date",
          "updatetime"  => "timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()",
          "owner"       => "char(40)",
          "status"      => "tinyint(3)",
          "kolomtampil" => "text",
          "group"       => "varchar(100) NULL",
          "subtotal"    => "varchar(100) NULL",
          "order"       => "varchar(100) NULL",
          "sort"        => "varchar(100) NULL",
          "Indexes"     => array(
            "PRIMARY" => array("rnumber")
          ),
          "COLLATION"   => array(
            'ENGINE'  => "InnoDB DEFAULT",
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

    function selectTable($where = '', $order = '') {
      $data = array();
      $q = "SELECT {$this->select()} FROM ".$this->db->dbname.".`tool_userdefinedreport` {$where} {$order}";
      $data = $this->query($q, 'ASSOC');

      return $data;
    }

    function get($where = '', $order = '') {
      $data = array();
      if($dataRow = $this->selectTable($where, $order) and $dataRow->rowCount() > 0) {
        $data = $this->fetch($dataRow);
      } 

      return $data;
    }

    function getRnumber($user) {
      $data = array();
      $result = array();
      $q = "SELECT rnumber FROM ".$this->db->dbname.".`tool_userdefinedreport` WHERE owner = '".$user."' AND status = 1 ORDER BY rnumber";
      $query = $this->query($q, 'ASSOC');
      $data = $this->fetch($query);
      foreach($data as $k => $v) {
        $result[] = $v['rnumber'];
      }
      
      return $result;
    }
    
    function getTables($dbname="") {
      $data = array();
      $q = "SHOW TABLES FROM ".$dbname."";
      $query = $this->query($q, 'ASSOC');
      if(!empty($data = $this->fetch($query))) {
        foreach($data as $k => $v) {
          $data[$k] = array_shift($v);
          if (in_array($data[$k], ['user', 'admin_list', 'auth', 'master_lisensi', 'menu'])) {
            unset($data[$k]);
          }
        }
      }

      return $data;
    }
    
    function getColumns($dbname='', $tableName="") {
      $data = array();
      $q = "SHOW COLUMNS FROM ".$dbname.".".$tableName;
      $query = $this->query($q, 'ASSOC');
      $data = $this->fetch($query);

      return $data;
    }

    function getQueryColumn($rnumber) {
      $data = array();
      $q = "SELECT query, dbname, kolomtampil FROM ".$this->db->dbname.".`tool_userdefinedreport` WHERE rnumber = ".$rnumber;
      $query = $this->query($q, 'ASSOC');
      $data = $this->fetch($query);

      return $data;
    }

    function getData($q) {
      $data = array();
      // $query = $this->query($q, 'ASSOC');
      // $data = $this->fetch($query);
      $data = $this->connection->owlPDO->query($q)->fetchAll(PDO::FETCH_ASSOC);

      return $data;
    }

    function getLastRnumber() {
      $data = array();
      $q = "SELECT MAX(rnumber) AS last FROM ".$this->db->dbname.".`tool_userdefinedreport`";
      $query = $this->query($q, 'ASSOC');
      $data = $this->fetch($query);

      return $data[0]['last'] ?? 0;
    }

    function insertTable($data = array()) {
      $errorArray = false;
      $dataExec = array();
      $columnTabel = $this->column_table($this->prepareDB['table']['tool_userdefinedreport']);
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

      return $this->insert($dataExec, $this->db->dbname.".`tool_userdefinedreport`");
    }
    
    function listData($pageLimit, $where = '', $order = '') {
      if (count($pageLimit) > 0) {
        $order .= " DESC LIMIT ".implode(",", $pageLimit);
      }
      
      $data = $this->get($where, $order);
      
      return $data;
    }

    function updateTable($data = array(), $where = '') {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['tool_userdefinedreport']);
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

      return $this->update($dataExec, $this->db->dbname.".`tool_userdefinedreport`", $where);
    }

    function updateStatus($status, $rnumber) {
      $result = false;
      $str = "update ".$this->db->dbname.".tool_userdefinedreport set status = ".$status." where rnumber = ".$rnumber;
      $query = $this->query($str, 'ASSOC');
      $fetch = $this->fetch($query);
      if ($fetch) {
        $result = true;
      }

      return $result;
    }

    function deleteTable($where = '') {
      return $this->delete($this->db->dbname.".`tool_userdefinedreport`", $where);
    }
  }
