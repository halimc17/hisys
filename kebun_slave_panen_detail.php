<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zGrid.php');
#include_once('lib/rGrid.php');
include_once('lib/formTable.php');

//$proses = $_GET['proses'];
$proses=checkPostGet('proses','');
$param = $_POST;

$str="select * from ".$dbname.".bgt_regional_assignment 
        where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $regional=$bar->regional;
}

switch($proses) {
	case'cariblok'://exit("Error:ata");
		
		if($param['divisi']==''){
			$param['divisi']=$_SESSION['empl']['lokasitugas'];	
		}
	
		if($param['divisi']!=''){
			$where.=" and kodeorg like '%".$param['divisi']."%' ";
		}
		if($param['schblok']!==''){
			$where.=" and kodeorg like '%".$param['schblok']."%'";
		}
	
		$data="
				<table  class=sortable cellspacing=1 border=0 cellpadding=1 width=100%><thead>";
		$data.="<tr class=rowheader >
				<td align=center>".$_SESSION['lang']['blok']."</td>
				<td align=center width=50px>".$_SESSION['lang']['tahuntanam']."</td>
				<td align=center>".$_SESSION['lang']['luas']."</td>
				<td align=center>".$_SESSION['lang']['pokok']."</td>
				</tr></thead><tbody>";
				
		$blokStatus = $_SESSION['tmp']['actStat'];
		switch($blokStatus) {
			case 'lc':
			$whereBlok = " and statusblok='TB' ";
			break;
			case 'bibit':
			$whereBlok = " and statusblok='BBT' ";
			break;
			case 'tbm':
			$whereBlok = " and statusblok='TBM' ";
			break;
			case 'tm':
			$whereBlok = "and statusblok='TM'";
			break;
			default:
			break;
		}
		
		if($blokStatus=='bibit')
		{
			$whereOrg = "a.tipe='BIBITAN' and length(a.kodeorganisasi)>6 and a.kodeorganisasi like '".$param['divisi']."%' and a.kodeorganisasi like '%".$param['schblok']."%'";
		}
        else
		{
			$whereOrg = " b.kodeorg like '".$param['divisi']."%' and luasareaproduktif>0 ".$whereBlok." and a.tipe='BLOK' and a.kodeorganisasi like '%".$param['schblok']."%'";

        }
				
		$str = "select a.kodeorganisasi as kodeorg, b.luasareaproduktif, b.tahuntanam, b.jumlahpokok from ".$dbname.".organisasi a
				left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
				where ".$whereOrg."";
		// $str="select kodeorg, luasareaproduktif,tahuntanam,jumlahpokok from ".$dbname.".setup_blok where 1=1 ".$where." ";
		//exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data.=" <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveblok('".$bar['kodeorg']."');\">";
			$data.="<td>".$bar['kodeorg']."</td>";
			$data.="<td align=center>".$bar['tahuntanam']."</td>";
			$data.="<td align=right>".number_format($bar['luasareaproduktif'])."</td>";
			$data.="<td align=right>".number_format($bar['jumlahpokok'])."</td>";
			$data.="</tr>";
			
		}
		
			$data.="</tbody></table>";
		echo $data;
		
		
	
	break;
	
    case 'showDetail':
                #== Prep Tab
                $headFrame = array(
					$_SESSION['lang']['prestasi'],
					$_SESSION['lang']['absensi'],
					$_SESSION['lang']['material']
                );
                $contentFrame = array();

                // Tanggal
                $tmpTgl = explode('-',$param['tanggal']);
                $tahun = $tmpTgl[2];

                # Options
                $tanggalx=substr($param['notransaksi'],0,4).'-'.substr($param['notransaksi'],4,2).'-'.substr($param['notransaksi'],6,2);
				
				#============== KHT, KHL dan Kontrak ======================
				$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and ".
                        "tipekaryawan in (2,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tanggalx."')";
                $whereKary2 = "lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN') and ".
                        "tipekaryawan in (2,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tanggalx."')";
                #============== KHT, KHL dan Kontrak ======================
                $whereKeg = "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and ";
                $whereKeg .= "kelompok='PNN'";

                $optKary = makeOption($dbname,'datakaryawan','karyawanid,nik,subbagian,namakaryawan',$whereKary,'6');
                $optKary2 = makeOption($dbname,'datakaryawan','karyawanid,nik,subbagian,namakaryawan',$whereKary2,'6');
                $optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$whereKeg);
                
                $whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where
				left(kodeorg,4)='".$_SESSION['empl']['lokasitugas']."' 
                                and luasareaproduktif>0 and statusblok='TM')
                          and tipe='BLOK' and left(kodeorganisasi,4)='".$_SESSION['empl']['lokasitugas']."'";
               
                $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);
                //$optOrg = getOrgBelow($dbname,$param['afdeling'],false,'bloktbm');
                $optThTanam= makeOption($dbname,'setup_blok','kodeorg,tahuntanam',
                        "kodeorg='".key($optOrg)."'");
                $optBin = array('1'=>'Ya','0'=>'Tidak');
                $thTanam = $optThTanam[key($optOrg)];
				$bjr = 0;

                // Validasi Empty
                if(empty($optKary)) {
                        exit("Warning: Data Karyawan KHT dan KHL tidak ada.".
                                 "\nTransaksi panen tidak dapat dilanjutkan");
                }
				array_push($optOrg, '');
				
                #=============================== Get UMR ==============================
                $firstKary = getFirstKey($optKary);
                $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
                        "karyawanid=".$firstKary." and tahun=".$tahun." and idkomponen in (1,31)");
                $Umr = fetchData($qUMR);
                $umrHarian = $Umr[0]['nilai']/25;
                #=============================== Get UMR ==============================

                #================ Prestasi =============================
                # Get Data
                $where = "notransaksi='".$param['notransaksi']."'";
                $cols = "nik,kodeorg,tahuntanam,bjr,norma,outputminimal,hasilkerja,hasilkerjakg,upahkerja,luaspanen,brondolan,upahpremi,upahpremilebihbasis,".
                        "upahpenalty,penalti1,penalti2,penalti3,penalti4,penalti5,penalti6,penalti7,penalti8,penalti9,penalti10,penalti11,penalti12,penalti13,rupiahpenalty";
                $query = selectQuery($dbname,'kebun_prestasi',$cols,$where);
                $data = fetchData($query);
                $dataShow = $data;

                // Masking Segment
                $arrSegment = array();
                foreach($data as $row) {
                        $arrSegment[$row['kodesegment']] = "'".$row['kodesegment']."'";
                }
                if(!empty($arrSegment)) {
                        $whereSegment = "kodesegment in (".implode(',',$arrSegment).")";
                        $optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',$whereSegment);
                } else {
                        $optSegment = array();
                }
                $optSegment[''] = '';

                foreach($dataShow as $key=>$row) {
						if($optKary[$row['nik']]=='')
						{
							$dataShow[$key]['nik'] = $optKary2[$row['nik']];							
						}
						else
						{
							$dataShow[$key]['nik'] = $optKary[$row['nik']];
						}
                        $dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
                        $dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
                        #$dataShow[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
                        #$dataShow[$key]['pekerjaanpremi'] = $optBin[$row['pekerjaanpremi']];
                }

                        // cari hari
                        $day = date('D', strtotime($tanggalx));
                        if($day=='Sun')$libur=true; else $libur=false;
                        // kamus hari libur
                        $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggalx."' and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
                        $queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
                        $queorg->setFetchMode(PDO::FETCH_ASSOC);                   
                        while($roworg=$queorg->fetch())
                        {
                                if($roworg['keterangan']=='libur')$libur=true;
                                if($roworg['keterangan']=='masuk')$libur=false;
                        }    

                # Form
                $theForm2 = new uForm('prestasiForm','Form Prestasi',3);       
                $theForm2->addEls('nik',$_SESSION['lang']['nama'],'','selectsearch','L',25,$optKary);
				$theForm2->_elements[0]->_attr['onchange'] = "updUpah()";
				
				/*
                if($libur==false){
					if($regional!='KALTIM')
						$theForm2->_elements[0]->_attr['onchange'] = "updUpah()";
					else
						$theForm2->_elements[0]->_attr['onchange'] = "updUpah2()";
                }
				*/
				
                $theForm2->addEls('kodeorg',$_SESSION['lang']['blok'],'','selectwithsearch','L',25,$optOrg);
				$theForm2->_elements[1]->_attr['onchange'] = "updTahunTanam();";
				$theForm2->_elements[1]->_attr['onclick'] = "searchblok('".$_SESSION['lang']['find']." ".$_SESSION['lang']['blok']."','<div id=formPencariandata></div>',event)";
                
				/*
				if($libur==false){
					if($regional!='KALTIM')
						$theForm2->_elements[1]->_attr['onchange'] = "updTahunTanam();";  
					else 
						$theForm2->_elements[1]->_attr['onchange'] = "updTahunTanam2();";
                }
				else 
					$theForm2->_elements[1]->_attr['onchange'] = "updTahunTanam2();";  
				*/
                 //$theForm2->addEls('kodesegment',$_SESSION['lang']['kodesegment'],'','searchSegment','L',25);
				
                $theForm2->addEls('tahuntanam',$_SESSION['lang']['tahuntanam'],$thTanam,'textnum','R',6);
                $theForm2->_elements[2]->_attr['disabled'] = 'disabled';
				
				$theForm2->addEls('bjr',$_SESSION['lang']['bjr'],$bjr,'textnum','R',6);
                $theForm2->_elements[3]->_attr['disabled'] = 'disabled';
				
                $theForm2->addEls('norma',$_SESSION['lang']['basisjjg'],'0','textnum','R',10);
				$theForm2->_elements[4]->_attr['disabled'] = 'disabled';
                
				/*
				if($libur==false)
				{
					if($regional!='KALTIM')
						$theForm2->_elements[4]->_attr['disabled'] = 'disabled';
                }
				else 
					$theForm2->_elements[4]->_attr['disabled'] = 'disabled';
				*/
				$theForm2->_elements[4]->_attr['title'] = 'Basis diambil dari tabel berdasarkan BJR';
				$theForm2->addEls('outputminimal',$_SESSION['lang']['outputminimal'],'0','textnum','R',10);
				$theForm2->_elements[5]->_attr['disabled'] = 'disabled';
                /*
				if($libur==false)
				{
					if($regional!='KALTIM')
						$theForm2->_elements[5]->_attr['disabled'] = 'disabled';
                }
				else 
					$theForm2->_elements[5]->_attr['disabled'] = 'disabled';
				*/
				$theForm2->_elements[5]->_attr['title'] = 'Output minimal';
                
				$theForm2->addEls('hasilkerja',$_SESSION['lang']['hasilkerja'],'0','textnum','R',10);
                $theForm2->_elements[6]->_attr['onkeyup'] = "countPremi();";
                
                $theForm2->addEls('hasilkerjakg',$_SESSION['lang']['hasilkerjakg'],'0','textnum','R',10);
                $theForm2->_elements[7]->_attr['disabled'] = 'disabled';
                $theForm2->_elements[7]->_attr['title'] = 'Hasil Kerja (JJG) * BJR bulan lalu';
                
				$theForm2->addEls('upahkerja',$_SESSION['lang']['upahkerja'],$Umr[0]['nilai']/25,'textnum','R',10);
				$theForm2->_elements[8]->_attr['disabled'] = 'disabled';
				/*
				if($libur==false)
				{
					$theForm2->addEls('upahkerja',$_SESSION['lang']['upahkerja'],$Umr[0]['nilai']/25,'textnum','R',10);
					if($regional!='KALTIM')
						$theForm2->_elements[8]->_attr['disabled'] = 'disabled';
                } 
				else 
				{
					$theForm2->addEls('upahkerja',$_SESSION['lang']['upahkerja'],'0','textnum','R',10);
                    $theForm2->_elements[8]->_attr['disabled'] = 'disabled';
                }
				*/
                $theForm2->_elements[8]->_attr['title'] = 'Upah harian';
                
				$theForm2->addEls('luaspanen',$_SESSION['lang']['luaspanen'],'0','textnum','R',10);
				$theForm2->_elements[9]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('brondolan',$_SESSION['lang']['brondolan'],'0','textnum','R',10);
                $theForm2->_elements[10]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('upahpremi',$_SESSION['lang']['premibasis']." (Rp)",'0','textnum','R',10);
                $theForm2->_elements[11]->_attr['disabled'] = 'disabled';
				$theForm2->_elements[11]->_attr['title'] = 'Hasil Kerja > Basis * Premi Lebih Basis';
				/*
				if($libur==false)
				{
					if($regional!='KALTIM')
						$theForm2->_elements[11]->_attr['disabled'] = 'disabled';
                }
				else 
					$theForm2->_elements[11]->_attr['disabled'] = 'disabled';
				$theForm2->_elements[11]->_attr['title'] = 'Hasil Kerja > Basis * Premi Lebih Basis';
				*/
				
				
				
				$theForm2->addEls('upahpremilebihbasis',$_SESSION['lang']['premlebihbasis']." (Rp)",'0','textnum','R',10);
                $theForm2->_elements[12]->_attr['disabled'] = 'disabled';
				$theForm2->_elements[12]->_attr['title'] = 'Hasil Kerja > Basis * Premi Lebih Basis';
				
				/*
				if($libur==false)
				{
					if($regional!='KALTIM')
						$theForm2->_elements[12]->_attr['disabled'] = 'disabled';
                }
				else 
					
					$theForm2->_elements[12]->_attr['disabled'] = 'disabled';
				$theForm2->_elements[12]->_attr['title'] = 'Hasil Kerja > Basis * Premi Lebih Basis';
				*/
				
                $theForm2->addEls('upahpenalty',$_SESSION['lang']['upahpenalty'],'0','textnum','R',10);
                $theForm2->_elements[13]->_attr['disabled'] = 'disabled';
                $theForm2->_elements[13]->_attr['title'] = 'Denda upah harian';
				/*
				if($libur==false)
				{
					if($regional!='KALTIM')
						$theForm2->_elements[13]->_attr['disabled'] = 'disabled';
                }
				else 
					$theForm2->_elements[13]->_attr['disabled'] = 'disabled';
                $theForm2->_elements[13]->_attr['title'] = 'Denda upah harian';
				*/
				
                $theForm2->addEls('penalti1',$_SESSION['lang']['penalty1'],'0','textnum','R',10);
                $theForm2->_elements[14]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti2',$_SESSION['lang']['penalty2'],'0','textnum','R',10);
                $theForm2->_elements[15]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti3',$_SESSION['lang']['penalty3'],'0','textnum','R',10);
                $theForm2->_elements[16]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti4',$_SESSION['lang']['penalty4'],'0','textnum','R',10);
                $theForm2->_elements[17]->_attr['onkeyup'] = "countPremi()";
                //$theForm2->_elements[17]->_attr['maxlength'] = "1";
                //$theForm2->_elements[17]->_attr['onblur'] = "checkval('".$_SESSION['lang']['penalty4']."',this)";
                
				$theForm2->addEls('penalti5',$_SESSION['lang']['penalty5'],'0','textnum','R',10);
                $theForm2->_elements[18]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti6',$_SESSION['lang']['penalty6'],'0','textnum','R',10);
                $theForm2->_elements[19]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti7',$_SESSION['lang']['penalty7'],'0','textnum','R',10);
                $theForm2->_elements[20]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti8',$_SESSION['lang']['penalty8'],'0','textnum','R',10);
                $theForm2->_elements[21]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti9',$_SESSION['lang']['penalty9'],'0','textnum','R',10);
                $theForm2->_elements[22]->_attr['onkeyup'] = "countPremi()";
                
				$theForm2->addEls('penalti10',$_SESSION['lang']['penalty10'],'0','textnum','R',10);
                $theForm2->_elements[23]->_attr['onkeyup'] = "countPremi()";
				
				$theForm2->addEls('penalti11',$_SESSION['lang']['penalty11'],'0','textnum','R',10);
                $theForm2->_elements[24]->_attr['onkeyup'] = "countPremi()";
				//$theForm2->_elements[24]->_attr['maxlength'] = "1";
               // $theForm2->_elements[24]->_attr['onblur'] = "checkval('".$_SESSION['lang']['penalty11']."',this)";
				
				$theForm2->addEls('penalti12',$_SESSION['lang']['penalty12'],'0','textnum','R',10);
                $theForm2->_elements[25]->_attr['onkeyup'] = "countPremi()";
				//$theForm2->_elements[25]->_attr['maxlength'] = "1";
               // $theForm2->_elements[25]->_attr['onblur'] = "checkval('".$_SESSION['lang']['penalty12']."',this)";
				
				$theForm2->addEls('penalti13',$_SESSION['lang']['penalty13'],'0','textnum','R',10);
                $theForm2->_elements[26]->_attr['onkeyup'] = "countPremi()";
				//$theForm2->_elements[26]->_attr['maxlength'] = "1";
                //$theForm2->_elements[26]->_attr['onblur'] = "checkval('".$_SESSION['lang']['penalty13']."',this)";
                
				$theForm2->addEls('rupiahpenalty',$_SESSION['lang']['rupiahpenalty'],'0','textnum','R',10);
                $theForm2->_elements[27]->_attr['disabled'] = 'disabled';
                $theForm2->_elements[27]->_attr['title'] = 'Rupiah Penalty';
                
                # Table
                $theTable2 = new uTable('prestasiTable','Tabel Prestasi',$cols,$data,$dataShow);
				
				# FormTable
                $formTab2 = new uFormTable('ftPrestasi',$theForm2,$theTable2,null,array('notransaksi','tanggal'));
                $formTab2->_target = "kebun_slave_panen_detail";
                $formTab2->_noClearField = '##kodeorg##tahuntanam';
				$formTab2->_afterEditMode = "checkpt";
				
				$formTab2->_noEnable = '##tahuntanam##norma##outputminimal##hasilkerjakg##upahkerja##upahpenalty##upahpremi##premibasis##rupiahpenalty##jjgpenalty##kodesegment';
                $formTab2->_defValue = '##upahkerja='.$Umr[0]['nilai']/25;
				
				/*
                if($libur==false){
                        if($regional!='KALTIM')$formTab2->_noEnable = '##tahuntanam##norma##outputminimal##hasilkerjakg##upahkerja##upahpenalty##upahpremi##premibasis##rupiahpenalty##jjgpenalty##kodesegment';
                        else $formTab2->_noEnable = '##tahuntanam##rupiahpenalty##jjgpenalty##kodesegment';
                        $formTab2->_defValue = '##upahkerja='.$Umr[0]['nilai']/25;
                }else $formTab2->_noEnable = '##tahuntanam##outputminimal##hasilkerjakg##upahkerja##upahpenalty##premibasis##rupiahpenalty##jjgpenalty##kodesegment';
                */
				
				$formTab2->_defValue = '##upahkerja=0##kodesegment=';
                $formTab2->_afterCrud = "showDetail";
                $formTab2->_numberFormat = '##upahkerja##upahpremi##premibasis##upahpremilebihbasis##bjr##hasilkerjakg##upahpenalty##rupiahpenalty';

                // List Karyawan
                $listKary = array();
                foreach($data as $row) {
                        $listKary[$row['nik']] = $row['nik'];
                }

                // Cek Transaksi Tanggal sama
                $qPres = selectQuery($dbname,'kebun_prestasi_vw','COUNT(karyawanid) AS jumlah,karyawanid',
                                                         "karyawanid in ('".implode("','",$listKary)."') and
                                                         tanggal='".tanggalsystem($param['tanggal'])."'").' group by karyawanid';
                $resPres = fetchData($qPres);

                // Jumlah Transaksi Panen per Karyawan
                $karyTrans = array();
                foreach($resPres as $row) {
                        if($row['jumlah']>1)
                                $karyTrans[] = $row['karyawanid'];
                }

                #== Display View
				
				$lokasi=$_SESSION['empl']['lokasitugas'];
				$optDivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
				$sDiv="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$lokasi."'"; 
				
				$res=$owlPDO->query($sDiv) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bDiv=$res->fetch())
				{
						$optDivisi .= "<option value=".$bDiv->kodeorganisasi.">".$bDiv->namaorganisasi."</option>";
				}
				
                # Draw Tab
                echo "<input id=listKary type='hidden' value='".json_encode($karyTrans)."'>";
                echo "<fieldset><legend><b>Detail</b></legend>";
                echo "<param id='denda' value='{}'>";
                echo "<table>
					<tr>
						<td>Divisi</td>
						<td>:</td>
						<td>";
						
					echo "		<select id=divisi onchange=getDivisiPanen('ftPrestasi_nik','ftPrestasi_kodeorg',this)>".$optDivisi."</select>
						</td>
						<td>
							<input type=checkbox id=filterpt onclick=filterPT('ftPrestasi_nik',this) title='Per PT'>Filter Kary Per PT</checkbox>
							<input type=checkbox id=filtermandor onclick=filterMandor('ftPrestasi_nik',this) title='Per Mandor'>Filter Kary Per Mandor</checkbox>
						</td>
					</tr>
				</table> 
				<br>
				";//exit("Error:AAA");
                   # echo "<button class=mybutton id=filternik onclick=filterKaryawan(val='null') title='Tampilkan Semua Karyawan'>Show All</button>";
                $formTab2->render();
               // exit("Error:AAAz");
				echo "</fieldset>";
                break;

    case 'add':
		// Cek absensi perawatan
		cekPrestasi($param);
		
	
