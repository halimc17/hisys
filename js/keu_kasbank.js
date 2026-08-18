
function pilihautokb(){
    var centang = document.getElementById('autokb');
    if(centang.checked!=true){

        document.getElementById('norekpenerima').disabled=true;        
        document.getElementById('namapenerima').disabled=true;   
        document.getElementById('noakun2b').disabled=true;   
		
        document.getElementById('norekpenerima').value='';  
        document.getElementById('namapenerima').value='';
        document.getElementById('noakun2b').value='';
		
    } else {

        document.getElementById('norekpenerima').disabled=false;        
        document.getElementById('namapenerima').disabled=false;        
        document.getElementById('noakun2b').disabled=false;        
    }
	getbank2();
}






function getbank2() {
	
	noakun2a=document.getElementById('noakun2b').value;
	kodeorg=document.getElementById('namapenerima').value;
	notransaksi=document.getElementById('notransaksi').value;
	
	param = 'noakun2a='+noakun2a+'&proses=getbank2'+'&kodeorg='+kodeorg+'&notransaksi='+notransaksi;
	post_response_text('keu_slave_kasbank_detail.php', param, respon);
	
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                } else {
					// === Success Response
                    document.getElementById('norekpenerima').innerHTML = con.responseText;
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function bayar(numRow){
	content= "<div id=formbayar style=\"height:100%;width:100%;\"></div>";
	title='Bayar';
	height='';
	width='';
	showDialog5(title,content,width,height,'event');
	
	var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
	kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
	noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
	tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
	
	var param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
        "&tipetransaksi="+tipetransaksi+"&numRow="+numRow;
		
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   document.getElementById('formbayar').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	post_response_text('keu_slave_kasbank.php?proses=showformbayar', param, respon);		
}

function kasbank(notrans, kodeorg, noakun, tipetransaksi, novoucher,numRow,efill) {
	var tglpost=trim(document.getElementById('tglbayar').value);
	var file = document.getElementById("upload").files[0];
	// var noakun2a = document.getElementById("noakun2a").files[0];
	// var rekening = document.getElementById("rekening").files[0];
	
	var noakun2a=trim(document.getElementById('noakun2a').value);
	var rekening=trim(document.getElementById('rekening').value);
	
	var cgttu=trim(document.getElementById('cgttu').value);
	var nocek=trim(document.getElementById('nocek').value);
	
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notransaksi", notrans);
	formdata.append("kodeorg", kodeorg);
	formdata.append("noakun", noakun);
	formdata.append("tipetransaksi", tipetransaksi);
	formdata.append("novoucher", novoucher);
	formdata.append("tglpost", tglpost);	
	formdata.append("noakun2a", noakun2a);	
	formdata.append("rekening", rekening);	
	
	formdata.append("cgttu", cgttu);	
	formdata.append("nocek", nocek);	
	
	formdata.append("efill", efill);	
	
	if(tglpost==''){
		alert("Tanggal wajib diisi !!!"); return;
	}
	
	if(efill=='1'){
		if (getValue('upload') == "") {
			alert("warning : Upload file harus dilengkapi.");
			return false;
		}
	}
	
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "keu_slave_kasbank_posting.php?x=x", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog5();
					x=document.getElementById('tr_'+numRow);
					x.cells[16].innerHTML='';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}

function getnoakun(){
	noaruskas=trim(document.getElementById('noaruskas').value);
	kodeorg=trim(document.getElementById('kodeorg').value);
	noakun=trim(document.getElementById('noakun2a').value);
	param='noaruskas='+noaruskas+'&kodeorg='+kodeorg+'&noakun='+noakun;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getnoakun', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
                    document.getElementById('noakun').innerHTML = isdt[0];
                    //document.getElementById('keterangan2temp').innerHTML = isdt[1];
					updFieldAktif();
				
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function searchclmed(title,content,ev){
    width='';
	height='';
	showDialog4(title,content,width,height,ev);
    getformclmed();
}

function save_filex(){
	var file = document.getElementById("filex").files[0];
    var notransaksi = document.getElementById('notransaksi').value;
    var jenisupload = document.getElementById('jenisupload').value;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("jenisupload", jenisupload);
    formdata.append("file", file);
    formdata.append("fileupload", document.getElementById("filex").value);
    //alert(document.getElementById("filex").value);
    if (document.getElementById("filex").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "keu_slave_kasbank.php?proses=submitfilex", true);
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
                    document.getElementById("filex").value = "";
                    load_filex();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function load_filex(){
	//alert(getValue('notransaksi'));
	param='';
	param='notransaksi='+getValue('notransaksi');
	tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getfilex', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tbody_filex').innerHTML=con.responseText;
					pilihautokb();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
} 

function delete_filex(notransaksi, namafile) {
    param = "notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    post_response_text('keu_slave_kasbank.php?proses=deletefilex', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    load_filex();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getformclmed(){
	param='';
	tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getformclmed', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formPencariandata').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
} 


function findclmed(){
	unit=trim(document.getElementById('kodeorg').value);
	notran=trim(document.getElementById('notran').value);
	param='unit='+unit+'&notran='+notran;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getdataclmed', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getclmed(notran,unit,karid,jumlah) {
	var param='notran='+notran+'&unit='+unit+'&karid='+karid+'&jumlah='+jumlah;
    param += '&notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
    param += '&bulan='+getValue('bulanclmed')+'&tahun='+getValue('tahunclmed');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&tanggal='+getValue('tanggal')+'&noakunhutang='+getValue('noakunhutang');
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=addclmed', param, respog);
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog4();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


/*
kasbank
*/

function searchkasbank(title,content,ev){
    width='';
	height='';
	showDialog4(title,content,width,height,ev);
    getformkasbank();
}

function getformkasbank(){
	param='';
	tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getformkasbank', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formPencariandata').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
} 



function findkasbank(){
	unit=trim(document.getElementById('unit').value);
	notran=trim(document.getElementById('notran').value);
	if(unit==''){
		alert('Unit kosong');return;
	}
	param='unit='+unit+'&notran='+notran;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getdatakasbank', param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getkasbank(notran,unit,noakundt,jumlah) {
	var param='notran='+notran+'&unit='+unit+'&noakundt='+noakundt+'&jumlah='+jumlah;
    param += '&notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&tanggal='+getValue('tanggal')+'&noakunhutang='+getValue('noakunhutang');
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=addkasbank', param, respog);
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog4();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/*deposito*/

function searchdeposito(title,content,ev){
    width='';
	height='';
	showDialog4(title,content,width,height,ev);
    getformdeposito();
}

function getformdeposito(){
	param='';
	tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getformdeposito', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formPencariandata').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function finddeposito(){
	notran=trim(document.getElementById('notran').value);
	tanggalinput=trim(document.getElementById('tanggalinput').value);
	rekening=trim(document.getElementById('rekening').value);
	bulan=trim(document.getElementById('bulandeposito').value);
	tahun=trim(document.getElementById('tahundeposito').value);
	param='notran='+notran+'&tanggalinput='+tanggalinput+'&rekening='+rekening+'&bulan='+bulan+'&tahun='+tahun;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getdatadeposito', param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdeposito(notran,unit,noakundt,jumlah,noaruskasdt,keterangandt) {
	var param='notran='+notran+'&unit='+unit+'&noakundt='+noakundt+'&jumlah='+jumlah+'&noaruskas='+noaruskasdt+'&keterangan2temp='+keterangandt;
    param += '&notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit')+'&rekening='+getValue('rekening');
    param += '&bulandeposito='+getValue('bulandeposito')+'&tahundeposito='+getValue('tahundeposito');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&tanggal='+getValue('tanggal')+'&noakunhutang='+getValue('noakunhutang');
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=adddeposito', param, respog);

	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog4();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/* end of deposito */

/*penarikan semua data*/

function searchdata(title,content,ev){
    width='';
	height='';
	showDialog4(title,content,width,height,ev);
    getformdata();
}

function getformdata(){
	param='';
	tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getformdata', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formPencariandata').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function finddata(){
	notran=trim(document.getElementById('notran').value);
	jenisdata=trim(document.getElementById('jenisdata').value);
	kodeorg=trim(document.getElementById('kodeorg').value);
	tipetransaksi=trim(document.getElementById('tipetransaksi').value);
	rekening=trim(document.getElementById('rekening').value);
	cgttu=trim(document.getElementById('cgttu').value);
	bulan=trim(document.getElementById('bulandata').value);
	tahun=trim(document.getElementById('tahundata').value);
	param='notran='+notran+'&jenisdata='+jenisdata+'&kodeorg='+kodeorg+'&tipetransaksi='+tipetransaksi+'&rekening='+rekening+'&cgttu='+cgttu;
	param+='&bulan='+bulan+'&tahun='+tahun;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getdata', param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
					showDetail();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdatadt(notran,unit,noakundt,jumlah,noaruskasdt,keterangandt,nourut,penerima='',keterangan) {

	var param='notran='+notran+'&unit='+unit+'&noakundt='+noakundt+'&jumlah='+jumlah+'&keterangan2='+keterangandt;
    param += '&notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg')+'&jenisdata='+getValue('jenisdata');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
    param += '&bulan='+getValue('bulandata')+'&tahun='+getValue('tahundata');
    param += '&pemilikhutang='+getValue('pemilikhutang')+'&tanggal='+getValue('tanggalinput')+'&noakunhutang='+getValue('noakunhutang');
    param += '&penerima='+penerima+'&lainnya='+keterangan;
	if(nourut!=''){
		param += '&nourut='+nourut;
    }
	
	if (jenisdata=='umpjdinas' || jenisdata=='realpjdinas' || jenisdata=='claimpjdinas' || jenisdata=='batalpjd') {
		param += '&noaruskas='+getValue('aruskaspjd');
	}else{
		param += '&noaruskas='+noaruskasdt;
	}
	
    

	// if (jenisdata=='bansos' && noakundt=='') {
		// alert('No akun untuk kategori bansos belum ada, silahkan tambah terlebih dahulu melalui menu : Legal - Setup - Kategori bansos.');
		// return;
	// }
	
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=adddatadt', param, respog);
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					finddata();
                    
                    // closeDialog4();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/* end of data */

/*
dokumen
*/


function searchdok(title,content,ev){
	noakun=trim(document.getElementById('noakun').value);
	if(noakun==''){
		alert('Isi nomor akun dahulu');return;
	}
    width='';
	height='';
	showDialog4(title,content,width,height,ev);
    getformdok();
}

function getformdok(){
	noakun=trim(document.getElementById('noakun').value);
	param='noakun='+noakun;
	tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getformdok', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formPencariandata').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
} 


function finddok(){
	unit=trim(document.getElementById('kodeorg').value);
	noakun=trim(document.getElementById('noakun').value);
	notran=trim(document.getElementById('notran').value);
	if (document.getElementById('r1').checked) {
		tipe = document.getElementById('r1').value;
	}
	if (document.getElementById('r2').checked) {
		tipe = document.getElementById('r2').value;
	}
	param='unit='+unit+'&noakun='+noakun+'&notran='+notran+'&tipe='+tipe;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getdatadok', param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function getdok(notran,karid,rupiah) {
	setValue('nodok',notran);
	setValue('nik',karid);
	getById('ftPrestasi_jumlah').firstChild.value = rupiah;
	closeDialog4();
}


/*

/**
 * searchDok
 * Search Dokumen PO / Kontrak
 
function searchDok(ev) {
	var notransaksi = getValue('notransaksi'),
		noakun = getValue('noakun'),
		tipetransaksi = getValue('tipetransaksi'),
		nik = getValue('nik');
	if(noakun=='') {
		alert(notifnoakun);
	} else {
		param = "notransaksi="+notransaksi+"&noakun="+noakun+"&nik="+nik;
		tujuan='keu_slave_kasbank.php';
		post_response_text(tujuan+'?'+'proses=getUangMuka', param, respog);
	}
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    width='510';
					height='300';
					showDialog1(notifdaftaruangmuka,con.responseText,width,height,ev);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
/**
 * setNodok
 * Set NoDokumen, NIK dan Jumlah

function setNodok(notransaksi,nik,jumlah) {
	setValue('nodok',notransaksi);
	setValue('nik',nik);
	getById('ftPrestasi_jumlah').firstChild.value = jumlah;
	closeDialog();
}


 */



function getkeg() {
    var kodeasset = document.getElementById('kodeasset').value;
    var param = "kodeasset="+kodeasset;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					//document.getElementById('noakun').innerHTML=con.responseText;
					//alert(con.responseText);
					datAkun=document.getElementById('noakun');
					for(a=0;a<datAkun.length;a++){
			            if(datAkun.options[a].value==con.responseText)
			                {
			                    datAkun.options[a].selected=true;
			                }
			        }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
	post_response_text('keu_slave_kasbank_detail.php?proses=getkeg', param, respon);
    
} 
 



var showPerPage = 10;

function getValue(id) {
    var tmp = document.getElementById(id);
    
    if(tmp) {
        if(tmp.options) {
            return tmp.options[tmp.selectedIndex].value;
        } else if(tmp.nodeType=='checkbox') {
            if(tmp.checked==true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return tmp.value;
        }
    } else {
        return false;
    }
}












function getsup(){
	tipeinv=document.getElementById('tipeinv').options[document.getElementById('tipeinv').selectedIndex].value;
	param='tipeinv='+tipeinv;
    tujuan='keu_slave_kasbank_detail.php';

	post_response_text(tujuan+'?'+'proses=getsup', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('supplierIdcr').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}











function getKurs2(){
	tanggal=document.getElementById('tanggal').value;	
	matauang = getById('ftPrestasi_matauang').firstChild.value;
	
	if (matauang!='IDR') {
		if(tanggal=='' || matauang=='')
		{
			alert(notifdatecurrency);
			document.getElementById('kurs').value=''; 
			return;
		}
		param='proses=getKurs'+'&matauang='+matauang+'&tanggal='+tanggal;
		tujuan='keu_slave_kasbank_kurs.php';
		post_response_text(tujuan, param, respog);
	} else {
		getById('ftPrestasi_kurs').firstChild.value='1';
		getById('ftPrestasi_kurs').firstChild.setAttribute('disabled','disabled');
	}
	
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
						
				} else {
					if(con.responseText=='') {
						getById('ftPrestasi_kurs').firstChild.value='0';
						alert(notifinputcurrency);
						return;
					} else {
						getById('ftPrestasi_kurs').firstChild.value=con.responseText;
					}
				}
			}
			else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}

function getKurs()
{
    matauang=document.getElementById('matauang').value;
    tanggal=document.getElementById('tanggalinput').value;
	
	// document.getElementById('addHead').setAttribute('disabled','disabled');
	// if (document.getElementById('editHead')) {
	// 	document.getElementById('editHead').setAttribute('disabled','disabled');
	// }
	
	if (matauang!='IDR') {
		if(tanggal=='' || matauang=='')
		{
			alert(notifdatecurrency);
			document.getElementById('kurs').value=''; 
			return;
		}
		param='proses=getKurs'+'&matauang='+matauang+'&tanggal='+tanggal;
		
		//alert(param);
		tujuan='keu_slave_kasbank_kurs.php';
		
		post_response_text(tujuan, param, respog);
	} else {
		document.getElementById('kurs').value='1';
		document.getElementById('kurs').setAttribute('disabled','disabled');
		// document.getElementById('addHead').removeAttribute('disabled');
		// if (document.getElementById('editHead')) {
		// 	document.getElementById('editHead').removeAttribute('disabled');
		// }
	}
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
						
				} else {
					//document.getElementById('kurs').removeAttribute('disabled');
					if(con.responseText=='') {
						document.getElementById('kurs').value='';
						// document.getElementById('addHead').setAttribute('disabled','disabled');
						// if (document.getElementById('editHead')) {
						// 	document.getElementById('editHead').setAttribute('disabled','disabled');
						// }
						alert(notifinputcurrency);
						return;
					} else {
						document.getElementById('kurs').value=con.responseText;
						// document.getElementById('addHead').removeAttribute('disabled');
						// if (document.getElementById('editHead')) {
						// 	document.getElementById('editHead').removeAttribute('disabled');
						// }
					}
				}
			}
			else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}

