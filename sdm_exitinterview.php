<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/sdm_exitinterview.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src=js/zTools.js></script>
<?php

// make option untuk menampilkan nama pilihannya di form
$where = "`tipe`='HOLDING' and length(kodeorganisasi)=3";

$optCurr = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');


$optPemilik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


$optOrgpemilik=array('GLOBAL'=>'GLOBAL');
foreach ($optOrgpemilik as $key) {
 @$optPemilik.="<option value= '".@$key."'>".strtoupper(@$_SESSION['lang'][strtolower($key)]). "</option>";
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//fetch obj untuk dijadikan object
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
   $optPemilik.= "<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

$optpengganti="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan,nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optpengganti.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=3 and tipe='HOLDING' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//fetch obj untuk dijadikan object
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
   $optholding.= "<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_exitinterview').'</span></br>');

echo"<fieldset style='width:700px;'>";
        echo"<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>

                <tr>
                <b>Kami akan menghargai Anda meluangkan waktu untuk menjawab pertanyaan-pertanyaan berikut sejujur mungkin. Jawaban Anda diperlakukan  rahasia.</b><br>
                <b></b><br>
                <b>Kami percaya bahwa informasi ini sangat penting dan akan membantu kami dalam menganalisis faktor yang berkontribusi terhadap kemajuan perusahaan dimasa mendatang</b><br>
                <b></b></br>

                </tr>

        </table>

      
        </fieldset>  <br></br>";

        echo"<fieldset style='width:700px;'>";
    echo"<legend>".$_SESSION['lang']['form'] ."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>

                
                <tr>
                    <td>".$_SESSION['lang']['nama']."</td>
                    <td>:</td>
                    <td><select id=nama onchange='getData()' style=\"width:200px;\">".$optpengganti."</select></td>

                    <td>".$_SESSION['lang']['departemen']."</td> 
                    <td>:</td>
                    <td><input type=text  id=departemen onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\"  class=myinputtext style=\"width:200px;\">
                    </td>
                </tr>

                <tr>
				 <td style=width:100px;>".$_SESSION['lang']['tanggalmasuk']."</td><td>:</td><td><input style=\"width:200px;\" type='text' class='myinputtext' id='tglmasuk' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:200px; /></td>

				 <td style=width:100px;>".$_SESSION['lang']['jabatan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=jabatan onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\"  class=myinputtext style=\"width:200px;\">
                    </td>
			  </tr>

			   <tr>
				 <td>".$_SESSION['lang']['email']."</td> 
                    <td>:</td>
                    <td><input type=text  id=email onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\"  class=myinputtext style=\"width:200px;\">
                    </td>

				 <td>".$_SESSION['lang']['nohp']."</td> 
                    <td>:</td>
                    <td><input type=text  id=nohp onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\"  class=myinputtext style=\"width:200px;\">
                    </td>
			  </tr>

			  <tr>
				 <td style=width:100px;>".$_SESSION['lang']['tanggalkeluar']."</td><td>:</td><td><input style=\"width:200px;\" type='text' class='myinputtext' id='tglkeluar' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:200px; /></td>

				 <td>".$_SESSION['lang']['perusahaan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=perusahaan onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\"  class=myinputtext style=\"width:200px;\">
                    </td>
			  </tr>

        </table>

      
        </fieldset> <br></br>";



        echo"<fieldset style='width:700px;'>";
    echo"<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
 
                <tr>
                <td colspan=5><h3>1. Apakah alasan utama Anda keluar dari STH Group? </h3>
				</td>
				</tr>

                <td>&nbsp;<td valign=top>            
				<table cellspacing=1 border=0>					
                        <tbody>
                        <td><input type=checkbox id=alasan1 onchange='pilihan()'>".$_SESSION['lang']['kondisi']." ".$_SESSION['lang']['kerja']."</td>
                 </td>

                <tr>
                <td><input type=checkbox id=alasan2 onchange='pilihan()'>".$_SESSION['lang']['kompensasi']."</td>
                  </tr>

                <tr>
                <td><input type=checkbox id=alasan3 onchange='pilihan()'>".$_SESSION['lang']['gangguan']." ".$_SESSION['lang']['kerja']."</td>
                 </tr>

                <tr>
                <td><input type=checkbox id=alasan4 onchange='pilihan()'>".$_SESSION['lang']['diskriminasi']."</td>                
                 </tr>

                <tr>
                <td><input type=checkbox id=alasan5 onchange='pilihan()'>".$_SESSION['lang']['alasan']." ".$_SESSION['lang']['kesehatan']."</td>
                 </tr>

                <tr>
                <td><input type=checkbox id=alasan6 onchange='pilihan()'>".$_SESSION['lang']['dekatdengankel']."</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=alasan7 onchange='pilihan()'>".$_SESSION['lang']['mengikutisuami']."</td>               
                 </tr>

                  <tr>
                <td><input type=checkbox id=alasan8 onchange='pilihan()'>".$_SESSION['lang']['pensiun']."</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=alasan9 onchange='pilihan()'>".$_SESSION['lang']['phk']."</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=alasan10 onchange='pilihan()'>".$_SESSION['lang']['melanjutkan']." ".$_SESSION['lang']['pendidikan']."</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=alasan11 onchange='pilihan()'>".$_SESSION['lang']['alasanpribadi']."</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=alasan12 onchange='pilihan()'>".$_SESSION['lang']['ketidakpastian']."</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=alasan13 onchange='pilihan()'>".$_SESSION['lang']['kurangpengakuan']."</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=alasan14 onchange='pilihan()'>".$_SESSION['lang']['kurangtantangan']."</td>
                 </tr>
					</tbody>
				</table>
				
		<td>&nbsp;<td valign=top>	
				<table cellspacing=1 border=0>
					
					<tbody>
                        <tr>
							<td><input type=checkbox id=alasan15 onchange='pilihan()'>Terlalu banyak tekanan untuk memenuhi target</td>
						</tr>
						<tr>
							<td><input type=checkbox id=alasan16 onchange='pilihan()'>Pindah Keperusahaan Lain, dengan alasan sebagai berikut: </td>
						</tr>
						<tr>
							<td>==><input type=checkbox id=promosi onchange='opsi()' disabled>".$_SESSION['lang']['Promosi']."</td>
						</tr>
                        <tr>
							<td>==><input type=checkbox id=jarak onchange='opsi()' disabled>".$_SESSION['lang']['jarakdari']." ".$_SESSION['lang']['kantor']."</td>
                        </tr>
                        <tr>
							<td>==><input type=checkbox id=jamkerja onchange='opsi()' disabled>".$_SESSION['lang']['jam']." ".$_SESSION['lang']['kerja']."</td>
						</tr>
                        <tr>
							<td>==><input type=checkbox id=benefit onchange='opsi()' disabled>".$_SESSION['lang']['benefit']."</td>
						</tr>
						<tr>
                        <td>==><input type=checkbox id=gajibaik onchange='opsi()' disabled>".$_SESSION['lang']['gaji']." ".$_SESSION['lang']['lebihbaik']."</td>
                        </tr>
						<tr>
                        <td>==><input type=checkbox id=perubahan onchange='opsi()' disabled>".$_SESSION['lang']['perubahan']." ".$_SESSION['lang']['karir']."</td>
                        </tr>
						
                        <tr>
                        <td><input type=checkbox id=alasan17 onchange='pilihan()'>".$_SESSION['lang']['kesempatan']." ".$_SESSION['lang']['karir']." (jelaskan):</td>
                        </tr>

                        <tr>
                        <td colspan=5><textarea name=kesempatantext id=kesempatantext style=\"width:300px;\" onkeypress=\"return tanpa_kutip(event);\" rows='1' cols='20' disabled></textarea></td>
                        </tr>

                        <tr>
                        <td><input type=checkbox id=alasan18 onchange='pilihan()'>".$_SESSION['lang']['lain']." (sebutkan):</td>
                        </tr>

                        <tr>
                        <td colspan=5><textarea name=lainnya id=lainnya style=\"width:300px;\" onkeypress=\"return tanpa_kutip(event);\" rows='1' cols='20' disabled></textarea></td>
                        </tr>

					</tbody>

				</table>

        </table>";

        echo"<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>



                 <tr>
                <td colspan=5><h3>2. Sikap dan peran atasan langsung Anda selama ini:</h3>
				</td>
				</tr>

                 <tr>
                <td>Memberikan bimbingan</td>
                <td width=150><input type=checkbox id=baik1 onchange='pilih(1)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td width=150><input type=checkbox id=cukup1 onchange='pilih(1)'>".$_SESSION['lang']['cukup']."</td>
                	<td width=150><input type=checkbox id=kurang1 onchange='pilih(1)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td>Mengarahkan</td>
                <td><input type=checkbox id=baik2 onchange='pilih(2)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td>	<input type=checkbox id=cukup2 onchange='pilih(2)'>".$_SESSION['lang']['cukup']."</td>
                	<td><input type=checkbox id=kurang2 onchange='pilih(2)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Bersikap ramah</td>
                <td><input type=checkbox id=baik3 onchange='pilih(3)'>".$_SESSION['lang']['sangatbaik']."</td>
                	<td><input type=checkbox id=cukup3 onchange='pilih(3)'>".$_SESSION['lang']['cukup']."</td>
                	<td><input type=checkbox id=kurang3 onchange='pilih(3)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Bersikap Pemarah</td>
                <td><input type=checkbox id=baik4 onchange='pilih(4)'>".$_SESSION['lang']['sangatbaik']."</td>
                	<td><input type=checkbox id=cukup4 onchange='pilih(4)'>".$_SESSION['lang']['cukup']."</td>
                	<td><input type=checkbox id=kurang4 onchange='pilih(4)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Memperhatikan bawahan</td>
                <td><input type=checkbox  id=baik5 onchange='pilih(5)'>".$_SESSION['lang']['sangatbaik']."</td>
	                <td><input type=checkbox id=cukup5 onchange='pilih(5)'>".$_SESSION['lang']['cukup']."</td>
	                <td><input type=checkbox id=kurang5 onchange='pilih(5)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Menunjukan Perlakuan yang adil dan setara</td>
                <td><input type=checkbox id=baik6 onchange='pilih(6)'>".$_SESSION['lang']['sangatbaik']."</td>
                	<td><input type=checkbox id=cukup6 onchange='pilih(6)'>".$_SESSION['lang']['cukup']."</td>
                	<td><input type=checkbox id=kurang6 onchange='pilih(6)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td>Memberikan pengakuan/penghargaan pada pekerjaan</td>
                <td><input type=checkbox id=baik7 onchange='pilih(7)'>".$_SESSION['lang']['sangatbaik']."</td>
                	<td><input type=checkbox id=cukup7 onchange='pilih(7)'>".$_SESSION['lang']['cukup']."</td>
                	<td><input type=checkbox id=kurang7 onchange='pilih(7)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                  <tr>
                <td>Berkerjasama dalam tim untuk maju</td>
                <td><input type=checkbox id=baik8 onchange='pilih(8)'>".$_SESSION['lang']['sangatbaik']."</td>
                	<td><input type=checkbox id=cukup8 onchange='pilih(8)'>".$_SESSION['lang']['cukup']."</td>
                	<td><input type=checkbox id=kurang8 onchange='pilih(8)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td>Mendorong / mendengarkan saran</td>
                <td><input type=checkbox id=baik9 onchange='pilih(9)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup9 onchange='pilih(9)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang9 onchange='pilih(9)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td>Menyeleseikan Keluhan dan masalah</td>
                <td><input type=checkbox id=baik10 onchange='pilih(10)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup10 onchange='pilih(10)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang10 onchange='pilih(10)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td>Mempraktekan pekerjaan untuk diikuti</td>
                <td><input type=checkbox id=baik11 onchange='pilih(11)'>".$_SESSION['lang']['sangatbaik']." </td>
                <td><input type=checkbox id=cukup11 onchange='pilih(11)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang11 onchange='pilih(11)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td colspan=5><h3>3. Bagaimana Anda akan menilai hal berikut dalam kaitannya dengan pekerjaan Anda? </h3>
				</td>
				</tr>

				<tr>
                <td>Hubungan dan Kerjasama dalam departemen Anda</td>
                <td><input type=checkbox id=baik12 onchange='pilih(12)'>".$_SESSION['lang']['sangatbaik']."</td>
                	<td><input type=checkbox id=cukup12 onchange='pilih(12)'>".$_SESSION['lang']['cukup']."</td>
                	<td><input type=checkbox id=kurang12 onchange='pilih(12)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

               <tr>
                <td>Hubungan dan Kerja sama antar departemen</td>
                <td><input type=checkbox id=baik13 onchange='pilih(13)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup13 onchange='pilih(13)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang13 onchange='pilih(13)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Komunikasi dalam departemen Anda</td>
                <td><input type=checkbox id=baik14 onchange='pilih(14)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup14 onchange='pilih(14)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang14 onchange='pilih(14)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Komunikasi dalam organisasi secara keseluruhan</td>
                <td><input type=checkbox id=baik15 onchange='pilih(15)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup15 onchange='pilih(15)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang15 onchange='pilih(15)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Komunikasi antara Anda dan atasan langsung Anda</td>
                <td><input type=checkbox id=baik16 onchange='pilih(16)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup16 onchange='pilih(16)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang16 onchange='pilih(16)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>Training/Pelatihan yang Anda terima</td>
                <td><input type=checkbox id=baik17 onchange='pilih(17)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup17 onchange='pilih(17)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang17 onchange='pilih(17)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td>Potensi pertumbuhan karir anda</td>
                <td><input type=checkbox id=baik18 onchange='pilih(18)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup18 onchange='pilih(18)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang18 onchange='pilih(18)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                  <tr>
                <td>Peluang untuk kemajuan</td>
                <td><input type=checkbox id=baik19 onchange='pilih(19)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup19 onchange='pilih(19)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang19 onchange='pilih(19)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td colspan=5><h3>4. Pendapat anda mengenai pekerjaan terakhir anda? </h3>
				</td>
                 </tr>

                 <tr>
                <td width=350><input type=checkbox id=pendapat1 onchange='choose()'>Beban pekerjaan  terlalu berat/banyak</td>                
                 </tr>

                <tr>
                <td><input type=checkbox id=pendapat2 onchange='choose()'>Beban kerja Bervariasi, tapi masih teratasi</td>
                 </tr>

                <tr>
                <td><input type=checkbox id=pendapat3 onchange='choose()'>Beban kerja cukup Baik</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=pendapat4 onchange='choose()'>Beban pekerjaan cukup ringan/terlalu banyak menganggur</td>               
                 </tr>

                  <tr>
                <td><input type=checkbox id=pendapat5 onchange='choose()'>Pekerjaan terlalu rutin, sehingga membosankan</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=pendapat6 onchange='choose()'>Tugas-tugas yang diberikan tidak jelas</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=pendapat7 onchange='choose()'>Pekerjaan tidak sesuai dengan minat</td>
                 </tr>

                 <tr>
                <td><input type=checkbox id=pendapat8 onchange='choose()'>Komentar Lainnya:</td>
                <td colspan=5><textarea name=lanjutkan id=lanjutkan style=\"width:250px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr>
                <td colspan=5><h3> 5. Bagaimana pendapat Anda tentang gaji dan tunjangan yang disediakan oleh perusahaan? </h3>
				</td>
                 </tr>

                 <tr>
                <td width=150>".$_SESSION['lang']['gajipokok']."</td>
                <td width=150><input type=checkbox id=baik20 onchange='pilih(20)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td width=150><input type=checkbox id=cukup20 onchange='pilih(20)'>".$_SESSION['lang']['cukup']."</td>
                <td width=150><input type=checkbox id=kurang20 onchange='pilih(20)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

               <tr>
                <td>".$_SESSION['lang']['tjmedis']."</td>
                <td><input type=checkbox id=baik21 onchange='pilih(21)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup21 onchange='pilih(21)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang21 onchange='pilih(21)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                <td>".$_SESSION['lang']['tjhidup']."</td>
                <td><input type=checkbox id=baik22 onchange='pilih(22)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup22 onchange='pilih(22)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang22 onchange='pilih(22)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                <tr>
                <td>".$_SESSION['lang']['tjlibur']."</td>
                <td><input type=checkbox id=baik23 onchange='pilih(23)'>".$_SESSION['lang']['sangatbaik']."</td>
                <td><input type=checkbox id=cukup23 onchange='pilih(23)'>".$_SESSION['lang']['cukup']."</td>
                <td><input type=checkbox id=kurang23 onchange='pilih(23)'>".$_SESSION['lang']['kurang']."</td>
                 </tr>

                 <tr>
                 <td></td>
                 </tr>
                 <tr>
                 <td></td>
                 </tr>

                <tr>
                <td>Apakah ada manfaat lain yang menurut Anda harus anda dapatkan?</td>
                <td><input type=checkbox id=baik24 onchange='pilih(24)'>".$_SESSION['lang']['yes']."
                	<input type=checkbox id=cukup24 onchange='pilih(24)'>".$_SESSION['lang']['no']."
                	<input type=hidden id=kurang24 onchange='pilih(24)'>".$_SESSION['lang']['tidaktahu']."
                	</td>
                 </tr>

                <tr>
                <td>Jika Ya, apa? </td>
                <td colspan=5><input type=text id=yaapa \"return tanpa_kutip(event);\" class=myinputtext style=\"width:300px;\"></td>

                 </tr>

                 <tr>
                <td>Komentar lain mengenai manfaat/benefits?</td>
                <td colspan=5><input type=text id=komenlain \"return tanpa_kutip(event);\" class=myinputtext style=\"width:300px;\"></td>

                 </tr>

                 <tr>
                <td colspan=5><h3> 6. Seberapa sering Anda mendapatkan umpan balik  / performance reviews ? Bagaimana perasaan Anda?</h3>
				</td>
                 </tr>

               <tr>
                <td colspan=5><textarea name=text6 id=umpanbalik style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr><tr></tr></tr>

                 <tr>
                <td colspan=5><h3> 7. Seberapa sering anda berdiskusi dengan manajer Anda tentang tujuan karir Anda?</h3>
				</td>
               
                 </tr>

               <tr>
                <td colspan=5><textarea name=text7 id=diskusi style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                  <tr><tr></tr></tr>

                <tr>
                <td colspan=5><h3> 8. Apa yang membuat Anda dahulu berminat bekerja di STH Group Indonesia?</h3>
				</td>
               
                 </tr>

               <tr>
                <td colspan=5><textarea name=text8 id=minat style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr>
                <td colspan=5><h3> 9. Hal apakah yang paling Anda sukai selama bekerja di STH Group Indonesia?</h3>
				</td>
               
                 </tr>

               <tr>
                <td colspan=5><textarea name=text9 id=suka style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr>
                <td colspan=5><h3> 10. Hal apakah yang paling kurang Anda sukai selama bekerja di STH Group Indonesia?</h3>
				</td>
               
                 </tr>

               <tr>
                <td colspan=5><textarea name=text10 id=kurangsuka style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr>
                <td colspan=5><h3> 11. Sebagai bahan pembelajaran bagi perusahaan,kami sangat menghargai keterbukaan anda dalam memberikan saran sehingga kami dapat mencari jalan untuk perbaikan. Setelah bersama dengan perusahaan dalam periode ini,apa yang anda fikirkan dalam upaya untuk meningkatkan kemajuan perusahaan ini? </h3>
				</td>
               
                 </tr>

               <tr>
                <td colspan=5><textarea name=text11 id=kemajuan style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr>
                <td colspan=5><h3> 12. Komentar atau saran lain yang ingin Anda sampaikan?</h3>
				</td>
               
                 </tr>

               <tr>
                <td colspan=5><textarea name=text12 id=komentar style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr>
                <td colspan=5><h3> 13. Daftar inventaris perusahaan yang di Serahterimakan :</h3>
				</td>
               
                 </tr>

               <tr>
                <td>a. <input type=text id=invent1 class=myinputtext style=\"width:300px;\"></td>
                 </tr>
                 <tr>
                <td>b. <input type=text id=invent2 class=myinputtext style=\"width:300px;\"></td>
                 </tr>
                 <tr>
                <td>c. <input type=text id=invent3 class=myinputtext style=\"width:300px;\"></td>
                 </tr>
                 <tr>
                <td>d. <input type=text id=invent4 class=myinputtext style=\"width:300px;\"></td>
                 </tr>


                 <tr>
                <td colspan=5><h3> 14. Keterangan tambahan untuk kemungkinan dipertahankan (diisi oleh HRD )</h3>
				</td>
               
                 </tr>

               <tr>
                <td colspan=5><textarea name=text14 id=keterangan style=\"width:650px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>
                 </tr>

                 <tr><td colspan=2>
                 <input type=hidden id=method value='insert'>
                    	<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>	  

                        </td>
                </tr>

        </table>

      
        </fieldset>";

CLOSE_BOX();
?>



<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
        <div id=container> 
            <script>loadData(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>