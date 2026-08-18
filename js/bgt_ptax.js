function getpopupbarang() {	
	param = 'method=getpopupbarang';
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('700px','500px');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getbarang() {	
	kodebarang= document.getElementById('kodebarangcari').value;
	kodeorg   = document.getElementById('unit').value;
	tahun     = document.getElementById('tahun').value;
	
	param = 'method=getbarang';
	param += '&kodebarang=' + kodebarang;
	param += '&kodeorg=' + kodeorg;
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('getpopupbarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setdata(kodebarang,namabarang,satuan,hargasatuan){
	document.getElementById('hargasatuan').value = hargasatuan;
	document.getElementById('kodebarang').value = kodebarang;
	document.getElementById('namabarang').value = namabarang;
	document.getElementById('satuanbrg').value = satuan;
	document.getElementById('kuantitas').value = '';
	document.getElementById('kuantitas').disabled = false;
	alertify.popup().destroy();
}

function hitungbarang(){
	hargasatuan= document.getElementById('hargasatuan').value;
	kuantitas  = document.getElementById('kuantitas').value;
	
	rp = parseFloat(hargasatuan)*parseFloat(kuantitas);
	document.getElementById('rupiahbarang').value = rp;
	document.getElementById('jlhumum').value = rp;
}

function postingconfirm(notransaksi) {
	param = 'method=postingconfirm&notransaksi=' + notransaksi;
	if(confirm("Anda yakin ???")){		
		tujuan = 'bgt_slave_ptax.php';
	}
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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

function detailData(notransaksi,tipe,ev,jenis) {
	// width = 1024;
	// height = 400;
	
	// content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "Preview";
	// showDialog4(title, content, width, height, ev);
	
	param = 'method=previewdata' + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailPDF(notransaksi,tipe,ev,jenis){
	param = 'method=previewdata' + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_ptax.php' + "?" + param;
	width = 1024;
	height = 400;
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1(title, content, width, height, ev);
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='bgt_slave_ptax.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function detailExcel(notransaksi,tipe,ev,jenis) {
	judul = 'Report Ms.Excel';
	param = 'method=previewdata' + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	
	printnopopup(tujuan+"?"+param);
	//printFile(param, tujuan, judul, ev);
}

function add_new_data(){
	document.getElementById('detail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('formpencarianheader').style.display='none';
	batalheader();
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('formpencarianheader').style.display='';
	document.getElementById('detail').style.display = 'none';
	batallist();
}

function batallist(){
	document.getElementById('notransaksilist').value='';
	//document.getElementById('namakarylist').value='';
	loaddata(0);
}


function loaddata(page) {
	notransaksilist = document.getElementById('notransaksilist').value;
	//namakarylist = document.getElementById('namakarylist').value;
	
	param = 'method=loaddata&page=' + page;
	param += '&notransaksi=' + notransaksilist;
	//param += '&namakarylist=' + namakarylist;
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function batalheader(){
	document.getElementById('notransaksi').value='';
	document.getElementById('tahun').value='';
	document.getElementById('tipebudget').value='';
	document.getElementById('tipepta').value='';
	document.getElementById('ket').value='';
	document.getElementById('tanggal').value='';
	
	document.getElementById('methodheader').value='insertheader';
	document.getElementById('contdetail').innerHTML = '';
	
	document.getElementById('tahun').disabled=false;
	document.getElementById('tanggal').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('tipebudget').disabled=false;
	document.getElementById('tipepta').disabled=false;
	document.getElementById('ket').disabled=false;
	
}

function simpanheader(sumbertrans){
    param = "";
	method = document.getElementById('methodheader').value;
	param += '&method=' + method;
	param += '&notransaksi=' + getValue('notransaksi');
    param += '&tahun=' + getValue('tahun');
    param += '&unit=' + getValue('unit');
    param += '&tipebudget=' + getValue('tipebudget');
    param += '&tipepta=' + getValue('tipepta');
    param += '&ket=' + getValue('ket');
    param += '&tanggal=' + getValue('tanggal');
    tujuan = 'bgt_slave_ptax.php';
	
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else {
					//Pada saat pertama kali tidak di simpan ke db, exe nanti pada saat simpan detail.
					//jika ada update baru di exe.
                    document.getElementById('methodheader').value = 'updateheader';
                    document.getElementById('tahun').disabled=true;
                    document.getElementById('unit').disabled=true;
                    document.getElementById('tipebudget').disabled=true;
                    document.getElementById('tipepta').disabled=true;
					
					loadinputdetail();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function gethargabarang(){
    param  = "";
	param += '&method=gethargabarang';
    param += '&tahun=' + getValue('tahun');
    param += '&unit=' + getValue('unit');
    param += '&kodebarang=' + getValue('kodebarang');
    tujuan = 'bgt_slave_ptax.php';
	
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else {
                    document.getElementById('rppersat').value = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loadinputdetail(){
	param = "";
    param += '&method=loadinputdetail';
	param += '&notransaksi=' + getValue('notransaksi');
    param += '&tahun=' + getValue('tahun');
    param += '&unit=' + getValue('unit');
    param += '&tipebudget=' + getValue('tipebudget');
    param += '&tipepta=' + getValue('tipepta');
    param += '&ket=' + getValue('ket');
	param += '&tanggal=' + getValue('tanggal');
	
    tujuan = 'bgt_slave_ptax.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else {
					document.getElementById('contdetail').style.display = 'block';
					document.getElementById('contdetail').innerHTML = con.responseText;
					if(getValue('tipepta')=='UMUM'){
						loaddataumum();
					}else if(getValue('tipepta')=='KAPITAL'){
						loaddatakapital();
					}else if(getValue('tipepta')=='MILL'){
						loaddatasdmpks(notransaksi);
					}else{						
						loaddatasdm(notransaksi);
					}
					
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getnotrans() {
	tahun = document.getElementById('tahun').value;
	unit = document.getElementById('unit').value;
	tipepta = document.getElementById('tipepta').value;
	tipebudget = document.getElementById('tipebudget').value;
	
	param  = 'method=getnotrans';
	param += '&tahun=' + tahun;
	param += '&unit=' + unit;
	param += '&tipepta=' + tipepta;
	param += '&tipebudget=' + tipebudget;
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function gettipepta() {
	tahun = document.getElementById('tahun').value;
	unit = document.getElementById('unit').value;
	tipebudget = document.getElementById('tipebudget').value;
	
	param  = 'method=gettipepta';
	param += '&tahun=' + tahun;
	param += '&unit=' + unit;
	param += '&tipebudget=' + tipebudget;
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tipepta').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function fillfield(notransaksi,sumber) {
	document.getElementById('contdetail').innerHTML = '';
	param  = 'method=fillfield';
	param += '&notransaksi=' + notransaksi;
	param += '&sumber=' + sumber;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('detail').style.display = 'block';
					document.getElementById('listData').style.display = 'none';
					document.getElementById('formpencarianheader').style.display='none';
					
					document.getElementById('notransaksi').value=notransaksi;
					document.getElementById('tahun').value=data[1];
					document.getElementById('unit').value=data[2];
					document.getElementById('tipebudget').value=data[3];
					document.getElementById('tipepta').innerHTML="<option value='"+ data[4] +"'>"+ data[4] +"</option>"
					document.getElementById('ket').value=data[5];
					document.getElementById('tanggal').value=data[6];
					
					document.getElementById('tahun').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('tipebudget').disabled=true;
					document.getElementById('tipepta').disabled=true;
					
					document.getElementById('methodheader').value = 'updateheader';
					
					simpanheader();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(notransaksi,tahun,tipe) {
	param  = 'method=delete';
	param += '&notransaksi=' + notransaksi;
	param += '&tahun=' + tahun;
	param += '&tipepta=' + tipe;
	
	tujuan = 'bgt_slave_ptax.php';
	if (confirm("Anda yakin ?")) {	
		post_response_text(tujuan, param, respog);
	} 
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog6(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic6').style.top = 600 + 'px';
	document.getElementById('dynamic6').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic6').style.display = '';
}

function showupload(ev,notransaksi,jenisupload){
	if (notransaksi == "") {
		alertify.alert("warning : Notransaksi wajib diisi.");
		return false;
	}
	showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	param+='&jenisupload=' + jenisupload;
	tujuan='bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(notransaksi,jenisupload);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function submitfile(notransaksi,jenisupload) {
	var file = document.getElementById("upload").files[0];
	var jenisupload = document.getElementById("jenisupload").value;
	var formdata = new FormData();

	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("jenisupload", jenisupload);
	if (getValue('upload') == "") {
		alertify.alert("warning : Upload file has been empty.");
		return false;
	}

	if (notransaksi == "") {
		alertify.alert("warning : Notransaksi wajib diisi.");
		return false;
	}
	if (jenisupload == "") {
		alertify.alert("warning : Jenis Biaya wajib diisi.");
		return false;
	}
	
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	document.getElementById('btnsubmit').style.display='none';
	busy_on();
	con.open("POST", "bgt_slave_ptax.php?method=submitfile", true);
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
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById('btnsubmit').style.display='';
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function enabletombol(){
	document.getElementById('btnsubmit').disabled=false;
	document.getElementById('btnsubmit').style.display='';
	busy_off();
}
function loadfiles(notransaksi,jenisupload) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	param+='&jenisupload=' + jenisupload;
	tujuan = 'bgt_slave_ptax.php';
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

function popupfile(notransaksi,jenisupload,jenis){
	ev='event';
	showformupload(ev);
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	param+='&jenisupload=' + jenisupload;
	param+='&jenis=' + jenis;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (document.getElementById('contUpload') !== null) {
						document.getElementById('contUpload').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
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
	document.getElementById('dynamic5').style.top = 600 + 'px';
	document.getElementById('dynamic5').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic5').style.display = '';
	
}
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'bgt_slave_ptax.php';
		post_response_text(tujuan, param, respog);
	} else {
		alertify.alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(notransaksi, namafile,jenisupload) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form_ajukan(notransaksi,jenis, file) {
	// width = '350';
	// height = '';
	// content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:320px;max-height:150px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog5(title, content, width, height, ev);

	param = 'method=form_ajukan' + '&jenis=' + jenis;
	param += '&notransaksi=' + notransaksi;
	param += '&file=' + file;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					// closeDialog5();
				} else {
					alertify.popup().destroy();
					alertify.popup("Ajukan",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('400px','250px');
					// document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function ajukan() {
	kepada      = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	jenis       = document.getElementById('jenisaju').value;
	kodeapproval= document.getElementById('kodeapprovalaju').value;
	
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	param += '&jenis=' + jenis;
	param += '&kodeapproval=' + kodeapproval;

	if (kepada == '') {
		alertify.alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert('Sucses');
					// closeDialog5();
					alertify.popup().destroy();
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form_batal(notransaksi) {
	width = '350';
	height = '';
	content = "<fieldset><legend>Batal</legend><div id=containerbatal align=center style=\"width:320px;max-height:150px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);

	param = 'method=form_batal';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerbatal').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getthntnm() {
	tahun = document.getElementById('tahun').value;
	unit = document.getElementById('unit').value;
	tipepta = document.getElementById('tipepta').value;
	tipebudget = document.getElementById('tipebudget').value;
	divisi = document.getElementById('divisi').value;
	
	param  = 'method=getthntnm';
	param += '&tahun=' + tahun;
	param += '&unit=' + unit;
	param += '&tipepta=' + tipepta;
	param += '&tipebudget=' + tipebudget;
	param += '&divisi=' + divisi;
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tahuntanam').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getluas() {
	tahun = document.getElementById('tahun').value;
	unit = document.getElementById('unit').value;
	tipepta = document.getElementById('tipepta').value;
	tipebudget = document.getElementById('tipebudget').value;
	divisi = document.getElementById('divisi').value;
	tahuntanam = document.getElementById('tahuntanam').value;
	
	param  = 'method=getluas';
	param += '&tahun=' + tahun;
	param += '&unit=' + unit;
	param += '&tipepta=' + tipepta;
	param += '&tipebudget=' + tipebudget;
	param += '&divisi=' + divisi;
	param += '&tahuntanam=' + tahuntanam;
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('luasareal').value = data[0];
					document.getElementById('kegiatan').innerHTML = data[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getluaspta() {
	luasareal = document.getElementById('luasareal').value;
	rotasi = document.getElementById('rotasi').value;
	luaspta = parseFloat(luasareal)*parseFloat(rotasi);
	if(isNaN(luaspta)){luaspta=0;}else{luaspta=luaspta;}
	document.getElementById('luaspta').value=luaspta;
}

function getrupiah(sumber) {
	if(sumber=='sdm'){
		jhk = document.getElementById('jhk').value;
		rpperhk = document.getElementById('rpperhk').value;
		ttlrupiahhk = parseFloat(jhk)*parseFloat(rpperhk);		
		if(isNaN(ttlrupiahhk)){ttlrupiahhk=0;}else{ttlrupiahhk=ttlrupiahhk;}
		document.getElementById('ttlrupiahhk').value=numberFormat(ttlrupiahhk);
	}
	if(sumber=='mat'){
		vol = document.getElementById('volume').value;
		rppermat = document.getElementById('rppermat').value;
		ttl = parseFloat(vol)*parseFloat(rppermat);		
		if(isNaN(ttl)){ttl=0;}else{ttl=ttl;}
		document.getElementById('ttlrupiahmat').value=numberFormat(ttl);
	}
	if(sumber=='kapital'){
		vol = document.getElementById('jlhkap').value;
		rppermat = document.getElementById('rppersat').value;
		ttl = parseFloat(vol)*parseFloat(rppermat);		
		if(isNaN(ttl)){ttl=0;}else{ttl=ttl;}
		document.getElementById('ttlrupiahkap').value=numberFormat(ttl);
	}
}

function ValReq(e){ 
    i = getValue(e);
    if(i == "" || i == undefined){
        document.getElementById(e).focus();
        document.getElementById(e).style.borderColor='red';
        alertify.alert('Info',e + ' kosong!');
        throw new Error(e + ' required!');
    }else{
        return i;
    }
}


function simpandetail(jenis) {	
	param  = 'method=simpandetail';
	param += '&notransaksi=' + ValReq('notransaksi');
	param += '&unit=' + ValReq('unit');
	param += '&tahun=' + ValReq('tahun');
	param += '&tipepta=' + ValReq('tipepta');
	param += '&tipebudget=' + ValReq('tipebudget');
	param += '&keterangan2=' + ValReq('ket');
	param += '&tanggal=' + ValReq('tanggal');
	param += '&jenis=' + jenis;
	
	if(jenis=='sdmmill'){		
		param += '&station=' + ValReq('station');
		
		param += '&kodebudget=' + ValReq('tipekary');
		param += '&jumlah=' + ValReq('jhk');
		param += '&rpperhk=' + ValReq('rpperhk');
		param += '&rupiah=' + ValReq('ttlrupiahhk');
		param += '&keterangan=' + ValReq('ketsdm');
	}
	if(jenis=='matmill'){		
		param += '&station=' + ValReq('station');
		
		param += '&noakun=' + ValReq('akunmill');
		param += '&kodebudget=' + ValReq('kelbrg');
		param += '&kodebarang=' + ValReq('namabarang');
		param += '&jumlah=' + ValReq('volume');
		param += '&rpperhk=' + ValReq('rppermat');
		param += '&rupiah=' + ValReq('ttlrupiahmat');
		param += '&keterangan=' + ValReq('ketmat');
	}
	if(jenis=='sdm'){		
		param += '&divisi=' + ValReq('divisi');
		param += '&tahuntanam=' + ValReq('tahuntanam');
		param += '&luasareal=' + ValReq('luasareal');
		param += '&kegiatan=' + ValReq('kegiatan');
		param += '&rotasi=' + ValReq('rotasi');
		param += '&volume=' + ValReq('luaspta');
		
		param += '&kodebudget=' + ValReq('tipekary');
		param += '&jumlah=' + ValReq('jhk');
		param += '&rpperhk=' + ValReq('rpperhk');
		param += '&rupiah=' + ValReq('ttlrupiahhk');
		param += '&keterangan=' + ValReq('ketsdm');
	}
	if(jenis=='mat'){		
		param += '&divisi=' + ValReq('divisi');
		param += '&tahuntanam=' + ValReq('tahuntanam');
		param += '&luasareal=' + ValReq('luasareal');
		param += '&kegiatan=' + ValReq('kegiatan');
		param += '&rotasi=' + ValReq('rotasi');
		param += '&volume=' + ValReq('luaspta');
		
		param += '&kodebudget=' + ValReq('kelbrg');
		param += '&kodebarang=' + ValReq('namabarang');
		param += '&jumlah=' + ValReq('volume');
		param += '&rpperhk=' + ValReq('rppermat');
		param += '&rupiah=' + ValReq('ttlrupiahmat');
		param += '&keterangan=' + ValReq('ketmat');
	}
	if(jenis=='sdmtrk'){		
		param += '&divisi=' + ValReq('kodetraksi');
		param += '&kodevhc=' + ValReq('kodevhc');
		
		param += '&kodebudget=' + ValReq('tipekary');
		param += '&jumlah=' + ValReq('jhk');
		param += '&rpperhk=' + ValReq('rpperhk');
		param += '&rupiah=' + ValReq('ttlrupiahhk');
		param += '&keterangan=' + ValReq('ketsdm');
	}
	if(jenis=='mattrk'){		
		param += '&divisi=' + ValReq('kodetraksi');
		param += '&kodevhc=' + ValReq('kodevhc');
		
		param += '&kodebudget=' + ValReq('kelbrg');
		param += '&kodebarang=' + ValReq('namabarang');
		param += '&jumlah=' + ValReq('volume');
		param += '&rpperhk=' + ValReq('rppermat');
		param += '&rupiah=' + ValReq('ttlrupiahmat');
		param += '&keterangan=' + ValReq('ketmat');
	}
	if(jenis=='umum'){		
		param += '&noakun=' + ValReq('noakun');
		param += '&aruskas=' + ValReq('aruskasumum');
		param += '&rupiah=' + ValReq('jlhumum');
		param += '&keterangan=' + ValReq('ketumum');
		param += '&kodebarang=' + getValue('kodebarang');
		param += '&kuantitas=' + getValue('kuantitas');
	}
	
	if(jenis=='kapital'){		
		param += '&jnskapital=' + ValReq('jnskapital');
		param += '&lokasi=' + ValReq('lokasi');
		param += '&keterangan=' + ValReq('ketkapital');
		param += '&jumlah=' + ValReq('jlhkap');
		param += '&aruskas=' + ValReq('aruskas');
		param += '&kodebarang=' + getValue('kodebarang');
		param += '&flagbarang=' + getValue('flagbarang');
		
		param += '&rppersat=' + ValReq('rppersat');
		param += '&rupiah=' + ValReq('ttlrupiahkap');
		
		if(getValue('flagbarang')!='' && getValue('kodebarang')==''){
			alertify.alert('Kode barang harus diisi.'); return;
		}
	}
	
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert('done');
					cleardetail(jenis);
					if(jenis=='umum'){
						loaddataumum();
					}else if(jenis=='kapital'){
						loaddatakapital();
					}else if(jenis=='sdmmill'){
						loaddatasdmpks();
					}else if(jenis=='matmill'){
						loaddatamatpks();
					}else{						
						loaddatasdm();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardetail(jenis){
	if(jenis=='sdm' || jenis=='sdmtrk'){		
		setValue2('tipekary','');
		setValue2('jhk','');
		setValue2('rpperhk','');
		setValue2('ttlrupiahhk','');
		setValue2('ketsdm','');
	}
	if(jenis=='mat' || jenis=='mattrk'){		
		//setValue2('kelbrg','');
		setValue2('namabarang','');
		setValue2('volume','');
		setValue2('rppermat','');
		setValue2('ttlrupiahmat','');
		setValue2('ketmat','');
	}
	if(jenis=='kapital'){		
		setValue2('jnskapital','');
		setValue2('lokasi','');
		setValue2('ketkapital','');
		setValue2('jlhkap','');
		setValue2('rppersat','');
		setValue2('ttlrupiahkap','');
		setValue2('kodebarang',null);
		setValue2('aruskas',null);
	}
	if(jenis=='umum'){		
		setValue2('kodebarang','');
		setValue2('namabarang','');
		setValue2('satuanbrg','');
		setValue2('kuantitas','');
		setValue2('rupiahbarang','');
		setValue2('jlhumum','');
		document.getElementById('kuantitas').disabled = true;
	}
}

function loaddatakapital(){
	param  = 'method=loaddatakapital';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatakapital').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddataumum(){
	param  = 'method=loaddataumum';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddataumum').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatasdmpks(){
	param  = 'method=loaddatasdmpks';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatasdmpks').innerHTML = con.responseText;
					loaddatamatpks();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatasdm(){
	param  = 'method=loaddatasdm';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatasdm').innerHTML = con.responseText;
					loaddatamat();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatamatpks(){
	param  = 'method=loaddatamatpks';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatamatpks').innerHTML = con.responseText;
					loaddatarekappks();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatamat(){
	param  = 'method=loaddatamat';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatamat').innerHTML = con.responseText;
					loaddatarekap();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatarekappks(){
	param  = 'method=loaddatarekappks';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatarekappks').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatarekap(){
	param  = 'method=loaddatarekap';
	param += '&notransaksi=' + getValue('notransaksi');
	param += '&unit=' + getValue('unit');
	param += '&tahun=' + getValue('tahun');
	param += '&tipepta=' + getValue('tipepta');
	param += '&tipebudget=' + getValue('tipebudget');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatarekap').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deleterekap(divisi,kegiatan,kodebudget,tahun,notransaksi){
	param  = 'method=deleterekap';
	param += '&divisi=' + divisi;
	param += '&kegiatan=' + kegiatan;
	param += '&kodebudget=' + kodebudget;
	param += '&tahun=' + tahun;
	param += '&notransaksi=' + notransaksi;
	
	tujuan = 'bgt_slave_ptax.php';
	if(confirm("Anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddatasdm();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedetail(kunci){
	param  = 'method=deletedetail';
	param += '&kunci=' + kunci;
	param += '&tipepta=' + getValue('tipepta');
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(getValue('tipepta')=='MILL'){						
						loaddatasdmpks();
					}else{
						loaddatasdm();						
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkodebarang() {
	kelbrg = document.getElementById('kelbrg').value;
	
	param  = 'method=getkodebarang';
	param += '&kelbrg=' + kelbrg;
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('namabarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getharga() {
	namabarang = document.getElementById('namabarang').value;
	unit = document.getElementById('unit').value;
	tahun = document.getElementById('tahun').value;
	
	param  = 'method=getharga';
	param += '&namabarang=' + namabarang;
	param += '&unit=' + unit;
	param += '&tahun=' + tahun;
	
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rppermat').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getaruskas(idsumber,idtujuan,akun,aruskas,kodebarang,sumber){
	kodebgt = document.getElementById(idsumber).value;
    param = 'kodebgt=' + kodebgt;
    param += '&akun=' + akun;
    param += '&aruskas=' + aruskas;
    param += '&kodebarang=' + kodebarang;
	if(sumber!=undefined){
		param += '&method=getaruskasglobal';
	}else{		
		param += '&method=getaruskas';
	}
    tujuan = 'bgt_slave_ptax.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data=con.responseText.split("####");
					if(sumber!=undefined){
						document.getElementById(idtujuan).innerHTML = data[0];
					}else{						
						if(trim(data[2])=='1'){
							document.getElementById('kodebarang').disabled=false;						
						}else{
							document.getElementById('kodebarang').disabled=true;
						}
						document.getElementById('flagbarang').value = trim(data[2]);
						document.getElementById(idtujuan).innerHTML = data[0];
						document.getElementById('kodebarang').innerHTML = data[1];
						setValue2('kodebarang',kodebarang);
					}
					
					// alert(data[0]);
					//setharga();
					//alert("masuk");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function showupload(notransaksi){
	ev = 'event';
	//showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	
	tujuan='bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    //document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup().destroy();
					alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('400px','400px');
					
					loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

// fungsi untuk progress bar
function progressHandler(event) {
	document.getElementById("progressBar").style.display="block";
	document.getElementById("loaded_n_total").innerHTML = "Uploaded " + numberFormat(Math.round(event.loaded/1024)) + " KB of " + numberFormat(Math.round(event.total/1024))+" KB";
	var percent = (event.loaded / event.total) * 100;
	document.getElementById("progressBar").value = Math.round(percent);
	document.getElementById("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
	document.getElementById("progressBar").style.display="none";
	document.getElementById("status").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
  document.getElementById("status").innerHTML = "Upload Failed";
}
function abortHandler(event) {
  document.getElementById("status").innerHTML = "Upload Aborted";
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
	//tambahan progress bar
	con.upload.addEventListener("progress", progressHandler, false);
	con.addEventListener("load", completeHandler, false);
	con.addEventListener("error", errorHandler, false);
	con.addEventListener("abort", abortHandler, false);
	//tambahan progress bar -end-
	con.open("POST", "bgt_slave_ptax.php?method=submitfile", true);
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
	tujuan = 'bgt_slave_ptax.php';
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
	tujuan = 'bgt_slave_ptax.php';
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
	//formupload();
	param = 'method=viewfile&idfile=' + idfile;
	tujuan = 'bgt_slave_ptax.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contviewupload').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}