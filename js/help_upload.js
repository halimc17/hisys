function getPage(){
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function displayList(){
	document.getElementById('headher').style.display = 'none';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('scmodul').value = '';
	document.getElementById('scjudul').value = '';
	loaddata(0);
}

function add_new_data() {
	document.getElementById('headher').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	batal();
}

function bersih() {
	document.getElementById('modul').selectedIndex = 0;
	document.getElementById('judul').value = '';
	document.getElementById('bahasa').value = 'ID';
	document.getElementById('method').value = 'insert';
	document.getElementById('modulold').value = '';
	document.getElementById('judulold').value = '';
	loadfiles();
}

function batal() {
	param = 'method=batal';
	tujuan = 'help_slave_upload.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					bersih();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var modul=document.getElementById('modul').options[document.getElementById('modul').selectedIndex].text;
	var bahasa=document.getElementById('bahasa').value;
	var judul=document.getElementById('judul').value;
	var addfile = document.getElementById('addfile');
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("modul", modul);
	formdata.append("bahasa", bahasa);
	formdata.append("judul", judul);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	
	if (getValue('upload') == "") {
		alert("Gagal, File upload masih kosong.");
		return false;
	}
	
	addfile.removeAttribute('onclick');
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "help_slave_upload.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					addfile.setAttribute('onclick',"submitfile()");
					alert(con.responseText);
				} else {
					addfile.setAttribute('onclick',"submitfile()");
					document.getElementsByClassName("mybutton").disabled = false;
					document.getElementById("upload").value = "";
					loadfiles();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles() {
	param='method=loadfiles';
	tujuan='help_slave_upload.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('containerupload').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(namafile) {
	param = 'method=deletefile&namafile='+namafile;
	tujuan = 'help_slave_upload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata(pg){
	scmodul = document.getElementById('scmodul').value;
	scjudul = document.getElementById('scjudul').value;

	param = 'method=loaddata&page='+pg+'&scmodul='+scmodul+'&scjudul='+scjudul;
	tujuan = 'help_slave_upload.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contain').innerHTML = con.responseText;
					document.getElementById('headher').style.display = 'none';
					document.getElementById('listData').style.display = 'block';
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan() {
	modul = document.getElementById('modul').value;
	judul = document.getElementById('judul').value;
	modulold = document.getElementById('modulold').value;
	judulold = document.getElementById('judulold').value;
	method = document.getElementById('method').value;
	
	param = 'modul='+modul+'&judul='+judul+'&modulold='+modulold+'&judulold='+judulold+'&method='+method;
	tujuan = 'help_slave_upload.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('method').value='update';
					alert("Berhasil simpan data.");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fillField(modul, judul){
	document.getElementById('method').value = 'update';
	
	param = 'modul='+modul+'&judul='+judul+'&method=showData';
	tujuan = 'help_slave_upload.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('headher').style.display = 'block';
					document.getElementById('listData').style.display = 'none';					
					document.getElementById('modul').value = modul;
					document.getElementById('modulold').value = modul;
					document.getElementById('judul').value = judul;
					document.getElementById('judulold').value = judul;
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deldata(modul, judul){
	param = 'modul='+modul+'&judul='+judul+'&method=deldata';
	tujuan = 'help_slave_upload.php';
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Anda yakin hapus data ini?"))
		post_response_text(tujuan, param, respog);
}

function viewpdf(modul,modultext,judul,bahasa,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>"+modultext+" - "+judul+"</legend><div id=containerpdf></div></fieldset>";
	title = "";
	showDialogx(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamicx').style.top = pos[1] + 'px';
	document.getElementById('dynamicx').style.left = (pos[0]) + 'px';
	document.getElementById('dynamicx').style.display = '';
	
	var param = "modul="+modul+'&judul='+judul+"&method=viewpdf";
	tujuan = 'help_slave_upload.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('containerpdf').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}