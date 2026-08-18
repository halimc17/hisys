/**
 * @author repindra.ginting
 */
function searchBarang(title,content,ev) {
	width='500';
	height='400';
	showDialog1(title,content,width,height,ev);
}

function findBarang() {
	txt=trim(document.getElementById('namabrg').value);
	if(txt.length<3) {
        alert('Minimum text is 3 char');
    } else {
		param='txtfind='+txt;
		tujuan='log_slave_get_barang.php';
		post_response_text(tujuan, param, respog);
	}
	
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
				}
            } else {
				busy_off();
				error_catch(con.status);
			}
        }	
    }  	
}

function setKodeBarang(kelompok,kode,nama,satuan) {
	document.getElementById('namadisabled').value=nama;
	document.getElementById('sat').innerHTML=satuan;
	document.getElementById('kodebarang').innerHTML=kode;
	checkChkNol();
	closeDialog();
	changeJenis();
}

// joki

function deletedata(id) {
	pg = document.getElementById("pages");
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
  
	param = "method=deletedata" + "&id=" + id;
	tujuan = "log_slave_stockOpnameList.php";
	if (confirm("Anda yakin ???")) {
	  post_response_text(tujuan, param, respog);
	}
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alert(con.responseText);
		  } else {
			loadData(0);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }

function postingdata(id) {
	param = "method=postingdata" + "&id=" + id;
	tujuan = "log_slave_stockOpnameList.php";
	// if (confirm(" Anda yakin ?")) {
	  post_response_text(tujuan, param, respog);
	// }
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			alertify.alert(trim(con.responseText));
			loadData(0);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }

