<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$proses=checkPostGet('proses','');
$kdPabrik=checkPostGet('kdOrg','');
$kdStatsiun=checkPostGet('statId','');
$noTrans=checkPostGet('noTrans','');
$pbrkId=checkPostGet('pbrkId','');
$shft=checkPostGet('shft','');
$statid=checkPostGet('statid','');
$mesinId=checkPostGet('mesinId','');
$tgl=tanggalsystem(checkPostGet('tgl',''));
$jmAwal=substr(tanggalsystemd(checkPostGet('jmAwal','')),0,10);
$jmAkhir=substr(tanggalsystemd(checkPostGet('jmAkhir','')),0,10);
$kdbrg=checkPostGet('kdbrg','');
$satuan=checkPostGet('satuan','');
$jmlhMinta=checkPostGet('jmlhMinta','');
$ketrngn=checkPostGet('ketrngn','');
$userOnline=$_SESSION['standard']['userid'];
$kegiatan=checkPostGet('kgtn','');
$pbrikId=checkPostGet('kdrg','');
//$sttId=checkPostGet('','');
$jamMulai=checkPostGet('jamMulai','');
$mntMulai=checkPostGet('mntMulai','');
$jamSlsi=checkPostGet('jamSlsi','');
$mntSlsi=checkPostGet('mntSlsi','');
$jmAwal=$jmAwal." ".$jamMulai.":".$mntMulai;
$jmAkhir=$jmAkhir." ".$jamSlsi.":".$mntSlsi;
//exit("Error".$jmAwal);
        switch($proses)
        {

                case'GetStat':
                //echo"warning:masuk";
                if($kdPabrik!='')
                {
                $sOrg="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$kdPabrik."'";
                $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                 $optStat.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                while($rOrg=$qOrg->fetch())
                {
                        if($statid!=''){
                                $optStat.="<option value=".$rOrg['kodeorganisasi']." ".($rOrg['kodeorganisasi']==$statid?'selected':'').">".$rOrg['namaorganisasi']."</option>";
                        }
                        else
                        {
                                $optStat.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
                        }
                }
                $sShft="select shift from ".$dbname.".pabrik_5shift where kodeorg='".$kdPabrik."' and shift!='0'";
                $qShft=$owlPDO->query($sShft) or die(print " Gagal: ".PDOException::getMessage());
                $qShft->setFetchMode(PDO::FETCH_ASSOC);
                while($rShft=$qShft->fetch())
                {
                        if($shft!='')
                        {
                                $optShift.="<option value=".$rShft['shift']." ".($rShft['shift']==$shft?'selected':'').">".$rShft['shift']."</option>";
                        }
                        else
                        {
                                $optShift.="<option value=".$rShft['shift'].">".$rShft['shift']."</option>";
                        }
                }

                echo $optStat."###".$optShift;
                }
                else 
                {
                        echo"warning : Organization code is obligatory";	
                }
                break;


                case'GetMsn':
                $sOrg="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$kdStatsiun."'"; //echo "warning".$sOrg;
                $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                while($rOrg=$qOrg->fetch())
                {
                    if($mesinId!='')
                    {
                        $optMsn.="<option value=".$rOrg['kodeorganisasi']." ".($rOrg['kodeorganisasi']==$mesinId?'selected':'').">".$rOrg['namaorganisasi']."</option>";
                    }
                    else
                    {
                        $optMsn.="<option value=".$rOrg['kodeorganisasi']." >".$rOrg['namaorganisasi']."</option>";
                    }
                }
                echo $optMsn;
                break;

                case'CreateNo':
                $jmAwal=explode(" ",$jmAwal);
                $jmAkhir=explode(" ",$jmAkhir);
                if($jmAkhir[0]<$jmAwal[0])
                {
                        echo"warning: Start time must lower then end time";
                        exit();
                }

                $tgl=  date('Ymd');
                $bln = substr($tgl,4,2);
                $thn = substr($tgl,0,4);

                $notransaksi="/".$kdStatsiun."/".date('m')."/".date('Y');
        $ql="select `notransaksi` from ".$dbname.".`pabrik_rawatmesinht` where notransaksi like '%".$notransaksi."%' order by `notransaksi` desc limit 0,1";
        $qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_OBJ);
        $rp=$qr->fetch();
		setIt($rp->notransaksi,'');
        $awal=substr($rp->notransaksi,0,4);
                //echo "warning:".$awal;exit();
        $awal=intval($awal);
        $cekbln=substr($rp->notransaksi,-7,2);
        $cekthn=substr($rp->notransaksi,-12,4);

        if(($bln!=$cekbln)&&($thn!=$cekthn))
        {
            $awal=1;
        }
        else
        {
                    $awal++;
        }
        $counter=addZero($awal,4);
                $notransaksi=$counter."/".$kdStatsiun."/".$bln."/".$thn;
        echo $notransaksi;
                break;

                case'cekData':
                /*echo"warning:masuk";
                exit();*/
                if(($shft=='')||($statid=='')||($mesinId=='')||($tgl=='')||($kdbrg==''))
                {
                        echo"warning: Please complete the form";
                        exit();
                }
                $sCek="select notransaksi from ".$dbname.".pabrik_rawatmesinht where notransaksi='".$noTrans."'"; //echo "warning:".$sCek;
                $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $qCek->setFetchMode(PDO::FETCH_OBJ);
                $rCek=owlBaris($qCek);
                if($rCek<1)
                {
                        $sIns="insert into ".$dbname.".pabrik_rawatmesinht (notransaksi, pabrik, tanggal, shift, statasiun, mesin, kegiatan, jammulai, jamselesai, updateby) 
                        values ('".$noTrans."','".$pbrkId."','".$tgl."','".$shft."','".$statid."','".$mesinId."','".$kegiatan."','".$jmAwal."','".$jmAkhir."','".$userOnline."')";
                        try{
                            $owlPDO->exec($sIns); 
                            $sInd="insert into ".$dbname.".pabrik_rawatmesindt (notransaksi, kodebarang, satuan, jumlah, keterangan) values ('".$noTrans."','".$kdbrg."','".$satuan."','".$jmlhMinta."','".$ketrngn."')";
                            try{
                                $owlPDO->exec($sInd); 
                            }catch (PDOException $e){
                                echo "DB Error : " . $e->getMessage();
                                die();
                            }
                        }catch (PDOException $e){
                            echo "DB Error : " . $e->getMessage();
                            die();
                        }
                }
                $test=count($_POST['kdbrg']);
                echo $test;
                break;
                case'saveHeader':
                if(($shft=='')||($statid=='')||($mesinId=='')||($tgl==''))
                {
                        echo"warning: Please complete the form";
                        exit();
                }
                $sCek="select notransaksi from ".$dbname.".pabrik_rawatmesinht where notransaksi='".$noTrans."'"; //echo "warning:".$sCek;
                $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $qCek->setFetchMode(PDO::FETCH_OBJ);
                $rCek=owlBaris($qCek);
                if($rCek<1)
                {
                        $sIns="insert into ".$dbname.".pabrik_rawatmesinht (notransaksi, pabrik, tanggal, shift, statasiun, mesin, kegiatan, jammulai, jamselesai, updateby) 
                        values ('".$noTrans."','".$pbrkId."','".$tgl."','".$shft."','".$statid."','".$mesinId."','".$kegiatan."','".$jmAwal."','".$jmAkhir."','".$userOnline."')";
                        //echo"warning:".$sIns;exit();
                        try{
                            $owlPDO->exec($sIns); 
                        }catch (PDOException $e){
                            echo "DB Error : " . $e->getMessage();
                            die();
                        }
                }
                break;
                case'cari_barang':
                        $txtcari=$_POST['txtcari'];
                $str="select a.kodebarang,a.namabarang,a.satuan from
                      ".$dbname.".log_5masterbarang a where a.namabarang like '%".$txtcari."%' or a.kodebarang like '%".$txtcari."'";
                         // echo $str;
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);                      
                if(owlBaris($res)<1)
                {
                        echo"Error: ".$_SESSION['lang']['tidakditemukan'];			
                }
                else
                {
                                echo"
                                <fieldset>
                                <legend>".$_SESSION['lang']['result']."</legend>
                                <div style=\"width:450px; height:300px; overflow:auto;\">
                                        <table class=sortable cellspacing=1 border=0>
                                         <thead>
                                                  <tr class=rowheader>
                                                          <td>No</td>
                                                          <td>".$_SESSION['lang']['kodebarang']."</td>
                                                          <td>".$_SESSION['lang']['namabarang']."</td>
                                                          <td>".$_SESSION['lang']['satuan']."</td>
                                                  </tr>
                                         </thead>
                                         <tbody>";
                                        $no=0;	 
                                        while($bar=$res->fetch())
                                        {
                                                $no+=1;
                                                echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"throwThisRow('".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."');\">
                                                   <td>".$no."</td>
                                                  <td>".$bar->kodebarang."</td>
                                                  <td>".$bar->namabarang."</td>
                                                  <td>".$bar->satuan."</td>
                                                  </tr>";			   	
                                        }
                                echo    "
                                                 </tbody>
                                                 <tfoot></tfoot>
                                                 </table></div></fieldset>";	
                }  
                break;
                case'loadData':
                $limit=25;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_rawatmesinht  order by tanggal desc";// echo $ql2;
                $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $query2->setFetchMode(PDO::FETCH_OBJ);  
                while($jsl=$query2->fetch()){
                $jlhbrs= $jsl->jmlhrow;
                }

                $slvhc="select * from ".$dbname.".pabrik_rawatmesinht  order by tanggal desc limit ".$offset.",".$limit." ";
                $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
                $qlvhc->setFetchMode(PDO::FETCH_ASSOC);  
                $user_online=$_SESSION['standard']['userid'];
                while($rlvhc=$qlvhc->fetch())
                {
                $no+=1;
                $dtJamMulai=explode(" ",$rlvhc['jammulai']);
                $jamMulai=explode(":",$dtJamMulai[1]);

                $dtJamSlsi=explode(" ",$rlvhc['jamselesai']);
                $jamSlsi=explode(":",$dtJamSlsi[1]);
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rlvhc['notransaksi']."</td>
                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                <td>".$rlvhc['shift']."</td>
                <td>".$rlvhc['statasiun']."</td>
                <td>".$rlvhc['mesin']."</td>
                <td>".tanggalnormald($rlvhc['jammulai'])."</td>
                <td>".tanggalnormald($rlvhc['jamselesai'])."</td>";
                if($rlvhc['statPost']==0)
                {
                        if($rlvhc['updateby']==$userOnline)
                        {
                        echo"<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['notransaksi']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['pabrik']."','".$rlvhc['shift']."','".$rlvhc['statasiun']."','".$rlvhc['mesin']."','".$rlvhc['kegiatan']."','".tanggalnormal($dtJamMulai[0])."','".tanggalnormal($dtJamSlsi[0])."','".$jamMulai[0]."','".$jamMulai[1]."','".$jamSlsi[0]."','".$jamSlsi[1]."');\"><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['notransaksi']."');\" ><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event)\"></td>";
                         } else {
                                 echo"
                        <td><img src=images/pdf.jpg class=resicon  title='Print' onclick=onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event);\"></td>";}//end if updateby
                }
                else
                { 
                echo"
                        <td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event)\"></td>";	
                }//end if posting
        }//end while
                echo"
                </tr><tr class=rowheader><td colspan=9 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                break;
                case'cariTransaksi':

                $limit=20;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
				$txt_search='';
                $txt_tgl='';
                if(!empty($_POST['txtSearch']))
                {
                    $txt_search=$_POST['txtSearch'];
				}
				if(!empty($_POST['txtTgl'])) {
                        $txt_tgl=tanggalsystem($_POST['txtTgl']);
                        $txt_tgl_a=substr($txt_tgl,0,4);
                        $txt_tgl_b=substr($txt_tgl,4,2);
                        $txt_tgl_c=substr($txt_tgl,6,2);
                        $txt_tgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
                }
                        if($txt_search!='')
                        {
                                $where=" notransaksi LIKE  '%".$txt_search."%'";
                        }
                        elseif($txt_tgl!='')
                        {
                                $where.=" tanggal LIKE '".$txt_tgl."'";
                        }
                        elseif(($txt_tgl!='')&&($txt_search!=''))
                        {
                                $where.=" notransaksi LIKE '%".$txt_search."%' and tanggal LIKE '%".$txt_tgl."%'";
                        }
                if(!empty($where)) $where = " where ".$where;
				$strx="select * from ".$dbname.".pabrik_rawatmesinht ".$where." order by notransaksi desc 
				limit ".$offset.",".$limit."";
				$sql="select count(*) jmlhrow from ".$dbname.".pabrik_rawatmesinht ".$where." order by notransaksi desc";
                $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $query->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query->fetch()){
                $jlhbrs= $jsl->jmlhrow;
                }
                    $res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                                $numrows=owlBaris($res);
                                if($numrows<1)
                                {
                                        echo"<tr class=rowcontent><td colspan=9>Not Found</td></tr>";
                                }
                                else
                                {
                                        while($rlvhc=$res->fetch())
                                        {

                                                $dtJamMulai=explode(" ",$rlvhc['jammulai']);
                                                $jamMulai=explode(":",$dtJamMulai[1]);

                                                $dtJamSlsi=explode(" ",$rlvhc['jamselesai']);
                                                $jamSlsi=explode(":",$dtJamSlsi[1]);
                                                $no+=1;
                                        echo"
                                                <tr class=rowcontent>
                                                <td>".$no."</td>
                                                <td>".$rlvhc['notransaksi']."</td>
                                                <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                                                <td>".$rlvhc['shift']."</td>
                                                <td>".$rlvhc['statasiun']."</td>
                                                <td>".$rlvhc['mesin']."</td>
                                                <td>".tanggalnormald($rlvhc['jammulai'])."</td>
                                                <td>".tanggalnormald($rlvhc['jamselesai'])."</td>";
                                                if($rlvhc['updateby']==$userOnline)
                                                {
                                                echo"<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['notransaksi']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['pabrik']."','".$rlvhc['shift']."','".$rlvhc['statasiun']."','".$rlvhc['mesin']."','".$rlvhc['kegiatan']."','".tanggalnormal($dtJamMulai[0])."','".tanggalnormal($dtJamSlsi[0])."','".$jamMulai[0]."','".$jamMulai[1]."','".$jamSlsi[0]."','".$jamSlsi[1]."');\">
                                                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['notransaksi']."');\" >
                                                    <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event)\"></td>";
                                                 } else {
                                                         echo"
                                                <td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$rlvhc['notransaksi']."','','pabrik_slavePemeliharaanPdf',event)\"></td>";}
                                                 }
                                                echo"
                                                </tr><tr class=rowheader><td colspan=9 align=center>
                                                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                                                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                                                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                                                </td>
                                                </tr>";
                                }
                        
                break;

                case'deletData':
                $sDel="delete from ".$dbname.".pabrik_rawatmesinht where notransaksi='".$noTrans."'";
                try{
                    $owlPDO->exec($sDel); 
                    $sdelDet="delete from ".$dbname.".pabrik_rawatmesindt where notransaksi='".$noTrans."'";
                    try{
                        $owlPDO->exec($sdelDet); 
                    }catch (PDOException $e){
                        echo "DB Error : " . $e->getMessage();
                        die();
                    }
                }catch (PDOException $e){
                    echo "DB Error : " . $e->getMessage();
                    die();
                }

                break;

                case'upDate':
                if(($jmAkhir=='')||($jmAwal=='')||($tgl==''))
                {
                        echo"warning: Please complete the form";
                        exit();
                }
                $sUp="update  ".$dbname.".pabrik_rawatmesinht set kegiatan='".$kegiatan."', jammulai='".$jmAwal."', jamselesai='".$jmAkhir."', tanggal='".$tgl."' where notransaksi='".$noTrans."'";
                try{
                    $owlPDO->exec($sUp); 
                }catch (PDOException $e){
                    echo "DB Error : " . $e->getMessage();
                    die();
                }
                break;
                default:
                break;
        }
?>