<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$blok = checkPostGet('blok', '');
$per2 = checkPostGet('per2', '');

$tahun=substr($per2,0,4);
$per1=$tahun.'-01';
$tgl1=$tahun.'-01-01';

##ambil tanggal akhir
$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per2."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
    $tgl2=$bar['tanggalsampai'];

#bentuk untuk bgt..
$expblnbgt=  explode('-', $per2);
$blnbgt=$expblnbgt[1];


$str="select * from ".$dbname.".setup_blok_tahunan where kodeorg = '".$blok."' and tahun='".str_replace('-', '', $per2)."' ";

//exit('Error :'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($res);
if($numrows==0){

$str="select * from ".$dbname.".setup_blok where kodeorg = '".$blok."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

}

$bar=$res->fetch();
    $luas=$bar['luasareaproduktif'];
    $pkk=$bar['jumlahpokok'];
    $tt=$bar['tahuntanam'];
    $jnsbbt=$bar['jenisbibit'];
	
	
$pnn='611';	
$tbm='126';
$tm='621';
$bbt='128';

$stream="";//<button id=tomboldetail class=mybutton onclick=excel2(event,'".$blok."','".$per2."')>" . $_SESSION['lang']['excel'] . " Level 1</button>


//$stream.="<fieldset style='float:left;'><legend>PRODUKSI</legend>";
if ($method=='excel2') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}

$stream.="
        <tr>
            <td bgcolor='black'><font color='#FF0000'><b>".$_SESSION['lang']['blok']."</b></font></td>
            <td align=left  bgcolor='black'><font color='#FF0000'><b>".getNamaOrg($blok)."</b></font></td>
        </tr>
		<tr class=rowcontent>
			<td >".$_SESSION['lang']['luas']."</td>
            <td align=right>".$luas."</td>
		</tr>
        <tr class=rowcontent>
            <td>".$_SESSION['lang']['thntnm']."</td>
            <td align=right>".$tt."</td>
        </tr>
		<tr class=rowcontent>
			<td>".$_SESSION['lang']['pokok']."</td>
            <td align=right>".number_format($pkk)."</td>
		</tr>
		<tr class=rowcontent>
			<td>".$_SESSION['lang']['sph']."</td>
            <td align=right>".number_format($pkk/$luas,2)."</td>
		</tr>
		<tr class=rowcontent>
			<td>".$_SESSION['lang']['jenisbibit']."</td>
            <td>".$jnsbbt."</td>
		</tr>
		</table>";

$stream.="<br>";	
		
$stream.="<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['produksi']."</b></legend>";
if ($method=='excel2') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}		
	$stream.="<thead>
        <tr class=rowheader>
            <th rowspan=2 colspan=2 align=center>ITEM</th>
            <th colspan=2 align=center>".$_SESSION['lang']['realisasi']."</th>
            <th colspan=3 align=center>".$_SESSION['lang']['budget']."</th>
            <th colspan=5 align=center>".$_SESSION['lang']['sensus']."</th>
        </tr>
        <tr>
            <th align=center>".$_SESSION['lang']['bi']."</th>
            <th align=center>".$_SESSION['lang']['sbi']."</th>
            <th align=center>".$_SESSION['lang']['bi']."</th>
            <th align=center>".$_SESSION['lang']['sbi']."</th>
            <th align=center>".$_SESSION['lang']['setahun']."</th>
            <th align=center>".$_SESSION['lang']['bi']."</th>
            <th align=center>".$_SESSION['lang']['bi']."</th>
			<th align=center>SM I</th>
			<th align=center>SM II</th>
			<th align=center>".$_SESSION['lang']['setahun']."</th>
        </tr>
    </thead>
 <tbody>";


$str="select sum(kgwb) as kgwb,sum(kgbjr) as kgkebun,sum(jjg) as jjg from ".$dbname.".kebun_spb_detail_vw "
        . " where blok='".$blok."' and tanggal like '".$per2."%' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $kgpksbi=$bar['kgwb'];
    //$kgkebunbi=$bar['kgkebun'];
    $jjgkirimbi=$bar['jjg'];
}

$str="select sum(kgwb) as kgwb,sum(kgbjr) as kgkebun,sum(jjg) as jjg from ".$dbname.".kebun_spb_detail_vw "
        . " where blok='".$blok."' and tanggal between '".$tgl1."' and '".$tgl2."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $kgpkssdbi=$bar['kgwb'];
    //$kgkebunsdbi=$bar['kgkebun'];
    $jjgkirimsdbi=$bar['jjg'];
}

#budget

$addstr="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";
$str="select kg".$blnbgt." as kgbi,kgsetahun,".$addstr." as kgsdbi from ".$dbname.".bgt_produksi_kbn_kg_vw "
        . " where tahunbudget='".$tahun."' and kodeblok='".$blok."' ";	

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $kgkebunbgtbi=$bar['kgbi'];
    $kgkebunbgtsetahun=$bar['kgsetahun'];
    $kgkebunbgtsdbi=$bar['kgsdbi'];
}


$str="select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir,"
        . " sum(tenagakerja) as tenagakerja,sum(luaspanen) as luaspanen,sum(kgkebun) as kgkebun"
        . " from ".$dbname.".kebun_rekappnn_vw where tanggal like '".$per2."%' and blok='".$blok."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kgkebunbi=$bar['kgkebun'];//indra
    $hkpnnbi=$bar['tenagakerja'];
    $jjgafkirbi=$bar['jjgafkir'];
    $jjgpnnbi=$bar['jjgpanen'];
    $luaspnnbi=$bar['luaspanen'];
}

$str="select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir,"
        . " sum(tenagakerja) as tenagakerja,sum(luaspanen) as luaspanen,sum(kgkebun) as kgkebun"
        . " from ".$dbname.".kebun_rekappnn_vw where tanggal between '".$tgl1."' and '".$tgl2."%' and blok='".$blok."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kgkebunsdbi=$bar['kgkebun'];
    $hkpnnsdbi=$bar['tenagakerja'];
    $jjgafkirsdbi=$bar['jjgafkir'];
    $jjgpnnsdbi=$bar['jjgpanen'];
    $luaspnnsdbi=$bar['luaspanen'];
}


$addstr="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="jjg0".$i;
    }
    else 
    {
        $isi="jjg".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";

$addstrthn="(";
for($i=1;$i<=12;$i++)
{
    if($i<10)
    {
        $isi="jjg0".$i;
    }
    else 
    {
        $isi="jjg".$i;
    }
    if($i<12)
    {
        $addstrthn.=$isi."+";
    }
    else
    {
        $addstrthn.=$isi;
    }
}
$addstrthn.=")";
$str="select jjg".$blnbgt." as jjg,".$addstr." as jjgsdbi,".$addstrthn." as jjgnsetahun "
        . " from ".$dbname.".bgt_produksi_kbn_vw "
        . " where tahunbudget='".$tahun."' and kodeblok='".$blok."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $jjgkirimbgtbi=$bar['jjg'];
    $jjgkirimbgtsdbi=$bar['jjgsdbi'];
    $jjgkirimbgtsetahun=$bar['jjgnsetahun'];
    
}



##panen hk budget
$kunci="(";
$str="SELECT distinct kodeorg, kodebudget, kegiatan, jumlah, satuanj,kunci FROM ".$dbname.".bgt_budget where kodebudget in('SDM-PHL','SDM-KHT','SDM-KBL')
		and kodeorg='".$blok."' and satuanj='HK'  and kegiatan='611010201' and  tahunbudget='".$tahun."'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=owlBaris($res);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$rowkunci+=1;
	@$hkpnnthn+=$bar['jumlah'];
	if($rowkunci==$row)
	{
		$kunci.="'".$bar['kunci']."'";
	}
	else
	{
		$kunci.="'".$bar['kunci']."',";
	}
}
$kunci.=")";

