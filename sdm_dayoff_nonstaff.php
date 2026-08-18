<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('sdm_dayoff_nonstaff')."</span>"); //1 O
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<!-- <script type="text/javascript" src="js/sdm_dayoff.js?VER=1.6" /></script> -->
<script language=javascript1.2 src='js/sdm_dayoff_nonstaff.js?v=<?php echo time(); ?>'></script>
<style>
    .truncate-keterangan {
        width: 250px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all .2s linear;
        white-space: nowrap;
    }

    .truncate-keterangan:focus, .truncate-keterangan:hover {
        color:transparent;
    }
    .truncate-keterangan:focus:after,.truncate-keterangan:hover:after{
        content:attr(data-text);
        overflow: visible;
        text-overflow: inherit;
        background: #fff;
        position: absolute;
        left:auto;
        top:auto;
        width: auto;
        max-width: 20rem;
        border: 1px solid #eaebec;
        padding: 0 .5rem;
        box-shadow: 0 2px 4px 0 rgba(0,0,0,.28);
        white-space: normal;
        word-wrap: break-word;
        display:block;
        color:black;
        margin-top:-1.25rem;
    }

</style>
<?php

$jnsapp = "DOFNS";

$optkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where
        (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar' and tipekaryawan in ('1','2','3','6') 
        and lokasitugas='".$_SESSION['empl']['lokasitugas']."' order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

//FORM CARI
echo"<table>
    <tr valign=middle>
        <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
                    <td><fieldset>
                            <legend>".$_SESSION['lang']['find']."</legend>
                            <table>
                                <tr>
                                    <td>".$_SESSION['lang']['tanggal']." Pengajuan</td>
                                    <td>:</td>
                                    <td><input type='text' style='width:145px;' class='myinputtext' id='tglpengajuansch' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' onkeypress=\"return false;\" value='' readonly></td>

                                    <td>&nbsp&nbsp".$_SESSION['lang']['tanggal']." Masuk Hari Libur</td>  
                                    <td>:</td>
                                    <td><input type='text' style='width:145px;' class='myinputtext' id='tgldarisch' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' onkeypress=\"return false;\" value='' readonly></td>                                      
                                </tr>
                                <tr>
                                    <td>".$_SESSION['lang']['notransaksi']."</td>
                                    <td>:</td>
                                    <td><input type='text' style='width:145px' class='myinputtext' id='notransaksi' /></td>

                                    <td>&nbsp&nbspJumlah Day Off (".$_SESSION['lang']['hari'].")</td>
                                    <td>:</td>
                                    <td><input type=text id=crjmldayoff onkeypress='return tanpa_kutip(event)' class=myinputtext style=width:146px;></td>
                                </tr>
                                <tr>
                                    <td colspan=2></td>
                                    <td>
                                        <button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
                                        <button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
                                    </td>
                                </tr>
                            </table>
                        </fieldset></td> 
                    </tr>
            </table>";
CLOSE_BOX();

//LIST DATA
OPEN_BOX();
echo"<div id=listData>";
// echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['namakaryawan']."</td>";
echo"<td>".$_SESSION['lang']['tanggal']." Pengajuan</td>";
echo"<td>".$_SESSION['lang']['tanggal']." Masuk Hari Libur</td>";
echo"<td>".$_SESSION['lang']['tanggal']." Masa Berlaku</td>";
echo"<td>Jumlah Day Off<br>(".$_SESSION['lang']['hari'].")</td>";
echo"<td>Keterangan</td>";
$countApp = getCountApproval('DOFNS');
for($i=1;$i<=$countApp;$i++){
echo"<td>Persetujuan Ke - ".$i."</td>";
}
echo"<td colspan=3>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0);</script>";

echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";

//FORM HEADER
echo"<div id=formInput style=display:none;>";
echo"<fieldset style=><legend>".$_SESSION['lang']['form']."</legend>
    <table border=0 >";

echo"<tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>
		<td>
        <input type='text' class='myinputtext' style='width:145px;' disabled id='notransaksi2' name='notransaksi' onkeypress='return angka_doang(event);' maxlength='5'/>
        </td>
    <tr>"; 
echo"<tr>
        <td>".$_SESSION['lang']['namakaryawan']."</td>
        <td>:</td>
        <td>
        <select id='karyawanid' onchange=getatasan(); style='width:250px'>".$optkaryawan."</select>
            <img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
        </td>
    <tr>"; 
echo"<tr>
        <td>".$_SESSION['lang']['tanggal']." Pengajuan</td>
		<td>:</td>
		<td>
			<input type='text' style='width:145px;' class='myinputtext' id='tglpengajuan' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' onkeypress=\"return false;\" value='".date('d-m-Y')."' readonly disabled>
        </td>
    <tr>"; 
echo"<tr>
        <td>".$_SESSION['lang']['tanggal']." Masuk Hari Libur</td>
        <td>:</td>
        <td>
        <input autocomplete=off type='text' class='myinputtext style='width:145px;' id='tglAwal' onchange='getjmldayoff()' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:145px;' value='".date('d-m-Y')."' readonly />
        </td>
    </tr>";
echo"<tr>
		<td>Jumlah Day Off (".$_SESSION['lang']['hari'].")</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:50px;' id='jmldayoff' name='keperluan' onkeypress='return angka_doang(event);' maxlength='5' value='1' disabled/>
		</td>
    </tr>
    <tr>
        <td>Keterangan <span style='color:red'>*</span></td>
        <td>:</td>
        <td>
            <textarea id='keterangan' cols='40' rows='2' style='width:300px;'></textarea>
        </td>
    </tr>
    <tbody id='trapproval'>";    
    ## APPROVAL DINAMIS SESUAI SETUP##
    // echo "<pre>"; print_r($_SESSION['empl']); exit;
    $countApp = getCountApproval($jnsapp,$_SESSION['empl']['lokasitugas'], $_SESSION['empl']['bagian']);
    for($i=1;$i<=$countApp;$i++)
    {
        $optApp="";
        $arrlistapp = listApprove($i,$jnsapp,$_SESSION['empl']['lokasitugas'], $_SESSION['empl']['bagian']);
        foreach($arrlistapp as $key=>$val)
        {
            $optApp.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
        }
        echo"<tr>
            <td>".$_SESSION['lang']['persetujuan']." ".$i."</td>
            <td>:</td>
            <td>
                <select id='persetujuan".$i."'>".$optApp."</select>
            </td>
        </tr>";
    }
echo"</tbody><tr>
        <td></td><td></td>
        <input type=hidden class=myinputtext style=width:200px id=metode value='update'>
        <td><button id=tombolsave class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
        <button id=tomboledit hidden class=mybutton onclick=saveData()>" . $_SESSION['lang']['edit'] . "</button>
            <button class=mybutton onclick=clearForm1()>".$_SESSION['lang']['cancel']."</button>
        </td>
     </tr>
     <input type=hidden id=method value='insertht'/>
     </table>";
echo"</fieldset>"; 
echo"</div>";
echo"<br>";

CLOSE_BOX();
echo close_body(); ?>
