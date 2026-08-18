<?php
    require_once('master_validation.php');
    include('lib/nangkoelib.php');
    include_once('lib/zLib.php');
    include('lib/zFunction.php');
    echo open_body();
    include('master_mainMenu.php');
    require_once('lib/zSelect2.php');
    if(empty(getOrgDetail(13))){
        $rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
        exit($rusak);
    }
    if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
        $rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
        exit($rusak);
    }
?>

<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zSearch.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script type="text/javascript" src="js/pabrik_gradingtbs.js?v=<?php echo time(); ?>"></script>
<script>
    dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
    $(document).ready(function() {
        $('.select2').select2({
            dropdownAutoWidth:true
        });
    });
</script>

<!----------------------------------- Deklarasi ------------------------------------>
<?php
    $optblok = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $query = selectQuery($dbname,'setup_blok','*',"indukblok NOT REGEXP '^(MHA|DMA|PPP)'");
    $hasil = fetchData($query);
    foreach ($hasil as $h) {
        $optblok.="<option value='".$h['indukblok']."'>".getIndukBlok($h['indukblok'])." [".getNamaOrg(substr($h['indukblok'],0,4))."]  [".getNamaOrg(substr($h['indukblok'],0,6))."] [".getDataIndukBlok($h['indukblok'],'tahuntanam')."]</option>";
    }
?>

<!------------------- HEADER untuk BUAT BARU, LIST DATA dan CARI ------------------->
<?php
	OPEN_BOX('','<span class=judul>'.getMenu('pabrik_gradingtbs').'</span>');
	echo"<div>";
	echo   "<table cellspacing=1 border=0>
				<tbody>
					<tr valign=middle>
						<td style=width:100px;cursor:pointer; onclick=\"formbaru();\" align=center>
							<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>
							".$_SESSION['lang']['new']."
						</td>
						<td style=width:100px;cursor:pointer; onclick=\"displayList();\" align=center>
							<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>
							".$_SESSION['lang']['list']."
							<td>
						</td>
						<td id=listcari style='display:block'>
                            <fieldset style='width:auto;'>
                                <legend>".$_SESSION['lang']['find']." Data</legend>
                                <table cellspacing=\"1\" border=\"0\">
                                    <tr>
                                        <td>".$_SESSION['lang']['noTiket']." </td>
                                        <td><input type=\"text\" id='txtCari' onkeyup='loaddata(0)' name='txtCari' style='width:130px' class=myinputtext /></td>
                                        <td>".$_SESSION['lang']['tanggal']."
                                            <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onchange='loaddata(0)' size=8 maxlength=10 readonly/> s.d 
                                            <input type=text class=myinputtext id=tgl_carisd onmousemove=setCalendar(this.id) onchange='loaddata(0)' size=8 maxlength=10 readonly/>
                                        </td>
                                        <td><button class=mybutton  onclick=batalcariDataTransaksi()>".$_SESSION['lang']['cancel']."</button></td>
                                    </tr>
                                </table>
                            </fieldset>
						</td>
					</tr>
				</tbody>
			</table>";
	echo"</div>";
	CLOSE_BOX();
?>

<!----------------------------------- LIST DATA ------------------------------------>
<?php
    echo"<div id=listData>";
    OPEN_BOX();
        echo"<div style='height:65vh;'>
                <table cellspacing=1 class=sortable cellpadding=5>
                    <thead>
                        <tr class=\"rowheader\">
                            <th align=center>".$_SESSION['lang']['nourut']."</td>
                            <th align=center>".$_SESSION['lang']['noTiket']."</th>
                            <th align=center>".$_SESSION['lang']['tanggal']."</th>
                            <th align=center>".$_SESSION['lang']['kodevhc']."</th>
                            <th align=center hidden>".$_SESSION['lang']['blok']."</th>
							<th align=center>".$_SESSION['lang']['createby']."</th>
							<th align=center>".$_SESSION['lang']['createtime']."</th>
                            <th align=center style='width:60px' colspan=3>".$_SESSION['lang']['action']."</th>
                        </tr>
                    </thead>
                    <tbody id='contain'><script>loaddata(0)</script></tbody>
                    <tfoot id='containfoot'></tfoot>
                </table>
                </div>";
    CLOSE_BOX();
    echo "</div>";
?>

<!------------------------------ Buat Baru Pekerjaan ------------------------------->
<?php
	echo "<div id=formnew style='display:none'>";
    OPEN_BOX();
    echo"<fieldset><legend>".$_SESSION['lang']['header']."</legend>
            <table cellspacing=0 cellpadding=4 border=0>
                <tr class=rowcontent>
                    <td>".$_SESSION['lang']['pabrik']."</td><td>:</td>
                    <td><select class=select2 id=kodeorg name=kodeorg style=width:255px;><option selected value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."</option></select></td>
                    <td>".$_SESSION['lang']['tanggal']."</td><td>:</td>
                    <td><input type=text class=myinputtext id=tanggal name=tanggal onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:70px; onchange='getnotiket()' readonly/></td>&nbsp;
                    <td>".$_SESSION['lang']['noTiket']."</td><td>:</td>
                    <td><select class=select2 id=notiket name=notiket style=width:200px;><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
                    <td hidden>".$_SESSION['lang']['blok']."</td><td hidden>:</td>
                    <td hidden><select class=select2 multiple id=blok name=blokname style=width:250px;>".$optblok."</select></td>
                    &nbsp;&nbsp;
                    <td>
                    <button class=mybutton id=save_kepala name=save_kepala onclick=save_header()  disabled >".$_SESSION['lang']['preview']."</button>
                    <input type=hidden id=proses name=proses value=insert_header >	</td>
                </tr>
            </table>
        </fieldset>";
    CLOSE_BOX();
    echo"</div>";
?>

<!-------------------------------- Detail Pekerjaan -------------------------------->
<?php
    echo "<div id=detail style='display:none;'>";
    OPEN_BOX();
    echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>
        <div style='float:left;padding-right:100px'>
        <table cellpadding=5 cellspacing=1 border=0  class=sortable>
            <thead>
                <tr class=\"rowheader\">
                    <th align=center>No.</th>
                    <th align=center colspan=3>Kriteria Grading</th>
                </tr>
            </thead>
            <tbody id=containdetail>
            </tbody>
        </table>
        </div>
        <div>
        <table cellpadding=5 cellspacing=1 border=0  class=sortable>
            <thead>
                <tr class=\"rowheader\">
                    <th align=center colspan=4>KRITERIA TBS KALIBRASI</th>
                </tr>
            </thead>
            <tbody id=containdetail2>
            </tbody>
        </table>
        </div>
        </fieldset>
        <div style='padding-top:10px'>
            <input type=hidden id=proses_pekerjaan name=proses_pekerjaan value=insert_pekerjaan />
            <button class=mybutton  onclick=save_detail()>".$_SESSION['lang']['save']."</button>
        </div>";
    CLOSE_BOX();
    echo "</div>";
    echo close_body();
?>