if($kunci=='()')
{
	$kunci="('')";
}

$addstr="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";

$addstrthn="(";
for($i=1;$i<=12;$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<12)
    {
        $addstrthn.=$isi."+";
    }
    else
    {
        $addstrthn.=$isi;
    }
}
$addstrthn.=")";
$str="select rp".$blnbgt." as rpbi,".$addstr." as rpsdbi,".$addstrthn." as rpthn from ".$dbname.".bgt_budget_detail "
        . " where tahunbudget='".$tahun."' and kodeorg='".$blok."' and kunci in ".$kunci." ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$rpbi+=$bar['rpbi'];
	@$rpsdbi+=$bar['rpsdbi'];
	@$rpthn+=$bar['rpthn'];
}


@$hkpnnbgtbi=$rpbi/$rpthn*$hkpnnthn;
@$hkpnnbgtsdbi=$rpsdbi/$rpthn*$hkpnnthn;

##ambil rotasi panen
$str="SELECT distinct kodeorg, kodebudget, kegiatan, jumlah, satuanj,kunci,rotasi FROM ".$dbname.".bgt_budget where kodebudget in('SDM-PHL','SDM-KHT','SDM-KBL')
		and kodeorg='".$blok."' and kegiatan='611010201' and tahunbudget='".$tahun."'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$rotasibgtthn=$bar['rotasi'];
	$rotasibgtbi=$rotasibgtthn/12; //disebut juga rotasi perbulan
	$rotasibgtsdbi=$rotasibgtbi*$blnbgt;




#sensus
##bi
$str=" select * from ".$dbname.".kebun_rencanapanen_vw where kodeblok='".$blok."' and bulan='".intval($blnbgt)."' and tahun='".$tahun."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$kgpkssensusbi+=$bar['kgsensus'];
	@$jjgsensusbi+=$bar['jumlah'];
}

#sdbi
$str=" select * from ".$dbname.".kebun_rencanapanen_vw where kodeblok='".$blok."' 
		and bulan between 1 and ".intval($blnbgt)." and tahun='".$tahun."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$kgpkssensussdbi+=$bar['kgsensus'];
	@$jjgsensussdbi+=$bar['jumlah'];
}

#sm1
$str=" select * from ".$dbname.".kebun_rencanapanen_vw where kodeblok='".$blok."' 
		and bulan between 1 and 6 and tahun='".$tahun."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$kgpkssensussm1+=$bar['kgsensus'];
	@$jjgsensussm1+=$bar['jumlah'];
}

#sm2
$str=" select * from ".$dbname.".kebun_rencanapanen_vw where kodeblok='".$blok."' 
		and bulan between 7 and 12 and tahun='".$tahun."' ";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$kgpkssensussm2+=$bar['kgsensus'];
	@$jjgsensussm2+=$bar['jumlah'];
}

#1thn
$str=" select * from ".$dbname.".kebun_rencanapanen_vw where kodeblok='".$blok."' 
		and  tahun='".$tahun."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$kgpkssensusthn+=$bar['kgsensus'];
	@$jjgsensusthn+=$bar['jumlah'];
}



$stream.="
        <tr class=rowcontent>
            <td align=center><b>1</b></td>
            <td colspan=11><b>".$_SESSION['lang']['produksi']."</b></td>
        </tr>
        ";
$stream.="
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>Kg PKS</td>
            <td align=right>".@number_format($kgpksbi,2)."</td>
            <td align=right>".@number_format($kgpkssdbi,2)."</td>
            <td align=right>".@number_format($kgkebunbgtbi,2)."</td>    
            <td align=right>".@number_format($kgkebunbgtsdbi,2)."</td>   
            <td align=right>".@number_format($kgkebunbgtsetahun,2)."</td>       
            <td align=right>".@number_format($kgpkssensusbi,2)."</td>   
            <td align=right>".@number_format($kgpkssensussdbi,2)."</td> 

			<td align=right>".@number_format($kgpkssensussm1,2)."</td>   
            <td align=right>".@number_format($kgpkssensussm2,2)."</td> 
			<td align=right>".@number_format($kgpkssensusthn,2)."</td> 
			
			
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>Kg Kebun</td>
            <td align=right>".@number_format($kgkebunbi,2)."</td>
            <td align=right>".@number_format($kgkebunsdbi,2)."</td>
            <td align=right>".@number_format($kgkebunbgtbi,2)."</td>    
            <td align=right>".@number_format($kgkebunbgtsdbi,2)."</td>   
            <td align=right>".@number_format($kgkebunbgtsetahun,2)."</td>      
			<td align=right>".@number_format($kgpkssensusbi,2)."</td>   
            <td align=right>".@number_format($kgpkssensussdbi,2)."</td> 

			<td align=right>".@number_format($kgpkssensussm1,2)."</td>   
            <td align=right>".@number_format($kgpkssensussm2,2)."</td>
			<td align=right>".@number_format($kgpkssensusthn,2)."</td>
			
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>BJR</td>
            <td align=right>".@number_format($kgpksbi/$jjgkirimbi,2)."</td>
            <td align=right>".@number_format($kgkebunsdbi/$jjgkirimsdbi,2)."</td>  
            
            <td align=right>".@number_format($kgkebunbgtbi/$jjgkirimbgtbi,2)."</td>
            <td align=right>".@number_format($kgkebunbgtsdbi/$jjgkirimbgtsdbi,2)."</td>  
            <td align=right>".@number_format($kgkebunbgtsetahun/$jjgkirimbgtsetahun,2)."</td>    
			<td align=right>".@number_format($kgpkssensusbi/$jjgsensusbi,2)."</td>   
            <td align=right>".@number_format($kgpkssensussdbi/$jjgsensussdbi,2)."</td> 	
			
			<td align=right>".@number_format($kgpkssensussm1/$jjgsensussm1,2)."</td>   
            <td align=right>".@number_format($kgpkssensussm2/$jjgsensussm2,2)."</td> 
			<td align=right>".@number_format($kgpkssensusthn/$jjgsensusthn,2)."</td>
			
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>Jjg Kirim</td>
            <td align=right>".@number_format($jjgkirimbi)."</td>
            <td align=right>".@number_format($jjgkirimsdbi)."</td>    
                
            <td align=right>".@number_format($jjgkirimbgtbi)."</td>    
            <td align=right>".@number_format($jjgkirimbgtsdbi)."</td>    
            <td align=right>".@number_format($jjgkirimbgtsetahun)."</td>   
			
			<td align=right>".@number_format($jjgsensusbi)."</td>    
            <td align=right>".@number_format($jjgsensussdbi)."</td>  
			<td align=right>".@number_format($jjgsensussm1)."</td>    
            <td align=right>".@number_format($jjgsensussm2)."</td>  
			<td align=right>".@number_format($jjgsensusthn)."</td>  
			
			
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>Afkir(".$_SESSION['lang']['jjg'].")</td>
            <td align=right>".@number_format($jjgafkirbi)."</td>
            <td align=right>".@number_format($jjgafkirbi)."</td> 
            <td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>     
			<td bgcolor=gray title='tidak ada data'></td>   
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>".$_SESSION['lang']['panen']." (".$_SESSION['lang']['jjg'].")</td>
            <td align=right>".@number_format($jjgpnnbi)."</td>
            <td align=right>".@number_format($jjgpnnsdbi)."</td>
            <td align=right>".@number_format($jjgkirimbgtbi)."</td>    
            <td align=right>".@number_format($jjgkirimbgtsdbi)."</td>    
            <td align=right>".@number_format($jjgkirimbgtsetahun)."</td>   
			<td align=right>".@number_format($jjgsensusbi)."</td>    
            <td align=right>".@number_format($jjgsensussdbi)."</td>  
			<td align=right>".@number_format($jjgsensussm1)."</td>    
            <td align=right>".@number_format($jjgsensussm2)."</td> 
			<td align=right>".@number_format($jjgsensusthn)."</td> 
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>".$_SESSION['lang']['jhk']." ".$_SESSION['lang']['panen']."</td>
            <td align=right>".@number_format($hkpnnbi,2)."</td>
            <td align=right>".@number_format($hkpnnsdbi,2)."</td>
			<td align=right>".@number_format($hkpnnbgtbi,2)."</td>
            <td align=right>".@number_format($hkpnnbgtsdbi,2)."</td>
			<td align=right>".@number_format($hkpnnthn,2)."</td>
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			


        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>".$_SESSION['lang']['luas']." ".$_SESSION['lang']['panen']."(Ha)</td>
            <td align=right>".@number_format($luaspnnbi,2)."</td>
            <td align=right>".@number_format($luaspnnsdbi,2)."</td>
			
			<td align=right>".@number_format($rotasibgtbi*$luas,2)."</td>
			<td align=right>".@number_format($rotasibgtsdbi*$luas,2)."</td>
			<td align=right>".@number_format($rotasibgtthn*$luas,2)."</td>
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			
			
			
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>Kg / ".$_SESSION['lang']['jhk']."</td>
            <td align=right>".@number_format($kgpksbi/$hkpnnbi,2)."</td>
            <td align=right>".@number_format($kgpkssdbi/$hkpnnsdbi,2)."</td>

			<td align=right>".@number_format($kgkebunbgtbi/$hkpnnbgtbi,2)."</td>    
            <td align=right>".@number_format($kgkebunbgtsdbi/$hkpnnbgtsdbi,2)."</td>   
            <td align=right>".@number_format($kgkebunbgtsetahun/$hkpnnthn,2)."</td>       
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			
			
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>Ton / Ha</td>
            <td align=right>".@number_format($kgpksbi/$luas/1000,2)."</td>
            <td align=right>".@number_format($kgpkssdbi/$luas/1000,2)."</td>
			
			<td align=right>".@number_format(($kgkebunbgtbi/$luas)/1000,2)."</td>    
            <td align=right>".@number_format(($kgkebunbgtsdbi/$luas)/1000,2)."</td>   
            <td align=right>".@number_format(($kgkebunbgtsetahun/$luas)/1000,2)."</td>       
			<td align=right>".@number_format($kgpkssensusbi/$luas/1000,2)."</td>   
            <td align=right>".@number_format($kgpkssensussdbi/$luas/1000,2)."</td> 
			
			<td align=right>".@number_format($kgpkssensussm1/$luas/1000,2)."</td>   
            <td align=right>".@number_format($kgpkssensussm2/$luas/1000,2)."</td> 
			<td align=right>".@number_format($kgpkssensusthn/$luas/1000,2)."</td>   
			
        </tr>
        <tr class=rowcontent>
            <td align=center>-</td>
            <td>".$_SESSION['lang']['rotasi']." ".$_SESSION['lang']['panen']." (kali)</td>
            <td align=right>".@number_format($luaspnnbi/$luas,2)."</td>
            <td align=right>".@number_format($luaspnnsdbi/$luas,2)."</td>
			<td align=right>".@number_format($rotasibgtbi,2)."</td>
			<td align=right>".@number_format($rotasibgtsdbi,2)."</td>
			<td align=right>".@number_format($rotasibgtthn,2)."</td>
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
			<td bgcolor=gray title='tidak ada data'></td>   
        </tr>
    ";
