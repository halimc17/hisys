<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
$method=checkPostGet('method','');
$kdBrg=checkPostGet('kdBrg','');
$noTrans=checkPostGet('noTrans','');
$idCust=checkPostGet('idCust','');
$kdPbrk=checkPostGet('kdPbrk','');
$jmMasuk=explode(':',checkPostGet('jmMasuk','00:00'));
$jmKeluar=explode(':',checkPostGet('jmKeluar','00:00'));
$BuahStat=checkPostGet('BuahStat','');
$thntnm1=checkPostGet('thntnm1','');
$thntnm2=checkPostGet('thntnm2','');
$thntnm3=checkPostGet('thntnm3','');
$kdKbn=checkPostGet('kdKbn','');
$statTmbngn=checkPostGet('statTmbngn','');
$txtSearch=checkPostGet('txtSearch','');
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
if(!empty($_POST['tglCari']))
{
$tglCari=explode("-",$_POST['tglCari']);
$tglCari=$tglCari[2]."-".$tglCari[1]."-".$tglCari[0];
}

$sCust="select kodecustomer,namacustomer  from ".$dbname.".pmn_4customer order by namacustomer";
$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
$qCust->setFetchMode(PDO::FETCH_ASSOC);
$optCust="";
while($rCust=$qCust->fetch())
{
$optCust.="<option value=".$rCust['kodecustomer']." ".($rCust['kodecustomer']==$idCust?'selected':'').">".$rCust['namacustomer']."</option>";
}	
$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//exit("Error".$_SESSION['empl']['tipelokasitugas']);
if(($_SESSION['empl']['tipelokasitugas']=='KANWIL')||($_SESSION['empl']['tipelokasitugas']=='HOLDING'))
{
    $sPbrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
}
else
{
    $sPbrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi='".$lksiTugas."'";
}

$qPabrik=$owlPDO->query($sPbrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch()){
    $kdPbrk!=''?$optPabrik.="<option value=".$rPabrik['kodeorganisasi']." ".($rPabrik['kodeorganisasi']==$kdPbrk?'selected':'').">".$rPabrik['namaorganisasi']."</option>":$optPabrik.="<option value=".$rPabrik['kodeorganisasi']." >".$rPabrik['namaorganisasi']."</option>";
}
$thn=intval(date("Y"));
$optThn=$optThn2=$optThn3="";
for($i=1982;$i<=$thn;$i++)
{
        $optThn.="<option value=".$i." ".($i==$thntnm1?'selected':'').">".$i."</option>";
        $optThn2.="<option value=".$i." ".($i==$thntnm2?'selected':'').">".$i."</option>";
        $optThn3.="<option value=".$i." ".($i==$thntnm3?'selected':'').">".$i."</option>";
}
$jmMsk=$jmKlr=$mntMsk=$mntKlr="";
for($i=0;$i<24;)
{
        if(strlen($i)<2)
        {
                $i="0".$i;
        }
   $jmMsk.="<option value=".$i." ".($i==$jmMasuk[0]?'selected':'').">".$i."</option>";
   $jmKlr.="<option value=".$i." ".($i==$jmKeluar[0]?'selected':'').">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
        if(strlen($i)<2)
        {
                $i="0".$i;
        }
   $mntMsk.="<option value=".$i." ".($i==$jmMasuk[1]?'selected':'').">".$i."</option>";
   $mntKlr.="<option value=".$i." ".($i==$jmKeluar[1]?'selected':'').">".$i."</option>";
   $i++;
}
//1 = internal, 2 =afiliasi, 0 external
$arrOptIntex=array("External","Internal","Afiliasi");
$OptIntex="";
foreach($arrOptIntex as $isi =>$tks)
{
        if(isset($_POST['BuahStat']))
        {$OptIntex.="<option value=".$isi." ".($isi==$BuahStat?'selected':'').">".$tks."</option>";}
        else
        {$OptIntex.="<option value=".$isi." >".$tks."</option>";}
}
$arrStat=array("On","Off");
$optStatTimb="";
foreach($arrStat as $is =>$tks)
{
        if(isset($_POST['statTmbngn']))
        {$optStatTimb.="<option value='".$is."'  ".($is==$statTmbngn?'selected':'').">".$tks."</option>";}
        else
        {$optStatTimb.="<option value='".$is."'>".$tks."</option>";}
}

