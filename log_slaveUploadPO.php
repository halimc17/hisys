<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script type="text/javascript" src="js/log_pnwrharga.js?v=<?=time()?>" /></script>
<script type="text/javascript" src="js/generic.js"></script>
<link rel=stylesheet type=text/css href=style/generic.css>
<link rel="stylesheet" type="text/css" href="style/zTable.css">

<?php 

$notransaksi = checkPostGet('notransaksi','');
$method=checkPostGet('method','');
$supplierid=checkPostGet('supplierid','');
$namafile=checkPostGet('namafile','');
$kriteriaefil=checkPostGet('kriteriaefil','');

$emodul = "RPH";

switch ($method) {
    case'formUploadPo': 
        OPEN_BOX();

        $qPermintaanHargaHt = selectQuery($dbname, 'log_perintaanhargaht', 'distinct *', "nomor='".$notransaksi."'"); 
        $resPermintaanHargaHt = fetchData($qPermintaanHargaHt);
        foreach ($resPermintaanHargaHt as $row) {
            $dtNomor[]=$row['nourut'];
            $dtSupp[$row['nourut']]=$row['supplierid'];
        }
        $countDtNomor = count($dtNomor);

        ?>
        <fieldset style='height:85vh;'><legend>Upload PO</legend>
            <table cellspacing='1' class='sortable' border='0'>
                <thead class='rowheader'>
                    <tr>
                        <th align='center' rowspan="2"></th>
                        <th align='center' colspan=<?=$countDtNomor?>>Supplier</th>
                    </tr>
                    <tr>
        <?php 
        foreach ($dtNomor as $brs) :
            $optSupplier = "";
            $qSupplier = selectQuery($dbname, 'log_5supplier', 'supplierid,namasupplier');
            $resSupplier = fetchData($qSupplier);
            foreach ($resSupplier as $bar) {
                $optSupplier.="<option value='".$bar['supplierid']."' ".($bar['supplierid']==$dtSupp[$brs]?"selected":"").">".$bar['namasupplier']."</option>";
            }
        ?>
                        <th align='center'>
                            <select style='width:300px;' disabled id='supplierId_<?=$brs?>'><?=$optSupplier?></select>
                        </th>
        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr class='rowcontent'>
                        <td class="left-fixed" style="left: 116px;" valign="top">Upload Data</td>

        <?php 
        $ard=0;
        $arrmodul = getmodulefil($emodul);

        foreach($arrmodul as $key=>$val) {
            $optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
        }

        foreach ($dtNomor as $brs) :

        ?>
                        <td valign = 'top'>
                            <div id='listfiles_<?=$notransaksi?>_<?=$dtSupp[$brs]?>'>
                                <table>
        <?php 
        $qPermintaanHargaFile = selectQuery($dbname, 'log_permintaanhargafile', '*', "nomor='".$notransaksi."' AND supplierid = '".$dtSupp[$brs]."' AND status = '1'");
        $resPermintaanHargaFile = fetchData($qPermintaanHargaFile);
        $nofiles = 0;
        foreach ($resPermintaanHargaFile as $bar) :
            $nofiles++;
        ?>
                                    <tr class='rowcontent'>
                                        <td style='font-family:Arial, Helvetica, sans-serif;font-size:12px;'><?=getcriterianame($bar['kriteriaefil'])?></td>
                                        <td style='font-family:Arial, Helvetica, sans-serif;font-size:12px;'><a href='fileupload/rph/<?=$bar['namafile']?>' download title='<?=$bar['namafile']?>'><?=substr($bar['namafile'], 0, 40)?>...</a></td>
                                        <td>
                                            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=deletefile2('<?=$notransaksi?>','<?=$dtSupp[$brs]?>','<?=$bar['namafile']?>'); >
                                        </td>
                                    </tr>
        <?php endforeach; ?>

                                    <tr class='rowcontent'>
                                        <td>
                                            <select id='kriteriaefil_<?=$notransaksi?>_<?=$dtSupp[$brs]?>'><?= $optkriteria?></select>
                                        </td>
                                        <td>
                                            <input type='file' name='upload_<?=$notransaksi?>_<?=$dtSupp[$brs]?>' id='upload_<?=$notransaksi?>_<?=$dtSupp[$brs]?>' style='height:28px;' class='mybutton'>
                                        </td>
                                        <td>
                                            <img id='detail_add' title='Tambah' class='resicon' onclick=addfile2('<?=$notransaksi?>','<?=$dtSupp[$brs]?>') src='images/plus.png'>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </fieldset>


        <?php CLOSE_BOX();
    break;
    case 'submitfile':
		$tgl = date("YmdHis");
		// exit("error : ".$tgl);
		$data = $_POST;
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					// if($_FILES['file']['size'] <= 250000)
					// {
						$str = "insert into ".$dbname.".log_permintaanhargafile values ('".$notransaksi."','".$supplierid."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						try
						{
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname,"fileupload/rph/$filename");
						}
						catch(PDOException $e)
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
				}else{
					exit("Warning : Format file upload harus .jpg .jpeg .png .pdf .xls .xlsx .doc .docx");
				}
			}
		}
	break;
    case 'loadfiles':
        $arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
		$tab="";
		$tab.="<table>";
		$str="select * from ".$dbname.".log_permintaanhargafile where nomor='".$notransaksi."' and supplierid='".$supplierid."' and status='1'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
            // var_dump(getcriterianame($bar['kriteriaefil'])); exit;
			$tab.="<tr>
				<td style='font-family:Arial, Helvetica, sans-serif;font-size:12px;'>".getcriterianame($bar['kriteriaefil'])."</td>
				<td style='font-family:Arial, Helvetica, sans-serif;font-size:12px;'><a href='fileupload/rph/".$bar['namafile']."' download title='".$bar['namafile']."'>".substr($bar['namafile'],0,40)."...</a></td>
				<td>
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile2('".$notransaksi."','".$supplierid."','".$bar['namafile']."');\" >
				</td>
			</tr>";
		}
		$tab.="<tr>
			<td>
				<select id='kriteriaefil_".$notransaksi."_".$supplierid."'>". $optkriteria."</select>
			</td>
			<td>
				<input type='file' name='upload_".$notransaksi."_".$supplierid."' id='upload_".$notransaksi."_".$supplierid."' style='height:28px;' class='mybutton'>
			</td>
			<td>
				<img id='detail_add' title='Tambah' class='resicon' onclick=\"addfile2('".$notransaksi."','".$supplierid."')\" src='images/plus.png'>
			</td>
		</tr>
		</table>";
		echo $tab;
	break;
    case 'deletefile':
		$str="delete from ".$dbname.".log_permintaanhargafile where nomor='".$notransaksi."' and supplierid='".$supplierid."' and namafile='".$namafile."'";
		try
		{
			$owlPDO->exec($str);
			$path = "fileupload/rph/".$namafile;
			unlink($path);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
}


echo"<div id='progress' style='display:none;border:orange solid 1px;width:150px;position:fixed;right:20px;top:65px;color:#ff0000;font-family:Tahoma;font-size:13px;font-weight:bolder;text-align:center;background-color:#FFFFFF;z-index:10000;'>
Please wait.....!
<img src='images/progress.gif'>
</div>";
?>