//        if($tanggal<'20140201'){ // sebelum tanggal 1 FEB 2014
//
//        }else{
            // cek yang bisa panen berdasarkan taksasi
            $luastaksasi=0;
            $hktaksasi=0;
            $query = "SELECT *
                FROM ".$dbname.".`kebun_taksasi` a
                WHERE a.`tanggal` = '".substr($param['notransaksi'],0,8)."' and a.`blok` = '".$param['kodeorg']."'";
                $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
                $qDetail->setFetchMode(PDO::FETCH_ASSOC);       
            while($rDetail=$qDetail->fetch())
            {
                $luastaksasi=($rDetail['hasisa']+$rDetail['haesok']);
//                $hktaksasi=$rDetail['hkdigunakan'];
                $jjgmasak=$rDetail['jjgmasak'];
                $jjgoutput=$rDetail['hkdigunakan'];
            }

            @$hktaksasi=ceil($jjgmasak/$jjgoutput);

            $yangbisapanen=0;
            @$luasperhk=ceil($luastaksasi/$hktaksasi);
            if($luasperhk<=6){
                $yangbisapanen=$hktaksasi;            
            }else{
                $yangbisapanen=$luasperhk;
            }

            // cek hk panen 
            $hkpanen=0;
            $query = "SELECT count(*) as hkpanen
                FROM ".$dbname.".`kebun_prestasi_vw`
                WHERE `tanggal` = '".substr($param['notransaksi'],0,8)."' and `kodeorg` like '".$param['kodeorg']."'";
                $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
                $qDetail->setFetchMode(PDO::FETCH_ASSOC);   
            while($rDetail=$qDetail->fetch())
            {
                $hkpanen=$rDetail['hkpanen'];
            }          

        // cari hari
        $day = date('D', strtotime(substr($param['notransaksi'],0,8)));
        if($day=='Sun')$libur=true; else $libur=false;
        // kamus hari libur
        $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".substr($param['notransaksi'],0,8)."'  and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
        $strorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
        $strorg->setFetchMode(PDO::FETCH_ASSOC);        
        while($roworg=$strorg->fetch())
        {
//            $libur=true;
            if($roworg['keterangan']=='libur')$libur=true;
            if($roworg['keterangan']=='masuk')$libur=false;
        }        

                $cols = array(
                        'nik','kodeorg','tahuntanam','bjr','norma','outputminimal','hasilkerja','hasilkerjakg',
                        'upahkerja','luaspanen','brondolan','upahpremi','upahpremilebihbasis','upahpenalty',
                        'penalti1','penalti2','penalti3','penalti4','penalti5','penalti6','penalti7','penalti8','penalti9','penalti10','penalti11','penalti12','penalti13',
                        'rupiahpenalty','notransaksi','kodekegiatan','statusblok','pekerjaanpremi'
                );
                $data = $param;

                unset($data['numRow']);
                # Additional Default Data
                $data['kodekegiatan'] = '0';
                $data['statusblok'] = 0;$data['pekerjaanpremi'] = 0;
				
        if($data['kodeorg']=='0'){
            $warning="Blok";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }
		
		if($data['hasilkerja']==0){
            $warning="Hasil Kerja (Jjg)";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }
		
		if($data['luaspanen']==0){
            $warning="Luas Panen(Ha)";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }
		
		if($data['bjr']==0 || $data['bjr']==''){
            $warning="BJR melalui Kebun - Setup - BJR";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }
		
		//cek basis
		if($data['norma']==0){
            echo "error: Basis Masih kosong";
            exit();
        }
		
        # periksa luas panen hari ini apakah sudah melebihi setup blok
        // cari luas blok
        $query = "SELECT luasareaproduktif
            FROM ".$dbname.".`setup_blok`
            WHERE `kodeorg` = '".$param['kodeorg']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        {
            $luasbloknya=$rDetail['luasareaproduktif'];
        }
		
        // cari tanggal
        $query = "SELECT distinct tanggal
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `notransaksi` = '".$param['notransaksi']."'";
        $tanggalnya = '';
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        {            
            $tanggalnya=$rDetail['tanggal'];
        }
		
		if($tanggalnya==''){
			$tanggalnya= tanggalsystemn($param['tanggal']);
		}
		
        // cari luas panen yang sudah diinput ditambah inputan
        $query = "SELECT sum(luaspanen) as luaspanen
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."'  and karyawanid!='".$param['nik']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        {   
            $luaspanennya=$rDetail['luaspanen'];
        }
        $luaspanennya+=$data['luaspanen'];

        if($luaspanennya>$luasbloknya){
            $warning="Luas Panen ".$luaspanennya." melebihi Luas Blok ".$luasbloknya." (Ha)";
            echo " error: ".$warning.".";
            exit();               
        }
        unset($data['tanggal']);
		
		$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }        
 
        unset($data['notransaksi']);unset($data['kodekegiatan']);
        unset($data['statusblok']);
        unset($data['pekerjaanpremi']);

        $res = "";
        foreach($data as $cont) {
            $res .= "##".$cont;
        }

        $result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
        echo $result;
		/*
        if($libur==false){
            if($regional!='KALTIM'){

                // cek janjang taksasi
                $jjgmasak=0;
                $query = "SELECT *
                    FROM ".$dbname.".`kebun_taksasi` a
                    WHERE a.`tanggal` = '".$tanggalnya."' and a.`blok` = '".$param['kodeorg']."'";
                $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
                $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
                while($rDetail=$qDetail->fetch())
                { 
                    $jjgmasak=$rDetail['jjgmasak'];
                }

                // cek janjang panen
                $hasilkerja=0;
                $query = "SELECT sum(hasilkerja) as hasilkerja
                    FROM ".$dbname.".`kebun_prestasi_vw`
                    WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."'";
                $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
                $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
                while($rDetail=$qDetail->fetch())
                { 
                    $hasilkerja=$rDetail['hasilkerja'];
                }          

                $jjgmasak=$jjgmasak*1.1;

            }
                        
        }*/
		proporsiUpah($param);
        break;

    case 'edit':
	
	
                // Cek absensi perawatan
                cekPrestasi($param);

                $data = $param;

        // cek inputan luas
        if($data['luaspanen']==0){
            $warning="Luas Panen(Ha)";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }
		
		//cek basis
		if($data['norma']==0){
            echo "error: Basis Masih kosong";
            exit();
        }
		
		// cek BJR
        if($data['bjr']==0 || $data['bjr']==''){
            $warning="BJR melalui Kebun - Setup - BJR";
            echo "error: Silakan mengisi ".$warning.".";
            exit();
        }

        # periksa luas panen hari ini apakah sudah melebihi setup blok
        // cari luas blok
        $query = "SELECT luasareaproduktif
            FROM ".$dbname.".`setup_blok`
            WHERE `kodeorg` = '".$param['kodeorg']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        { 
            $luasbloknya=$rDetail['luasareaproduktif'];
        }          

        // cari tanggal
        $query = "SELECT distinct tanggal
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `notransaksi` = '".$param['notransaksi']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        { 
            $tanggalnya=$rDetail['tanggal'];
        }

        // cari luas panen yang sudah diinput ditambah inputan
        $query = "SELECT sum(luaspanen) as luaspanen
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."' and karyawanid!='".$param['nik']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        { 
            $luaspanennya=$rDetail['luaspanen'];
        }   
        $luaspanennya+=$data['luaspanen'];
        if($luaspanennya>$luasbloknya){
            $warning="Luas Panen ".$luaspanennya." melebihi Luas Blok ".$luasbloknya." (Ha)";
            echo "error: ".$warning.".";
            exit();               
        }else{

        }        

                unset($data['notransaksi']);
                foreach($data as $key=>$cont) {
                        if(substr($key,0,5)=='cond_') {
                        unset($data[$key]);
                        }
                }
                
    $data['outputminimal']=str_replace(",","",$data['outputminimal']);
    $data['hasilkerja']=str_replace(",","",$data['hasilkerja']);
    $data['hasilkerjakg']=str_replace(",","",$data['hasilkerjakg']);
    $data['upahkerja']=str_replace(",","",$data['upahkerja']);
    $data['luaspanen']=str_replace(",","",$data['luaspanen']);
    $data['brondolan']=str_replace(",","",$data['brondolan']);
    $data['upahpremi'] =str_replace(",","",$data['upahpremi']);
    $data['upahpenalty']=str_replace(",","",$data['upahpenalty']);
    $data['penalti1']=str_replace(",","",$data['penalti1']);
    $data['penalti2']=str_replace(",","",$data['penalti2']);
    $data['penalti3']=str_replace(",","",$data['penalti3']);
    $data['penalti4'] =str_replace(",","",$data['penalti4']);
    $data['penalti5'] =str_replace(",","",$data['penalti5']);
    $data['penalti6'] =str_replace(",","",$data['penalti6']);
    $data['penalti7'] =str_replace(",","",$data['penalti7']);
    $data['penalti8'] =str_replace(",","",$data['penalti8']);
    $data['penalti9'] =str_replace(",","",$data['penalti9']);
    $data['penalti10'] =str_replace(",","",$data['penalti10']);
    $data['penalti11'] =str_replace(",","",$data['penalti11']);
    $data['penalti12'] =str_replace(",","",$data['penalti12']);
    $data['penalti13'] =str_replace(",","",$data['penalti13']);
    $data['rupiahpenalty'] =str_replace(",","",$data['rupiahpenalty']);
    // $data['jjgpenalty']=str_replace(",","",$data['jjgpenalty']);
        
                unset($data['tanggal']);
                $where = "notransaksi='".$param['notransaksi']."' and nik='".$param['cond_nik'].
                        "' and kodeorg='".$param['cond_kodeorg']."'";
                $query = updateQuery($dbname,'kebun_prestasi',$data,$where);
				// exit("error : ".$query);
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }

        // cari hari
        $day = date('D', strtotime($tanggalnya));
        if($day=='Sun')$libur=true; else $libur=false;
        // kamus hari libur
        $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggalnya."'  and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
        $queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
        $queorg->setFetchMode(PDO::FETCH_ASSOC);           
        while($roworg=$queorg->fetch())
        {
            if($roworg['keterangan']=='libur')$libur=true;
            if($roworg['keterangan']=='masuk')$libur=false;
        }        

                echo json_encode($param);
			
			/*
			if($libur==false){
            if($regional!='KALTIM'){
                
                // cek janjang taksasi
                $jjgmasak=0;
                $query = "SELECT *
                    FROM ".$dbname.".`kebun_taksasi` a
                    WHERE a.`tanggal` = '".$tanggalnya."' and a.`blok` = '".$param['kodeorg']."'";
                $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
                $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
                while($rDetail=$qDetail->fetch())
                {
                    $jjgmasak=$rDetail['jjgmasak'];
                }

                // cek janjang panen
                $hasilkerja=0;
                $query = "SELECT sum(hasilkerja) as hasilkerja
                    FROM ".$dbname.".`kebun_prestasi_vw`
                    WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['kodeorg']."'";
                $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
                $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
                while($rDetail=$qDetail->fetch())
                {
                    $hasilkerja=$rDetail['hasilkerja'];
                }          

                $jjgmasak=$jjgmasak*1.1;
           
            }
			proporsiUpah($param);
        }*/
		proporsiUpah($param);
	break;

    case 'delete':
                $where = "notransaksi='".$param['notransaksi']."' and nik='".$param['nik'].
                        "' and kodeorg='".$param['kodeorg']."'";
                $query = "delete from `".$dbname."`.`kebun_prestasi` where ".$where;
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
                proporsiUpah($param);
                break;

    case 'updTahunTanam':
            ##buat cek apakah ada di rekappnn
            $jumlah='0';
            $str="select count(*) as jumlah from ".$dbname.".kebun_rekappnn_vw where "
            . " blok='".$param['kodeorg']."' and tanggal='".tanggalsystem($param['tanggal'])."' and posting=1 ";
                $qDetail=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $qDetail->setFetchMode(PDO::FETCH_ASSOC);
                while($rDetail=$qDetail->fetch())
                {
                    $jumlah=$rDetail['jumlah'];  
                }
               
                if($jumlah=='0')
                {
                    $a="x";
                }
                else 
                {
                    $a="y";
                }

                
                $query = selectQuery($dbname,'setup_blok','kodeorg,tahuntanam',
                    "kodeorg='".$param['kodeorg']."'");
                $res = fetchData($query);
                if(!empty($res)) {
                    $b=$res[0]['tahuntanam'];
                } else {
                    $b='0';
                }
				
				$tgl = explode('-',$param['tanggal']);
				
				if($tgl[1]==11 || $tgl[1]==12 || $tgl[1]==1 ){
					if($tgl[1]==1){
						$tgl = ($tgl[2]-1)."-11";
					}else{
						$tgl = $tgl[2]."-11";
					}
				} else if($tgl[1]==2 || $tgl[1]==3 || $tgl[1]==4 ){
					if($tgl[1]==1){
						$tgl = ($tgl[2]-1)."-02";
					}else{
						$tgl = $tgl[2]."-02";
					}
				} else if($tgl[1]==5 || $tgl[1]==6 || $tgl[1]==7 ){
					if($tgl[1]==1){
						$tgl = ($tgl[2]-1)."-05";
					}else{
						$tgl = $tgl[2]."-05";
					}
				} else if($tgl[1]==8 || $tgl[1]==9 || $tgl[1]==10 ){
					if($tgl[1]==1){
						$tgl = ($tgl[2]-1)."-08";
					}else{
						$tgl = $tgl[2]."-08";
					}
				}
				
				
				
				//$tgl = explode('-',$param['tanggal']);
				$tgl = explode('-',$tgl);
				
				if($tgl[1]==1){
					$tglbjr = ($tgl[0]-1)."-12";
				}else{
					$tglbjr = $tgl[0]."-".addZero(($tgl[1]-1),2);
				}
				
				$tgl = explode('-',$tglbjr);
				if($tgl[1]==1){
					$tglbjr1 = ($tgl[0]-1)."-12";
				}else{
					$tglbjr1 = $tgl[0]."-".addZero(($tgl[1]-1),2);
				}
				
				$tgl = explode('-',$tglbjr1);
				if($tgl[1]==1){
					$tglbjr2 = ($tgl[0]-1)."-12";
				}else{
					$tglbjr2 = $tgl[0]."-".addZero(($tgl[1]-1),2);
				}
				/*
				$jumlah = 0;
				$str = "select sum(kgwb) as kgwb, sum(jjg) as jjg from ".$dbname.".kebun_spb_vw where blok='".$param['kodeorg']."' and (tanggal like '".$tglbjr."%' or tanggal like '".$tglbjr1."%' or tanggal like '".$tglbjr2."%')";
				
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while($bar=$res->fetch())
				{
                    $jumlah = @($bar['kgwb']/$bar['jjg']);  
                }
				
				if(empty($jumlah))
				{
					$str = "select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$param['kodeorg']."' and tahunproduksi = '".$tgl[2]."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch())
					{
						$valuebjr = $bar['bjr'];  
					}
				}
				else
				{
					$valuebjr = $jumlah;
				}
				*/
				
                #BJR diambil dari setup BJR
				$str = "select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$param['kodeorg']."' and periode = '".$tglbjr2."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch())
					{
						$valuebjr = $bar['bjr'];  
					}
				// if($valuebjr==''||$valuebjr=='0'){
					// exit("Error: BJR tidak ditemukan, silahkan isi BJR melalui Menu Kebun - Setup - BJR");
				// }
				
                echo $a.'##'.$b.'##'.$valuebjr;
                
                break;

    case 'updBjr':

        // KALO ADA UPDATE DI SINI, UPDATE JUGA YANG ADA DI KEBUN_SLAVE_TAKSASI: getSPH
        $tahuntahuntahun=substr($param['notransaksi'],0,4);
        $bulanbulanbulan=substr($param['notransaksi'],4,2); 
        $firstKary = $param['nik'];
        $tanggal=$param['tanggal'];
        $tanggal=tanggalsystem($tanggal);

        $hari=date('l', strtotime($tanggal)); 

        if($bulanbulanbulan=='01'){
            $bulanbulanbulan='12';
            $tahuntahuntahun-=1;
        }else{
            $bulanbulanbulan-=1;
            if(strlen($bulanbulanbulan)==1)$bulanbulanbulan='0'.$bulanbulanbulan;
        }

        $janjangjanjangjanjang=$param['hasilkerja'];
        $luaspanen=$param['luaspanen'];
        $afdelingafdelingafdeling=substr($param['kodeorg'],0,6);  


        // ambil bjr budget
        $query = "SELECT a.kodeblok, a.thntnm, b.bjr
            FROM ".$dbname.".`bgt_blok` a
            LEFT JOIN ".$dbname.".bgt_bjr b ON a.tahunbudget = b.tahunbudget
                AND substr( a.kodeblok, 1, 4 ) = b.kodeorg
                AND a.thntnm = b.thntanam
            WHERE a.`tahunbudget` =".$tahuntahuntahun."
                AND a.`kodeblok` LIKE '".$param['kodeorg']."'";
                $res = fetchData($query);
                if(!empty($res)) {
                        $bjr=$res[0]['bjr'];
                }


        // cek bjr via SETUP
        $query = "SELECT *
            FROM ".$dbname.".`kebun_5bjr` a
            WHERE a.`tahunproduksi` = '".substr($param['notransaksi'],0,4)."' and a.`kodeorg` = '".$param['kodeorg']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        {        
            $bjr=$rDetail['bjr'];
        }            

        $basis=0;
        // ambil basis yang paling kecil
        $query = "SELECT bjr, afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' order by bjr asc limit 1";
                $res = fetchData($query);
                if(!empty($res)) {
                                $bjrpalingkecil=$res[0]['bjr'];
                }
        // ambil basis yang paling besar
        $query = "SELECT bjr, afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' order by bjr desc limit 1
            ";
                $res = fetchData($query);
                if(!empty($res)) {
                                $bjrpalingbesar=$res[0]['bjr'];          
                }

        $bjr2=$bjr;
        if($bjr<$bjrpalingkecil)$bjr2=$bjrpalingkecil;
        if($bjr>$bjrpalingbesar)$bjr2=$bjrpalingbesar;

        // ambil basis berdasarkan bjr + afdeling
        $query = "SELECT afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' and bjr = ".round($bjr2,2);
                $res = fetchData($query);
                if(!empty($res)) {
                                $basis=$res[0]['basis'];
                                $premibasis=$res[0]['premibasis'];            
                                $premilebihbasis=$res[0]['premilebihbasis'];            
                }

        // kalo hari jumat basisnya 5/7
        if($hari=='Friday'){
            @$basis=5/7*$basis;
        }
        $basis=round($basis);

        // itung premi lebih basis
        $lebihbasis=$janjangjanjangjanjang-$basis;
        if($lebihbasis>0){
            $premilebihbasis=$lebihbasis*$premilebihbasis;            
        }else{
            $premilebihbasis=0;
        }

        //update upah penalty
                $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
                        "karyawanid=".$firstKary." and tahun=".substr($param['notransaksi'],0,4)." and idkomponen in (1,31)");
                $Umr = fetchData($qUMR);        
        $hasilkerja=$param['hasilkerja'];
        // cek yang bisa panen berdasarkan taksasi

        $luastaksasi=0;
        $hktaksasi=0;
        $jjgmasak=0;
        $akp=0;
        $query = "SELECT *
            FROM ".$dbname.".`kebun_taksasi` a
            WHERE a.`tanggal` = '".substr($param['notransaksi'],0,8)."' and a.`blok` = '".$param['kodeorg']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        { 
            $luastaksasi=($rDetail['hasisa']+$rDetail['haesok']);
