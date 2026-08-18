function unhideheader() {
	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
	document.getElementById('hidebtn').style.display = 'block';
	document.getElementById('unhidebtn').style.display = 'none';
}
function hideheader() {
	document.getElementById('header_trans').style.display = 'none';
	document.getElementById('judul_header').style.display = 'none';
	document.getElementById('hidebtn').style.display = 'none';
	document.getElementById('unhidebtn').style.display = '';
}
function detailExcel(notransaksi, numRow, ev,jenis) {
	param = "method=preview&notransaksi=" + notransaksi+"&jenis=" + jenis;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:1195px;height:400px'" +
		" src='kebun_slave_realkontanan.php?" + param + "'></iframe>", '1200', '400', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}

function detailData(notransaksi, numRow, ev,jenis) {
	width = '';
	height = '';
	content = "<fieldset><legend>Detail</legend><div id=containerx align=center style=\"width:100%;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Data Detail";
	showDialog1(title, content, width, height, ev);
	param = "method=preview&notransaksi=" + notransaksi+"&jenis=" + jenis;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdetaildata(tanggal,divisi,jenis,tipe) {
	width = '1000';
	height = '';
	content = "<fieldset><legend>Detail</legend><div id=contx style=\"width:980px;height:400px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Data Detail";
	showDialog2(title, content, width, height, ev);
	if(tipe=='spb'){
		param = 'proses=previewdata&noSpb=' + tanggal;
		tujuan = 'kebun_slave_save_spb.php';
	}else{
		param = "tanggal=" + tanggal+"&divisi=" + divisi+"&jenis=" + jenis+"&method=" + tipe;
		tujuan = 'kebun_slave_operasional_print_detail_panen_kontan.php';		
	}
	
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailPDF(notransaksi, numRow, ev,jenis) {
	param = "method=preview&notransaksi=" + notransaksi+"&jenis=" + jenis;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:1195px;height:400px'" +
		" src='kebun_slave_realkontanan.php?" + param + "'></iframe>", '1200', '400', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
function postingData(notransaksi, numRow) {
	var param = "notransaksi=" + notransaksi;
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					//alert('Posting Berhasil');
					//javascript:location.reload(true);
					x = document.getElementById('tr_' + numRow);
					x.cells[11].innerHTML = '';
					x.cells[12].innerHTML = '';
					x.cells[13].innerHTML = "<img class=\"zImgOffBtn\" title=\"Posted\" src=\"images/skyblue/posted.png\">";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm('Akan dilakukan posting untuk transaksi ' + notransaksi +
			'\nData tidak dapat diubah setelah ini. Anda yakin?')) {
		post_response_text('kebun_slave_panen_posting.php', param, respon);
	}
}

//========================================================================================================================
function edit(notransaksi,kodeorg,tgl,nourut) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('tgl').value = tgl;
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('mode').value = 'edit';
	addHeader(notransaksi);
}
function getnotransaksi() {
	kodeorg = document.getElementById('kodeorg').value;
	tgl = document.getElementById('tgl').value;
	document.getElementById('notransaksi').value = '';
	param = 'tgl=' + tgl + '&kodeorg=' + kodeorg + '&method=getnotransaksi';
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function addHeader() {
	kodeorg = document.getElementById('kodeorg').value;
	tgl = document.getElementById('tgl').value;
	mode = document.getElementById('mode').value;
	notransaksi = document.getElementById('notransaksi').value;
	if (tgl == '' || kodeorg == '') {
		alert('Tanggal dan atau Kode Organisasi harus di isi !');
		return;
	}
	document.getElementById('tgl').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	if(mode=='baru'){
		//document.getElementById('tomboldetail').disabled = true;
	}else{
		document.getElementById('tomboldetail').disabled = false;
	}
	param = 'method=detail';
	param += '&tgl=' + tgl + '&kodeorg=' + kodeorg + '&notransaksi=' + notransaksi+ '&mode=' + mode;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detailx').style.display = 'block';
					document.getElementById('detail').innerHTML = data[1];
					document.getElementById('notransaksi').value = data[0];
					loddatadetail(data[0]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function inputdetail(notransaksi) {
	kodeorg = document.getElementById('kodeorg').value;
	filterdivisi = document.getElementById('filterdivisi').value;
	tgl = document.getElementById('tgl').value;
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=inputdetail';
	param += '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi + '&filterdivisi=' + filterdivisi;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('inputdetail').innerHTML = con.responseText;
					loddatadetail(notransaksi);
					
					loaddatanospb('spb');						
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnospb(divisi,tgl,notransaksi,jenis){
	width = '';
	height = '';
	content = "<fieldset><div id=contaddnospb style=\"max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Add";
	showDialog5(title, content, width, height, ev);
	
	param = 'method=getnospb' + '&divisi=' + divisi + '&tgl=' + tgl + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contaddnospb').innerHTML = con.responseText;
					previewaddnospb(divisi,notransaksi,jenis);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewaddnospb(divisi,notransaksi,jenis){
	tgl = document.getElementById('tglnospb').value;
	jenis = document.getElementById('jenis').innerHTML;
	param = 'method=previewaddnospb' + '&divisi=' + divisi + '&tgl=' + tgl + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contaddnospbdetail').innerHTML = con.responseText;
					loaddatanospb(jenis);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function saveaddnospb(divisi,notransaksi,nourut,jenis){
	tgl = document.getElementById('tglnospb').value;
	param = 'method=saveaddnospb' + '&divisi=' + divisi + '&tgl=' + tgl + '&notransaksi=' + notransaksi + '&jenis=' + jenis;
	
	if(jenis=='spb'){
		nospb = document.getElementById('add_nospb_'+nourut).innerHTML;
		kgwb = document.getElementById('add_kgspb_'+nourut).innerHTML;		
		param += '&nospb=' + nospb + '&kgwb=' + kgwb;
	}else if(jenis=='bmtbs'){
		kgbm = document.getElementById('kgbm').innerHTML;
		rpupahbm = document.getElementById('rpupahbm').innerHTML;
		rppremibm = document.getElementById('rppremibm').innerHTML;
		notrbm = document.getElementById('notrbm').innerHTML;	
		param += '&notrbm=' + notrbm + '&kgbm=' + kgbm;
		param += '&rpupahbm=' + rpupahbm + '&rppremibm=' + rppremibm;
	}
	
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(jenis=='spb'){
						x = document.getElementById('rownospb' + nourut);
						x.cells[4].innerHTML = '';						
					}else if(jenis=='bmtbs'){
						x = document.getElementById('rownobmtbs');
						x.cells[6].innerHTML = '';						
					}
					loaddatanospb(jenis);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletespb(notransaksi,divisi,tgl,jenis,nospb){
	param = 'method=deletespb'+ '&notransaksi=' + notransaksi+ '&jenis=' + jenis+ '&divisi=' + divisi+ '&tgl=' + tgl+ '&nospb=' + nospb;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(jenis=='bmtbs'){
						loaddatanobm();						
					}else{
						loaddatanospb();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatanospb(){
	jenis='spb';
	notransaksi = document.getElementById('notransaksi').value;
	divisi = document.getElementById('filterdivisi').value;
	param = 'method=loaddatanospb'+ '&notransaksi=' + notransaksi+ '&jenis=' + jenis+ '&divisi=' + divisi;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('nospb').innerHTML = data[0];
					document.getElementById('kgpks').innerHTML = numberFormat(data[1]);
					loaddatanobm();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatanobm(){
	jenis='bmtbs';
	notransaksi = document.getElementById('notransaksi').value;
	divisi = document.getElementById('filterdivisi').value;
	rphk = document.getElementById('rphk').innerHTML;
	ttlrppanen = document.getElementById('ttlrppanen').innerHTML;
	
	rphk=remove_comma_var(rphk);
	ttlrppanen=remove_comma_var(ttlrppanen);
	
	param = 'method=loaddatanospb'+ '&notransaksi=' + notransaksi+ '&jenis=' + jenis+ '&divisi=' + divisi;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('bmtbs').innerHTML = data[0];
					gttl = parseFloat(rphk)+parseFloat(ttlrppanen)+parseFloat(data[1]);
					document.getElementById('gttl').innerHTML = numberFormat(gttl);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail(){
	notransaksi = document.getElementById('notransaksi').value;
	kodeorg = document.getElementById('kodeorg').value;
	tgl = document.getElementById('tgl').value;
	divisi = document.getElementById('filterdivisi').value;
	hk = document.getElementById('hk').innerHTML;
	kghasilkerja = document.getElementById('kghasilkerja').innerHTML;
	rphk = document.getElementById('rphk').innerHTML;
	rpmdr = document.getElementById('rpmdr').innerHTML;
	rpkrn = document.getElementById('rpkrn').innerHTML;
	rpmdrtrk = document.getElementById('rpmdrtrk').innerHTML;
	rpmdr1 = document.getElementById('rpmdr1').innerHTML;
	kgpks = document.getElementById('kgpks').innerHTML;
	ttlkgbmtbs = document.getElementById('ttlkgbmtbs').value;
	
	param = 'method=insertdetail' + '&divisi=' + divisi + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	param += '&kodeorg=' + kodeorg + '&hk=' + hk;
	param += '&kghasilkerja=' + kghasilkerja + '&rphk=' + rphk;
	param += '&rpmdr=' + rpmdr + '&rpkrn=' + rpkrn;
	param += '&rpmdrtrk=' + rpmdrtrk;
	param += '&rpmdr1=' + rpmdr1;
	param += '&kgpks=' + kgpks;
	param += '&ttlkgbmtbs=' + ttlkgbmtbs;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loddatadetail(){
	notransaksi = document.getElementById('notransaksi').value;
	kodeorg = document.getElementById('kodeorg').value;
	tgl = document.getElementById('tgl').value;
	divisi = document.getElementById('filterdivisi').value;
	
	param = 'method=loaddatadetail' + '&divisi=' + divisi + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedetail(notransaksi, divisi) {
	param = 'method=deletedetail' + '&notransaksi=' + notransaksi + '&divisi=' + divisi;
	tujuan = 'kebun_slave_realkontanan.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loddatadetail();
					document.getElementById('inputdetail').innerHTML = "";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(notransaksi, numrow) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	param = 'method=delete' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_realkontanan.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata(paged);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function form_ajukan(notransaksi, kodeorg, numrow) {
	width = '300';
	height = '';
	content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&kodeorg=' + kodeorg + '&numrow=' + numrow;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containeraju').innerHTML = con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[10].innerHTML = '';
					x.cells[11].innerHTML = '';
					x.cells[12].innerHTML = '';
					alert('Sucses');
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//==========================================================================================================
function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	cancel();
}
function displayList() {
	document.getElementById('mode').value = 'baru';
	document.getElementById('notransaksisch').value = '';
	document.getElementById('tglmulai').value = '';
	document.getElementById('postingsrc').value = '';
	document.getElementById('periodesch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
	

	// document.getElementById('hidebtn').style.display = 'block';
	// document.getElementById('unhidebtn').style.display = 'none';
	loaddata(0);
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	notransaksisch = document.getElementById('notransaksisch').value;
	tglmulai = document.getElementById('tglmulai').value;
	postingsrc = document.getElementById('postingsrc').value;
	periodesch = document.getElementById('periodesch').value;
	param = 'method=loaddata&page=' + page;
	
	if (notransaksisch != '') {
		param += '&notransaksisch=' + notransaksisch;
	}
	if (tglmulai != '') {
		param += '&tglmulai=' + tglmulai;
	}
	
	if (postingsrc != '') {
		param += '&postingsrc=' + postingsrc;
	}
	if (periodesch != '') {
		param += '&periodesch=' + periodesch;
	}
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
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
function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('tgl').disabled = false;
	document.getElementById('tgl').value = '';
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('kodeorg').value = '';
	document.getElementById('notransaksi').value = '';
	document.getElementById('mode').value = 'baru';
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
function form() {
	width = '720';
	height = '';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<fieldset><div id=containerd align=center style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}
function html(notransaksi, kodeorg, tgl) {
	form();
	param = 'method=html' + '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_realkontanan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function excel(ev, tujuan) {
	unitexp = document.getElementById('unitexp').value;
	perexp = document.getElementById('perexp').value;
	if (unitexp == '' || perexp == '') {
		alert('Lengkapi unit dan periode.');
		return;
	}
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
	printFile(param, tujuan, judul, ev);
}

