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
// fungsi untuk progress bar


function submitfiletambahan(norph,supplierid, notransaksi,kodebarang,norphmenang,supplieridmenang) {
	var kriteriaefil = document.getElementById("kriteriaefil"+supplierid).value;
	var uploadtambahan = document.getElementById("uploadtambahan"+supplierid).value;
	var file     = document.getElementById("uploadtambahan"+supplierid).files[0];
	var formdata = new FormData();
	formdata.append("norph", norph);
	formdata.append("supplierid", supplierid);
	formdata.append("file", file);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("fileupload", uploadtambahan);
	
	
	if (uploadtambahan == "") {
		alertify.alert("warning : Upload file has been empty.");
		return false;
	}
	
		
	var ext=['.jpeg','.jpg','.png','.pdf','.xls','.xlsx','.doc','.docx'];
	// cek dulu ext filenya
	cekfileupload(uploadtambahan);
	
	busy_on();
	var con = createXMLHttpRequest();
	//tambahan progress bar
	// con.upload.addEventListener("progress", progressHandler, false);
	// con.addEventListener("load", completeHandler, false);
	// con.addEventListener("error", errorHandler, false);
	// con.addEventListener("abort", abortHandler, false);
	//tambahan progress bar -end-
	con.open("POST", "log_slave_link.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert('Uploaded Success.');
					title = "Detail Riwayat Perbandingan Harga";
					ev = "event";
					previewlinkpp2(norphmenang,notransaksi, kodebarang, supplieridmenang, title, ev);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefiletambahan(notransaksi, supplierid, namafile,baris) {
    param = 'method=deletefiletambahan&notransaksi=' + notransaksi + '&supplierid=' + supplierid + '&namafile=' + namafile;
    tujuan = 'log_slave_link.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("baris_"+supplierid+"_"+baris).innerHTML='';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cekfileupload(namafile, ext=''){
	if(ext==''){
		var ext=['.jpeg','.jpg','.png','.pdf','.xls','.xlsx','.doc','.docx'];
	}
	var val = 0;
	for(i=0;i<ext.length;i++){		
		namafile=namafile.toLowerCase();
		if (namafile.lastIndexOf(ext[i]) > -1){
			val++;
		}
	}
	if (val==0){
		alertify.alert("Format file harus "+ext);
		throw Error('Stop!');
	}
}

function showdetaillink(notransaksi, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + notransaksi + "</legend><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=notransaksi name=notransaksi value=" + notransaksi + " />";
	showDialog1(title, content, width, height, ev);
}

function showdetaillink2(notransaksi, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + notransaksi + "</legend><div id=contDetail2 style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=notransaksi name=notransaksi value=" + notransaksi + " />";
	showDialog2(title, content, width, height, ev);
}

function previewlink(notransaksi, supplierid, title, ev){
	showdetaillink(notransaksi, title, ev);
	notransaksi = notransaksi;
	param = 'notransaksi='+notransaksi+'&method=previewlink&supplierid='+supplierid;
	tujuan = 'log_slave_link.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contDetail').innerHTML = con.responseText;
					// loadfiles(nopP);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewlinkpp(notransaksi, kodebarang, title, ev){
	showdetaillink(notransaksi, title, ev);
	notransaksi = notransaksi;
	param = 'notransaksi='+notransaksi+'&method=previewlinkpp&kodebarang='+kodebarang;
	tujuan = 'log_slave_link.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contDetail').innerHTML = con.responseText;
					// loadfiles(nopP);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewlinkpp2(norph,notransaksi, kodebarang, supplierid, title, ev){
	showdetaillink2(notransaksi, title, ev);
	notransaksi = notransaksi;
	param = 'notransaksi='+notransaksi+'&method=previewlinkpp&kodebarang='+kodebarang+'&supplierid='+supplierid+'&norph='+norph;
	tujuan = 'log_slave_link.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alertify.popup2(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					document.getElementById('contDetail2').innerHTML = con.responseText;
					// loadfiles(nopP);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewlinkpp3(notransaksi, kodebarang, title, ev){
	showdetaillink(notransaksi, title, ev);
	notransaksi = notransaksi;
	param = 'notransaksi='+notransaksi+'&method=previewlinkpp3&kodebarang='+kodebarang;
	tujuan = 'log_slave_link.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alertify.popup2(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					document.getElementById('contDetail').innerHTML = con.responseText;
					// loadfiles(nopP);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewlinkpemenang(notransaksi, supplierid, title, ev){
	showdetaillink(notransaksi, title, ev);
	notransaksi = notransaksi;
	param = 'notransaksi='+notransaksi+'&method=previewlinkpemenang&supplierid='+supplierid;
	tujuan = 'log_slave_link.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contDetail').innerHTML = con.responseText;
					// alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					// loadfiles(nopP);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewlinkdt(nopp,kodebarang, ev) {
	showdetaillinkdt(nopp, ev);
	param = 'nopp='+nopp+'&method=previewlinkdt&kodebarang='+kodebarang;
	tujuan = 'log_slave_link.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contDetaildt').innerHTML = con.responseText;
					// title="Detail";
					// alertify.popup2(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					loadfiles("listfileupload",nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadPPChat(nopp, kodebarang, ev) {
	title = "Chat:" + nopp + " - " + kodebarang;
	content = "<iframe frameborder=0 style='width:590px;height:490px;' src='log_slaveChatPP.php?nopp=" + nopp + "&kodebarang=" + kodebarang + "'></iframe>";
	width = '600';
	height = '450';
	showDialog2(title, content, width, height, ev);
	alertify.popup().destroy();
	alertify.popup2().destroy();
}