//




/* Search
 * Filtering Data
 */
function searchTrans() {
    // var notrans = document.getElementById('sNoTrans');
    // var rupiah = document.getElementById('sRupiah');
    // var tanggal = getValue('sTanggal');
    
    // var noakun = getValue('sAkun');
    // var posting = getValue('sPosting');
    // var supplier = getValue('sSupplier');
   // var tanggal2 = getValue('sTanggal2');
    // var tipetransaksi=getValue('sTipe');
    // var bayarkepada=getValue('sBayarke');
    
    
    // if(tanggal!='') {
        // var tmpTanggal = tanggal.split('-');
        // var tanggalR = tmpTanggal[2]+"-"+tmpTanggal[1]+"-"+tmpTanggal[0];
    // } else {
        // var tanggalR = '';
    // }
    
     // if(tanggal2!='') {
        // var tmpTanggal2 = tanggal2.split('-');
        // var tanggalR2 = tmpTanggal2[2]+"-"+tmpTanggal2[1]+"-"+tmpTanggal2[0];
    // } else {
        // var tanggalR2 = '';
    // }
    
    // var where = '[["notransaksi","'+notrans.value+'"],["tanggal","'+tanggalR+'"],["tanggal2","'+tanggalR2+'"],["supplier","'+supplier+'"],["jumlah","'+remove_comma_var(rupiah.value)+'"],["noakun","'+noakun+'"],["posting","'+posting+'"],["tipetransaksi","'+tipetransaksi+'"],["bayarkepada","'+bayarkepada+'"]]';
  
    // goToPages(1,showPerPage,where);
    goToPages(1,showPerPage);
}