function loadData(pg) {
	// carinopppr = document.getElementById('txtsearch').value;
	// carinamabarang = document.getElementById('txtsearch2').value;
	// caritanggalpppr = document.getElementById('tgl_cari').value;
	// param = 'method=loaddata' + '&page=' + pg + '&carinopppr=' + carinopppr + '&caritanggalpppr=' + caritanggalpppr+'&carinamabarang='+carinamabarang;
	param = 'method=loaddata' + '&page=' + pg;
	tujuan = 'log_slave_stockOpnameList.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('liststocOpname').innerHTML = data[0];
					document.getElementById('containft').innerHTML = data[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function savelistAdjustment() {
	kodebarang=document.getElementById('kodebarang').innerHTML;
    kodegudang=getValue('kodegudang');
    jumlah=getValue('jumlah');
    harga=getValue('harga');
    chkNol=getValue('chkNol');
    tgladj=getValue('tgladj');
	var file = document.getElementById("upload").files[0];
	upload = getValue("upload");
	if (getValue("upload") == "") {
		alert("warning : Upload file has been empty.");
		return false;
	  }
    
    jenisAdjust=getValue('jenisAdjust');
    
    if(harga=='') {
        harga=0;
    }
    if(jumlah=='') {
        jumlah=0;
    }
    
    if(!kodegudang || kodebarang=='') {
        alert('Data incomplete');
    }
    //jika masuk
    // else if (jenisAdjust=='in' && harga==0)
    // {
    //     alert('Harga harus di-isi');
    // }
    else if(jenisAdjust=='out' && harga==0 && chkNol=='0')
    {
        alert('Harga harus di-isi');
    }
    else {
		var file = document.getElementById("upload").files[0];
		var formdata = new FormData();
		formdata.append("file", file);
		formdata.append("upload", getValue("upload"));
		formdata.append("kodegudang", kodegudang);
		formdata.append("kodebarang", kodebarang);
		formdata.append("jenis", jenisAdjust);
		formdata.append("jumlah", jumlah);
		formdata.append("harga", harga);
		formdata.append("notransreferensi", getValue("notransreferensi"));
		formdata.append("keterangan", getValue("keterangan"));
		formdata.append("tgladj", getValue("tgladj"));
		if (getValue("upload") == "") {
		  alert("warning : Upload file has been empty.");
		  return false;
		}
		document.getElementsByClassName("mybutton").disabled = true;
		busy_on();
		var con = createXMLHttpRequest();
		con.open("POST", "log_slave_stockOpnameList.php?method=savelistAdjustment", true);
		con.onreadystatechange = eval(respon);
		con.send(formdata);
		  function respon() {
			  if (con.readyState == 4) {
				  if (con.status == 200) {
					  busy_off();
					  if (!isSaveResponse(con.responseText)) {
						  alertify.alert(con.responseText);
					  } else {
						alert('Done');
						document.getElementById('namadisabled').value='';
						document.getElementById('sat').innerHTML='';
						document.getElementById('kodebarang').innerHTML='';
						document.getElementById('jumlah').value=0;
						document.getElementById('harga').value=0;
						setValue('notransreferensi','');
						setValue('keterangan','');
						setValue('tgladj','');
						setValue('upload','');
						loadData(0);
					  }
				  } else {
					  busy_off();
					  error_catch(con.status);
				  }
			  }
		  }
	  }
	}

//         param='method=savelistAdjustment' + '&kodebarang='+kodebarang+'&kodegudang='+kodegudang+'&harga='+harga+
// 			'&jumlah='+jumlah+'&jenis='+getValue('jenisAdjust')+
// 			'&notransreferensi='+getValue('notransreferensi')+
// 			'&keterangan='+getValue('keterangan')+'&tgladj='+tgladj+'&upload='+upload+'&file='+file;
//         tujuan='log_slave_stockOpnameList.php';
//         if(confirm('Data Akan Disimpan?')){
// 			post_response_text(tujuan, param, respog);
// 		}
//     }
    
//     function respog() {
//         if(con.readyState==4) {
//             if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					alert('Done');
// 					document.getElementById('namadisabled').value='';
// 					document.getElementById('sat').innerHTML='';
// 					document.getElementById('kodebarang').innerHTML='';
// 					document.getElementById('jumlah').value=0;
// 					document.getElementById('harga').value=0;
// 					setValue('notransreferensi','');
// 					setValue('keterangan','');
// 					loadData(0);
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
//         }	
//     }  
// }
function saveAdjustmentPosting(id,kodebarang,kodegudang,jumlah,harga,tgladj,jenisAdjust,notransreferensi,keterangan) {
    // kodebarang=document.getElementById('kodebarang').innerHTML;
    // kodegudang=getValue('kodegudang');
    // jumlah=getValue('jumlah');
    // harga=getValue('harga');
    // chkNol=getValue('chkNol')
    // tgladj=getValue('tgladj')
    
    // jenisAdjust=getValue('jenisAdjust');
    
    if(harga=='') {
        harga=0;
    }
    if(jumlah=='') {
        jumlah=0;
    }
    
    if(!kodegudang || kodebarang=='') {
        alert('Data incomplete');
    }
    //jika masuk
    // else if (jenisAdjust=='in' && harga==0)
    // {
    //     alert('Harga harus di-isi');
    // }
    // else if(jenisAdjust=='out' && harga==0 && chkNol=='0')
    // {
    //     alert('Harga harus di-isi');
    // }
    else {
        param='kodebarang='+kodebarang+'&kodegudang='+kodegudang+'&harga='+harga+
			'&jumlah='+jumlah+'&jenis='+jenisAdjust+
			'&notransreferensi='+notransreferensi+
			'&keterangan='+keterangan+'&tgladj='+tgladj;
        tujuan='log_slave_stockOpname.php';
        if(confirm('Update material balance..?')){
			post_response_text(tujuan, param, respog);
		}
    }
    
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done');
					document.getElementById('namadisabled').value='';
					document.getElementById('sat').innerHTML='';
					document.getElementById('kodebarang').innerHTML='';
					document.getElementById('jumlah').value=0;
					document.getElementById('harga').value=0;
					setValue('notransreferensi','');
					setValue('keterangan','');
					postingdata(id);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
        }	
    }  	
}

