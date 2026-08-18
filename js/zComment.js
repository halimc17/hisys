var menuDisplayed = false;
var menuBox = null;

function rightclick(ev,e,tipe='0',akun,kodeorg,periode,bi,real){
	if (ev.which == 3) {
		pos = new Array();
		pos = getMouseP(ev);
		
		menuBox = window.document.querySelector(".menu");
		menuBox.style.left = pos[0] + "px";
		menuBox.style.top = pos[1] + "px";
		menuBox.style.display = "block";
		menuDisplayed = true;
		let id = "";
		
		//tipe = 
		//0 baru belum ada comment
		//1 sudah ada comment
		if(tipe=='0'){
			document.getElementById('btninscmnt').style.display = '';
			document.getElementById('btnshowcmn').style.display = 'none';
			document.getElementById('btnreloadframe').style.display = '';
			
			document.getElementById('btninscmnt').onclick = function(){ addcomment(e,id,akun,kodeorg,periode,bi,real); };
			// document.getElementById('btnshowcmn').onclick = function(){ showcomment(id,akun,kodeorg,periode,bi,real); };
			document.getElementById('btnreloadframe').onclick = function(){ reloadframe(); };
		}else if(tipe=='1'){
			document.getElementById('btninscmnt').style.display = '';
			document.getElementById('btnshowcmn').style.display = '';
			document.getElementById('btnreloadframe').style.display = '';
			
			document.getElementById('btninscmnt').onclick = function(){ addcomment(e,id,akun,kodeorg,periode,bi,real); };
			document.getElementById('btnshowcmn').onclick = function(){ showcomment(id,akun,kodeorg,periode,bi,real); };
			document.getElementById('btnreloadframe').onclick = function(){ reloadframe(); };
		}else if(tipe=='2'){
			document.getElementById('btninscmnt').style.display = 'none';
			document.getElementById('btnshowcmn').style.display = '';
			document.getElementById('btnreloadframe').style.display = '';
			
			// document.getElementById('btninscmnt').onclick = function(){ addcomment(e,id,akun,kodeorg,periode,bi,real); };
			document.getElementById('btnshowcmn').onclick = function(){ showcomment(id,akun,kodeorg,periode,bi,real); };
			document.getElementById('btnreloadframe').onclick = function(){ reloadframe(); };
		}
	}
}

document.addEventListener("contextmenu", function (e) {
	e.preventDefault();
}, false);

window.addEventListener("click", function() {
	if(menuDisplayed == true){
		menuBox.style.display = "none"; 
	}
}, true);

function reloadframe(){
	window.location.reload();
}

function showspoiler(e,no){
	if(e=='show'){
		document.getElementById('showspoiler'+no).style.display = 'none';
		document.getElementById('hidespoiler'+no).style.display = 'block';
		document.getElementById('spoiler'+no).style.display = 'block';
	}else{
		document.getElementById('showspoiler'+no).style.display = 'block';
		document.getElementById('hidespoiler'+no).style.display = 'none';
		document.getElementById('spoiler'+no).style.display = 'none';
	}
	
}

function showhidespoiler(e){
	if (e.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') {
		e.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; 
		e.innerText = ''; 
		e.value = 'Hide'; 
	} else { 
		e.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; 
		e.innerText = ''; 
		e.value = 'Show'; 
	}	
}

function delcomment(id,no){
	param = 'method=delcomment';
	param += '&id=' + id;
    tujuan = 'slave_zComment.php';
	alertify.confirm("Delete","Anda yakin ???",
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
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('field'+no).style.display='none';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showcomment(id,akun,kodeorg,periode,bi,real){
	title   = 'Show Comment';
	param   = 'method=showcomment';
	param  += '&id=' + id;
	param  += '&akun=' + akun;
	param  += '&kodeorg=' + kodeorg;
	param  += '&periode=' + periode;
	param  += '&bi=' + bi;
	param  += '&real=' + real;
	tujuan  = 'slave_zComment.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText,'title':title}).resizeTo('70%','80%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}	

function addcomment(e,id,akun,kodeorg,periode,bi,real){	
	title   = 'Insert Comment';
	param   = 'method=addcomment';
	param  += '&id=' + id;
	param  += '&akun=' + akun;
	param  += '&kodeorg=' + kodeorg;
	param  += '&periode=' + periode;
	param  += '&bi=' + bi;
	param  += '&real=' + real;
	tujuan  = 'slave_zComment.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup3().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText,'title':title}).resizeTo('70%','80%').show();
					$(document).ready(function() {
						$('.select2.help').select2({
							dropdownAutoWidth:true
						});
					});
					
					e.setAttribute('class','has_sign');
					e.setAttribute('onmousedown',"rightclick(event,'','1','"+akun+"','"+kodeorg+"','"+periode+"','"+bi+"','"+real+"')");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpancomment(){
	regional   = document.getElementById('region').value;
	kodept     = document.getElementById('kodept').value;
	unit       = document.getElementById('unit').value;
	periode    = document.getElementById('periode').value;
	bi         = document.getElementById('bi').value;
	real       = document.getElementById('real').value;
	id         = document.getElementById('id').value;
	action     = document.getElementById('action').value;
	kegiatan   = document.getElementById('kegiatan').value;
	akun       = document.getElementById('akun').value;
	tags       = $('#mentionuser').val();
	
	penjelasan = document.getElementById('penjelasancomment').value;
	var menuid = $(".badge.badge-info.badge-smaller").attr('onclick');
	
	if (!isSaveResponse(penjelasan)) {
		alertify.alert("errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL");
		throw Error('Stop!');
	}
	
	var formdata = new FormData();
	var totalfiles = document.getElementById('filescomment').files.length;
	if(totalfiles>10){
		alertify.alert("File terlalu banyak, maksimal hanya 10 file."); return;
	}
	for (var i = 0; i < totalfiles; i++) {
		formdata.append("file[]", document.getElementById('filescomment').files[i]);
	}
	
	formdata.append("fileupload", getValue('filescomment'));
	formdata.append("regional", regional);	
	formdata.append("kodept", kodept);	
	formdata.append("unit", unit);	
	formdata.append("periode", periode);	
	formdata.append("bi", bi);	
	formdata.append("real", real);	
	formdata.append("id", id);	
	formdata.append("action", action);	
	formdata.append("kegiatan", kegiatan);	
	formdata.append("akun", akun);	
	formdata.append("penjelasan", penjelasan);	
	formdata.append("menuid", menuid);	
	formdata.append("tags", tags);	
	
	
    busy_on;
	var con = createXMLHttpRequest();
	con.open("POST", "slave_zComment.php?method=simpancomment", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert("Data sudah disimpan.");
					alertify.closeAll();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}