//            $hktaksasi=$rDetail['hkdigunakan'];
            $jjgmasak=$rDetail['jjgmasak'];
            $jjgoutput=$rDetail['jjgoutput'];

            $akp=$rDetail['persenbuahmatang'];
        }

                $sorg="select kodeorg, jumlahpokok as pokokthnini, luasareaproduktif as hathnini from ".$dbname.".setup_blok where kodeorg ='".$param['kodeorg']."'";
                $qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
                $qorg->setFetchMode(PDO::FETCH_ASSOC);                     
                while($rorg=$qorg->fetch()){
                        $pokok=$rorg['pokokthnini'];
                        $luas=$rorg['hathnini'];
                }
                @$sph=round($pokok/$luas);        

        @$hktaksasi=ceil($jjgmasak/$jjgoutput);

                $yangbisapanen=0;
        @$luasperhk=ceil($luastaksasi/$hktaksasi);
        if($luasperhk<=6){
            $yangbisapanen=$hktaksasi;            
        }else{
            $yangbisapanen=$luasperhk;
        }       

        $upahharian=round($Umr[0]['nilai']/25);

        @$capaibasis=$hasilkerja/$basis;        
        if($tanggal<'20140201'){ // sebelum tanggal 1 FEB 2014
            @$batasproporsi=round(0.8*$basis);
            if(($capaibasis>=(0.8))or($luaspanen>=6)){ // luas lebih 6 ha lon dibuang
                $upahpenalty=0;
            }else{
                @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
                $upahpenalty=$upahharian-$upahpenalty;
            }            
        }else{ //setelah tanggal 1 FEB 2014
            if($luasperhk <= 6){
    //            if(($capaibasis>=(0.8))or($luaspanen>=6)){ // luas lebih 6 ha lon dibuang
                @$batasproporsi=round(0.8*$basis);
                if($capaibasis>=(0.8)){ // luas lebih 6 ha lon dibuang
                    $upahpenalty=0;
                }else{
                    @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
                    $upahpenalty=$upahharian-$upahpenalty;
                }
            }else{
                @$batasproporsi=round($sph*6*$akp/100);
                if($hasilkerja>=($batasproporsi)){ // luas lebih 6 ha dibuang
                    $upahpenalty=0;
                }else{
                    @$upahpenalty=round($Umr[0]['nilai']/25*($capaibasis));
//        echo "error: uh:".$upahharian." up".$upahpenalty." hk".$hasilkerja." bp".$batasproporsi." b".$basis; exit;
                    $upahpenalty=$upahharian-$upahpenalty;
                }
            }     

        }

        if($upahpenalty<0)$upahpenalty=0;

