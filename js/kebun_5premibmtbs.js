function gettahuntanam(e) {
	unit = document.getElementById('unit').value;
	
	param = 'unit=' + unit;
	param += '&method=gettahuntanam';
	tujuan = 'kebun_slave_5premibmtbs.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					i = con.responseText.split("####");
					document.getElementById('divisi').innerHTML = i[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function savedetail() {	
	method   = document.getElementById('method').value;
	unit     = document.getElementById('unit').value;
	divisi   = document.getElementById('divisi').value;
	rpangkut = document.getElementById('rpangkut').value;
	denda    = document.getElementById('denda').value;
	toleransi= document.getElementById('toleransi').value;
	tglberlaku= document.getElementById('tglberlaku').value;
	kegiatan= document.getElementById('kegiatan').value;
	jenispremi= document.getElementById('jenispremi').value;
	
	param = "";
	param += '&divisi=' + divisi;
	param += '&unit=' + unit;
	param += '&rpangkut=' + rpangkut;
	param += '&denda=' + denda;
	param += '&toleransi=' + toleransi;
	param += '&tglberlaku=' + tglberlaku;
	param += '&kegiatan=' + kegiatan;
	param += '&jenispremi=' + jenispremi;
	
	param += '&method=' + method;
	tujuan = 'kebun_slave_5premibmtbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					bataldetail();
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function edit(unit,divisi,rpangkut,denda,toleransi,tglberlaku,kegiatan,jenispremi) {
	document.getElementById('unit').value = unit;
	document.getElementById('kegiatan').value = kegiatan;
	document.getElementById('divisi').value = divisi;
	document.getElementById('rpangkut').value = rpangkut;
	document.getElementById('denda').value = denda;
	document.getElementById('toleransi').value = toleransi;
	document.getElementById('tglberlaku').value = tglberlaku;
	document.getElementById('jenispremi').value = jenispremi;
	document.getElementById('tglberlaku').disabled=true;
	document.getElementById('unit').disabled=true;
	document.getElementById('divisi').disabled=true;
	document.getElementById('method').value = 'update';
}

function bataldetail() {
	document.getElementById('tglberlaku').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('divisi').disabled=false;
	document.getElementById('unit').value = '';
	document.getElementById('divisi').value = '';
	document.getElementById('rpangkut').value = '';
	document.getElementById('denda').value = '';
	document.getElementById('toleransi').value = '';
	document.getElementById('tglberlaku').value = '';
	document.getElementById('method').value = 'insert';
}
function batalcari() {
	document.getElementById('find_divisi').value = '';
	loaddata();
}
function loaddata(num) {
	divisi = document.getElementById('find_divisi').value;
	param = 'method=loaddata';
	param += '&page=' + num + '&divisi=' + divisi;
	tujuan = 'kebun_slave_5premibmtbs.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
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

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function del(divisi,tglberlaku,jenispremi) {
	param = 'method=delete' + '&divisi=' + divisi;
	param += '&tglberlaku=' + tglberlaku;
	param += '&jenispremi=' + jenispremi;
	tujuan = 'kebun_slave_5premibmtbs.php';
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	).set('resizable',false).resizeTo(100,250);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
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