/* Paging
 * Paging Data
 */
function defaultList() {
	document.getElementById('sNoTrans').value='';
	document.getElementById('sAkun').value='';
	document.getElementById('sRupiah').value='';
	document.getElementById('sTanggal').value='';
	document.getElementById('sTanggal2').value='';
	document.getElementById('sTipe').value='';
	document.getElementById('sPosting').value='';
	document.getElementById('sSupplier').value='';
	document.getElementById('sBayarke').value='';
	document.getElementById('sKeterangan').value='';
	document.getElementById('sKeterangan1').value='';
    goToPages(1,showPerPage);
}

function goToPages(page,shows,where) {
	//ini datanya
	/*
	var notrans = document.getElementById('sNoTrans');
    var rupiah = document.getElementById('sRupiah');
    var tanggal = getValue('sTanggal');

    var noakun = getValue('sAkun');
    var posting = getValue('sPosting');
    var tipetransaksi=getValue('sTipe');
    var supplier=getValue('sSupplier');
	var tanggal2 = getValue('sTanggal2');
	var bayarkepada=getValue('sBayarke');
    
    if(tanggal!='') {
        var tmpTanggal = tanggal.split('-');
        var tanggalR = tmpTanggal[2]+"-"+tmpTanggal[1]+"-"+tmpTanggal[0];
    } else {
        var tanggalR = '';
    }
	
	 if(tanggal2!='') {
        var tmpTanggal2 = tanggal2.split('-');
        var tanggalR2 = tmpTanggal2[2]+"-"+tmpTanggal2[1]+"-"+tmpTanggal2[0];
    } else {
        var tanggalR2 = '';
    }
    
    var where = '[["notransaksi","'+notrans.value+'"],["tanggal","'+tanggalR+'"],["tanggal2","'+tanggalR2+'"],["jumlah","'+remove_comma_var(rupiah.value)+'"],["noakun","'+noakun+'"],["supplier","'+supplier+'"],["posting","'+posting+'"],["tipetransaksi","'+tipetransaksi+'"],["bayarkepada","'+bayarkepada+'"]]';
  
	
    if(typeof where != 'undefined') {
        var newWhere = where.replace(/'/g,'"');
    }
    var workField = document.getElementById('workField');
    var param = "page="+page;
    param += "&shows="+shows+"&tipe=KB";
    if(typeof where != 'undefined') {
        param+="&where="+newWhere;
    }
	*/
	
	notransaksi = trim(document.getElementById('sNoTrans').value);
	noakun = trim(document.getElementById('sAkun').value);
	jumlah = trim(document.getElementById('sRupiah').value);jumlah=remove_comma_var(jumlah);
	tanggalinput1=trim(document.getElementById('sTanggal').value);
	tanggalinput2=trim(document.getElementById('sTanggal2').value);
	tipetransaksi=trim(document.getElementById('sTipe').value);
	kodesupplier=trim(document.getElementById('sSupplier').value);
	bayarkepada=trim(document.getElementById('sBayarke').value);
	posting=trim(document.getElementById('sPosting').value);
	keterangan=trim(document.getElementById('sKeterangan').value);
	keterangan1=trim(document.getElementById('sKeterangan1').value);
	
	
	param='';
	param+='&page='+page+'&shows='+shows;
	param+='&notransaksi='+notransaksi+'&noakun='+noakun+'&jumlah='+jumlah+'&keterangan1='+keterangan1;
	param+='&tanggalinput1='+tanggalinput1+'&tanggalinput2='+tanggalinput2+'&keterangan='+keterangan;
	param+='&tipetransaksi='+tipetransaksi+'&kodesupplier='+kodesupplier+'&bayarkepada='+bayarkepada+'&posting='+posting;
	
	
	//alert(param);
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showHeadList', param, respon);
}


