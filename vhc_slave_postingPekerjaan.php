<?php 
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$kode_jns=checkPostGet('jns_id','');
$lokasi=$_SESSION['empl']['lokasitugas'];
$user_entry=$_SESSION['standard']['userid'];
$kode_vhc=checkPostGet('kode_vhc','');
$tgl_kerja=tanggalsystem(checkPostGet('tglKerja',''));
$kmhmAwal=checkPostGet('kmhmAwal','');
$kmhmAkhir=checkPostGet('kmhmAkhir','');
$satuan=checkPostGet('satuan','');
$jnsBbm=checkPostGet('jnsBbm','');
$jumlahBbm=checkPostGet('jumlah','');
$notransaksi_head=checkPostGet('no_trans','');
$proses=checkPostGet('proses','');
$kdVhc=checkPostGet('kdVhc','');
$statKary=0;

$sOrg="select kodeorganisasi from ".$dbname.".organisasi where  kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";//tipe in ('KEBUN','KANWIL','TRAKSI')";
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$kodeOrg="";
while($rOrg=$res->fetch())
{
    $kodeOrg.="'".$rOrg['kodeorganisasi']."',";
}
$pnjgn=strlen($kodeOrg)-1;

$personPosting = getPostingJabatan('traksi');
  
switch($proses)
{
        case'load_data_header':
        echo"
        <table cellspacing='1' cellpadding=5 border='0' class=sortable>
        <thead>
        <tr class=\"rowheader\">
        <td align=center>No.</td>
        <td align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td align=center>".$_SESSION['lang']['jenisvch']."</td>
        <td align=center>".$_SESSION['lang']['kodevhc']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <th align=center>".$_SESSION['lang']['nopol']."</th>
        <td align=center>".$_SESSION['lang']['satuan']."</td>
		<td align=center>".$_SESSION['lang']['vhc_kmhm_awal']."</td>
        <td align=center>".$_SESSION['lang']['vhc_kmhm_akhir']."</td>
        <td align=center>".$_SESSION['lang']['vhc_jenis_bbm']."</td>
        <td align=center>".$_SESSION['lang']['vhc_jumlah_bbm']."</td>
        <td align=center><input type=checkbox id=chkAll onclick=selectAll()></td>
        </tr></thead><tbody id=contentIsi>";

        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;
		$maxdisplay=($page*$limit);
		$no=0;
		$no=$maxdisplay;

        $ql2="select count(*) as jmlhrow from ".$dbname.".vhc_runht where kodeorg='".$lokasi."' order by notransaksi,posting desc"; //echo $ql2;
        $res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$res->fetch()){
                $jlhbrs= $jsl->jmlhrow;
        }
        $ql2="select kodevhc,nopol from ".$dbname.".vhc_5master"; //echo $ql2;
        $res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$res->fetch()){
                $nopoll= $jsl->nopol;
        }

        $sql="select b.kmhmawal as kmhmawal,(b.kmhmawal+sum(jumlah)) as   
              kmhmakhir,sum(jumlah) as totkmhm,b.satuan,a.notransaksi,a.jenisvhc,a.jenisbbm,a.kodevhc,a.tanggal, 
              a.jlhbbm,a.posting,a.updateby from ".$dbname.".vhc_runht a
              left join ".$dbname.".vhc_rundt b on a.notransaksi=b.notransaksi where kodeorg like '%".$_SESSION['empl']['lokasitugas']."%'  and a.posting='0'
                  group by b.notransaksi order by tanggal asc, a.notransaksi asc limit ".$offset.",".$limit."";
        $resc=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $resc->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$resc->fetch())
        {
			
				#kmhmawal ambil paling kecil
				$strz="select kmhmawal from ".$dbname.".vhc_rundt where notransaksi='".$res['notransaksi']."' order by kmhmawal asc limit 1 ";
				$resz=$owlPDO->query($strz) or die(print " Gagal: ".PDOException::getMessage());
				$resz->setFetchMode(PDO::FETCH_ASSOC);
				$barz=$resz->fetch();
					$kmhmawaldata=$barz['kmhmawal'];
					$kmhmakhirdata=$kmhmawaldata+$res['totkmhm'];
				/*
				<td align=right>".number_format($res['kmhmawal'],0)."</td>
                <td align=right>".number_format($res['kmhmakhir'],0)."</td>
				*/
			//singkong
                $optnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$res['kodevhc']."'");

                $sbrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['jenisbbm']."'";
                $res2=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
                $res2->setFetchMode(PDO::FETCH_ASSOC);
                $rbrg=$res2->fetch();
                $rbrg['namabarang'];
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td align=center>".$no."</td>
                <td align=center id=notransaksi_".$no.">".$res['notransaksi']."</td>
                <td align=center>".$res['jenisvhc']."</td>
                <td align=left  id=kdvhc_".$no.">".$res['kodevhc']."</td>
                <td align=center id=tgl_data_".$no.">".tanggalnormal($res['tanggal'])."</td> 
                <td align=left>".$optnopol[$res['kodevhc']]."</td>
                <td align=center>".$res['satuan']."</td>
				<td align=right>".number_format($kmhmawaldata,2)."</td>
                <td align=right>".number_format($kmhmakhirdata,2)."</td>
				
				
                
                <td align=center>".$rbrg['namabarang']."</td>
                <td align=right>".$res['jlhbbm']."</td>";
                $sCek="select kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
                $res3=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $res3->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=$res3->fetch();
                if(in_array($rCek['kodejabatan'],$personPosting))
                {
                        echo"<td  align=center>
                        <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">";
                        if($res['posting']<1  and $_SESSION['empl']['lokasitugas']==substr($res['notransaksi'],0,4))
                        {
                                echo"<input type=checkbox id=checkDt_".$no." title=".$_SESSION['lang']['belumposting']."  onclick=postData(".$no.")>";
                        }
                        else
                        {
                                echo "&nbsp;".$_SESSION['lang']['posting'];
                        }
                        echo"</td>";
                }
                else
                {
                   
                        if($res['posting']<1  and $_SESSION['empl']['lokasitugas']==substr($res['notransaksi'],0,4))
                        {
                        echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\"> [".$_SESSION['lang']['belumposting']."] </td>";
                        }
                        else
                        {
                                echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">  [".$_SESSION['lang']['posting']."] </td>";
                        }
                }
                echo"</tr>";

        }
        echo"<tr class=rowheader><td colspan=12 align=center><div style=display:none id=tmblPosting><button class=mybutton onclick=postingData()>".$_SESSION['lang']['posting']."</button></div></td></tr>";
        echo" <tr><td colspan=11 align=center><div id=btnNextSmua style=display:block>
                                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
                                <br />
                                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                                </div>
                                </td>
                                </tr></tbody>
                    </table>";
        break;
	
    case 'cari_transaksi':
        $where=$whr="";
        if($_POST['txtSearch']!='')
                        {
                                $where.=" and a.notransaksi LIKE  '%".$_POST['txtSearch']."%'";
                                $whr.=" and notransaksi LIKE  '%".$_POST['txtSearch']."%'";
                        }

                        if($_POST['txtTglCr']!='')
                        {
                            $bln=explode("-",$_POST['txtTglCr']);
                            $cek=$bln[2]."-".$bln[1];
                            if($_POST['txtTgl']!='')
                            {
                                if($cek!=$_POST['txtTgl'])
                                {
                                    exit("Error:Tanggal tidak sama dengan periode");
                                }
                            }
                            $where.="and tanggal='".$bln[2]."-".$bln[1]."-".$bln[0]."'";
                            $whr.="and tanggal='".$bln[2]."-".$bln[1]."-".$bln[0]."'";
                        }
                        if($_POST['txtTgl']!='')
                        {
                                $where.=" and tanggal LIKE  '".$_POST['txtTgl']."%' ";
                                $whr.=" and tanggal LIKE  '".$_POST['txtTgl']."%' ";
                        }

                        if($_POST['statId']!='')
                        {
                                $where.=" and posting='".$_POST['statId']."'";
                                $whr.=" and posting='".$_POST['statId']."'";
                        }
                        else
                        {
                                $where.=" and posting='0'";
                                $whr.=" and posting='0'";
                        }
                        if($_POST['kdVhc']!='')
                        {
                            $where.=" and a.kodevhc like '%".$_POST['kdVhc']."%'";
                            $whr.=" and kodevhc='".$_POST['kdVhc']."'";
                        }
                $limit=20;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
                
             
                $ql2="select count(notransaksi) as jmlhrow from ".$dbname.".vhc_runht 
                      where  kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' ".$whr."
                      order by notransaksi desc"; //echo $ql2;
                        
                $strx="select b.kmhmawal as kmhmawal,(b.kmhmawal+sum(jumlah)) as
                        kmhmakhir,sum(jumlah) as totkmhm,b.satuan,a.notransaksi,a.jenisvhc,a.jenisbbm,a.kodevhc,a.tanggal,a.jlhbbm,a.posting from ".$dbname.".vhc_runht a
                        left join ".$dbname.".vhc_rundt b on a.notransaksi=b.notransaksi where kodeorg like '%".$_SESSION['empl']['lokasitugas']."%'   
                        ".$where." and a.posting='0'
                        group by a.notransaksi order by notransaksi  limit ".$offset.",".$limit."";
                        
         echo"
        <div style='overflow:auto; height:550px;'>
        <table cellspacing='1' border='0' class=\"sortable\">
        <thead>
        <tr class=\"rowheader\">
        <td align=center>No.</td>
        <td align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td align=center>".$_SESSION['lang']['jenisvch']."</td>
        <td align=center>".$_SESSION['lang']['kodevhc']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=center>".$_SESSION['lang']['satuan']."</td>
		<td align=center>".$_SESSION['lang']['vhc_kmhm_awal']."</td>
        <td align=center>".$_SESSION['lang']['vhc_kmhm_akhir']."</td>
        
        <td align=center>".$_SESSION['lang']['vhc_jenis_bbm']."</td>
        <td align=center>".$_SESSION['lang']['vhc_jumlah_bbm']."</td>
        <td align=center><input type=checkbox id=chkAll onclick=selectAll()></td>
        </tr></thead><tbody  id=contentIsi>";	
            $res3=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $res3->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$res3->fetch()){
                $jlhbrs= $jsl->jmlhrow;
                }
            $res4=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
            $res4->setFetchMode(PDO::FETCH_ASSOC);                
            $numrows=owlBaris($res4);
                        if($numrows<1)
                        {
                                echo"<tr class=rowcontent><td colspan=11>Not Found</td></tr>";
                        }
                        else
                        {
                            while($res=$res4->fetch())
                            {
								
								#kmhmawal ambil paling kecil
								$strz="select kmhmawal from ".$dbname.".vhc_rundt where notransaksi='".$res['notransaksi']."' order by kmhmawal asc limit 1 ";
								$resz=$owlPDO->query($strz) or die(print " Gagal: ".PDOException::getMessage());
								$resz->setFetchMode(PDO::FETCH_ASSOC);
								$barz=$resz->fetch();
									$kmhmawaldata=$barz['kmhmawal'];
									$kmhmakhirdata=$kmhmawaldata+$res['totkmhm'];
									
									
                                    $sbrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['jenisbbm']."'";
                                    $res5=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
                                    $res5->setFetchMode(PDO::FETCH_ASSOC);
                                    $rbrg=$res5->fetch();
                                    $rbrg['namabarang'];
                                    $no+=1;
                                    echo"
                                    <tr class=rowcontent>
                                    <td  align=center >".$no."</td>
                                    <td align=center  id=notransaksi_".$no.">".$res['notransaksi']."</td>
                                    <td align=center>".$res['jenisvhc']."</td>
                                    <td align=center  id=kdvhc_".$no.">".$res['kodevhc']."</td>
                                    <td align=center id=tgl_data_".$no.">".tanggalnormal($res['tanggal'])."</td>
                                    <td align=center>".$res['satuan']."</td>
									<td align=right>".number_format($kmhmawaldata,2)."</td>
                                    <td align=right>".number_format($kmhmakhirdata,2)."</td>
                                    
                                    <td align=center>".$rbrg['namabarang']."</td>
                                    <td align=right>".$res['jlhbbm']."</td>";
                                    $sCek="select kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
                                    $res6=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                                    $res6->setFetchMode(PDO::FETCH_ASSOC);
                                    $rCek=$res6->fetch();
                                    if(in_array($rCek['kodejabatan'],$personPosting))
                                    {
                                    echo"
                                    <td align=center>
                                    <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">";
                                    if($res['posting']<1  and $_SESSION['empl']['lokasitugas']==substr($res['notransaksi'],0,4))
                                    {
                                            echo"<input type=checkbox id=checkDt_".$no." title=".$_SESSION['lang']['belumposting']." onclick=postData(".$no.")>";
                                    }
                                    else
                                    {
                                            echo "&nbsp;".$_SESSION['lang']['posting'];
                                    }
                                    echo"</td>";}
                                    else
                                    {
                                            if($res['posting']<1  and $_SESSION['empl']['lokasitugas']==substr($res['notransaksi'],0,4))
                                            {
                                            echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\"> [".$_SESSION['lang']['belumposting']."] </td>";
                                            }
                                            else
                                            {
                                                    echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">  [".$_SESSION['lang']['posting']."] </td>";
                                            }
                                    }

                            }
                            echo"<tr class=rowheader><td colspan=11 align=center><div style=display:none id=tmblPosting><button class=mybutton onclick=postingData()>".$_SESSION['lang']['posting']."</button></div></td></tr>";
                            echo" <tr><td colspan=11 align=center><div id=btnNextSmua style=display:block>
                                                    ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
                                                    <br />
                                                    <button class=mybutton onclick=cariData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                                                    <button class=mybutton onclick=cariData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                                                    </div>
                                                    </td>
                                                    </tr>";
                            echo" </tbody></table></div>";
                        }	
                break;
        case'postData':
        echo "warning:masuk";
		exit("error");
                $scek="select kodeorg,updateby from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";//echo "warning".$scek;
                $res6=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                $res6->setFetchMode(PDO::FETCH_ASSOC);
                $rcek=$res6->fetch();
                $sCek="select kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
                $res7=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $res7->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=$res7->fetch();
                if($rCek['kodejabatan']!=$personPosting)
                {
                        echo"warning: You are not authorized";
                        exit();
                }		
				$postedtime=date('Y-m-d H:i:s');
                $sudPost="update ".$dbname.".vhc_runht set posting='1',postingby='".$user_entry."',postedtime='".$postedtime."' where notransaksi='".$notransaksi_head."'";
                try{$owlPDO->exec($sudPost); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;


        case'postingDa':
        // echo "warning:masukbanyak";
		// exit("error");
		// exit();
			$param=$_POST;
            // if(is_array($_POST['notransaksi'])){
			$sCek="select kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
			$res3=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$res3->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=$res3->fetch();
			if(in_array($rCek['kodejabatan'],$personPosting)){
				
				$arrUpload = array();
				foreach($_POST['notransaksi'] as $barisNtrns =>$dtrnotrans){
					#=== Cek if Upload Absensi ===
					$queryH = selectQuery($dbname,'vhc_runht',"*","notransaksi='".$dtrnotrans."'");
					$dataH = fetchData($queryH);
					
					$queryD = selectQuery($dbname,'vhc_runhk',"*","notransaksi='".$dtrnotrans."' and upah>'0'");
					$dataD = fetchData($queryD);
					foreach($dataD as $row){
						$arrUpload[]['nik'] = $row['idkaryawan'];
					}
					#query pengecekan apakah FP aktif / tidak
					$str = "select status from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataH[0]['kodeorg']."' and tanggal <= '".$dataH[0]['tanggal']."'";
					$res = fetchdata($str);
					$statusfp    = $res[0]['status'];//1 aktif,0 tidak
					$tipevalidasi= $res[0]['tipevalidasi'];
					$detailexp   = explode(",",$res[0]['detailvalidasi']);
					foreach($detailexp as $vald){
						$detval[$vald]=$vald;
					}
					#validasi posting
					validasiInput($dataH[0]['kodeorg'],'','TRKPOST',$dataH[0]['tanggal'],$exit='1');


					if($statusfp==1 and count($arrUpload)>0){
						validasifp($tipevalidasi,$detval,'TRK',$arrUpload,$dataH[0]['tanggal'],'1');
						
						/* $countUpload=0;
						foreach($arrUpload as $row){
							$str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."' limit 1";
							$bar = fetchdata($str)[0];
							if($row['nik'] != $bar['karyawanid']){
								$no++;
								$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$row['nik']."'");
								$nikkary = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$row['nik']."'");
								$errorUpload .= $no.". ".$nikkary[$row['nik']]." = ".$optNamaKaryawan[$row['nik']]."<br>";
								$countUpload = $countUpload + 1;
							}
						}
						if($countUpload > 0){
							exit("Warning: Absen fingerprint untuk karyawan dg NIK : <br>".$errorUpload."belum ada.<br>Silahkan lakukan proses melalui menu : SDM - Proses - Fingerprint.");
						} */
					}
				}

				
				foreach($_POST['notransaksi'] as $barisNtrns =>$dtrnotrans){
					$dt=0;
					$str="select count(*) as dt from ".$dbname.".vhc_rundt where notransaksi='".$dtrnotrans."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$dt=$bar['dt'];
					
					if($dt<1){
						exit("Warning:Data Detail Pekerjaan kosong, silahkan dihapus transaksi ".$dtrnotrans."");
					}
					
					$tglData=tanggalsystem($_POST['tglData'][$barisNtrns]);
					$kdvhc=$_POST['kdVhc'][$barisNtrns];
					$tgl=substr($tglData,0,4);
					$tglm=substr($tglData,4,2);
					$tgld=substr($tglData,6,2);
					$period=$tgl."-".$tglm."-".$tgld;
					//cek kendaraan untuk //cek operator jika kendaraan bukan sewa
					$sStatKend="select kepemilikan from ".$dbname.".vhc_5master where kodevhc='".$kdvhc."'";
					$res7=$owlPDO->query($sStatKend) or die(print " Gagal: ".PDOException::getMessage());
					$res7->setFetchMode(PDO::FETCH_ASSOC);
					$rStatKend=$res7->fetch();
					if($rStatKend['kepemilikan']!=0){	
						$sOpt="select idkaryawan from ".$dbname.".vhc_runhk  where notransaksi='".$dtrnotrans."'";
						$res8=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
						$res8->setFetchMode(PDO::FETCH_ASSOC);
						while($rCopt=$res8->fetch()){
							$statKary=0;
							$sPost="select a.satuan from ".$dbname.".vhc_rundt a 
							left join ".$dbname.".vhc_runhk b on a.notransaksi=b.notransaksi 
							left join ".$dbname.".vhc_runht c on c.notransaksi=b.notransaksi 
							where b.idkaryawan='".$rCopt['idkaryawan']."' and c.tanggal like '".$period."%' group by a.satuan";
							$res9=$owlPDO->query($sPost) or die(print " Gagal: ".PDOException::getMessage());
							$res9->setFetchMode(PDO::FETCH_ASSOC);
							$numrows=owlBaris($res9);
							$rPost=$numrows;
							if($rPost>1){
								$statKary+=1;
							}
						}
						// if($statKary!=0){
							// echo"warning: Fail, there are ".$statKary." person, with different UOM";
							// exit();
						// }else{
							$postedtime=date('Y-m-d H:i:s');
							$sudPost="update ".$dbname.".vhc_runht set posting='1',postingby='".$_SESSION['standard']['userid']."',postedtime='".$postedtime."' where notransaksi='".$dtrnotrans."'";
							try{$owlPDO->exec($sudPost); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
						// }
					}else{
						$postedtime=date('Y-m-d H:i:s');
						$sudPost="update ".$dbname.".vhc_runht set posting='1',postingby='".$_SESSION['standard']['userid']."',postedtime='".$postedtime."' where notransaksi='".$dtrnotrans."'";
						try{$owlPDO->exec($sudPost); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
					}
				}
            }else{
               exit(" Gagal : Jabatan anda belum terdaftar untuk posting transaksi");
            }
        break;
        case'postSat':
		// echo "warning:postSat";
		// exit("error");
		// exit();
		
		
		$param=$_POST;
        $tgl=substr($_POST['tglData'],0,4);
        $tglm=substr($_POST['tanggal'],5,2);
        $period=$tgl."-".$tglm;
		
		#cek apakah ada detail 
		
		$dt=0;
		$str="select count(*) as dt from ".$dbname.".vhc_rundt where notransaksi='".$_POST['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			$dt=$bar['dt'];
		
		if($dt<1){
			exit("Warning:Data Detail Pekerjaan kosong, silahkan dihapus transaksi ini");
		}
		
		
		#=== Cek if Upload Absensi ===
		$queryH = selectQuery($dbname,'vhc_runht',"*","notransaksi='".$param['notransaksi']."'");
		$dataH = fetchData($queryH);
		
		$arrUpload = array();
		$queryD = selectQuery($dbname,'vhc_runhk',"*","notransaksi='".$param['notransaksi']."' and upah>'0'");
		$dataD = fetchData($queryD);
		foreach($dataD as $row){
			$arrUpload[]['nik'] = $row['idkaryawan'];
		}


		#query pengecekan apakah FP aktif / tidak
		$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataH[0]['kodeorg']."' and tanggal <= '".$dataH[0]['tanggal']."'";
		$res = fetchdata($str);
		$statusfp    = $res[0]['status'];//1 aktif,0 tidak
		$tipevalidasi= $res[0]['tipevalidasi'];
		$detailexp   = explode(",",$res[0]['detailvalidasi']);
		foreach($detailexp as $vald){
			$detval[$vald]=$vald;
		}
		
		#validasi posting
		validasiInput($dataH[0]['kodeorg'],'','TRKPOST',$dataH[0]['tanggal'],$exit='1');

		
		if($statusfp==1 and count($arrUpload)>0){
			validasifp($tipevalidasi,$detval,'TRK',$arrUpload,$dataH[0]['tanggal'],'1');
			
			// $countUpload=0;
			// foreach($arrUpload as $row){
				// $str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."' limit 1";
				// $bar = fetchdata($str)[0];
				// if($row['nik'] != $bar['karyawanid']){
					// $no++;
					// $optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$row['nik']."'");
					// $nikkary = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$row['nik']."'");
					// $errorUpload .= $no.". ".$nikkary[$row['nik']]." = ".$optNamaKaryawan[$row['nik']]."<br>";
					// $countUpload = $countUpload + 1;
				// }
			// }
			// if($countUpload > 0){
				// exit("Warning: Absen fingerprint untuk karyawan dg NIK : <br>".$errorUpload."belum ada.<br>Silahkan lakukan proses melalui menu : SDM - Proses - Fingerprint.");
			// }
		}
		
		
		
		
		
        $sOpt="select idkaryawan from ".$dbname.".vhc_runhk where notransaksi='".$_POST['notransaksi']."'";
        $resw=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
        $resw->setFetchMode(PDO::FETCH_ASSOC);
		while($rCopt=$resw->fetch()){
				$sPost="select a.satuan from ".$dbname.".vhc_rundt a 
				left join ".$dbname.".vhc_runhk b on a.notransaksi=b.notransaksi 
				left join ".$dbname.".vhc_runht c on c.notransaksi=b.notransaksi 
				where b.idkaryawan='".$rCopt['idkaryawan']."' and c.tanggal like '%".$period."%' group by a.satuan";
				$resd=$owlPDO->query($sPost) or die(print " Gagal: ".PDOException::getMessage());
				$resd->setFetchMode(PDO::FETCH_ASSOC);
				$numrows=owlBaris($resd);
				$rPost=$numrows;
				if($rPost>1){
					$statKary+=1;
				}
		}
		// if($statKary!=0){
			// echo"warning: Fail, there are ".$statKary." person, with different UOM";
			// exit();
		// }else{
			$postedtime=date('Y-m-d H:i:s');
			$sudPost="update ".$dbname.".vhc_runht set posting='1',postingby='".$_SESSION['standard']['userid']."',postedtime='".$postedtime."' where notransaksi='".$_POST['notransaksi']."'";
			try{$owlPDO->exec($sudPost); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		// }
        break;
        case'postingByTrip':
		
        //echo "warning:masuk";
        $sNotrans="select a.*,b.idkaryawan,b.posisi,c.alokasibiaya,c.jenispekerjaan,c.jumlahrit,c.beratmuatan from 
        ".$dbname.".vhc_runht a inner join ".$dbname.".vhc_runhk b on a.notransaksi=b.notransaksi 
        inner join ".$dbname.".vhc_rundt c on c.notransaksi=b.notransaksi
        where a.notransaksi='".$notransaksi_head."'"; //echo"warning:".$sNotrans;
        $resq=$owlPDO->query($sNotrans) or die(print " Gagal: ".PDOException::getMessage());
        $resq->setFetchMode(PDO::FETCH_ASSOC);
        while($rNotrans=$resq->fetch())
        {
                $rNotrans['alokasibiaya']=substr($rNotrans['alokasibiaya'],0,4);
                $sPremi="select keycode from ".$dbname.".setup_mappremi where kodeorg='".$rNotrans['alokasibiaya']."'";//	echo"warning:".$sPremi;
                $resq1=$owlPDO->query($sPremi) or die(print " Gagal: ".PDOException::getMessage());
                $resq1->setFetchMode(PDO::FETCH_ASSOC);
                $rPremi=$resq1->fetch();
                if($rNotrans['premi']=='1')
                {	
                        if($rPremi['keycode']=='TRANS02')
                        {
                                $sKbn="select keycode,jumlahtrip,nomor,rate from ".$dbname.".kebun_5ratetransport where keycode='".$rPremi['keycode']."' 
                                and tipeangkutan='".$rNotrans['jenispekerjaan']."' and jobposition='".$rNotrans['posisi']."'";
                                $resq2=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
                                $resq2->setFetchMode(PDO::FETCH_ASSOC);
                                $rKbn=$resq2->fetch();
                                if($rNotrans['jumlahrit']>=$rKbn['jumlahtrip'])
                                {
                                        $set=" premi='".$rKbn['rate']."'";
                                        //echo "warning:masuk a".$set;
                                }
                                else if($rNotrans['jumlahrit']<$rKbn['jumlahtrip'])
                                {
                                        $set=" premi='0'";
                                }
                                $sIsi="update ".$dbname.".vhc_runhk set ".$set." where notransaksi='".$rNotrans['notransaksi']."' 
                                and idkaryawan='".$rNotrans['idkaryawan']."' and posisi='".$rNotrans['posisi']."'";
                                try{
                                    $owlPDO->exec($sIsi); 
									$postedtime=date('Y-m-d H:i:s');
                                    $sHead="update ".$dbname.".vhc_runht set posting='1',postingby='".$user_entry."',postedtime='".$postedtime."' where notransaksi='".$notransaksi_head."'";	
                                    try{$owlPDO->exec($sHead); }catch (PDOException $e) {print " Gagal  update flag post!: " . $e->getMessage() . "\n"; die(); }
                                }catch (PDOException $e) {print " Gagal  update idkaryawan!: " . $e->getMessage() . "\n"; die(); }
                        }
                        elseif($rPremi['keycode']=='TRANS01')
                        {

                                $sKbn="select keycode,jaraksampai,jarakdari,nomor,rate from ".$dbname.".kebun_5ratetransport where keycode='".$rPremi['keycode']."' 
                                and tipeangkutan='".$rNotrans['jenispekerjaan']."' and jobposition='".$rNotrans['posisi']."'";
                                $resq2=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
                                $resq2->setFetchMode(PDO::FETCH_ASSOC);
                                $rKbn=$resq2->fetch();
                                if($rNotrans['jumlah']>=$rKbn['jaraksampai'])
                                {
                                        $setBasis=" premi='".$rKbn['rate']."'";
                                }
                                else if($rNotrans['jumlah']<$rKbn['jarakdari'])
                                {
                                        $setBasis=" premi='0'";
                                }
                                else if(($rNotrans['jumlah']>$rKbn['jarakdari'])&&($rNotrans['jumlah']<$rKbn['jaraksampai']))
                                {
                                        $setBasis=" premi='".$rKbn['rate']."'";
                                }

                                $sIsi="update ".$dbname.".vhc_runhk set ".$setBasis." where notransaksi='".$rNotrans['notransaksi']."' 
                                and idkaryawan='".$rNotrans['idkaryawan']."' and posisi='".$rNotrans['posisi']."'";//;echo "warning:".$sIsi."____2";exit();
                                try{
                                        $owlPDO->exec($sIsi); 
										$postedtime=date('Y-m-d H:i:s');
                                        $sHead="update ".$dbname.".vhc_runht set posting='1',postingby='".$user_entry."',postedtime='".$postedtime."' where notransaksi='".$notransaksi_head."'";	
                                        try{$owlPDO->exec($sHead); }catch (PDOException $e) {print " Gagal  update flag posting!: " . $e->getMessage() . "\n"; die(); }
                                }catch (PDOException $e) {print " Gagal  update basis!: " . $e->getMessage() . "\n"; die(); }
                        }
                }
        }
        break;
        default:
        break;	
}
?>