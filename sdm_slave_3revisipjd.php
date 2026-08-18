<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi = checkPostGet('notransaksi', '');
$notransaksi2 = checkPostGet('notransaksi2', '');
$tanggal = tanggalsystem(checkPostGet('tanggal', ''));
$jenisby = checkPostGet('jenisby', '');
$jumlahhrd = checkPostGet('jumlahhrd', '');
$kodeOrg = checkPostGet('kodeOrg', '');
$jumlah = checkPostGet('jumlah', '');
$proses = checkPostGet('proses', '');

switch ($proses) {

    case'getData':
        $kd = substr($notransaksi, 0, 4);
        $sOrg = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi 
           where char_length(kodeorganisasi)='4' order by namaorganisasi asc";
        $optOrg = "";
        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optOrg.="<option value='" . $rOrg['kodeorganisasi'] . "' " . ($kd == $rOrg['kodeorganisasi'] ? "selected" : "") . ">" . $rOrg['namaorganisasi'] . "</option>";
        }
        echo"<tr class=rowcontent><td colspan=4>Ganti Notransaksi</td>";
        echo"<td><select id=kdOrg onchange=getNotrans()>" . $optOrg . "</select></td>";
        echo"<td><img src='images/save.png' title='Save' class=resicon onclick=saveNotrans()></td>";
        echo"</tr>";

        $str = "select a.*,b.keterangan as jns,b.id as bid from " . $dbname . ".sdm_pjdinasdt a
          left join " . $dbname . ".sdm_5jenisbiayapjdinas b on a.jenisbiaya=b.id
              where a.notransaksi='" . $notransaksi . "'";
        $no = 0;
        $total = 0;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>
                    <td>" . $no . "</td>
                        <td>" . $bar->jns . "</td>
                            <td>" . tanggalnormal($bar->tanggal) . "</td>
                            <td>" . $bar->keterangan . "</td>
                            <td align=right>" . number_format($bar->jumlah, 2, '.', '.') . "</td>
                            <td align=right>
                            <img src='images/puzz.png' style='cursor:pointer;' title='click to get value' onclick=\"document.getElementById('jumlahhrd" . $bar->bid . $no . "').value='" . $bar->jumlah . "'\">
                            <input type=text id='jumlahhrd" . $bar->bid . $no . "' class=myinputtextnumber size=15 onkeypress=\"return angka_doang(event);\" onblur=change_number(this) value='" . number_format($bar->jumlahhrd, 2, '.', ',') . "'>
                            <img src='images/save.png' title='Save' class=resicon onclick=saveApprvPJD('" . $bar->bid . "','" . $bar->notransaksi . "','" . tanggalnormal($bar->tanggal) . "','" . $bar->jumlah . "','" . $no . "')></td>
                            </tr>";
            @$total+=$bar->jumlah;
        }
        echo"<tr class=rowcontent>
                    <td colspan=4 align=center>TOTAL</td>
                            <td align=right>" . number_format($total, 2, '.', '.') . "</td>
                        <td></td>
                            </tr>";
        break;
    case'getNotrans':
        $orge = substr($notransaksi, 0, 4);
        if ($kodeOrg == $orge) {
            exit("Error:Kodeorganisasi Yang Sama");
        }
        $potSK = $kodeOrg . date('Y');
        $str = "select notransaksi from " . $dbname . ".sdm_pjdinasht
      where  notransaksi like '" . $potSK . "%'
          order by notransaksi desc limit 1";

        $notrx = 0;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $notrx = substr($bar->notransaksi, 10, 5);
        }
        $notrx = intval($notrx);
        $notrx = $notrx + 1;
        $notrx = str_pad($notrx, 5, "0", STR_PAD_LEFT);
        $notrx = $potSK . $notrx;
        echo $notrx;
        break;
    case'saveNotrans':
        $orge = substr($notransaksi, 0, 4);
        if ($kodeOrg == $orge) {
            exit("Error:Kodeorganisasi Yang Sama");
        }
        $supd = "update " . $dbname . ".sdm_pjdinasht set notransaksi='" . $notransaksi2 . "' where notransaksi='" . $notransaksi . "'";
        try {
            $owlPDO->exec($supd);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        echo $notransaksi2;
        break;
}
?>