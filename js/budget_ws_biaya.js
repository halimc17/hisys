function previewpdf(tipebudget,tahunbudget,kodeorg,tipe){
	tipebudgetV = tipebudget;
	tahunbudgetV= tahunbudget;
	kodewsV     = kodeorg;
	jenis       = 'popup';
	ev          = 'event';
	title       = "Data Detail";
	
	//param  = 'cekapa=tab0';
	param = "";
	param += '&tipebudget=' + tipebudgetV + '&tahunbudget=' + tahunbudgetV + '&kodews=' + kodewsV;
	param += '&jenis='+jenis;
	param += '&tipe='+tipe;
	content = "<iframe frameborder=0 style='width:100%;min-height:400px' src='budget_slave_ws_biaya_hkef.php?cekapa=tab0"+param+"'></iframe>";
	content += "<iframe frameborder=0 style='width:100%;min-height:400px' src='budget_slave_ws_biaya_hkef.php?cekapa=tab1"+param+"'></iframe>";
	content += "<iframe frameborder=0 style='width:100%;min-height:400px' src='budget_slave_ws_biaya_hkef.php?cekapa=tab2"+param+"'></iframe>";
	content += "<iframe frameborder=0 style='width:100%;min-height:400px' src='budget_slave_ws_biaya_hkef.php?cekapa=tab3"+param+"'></iframe>";
	
	printnopopup("budget_slave_ws_biaya_hkef.php?cekapa=tab0"+param);
	printnopopup("budget_slave_ws_biaya_hkef.php?cekapa=tab1"+param);
	printnopopup("budget_slave_ws_biaya_hkef.php?cekapa=tab2"+param);
	printnopopup("budget_slave_ws_biaya_hkef.php?cekapa=tab3"+param);
	
	
	//showDialog1(title,content,'200','100',ev);	
	//updateTab0('all',tipebudget,tahunbudget,kodeorg,'popup',tipe);
}

function preview(tipebudget,tahunbudget,kodeorg,tipe){	
	width = '';
	height = '';
	content = "<fieldset><div id=contpreview_sdm align=center style=\"max-width:700px;max-height:200px;overflow:auto;\"></div></fieldset>";
	content += "<hr><fieldset><div id=contpreview_mat align=center style=\"max-width:700px;max-height:200px;overflow:auto;\"></div></fieldset>";
	content += "<hr><fieldset><div id=contpreview_tool align=center style=\"max-width:700px;max-height:200px;overflow:auto;\"></div></fieldset>";
	content += "<hr><fieldset><div id=contpreview_oth align=center style=\"max-width:700px;max-height:200px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	updateTab0('all',tipebudget,tahunbudget,kodeorg,'popup',tipe);
}

function editoth(kunci,kodebudget,noakun,rupiah){
	document.getElementById('index').value=kunci;
	document.getElementById('kodebudget3').value=kodebudget;
	document.getElementById('kodeakun3').value=noakun;
	
	setValue2('kodebudget3',kodebudget);
	setValue2('kodeakun3',noakun);
	
	document.getElementById('totalbiaya3').value=rupiah;
	document.getElementById('proses').value='update';
}

function edittool(kunci,kodebudget,kodebarang,namabarang,satuan,volume,rupiah){
	document.getElementById('index').value=kunci;
	document.getElementById('kodebudget2').value=kodebudget;
	document.getElementById('kodebarang2').value=kodebarang;
	document.getElementById('namabarang2').innerHTML=namabarang;
	document.getElementById('satuan2').innerHTML=satuan;
	document.getElementById('jumlah2').value=volume;
	document.getElementById('jumlah2').disabled=false;
	document.getElementById('totalharga2').value=rupiah;
	document.getElementById('proses').value='update';
}

