function clearperiode(){
	document.getElementById('tglsampai').value='';
}

function popupnotranindk(ev){
	width = '';
	height = '';
	content = "<fieldset><div id=popuppreview style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	title = "&nbsp;&nbsp;Cari No Transaksi Induk";
	showDialog5(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);	
	document.getElementById('dynamic5').style.top = pos[1] + 'px';
	
	param='method=popupnotranindk';
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('popuppreview').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function carikontrakinduk(){
	snotransaksiinduk = document.getElementById('snotransaksiinduk').value;
	
	param='method=carikontrakinduk&snotransaksiinduk='+snotransaksiinduk;
	tujuan = 'log_slave_kontrakjasa.php';
	post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listnokontrakinduk').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function setnokontrak(notransaksiinduk){
	param='method=setnokontrak&notransaksiinduk='+notransaksiinduk;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);		
		
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					closeDialog5();
					vsplt=con.responseText.split("##");
					var data = JSON.parse(vsplt[0]);
					
					document.getElementById('notransaksiinduk').value=notransaksiinduk;
					document.getElementById('tanggalkontrak').value=data[0]['tanggalkontrak'];
					document.getElementById('deskripsi').value=data[0]['deskripsi'];
					document.getElementById('supplier').value=data[0]['supplier'];
					document.getElementById('tgldari').value=data[0]['tgldari'];
					document.getElementById('tglsampai').value=data[0]['tglsampai'];
					document.getElementById('spesifikasi').value=data[0]['spesifikasi'];
					document.getElementById('uangmuka').value=data[0]['uangmuka'];
					document.getElementById('retensipersen').value=data[0]['retensipersen'];
					document.getElementById('retensinilai').value=data[0]['retensinilai'];
					document.getElementById('clne').value='1';
					
					getunit('',loadpajak);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function getunit(unit,newFunc){
	pt=document.getElementById('pt').value;
	
	param='method=getunit&pt='+pt+'&unit='+unit;
	tujuan = 'log_slave_kontrakjasa.php';
	post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('unit').innerHTML=con.responseText;
					if(typeof newFunc !== 'undefined' && typeof newFunc == 'function'){
						eval(newFunc());
					}
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function addpajak(){
	jenispajak=document.getElementById('jenispajak').value;
	nilaipajak=document.getElementById('nilaipajak').value;
	
	param='method=addpajak&jenispajak='+jenispajak+'&nilaipajak='+nilaipajak;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadpajak();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function  loadpajak(){
	document.getElementById('jenispajak').selectedIndex=0;
	document.getElementById('nilaipajak').value=0;
	param='method=loadpajak';
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listpajak').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function deletepajak(jenispajak){
	param='method=deletepajak&jenispajak='+jenispajak;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadpajak();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function batal(){
	document.getElementById('notransaksi').value="";
	document.getElementById('notransaksiinduk').value="";
	document.getElementById('deskripsi').value="";
	document.getElementById('supplier').selectedIndex=0;
	document.getElementById('spesifikasi').value='';
	document.getElementById('uangmuka').value='';
	document.getElementById('retensinilai').value='';
	document.getElementById('retensipersen').value='';
	document.getElementById('jenispajak').selectedIndex=0;
	document.getElementById('nilaipajak').value=0;
	document.getElementById('listitem').style.display='none';
	document.getElementById('clne').value='0';
	
	document.getElementById('imgnotransaksiinduk').style.display='';
	document.getElementById('notransaksiinduk').disabled=false;
	document.getElementById('pt').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('tanggalkontrak').disabled=false;
	
	getunit('',clearpajak);
}

function clearpajak(){
	param='method=clearpajak';
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadpajak();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function simpan(){
	notransaksi     =document.getElementById('notransaksi').value;
	notransaksiinduk=document.getElementById('notransaksiinduk').value;
	pt              =document.getElementById('pt').value;
	unit            =document.getElementById('unit').value;
	tanggalkontrak  =document.getElementById('tanggalkontrak').value;
	deskripsi       =document.getElementById('deskripsi').value;
	supplier        =document.getElementById('supplier').value;
	tgldari         =document.getElementById('tgldari').value;
	tglsampai       =document.getElementById('tglsampai').value;
	spesifikasi     =document.getElementById('spesifikasi').value;
	uangmuka        =document.getElementById('uangmuka').value;
	retensinilai    =document.getElementById('retensinilai').value;
	retensipersen   =document.getElementById('retensipersen').value;
	clne            =document.getElementById('clne').value;
	
	param='method=simpan&notransaksi='+notransaksi+'&notransaksiinduk='+notransaksiinduk+'&pt='+pt+'&unit='+unit+'&tanggalkontrak='+tanggalkontrak+'&deskripsi='+deskripsi+'&supplier='+supplier+'&tgldari='+tgldari+'&tglsampai='+tglsampai+'&spesifikasi='+spesifikasi+'&uangmuka='+uangmuka+'&retensipersen='+retensipersen+'&retensinilai='+retensinilai+'&clne='+clne;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					if(notransaksi==''){
						document.getElementById('listitem').style.display='';
					}
					
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('imgnotransaksiinduk').style.display='none';
					document.getElementById('notransaksiinduk').disabled=true;
					document.getElementById('pt').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('tanggalkontrak').disabled=true;
					document.getElementById('clne').value='0';
					document.getElementById('listitem').style.display='block';
					loaddt(con.responseText,'');
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function loaddata(pg){
	scnotransaksi = document.getElementById('scnotransaksi').value;
	
	param='method=loaddata&page='+pg+'&scnotransaksi='+scnotransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listdata').style.display='block';
					document.getElementById('forminput').style.display='none';
					vsplt = con.responseText.split("####");
					document.getElementById('contain').innerHTML=vsplt[0];
					document.getElementById('containft').innerHTML=vsplt[1];
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}		
}

function ubah(notransaksi){
	param='method=ubah&notransaksi='+notransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	document.getElementById('listdata').style.display='none';
	document.getElementById('forminput').style.display='block';
	document.getElementById('listitem').style.display='block';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('notransaksi').value=notransaksi;
					vsplt=con.responseText.split("##");
					var data = JSON.parse(vsplt[0]);
					
					document.getElementById('notransaksiinduk').value=data[0]['notransaksiinduk'];
					document.getElementById('pt').value=data[0]['pt'];
					document.getElementById('tanggalkontrak').value=data[0]['tanggalkontrak'];
					document.getElementById('deskripsi').value=data[0]['deskripsi'];
					document.getElementById('supplier').value=data[0]['supplier'];
					document.getElementById('tgldari').value=data[0]['tgldari'];
					document.getElementById('tglsampai').value=data[0]['tglsampai'];
					document.getElementById('spesifikasi').value=data[0]['spesifikasi'];
					document.getElementById('uangmuka').value=data[0]['uangmuka'];
					document.getElementById('retensipersen').value=data[0]['retensipersen'];
					document.getElementById('retensinilai').value=data[0]['retensinilai'];
					document.getElementById('listdt').innerHTML=vsplt[1];
					
					document.getElementById('imgnotransaksiinduk').style.display='none';
					document.getElementById('notransaksiinduk').disabled=true;
					document.getElementById('pt').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('tanggalkontrak').disabled=true;
					
					getunit(data[0]['unit'],loadpajak);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function hapus(notransaksi){
	param='method=hapus&notransaksi='+notransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
	if(confirm('Anda yakin hapus no transaksi '+notransaksi+'?')){
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
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

function posting(notransaksi){
	param='method=posting&notransaksi='+notransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
	if(confirm('Anda yakin posting no transaksi '+notransaksi+'?')){
		post_response_text(tujuan, param, respog);		
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
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

function closekotrakform(notransaksi,ev){
	width = '';
	height = '';
	content = "<fieldset><div id=popuppreview style='overflow:auto;width:auto;height:auto;'></div></fieldset>";
	title = "&nbsp;&nbsp;Tutup/Close Kontrak";
	showDialog5(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);	
	document.getElementById('dynamic5').style.top = pos[1] + 'px';
	document.getElementById('dynamic5').style.left = (pos[0]-300) + 'px';
	
	param='method=closekotrakform&notransaksi='+notransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('popuppreview').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function closekontrak(){
	notransaksi=document.getElementById('notransaksiclose').value;
	keteranganclose=document.getElementById('keteranganclose').value;
	
	param='method=closekontrak&notransaksi='+notransaksi+'&keteranganclose='+keteranganclose;
    tujuan = 'log_slave_kontrakjasa.php';
	post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					closeDialog5();
					getpage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}


function adddt(x=''){
	notransaksi=document.getElementById('notransaksi'+x).value;
	tipedt=document.getElementById('tipedt'+x).value;
	kegiatandt=document.getElementById('kegiatandt'+x).value;
	satuandt=document.getElementById('satuandt'+x).value;
	rpdt=document.getElementById('rpdt'+x).value;
	ketegoridt=document.getElementById('ketegoridt'+x).value;
		
	param='method=adddt&notransaksi='+notransaksi+'&tipedt='+tipedt+'&kegiatandt='+kegiatandt+'&satuandt='+satuandt+'&rpdt='+rpdt+'&insertafter='+x;
	param += "&ketegoridt=" + ketegoridt
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaddt(notransaksi,x);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function bataldt(x=''){
	document.getElementById('kegiatandt'+x).value='';
	document.getElementById('satuandt'+x).selectedIndex=0;
	document.getElementById('rpdt'+x).value='';
}



function savecategorydt(notransaksi,kegiatandt,ketegoridt){
	param='method=savecategorydt&notransaksi='+notransaksi+'&kegiatandt='+kegiatandt+'&ketegoridt='+ketegoridt;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaddt(notransaksi,'x');
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function loaddt(notransaksi,x=''){
	document.getElementById('kegiatandt').value='';
	document.getElementById('satuandt').value='';
	document.getElementById('rpdt').value='';
	
	param='method=loaddt&notransaksi='+notransaksi+'&insertafter='+x;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listdt'+x).innerHTML=con.responseText;
					bataldt(x);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function hapusdt(notransaksi,kegiatandt,x=''){
	document.getElementById('kegiatandt').value='';
	document.getElementById('satuandt').value='';
	document.getElementById('rpdt').value='';
	
	param='method=hapusdt&notransaksi='+notransaksi+'&kegiatandt='+kegiatandt;
    tujuan = 'log_slave_kontrakjasa.php';
	if(confirm('Anda yakin hapus no kegiatan/material '+kegiatandt+'?')){
		post_response_text(tujuan, param, respog);		
	}
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaddt(notransaksi,x);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function displayforminput(){
	document.getElementById('listdata').style.display='none';
	document.getElementById('forminput').style.display='block';
	batal();
}

function displaylist(){
	document.getElementById('listdata').style.display='block';
	document.getElementById('forminput').style.display='none';
	document.getElementById('scnotransaksi').value='';
	loaddata(0);
}

function getpage(){
	pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function preview(notransaksi,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><div id=popuppreview style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	// title = "&nbsp;&nbsp;Preview";
	// showDialog5(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic5').style.top = pos[1] + 'px';
	// document.getElementById('dynamic5').style.left = (pos[0]-600) + 'px';
	
	param='method=preview&notransaksi='+notransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//document.getElementById('popuppreview').innerHTML=con.responseText;
					alertify.popup2("Preview",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function previewpdfgr(ev,notransaksi){	
	param='method=previewpdfgr&notransaksi='+notransaksi;
    tujuan = 'log_slave_noninventory.php';
    
	title='Report PDF';
	tujuan=tujuan+"?"+param;  
	width = 1024;
	height = 500;
	content = "<iframe frameborder=0 width=1024px height=500px src='" + tujuan + "'></iframe>"
	showDialog5(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);	
	document.getElementById('dynamic5').style.top = pos[1] + 'px';
	document.getElementById('dynamic5').style.left = (pos[0]-600) + 'px';
}

function previewba(notransaksi,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><div id=popuppreview style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	// title = "&nbsp;&nbsp;Preview";
	// showDialog1(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-800) + 'px';
	
	param='method=preview&notransaksi='+notransaksi;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//document.getElementById('popuppreview').innerHTML=con.responseText;
					alertify.popup("Preview",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

// Fungsi Baru Rizky
function showupload(notransaksi){
	ev = 'event';
	showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	
	tujuan='log_slave_kontrakjasa.php';
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

// Fungsi Baru Rizky
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

// Fungsi Baru Rizky
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
	con.open("POST", "log_slave_kontrakjasa.php?method=submitfile", true);
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

// Fungsi Baru Rizky
function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'log_slave_kontrakjasa.php';
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

// Fungsi Baru Rizky
function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'log_slave_kontrakjasa.php';
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

// Fungsi Baru Rizky
function formupload() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewupload style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}

// Fungsi Baru Rizky
function viewfile(idfile,sumber) {
	formupload();
	param = 'method=viewfile&idfile=' + idfile;
	tujuan = 'log_slave_kontrakjasa.php';
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