$stream.="</table></fieldset>";	







########################
########################	


$stream.="<fieldset><legend><b>".$_SESSION['lang']['biaya']."</b></legend>";
if ($method=='excel2') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
$stream.="<thead>
	<tr>
        <th align=center colspan='2' rowspan='3'>".$_SESSION['lang']['kegiatan']."</th>
        <th align=center colspan='10'>".$_SESSION['lang']['bi']."</th>
        <th align=center colspan='10'>".$_SESSION['lang']['sbi']."</th>
        <th align=center colspan='5' rowspan='2'>".$_SESSION['lang']['busget']." ".$_SESSION['lang']['setahun']."</th>
    </tr>
    <tr>
        <th align=center colspan='5'>".$_SESSION['lang']['realisasi']."</th>
        <th align=center colspan='5'>".$_SESSION['lang']['budget']."</th>
        <th align=center colspan='5'>".$_SESSION['lang']['realisasi']."</th>
        <th align=center colspan='5'>".$_SESSION['lang']['budget']."</th>
    </tr>
    <tr>";
	for($i=1;$i<=5;$i++)
	{
		$stream.="
			<th align=center>".$_SESSION['lang']['upah']."</th>
			<th align=center>".$_SESSION['lang']['material']."</th>
			<th align=center>".$_SESSION['lang']['transport']."</th>
			<th align=center>".$_SESSION['lang']['spk1']."</th>
			<th align=center>Total</th>
			";
	}
$stream.="        
    </tr>
    </thead>
        ";
		

$str="select substr(noakun,1,5) as akunlima,noakun,namaakun from ".$dbname.".keu_5akun 
		where  ((noakun like '611%' or noakun like '621%' or noakun like '126%' or (noakun between '1280101' and '1280199')) and length(noakun)>=5) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$namaakun[$bar['noakun']]=$bar['namaakun'];
	
}

//BI
$str="select substr(a.noakun,1,5) as akunlima,a.noakun,a.jumlah,a.tanggal,a.kodeblok,a.nojurnal,b.kodejurnal from ".$dbname.".keu_jurnaldt_vw a 
		left join ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal 
		where a.kodeblok='".$blok."' and a.tanggal like '".$per2."%' and 
		(a.noakun like '611%' or a.noakun like '621%' or a.noakun like '126%' or (noakun between '1280101' and '1280199')) ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima[$bar['akunlima']]=$bar['akunlima'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['akunlima']][$bar['noakun']]=$bar['noakun'];
	
	
	if ($bar['akunlima']=='61102')
	{
		if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$biupah[$bar['akunlima']][$bar['noakun']]['spk']+=$bar['jumlah'];
		}
		else
		{
			@$biupah[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
	}
	else
	{
		if(substr($bar['kodejurnal'],0,3)=='INV')
		{
			@$biupah[$bar['akunlima']][$bar['noakun']]['material']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='VHC')
		{
			@$biupah[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$biupah[$bar['akunlima']][$bar['noakun']]['spk']+=$bar['jumlah'];
		}
		else
		{
			@$biupah[$bar['akunlima']][$bar['noakun']]['upah']+=$bar['jumlah'];	
		}
	}
	@$biupah[$bar['akunlima']][$bar['noakun']]['total']+=$bar['jumlah'];	
}	




$str="select substr(a.noakun,1,5) as akunlima,a.noakun,a.jumlah,a.tanggal,a.kodeblok,a.nojurnal,b.kodejurnal from ".$dbname.".keu_jurnaldt_vw a 
		left join ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal 
		where a.kodeblok='".$blok."' and a.tanggal between '".$tgl1."' and '".$tgl2."' and 
		(a.noakun like '611%' or a.noakun like '621%' or a.noakun like '126%' or (noakun between '1280101' and '1280199')) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima[$bar['akunlima']]=$bar['akunlima'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['akunlima']][$bar['noakun']]=$bar['noakun'];
	
	
	if($bar['akunlima']=='61102')
	{
		if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$sdbiupah[$bar['akunlima']][$bar['noakun']]['spk']+=$bar['jumlah'];
		}
		else
		{
			@$sdbiupah[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
	}
	else
	{
		if(substr($bar['kodejurnal'],0,3)=='INV')
		{
			@$sdbiupah[$bar['akunlima']][$bar['noakun']]['material']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='VHC')
		{
			@$sdbiupah[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$sdbiupah[$bar['akunlima']][$bar['noakun']]['spk']+=$bar['jumlah'];
		}
		else
		{
			@$sdbiupah[$bar['akunlima']][$bar['noakun']]['upah']+=$bar['jumlah'];	
		}
	}
	@$sdbiupah[$bar['akunlima']][$bar['noakun']]['total']+=$bar['jumlah'];	
}	







#1 thn
#sdthn
$addstrsdbi="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstrsdbi.=$isi."+";
    }
    else
    {
        $addstrsdbi.=$isi;
    }
}
$addstrsdbi.=")";


