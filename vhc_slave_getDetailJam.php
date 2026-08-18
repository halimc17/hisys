<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  

  echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 
  
$param=$_GET;
  $str="select b.tanggal,a.notransaksi,a.alokasibiaya,a.keterangan,a.jumlah 
        from ".$dbname.".vhc_rundt a 
        left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi  
        where kodevhc='".$param['kodevhc']."' 
        and tanggal = '".$param['tanggal']."'";
echo "Detail Activity : ".$param['kodevhc']." ".$_SESSION['lang']['tanggal']." : ".tanggalnormal($param['tanggal'])."
      <table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%><thead>
      <tr class=rowheader>
		  <th align=center>".$_SESSION['lang']['nourut']."</th>
          <th align=center>".$_SESSION['lang']['tanggal']."</th>
          <th align=center>".$_SESSION['lang']['notransaksi']."</th>
          <th align=center>".$_SESSION['lang']['alokasibiaya']."</th>
          <th align=center>".$_SESSION['lang']['keterangan']."</th>
          <th align=center>".$_SESSION['lang']['jumlah']." (HM/KM)</th>  
      </tr>
      </thead>
      <tbody>";
$no=0;
$ttl=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
   $no+=1;
    echo"<tr class=rowcontent>
          <td align=center>".$no."</td>
          <td>".tanggalnormal($bar->tanggal)."</td>   
          <td>".$bar->notransaksi."</td>
          <td>".$bar->alokasibiaya."</td>
          <td>".$bar->keterangan."</td>    
          <td align=right>".$bar->jumlah."</td>
      </tr>";  
    $ttl+=$bar->jumlah;
}
    echo"<tr class=rowcontent>
          <td colspan=5 align=center>Total</td> 
          <td align=right>".$ttl."</td>
      </tr>"; 
echo"</tbody><tfoot></tfoot></table>";
?>