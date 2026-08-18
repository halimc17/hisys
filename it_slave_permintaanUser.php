<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$jenislayanan = checkPostGet('jenislayanan', '');
$deskripsi = checkPostGet('deskripsi', '');
$atasan = checkPostGet('atasan', '');
$managerit = checkPostGet('managerit', '');
$date = date('Y-m-d');
$d = substr($date, 8, 2);
$m = numToMonth(substr($date, 6, 2), $lang = 'I', $format = 'long');
$y = substr($date, 0, 4);
$tanggal = $d . " " . $m . " " . $y;
$lokasitugas = $_SESSION['empl']['lokasitugas'];
$karyawanid = $_SESSION['standard']['userid'];
$kepuasanuser = checkPostGet('kepuasanuser', '');
$nilaikomunikasi = checkPostGet('nilaikomunikasi', '');
$notransaksi = checkPostGet('notransaksi', '');
$saranuser = checkPostGet('saranuser', '');
$tolak = checkPostGet('tolak', '');
$transaksi = checkPostGet('transaksi', '');

switch ($proses) {
    case 'insert':
        $notransaksi = 0;
        $insert = "insert into " . $dbname . ".it_request
        (notransaksi,kodekegiatan,deskripsi,tanggal,lokasitugas,karyawanid,atasan,managerit)
         values('" . $notransaksi . "','" . $jenislayanan . "','" . $deskripsi . "','" . $date . "','" . $lokasitugas . "',
        '" . $karyawanid . "','" . $atasan . "','" . $managerit . "')";
		try{
			$owlPDO->exec($insert); 
			
			$s_ket = "select a.keterangan as ket from " . $dbname . ".it_standard a where a.kodekegiatan='" . $jenislayanan . "'";
			$q_ket=$owlPDO->query($s_ket) or die(print " Gagal: ".PDOException::getMessage());
			$q_ket->setFetchMode(PDO::FETCH_ASSOC);
            $r_ket = $q_ket->fetch();
            $ket = $r_ket['ket'];
            if ($atasan != '') {
                #send an email to incharge person
                $to = getUserEmail($atasan);
                $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
                $subject = "[Notifikasi] Permintaan Layanan " . $ket . " ";
                $body = "<html>
         <head>
         <body>
           Dengan Hormat,<br>
           <br>
           Karyawan a/n: " . $namakaryawan . " meminta layanan " . $ket . " pada tanggal " . $tanggal . " 
           ke departemen IT<br>dengan deskripsi " . $deskripsi . "<br>Mohon konfirmasi dari bapak/ibu melalui 
           menu IT->Permintaan Layanan
           <br>
           <br>
           Regards,<br>
           Owl-Plantation System.
         </body>
         </head>
        </html>
        ";
                $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
            }
            if ($managerit != '') {
                #send an email to incharge person
                $to = getUserEmail($managerit);
                $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
                $subject = "[Notifikasi] Permintaan Layanan " . $ket . "";
                $body = "<html>
         <head>
         <body>
           Dengan Hormat,<br>
           <br>
           Karyawan a/n: " . $namakaryawan . " meminta layanan " . $ket . " pada tanggal " . $tanggal . " 
           ke departemen IT<br>dengan deskripsi " . $deskripsi . "<br>Mohon konfirmasi dari bapak/ibu melalui 
           menu IT->Permintaan Layanan
           <br>
           <br>
           Regards,<br>
           Owl-Plantation System.
         </body>
         </head>
        </html>
        ";
                $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
            }
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'loaddata':
        $limit = 25;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $sCount = "select count(*) as jmlhrow from " . $dbname . ".it_request 
         where karyawanid='" . $_SESSION['standard']['userid'] . "' or atasan='" . $_SESSION['standard']['userid'] . "'
         or managerit='" . $_SESSION['standard']['userid'] . "' order by notransaksi asc";
		 $qCount=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
		 $qCount->setFetchMode(PDO::FETCH_OBJ);
        while ($rCount = $qCount->fetch()) {
            $jmlbrs = $rCount->jmlhrow;
        }
        $offset = $page * $limit;
        if ($jmlbrs < ($offset))
            $page-=1;
        $offset = $page * $limit;
        $no = $offset;

        $s_login = "select a.tanggal as tgl,b.keterangan as namakegiatan,c.namakaryawan as namakaryawan,
          a.atasan as atasan,a.statusatasan as statusatasan, tanggalatasan as tglatasan,
          a.statusmanagerit as statusmgr,a.pelaksana as pelaksana,a.waktupelaksanaan as wktpelaksanaan,
          a.waktuselesai as wktselesai,a.nilaikomunikasi as nilaikom,a.saranuser as saran,
          a.saranpelaksana as saranpelaksana,a.karyawanid as karyawanid,
          a.nilaihasilkerja as nilaihasilkerja,a.saranuser as saran,a.notransaksi as notransaksi,
          a.managerit as managerit
          from " . $dbname . ".it_request a
          left join " . $dbname . ".it_standard b on a.kodekegiatan=b.kodekegiatan
          left join " . $dbname . ".datakaryawan c on a.karyawanid=c.karyawanid
          where a.karyawanid='" . $_SESSION['standard']['userid'] . "' or a.atasan='" . $_SESSION['standard']['userid'] . "'
          or a.managerit='" . $_SESSION['standard']['userid'] . "'
          order by a.notransaksi asc limit " . $offset . "," . $limit . " ";
		  $q_login=$owlPDO->query($s_login) or die(print " Gagal: ".PDOException::getMessage());
		  $q_login->setFetchMode(PDO::FETCH_ASSOC);
        while ($r_login = $q_login->fetch()) {
			$optNamaKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
            // $s_atasan = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $r_login['atasan'] . "'";
			// $q_atasan=$owlPDO->query($s_atasan) or die(print " Gagal: ".PDOException::getMessage());
			// $q_atasan->setFetchMode(PDO::FETCH_ASSOC);
            // $r_atasan = $q_atasan->fetch();
            $no+=1;
            echo"<tr class=rowcontent>
				<td id='no' align='center'>" . $no . "</td>
				<td id='tgl_" . $no . "' align='center'>" . tanggalnormal($r_login['tgl']) . "</td>
				<td id='namakegiatan_" . $no . "' align='left'>" . $r_login['namakegiatan'] . "</td>
				<td id='namakaryawan_" . $no . "' align='left'>" . $r_login['namakaryawan'] . "</td>
				<td id='atasan_" . $no . "' align='left'>" . (isset($optNamaKar[$r_login['atasan']]) ? $optNamaKar[$r_login['atasan']] : '') . "</td>";

            $s_kepuasanuser = "select keterangan from " . $dbname . ".it_stkepuasan 
                         where kode='HASILKERJA' and nilai='" . $r_login['nilaihasilkerja'] . "' order by nilai asc";
			$q_kepuasanuser=$owlPDO->query($s_kepuasanuser) or die(print " Gagal: ".PDOException::getMessage());
			$q_kepuasanuser->setFetchMode(PDO::FETCH_ASSOC);
            $r_kepuasanuser = $q_kepuasanuser->fetch();
            $s_nilaikom = "select keterangan from " . $dbname . ".it_stkepuasan 
                 where kode='KOMUNIKASI' and nilai='" . $r_login['nilaikom'] . "' order by nilai asc";
			$q_nilaikom=$owlPDO->query($s_nilaikom) or die(print " Gagal: ".PDOException::getMessage());
			$q_nilaikom->setFetchMode(PDO::FETCH_ASSOC);
            $r_nilaikom = $q_nilaikom->fetch();

            # Jika yang login adalah atasan maka: 
            # jika statusatasan='0' maka munculkan tombol setuju dan tombol tolak pada kolom statusatasan, 
            # jika setuju di click maka update table isi dengan angka 1, 
            # dan jika ditolak maka munculkan form isian komentar penolakan dengan tombol save, 
            # pada saat save penolakan update kolom statusatasan dengan alasan tsb, 
            # kemudian isi tanggalatasan dengan date('Y-m-d')	
            if ($karyawanid == $r_login['atasan']) {
                if (($r_login['statusatasan'] == '0')) {
                    echo "<td id='statusatasan_" . $no . "' align='center'>
						<button class=mybutton onclick=setuju('" . $no . "');>" . $_SESSION['lang']['setuju'] . "</button>
						<button class=mybutton onclick=tolak('" . $no . "');>" . $_SESSION['lang']['tolak'] . "</button></td>";
                } else if (($r_login['statusatasan'] == '1')) {
                    echo "<td id='statusatasan_" . $no . "' align='left'>Setuju</td>";
                } else {
                    echo "<td id='statusatasan_" . $no . "' align='left'>Ditolak, " . $r_login['statusatasan'] . "</td>";
                }
                echo "<td id='tglatasan_" . $no . "' align='center'>" . ($r_login['tglatasan'] == '0000-00-00' ? "" : tanggalnormal($r_login['tglatasan'])) . "</td>
				<td id='statusmgr_" . $no . "' align='center'>" . ($r_login['statusmgr'] == '1' ? "Setuju" : ($r_login['statusmgr'] == '0' ? "Waiting" : "Ditolak, ".$r_login['statusmgr'])) . "</td>
				<td id='pelaksana_" . $no . "' align='left'>" . (isset($optNamaKar[$r_login['pelaksana']]) ? $optNamaKar[$r_login['pelaksana']] : "") . "</td>
				<td id='wktpelaksana_" . $no . "' align='center'>" . ($r_login['wktpelaksanaan'] == "0000-00-00 00:00:00" ? "" : tanggalnormald($r_login['wktpelaksanaan'])) . "</td>
				<td id='wktselesai_" . $no . "' align='center'>" . ($r_login['wktselesai'] == "0000-00-00 00:00:00" ? "" : tanggalnormald($r_login['wktselesai'])) . "</td>
				<input type=hidden id='notransaksi_" . $no . "' value='" . $r_login['notransaksi'] . "'>
				<td id='kepuasanuser_" . $no . "' align='center'>" . $r_kepuasanuser['keterangan'] . "</td>
				<td id='nilaikomunikasi_" . $no . "' align='center'>" . $r_nilaikom['keterangan'] . "</td>
				<td id='saran_" . $no . "' colspan=2 align='left'>" . $r_login['saran'] . "</td>
				<td id='saranpelaksana_" . $no . "' align='center'>" . $r_login['saranpelaksana'] . "</td>
				<td align=center><img onclick=view('" . $no . "') title=\"View\" class=\"resicon\" src=\"images/zoom.png\"></td>";
            } else if ($karyawanid == $r_login['karyawanid']) {
                echo "<td id='statusatasan_" . $no . "' align='left'>".($r_login['statusatasan'] == '1' ? "Setuju" : ($r_login['statusatasan'] == '0' ? 'Waiting' : "Ditolak, ".$r_login['statusatasan']))."</td>
				<td id='tglatasan_" . $no . "' align='center'>" . ($r_login['tglatasan'] == '0000-00-00' ? "" : tanggalnormal($r_login['tglatasan'])) . "</td>
				<td id='statusmgr_" . $no . "' align='center'>" . ($r_login['statusmgr'] == '1' ? "Setuju" : ($r_login['statusmgr'] == '0' ? "Waiting" : "Ditolak, ".$r_login['statusmgr'])) . "</td>
				<td id='pelaksana_" . $no . "' align='left'>" . (isset($optNamaKar[$r_login['pelaksana']]) ? $optNamaKar[$r_login['pelaksana']] : '') . "</td>
				<td id='wktpelaksana_" . $no . "' align='center'>" . ($r_login['wktpelaksanaan'] == "0000-00-00 00:00:00" ? "" : tanggalnormald($r_login['wktpelaksanaan'])) . "</td>
				<td id='wktselesai_" . $no . "' align='center'>" . ($r_login['wktselesai'] == "0000-00-00 00:00:00" ? "" : tanggalnormald($r_login['wktselesai'])) . "</td>
				<input type=hidden id='notransaksi_" . $no . "' value='" . $r_login['notransaksi'] . "'>";

                # Jika yang login adalah pembuat, maka: 
                # jika nilaihasilkerja=0, munculkan option pada kolom Kepuasan User ambil dari table it_stkepuasan dengan 
                # kode=HASILKERJA, default optionnya adalah blank dan onchange update table pada field nilaihasilkerja, 
                # jika nilaihasilkerja!=0 maka munculkan keterangan nilainya	
                if (($r_login['nilaihasilkerja'] == 0)) {
                    $opt_kepuasanuser = "<option value=''></option>";
                    $s_kepuasanuser = "select nilai,keterangan from " . $dbname . ".it_stkepuasan 
                             where kode='HASILKERJA' order by nilai asc";
					$q_kepuasanuser=$owlPDO->query($s_kepuasanuser) or die(print " Gagal: ".PDOException::getMessage());
					$q_kepuasanuser->setFetchMode(PDO::FETCH_ASSOC);
                    while ($r_kepuasanuser = $q_kepuasanuser->fetch()) {
                        $opt_kepuasanuser.="<option value='" . $r_kepuasanuser['nilai'] . "'>" . $r_kepuasanuser['keterangan'] . "</option>";
                    }
                    echo"<td><select id='kepuasanuser_" . $no . "' style='width:150px;' onchange=update_nilaihk('" . $no . "'); >" . $opt_kepuasanuser . "</select></td>";
                } else {
                    echo"<td id='kepuasanuser_" . $no . "' align='left'>" . $r_kepuasanuser['keterangan'] . "</td>";
                }

                #Jika yang login adalah pembuat, maka: 
                #jika nilaikomunikasi=0, munculkan option pada kolom Nilai Komunikasi ambil dari table it_stkepuasan dengan 
                #kode=KOMUNIKASI, default optionnya adalah blank dan onchange update table pada field nilaikomunikasi, 
                #jika nilaikomunikasi!=0 munculkan keterangan nilainya
                if (($r_login['nilaikom'] == 0)) {
                    $opt_nilaikom = "<option value=''></option>";
                    $s_nilaikom = "select nilai,keterangan from " . $dbname . ".it_stkepuasan 
                         where kode='KOMUNIKASI' order by nilai asc";
					$q_nilaikom=$owlPDO->query($s_nilaikom) or die(print " Gagal: ".PDOException::getMessage());
					$q_nilaikom->setFetchMode(PDO::FETCH_ASSOC);
                    while ($r_nilaikom = $q_nilaikom->fetch()) {
                        $opt_nilaikom.="<option value='" . $r_nilaikom['nilai'] . "'>" . $r_nilaikom['keterangan'] . "</option>";
                    }
                    echo"<td><select id='nilaikomunikasi_" . $no . "' style='width:150px;' onchange=update_nilaikom('" . $no . "');>" . $opt_nilaikom . "
                </select></td>";
                } else {
                    echo"<td id='nilaikomunikasi_" . $no . "' align='left'>" . $r_nilaikom['keterangan'] . "</td>";
                }

                # Jika yang login adalah pembuat, maka: 
                # jika saranuser is null maka munculkan textbox return tanpa kutip, dibelakangnya kasih icon save, 
                # jika saran user sudah terisi maka tampilkan saran usernya(ambil 15 character pertama dan akhiri dengan …
                # (tiga titik) jadi tidak semua isi saran ditampilkan.									
                if (($r_login['saran'] == '')) {
                    echo "<td><textarea rows=2 cols=15 id='saranuser_" . $no . "' onkeypress=return tanpa_kutip(); /></textarea></td>
                  <td><img onclick=simpan('" . $no . "'); class=\"resicon\" src=\"images/skyblue/save.png\"></td>";
                } else {
                    $saran = substr($r_login['saran'], 0, 15);
                    echo"<td colspan=2 id='saran_" . $no . "' align='left'>" . $saran . "..." . "</td>";
                }

                # Kolom saran pelaksana juga ambil 15 character saja dan diakhiri dengan… 
                if (($r_login['saranpelaksana'] != '')) {
                    echo"<td id='saranpelaksana_" . $no . "' align='left'>" . substr($r_login['saranpelaksana'], 0, 15) . "..." . "</td>";
                } else {
                    echo"<td id='saranpelaksana_" . $no . "' align='left'>" . $r_login['saranpelaksana'] . "</td>";
                }
                echo"<td align=center><img onclick=view('" . $no . "') title=\"View\" class=\"resicon\" src=\"images/zoom.png\"></td>";
            } else if ($karyawanid == $r_login['managerit']) {
                echo "<td id='statusatasan_" . $no . "' align='left'>".($r_login['statusatasan'] == '1' ? "Setuju" : ($r_login['statusatasan'] == '0' ? 'Waiting' : "Ditolak, ".$r_login['statusatasan']))."</td>
                <td id='tglatasan_" . $no . "' align='center'>" . ($r_login['tglatasan'] == '0000-00-00' ? "" : tanggalnormal($r_login['tglatasan'])) . "</td>
				<td id='statusmgr_" . $no . "' align='center'>" . ($r_login['statusmgr'] == '1' ? "Setuju" : ($r_login['statusmgr'] == '0' ? "Waiting" : "Ditolak, ".$r_login['statusmgr'])) . "</td>
				<td id='pelaksana_" . $no . "' align='left'>" . (isset($optNamaKar[$r_login['pelaksana']]) ? $optNamaKar[$r_login['pelaksana']] : "") . "</td>
				<td id='wktpelaksana_" . $no . "' align='center'>" . ($r_login['wktpelaksanaan'] == "0000-00-00 00:00:00" ? "" : tanggalnormald($r_login['wktpelaksanaan'])) . "</td>
				<td id='wktselesai_" . $no . "' align='center'>" . ($r_login['wktselesai'] == "0000-00-00 00:00:00" ? "" : tanggalnormald($r_login['wktselesai'])) . "</td>
				<input type=hidden id='notransaksi_" . $no . "' value='" . $r_login['notransaksi'] . "'>
				<td id='kepuasanuser_" . $no . "' align='left'>" . $r_kepuasanuser['keterangan'] . "</td>
				<td id='nilaikomunikasi_" . $no . "' align='left'>" . $r_nilaikom['keterangan'] . "</td>
				<td id='saran_" . $no . "' colspan=2 align='left'>" . $r_login['saran'] . "</td>
				<td id='saranpelaksana_" . $no . "' align='left'>" . $r_login['saranpelaksana'] . "</td>
				<td align=center><img onclick=view('" . $no . "') title=\"View\" class=\"resicon\" src=\"images/zoom.png\"></td>";
            }
        }
        echo"
</tr><tr class=rowheader><td colspan=15 align=center>
" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jmlbrs . "<br />
<button class=mybutton onclick=pages(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
<button class=mybutton onclick=pages(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
</td>
</tr>";
        break;

    case 'update_nilaihk':
        $s_update = "update " . $dbname . ".it_request set nilaihasilkerja='" . $kepuasanuser . "' where notransaksi='" . $notransaksi . "' ";
		try{
			$owlPDO->exec($s_update); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
            exit();
		}
        break;

    case 'update_nilaikom':
        $s_update = "update " . $dbname . ".it_request set nilaikomunikasi='" . $nilaikomunikasi . "' where notransaksi='" . $notransaksi . "' ";
		try{
			$owlPDO->exec($s_update); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
            exit();
		}
        break;

    case 'update_saranuser':
        $s_update = "update " . $dbname . ".it_request set saranuser='" . $saranuser . "' where notransaksi='" . $notransaksi . "' ";
		try{
			$owlPDO->exec($s_update); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
            exit();
		}
        break;

    case 'setuju':
        $s_setuju = "update " . $dbname . ".it_request set statusatasan=1, tanggalatasan = now() where notransaksi='" . $notransaksi . "' ";
		try{
			$owlPDO->exec($s_setuju); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
            exit();			
		}
        break;

    case 'formpenolakan':
        $s_form = "select * from " . $dbname . ".it_request where notransaksi='" . $notransaksi . "' ";
		$q_from=$owlPDO->query($s_form) or die(print " Gagal: ".PDOException::getMessage());
		$q_from->setFetchMode(PDO::FETCH_ASSOC);
        $r_form = $q_from->fetch();
        echo "<div id=form_tolak><fieldset><legend>No Transaksi: " . $notransaksi . "</legend>
          <table cellspacing=1 border=0>
            <tr>
               <td><textarea rows=5 cols=34 id='tolak'></textarea></td>
               <td><button class=mybutton id=save onclick=save('" . $notransaksi . "')>";
        echo $_SESSION['lang']['save'];
        echo"</button></td></tr></table></filedset></div>";
        break;

    case 'update_statusatasan':
        $s_tolak = "update " . $dbname . ".it_request set statusatasan='" . $tolak . "',tanggalatasan='" . date('Y-m-d') . "' 
              where notransaksi='" . $transaksi . "' ";
        try{
			$owlPDO->exec($s_tolak); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
            exit();
		}
        break;

    case 'show':
        $s_form = "select * from " . $dbname . ".it_request where notransaksi='" . $notransaksi . "' ";
		$q_from=$owlPDO->query($s_form) or die(print " Gagal: ".PDOException::getMessage());
		$q_from->setFetchMode(PDO::FETCH_ASSOC);
        $r_form = $q_from->fetch();
        echo "<div id=view><fieldset><legend>No Transaksi: " . $notransaksi . "</legend>
          <table cellspacing=1 border=0>
              <tr><td style=width:100px;>Deskripsi</td><td>:</td><td align=left>" . $r_form['deskripsi'] . "</td></tr>
              <tr><td style=width:100px;>Saran User</td><td>:</td><td align=left>" . $r_form['saranuser'] . "</td></tr>
              <tr><td style=width:100px;>Saran Pelaksana</td><td>:</td><td align=left>" . $r_form['saranpelaksana'] . "</td></tr>
          </table></filedset></div>";
        break;

    default:
        break;
}
?>