##bgt bulan ini
$str=" select ".$addstrsdbi." as sdbi,rp".$blnbgt." as bi,noakun,substr(noakun,1,5) as akunlima,rupiah,kodebudget from ".$dbname.".bgt_budget_detail 
		where tahunbudget='".$tahun."' and kodeorg='".$blok."' 
		and (noakun like '611%' or noakun like '621%' or noakun like '126%' or (noakun between '1280101' and '1280199')) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	
	$akunlima[$bar['akunlima']]=$bar['akunlima'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['akunlima']][$bar['noakun']]=$bar['noakun'];
	
	if($bar['kodebudget']=='SUPERVISI' || substr($bar['kodebudget'],0,3)=='SDM')
	{
		@$bibgt[$bar['akunlima']][$bar['noakun']]['upah']+=$bar['bi'];
		@$sdbibgt[$bar['akunlima']][$bar['noakun']]['upah']+=$bar['sdbi'];
		@$bgttahun[$bar['akunlima']][$bar['noakun']]['upah']+=$bar['rupiah'];
	}
	else if (substr($bar['kodebudget'],0,2)=='M-' || $bar['kodebudget']=='TOOL')
	{
		@$bibgt[$bar['akunlima']][$bar['noakun']]['material']+=$bar['bi'];	
		@$sdbibgt[$bar['akunlima']][$bar['noakun']]['material']+=$bar['sdbi'];	
		@$bgttahun[$bar['akunlima']][$bar['noakun']]['material']+=$bar['rupiah'];
	}
	else if ($bar['kodebudget']=='VHC')
	{
		@$bibgt[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['bi'];	
		@$sdbibgt[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['sdbi'];	
		@$bgttahun[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['rupiah'];
	}
	else if ($bar['kodebudget']=='KONTRAK')
	{
		@$bibgt[$bar['akunlima']][$bar['noakun']]['spk']+=$bar['bi'];
		@$sdbibgt[$bar['akunlima']][$bar['noakun']]['spk']+=$bar['sdbi'];	
		@$bgttahun[$bar['akunlima']][$bar['noakun']]['spk']+=$bar['rupiah'];		
	}
	@$bibgt[$bar['akunlima']][$bar['noakun']]['total']+=$bar['bi'];	
	@$sdbibgt[$bar['akunlima']][$bar['noakun']]['total']+=$bar['sdbi'];	
	@$bgttahun[$bar['akunlima']][$bar['noakun']]['total']+=$bar['rupiah'];	
}




array_multisort($akunlima,SORT_ASC);
array_multisort($akun,SORT_ASC);

$stream.="<thead>
		<tr class=rowcontent class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$pnn."')>
            <td align=center><b>2</b></td>
            <td colspan=26><b>".$_SESSION['lang']['cost']." ".$_SESSION['lang']['panen']."</b></td>
		</tr></thead>
	";	// <button onclick=html3('".$blok."','".$per2."','".$pnn."')>DETAIL BIAYA PANEN</button><
foreach($akunlima as $noakunlima)
{
	if(@substr($noakunlima,0,3)=='611')
	{
		foreach($akun as $noakun)
		{
			if(@$listakun[$noakunlima][$noakun]!='')
			{
				$stream.="
				<tr class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$pnn."')>
					<td>".$noakun."</td>
					<td>".$namaakun[$noakun]."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['total'])."</td>  
					
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['total'])."</td>

					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['trans'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['spk'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['total'])."</td>
				
					
				</tr>
				";
				@$stbiupah[$noakunlima]['upah']+=$biupah[$noakunlima][$noakun]['upah'];
				@$stbiupah[$noakunlima]['material']+=$biupah[$noakunlima][$noakun]['material'];
				@$stbiupah[$noakunlima]['trans']+=$biupah[$noakunlima][$noakun]['trans'];
				@$stbiupah[$noakunlima]['spk']+=$biupah[$noakunlima][$noakun]['spk'];
				@$stbiupah[$noakunlima]['total']+=$biupah[$noakunlima][$noakun]['total'];
				
				@$stbibgt[$noakunlima]['upah']+=$bibgt[$noakunlima][$noakun]['upah'];
				@$stbibgt[$noakunlima]['material']+=$bibgt[$noakunlima][$noakun]['material'];
				@$stbibgt[$noakunlima]['trans']+=$bibgt[$noakunlima][$noakun]['trans'];
				@$stbibgt[$noakunlima]['spk']+=$bibgt[$noakunlima][$noakun]['spk'];
				@$stbibgt[$noakunlima]['total']+=$bibgt[$noakunlima][$noakun]['total'];
				
				@$stsdbiupah[$noakunlima]['upah']+=$sdbiupah[$noakunlima][$noakun]['upah'];
				@$stsdbiupah[$noakunlima]['material']+=$sdbiupah[$noakunlima][$noakun]['material'];
				@$stsdbiupah[$noakunlima]['trans']+=$sdbiupah[$noakunlima][$noakun]['trans'];
				@$stsdbiupah[$noakunlima]['spk']+=$sdbiupah[$noakunlima][$noakun]['spk'];
				@$stsdbiupah[$noakunlima]['total']+=$sdbiupah[$noakunlima][$noakun]['total'];
				
				
				
				
				@$stsdbibgt[$noakunlima]['upah']+=$sdbibgt[$noakunlima][$noakun]['upah'];
				@$stsdbibgt[$noakunlima]['material']+=$sdbibgt[$noakunlima][$noakun]['material'];
				@$stsdbibgt[$noakunlima]['trans']+=$sdbibgt[$noakunlima][$noakun]['trans'];
				@$stsdbibgt[$noakunlima]['spk']+=$sdbibgt[$noakunlima][$noakun]['spk'];
				@$stsdbibgt[$noakunlima]['total']+=$sdbibgt[$noakunlima][$noakun]['total'];
				
				@$stbgttahun[$noakunlima]['upah']+=$bgttahun[$noakunlima][$noakun]['upah'];
				@$stbgttahun[$noakunlima]['material']+=$bgttahun[$noakunlima][$noakun]['material'];
				@$stbgttahun[$noakunlima]['trans']+=$bgttahun[$noakunlima][$noakun]['trans'];
				@$stbgttahun[$noakunlima]['spk']+=$bgttahun[$noakunlima][$noakun]['spk'];
				@$stbgttahun[$noakunlima]['total']+=$bgttahun[$noakunlima][$noakun]['total'];
				
				
			}
		}
		$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=2>".$_SESSION['lang']['total']."  ".$namaakun[$noakunlima]."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['total'])."</td>
					
					
					<td align=right>".@number_format($stbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stbgttahun[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['total'])."</td>
                    
				</tr>";
				
				@$gtbiupah['upah']+=$stbiupah[$noakunlima]['upah'];
				@$gtbiupah['material']+=$stbiupah[$noakunlima]['material'];
				@$gtbiupah['trans']+=$stbiupah[$noakunlima]['trans'];
				@$gtbiupah['spk']+=$stbiupah[$noakunlima]['spk'];
				@$gtbiupah['total']+=$stbiupah[$noakunlima]['total'];
				
				@$gtbibgt['upah']+=$stbibgt[$noakunlima]['upah'];
				@$gtbibgt['material']+=$stbibgt[$noakunlima]['material'];
				@$gtbibgt['trans']+=$stbibgt[$noakunlima]['trans'];
				@$gtbibgt['spk']+=$stbibgt[$noakunlima]['spk'];
				@$gtbibgt['total']+=$stbibgt[$noakunlima]['total'];
				
				@$gtsdbiupah['upah']+=$stsdbiupah[$noakunlima]['upah'];
				@$gtsdbiupah['material']+=$stsdbiupah[$noakunlima]['material'];
				@$gtsdbiupah['trans']+=$stsdbiupah[$noakunlima]['trans'];
				@$gtsdbiupah['spk']+=$stsdbiupah[$noakunlima]['spk'];
				@$gtsdbiupah['total']+=$stsdbiupah[$noakunlima]['total'];
				
				@$gtsdbibgt['upah']+=$stsdbibgt[$noakunlima]['upah'];
				@$gtsdbibgt['material']+=$stsdbibgt[$noakunlima]['material'];
				@$gtsdbibgt['trans']+=$stsdbibgt[$noakunlima]['trans'];
				@$gtsdbibgt['spk']+=$stsdbibgt[$noakunlima]['spk'];
				@$gtsdbibgt['total']+=$stsdbibgt[$noakunlima]['total'];
				
				@$gtbgttahun['upah']+=$stbgttahun[$noakunlima]['upah'];
				@$gtbgttahun['material']+=$stbgttahun[$noakunlima]['material'];
				@$gtbgttahun['trans']+=$stbgttahun[$noakunlima]['trans'];
				@$gtbgttahun['spk']+=$stbgttahun[$noakunlima]['spk'];
				@$gtbgttahun['total']+=$stbgttahun[$noakunlima]['total'];
					
	}	
}	
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan=2>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right>".@number_format($gtbiupah['upah'])."</td>
			<td align=right>".@number_format($gtbiupah['material'])."</td>
			<td align=right>".@number_format($gtbiupah['trans'])."</td>
			<td align=right>".@number_format($gtbiupah['spk'])."</td>
			<td align=right>".@number_format($gtbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtbibgt['upah'])."</td>
			<td align=right>".@number_format($gtbibgt['material'])."</td>
			<td align=right>".@number_format($gtbibgt['trans'])."</td>
			<td align=right>".@number_format($gtbibgt['spk'])."</td>
			<td align=right>".@number_format($gtbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtsdbiupah['upah'])."</td>
			<td align=right>".@number_format($gtsdbiupah['material'])."</td>
			<td align=right>".@number_format($gtsdbiupah['trans'])."</td>
			<td align=right>".@number_format($gtsdbiupah['spk'])."</td>
			<td align=right>".@number_format($gtsdbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtsdbibgt['upah'])."</td>
			<td align=right>".@number_format($gtsdbibgt['material'])."</td>
			<td align=right>".@number_format($gtsdbibgt['trans'])."</td>
			<td align=right>".@number_format($gtsdbibgt['spk'])."</td>
			<td align=right>".@number_format($gtsdbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtbgttahun['upah'])."</td>
			<td align=right>".@number_format($gtbgttahun['material'])."</td>
			<td align=right>".@number_format($gtbgttahun['trans'])."</td>
			<td align=right>".@number_format($gtbgttahun['spk'])."</td>
			<td align=right>".@number_format($gtbgttahun['total'])."</td>
		</tr>";
				
