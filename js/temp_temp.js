
function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function displayList() {
	document.getElementById('mode').value = 'baru';
	document.getElementById('notransaksisch').value = '';
	
	
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
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
	param = 'method=loaddata&page=' + page;
	
	if (notransaksisch != '') {
		param += '&notransaksisch=' + notransaksisch;
	}
	
	tujuan = 'temp_slave_temp.php';
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
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('mode').value = 'baru';
}


function edit(notransaksi, tgl, kodeorg, nobkm, mandor, mandor1, kerani,sts) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('tgl').value = tgl;
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('nobkm').value = nobkm;
	document.getElementById('mandor').value = mandor;
	document.getElementById('mandor1').value = mandor1;
	document.getElementById('kerani').value = kerani;
	document.getElementById('status').value = sts;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	document.getElementById('mode').value = 'edit';
	addHeader(notransaksi);
}
function deletedetail(notransaksi, karyawanid, blok, numrow) {
	param = 'method=deletedetail' + '&notransaksi=' + notransaksi + '&karyawanid=' + karyawanid + '&blok=' + blok;
	tujuan = 'temp_slave_temp.php';
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
					loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function addHeader() {
	kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	mandor = document.getElementById('mandor').value;
	mandor1 = document.getElementById('mandor1').value;
	asst = document.getElementById('asst').value;
	kerani = document.getElementById('kerani').value;
	nobkm = document.getElementById('nobkm').value;
	tgl = document.getElementById('tgl').value;
	mode = document.getElementById('mode').value;
	document.getElementById('status').disabled = true;
	notransaksi = document.getElementById('notransaksi').value;
	if (tgl == '' || kodeorg == '') {
		alert('Tanggal dan atau Kode Organisasi harus di isi !');
		return;
	}
	if(mode=='baru'){
		document.getElementById('tomboldetail').disabled = true;
	}else{
		document.getElementById('tomboldetail').disabled = false;
	}
	param = 'method=detail';
	param += '&tgl=' + tgl + '&kodeorg=' + kodeorg + '&nobkm=' + nobkm + '&mandor=' + mandor + '&mandor1=' + mandor1 + '&asst=' + asst + '&kerani=' + kerani + '&notransaksi=' + notransaksi+ '&mode=' + mode;
	tujuan = 'temp_slave_temp.php';
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
					document.getElementById('detail').innerHTML = data[1];
					document.getElementById('notransaksi').value = data[0];
					inputdetail(data[0]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