function showdocpakaibarang(prddari, prdsampai, kdorg, barang, ev) {
	width = '';
	height = '';
	content = "<fieldset style='height:96%;width:97%';><legend>Pemakaian Material periode " + prddari + " s/d " + prdsampai + "</legend><div id=detailpakaibarang  style='overflow:auto;max-height:400px;max-width:900px';></div></fieldset>";
	title = "Detail";
	showDialog2(title, content, width, height, ev);
	param = 'proses=preview' + '&tgl1=' + prddari + '&tgl2=' + prdsampai + '&unit=' + kdorg + '&barang=' + barang;
	tujuan = 'log_slave_2pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailpakaibarang').innerHTML = con.responseText;
					title="Detail";
					// alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showdocpembelianterakhir(prddari, prdsampai, kdorg, barang, ev) {
	width = '';
	height = '';
	content = "<fieldset style='height:96%;width:98%';><legend>Pembelian Terakhir Material periode " + prddari + " s/d " + prdsampai + "</legend><div id=detailpakaibarang  style='overflow:auto;max-height:400px;max-width:100%';></div></fieldset>";
	title = "Detail";
	showDialog5(title, content, width, height, ev);
	param = 'proses=preview' + '&tglDr=' + prddari + '&tanggalSampai=' + prdsampai + '&unit=' + kdorg + '&kdBrg=' + barang;
	tujuan = 'log_slave_2detail_pembelian_brg.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailpakaibarang').innerHTML = con.responseText;
					title="Detail";
					// alertify.popup3(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showdetaillinkdt(nopp, ev) {
	title = "Purchase/Service Request detail";
	width = '';
	height = '';
	content = "<fieldset><legend>" + nopp + "</legend><div id=contDetaildt style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=nopp name=nopp value=" + nopp + " />";
	showDialog4(title, content, width, height, ev);
}

//fungsi ini duplicate dengan di log_approval
function loadfiles(listx,nopp) {
	param = 'method=loadfiles&nopp=' + nopp;
	param += "&notransaksi=" + notransaksi;
	tujuan = 'log_slave_link.php';
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