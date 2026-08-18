<?
  defined('BASEPATH') or exit('No direct script access allowed');

  class  Prc_tool_php_encode extends OWL_Model {
    public $result, $prepareDB;
    
    function __construct() {
      $d['table'] = array(
        "tool_php_encode" => array(
          "id" => "int(11)",
          "name" => "varchar(20)",
          "value" => "varchar(250)",
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
      $q = "SELECT {$this->select()} FROM ".$this->db->dbname.".`tool_php_encode` {$where}";
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
      $columnTabel = $this->column_table($this->prepareDB['table']['tool_php_encode']);
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

      return $this->insert($dataExec, $this->db->dbname.".`tool_php_encode`");
    }

    function updateTable($data = array(), $where = '') {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['tool_php_encode']);
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

      return $this->update($dataExec, $this->db->dbname.".`tool_php_encode`", $where);
    }

    function deleteTable($where = '') {
      return $this->delete($this->db->dbname.".`tool_php_encode`", $where);
    }

    function deleteData($where = '') {
      $q = "DELETE FROM ".$this->db->dbname.".`tool_php_encode` {$where}";
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
  }