######################################################
#############  TMMMMMMMMMMMMMMM ######################
######################################################

$gtbiupah['upah']=$gtbiupah['material']=$gtbiupah['trans']=$gtbiupah['spk']=$gtbiupah['total']=0;
$gtbibgt['upah']=$gtbibgt['material']=$gtbibgt['trans']=$gtbibgt['spk']=$gtbibgt['total']=0;
$gtsdbiupah['upah']=$gtsdbiupah['material']=$gtsdbiupah['trans']=$gtsdbiupah['spk']=$gtsdbiupah['total']=0;
$gtsdbibgt['upah']=$gtsdbibgt['material']=$gtsdbibgt['trans']=$gtsdbibgt['spk']=$gtsdbibgt['total']=0;
$gtbgttahun['upah']=$gtbgttahun['material']=$gtbgttahun['trans']=$gtbgttahun['spk']=$gtbgttahun['total']=0;

$stream.="<thead>
		<tr class=rowcontent  class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$tm."')>
            <td align=center><b>3</b></td>
            <td colspan=26><b>".$_SESSION['lang']['cost']." ".$_SESSION['lang']['tm']."</b></td>
		</tr></thead>
	";	
foreach($akunlima as $noakunlima)
{
	if(substr($noakunlima,0,3)=='621')
	{
		foreach($akun as $noakun)
		{
			if(@$listakun[$noakunlima][$noakun]!='')
			{
				$stream.="
				<tr class=rowcontent class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$tm."')>
					<td>".$noakun."</td>
					<td>".$namaakun[$noakun]."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['total'])."</td>  
					
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['total'])."</td>

					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['trans'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['spk'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['total'])."</td>
				
					
				</tr>
				";
				@$stbiupah[$noakunlima]['upah']+=$biupah[$noakunlima][$noakun]['upah'];
				@$stbiupah[$noakunlima]['material']+=$biupah[$noakunlima][$noakun]['material'];
				@$stbiupah[$noakunlima]['trans']+=$biupah[$noakunlima][$noakun]['trans'];
				@$stbiupah[$noakunlima]['spk']+=$biupah[$noakunlima][$noakun]['spk'];
				@$stbiupah[$noakunlima]['total']+=$biupah[$noakunlima][$noakun]['total'];
				
				@$stbibgt[$noakunlima]['upah']+=$bibgt[$noakunlima][$noakun]['upah'];
				@$stbibgt[$noakunlima]['material']+=$bibgt[$noakunlima][$noakun]['material'];
				@$stbibgt[$noakunlima]['trans']+=$bibgt[$noakunlima][$noakun]['trans'];
				@$stbibgt[$noakunlima]['spk']+=$bibgt[$noakunlima][$noakun]['spk'];
				@$stbibgt[$noakunlima]['total']+=$bibgt[$noakunlima][$noakun]['total'];
				
				@$stsdbiupah[$noakunlima]['upah']+=$sdbiupah[$noakunlima][$noakun]['upah'];
				@$stsdbiupah[$noakunlima]['material']+=$sdbiupah[$noakunlima][$noakun]['material'];
				@$stsdbiupah[$noakunlima]['trans']+=$sdbiupah[$noakunlima][$noakun]['trans'];
				@$stsdbiupah[$noakunlima]['spk']+=$sdbiupah[$noakunlima][$noakun]['spk'];
				@$stsdbiupah[$noakunlima]['total']+=$sdbiupah[$noakunlima][$noakun]['total'];
				
				
				
				
				@$stsdbibgt[$noakunlima]['upah']+=$sdbibgt[$noakunlima][$noakun]['upah'];
				@$stsdbibgt[$noakunlima]['material']+=$sdbibgt[$noakunlima][$noakun]['material'];
				@$stsdbibgt[$noakunlima]['trans']+=$sdbibgt[$noakunlima][$noakun]['trans'];
				@$stsdbibgt[$noakunlima]['spk']+=$sdbibgt[$noakunlima][$noakun]['spk'];
				@$stsdbibgt[$noakunlima]['total']+=$sdbibgt[$noakunlima][$noakun]['total'];
				
				@$stbgttahun[$noakunlima]['upah']+=$bgttahun[$noakunlima][$noakun]['upah'];
				@$stbgttahun[$noakunlima]['material']+=$bgttahun[$noakunlima][$noakun]['material'];
				@$stbgttahun[$noakunlima]['trans']+=$bgttahun[$noakunlima][$noakun]['trans'];
				@$stbgttahun[$noakunlima]['spk']+=$bgttahun[$noakunlima][$noakun]['spk'];
				@$stbgttahun[$noakunlima]['total']+=$bgttahun[$noakunlima][$noakun]['total'];
				
				
			}
		}
		$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=2>".$_SESSION['lang']['total']." ".$namaakun[$noakunlima]."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['total'])."</td>
					
					
					<td align=right>".@number_format($stbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stbgttahun[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['total'])."</td>
                    
				</tr>";
				
				@$gtbiupah['upah']+=$stbiupah[$noakunlima]['upah'];
				@$gtbiupah['material']+=$stbiupah[$noakunlima]['material'];
				@$gtbiupah['trans']+=$stbiupah[$noakunlima]['trans'];
				@$gtbiupah['spk']+=$stbiupah[$noakunlima]['spk'];
				@$gtbiupah['total']+=$stbiupah[$noakunlima]['total'];
				
				@$gtbibgt['upah']+=$stbibgt[$noakunlima]['upah'];
				@$gtbibgt['material']+=$stbibgt[$noakunlima]['material'];
				@$gtbibgt['trans']+=$stbibgt[$noakunlima]['trans'];
				@$gtbibgt['spk']+=$stbibgt[$noakunlima]['spk'];
				@$gtbibgt['total']+=$stbibgt[$noakunlima]['total'];
				
				@$gtsdbiupah['upah']+=$stsdbiupah[$noakunlima]['upah'];
				@$gtsdbiupah['material']+=$stsdbiupah[$noakunlima]['material'];
				@$gtsdbiupah['trans']+=$stsdbiupah[$noakunlima]['trans'];
				@$gtsdbiupah['spk']+=$stsdbiupah[$noakunlima]['spk'];
				@$gtsdbiupah['total']+=$stsdbiupah[$noakunlima]['total'];
				
				@$gtsdbibgt['upah']+=$stsdbibgt[$noakunlima]['upah'];
				@$gtsdbibgt['material']+=$stsdbibgt[$noakunlima]['material'];
				@$gtsdbibgt['trans']+=$stsdbibgt[$noakunlima]['trans'];
				@$gtsdbibgt['spk']+=$stsdbibgt[$noakunlima]['spk'];
				@$gtsdbibgt['total']+=$stsdbibgt[$noakunlima]['total'];
				
				@$gtbgttahun['upah']+=$stbgttahun[$noakunlima]['upah'];
				@$gtbgttahun['material']+=$stbgttahun[$noakunlima]['material'];
				@$gtbgttahun['trans']+=$stbgttahun[$noakunlima]['trans'];
				@$gtbgttahun['spk']+=$stbgttahun[$noakunlima]['spk'];
				@$gtbgttahun['total']+=$stbgttahun[$noakunlima]['total'];
					
	}	
}	
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan=2>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right>".@number_format($gtbiupah['upah'])."</td>
			<td align=right>".@number_format($gtbiupah['material'])."</td>
			<td align=right>".@number_format($gtbiupah['trans'])."</td>
			<td align=right>".@number_format($gtbiupah['spk'])."</td>
			<td align=right>".@number_format($gtbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtbibgt['upah'])."</td>
			<td align=right>".@number_format($gtbibgt['material'])."</td>
			<td align=right>".@number_format($gtbibgt['trans'])."</td>
			<td align=right>".@number_format($gtbibgt['spk'])."</td>
			<td align=right>".@number_format($gtbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtsdbiupah['upah'])."</td>
			<td align=right>".@number_format($gtsdbiupah['material'])."</td>
			<td align=right>".@number_format($gtsdbiupah['trans'])."</td>
			<td align=right>".@number_format($gtsdbiupah['spk'])."</td>
			<td align=right>".@number_format($gtsdbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtsdbibgt['upah'])."</td>
			<td align=right>".@number_format($gtsdbibgt['material'])."</td>
			<td align=right>".@number_format($gtsdbibgt['trans'])."</td>
			<td align=right>".@number_format($gtsdbibgt['spk'])."</td>
			<td align=right>".@number_format($gtsdbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtbgttahun['upah'])."</td>
			<td align=right>".@number_format($gtbgttahun['material'])."</td>
			<td align=right>".@number_format($gtbgttahun['trans'])."</td>
			<td align=right>".@number_format($gtbgttahun['spk'])."</td>
			<td align=right>".@number_format($gtbgttahun['total'])."</td>
		</tr>";


				