function choosePage(obj,shows,where) {
    var pageVal = obj.options[obj.selectedIndex].value;
    goToPages(pageVal,shows,where);
}

/* Halaman Manipulasi Data
 * Halaman add, edit, delete
 */
function showAdd() {
    var workField = document.getElementById('workField');
    var param = "";
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
					document.getElementById('hutangunit').disabled=false;
						document.getElementById('pemilikhutang').disabled=false;
						document.getElementById('noakunhutang').disabled=false;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showAdd', param, respon);
}

function showEditFromAdd() {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi');
    var param = "notransaksi="+trans.value+"&kodeorg="+getValue('kodeorg')+
        "&noakun="+getValue('noakun2a')+"&tipetransaksi="+getValue('tipetransaksi');
	param+= "&bayarkepada="+getValue('bayarkepada');
	param+= "&rekening="+getValue('rekening');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showEdit', param, respon);
}

function showEdit(num) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi_'+num).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+num).getAttribute('value');
    var noakun = document.getElementById('noakun_'+num).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+num).getAttribute('value');
    var param = "numRow="+num+"&notransaksi="+trans+"&kodeorg="+
        kodeorg+"&noakun="+noakun+"&tipetransaksi="+tipetransaksi;
    
	
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
					
                    showDetail();
						document.getElementById('hutangunit').disabled=true;
						document.getElementById('pemilikhutang').disabled=true;
						document.getElementById('noakunhutang').disabled=true;
						document.getElementById('matauang').disabled=true;
						document.getElementById('kurs').disabled=true;
						
						
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showEdit', param, respon);
}

/* Manipulasi Data
 * add, edit, delete
 */
function addDataTable() {
    var hutangunit='';
    var pemilikhutang=getValue('pemilikhutang');
    var noakunhutang=getValue('noakunhutang');
    if(document.getElementById("hutangunit").checked==true){
        hutangunit='1';
    }else{
        pemilikhutang='';
        noakunhutang='';
    }
    
	if(document.getElementById("autokb").checked==true){
        autokb='1';
    }else{
        autokb='0';
    }
	
	
    if(getValue('kurs')=='' || getValue('kurs')==0)
    {
        alert(notifcurrency);return;
    }
    noakun2a=document.getElementById('noakun2a');
    noakun2a=noakun2a.options[noakun2a.selectedIndex].value;
    matauang=document.getElementById('matauang');
    matauang=matauang.options[matauang.selectedIndex].value;
    
    var param = "notransaksi="+getValue('notransaksi')+"&noakun="+noakun2a;
    param += "&tanggalinput="+getValue('tanggalinput')+"&matauang="+matauang;
    param += "&kurs="+getValue('kurs')+"&tipetransaksi="+getValue('tipetransaksi');
    param += "&jumlah="+getValue('jumlah')+"&cgttu="+getValue('cgttu');
    param += "&keterangan="+getValue('keterangan')+"&yn="+getValue('yn')+"&kodeorg="+getValue('kodeorg');
    param += "&nocek="+getValue('nocek');
    param+= "&hutangunit="+hutangunit;
    param+= "&pemilikhutang="+pemilikhutang;
    param+= "&noakunhutang="+noakunhutang;
	param+= "&bayarkepada="+getValue('bayarkepada');
	param+= "&rekening="+getValue('rekening');
	param+= "&namabank="+getValue('namabank');
	
	
	
	param+= "&norekpenerima="+getValue('norekpenerima');
	param+= "&namapenerima="+getValue('namapenerima');
	param+= "&noakun2b="+getValue('noakun2b');
	param+= "&autokb="+autokb;
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('notransaksi').value = con.responseText;
                    showEditFromAdd();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=add', param, respon);
}


//indra
function editDataTable() {
    var hutangunit='';
    var pemilikhutang=getValue('pemilikhutang');
    var noakunhutang=getValue('noakunhutang');
    if(document.getElementById("hutangunit").checked==true){
        hutangunit='1';
    }else{
        pemilikhutang='';
        noakunhutang='';
    }
	
	
	if(document.getElementById("autokb").checked==true){
        autokb='1';
    }else{
        autokb='0';
    }
	
    var param = "notransaksi="+getValue('notransaksi')+"&noakun="+getValue('noakun2a');
    param += "&tanggalinput="+getValue('tanggalinput')+"&matauang="+getValue('matauang');
    param += "&kurs="+getValue('kurs')+"&tipetransaksi="+getValue('tipetransaksi');
    param += "&jumlah="+getValue('jumlah')+"&cgttu="+getValue('cgttu');
    param += "&keterangan="+getValue('keterangan')+"&yn="+getValue('yn')+"&kodeorg="+getValue('kodeorg');
    param+= "&oldNoakun="+getValue('oldNoakun');
    param += "&nocek="+getValue('nocek');
    param+= "&autokb="+autokb;
    param+= "&hutangunit="+hutangunit;
    param+= "&pemilikhutang="+pemilikhutang;
    param+= "&noakunhutang="+noakunhutang;
	param+= "&bayarkepada="+getValue('bayarkepada');
	param+= "&rekening="+getValue('rekening');
	param+= "&namabank="+getValue('namabank');

	param+= "&norekpenerima="+getValue('norekpenerima');
	param+= "&namapenerima="+getValue('namapenerima');
	param+= "&noakun2b="+getValue('noakun2b');
	param+= "&autokb="+autokb;
    
    function respon() {
		
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    alert(con.responseText);
                    // defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=edit', param, respon);
}

/*
 * Detail
 */
 

function showDetail() {
	
    var detailField = document.getElementById('detailField');
    var notrans = document.getElementById('notransaksi').value;
    var param = "notransaksi="+notrans+"&kodeorg="+getValue('kodeorg')+"&tipetransaksi="+
        getValue('tipetransaksi')+"&noakun="+getValue('noakun2a')+"&rekening="+getValue('rekening');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					var res = con.responseText;
					res = res.split('<script>');
                    detailField.innerHTML = res[0];
					if(res.length>1) {
						res[1] = res[1].replace('</script></fieldset>','');
						console.log(res[1]);
						eval(res[1]);
					}
					load_filex();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank_detail.php?proses=showDetail', param, respon);
}

function pilihhutang(){
//    var kodeorg=getValue('kodeorg');
//    if(kodeorg.substring(2, 4)=='HO'){
//        
//    }else{
//        alert('Pilihan hanya untuk HO');
//        document.getElementById('hutangunit').checked=false;
//        document.getElementById('pemilikhutang').disabled=true;
//        document.getElementById('noakunhutang').disabled=true;
//        exit();
//    }
    var centang = document.getElementById('hutangunit');
    if(centang.checked!=true){
        document.getElementById('pemilikhutang').disabled=true;
        document.getElementById('noakunhutang').disabled=true;
    }else{
        document.getElementById('pemilikhutang').disabled=false;
        document.getElementById('noakunhutang').disabled=false;        
    }
}

//function gantiValue(obj){
//    if(obj.value==1)
//        obj.value=0; else obj.value=1;
//}

function deleteData(num) {
    var notrans = document.getElementById('notransaksi_'+num).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+num).getAttribute('value');
    var noakun = document.getElementById('noakun_'+num).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+num).getAttribute('value');
    var param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
        "&tipetransaksi="+tipetransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    var tmp = document.getElementById('tr_'+num);
                    tmp.parentNode.removeChild(tmp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Delete '+notrans+'\nThis transaction. are you sure?')) {
        post_response_text('keu_slave_kasbank.php?proses=delete', param, respon);
    }
   
}

