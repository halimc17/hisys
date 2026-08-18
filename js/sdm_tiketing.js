function viewlist(){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('header').style.display = 'none';
	document.getElementById('formpencarianheader').style.display = 'none';
	document.getElementById('viewlist').style.display = 'block';
	document.getElementById('formpencarianview').style.display = 'block';
	viewlistdata();
}
function getPagelist() {
	pg = document.getElementById('pagelist');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	viewlistdata(paged);
}
function viewlistdata(page){
	jenis       = document.getElementById('jenisview').value;
	kodeorg     = document.getElementById('kodeorgview').value;
	tgl         = document.getElementById('tglview').value;
	notransaksi = document.getElementById('notransaksiview').value;
	namakaryawan= document.getElementById('namaview').value;
	sumber    = document.getElementById('sumberview').value;
	
	param = 'method=viewlistdata';
	param += '&page=' + page;
	param += '&notransaksi=' + notransaksi;
	param += '&kodeorg=' + kodeorg;
	param += '&namakaryawan=' + namakaryawan;
	param += '&jenis=' + jenis;
	param += '&tgl=' + tgl;
	param += '&sumber=' + sumber;
	
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('containview').innerHTML = data[0];
					if(data[1]!=undefined){						
						document.getElementById('footDataview').innerHTML = data[1];
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('formpencarianheader').style.display = 'none';
	document.getElementById('viewlist').style.display = 'none';
	document.getElementById('formpencarianview').style.display = 'none';
	document.getElementById('mode').value='baru';
	cancel();
}
function cancel(){
	document.getElementById('mode').value='baru';
	document.getElementById('kodeorg').disabled=false;
	document.getElementById('kodeorg').value='';
	document.getElementById('sumber').value='';
	document.getElementById('tgl').value='';
	document.getElementById('notransaksi').value='';
	document.getElementById('sumber').disabled=false;
	document.getElementById('tgl').disabled=false;
	document.getElementById('tomboldetail').style.display='';
	
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('detail').innerHTML='';
}

function previewdata() {
	kodeorg    = document.getElementById('kodeorg').value;
	sumber     = document.getElementById('sumber').value;
	tgl        = document.getElementById('tgl').value;
	mode       = document.getElementById('mode').value;
	notransaksi= document.getElementById('notransaksi').value;
	
	
	if (kodeorg == '') {
		notif('kodeorg','Kode Organisasi wajib diisi.'); return;
	}
	if (sumber == '') {
		notif('sumber','Sumber wajib diisi.'); return;
	}
	if (tgl == '') {
		notif('tgl','Tanggal wajib diisi.'); return;
	}
	
	param = 'method=previewdata';
	param += '&kodeorg=' + kodeorg;
	param += '&sumber=' + sumber;
	param += '&tgl=' + tgl;
	param += '&mode=' + mode;
	param += '&notransaksi=' + notransaksi;
	
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('detail_cont').style.display = '';
					document.getElementById('detail').innerHTML=data[0];
					document.getElementById('notransaksi').value=data[1];
					document.getElementById('mode').value='edit';
					document.getElementById('kodeorg').disabled=true;
					document.getElementById('sumber').disabled=true;
					document.getElementById('tgl').disabled=true;
					document.getElementById('tomboldetail').style.display='none';
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});

					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getformnopjd(){
	// width    = '';
	// height   = '';
	// title    = "No Referensi";
	// content = "<fieldset><div id=container style=\"width:100%;max-height:385px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // showDialog3(title, content, width, height, ev); 
	tgl  = document.getElementById('tgl').value;
	jenis= document.getElementById('jenis').value;
	
	param = 'method=getformnopjd';
	param += '&tgl=' + tgl;
	param += '&jenis=' + jenis;
	
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('container').innerHTML = con.responseText;
					alertify.popup("No Referensi",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					getnopjd();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getnopjd(page){
	notransaksi = document.getElementById('notransaksisrc').value;
	namakaryawan= document.getElementById('namakaryawansrc').value;
	jenis       = document.getElementById('jenisadd').value;
	sumber       = document.getElementById('sumber').value;
	tgl         = document.getElementById('tgl').value;
	
	param = 'method=getnopjd';
	param += '&page=' + page;
	param += '&notransaksi=' + notransaksi;
	param += '&namakaryawan=' + namakaryawan;
	param += '&jenis=' + jenis;
	param += '&tgl=' + tgl;
	param += '&sumber=' + sumber;
	
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('contformpjd').innerHTML = data[0];
					if(data[1]!=undefined){						
						document.getElementById('contformpjdfoot').innerHTML = data[1];
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getPageSrc() {
	pg = document.getElementById('pagesrc');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	getnopjd(paged);
}

function detailDataPJD(notransaksi,ev,jenis) {
	// width = 1024;
	// height = 400;
	
	// content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "Preview";
	// showDialog4(title, content, width, height, ev);
	
	param = 'method=previewdata' + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	tujuan = 'sdm_slave_pjdx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addpjd(nopjd,jenis,tgldibutuhkan,karid,namakar,kdgol,nmgol,ket,tgldr,tglsd,idpengajuan){
	document.getElementById('nopjd').value=nopjd;
	document.getElementById('jenis').value=jenis;
	document.getElementById('tgldibutuhkan').value=tgldibutuhkan;
	document.getElementById('idkaryawan').value=karid;
	document.getElementById('namakaryawan').value=namakar;
	document.getElementById('golongan').value=kdgol;
	document.getElementById('namagolongan').value=nmgol;
	document.getElementById('keterangan').value=ket;
	document.getElementById('tgldinasdari').value=tgldr;
	document.getElementById('tgldinassampai').value=tglsd;
	document.getElementById('tgldinassampai').value=tglsd;
	document.getElementById('idpengajuan').value=idpengajuan;
	alertify.popup().destroy();
}


function notif(idkolom,isipesan){
	col = idkolom.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.focus();
		kolom.style.borderColor='red';
		kolom.style.backgroundColor='#F2F94D';
		kolom.style.fontWeight='bold';
		kolom.value='';
	}
	alert(isipesan);
}
function hapuswarna(id){
	col = id.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.style.borderColor='';
		kolom.style.backgroundColor='';
		kolom.style.fontWeight='';
	}
}
function simpandetail() {
	kodeorg       = document.getElementById('kodeorg').value;
	sumber        = document.getElementById('sumber').value;
	tgl           = document.getElementById('tgl').value;
	mode          = document.getElementById('mode').value;
	notransaksi   = document.getElementById('notransaksi').value;
	jenis         = document.getElementById('jenis').value;
	nopjd         = document.getElementById('nopjd').value;
	karyawanid    = document.getElementById('idkaryawan').value;
	namakaryawan  = document.getElementById('namakaryawan').value;
	golongan      = document.getElementById('golongan').value;
	namagolongan  = document.getElementById('namagolongan').value;
	tgldinasdari  = document.getElementById('tgldinasdari').value;
	tgldinassampai= document.getElementById('tgldinassampai').value;
	tgldibutuhkan = document.getElementById('tgldibutuhkan').value;
	supplier      = document.getElementById('supplier').value;
	jumlah        = document.getElementById('jumlah').value;
	keterangan    = document.getElementById('keterangan').value;
	method        = document.getElementById('methoddetail').value;
	id            = document.getElementById('id').value;
	idpengajuan   = document.getElementById('idpengajuan').value;
	pembayaran    = document.getElementById('pembayaran').value;
	
	if (notransaksi == '') {
		notif('notransaksi','Notransaksi wajib diisi.'); return;
	}
	if (kodeorg == '') {
		notif('kodeorg','Kode Organisasi wajib diisi.'); return;
	}
	if (sumber == '') {
		notif('sumber','Sumber wajib diisi.'); return;
	}
	if (tgl == '') {
		notif('tgl','Tanggal wajib diisi.'); return;
	}
	if (jenis == '') {
		notif('jenis','Jenis wajib diisi.'); return;
	}
	if(nopjd=='' && sumber=='1'){
		notif('nopjd','Nomor referensi wajib diisi.'); return;
	}
	if(karyawanid=='' && sumber=='1'){
		notif('karyawanid','Karyawan ID wajib diisi.'); return;
	}
	if (namakaryawan == '') {
		notif('namakaryawan','Nama karyawan wajib diisi.'); return;
	}
	if (tgldinasdari == '') {
		notif('tgldinasdari','Tanggal dari wajib diisi.'); return;
	}
	if (tgldinassampai == '') {
		notif('tgldinassampai','Tanggal sampai wajib diisi.'); return;
	}
	if (tgldibutuhkan == '') {
		notif('tgldibutuhkan','Tanggal dibutuhkan wajib diisi.'); return;
	}
	if (supplier == '') {
		notif('supplier','Vendor / Assigment wajib diisi.'); return;
	}
	if (pembayaran == '') {
		notif('pembayaran','Pembayaran wajib diisi.'); return;
	}
	if (jumlah == '') {
		notif('jumlah','Jumlah wajib diisi.'); return;
	}
	param = '';
	param += '&id=' + id;
	param += '&method=' + method;
	param += '&kodeorg=' + kodeorg;
	param += '&sumber=' + sumber;
	param += '&tgl=' + tgl;
	param += '&mode=' + mode;
	param += '&notransaksi=' + notransaksi;
	param += '&jenis=' + jenis;
	param += '&nopjd=' + nopjd;
	param += '&karyawanid=' + karyawanid;
	param += '&namakaryawan=' + namakaryawan;
	param += '&golongan=' + golongan;
	param += '&namagolongan=' + namagolongan;
	param += '&tgldinasdari=' + tgldinasdari;
	param += '&tgldinassampai=' + tgldinassampai;
	param += '&tgldibutuhkan=' + tgldibutuhkan;
	param += '&supplier=' + supplier;
	param += '&jumlah=' + jumlah;
	param += '&keterangan=' + keterangan;
	param += '&idpengajuan=' + idpengajuan;
	param += '&pembayaran=' + pembayaran;
	
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('methoddetail').value='simpandetail';
					loaddatadetail();
					canceldetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetail() {
	notransaksi= document.getElementById('notransaksi').value;
	
	param = 'method=loaddatadetail';
	param += '&notransaksi=' + notransaksi;
	
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdetail(id,jenis,notranspjd,idkar,nama,kodegol,gol,tgldinasdari,tgldinassampai,tanggal,supplierid,biaya,keterangan,pembayaran){
	document.getElementById('id').value=id;
	document.getElementById('jenis').value=jenis;
	document.getElementById('nopjd').value=notranspjd;
	document.getElementById('idkaryawan').value=idkar;
	document.getElementById('namakaryawan').value=nama;
	document.getElementById('golongan').value=kodegol;
	document.getElementById('namagolongan').value=gol;
	document.getElementById('tgldinasdari').value=tgldinasdari;
	document.getElementById('tgldinassampai').value=tgldinassampai;
	document.getElementById('tgldibutuhkan').value=tanggal;
	document.getElementById('supplier').value=supplierid;
	document.getElementById('jumlah').value=biaya;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('methoddetail').value='editdetail';
	setValue2('pembayaran',pembayaran);
	setValue2('supplier',supplierid);
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);
}

function loaddata(page){
	notransaksi=document.getElementById('notransaksisch').value;
	kodeorg    =document.getElementById('kodeorgsch').value;
	tgl        =document.getElementById('tglsch').value;
	nopjd      =document.getElementById('nopjdsch').value;
	nama       =document.getElementById('namasch').value;
	supplier   =document.getElementById('suppliersch').value;

	param = 'method=loaddata&page=' + page;
	param += '&notransaksi=' + notransaksi;
	param += '&kodeorg=' + kodeorg;
	param += '&tgl=' + tgl;
	param += '&nopjd=' + nopjd;
	param += '&nama=' + nama;
	param += '&supplier=' + supplier;

    tujuan = 'sdm_slave_tiketing.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function displayList() {
	document.getElementById('formpencarianheader').style.display = 'block';
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('header').style.display = 'none';
	document.getElementById('viewlist').style.display = 'none';
	document.getElementById('formpencarianview').style.display = 'none';
	
	batallist();
}
function batallist(){
	document.getElementById('notransaksisch').value='';
	//document.getElementById('kodeorgsch').value='';
	document.getElementById('tglsch').value='';
	document.getElementById('nopjdsch').value='';
	document.getElementById('namasch').value='';
	document.getElementById('suppliersch').value='';
	loaddata();
}

function edit(notransaksi,tanggal,kodeorg,sumber){
	document.getElementById('header').style.display = 'block';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('formpencarianheader').style.display = 'none';
	
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('sumber').value=sumber;
	document.getElementById('tgl').value=tanggal;
	document.getElementById('mode').value='edit';
	document.getElementById('notransaksi').value=notransaksi;
	previewdata();
}
function canceldetail(){
	document.getElementById('id').value='';
	document.getElementById('idpengajuan').value='';
	// document.getElementById('jenis').value='';
	document.getElementById('nopjd').value='';
	document.getElementById('idkaryawan').value='';
	document.getElementById('namakaryawan').value='';
	document.getElementById('golongan').value='';
	document.getElementById('namagolongan').value='';
	document.getElementById('tgldinasdari').value='';
	document.getElementById('tgldinassampai').value='';
	document.getElementById('tgldibutuhkan').value='';
	document.getElementById('jumlah').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('methoddetail').value='simpandetail';
}

function deldetail(notransaksi,id){
	param = 'method=deldetail&id=' + id;
	param+= '&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_tiketing.php';
	if(confirm("Anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function del(notransaksi){
	param = 'method=del&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_tiketing.php';
	if(confirm("Anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
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
function preview(notransaksi,tipe){
	// width    = '';
	// height   = '';
	// title    = "Preview";
	// content = "<div id=container style=\"width:100%;max-height:385px;overflow:auto;\"></div>";
    // ev = 'event';
    // showDialog6(title, content, width, height, ev); 
	
	param = 'method=preview';
	param += '&notransaksi=' + notransaksi;
	param += '&tipe=' + tipe;
	
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('container').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function detailExcel(notransaksi,tipe){
	param = 'method=preview' + '&notransaksi=' + notransaksi+ '&tipe=' + tipe;
	tujuan = 'sdm_slave_tiketing.php' + "?" + param;
	if(tipe=='pdf'){
		width = '950';
		height = '400';
	}else{		
		width = '';
		height = '';
	}
	ev = 'event';
	title = "Preview";
	//content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	if(tipe=='pdf'){
		// showDialog1(title, content, width, height, ev);
		alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}else if(tipe=='excel'){
		printnopopup(tujuan);
	}else{
		// showDialog6(title, content, width, height, ev);
		alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
	}
}

function form_ajukan(notransaksi,kodeapproval) {
	width = '350';
	height = '';
	content = "<fieldset><div id=containeraju style=\"width:320px;max-height:150px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);

	param = 'method=form_ajukan' + '&kodeapproval=' + kodeapproval;
	param += '&notransaksi=' + notransaksi;
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan() {
	kepada      = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	kodeapproval= document.getElementById('kodeapprovalaju').value;
	
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	param += '&kodeapproval=' + kodeapproval;

	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alert('Sucses');
					closeDialog5();
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tampilDetail(notransaksi,noakun,tipetransaksi,kodeorg){
	ev ="event";
	param = "proses=html&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
        title="Data Detail";
        showDialog1(title,"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(ev,id,notransaksi){
	param='method=showupload&notransaksi='+notransaksi;
	param+='&id=' + id;
	tujuan='sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					// showformupload(ev);
                    // document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup("Upload",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('30%','50%');
					loadfiles(notransaksi,id);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function submitfile(notransaksi,id) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();

	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("id", id);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "sdm_slave_tiketing.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,id);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(notransaksi,id) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	param+='&id=' + id;
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function deletefile(notransaksi, id,namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	param += "&id=" + id;
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi,id);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(notransaksi) {
	param = "method=posting";
	param += "&notransaksi=" + notransaksi;
	tujuan = 'sdm_slave_tiketing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