function editmat(kunci,kodebudget,kodebarang,namabarang,satuan,volume,rupiah){
	document.getElementById('index').value=kunci;
	document.getElementById('kodebudget1').value=kodebudget;
	setValue2('kodebudget1',kodebudget);
	document.getElementById('kodebarang1').value=kodebarang;
	document.getElementById('namabarang1').innerHTML=namabarang;
	document.getElementById('satuan1').innerHTML=satuan;
	document.getElementById('jumlah1').value=volume;
	document.getElementById('jumlah1').disabled=false;
	document.getElementById('totalharga1').value=rupiah;
	document.getElementById('proses').value='update';
}

function editsdm(kunci,kodebudget,jumlah,volume,rupiah){
	document.getElementById('index').value=kunci;
	document.getElementById('kodebudget0').value=kodebudget;
	document.getElementById('jumlahpersonel0').value=jumlah;
	document.getElementById('jlhhksdm').value=volume;
	document.getElementById('totalbiaya0').value=rupiah;
	document.getElementById('proses').value='update';
	setValue2('kodebudget0',kodebudget);
}

function form() {
	width = '';
	height = '';
	content = "<fieldset><div id=contpreview align=center style=\"max-width:700px;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
}

function getdatadetail(tipe,tipebudget,tahunbudget,kodeorg){	
	if(tipe=='sdm'){
		//form();
		updateTab0('popup',tipebudget,tahunbudget,kodeorg,'popup');
	}
	if(tipe=='mat'){
		// form();
		updateTab1('popup',tipebudget,tahunbudget,kodeorg,'popup');
	}
	if(tipe=='tool'){
		// form();
		updateTab2('popup',tipebudget,tahunbudget,kodeorg,'popup');
	}
	if(tipe=='trans'){
		// form();
		updateTab3('popup',tipebudget,tahunbudget,kodeorg,'popup');
	}
}

