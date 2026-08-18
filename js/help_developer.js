function add_new_data(){
    document.getElementById('headher').style.display='block';
    document.getElementById('listData').style.display='none';
	batal();
	document.getElementById('method').value='insert';
}


function displayList() {
	document.getElementById('listData').style.display='block';
	document.getElementById('headher').style.display='none';
	document.getElementById('keterangansch').value='';
	loaddata();
}

function batal() {
	CKEDITOR.instances.keterangan.setData('');
	document.getElementById('keterangansch').value = '';
	document.getElementById('tipe').value = '';
	document.getElementById('judul').value = '';
	document.getElementById('method').value = 'insert';
	// loaddata(0);
}

function batalcari() {
	document.getElementById('keterangansch').value = '';
	loaddata();
}
function loaddata(num) {
	keterangansch = document.getElementById('keterangansch').value;
	param = 'method=loaddata';
	param += '&page=' + num + '&keterangansch=' + keterangansch;
	tujuan = 'help_slave_developer.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan() {
	tipe = document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value;
	kode = document.getElementById('kode').value;
	judul = document.getElementById('judul').value;
	keterangan =  CKEDITOR.instances.keterangan.getData();
	method = document.getElementById('method').value;
	if (tipe == '' || judul == '' || keterangan == '') {
		alert('Field Was Empty');
		return false;
	}
	param = 'method=' + method + '&keterangan=' + keterangan + '&kode=' + kode;
	param += '&judul=' + judul + '&tipe=' + tipe;
	tujuan = 'help_slave_developer.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batal();
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdata(kode){
	param = 'method=getketerangan' + '&kode=' + kode;
	tujuan = 'help_slave_developer.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					arr=con.responseText.split("####");
					document.getElementById('judul').value=arr[0];
					document.getElementById('tipe').value=arr[1];
					CKEDITOR.instances.keterangan.insertHtml(arr[2]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(kode) {
	CKEDITOR.instances.keterangan.setData('');
	document.getElementById('listData').style.display='none';
	document.getElementById('headher').style.display='block';
	document.getElementById('kode').value = kode;
	document.getElementById('method').value = 'update';
	getdata(kode);
}

function del(kode) {
	param = 'method=delete' + '&kode=' + kode;
	tujuan = 'help_slave_developer.php';
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
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formx() {
	width = '720';
	height = '';
	content = "<div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
}

function viewpdf(kode) {
	param = 'method=viewpdf' + '&kode=' + kode;
	tujuan = 'help_slave_developer.php?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}

function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic5').style.top = (pos[1] - 300) + 'px';
	document.getElementById('dynamic5').style.left = (pos[0] - 200) + 'px';
	document.getElementById('dynamic5').style.display = '';
}
