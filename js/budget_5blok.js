function unposting(tahunbudget,divisi) {
	param = 'proses=unposting' + '&divisi=' + divisi;
	param += '&tahunbudget=' + tahunbudget;
	tujuan = 'budget_slave_5blok.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
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


function posting(tahunbudget,divisi) {
	param = 'proses=posting' + '&divisi=' + divisi;
	param += '&tahunbudget=' + tahunbudget;
	tujuan = 'budget_slave_5blok.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
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

function del(tahun,divisi){
	param   = 'proses=delete' + '&tahun=' + tahun + '&divisi=' + divisi;
	tujuan  = 'budget_slave_5blok.php';
	if(confirm("Anda Yakin ???")){		
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

function tampilkan(){
	jenis = document.getElementById('jenis').value;
	if(jenis=='lama'){
		cekData();
		document.getElementById('formbloklama').style.display = 'block';
		document.getElementById('formblokbaru').style.display = 'none';
	}else{
		cekDataBr();
		// document.getElementById('formbloklama').style.display = 'none';
		// document.getElementById('formblokbaru').style.display = 'block';
	}
}
function hitungttl(id,tujuan){
	row = document.getElementById('jmlhRow').value;
	t = 0;
	for(i=1;i<=row;i++){
		e = document.getElementById(id+i).value;
		t = parseFloat(e)+t;
	}
	document.getElementById(tujuan).innerHTML=t;
}
function getdivisi(sumber){
	if(sumber=='header'){
		kodeorg = document.getElementById('kodeorgsch').value;
	}else{
		kodeorg = document.getElementById('kodeorg').value;
	}
	
	param = 'kodeorg=' + kodeorg + '&proses=getdivisi';
	tujuan = 'budget_slave_5blok.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(sumber=='header'){
						document.getElementById('divisisch').innerHTML = con.responseText;
					}else{
						document.getElementById('idAfd').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function add_new_data(){
	document.getElementById('contlistdata').style.display = 'none';
	document.getElementById('formIsian').style.display = 'block';
	document.getElementById('formjudulIsian').style.display = 'block';
}

function displayList() {
	document.getElementById('contlistdata').style.display = 'block';
	document.getElementById('formIsian').style.display = 'none';
	document.getElementById('formjudulIsian').style.display = 'none';
	loadDataLama(0);
	
}

function cekData() {
	thnAng = document.getElementById('thnAnggran').value;
	afdId = document.getElementById('idAfd').value;
	param = 'thnAngrn=' + thnAng + '&afdId=' + afdId + '&proses=cekData';
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('save_kepala').disabled = true;
					document.getElementById('idAfd').disabled = true;
					document.getElementById('kodeorg').disabled = true;
					if (con.responseText >= 1) {
						if (confirm("Data sudah pernah ada, anda mau edit..?\n Tekan ok untuk mengedit data yang sudah di simpan\n atau tekan cancel untuk mengulang dengan blok tahun lalu")) {
						oldData(con.responseText);
						} else {
							prevData();
						}
					} else {
						prevData();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function prevData() {
	thnAng = document.getElementById('thnAnggran').value;
	afdId = document.getElementById('idAfd').value;
	param = 'thnAngrn=' + thnAng + '&afdId=' + afdId + '&proses=getPreview' + '&jmlh=' + 0;
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('save_kepala').disabled = true;
					document.getElementById('thnAnggran').disabled = true;
					document.getElementById('idAfd').disabled = true;
					document.getElementById('kodeorg').disabled = true;
					document.getElementById('isiContainer').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function oldData(x) {
	thnAng = document.getElementById('thnAnggran').value;
	afdId = document.getElementById('idAfd').value;
	param = 'thnAngrn=' + thnAng + '&afdId=' + afdId + '&proses=getPreview' + '&jmlh=' + x;
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('save_kepala').disabled = true;
					document.getElementById('thnAnggran').disabled = true;
					document.getElementById('isiContainer').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function editData(thn, kebun , afd) {
	document.getElementById('thnAnggran').value = thn;
	document.getElementById('idAfd').value = afd;
	document.getElementById('kodeorg').value = kebun;
	setValue2('kodeorg',kebun);
	setValue2('idAfd',afd);
	
	add_new_data();
	tampilkan();
}
function batalsimpan(){
	//document.getElementById('dataList').style.display = 'none';
	//document.getElementById('datatersimpan').style.display = 'block';
}

function batal() {
	document.getElementById('save_kepala').disabled = false;
	document.getElementById('thnAnggran').disabled = false;
	document.getElementById('idAfd').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	//document.getElementById('idAfd').value = '';
	//document.getElementById('thnAnggran').value = '';
	document.getElementById('isiContainer').innerHTML = '';
	document.getElementById('isiContainerBr').innerHTML = '';
	document.getElementById('containDetail').innerHTML = '';
	//document.getElementById('datatersimpan').style.display = 'block';
	add_new_data();
}

function saveAll(x) {
	thnAng      = document.getElementById('thnAnggran').value;
	kBlok       = document.getElementById('kdBlok_' + x).innerHTML;
	haThnLalu   = document.getElementById('luas_' + x).innerHTML;
	haThnIni    = document.getElementById('hathnIni_' + x).value;
	pkkThnLalu  = document.getElementById('pkk_' + x).innerHTML;
	pokokThnIni = document.getElementById('pokokThnINi_' + x).value;
	statBlok    = document.getElementById('statBlok_' + x).value;
	topoGrafi   = document.getElementById('topoGrafi_' + x).innerHTML;
	thnTmn      = document.getElementById('thnTmn_' + x).innerHTML;
	lcThnini    = document.getElementById('lcThn_' + x).value;
	haNon       = document.getElementById('haNon_' + x).value;
	pkkProduktif= document.getElementById('pkkProduk_' + x).value;
	totRow      = document.getElementById('jmlhRow').value;
	plsma       = document.getElementById('statPlasma_' + x);

	ar = topoGrafi.split("-");
	param = 'proses=insertAll' + '&thnAngrn=' + thnAng + '&haThnLalu=' + haThnLalu + '&kdBlok=' + kBlok;
	param += '&haThnIni=' + haThnIni + '&pkkThnLalu=' + pkkThnLalu + '&pokokThnIni=' + pokokThnIni + '&lcThnini=' + lcThnini;
	param += '&statBlok=' + statBlok + '&topoGrafi=' + ar[0] + '&thnTmn=' + thnTmn + '&haNon=' + haNon + '&pkkProduktif=' + pkkProduktif;
	if (plsma.checked == true) {
		param += '&statPlsma=P';
	}
	
	tujuan = 'budget_slave_5blok.php';
	if (x == 1 && confirm('Anda Yakin Melakukan Proses Ini?'))
		post_response_text(tujuan, param, respog);
	else
		post_response_text(tujuan, param, respog);
	document.getElementById('rew_' + x).style.backgroundColor = 'orange';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('rew_' + x).style.backgroundColor = 'red';
				} else {
					b = x;
					row = x + 1;
					x = row;
					if (x <= totRow) {
						document.getElementById('rew_' + b).style.backgroundColor = 'green';
						saveAll(x);
					} else {
						document.getElementById('rew_' + b).style.backgroundColor = 'green';
						loadDataLama();
						batal();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function getDatab(c) {
	isi = document.getElementById('pkk_' + c).innerHTML;
	document.getElementById('pokokThnINi_' + c).value = isi;
}
function getData(b) {
	isi = document.getElementById('luas_' + b).innerHTML;
	document.getElementById('hathnIni_' + b).value = isi;
}

function cekDataBr() {
	thnAngBr = document.getElementById('thnAnggran').value;
	afdIdBr = document.getElementById('idAfd').value;
	
	param = 'thnAngBr=' + thnAngBr + '&afdIdBr=' + afdIdBr + '&proses=cekDataBr' + '&jmlh=1';
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('formbloklama').style.display = 'none';
					document.getElementById('formblokbaru').style.display = 'block';
		
					document.getElementById('isiContainerBr').innerHTML = con.responseText;
					document.getElementById('dataListBr').style.display = 'block';
					document.getElementById('save_kepala').disabled = true;
					document.getElementById('thnAnggran').disabled = true;
					document.getElementById('kodeorg').disabled = true;
					document.getElementById('idAfd').disabled = true;
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

// function prevDataBr() {
	// thnAngBr = document.getElementById('thnAnggran').value;
	// afdIdBr = document.getElementById('idAfd').value;
	// param = 'thnAngBr=' + thnAngBr + '&afdIdBr=' + afdIdBr + '&proses=getPreviewBr' + '&jmlh=' + 0;
	// tujuan = 'budget_slave_5blok.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alertify.alert(con.responseText);
				// } else {
					// document.getElementById('isiContainerBr').innerHTML = con.responseText;
					// document.getElementById('dataListBr').style.display = 'block';
					// document.getElementById('save_kepala').disabled = true;
					// document.getElementById('thnAnggran').disabled = true;
					// document.getElementById('kodeorg').disabled = true;
					// document.getElementById('idAfd').disabled = true;
					// loadData();
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function oldDataBr(x) {
	// thnAngBr = document.getElementById('thnAnggran').value;
	// afdIdBr = document.getElementById('idAfd').value;
	// param = 'thnAngBr=' + thnAngBr + '&afdIdBr=' + afdIdBr + '&proses=getPreviewBr' + '&jmlh=' + x;
	// tujuan = 'budget_slave_5blok.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alertify.alert(con.responseText);
				// } else {
					// document.getElementById('isiContainerBr').innerHTML = con.responseText;
					// document.getElementById('dataListBr').style.display = 'block';
					// document.getElementById('save_kepala').disabled = true;
					// document.getElementById('thnAnggran').disabled = true;
					// document.getElementById('kodeorg').disabled = true;
					// document.getElementById('idAfd').disabled = true;
					// loadData();
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }
function delbaru(tahun,blok){
	param   = 'proses=delbaru' + '&tahun=' + tahun + '&blok=' + blok;
	tujuan  = 'budget_slave_5blok.php';
	if(confirm("Anda Yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadData() {
	afdIdBr = document.getElementById('idAfd').value;
	thnAngBr= document.getElementById('thnAnggran').value;
	param   = 'proses=loadData' + '&afdIdBr=' + afdIdBr + '&thnAngBr=' + thnAngBr;
	tujuan  = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('rew_' + x).style.backgroundColor = 'red';
				} else {
					document.getElementById('containDetail').innerHTML = con.responseText;
					getThnBudgt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalBr() {
	document.getElementById('save_kepala').disabled = false;
	document.getElementById('thnAnggran').disabled = false;
	document.getElementById('idAfd').disabled = false;
	document.getElementById('idAfd').value = '';
	document.getElementById('thnAnggran').value = '';
	document.getElementById('dataListBr').style.display = 'none';
	document.getElementById('isiContainerBr').innerHTML = '';
}

function saveAllBr(x) {
	thnAngBr     = document.getElementById('thnAnggran').value;
	idAfdBr      = document.getElementById('idAfd').value;
	kBlokBr      = document.getElementById('kdBlokBr_' + x).value;
	kBlokBr2     = idAfdBr + kBlokBr;
	haThnIniBr   = document.getElementById('hathnIniBr_' + x).value;
	pokokThnIniBr= document.getElementById('pokokThnINiBr_' + x).value;
	statBlokBr   = document.getElementById('statBlokBr_' + x).value;
	topoGrafiBr  = document.getElementById('topoGrafiBr_' + x).value;
	thnTmnBr     = document.getElementById('thnTmnBr_' + x).value;
	lcThnBr      = document.getElementById('lcThnBr_' + x).value;
	haNonBr      = document.getElementById('haNonBr_' + x).value;
	pkkProdukBr  = document.getElementById('pkkProdukBr_' + x).value;
	totRow       = document.getElementById('jmlhRow').value;
	thnAngrnOld  = document.getElementById('thnAngrnOld').value;
	oldBlok      = document.getElementById('oldBlok').value;
	plsma        = document.getElementById('statPlasmaBr_' + x);
	topoGrafOld  = document.getElementById('topoGrafOld').value;
	if (topoGrafOld == '') {
		topoGrafOld = topoGrafiBr;
	}
	if (thnAngrnOld == '') {
		thnAngrnOld = thnAngBr;
	}
	if (oldBlok == '') {
		oldBlok = kBlokBr;
	}
	param = 'proses=insertAllBr' + '&thnAngBr=' + thnAngBr + '&kdBlokBr=' + kBlokBr2;
	param += '&haThnIniBr=' + haThnIniBr + '&pokokThnIniBr=' + pokokThnIniBr + '&lcThnBr=' + lcThnBr;
	param += '&statBlokBr=' + statBlokBr + '&topoGrafiBr=' + topoGrafiBr + '&thnTmnBr=' + thnTmnBr + '&haNonBr=' + haNonBr;
	param += '&thnAngrnOld=' + thnAngrnOld + '&oldBlok=' + oldBlok + '&topoGrafOld=' + topoGrafOld + '&pkkProdukBr=' + pkkProdukBr;
	if (plsma.checked == true) {
		param += '&statPlasmaBr=P';
	}
	tujuan = 'budget_slave_5blok.php';
	if (x == 1 && confirm('Anda Yakin Melakukan Proses Ini?')) {
		post_response_text(tujuan, param, respog);
	} else if (x != 1) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					b = x;
					row = x + 1;
					x = row;
					if (x <= totRow) {
						document.getElementById('rewBr_' + b).style.backgroundColor = 'green';
						saveAllBr(x);
					} else {
						document.getElementById('thnAngrnOld').value = '';
						document.getElementById('oldBlok').value = '';
						document.getElementById('topoGrafOld').value = '';
						document.getElementById('thnTmnBr_' + b).value = '';
						document.getElementById('kdBlokBr_' + b).value = '';
						document.getElementById('hathnIniBr_' + b).value = '0';
						document.getElementById('hathnIniBr_' + b).value = '0';
						document.getElementById('pokokThnINiBr_' + b).value = '0';
						document.getElementById('statBlokBr_' + b).value = 'TM';
						document.getElementById('topoGrafiBr_' + b).value = 'B1';
						document.getElementById('pkkProdukBr_' + b).value = '0';
						loadData();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function fillField(tahunbudget,kbn,afd,kodeblok,hathnini,pokokthnini,statusblok,topografi,thntnm,lcthnini,hanonproduktif,pokokproduksi,ip,blok){
	document.getElementById('thn_1').innerHTML=tahunbudget;
	document.getElementById('kdKbn_1').innerHTML=kbn;
	document.getElementById('kdAfdling_1').value=afd;
	document.getElementById('kdBlokBr_1').value=kodeblok;
	document.getElementById('hathnIniBr_1').value=hathnini;
	document.getElementById('pokokThnINiBr_1').value=pokokthnini;
	document.getElementById('statBlokBr_1').value=statusblok;
	document.getElementById('topoGrafiBr_1').value=topografi;
	document.getElementById('thnTmnBr_1').value=thntnm;
	document.getElementById('lcThnBr_1').value=lcthnini;
	document.getElementById('haNonBr_1').value=hanonproduktif;
	document.getElementById('pkkProdukBr_1').value=pokokproduksi;
	document.getElementById('thnAngrnOld').value=tahunbudget;
	document.getElementById('oldBlok').value=blok;
	document.getElementById('topoGrafOld').value=topografi;
	if(ip=='I'){		
		document.getElementById('statPlasmaBr_1').checked=false;
	}else{
		document.getElementById('statPlasmaBr_1').checked=false;
	}
}

function addDetail(b) {
	saveAllBr(b);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loadDataLama(paged);
}

function loadDataLama(page) {
	thnbgt = document.getElementById('thnbgtsch').value;
	kodeorg= document.getElementById('kodeorgsch').value;
	divisi = document.getElementById('divisisch').value;
	
	param  = 'proses=loadDataLama';
	param += '&thnbgt=' + thnbgt;
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&page=' + page;
	
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('containData').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '300';
	height = '100';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function datakeExcel(ev, thndget, blkid, smbr,jenis) {
	param = 'proses=printExcel' + '&thnAngrn=' + thndget + '&sumber=' + smbr + '&afdId=' + blkid+ '&jenis=' + jenis;
	tujuan = 'budget_slave_5blok.php';
	judul = 'List Data';
	//printFile(param, tujuan, judul, ev)
	
	
	printnopopup(tujuan+"?"+param);
}

function preview(ev, thndget, blkid, smbr,jenis){	
	// width = '';
	// height = '';
	// content = "<fieldset><div id=contpreview align=center style=\"max-width:700px;max-height:500px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog1(title, content, width, height, ev);
	param = 'proses=printExcel' + '&thnAngrn=' + thndget + '&sumber=' + smbr + '&afdId=' + blkid+ '&jenis=' + jenis;
	
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
					// document.getElementById('contpreview').innerHTML = con.responseText;
					//leftFixedTable();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function cekThis(j) {
	pkkThnIni = document.getElementById('pokokThnINi_' + j).value;
	pkkProduktip = document.getElementById('pkkProduk_' + j).value;
	if (parseFloat(pkkProduktip) > parseFloat(pkkThnIni)) {
		alert("Pokok Produktif Tidak Boleh Lebih Besar Dari Pokok Tahun Ini");
		document.getElementById('pkkProduk_' + j).value = pkkThnIni;
		document.getElementById('pkkProduk_' + j).focus();
		return;
	}
}

// function cekThis(s) {
	// pkkThnIni = document.getElementById('hathnIniBr_' + s).value;
	// pkkProduktip = document.getElementById('pkkProdukBr_' + s).value;
	// if (parseFloat(pkkProduktip) > parseFloat(pkkThnIni)) {
		// alert("Pokok Produktif Tidak Boleh Lebih Besar Dari Pokok Tahun Ini");
		// document.getElementById('pkkProdukBr_' + s).value = pkkThnIni;
		// document.getElementById('pkkProdukBr_' + s).focus();
		// return;
	// }
// }
function getThnBudgt() {
	param = 'proses=getThnBudgt';
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('thnBudget').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
