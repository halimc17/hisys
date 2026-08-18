function add_new_data(){
	document.getElementById('inputform').style.display = "block";
	document.getElementById('loaddataform').style.display = "none";
	
}

function displayList(){
	document.getElementById('inputform').style.display = "none";
	document.getElementById('loaddataform').style.display = "block";
	document.getElementById('tglcr1').value = "";
	document.getElementById('tglcr2').value = "";
	loaddata();
}


function getlog(kodeorg, tanggal) {

    // content = "<div id=listgetlog style=\"height:500px;width:800px;overflow:scroll;\"></div>";
    // // title = title + ' Kontraktor :';
    // title = ' data :';
    // width = '800';
    // height = '500';
    // ev = 'event';
    // showDialog1(title, content, width, height, ev);

    param = 'method=getlog' + '&kodeorg=' + kodeorg + '&tanggal=' + tanggal;
    tujuan = 'pabrik_slave_produksi.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('listgetlog').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(kodeorg, tanggal, sisatbskemarin, tbsmasuk, tbsdiolah, sisahariini, oer, ffa,
    kadarkotoran, kadarair, oerpk, ffapk, kadarkotoranpk, kadarairpk, dobi,
    usbbefore, usbafter, oildiluted, oilin, oilinheavy, caco,
    hydrocyclone, fruitineb, ebstalk, fibre, nut, effluent, soliddecanter,
    fruitinebker, cyclone, ltds, claybath, lorirestanhi, cangkang, condensatesterilizer,
    tbsmasuknetto, tbsdiolahnetto, sisatbskemarinnetto, sisanetto, keterangan, cpoonsistem, loadinggudang) {
    var re = /<br *\/?>/gi;
    //document.getElementById('edit').disabled=false;
    // document.getElementById('edit').style.display='block';
    // document.getElementById('edit').disabled=false;
    // document.getElementById('simpan').style.display='none';
    document.getElementById('kodeorg').value = kodeorg;

    tahun = tanggal.substr(0, 4);
    bln = tanggal.substr(5, 2);
    hr = tanggal.substr(8, 2);
    tanggal1 = hr + '-' + bln + '-' + tahun;
    //alert(tanggal1);
    //alert($.datepicker.formatDate('dd M yy', mydate))
    document.getElementById('tanggal').value = tanggal1;

    document.getElementById('sisatbskemarin').value = sisatbskemarin;
    document.getElementById('tbsmasuk').value = tbsmasuk;
    document.getElementById('tbsdiolah').value = tbsdiolah;
    document.getElementById('sisa').value = sisahariini;

    document.getElementById('kodeorg').disabled = true;
    // document.getElementById('tanggal').disabled=true;
    document.getElementById('sisatbskemarin').disabled = true;
    document.getElementById('tbsmasuk').disabled = true;
    // document.getElementById('tbsdiolah').disabled=true;
    document.getElementById('sisa').disabled = true;

    document.getElementById('oercpo').value = oer;
    document.getElementById('dirtcpo').value = kadarkotoran;
    document.getElementById('kadaraircpo').value = kadarair;
    document.getElementById('ffacpo').value = ffa;
    document.getElementById('cpoonsistem').value = cpoonsistem;
    document.getElementById('loadinggudang').value = loadinggudang;
    document.getElementById('oerpk').value = oerpk;
    document.getElementById('dirtpk').value = kadarkotoranpk;

    document.getElementById('kadarairpk').value = kadarairpk;
    document.getElementById('ffapk').value = ffapk;
    document.getElementById('usbcpo').value = dobi;

    document.getElementById('usbbefore').value = usbbefore;
    document.getElementById('usbafter').value = usbafter;
    document.getElementById('oildiluted').value = oildiluted;
    document.getElementById('oilin').value = oilin;
    document.getElementById('oilinheavy').value = oilinheavy;
    document.getElementById('caco').value = caco;

    document.getElementById('hydrocyclone').value = hydrocyclone;
    document.getElementById('fruitineb').value = fruitineb;
    document.getElementById('ebstalk').value = ebstalk;
    document.getElementById('fibre').value = fibre;
    document.getElementById('nut').value = nut;
    document.getElementById('effluent').value = effluent;
    document.getElementById('soliddecanter').value = soliddecanter;

    document.getElementById('fruitinebker').value = fruitinebker;
    document.getElementById('cyclone').value = cyclone;
    document.getElementById('ltds').value = ltds;
    document.getElementById('claybath').value = claybath;

    document.getElementById('lorirestanhi').value = lorirestanhi;
    document.getElementById('cangkang').value = cangkang;
    document.getElementById('condensatesterilizer').value = condensatesterilizer;

    document.getElementById('tbsmasuknetto').value = tbsmasuknetto;
    document.getElementById('tbsdiolahnetto').value = tbsdiolahnetto;
    document.getElementById('sisatbskemarinnetto').value = sisatbskemarinnetto;
    document.getElementById('sisanetto').value = sisanetto;

    document.getElementById('method').value = 'update';

    document.getElementById('keterangan').value = keterangan.replace(re, '\n');
	
	add_new_data();
    // method=document.getElementById('method').value;
    //document.getElementById('method').value="updateDetail";
}