/* Posting Data
 */
 
 
 
//rename dlu indra
function postingDatax(numRow){
  // var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value'),
	// kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value'),
	// noakun = document.getElementById('noakun_'+numRow).getAttribute('value'),
	// tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
	content= "<div id=formpost  style=\"height:100%;width:325px;\"></div>";
	//content+="<div id=formCariBarang></div>";
	title='posting';
	height='';
	width='330';
	showDialog1(title,content,width,height,'event');	
	// getformPost(notrans,kodeorg,noakun,tipetransaksi,numRow);
	insertefill(numRow);
} 

function insertefill(numRow){
	var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
	kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
	noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
	tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
	
	param='method=insertefill&notransaksi='+notrans;
    tujuan='keu_slave_efill.php';
	
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
					closeDialog2();
				}else{
					getformPost(notrans,kodeorg,noakun,tipetransaksi,numRow);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function viewefill(notransaksi,showhideefil,ev){
	content= "<div id=formviewefill  style=\"height:100%;\"></div>";
	title='View Efilling System';
	height='';
	width='';
	showDialog5(title,content,width,height,'event');
	showefil(notransaksi,showhideefil);
}

function showefil(notransaksi,showhideefil){
	param='method=viewefill&notransaksi='+notransaksi+'&showhideefil='+showhideefil;
    tujuan='keu_slave_efill.php';
	
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('formviewefill').innerHTML = con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}


function getformPost(notrans,kodeorg,noakun,tipetransaksi,numRow){
	 var param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
        "&tipetransaksi="+tipetransaksi+"&numRow="+numRow;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   document.getElementById('formpost').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	post_response_text('keu_slave_kasbank.php?proses=showform', param, respon);		
} 
 
 
// #= diubah menjadi persetujuan
function savePosting(notrans,kodeorg,noakun,tipetransaksi,numRow,maxaproval) {
	novoucher=document.getElementById('novoucher').value;
	tglpost=document.getElementById('tglpost').value;	
	//persetujuan=document.getElementById('persetujuan').value;	
	strper='';
	//awalnya jika tipetransaksi masuk gak ke form,permintaan terakhir kembali ke semula
	// if(tipetransaksi!="M"){
		for(i=1;i<=maxaproval;i++){
		 strper += '&persetujuan['+i+']='+trim(document.getElementById('persetujuan'+i).value)
		}
		param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+"&maxaproval="+maxaproval+
		"&tipetransaksi="+tipetransaksi+"&novoucher="+novoucher+"&tglpost="+tglpost+"&proses=persetujuan";
		param+=strper;	
	// }else{
		//param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+"&tipetransaksi="+tipetransaksi+"&novoucher="+novoucher+"&tglpost="+tglpost+"&proses=persetujuan";
	//}
	
	
	if(tglpost=='') {
		alert('Date aproval must be filled');
		return;
	}
	//if(tipetransaksi=="M"){
	//	savePostingMasuk(notrans,kodeorg,noakun,tipetransaksi,numRow,maxaproval);
	//}else{
		if(confirm('Posting '+notrans+'\nThis transaction will released. are you sure?')) {
           post_response_text('keu_slave_kasbank.php', param, respon);
        }
	    function respon() {
	        if (con.readyState == 4) {
	            if (con.status == 200) {
	                busy_off();
	                if (!isSaveResponse(con.responseText)) {
	                    alert(con.responseText);
	                } else {
	                   //=== Success Response
	                    //alert('Posting Berhasil');
	                    x=document.getElementById('tr_'+numRow);
	                    x.cells[10].innerHTML='Proses Persetujuan';
						x.cells[12].innerHTML='';
						x.cells[13].innerHTML='';
						x.cells[14].innerHTML='';
						closeDialog();		
						
	                }
	            } else {
	                busy_off();
	                error_catch(con.status);
	            }
	        }
	    }  
	//}
} 



function postingData(numRow){
  // var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value'),
	// kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value'),
	// noakun = document.getElementById('noakun_'+numRow).getAttribute('value'),
	// tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
	content= "<div id=formpost  style=\"height:100%;width:325px;\"></div>";
	//content+="<div id=formCariBarang></div>";
	title='posting';
	height='';
	width='330';
	showDialog1(title,content,width,height,'event');	
	// getformPost(notrans,kodeorg,noakun,tipetransaksi,numRow);
	showontop();
	insertefill(numRow);
} 

// function savePostingMasuk(notrans,kodeorg,noakun,tipetransaksi,numRow) {  //indra rename
function postingDatax(numRow) { 
    
	/*
	novoucher=document.getElementById('novoucher').value;
	tglpost=document.getElementById('tglpost').value;	
	
	param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
	"&tipetransaksi="+tipetransaksi+"&novoucher="+novoucher+"&tglpost="+tglpost;
		*/
		
		
	var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
	
	var tglpost = '2019-04-29';
	var novoucher = notrans;
	
   param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
	"&tipetransaksi="+tipetransaksi+"&novoucher="+novoucher+"&tglpost="+tglpost;
		

		
		
    //alert(param);
	// if(novoucher=='') {
		// alert('Voucher Number must be filled');
		// return;
	// }
	if(tglpost=='') {
		alert('Date must be filled');
		return;
	}
	if(confirm('Posting '+notrans+'\nThis transaction will released. are you sure?')) {
           post_response_text('keu_slave_kasbank_posting.php', param, respon);
        }
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   //=== Success Response
                    //alert('Posting Berhasil');
                    x=document.getElementById('tr_'+numRow);
                    x.cells[10].innerHTML='';
                    x.cells[11].innerHTML='';
					x.cells[12].innerHTML='<img class=\"zImgOffBtn\" title=\"Posting\" src=\"images/skyblue/posted.png\">';
                   // x.cells[12].innerHTML='';
                    //javascript:location.reload(true);
					closeDialog();		
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
} 

 /*
function savePosting(notrans,kodeorg,noakun,tipetransaksi,numRow) {
    
	novoucher=document.getElementById('novoucher').value;
	tglpost=document.getElementById('tglpost').value;	
	
	param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
	"&tipetransaksi="+tipetransaksi+"&novoucher="+novoucher+"&tglpost="+tglpost;
		
		
    //alert(param);
	// if(novoucher=='') {
		// alert('Voucher Number must be filled');
		// return;
	// }
	if(tglpost=='') {
		alert('Date must be filled');
		return;
	}
	if(confirm('Posting '+notrans+'\nThis transaction will released. are you sure?')) {
           post_response_text('keu_slave_kasbank_posting.php', param, respon);
        }
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   //=== Success Response
                    //alert('Posting Berhasil');
                    x=document.getElementById('tr_'+numRow);
                    x.cells[10].innerHTML='';
                    x.cells[11].innerHTML='';
					x.cells[12].innerHTML='<img class=\"zImgOffBtn\" title=\"Posting\" src=\"images/skyblue/posted.png\">';
                   // x.cells[12].innerHTML='';
                    //javascript:location.reload(true);
					closeDialog();		
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
} 
*/
 
/*
function postingData(numRow) {
    var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
        "&tipetransaksi="+tipetransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    //alert('Posting Berhasil');
                    x=document.getElementById('tr_'+numRow);
                    x.cells[9].innerHTML='';
                    x.cells[10].innerHTML='';
					x.cells[11].innerHTML='<img class=\"zImgOffBtn\" title=\"Posting\" src=\"images/skyblue/posted.png\">';
                   // x.cells[12].innerHTML='';
                    //javascript:location.reload(true);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm('Posting '+notrans+'\nThis transaction will released. are you sure?')) {
        post_response_text('keu_slave_kasbank_posting.php', param, respon);
    }
}
*/

function printPDF(ev) {
    // Prep Param
    param = "proses=pdf";
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function printXLS(ev) {
    // Prep Param
    param = "proses=excel";
    
    showDialog1('Print Excel',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
/*
function detailPDF(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    param = "proses=pdf&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}*/


function detailPDF(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    param = "proses=pdfnew&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}


function detailPDF2(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    param = "proses=pdf2&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailPDF3(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    param = "proses=pdf3&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}


function tampilDetail(numRow,ev)
{
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
   param = "proses=html&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
        title="Data Detail";
        showDialog1(title,"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}
/* Update No Urut di halaman absensi
 */
function updNoUrut() {
    var tabBody = document.getElementById('mTabBody');
    var nourut = document.getElementById('nourut');
    var maxNum = 0;
    
    if(tabBody.childNodes.length>0) {
        for(i=0;i<tabBody.childNodes.length;i++) {
            var tmp = document.getElementById('nourut_'+i);
            if(tmp.innerHTML > maxNum) {
                maxNum = tmp.innerHTML;
            }
        }
    }
    nourut.value = parseInt(maxNum)+1;
}

/* Update Field Aktif berdasarkan akun yang dipilih
 */
function updFieldAktif() {
	var id='ftPrestasi_';
    var noakun = document.getElementById(id+'noakun').childNodes;
    var kodekegiatan = document.getElementById(id+'kodekegiatan').childNodes;
    var kodeasset = document.getElementById(id+'kodeasset').childNodes;
    //var kodebarang = document.getElementById(id+'kodebarang').childNodes;
    var nik = document.getElementById(id+'nik').childNodes;
    var kodecustomer = document.getElementById(id+'kodecustomer').childNodes;
    var kodesupplier = document.getElementById(id+'kodesupplier').childNodes;
    var kodevhc = document.getElementById(id+'kodevhc').childNodes;
    var orgalokasi = document.getElementById(id+'orgalokasi').childNodes;
    var param = "noakun="+noakun[0].options[noakun[0].selectedIndex].value;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    var res = con.responseText;
					res=res.trim();
                    // Kegiatan
                    if(res[0]==0) {
                        kodekegiatan[0].setAttribute('disabled','disabled');
                        kodekegiatan[0].selectedIndex=0;
                    } else {
                        kodekegiatan[0].removeAttribute('disabled');
                    }
                    
                    // Asset
                    if(res[1]==0) {
                        kodeasset[0].setAttribute('disabled','disabled');
                        kodeasset[0].selectedIndex=0;
                    } else {
                        kodeasset[0].removeAttribute('disabled');
                    }
                    
                    // Barang
                    // if(res[2]==0) {
                    //     kodebarang[0].setAttribute('disabled','disabled');
                    //     kodebarang[2].setAttribute('disabled','disabled');
                    //     kodebarang[3].setAttribute('disabled','disabled');
                    //     kodebarang[0].value='';
                    //     kodebarang[2].value='';
                    // } else {
                    //     kodebarang[0].removeAttribute('disabled');
                    //     kodebarang[2].removeAttribute('disabled');
                    //     kodebarang[3].removeAttribute('disabled');
                    // }
                    
                    // Karyawan
                    if(res[2]==0) {
                        nik[0].setAttribute('disabled','disabled');
                        nik[0].selectedIndex=0;
                    } else {
                        nik[0].removeAttribute('disabled');
                    }
                    
                    // Customer
                    if(res[3]==0) {
                        kodecustomer[0].setAttribute('disabled','disabled');
                        kodecustomer[0].selectedIndex=0;
                    } else {
                        kodecustomer[0].removeAttribute('disabled');
                    }
                    
                    // Supplier
                    if(res[4]==0) {
                        kodesupplier[0].setAttribute('disabled','disabled');
                        kodesupplier[0].selectedIndex=0;
                    } else {
                        kodesupplier[0].removeAttribute('disabled');
                    }
                    
                    // Kendaraan
                    if(res[5]==0) {
                        kodevhc[0].setAttribute('disabled','disabled');
                        kodevhc[0].selectedIndex=0;
                    } else {
                        kodevhc[0].removeAttribute('disabled');
                    }
                    // blok
                    if(res[6]==0) {
                        orgalokasi[0].setAttribute('disabled','disabled');
                        orgalokasi[0].selectedIndex=0;
                    } else {
                        orgalokasi[0].removeAttribute('disabled');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank_detail.php?proses=updField', param, respon);
}









//jamhari
function searchNopo(title,content,ev)
{
        //isi=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
        //content=content+"<input type='hidden' id='jnsInvoice' value="+isi+">";
	width='';
	height='';
	showDialog6(title,content,width,height,ev);
        getForminvoice(0);
	//alert('asdasd');
}

function searchKontrak(title,content,ev)
{
    width='';
	height='';
	showDialog2(title,content,width,height,ev);
    getForminvoice(1);
}

function searchMemo(title,content,ev)
{
    width='';
	height='';
	showDialog4(title,content,width,height,ev);
    getForminvoice(2);
	//alert('asdasd');
}




function findNoinvoice(tipe)
{
	txt=trim(document.getElementById('no_brg').value);
        idSupplier=document.getElementById('supplierIdcr').options[document.getElementById('supplierIdcr').selectedIndex].value;
	param='txtfind='+txt;
    if(idSupplier!='')
    {
        param+='&idSupplier='+idSupplier
    }
    param += '&sNopo='+getValue('sNopo')+'&sInvSupp='+getValue('sInvSupp');
    param += '&sNilai='+getValue('sNilai')+'&sYm='+getValue('sYm');
	param += '&matauang='+getValue('matauang')+'&tipeinv='+getValue('tipeinv');
	param += '&kodeorg='+getValue('kodeorg');
	param += '&tipeinvoice='+getValue('tipeinvoiceap');
    tujuan='keu_slave_kasbank_detail.php';
    if((txt=='')&&(idSupplier==''))
    {
        alert(notifnoinvoicepilih);
    } else {
		if(tipe==0)
			post_response_text(tujuan+'?'+'proses=getInvoice', param, respog);
		else
			post_response_text(tujuan+'?'+'proses=getInvoiceAR', param, respog);
	}
        
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getForminvoice(tipe)
{
	param='';
	tujuan='keu_slave_kasbank_detail.php';
	if(tipe==0) {
		post_response_text(tujuan+'?'+'proses=getForminvoice', param, respog);
	} else if (tipe==1) {
		post_response_text(tujuan+'?'+'proses=getFormInvoiceAR', param, respog);
	} else {
		post_response_text(tujuan+'?'+'proses=getFormMemo', param, respog);
	}
	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							//alert(con.responseText);
							document.getElementById('formPencariandata').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }
	 }
} 


function findMemo()
{
	var param='nojurnal='+getValue('sNojurnal')+'&periode='+getValue('sYm')+
			'&tipetransaksi='+getValue('tipetransaksi'),
		tujuan='keu_slave_kasbank_detail.php?proses=getMemo';
	var hutangunit=0;
        if(document.getElementById('hutangunit').checked==true){
            hutangunit=1;
        }
        pemilikHutang=document.getElementById('pemilikhutang');
        pemilikHutang=pemilikHutang.options[pemilikHutang.selectedIndex].value;
        noakunHutang=document.getElementById('noakunhutang');
        noakunHutang=noakunHutang.options[noakunHutang.selectedIndex].value;
        param+='&hutangunit='+hutangunit+'&pemilikhutang='+pemilikHutang+'&noakunhutang='+noakunHutang;
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setPo(np,nilai,akn,ket,supp,nopo)
{
    document.getElementById('keterangan1').value=np;
//    document.getElementById('jumlah').value='';
    ds=document.getElementById('ftPrestasi_jumlah');
    ds.childNodes[0].value=nilai;
   // document.getElementById('noakun').value=akn;
    document.getElementById('keterangan2').value=nopo;
    l=document.getElementById('noakun');
    document.getElementById('nodok').value=nopo;
    
    for(a=0;a<l.length;a++)
        {
            if(l.options[a].value==akn)
                {
                    l.options[a].selected=true;
                }
        }
  l2=document.getElementById('kodesupplier');  
    for(a2=0;a2<l2.length;a2++)
        {
            if(l2.options[a2].value==supp)
                {
                    l2.options[a2].selected=true;
                }
        }
    closeDialog();
}

// function checkAll() {
    // var els = document.getElementById('invTbody').getElementsByClassName('inv-chk');
    // for(var i=0;i<els.length;i++) {
        // els[i].checked = true;
    // }
// }

function checkAll()
{
    drt = document.getElementById('btnAllInvoice');
    if (drt.checked == true)
    {
        chk = true;
    }
    else
    {
        chk = false;
    }
    var tbl = document.getElementById("invTbody");
    var row = tbl.rows.length;
    row = row - 1;
    for (i = 0; i <= row; i++)
    {
        document.getElementById('inv_' + i).checked = chk;
    }
}








function getMemo(nojurnal) {
	var param='nojurnal='+nojurnal;
    param += '&notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&tanggal='+getValue('tanggal')+'&noakunhutang='+getValue('noakunhutang');
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=addFromMemo', param, respog);
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function add2detail() {
    var els = document.getElementById('invTbody').getElementsByClassName('inv-chk'),
        invNo = [],param='';
        var isihtng=0;
    htng=document.getElementById('hutangunit');
    if(htng.checked==true){
        var isihtng=1;
    }
    param += 'notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+isihtng;
	param += '&pemilikhutang='+getValue('pemilikhutang');
	param += '&tipeinv='+getValue('tipeinv');
    param += '&bulan='+getValue('bulanap')+'&tahun='+getValue('tahunap');
	


    for(var i=0;i<els.length;i++) {
        if(els[i].checked) {
            invNo.push(els[i].getAttribute('invNo'));
            param+='&invoice[]='+els[i].getAttribute('invNo');
            param+='&sisa[]='+els[i].getAttribute('sisa');
			param+='&tgltk[]='+els[i].getAttribute('tgltk');
			param+='&kdsup[]='+els[i].getAttribute('kdsup');
			param+='&noakundet[]='+els[i].getAttribute('noakundet');
			// param+='&noaruskas[]='+getValue('noaruskas_'+i);
			// param+='&keterangan[]='+getValue('keterangan_'+i);
        }
    }
    
	
    //alert(param);return;
	if(invNo.length>0) {
		tujuan='keu_slave_kasbank_detail.php';
		post_response_text(tujuan+'?'+'proses=addFromInvoice', param, respog);
	} else {
		alert(notifnoinvoicepilih);
        return;
	}
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					// update bayarkepada 
					/*
					#= ganti keteranganht 
					#= keterangan bayar / masuk ambil dari kodesupplier
					#= berdasarkan CRF dari RO, hasil kunjungan awal maret 2021
					#= point 14
					*/
					if(con.responseText!=''){
						document.getElementById('bayarkepada').value=con.responseText;
					}
					
					
                    showDetail();
                    closeDialog6();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function add2detailAR() {
    var els = document.getElementById('invTbody').getElementsByClassName('inv-chk'),
        invNo = [],param='';
    
    param += 'notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
    param += '&bulan='+getValue('bulanar')+'&tahun='+getValue('tahunar');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&jumlah='+getValue('jumlah');
	param += '&tanggal='+getValue('tanggalinput');
    for(var i=0;i<els.length;i++) {
        if(els[i].checked) {
            invNo.push(els[i].getAttribute('invNo'));
            param+='&invoice[]='+els[i].getAttribute('invNo');
            param+='&sisa[]='+els[i].getAttribute('sisa');
        }
    }
    
    if(invNo.length>0) {
		tujuan='keu_slave_kasbank_detail.php';
		post_response_text(tujuan+'?'+'proses=addFromInvoiceAR', param, respog);
	} else {
		alert('Tidak ada No Invoice yang dipilih');
	}
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    // update bayarkepada 
					/*
					#= ganti keteranganht 
					#= keterangan bayar / masuk ambil dari kodesupplier
					#= berdasarkan CRF dari RO, hasil kunjungan awal maret 2021
					#= point 14
					*/
					document.getElementById('bayarkepada').value=con.responseText;
					showDetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cekKurs() {
	var kurs = getValue('kurs');
	if (kurs > 0) {
		document.getElementById('addHead').removeAttribute('disabled');
		if (document.getElementById('editHead')) {
			document.getElementById('editHead').removeAttribute('disabled');
		}	
	} else {
		document.getElementById('addHead').setAttribute('disabled','disabled');
		if (document.getElementById('editHead')) {
			document.getElementById('editHead').setAttribute('disabled','disabled');
		}
	}
}

function searchcangsrn(title,content,ev){
    width='650';
	height='450';
	showDialog4(title,content,width,height,ev);
    getformangsrn();
}

function getformangsrn(){
	tgl=document.getElementById('tanggal').value;
	param='tanggal='+tgl;
	tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getformangsrn', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formPencariandata').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
} 
function findangsuran(){
	unit=trim(document.getElementById('unitSrc').value);
	tgl=document.getElementById('tanggal2').value;
	param='unit='+unit+'&tanggal='+tgl;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getAngsuran', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function add2detilangsrn() {
    var els = document.getElementById('invTbody').getElementsByClassName('inv-chk'),
        invNo = [],param='';
    param += 'notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&pemilikhutang='+getValue('pemilikhutang');
	param += '&tanggal='+getValue('tanggal');
    for(var i=0;i<els.length;i++) {
        if(els[i].checked) {
            invNo.push(els[i].getAttribute('invNo'));
            param+='&karyDt[]='+els[i].getAttribute('karyDt');
            param+='&bulanan[]='+els[i].getAttribute('bulanan');
            param+='&jenis[]='+els[i].getAttribute('jenis');
            param+='&unit[]='+els[i].getAttribute('unit');
            ket=document.getElementById('ketdet_'+i).value;
            param+='&ketdet[]='+ket;
        }
    }
    
    if(invNo.length>0) {
		tujuan='keu_slave_kasbank_detail.php';
		post_response_text(tujuan+'?'+'proses=addFromAngsuran', param, respog);
	} else {
		alert('Tidak ada data yang dipilih');
	}
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getakunkasbank(kdorg)
{
	kdorg = kdorg.value;
	
	param = 'kdorg='+kdorg+'&proses=getakunkasbank';
	post_response_text('keu_slave_kasbank_detail.php', param, respon);
	
	function respon() 
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
					// === Success Response
					isdt = con.responseText.split("####");
                    document.getElementById('noakun2a').innerHTML = isdt[0];
                    document.getElementById('namapenerima').innerHTML = isdt[1];
                    // document.getElementById('noakun2a').innerHTML = con.responseText;
                    getbank();
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

function getbank()
{
	kodeorg=document.getElementById('kodeorg').value;
	noakun2a=document.getElementById('noakun2a').value;
	
	param = 'noakun2a='+noakun2a+'&proses=getbank'+'&kodeorg='+kodeorg;
	post_response_text('keu_slave_kasbank_detail.php', param, respon);
	
	function respon() 
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
					// === Success Response
                    document.getElementById('rekening').innerHTML = con.responseText;
					getbank2();
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





function getbuktibayar()
{
	kodeorg=document.getElementById('kodeorg').value;
	rekening=document.getElementById('rekening').value;
	cgttu=document.getElementById('cgttu').value;
	
	param = 'rekening='+rekening+'&proses=getbuktibayar'+'&cgttu='+cgttu+'&kodeorg='+kodeorg;
	post_response_text('keu_slave_kasbank_detail.php', param, respon);
	
	function respon() 
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
					// === Success Response
                    document.getElementById('nocek').value = con.responseText;
                    getmatauang();
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

function getmatauang()
{
	rekening=document.getElementById('rekening').value;
    tanggal=document.getElementById('tanggalinput').value;
	
	param = 'rekening='+rekening+'&proses=getmatauang'+'&tanggal='+tanggal;
	post_response_text('keu_slave_kasbank_detail.php', param, respon);
	
	function respon() 
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
					// === Success Response
					isdt = con.responseText.split("####");
                    document.getElementById('matauang').innerHTML = isdt[0];
                    document.getElementById('kurs').value = isdt[1];
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

function getRekPT(title,content,ev){
    width='650';
	height='450';
	showDialog4(title,content,width,height,ev);
    getformRekPt();
}

function getformRekPt(){
	param='';
	tujuan='keu_slave_kasbank.php';
	post_response_text(tujuan+'?'+'proses=getformRekPt', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formPencariandataGetformRek').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
} 
function findgetRekPT(){
	kodeorg=trim(document.getElementById('unitSrcRek').value);
	param='kodeorg='+kodeorg;
    tujuan='keu_slave_kasbank.php';
	post_response_text(tujuan+'?'+'proses=getRekPT', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerRekPT').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function fillRekPt(nobrp){
	norek=document.getElementById('rekeningGet_'+nobrp).value;
	atasNama=document.getElementById('atasnamaGet_'+nobrp).value;
	document.getElementById('norekpenerima').value=norek;
	document.getElementById('namapenerima').value=atasNama;
}

function getstatuslahan(idlahan,ev) {
	title = "View";
	width = '1350px';
	height = '';
	content = "<div id=containerview style='overflow:auto;width:1230px;height:auto;vertical-align:top;'></div>";
	showDialog5(title, content, width, height, ev);
	
	param = "";
	param += "idlahan=" + idlahan;
	param += '&method=getstatuslahan';
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('containerview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdaftarmasy(idlahan,ev) {
	title = "View";
	width = '950px';
	height = '';
	content = "<div id=containerview style='overflow:auto;width:930px;height:auto;'></div>";
	showDialog5(title, content, width, height, ev);
	
	param = "";
	param += "idlahan=" + idlahan;
	param += '&method=getdaftarmasy';
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('containerview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}