//        echo "error: ".$batasproporsi; exit;

        // itung premi basis (kalo 2x basis, dapet 2x... dst)
        @$kalibasis=floor($janjangjanjangjanjang/$basis);        
        $premibasis=$premibasis*$kalibasis;            

        $hasilkerjakg=round($bjr*$janjangjanjangjanjang,2);
        $hasilhasilhasil=$hasilkerjakg.'##'.$basis.'##'.$premibasis.'##'.$premilebihbasis.'##'.$upahpenalty.'##'.$upahharian.'##'.$batasproporsi;
        echo $hasilhasilhasil;
                break;

    case 'updBjr2': // if($regional=='KALTIM')
        $tahuntahuntahun=substr($param['notransaksi'],0,4);
        $hasilhasilhasil=$param['hasilkerja'];
                $query = selectQuery($dbname,'kebun_5bjr','kodeorg,bjr',
                        "kodeorg='".$param['kodeorg']."' and tahunproduksi = '".$tahuntahuntahun."'");
                $res = fetchData($query);
                if(!empty($res)) {
                                $hasilhasil=$hasilhasilhasil*$res[0]['bjr'];
                        echo $hasilhasil;
                } else {
                        echo '0';
                }
                break;

    case 'updBjr3': // khusus hari libur
        $tahuntahuntahun=substr($param['notransaksi'],0,4);
        $hasilhasilhasil=$param['hasilkerja'];
        $afdelingafdelingafdeling=substr($param['kodeorg'],0,6);  

                $query = selectQuery($dbname,'kebun_5bjr','kodeorg,bjr',
                        "kodeorg='".$param['kodeorg']."' and tahunproduksi = '".$tahuntahuntahun."'");
                $res = fetchData($query);
                if(!empty($res)) {
                                $bjr2=$res[0]['bjr'];
                                $hasil3=$hasilhasilhasil*$bjr2;
                } else {
                                $bjr2=0;
                        $hasil3=0;
                }

        // ambil basis berdasarkan bjr + afdeling
        $query = "SELECT afdeling, basis, premibasis, premilebihbasis
            FROM ".$dbname.".`kebun_5basispanen2`
            WHERE afdeling LIKE '".$afdelingafdelingafdeling."' and bjr = ".round($bjr2,2);
                $res = fetchData($query);
                if(!empty($res)) {
                                $basis=$res[0]['basis'];
                                $premibasis=$res[0]['premibasis'];            
                                $premilebihbasis=$res[0]['premilebihbasis'];            
                }
        $hasil33=$hasilhasilhasil*$premilebihbasis;

        // itung premi basis (kalo 2x basis, dapet 2x... dst)
        @$kalibasis=floor($hasilhasilhasil/$basis);        
        $premibasis=$premibasis*$kalibasis;                    

        echo $hasil3.'##'.$hasil33.'##'.$basis.'##'.$premibasis;
                break;

    case 'updUpah':
                $firstKary = $param['nik'];
                $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
                        "karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen ='1'");
                $Umr = fetchData($qUMR);
        $upahharian=$Umr[0]['nilai']/25;
        $luaspanen=$param['luaspanen'];
        $hasilkerja=$param['hasilkerja'];
        $basis=$param['basis'];
		
		if($upahharian==''){
			exit("Error : Gaji Pokok untuk tahun ".$param['tahun']." belum ada !");
		}
                // Get Region
                $qRegion = selectQuery($dbname,'bgt_regional_assignment','regional',
                                                           "kodeunit");
                $resRegion = fetchData($qRegion);

        // cek yang bisa panen berdasarkan taksasi
        $query = "SELECT *
            FROM ".$dbname.".`kebun_taksasi` a
            WHERE a.`tanggal` = '".tanggalsystem($param['tanggal'])."' and a.`blok` = '".$param['kodeorg']."'";
        $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        { 
            $luastaksasi=($rDetail['hasisa']+$rDetail['haesok']);
//            $hktaksasi=$rDetail['hk'];
            $jjgmasak=$rDetail['jjgmasak'];
            $jjgoutput=$rDetail['jjgoutput'];

            $akp=$rDetail['persenbuahmatang'];
        }

                $sorg="select kodeorg, jumlahpokok as pokokthnini, luasareaproduktif as hathnini from ".$dbname.".setup_blok where kodeorg ='".$param['kodeorg']."'";
                $qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
                $qorg->setFetchMode(PDO::FETCH_ASSOC);                  
                while($rorg=$qorg->fetch()){
                        $pokok=$rorg['pokokthnini'];
                        $luas=$rorg['hathnini'];
                }
                @$sph=round($pokok/$luas);          

        @$hktaksasi=ceil($jjgmasak/$jjgoutput);

        @$luasperhk=ceil($luastaksasi/$hktaksasi);
        if($luasperhk<=6){
            $yangbisapanen=$hktaksasi;            
        }else{
            $yangbisapanen=$luasperhk;
        }        

        @$capaibasis= ($basis - $hasilkerja)/$basis;
                $upahpenalty=0;
                if(!empty($resRegional) and $resRegional[0]['regional']=='PAPUA' and $capaibasis < 1)
                        $upahpenalty = $upahharian * $capaibasis;

                echo $upahharian.'##'.round($upahpenalty);
                break;

    case 'updUpah2': // if($regional=='KALTIM')
                $firstKary = $param['nik'];
                $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
                        "karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen in (1,31)");
                $Umr = fetchData($qUMR);
                echo $Umr[0]['nilai']/25;
                break;

        case 'countPremi':
			
			#lakukan pengecekan hari masuk / jumat / hari minggu dan libur
			#kode saja untuk filter hari, 0=hari kerja biasa , 1=hari libur, 2=hari jumat
			$tanggalx=tanggalsystemn($param['tanggal']);
			$day = date('D', strtotime($tanggalx));
			if($day=='Sun'){
				$libur=true;
				$kodesaja=1;
			}else if ($day=='Fri'){
				$kodesaja=2;
				$libur=false;
			} else{	
				$libur=false;
				$kodesaja=0;
			}
		
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggalx."' and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')  ";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur'){
					$libur=true;
					$kodesaja=1;
				}
				if($roworg['keterangan']=='masuk'){
					$libur=false;
					$kodesaja=0;
				}
			}
			
			// Get Areal Statement (Setup Blok)
			$qBlok = selectQuery($dbname,'setup_blok','*',"kodeorg='".$param['blok']."'");
			$resBlok = fetchData($qBlok);
			if(empty($resBlok))
				exit("Warning: Areal Statement blok ".$param['blok']." belum ada");
			$dataBlok = $resBlok[0];
			
			// Get Basis Panen dan Ketentuan Premi
			$whereBasis = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
					and bjrdari <= '".$param['bjr']."' and bjrsampai >= '".$param['bjr']."'";
			$qBasis = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis);
			$resBasis = fetchData($qBasis);
			if(empty($resBasis)) 
				exit("Warning: Basis Panen belum ada untuk\nPT ".
					$_SESSION['org']['kodeorganisasi']."");
			$rumusPremi = $resBasis[0];
			
			/*
			#penjelasan kembali pembentukan kodesaja
			kodesaja 0 = hari kerja
			kodesaja 1 = hari libur
			kodesaja 2 = hari jumat (perbedaan di premi basis saja)
			*/
			if($kodesaja==0){
				$basis = $rumusPremi['basis'];
				$premicapaibasis=$rumusPremi['premibasis'];
			}else if ($kodesaja==1){
				$basis = $rumusPremi['basislibur'];
				$premicapaibasis=$rumusPremi['premiliburcapaibasis'];
			}else if ($kodesaja==2){
				$basis = $rumusPremi['basislibur'];
				$premicapaibasis=$rumusPremi['premibasis'];
			}
			
			
			//Denda Upah
			//if($param['hasilkerja'] < $basis){
			$param['upahkerja']=str_replace(',','',$param['upahkerja']);
			if($param['hasilkerja'] < $basis or $param['luaspanen'] < $rumusPremi['luastopografi']){				
				// $dendaupah = $param['upahkerja'] - (@($param['hasilkerja']/$basis)*$param['upahkerja']);
			} else {
				// $dendaupah = 0;
			}
			
			$dendaupah = 0;
			
		//	exit("Error:".$param['hasilkerja']._.$basis._.$param['luaspanen']._.$rumusPremi['luastopografi']);
			
			//Premi basis dan lebih basis
			if($param['hasilkerja']==$basis){
				$premibasis = $premicapaibasis;
				$premilebihbasis = 0;
			} else if($param['hasilkerja'] < $basis){
				$premibasis = 0;
				$premilebihbasis = 0;
			} else if($param['hasilkerja'] > $basis) {
				$premi=0;
				#cek apakah masuk ke premi lb1
				$jjgpnnlb=$param['hasilkerja']-$basis;
				if($jjgpnnlb>100){
					$sisalb=$jjgpnnlb-100;
					$premilebihbasis=(100*$rumusPremi['premilebihbasis'])+($sisalb*$rumusPremi['premilebihbasis2']);
				}else if ($jjgpnnlb<=100){
					$premilebihbasis=$jjgpnnlb*$rumusPremi['premilebihbasis'];
				}
				$premibasis = $premicapaibasis;
			}
			
			// Get Denda
			$qDenda = selectQuery($dbname,'kebun_5dendapanen',"*","kodeorg='".substr($param['blok'],0,4)."'");
			$resDenda = fetchData($qDenda);
			$optDenda = array();
			foreach($resDenda as $row) 
			{
				$optDenda[$row['kodedenda']] = array(
					'jenis' => $row['jenisdenda'],
					'nilai' => $row['denda']
				);
			}
			
			/**
			 * [START] Perhitungan Denda & Premi
			 */
			// Init
			$premi = 0;
			$premilebih = 0;
			$denda = array(
				'jjg' => 0,
				'rp' => 0
			);
			
			if(is_array($param['penalti'])){
				foreach($param['penalti'] as $kode=>$val) {
					if(isset($optDenda[$kode])) {
						$denda['rp'] += $val * $optDenda[$kode]['nilai'];
						
					}
				}
			}
			
			if($denda['rp']<0){
				$denda['rp']=0;
			}
			
			/*
            * [END] Perhitungan Denda & Premi
            */
			

			
			$hasilkerjakg = $param['hasilkerja'] * $param['bjr'];
			$res = array(
				'basis' => $basis,
				'hasilkerjakg' => $hasilkerjakg,
				'dendaupah' => $dendaupah,
				'premibasis' => $premibasis,
				'premilebihbasis' => $premilebihbasis,
				'dendarp' => $denda['rp']
				// 'dendajjg' => $denda['jjg'],
				// 'premi' => $premi,
				// 'premilebih' => $premilebih,
				// 'basis' => $rumusPremi['basis'],
				// 'hari' => $jenisPremi,
				// 'upahpenalty' => $upahpenalty,
			);
			echo json_encode($res);
	break;
	
	case 'getDivisi':
		$tanggalx=substr($param['notransaksi'],0,4).'-'.substr($param['notransaksi'],4,2).'-'.substr($param['notransaksi'],6,2);
		$blokStatus = $_SESSION['tmp']['actStat'];
                if($blokStatus=='bibit')
                        {
                            $whereOrg = " tipe='BIBITAN' and length(kodeorganisasi)>6 and left(kodeorganisasi,4)='".$param['chKdOrg']."'";
                        }
                        else
                        {
                            $whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where "
                                    . "left(kodeorg,4)='".$param['chKdOrg']."' and luasareaproduktif>0 and statusblok='TM') "
                                    . "and tipe='BLOK' and left(kodeorganisasi,4)='".$param['chKdOrg']."'";
                }
		
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$whereOrg." and induk like '".$param['divisi']."%' order by namaorganisasi";
		
		$tab1 = "<select style=width:195px id='kodeorg' onchange='updTahunTanam()'>";
		$tab1 .= "<option value=''></option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $tab1 .= "<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
        }
        $tab1 .= "</select>";
		
		//Get Karyawan
		$str="select karyawanid,nik,subbagian,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan in (2,3,4,6) and subbagian LIKE '".$param['divisi']."%' and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tanggalx."')";
		$tab2 = "<select style=width:162.5px id='nik' onchange='updUpah()'>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
        {
			$tab2 .= "<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
        }
        $tab2 .= "</select>  <img id='nik_find' onclick=\"z.elSearch('nik',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;'>";
		
		echo $tab1."##".$tab2;
		break;
		
	case 'gatKarywanAFD':
			$nikmandor = $param['nikmandor'];
			
			$str = "select t1.karyawanid,t2.namakaryawan,t2.nik,t2.subbagian from ".$dbname.".kebun_5mandor t1
			left join ".$dbname.".datakaryawan t2 on t1.karyawanid = t2.karyawanid 
			where t1.mandorid='".$nikmandor."' order by t1.nourut";
			$optKary = "<select style=width:162.5px id='nik' onchange='updUpah()'>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar = $res->fetch()){
				$optKary .= "<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
			}
			$optKary .= "</select>
			<img id='nik_find' onclick=\"z.elSearch('nik',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;'>";
			
			echo $optKary;
		break;
		
		case 'gatKarywanPT':
			$str="select karyawanid,namakaryawan,nik,subbagian from ".$dbname.".datakaryawan where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN') and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")
                and tipekaryawan in('2','3','4','6')  order by namakaryawan";
			$optKary = "<select style=width:162.5px id='nik' onchange='updUpah()'>";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{				
				if($param['listKary']==$bar->karyawanid)
				{
					$optKary .= "<option value='".$bar->karyawanid."' selected>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
				}
				else
				{
					$optKary .= "<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
				}
			}
			$optKary .= "</select>
			<img id='nik_find' onclick=\"z.elSearch('nik',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;'>";
			echo $optKary;
		break;

    default:
                break;
}

