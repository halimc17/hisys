function simpan() {
	unit = document.getElementById('unit').value;
	nama = trim(document.getElementById('nama').value);
	alamat = trim(document.getElementById('alamat').value);
	status = document.getElementById('status').value;
	method = document.getElementById('method').value;
	id = document.getElementById('id').value;
	
	param = 'unit=' + unit;
	param += '&nama=' + nama;
	param += '&id=' + id;
	param += '&alamat=' + alamat;
	param += '&status=' + status;
	param += '&method=' + method;
	tujuan = 'kebun_slave_5namafee.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batal();
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function batalcari() {
	document.getElementById('find_nama').value = '';
	document.getElementById('find_unit').value = '';
	loaddata();
}
function loaddata(num) {
	find_nama = document.getElementById('find_nama').value;
	find_unit = document.getElementById('find_unit').value;
	
	param = 'method=loaddata';
	param += '&page=' + num + '&find_nama=' + find_nama;
	param += '&find_unit=' + find_unit;
	tujuan = 'kebun_slave_5namafee.php';
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

function edit(id,nama,alamat,kodeorg,stat){
	document.getElementById('unit').value=kodeorg;
	document.getElementById('nama').value=nama;
	document.getElementById('alamat').value=alamat;
	document.getElementById('status').value=stat;
	document.getElementById('id').value=id;
	document.getElementById('method').value='update';
}

function batal(){
	document.getElementById('id').value='';
	document.getElementById('nama').value='';
	document.getElementById('alamat').value='';
	document.getElementById('status').value='1';
	document.getElementById('method').value='insert';
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function del(id) {
	param = 'method=delete' + '&id=' + id;
	tujuan = 'kebun_slave_5namafee.php';
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