function grafikProduksiFfa(periode, tampil, pabrik, ev) {
    param = 'periode=' + periode + '&tampil=' + tampil + '&pabrik=' + pabrik;
    //document.getElementById('container').innerHTML="<img src='pabrik_slave_grafikProduksi.php?"+param+"'>";
    tujuan = 'pabrik_slave_grafikProduksiFfa.php?' + param;
    title = periode;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}

function grafikTbs(periode, tampil, pabrik, ev) {
    param = 'periode=' + periode + '&tampil=' + tampil + '&pabrik=' + pabrik;
    //document.getElementById('container').innerHTML="<img src='pabrik_slave_grafikProduksi.php?"+param+"'>";
    tujuan = 'pabrik_slave_grafikTbs.php?' + param;
    title = periode;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}

function getData() {
    kodeorg = document.getElementById('kodeorg').value;
    tanggal = document.getElementById('tanggal').value;
    param = 'kodeorg=' + kodeorg + '&method=getData' + '&tanggal=' + tanggal;
    tujuan = 'pabrik_slave_produksi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    arr = con.responseText.split("###");
                    document.getElementById('sisatbskemarin').value = arr[0];
                    document.getElementById('tbsmasuk').value = arr[1];
                    document.getElementById('tbsdiolah').value = arr[2];
                    document.getElementById('sisatbskemarinnetto').value = arr[3];
                    document.getElementById('tbsmasuknetto').value = arr[4];
                    document.getElementById('tbsdiolahnetto').value = arr[5];
                    hitungSisa();
                    getCpo();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function getCpo() {
    kodeorg = document.getElementById('kodeorg').value;
    tanggal = document.getElementById('tanggal').value;
    param = 'kodeorg=' + kodeorg + '&method=getCpo' + '&tanggal=' + tanggal;
    tujuan = 'pabrik_slave_produksi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('oercpo').value = con.responseText;
                    getKernel();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function getKernel() {
    kodeorg = document.getElementById('kodeorg').value;
    tanggal = document.getElementById('tanggal').value;
    param = 'kodeorg=' + kodeorg + '&method=getKernel' + '&tanggal=' + tanggal + '&loadinggudang=' + remove_comma_var(document.getElementById('loadinggudang').value);
    tujuan = 'pabrik_slave_produksi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('oerpk').value = numberFormat(con.responseText);
                    // hitungSisa();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function simpan() {
	kodeorg             = document.getElementById('kodeorg').value;
	tanggal             = document.getElementById('tanggal').value;
	sisatbskemarin      = document.getElementById('sisatbskemarin').value;
	tbsmasuk            = document.getElementById('tbsmasuk').value;
	tbsdiolah           = document.getElementById('tbsdiolah').value;
	sisahariini         = document.getElementById('sisa').value;

	oer                 = document.getElementById('oercpo').value;
	dirt                = document.getElementById('dirtcpo').value;
	kadarair            = document.getElementById('kadaraircpo').value;
	ffa                 = document.getElementById('ffacpo').value;
	cpoonsistem         = document.getElementById('cpoonsistem').value;
	loadinggudang       = document.getElementById('loadinggudang').value;
	oerpk               = document.getElementById('oerpk').value;
	dirtpk              = document.getElementById('dirtpk').value;
	kadarairpk          = document.getElementById('kadarairpk').value;
	ffapk               = document.getElementById('ffapk').value;

	usbb                = document.getElementById('usbbefore').value;
	usbaf               = document.getElementById('usbafter').value;
	oildil              = document.getElementById('oildiluted').value;
	oilin               = document.getElementById('oilin').value;
	oilinhe             = document.getElementById('oilinheavy').value;
	caco                = document.getElementById('caco').value;

	//cpo loses
	fruit               = document.getElementById('fruitineb').value;
	ebstalk             = document.getElementById('ebstalk').value;
	fibre               = document.getElementById('fibre').value;
	nut                 = document.getElementById('nut').value;
	efflue              = document.getElementById('effluent').value;
	solidd              = document.getElementById('soliddecanter').value;

	//kernel loses
	fruitiker           = document.getElementById('fruitinebker').value;
	cycl                = document.getElementById('cyclone').value;
	ltds                = document.getElementById('ltds').value;
	claybath            = document.getElementById('claybath').value;
	usbcpo              = document.getElementById('usbcpo').value;
	usbpk               = document.getElementById('usbpk').value;

	hydrocyclone        = document.getElementById('hydrocyclone').value;

	lorirestanhi        = document.getElementById('lorirestanhi').value;
	cangkang            = document.getElementById('cangkang').value;
	condensatesterilizer= document.getElementById('condensatesterilizer').value;

	method              = document.getElementById('method').value;

	sisatbskemarinnetto = document.getElementById('sisatbskemarinnetto').value;
	tbsmasuknetto       = document.getElementById('tbsmasuknetto').value;
	tbsdiolahnetto      = document.getElementById('tbsdiolahnetto').value;
	sisanetto           = document.getElementById('sisanetto').value;

	sisatbskemarin      = remove_comma_var(sisatbskemarin);
	tbsmasuk            = remove_comma_var(tbsmasuk);
	tbsdiolah           = remove_comma_var(tbsdiolah);
	sisahariini         = remove_comma_var(sisahariini);

	sisatbskemarinnetto = remove_comma_var(sisatbskemarinnetto);
	tbsmasuknetto       = remove_comma_var(tbsmasuknetto);
	tbsdiolahnetto      = remove_comma_var(tbsdiolahnetto);
	sisanetto           = remove_comma_var(sisanetto);
	loadinggudang       = remove_comma_var(loadinggudang);
	oerpk               = remove_comma_var(oerpk);
	oer                 = remove_comma_var(oer);

	keterangan          = document.getElementById('keterangan').value;

    if (kodeorg == '' || tanggal == '' || sisahariini == '' || sisahariini == null || sisatbskemarin == '' || sisatbskemarin == null || tbsmasuk == '' || tbsmasuk == null || tbsdiolah == '' || tbsdiolah == null || oer == '' || oer == null || kadarair == '' || kadarair == null || ffa == '' || ffa == null || dirt == '' || dirt == null || oerpk == '' || oerpk == null || kadarairpk == '' || kadarairpk == null || ffa == '' || ffa == null || dirtpk == '' || dirtpk == null) {
        alert('All fields are required');
    } else {
        param = 'kodeorg=' + kodeorg + '&tanggal=' + tanggal;
        param += '&tbsmasuk=' + tbsmasuk + '&tbsdiolah=' + tbsdiolah;
        param += '&sisahariini=' + sisahariini + '&sisatbskemarin=' + sisatbskemarin;
        param += '&dirt=' + dirt + '&kadarair=' + kadarair;
        param += '&oer=' + oer + '&ffa=' + ffa;
        param += '&dirtpk=' + dirtpk + '&kadarairpk=' + kadarairpk;
        param += '&oerpk=' + oerpk + '&ffapk=' + ffapk;
        param += '&loadinggudang=' + loadinggudang;
        param += '&usbbefore=' + usbb + '&usbafter=' + usbaf;
        param += '&oildiluted=' + oildil + '&oilin=' + oilin;
        param += '&oilinheavy=' + oilinhe + '&caco=' + caco;
        //cpo loses
        param += '&fruitineb=' + fruit + '&ebstalk=' + ebstalk;
        param += '&fibre=' + fibre + '&nut=' + nut;
        param += '&effluent=' + efflue + '&soliddecanter=' + solidd;

        //kernel loses
        param += '&fruitinebker=' + fruitiker + '&cyclone=' + cycl;
        param += '&ltds=' + ltds + '&claybath=' + claybath;
        param += '&usbcpo=' + usbcpo + '&usbpk=' + usbpk + '&hydrocyclone=' + hydrocyclone;

        param += '&lorirestanhi=' + lorirestanhi + '&cangkang=' + cangkang + '&condensatesterilizer=' + condensatesterilizer;

        param += '&sisatbskemarinnetto=' + sisatbskemarinnetto + '&tbsmasuknetto=' + tbsmasuknetto;
        param += '&tbsdiolahnetto=' + tbsdiolahnetto + '&sisanetto=' + sisanetto;
        param += '&method=' + method + '&keterangan=' + keterangan + '&cpoonsistem=' + cpoonsistem;

        tujuan = 'pabrik_slave_produksi.php';
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    bersihkanForm();
                    displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function bersihkanForm() {

    // document.getElementById('simpan').style.display='block';
    // document.getElementById('edit').style.display='none';

    document.getElementById('kodeorg').disabled = false;
    document.getElementById('tanggal').disabled = false;
    document.getElementById('tbsdiolah').disabled = false;
    document.getElementById('sisatbskemarin').disabled = true;
    //document.getElementById('tbsmasuk').disabled=false;
    document.getElementById('sisa').disabled = true;
    document.getElementById('tanggal').value = '';
    document.getElementById('sisatbskemarin').value = '0';
    document.getElementById('tbsmasuk').value = '0';
    document.getElementById('tbsdiolah').value = '0';
    document.getElementById('sisa').value = '0';

    document.getElementById('cpoonsistem').value = '0';
    document.getElementById('oercpo').value = '0';
    document.getElementById('dirtcpo').value = '0';
    document.getElementById('kadaraircpo').value = '0';
    document.getElementById('ffacpo').value = '0';
    document.getElementById('oerpk').value = '0';
    document.getElementById('loadinggudang').value = '0';
    document.getElementById('dirtpk').value = '0';
    document.getElementById('kadarairpk').value = '0';
    document.getElementById('ffapk').value = '0';
    document.getElementById('usbbefore').value = '0';
    document.getElementById('usbafter').value = '0';
    document.getElementById('oildiluted').value = '0';
    document.getElementById('oilin').value = '0';
    document.getElementById('oilinheavy').value = '0';
    document.getElementById('caco').value = '0';
    document.getElementById('hydrocyclone').value = '0';

    //cpo loses
    document.getElementById('fruitineb').value = '0';
    document.getElementById('ebstalk').value = '0';
    document.getElementById('fibre').value = '0';
    document.getElementById('nut').value = '0';
    document.getElementById('effluent').value = '0';
    document.getElementById('soliddecanter').value = '0';

    //kernel loses
    document.getElementById('fruitinebker').value = '0';
    document.getElementById('cyclone').value = '0';
    document.getElementById('ltds').value = '0';
    document.getElementById('claybath').value = '0';
    document.getElementById('usbcpo').value = '0';
    document.getElementById('usbpk').value = '0';

    document.getElementById('lorirestanhi').value = 0;
    document.getElementById('cangkang').value = 0;
    document.getElementById('condensatesterilizer').value = 0;

    document.getElementById('sisatbskemarinnetto').value = 0;
    document.getElementById('tbsmasuknetto').value = 0;
    document.getElementById('tbsdiolahnetto').value = 0;
    document.getElementById('sisanetto').value = 0;
    document.getElementById('keterangan').value = '';
    document.getElementById('method').value = 'insert';

}

function del(kodeorg, tanggal) {
    param = 'method=delete' + '&kodeorg=' + kodeorg + '&tanggal=' + tanggal;
    if (confirm('Delete ..?')) {
        tujuan = 'pabrik_slave_produksi.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function postingproduksi(notransaksi) {
    param = 'method=posting' + '&notransaksi=' + notransaksi;
    if (confirm('Apakah anda yakin ingin posting Notransaksi '+notransaksi+' ..?')) {
        tujuan = 'pabrik_slave_produksi.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function unposting(kodeorg, tanggal) {
    param = 'method=unposting' + '&kodeorg=' + kodeorg + '&tanggal=' + tanggal;
    if (confirm('Unposting ..?')) {
        tujuan = 'pabrik_slave_produksi.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loaddata(num) {

    tglcr1 = document.getElementById('tglcr1').value;
    tglcr2 = document.getElementById('tglcr2').value;
    param = 'method=loaddata' + '&tglcr1=' + tglcr1 + '&tglcr2=' + tglcr2;
    param += '&page=' + num;
    tujuan = 'pabrik_slave_produksi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function numberFormat(number, digit) {
    number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
    //Seperates the components of the number
    var components = (parseFloat(number).toFixed(digit)).split(".");
    //Comma-fies the first part
    components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //Combines the two sections
    return components.join(".");
}

function hitungSisa() {
    sisatbskemarin = (document.getElementById('sisatbskemarin').value);
    tbsmasuk = (document.getElementById('tbsmasuk').value);
    tbsdiolah = (document.getElementById('tbsdiolah').value);

    sisatbskemarin = remove_comma_var(sisatbskemarin);
    tbsmasuk = remove_comma_var(tbsmasuk);
    tbsdiolah = remove_comma_var(tbsdiolah);

    var sisa = 0;
    /*
    sisatbskemarin	=parseInt(document.getElementById('sisatbskemarin').value);
    tbsmasuk		=parseInt(document.getElementById('tbsmasuk').value);
    tbsdiolah		=parseInt(document.getElementById('tbsdiolah').value);
     */
    if (isNaN(tbsdiolah)) {
        tbsdiolah = 0;
    }
    if (isNaN(tbsmasuk)) {
        tbsmasuk = 0;
    }
    if (isNaN(sisatbskemarin)) {
        sisatbskemarin = 0;
    }
    document.getElementById('sisa').value = sisa;
    sisa = (parseFloat(sisatbskemarin) + parseFloat(tbsmasuk)) - parseFloat(tbsdiolah);
    //alert(parseFloat(sisatbskemarin)+"__"+parseFloat(tbsmasuk)+"__"+parseFloat(tbsdiolah));
    //return;
    if (sisa > 0) {
        document.getElementById('sisa').value = numberFormat(sisa);
    }

    hitungSisanetto();
}

function hitungSisanetto() {
    sisatbskemarin = (document.getElementById('sisatbskemarinnetto').value);
    tbsmasuk = (document.getElementById('tbsmasuknetto').value);
    tbsdiolah = (document.getElementById('tbsdiolahnetto').value);

    sisatbskemarin = remove_comma_var(sisatbskemarin);
    tbsmasuk = remove_comma_var(tbsmasuk);
    tbsdiolah = remove_comma_var(tbsdiolah);

    var sisa = 0;
    /*
    sisatbskemarin	=parseInt(document.getElementById('sisatbskemarinnetto').value);
    tbsmasuk		=parseInt(document.getElementById('tbsmasuknetto').value);
    tbsdiolah		=parseInt(document.getElementById('tbsdiolahnetto').value);
     */
    if (isNaN(tbsdiolah)) {
        tbsdiolah = 0;
    }
    if (isNaN(tbsmasuk)) {
        tbsmasuk = 0;
    }
    if (isNaN(sisatbskemarin)) {
        sisatbskemarin = 0;
    }
    sisa = (parseFloat(sisatbskemarin) + parseFloat(tbsmasuk)) - parseFloat(tbsdiolah);
    //sisa=(sisatbskemarin+tbsmasuk)-tbsdiolah;
    if (sisa >= 0) {
        document.getElementById('sisanetto').value = sisa;
    } else {
        //alert('Invalid character');
        document.getElementById('sisanetto').value = 0;
    }
}

function periksaCPO(obj) {
    dirt = parseFloat(document.getElementById('dirtcpo').value);
    kadarair = parseFloat(document.getElementById('kadaraircpo').value);
    ffa = parseFloat(document.getElementById('ffacpo').value);
    x = dirt + kadarair + ffa;
    if (x > 50) //yang tidak terpakai lebih besar di dalam cpo
    {
        //alert('Invalid character');
        obj.focus();
        obj.value = 0;

    }
}
function periksaPK(obj) {
    oerpk = parseFloat(document.getElementById('oerpk').value);
    dirtpk = parseFloat(document.getElementById('dirtpk').value);
    kadarairpk = parseFloat(document.getElementById('kadarairpk').value);
    ffapk = parseFloat(document.getElementById('ffapk').value);
    x = dirtpk + kadarairpk + ffapk;
    if (x > 50) //yang tidak terpakai lebih besar di dalam pk
    {
        //alert('Invalid character');
        obj.focus();
        obj.value = 0;

    }
}

function periksaOERCPO(obj) {
    oercpo = parseFloat(document.getElementById('oercpo').value);
    if (oercpo < 1) {
        //alert('Invalid character');
        obj.focus();
        obj.value = 0;

    }
}
function periksaOERPK(obj) {
    oerpk = parseFloat(document.getElementById('oerpk').value);
    if (oerpk < 1) {
        //alert('Invalid character');
        obj.focus();
        obj.value = 0;

    }
}

function getLaporanPrdPabrik() {
    periode = document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
    tampil = document.getElementById('periode').options[document.getElementById('periode').selectedIndex].text;
    pabrik = document.getElementById('pabrik').options[document.getElementById('pabrik').selectedIndex].text;
    param = 'periode=' + periode + '&tampil=' + tampil + '&pabrik=' + pabrik;
    tujuan = 'pabrik_slave_3produksiHarian.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else { ;
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function laporanPDF(periode, tampil, pabrik, ev) {
    param = 'periode=' + periode + '&tampil=' + tampil + '&pabrik=' + pabrik;
    tujuan = 'pabrik_slave_printProduksi_pdf.php?' + param;
    //display window
    title = periode;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}

function grafikProduksi(periode, tampil, pabrik, ev) {
    param = 'periode=' + periode + '&tampil=' + tampil + '&pabrik=' + pabrik;
    //document.getElementById('container').innerHTML="<img src='pabrik_slave_grafikProduksi.php?"+param+"'>";
    tujuan = 'pabrik_slave_grafikProduksi.php?' + param;
    title = periode;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}

function laporanEXCEL(periode, tampil, pabrik, ev) {
    param = 'periode=' + periode + '&tampil=' + tampil + '&pabrik=' + pabrik;
    tujuan = 'pabrik_slave_printProduksi_excel.php?' + param;
    //display window
    title = periode;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}
function showDetail(tgl, kdorg, ev) {

    title = "Data Detail";
    content = "<fieldset><legend>Unit : " + kdorg + ", Date " + tgl + "</legend><div id=contDetail style='overflow:auto; width:750px; height:320px;' ></div></fieldset>";
    width = '800';
    height = '370';
    showDialog1(title, content, width, height, ev);
}
function previewDetail(tgl, kdorg, ev) {
    showDetail(tgl, kdorg, ev);
    param = 'kdorg=' + kdorg + '&method=getDetailPP' + '&tgl=' + tgl;
    tujuan = 'pabrik_slave_produksi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('contDetail').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}