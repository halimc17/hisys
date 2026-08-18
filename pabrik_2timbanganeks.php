<?php
    require_once('master_validation.php');
    include('lib/nangkoelib.php');
    include_once('lib/zLib.php');
    echo open_body();
    include('master_mainMenu.php');
    require_once('lib/zSelect2.php');
    OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2timbanganeks').'</span>');
?>

<script language="javascript" src="js/zSelect2.js?ver=<?= time(); ?>"></script>
<script language=javascript src='js/pabrik_2timbanganeks.js?v=<?php echo time(); ?>'></script>

<?php
    $kdPbrk = $_SESSION['empl']['lokasitugas'];
    $optCust="<option value=''>".$_SESSION['lang']['all']."</option>";
    
    $sBrg="SELECT namabarang,kodebarang FROM $dbname.log_5masterbarang WHERE (`kelompokbarang`='400' OR `kelompokbarang`='401') AND inisial='TBS' ORDER BY kelompokbarang ASC";
    $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
    $qBrg->setFetchMode(PDO::FETCH_ASSOC);
    while($rBrg=$qBrg->fetch())
    {
        @$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
    }

    $sPbrik="SELECT kodeorganisasi,namaorganisasi FROM $dbname.organisasi WHERE tipe IN ('PABRIK')";
    $qPabrik=$owlPDO->query($sPbrik) or die(print " Gagal: ".PDOException::getMessage());
    $qPabrik->setFetchMode(PDO::FETCH_ASSOC);
    while($rPabrik=$qPabrik->fetch()){
        @$optPabrik.="<option value=".$rPabrik['kodeorganisasi']." ".($rPabrik['kodeorganisasi']==$kdPbrk?'selected':'').">".$rPabrik['namaorganisasi']."</option>";
    }

    # Vendor
    $whrtipe = "SELECT supplierid FROM {$dbname}.log_5supkelompok WHERE tipe LIKE '%SUPPLIERTBS%'";
    $sql = selectQuery($dbname,"log_5supplier","*","supplierid IN (".$whrtipe.") ORDER BY supplierid DESC");
    $res = fetchData($sql);
    foreach($res as $row):
        $optCust .= "<option value='".$row['supplierid']."'>".$row['namasupplier']."</option>";
    endforeach;
?>
<?php
    echo"
    <table>
	    <tr>
		    <td style='vertical-align:top'>
                <fieldset>
                    <legend>Timbangan Eksternal</legend>
                    <table>
                        <tr>
                            <td>".$_SESSION['lang']['pabrik']."</td>
                            <td>:</td>
                            <td>
                                <select class=select2 id=kdpabrik1 style=width:150px>".$optPabrik."</select>
                            </td>
                            <td>".$_SESSION['lang']['namabarang']."</td>
                            <td>:</td>
                            <td>
                                <select class=select2 id=kdbrg1 style=width:150px>".$optBrg."</select>
                            </td>
                            <td>".$_SESSION['lang']['vendor']."</td>
                            <td>:</td>
                            <td>
                                <select class=select2 id=cust1 style=width:150px>".$optCust."</select>
                            </td>
                            <td>".$_SESSION['lang']['tanggal']."</td>
                            <td>:</td>
                            <td>
                                <input type=text class=myinputtext id=tgltrans1 onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:70px size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
                                <input type=text class=myinputtext id=tgltrans2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:70px size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
                            </td>
                            
                            <td colspan=2></td>
                            <td>
                                <button class=mybutton onclick=preview1('html')>".$_SESSION['lang']['preview']."</button>
                                <button class=mybutton onclick=preview1('excel')>".$_SESSION['lang']['excel']."</button>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>";

    CLOSE_BOX();
    OPEN_BOX();

    echo "
        <div id='contain' style='height:60vh!important;'>
        </div>";
    CLOSE_BOX();
    echo close_body();
?>