function add_new_data() {
	param = 'proses=CekData';
	//alert(param);
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//	alert(con.responseText);
					document.getElementById('headher').style.display = 'block';
					document.getElementById('listData').style.display = 'none';
					bersih();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function bersih() {
	document.getElementById('supp').selectedIndex = 0;
	
	document.getElementById('kontrak').disabled = false;
	document.getElementById('kontrak').value = '';
	document.getElementById('delivtime').selectedIndex = 0;
	document.getElementById('tmpt_krm').selectedIndex = 0;
	document.getElementById('invc_krm').selectedIndex = 0;
	document.getElementById('term_pay').selectedIndex = 0;
	document.getElementById('ppN').value = '';
	document.getElementById('ppH').value = '';
	document.getElementById('cttn').value = '';
	document.getElementById('status').selectedIndex = 0;
	
	
	document.getElementById('kdbrg').selectedIndex = 0;
	document.getElementById('qty').value = '';
	document.getElementById('harga').value = '';
	document.getElementById('lblkodebarang').innerHTML = '';
	document.getElementById('lblsatuan').innerHTML = '';
	
	getdatasupplier(loaditemkontrak);
}
function cancelSave() {
	param = 'proses=cancelSave';
	tujuan = 'log_slave_kontrakpayung.php';
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

function loadData(pg) {
	kontrakcari = document.getElementById('kontrakcari').value;
	daTtgl = document.getElementById('tgl_cari').value;

	param = 'proses=LoadData&page=' + pg + '&kontrakcari=' + kontrakcari + '&daTtgl=' + daTtgl;
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					document.getElementById('headher').style.display = 'none';
					document.getElementById('listData').style.display = 'block';
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
	loadData(paged);
}

function displayList() {
	document.getElementById('headher').style.display = 'none';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('kontrakcari').value = '';
	document.getElementById('tgl_cari').value = '';
	loadData(0);
}

function saveData() {
	supp = document.getElementById('supp').value;
	alamat_sup = document.getElementById('alamat_sup').value;
	npwp_sup = document.getElementById('npwp_sup').value;
	bank_acc = document.getElementById('bank_acc').value;
	kontrak = document.getElementById('kontrak').value;
	tgl = document.getElementById('tgl').value;
	tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
	delivtime = document.getElementById('delivtime').value;
	tmpt_krm = document.getElementById('tmpt_krm').value;
	invc_krm = document.getElementById('invc_krm').value;
	term_pay = document.getElementById('term_pay').value;
	ppN = document.getElementById('ppN').value;
	ppH = document.getElementById('ppH').value;
	cttn = document.getElementById('cttn').value;
	status = document.getElementById('status').value;
	pros = document.getElementById('proses').value;
	
	param = '&supp='+supp+'&alamat_sup='+alamat_sup+'&npwp_sup='+npwp_sup+'&bank_acc='+bank_acc+'&kontrak='+kontrak+'&tgl='+tgl+'&tgl1='+tgl1+'&tgl2='+tgl2+'&delivtime='+delivtime+'&tmpt_krm='+tmpt_krm+'&invc_krm='+invc_krm+'&term_pay='+term_pay+'&ppN='+ppN+'&ppH='+ppH+'&cttn=' + cttn + '&status=' + status + '&proses=' + pros;
	
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kontrakcari').value=kontrak;
					loadData(0);
					alert("Saved");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdatasupplier(newFunc,alamat_sup='',npwp_sup='',bank_acc=''){
	supp=document.getElementById('supp').value;
	param='supp='+supp+'&alamat_sup='+alamat_sup+'&npwp_sup='+npwp_sup+'&bank_acc='+bank_acc;
	param+='&proses=getdatasupplier';
	tujuan='log_slave_kontrakpayung.php';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					ar = con.responseText.split("###");
					document.getElementById('alamat_sup').innerHTML=ar[0];
					document.getElementById('npwp_sup').innerHTML=ar[1];
					document.getElementById('bank_acc').innerHTML=ar[2];
					if(typeof newFunc !== 'undefined' && typeof newFunc == 'function'){
						eval(loaditemkontrak());
					}
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respog);
}

function fillField(kontrak, pt){
	document.getElementById('proses').value = 'update';
	param = 'kontrak=' + kontrak + '&pt=' + pt + '&proses=showData';
	tujuan = 'log_slave_kontrakpayung.php';
	
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('headher').style.display = 'block';
					document.getElementById('listData').style.display = 'none';
					
					var data = JSON.parse(con.responseText);
					
					document.getElementById('kontrak').disabled=true;
					document.getElementById('kontrak').value=kontrak;
					document.getElementById('supp').value=data[0]['supp'];
					document.getElementById('alamat_sup').value=data[0]['alamat_sup'];
					document.getElementById('npwp_sup').value=data[0]['npwp_sup'];
					document.getElementById('bank_acc').value=data[0]['bank_acc'];
					document.getElementById('tgl').value=data[0]['tgl'];
					document.getElementById('tgl1').value=data[0]['tgl1'];
					document.getElementById('tgl2').value=data[0]['tgl2'];
					document.getElementById('delivtime').value=data[0]['delivtime'];
					document.getElementById('tmpt_krm').value=data[0]['tmpt_krm'];
					document.getElementById('invc_krm').value=data[0]['invc_krm'];
					document.getElementById('term_pay').value=data[0]['term_pay'];
					document.getElementById('ppN').value=data[0]['ppN'];
					document.getElementById('ppH').value=data[0]['ppH'];
					document.getElementById('cttn').value=data[0]['cttn'];
					document.getElementById('status').value=data[0]['status'];
					getdatasupplier(loaditemkontrak,data[0]['alamat_sup'],data[0]['npwp_sup'],data[0]['bank_acc']);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function deldata(kontrak, pt) {

	param = 'kontrak=' + kontrak + '&pt=' + pt + '&proses=delData';
	//alert(param);
	tujuan = 'log_slave_kontrakpayung.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//	alert(con.responseText);
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Are You Sure Want Delete This Data"))
		post_response_text(tujuan, param, respog);
}

function cari() {
	kontrakcari = document.getElementById('kontrakcari').value;
	daTtgl = document.getElementById('tgl_cari').value;
	param = 'kontrakcari=' + kontrakcari + '&daTtgl=' + daTtgl + '&proses=cariData';
	//alert(param);
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//	alert(con.responseText);
					document.getElementById('contain').innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getsatuan() {
	kdbrg = document.getElementById('kdbrg').value;
	param = 'kdbrg='+kdbrg;

	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan + '?proses=getsatuan', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					ar = con.responseText.split("#####");
					document.getElementById('lblkodebarang').innerHTML = ar[1];
					document.getElementById('lblsatuan').innerHTML = ar[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	
	if (getValue('upload') == "") {
		alert("Gagal, File upload masih kosong.");
		return false;
	}
	
	document.getElementsByClassName("mybutton").disabled = true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_kontrakpayung.php?proses=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
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
	param = 'proses=loadfiles';
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerupload').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(namafile) {
	param = 'proses=deletefile&namafile='+namafile;
	tujuan = 'log_slave_kontrakpayung.php';
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

function selesai() {
	displayList();
}

function postingData(kontrak, pt) {
	param = 'proses=postingData&kontrak=' + kontrak + '&pt=' + pt;
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function additem(){
	kdbrg = document.getElementById("kdbrg").value;
	satuan = document.getElementById("lblsatuan").innerHTML;
	qty = document.getElementById("qty").value;
	harga = document.getElementById("harga").value;
	
	if(kdbrg==''){
		alert("Nama barang harus dipilih.");
		return false;
	}
	
	if(harga==''||harga=='0'){
		alert("Harga satuan harus diisi.");
		return false;
	}
	
	param = 'proses=additem&kdbrg='+kdbrg+'&satuan='+satuan+'&qty='+qty+'&harga='+harga;
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaditemkontrak();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaditemkontrak() {
	param = 'proses=loaditemkontrak';
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('itemkontrak').innerHTML = con.responseText;
					clearitem();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deleteitemkontrak(kdbrg) {
	param = 'proses=deleteitemkontrak&kdbrg='+kdbrg;
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaditemkontrak();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearitem(){
	document.getElementById("lblkodebarang").innerHTML="";
	document.getElementById("kdbrg").selectedIndex=0;
	document.getElementById("lblsatuan").innerHTML='';
	document.getElementById("qty").value='';
	document.getElementById("harga").value='';
	loadfiles();
}

function viewdetail(nokontrak,tipe,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>Detail Kontrak "+nokontrak+"</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	title = "";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = (pos[0]-500) + 'px';
	document.getElementById('dynamic1').style.display = '';
	
	var param = "nokontrak="+nokontrak+'&tipe='+tipe;
	
	param += '&proses=viewdetail';
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contRekap').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}