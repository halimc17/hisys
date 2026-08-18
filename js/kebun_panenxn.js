function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(notransaksi){
	ev = 'event';
	//showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	
	tujuan='kebun_slave_panenx_spb.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    // document.getElementById('contUpload').innerHTML=con.responseText;
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
	document.getElementById("statusbar").innerHTML = Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
	document.getElementById("progressBar").style.display="none";
	document.getElementById("statusbar").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Failed";
}
function abortHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Aborted";
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
	con.open("POST", "kebun_slave_panenx_spb.php?method=submitfile", true);
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
	tujuan = 'kebun_slave_panenx_spb.php';
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
	tujuan = 'kebun_slave_panenx_spb.php';
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
	// formupload();
	param = 'method=viewfile&idfile=' + idfile;
	tujuan = 'kebun_slave_panenx_spb.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contviewupload').innerHTML = con.responseText;
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnospk(){
	kodeorg=document.getElementById('kodeorg').value;
	divisi =document.getElementById('divisi').value;
	tgl    =document.getElementById('tgl').value;
	jenis  =document.getElementById('jenis').value;
	
	validate([
		["kodeorg","Kodeorg wajib diisi."],
		["tgl","Tanggal wajib diisi."]
	]);
	
    param='method=getnospk&tgl='+tgl+'&kodeorg='+kodeorg;
	param += "&divisi="+divisi;
    tujuan='kebun_slave_panenxn.php';
	if(jenis=='BOR'){
		document.getElementById('nospk').disabled=false;
		post_response_text(tujuan, param, respog);
	}else{
		document.getElementById('nospk').disabled=true;
		document.getElementById('nospk').value = '';
	}
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
                } else {
					document.getElementById('nospk').innerHTML = con.responseText;
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukankeasst(nobkm){
	param='method=ajukankeasst&nobkm='+nobkm;
    tujuan='kebun_slave_panenxn.php';
	alertify.confirm("Ajukan ke Pimpinan untuk di Posting ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert('Info',con.responseText);
                } else {
					getPage();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function perbaikandata(nobkm){
	param='method=perbaikandata&nobkm='+nobkm;
    tujuan='kebun_slave_panenxn.php';
	alertify.confirm("Ajukan ke Pimpinan untuk di Posting ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert('Info',con.responseText);
                } else {
					alertify.popup().destroy();
					getPage();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function proseshitungpremi(notransaksi){
	
    param='method=proseshitungpremi&notransaksi='+notransaksi;
    tujuan='kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert('Info',con.responseText);
                } else {
					alertify.alert('Info',"Done");
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdivmdr(sumber){
	kodeorg=document.getElementById('kodeorg').value;
	divisi =document.getElementById('divisi').value;
	tgl    =document.getElementById('tgl').value;
	
	
    param='method=getdivmdr&tgl='+tgl+'&kodeorg='+kodeorg;
	param += "&divisi="+divisi;
	param += "&sumber="+sumber;
    tujuan='kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert('Info',con.responseText);
                } else {
					data = con.responseText.split("####");
					if(sumber=='kebun'){						
						document.getElementById('divisi').innerHTML = data[0];
					}
					document.getElementById('mandor').innerHTML = data[1];
					document.getElementById('mandor1').innerHTML = data[2];
					document.getElementById('kerani').innerHTML = data[3];
					document.getElementById('asst').innerHTML = data[4];
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function unhideheader() {
	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
	document.getElementById('hidebtn').style.display = 'block';
	document.getElementById('unhidebtn').style.display = 'none';
}
function hideheader() {
	document.getElementById('header_trans').style.display = 'none';
	document.getElementById('judul_header').style.display = 'none';
	document.getElementById('hidebtn').style.display = 'none';
	document.getElementById('unhidebtn').style.display = '';
}
function detailData(notransaksi, numRow,nobkm, ev, tipe,tampil) {
	
	param = "proses=html&tipe=" + tipe + "&notransaksi=" + notransaksi+ "&nobkm=" + nobkm;
	param += '&tampil=' + tampil;
	
	tujuan = 'kebun_slave_operasional_print_detail_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function detailExcel(notransaksi, numRow,nobkm, ev, tipe,tampil) {
	param = "proses=excel&tipe=" + tipe + "&notransaksi=" + notransaksi+ "&nobkm=" + nobkm;
	param += '&tampil=' + tampil;
	tujuan = 'kebun_slave_operasional_print_detail_panenxn.php';
	
	printnopopup(tujuan+"?"+param);
}
function detailPDF(notransaksi, numRow, ev) {
	param = "proses=pdf&tipe=PNN" + "&notransaksi=" + notransaksi;
	// showDialog1('Print PDF', "<iframe frameborder=0 style='width:895px;height:400px'" +
		// " src='kebun_slave_operasional_print_detail_panen_pdf.php?" + param + "'></iframe>", '900', '400', ev);
	// var dialog = document.getElementById('dynamic1');
	// dialog.style.top = '50px';
	// dialog.style.left = '15%';
	
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detail_panen_pdf.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}
function postingData(notransaksi, numRow,abs) {
	param = "notransaksi=" + notransaksi;
	param += "&method=posting";
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					getPage();
					//closeDialog();
					alertify.popup().destroy();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	
	alertify.confirm("Warning","Akan dilakukan posting untuk transaksi " + notransaksi +"<br>Data tidak dapat diubah setelah ini. Anda yakin ?",
		function(){
			if(abs=='absensi'){
				param += "&method=postingabsensi";
				post_response_text('kebun_slave_panenxn.php', param, respon);
			}else{			
				post_response_text('kebun_slave_panen_postingxn.php', param, respon);
			}
		},
		function(){
			return;
		}
	);
}
function edit(notransaksi) {
	param = 'method=edithead' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_panenxn.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					data = con.responseText.split("####");
					
					document.getElementById('notransaksi').value = data[0];
					document.getElementById('tgl').value = data[1];
					document.getElementById('kodeorg').value = data[2];
					document.getElementById('nobkm').value = data[3];
					document.getElementById('mandor').value = data[4];
					document.getElementById('mandor1').value = data[5];
					document.getElementById('kerani').value = data[6];
					document.getElementById('divisi').value = trim(data[7]);
					document.getElementById('jenis').value = trim(data[8]);
					document.getElementById('nospk').value = trim(data[9]);
					document.getElementById('status').value = 1;
					
					document.getElementById('mandor').innerHTML = data[11];
					document.getElementById('mandor1').innerHTML = data[12];
					document.getElementById('kerani').innerHTML = data[13];
					document.getElementById('asst').innerHTML = data[14];
					
					document.getElementById('listData').style.display = 'none';
					document.getElementById('header').style.display = 'block';
					document.getElementById('formpencarianheader').style.display='none';
					document.getElementById('mode').value = 'edit';
					
					addHeader(data[0]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


// function editx(notransaksi, tgl, kodeorg, nobkm, mandor, mandor1, kerani,divisi,sts) {
	// document.getElementById('notransaksi').value = notransaksi;
	// document.getElementById('divisi').value = divisi;
	// document.getElementById('tgl').value = tgl;
	// document.getElementById('kodeorg').value = kodeorg;
	// document.getElementById('nobkm').value = nobkm;
	// document.getElementById('mandor').value = mandor;
	// document.getElementById('mandor1').value = mandor1;
	// document.getElementById('kerani').value = kerani;
	// document.getElementById('status').value = sts;
	// document.getElementById('listData').style.display = 'none';
	// document.getElementById('header').style.display = 'block';
	// document.getElementById('formpencarianheader').style.display='none';
	// document.getElementById('mode').value = 'edit';
	// addHeader(notransaksi);
// }
function deletedetail(notransaksi, karyawanid, blok, tph,sesi,noreferensi,sumber) {
	jenispremi = document.getElementById('jenispremi').innerHTML;
	param = 'method=deletedetail' + '&notransaksi=' + notransaksi + '&karyawanid=' + karyawanid + '&blok=' + blok;
	param += '&jenispremi=' + jenispremi;
	param += '&tph=' + tph;
	param += '&sesi=' + sesi;
	param += '&noreferensi=' + noreferensi;
	param += '&sumber=' + sumber;
	tujuan = 'kebun_slave_panenxn.php';
	if(sumber=='kary'){
		info = 'Anda yakin menghapus transaksi 1 karyawan ???';
	}else if(sumber=='blok'){
		info = 'Anda yakin menghapus transaksi 1 blok ???';
	}else{
		info = 'Anda yakin ???';
	}
	
	alertify.confirm("Warning","Anda yakin?",
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
					alertify.alert('Info',con.responseText);
				} else {
					loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function editdetail(notransaksi, karyawanid, blok, tt, hapanen,
					jjgpanen, brdpanen, kgpanen, upah,upahpremi, basis, basis2, 
					lbasis,lbasis2,premibrondol, denda_rp,kontan,namakary,hk,
					numrow,kodeiddenda,denda,tph,sesi,noreferensi,bjr) {
	row = document.getElementById('jlhbrs').value;
	if (row != '' || row != 0) {
		alertify.alert('Info','Silahkan uncheck Per Mandor untuk melakukan Edit\n\nJika nama karyawan tidak muncul silahkan pilih Filter Divisi = Seluruhnya');
		return;
	}
	document.getElementById('noreferensi').value = noreferensi;
	document.getElementById('bjr').value = bjr;
	document.getElementById('tphold').value = tph;
	document.getElementById('sesiold').value = sesi;
	document.getElementById('tph').value = tph;
	document.getElementById('sesi').value = sesi;
	document.getElementById('notransaksi').value = notransaksi;
	//document.getElementById('karyawanid').value = karyawanid;
	document.getElementById('karyawanid').innerHTML="<option value='"+ karyawanid +"'>"+ namakary +"</option>"
	document.getElementById('karyawanid').disabled = true;
	document.getElementById('blok').value = blok;
	document.getElementById('blok').innerHTML="<option value='"+ blok +"'>"+ blok +"</option>"
	document.getElementById('blok').disabled = true;
	//setValue2('karyawanid',karyawanid);
	// setValue2('blok',blok);
	document.getElementById('tt').value = tt;
	document.getElementById('hapanen').value = hapanen;
	document.getElementById('jjgpanen').value = jjgpanen;
	document.getElementById('brdpanen').value = brdpanen;
	document.getElementById('kgpanen').value = kgpanen;
	document.getElementById('upah').value = upah;
	document.getElementById('hk').value = hk;
	document.getElementById('upahpremi').value = upahpremi;
	document.getElementById('basis').value = basis;
	document.getElementById('basis2_').value = basis2;
	document.getElementById('lbasis').value = lbasis;
	document.getElementById('lbasis2_').value = lbasis2;
	document.getElementById('rpbrondol').value = premibrondol;
	document.getElementById('modedetail').value = 'edit';
	jlhdenda   = kodeiddenda.split("##");
	
	isi=denda.split("####");
	if (jlhdenda.length != '') {
		for (i = 0; i <= (jlhdenda.length - 1); i++) {
			document.getElementById('penalti'+jlhdenda[i]).value = isi[(i+1)];
		}
	}
	
	document.getElementById('denda_rp').value = denda_rp;
	if(kontan=='KONTAN'){
		document.getElementById('kontan').checked=true;
		//document.getElementById('info_kontan').innerHTML='Ya';
	}else{
		document.getElementById('kontanfalse').checked=true;
		//document.getElementById('info_kontan').innerHTML='Tidak';
	}
	
	window.scroll(100,0);
	document.getElementById('jjgpanen').focus();
	//getDataDetail();
}

function hidedendav2(id){
	nama = document.getElementsByName(id);
	for (var i = 0; i < nama.length; i++) {
		dis = nama[i].getAttribute("style");
		if(dis!=null && (dis.includes("display:none") || dis.includes("display:none;") || dis.includes("display: none;"))){
			if(nama[i]!=undefined){				
				nama[i].style.display="";
			}
			if(document.getElementById('showdenda')!=undefined){		
				document.getElementById('showdenda').value = '1';
			}
		}else{
			if(nama[i]!=undefined){				
				nama[i].style.display="none";
			}
			if(document.getElementById('showdenda')!=undefined){		
				document.getElementById('showdenda').value = '';
			}
		}
	}
	if(document.getElementById('infokarymandoran')!=undefined){		
		document.getElementById('infokarymandoran').colSpan = '23';
	}
}


function cleardetail(baris) {
	row        = document.getElementById('jlhbrs').value;
	e          = document.getElementById('jumlahkolomdenda').value;
	kodeiddenda= document.getElementById('kodeiddenda').value;
	jlhdenda   = kodeiddenda.split("##");
	if (row == 0) {
		document.getElementById('modedetail').value = 'new';
		document.getElementById('karyawanid').value = '';
		document.getElementById('karyawanid').disabled = false;
		document.getElementById('blok').disabled = false;
		//document.getElementById('blok').value = '';
		setValue2('karyawanid',null);
		document.getElementById('tt').value = '';
		document.getElementById('hapanen').value = '';
		document.getElementById('jjgpanen').value = '';
		document.getElementById('brdpanen').value = '';
		document.getElementById('kgpanen').value = '';
		document.getElementById('upah').value = '';
		document.getElementById('upahpremi').value = '';
		document.getElementById('basis').value = '';
		document.getElementById('basis2_').value = '';
		document.getElementById('lbasis').value = '';
		document.getElementById('lbasis2_').value = '';
		document.getElementById('rpbrondol').value = '';
		document.getElementById('dendaupah').value = '';
		document.getElementById('hk').value = '';
		document.getElementById('denda_rp').value = '';
		document.getElementById('jjgbasis').value = '';
		document.getElementById('tph').value = '';
		document.getElementById('sesi').value = '1';
		document.getElementById('noreferensi').value = '';
		document.getElementById('tphold').value = '';
		document.getElementById('sesiold').value = '1';
		
		if (jlhdenda.length != '') {
			for (i = 0; i <= (jlhdenda.length - 1); i++) {
				document.getElementById('penalti'+jlhdenda[i]).value='';
			}
		}
		//getdivisi();
		getdata();
		hapuswarna('karyawanid#blok#hapanen#jjgpanen#dendaupah#upahpremi');
		document.getElementById('inforekappnn').innerHTML = '';
	} else {
		document.getElementById('blok' + baris).value = '';
		document.getElementById('tt' + baris).value = '';
		document.getElementById('hapanen' + baris).value = '';
		document.getElementById('jjgpanen' + baris).value = '';
		document.getElementById('brdpanen' + baris).value = '';
		document.getElementById('kgpanen' + baris).value = '';
		document.getElementById('upah' + baris).value = '';
		document.getElementById('upahpremi' + baris).value = '';
		document.getElementById('basis' + baris).value = '';
		document.getElementById('basis2_' + baris).value = '';
		document.getElementById('lbasis' + baris).value = '';
		document.getElementById('denda_rp'+baris).value = '';
		document.getElementById('lbasis2_'+baris).value = '';
		document.getElementById('rpbrondol'+baris).value = '';
		document.getElementById('dendaupah'+baris).value = '';
		document.getElementById('hk'+baris).value = '';
		document.getElementById('jjgbasis'+baris).value = '';
		document.getElementById('tph'+baris).value = '';
		document.getElementById('sesi'+baris).value = '1';
		
		if (jlhdenda.length != '') {
			for (i = 0; i <= (jlhdenda.length - 1); i++) {
				document.getElementById('penalti'+jlhdenda[i] + baris).value='';
			}
		}
		hapuswarna('karyawanid'+ baris+'#blok'+ baris+'#hapanen'+ baris+'#jjgpanen'+ baris+'#dendaupah'+ baris+'#upahpremi'+ baris);
		document.getElementById('inforekappnnall').innerHTML = '';
	}
}
function checkval(word, value) {
	if (value.value > 1 && word=='PERHARI') {
		alertify.alert('Info',"Value " + word + " maximal adalah 1");
		value.value = '';
		value.focus();
	}
}
maxf = 0
sekarang = 1;
function saveAll(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alertify.alert('Info','Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	alertify.confirm("Warning","Simpan seluruhnya ?",
		function(){
			maxf = maxRow;
			savedetail(1, maxRow);
		},
		function(){
			return;
		}
	);
}
function savedetail(currRow, maxRow) {
	kontan = document.getElementById('kontan');   
	if(kontan.checked==true){
		kontan='KONTAN';
	}else{
		kontan='';
	}
	row        = document.getElementById('jlhbrs').value;
	notransaksi= document.getElementById('notransaksi').value;
	nobkm      = document.getElementById('nobkm').value;
	sts        = document.getElementById('status').value;
	method     = document.getElementById('method').value;
	jenispremi = document.getElementById('jenispremi').innerHTML;
	kodeiddenda= document.getElementById('kodeiddenda').value;
	jenis      = document.getElementById('jenis').value;
	nospk      = document.getElementById('nospk').value;
	jlhdenda   = kodeiddenda.split("##");
	param = "";
	if (row == 0) {
		karyawanid = document.getElementById('karyawanid').value;
		blok       = document.getElementById('blok').value;
		hapanen    = document.getElementById('hapanen').value;
		tt         = document.getElementById('tt').value;
		jjgpanen   = document.getElementById('jjgpanen').value;
		brdpanen   = document.getElementById('brdpanen').value;
		kgpanen    = document.getElementById('kgpanen').value;
		hk         = document.getElementById('hk').value;
		upah       = document.getElementById('upah').value;
		upahpremi  = document.getElementById('upahpremi').value;
		dendaupah  = document.getElementById('dendaupah').value;
		basis      = document.getElementById('basis').value;
		basis2      = document.getElementById('basis2_').value;
		lbasis     = document.getElementById('lbasis').value;
		lbasis2    = document.getElementById('lbasis2_').value;
		denda_rp   = document.getElementById('denda_rp').value;
		bjr        = document.getElementById('bjr').value;
		jjgbasis   = document.getElementById('jjgbasis').value;
		rpbrondol  = document.getElementById('rpbrondol').value;
		tph        = document.getElementById('tph').value;
		sesi       = document.getElementById('sesi').value;
		tphold     = document.getElementById('tphold').value;
		sesiold    = document.getElementById('sesiold').value;
		noreferensi= document.getElementById('noreferensi').value;
			
		if (jlhdenda.length != '') {
			r ='';
			for (i = 0; i <= (jlhdenda.length - 1); i++) {
				r = jlhdenda[i];
				r=document.getElementById('penalti'+jlhdenda[i]).value;
				param += "&penalti"+jlhdenda[i]+"=" + r;
			}
		}
		
		// validate([
			// ["karyawanid","Nama karyawan wajib diisi."],
			// ["blok","Kode blok wajib diisi."]
		// ]);
		
		if(karyawanid==''){notif('karyawanid','Nama karyawan wajib diisi.'); return;}
		if(blok==''){notif('blok','Kode blok wajib diisi.'); return;}
		
		if(jjgpanen=='' || jjgpanen=='0' && brdpanen>0){
		}else{			
			if(hapanen=='' || hapanen=='0'){notif('hapanen','Luas ha panen wajib diisi.'); return;}
			if(jjgpanen=='' || jjgpanen=='0'){notif('jjgpanen','Jumlah janjang panen wajib diisi.'); return;}
		}
		
	} else {
		karyawanid= document.getElementById('karyawanid' + currRow).value;
		blok      = document.getElementById('blok' + currRow).value;
		hapanen   = document.getElementById('hapanen' + currRow).value;
		tt        = document.getElementById('tt' + currRow).value;
		jjgpanen  = document.getElementById('jjgpanen' + currRow).value;
		brdpanen  = document.getElementById('brdpanen' + currRow).value;
		kgpanen   = document.getElementById('kgpanen' + currRow).value;
		hk        = document.getElementById('hk' + currRow).value;
		upah      = document.getElementById('upah' + currRow).value;
		upahpremi = document.getElementById('upahpremi' + currRow).value;
		dendaupah = document.getElementById('dendaupah' + currRow).value;
		basis     = document.getElementById('basis' + currRow).value;
		basis2    = document.getElementById('basis2_' + currRow).value;
		lbasis    = document.getElementById('lbasis' + currRow).value;
		lbasis2   = document.getElementById('lbasis2_' + currRow).value;
		denda_rp  = document.getElementById('denda_rp' + currRow).value;
		bjr       = document.getElementById('bjr' + currRow).value;
		jjgbasis  = document.getElementById('jjgbasis' + currRow).value;
		rpbrondol = document.getElementById('rpbrondol' + currRow).value;
		tph       = document.getElementById('tph' + currRow).value;
		sesi      = document.getElementById('sesi' + currRow).value;
		tphold    = document.getElementById('tphold' + currRow).value;
		sesiold   = document.getElementById('sesiold' + currRow).value;
		noreferensi   = document.getElementById('noreferensi' + currRow).value;
		
		if (jlhdenda.length != '') {
			r ='';
			for (i = 0; i <= (jlhdenda.length - 1); i++) {
				r = jlhdenda[i];
				r=document.getElementById('penalti'+jlhdenda[i] + currRow).value;
				param += "&penalti"+jlhdenda[i]+"=" + r;
			}
		}
	}
	
	if(tphold==''){tphold=tph;}
	if(sesiold==''){sesiold=sesi;}
	param += '&jenis=' + jenis;
	param += '&nospk=' + nospk;
	param += '&divisi=' + getValue('divisi');
	param += '&tphold=' + tphold;
	param += '&sesiold=' + sesiold;
	param += '&noreferensi=' + noreferensi;	
	param += '&tph=' + tph;
	param += '&sesi=' + sesi;
	param += '&nobkm=' + nobkm;
	param += '&notransaksi=' + notransaksi;
	param += '&hk=' + hk;
	param += '&upahpremi=' + upahpremi;
	param += '&jenispremi=' + jenispremi;
	param += '&rpbrondol=' + rpbrondol;
	param += '&jjgbasis=' + jjgbasis;
	param += '&dendaupah=' + dendaupah;
	param += '&lbasis2=' + lbasis2;
	param += '&basis2=' + basis2;
	param += '&karyawanid=' + karyawanid + '&blok=' + blok + '&hapanen=' + hapanen + '&jjgpanen=' + jjgpanen;
	param += '&brdpanen=' + brdpanen + '&kgpanen=' + kgpanen + '&upah=' + upah + '&basis=' + basis;
	param += '&lbasis=' + lbasis + '&denda_rp=' + denda_rp + '&tt=' + tt + '&bjr=' + bjr;
	param += '&sts=' + sts;
	param += '&kontan=' + kontan;
	param += '&method=' + method;
	param += '&kodeiddenda=' + kodeiddenda;
	param += '&jlhdenda=' + jlhdenda.length;
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	if (currRow != undefined) {
		document.getElementById('row'+currRow).style.backgroundColor='cyan';
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
					if (currRow != undefined) {
						document.getElementById('row' + currRow).style.backgroundColor = 'red';
					}
				} else {
					cleardetail(currRow);
					if (currRow != undefined) {
						document.getElementById('row' + currRow).style.backgroundColor = '';
					}
					currRow += 1;
					sekarang = currRow;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						if (row == 0) {
							document.getElementById('inforekappnn').innerHTML = "";
						}else{
							document.getElementById('inforekappnnall').innerHTML = "";
						}
						loaddatadetail();
					} else {
						savedetail(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getHitungDenda(brs, isi) {
	row         = document.getElementById('jlhbrs').value;
	kodeorg     = document.getElementById('kodeorg').value;
	filterdivisi= document.getElementById('filterdivisi').value;
	tgl         = document.getElementById('tgl').value;
	kodeiddenda = document.getElementById('kodeiddenda').value;
	jlhdenda    = kodeiddenda.split("##");
	
	param = "";
	if (row == 0) {
		karyawanid= document.getElementById('karyawanid').value;
		blok      = document.getElementById('blok').value;
		jjgpanen  = document.getElementById('jjgpanen').value;
	} else {
		karyawanid= document.getElementById('karyawanid' + brs).value;
		blok      = document.getElementById('blok' + brs).value;
		jjgpanen  = document.getElementById('jjgpanen' + brs).value;
	}
	if (row == 0) {
		if (jlhdenda.length != '') {
			r ='';
			for (i = 0; i <= (jlhdenda.length - 1); i++) {
				r = jlhdenda[i];
				r=document.getElementById('penalti'+jlhdenda[i]).value;
				param += "&penalti["+jlhdenda[i]+"]=" + r;
			}
		}
	} else {
		if (jlhdenda.length != '') {
			r ='';
			for (i = 0; i <= (jlhdenda.length - 1); i++) {
				r = jlhdenda[i];
				r=document.getElementById('penalti'+jlhdenda[i] + brs).value;
				param += "&penalti["+jlhdenda[i]+"]=" + r;
			}
		}
	}
	if (karyawanid == '' || blok == '' || jjgpanen == '') {
		alertify.alert('Info','Silahkan isi Karyawan, Blok dan Jjg Panen terlebih dahulu.');
		isi.value = 0;
		isi.focus();
		return;
	}
	param += '&divisi=' + getValue('divisi');
	param += '&method=getHitungDenda' + '&filterdivisi=' + filterdivisi;
	param += '&tgl=' + tgl + '&karyawanid=' + karyawanid + '&blok=' + blok + '&jjgpanen=' + jjgpanen + '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					if (row == 0) {
						document.getElementById('denda_rp').value = numberFormat(trim(con.responseText));
					} else {
						document.getElementById('denda_rp' + brs).value = numberFormat(trim(con.responseText));
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getDataDetail(baris,id) {
	row         = document.getElementById('jlhbrs').value;
	kodeorg     = document.getElementById('kodeorg').value;
	filterdivisi= document.getElementById('filterdivisi').value;
	tgl         = document.getElementById('tgl').value;
	jenispremi  = document.getElementById('jenispremi').innerHTML;
	jenis       = document.getElementById('jenis').value;
	nospk       = document.getElementById('nospk').value;
	kontan      = document.getElementById('kontan');
	if(kontan.checked==true){
		kontan='KONTAN';
	}else{
		kontan=0;
	}
	param = "";
	if (row == 0) {
		karyawanid= document.getElementById('karyawanid').value;
		blok      = document.getElementById('blok').value;
		jjgpanen  = document.getElementById('jjgpanen').value;
		bjr       = document.getElementById('bjr').value;
		hapanen   = document.getElementById('hapanen').value;
		brondol   = document.getElementById('brdpanen').value;
		modedetail= document.getElementById('modedetail').value;
		param += '&modedetail=' + modedetail;
	} else {
		karyawanid= document.getElementById('karyawanid' + baris).value;
		blok      = document.getElementById('blok' + baris).value;
		jjgpanen  = document.getElementById('jjgpanen' + baris).value;
		bjr       = document.getElementById('bjr'+ baris).value;
		hapanen   = document.getElementById('hapanen'+ baris).value;
		brondol   = document.getElementById('brdpanen'+ baris).value;
	}
	param += '&method=getDataDetail' + '&filterdivisi=' + filterdivisi + '&tgl=' + tgl + '&karyawanid=' + karyawanid + '&blok=' + blok + '&jjgpanen=' + jjgpanen + '&kodeorg=' + kodeorg+ '&kontan=' + kontan;
	param += '&bjr=' + bjr;
	param += '&jenis=' + jenis;
	param += '&nospk=' + nospk;
	param += '&hapanen=' + hapanen;
	param += '&brondol=' + brondol;
	param += '&jenispremi=' + jenispremi;
	param += '&divisi=' + getValue('divisi');
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					isdt        = con.responseText.split("######");
					bjr         = trim(isdt[0]);
					umr         = trim(isdt[1]);
					tt          = trim(isdt[2]);
					rkppnn      = trim(isdt[3]);
					rplb1       = trim(isdt[4]);
					hk          = trim(isdt[5]);
					rpsiapbasis = trim(isdt[6]);
					rplbbss1    = trim(isdt[7]);
					rplbbss2    = trim(isdt[8]);
					rpbrondol   = trim(isdt[9]);
					dendaupah   = trim(isdt[10]);
					jjgbasis    = trim(isdt[11]);
					hksebulan   = trim(isdt[12]);
					info        = trim(isdt[13]);
					rpsiapbasis2= trim(isdt[14]);
					
					kgpanen = trim(isdt[0]) * parseFloat(jjgpanen);
					if (isNaN(kgpanen) == true) {kgpanen = 0;}
					if(dendaupah>0){
						warna = "red";
					}else{
						warna = "";
					}
					
					
					if (rkppnn == 'x' && tt != '') {
						alert('Data di menu : Kebun - Transaksi - Rekap Panen belum di input atau di posting.');
						if (row == 0) {
							document.getElementById('hapanen').disabled = true;
							document.getElementById('jjgpanen').disabled = true;
							document.getElementById('brdpanen').disabled = true;
						} else {
							document.getElementById('hapanen' + baris).disabled = true;
							document.getElementById('jjgpanen' + baris).disabled = true;
							document.getElementById('brdpanen' + baris).disabled = true;
						}
						return;
					} else {
						if (row == 0) {
							document.getElementById('hapanen').disabled = false;
							document.getElementById('jjgpanen').disabled = false;
							document.getElementById('brdpanen').disabled = false;
							document.getElementById('inforekappnn').innerHTML = info;
						} else {
							document.getElementById('hapanen' + baris).disabled = false;
							document.getElementById('jjgpanen' + baris).disabled = false;
							document.getElementById('brdpanen' + baris).disabled = false;
							document.getElementById('inforekappnnall').innerHTML = info;
						}
					}
					if (row == 0) {
						document.getElementById('tt').value = tt;
						document.getElementById('bjr').value = bjr;
						document.getElementById('kgpanen').disabled = true;
						document.getElementById('kgpanen').value = numberFormat(kgpanen);
						document.getElementById('hk').value = hk;
						document.getElementById('jjgbasis').value = jjgbasis;
						document.getElementById('upah').value = numberFormat(umr);
						document.getElementById('dendaupah').value = numberFormat(dendaupah);
						document.getElementById('dendaupah').style.backgroundColor = warna;
						document.getElementById('basis').value = rpsiapbasis;
						document.getElementById('basis2_').value = rpsiapbasis2;
						document.getElementById('lbasis').value = rplbbss1;
						document.getElementById('lbasis2_').value = rplbbss2;
						document.getElementById('rpbrondol').value = rpbrondol;
					} else {
						document.getElementById('tt' + baris).value = tt;
						document.getElementById('bjr' + baris).value = bjr;
						document.getElementById('kgpanen' + baris).disabled = true;
						document.getElementById('kgpanen' + baris).value = numberFormat(kgpanen);
						document.getElementById('hk' + baris).value = hk;
						document.getElementById('jjgbasis'+ baris).value = jjgbasis;
						document.getElementById('upah' + baris).value = numberFormat(umr);
						document.getElementById('dendaupah' + baris).value = numberFormat(dendaupah);
						document.getElementById('dendaupah'+ baris).style.backgroundColor = warna;
						document.getElementById('basis'+ baris).value = rpsiapbasis;
						document.getElementById('basis2_'+ baris).value = rpsiapbasis2;
						document.getElementById('lbasis'+ baris).value = rplbbss1;
						document.getElementById('lbasis2_'+ baris).value = rplbbss2;
						document.getElementById('rpbrondol'+ baris).value = rpbrondol;
					}
					if(hksebulan!='' && jenispremi!='LIBUR'){
						if(row==0){	
							document.getElementById('upah').value='';
							document.getElementById('dendaupah').value='';
							document.getElementById('hk').value='';
							document.getElementById('upahpremi').disabled=false;
							document.getElementById('upahpremi').value=numberFormat(umr);
							
							/* Munculkan notifikasi hanya pada saat click nama karyawan */
							if(id=='karyawanid'){
								notif('upahpremi',hksebulan+'\n\nJika ingin melanjutkan silahkan isi Rupiah HK di kolom Upah Premi');
								return false;								
							}
						} else {
							document.getElementById('upah'+baris).value='';
							document.getElementById('dendaupah'+baris).value='';
							document.getElementById('hk'+baris).value='';
							document.getElementById('upahpremi'+baris).disabled=false;
							document.getElementById('upahpremi'+baris).value=numberFormat(umr);
							
							/* Munculkan notifikasi hanya pada saat click nama karyawan */
							if(id=='karyawanid'+baris || id=='blok'+baris){
								notif('upahpremi'+baris,hksebulan+'\n\nJika ingin melanjutkan silahkan isi Rupiah HK di kolom Upah Premi');
								return false;
							}
						}
					}else{
						if(row==0){	
							document.getElementById('upahpremi').value='';
							document.getElementById('upahpremi').disabled=true;
						} else {
							document.getElementById('upahpremi'+baris).value='';
							document.getElementById('upahpremi'+baris).disabled=true;
						}
					}
					
					if(row!=0){						
						baris+=1;
						if((baris>row) || (row == undefined)){
							//gak ada apa apa disini
						} else {
							copy = document.getElementById('copyblok');
							if(id.substring(0,4)=='blok' && copy.checked==true && getValue(id)!=''){
								document.getElementById('blok'+baris).value=blok;
								getDataDetail(baris,id)
							}
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdatamandor() {
	filterdivisi= document.getElementById('filterdivisi').value;
	mandor      = document.getElementById('mandor').value;
	tgl         = document.getElementById('tgl').value;
	kodeorg     =document.getElementById('kodeorg').value;
	jenispremi  =document.getElementById('jenispremi').innerHTML;
	filterunit  =document.getElementById('filterunit').value;
	
	showpermandor = document.getElementById('showpermandor');
	e = document.getElementById('jumlahkolomdenda').value;
	if (showpermandor.checked == true) {
		method = 'getdatamandor';
	} else {
		method = 'inputdetail';
	}
	param = 'method=' + method + '&filterdivisi=' + filterdivisi + '&mandor=' + mandor + '&tgl=' + tgl+ '&kodeorg=' + kodeorg;
	param += '&jenispremi=' + jenispremi;
	param += '&filterunit='+filterunit;
	param += '&divisi=' + getValue('divisi');
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					isdtmdr = con.responseText.split("######");
					document.getElementById('inputdetail').innerHTML = isdtmdr[0];
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
					
					document.getElementById('phead').style.display = 'none';
					document.getElementById('pfot').style.display = 'none';
					kodeiddenda= document.getElementById('kodeiddenda').value;
					jlhdenda   = kodeiddenda.split("##");
					
					if (jlhdenda.length != '') {
						for (i = 0; i <= (jlhdenda.length - 1); i++) {
							document.getElementById('p'+jlhdenda[i]).style.display = 'none';
						}
					}
					if(isdtmdr[1]!=undefined){
						row = trim(isdtmdr[1]);
					}
					getdata(filterdivisi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdata(filterdivisi) {
	if(filterdivisi==undefined){		
		filterdivisi=document.getElementById('filterdivisi').value; 
	}
	row        = document.getElementById('jlhbrs').value;
	tgl        = document.getElementById('tgl').value;
	kodeorg    = document.getElementById('kodeorg').value;
	kodeorgkary= document.getElementById('filterunit').value;
	notransaksi= document.getElementById('notransaksi').value;
	jenis      = document.getElementById('jenis').value;
	nospk      = document.getElementById('nospk').value;
	
	param = 'method=getdata' + '&filterdivisi=' + filterdivisi + '&tgl=' + tgl + '&kodeorg=' + kodeorg+'&kodeorgkary='+kodeorgkary;
	param += '&divisi=' + getValue('divisi');
	param += '&notransaksi=' + notransaksi;
	param += '&jenis=' + jenis;
	param += '&nospk=' + nospk;
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					if (row == 0) {
						isdata = con.responseText.split("######");
						document.getElementById('karyawanid').innerHTML = isdata[0];
						document.getElementById('karyawanidabsensi').innerHTML = isdata[0];
						document.getElementById('blok').innerHTML = isdata[1];
						//loaddatadetail();
					} else {
						for (i = 1; i <= row; i++) {
							isdata = con.responseText.split("######");
							document.getElementById('blok' + i).innerHTML = isdata[1];
						}
					}
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getnotransaksi() {
	kodeorg= document.getElementById('kodeorg').value;
	tgl    = document.getElementById('tgl').value;
	document.getElementById('notransaksi').value = '';
	param = 'tgl=' + tgl + '&kodeorg=' + kodeorg + '&method=getnotransaksi';
	param += '&divisi=' + getValue('divisi');
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					document.getElementById('notransaksi').value = trim(con.responseText);
					document.getElementById('nobkm').value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function addHeader() {
	kodeorg    = document.getElementById('kodeorg').value;
	mandor     = document.getElementById('mandor').value;
	mandor1    = document.getElementById('mandor1').value;
	asst       = document.getElementById('asst').value;
	kerani     = document.getElementById('kerani').value;
	nobkm      = document.getElementById('nobkm').value;
	tgl        = document.getElementById('tgl').value;
	mode       = document.getElementById('mode').value;
	divisi     = document.getElementById('divisi').value;
	notransaksi= document.getElementById('notransaksi').value;
	document.getElementById('status').disabled = true;
	
	validate([
        ["kodeorg","Kebun tidak boleh kosong."],
        ["tgl","Tanggal tidak boleh kosong."],
        ["divisi","Divisi pekerjaan tidak boleh kosong."]
    ]);
	
	jenis= document.getElementById('jenis').value;
	nospk= document.getElementById('nospk').value;
	if(jenis=='BOR' && nospk==''){
		alertify.alert('Info',"No SPK wajib diisi."); return;
	}

	
	if(mode=='baru'){
		document.getElementById('tomboldetail').disabled = true;
		document.getElementById('mode').value='edit';
	}else{
		document.getElementById('tomboldetail').disabled = false;
	}
	
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('mandor').disabled = true;
	document.getElementById('divisi').disabled = true;
	document.getElementById('tgl').disabled = true;
	document.getElementById('jenis').disabled = true;
	document.getElementById('nospk').disabled = true;
	
	param = 'method=detail';
	param += '&jenis=' + jenis;
	param += '&nospk=' + nospk;
	param += '&tgl=' + tgl + '&kodeorg=' + kodeorg + '&nobkm=' + nobkm + '&mandor=' + mandor + '&mandor1=' + mandor1 + '&asst=' + asst + '&kerani=' + kerani + '&notransaksi=' + notransaksi+ '&mode=' + mode;
	param += '&divisi=' + divisi;
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					//data = con.responseText.split("####");
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detailx').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					
					document.getElementById('notransaksi').value = notransaksi;
					inputdetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function inputdetail(notransaksi) {
	kodeorg      = document.getElementById('kodeorg').value;
	filterdivisi = document.getElementById('filterdivisi').value;
	jenispremi   = document.getElementById('jenispremi').innerHTML;
	showpermandor= document.getElementById('showpermandor');
	e = document.getElementById('jumlahkolomdenda').value;
	if (showpermandor.checked == true) {
		showpermandor = 1;
	} else {
		showpermandor = 0;
	}
	tgl        = document.getElementById('tgl').value;
	notransaksi= document.getElementById('notransaksi').value;
	filterunit = document.getElementById('filterunit').value;
	jenis      = document.getElementById('jenis').value;
	nospk      = document.getElementById('nospk').value;
	
	param = 'method=inputdetail';
	param += '&jenis=' + jenis;
	param += '&nospk=' + nospk;
	param += '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi + '&filterdivisi=' + filterdivisi + '&showpermandor=' + showpermandor + '&jenispremi=' + jenispremi;
	param += '&filterunit='+filterunit;
	param += '&divisi=' + getValue('divisi');
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					document.getElementById('inputdetail').innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
					
					document.getElementById('phead').style.display = 'none';
					document.getElementById('pfot').style.display = 'none';					
					kodeiddenda= document.getElementById('kodeiddenda').value;
					jlhdenda   = kodeiddenda.split("##");
					
					if (jlhdenda.length != '') {
						for (i = 0; i <= (jlhdenda.length - 1); i++) {
							document.getElementById('p'+jlhdenda[i]).style.display = 'none';
						}
					}
					
					loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function add_new_data(sumber) {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	document.getElementById('formpencarianheader').style.display = 'none';
	getdivmdr(sumber);
	cancel();
}
function del(notransaksi, numrow) {
	param = 'method=delete' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_panenxn.php';
	alertify.confirm("Warning","Anda yakin?",
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
					alertify.alert('Info',con.responseText);
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
function displayList() {
	document.getElementById('mode').value = 'baru';
	document.getElementById('notransaksisch').value = '';
	document.getElementById('tglmulai').value = '';
	document.getElementById('tglselesai').value = '';
	//document.getElementById('divsch').value = '';
	document.getElementById('postingsrc').value = '';
	//document.getElementById('periodesch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
	document.getElementById('formpencarianheader').style.display='block';
	// document.getElementById('hidebtn').style.display = 'block';
	// document.getElementById('unhidebtn').style.display = 'none';
	setValue2('postingsrc',null);
	setValue2('divsch',null);
	loaddata(0);
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	notransaksisch= document.getElementById('notransaksisch').value;
	tglmulai      = document.getElementById('tglmulai').value;
	tglselesai    = document.getElementById('tglselesai').value;
	divsch        = document.getElementById('divsch').value;
	postingsrc    = document.getElementById('postingsrc').value;
	periodesch    = document.getElementById('periodesch').value;
	mandor        = document.getElementById('mandorsrc').value;
	kontan        = document.getElementById('kontanansch').value;
	
	param = 'method=loaddata&page=' + page;
	param += '&mandor=' + mandor;
	param += '&kontan=' + kontan;
	param += '&divsch=' + divsch;
	param += '&notransaksisch=' + notransaksisch;
	param += '&tglmulai=' + tglmulai;
	param += '&tglselesai=' + tglselesai;
	param += '&postingsrc=' + postingsrc;
	param += '&periodesch=' + periodesch;
	
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
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
function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('tgl').disabled = false;
	document.getElementById('tgl').value = '';
	document.getElementById('status').value = '0';
	document.getElementById('status').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('divisi').disabled = false;
	document.getElementById('mandor').disabled = false;
	// document.getElementById('kodeorg').value = '';
	document.getElementById('divisi').value = '';
	document.getElementById('notransaksi').value = '';
	document.getElementById('nobkm').value = '';
	document.getElementById('mandor').value = '';
	document.getElementById('mandor1').value = '';
	document.getElementById('kerani').value = '';
	document.getElementById('mode').value = 'baru';
	
	setValue2('kodeorg',null);
	setValue2('mandor',null);
	setValue2('divisi',null);
	setValue2('mandor1',null);
	setValue2('kerani',null);
}

function cariby(val,sumber){
	if(sumber=='namakary'){
		if(getValue('namakarydetsch')==''){
			document.getElementById('namakarydetsch').value=val;			
		}else{
			document.getElementById('namakarydetsch').value='';			
		}
	}
	if(sumber=='blok'){
		if(getValue('blokdetsch')==''){
			document.getElementById('blokdetsch').value=val;			
		}else{
			document.getElementById('blokdetsch').value='';
		}
	}
	if(sumber=='tt'){
		if(getValue('ttdetsch')==''){
			document.getElementById('ttdetsch').value=val;			
		}else{
			document.getElementById('ttdetsch').value='';			
		}
	}
	loaddatadetail();
}

function cancelcari(){
	document.getElementById('namakarydetsch').value='';
	document.getElementById('blokdetsch').value='';
	document.getElementById('ttdetsch').value='';
	loaddatadetail();
}

function loaddatadetailxls(notransaksi,jenis){
    ev = 'event';
	tipe = 'excel';
	notransaksi= document.getElementById('notransaksi').value;
	
	param = "method=loaddatadetail&tipe="+tipe;
	param += '&notransaksi=' + notransaksi;
	
	title="Excel";
	showDialog1(title,"<iframe frameborder=0 style='width:100%;min-height:400px'"+" src='kebun_slave_panenxn.php?"+param+"'></iframe>",'900','400',ev);	
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}

function loaddatadetail(notransaksi) {
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('tgl').disabled = true;
	tgl        = document.getElementById('tgl').value;
	kodeorg    = document.getElementById('kodeorg').value;
	notransaksi= document.getElementById('notransaksi').value;
	
	namakary   =document.getElementById('namakarydetsch').value;
	blok       =document.getElementById('blokdetsch').value;
	tt         =document.getElementById('ttdetsch').value;
	showdenda  =document.getElementById('showdenda').value;
	jenis      = document.getElementById('jenis').value;
	nospk      = document.getElementById('nospk').value;
	
	showdet=document.getElementById('showdetail');
	if(showdet.checked==true){showdetail=1;}else{showdetail=0;}
	showblo=document.getElementById('showblok');
	if(showblo.checked==true){showblok=1;}else{showblok=0;}
	showkar=document.getElementById('showkary');
	if(showkar.checked==true){showkary=1;}else{showkary=0;}
	
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	param += '&jenis=' + jenis;
	param += '&nospk=' + nospk;
	param += '&namakary=' + namakary;
    param += '&blok=' + blok;
    param += '&tt=' + tt;
    param += '&showdetail=' + showdetail;
    param += '&showblok=' + showblok;
    param += '&showdenda=' + showdenda;
    param += '&showkary=' + showkary;
    param += '&divisi=' + getValue('divisi');
	
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
					loaddataabsensi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
}
function form() {
	width = '720';
	height = '';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<fieldset><div id=containerd align=center style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}
function html(notransaksi, kodeorg, tgl) {
	form();
	param = 'method=html' + '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function excel(ev, tujuan) {
	unitexp = document.getElementById('unitexp').value;
	perexp = document.getElementById('perexp').value;
	if (unitexp == '' || perexp == '') {
		alertify.alert('Info','Lengkapi unit dan periode.');
		return;
	}
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
	printFile(param, tujuan, judul, ev);
}
				
function getkontan(jenispremi){
	row = document.getElementById('jlhbrs').value;
	kontan = document.getElementById('kontan');   
	kontanfalse = document.getElementById('kontanfalse');   
	if(jenispremi=='LIBUR'){
		if(kontan.checked==true){
			kontan='KONTAN';
			//document.getElementById('info_kontan').innerHTML='Ya';
		}else{
			kontan=0;
			//document.getElementById('info_kontan').innerHTML='Tidak';
		}
	}else{
		document.getElementById('kontan').checked=false;
		document.getElementById('kontanfalse').checked=true;
		//document.getElementById('info_kontan').innerHTML='Tidak';
		alertify.alert('Info','Kontanan hanya di perbolehkan pada hari libur.'); return;
	}
}

function getbasispnn(jenispremi,jenis,brs) {
	// width = '';
	// height = '';
	// content = "<div id=contbsspnn align=center style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
	// ev = 'event';
	title = "Basis Panen";
	//showDialog5(title, content, width, height, ev);
	
	kodeorg = document.getElementById('kodeorg').value;
	tgl = document.getElementById('tgl').value;
	param = 'method=getbasispnn' + '&kodeorg=' + kodeorg+ '&jenispremi=' + jenispremi;
	param += '&tgl=' + tgl;
	
	if(jenis=='detail'){		
		bjr = document.getElementById('bjr' + brs).value;
		tahuntanam = document.getElementById('tt' + brs).value;
		param += '&bjr=' + bjr;
		param += '&tahuntanam=' + tahuntanam;
	}
	
	tujuan = 'kebun_slave_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					//document.getElementById('contbsspnn').innerHTML = con.responseText;
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function notif(idkolom,isipesan){
	col = idkolom.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.focus();
		kolom.style.borderColor='red';		
		//kolom.style.backgroundColor='#F2F94D';
		kolom.style.fontWeight='bold';
	}
	alertify.alert('Info',isipesan); return;
}

function hapuswarna(id){
	col = id.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.style.borderColor='';		
		kolom.style.backgroundColor='';
		kolom.style.fontWeight='';
	}
}


function saveabsensi(){
	tgl           =document.getElementById('tgl').value;
	nobkm         =document.getElementById('nobkm').value;
	notransaksi   =document.getElementById('notransaksi').value;
	karyawanid    =document.getElementById('karyawanidabsensi').value;
	jhk           =document.getElementById('jhkabsen').value;
	kodeabsen     =document.getElementById('kodeabsen').value;
	upah          =document.getElementById('upahabsen').value;
	premi         =document.getElementById('premiabsen').value;
	keterangan    =document.getElementById('keteranganabsen').value;
	method        =document.getElementById('methodabsensi').value;
	jenispremi    =document.getElementById('jenispremi').innerHTML;
	kodeorg       =document.getElementById('kodeorg').value;
	kodeorgabsensi=document.getElementById('kodeorgabsensi').value;
	noakun        =document.getElementById('noakunabsensi').value;
	
	if(karyawanid==''){
		notif('karyawanidabsensi','Nama Karyawan Wajib diisi.');return;
	}
	if(kodeabsen==''){
		notif('kodeabsen','Kode Absensi tidak boleh kosong.');return;
	}
	if(noakun==''){
		notif('noakunabsensi','Noakun tidak boleh kosong.');return;
	}
	if(keterangan==''){
		notif('keteranganabsen','Keterangan tidak boleh kosong.');return;
	}
	if(upah==''){upah=0;}
	if(premi==''){premi=0;}
	if(upah==0 && premi==0){
		//notif('upahabsen#premiabsen','Upah atau Premi wajib diisi.');return;
	}
	
    param ='';
    param +='&notransaksi='+notransaksi;
    param +='&kodeorg='+kodeorg;
    param +='&jenispremi='+jenispremi;
    param +='&nobkm='+nobkm;
    param +='&tgl='+tgl;
    param +='&method='+method;
    param +='&karyawanid='+karyawanid;
	param += '&jhk=' + jhk;
	param += '&kodeabsen=' + kodeabsen;
	param += '&upah=' + upah;
	param += '&noakun=' + noakun;
	param += '&premi=' + premi;
	param += '&keterangan=' + keterangan;
	param += '&kodeorgabsensi=' + kodeorgabsensi;
	param += '&divisi=' + getValue('divisi');
    tujuan='kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
                } else {
                    clearabsensi();
					loaddataabsensi();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function clearabsensi(){
	document.getElementById('karyawanidabsensi').value='';
	document.getElementById('karyawanidabsensi').disabled=false;
	document.getElementById('kodeabsen').value='H';
	setValue2('karyawanidabsensi',null);
	setValue2('kodeabsen','H');
	
	document.getElementById('jhkabsen').value='1';
	document.getElementById('upahabsen').value='';
	document.getElementById('premiabsen').value='';
	document.getElementById('keteranganabsen').value='';
	document.getElementById('kodeorgabsensi').value='';
	document.getElementById('methodabsensi').value='insertabsensi';
	hapuswarna('kodeabsen#jhkabsen#upahabsen#karyawanidabsensi#premiabsen');
}

function loaddataabsensi(){
	tgl        =document.getElementById('tgl').value;
	notransaksi=document.getElementById('notransaksi').value;
	nobkm      =document.getElementById('nobkm').value;
	
    param ='';
    param +='&method=loaddataabsensi';
    param +='&notransaksi='+notransaksi;
    param +='&tgl='+tgl;
    param +='&nobkm='+nobkm;
    param += '&divisi=' + getValue('divisi');
    tujuan='kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('loaddataabsensi').innerHTML = con.responseText;
					getdata();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function editabsensi(karyawanid,absensi,nilaihk,umr,premi,penjelasan,kodeorgabsensi, noakun){
	document.getElementById('karyawanidabsensi').value=karyawanid;
	document.getElementById('kodeabsen').value=absensi;
	document.getElementById('jhkabsen').value=nilaihk;
	document.getElementById('upahabsen').value=umr;
	document.getElementById('premiabsen').value=premi;
	document.getElementById('keteranganabsen').value=penjelasan;
	document.getElementById('kodeorgabsensi').value=kodeorgabsensi;
	document.getElementById('methodabsensi').value='updateabsensi';
	document.getElementById('karyawanidabsensi').disabled=true;
	setValue2('noakunabsensi',noakun);
	setValue2('karyawanidabsensi',karyawanid);
	setValue2('kodeabsen',absensi);
}

function delabsen(notransaksi,tgl,kodeorg,karyawanid){
	param ='';
    param +='&method=delabsen';
    param +='&notransaksi='+notransaksi;
    param +='&tgl='+tgl;
    param +='&kodeorg='+kodeorg;
    param +='&karyawanid='+karyawanid;
    tujuan='kebun_slave_panenxn.php';
	
	alertify.confirm("Warning","Anda yakin?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
                } else {
                    loaddataabsensi();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function getumrabsensi(){
	tgl       =document.getElementById('tgl').value;
	karyawanid=document.getElementById('karyawanidabsensi').value;
	jhk       =document.getElementById('jhkabsen').value;
	kodeorg   =document.getElementById('kodeorg').value;
	jenispremi=document.getElementById('jenispremi').innerHTML;
	kodeabsen =document.getElementById('kodeabsen').value;
	if(kodeabsen=='H'){
		document.getElementById('jhkabsen').disabled=false;
	}else{
		document.getElementById('jhkabsen').disabled=true;
	}
	
	if(jhk>1){
		alertify.alert('Info','Jumlah HK maksimal dalam sehari = 1 HK'); 
		document.getElementById('jhkabsen').value='';
		document.getElementById('upahabsen').value='';
		return false;
	}
	
    param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl+'&kodeorg='+kodeorg+'&jhk='+jhk+'&jenispremi='+jenispremi;
	param += '&divisi=' + getValue('divisi');
    tujuan='kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert('Info',con.responseText);
                } else {
					data = con.responseText.split("####"); 
					umr = data[0];
					jenishari = trim(data[1]);
					jumlahhk = trim(data[2]);
					jlhrp = parseFloat(trim(umr))*parseFloat(jhk);
					if(isNaN(jlhrp)==true){
						jlhrp=0;
					}
					if(umr=='' || umr==0){
						notif('upahabsen','Gaji Pokok Karyawan belum ada');	 return;
					}
					
					if(jenishari=='LIBUR' && jhk>0 ){
						document.getElementById('upahabsen').value='';
						document.getElementById('jhkabsen').value='';
						notif('premiabsen','Untuk hari libur Upah = Nol, Absensi = HM / HB dan rupiah biaya langsung masuk ke Premi.');						
						return false;
					}
					
					//Jika HK KHL > 20
					if(jumlahhk!=''){						
						document.getElementById('kodeabsen').value='';
						document.getElementById('jhkabsen').value='';
						document.getElementById('upahabsen').value='';
						
						notif('kodeabsen#jhkabsen#upahabsen',jumlahhk);
						//alert(jumlahhk); 
						return false;
					}
					
                    document.getElementById('upahabsen').value = jlhrp;
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function getnilaihk(){
	kodeabsen=document.getElementById('kodeabsen').value;
    param='method=getnilaihk'+'&kodeabsen='+kodeabsen;
    tujuan='kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('jhkabsen').value = trim(con.responseText);
					getumrabsensi();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}


function getunit(){
    filterpt=document.getElementById('filterpt').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=getunit';
    param += '&filterpt=' + filterpt;
    param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + getValue('divisi');
    tujuan = 'kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert('Info',con.responseText);
                } else {
					data = con.responseText.split("####");
                    document.getElementById('filterunit').innerHTML = trim(data[0]);
                    document.getElementById('filterdivisi').innerHTML = trim(data[1]);
					getdata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdivisi(){
    filterunit=document.getElementById('filterunit').value;
    
    param = 'method=getdivisi';
    param += '&filterunit=' + filterunit;
	param += '&divisi=' + getValue('divisi');
	param += '&tgl=' + getValue('tgl');
	param += '&kodeorg=' + getValue('kodeorg');
    tujuan = 'kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('filterdivisi').innerHTML = con.responseText;
					getdata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getjurnal(pt,notransaksi,tgl1,tgl2){
	// width    = '';
	// height   = '';
	// title    = "Detail Jurnal";
	// content  = "<fieldset ><legend>"+title+"</legend>";
	// content += "<div style=height:370px;width:880px;overflow:auto; id=containerjurnal><pre>";
	// content += "</div>";
	// content += "</fieldset>";
	
    // ev = 'event';
    // showDialog1(title, content, width, height, ev); 
	
	param = 'pt=' + pt;
	param += '&ref=' + notransaksi;
	param += '&periode=' + tgl1;
	param += '&periode1=' + tgl2;
	param += '&tipelaporan=html';
	tujuan = 'keu_laporanJurnal.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					//document.getElementById('containerjurnal').innerHTML = con.responseText;
					alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','80%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getdatatph(baris){
	row        = document.getElementById('jlhbrs').value;
	tgl        = document.getElementById('tgl').value;
	notransaksi=document.getElementById('notransaksi').value;
	
	if (row == 0) {
		karyawanid= document.getElementById('karyawanid').value;
		blok      = document.getElementById('blok').value;
		tph       = document.getElementById('tph').value;
		sesi       = document.getElementById('sesi').value;
	} else {
		karyawanid= document.getElementById('karyawanid' + baris).value;
		blok      = document.getElementById('blok' + baris).value;
		tph       = document.getElementById('tph' + baris).value;
		sesi       = document.getElementById('sesi' + baris).value;
	}
	
	param = 'method=getdatatph';
    param += '&notransaksi=' + notransaksi;
    param += '&tph=' + tph;
    param += '&sesi=' + sesi;
    param += '&karyawanid=' + karyawanid;
    param += '&blok=' + blok;
	param += '&divisi=' + getValue('divisi');
    tujuan = 'kebun_slave_panenxn.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert('Info',con.responseText);
                } else {
					data = con.responseText.split("##");
                    if(parseFloat(trim(data[1]))>0){
						alertify.alert('Info',"TPH sudah pernah diinput."); return;
					}
					if (row == 0) {						
						document.getElementById('hapanen').innerHTML = trim(data[0]);
					}else{
						document.getElementById('hapanen'+baris).innerHTML = trim(data[0]);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function hiderow(awal,akhir,sumber){	
	if(sumber=='nik'){
		rowid= 'groupbyblok';
		no = akhir;
	}else{		
		rowid= 'rowdetail';
		no = awal;
	}
	dis  = document.getElementById(rowid+no).getAttribute("style");
	
	awal = parseFloat(awal);
	akhir= parseFloat(akhir);
	if(sumber=='nik'){
		document.getElementById('rowplusnik'+akhir).style.color='blue';
		document.getElementById('rowplusnnik'+akhir).style.color='blue';
		if(dis!=null && (dis.includes("display:none") || dis.includes("display:none;") || dis.includes("display: none;"))){
			document.getElementById('rowplusnik'+akhir).innerHTML="-";
			document.getElementById('rowplusnnik'+akhir).innerHTML="-";
		}else{
			document.getElementById('rowplusnik'+akhir).innerHTML="+";
			document.getElementById('rowplusnnik'+akhir).innerHTML="+";
		}
	}else{		
		document.getElementById('rowplus'+akhir).style.color='blue';
		document.getElementById('rowplusn'+akhir).style.color='blue';
		if(dis!=null && (dis.includes("display:none") || dis.includes("display:none;") || dis.includes("display: none;"))){
			document.getElementById('rowplus'+akhir).innerHTML="-";
			document.getElementById('rowplusn'+akhir).innerHTML="-";
		}else{
			document.getElementById('rowplus'+akhir).innerHTML="+";
			document.getElementById('rowplusn'+akhir).innerHTML="+";
		}
	}
	for (var i=awal;i<=akhir;i++){
		if(dis!=null && (dis.includes("display:none") || dis.includes("display:none;") || dis.includes("display: none;"))){
			if(document.getElementById(rowid+i)!=undefined){				
				document.getElementById(rowid+i).style.display="";
			}
		}else{
			if(document.getElementById(rowid+i)!=undefined){				
				document.getElementById(rowid+i).style.display="none";
			}
		}
		if(sumber=='nik'){
			if(document.getElementById('rowdetail'+i)!=undefined){				
				document.getElementById('rowdetail'+i).style.display="none";
			}
			if(document.getElementById('rowplus'+i)!=undefined){
				document.getElementById('rowplus'+i).innerHTML="+";				
			}
			if(document.getElementById('rowplusn'+i)!=undefined){
				document.getElementById('rowplusn'+i).innerHTML="+";
			}			
		}
	}
}

function getmark(id){
	dis = document.getElementById(id).style.backgroundColor;
	if(dis!=''){
		document.getElementById(id).style.backgroundColor="";		
	}else{		
		document.getElementById(id).style.backgroundColor="cyan";
	}
}