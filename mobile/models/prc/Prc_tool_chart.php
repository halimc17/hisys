<?
  defined('BASEPATH') or exit('No direct script access allowed');

  class  Prc_tool_chart extends OWL_Model {
    public $result, $prepareDB;

    function __construct() {
      $d['table'] = array(
        "tool_chart" => array(
          "id"         => "int(11) NOT NULL AUTO_INCREMENT",
          "rnumber"    => "int(10)",
          "nama"       => "varchar(100)",
          "kolomlabel" => "varchar(100)",
          "kolomdata"  => "varchar(100)",
          "operation"  => "varchar(100)",
          "type"       => "varchar(25)",
          "version"    => "varchar(10)",
          "status"     => "tinyint(3)",
          "Indexes"    => array(
            "PRIMARY" => array("id"),
            "INDEX"   => array("rnumber")
          ),
          "COLLATION"  => array(
            'ENGINE'  => "InnoDB DEFAULT",
            'CHARSET' => "utf8mb4",
            'COLLATE' => "utf8mb4_general_ci",
          )
        )
      );

      $this->prepareDB = $d;
      $this->init();
    }

    function init(){
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
      $q = "SELECT {$this->select()} FROM ".$this->db->dbname.".`tool_chart` {$where}";
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

    function getId($rnumber) {
      $data = array();
      $result = array();
      $q = "SELECT id FROM ".$this->db->dbname.".`tool_chart` WHERE rnumber IN (".$rnumber.") AND status = 1 ORDER BY id";
      $query = $this->query($q, 'ASSOC');
      $data = $this->fetch($query);
      foreach($data as $k => $v) {
        $result[] = $v['id'];
      }
      
      return $result;
    }
    
    function listData($pageLimit, $where = '') {
      if (count($pageLimit) > 0) {
        $where .= " DESC LIMIT ".implode(",", $pageLimit);
      }
      
      $data = $this->get($where);
      
      return $data;
    }

    function insertTable($data = array()) {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['tool_chart']);
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

      return $this->insert($dataExec, $this->db->dbname.".`tool_chart`");
    }

    function updateTable($data = array(), $where = '') {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['tool_chart']);
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

      return $this->update($dataExec, $this->db->dbname.".`tool_chart`", $where);
    }

    function updateStatus($status, $id) {
      $result = false;
      $str = "update ".$this->db->dbname.".tool_chart set status = ".$status." where id = ".$id;
      $query = $this->query($str, 'ASSOC');
      $fetch = $this->fetch($query);
      if ($fetch) {
        $result = true;
      }

      return $result;
    }

    function deleteTable($where = '') {
      return $this->delete($this->db->dbname.".`tool_chart`", $where);
    }
  }