###########################################
#############  T B M  #####################
###########################################

$gtbiupah['upah']=$gtbiupah['material']=$gtbiupah['trans']=$gtbiupah['spk']=$gtbiupah['total']=0;
$gtbibgt['upah']=$gtbibgt['material']=$gtbibgt['trans']=$gtbibgt['spk']=$gtbibgt['total']=0;
$gtsdbiupah['upah']=$gtsdbiupah['material']=$gtsdbiupah['trans']=$gtsdbiupah['spk']=$gtsdbiupah['total']=0;
$gtsdbibgt['upah']=$gtsdbibgt['material']=$gtsdbibgt['trans']=$gtsdbibgt['spk']=$gtsdbibgt['total']=0;
$gtbgttahun['upah']=$gtbgttahun['material']=$gtbgttahun['trans']=$gtbgttahun['spk']=$gtbgttahun['total']=0;

$stream.="<thead>
		<tr class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$tbm."')>
            <td align=center><b>4</b></td>
            <td colspan=26><b>".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['tbm']."</b></td>
		</tr></thead>
	";	
foreach($akunlima as $noakunlima)
{
	if(substr($noakunlima,0,3)=='126')
	{
		foreach($akun as $noakun)
		{
			if(@$listakun[$noakunlima][$noakun]!='')
			{
				$stream.="
				<tr class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$tbm."')>
					<td>".$noakun."</td>
					<td>".$namaakun[$noakun]."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['total'])."</td>  
					
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['total'])."</td>

					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['trans'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['spk'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['total'])."</td>
				
					
				</tr>
				";
				@$stbiupah[$noakunlima]['upah']+=$biupah[$noakunlima][$noakun]['upah'];
				@$stbiupah[$noakunlima]['material']+=$biupah[$noakunlima][$noakun]['material'];
				@$stbiupah[$noakunlima]['trans']+=$biupah[$noakunlima][$noakun]['trans'];
				@$stbiupah[$noakunlima]['spk']+=$biupah[$noakunlima][$noakun]['spk'];
				@$stbiupah[$noakunlima]['total']+=$biupah[$noakunlima][$noakun]['total'];
				
				@$stbibgt[$noakunlima]['upah']+=$bibgt[$noakunlima][$noakun]['upah'];
				@$stbibgt[$noakunlima]['material']+=$bibgt[$noakunlima][$noakun]['material'];
				@$stbibgt[$noakunlima]['trans']+=$bibgt[$noakunlima][$noakun]['trans'];
				@$stbibgt[$noakunlima]['spk']+=$bibgt[$noakunlima][$noakun]['spk'];
				@$stbibgt[$noakunlima]['total']+=$bibgt[$noakunlima][$noakun]['total'];
				
				@$stsdbiupah[$noakunlima]['upah']+=$sdbiupah[$noakunlima][$noakun]['upah'];
				@$stsdbiupah[$noakunlima]['material']+=$sdbiupah[$noakunlima][$noakun]['material'];
				@$stsdbiupah[$noakunlima]['trans']+=$sdbiupah[$noakunlima][$noakun]['trans'];
				@$stsdbiupah[$noakunlima]['spk']+=$sdbiupah[$noakunlima][$noakun]['spk'];
				@$stsdbiupah[$noakunlima]['total']+=$sdbiupah[$noakunlima][$noakun]['total'];
				
				@$stsdbibgt[$noakunlima]['upah']+=$sdbibgt[$noakunlima][$noakun]['upah'];
				@$stsdbibgt[$noakunlima]['material']+=$sdbibgt[$noakunlima][$noakun]['material'];
				@$stsdbibgt[$noakunlima]['trans']+=$sdbibgt[$noakunlima][$noakun]['trans'];
				@$stsdbibgt[$noakunlima]['spk']+=$sdbibgt[$noakunlima][$noakun]['spk'];
				@$stsdbibgt[$noakunlima]['total']+=$sdbibgt[$noakunlima][$noakun]['total'];
				
				@$stbgttahun[$noakunlima]['upah']+=$bgttahun[$noakunlima][$noakun]['upah'];
				@$stbgttahun[$noakunlima]['material']+=$bgttahun[$noakunlima][$noakun]['material'];
				@$stbgttahun[$noakunlima]['trans']+=$bgttahun[$noakunlima][$noakun]['trans'];
				@$stbgttahun[$noakunlima]['spk']+=$bgttahun[$noakunlima][$noakun]['spk'];
				@$stbgttahun[$noakunlima]['total']+=$bgttahun[$noakunlima][$noakun]['total'];
				
				
			}
		}
		$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=2>".$_SESSION['lang']['total']." ".$noakunlima." ".$namaakun[$noakunlima]."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['total'])."</td>
					
					
					<td align=right>".@number_format($stbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stbgttahun[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['total'])."</td>
                    
				</tr>";
				
				@$gtbiupah['upah']+=$stbiupah[$noakunlima]['upah'];
				@$gtbiupah['material']+=$stbiupah[$noakunlima]['material'];
				@$gtbiupah['trans']+=$stbiupah[$noakunlima]['trans'];
				@$gtbiupah['spk']+=$stbiupah[$noakunlima]['spk'];
				@$gtbiupah['total']+=$stbiupah[$noakunlima]['total'];
				
				@$gtbibgt['upah']+=$stbibgt[$noakunlima]['upah'];
				@$gtbibgt['material']+=$stbibgt[$noakunlima]['material'];
				@$gtbibgt['trans']+=$stbibgt[$noakunlima]['trans'];
				@$gtbibgt['spk']+=$stbibgt[$noakunlima]['spk'];
				@$gtbibgt['total']+=$stbibgt[$noakunlima]['total'];
				
				@$gtsdbiupah['upah']+=$stsdbiupah[$noakunlima]['upah'];
				@$gtsdbiupah['material']+=$stsdbiupah[$noakunlima]['material'];
				@$gtsdbiupah['trans']+=$stsdbiupah[$noakunlima]['trans'];
				@$gtsdbiupah['spk']+=$stsdbiupah[$noakunlima]['spk'];
				@$gtsdbiupah['total']+=$stsdbiupah[$noakunlima]['total'];
				
				@$gtsdbibgt['upah']+=$stsdbibgt[$noakunlima]['upah'];
				@$gtsdbibgt['material']+=$stsdbibgt[$noakunlima]['material'];
				@$gtsdbibgt['trans']+=$stsdbibgt[$noakunlima]['trans'];
				@$gtsdbibgt['spk']+=$stsdbibgt[$noakunlima]['spk'];
				@$gtsdbibgt['total']+=$stsdbibgt[$noakunlima]['total'];
				
				@$gtbgttahun['upah']+=$stbgttahun[$noakunlima]['upah'];
				@$gtbgttahun['material']+=$stbgttahun[$noakunlima]['material'];
				@$gtbgttahun['trans']+=$stbgttahun[$noakunlima]['trans'];
				@$gtbgttahun['spk']+=$stbgttahun[$noakunlima]['spk'];
				@$gtbgttahun['total']+=$stbgttahun[$noakunlima]['total'];
					
	}	
}	
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan=2>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right>".@number_format($gtbiupah['upah'])."</td>
			<td align=right>".@number_format($gtbiupah['material'])."</td>
			<td align=right>".@number_format($gtbiupah['trans'])."</td>
			<td align=right>".@number_format($gtbiupah['spk'])."</td>
			<td align=right>".@number_format($gtbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtbibgt['upah'])."</td>
			<td align=right>".@number_format($gtbibgt['material'])."</td>
			<td align=right>".@number_format($gtbibgt['trans'])."</td>
			<td align=right>".@number_format($gtbibgt['spk'])."</td>
			<td align=right>".@number_format($gtbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtsdbiupah['upah'])."</td>
			<td align=right>".@number_format($gtsdbiupah['material'])."</td>
			<td align=right>".@number_format($gtsdbiupah['trans'])."</td>
			<td align=right>".@number_format($gtsdbiupah['spk'])."</td>
			<td align=right>".@number_format($gtsdbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtsdbibgt['upah'])."</td>
			<td align=right>".@number_format($gtsdbibgt['material'])."</td>
			<td align=right>".@number_format($gtsdbibgt['trans'])."</td>
			<td align=right>".@number_format($gtsdbibgt['spk'])."</td>
			<td align=right>".@number_format($gtsdbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtbgttahun['upah'])."</td>
			<td align=right>".@number_format($gtbgttahun['material'])."</td>
			<td align=right>".@number_format($gtbgttahun['trans'])."</td>
			<td align=right>".@number_format($gtbgttahun['spk'])."</td>
			<td align=right>".@number_format($gtbgttahun['total'])."</td>
		</tr>";

		
		
		
		
