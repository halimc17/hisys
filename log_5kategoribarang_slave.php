<?
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
$param = $_POST;
if(count($param)==0) $param = $_GET;

switch($method) {
    case 'loaddata':
        $tab .= "<table id=mytable class='sortable' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['jenis'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['keterangan'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['warna'] . "</th>
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

        $str = "select * from " . $dbname . ".log_5kategoribarang order by id asc";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            @$no += 1;
            $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td>" . $bar['jenis'] . "</td>
						<td align=left>" . $bar['keterangan'] . "</td>
						<td align=center><input type=color value='" . $bar['color'] . "' disabled /></td>
						<td style='text-align:left;'>" . getKary($bar['updateby']) . "</td>
						<td style='text-align:left;'>" . $bar['updatetime'] . "</td>
						<td align=center>
							<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('Edit Data Kategori Barang','" . $bar['id'] . "','" . $bar['jenis'] . "','" . $bar['keterangan'] . "', '".$bar['color']."')\";>
						</td>
						<td align=center>
							<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('" . $bar['id'] . "');>
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
        $tab .= "<table border=0 cellpadding=2 cellspacing=1>
					<tr>
						<td class=bintang style=min-width:150px>" . $_SESSION['lang']['jenis'] . "</td>
						<td>
                            <input id=jenis class=myinputtext style='width:350px;padding:8px 0;' />
						</td>
					</tr>
					<tr>
						<td class=bintang style=min-width:150px>" . $_SESSION['lang']['keterangan'] . "</td>
						<td>
                            <textarea id=keterangan style='width:330px;height:100px;resize:none'></textarea>
						</td>
					</tr>
                    <tr>
						<td class=bintang style=min-width:150px>" . $_SESSION['lang']['warna'] . "</td>
						<td>
                            <input type=color id=color title='Klik Untuk Pilih Warna' class=myinputtext style='width:350px;height:70px;padding:8px 0;' />
						</td>
					</tr>
				
	                <tr>
	                    <td><input type=hidden id=mode value=insert><input type=hidden id=id value=''></td>
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
                'jenis' => $param['jenis'],
                'keterangan' => $param['keterangan'],
                'color' => $param['color'],
                'createby'  => $_SESSION['standard']['userid'],
                'updateby'  => $_SESSION['standard']['userid'],
                'createtime' => date("Y-m-d H:i:s")
            );

            $cols = array();
            foreach($data as $key => $row) {
                $cols[] = $key;
            }

            $query = insertQuery($dbname,'log_5kategoribarang',$data,$cols);
            $owlPDO->exec($query);

            $owlPDO->commit();
        } catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}

    break;

    case 'ubah':
        try {
            $owlPDO->beginTransaction();

            #= Data Update Array
            $data = array(
                'jenis' => $param['jenis'],
                'keterangan' => $param['keterangan'],
                'color' => $param['color'],
                'updateby'  => $_SESSION['standard']['userid'],
                'updatetime' => date("Y-m-d H:i:s")
            );

            $where = "id='" . $param['id'] . "'";
            $query = updateQuery($dbname, 'log_5kategoribarang', $data, $where); #exit("warningcode".$query);
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

            $str = "delete from " . $dbname . ".log_5kategoribarang where id='" . $param['id'] . "'";
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
?>