function del(tipebudget,tahunbudget,kodeorg) {
	param = 'cekapa=del' + '&tipebudget=' + tipebudget;
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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

function unposting(tipebudget,tahunbudget,kodeorg) {
	param = 'cekapa=unposting' + '&tipebudget=' + tipebudget;
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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


function posting(tipebudget,tahunbudget,kodeorg) {
	param = 'cekapa=posting' + '&tipebudget=' + tipebudget;
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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


function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	tahunbudget= document.getElementById('tahunbudgetsch').value;
	kodeorg    = document.getElementById('kodeorgsch').value;
	kodews     = document.getElementById('kodewssch').value;
	
	param = 'cekapa=loaddata&page=' + page;
	param += '&tahunbudget=' + tahunbudget + '&kodeorg=' + kodeorg;
	param += '&kodews=' + kodews;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function add_new_data(){
	document.getElementById('datainputdetail').style.display = 'block';
	document.getElementById('inputdetail').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	//batal();
}

function displayList() {
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('datainputdetail').style.display = 'none';
	document.getElementById('inputdetail').style.display = 'none';
	// batal();
	loaddata(0);
}

function editdetail(tipebudget,tahunbudget,kodeorg){
	document.getElementById('inputdetail').style.display = 'block';
	document.getElementById('datainputdetail').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	
	document.getElementById('tipebudget').value=tipebudget;
	document.getElementById('tahunbudget').value=tahunbudget;
	document.getElementById('kodews').value=kodeorg;
	setValue2('kodews',kodeorg);
	prosesSimpan();
}

function prosesBaru() {
	document.getElementById('container0').innerHTML = '';
	document.getElementById('container1').innerHTML = '';
	document.getElementById('container2').innerHTML = '';
	document.getElementById('container3').innerHTML = '';
	document.getElementById('container4').innerHTML = '';
	document.getElementById('tab0').disabled = true;
	document.getElementById('tab1').disabled = true;
	document.getElementById('tab2').disabled = true;
	document.getElementById('tab3').disabled = true;
	document.getElementById('tab4').disabled = true;
	document.getElementById('tahunbudget').disabled = false;
	document.getElementById('kodews').disabled = false;
	document.getElementById('kodebudget0').value = '';
	document.getElementById('hkefektif0').value = '';
	document.getElementById('jumlahpersonel0').value = '';
	document.getElementById('totalbiaya0').value = '';
	document.getElementById('kodebudget1').value = '';
	document.getElementById('kodebarang1').value = '';
	document.getElementById('namabarang1').value = '';
	document.getElementById('satuan1').value = '';
	document.getElementById('jumlah1').value = '';
	document.getElementById('totalharga1').value = '';
	document.getElementById('kodebudget2').value = '';
	document.getElementById('kodebarang2').value = '';
	document.getElementById('namabarang2').value = '';
	document.getElementById('satuan2').value = '';
	document.getElementById('jumlah2').value = '';
	document.getElementById('totalharga2').value = '';
	document.getElementById('kodebudget3').value = '';
	document.getElementById('kodeakun3').value = '';
	document.getElementById('totalbiaya3').value = '';
	document.getElementById('tutup4').disabled = true;

}

//fixation
function prosesSimpan() {
	tahunbudget = document.getElementById('tahunbudget');
	tahunbudgetV = tahunbudget.value;
	kodews = document.getElementById('kodews');
	kodewsV = kodews.options[kodews.selectedIndex].value;
	if (tahunbudgetV == '') {
		alertify.alert('Budget is empty.');
		return;
	}
	if (kodewsV == '') {
		alertify.alert('Workshop is empty.');
		return;
	}

	param = 'cekapa=hkef&tahunbudget=' + tahunbudgetV;
	param += '&kodews=' + kodewsV;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//                    showById('printPanel');
					if (con.responseText == '') {
						alertify.alert('HK Efektif(Effective working days) not found.\n Please provide it first');
					} else {
						//."#####".
						isidt = con.responseText.split("#####");
						document.getElementById('hkefektif0').value = isidt[0];
						document.getElementById('kodebudget0').innerHTML = isidt[1];
						document.getElementById('tahunbudget').disabled = true;
						document.getElementById('kodews').disabled = true;
						document.getElementById('tab0').disabled = false;
						document.getElementById('tab1').disabled = false;
						document.getElementById('tab2').disabled = false;
						document.getElementById('tab3').disabled = false;
						//document.getElementById('tab4').disabled = false;
						//document.getElementById('tutup4').disabled = true;
						updateTab0('all');
					}
					//                    alertify.alert(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function bersihkan(tab) {
	if (tab == 1) {
		kodebudget1 = document.getElementById('kodebudget1');
		kodebudget1V = kodebudget1.options[kodebudget1.selectedIndex].value;
		if (kodebudget1V == '') {
			document.getElementById('kodebarang1').disabled = true;
			document.getElementById('jumlah1').disabled = true;
			document.getElementById('search1').disabled = true;

		} else { // ada kodebudget
			document.getElementById('kodebarang1').disabled = false;
			document.getElementById('jumlah1').disabled = true;
			document.getElementById('search1').disabled = false;
			//        document.getElementById('kodebarang1').value=Right(kodebudget1V,3);
			document.getElementById('kodebarang1').value = kodebudget1V.slice(2);

		}
		document.getElementById('jumlah1').value = '';
		document.getElementById('totalharga1').value = '';
		document.getElementById('namabarang1').innerHTML = '';
		document.getElementById('satuan1').innerHTML = '';
	}
	if (tab == 2) {
		kodebudget2 = document.getElementById('kodebudget2');
		kodebudget2V = kodebudget2.options[kodebudget2.selectedIndex].value;
		if (kodebudget2V == '') {
			document.getElementById('kodebarang2').disabled = true;
			document.getElementById('jumlah2').disabled = true;
			document.getElementById('search2').disabled = true;

		} else { // ada kodebudget
			document.getElementById('kodebarang2').disabled = false;
			document.getElementById('jumlah2').disabled = true;
			document.getElementById('search2').disabled = false;
			//        document.getElementById('kodebarang1').value=Right(kodebudget1V,3);

		}
		document.getElementById('jumlah2').value = '';
		document.getElementById('totalharga2').value = '';
		document.getElementById('namabarang2').innerHTML = '';
		document.getElementById('satuan2').innerHTML = '';
	}
}

function jumlahkan0() {
	kodebudget0V    = document.getElementById('kodebudget0').value;
	hkefektif0V     = document.getElementById('hkefektif0').value;
	jumlahpersonel0V= document.getElementById('jumlahpersonel0').value;
	kodews          = document.getElementById('kodews').value;
	
	param = 'cekapa=upah&kodebudget0=' + kodebudget0V;
	param += '&kodews=' + kodews;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						document.getElementById('kodebudget0').value = '';
						alertify.alert('Daily salary(Upah harian) not yet provided or not yet posted');
					} else {
						upah = con.responseText
						jumlah = hkefektif0V * upah * jumlahpersonel0V;
						document.getElementById('totalbiaya0').value = numberFormat(jumlah);
						document.getElementById('jlhhksdm').value = numberFormat(hkefektif0V * jumlahpersonel0V);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function jumlahkan1() {
	kodebarang1 = document.getElementById('kodebarang1');
	kodebarang1V = kodebarang1.value;
	jumlah1 = document.getElementById('jumlah1');
	jumlah1V = jumlah1.value;
	kodews = document.getElementById('kodews');
	kodewsV = kodews.options[kodews.selectedIndex].value;
	tahunbudget = document.getElementById('tahunbudget');
	tahunbudgetV = tahunbudget.value;
	param = 'cekapa=regional&kodews=' + kodewsV;
	param2 = 'cekapa=barang&tahunbudget=' + tahunbudgetV + '&kodebarang1=' + kodebarang1V;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						document.getElementById('jumlah1').value = '';
						alertify.alert('This workshop does not assingned to any regional.\n Please assign it first');
					} else {
						document.getElementById('regional1').value = con.responseText;
						param2 = param2 + '&regional=' + con.responseText;
						post_response_text(tujuan, param2, respog2);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	function respog2() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						//document.getElementById('jumlah1').value = '';
						alertify.alert('Material price not found');
					} else {
						harga = con.responseText;
						jumlah = harga * jumlah1V;
						document.getElementById('totalharga1').value = jumlah;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function jumlahkan2() {
	kodebarang2 = document.getElementById('kodebarang2');
	kodebarang2V = kodebarang2.value;
	jumlah2 = document.getElementById('jumlah2');
	jumlah2V = jumlah2.value;
	kodews = document.getElementById('kodews');
	kodewsV = kodews.options[kodews.selectedIndex].value;
	tahunbudget = document.getElementById('tahunbudget');
	tahunbudgetV = tahunbudget.value;
	param = 'cekapa=regional&kodews=' + kodewsV;
	param2 = 'cekapa=barang&tahunbudget=' + tahunbudgetV + '&kodebarang1=' + kodebarang2V;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						document.getElementById('jumlah2').value = '';
						alertify.alert('This workshop does not assingned to any regional.\n Please assign it first');
					} else {
						document.getElementById('regional2').value = con.responseText;
						param2 = param2 + '&regional=' + con.responseText;
						post_response_text(tujuan, param2, respog2);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	function respog2() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						document.getElementById('jumlah2').value = '';
						alertify.alert('Material price not found');
					} else {
						harga = con.responseText;
						jumlah = harga * jumlah2V;
						document.getElementById('totalharga2').value = jumlah;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan0() {
	kodebudget0V    = document.getElementById('kodebudget0').value;
	hkefektif0V     = document.getElementById('hkefektif0').value;
	jumlahpersonel0V= document.getElementById('jumlahpersonel0').value;
	totalbiaya0V    = document.getElementById('totalbiaya0').value;
	tipebudgetV     = document.getElementById('tipebudget').value;
	tahunbudgetV    = document.getElementById('tahunbudget').value;
	kodewsV         = document.getElementById('kodews').value;
	proses          = document.getElementById('proses').value;
	index           = document.getElementById('index').value;
	
	totalbiaya0V=remove_comma_var(totalbiaya0V);
	
	if (kodebudget0V == '') {
		alertify.alert('Kode is empty.');
		return;
	}
	if (jumlahpersonel0V == '') {
		alertify.alert('Personel is empty.');
		return;
	}

	param = 'tab=0&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV + '&kodebudget0=' + kodebudget0V + '&hkefektif0=' + hkefektif0V + '&jumlahpersonel0=' + jumlahpersonel0V + '&totalbiaya0=' + totalbiaya0V;
	param += '&proses=' + proses;
	param += '&index=' + index;
	tujuan = 'budget_slave_ws_biaya_save.php';
	param2 = 'tab=cekclose&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV;
	post_response_text(tujuan, param2, respon2);
	function respon2() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						post_response_text(tujuan, param, respon);
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					//ada error
					alertify.alert(con.responseText);
				} else {
					//tidak ada error, cek response
					if (con.responseText == '') {
						alertify.alert('Done');
						document.getElementById('kodebudget0').value = '';
						document.getElementById('jumlahpersonel0').value = '';
						document.getElementById('totalbiaya0').value = '';
						document.getElementById('proses').value = '';
						document.getElementById('index').value = '';
						updateTab0();
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateTab0(apa,tipebudget,tahunbudget,kodeorg,jenis,tipe){
	if(jenis=='popup'){
		tipebudgetV = tipebudget;
		tahunbudgetV= tahunbudget;
		kodewsV     = kodeorg;
	}else{		
		tipebudgetV = document.getElementById('tipebudget').value;
		tahunbudgetV= document.getElementById('tahunbudget').value;
		kodewsV     = document.getElementById('kodews').value;
	}
	
	
	param = 'cekapa=tab0&tipebudget=' + tipebudgetV + '&tahunbudget=' + tahunbudgetV + '&kodews=' + kodewsV;
	param += '&jenis='+jenis;
	param += '&tipe='+tipe;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('container0')!=undefined){						
						document.getElementById('container0').innerHTML = con.responseText;
					}
					if(jenis=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('contpreview_sdm')!=undefined){						
						document.getElementById('contpreview_sdm').innerHTML = con.responseText;						
					}
					
					leftFixedTable();
					if (apa == 'all'){
						//updateTab1('all');
						updateTab1(apa,tipebudget,tahunbudget,kodeorg,jenis,tipe);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateTab1(apa,tipebudget,tahunbudget,kodeorg,jenis,tipe) {
	if(jenis=='popup'){
		tipebudgetV = tipebudget;
		tahunbudgetV= tahunbudget;
		kodewsV     = kodeorg;
	}else{		
		tipebudgetV = document.getElementById('tipebudget').value;
		tahunbudgetV= document.getElementById('tahunbudget').value;
		kodewsV     = document.getElementById('kodews').value;
	}
	
	param = 'cekapa=tab1&tipebudget=' + tipebudgetV + '&tahunbudget=' + tahunbudgetV + '&kodews=' + kodewsV;
	param += '&jenis='+jenis;
	param += '&tipe='+tipe;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('container1')!=undefined){						
						document.getElementById('container1').innerHTML = con.responseText;
					}
					if(jenis=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('contpreview_mat')!=undefined){
						document.getElementById('contpreview_mat').innerHTML = con.responseText;						
					}
					leftFixedTable();
					if (apa == 'all'){
						//updateTab2('all');
						updateTab2(apa,tipebudget,tahunbudget,kodeorg,jenis,tipe);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateTab2(apa,tipebudget,tahunbudget,kodeorg,jenis,tipe) {
	if(jenis=='popup'){
		tipebudgetV = tipebudget;
		tahunbudgetV= tahunbudget;
		kodewsV     = kodeorg;
	}else{		
		tipebudgetV = document.getElementById('tipebudget').value;
		tahunbudgetV= document.getElementById('tahunbudget').value;
		kodewsV     = document.getElementById('kodews').value;
	}
	
	
	param = 'cekapa=tab2&tipebudget=' + tipebudgetV + '&tahunbudget=' + tahunbudgetV + '&kodews=' + kodewsV;
	param += '&jenis='+jenis;
	param += '&tipe='+tipe;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('container2')!=undefined){						
						document.getElementById('container2').innerHTML = con.responseText;
					}
					if(jenis=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('contpreview_tool')!=undefined){
						document.getElementById('contpreview_tool').innerHTML = con.responseText;						
					}
					leftFixedTable();
					if (apa == 'all'){
						//updateTab3('all');
						updateTab3(apa,tipebudget,tahunbudget,kodeorg,jenis,tipe);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateTab3(apa,tipebudget,tahunbudget,kodeorg,jenis,tipe) {
	if(jenis=='popup'){
		tipebudgetV = tipebudget;
		tahunbudgetV= tahunbudget;
		kodewsV     = kodeorg;
	}else{		
		tipebudgetV = document.getElementById('tipebudget').value;
		tahunbudgetV= document.getElementById('tahunbudget').value;
		kodewsV     = document.getElementById('kodews').value;
	}
	
	
	param = 'cekapa=tab3&tipebudget=' + tipebudgetV + '&tahunbudget=' + tahunbudgetV + '&kodews=' + kodewsV;
	param += '&jenis='+jenis;
	param += '&tipe='+tipe;
	tujuan = 'budget_slave_ws_biaya_hkef.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('container3')!=undefined){						
						document.getElementById('container3').innerHTML = con.responseText;
					}
					if(jenis=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('contpreview_oth')!=undefined){
						document.getElementById('contpreview_oth').innerHTML = con.responseText;						
					}
					
					leftFixedTable();
					//updateTab4();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan1() {
	satuan1V    = document.getElementById('satuan1').innerHTML;
	jumlah1V    = document.getElementById('jumlah1').value;
	regional1V  = document.getElementById('regional1').value;
	kodebarang1V= document.getElementById('kodebarang1').value;
	totalharga1V= document.getElementById('totalharga1').value;
	kodebudget1V= document.getElementById('kodebudget1').value;
	tipebudget  = document.getElementById('tipebudget').value;
	tahunbudgetV= document.getElementById('tahunbudget').value;
	kodewsV     = document.getElementById('kodews').value;

	if (jumlah1V == '') {
		alertify.alert('Ammount is empty.');
		return;
	}
	param = 'tab=1&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV + '&kodebudget1=' + kodebudget1V + '&totalharga1=' + totalharga1V + '&kodebarang1=' + kodebarang1V + '&regional1=' + regional1V + '&jumlah1=' + jumlah1V + '&satuan1=' + satuan1V;
	param += '&proses=' + getValue('proses');
	param += '&index=' + getValue('index');
	tujuan = 'budget_slave_ws_biaya_save.php';
	param2 = 'tab=cekclose&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV;
	post_response_text(tujuan, param2, respon2);
	function respon2() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						post_response_text(tujuan, param, respon);
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						alertify.alert('Done');
						document.getElementById('satuan1').innerHTML = '';
						document.getElementById('jumlah1').value = '';
						document.getElementById('namabarang1').innerHTML = '';
						document.getElementById('totalharga1').value = '';
						document.getElementById('index').value = '';
						document.getElementById('proses').value = '';
						updateTab1();
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan2() {
	satuan2V    = document.getElementById('satuan2').innerHTML;
	jumlah2V    = document.getElementById('jumlah2').value;
	regional2V  = document.getElementById('regional2').value;
	kodebarang2V= document.getElementById('kodebarang2').value;
	totalharga2V= document.getElementById('totalharga2').value;
	kodebudget2V= document.getElementById('kodebudget2').value;
	tipebudget  = document.getElementById('tipebudget').value;
	tahunbudgetV= document.getElementById('tahunbudget').value;
	kodewsV     = document.getElementById('kodews').value;

	if (jumlah2V == '') {
		alertify.alert('Ammount is empty.');
		return;
	}
	param = 'tab=2&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV + '&kodebudget2=' + kodebudget2V + '&totalharga2=' + totalharga2V + '&kodebarang2=' + kodebarang2V + '&regional2=' + regional2V + '&jumlah2=' + jumlah2V + '&satuan2=' + satuan2V;
	param += '&proses=' + getValue('proses');
	param += '&index=' + getValue('index');
	tujuan = 'budget_slave_ws_biaya_save.php';
	param2 = 'tab=cekclose&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV;
	post_response_text(tujuan, param2, respon2);
	function respon2() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						post_response_text(tujuan, param, respon);
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						alertify.alert('Done');
						document.getElementById('satuan2').innerHTML = '';
						document.getElementById('jumlah2').value = '';
						document.getElementById('namabarang2').innerHTML = '';
						document.getElementById('kodebarang2').value = '';
						document.getElementById('totalharga2').value = '';
						//document.getElementById('kodebudget2').value = '';
						document.getElementById('index').value = '';
						document.getElementById('proses').value = '';
						updateTab2();
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan3() {
	kodeakun3V  = document.getElementById('kodeakun3').value;
	totalbiaya3V= document.getElementById('totalbiaya3').value;
	kodebudget3V= document.getElementById('kodebudget3').value;
	tipebudget = document.getElementById('tipebudget').value;
	tahunbudgetV= document.getElementById('tahunbudget').value;
	kodewsV     = document.getElementById('kodews').value;

	if (kodebudget3V == '') {
		alertify.alert('Please fill budget code.');
		return;
	}
	if (kodeakun3V == '') {
		alertify.alert('Please fill budget account.');
		return;
	}
	if (totalbiaya3V == '') {
		alertify.alert('Cost required.');
		return;
	}
	param = 'tab=3&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV + '&kodebudget3=' + kodebudget3V + '&totalbiaya3=' + totalbiaya3V + '&kodeakun3=' + kodeakun3V;
	param += '&proses=' + getValue('proses');
	param += '&index=' + getValue('index');
	tujuan = 'budget_slave_ws_biaya_save.php';
	param2 = 'tab=cekclose&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&kodews=' + kodewsV;
	post_response_text(tujuan, param2, respon2);
	function respon2() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						post_response_text(tujuan, param, respon);
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == '') {
						alertify.alert('Done');
						setValue2('kodeakun3',null);
						document.getElementById('totalbiaya3').value = '';
						//document.getElementById('kodebudget3').value = '';
						document.getElementById('index').value = '';
						document.getElementById('proses').value = '';
						updateTab3();
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deleteRow(tab, kunci) { {
		param = 'cekapa=delete0&kunci=' + kunci;
		tujuan = 'budget_slave_ws_biaya_hkef.php';
		if (confirm('Delete?'))
			post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert('Done.');
					if (tab == '0')
						updateTab0();
					if (tab == '1')
						updateTab1();
					if (tab == '2')
						updateTab2();
					if (tab == '3')
						updateTab3();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function searchBrg(tab, title, content, ev) {
	if (tab == '1') {
		qwe = document.getElementById('kodebarang1');
	}
	if (tab == '2') {
		qwe = document.getElementById('kodebarang2');
	}
	qweV = qwe.value;
	// width = '500';
	// height = '400';
	// showDialog1(title, content, width, height, ev);
	alertify.popup("Find",content).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%');
	if (qweV == '') {
	}else {
		if (tab == '1') {
			document.getElementById('no_brg').value = qweV;
		}
		if (tab == '2') {
			document.getElementById('no_brg2').value = qweV;
		}
		findBrg(tab);
	}
}

function findBrg(tab) {
	if (tab == '1') {
		kodebudget1 = document.getElementById('kodebudget1');
		kodebudget1V = kodebudget1.options[kodebudget1.selectedIndex].value;
		kodebudget1V = kodebudget1V.slice(2);
		txt = trim(document.getElementById('no_brg').value);
	}
	kodews = document.getElementById('kodews').value;
	thnBudget = trim(document.getElementById('tahunbudget').value);
	if (tab == '2') {
		txt = trim(document.getElementById('no_brg2').value);
	}
	
	if (tab == '1') {
		param = 'tab=1&txtfind=' + txt + '&awalan=' + kodebudget1V+ '&thnBudget=' + thnBudget;
		param += '&kodews=' + kodews;
	}
	if (tab == '2') {
		param = 'tab=2&txtfind=' + txt + '&awalan=';
		param += '&kodews=' + kodews;
	}
	param += '&thnBudget=' + thnBudget;
	tujuan = 'budget_slave_ws_biaya_barang.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					
					if (tab == '1')
						document.getElementById('container').innerHTML = con.responseText;
					if (tab == '2')
						document.getElementById('containerx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setBrg(tab, no_brg, namabrg, satuan, hargasatuan, nomor) {
	if (tab == '1') {
		klmpk="M-"+no_brg.substr(0,3);
		der=document.getElementById('kodebudget1');
		for (a = 0; a < der.length; a++) {
			if (der.options[a].value == klmpk) {
				der.options[a].selected = true;
			}
		}
		document.getElementById('jumlah1').value = '';
		document.getElementById('totalharga1').value = '';
		document.getElementById('kodebarang1').value = no_brg;
		document.getElementById('namabarang1').innerHTML = namabrg;
		document.getElementById('satuan1').innerHTML = satuan;
		document.getElementById('jumlah1').disabled = false;
		document.getElementById('hargasatuan1').value = hargasatuan;
	}
	if (tab == '2') {
		document.getElementById('jumlah2').value = '';
		document.getElementById('totalharga2').value = '';
		document.getElementById('kodebarang2').value = no_brg;
		document.getElementById('namabarang2').innerHTML = namabrg;
		document.getElementById('satuan2').innerHTML = satuan;
		document.getElementById('jumlah2').disabled = false;
		document.getElementById('hargasatuan2').value = hargasatuan;
	}
	// closeDialog();
	alertify.popup().destroy();
}


function showformupload(ev) {
	ev = 'event';
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;'></div></fieldset>";
	showDialog2(title, content, width, height, ev);
}

function showupload(kodebudget){
	tahun  = document.getElementById('tahunbudget').value;
	kodeorg= document.getElementById('kodews').value;
	tipebgt= document.getElementById('tipebudget').value;
	if(tahun==''){
		alert("Tahun wajib diisi."); return;
	}
	if(kodeorg==''){
		alert("Kode traksi wajib diisi."); return;
	}
	
	ev = 'event';
	showformupload(ev);
	param  = 'tab=showupload';
	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&tipebgt=' + tipebgt;
	param += '&kodebudget=' + kodebudget;
	tujuan = 'budget_slave_ws_biaya_save.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    document.getElementById('contUpload').innerHTML=con.responseText;
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}


function fileSelected(jenis){
	tahun  = document.getElementById('tahunbudget').value;
	kodeorg= document.getElementById('kodews').value;
	tipebgt= document.getElementById('tipebudget').value;
	kodebudget= document.getElementById('kodebudgetupload').value;
	
	var file = document.getElementById('upload').files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("tahun", tahun);
	formdata.append("kodeorg", kodeorg);
	formdata.append("tipebgt", tipebgt);
	formdata.append("jenis", jenis);
	formdata.append("kodebudget", kodebudget);
	formdata.append("tab", "fileSelected");
	
	if(jenis=='simpan'){
		alert("Hanya barang yang memiliki harga yg akan disimpan.");
	}
	
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "budget_slave_ws_biaya_save.php?tab=fileSelected", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    
    function respon(){
        if (con.readyState == 4){
			if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
					if(jenis=='simpan'){
						closeDialog2();
						updateTab1('all');
						alert("Done");
					}else{						
						document.getElementById('listfiles').innerHTML=con.responseText;
						//leftFixedTable();
					}
                }
            }else{
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function downloadmaster(){
	tahun  = document.getElementById('tahunbudget').value;
	kodeorg= document.getElementById('kodews').value;
	tipebgt= document.getElementById('tipebudget').value;
	
	param  = "tab=downloadmaster&tahun="+tahun+"&kodeorg="+kodeorg+"&tipebgt="+tipebgt;

	ev   = 'event';
	title="Master Data";
	showDialog1(title,"<iframe frameborder=0 src='budget_slave_ws_biaya_save.php?"+param+"'></iframe>",'','',ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