###########################################
#############  B B T  #####################
###########################################

$gtbiupah['upah']=$gtbiupah['material']=$gtbiupah['trans']=$gtbiupah['spk']=$gtbiupah['total']=0;
$gtbibgt['upah']=$gtbibgt['material']=$gtbibgt['trans']=$gtbibgt['spk']=$gtbibgt['total']=0;
$gtsdbiupah['upah']=$gtsdbiupah['material']=$gtsdbiupah['trans']=$gtsdbiupah['spk']=$gtsdbiupah['total']=0;
$gtsdbibgt['upah']=$gtsdbibgt['material']=$gtsdbibgt['trans']=$gtsdbibgt['spk']=$gtsdbibgt['total']=0;
$gtbgttahun['upah']=$gtbgttahun['material']=$gtbgttahun['trans']=$gtbgttahun['spk']=$gtbgttahun['total']=0;

$stream.="<thead>
		<tr class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$bbt."')>
            <td align=center><b>5</b></td>
            <td colspan=26><b>".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['pembibitan']."</b></td>
		</tr></thead>
	";	
foreach($akunlima as $noakunlima)
{
	if(substr($noakunlima,0,3)=='128')
	{
		foreach($akun as $noakun)
		{
			if(@$listakun[$noakunlima][$noakun]!='')
			{
				$stream.="
				<tr class=rowcontent  class=rowcontent style=cursor:pointer; title='click detail' onclick=html3('".$blok."','".$per2."','".$bbt."')>
					<td>".$noakun."</td>
					<td>".$namaakun[$noakun]."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($biupah[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bibgt[$noakunlima][$noakun]['total'])."</td>  
					
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['upah'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($sdbiupah[$noakunlima][$noakun]['total'])."</td>

					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['material'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['trans'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['spk'])."</td> 
					<td align=right>".@number_format($sdbibgt[$noakunlima][$noakun]['total'])."</td> 
					
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['upah'])."</td>  
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['material'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['trans'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['spk'])."</td>
					<td align=right>".@number_format($bgttahun[$noakunlima][$noakun]['total'])."</td>
				
					
				</tr>
				";
				@$stbiupah[$noakunlima]['upah']+=$biupah[$noakunlima][$noakun]['upah'];
				@$stbiupah[$noakunlima]['material']+=$biupah[$noakunlima][$noakun]['material'];
				@$stbiupah[$noakunlima]['trans']+=$biupah[$noakunlima][$noakun]['trans'];
				@$stbiupah[$noakunlima]['spk']+=$biupah[$noakunlima][$noakun]['spk'];
				@$stbiupah[$noakunlima]['total']+=$biupah[$noakunlima][$noakun]['total'];
				
				@$stbibgt[$noakunlima]['upah']+=$bibgt[$noakunlima][$noakun]['upah'];
				@$stbibgt[$noakunlima]['material']+=$bibgt[$noakunlima][$noakun]['material'];
				@$stbibgt[$noakunlima]['trans']+=$bibgt[$noakunlima][$noakun]['trans'];
				@$stbibgt[$noakunlima]['spk']+=$bibgt[$noakunlima][$noakun]['spk'];
				@$stbibgt[$noakunlima]['total']+=$bibgt[$noakunlima][$noakun]['total'];
				
				@$stsdbiupah[$noakunlima]['upah']+=$sdbiupah[$noakunlima][$noakun]['upah'];
				@$stsdbiupah[$noakunlima]['material']+=$sdbiupah[$noakunlima][$noakun]['material'];
				@$stsdbiupah[$noakunlima]['trans']+=$sdbiupah[$noakunlima][$noakun]['trans'];
				@$stsdbiupah[$noakunlima]['spk']+=$sdbiupah[$noakunlima][$noakun]['spk'];
				@$stsdbiupah[$noakunlima]['total']+=$sdbiupah[$noakunlima][$noakun]['total'];
				
				
				
				
				@$stsdbibgt[$noakunlima]['upah']+=$sdbibgt[$noakunlima][$noakun]['upah'];
				@$stsdbibgt[$noakunlima]['material']+=$sdbibgt[$noakunlima][$noakun]['material'];
				@$stsdbibgt[$noakunlima]['trans']+=$sdbibgt[$noakunlima][$noakun]['trans'];
				@$stsdbibgt[$noakunlima]['spk']+=$sdbibgt[$noakunlima][$noakun]['spk'];
				@$stsdbibgt[$noakunlima]['total']+=$sdbibgt[$noakunlima][$noakun]['total'];
				
				@$stbgttahun[$noakunlima]['upah']+=$bgttahun[$noakunlima][$noakun]['upah'];
				@$stbgttahun[$noakunlima]['material']+=$bgttahun[$noakunlima][$noakun]['material'];
				@$stbgttahun[$noakunlima]['trans']+=$bgttahun[$noakunlima][$noakun]['trans'];
				@$stbgttahun[$noakunlima]['spk']+=$bgttahun[$noakunlima][$noakun]['spk'];
				@$stbgttahun[$noakunlima]['total']+=$bgttahun[$noakunlima][$noakun]['total'];
				
				
			}
		}
		$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=2>".$_SESSION['lang']['total']." ".$noakunlima." ".$namaakun[$noakunlima]."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbiupah[$noakunlima]['total'])."</td>
					
					
					<td align=right>".@number_format($stbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbiupah[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stsdbibgt[$noakunlima]['total'])."</td>
					
					<td align=right>".@number_format($stbgttahun[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['material'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['spk'])."</td>
					<td align=right>".@number_format($stbgttahun[$noakunlima]['total'])."</td>
                    
				</tr>";
				
				@$gtbiupah['upah']+=$stbiupah[$noakunlima]['upah'];
				@$gtbiupah['material']+=$stbiupah[$noakunlima]['material'];
				@$gtbiupah['trans']+=$stbiupah[$noakunlima]['trans'];
				@$gtbiupah['spk']+=$stbiupah[$noakunlima]['spk'];
				@$gtbiupah['total']+=$stbiupah[$noakunlima]['total'];
				
				@$gtbibgt['upah']+=$stbibgt[$noakunlima]['upah'];
				@$gtbibgt['material']+=$stbibgt[$noakunlima]['material'];
				@$gtbibgt['trans']+=$stbibgt[$noakunlima]['trans'];
				@$gtbibgt['spk']+=$stbibgt[$noakunlima]['spk'];
				@$gtbibgt['total']+=$stbibgt[$noakunlima]['total'];
				
				@$gtsdbiupah['upah']+=$stsdbiupah[$noakunlima]['upah'];
				@$gtsdbiupah['material']+=$stsdbiupah[$noakunlima]['material'];
				@$gtsdbiupah['trans']+=$stsdbiupah[$noakunlima]['trans'];
				@$gtsdbiupah['spk']+=$stsdbiupah[$noakunlima]['spk'];
				@$gtsdbiupah['total']+=$stsdbiupah[$noakunlima]['total'];
				
				@$gtsdbibgt['upah']+=$stsdbibgt[$noakunlima]['upah'];
				@$gtsdbibgt['material']+=$stsdbibgt[$noakunlima]['material'];
				@$gtsdbibgt['trans']+=$stsdbibgt[$noakunlima]['trans'];
				@$gtsdbibgt['spk']+=$stsdbibgt[$noakunlima]['spk'];
				@$gtsdbibgt['total']+=$stsdbibgt[$noakunlima]['total'];
				
				@$gtbgttahun['upah']+=$stbgttahun[$noakunlima]['upah'];
				@$gtbgttahun['material']+=$stbgttahun[$noakunlima]['material'];
				@$gtbgttahun['trans']+=$stbgttahun[$noakunlima]['trans'];
				@$gtbgttahun['spk']+=$stbgttahun[$noakunlima]['spk'];
				@$gtbgttahun['total']+=$stbgttahun[$noakunlima]['total'];
					
	}	
}	
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan=2>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right>".@number_format($gtbiupah['upah'])."</td>
			<td align=right>".@number_format($gtbiupah['material'])."</td>
			<td align=right>".@number_format($gtbiupah['trans'])."</td>
			<td align=right>".@number_format($gtbiupah['spk'])."</td>
			<td align=right>".@number_format($gtbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtbibgt['upah'])."</td>
			<td align=right>".@number_format($gtbibgt['material'])."</td>
			<td align=right>".@number_format($gtbibgt['trans'])."</td>
			<td align=right>".@number_format($gtbibgt['spk'])."</td>
			<td align=right>".@number_format($gtbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtsdbiupah['upah'])."</td>
			<td align=right>".@number_format($gtsdbiupah['material'])."</td>
			<td align=right>".@number_format($gtsdbiupah['trans'])."</td>
			<td align=right>".@number_format($gtsdbiupah['spk'])."</td>
			<td align=right>".@number_format($gtsdbiupah['total'])."</td>
			
			<td align=right>".@number_format($gtsdbibgt['upah'])."</td>
			<td align=right>".@number_format($gtsdbibgt['material'])."</td>
			<td align=right>".@number_format($gtsdbibgt['trans'])."</td>
			<td align=right>".@number_format($gtsdbibgt['spk'])."</td>
			<td align=right>".@number_format($gtsdbibgt['total'])."</td>
			
			<td align=right>".@number_format($gtbgttahun['upah'])."</td>
			<td align=right>".@number_format($gtbgttahun['material'])."</td>
			<td align=right>".@number_format($gtbgttahun['trans'])."</td>
			<td align=right>".@number_format($gtbgttahun['spk'])."</td>
			<td align=right>".@number_format($gtbgttahun['total'])."</td>
		</tr>";		
		
		
$stream.="
 </tbody>
     </table>";
$stream.="</table></fieldset>";	

switch ($method) {
######PREVIEW
    case 'html2':
		//echo $blok;
		echo"
			<button id=tomboldetail class=mybutton onclick=kehtml1()>Level 1</button> 
			<button id=tomboldetail class=mybutton disabled>Level 2</button>
		";
		echo"<br>";
		echo "
			<button id=tomboldetail class=mybutton onclick=excel1(event) disabled>" . $_SESSION['lang']['excel'] . " 1</button>   
			<button id=tomboldetail class=mybutton onclick=excel2(event,'".$blok."','".$per2."')>" . $_SESSION['lang']['excel'] . " 2</button>   
		";
		
		echo"<br>";
		echo"<br>";
		
        echo $stream;
        break;

######EXCEL	
    case 'excel2':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "BIAYA_DAN_PRODUKSI_PERBLOK_LV2_" . $blok;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;
}
?>