function form_ajukan(notransaksi,unit) {
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&unit=' + unit ;
	tujuan = 'log_slave_stockOpnameList.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup2().destroy();
					alertify.popup2("Approval Form",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('30%','30%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan_apv_aso(notransaksi) {
		// total_persetujuan = document.getElementById('total_persetujuan').value;
		  // this is
		  persetujuan = '';
		  // for (i = 1; i <= total_persetujuan; i++) {
		//   for (i = 1; i <= 1; i++) {
		// 	  if (document.getElementById('persetujuan' + i).value == '') {
		// 		  alert("Persetujuan belum dipilih. Silahkan hubungi Administrator");
		// 		  return false;
		// 	  } else {
				//   persetujuan +='&persetujuan'+i+'='+document.getElementById('persetujuan'+i).value+'&level[]='+i;
				// 	  }
				//   }
				
		persetujuan +='&persetujuan1='+document.getElementById('persetujuan1').value+'&level=1';
	  
		param = "notransaksi=" + notransaksi + "&method=ajukan_apv_aso";
		param += persetujuan;
		tujuan = "log_slave_stockOpnameList.php";
	  
		if (confirm("Ajukan Persetujuan..?"))
		  post_response_text(tujuan, param, respog);
	  
		function respog() {
		  if (con.readyState == 4) {
			if (con.status == 200) {
			  busy_off();
			  if (!isSaveResponse(con.responseText)) {
				alert(con.responseText);
			  } else {
				alertify.popup2().destroy();
						  alertify.set('notifier','position', 'top-right');
						  alertify.set('notifier','delay', 2);
						  alertify.success('Sukses');
						  loadData(0);
			  }
			} else {
			  busy_off();
			  error_catch(con.status);
			}
		  }
		}
	  }



// end joki
// old
function saveAdjustment() {
    kodebarang=document.getElementById('kodebarang').innerHTML;
    kodegudang=getValue('kodegudang');
    jumlah=getValue('jumlah');
    harga=getValue('harga');
    chkNol=getValue('chkNol')
    tgladj=getValue('tgladj')
    
    jenisAdjust=getValue('jenisAdjust');
    
    if(harga=='') {
        harga=0;
    }
    if(jumlah=='') {
        jumlah=0;
    }
    
    if(!kodegudang || kodebarang=='') {
        alert('Data incomplete');
    }
    //jika masuk
    // else if (jenisAdjust=='in' && harga==0)
    // {
    //     alert('Harga harus di-isi');
    // }
    else if(jenisAdjust=='out' && harga==0 && chkNol=='0')
    {
        alert('Harga harus di-isi');
    }
    else {
        param='kodebarang='+kodebarang+'&kodegudang='+kodegudang+'&harga='+harga+
			'&jumlah='+jumlah+'&jenis='+getValue('jenisAdjust')+
			'&notransreferensi='+getValue('notransreferensi')+
			'&keterangan='+getValue('keterangan')+'&tgladj='+tgladj;
        tujuan='log_slave_stockOpname.php';
        if(confirm('Update material balance..?')){
			post_response_text(tujuan, param, respog);
		}
    }
    
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done');
					document.getElementById('namadisabled').value='';
					document.getElementById('sat').innerHTML='';
					document.getElementById('kodebarang').innerHTML='';
					document.getElementById('jumlah').value=0;
					document.getElementById('harga').value=0;
					setValue('notransreferensi','');
					setValue('keterangan','');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
        }	
    }  	
}

/**
 * changeJenis
 * onChange field Jenis, jika transaksi keluar maka freeze rupiah
 */
function changeJenis() {
	var jenis = getValue('jenisAdjust'),
		harga = getById('harga');
	if(jenis=='in') {
		harga.disabled = false;
		document.getElementById('divChkNol').style.display = 'none';
	} else {
		harga.disabled = true;
		document.getElementById('divChkNol').style.display = 'block';
		harga.value = 0;
	}
	jenisAdjust=document.getElementById('jenisAdjust').value;
	if(jenisAdjust=='out'){
		// alert('Adjust untuk barang keluar tidak diperbolehkan merubah harga satuan');return;
		document.getElementById('chkNol').disabled=true;
	}
	checkChkNol();
}
function getHargaTerakhir(){
	kodebarang=document.getElementById('kodebarang').innerHTML;
	kodegudang=getValue('kodegudang');
	jenisAdjust=getValue('jenisAdjust');
	if(jenisAdjust=='out'){
		if(kodebarang != ''){
			param='kodebarang='+kodebarang+'&kodegudang='+kodegudang;
			tujuan='log_slave_stockOpnameHarga.php';
			post_response_text(tujuan, param, respog);
		}
	}else{
		document.getElementById('harga').value=0;
	}
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('harga').value=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
	
}

function checkChkNol(){
	chkNol=document.getElementById('chkNol');
	if(chkNol.checked==true){
        document.getElementById('harga').value=0;
	}else{
		getHargaTerakhir();
	}
}
	   

function pdf(id) {
	tujuan = "log_slave_stockOpnameList.php";
	param = "method=pdf&id=" + id;
	tujuan = tujuan + "?" + param;
	alertify
	.popuppdf(
	"PDF",
	"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_stockOpnameList.php?" +param+ "'></iframe>"
	)
	.set({ resizable: true, overflow: false })
	.resizeTo("80%", "70%");
}