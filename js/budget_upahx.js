function add_new_data(){
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}
function form() {
	width = '720';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}
function html(tahun,kodeorg) {
	//form();
	param = 'method=html'  + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_upahx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function displayList() {
	document.getElementById('tahunsch').value = '';
	document.getElementById('kodeorgsch').value = '';

	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('contdetail').style.display = 'none';
	loaddata(0);
}
function edit(tahun,kodeorg) {
	document.getElementById('tahun').value = tahun;
	document.getElementById('kodeorg').value = kodeorg;
	setValue2('kodeorg',kodeorg);
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	getbgtkode();
}
function deletedetail(tahun,kodeorg,golongan) {
	param = 'method=deletedetail' + '&tahun=' + tahun + '&kodeorg=' + kodeorg + '&golongan=' + golongan;
	tujuan = 'budget_slave_upahx.php';
	if(confirm(' Anda yakin ???')){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail(tahun,kodeorg);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function del(tahun,kodeorg) {
	param = 'method=delete' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_upahx.php';
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
function posting(tahun,kodeorg) {
	param = 'method=posting' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_upahx.php';
	
	alertify.confirm("WARNING","Perhatian : Proses ini akan merekalkulasi nilai upah terhadap seluruh data budget yang menggunakan TK, antara lain :<br>1. Upah untuk budget Workshop dan Traksi (Kendaraan).<br>2. Budget biaya kebun atau pabrik.<br>3. Budget biaya umum.<br><br>Apakah anda yakin untuk melanjutkan ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);

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
function unposting(tahun,kodeorg) {
	param = 'method=unposting' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_upahx.php';
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
function insertdetail() {
	tahun   = document.getElementById('tahun').value;
	kodeorg = document.getElementById('kodeorg').value;
	golongan= document.getElementById('golongan').value;
	nilai   = document.getElementById('nilai').value;
	method   = document.getElementById('method').value;
	if (tahun == '' || kodeorg == '' || golongan == '' || nilai == '') {
		alert('Semua kolom harus terisi.');
		return;
	}
	param = 'method='+method;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&golongan=' + golongan + '&nilai=' + nilai;
	tujuan = 'budget_slave_upahx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail(tahun,kodeorg);
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
	tahun  = document.getElementById('tahunsch').value;
	kodeorg= document.getElementById('kodeorgsch').value;
	
	param = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_upahx.php';
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancel() {
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('tahun').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('golongan').disabled = false;
	document.getElementById('golongan').value = '';
	document.getElementById('nilai').value = '';
	document.getElementById('method').value = 'insert';
}
function loaddatadetail(tahun, kodeorg) {
	tahun   = document.getElementById('tahun').value;
	kodeorg = document.getElementById('kodeorg').value;
	
	param = 'method=loaddatadetail';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_upahx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contdetail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getbgtkode(golongan) {
	tahun   = document.getElementById('tahun').value;
	kodeorg = document.getElementById('kodeorg').value;
	
	param = 'method=getbgtkode';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&golongan=' + golongan;
	tujuan = 'budget_slave_upahx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('golongan').innerHTML = con.responseText;	
					loaddatadetail(tahun, kodeorg);					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdetail(tahunbudget,kodeorg,golongan,jumlah){
	document.getElementById('tahun').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('golongan').disabled = true;
	document.getElementById('tahun').value = tahunbudget;
	// document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('golongan').value = golongan;
	document.getElementById('nilai').value = jumlah;
	document.getElementById('method').value = 'updatedetail';
	setValue2('kodeorg',kodeorg);
	// setValue2('golongan',golongan);
	getbgtkode(golongan);
}