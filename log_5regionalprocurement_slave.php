<?
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
$param = $_POST;
if(count($param)==0) $param = $_GET;

switch($method) {
    case 'getUnitRegion':
        
        #= Ambil data dari Organisasi
        $qUnitRegion = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "length(kodeorganisasi)=4 AND induk='".$param['pt']."'", "namaorganisasi asc");
        $resUR = fetchData($qUnitRegion);

        $optUR = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        foreach($resUR as $val):
            $optUR .= "<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
        endforeach;
        
        echo $optUR;
    break;
    case 'loaddata':
        $tab .= "<table id=mytable class='sortable' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
				<th style='text-align:center;' rowspan=2>Nama Regional</th>
				<th style='text-align:center;' rowspan=2>Perusahaan Regional</th>
				<th style='text-align:center;' rowspan=2>Unit Regional</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['updateby'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['updatetime'] . "</th>
				<th style='text-align:center;' colspan=2>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";
        // $arrdata = array(
        //     'KPI' => 'Hasil (KPI)',
        //     'Core Values' => 'FASTER (Core Values)',
        //     'Man Management' => 'Memimpin Tim (Man Management)'
        // );

        $str = "select * from " . $dbname . ".log_5regionalprocurement order by idregional asc";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            @$no += 1;
            $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td>" . $bar['namaregional'] . "</td>
						<td>" . $bar['ptregional'] . "</td>
						<td align=left>" . $bar['unitregional'] . "</td>
						<td style='text-align:left;'>" . getKary($bar['updateby']) . "</td>
						<td style='text-align:left;'>" . $bar['updatetime'] . "</td>
						<td align=center>
							<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('Edit Data Kategori Barang','" . $bar['idregional'] . "','" . $bar['namaregional'] . "','" . $bar['ptregional'] . "','" . $bar['unitregional'] . "')\";>
						</td>
						<td align=center>
							<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('" . $bar['idregional'] . "');>
						</td>
					</tr>";
        }

        $tab .= "</tbody>
		<tfoot>
		</tfoot>
		</table>";
        echo $tab;
    break;

    case 'addnew':
        #= Get Data PT
        $qPt = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "tipe='PT' AND kodeorganisasi NOT IN ('SDP')");
        $resPt = fetchData($qPt);

        $optPt = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optUnit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

        foreach($resPt as $val):
            $optPt .= "<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
        endforeach;

        $tab .= "<table border=0 cellpadding=2 cellspacing=1>
					<tr>
						<td style=min-width:150px>Nama Regional</td>
						<td>
                            <input id=namaRegion class=myinputtext style='width:350px;padding:8px 0;' />
						</td>
					</tr>
                    <tr>
						<td style=min-width:150px>Perusahaan</td>
						<td>
                            <select id=pt class=select2 style='width:350px;' onchange=\"getUnitRegion()\">".$optPt."</select>
						</td>
					</tr>
					<tr>
						<td style=min-width:150px>Unit Regional</td>
						<td>
                            <select class=select2 id=unitRegion style='width:350px;'>".$optUnit."</select>
						</td>
					</tr>
				
	                <tr>
	                    <td><input type=hidden id=mode value=insert><input type=hidden id=idRegion value=''></td>
	                    <td>
							<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
	                    </td>
	                </tr>
            </table>";
        echo $tab;
    break;

    case 'insert':
        
        try {

            $owlPDO->beginTransaction();
            #= Data Insert Array
            $data = array(
                'namaregional' => strtoupper($param['namaRegion']),
                'ptregional' => $param['ptRegion'],
                'unitregional' => $param['unitRegion'],
                'createby'  => $_SESSION['standard']['userid'],
                'updateby'  => $_SESSION['standard']['userid'],
                'createtime' => date("Y-m-d H:i:s")
            );

            $cols = array();
            foreach($data as $key => $row) {
                $cols[] = $key;
            }

            $query = insertQuery($dbname,'log_5regionalprocurement',$data,$cols);
            $owlPDO->exec($query);

            $owlPDO->commit();
        } catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}

    break;

    case 'ubah':
        try {
            $owlPDO->beginTransaction();

            #= Data Update Array
            $data = array(
                'namaregional' => strtoupper($param['namaRegion']),
                'ptregional' => $param['ptRegion'],
                'unitregional' => $param['unitRegion'],
                'createby'  => $_SESSION['standard']['userid'],
                'updateby'  => $_SESSION['standard']['userid'],
                'createtime' => date("Y-m-d H:i:s")
            );

            $where = "idregional='" . $param['idRegion'] . "'";
            $query = updateQuery($dbname, 'log_5regionalprocurement', $data, $where); #exit("warningcode".$query);
            $owlPDO->exec($query);
            
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Errorcode, " . addslashes($e->getMessage());
            die();
        }	

    break;

    case 'hapus':
        try {
            $owlPDO->beginTransaction();

            $str = "delete from " . $dbname . ".log_5regionalprocurement where idregional='" . $param['idRegion'] . "'";
            $owlPDO->exec($str);

            #execute
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Errorcode, " . addslashes($e->getMessage());
            die();
        }
    break;
}
