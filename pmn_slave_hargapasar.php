<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
//$arr="##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##proses";


$proses = checkPostGet('proses', '');
$kdBarang = checkPostGet('kdBarang', '');
$satuan = checkPostGet('satuan', '');
$idPasar = checkPostGet('idPasar', '');
$idMatauang = checkPostGet('idMatauang', '');
$hrgPasar = checkPostGet('hrgPasar', '');
$ffa = checkPostGet('ffa', '');
$status = checkPostGet('status', '');
$mni = checkPostGet('mni', '');
$kdBrgCari = checkPostGet('kdBrgCari', '');
$keterangan = checkPostGet('keterangan', '');
$tglHarga = tanggalsystem(checkPostGet('tglHarga', ''));
$path = "fileupload/hargapasar/";

$nopp = checkPostGet('rnopp', '');
$namafile = checkPostGet('namafile', '');

$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$where = "tanggal='" . $tglHarga . "' and kodeproduk='" . $kdBarang . "' and pasar='" . $idPasar . "'";
switch ($proses) {
    case 'getPasar':
        //exit("error:".$_POST['komoditi']);
        $str = "SELECT * FROM " . $dbname . ".pmn_5pasar where komoditi='" . $_POST['komoditi'] . "'";
        //exit("error:".$str);
        $optPasar = '';
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $optPasar .= "<option value='" . $bar->pasar . "'>" . $bar->pasar . "</option>";
        }
        if ($optPasar != '') {
            echo $optPasar;
        } else {
            echo $optBrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
            //echo "Error: Daftar Pasar tidak ditemukan, silahkan Set Up!";
        }
        exit(); //jangan dihapus exit ini
        break;
    case 'getSatuan':
        $sSatuan = "select distinct satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $kdBarang . "'";
        $qSatuan = $owlPDO->query($sSatuan) or die(print " Gagal: " . PDOException::getMessage());
        $qSatuan->setFetchMode(PDO::FETCH_ASSOC);
        $rSatuan = $qSatuan->fetch();
        echo $rSatuan['satuan'];
        break;
    case 'insert':
        if ($tglHarga == '') {
            exit('warning :' . $_SESSION['lang']['notiftanggal']);
        }
        if ($idPasar == '') {
            exit('warning :' . $_SESSION['lang']['silakanisi'] . " " . $_SESSION['lang']['pasar']);
        }
        if ($kdBarang == '') {
            exit('warning :' . $_SESSION['lang']['silakanisi'] . ' ' . $_SESSION['lang']['komoditi']);
        }
        if (($hrgPasar == '') || ($hrgPasar == '0')) {
            exit('warning :' . $_SESSION['lang']['harga'] . " " . $_SESSION['lang']['notifemptyzero']);
        }
        $sCek = "select distinct * from " . $dbname . ".pmn_hargapasar where " . $where . "";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $rCek = owlBaris($qCek);
        if ($rCek < 1) {
            $sIns = "insert into " . $dbname . ".pmn_hargapasar (tanggal, kodeproduk, pasar, satuan, harga, matauang,statusharga,ffa,mni,createby,createtime,keterangan) 
                   values ('" . $tglHarga . "','" . $kdBarang . "','" . $idPasar . "','" . $satuan . "','" . $hrgPasar . "',"
                . "'" . $idMatauang . "','" . $status . "','" . $ffa . "','" . $mni . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','".$keterangan."')";
            try {
                $owlPDO->exec($sIns);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        } else {
            exit("Error: Already exist");
        }
        break;
    case 'update':
        if ($tglHarga == '') {
            exit('warning :' . $_SESSION['lang']['notiftanggal']);
        }
        if ($idPasar == '') {
            exit('warning :' . $_SESSION['lang']['silakanisi'] . " " . $_SESSION['lang']['pasar']);
        }
        if ($kdBarang == '') {
            exit('warning :' . $_SESSION['lang']['silakanisi'] . ' ' . $_SESSION['lang']['komoditi']);
        }
        if (($hrgPasar == '') || ($hrgPasar == '0')) {
            exit('warning :' . $_SESSION['lang']['harga'] . " " . $_SESSION['lang']['notifemptyzero']);
        }
        $sIns = "update " . $dbname . ".pmn_hargapasar set statusharga='" . $status . "',harga='" . $hrgPasar . "',"
            . "matauang='" . $idMatauang . "',statusharga='" . $status . "',ffa='" . $ffa . "',mni='" . $mni . "',updateby='" . $_SESSION['standard']['userid'] . "',keterangan='".$keterangan."' where " . $where . "";
			// exit("Error:$sIns");
        try {
            $owlPDO->exec($sIns);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;



    case 'loadData':

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_hargapasar order by `tanggal` desc"; // echo $ql2;
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }


        $str = "select * from " . $dbname . ".pmn_hargapasar order by `tanggal` desc  limit " . $offset . "," . $limit . "";
        if (($page * $limit) == 0) {
            $no = 0;
        } else {
            $no = ($page * $limit);
        }
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $barisData = owlBaris($res);
        if ($barisData > 0) {
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {

                $no += 1;
                if ($bar->updateby == '0000000000') {
                    $lastupdate = $bar->createby;
                } else {
                    $lastupdate = $bar->updateby;
                }

                echo "<tr class=rowcontent id='tr_" . $no . "'>
                        <td align=center>" . $no . "</td>
						<td id=detail_kode" . $no . " hidden>" . str_replace('-', '', $bar->tanggal) . "-" . $bar->kodeproduk . "-" . $bar->pasar . "</td>
                        <td align=center>" . tanggalnormal($bar->tanggal) . "</td>
                        <td>" . $bar->kodeproduk . " - " . $optNmBarang[$bar->kodeproduk] . "</td>
                        <td align=center>" . $bar->satuan . "</td>
                        <td>" . $bar->pasar . "</td>
                        <td align=center>" . $bar->matauang . "</td>
                        <td align=right>" . number_format($bar->harga, 2) . "</td>
                        <td>" . $bar->statusharga . "</td>
                        <td align=center>" . $bar->ffa . "</td>
                        <td align=center>" . $bar->mni . "</td>
                        <td align=center>" . $bar->keterangan . "</td>
                        <td align=center>" . getNamaKaryawan($lastupdate) . "</td>
                        <td align=center width=25px>
							<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->satuan . "','" . $bar->pasar . "','" . $bar->matauang . "','" . $bar->harga . "','" . $bar->statusharga . "','" . $bar->ffa . "','" . $bar->mni . "','" . $bar->keterangan . "');\"> 
						</td>	
						<td align=center width=25px>
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->pasar . "');\">
						</td>	
						<td align=center width=25px>	
							<img src=images/upload-2-xxl.png class=resicon  title='Document' onclick='showupload(event," . $no . ")' >
						</td>
						</tr>";
            }
        } else {
            echo "<tr class=rowcontent><td colspan=14 style='text-align:center;'>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo "
                        <tr><td colspan=14 align=center>
                        " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                        <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                        <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                        </td>
                        </tr>";
        echo "</tbody></table>";
        break;


    case 'cariData':
        $wre = "";
        if ($kdBrgCari != '') {
            $wre .= " and kodeproduk='" . $kdBrgCari . "'";
        }
        if ($tglHarga != '') {
            $wre .= " and tanggal='" . $tglHarga . "'";
        }
        if ($idPasar != '') {
            $wre .= " and pasar='" . $idPasar . "'";
        }
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_hargapasar where tanggal!='' " . $wre . " order by `tanggal` desc"; // echo $ql2;
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }


        $str = "select * from " . $dbname . ".pmn_hargapasar where tanggal!='' " . $wre . " order by `tanggal` desc  limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $barisData = owlBaris($res);
        if ($barisData > 0) {
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {

                $no += 1;


                echo "<tr class=rowcontent id='tr_" . $no . "'>
                        <td align=center>" . $no . "</td>
                        <td id=detail_kode" . $no . " hidden>" . str_replace('-', '', $bar->tanggal) . "-" . $bar->kodeproduk . "-" . $bar->pasar . "</td>

                        <td align=center>" . tanggalnormal($bar->tanggal) . "</td>
                        <td>" . $bar->kodeproduk . " - " . $optNmBarang[$bar->kodeproduk] . "</td>
                        <td align=center>" . $bar->satuan . "</td>
                        <td>" . $bar->pasar . "</td>
                        <td align=center>" . $bar->matauang . "</td>
                        <td align=right>" . number_format($bar->harga, 2) . "</td>
                        <td>" . $bar->statusharga . "</td>
                        <td align=center>" . $bar->ffa . "</td>    
                            <td align=center>" . $bar->mni . "</td>   
                            <td align=center>" . $bar->keterangan . "</td>   
                        <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->satuan . "','" . $bar->pasar . "','" . $bar->matauang . "','" . $bar->harga . "','" . $bar->statusharga . "','" . $bar->ffa . "','" . $bar->mni . "','" . $bar->keterangan . "');\">
                            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->pasar . "');\">
                        <img src=images/upload-2-xxl.png class=resicon  title='Document' onclick='showupload(event," . $no . ")' ></td>
						</tr>";
            }
        } else {
            echo "<tr class=rowcontent><td colspan=12 style='text-align:center;'>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo "
                        <tr><td colspan=11 align=center>
                        " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                        <button class=mybutton onclick=cariTrans(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                        <button class=mybutton onclick=cariTrans(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                        </td>
                        </tr>";
        echo "</tbody></table>";
        break;


    case 'delData':
        $sDel = "delete from " . $dbname . ".pmn_hargapasar where " . $where . " ";
        try {
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;





        ########################################################################################################################################
        ########################################################################################################################################

        ########################################################################################################################################
        ########################################################################################################################################

    case 'showupload':
        $tab = "";

        $tab .= "<table cellspacing='1' border='0' id='uploadpopup'>
			<tr hidden>
				<td>No. Kontrak</td>
				<td>:</td>
				<td>
					<label id='noppupload' style='font-weight:bold'>" . $nopp . "</label>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		<p />";

        $tab .= "<fieldset>
			<legend>" . $_SESSION['lang']['list'] . "</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";



        echo $tab;
        break;



    case 'submitfile':

        $tgl = date("YmdHis");
        // exit("error : ".$tgl);
        $data = $_POST;

        if ($data['fileupload'] != '') {
            if ($_FILES['file']['error'] == 0) {
                $filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
                $newfilename = str_replace($filetype, '', $_FILES['file']['name']);
                $filename = $newfilename . "_" . $tgl . "" . $filetype;
                $file_tmpname = $_FILES['file']['tmp_name'];

                if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx') || ($filetype == '.rar')) {
                    if ($_FILES['file']['size'] <= 250000) {
                        $str = "insert into " . $dbname . ".listfileupload values ('','" . $data['rnopp'] . "','" . $filename . "','" . $filetype . "','others','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
                        try {
                            $owlPDO->exec($str);
                            if (!file_exists($path)) {
                                mkdir($path, 0777, true);
                            }
                            move_uploaded_file($file_tmpname, $path . $filename);
                        } catch (PDOException $e) {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    } else {
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                } else {
                    exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
                }
            }
        }
        break;



    case 'loadfiles':
        $no = 0;
        $tab = "";
        // exit("Error:".$nopp);

        $str = "select * from " . $dbname . ".pmn_hargapasar where tanggal='" . $expl[0] . "' and kodeproduk='" . $expl[1] . "' and pasar='" . $expl[2] . "'  ";
        $resv = fetchData($str);
        foreach ($resv as $bar => $barv) {
            $close = $barv['close'];
        }

        $str = "select * from " . $dbname . ".listfileupload where notransaksi = '" . $nopp . "' and status='1'";
        $res = fetchData($str);
        if (empty($res)) {
            $tab .= "<tr class=rowcontent><td colspan=4 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        } else {
            foreach ($res as $key => $val) {
                $no++;
                $tab .= "<tr id='ppDetailTable' class=rowcontent>
					<td style='text-align:center'>" . $no . "</td>";

                if ($val['formaticon'] == '.jpeg' || $val['formaticon'] == '.jpg') {
                    $tab .= "<td style='text-align:center'>
						<a href='fileupload/hargapasar/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
                } elseif ($val['formaticon'] == '.png') {
                    $tab .= "<td style='text-align:center'>
						<a href='fileupload/hargapasar/" . $val['namafile'] . "' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
                } elseif ($val['formaticon'] == '.pdf') {
                    $tab .= "<td style='text-align:center'>
						<a href='fileupload/hargapasar/" . $val['namafile'] . "' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
                } elseif ($val['formaticon'] == '.xls' || $val['formaticon'] == '.xlsx') {
                    $tab .= "<td style='text-align:center'>
						<a href='fileupload/hargapasar/" . $val['namafile'] . "' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
                } elseif ($val['formaticon'] == '.doc' || $val['formaticon'] == '.docx') {
                    $tab .= "<td style='text-align:center'>
						<a href='fileupload/hargapasar/" . $val['namafile'] . "' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
                } else {
                    $tab .= "<td style='text-align:center'>
						<a href='fileupload/hargapasar/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
                }

                $tab .= "<td style='text-align:left'>" . $val['namafile'] . "</td>
					<td align=center>
						<a href='fileupload/hargapasar/" . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if ($close == 0) {
                    $tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $nopp . "','" . $val['namafile'] . "');\" >";
                }
                $tab . "	</td>
				</tr>";
            }
        }
        echo $tab;
        break;


    case 'deletefile':
        $str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $nopp . "' and namafile='" . $namafile . "'";
        try {
            $owlPDO->exec($str);
            $path = "fileupload/hargapasar/" . $namafile;
            unlink($path);
        } catch (PDOException $e) {
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;



    default:
        break;
}
