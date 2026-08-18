<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/fpdf.php');
include_once('lib/zLib.php');
$nmBarang=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$method=$_POST['method'];
$kolom= isset($_POST['kolom'])? $_POST['kolom']: '';


$explodepp=explode("/",$_POST['nopp']);
$kdunit=$explodepp[4];
$tporg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
$tpunit=$tporg[$kdunit];



switch($method){
    case'get_form_approval':

        $sql="select * from ".$dbname.".log_prapoht where nopp='".$_POST['nopp']."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
        $rest=$query->fetch();
        
        for($i=1;$i<6;$i++) {

            if($_SESSION['standard']['userid']==$rest['persetujuan'.$i]){
                if(($rest['persetujuan5']!='')&&($rest['persetujuan5']!='0')){
                    echo"<div id=approve>
                    <fieldset>
                    <legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
                    <table cellspacing=1 border=0>
                    <tr>
                    <td colspan=3>
                    Submit to Purchasing Dept.</td></tr>
                    <tr>
                    <td>".$_SESSION['lang']['note']."</td>
                    <td>:</td>
                    <td><input type=text id=note name=note class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
                    </tr>
                    <tr><td colspan=3 align=center>
                    <button class=mybutton onclick=close_pp() >".$_SESSION['lang']['ok']."</button></td></tr></table>
                    </fieldset>
                    </div>";
                } else {
                    echo"
                        <div id=test style=display:block>
                        <fieldset>
                        <legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
                        <table cellspacing=1 border=0>
                        <tr>
                        <td colspan=3>
                         Submit to the next approval :</td>
                        </tr>
                        <td>".$_SESSION['lang']['namakaryawan']."</td>
                        <td>:</td>
                        <td valign=top>";
                    $kd=substr($_POST['nopp'],17,2);
                    $unit=substr($_POST['nopp'],15,4);
                    $optPur='';
                    
                    $setujuKe=$i+1;
                    $str="select a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a"
                            . " left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid  "
                            . " where applikasi='PP".$setujuKe."' and kodeunit='".$unit."' and "
                            . " a.karyawanid!='".$_SESSION['standard']['userid']."' ";
                    $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$qry->setFetchMode(PDO::FETCH_ASSOC);
                    while($rkry=$qry->fetch()) {
                        $optPur.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
                    }
                    echo"
                        <select id=user_id name=user_id  style=\"width:150px;\">
                                ".$optPur." 
                        </select></td></tr>
                        <tr>
                        <tr>
                        <td>".$_SESSION['lang']['note']."</td>
                        <td>:</td>
                        <td><input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:145px;\" /></td>
                        </tr>
                        <td><td><td>
                        <button class=mybutton onclick=forward_pp() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>

                        <button class=mybutton onclick=cancel_pp() title=\" Close this form \">".$_SESSION['lang']['cancel']."</button>
                        </td></tr></table> 
                        <input type=hidden name=method id=method  /> 
                        <input type=hidden name=user_id id=user_id value=".$_SESSION['standard']['userid']." />
                        <input type=hidden name=nopp id=nopp value=".$_POST['nopp']."  /> 
                        </fieldset></div>
                        ";
                    
                    #hanya persetujuan ke 3 yang bisa ke purch
                    // if($i>=3 and $optPur=='')
					
					if($tpunit=='HOLDING'){
						
						if($i>=2){
							echo"
							<div id=approve style=display:block>
							<fieldset>
							<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
							<table cellspacing=1 border=0>
							<tr>
							<td colspan=3>
							 Approve and submit directly to Purchasing Dept.</td></tr>
							<tr>
							<td>".$_SESSION['lang']['note']."t</td>
							<td>:</td>
							<td><input type=text id=note name=note class=myinputtext onClick=\"return tanpa_kutip(event)\" style=\"width:190px;\" /></td>
							</tr>
							<tr><td colspan=3 align=center>
							<button class=mybutton onclick=close_pp() title=\"You are agree to this PR and submit it to Purchasing Dept. \"  >".$_SESSION['lang']['kePurchaser']."</button><button class=mybutton onclick=cancel_pp() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button></td></tr></table>
							</fieldset>
							</div>
							";
						}
						
					}else{
						
						if($i>=4){
							echo"
							<div id=approve style=display:block>
							<fieldset>
							<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
							<table cellspacing=1 border=0>
							<tr>
							<td colspan=3>
							 Approve and submit directly to Purchasing Dept.</td></tr>
							<tr>
							<td>".$_SESSION['lang']['note']."t</td>
							<td>:</td>
							<td><input type=text id=note name=note class=myinputtext onClick=\"return tanpa_kutip(event)\" style=\"width:190px;\" /></td>
							</tr>
							<tr><td colspan=3 align=center>
							<button class=mybutton onclick=close_pp() title=\"You are agree to this PR and submit it to Purchasing Dept. \"  >".$_SESSION['lang']['kePurchaser']."</button><button class=mybutton onclick=cancel_pp() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button></td></tr></table>
							</fieldset>
							</div>
							";
						}
							
					}
				
                    
                }
            }
        }
        break;
    
    case 'get_form_rejected':
	
	
		#lakukan pengecekan apakah ada barang yg sudah di alokasikan ke purchaser
		// $str="select count(*) as jumlah from ".$dbname.".log_prapodt where nopp='".$_POST['nopp']."' and create_po!=0 ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// 	$jumalokasi=$bar['jumlah'];
			
		// if($jumalokasi>0){
		// 	echo" Sudah ada barang yang dialokasikan, harap gunakan <b>Tolak Beberapa</b>";
		// }else{
			 echo"<div id=rejected_form>
			<fieldset>
			<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
			<table cellspacing=1 border=0>
			<tr>
			<td colspan=3>
			 PR Rejection form </td></tr>
			<tr>
			<td>".$_SESSION['lang']['note']."</td>
			<td>:</td>
			<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
			</tr>
			<tr><td colspan=3 align=center>
			<button class=mybutton onclick=\"rejected_pp_proses(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
			</td></tr></table>
			</fieldset>
			</div>";
		// }
			
	
       
        break;
		
        case 'get_form_rejected_some':
        $nopp=$_POST['nopp'];
            
        $sql="select * from ".$dbname.".log_prapodt where `nopp`='".$nopp."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);

        echo"
        <fieldset>
        <legend><input class=myinputtext disabled type=text id=rnopp name=rnopp value=".$nopp." readonly=readonly /></legend>
        <div style=overflow:auto;max-width=850px;max-height:350px;>
        <table cellspacing=1 border=0 class=sortable max-width=850px>
        <thead class=rowheader>
        <tr>
        <td align=center>No.</td>
        <td align=center>".$_SESSION['lang']['kodebarang']."</td>
        <td align=center>".$_SESSION['lang']['namabarang']."</td>
        <td align=center>".$_SESSION['lang']['satuan']."</td>
        <td align=center style='display:none'>".$_SESSION['lang']['kodeanggaran']."</td>
        <td align=center>".$_SESSION['lang']['jmlhDiminta']."</td>
        <td align=center>".$_SESSION['lang']['tanggalSdt']."</td>
        <td align=center>".$_SESSION['lang']['keterangan']."</td>
        <td align=center>".$_SESSION['lang']['alasanDtolak']."</td>
        <td>Action</td>
        </tr>
        </thead>

        <tbody id=reject_some class=rowcontent>

        ";
        while($res=$query->fetch()){
        $no+=1;
        $sql2="select * from ".$dbname.".log_5masterbarang where `kodebarang`='".$res['kodebarang']."'";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_ASSOC);
        $res2=$query2->fetch();
        if($res['status']==3) {
                $dis="disabled=disabled";
                $stadData="checked";
        } else {
                $dis="";
                $stadData="";
        }
        
		//diubah agar mengakomodasi retur parsial barang
        if($res['create_po']==1 || $res['purchaser']!=0) {
            $disablepo="disabled=disabled";
            $titlepo="title='barang sudah di PO / barang sudah dialokasikan ke purchaser'";
        } else {
           $disablepo=""; 
           $titlepo="";
           
        }
        echo"<tr ".$titlepo." >
        <td align=center>".$no."</td>
        <td align=center id=kd_brg_".$no.">".$res['kodebarang']."</td>
        <td>".$res2['namabarang']."</td>
        <td>".$res2['satuan']."</td>
        <td id=kd_angrn_".$no." style='display:none'>".$res['kd_anggran']."</td>
        <td align=right id=jmlh_".$no.">".$res['jumlah']."</td>
        <td align=center id=tgl_".$no.">".$res['tgl_sdt']."</td>
        <td id=ket_".$no.">".$res['keterangan']."</td>
        <td><input style=width:200px type=text id=alsnDtolak_".$no." name=alsnDtolak_".$no." class=myinputtext style=width:100px ".$dis." value='".$res['alasanstatus']."' ".$disablepo." /></td>
        <td align=center><input type=checkbox onclick='checkAlasan(".$no.")' id='tolak_chk_".$no."' ".$stadData." ".$dis." ".$disablepo." /></td>
        </tr>";
        }
        //<button class=mybutton onclick=\"rejected_some('".$nopp."','".$no."','".$kolom."')\" ".$dis." >".$_SESSION['lang']['ditolak']."</button>
        echo"</tbody><tfoot><tr><td colspan=10 align=center><button class=mybutton onclick=\"rejected_some_done('".$nopp."','".$kolom."','".$no."')\" >".$_SESSION['lang']['done']."</button></td></tr></tfoot></table></div></fieldset><input type=hidden id=user_id name=user_id value='".$_SESSION['standard']['userid']."'>";
        break;

		########## ?????????? ##########
        case 'rejected_some_done':
        $user_id=$_POST['user_id'];
        for($i=1;$i<6;$i++)
        {
                $sql="select * from ".$dbname.".log_prapoht where nopp='".$_POST['nopp']."' and persetujuan".$i."='".$user_id."' ";
                $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $query->setFetchMode(PDO::FETCH_ASSOC);  
                        while($res=$query->fetch())
                        {
                                for($i=1;$i<6;$i++)
                                {	
                                        if($res['hasilpersetujuan'.$i]=='')
                                        {
												exit("error : test");
                                                $sql2="update ".$dbname.".log_prapoht set hasilpersetujuan".$i."='1'";
                                        }
                                }
                        }

                        break;
        }
        break;
		########## ?????????? ##########
		
    case 'cari_pp':
        if((isset($_POST['txtSearch']))||(isset($_POST['tglCari'])))
        {
                $txt_search=$_POST['txtSearch'];
                $txt_tgl=tanggalsystem($_POST['tglCari']);
                $txt_tgl_a=substr($txt_tgl,0,4);
                $txt_tgl_b=substr($txt_tgl,4,2);
                $txt_tgl_c=substr($txt_tgl,6,2);
                $txt_tgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
        }
		
		$where = '';
        if($_POST['txtSearch']!='')
        {
            $where="and nopp LIKE  '%".$txt_search."%'  ";
        }
        elseif($_POST['tglCari']!='')
        {
            $where="and tanggal LIKE '%".$txt_tgl."%' ";
        }
        elseif(($txt_tgl!='')&&($txt_search!=''))
        {
            $where="nopp LIKE '%".$txt_search."%' and tanggal LIKE '%".$txt_tgl."%' ";
        }


        if($_POST['pembuat']!='')
        {
            $where.=" and dibuat='".$_POST['pembuat']."'";
        }
        $str="SELECT * FROM ".$dbname.".log_prapoht where (persetujuan1='".$_SESSION['standard']['userid']."' or persetujuan2='".$_SESSION['standard']['userid']."' or persetujuan3='".$_SESSION['standard']['userid']."' or persetujuan4='".$_SESSION['standard']['userid']."' or persetujuan5='".$_SESSION['standard']['userid']."')
                     ".$where." ORDER BY tanggal desc ";
        if($_POST['nmbrg']!='')
        {
            if(strlen($_POST['nmbrg'])>3)
            {

                $where.="and b.kodebarang in (select distinct kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$_POST['nmbrg']."%')";
                $str="SELECT distinct a.* FROM ".$dbname.".log_prapoht a left join ".$dbname.".log_prapodt b on a.nopp=b.nopp
                      where (persetujuan1='".$_SESSION['standard']['userid']."' or persetujuan2='".$_SESSION['standard']['userid']."' or persetujuan3='".$_SESSION['standard']['userid']."' or persetujuan4='".$_SESSION['standard']['userid']."' or persetujuan5='".$_SESSION['standard']['userid']."')
                     ".$where." ORDER BY tanggal desc ";

            }
            else
            {
                exit("Error:Harus 3 Karakter atau lebih");
            }
        }

		  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
          $rCek=owlBaris($res);
                if($rCek>0)
                {
					$no=0;
					    $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch())
                        {
                        $koderorg=substr($bar['nopp'],15,4);
                        $spr="select namaorganisasi from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; //echo $spr;
						$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
						$rep->setFetchMode(PDO::FETCH_OBJ);
                        $bas=$rep->fetch();
                        $no+=1;
                        echo"<tr class=rowcontent id='tr_".$no."'>
                                  <td>".$no."</td>
                                  <td id=td_".$no.">".$bar['nopp']."</td>
                                  <td>".tanggalnormal($bar['tanggal'])."</td>
                                  <td>".$bas->namaorganisasi."</td>
                                  <td align=center>
                                  <img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\"> &nbsp
                                  <img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"previewDetail('".$bar['nopp']."',event);\">    
                                  </td>";      
                                        if($bar['close']==2)
                                        {
                                                $accept=0;
                                                for($i=1;$i<6;$i++)
                                                {
                                                        if($bar['hasilpersetujuan'.$i]=='3')
                                                        {
                                                                $accept=3;
                                                                break;
                                                        }
                                                        elseif($bar['hasilpersetujuan'.$i]=='1')
                                                        {
                                                                $accept=1;

                                                        }
                                                }
                                                if($accept==3) {
                                                        echo"<td colspan=4>".$_SESSION['lang']['ditolak']."</td>";
                                                } elseif($accept==1) {
                                                        echo"<td colspan=4>".$_SESSION['lang']['disetujui']."</td>";
                                                }
                                        }
                                        elseif($bar['close']<2)
                                        {
                                                for($a=1;$a<6;$a++)
                                                {
                                                        if($bar['persetujuan'.$a]!='')
                                                        {
                                                                if(($bar['persetujuan'.$a]==$_SESSION['standard']['userid'])&&(($bar['hasilpersetujuan'.$a]!='')
                                                                and $holdbar['hasilpersetujuan'.$a]!=0))
                                                                {
                                                                        echo"<td colspan=3>&nbsp;</td>";
                                                                }
                                                                elseif(($bar['persetujuan'.$a]==$_SESSION['standard']['userid'])&&($bar['hasilpersetujuan'.$a]=='' 
                                                                or $bar['hasilpersetujuan'.$a]==0))
                                                                {
                                                                echo"
                                                                <td><a href=# onclick=\"get_data_pp('".$bar['nopp']."','".$a."')\">".$_SESSION['lang']['approve']."</a></td>
                                                                <td><a href=# onclick=rejected_pp('".$bar['nopp']."','".$a."') >".$_SESSION['lang']['ditolak']."</a></td>
                                                                <td><a href=# onclick=\"rejected_some_proses('".$bar['nopp']."','".$a."')\" >".$_SESSION['lang']['ditolak_some']."</a></td>";
                                                                
                                                                if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
                                                                {
                                                                    echo"<td><a href=# onclick=tambahBarang('".$bar['nopp']."','".$a."','".$_SESSION['lang']['find']."',event)>".$_SESSION['lang']['edit']." ".$_SESSION['lang']['jumlah']."</a></td>"; 
                                                                }
                                                                else
                                                                {echo"<td></td>";}
                                                               
                                                                }
                                                        }
                                                }
                                        }
                                 for($i=1;$i<6;$i++)
                                 {
                                        //echo $bar['hasilpersetujuan'.$i];
                                        if(($bar['persetujuan'.$i]!='')&&($bar['persetujuan'.$i]!=0))
                                        {	
                                                $kr=$bar['persetujuan'.$i];
                                                $sql="select * from ".$dbname.".datakaryawan where karyawanid='".$kr."'";
												$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
												$query->setFetchMode(PDO::FETCH_ASSOC);
                                                $yrs=$query->fetch();
                                                echo"<td><a href=# onclick=\"cek_status_pp('".$bar['hasilpersetujuan'.$i]."')\">".$yrs['namakaryawan']."</a></td>";
                                        }
                                        else
                                        {
                                                echo"<td>&nbsp;</td>";
                                        }
                                 }				 
                                 echo"</tr>";
                }	 	 	

          }
          else
          {
                echo"<tr class=rowcontent><td colspan=13 align=center>Not Found</td></tr>";  
          }
        break;
        case'tolakBeberapa':
        $tglskrng=date("Y-m-d");
        $adrt=0;
        foreach($_POST['kode_brg'] as $lstKdBrg=>$kdbrg)
        {
            $sUpadate="update ".$dbname.".log_prapodt set status=3, alasanstatus='".$_POST['alsan'][$lstKdBrg]."',
                       ditolakoleh='".$_SESSION['standard']['userid']."'
                       where kodebarang='".$kdbrg."' and nopp='".$_POST['nopp']."'";
            try{
				$owlPDO->exec($sUpadate);
				$adrt+=1;
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
        }

            // exit("Error".$sUpdate);
            if($adrt!=0)
            {
            $sData="select distinct dibuat,persetujuan1,persetujuan2,persetujuan3,persetujuan4,persetujuan5
            from ".$dbname.".log_prapoht where nopp='".$_POST['nopp']."'";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
            $rData=$qData->fetch();
            if($rData['persetujuan1']!='')
                $to=$rData['persetujuan1'];
               if($rData['persetujuan2']!='')
                $to.=",".$rData['persetujuan2'];
                if($rData['persetujuan3']!='')
                $to.=",".$rData['persetujuan3'];
                 if($rData['persetujuan4']!='')
                $to.=",".$rData['persetujuan4'];
                 if($rData['persetujuan5']!='')
                $to.=",".$rData['persetujuan5'];

                    #send an email to incharge person
                    $to=getUserEmail($to);
                    $namakaryawan=getNamaKaryawan($rData['dibuat']);
                    $nmpnlk=getNamaKaryawan($rData['persetujuan'.$_POST['kolom']]);
                     if($_SESSION['language']=='EN'){     
                    $subject="[Notification] Partially or all items on PR No:".$_POST['nopp']." submitted by ".$namakaryawan." rejected by ".$nmpnlk;
                    $body="<html>
                             <head>
                             <body>
                               <dd>Dear Sir/Madam,</dd><br>
                               <br>
                                Purchase Request No:".$_POST['nopp']." rejected by [".$nmpnlk."]  corresponding to below notes:
                               <br>
                               Item rejected : <ul>";
                    $sBrg="select kodebarang,alasanstatus from ".$dbname.".log_prapodt where nopp='".$_POST['nopp']."' and status='3'";
					$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
					$qBrg->setFetchMode(PDO::FETCH_ASSOC);
                    while($rBrg=$qBrg->fetch())
                    {
                       $body.="<li>".$nmBarang[$rBrg['kodebarang']].", note : ".$rBrg['alasanstatus']."</li>";
                    }
                    $body.="</ul><br>
                               <br>
                               Regards,<br>
                               Owl-Plantation System.
                             </body>
                             </head>
                           </html>
                           ";
                     }else{
                    $subject="[Notifikasi] Sebagian atau Seluruhnya PP No :".$_POST['nopp']." dari ".$namakaryawan." ditolak oleh ".$nmpnlk;
                    $body="<html>
                             <head>
                             <body>
                               <dd>Dengan Hormat,</dd><br>
                               <br>
                               Permintaan pembelian no.".$_POST['nopp']." ditolak oleh [".$nmpnlk."] dengan alasan.
                               <br>
                               Item yang ditolak adalah : <ul>";
                    $sBrg="select kodebarang,alasanstatus from ".$dbname.".log_prapodt where nopp='".$_POST['nopp']."' and status='3'";
					$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
					$qBrg->setFetchMode(PDO::FETCH_ASSOC);
                    while($rBrg=$qBrg->fetch())
                    {
                       $body.="<li>".$nmBarang[$rBrg['kodebarang']].", alasan : ".$rBrg['alasanstatus']."</li>";
                    }
                    $body.="</ul><br>
                               <br>
                               Regards,<br>
                               Owl-Plantation System.
                             </body>
                             </head>
                           </html>
                           ";                         
                     }
                     
                   // $x=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
                   // echo $x;
                }  

        break;

        default:
        break;
        }
?>	