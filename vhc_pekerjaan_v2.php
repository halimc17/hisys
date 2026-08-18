<?php
    require_once('master_validation.php');
    include('lib/nangkoelib.php');
    include_once('lib/zLib.php');
    include('lib/zFunction.php');
    echo open_body();
    include('master_mainMenu.php');
    require_once('lib/zSelect2.php');
?>

<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zSearch.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script type="text/javascript" src="js/vhc_pekerjaan_v2.js?v=<?php echo time(); ?>"></script>
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
    $optTraksi=$optkodeorg=$optjeniskendaraan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

    #=Ambil kode organisasi
    $sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".getOrgDetail(2).") and kodeorganisasi in (select distinct induk from organisasi where tipe='TRAKSI') order by kodeorganisasi asc";
    $rOrg=fetchData($sOrg);
    foreach ($rOrg as $val) {
        $optkodeorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
    }

    #=Ambil kode kode traksi
    $strak="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'TRAKSI' and kodeorganisasi regexp '".str_replace(',','|',str_replace("'","",getOrgDetail(2)))."' order by induk, namaorganisasi ";
    $rtrak=fetchData($strak);
    foreach ($rtrak as $val) {
        $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$val['kodeorganisasi']."'");
        $d=$induk[$val['kodeorganisasi']];
        if($d!=$n){			
            $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
            $optTraksi.="<optgroup label='".$nmorg[$d]."'>";
        }
        $optTraksi.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
        $n=$d;
        if($d!=$n){
            $optTraksi.="</optgroup>";
        }
    }

    #=Ambil kode Jenis BBM
    $optjenisbbm="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sbrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang ='351'";
    $rbrg=fetchData($sbrg);
    foreach ($rbrg as $val) {
        $optjenisbbm.="<option value=".$val['kodebarang'].">".$val['kodebarang']."-".$val['namabarang']."</option>";
    }

    #=Ambil Kode kendaraan
    $str = "select jenisvhc,namajenisvhc,kelompokvhc from ".$dbname.".vhc_5jenisvhc where kelompokvhc != 'SC' order by kelompokvhc"; 
    $res = fetchdata($str);
    foreach($res as $bar){
        $d=$bar['kelompokvhc'];
        if($d!=$n){	
            $arrjenis = array(
                            'AB' => array('Alat Berat' => 'Heavy Equipment'),
                            'KD' => array('Kendaraan' => 'Vehicle'),
                            'MS' => array('Mesin' => 'Machinery')
                        );
            foreach ($arrjenis as $jns => $arrbhs) {
                if($jns == $d){
                    foreach ($arrbhs as $id => $en) {
                        if($_SESSION['language']!='EN'){
                            $optjeniskendaraan.="<optgroup label='".$id."'>";                
                        }else{
                            $optjeniskendaraan.="<optgroup label='".$en."'>";                
                        }
                    }
                }
            }
        }
        $optjeniskendaraan.="<option value=".$bar['jenisvhc'].">".$bar['namajenisvhc']."</option>";
        $n=$d;
        if($d!=$n){
            $optjeniskendaraan.="</optgroup>";
        }
    }

    #=Ambil kode kendaraan
    $optkodevhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sbrg="select kodevhc,nopol,detailvhc from ".$dbname.".vhc_5master where status ='1'";
    $rbrg=fetchData($sbrg);
    foreach ($rbrg as $val) {
        $optkodevhc.="<option value=".$val['kodevhc'].">".$val['kodevhc']." - ".($val['nopol'] != '' ? $val['nopol']." - ".$val['detailvhc'] : $val['detailvhc'])."</option>";
    }

    #= Satuan
    $arrOpt=array("KM","HM");
    foreach($arrOpt as $brs => $isi){
        @$optsatuan.="<option value=".$isi.">".$isi."</option>";
    }
	$optkontanan="<option value='%'>".$_SESSION['lang']['all']."</option>";
	$arrkontann=array(""=>"TIDAK","KONTAN"=>"YA");
	foreach($arrkontann as $lstStatus=>$vwStatus){
		$optkontanan.="<option value='".$lstStatus."'>".$vwStatus."</option>";
	}

    #= Mandor
    $optMandor="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sbrg="select * from ".$dbname.".vhc_5mandortraksi ";
    $rbrg=fetchData($sbrg);
    foreach ($rbrg as $val) {
        $optMandor.="<option value=".$val['karyawanid'].">[".getNik($val['karyawanid'])."] - ".getNamaKaryawan($val['karyawanid'])."</option>";
    }
    
    #= VHC
    $optjnsvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sbrg="select * from ".$dbname.".vhc_5jenisvhc ";
    $rbrg=fetchData($sbrg);
    foreach ($rbrg as $val) {
        $optjnsvhc.="<option value=".$val['jenisvhc'].">".$val['jenisvhc']." - ".$val['namajenisvhc']."</option>";
    }


    #- Ambil Pt Untuk yang gak disabled upah nya 
    $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TRDU' "; 
    $res = fetchdata($str);
    @$arrpt = explode(',', $res[0]['nilai']);

    $nonDisabled = '';
    if (in_array($_SESSION['empl']['kodeorganisasi'],$arrpt)) {
        $nonDisabled = '';
    }else{
        $nonDisabled = "disabled";
    }

    #- Ambil Pt Untuk hidden kolom helper
    $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TRDH' "; 
    $res = fetchdata($str);
    @$arrpt = explode(',', $res[0]['nilai']);

    $hiddenHelper = '';
    if (in_array($_SESSION['empl']['kodeorganisasi'],$arrpt)) {
        $hiddenHelper = "hidden";
    }else{
        $hiddenHelper = '';
    }
  
