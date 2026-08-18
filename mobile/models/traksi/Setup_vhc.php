<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_vhc extends OWL_Model
{

	function selectQuery(array $pageLimit = array())
	{
		$limitPage = "";
		if (count($pageLimit) > 0) {
			$limitPage = "LIMIT " . implode(",", $pageLimit);
		}
		// $q = "SELECT a.*,b.namajenisvhc,c.tanggalawal,c.tanggalakhir,c.bsart FROM " . $this->db->dbname . ".vhc_5master a
		// left join " . $this->db->dbname . ".vhc_5jenisvhc b on a.jenisvhc = b.jenisvhc
		// left join " . $this->db->dbname . ".pmn_kontrakbeli c on c.kodeorg = a.kodeorg and c.nokontrak = a.nokontrak 
		// ORDER BY kelompokvhc,nopol ASC " . $limitPage;

		$q = "SELECT a.*,b.namajenisvhc FROM " . $this->db->dbname . ".vhc_5master a
		left join " . $this->db->dbname . ".vhc_5jenisvhc b on a.jenisvhc = b.jenisvhc
		ORDER BY a.kelompokvhc,a.nopol ASC " . $limitPage;

		// echo $q;
		$data = $this->query($q, 'ASSOC');
		return $data;
	}
	function selectdata(array $pageLimit = array())
	{
		$result = array();
		$data = $this->selectQuery($pageLimit);
		if ($data and $data->rowCount() > 0) {
			$result = $this->fetch($data);
		}
		return $result;
	}
	function type()
	{
		$data = array(
			'INTERNAL' => 'INTERNAL',
			'EXTERNAL' => 'EXTERNAL',
		);
		return $data;
	}

	function getDataApi($where="")
	{
		$data = array();
        $q = "select kodevhc,kodeorg,detailvhc,nopol from ".$this->db->dbname.".vhc_5master {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
				$d['kodevhc'] =$v['kodevhc'];
				$d['nopol'] =($v['nopol']==''?$v['kodevhc']:$v['nopol']);
				$d['detailvhc'] =($v['detailvhc']==''?'':$v['detailvhc']);
				$d['unit'] = ($v['kodeorg'] == '' ? '' : $v['kodeorg']);
                $data[] = $d;
            }
        }
        return $data;
	}
	function addData()
	{
		$data = array(
			'kodevhc' => '',
			'nopol' => '',
			'kodeorg' => '',
			'jenisvhc' => '',
			'nokontrak' => '',
			'nokontrak' => '',
			'tahunperolehan' => '',
			'kelompokvhc' => '',
			'kepemilikan' => '',
			'kodetraksi' => '',
			'status' => '',
			'createby' => $_SESSION['standard']['userid'],
			'createtime' => date('Y-m-d')
		);
		return $this->insert($dataInsert, $this->db->dbname . ".vhc_5master");
	}
	function updateData()
	{
		$data = array(
			'kodevhc' => '',
			'nopol' => '',
			'kodeorg' => '',
			'jenisvhc' => '',
			'nokontrak' => '',
			'nokontrak' => '',
			'tahunperolehan' => '',
			'kelompokvhc' => '',
			'kepemilikan' => '',
			'kodetraksi' => '',
			'status' => '',
			'createby' => $_SESSION['standard']['userid'],
			'createtime' => date('Y-m-d')
		);
		return $this->update($data, "vhc_5master", $where);
	}
}