// Init Variable
$notransaksi=$ar=$tgl=$nkntrak=$kdNopol=$nodo=$nosipb=$spr=$nospb=$statSortasi=
$ptgsSortasi="";

        switch($method)
        {
                case'GetForm':
        if(($kdBrg=='40000001')||($kdBrg=='40000005')||($kdBrg=='40000002'))
                {
					    if($noTrans!='')
                        {
                                $sGdt="select * from ".$dbname.".pabrik_timbangan where notransaksi='".$noTrans."'";
                                $qGdt=$owlPDO->query($sGdt) or die(print " Gagal: ".PDOException::getMessage());
                                $qGdt->setFetchMode(PDO::FETCH_ASSOC);
                                $rGdt=$qGdt->fetch();
								if($rGdt['notransaksi']!='')
                                {
                                        $ar="disabled";
                                        $notransaksi="value=".$rGdt['notransaksi']."";
                                        $tgl="value=".tanggalnormal($rGdt['tanggal'])."";
                                        $nkntrak="value=".$rGdt['nokontrak']." ";
                                        $kdNopol="value='".$rGdt['nokendaraan']."' ";
                                        $nodo="value='".$rGdt['nodo']."'";
                                        $nosipb="value=".$rGdt['nosipb']."";
                                        $spr="value='".$rGdt['supir']."'";
                                        $brtKsng=" value='".$rGdt['beratmasuk']."' ";
                                        $brtBrsh=" value=".$rGdt['beratbersih']." ";
                                        $brtKlr=" value='".$rGdt['beratkeluar']."'";
                                        $statSortasi="value='".$rGdt['statussortasi']."'";
                                        $ptgsSortasi="value='".$rGdt['petugassortasi']."'";

                                }
                        }
                        else
                        {
                                $brtKsng=" value='0' ";
                                $brtBrsh=" value='0' ";
                                $brtKlr=" value='0'";
                        }
                        echo"<input type=hidden id=method name=method value='insertCpk' />    <fieldset>
<legend>".$_SESSION['lang']['entryForm']."</legend>
<table>
 <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['notransaksi']."</td><td>
                 <input type=text id=noTrans name=noTrans class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=40 style=\"width:150px;\" ".$notransaksi." ".$ar." /></td>
          </tr>
           <tr>
                 <td style='valign:top'>".$_SESSION['lang']['tanggal']."</td>
                 <td>
                 <input type=text class=myinputtext id=tglTrans name=tglTrans onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 ".$tgl." style=\"width:150px;\" /></td>
          </tr>
            <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['NoKontrak']."</td><td>
                 <input type=text id=nokontrk name=nokontrk class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=40 style=\"width:150px;\" ".$nkntrak." /></td>
          </tr>
          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['nmcust']."</td><td>
                 <select id=nmCust name=nmCust  style=\"width:150px;\"><option value=></option>".$optCust."</select></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['kdpabrik']."</td><td>
                 <select id=kdPbrik name=kdPbrik  style=\"width:150px;\">".$optPabrik."</select></td>
          </tr>
          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['statTimbangan']."</td><td>
                 <select id=statTmbngn name=statTmbngn  style=\"width:150px;\"><option value=></option>".$optStatTimb."</select></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['nodo']." </td><td>
                 <input type=text id=nodo name=nodo class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=30 style=\"width:150px;\" ".$nodo."   /></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['nosipb']."</td><td>
                 <input type=text id=nosipb name=nosipb class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=30 style=\"width:150px;\" ".$nosipb."   /></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['kodenopol']."</td><td>
                <input type=text id=nopol name=nopol class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=8 style=\"width:150px;\" ".$kdNopol." /></td>
          </tr>
       <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['sopir']."</td><td>
                 <input type=text id=spir name=spir class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=30 style=\"width:150px;\" ".$spr." /></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratkosong']." </td><td>
         <input type=text id=brtKosong name=brtKosong class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtKsng."  /></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratBersih']."</td><td>
                 <input type=text id=brtBersih name=brtBersih class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtBrsh."  /></td>
          </tr>
                <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratKeluar']."</td><td>
                 <input type=text id=brtKeluar name=brtKeluar class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtKlr."  /></td>
          </tr>
                  <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['jammasuk']."</td><td>
                <select id=jmMasuk>".$jmMsk."</select> : <select id=mntMasuk>".$mntMsk."</select></td>
          </tr>
          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['jamkeluar']."</td><td>
                <select id=jmKeluar>".$jmKlr."</select> : <select id=mntKeluar>".$mntKlr."</select></td>
          </tr>
                <button class=mybutton onclick=saveCpk()>".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=clearDt()>".$_SESSION['lang']['cancel']."</button>
     </table>
</fieldset>";
                }
                elseif($kdBrg=='40000004')
                {
                        if($noTrans!='')
                        {
                                $sGdt="select * from ".$dbname.".pabrik_timbangan where notransaksi='".$noTrans."'";
                                $qGdt=$owlPDO->query($sGdt) or die(print " Gagal: ".PDOException::getMessage());
                                $qGdt->setFetchMode(PDO::FETCH_ASSOC);
                                $rGdt=$qGdt->fetch();
                                if($rGdt['notransaksi']!='')
                                {
                                        $ar="disabled";
                                        $notransaksi="value=".$rGdt['notransaksi']."";
                                        $tgl="value=".tanggalnormal($rGdt['tanggal'])."";
                                        $kdNopol="value='".$rGdt['nokendaraan']."' ";
                                        $spr="value='".$rGdt['supir']."'";
                                        $brtKsng=" value='".$rGdt['beratmasuk']."' ";
                                        $brtBrsh=" value=".$rGdt['beratbersih']." ";
                                        $brtKlr=" value='".$rGdt['beratkeluar']."'";
                                        $statSortasi="value='".$rGdt['statussortasi']."'";
                                        $ptgsSortasi="value='".$rGdt['petugassortasi']."'";
                                }
                        }
                        else
                        {
                                $brtKsng=" value='0' ";
                                $brtBrsh=" value='0' ";
                                $brtKlr=" value='0'";
                        }
                        echo"<input type=hidden id=method name=method value='insertJk' /> <fieldset>
<legend>".$_SESSION['lang']['entryForm']."</legend>
<table>
         <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['notransaksi']."</td><td>
                 <input type=text id=noTrans name=noTrans class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=40 style=\"width:150px;\" ".$notransaksi." ".$ar."  /></td>
          </tr>
          <tr>
                 <td style='valign:top'>".$_SESSION['lang']['tanggal']."</td>
                 <td><input type=text class=myinputtext id=tglTrans name=tglTrans onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 ".$tgl." style=\"width:150px;\"/></td>
          </tr>

          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['nmcust']."</td><td>
                 <select id=nmCust name=nmCust  style=\"width:150px;\"><option value=></option>".$optCust."</select></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['kdpabrik']."</td><td>
                 <select id=kdPbrik name=kdPbrik  style=\"width:150px;\">".$optPabrik."</select></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['statTimbangan']."</td><td>
                 <select id=statTmbngn name=statTmbngn  style=\"width:150px;\"><option value=></option>".$optStatTimb."</select></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['kodenopol']."</td><td>
                <input type=text id=nopol name=nopol class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=8 style=\"width:150px;\" ".$kdNopol."/></td>
          </tr>
       <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['sopir']."</td><td>
                 <input type=text id=spir name=spir class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=30 style=\"width:150px;\" ".$spr." /></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratkosong']." </td><td>
         <input type=text id=brtKosong name=brtKosong class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtKsng." /></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratBersih']."</td><td>
                 <input type=text id=brtBersih name=brtBersih class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtBrsh."  /></td>
          </tr>
                <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratKeluar']."</td><td>
                 <input type=text id=brtKeluar name=brtKeluar class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtKlr."  /></td>
          </tr>
                  <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['jammasuk']."</td><td>
                <select id=jmMasuk>".$jmMsk."</select> : <select id=mntMasuk>".$mntMsk."</select></td>
          </tr>
          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['jamkeluar']."</td><td>
                <select id=jmKeluar>".$jmKlr."</select> : <select id=mntKeluar>".$mntKlr."</select></td>
          </tr>
          <tr>
                <td colspan='2'><button class=mybutton onclick=saveJk()>".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=clearDt()>".$_SESSION['lang']['cancel']."</button></td></tr>
     </table>
</fieldset>";
                }
                elseif($kdBrg=='40000003')
                {
                        if($noTrans!='')
                        {
                                $sGdt="select * from ".$dbname.".pabrik_timbangan where notransaksi='".$noTrans."'";
                                $qGdt=$owlPDO->query($sGdt) or die(print " Gagal: ".PDOException::getMessage());
                                $qGdt->setFetchMode(PDO::FETCH_ASSOC);
                                $rGdt=$qGdt->fetch();
                                if($rGdt['notransaksi']!='')
                                {
                                        $ar="disabled";
                                        $notransaksi="value='".$rGdt['notransaksi']."'";
                                        $tgl="value='".tanggalnormal($rGdt['tanggal'])."'";
                                        $nospb="value='".$rGdt['nospb']."'";
                                        $jmlhtndn1="value='".$rGdt['jumlahtandan1']."'";
                                        $jmlhtndn2="value='".$rGdt['jumlahtandan2']."'";
                                        $jmlhtndn3="value='".$rGdt['jumlahtandan3']."'";
                                        $brndlan="value='".$rGdt['brondolan']."'";
                                        $kdNopol="value='".$rGdt['nokendaraan']."' ";
                                        $spr="value='".$rGdt['supir']."'";
                                        $brtKsng=" value='".$rGdt['beratkeluar']."' ";
                                        $brtBrsh=" value='".$rGdt['beratbersih']."'";
                                        $brtKlr=" value='".$rGdt['beratmasuk']."'";
                                        $statSortasi="value='".$rGdt['statussortasi']."'";
                                        $ptgsSortasi="value='".$rGdt['petugassortasi']."'";
                                }

                        }
                        else
                        {
                                $brtKsng=" value='0' ";
                                $brtBrsh=" value='0' ";
                                $brtKlr=" value='0'";
                                $jmlhtndn1="value='0'";
                                $jmlhtndn2="value='0'";
                                $jmlhtndn3="value='0'";
                                $brndlan="value='0' ";
                        }


                        echo"<input type=hidden id=method name=method value='insertTbs' />
                         <fieldset>
                        <legend>".$_SESSION['lang']['entryForm']."</legend>
                        <table>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['notransaksi']."</td><td>
                        <input type=text id=noTrans name=noTrans class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=40 style=\"width:150px;\" ".$notransaksi." ".$ar." /></td>
                        </tr>
                        <tr>
                        <td style='valign:top'>".$_SESSION['lang']['tanggal']."</td>
                        <td><input type=text class=myinputtext id=tglTrans name=tglTrans onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 ".$tgl." style=\"width:150px;\" /></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['kdpabrik']."</td><td>
                        <select id=kdPbrik name=kdPbrik  style=\"width:150px;\">".$optPabrik."</select></td>
                        </tr>
                          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['statTimbangan']."</td><td>
                 <select id=statTmbngn name=statTmbngn  style=\"width:150px;\"><option value=></option>".$optStatTimb."</select></td>
          </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['nospb']." </td><td>
                        <input type=text id=noSpb name=noSpb class=myinputtext onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\" ".$nospb." /></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['statusBuah']." </td><td>
                        <select id=statBuah name=statBuah  style=\"width:150px;\" onchange=getKbn(0,0,0)><option value=></option>".$OptIntex."</select></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['kebun']." </td><td>
                        <select id=kdOrg name=kdOrg  style=\"width:150px;\"><option value=></option></select></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['namasupplier']." </td><td>
                        <select id=suppId name=suppId  style=\"width:150px;\"><option value=></option></select></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['thntanam']." 1</td><td>
                        <select id=thnTnm1 name=thnTnm1  style=\"width:150px;\"><option value=></option>".$optThn."</select></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['jmlhTandan']." 1</td><td>
                        <input type=text id=jmlhTndn1 name=jmlhTndn1 class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=4 style=\"width:150px;\" ".$jmlhtndn1."  /></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['thntanam']." 2</td><td>
                        <select id=thnTnm2 name=thnTnm2  style=\"width:150px;\"><option value=></option>".$optThn2."</select></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['jmlhTandan']." 2</td><td>
                        <input type=text id=jmlhTndn2 name=jmlhTndn2 class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=4 style=\"width:150px;\" ".$jmlhtndn2." /></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['thntanam']." 3</td><td>
                        <select id=thnTnm3 name=thnTnm3  style=\"width:150px;\"><option value=></option>".$optThn3."</select></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['jmlhTandan']." 3</td><td>
                        <input type=text id=jmlhTndn3 name=jmlhTndn3 class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=4 style=\"width:150px;\" ".$jmlhtndn3." /></td>
                        </tr>
                        <tr> 	 
                        <td style='valign:top'>".$_SESSION['lang']['brondolan']."</td><td>
                        <input type=text id=brndln name=brndln class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=4 style=\"width:150px;\" ".$brndlan." /></td>
                        </tr>
                         <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['kodenopol']."</td><td>
                <input type=text id=nopol name=nopol class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=8 style=\"width:150px;\" ".$kdNopol."  /></td>
          </tr>
       <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['sopir']."</td><td>
                 <input type=text id=spir name=spir class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=30 style=\"width:150px;\" ".$spr."/></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratkosong']." </td><td>
         <input type=text id=brtKosong name=brtKosong class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtKsng."  /></td>
          </tr>
           <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratBersih']."</td><td>
                 <input type=text id=brtBersih name=brtBersih class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtBrsh."  /></td>
          </tr>
                <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['beratKeluar']."</td><td>
                 <input type=text id=brtKeluar name=brtKeluar class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=5 style=\"width:150px;\" ".$brtKlr."  /></td>
          </tr>
          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['statusSortasi']."</td><td>
                 <input type=text id=statSortasi name=statSortasi class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=5 style=\"width:150px;\" ".$statSortasi."   /></td>
          </tr>
          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['petugasSortasi']."</td><td>
                 <input type=text id=tgsSortasi name=tgsSortasi class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=20 style=\"width:150px;\" ".$ptgsSortasi."  /></td>
          </tr>
                          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['jammasuk']."</td><td>
                <select id=jmMasuk>".$jmMsk."</select> : <select id=mntMasuk>".$mntMsk."</select></td>
          </tr>
          <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['jamkeluar']."</td><td>
                <select id=jmKeluar>".$jmKlr."</select> : <select id=mntKeluar>".$mntKlr."</select></td>
          </tr>
                         <tr>
                <td colspan='2'><button class=mybutton onclick=saveTbs()>".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=clearDt()>".$_SESSION['lang']['cancel']."</button></td></tr>
                        </table>
          </fieldset>
                        ";
                }
                break;
                case'loadData':
                $limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_timbangan  order by `nokontrak` desc";// echo $ql2;
                $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $query2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query2->fetch()){
                $jlhbrs= $jsl->jmlhrow;
                }


                $slvhc="select * from ".$dbname.".pabrik_timbangan  order by `nokontrak` desc limit ".$offset.",".$limit."";
                $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
                $qlvhc->setFetchMode(PDO::FETCH_ASSOC);

                $user_online=$_SESSION['standard']['userid'];
                while($res=$qlvhc->fetch())
                {
                        $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer = '".$res['kodecustomer']."'"; //echo $sCust;
                        $qCUst=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                        $qCUst->setFetchMode(PDO::FETCH_ASSOC);
                        $rCust=$qCUst->fetch();

                        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where `kodebarang`='".$res['kodebarang']."'";
                        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rBrg=$qBrg->fetch();
//notrans,kdbrg,nmcust,factryId,wktMsk,wktKeluar,statBuah,thnTnm1,thnTnm2,thnTnm3
                $no+=1;
                echo"
                        <tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$res['notransaksi']."</td>
                        <td>".tanggalnormal($res['tanggal'])."</td>
                        <td>".$res['kodebarang']."</td>
                        <td>".$rBrg['namabarang']."</td>
                        <td>".$res['jammasuk']."</td>
                        <td>".$res['jamkeluar']."</td>
                        <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res['notransaksi']."','".$res['kodebarang']."','".$res['kodecustomer']."','".$res['millcode']."','".$res['jammasuk']."','".$res['jamkeluar']."','".$res['intex']."','".$res['thntm1']."','".$res['thntm2']."','".$res['thntm3']."','".$res['kodeorg']."','".$res['timbangonoff']."');\">
<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$res['notransaksi']."');\" >	
<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','".$res['notransaksi']."','','pabrik_timbanganPdf',event)\"></td>
                        </tr>";
                }
                echo"
                <tr class=rowheader><td colspan=8 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                break;
                case'getkbn':
                if($BuahStat=='0')
                {
                        $optkdOrg2="<opttion value=''>".$_SESSION['lang']['all']."</option>";
                        $sOrg="SELECT namasupplier,supplierid FROM ".$dbname.".log_5supplier WHERE kodetimbangan is not null";//echo "warning:".$sOrg;
                        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                        while($rOrg=$qOrg->fetch()){
                                $optkdOrg2.="<option value=".$rOrg['supplierid']."".($rOrg['supplierid']==$idCust?'selected':'').">".$rOrg['namasupplier']."</option>";
                        }
                        echo $optkdOrg2."###".$BuahStat;exit();
                }
                elseif($BuahStat==1)
                {
                        $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk='".$_SESSION['org']['kodeorganisasi']."'";//echo "warning:".$sOrg;
                }
                elseif($BuahStat==2)
                {
                        $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk!='".$_SESSION['org']['kodeorganisasi']."'"; //echo "warning:".$sOrg;
                }
                $optkdOrg="<opttion value=''>".$_SESSION['lang']['all']."</option>";
                $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                while($rOrg=$qOrg->fetch()){
                        $optkdOrg.="<option value=".$rOrg['kodeorganisasi']."".($rOrg['kodeorganisasi']==$kdKbn?'selected':'').">".$rOrg['namaorganisasi']."</option>";
                }

                echo $optkdOrg."###".$BuahStat;
                break;
                case'cariNotransaksi':
                if($txtSearch!='')
                {
                        $where.="and notransaksi like '%".$txtSearch."%'";
                }
                if($kdBrg!='')
                {
                    $where.=" and kodebarang='".$kdBrg."'";
                }
                if(!empty($tglCari))
                {
                    $where.=" and tanggal like '%".$tglCari."%'";
                }
                $awk=" (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')";
                $sCek2="select * from ".$dbname.".pabrik_timbangan where millcode!='' ".$where." and millcode in  ".$awk.""; //exit("error:".$sCek);//echo"warning:
                $qCek=$owlPDO->query($sCek2) or die(print " Gagal: ".PDOException::getMessage());
                $qCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=owlBaris($qCek);
                //echo $sCek;
                if($rCek>0)
                {
                        $limit=10;
                        $page=0;
                        if(isset($_POST['page']))
                        {
                        $page=$_POST['page'];
                        if($page<0)
                        $page=0;
                        }
                        $offset=$page*$limit;

                        $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_timbangan where millcode!='' ".$where." and millcode in  ".$awk." ";// echo $ql2;
                        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                        $query2->setFetchMode(PDO::FETCH_OBJ);
                        while($jsl=$query2->fetch()){
                        $jlhbrs= $jsl->jmlhrow;
                        }


                        $slvhc="select * from ".$dbname.".pabrik_timbangan where millcode!='' ".$where." and millcode in  ".$awk." limit ".$offset.",".$limit."";
                        $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
                        $qlvhc->setFetchMode(PDO::FETCH_OBJ);
                        $user_online=$_SESSION['standard']['userid'];
                        while($res=$qlvhc->fetch())
                        {
                                $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer = '".$res['kodecustomer']."'"; //echo $sCust;
                                $qCUst=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                                $qCUst->setFetchMode(PDO::FETCH_ASSOC);
                                $rCust=$qCUst->fetch();

                                $sBrg="select namabarang from ".$dbname.".log_5masterbarang where `kodebarang`='".$res['kodebarang']."'";
                                $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                                $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                                $rBrg=$qBrg->fetch();

                        $no+=1;
                        echo"
                                <tr class=rowcontent>
                                <td>".$no."</td>
                                <td>".$res['notransaksi']."</td>
                                <td>".tanggalnormal($res['tanggal'])."</td>
                                <td>".$res['kodebarang']."</td>
                                <td>".$rBrg['namabarang']."</td>
                                <td>".$res['jammasuk']."</td>
                                <td>".$res['jamkeluar']."</td>
                                <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res['notransaksi']."','".$res['kodebarang']."','".$res['kodecustomer']."','".$res['millcode']."','".$res['jammasuk']."','".$res['jamkeluar']."','".$res['intex']."','".$res['thntm1']."','".$res['thntm2']."','".$res['thntm3']."','".$res['kodeorg']."','".$res['timbangonoff']."');\">
<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$res['notransaksi']."');\" >	
<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','".$res['notransaksi']."','','pabrik_timbanganPdf',event)\"></td>
                                </tr>";
                        }
                        echo"
                        <tr class=rowheader><td colspan=9 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariTrk(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariTrk(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>";
                }
                else
                {
                        echo"<tr class=rowheader><td colspan=8 align=center>Not Found</td></tr>";
                }

                break;
                default:
                break;
        }
?>