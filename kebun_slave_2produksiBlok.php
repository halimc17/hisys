<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=$_GET['proses'];
switch ($proses){
	case 'preview':
		$param=$_POST; 
		break;
	case 'excel':
		$param=$_GET;    
		break;
}


$namaOrg=makeOption($dbname,'organisasi','indukblok,namaorganisasi');

    $bulanini=$param['periode'];
    $qwe=explode('-',$bulanini);
    @$tahunlalu=$qwe[0];
    @$bulanlalu=$qwe[1];
    if($bulanlalu=='01'){
        $tahunlalu-=1;
        $bulanlalu='12';
    }else{
        $bulanlalu-=1;
    }
    
    $bulanlalu=str_pad($bulanlalu, 2, "0", STR_PAD_LEFT);

    // bjr bulan kemarin =  taken from kebun_laporanPanen_orang.php
    $bulankemarin=$tahunlalu."-".$bulanlalu;
    
    $sbjrlalu="select blok, sum(jjg) as jjg, sum(kgwb) as kgwb from ".$dbname.".kebun_spb_vw
        where notiket IS NOT NULL and tanggal like '".$bulankemarin."%'
        group by blok";
	$qbjrlalu=$owlPDO->query($sbjrlalu) or die(print " Gagal: ".PDOException::getMessage());
	$qbjrlalu->setFetchMode(PDO::FETCH_ASSOC);
    while($rbjrlalu=$qbjrlalu->fetch())
    {
        @$beje=$rbjrlalu['kgwb']/$rbjrlalu['jjg'];
        $bjrlalu[$rbjrlalu['blok']]=$beje;
    }    
    
	if($param['intiplasma']!='')
	{
		$whrip=" and  intiplasma='".$param['intiplasma']."' ";
	}
	

        //ambil  tahun tanam
        $str="select indukblok,tahuntanam,indukblok from ".$dbname.".setup_blok where kodeorg like '".$param['idKebun']."%' ".@$whrip." group by indukblok ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $tt[$bar->kodeorg]=$bar->tahuntanam;
            $blok[]=$bar->indukblok;
        }
        
        //ambil  jjg panen
        $str="select sum(hasilkerja) as jjgpanen,kodeorg,tanggal from ".$dbname.".kebun_prestasi_vw where tanggal like '".$param['periode']."%'
                  and kodeorg like '".$param['idKebun']."%' group by tanggal,kodeorg";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $jjgpanen[$bar->tanggal][$bar->kodeorg]=$bar->jjgpanen;
        }

        //ambil janjang spb
        $str="select sum(jjg) as jjgangkut,blok,beratbersihtimbangan as kgwb, tanggal,sum(brondolan) as brd from ".$dbname.".kebun_spb_vw where tanggal like '".$param['periode']."%'
                  and kodeorg = '".$param['idKebun']."' group by tanggal,blok";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
        {
            $jjgangkut[$bar->tanggal][$bar->blok]=$bar->jjgangkut;
            $brdkbn[$bar->tanggal][$bar->blok]=$bar->brd;
            $berat[$bar->tanggal][$bar->blok]=$bar->kgwb;
        }        

        
        //======================================
        //ambil spb per tiket
        $str="select blok,jjg,tanggal,notiket from ".$dbname.".kebun_spb_vw where tanggal like '".$param['periode']."%'
                  and kodeorg = '".$param['idKebun']."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
            $spbk[$bar->notiket][$bar->tanggal][$bar->blok]=$bar->jjg;
            $spbktg[$bar->notiket]=$bar->tanggal;
        }

        //ambil brondolan per no tiket dari timbangan
        $str="select notransaksi,brondolan as bb from ".$dbname.".pabrik_timbangan
                  where notransaksi in(select notiket from ".$dbname.".kebun_spb_vw where tanggal like '".$param['periode']."%'
                  and kodeorg = '".$param['idKebun']."')";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$tiket = array();
        while($bar=$res->fetch())
        {
            $tiket[$bar->notransaksi]=$bar->bb;
        }        
        //kalkulasi brondolan per blok spb;
        foreach($tiket as $tik =>$nx)
        {
            foreach($spbk[$tik] as $tg){
                    $tjg=array_sum($tg);
                    foreach($tg as $bl=>$jg)
                    {
                            setIt($brd[$spbktg[$tik]][$bl],0);
                            $brd[$spbktg[$tik]][$bl]+=$jg/$tjg*$tiket[$tik];
                    }
            }    
            
        }
        
        
        
                ##tambahan ind
        #ambil tiket
        $iTim="select sum(kgsortasi) as kgsortasi,blok,tanggal from ".$dbname.".sortasi_pabrik_spb 
                  where tanggal like '".$param['periode']."%'
                  and blok like '%".$param['idKebun']."%' group by tanggal,blok ";
        $nTim=$owlPDO->query($iTim) or die(print " Gagal: ".PDOException::getMessage());
		$nTim->setFetchMode(PDO::FETCH_ASSOC);
        while($dTim=  $nTim->fetch())
        {
            //$berat[$bar->tanggal][$bar->blok]=$bar->kgwb;
            $sortasi[$dTim['tanggal']][$dTim['blok']]=$dTim['kgsortasi'];
        }
     
        // echo"<pre>";
        //print_r($sortasi);
        // echo"</pre>";
        
        
 $stream="
         <table class=sortable border=".($proses=='preview'?'0':'1')." cellspacing=1 cellpadding=5>
          <thead>
          <tr class=rowheader>
             <td align=center>No</td>
             <td align=center>".$_SESSION['lang']['tanggal']."</td>
             <td align=center>".$_SESSION['lang']['blok']."</td>
             <td align=center>".$_SESSION['lang']['tbs']." ".$_SESSION['lang']['panen']."(JJG)</td>
             <td align=center>".$_SESSION['lang']['pengiriman']."(JJG)</td>
             <td align=center>Netto(Kg)</td>        
             <td hidden align=center>Sortasi (Kg)</td> 
             <td align=center style=width:".($proses=='preview'?'60px':'200px').">Berat Normal (netto-sortasi) (Kg)</td> 
             <td align=center>".$_SESSION['lang']['bjr']." Actual</td>           
             <td hidden align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['blnlalu']."</td>           
          </tr></thead><tbody>
          ";
      //jumlah hari
      
      ##defaultnya ini
     //$mk=mktime(0,0,0,substr($param['periode'],5,2),15,substr($param['periode'],0,4));
      
      #kenapa jadi hanya 15 hari.. di move jadi 31 hari
 
    $str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' limit 1 ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
    $bar=  $res->fetch();
        $tglakhir=  explode('-', $bar['tanggalsampai']);
    
 
      $mk=mktime(0,0,0,substr($param['periode'],5,2),$tglakhir[2],substr($param['periode'],0,4));
      $jhari=date('j',$mk);
      $a=$tjp=$tja=$tbk=$tb=$tberat=0;
      
      
      for($x=1;$x<=$jhari;$x++){
          
      
          
          foreach($blok as $ki=>$bl){
              
              
              
              
            $tttt=str_pad($x, 2, "0", STR_PAD_LEFT);
            
           
            
			setIt($jjgpanen[$param['periode']."-".$tttt][$bl],0);
			setIt($jjgangkut[$param['periode']."-".$tttt][$bl],0);
			setIt($brdkbn[$param['periode']."-".$tttt][$bl],0);
			setIt($brd[$param['periode']."-".$tttt][$bl],0);
			setIt($berat[$param['periode']."-".$tttt][$bl],0);
            if($jjgpanen[$param['periode']."-".$tttt][$bl]>0 or $jjgangkut[$param['periode']."-".$tttt][$bl]>0 or $brdkbn[$param['periode']."-".$tttt][$bl]>0 or $brd[$param['periode']."-".$tttt][$bl]>0)
            {
                $a++;
                @$bjraktual=$berat[$param['periode']."-".$tttt][$bl]/$jjgangkut[$param['periode']."-".$tttt][$bl];
                if($bjraktual<@$bjrlalu[$bl]){
                    $merah=' bgcolor=red';
                }else{
                    $merah='';
                }
                $stream.="<tr class=rowcontent>
                           <td  align=center>".$a."</td>
                           <td>".$param['periode']."-".$tttt."</td>
                           <td>".$namaOrg[$bl]."</td>
                            <td align=right>".number_format($jjgpanen[$param['periode']."-".$tttt][$bl])."</td>
                            <td align=right>".number_format($jjgangkut[$param['periode']."-".$tttt][$bl])."</td>    
                           <td align=right>".number_format($berat[$param['periode']."-".$tttt][$bl],2)."</td> 
                            
                            <td hidden align=right>".number_format(@$sortasi[$param['periode']."-".$tttt][$bl],2)."</td> 
                            <td align=right>".number_format(@$berat[$param['periode']."-".$tttt][$bl]-@$sortasi[$param['periode']."-".$tttt][$bl],2)."</td>     
                           
                           <td align=right ".$merah.">".number_format($bjraktual,2)."</td> 
                           <td hidden align=right>".number_format(@$bjrlalu[$bl],2)."</td> 
                     </tr>";
				$tjp+=$jjgpanen[$param['periode']."-".$tttt][$bl];
                $tja+=$jjgangkut[$param['periode']."-".$tttt][$bl];
                $tbk+=$brdkbn[$param['periode']."-".$tttt][$bl];
                $tb+=$brd[$param['periode']."-".$tttt][$bl];
                $tberat+=$berat[$param['periode']."-".$tttt][$bl];
                
                @$tsortasi+=@$sortasi[$param['periode']."-".$tttt][$bl];
                @$tnormal+=$berat[$param['periode']."-".$tttt][$bl]-@$sortasi[$param['periode']."-".$tttt][$bl];
            }
          }
      }
      $stream.="</tbody><tfoot>
                    <tr class=rowcontent>
                       <td colspan=3  align=center>TOTAL</td>
                       <td align=right>".number_format($tjp,2)."</td>
                       <td align=right>".number_format($tja,2)."</td>
                       <td align=right>".number_format($tberat,2)."</td>
                       <td align=right>".number_format($tnormal,2)."</td>    
                       <td></td>
                       </tr align=right>
                 </tfoot></table>Pastikan SPB sudah diinput dengan Benar/Make sure all FFB Transport document has been confirmed";
        //========================================
switch ($proses){
        case 'preview':
                echo $stream;
            break;
         case 'excel':
            $nop_="produksiperblok_".$param['idKebun']."_".$param['periode'];
            if(strlen($stream)>0)
            {
                 $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                 gzwrite($gztralala, $stream);
                 gzclose($gztralala);
                 echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
            }
             break;
}

?>