?>

<!------------------- HEADER untuk BUAT BARU, LIST DATA dan CARI ------------------->
<?php
	OPEN_BOX('','<span class=judul>'.getMenu('vhc_pekerjaan_v2').'</span>');
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
                                        <td>".$_SESSION['lang']['notransaksi']." </td>
                                        <td><input type=\"text\" id='txtCari' onkeyup='loaddata(0)' name='txtCari' style='width:130px' class=myinputtext /></td>
                                        <td>".$_SESSION['lang']['tanggal']."
                                            <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onchange='loaddata(0)' size=8 maxlength=10 readonly/> s.d 
                                            <input type=text class=myinputtext id=tgl_carisd onmousemove=setCalendar(this.id) onchange='loaddata(0)' size=8 maxlength=10 readonly/>
                                        </td>
                                        <td>".$_SESSION['lang']['kodevhc']." 
                                        <select id=kodevhc_cari class=select2 name=kodevhc_cari onchange='loaddata(0)' style=width:150px;>".$optkodevhc."</select></td><td>Kontanan <select id=kontanan_cari class=select2 onchange='loaddata(0)'>".$optkontanan."</select></td>
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
        echo"<div class=table-scroll style='height:60vh;'>
                <table cellspacing=1 class=sortable cellpadding=5 width=99%>
                    <thead>
                        <tr class=\"rowheader\">
                            <th align=center>".$_SESSION['lang']['nourut']."</td>
                            <th align=center>".$_SESSION['lang']['notransaksi']."</th>
                            <th align=center>".$_SESSION['lang']['jenisvch']."</th>
                            <th align=center>".$_SESSION['lang']['kodevhc']."</th>
                            <th align=center>".$_SESSION['lang']['nopol']."</th>
                            <th align=center>".$_SESSION['lang']['detail']."</th>
                            <th align=center>".$_SESSION['lang']['mandor']."</th>
                            <th align=center>".$_SESSION['lang']['operator']."</th>
                            <th align=center>".$_SESSION['lang']['helper']." 1</th>
                            <th align=center>".$_SESSION['lang']['helper']." 2</th>
                            <th align=center>".$_SESSION['lang']['helper']." 3</th>
                            <th align=center>".$_SESSION['lang']['tanggal']."</th>
                            <th align=center>".$_SESSION['lang']['vhc_jenis_bbm']."</th>
                            <th align=center style='width:40px'>".$_SESSION['lang']['vhc_jumlah_bbm']."</th>
							<th align=center>".$_SESSION['lang']['kontanan']."</th>
							<th align=center>".$_SESSION['lang']['createby']."</th>
							<th align=center>".$_SESSION['lang']['createtime']."</th>
                            <th align=center style='width:60px' colspan=4>".$_SESSION['lang']['action']."</th>
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
    echo"<fieldset style='float:left;'><legend>".$_SESSION['lang']['header']."</legend>
            <table cellspacing=1 border=0>
                <tr>
                    <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
                    <td><input type=text id=no_trans name=no_trans disabled=disabled class=myinputtext style=width:140px; /></td>

                    <td>".$_SESSION['lang']['kodeorg']."&nbsp;<font style='font-size:10px;color:blue;'>(1)</font></td><td>:</td>
                    <td><select id=kodeorg class=select2 name=kodeorg style=width:220px onchange=\"getsubunit()\">".$optkodeorg."</select></td>
                    
                    <td>".$_SESSION['lang']['kodetraksi']."&nbsp;<font style='font-size:10px;color:blue;'>(2)</font></td><td>:</td>
                    <td><select id=kodetraksi class=select2 name=kodetraksi style=width:130px; onchange=\"getjenisvhc()\">".$optTraksi."</select></td>
                    
                    <td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['kendaraan']."&nbsp;<font style='font-size:10px;color:blue;'>(3)</font></td><td>:</td>
                    <td><select id=jenisvhc class=select2 name=jenisvhc style=width:110px; onchange=\"getKodeVhc()\">".$optjnsvhc."</select></td>

                    <td>".$_SESSION['lang']['mandor']."&nbsp;<font style='font-size:10px;color:blue;'>(4)</font></td><td>:</td>
                    <td><select id=mandor class=select2 name=mandor style=width:130px;>".$optMandor."</select></td>

                </tr>
                <tr>
                
                    <td>".$_SESSION['lang']['tanggal']."&nbsp;<font style='font-size:10px;color:blue;'>(5)</font></td><td>:</td>
                    <td><input type=text class=myinputtext id=tglpekerjaan name=tglpekerjaan onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:140px; readonly/></td>
                    
                    <td>".$_SESSION['lang']['kodevhc']."&nbsp;<font style='font-size:10px;color:blue;'>(6)</font></td><td>:</td>
                    <td><select id=kodevhc class=select2 name=kodevhc style=width:220px;></select></td>
                    
                    <td>".$_SESSION['lang']['vhc_jenis_bbm']."&nbsp;<font style='font-size:10px;color:blue;'>(7)</font></td><td>:</td>
                    <td><select id=jenisbbm class=select2 name=jenisbbm style=width:130px;>".$optjenisbbm."</select></td>

                    <td>".$_SESSION['lang']['vhc_jumlah_bbm']."&nbsp;<font style='font-size:10px;color:blue;'>(8)</font></td><td>:</td>
                    <td><input type=text class=myinputtextnumber id=jmlh_bbm name=jmlh_bbm maxlength=10 value=0 onkeypress=\"return angka_doang(event);\" style=width:108px; /></td>

                    <td>".$_SESSION['lang']['kontanan']."&nbsp;<font style='font-size:10px;color:blue;'>(9)</font></td><td>:</td>
                    <td><input type='checkbox' id='kontanan' style='vertical-align:middle'/></td>
                    
                </tr>                
                <tr>
                    <td><td>
                    <td>
                        <button class=mybutton id=save_kepala name=save_kepala onclick=save_header()  disabled >".$_SESSION['lang']['save']."</button>
                        <button class=mybutton id=cancel_kepala name=cancel_kepala onclick=cancel_kepala_form() disabled >".$_SESSION['lang']['cancel']."</button>
                        <input type=hidden id=proses name=proses value=insert_header >	
                    </td>
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
    echo"<fieldset><legend>".$_SESSION['lang']['vhc_detail_pekerjaan']."</legend>";
    echo"<table cellspacing=1 cellpadding=1 border=0>

    <tr>
        <td>".$_SESSION['lang']['vhc_jenis_pekerjaan']."&nbsp;<font style='font-size:10px;color:blue;'>(1)</font></td>
        <td>:</td>
        <td colspan=4><select class=select2 id=jns_kerja name=jns_kerja onchange=getSatuan(this.value) style=width:255px; tabindex=1></select><input type=hidden name=old_jnskerja id=old_jnskerja />
        </td>
        
        <td>".$_SESSION['lang']['operator']."&nbsp;/&nbsp;".$_SESSION['lang']['supir']."<font style='font-size:10px;color:blue;'>(8)</font></td>
        <td>:</td>
        <td><select class=select2 id=kode_karyawan name=kode_karyawan style=width:154px; onchange=getUmr(); tabindex=8></select></select>
            <input type=number hidden id=kode_karyawan_old tabindex=11 name=kode_karyawan_old class=myinputtext style=width:150px; />
        </td>

        <td >".$_SESSION['lang']['helper']." 1 &nbsp;<font style='font-size:10px;color:blue;' tabindex=10>(10)</font></td>
        <td >:</td>
        <td >
            <select id=kode_helper tabindex=12 class=select2 name=kode_helper style=width:154px; onchange=getUmr();><option value=''></select></select>
            <input hidden type=text id=kode_helper_old tabindex=11 name=kode_helper_old class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0 />
        </td>

        <td ".$hiddenHelper.">".$_SESSION['lang']['helper']." 2 &nbsp;<font style='font-size:10px;color:blue;' tabindex=11>(11)</font></td>
        <td ".$hiddenHelper.">:</td>
        <td ".$hiddenHelper.">
            <select id=kode_helper2 tabindex=12 class=select2 name=kode_helper2 style=width:154px; onchange=getUmr();><option value=''></select></select>
            <input hidden type=text id=kode_helper_old2 tabindex=11 name=kode_helper_old2 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0 />
        </td>

    </tr>

    <tr>
        <td>".$_SESSION['lang']['lokasi']."&nbsp;<font style='font-size:10px;color:blue;'>(2)</font></td>
        <td>:</td>
        <td colspan=4><select  class=select2 id=lokasi_kerja name=lokasi_kerja  style=width:255px; onchange=\"getBlok('','')\" tabindex=2><option value=''>".$_SESSION['lang']['pilihdata']."</option></select> <input type=hidden name=old_lokkerja id=old_lokkerja /></td>

        <td>".$_SESSION['lang']['upahkerja']." Operator</td>
        <td>:</td>
        <td>
            <input type=text id=uphOprt name=uphOprt  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' ".$nonDisabled."/>
            <input type=text hidden id=uphOprt_libur name=uphOprt_libur  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' />
            <input type=text hidden id=uphOprt_old name=uphOprt_old  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' />
        </td>

        <td >".$_SESSION['lang']['upahkerja']." ".$_SESSION['lang']['helper']." 1</td>
        <td >:</td>
        <td >
            <input type=text id=uphHelp name=uphHelp  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' />
            <input type=text hidden id=uphHelp_libur name=uphHelp_libur  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  />
            <input type=text hidden id=uphHelp_old name=uphHelp_old  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' />
        </td>

        <td ".$hiddenHelper.">".$_SESSION['lang']['upahkerja']." ".$_SESSION['lang']['helper']." 2</td>
        <td ".$hiddenHelper.">:</td>
        <td ".$hiddenHelper.">
            <input type=text id=uphHelp2 name=uphHelp2  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' />
            <input type=text hidden id=uphHelp_libur2 name=uphHelp_libur2  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  />
            <input type=text hidden id=uphHelp_old2 name=uphHelp_old2  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' />
        </td>

    </tr>

    <tr>
        <td>".$_SESSION['lang']['blok']."&nbsp;<font style='font-size:10px;color:blue;'>(3)</font></td>
        <td>:</td>
        <td colspan=4><select id=blok  class=select2 name=blok style=width:255px;  tabindex=3><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
        
        <td>".$_SESSION['lang']['premi']." (Rp) ".$_SESSION['lang']['operator']."</td>
        <td>:</td>
        <td>
            <input disabled type=text id=prmiOprt tabindex=11 name=prmiOprt class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" value=0 />&nbsp;<input type='checkbox' id='checklembur' name='checklembur' onclick=\"adalembur();\"></td>
            <input hidden type=text id=prmiOprt_old tabindex=11 name=prmiOprt_old class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" value=0 />
        </td>

        <td >".$_SESSION['lang']['premi']." (Rp) ".$_SESSION['lang']['helper']." 1</td>
        <td >:</td>
        <td >
            <input  type=text id=prmiHelp tabindex=14 name=prmiHelp class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" value=0 />
            <input hidden type=text id=prmiHelp_old tabindex=11 name=prmiHelp_old class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" value=0 />
        </td>

        <td ".$hiddenHelper.">".$_SESSION['lang']['premi']." (Rp) ".$_SESSION['lang']['helper']." 2</td>
        <td ".$hiddenHelper.">:</td>
        <td ".$hiddenHelper.">
            <input  type=text id=prmiHelp2 tabindex=14 name=prmiHelp2 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" />
            <input hidden type=text id=prmiHelp_old2 tabindex=11 name=prmiHelp_old2 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" value=0 />
        </td>

        <td hidden>*Jika pekerjaan dilakukan di Kebun (Obligatory if activity location on estate) &nbsp; => Harus di isi untuk karyawan internal (Obligatory if internal operator used)<td>
        <td hidden> <input type=hidden name=old_blok id=old_blok /></td>
    </tr>

    <tr hidden>
        <td>".$_SESSION['lang']['department']."</td>
        <td>:</td>
        <td colspan=4><select class=select2 id=dept name=dept style=width:232px;>
        <img id='dept' onclick=z.elSearch('dept',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
        </td>
    </tr>

    <tr hidden>
        <td>".$_SESSION['lang']['segment']."</td>
        <td>:</td>
        <td colspan=500><input type=hidden name=oldSegment id=oldSegment />".makeElement('kodesegment','searchSegment')."</td>
    </tr>

    <tr>
        <td valign=bottom>".$_SESSION['lang']['jumlahrit']."&nbsp;<font style='font-size:10px;color:blue;'>(4)</font></td>
        <td valign=middle>:</td>
        <td valign=bottom><input type=text class=myinputtextnumber id=jmlh_rit name=jmlh_rit maxlength=6 onkeyup=\"getPremi();\" onclick=\"this.select();\" onkeypress=\"return angka_doang(event);\" style=width:85px;  tabindex=4 value=1></td>
        
        <td valign=bottom>".$_SESSION['lang']['prestasi']."&nbsp;<font style='font-size:10px;color:blue;'>(5)</font></td>
        <td valign=middle>:</td>
        <td valign=bottom><input type=text class=myinputtextnumber id=brt_muatan name=brt_muatan maxlength=6  onkeyup=\"getUmr();\" onkeypress=\"return angka_doang(event);\" onclick=\"this.select();\" style=width:75px;  tabindex=5/>&nbsp;<span id='satuan'></span>
        <input hidden type=text class=myinputtextnumber id=oldbrt_muatan name=oldbrt_muatan maxlength=6 onkeypress=\"return angka_doang(event);\" style=width:80px; />&nbsp;<span id='satuan'>
        </td>

        <td ".$hiddenHelper." >".$_SESSION['lang']['premi']." Tambahan (Rp) ".$_SESSION['lang']['operator']."</td>
        <td ".$hiddenHelper." >:</td>
        <td ".$hiddenHelper." >
            <input type=text id=prmiOprtTambahan tabindex=11 name=prmiOprtTambahan class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" value=0 /></td>
        </td>

        <td hidden>".$_SESSION['lang']['premi']."</td>
        <td hidden>:</td>
        <td hidden><input type=text id=prmiOprt onfocus=getPremi();  name=prmiOprt class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0 /></td>
    </tr>


    <tr>
        <td>".$_SESSION['lang']['vhc_kmhm_awal']."&nbsp;<font style='font-size:10px;color:blue;'>(6)</font></td>
        <td>:</td>
        <td><input type=text onkeyup=getjumlah('awal'); class=myinputtextnumber  onkeydown=\"getPremi();\" id=kmhm_awal name=kmhm_awal maxlength=8 onkeypress=\"return angka_doang(event);\" style=width:85px;  tabindex=6/></td>

        <td>".$_SESSION['lang']['akhir']."&nbsp;<font style='font-size:10px;color:blue;'>(7)</font></td>
        <td>:</td>
        <td><input type=text onkeyup=getjumlah('akhir'); class=myinputtextnumber  onkeydown=\"getPremi();\" id=kmhm_akhir name=kmhm_akhir maxlength=8  onkeypress=\"return angka_doang(event);\" onclick=\"this.select();\" style=width:75px;  tabindex=7/></td>

        <td>".$_SESSION['lang']['rupiahpenalty']."</td>
        <td valign=middle>:</td>
        <td valign=middle><input type=text id=pnltyOprt name=pnltyOprt class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' maxlength=8 value=0 disabled/>&nbsp;<input type='checkbox' id=checkdenda name='checkdenda' onclick='adadenda();'></td>
    </tr>

    <tr>
        <td valign=top>".$_SESSION['lang']['satuan']."</td>
        <td valign=top>:</td>
        <td valign=top><select id=stn class=select2 name=stn style=width:92px;>".$optsatuan."</select></td>
        
        <td valign=top>".$_SESSION['lang']['jumlah']."</td>
        <td valign=top>:</td>
        <td valign=top><input class=myinputtextnumber onkeyup=getjumlah('jumlah'); onclick=\"this.select();\" id=jlhhm name=jlhhm style=width:75px;></td>

        <td valign=top rowspan=2>".$_SESSION['lang']['keterangan']."&nbsp;<font style='font-size:10px;color:blue;'>(9)</font></td>
        <td valign=top rowspan=2>:</td>
        <td valign=top rowspan=2 rowspan=3>
        <textarea rows='3' maxlength='200' id='ket' type='text' onkeypress='return tanpa_kutip(event)' style='width:136px;height:22px' tabindex=9></textarea>
        </td>

        <td hidden>".$_SESSION['lang']['biaya']." Rp</td>
        <td hidden>:</td>
        <td hidden><input type=text class=myinputtextnumber id=biaya name=biaya maxlength=45 onkeypress=\"return angka_doang(event);\" style=width:80px; /></td>
    </tr>

    <tr>
        <td hidden>".$_SESSION['lang']['keterangan']."</td>
        <td hidden>:</td>
        <td hidden>
            <input type=text class=myinputtext id=ketOprt name=ket maxlength=45 onkeypress=\"return tanpa_kutip(event);\" style=width:150px; />
        </td>
    </tr>
    <tr>    
        <td valign=top rowspan=1 hidden>".$_SESSION['lang']['helper']." 3 &nbsp;<font style='font-size:10px;color:blue;' tabindex=12>(12)</font></td>
        <td valign=top rowspan=1 hidden>:</td>
        <td valign=top rowspan=1 hidden>
            <select id=kode_helper3 tabindex=12 class=select2 name=kode_helper3 style=width:154px; onchange=getUmr();><option value=''></select></select>
            <input hidden type=text id=kode_helper_old3 tabindex=11 name=kode_helper_old3 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0 />
        </td>

        <td valign=top rowspan=1 hidden>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['helper']." 3</td>
        <td valign=top rowspan=1 hidden>:</td>
        <td valign=top rowspan=1 hidden>
            <input disabled type=text id=prmiHelp3 tabindex=14 name=prmiHelp3 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\"/>
            <input hidden type=text id=prmiHelp_old3 tabindex=11 name=prmiHelp_old3 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onclick=\"this.select();\" value=0 />
        </td>
    </tr>
    <tr>    
        <td valign=top hidden>".$_SESSION['lang']['upah']." ".$_SESSION['lang']['helper']." 3</td>
        <td valign=top hidden>:</td>
        <td valign=top hidden>
            <input type=text id=uphHelp3 name=uphHelp3  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' disabled/>
            <input type=text hidden id=uphHelp_libur3 name=uphHelp_libur3  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  />
            <input type=text hidden id=uphHelp_old3 name=uphHelp_old3  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' />
        </td>
    </tr>
    <tr>
        <td><td>
        <td colspan=6>	
            <input type=hidden id=proses_pekerjaan name=proses_pekerjaan value=insert_pekerjaan />
            <input type=hidden id=jnsstn value= />
            <input type=hidden id=jnsstnhelp value= />
            <input type=hidden id=basisborong value= />
            <input type=hidden id=basisboronghelp value= />
            <input type=hidden id=lebihdarisatupekerjaan value= />
            <button class=mybutton onclick=save_pekerjaan() >".$_SESSION['lang']['save']."</button>
            <button class=mybutton onclick=bersih_form_pekerjaan() >".$_SESSION['lang']['cancel']."</button>
            <button class=mybutton title=\"Refresh Data Tersimpan\" onclick=loaddetail() >Refresh</button>
        </td>

    </table>";

    echo"</fieldset>";
    echo"<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend><table cellpadding=5 cellspacing=1 border=0  class=sortable>
            <thead>
            <tr class=\"rowheader\">
            <th align=center>No.</th>
            <th align=center>".$_SESSION['lang']['notransaksi']."</th>
            <th align=center>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</th>
            <th align=center>".$_SESSION['lang']['alokasibiaya']."</th>
            <th align=center style='display:none'>".$_SESSION['lang']['department']."</th>
            <th align=center style='display:none'>".$_SESSION['lang']['segment']."</th>
            <th align=center>".$_SESSION['lang']['jumlahrit']."</th>
            <th align=center>".$_SESSION['lang']['prestasi']."</th>
            <th align=center>".$_SESSION['lang']['vhc_kmhm_awal']."</th>
            <th align=center>".$_SESSION['lang']['vhc_kmhm_akhir']."</th>
            <th align=center>".$_SESSION['lang']['jumlah']."</th>
            <th align=center>".$_SESSION['lang']['satuan']."</th>
            <th align=center>".$_SESSION['lang']['operator']."/<br>".$_SESSION['lang']['supir']."</th>
            <th align=center>".$_SESSION['lang']['upahkerja']."</th>
            <th align=center>".$_SESSION['lang']['upahpremi']."</th>
            <th align=center ".$hiddenHelper.">".$_SESSION['lang']['upahpremi']." Tambahan</th>
            <th align=center>".$_SESSION['lang']['rupiahpenalty']."</th>
            <th align=center >".$_SESSION['lang']['helper']." 1</th>
            <th align=center >".$_SESSION['lang']['upah']." ".$_SESSION['lang']['helper']." 1</th>
            <th align=center >".$_SESSION['lang']['premi']." ".$_SESSION['lang']['helper']." 1</th>
            <th align=center ".$hiddenHelper.">".$_SESSION['lang']['helper']." 2</th>
            <th align=center ".$hiddenHelper.">".$_SESSION['lang']['upah']." ".$_SESSION['lang']['helper']." 2</th>
            <th align=center ".$hiddenHelper.">".$_SESSION['lang']['premi']." ".$_SESSION['lang']['helper']." 2</th>
            <th align=center style='display:none'>".$_SESSION['lang']['helper']." 3</th>
            <th align=center style='display:none'>".$_SESSION['lang']['upah']." ".$_SESSION['lang']['helper']." 3</th>
            <th align=center style='display:none'>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['helper']." 3</th>
            <th align=center style='display:none'>".$_SESSION['lang']['biaya']." (Rp.)</th>
            <th align=center style='display:none'>".$_SESSION['lang']['vhc_posisi']."</th>
            <th align=center style='display:none'>".$_SESSION['lang']['upahkerja']."</th>
            <th align=center style='display:none'>".$_SESSION['lang']['rupiahpenalty']."</th>
            <th align=center >".$_SESSION['lang']['keterangan']."</th>
            <th align=center colspan=2>Action</th>
            </tr></thead><tbody id=containdetail>
            ";
    echo"</tbody></table></fieldset>";
    CLOSE_BOX();
    echo "</div>";
    echo close_body();
?>