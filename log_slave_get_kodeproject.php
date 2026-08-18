<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');

$kd_bag = $_POST['rkd_bag'];
if ((isset($_POST['txtfind3'])) != '') {
    $tipeOrg = fetchData(selectQuery($dbname, "organisasi", "tipe", "kodeorganisasi='{$kd_bag}'"))[0]['tipe'];
    $kodePt = makeOption($dbname, "organisasi", "kodeorganisasi,induk", "kodeorganisasi='{$kd_bag}'")[$kd_bag];
    $whereKodeorg = " AND kodeorg='{$kd_bag}'";
    $akses = [
        'HOLDING' => [
            'KEBUN',
            'PABRIK',
            'KANWIL',
            'HOLDING'
        ],
        'KANWIL' => [
            'KEBUN',
            'PABRIK',
            'KANWIL'
        ]
    ];
    if ($tipeOrg == "HOLDING" || $tipeOrg == "KANWIL") {
        $listAkses = "'" . implode("','", $akses[$tipeOrg]) . "'";
        $whereKodeorg = " AND kodeorg IN (SELECT kodeorganisasi FROM {$dbname}.organisasi WHERE tipe IN ('KEBUN','PABRIK','KANWIL','HOLDING') AND induk='{$kodePt}')";
    }

    $txtfind = $_POST['txtfind3'];
    $str = "select * from " . $dbname . ".project where  posting='0'  {$whereKodeorg} and statuspersetujuan!='9' and (kode like '%" . $txtfind . "%' or nama like '%" . $txtfind . "%')";
    // exit('error'.$str);
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    echo "
        <fieldset> 
        <legend>Result</legend>
        <div style=\"overflow:auto;height:50vh \" >
        <table class=sortable cellspacing=1 cellpadding=5  border=0>
            <thead>
                <tr class=rowheader>
                    <th>No.</th>
                    <th>" . $_SESSION['lang']['kode'] . "</th>
                    <th>" . $_SESSION['lang']['nama'] . "</th>
                    <th>" . $_SESSION['lang']['kodeorg'] . "</th>
                    
                    </tr>
                    </thead>
                    <tbody>";
    //			$no=0;	 
    while ($bar = $res->fetch()) {
        @$no += 1;
        echo "<tr class=rowcontent style='cursor:pointer;' onclick=\"setpjct('" . $bar->kode . "')\" title='Click' >
                            <td align=center>" . $no . "</td>
                            <td>" . $bar->kode . "</td>
                            <td>" . $bar->nama . "</td>
                            <td>" . $bar->kodeorg . "</td>
                    
                            </tr>";
    }
    echo "</tbody>
                    <tfoot>
                    </tfoot>
                    </table></div></fieldset>";
}
