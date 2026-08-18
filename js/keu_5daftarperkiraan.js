//JS
//untuk load page nya pake yg pg sesuaikan di slave laod datanya



function deldt(noakundt,kodeunitdt){
   
    param='method=deldt'+'&noakundt='+noakundt+'&kodeunitdt='+kodeunitdt;
    tujuan='keu_slave_5daftarperkiraan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                   alertify.alertify.alert("Informasi","Informasi",'ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    viewdetailbaru(noakundt);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savedt(){
    noakundt=document.getElementById('noakundt').value;
    kodeunitdt=document.getElementById('kodeunitdt').value;
   
    param='method=savedt'+'&kodeunitdt='+kodeunitdt+'&noakundt='+noakundt;
    
    tujuan='keu_slave_5daftarperkiraan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alertify.alertify.alert("Informasi","Informasi",'ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    viewdetailbaru(noakundt);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form(titledt){
    // width = '780px';
    // height = 'auto';
    // //nopp=document.getElementById('nopp_'+id).value;
    // content = "<fieldset><div id=containerd style=width:780px></div></fieldset>";
    // ev = 'event';
    title = titledt;//"Detail HTML";
    // showDialog1(title, content, width, height, ev);
	alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('55%','75%'); 
}


function viewdetailbaru(noakundt){
    titl="Detail unit :"+noakundt;
    // form(titl);
    param='method=viewdetailbaru'+'&noakundt='+noakundt;
    tujuan = 'keu_slave_5daftarperkiraan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alertify.alert("Informasi",'ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    // document.getElementById('containerd').innerHTML = con.responseText;
					 title = titl;//"Detail HTML";
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('400px','75%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getPage(pg) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loadData(paged);
	// cariBast(pg-1);
}
function simpan() {
	noakun = document.getElementById('noakun').value;
	namaakun = document.getElementById('namaakun').value;
	namaakun1 = document.getElementById('namaakun1').value;
	tipeakun = document.getElementById('tipeakun').value;
	level = document.getElementById('level').value;
	matauang = document.getElementById('matauang').value;
	kodeorg = document.getElementById('kodeorg').value;
	namapemilik = document.getElementById('pemilik').value;
	method = document.getElementById('method').value;
	
	// Line1 
	kasbankdetail = document.getElementById('kasbankdetail');
	if (kasbankdetail.checked == true)
		kasbankdetail = 1;
	else
		kasbankdetail = 0;
	kodekegiatan = document.getElementById('kodekegiatan');
	if (kodekegiatan.checked == true)
		kodekegiatan = 1;
	else
		kodekegiatan = 0;
	kodeblok = document.getElementById('kodeblok');
	if (kodeblok.checked == true)
		kodeblok = 1;
	else
		kodeblok = 0;

	// Line2
	tagihan = document.getElementById('tagihan');
	if (tagihan.checked == true)
		tagihan = 1;
	else
		tagihan = 0;
	kodeasset = document.getElementById('kodeasset');
	if (kodeasset.checked == true)
		kodeasset = 1;
	else
		kodeasset = 0;
	kodesupplier = document.getElementById('kodesupplier');
	if (kodesupplier.checked == true)
		kodesupplier = 1;
	else
		kodesupplier = 0;

	// Line3
	jurnalmemorial = document.getElementById('jurnalmemorial');
	if (jurnalmemorial.checked == true)
		jurnalmemorial = 1;
	else
		jurnalmemorial = 0;
	nik = document.getElementById('nik');
	if (nik.checked == true)
		nik = 1;
	else
		nik = 0;
	kodevhc = document.getElementById('kodevhc');
	if (kodevhc.checked == true)
		kodevhc = 1;
	else
		kodevhc = 0;

	// Line4
	detail = document.getElementById('detail');
	if (detail.checked == true)
		detail = 1;
	else
		detail = 0;
	nodok = document.getElementById('nodok');
	if (nodok.checked == true)
		nodok = 1;
	else
		nodok = 0;
	kodecustomer = document.getElementById('kodecustomer');
	if (kodecustomer.checked == true)
		kodecustomer = 1;
	else
		kodecustomer = 0;
	kasbank = document.getElementById('kasbank');
	if (kasbank.checked == true)
		kasbank = 1;
	else
		kasbank = 0;
		
	if (noakun == '' || namaakun == '' || namaakun1 == '' || tipeakun == '' || level == '' || matauang == '' || kodeorg == ''
		 || namapemilik == '') {
		alertify.alert('Field Was Empty');
		return;
	}
	param = 'noakun=' + noakun + '&namaakun=' + namaakun + '&namaakun1=' + namaakun1 + '&tipeakun=' + tipeakun +
		'&level=' + level + '&matauang=' + matauang + '&kodeorg=' + kodeorg + '&pemilik=' + namapemilik + '&method=' + method;
	param += '&kasbankdetail=' + kasbankdetail + '&kodekegiatan=' + kodekegiatan + '&kodeblok=' + kodeblok;
	param += '&tagihan=' + tagihan + '&kodeasset=' + kodeasset + '&kodesupplier=' + kodesupplier;
	param += '&jurnalmemorial=' + jurnalmemorial + '&nik=' + nik + '&kodevhc=' + kodevhc;
	param += '&detail=' + detail + '&nodok=' + nodok + '&kodecustomer=' + kodecustomer + '&kasbank=' + kasbank;
	tujuan = 'keu_slave_5daftarperkiraan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					cancel();
					loadData(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancel() {
	document.getElementById('noakun').value = '';
	document.getElementById('noakun').disabled = false;
	document.getElementById('namaakun').value = '';
	document.getElementById('namaakun1').value = '';
	document.getElementById('tipeakun').value = 'Aktiva';
	document.getElementById('level').value = '1';
	document.getElementById('matauang').value = 'IDR';
	document.getElementById('pemilik').value = 'GLOBAL';
	document.getElementById('method').value = 'insert';

	document.getElementById('kasbankdetail').checked = false;
	document.getElementById('kodekegiatan').checked = false;
	document.getElementById('kodeblok').checked = false;

	document.getElementById('tagihan').checked = false;
	document.getElementById('kodeasset').checked = false;
	document.getElementById('kodesupplier').checked = false;

	document.getElementById('jurnalmemorial').checked = false;
	document.getElementById('nik').checked = false;
	document.getElementById('kodevhc').checked = false;

	document.getElementById('detail').checked = false;
	document.getElementById('nodok').checked = false;
	document.getElementById('kodecustomer').checked = false;
	document.getElementById('kasbank').checked = false;
}
//==========CANCEL / RESET FORM cari awal ==================//
function cancelsearch() {
	document.getElementById('txtNoakun').value = '';
	document.getElementById('txtsearch').value = '';
	// document.getElementById('statusup').checked=false;
	// document.getElementById('alamat').value='';
	// document.getElementById('aktif').checked=false;
	// document.getElementById('method').value='insert';
	// document.getElementById('supplierid').disabled=false;
	loadData(0);
}
function loadData(num) {
	param = 'method=loadData';
	param += '&page=' + num;
	txtsearch = trim(document.getElementById('txtsearch').value);
	txtNoakun = trim(document.getElementById('txtNoakun').value);
	if (txtsearch != '') {
		param += '&txtsearch=' + txtsearch;
	}
	if (txtNoakun != '') {
		param += '&txtNoakun=' + txtNoakun;
	}
	tujuan = 'keu_slave_5daftarperkiraan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					// alertify.alert("Informasi",con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
					// getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//#searching data
function previewAkun(nosk, ev) {
	param = 'table=' + nosk;
	tujuan = 'keu_slave_5daftarperkiraan_pdf.php?' + param;
	//display window
	title = nosk;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}
function edit(noakun, namaakun, namaakun1, tipeakun, kasbank, level, matauang, kodeorg, detail, kasbankdetail, tagihan, jurnalmemorial,
	kodekegiatan, kodeasset, nik, kodecustomer, kodesupplier, kodevhc, kodeblok, nodok, pemilik) {
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('noakun').value = noakun;
	document.getElementById('noakun').disabled = true;
	document.getElementById('namaakun').value = namaakun;
	document.getElementById('namaakun1').value = namaakun1;
	document.getElementById('tipeakun').value = tipeakun;
	document.getElementById('level').value = level;
	document.getElementById('matauang').value = matauang;
	document.getElementById('pemilik').value = pemilik;
	document.getElementById('method').value = 'update';

	if (kodekegiatan == '1')
		document.getElementById('kodekegiatan').checked = true;
	else
		document.getElementById('kodekegiatan').checked = false;
	if (kodeasset == '1')
		document.getElementById('kodeasset').checked = true;
	else
		document.getElementById('kodeasset').checked = false;
	if (nik == '1')
		document.getElementById('nik').checked = true;
	else
		document.getElementById('nik').checked = false;
	if (kodecustomer == '1')
		document.getElementById('kodecustomer').checked = true;
	else
		document.getElementById('kodecustomer').checked = false;
	if (kodesupplier == '1')
		document.getElementById('kodesupplier').checked = true;
	else
		document.getElementById('kodesupplier').checked = false;
	if (kodevhc == '1')
		document.getElementById('kodevhc').checked = true;
	else
		document.getElementById('kodevhc').checked = false;
	if (kodeblok == '1')
		document.getElementById('kodeblok').checked = true;
	else
		document.getElementById('kodeblok').checked = false;
	if (nodok == '1'){
		document.getElementById('nodok').checked = true;
	}else{
		document.getElementById('nodok').checked = false;
	}
		
	if (kasbank == '1')
		document.getElementById('kasbank').checked = true;
	else
		document.getElementById('kasbank').checked = false;
	if (detail == '1')
		document.getElementById('detail').checked = true;
	else
		document.getElementById('detail').checked = false;
	if (kasbankdetail == '1')
		document.getElementById('kasbankdetail').checked = true;
	else
		document.getElementById('kasbankdetail').checked = false;
	if (tagihan == '1')
		document.getElementById('tagihan').checked = true;
	else
		document.getElementById('tagihan').checked = false;
	if (jurnalmemorial == '1')
		document.getElementById('jurnalmemorial').checked = true;
	else
		document.getElementById('jurnalmemorial').checked = false;
}
//#function searching ketika pop up
function searchBrg(title, content, ev) {
	width = 'auto';
	height = 'auto';
	showDialog1(title, content, width, height, ev);
	//alertify.alert("Informasi",'asdasd');
}
function searchNomor(title, content, ev) {
	width = 'auto';
	height = 'auto';
	showDialog1(title, content, width, height, ev);
	//alertify.alert("Informasi",'asdasd');
}
function findBrg() {
	txt = document.getElementById('searchAkun').value;
	// alertify.alert("Informasi",txt);
	if (txt == '') {
		alertify.alert("Informasi",'Text is obligatory');
	} else if (txt.length < 1) {
		alertify.alert("Informasi",'Too short words');
	} else {
		param = 'txtfind=' + txt + '&method=cariBarangDlmDtBs';
		// alertify.alert("Informasi",param);
		// return;
		tujuan = 'keu_slave_5daftarperkiraan.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//alertify.alert("Informasi",con.responseText);
					document.getElementById('containerSearch').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function findNomor() {
	txt = document.getElementById('searchNoAkun').value;
	// alertify.alert("Informasi",txt);
	if (txt == '') {
		alertify.alert("Informasi",'Text is obligatory');
	} else if (txt.length < 1) {
		alertify.alert("Informasi",'Too short words');
	} else {
		param = 'txtfind=' + txt + '&method=cariNoAkun';
		// alertify.alert("Informasi",param);
		// return;
		tujuan = 'keu_slave_5daftarperkiraan.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//alertify.alert("Informasi",con.responseText);
					document.getElementById('containerSearch2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setBrg(noakun, namaakun) {
	document.getElementById('noakun').value = noakun;
	document.getElementById('namaakun').value = namaakun;
	closeDialog();
}