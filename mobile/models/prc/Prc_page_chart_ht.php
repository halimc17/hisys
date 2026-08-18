<?
  defined('BASEPATH') or exit('No direct script access allowed');

  class  Prc_page_chart_ht extends OWL_Model {
    public $result, $prepareDB;
    
    function __construct() {
      $d['table'] = array(
        "page_chart_ht" => array(
          "id" => "int(11) NOT NULL AUTO_INCREMENT",
          "title" => "varchar(100)",
          "status" => "int(11)",
          "createtime" => "timestamp NOT NULL DEFAULT current_timestamp()",
          "updatetime" => "timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()",
          "Indexes" => array(
            "PRIMARY" => array("id")
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

    function selectTable($where = '', $select = '*') {
      $data = array();
      $q = "SELECT {$select} FROM ".$this->db->dbname.".`page_chart_ht` {$where}";
      $data = $this->query($q, 'ASSOC');

      return $data;
    }

    function get($where = '', $select = '*') {
      $data = array();
      if($dataRow = $this->selectTable($where, $select) and $dataRow->rowCount() > 0) {
        $data = $this->fetch($dataRow);
      }

      return $data;
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
      $columnTabel = $this->column_table($this->prepareDB['table']['page_chart_ht']);
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

      return $this->insert($dataExec, $this->db->dbname.".`page_chart_ht`");
    }

    function updateTable($data = array(), $where = '') {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['page_chart_ht']);
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

      return $this->update($dataExec, $this->db->dbname.".`page_chart_ht`", $where);
    }

    function updateStatus($status, $id) {
      $result = false;
      $str = "UPDATE ".$this->db->dbname.".`page_chart_ht` SET status = ".$status." WHERE id = ".$id;
      $query = $this->query($str, 'ASSOC');
      $fetch = $this->fetch($query);
      if ($fetch) {
        $result = true;
      }

      return $result;
    }

    function deleteTable($where = '') {
      return $this->delete($this->db->dbname.".`page_chart_ht`", $where);
    }
  }
