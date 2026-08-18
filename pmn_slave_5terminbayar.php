<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php

$method = checkPostGet('method', '');
$kode = checkPostGet('kode', '');
$satu = checkPostGet('satu', '');
$dua = checkPostGet('dua', '');
$tiga = checkPostGet('tiga', '');
$empat = checkPostGet('empat', '');
$lima = checkPostGet('lima', '');
?>

<?php

switch ($method) {
    case 'insert':
        $ha = "insert into " . $dbname . ".pmn_5terminbayar (`kode`,`satu`,`dua`,`tiga`,`empat`,`lima`,`updateby`)
        values ('" . $kode . "','" . $satu . "','" . $dua . "','" . $tiga . "','" . $empat . "','" . $lima . "','" . $_SESSION['standard']['userid'] . "')";
        /* if(mysql_query($ha))
          {
          }
          else
          {
          echo " Gagal,".addslashes(mysql_error($conn));
          } */
        try {
            $owlPDO->exec($ha);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'update':
        $ha = "update " . $dbname . ".pmn_5terminbayar set satu='" . $satu . "',dua='" . $dua . "',tiga='" . $tiga . "',empat='" . $empat . "',lima='" . $lima . "' where kode='" . $kode . "'";
        /* if(mysql_query($ha))
          {
          }
          else
          {
          echo " Gagal,".addslashes(mysql_error($conn));
          } */
        try {
            $owlPDO->exec($ha);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;


    case'loadData':
        echo"<div id=container>
                <table class=sortable cellspacing=1 border=0>
                 <thead>
                             <tr class=rowheader>
                                <td align=center>No</td>
                                <td align=center>" . $_SESSION['lang']['kode'] . "</td>
                                <td align=center>" . $_SESSION['lang']['termin'] . " 1</td>    
                                <td align=center>" . $_SESSION['lang']['termin'] . " 2</td>   
                                <td align=center>" . $_SESSION['lang']['termin'] . " 3</td>   
                                <td align=center>" . $_SESSION['lang']['termin'] . " 4</td>   
                                <td align=center>" . $_SESSION['lang']['termin'] . " 5</td>   
                                <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
                                <td align=center>" . $_SESSION['lang']['action'] . "</td>
                             </tr>
                    </thead>
                    <tbody>";
        $no = 0;
        $iList = "select * from " . $dbname . ".pmn_5terminbayar ";
        //$nList=mysql_query($iList) or die(mysql_error($conn));
        //while($dList=mysql_fetch_assoc($nList))

        $nList = $owlPDO->query($iList) or die(print " Gagal: " . PDOException::getMessage());
        $nList->setFetchMode(PDO::FETCH_ASSOC);
        while ($dList = $nList->fetch()) {
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . $dList['kode'] . "</td>";
            echo "<td align=right>" . $dList['satu'] . "</td>";
            echo "<td align=right>" . $dList['dua'] . "</td>";
            echo "<td align=right>" . $dList['tiga'] . "</td>";
            echo "<td align=right>" . $dList['empat'] . "</td>";
            echo "<td align=right>" . $dList['lima'] . "</td>";
            echo "<td>" . getNamaKaryawan($dList['updateby']) . "</td>";
            echo "<td align=center>
                    <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                    onclick=\"fillField('" . $dList['kode'] . "','" . $dList['satu'] . "','" . $dList['dua'] . "','" . $dList['tiga'] . "','" . $dList['empat'] . "','" . $dList['lima'] . "');\">

                    <img src=images/application/application_delete.png class=resicon  caption='Delete' 
                    onclick=\"del('" . $dList['kode'] . "');\">

                    </td>";
            echo "</tr>"; //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
        }
        break;

    case 'delete':
        $ha = "delete from " . $dbname . ".pmn_5terminbayar where kode='" . $kode . "' ";
        //exit("Error:$tab");
        /* if(mysql_query($tab))
          {
          }
          else
          {
          echo " Gagal,".addslashes(mysql_error($conn));
          } */
        try {
            $owlPDO->exec($ha);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;




    default:
}
?>