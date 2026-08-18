function edit(blok,nama,jenisfee,akun,rp,jenisvhc) {
	document.getElementById('unit').value=blok.substr(0,4);
	document.getElementById('divisi').value=blok.substr(0,6);
	document.getElementById('blok').value=blok;
	document.getElementById('namafee').value=nama;
	document.getElementById('jenisfee').value=jenisfee;
	document.getElementById('jenis').value=akun;
	document.getElementById('jenisvhc').value=jenisvhc;
	
	document.getElementById('unit').disabled=true;
	document.getElementById('divisi').disabled=true;
	document.getElementById('blok').disabled=true;
	document.getElementById('namafee').disabled=true;
	document.getElementById('jenisfee').disabled=true;
	document.getElementById('jenis').disabled=true;
	document.getElementById('tahuntanam').disabled=true;
	
	document.getElementById('rpfee').value=rp;
	previewdetail();
}

function getfindtt() {
	find_blok = document.getElementById('find_blok').value;
	
	param = 'find_blok=' + find_blok;
	param += '&method=getfindtt';
	tujuan = 'kebun_slave_5hargafee.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('find_tt').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getblok() {
	find_divisi = document.getElementById('find_divisi').value;
	
	param = 'find_divisi=' + find_divisi;
	param += '&method=getblok';
	tujuan = 'kebun_slave_5hargafee.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("####");
					document.getElementById('find_blok').innerHTML = isi[0];
					document.getElementById('find_tt').innerHTML = isi[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function gettahuntanam(e) {
	unit      = document.getElementById('unit').value;
	divisi    = document.getElementById('divisi').value;
	tahuntanam= document.getElementById('tahuntanam').value;
	
	param = 'unit=' + unit;
	param += '&divisi=' + divisi;
	param += '&tahuntanam=' + tahuntanam;
	param += '&method=gettahuntanam';
	tujuan = 'kebun_slave_5hargafee.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					i = con.responseText.split("####");
					if(e=='unit'){
						document.getElementById('divisi').innerHTML = i[0];
						document.getElementById('tahuntanam').innerHTML = i[1];
						document.getElementById('blok').innerHTML = i[2];
					}else if(e=='divisi'){
						document.getElementById('tahuntanam').innerHTML = i[1];
						document.getElementById('blok').innerHTML = i[2];
					}else{
						document.getElementById('blok').innerHTML = i[2];
					}
					document.getElementById('namafee').innerHTML = i[3];
					
					
					jenis = document.getElementById('jenisfee');
					for (x = 0; x < jenis.length; x++) {
						if (jenis.options[x].value == 'tempunak' && unit!='SD3E') {
							jenis.options[x].disabled = true;
						}else{
							jenis.options[x].disabled = false;
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewdetail() {
	unit = document.getElementById('unit').value;
	tahuntanam = document.getElementById('tahuntanam').value;
	divisi = document.getElementById('divisi').value;
	pkstujuanht = document.getElementById('pkstujuanht').value;
	jnskendht = document.getElementById('jnskendht').value;
	blok = document.getElementById('blok').value;
	
	param = 'unit=' + unit;
	param += '&tahuntanam=' + tahuntanam;
	param += '&divisi=' + divisi;
	param += '&pkstujuanht=' + pkstujuanht;
	param += '&jnskendht=' + jnskendht;
	param += '&blok=' + blok;
	param += '&method=previewdetail';
	tujuan = 'kebun_slave_5hargafee.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailinput').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail(currRow, maxRow) {	
	method  = document.getElementById('method').value;
	unit    = document.getElementById('unit').value;
	blok    = document.getElementById('blok').value;
	namafee = document.getElementById('namafee').value;
	jenisfee= document.getElementById('jenisfee').value;
	jenis   = document.getElementById('jenis').value;
	rpfee   = document.getElementById('rpfee').value;
	jenisvhc   = document.getElementById('jenisvhc').value;
	

	param = "";
	param += '&unit=' + unit;
	param += '&blok=' + blok;
	param += '&namafee=' + namafee;
	param += '&jenisfee=' + jenisfee;
	param += '&jenis=' + jenis;
	param += '&rpfee=' + rpfee;
	param += '&jenisvhc=' + jenisvhc;
	param += '&method=' + method;
	
	tujuan = 'kebun_slave_5hargafee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done');
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function bataldetail() {
	document.getElementById('unit').disabled=false;
	document.getElementById('divisi').disabled=false;
	document.getElementById('blok').disabled=false;
	document.getElementById('namafee').disabled=false;
	document.getElementById('jenisfee').disabled=false;
	document.getElementById('jenis').disabled=false;
	document.getElementById('tahuntanam').disabled=false;
	document.getElementById('jenisvhc').disabled=false;
	// document.getElementById('unit').value = '';
	// document.getElementById('tahuntanam').value = '';
	// document.getElementById('divisi').value = '';
	document.getElementById('rpfee').value = '';
	
	document.getElementById('method').value = 'insert';
}
function batalcari() {
	document.getElementById('namacr').value = '';
	document.getElementById('blokcr').value = '';
	document.getElementById('jeniscr').value = '';
	loaddata();
}
function loaddata(num) {
	nama = document.getElementById('namacr').value;
	blok = document.getElementById('blokcr').value;
	jenis = document.getElementById('jeniscr').value;
	jeniskend = document.getElementById('jeniskendcr').value;
	
	param = 'method=loaddata';
	param += '&page=' + num + '&nama=' + nama + '&blok=' + blok;
	param += '&jenis=' + jenis;
	param += '&jeniskend=' + jeniskend;
	tujuan = 'kebun_slave_5hargafee.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
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

function del(blok,nama,jenisfee,jenis,jenisvhc) {
	param = 'method=delete' + '&blok=' + blok;
	param += '&nama=' + nama;
	param += '&jenisfee=' + jenisfee;
	param += '&jenis=' + jenis;
	param += '&jenisvhc=' + jenisvhc;
	tujuan = 'kebun_slave_5hargafee.php';
	if (confirm(' Anda yakin ???')) {
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
