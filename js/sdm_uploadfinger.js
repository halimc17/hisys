function form(){
	width = '';
	height = '';
	content = "<fieldset><div id=container align=center style=\"width:1024px;max-height:400px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}

function previewtr(ev,notransaksi,tipe){
	param='notransaksi='+notransaksi+'&method=previewtr';
	param += '&tipe=' + tipe;
	tujuan='sdm_slave_uploadfinger.php';
	if(tipe=='html'){
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState == 4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert(con.responseText);
					}else{
						form();
						document.getElementById('container').innerHTML=con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}else if(tipe=='pdf'){
		title='PDF';
		tujuan=tujuan+"?"+param;  
		width = 1024;
		height = 400;
		content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
	}
	
}

function getsatuan(){
	komoditi = document.getElementById('komoditi').value;
	
	param = 'method=getsatuan'+'&komoditi='+komoditi;
	tujuan = 'sdm_slave_uploadfinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('lblsatuan').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	var file = document.getElementById('filex').files[0];
	method = document.getElementById('method').value;
	unit = document.getElementById('unit').value;
	idx = document.getElementById('idx').value;
	tanggal = document.getElementById('tanggal').value;
	
	var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('filex'));
    formdata.append("method", method);
    formdata.append("unit", unit);
    formdata.append("tanggal", tanggal);
    formdata.append("idx", idx);

    if(unit == ''){
		alert("Warning : Harap unit diisikan.");
		return false;
    }else if (getValue('filex') == "") {
		alert("Warning : Tidak ada data yang di upload !");
		return false;
	}

	busy_on();
    var con = createXMLHttpRequest();
    con.open("POST", "sdm_slave_uploadfinger.php?method="+method, true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);

    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alert("Data berhasil di simpan.");
					showalllist(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function form_ajukan(noba){
    width = '300';
    height = '';
    content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog1(title, content, width, height, ev);
    
    param = 'method=form_ajukan&noba='+noba;
    tujuan = 'sdm_slave_uploadfinger.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else
                {
                    document.getElementById('containeraju').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukan(){
    notransaksi     =document.getElementById('notransaksi_ajukan').value;
    jlh         =document.getElementById('jlh').value;
    var param   = 'method=ajukan';
    param       += '&noba=' + notransaksi;
    param       += '&jlh=' + jlh;
    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada'+ i + "=" + document.getElementById('kepada'+i).value;;
    }
    tujuan = 'sdm_slave_uploadfinger.php';
    closeDialog();
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alert('Sucses');
                    loaddata(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showalllist(pg) {
	document.getElementById('unitsc').value = '';
	document.getElementById('tanggalsc').value = '';
	document.getElementById('filex').value = '';
	document.getElementById('form_ba').style.display = 'none';
	document.getElementById('list_ba').style.display = 'block';
	loaddata(pg);
}

function displayFormInput() {
	clear_all_data();
	document.getElementById('list_ba').style.display = 'none';
	document.getElementById('form_ba').style.display = 'block';
}

function batal(){
	getpage();
	document.getElementById('list_ba').style.display = 'block';
	document.getElementById('form_ba').style.display = 'none';
}

function clear_all_data(){
	document.getElementById('unit').selectedIndex=0;
	document.getElementById('method').value="insert";
	document.getElementById('idx').value="";
	document.getElementById('lblmethod').innerHTML="(New)";
	
	document.getElementById('unit').disabled=false;
	
}

function getba(id) {
	idEle = document.getElementById(id);
	tanggal = document.getElementById('tanggal').value;
	unit = document.getElementById('unit').value;
	method = document.getElementById('method').value;
	if(method == 'insert'){
		param = 'method=getba'+'&tanggal='+tanggal+'&unit='+unit;
		tujuan = 'sdm_slave_uploadfinger.php';
		post_response_text(tujuan, param, respog);
		
		function respog() {
			if(con.readyState == 4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert(con.responseText);
						idEle.value = "";
						// console.log(document.getElementById(ele).options[0]);
						// if(typeof ele != 'undefined'){
							// document.getElementById(ele).options[0].selected = true;
						// }
					}else{
						idEle.value = con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}
function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(pg) {
	unitsc = document.getElementById('unitsc').value;
	tanggalsc = document.getElementById('tanggalsc').value;
	
	param = 'method=loaddata'+'&page='+pg+'&unitsc='+unitsc+'&tanggalsc='+tanggalsc;
	tujuan = 'sdm_slave_uploadfinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contain').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function edit(id){
	param = "method=edit&idx="+id;
	tujuan = 'sdm_slave_uploadfinger.php';
	post_response_text(tujuan, param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('list_ba').style.display = 'none';
					document.getElementById('form_ba').style.display = 'block';
					data = con.responseText.split("####");
					document.getElementById('idx').value=data[0];
					document.getElementById('tanggal').value=data[1];
					document.getElementById('unit').value=data[2];
					
					
					
					document.getElementById('method').value="update";
					document.getElementById('lblmethod').innerHTML="(Edit)";
					
					document.getElementById('tanggal').disabled=true;
					document.getElementById('unit').disabled=true;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedata(id){
	param = "method=deletedata&idx="+id;
	tujuan = 'sdm_slave_uploadfinger.php';
	
	if (confirm('Anda yakin menghapus data ini?')) {
		post_response_text(tujuan, param, respon);
	}
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					getpage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingdata(id){
	param = "method=postingdata&idx="+id;
	tujuan = 'sdm_slave_uploadfinger.php';
	
	if (confirm('Anda yakin posting data ini?')) {
		post_response_text(tujuan, param, respon);
	}
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					getpage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukantr(notransaksi,ev){
	title = "";
	width = '';
	height = '';
	content = "<fieldset><legend><b><label id='legendnopp'>Form Pengajuan</label></b></legend><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = (pos[0] - 300) + 'px';
	
	param = 'notransaksi='+notransaksi+'&method=formajukantr';
	post_response_text('sdm_slave_uploadfinger.php', param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contDetail').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function previewDetail(notransaksi){
    param   =  'method=previewDetail';
    param   += '&notransaksi=' + notransaksi;
    tujuan  =  'sdm_slave_uploadfinger.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    // title   = 'Detail Data';
                    // width   = '';
                    // height  = '';
                    // ev      = 'event';
                    // content = "<fieldset style=max-width:600px>"+con.responseText+"</fieldset>";
                    // closeDialog();
                    // showDialog2(title, content, width, height, ev);
                    alertify.popup2("Detail Data",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// Upload File
function showupload(notransaksi){
	ev = 'event';
	showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	
	tujuan='sdm_slave_uploadfinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}
function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form Upload</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic2').style.display = '';
}
function submitfile(notransaksi) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alertify.alert("Upload file has been empty.");
		return false;
	}
	if(notransaksi==''){
		alertify.alert("Nomor transaksi tidak ditemukan.");
		return false;
	}

	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').style.display="none";
	busy_on();
	con.open("POST", "sdm_slave_uploadfinger.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//=== Success Response
					alertify.alert('Uploaded Success.');
					document.getElementById('btnsubmit').style.display="";
					document.getElementById("upload").value = "";
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'sdm_slave_uploadfinger.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'sdm_slave_uploadfinger.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formupload() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewupload style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}
function viewfile(idfile,sumber) {
	formupload();
	param = 'method=viewfile&idfile=' + idfile;
	tujuan = 'sdm_slave_uploadfinger.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contviewupload').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
// Upload FIle
function simpanapproval(){
	notransaksi=document.getElementById('appnotransaksi').value;
	level=document.getElementById('level').value;
	approval=document.getElementById('approval').value;
	
	if(approval==''){
		alert('Gagal, Penyetuju masih belum di setting, silahkan hubungi Administrator');
		return false;
	}
	
	param = "method=simpanapproval&notransaksi="+notransaksi+'&approval='+approval+'&level='+level;
	tujuan = 'sdm_slave_uploadfinger.php';
	
	if (confirm('Anda yakin ajukan notransaksi '+notransaksi+'?')) {
		post_response_text(tujuan, param, respon);
	}
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					closeDialog();
					getpage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}