function cekPrestasi($param) {
        global $dbname;
		global $owlPDO;

		$tgl=explode('/',$param['notransaksi']);
		$tgl=$tgl[0];
		
		
	
		// if($param['bjr']<1 || $param['norma']<1){
			// exit("Warning:Data BJR dan Basis masih 0");
		// }			
		
		
		#cek mandor
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		#cek mandor1
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		#cek kerani
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where keranimuat='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		
		#cek nikasisten
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikasisten='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		if($jumtrans>0){
			exit("Warning:Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani");
		}
		
		
		
		
        // Cek Panen hanya di 1 blok
        $qPnn = selectQuery($dbname,'kebun_prestasi_vw','karyawanid',
                                                "karyawanid='".$param['nik']."' and tanggal='".
                                                tanggalsystem($param['tanggal'])."' and notransaksi!='".$param['notransaksi']."'");

        $resPnn = fetchData($qPnn);
        if(!empty($resPnn)){
			exit("Warning:Pemanen dapat memanen diblok berbeda hanya dalam 1 nomor bkm");
		}
			//exit("Warning: Pemanen hanya dapat terdaftar di 1 kali dalam hari yang sama");

        // Cek Perawatan
        // Jika sudah ada di perawatan tidak bisa input panen
        // Jika karyawan ada pekerjaan panen dan perawatan, maka harus malekukan input panen terlebih dahulu
        $qAbs = selectQuery($dbname,'kebun_kehadiran_vw','karyawanid,sum(jhk) as jhk, sum(umr) as umr',
                                                "karyawanid='".$param['nik']."' and tanggal='".tanggalsystem($param['tanggal'])."'");
        $resAbs = fetchData($qAbs);
		$jhkrawat = $resAbs[0]['jhk'];
		$umrrawat = $resAbs[0]['umr'];
		
		if(intval($jhkrawat)!='0' || intval($umrrawat)!='0') {
                exit("Warning: Karyawan sudah terdaftar di kegiatan perawatan");
        }
		
		#cek di vhc - kegiatan traksi
		$qAbs = selectQuery($dbname,'vhc_runhk','sum(upah) as jhk',
				"idkaryawan='".$param['nik']."' and tanggal='".tanggalsystem($param['tanggal'])."'");
        $resAbs = fetchData($qAbs);
		$jmlhkvhc = $resAbs[0]['jhk'];
		
		if(intval($jmlhkvhc)!='0') {
                exit("Warning: Karyawan sudah terdaftar di kegiatan traksi");
        }
		
        // if(!empty($resAbs)) {
                // exit("Warning: Karyawan sudah terdaftar di kegiatan perawatan");
        // }
}







