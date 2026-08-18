/**
 * @author repindra.ginting
 *///transaksi js
function setSloc(x) {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	//set value display periode
	tglstart = document.getElementById(gudang + '_start').value;
	tglend = document.getElementById(gudang + '_end').value;
	tglstart = tglstart.substr(6, 2) + "-" + tglstart.substr(4, 2) + "-" + tglstart.substr(0, 4);
	tglend = tglend.substr(6, 2) + "-" + tglend.substr(4, 2) + "-" + tglend.substr(0, 4);
	document.getElementById('displayperiod').innerHTML = tglstart + " - " + tglend;
	if (gudang != '') {
		if (x == 'simpan') {
			document.getElementById('sloc').disabled = true;
			document.getElementById('btnsloc').disabled = true;
			tujuan = 'log_slave_getBapbNumber.php';
			param = 'gudang=' + gudang;
			post_response_text(tujuan, param, respog);
		} else {
			document.getElementById('sloc').disabled = false;
			document.getElementById('sloc').options[0].selected = true;
			document.getElementById('btnsloc').disabled = false;
			kosongkan();
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					exp = con.responseText.split('####');
					document.getElementById('nodok').value = trim(exp[0]);
					document.getElementById('tdpersetujuan').innerHTML = trim(exp[1]);
					document.getElementById('trapp1').innerHTML = trim(exp[2]);
					document.getElementById('tdapp1').colSpan = trim(exp[3]);
					getBapbList(gudang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPOSupplier() {
	// persetujuan1 = document.getElementById('persetujuan1').options[document.getElementById('persetujuan1').selectedIndex].value;
	// persetujuan2 = document.getElementById('persetujuan2').options[document.getElementById('persetujuan2').selectedIndex].value;
	// if(persetujuan1=='' || persetujuan2=='')
	// {
	// alert("Persetujuan 1 dan Persetujuan 2 harus diisi.");
	// return false;
	// }
	//===================validate date
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	
	if(gudang==''){
		alert('Pilihan gudang masih kosong!!');
		return false;
	}
	
	tanggal = document.getElementById('tanggal').value;
	if(tanggal==''){
		alert('Tanggal transaki penerimaan harus diisi !!');
		return false;
	}
	x = tanggal;
	_start = document.getElementById(gudang + '_start').value;
	_end = document.getElementById(gudang + '_end').value;
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("-", "");
	}
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("/", "");
	}
	curdateY = x.substr(4, 4).toString();
	curdateM = x.substr(2, 2).toString();
	curdateD = x.substr(0, 2).toString();
	curdate = curdateY + curdateM + curdateD;
	curdate = parseInt(curdate);
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alert('Tanggal diluar periode Aktif. ('+_start.substr(4, 2).toString()+'-'+_start.substr(0, 4).toString()+')')
	} else {
		nopo = trim(document.getElementById('nopo').value);
		nosj = trim(document.getElementById('nosj').value);

		if (nopo == '')
			alert('No. PO wajib diisi !');

		// if(nosj == '') {
		// 	alert('Surat Jalan wajib di isi');
		// }
		else {
			//                    if(cekPT(gudang,nopo)){
			tujuan = 'log_slave_getPoContent.php';
			param = 'nopo=' + nopo + '&tipedata=supplier' + '&gudang=' + gudang + '&tanggal=' + tanggal + '&nosj=' + nosj;
			nodok = document.getElementById('nodok').value;
			nodok = trim(nodok);
			if (nodok == '') {
				alert('Please select and save Storage Location');
			} else
				post_response_text(tujuan, param, respog);
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('idsupplier').value = trim(con.responseText);
					document.getElementById('supplier').value = trim(con.responseText);
					//now get content
					param = 'nopo=' + nopo + '&tipedata=data&gudang='+gudang+ '&tanggal=' + tanggal+'&nosj='+nosj;
					getPOContent(param);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPOContent(param) {
	tujuan = 'log_slave_getPoContent.php';
	disableHeader();
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					exp = con.responseText.split('####');
					document.getElementById('container').innerHTML = exp[0];
					document.getElementById('tdpersetujuan').innerHTML = exp[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function disableHeader() {
	document.getElementById('tanggal').disabled = true;
	document.getElementById('nosj').disabled = true;
	document.getElementById('nofaktur').disabled = true;
	document.getElementById('nopo').disabled = true;
	// document.getElementById('persetujuan1').disabled=true;
	// document.getElementById('persetujuan2').disabled=true;
	document.getElementById('btnheader').disabled = true;
}
function enableHeader() {
	document.getElementById('tanggal').disabled = false;
	document.getElementById('nosj').disabled = false;
	document.getElementById('nofaktur').disabled = false;
	document.getElementById('nopo').disabled = false;
	// document.getElementById('persetujuan1').disabled=false;
	// document.getElementById('persetujuan2').disabled=false;
	document.getElementById('btnheader').disabled = false;
}
function kosongkan() {
	document.getElementById('nopo').value = '';
	document.getElementById('nofaktur').value = '';
	document.getElementById('idsupplier').value = '';
	document.getElementById('supplier').value = '';
	document.getElementById('nodok').value = '';
	// document.getElementById('persetujuan1').selectedIndex=0;
	// document.getElementById('persetujuan2').selectedIndex=0;
	document.getElementById('container').innerHTML = '';
	document.getElementById('containerlist').innerHTML = '';
	enableHeader();
}
function cekButton(txbox, idbtn) {
	x = trim(txbox.value);
	if (parseFloat(x) < 0 || x == '' || parseFloat(x) == 'NaN') {
		document.getElementById(idbtn).disabled = true;
	} else {
		document.getElementById(idbtn).disabled = false;
	}
}
function getBapbList(gudang) {
	param = 'gudang=' + gudang;
	tujuan = 'log_slave_getBapbList.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveItemPo(kodebarang, sisa, nopp, countpersetujuan) {

	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	
	if(gudang==''){
		alert('Pilihan gudang masih kosong!!');
		return false;
	}
	tanggal = document.getElementById('tanggal').value;
	if(tanggal==''){
		alert('Tanggal transaki penerimaan harus diisi !!');
		return false;
	}
	x = tanggal;
	_start = document.getElementById(gudang + '_start').value;
	_end = document.getElementById(gudang + '_end').value;
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("-", "");
	}
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("/", "");
	}
	curdateY = x.substr(4, 4).toString();
	curdateM = x.substr(2, 2).toString();
	curdateD = x.substr(0, 2).toString();
	curdate = curdateY + curdateM + curdateD;
	curdate = parseInt(curdate);
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alert('Tanggal diluar periode Aktif. ('+_start.substr(4, 2).toString()+'-'+_start.substr(0, 4).toString()+')');
		return false;
	}


	//get all data
	// console.log('mamamas');
	if (countpersetujuan <= 0) {
		alert("Penyetuju belum dipilih. Silahkan hubungi Administrator");
		return false;
	}
	//var file = document.getElementById("upload_"+ kodebarang).files[0];
	var formdata = new FormData();
	// gudang=document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	//formdata.append("file", file);
	//formdata.append("fileupload", getValue('upload_'+ kodebarang));
	formdata.append("kodegudang", getValue('sloc'));
	formdata.append("countpersetujuan", countpersetujuan);
	formdata.append("nopp", nopp);
	formdata.append("nodok", getValue('nodok'));
	formdata.append("idsupplier", getValue('idsupplier'));
	formdata.append("tanggal", getValue('tanggal'));
	formdata.append("nopo", getValue('nopo'));
	formdata.append("nofaktur", getValue('nofaktur'));
	formdata.append("nosj", getValue('nosj'));
	// formdata.append("persetujuan1", getValue('persetujuan1'));
	// formdata.append("persetujuan2", getValue('persetujuan2'));
	formdata.append("kodebarang", kodebarang);
	// formdata.append("upload", getValue('upload'));
	formdata.append("nosj", getValue('nosj'));
	formdata.append("qty", getValue('qty' + kodebarang + '_' + nopp));
	formdata.append("satuan", getValue('sat' + kodebarang + '_' + nopp));
	formdata.append("catatan", getValue('catatan' + kodebarang + '_' + nopp));
	// formdata.append("method", getValue('methodht'));
	for (i = 1; i <= countpersetujuan; i++) {
		if (document.getElementById('persetujuan' + i).value == '') {
			alert("Persetujuan belum dipilih. Silahkan hubungi Administrator");
			return false;
		} else {
			formdata.append("persetujuan" + i, getValue('persetujuan' + i));
		}
	}
	qty = document.getElementById('qty' + kodebarang + '_' + nopp).value;
	nodok = document.getElementById('nodok').value;
	if (nodok == '' || parseFloat(qty) < 0 || parseFloat(qty) == 'NaN') {
		alert('Volume or document number is obligatory');
		document.getElementById('qty' + kodebarang + '_' + nopp).style.backgroundColor = 'red';
	} else {
		if (parseFloat(qty) > sisa) {
			alert('Sorry, volume greater than volmun on PO');
			document.getElementById('qty' + kodebarang + '_' + nopp).style.backgroundColor = 'red';
		} else {
			document.getElementById('qty' + kodebarang + '_' + nopp).style.backgroundColor = 'orange';
			var con = createXMLHttpRequest();
			con.open("POST", "log_slave_saveBapb.php?", true);
			con.onreadystatechange = eval(respon);
			con.send(formdata);
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('qty' + kodebarang + '_' + nopp).style.backgroundColor = 'red';
				} else {
					document.getElementById('qty' + kodebarang + '_' + nopp).style.backgroundColor = '#E8F4F4';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	//post_response_text('keu_slave_tagihan.php?proses=add', param, respon);
}
// function saveItemPo(kodebarang,sisa,nopp,upload){
//         //get all data
// gudang=document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
//         nodok = trim(document.getElementById('nodok').value);
//         idsupplier = document.getElementById('idsupplier').value;
//         tanggal = document.getElementById('tanggal').value;
//         nopo = document.getElementById('nopo').value;
//         nofaktur = document.getElementById('nofaktur').value;
//         nosj = document.getElementById('nosj').value;
//         upload = document.getElementById('upload').value;
//         persetujuan1=document.getElementById('persetujuan1').options[document.getElementById('persetujuan1').selectedIndex].value;
//         persetujuan2=document.getElementById('persetujuan2').options[document.getElementById('persetujuan2').selectedIndex].value;
//         nosj = document.getElementById('nosj').value;
//         qty = document.getElementById('qty' + kodebarang+'_'+nopp).value;
//         satuan=trim(document.getElementById('sat' + kodebarang+'_'+nopp).innerHTML);
//         catatan = document.getElementById('catatan' + kodebarang+'_'+nopp).value;
//         param = 'nodok=' + nodok + '&idsupplier=' + idsupplier + '&tanggal=' + tanggal;
//         param += '&nopo=' + nopo + '&nofaktur=' + nofaktur + '&nosj=' + nosj+ '&catatan=' + catatan;
//         param += '&qty=' + qty+'&kodebarang='+kodebarang+'&kodegudang='+gudang;
//         param +='&satuan='+satuan+'&upload='+upload;
//         param +='&persetujuan1='+persetujuan1+'&persetujuan2='+persetujuan2;
//         param +='&nopp='+nopp;
//         alert(param);
//                 tujuan = 'log_slave_saveBapb.php';
//                 if (nodok == '' || parseFloat(qty) < 0 || parseFloat(qty) == 'NaN') {
//                   alert('Volume or document number is obligatory');
//                 }
//                 else {
//                                                 if(parseFloat(qty)>sisa){
//                                                     alert('Sorry, volume greater than volmun on PO');
//                                                 }else{
//                         document.getElementById('qty'+kodebarang+'_'+nopp).style.backgroundColor='orange';
//                         post_response_text(tujuan, param, respog);
//                                                 }
//                 }
//                 function respog(){
//                         if (con.readyState == 4) {
//                                 if (con.status == 200) {
//                                         busy_off();
//                                         if (!isSaveResponse(con.responseText)) {
//                                                 alert(con.responseText);
//                                                 document.getElementById('qty'+kodebarang).style.backgroundColor='red';
//                                         }
//                                         else {
//                        document.getElementById('qty'+kodebarang+'_'+nopp).style.backgroundColor='#E8F4F4';
//                                         }
//                                 }
//                                 else {
//                                         busy_off();
//                                         error_catch(con.status);
//                                 }
//                         }
//                 }
// }
function selesaiBapb() {
	// persetujuan1=document.getElementById('persetujuan1').options[document.getElementById('persetujuan1').selectedIndex].value;
	// persetujuan2=document.getElementById('persetujuan2').options[document.getElementById('persetujuan2').selectedIndex].value;
	// nodok=document.getElementById('nodok').value;
	// param = 'nodok='+nodok;
	// tujuan = 'log_kirimemailpenerimaan.php';
	// post_response_text(tujuan, param, respog);
	// function respog()
	// {
	// if (con.readyState == 4)
	// {
	// if (con.status == 200)
	// {
	// busy_off();
	// if (!isSaveResponse(con.responseText))
	// {
	// alert(con.responseText);
	// }
	// else
	// {
	selesaiBapb2();
	// }
	// }
	// else
	// {
	// busy_off();
	// error_catch(con.status);
	// }
	// }
	// }
}
function selesaiBapb2() {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	getBapbList(gudang);
	kosongkan();
	// document.getElementById('persetujuan1').selectedIndex=0;
	// document.getElementById('persetujuan2').selectedIndex=0;
	setSloc('simpan');
}
function previewBapb(notransaksi, ev) {
	param = 'notransaksi=' + notransaksi;
	tujuan = 'log_slave_print_bapb_pdf.php?' + param;
	//display window
	title = notransaksi;
	width = '800';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}
function editBapb(notransaksi, nopo, tanggal, nosj, nofaktur, supplier) {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	document.getElementById('nodok').value = notransaksi;
	document.getElementById('idsupplier').value = supplier;
	document.getElementById('nosj').value = nosj;
	document.getElementById('nofaktur').value = nofaktur;
	document.getElementById('nopo').value = nopo;
	document.getElementById('tanggal').value = tanggal;
	param = 'nopo=' + nopo + '&tipedata=edit&notransaksi=' + notransaksi+'&gudang='+gudang;
	getPOContent(param);
	tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1); //jangan tanya darimana
}
function delBapb(notransaksi) {
	param = 'notransaksi=' + notransaksi;
	tujuan = 'log_slave_deleteBapb.php';
	if (confirm('Deleting Document ' + notransaksi + ', are you sure..?'))
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
					setSloc('simpan');
					//getBapbList(gudang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariBapb(num) {
	tex = trim(document.getElementById('txtbabp').value);
	srcposo = trim(document.getElementById('txtposo').value);
	srcnamasup = trim(document.getElementById('txtnamasup').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	if (gudang == '') {
		alert('Storage Location  is obligatory')
	} else {
		param = 'gudang='+gudang+'&tex='+tex+'&srcposo='+srcposo+'&srcnamasup='+srcnamasup;
		param += '&page=' + num;
		tujuan = 'log_slave_getBapbList.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function resetBapb(){
	document.getElementById('txtbabp').value = '';
	document.getElementById('txtposo').value = '';
	document.getElementById('txtnamasup').value = '';
	cariBapb();
}
function cariPO(title, ev) {
	sloc=document.getElementById('sloc').value;
	
	if(sloc==''){
		alert('Pilihan gudan masih kosong!!');
		return false;
	}
	
	kosongkan();
	setSloc('simpan');
	content = "<div>";
	content += "<fieldset>Search : <input type=text id=textpo class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariPo()>Go</button> ";
	content += "<div id=containercari style=\"max-height:250px;min-height:auto;overflow:auto;\"></div></fieldset></div>";
	//display window
	title = title + ' PO :';
	width = '';
	height = '';
	showDialog1(title, content, width, height, ev);
}

function cariSJ(title, ev) {
	sloc=document.getElementById('sloc').value;
	
	if(sloc==''){
		alert('Pilihan gudan masih kosong!!');
		return false;
	}
	
	kosongkan();
	setSloc('simpan');
	content = "<div>";
	content += "<fieldset>Search : <input type=text id=textpo class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariSJ()>Go</button> ";
	content += "<div id=containercari style=\"max-height:250px;min-height:auto;overflow:auto;\"></div></fieldset></div>";
	//display window
	title = title + ' Surat Jalan :';
	width = '';
	height = '';
	showDialog1(title, content, width, height, ev);
}

function goCariPo() {
	nopo = trim(document.getElementById('textpo').value);
	nosj = trim(document.getElementById('nosj').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	if (nopo.length < 1)
		alert('Text too short');
	else {
		param = 'nopo=' + nopo+'&gudang='+gudang+'&nosj='+nosj;
		tujuan = 'log_slave_cariPo.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function goCariSJ() {
	nopo = trim(document.getElementById('textpo').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	if (nopo.length < 1)
		alert('Text too short');
	else {
		param = 'nopo=' + nopo+'&gudang='+gudang;
		tujuan = 'log_slave_cariPo.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showupload(ev,kodebarang) {
	showformupload(ev);
	nodok = document.getElementById('nodok').value;
	param = 'method=showupload&notransaksi='+nodok+'&kodebarang='+kodebarang;
	tujuan = 'log_slave_penerimaanUpload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfilesx(nodok,kodebarang);
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
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function loadfilesx(nodok,kodebarang) {
	param = 'method=loadfiles&notransaksi='+nodok+'&kodebarang='+kodebarang;
	tujuan = 'log_slave_penerimaanUpload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfilestop') !== null) {
						document.getElementById('listfilestop').innerHTML = con.responseText;
					}
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('listfilesview') !== null) {
						document.getElementById('listfilesview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function save_filex(){
	var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('nodok').value;
    var kodebarang = document.getElementById('kodebarangupload').innerHTML;
    var jenisupload = document.getElementById('kriteriaefil').value;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("kodebarang", kodebarang);
    formdata.append("jenisupload", jenisupload);
    formdata.append("file", file);
    formdata.append("fileupload", document.getElementById("upload").value);
    //alert(document.getElementById("filex").value);
    if (document.getElementById("upload").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "log_slave_penerimaanUpload.php?method=submitfilex", true);
    busy_on();
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
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfilesx(notransaksi,kodebarang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
	var notransaksi = document.getElementById("nodok").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notransaksi", notransaksi);
	formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "keu_slave_tagihanv2.php?proses=submitfile", true);
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
					document.getElementsByClassName("mybutton").disabled=false;
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(noinvoice);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefilex(notransaksi, namafile) {
	//alert(namafile);
    var kodebarang = document.getElementById('kodebarangupload').innerHTML;
	param = 'method=deletefilex&notransaksi=' + notransaksi + '&namafile=' + namafile;
	tujuan = 'log_slave_penerimaanUpload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfilesx(notransaksi,kodebarang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function goPickPo(nopo) {
	// persetujuan1 = document.getElementById('persetujuan1').value;
	// persetujuan2 = document.getElementById('persetujuan2').value;
	// if(persetujuan1=='' || persetujuan2=='')
	// {
	// alert('Persetujuan 1 dan Persetujuan 2 harus diisi');
	// return false;
	// }
	document.getElementById('nopo').value = nopo;
	if (getPOSupplier()) {
		gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
		param = 'nopo='+nopo+'&tipedata=data&gudang='+gudang;
		getPOContent(param);
	}
	closeDialog();
}
function uploadform(id) {
	console.log(id);
	var title = "Upload File " + id;
	var content = '<div class="x-box-mc" style="padding:0px;margin:0px;">';
	content += '<form id="fileform" action="slave_supplier_form.php?prosses=uploadfile" method="POST" enctype="multipart/form-data" onsubmit="ajaxUpload(this,\'' + id + '\');return false;" style="margin:15px 0px;width:100%;clear:both;">';
	// var btnval = document.getElementById("lamp"+id);
	// var oldnameFile = btnval.getAttribute('namefile');
	// if(oldnameFile == null){
	//     oldnameFile = "";
	// }
	// content     += '<input type="hidden" name="namefile" value="'+oldnameFile+'">';
	content += '<input class="pull-left myinputtext" type="file" name="file" style="margin-left:10px; width:400px;">';
	content += '<input class="pull-right mybutton" type="submit" value="UPLOAD" style="margin-right:10px;">';
	content += '</form>';
	content += '</div>';
	showDialog1(title, content, '500', '50', event);
}
function detaildt(transaksi_detail) {
	// title = "Detail : " + transaksi_detail;
	// width = '';
	// height = '';
	// formListPP(title, width, height);
	param = 'transaksi_detail=' + transaksi_detail;
	tujuan = 'log_penerimaanBarangDetail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containerAkun').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('30px','300px');
					loadfiles(transaksi_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function uploadfile1(transaksi_detail) {
	title = "Detail : " + transaksi_detail;
	width = '';
	height = '';
	formListPP1(title, width, height);
	param = 'transaksi_detail=' + transaksi_detail;
	tujuan = 'log_penerimaanBarangUpload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerAkun1').innerHTML = con.responseText;
					loadfiles1(transaksi_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formListPP(title, wdth, heig) {
	//closeDialog();
	width = '';
	height = '';
	if (wdth != '') {
		width = wdth;
	}
	if (heig != '') {
		height = heig;
	}
	content = "<div id=containerAkun></div>";
	ev = 'event';
	showDialog4(title, content, width, height, ev);
}
function formListPP1(title, wdth, heig) {
	//closeDialog();
	width = '';
	height = '';
	if (wdth != '') {
		width = wdth;
	}
	if (heig != '') {
		height = heig;
	}
	content = "<div id=containerAkun1></div>";
	ev = 'event';
	showDialog4(title, content, width, height, ev);
}
function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'log_slave_penerimaanUpload.php';
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('containerAkundetail').innerHTML = con.responseText;
					// getPage();
					// detaildt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles1(notransaksi) {
	param = 'method=loadfiles1&notransaksi=' + notransaksi;
	tujuan = 'log_slave_penerimaanUpload.php';
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('containerAkundetail1').innerHTML = con.responseText;
					// getPage();
					// detaildt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadDataAkun(transaksi_detail) {
	// alert('masukk');
	param = 'method=loadData4';
	param += '&notransaksi=' + transaksi_detail;
	tujuan = 'log_slave_getBapbNumber.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('containerAkun').innerHTML = con.responseText;
					// getPage();
					// detaildt();
					// loadData(transaksi_detail)
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function submitfile() {
	var notransaksi = document.getElementById("notransaksi").value;
	var file = document.getElementById("upload").files[0];
	// console.log(file);
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_penerimaanUpload.php?method=submitfile", true);
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
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles1(notransaksi);
					// setSloc('simpan');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function downloadfile(path, filename) {
	param = 'path=' + path + '&filename=' + filename;
	tujuan = 'download.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(id) {
	param = 'method=deletefile&id=' + id;
	alert(param);
	tujuan = 'log_penerimaanBarangDetail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles1(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}