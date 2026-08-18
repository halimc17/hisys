<?
  defined('BASEPATH') or exit('No direct script access allowed');

  class  Prc_page_chart_dt extends OWL_Model {
    public $result, $prepareDB;
    
    function __construct() {
      $d['table'] = array(
        "page_chart_dt" => array(
          "id" => "int(11) NOT NULL AUTO_INCREMENT",
          "parentid" => "int(11)",
          "chartid" => "int(11)",
          "x" => "int(11)",
          "y" => "int(11)",
          "w" => "int(11)",
          "h" => "int(11)",
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
      $q = "SELECT {$this->select()} FROM ".$this->db->dbname.".`page_chart_dt` {$where}";
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

    function getParentId($chartid) {
      $data = array();
      $result = array();
      $q = "SELECT DISTINCT parentid FROM ".$this->db->dbname.".`page_chart_dt` WHERE chartid IN (".$chartid.") ORDER BY parentid";
      $query = $this->query($q, 'ASSOC');
      $data = $this->fetch($query);
      foreach($data as $k => $v) {
        $result[] = $v['parentid'];
      }
      
      return $result;
    }

    function insertTable($data = array()) {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['page_chart_dt']);
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

      return $this->insert($dataExec, $this->db->dbname.".`page_chart_dt`");
    }

    function updateTable($data = array(), $where = '') {
      $dataExec = array();
      $errorArray = false;
      $columnTabel = $this->column_table($this->prepareDB['table']['page_chart_dt']);
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

      return $this->update($dataExec, $this->db->dbname.".`page_chart_dt`", $where);
    }

    function deleteTable($where = '') {
      return $this->delete($this->db->dbname.".`page_chart_dt`", $where);
    }
  }
