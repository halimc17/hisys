// Umar
function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
	cancelht();
	// document.getElementById('detailhead').style.display='none';
}

function cancelht(){
	document.getElementById('detail').style.display = 'none';
	document.getElementById('notransaksi').value='';
	document.getElementById('unit').value='';
	document.getElementById('notransaksireferensi').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('nopo').value='';
	document.getElementById('tipe').value='';
	document.getElementById('supplierid').value='';
	document.getElementById('namasupplier').value='';
	document.getElementById('termin').value='';
}

function getdata(title){
	ev='event';
	unit= document.getElementById('unit').value;
	if(unit==''){
		alert('Unit Masih Kosong');return;
	}
	param = 'method=formdata';
	param += '&unit=' + unit;
	tujuan = 'log_retursuppliernoninventory_slave.php';
	post_response_text(tujuan, param, respon);
	function respon(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','70%'); 
					// alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function finddata(){
	unit= document.getElementById('unit').value;
	notransaksi= document.getElementById('notransaksidata').value;
	nopo= document.getElementById('nopodata').value;
	param = 'method=finddata';
	param += '&unit=' + unit+'&notransaksi=' + notransaksi+'&nopo=' + nopo;
	tujuan = 'log_retursuppliernoninventory_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					leftFixedTable();
					document.getElementById('formdatadetail').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function movedata(notransaksireferensi,nopo,tipe,supplierid,namasupplier,termin){
	document.getElementById('notransaksireferensi').value=notransaksireferensi;
	document.getElementById('nopo').value=nopo;
	document.getElementById('tipe').value=tipe;
	document.getElementById('supplierid').value=supplierid;
	document.getElementById('namasupplier').value=namasupplier;
	document.getElementById('termin').value=termin;
	alertify.popup().destroy();
}

function saveht(parameter) {
	method='saveht';
	tujuan='log_retursuppliernoninventory_slave.php';
    var passP = parameter.split('###');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
		param += "&"+passP[i]+"="+getValue(passP[i]);
    }
	
	notransaksireferensi=document.getElementById('notransaksireferensi').value;
	
	param += '&method=' + method;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alertify.alert('Informasi',con.responseText);
					alertify.alert('Informasi',con.responseText);
                } else {
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('detail').style.display='block';
					document.getElementById('notransaksi').disabled=true;
					loaddatadt();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function loaddatadt(no) {
	
	notransaksi=trim(document.getElementById('notransaksi').value);
	notransaksireferensi=trim(document.getElementById('notransaksireferensi').value);
	param = 'method=loaddatadt';
	param += '&notransaksi=' + notransaksi;
	param += '&notransaksireferensi=' + notransaksireferensi;
	tujuan = 'log_retursuppliernoninventory_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('listdatadt').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedt(no) {
	
	notransaksi=trim(document.getElementById('notransaksi').value);
	notransaksireferensi=trim(document.getElementById('notransaksireferensi').value);
	
	nopp=trim(document.getElementById('noppdt'+no).innerHTML);
	kodebarang=trim(document.getElementById('kodebarangdt'+no).innerHTML);
	satuan=trim(document.getElementById('satuandt'+no).innerHTML);
	jumlah=trim(document.getElementById('jumlahdt'+no).value);
	hargasatuan=trim(document.getElementById('hargasatuandt'+no).innerHTML);
	subunit=trim(document.getElementById('subunitdt'+no).innerHTML);
	subunitdt=trim(document.getElementById('subunitdtdt'+no).innerHTML);
	kodekegiatan=trim(document.getElementById('kodekegiatandt'+no).innerHTML);
	nopo=trim(document.getElementById('nopodt'+no).innerHTML);
	catatan=trim(document.getElementById('catatandt'+no).innerHTML);
	
	jumlahtransaksidt=trim(document.getElementById('jumlahtransaksidt'+no).innerHTML);
	
	param = 'method=savedt';
	param += '&notransaksi=' + notransaksi+'&notransaksireferensi=' + notransaksireferensi+'&jumlahtransaksidt=' + jumlahtransaksidt;
	param += '&nopp=' + nopp+'&kodebarang=' + kodebarang+'&satuan=' + satuan+'&jumlah=' + jumlah+'&hargasatuan=' + hargasatuan;
	param += '&subunit=' + subunit+'&subunitdt=' + subunitdt+'&kodekegiatan=' + kodekegiatan+'&nopo=' + nopo+'&catatan=' + catatan;
	tujuan = 'log_retursuppliernoninventory_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					// ar = con.responseText.split("###");
					// document.getElementById('listdatadt').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	// document.getElementById('rekeningsch').disabled=true;
	// document.getElementById('kodesuppliersch').value='';
	loaddata(0);
}

function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(num) {
	
	if (document.getElementById('listdata') !== null) {
		document.getElementById('listdata').style.display = 'block';
	}
	if (document.getElementById('header') !== null) {
		document.getElementById('header').style.display = 'none';
	}
	if (document.getElementById('detail') !== null) {
		document.getElementById('detail').style.display = 'none';
	}
	
	 // = document.getElementById('').value;
	param = 'method=loaddata&page=' + num;
	// param += '&notransaksi=' + notransaksi+'&tanggalmulai=' + tanggalmulai+'&tanggalselesai=' + tanggalselesai;
	// param += '&kodeorg=' + kodeorg+'&tipetransaksi=' + tipetransaksi;
	// param += '&dibuat=' + dibuat+'&keterangan=' + keterangan;
	// param += '&noakun=' + noakun+'&rekening=' + rekening;
	// param += '&jumlah=' + jumlah+'&noinvoice=' + noinvoice+'&kodesupplier=' + kodesupplier;
	// param += '&appstatus=' + appstatus+'&bayarke=' + bayarke+'&pembayaran=' + pembayaran;
	// alert(param);
	tujuan = 'log_retursuppliernoninventory_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					// leftFixedTable();
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

function deleteht(notransaksi){
	param 	= 'method=deleteht&notransaksi='+notransaksi;
    tujuan 	= 'log_retursuppliernoninventory_slave.php';
    
	if(confirm('Anda yakin hapus no transaksi '+notransaksi+'?')){
		post_response_text(tujuan, param, respog);		
	}
    
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Sukses");
					getpage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function editht(notransaksi, unit, referensi, tanggal, keterangan, tipe, pt, nopo, nosj, penerima, supplierid, termin, namasupplier){
	if (document.getElementById('listdata') !== null) {
		document.getElementById('listdata').style.display = 'none';
	}
	if (document.getElementById('header') !== null) {
		document.getElementById('header').style.display = '';
	}
	if (document.getElementById('detail') !== null) {
		document.getElementById('detail').style.display = '';
	}

	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('unit').value = unit;
	document.getElementById('notransaksireferensi').value = referensi;
	document.getElementById('tanggal').value = tanggal;
	document.getElementById('keterangan').value = keterangan;

	movedata(referensi, nopo, tipe, supplierid, namasupplier, termin);
	loadfilesx(notransaksi);
}

function loadfilesx(nodok,kodebarang='') {
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

					saveht("###notransaksi###nopo###unit###tipe###notransaksireferensi###supplierid###tanggal###namasupplier###keterangan###termin");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile(){
	var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('notransaksi').value;
    // var kodebarang = document.getElementById('kodebarangupload').innerHTML;
    var jenisupload = document.getElementById('kriteriaefil').value;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    // formdata.append("kodebarang", kodebarang);
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
                    loadfilesx(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefilex(notransaksi, namafile) {
    param = "notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    post_response_text('log_slave_penerimaanUpload.php?proses=deletefilex', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfilesx(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function previewpdfbars(ev,notransaksi){	
	param  = 'method=previewpdfbars&notransaksi='+notransaksi;
    tujuan = 'log_retursuppliernoninventory_slave.php';
    
	title  = 'Report PDF';
	tujuan = tujuan+"?"+param;  
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function previewpdfrs(ev,notransaksi){	
	param  = 'method=previewpdfrs&notransaksi='+notransaksi;
    tujuan = 'log_retursuppliernoninventory_slave.php';
    
	title  = 'Report PDF';
	tujuan = tujuan+"?"+param;  
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}



function posting(notransaksi){
	param = "notransaksi=" + notransaksi;
    post_response_text('log_retursuppliernoninventory_slave.php?method=posting', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
