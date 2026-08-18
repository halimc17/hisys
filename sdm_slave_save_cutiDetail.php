<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$kodeorg	=$_POST['kodeorgJ'];
$karyawanid	=$_POST['karyawanidJ'];
$periode	=$_POST['periodeJ'];
$dari		=tanggalsystem($_POST['dariJ']);
$sampai		=tanggalsystem($_POST['sampaiJ']);
$diambil	=$_POST['diambilJ'];
$keterangan	=$_POST['keteranganJ'];
$method     =$_POST['method'];

$optSubBagian = makeOption($dbname,'datakaryawan','karyawanid,subbagian');
$optlokasitugas = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas');


//periksa apakah ada yang tidak benar
//==============================================
function getRangeTanggal($tglAwal,$tglAkhir){
        $jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
        $jlhHari = $jlh / (3600*24);
        return $jlhHari + 1;
}

$rangeTgl = rangeTanggal($dari, $sampai);

if($method=='insert')
{
        // if(getRangeTanggal($dari,$sampai) != $diambil){
                // exit("Gagal : Perisaka kembali tanggal dari/sampai cuti.");
        // }

        $strAbsen = "select * from ".$dbname.".sdm_absensidt where karyawanid = '".$karyawanid."' and tanggal between '".$dari."' and '".$sampai."'";
        $res=$owlPDO->query($strAbsen) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res);
        if($numrows> 0){
                exit("Gagal : Untuk range tanggal awal s/d akhir cuti sudah ada absen.");
        }

		$strc="select * from ".$dbname.".sdm_cutidt
       where karyawanid = '".$karyawanid."' and ((daritanggal>=".$dari." and daritanggal<=".$sampai.")
           or (sampaitanggal>=".$dari." and sampaitanggal<=".$sampai.")
           or (daritanggal<=".$dari." and sampaitanggal>=".$sampai."))";
        $res=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res);
        if($numrows>0)
        {
                echo " Error ".$_SESSION['lang']['irisan'];
                exit(0);
        }	
        else if($sampai<$dari)
        {
                echo " Error < >";
                exit(0);
        } 
}

//===============================================

        if($diambil==''){
                $diambil=0;
        }

        switch($method)
        {
        case 'delete':	
				if($optSubBagian[$karyawanid]==''){
					$optSubBagian[$karyawanid]=$optlokasitugas[$karyawanid];
				}else{
					$optSubBagian[$karyawanid]=$optSubBagian[$karyawanid];
				}

		
                $rangeTglDel = rangeTanggal($_POST['dariJ'], $_POST['sampaiJ']);

                foreach($rangeTglDel as $val){
                        $strDelAbs = "delete from ".$dbname.".sdm_absensidt where kodeorg='".$optSubBagian[$karyawanid]."' and karyawanid='".$karyawanid."' and tanggal='".$val."' and absensi='C'";
                        $owlPDO->exec($strDelAbs);
                }

                $str="delete from ".$dbname.".sdm_cutidt
                       where kodeorg='".$kodeorg."'
                           and karyawanid=".$karyawanid."
                           and periodecuti='".$periode."'
                           and daritanggal='".$_POST['dariJ']."'";
                break;	   
        case 'insert':
		
				if($optSubBagian[$karyawanid]==''){
					$optSubBagian[$karyawanid]=$optlokasitugas[$karyawanid];
				}else{
					$optSubBagian[$karyawanid]=$optSubBagian[$karyawanid];
				}

				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='1'");
				$gajipokok = @($optGaji[$karyawanid]/25);
		
                foreach($rangeTgl as $val){
					$tgl=$val;
					#cek hari minggu dan hari besar
					$day = date('D', strtotime($tgl));
					if($day=='Sun')$libur=true; else $libur=false;
					// kamus hari libur
					$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."'";
					$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
					$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
					while($roworg=$queorg->fetch()){
						if($roworg['keterangan']=='libur')$libur=true;
						if($roworg['keterangan']=='masuk')$libur=false;
					}
					if($libur==false){
						$strAbs = "insert into ".$dbname.".sdm_absensidt 
						(kodeorg,tanggal,karyawanid,absensi,penjelasan,hk,umr) 
						values 
						('".$optSubBagian[$karyawanid]."','".$val."','".$karyawanid."','C','".$keterangan."','1','".$gajipokok."')";		
						$owlPDO->exec($strAbs);
					}
                }

                $str="insert into ".$dbname.".sdm_cutidt 
                      (kodeorg,karyawanid,periodecuti,daritanggal,
                          sampaitanggal,jumlahcuti,keterangan
                          )
                      values('".$kodeorg."',".$karyawanid.",
                          '".$periode."','".$dari."','".$sampai."',
                          ".$diambil.",'".$keterangan."'
                          )";
                break;
        default:
           break;					
        }
try{
    $owlPDO->exec($str); 
                //ambil sum jumlah diambil dan update table header
            $strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt
                   where kodeorg='".$kodeorg."'
                       and karyawanid=".$karyawanid."
                       and periodecuti='".$periode."'";

            $diambil=0;
            $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
            $resx->setFetchMode(PDO::FETCH_OBJ);
            while($barx=$resx->fetch())
            {
                    $diambil=$barx->diambil;
            }
            if($diambil=='')
                $diambil=0;
            $strup="update ".$dbname.".sdm_cutiht set diambil=".$diambil.",sisa=(hakcuti-".$diambil.")	
                   where kodeorg='".$kodeorg."'
                       and karyawanid=".$karyawanid."
                       and periodecuti='".$periode."'";
             try{$owlPDO->exec($strup); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
?>