function proporsiUpah($param) {
        global $dbname;
        global $conn;
        global $owlPDO;
		
		
        // Get Tahun
		$tmpTgl = explode('-',$param['tanggal']);
        $tahun = $tmpTgl[2];

        // Get UMR
        $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
                "karyawanid=".$param['nik']." and tahun=".$tahun." and idkomponen in (1)");
        $Umr = fetchData($qUMR);
        $upahharian=round($Umr[0]['nilai']/25);
		
		
		#bentuk data
		$str="select sum(luaspanen) as luaspanen,sum(hasilkerja) as hasilkerja,count(*) as jumblok 
				from ".$dbname.".kebun_prestasi_vw where karyawanid='".$param['nik']."' 
				and tanggal='".tanggalsystem($param['tanggal'])."' ";
			
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tluas=$bar['luaspanen'];
			$tjjg=$bar['hasilkerja'];
			$jumblok=$bar['jumblok'];
			@$upahpro=$upahharian/$jumblok;
			
		#ambil Premi yg tersimpan
		$strx="select * from ".$dbname.".kebun_prestasi_vw where karyawanid='".$param['nik']."' 
				and tanggal='".tanggalsystem($param['tanggal'])."' and kodeorg!='".$param['kodeorg']."'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($barx=$resx->fetch()){
			$premibasislama+=$barx['upahpremi'];
			$premilebihbasislama+=$barx['upahpremilebihbasis'];
			$dendalama+=$barx['rupiahpenalty'];
		}	
		$totalpremilama=($premibasislama+$premilebihbasislama)-$dendalama;
		
		$temppensenpenc=$no=$templb=$tempmasuklbdua=0;
		
		$tempbasisbaru=0;
		$counterlebihbasis=0;
		$str="select * from ".$dbname.".kebun_prestasi_vw where karyawanid='".$param['nik']."' 
				and tanggal='".tanggalsystem($param['tanggal'])."' "; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			$tanggalx=tanggalsystemn($param['tanggal']);
			$day = date('D', strtotime($tanggalx));			
			if($day=='Sun'){
				$libur=true;
				$kodesaja=1;
			}else if ($day=='Fri'){
				$kodesaja=2;
				$libur=false;
			} else{	
				$libur=false;
				$kodesaja=0;
			}
		
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggalx."' and (kebun='GLOBAL' or kebun='".$bar['kodeorg']."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur'){
					$libur=true;
					$kodesaja=1;
				}
				if($roworg['keterangan']=='masuk'){
					$libur=false;
					$kodesaja=0;
				}
			} 
			
		
			$qBlok = selectQuery($dbname,'setup_blok','*',"kodeorg='".$bar['kodeorg']."'");
			$resBlok = fetchData($qBlok);
			if(empty($resBlok))
				exit("Warning: Areal Statement blok ".$bar['kodeorg']." belum ada");
			$dataBlok = $resBlok[0];

			// Get Basis Panen dan Ketentuan Premi
			$whereBasis = "afdeling='".$_SESSION['org']['kodeorganisasi']."'
					and bjrdari <= '".$bar['bjr']."' and bjrsampai >= '".$bar['bjr']."'";
			$qBasis = selectQuery($dbname,'kebun_5basispanen2',"*",$whereBasis);
			$resBasis = fetchData($qBasis);
			if(empty($resBasis)) 
				exit("Warning: Basis Panen belum ada untuk\nPT ".
					$_SESSION['org']['kodeorganisasi']."");
						
					
			$rumusPremi = $resBasis[0];
			/*
				kodesaja 0 = hari kerja
				kodesaja 1 = hari libur
				kodesaja 2 = hari jumat (perbedaan di premi basis saja)
			*/
			if($kodesaja==0){
				$premicapaibasis=$rumusPremi['premibasis'];
			}else if ($kodesaja==1){
				$premicapaibasis=$rumusPremi['premiliburcapaibasis'];
			}else if ($kodesaja==2){
				$premicapaibasis=$rumusPremi['premibasis'];
			}
			
			
			
			
			//tidak capai basis
			
			
			#bentuk proporsi dulu
			$luaspnn=$bar['luaspanen'];
			$jjgpnn=$bar['hasilkerja'];
			$projjg=$jjgpnn/$tjjg;
			$basisbaru=$projjg*$bar['norma'];
			$lebihbasisbaru=$bar['hasilkerja']-$basisbaru;
			
			if($jjgpnn < $basisbaru){
				// $dendaupah = $upahpro - (@($jjgpnn/$basisbaru)*$upahpro);
			}else{
				// $dendaupah = 0;
			}
			$dendaupah = 0;
			
			#baru
			
			
			$no++;
			$basislama=$bar['norma'];
			$jjgpnn=$bar['hasilkerja'];
		
			
			//$pensenpenc=number_format($jjgpnn/$basislama,2)*100;
			$pensenpenc=$jjgpnn/$basislama*100;
			$tempbasisbaru+=$pensenpenc;
			if($pensenpenc>100){
				$basisbaru=$basislama;
			}else{
				if($temppensenpenc>100){
					$persenbasisbaru=0;
				}else{
					if($no==1){
						$persenbasisbaru=$pensenpenc;
					}else{
						$persenbasisbaru=100-$temppensenpenc;
					}
				}
			
				
				if($tempbasisbaru>100){
					$basisbaru=$persenbasisbaru/100*$jjgpnn;
				}else{
					$basisbaru=$jjgpnn;
				}
			}
			
			
			// if($no==1){
				// exit("Error:".$persenbasisbaru);
			// }
		
			$lebihbasis=$jjgpnn-$basisbaru;
			//$tempjjglama=$tempjjg;
			
			
			
			if($lebihbasis>=0){
				if($counterlebihbasis==0){
					if($lebihbasis>100){
						$lebihsatu=100;
						$lebihdua=$lebihbasis-$lebihsatu;
						@$counterdua+=1;
					}else{				
						$tempsisasatu=100-$lebihbasis;
						$lebihsatu=$lebihbasis;
						@$countersatu+=1;
						if($jjgpnn>$basislama){
							$tempsisasatu=$lebihbasis;
						}
					}
					@$tempjjg+=$lebihbasis;
				}else{#buat ke 2 dst
					if($countersatu>0){
						if($jjgpnn>$basislama){
							$lebihsatu=$tempsisasatu;
							$lebihdua=$lebihbasis-$lebihsatu;
						}else{
							$lebihsatu=$tempsisasatu;
							$lebihdua=$lebihbasis-$lebihsatu;
							if($lebihdua<0){
								$lebihdua=0;
								$lebihsatu=0;
							}
						}
					}
					if($counterdua>0){
						$lebihsatu=0;
						$lebihdua=$lebihbasis;
						
					}
				}
				$counterlebihbasis++;	
			}
			
			// if($no==3){
			// exit("Error".$lebihsatu._.$lebihdua);
			// }
			if($counterlebihbasis==1){ 
				$premibasis = $premicapaibasis;	
			}else{
				$premibasis=0;
			}
			
			$premisatu=$lebihsatu*$rumusPremi['premilebihbasis'];
			$premidua=$lebihdua*$rumusPremi['premilebihbasis2'];
			$premilebihbasis=$premisatu+$premidua;
			
			
			$rupiahpenalty=$bar['rupiahpenalty'];
			$totpremi=$premibasis+$premilebihbasis;
			
			
			
			
			if($totalpremilama<0){
				$rupiahpenalty=$totpremi;
		
			}else if($rupiahpenalty>($totpremi+$totalpremilama)){
				$rupiahpenalty=($totpremi+$totalpremilama);
			}
			
			#query update yang baru //upahpenalty
			$strupd=" update ".$dbname.".kebun_prestasi set upahkerja='".$upahpro."',rupiahpenalty='".$rupiahpenalty."',
					upahpremi='".$premibasis."',upahpremilebihbasis='".$premilebihbasis."',upahpenalty='".$dendaupah."'
					where notransaksi='".$bar['notransaksi']."' and nik='".$bar['karyawanid']."' 
					and kodeorg='".$bar['kodeorg']."' and kodesegment='".$bar['kodesegment']."' ";
			try{
				$owlPDO->exec($strupd);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
			
		$temppensenpenc+=$pensenpenc;
		//$templebihdua+=$templebihdua;
			
		}
		   
}


















