<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['bantu']).'</span>');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/help_bantuan.js"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />

<div id="list_ganti">
    <div id="action_list">

    </div>
    <fieldset style='float:left;'>
        <legend><?php echo $_SESSION['lang']['find'] . " " . $_SESSION['lang']['bantu'] ?></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><?php echo $_SESSION['lang']['find'] ?></td><td>:</td>
                <td>
                    <input type='text' class='myinputtext' id='cariindex' onkeypress="return tanpa_kutip();"  size='10' maxlength='30'  style="width:150px;" />
                    <button class=mybutton id='cari' onclick=cariHelp_en()><?php echo $_SESSION['lang']['find'] ?></button>
                </td>
            </tr>
        </table>
        <table cellspacing="1" border="0" class="sortable">
            <thead>

                <tr class="rowheader">
                    <td align="center">No.</td>
                    <td align="center"><?php echo $_SESSION['lang']['index'] ?></td>
                    <td align="center"><?php echo $_SESSION['lang']['modul'] ?></td>
                    <td align="center"><?php echo $_SESSION['lang']['tentang'] ?></td>
                    <td colspan="3" align="center"><?php echo $_SESSION['lang']['action'] ?></td>
                </tr>
            </thead>
            <tbody id="contain">
                <?php
                $limit = 10;
                $page = 0;
                if (isset($_POST['page'])) {
                    $page = $_POST['page'];
                    if ($page < 0)
                        $page = 0;
                }
                $offset = $page * $limit;

                $q = "select count(*) as jmlhrow from " . $dbname . ".owl_help_en order by kode asc";
				$query=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
				$query->setFetchMode(PDO::FETCH_OBJ);
                while ($jsl = $query->fetch()) {
                    $jmlbrs = $jsl->jmlhrow;
                }

                $q2 = "select * from " . $dbname . ".owl_help_en order by kode asc,tentang,modul,isi limit " . $offset . "," . $limit . " ";
				$query2=$owlPDO->query($q2) or die(print " Gagal: ".PDOException::getMessage());
				$query2->setFetchMode(PDO::FETCH_ASSOC);
                $no = 0;
                while ($row = $query2->fetch()) {
                    $no+=1;
                    echo"<tr class=rowcontent>
                <td id='no'>" . $no . "</td>
                <td id='index_" . $row['kode'] . "' value='" . $row['kode'] . "' align='center'>" . $row['kode'] . "</td>
                <td id='modul_" . $row['kode'] . "' value='" . $row['modul'] . "'>" . $row['modul'] . "</td>
                <td id='tentang_" . $row['kode'] . "' value='" . $row['tentang'] . "'>" . $row['tentang'] . "</td>
                <td><img onclick=\"detailHelp_en(event,'" . str_replace(" ", "", $row['kode']) . "','" . $row['modul'] . "');\" title=\"Detail Help\" class=\"resicon\" src=\"images/zoom.png\"></td></tr>";
                }
                echo"<tr class=rowheader><td colspan=5 align=center>
                " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jmlbrs . "<br />
                <button class=mybutton onclick=cariBast_en(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=cariBast_en(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
                ?>

            </tbody>
        </table>
    </fieldset>
</div>
<?php CLOSE_BOX() ?>
<?php
echo close_body();
?>