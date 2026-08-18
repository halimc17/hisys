<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');

$kets = checkPostGet('kets', '');
$tgls = checkPostGet('tgls', '');
$bloks = checkPostGet('bloks', '');
$angkas = checkPostGet('angkas', '');

switch ($proses) {
######PREVIEW
    case 'preview':
            
            if($kdorg=='')
            {
                if($_SESSION['language']=='EN')
                    echo"Warning: Business unit required";
                else
                    echo"Warning: Unit tidak boleh kosong";     
                exit;
            }

            if(($tgl1=='')or($tgl2==''))
            {
                if($_SESSION['language']=='EN')
                    echo"Warning: Date required";
                else
                    echo"Warning: Tanggal tidak boleh kosong";     
                exit;
            }

            else if($tgl1>$tgl2)
            {
                if($_SESSION['language']=='EN')
                    echo"Warning: Starting date must lower";
                else
                    echo"Warning: Tanggal pertama tidak boleh lebih besar dari tanggal kedua";     
                exit;
            }
            
            $rangetanggal = rangeTanggal($tgl1, $tgl2);
            $cek=count($rangetanggal);
            
            if ($cek>8)
            {
                if($_SESSION['language']=='EN')
                    echo"Warning: Max 7 days";
                else
                    echo"Warning: Maksimal 7 hari";     
                exit;
            }
            
           

            ######################################
            ############# prepare data ###########
            ######################################
            
            
            $tglkemarinlusa = strtotime('-2 day',strtotime($tgl1));
            $tglkemarinlusa = date('Y-m-d', $tglkemarinlusa);
            
            $tglkemarin = strtotime('-1 day',strtotime($tgl1));
            $tglkemarin = date('Y-m-d', $tglkemarin);


            #bentuk data blok dari rekap panen
            $str="select distinct(blok) as blok,divisi,tahuntanam from ".$dbname.".kebun_rekappnn_vw where "
                    . " divisi like '".$kdorg."%' and  tanggal between '".$tgl1."' and '".$tgl2."' order by blok asc ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
            {
                $kdblok[$bar['blok']]=$bar['blok'];
                
                //$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
            }
            
            
            $str="select distinct(blok) as blok,divisi,tahuntanam from ".$dbname.".kebun_pusingan_vw where "
                    . " unit = '".$kdorg."'  order by blok asc ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
            {
                $kdblok[$bar['blok']]=$bar['blok'];
                
                //$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
            }
            
            
            #bentuk data panen
            $str=" select * from ".$dbname.".kebun_rekappnn_vw where divisi like '".$kdorg."%' "
                    . " and tanggal between '".$tglkemarinlusa."' and '".$tgl2."' order by blok asc ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
            {
                $data[$bar['blok']][$bar['tanggal']]=array('panen'=>'P');
                $angka[$bar['blok']][$bar['tanggal']]=0;
            }
             
            
            #ambil data dari kebun_pusingan
            $str=" select * from ".$dbname.".kebun_pusingan where blok like '".$kdorg."%' "
                    . " and tanggal = '".$tglkemarin."'  order by blok asc ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
                $datakmrn[$bar['blok']][$bar['tanggal']]=$bar['angka'];
            }

            @$cekdatakemarin=count($datakmrn);
            if($cekdatakemarin<1){
                if($_SESSION['language']=='EN')
                    echo"Warning: Required process on previous date";
                else
                    echo"Warning: Tanggal kemarin belum di proses";     
                exit;
            }
            
            //echo"<pre>";
            //print_r($datakmrn);
           // echo"</pre>";
           

            $stream = "<table class=sortable cellspacing=1>";
            $stream.="
                <thead>
                    <tr class=rowheader>
                        <td align=center>" . $_SESSION['lang']['nourut'] . "</td>  
                        <td align=center>" . $_SESSION['lang']['blok'] . "</td>     
                        <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>  
                        <td align=center>Angka</td>     
                        <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>  
                           
                    </tr>";
            $stream.="
                    </tr>
                </thead>
             <tbody>";


            foreach($kdblok as $blok)
            {
                @$noblok+=1;
                foreach($rangetanggal as $listtanggal => $tgl)
                {

                    ########cara hitung tanggal kemarin###############
                    $tglkemarin = strtotime('-1 day',strtotime($tgl));
                    $tglkemarin = date('Y-m-d', $tglkemarin);

                    $tglkemarinlusa = strtotime('-2 day',strtotime($tgl));
                    $tglkemarinlusa = date('Y-m-d', $tglkemarinlusa);
                    
                    $bloklama=isset($bloklama)?$bloklama:'';
                    if($bloklama==$blok){
                        $angkakemarin=$angkakemarin;
                    }else{
                        $angkakemarin=1;
                    }
                    
                    $datakmrn[$blok][$tglkemarin]=isset($datakmrn[$blok][$tglkemarin])?$datakmrn[$blok][$tglkemarin]:'';
                    //if($datakmrn[$blok][$tglkemarin]!='' || $datakmrn[$blok][$tglkemarin]!='0')
                    if($datakmrn[$blok][$tglkemarin]!=''){
                        $angkakemarin=$datakmrn[$blok][$tglkemarin];
                    }
                    
                    $data[$blok][$tgl]['panen']=isset($data[$blok][$tgl]['panen'])?$data[$blok][$tgl]['panen']:'';
                    $data[$blok][$tglkemarin]['panen']=isset($data[$blok][$tglkemarin]['panen'])?$data[$blok][$tglkemarin]['panen']:'';
                    $data[$blok][$tglkemarinlusa]['panen']=isset($data[$blok][$tglkemarinlusa]['panen'])?$data[$blok][$tglkemarinlusa]['panen']:'';
                    
                    if($data[$blok][$tgl]['panen']=='P' && ($data[$blok][$tglkemarin]['panen']=='P' || $data[$blok][$tglkemarinlusa]['panen']=='P'))
                    {
                        $angka=$angkakemarin+1; 
                    }
                    else if($data[$blok][$tgl]['panen']=='' && ($data[$blok][$tglkemarin]['panen']=='P' || $data[$blok][$tglkemarinlusa]['panen']=='P'))
                    {
                        $angka=$angkakemarin+1;
                    }
                    else if($data[$blok][$tgl]['panen']=='' && ($data[$blok][$tglkemarin]['panen']=='' || $data[$blok][$tglkemarinlusa]['panen']==''))                       
                    {
                        $angka=$angkakemarin+1;  
                    }  
                    else
                    {
                        $angka=1; 
                    }
                    
                    
                    
                    if($noblok%2==0)
                    {
                        $bgcolor="bgcolor=beige";
                    }
                    else
                    {
                        $bgcolor="bgcolor=pink";
                    }
                    
                  
                    $no+=1;//class=rowcontent
                    $stream.="<tr $bgcolor id=row".$no.">
                                <td align=center $bgcolor>".$no."</td>
                                <td align=center id=bloks".$no.">".$blok."</td>
                                <td align=center id=tgls".$no.">".$tgl."</td>
                                <td align=center id=angkas".$no.">".$angka."</td>   
                                <td align=center id=kets".$no.">".$data[$blok][$tgl]['panen']."</td>    
                                 
                            </tr>     
                ";
                    $angkakemarin=$angka;
                    
                    $bloklama=$blok;
                    
                }

            }
            $stream.="<button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['proses']."</button>";
            $stream.="
             </tbody>
                 </table>";
        echo $stream;
    break;
        
        
    case'savedata':
        
        $str="insert into ".$dbname.".kebun_pusingan (`blok`, `angka`, `tanggal`, `keterangan`,`updateby`) 
        values ('".$bloks."','".$angkas."','".$tgls."','".$kets."','".$_SESSION['standard']['userid']."')";
        try
        {
            $owlPDO->exec($str); 
        }
        catch (PDOException $e) 
        {
            $str="update  ".$dbname.".kebun_pusingan set angka='".$angkas."',keterangan='".$kets."',"
                    . "updateby='".$_SESSION['standard']['userid']."' where blok='".$bloks."' and tanggal='".$tgls."' ";
            try
            {
                $owlPDO->exec($str); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        }
    break;
}
?>