function proporsiUpahlama($param) {
        global $dbname;
        global $conn;
        global $owlPDO;
        // Get Tahun
		
		//print_r($param);exit("Error:A");
		
        $tmpTgl = explode('-',$param['tanggal']);
        $tahun = $tmpTgl[2];

        // Get UMR
        $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
                "karyawanid=".$param['nik']." and tahun=".$tahun." and idkomponen in (1,31)");
        $Umr = fetchData($qUMR);
        $upahharian=round($Umr[0]['nilai']/25);

        // Get Data Panen
        $qPres = selectQuery($dbname,'kebun_prestasi_vw','*',
                                                 "karyawanid='".$param['nik']."' and tanggal='".tanggalsystem($param['tanggal'])."'");
        $resPres = fetchData($qPres);
		//exit("Error:$qPres");
		
		
        // Proses hanya jika masih ada data
        if(!empty($resPres)) {
                // Upah Per Blok
                $upahPerBlok = $upahharian / count($resPres);

                // Update Data Upah
                $dataUpd = array('upahkerja' => $upahPerBlok);

                // Iterasi per transaksi
                foreach($resPres as $row) {
                        $qUpd = updateQuery($dbname,'kebun_prestasi',$dataUpd,
                                "nik='".$row['karyawanid']."' and
                                notransaksi='".$row['notransaksi']."' and
                                kodeorg='".$row['kodeorg']."' and
                                kodesegment='".$row['kodesegment']."'");
                    try{$owlPDO->exec($qUpd); }catch (PDOException $e) {print "Proporsi Error !: " . $e->getMessage() . "\n"; die(); }    
                }
        }
}