function viewfile(idfile,sumber) {
	//formupload();
	param = 'method=viewfile&idfile=' + idfile;
	tujuan = 'keu_kasdanbank_slave.php';
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


function deletefile(notransaksi, namafile, no) {
    param ="method=deletefile&notransaksi=" + notransaksi + "&namafile=" + namafile;
    tujuan = "keu_kasdanbank_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById('ppDetailTable' + no).innerHTML = '';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function kirimkanemail() {
	email  =document.getElementById('email').value;
	subject=document.getElementById('subject').value;
	body   =document.getElementById('body').value;
	file   =document.getElementById('file').value;
	cc   =document.getElementById('cc').value;
	notransaksi   =document.getElementById('notransaksiemail').value;
	
	
    param = "method=kirimkanemail";
    param += "&email=" + email;
    param += "&subject=" + subject;
    param += "&body=" + body;
    param += "&file=" + file;
    param += "&cc=" + cc;
    param += "&notransaksi=" + notransaksi;
    tujuan = "keu_kasdanbank_slave.php";
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    alertify.alert("Done");
					alertify.popup2().destroy();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function kirimemail(notransaksi) {
    param = "method=kirimemail";
    param += "&notransaksi=" + notransaksi;
    tujuan = "keu_kasdanbank_slave.php";
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    alertify
                    .popup2()
                    .set({
                        resizable: true,
                        maximizable: true,
                        startMaximized: false,
                        message: con.responseText,
                    })
                    .resizeTo("70%", "80%")
                    .show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getsaldokasbank(e,notransaksi) {
	tglrencbayar=document.getElementById('tglrencbayar').value;
	method = 'getsaldokasbank';
	param='';
	param += '&norek=' + e.value;
	param += '&tglrencbayar=' + tglrencbayar;
	param += '&notransaksi=' + notransaksi;
	param += '&method=' + method;
	// alert(param);
	tujuan = 'keu_kasir_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
                } else {
                    document.getElementById('contsaldokasbank').innerHTML = con.responseText;
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getvaluethis(e){
	val = e.innerHTML;
	val = remove_comma_var(val);
	
	navigator.clipboard.writeText(val);
	
	alertify.set('notifier','position', 'top-center');
	alertify.success("Copied: "+val);
}

function showhideinfo() {
	var row = document.getElementById('forminfo');
	if (row !== null) {
		if (row.style.display == '') {
			row.style.display = 'none';
		} else {
			row.style.display = '';
		}
	}
}



function getrekeningsch() {
	noakun=document.getElementById('noakunsch').value;
	kodeorg=document.getElementById('kodeorgsch').value;
	
	method = 'getrekeningsch';
	param='';
	param += '&noakun=' + noakun + '&kodeorg=' + kodeorg;
	param += '&method=' + method;
	// alert(param);
	tujuan = 'keu_kasdanbank_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
                } else {
					
					document.getElementById('rekeningsch').disabled=false;
                    document.getElementById('rekeningsch').innerHTML = con.responseText;
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}




// function showhidefilter() {
	// var row = document.getElementById('formsch');
	// if (row !== null) {
		// if (row.style.display == '') {
			// row.style.display = 'none';
		// } else {
			// row.style.display = '';
		// }
	// }
// }




function getcheckbox(notransaksi, no) {
    n = document.getElementById('no_' + no);
    if (n.checked == true) {
        addnotransaksi(notransaksi);
    } else {
        deletenotransaksi(notransaksi);
    }
}

function addnotransaksi(notransaksi) {
    if (notransaksi == undefined) {
        notransaksi = document.getElementById('notransaksi').value;
    }
    if (notransaksi == '') {
        alert('Notransaksi harus dipilih');
        return;
    }
    param = 'method=addnotransaksi&notransaksi=' + notransaksi;
    //param += '&tipe=' + tipe;
    tujuan = 'keu_kasir_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    listtransaksi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function getrekening() {
    supplier = document.getElementById('supplier').value;
    param = 'method=getrekening&supplier=' + supplier;
    tujuan = 'keu_kasir_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('rekeningext').innerHTML = con.responseText;
                    getdetailrekening();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getdetailrekening() {
    supplier = document.getElementById('supplier').value;
    rekeningext = document.getElementById('rekeningext').value;
    param = 'method=getdetailrekening&supplier=' + supplier;
	 param += '&rekeningext=' + rekeningext;
	 // alert(param);
    tujuan = 'keu_kasir_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data = con.responseText.split("####");
                    document.getElementById('namabank').value = data[0];
                    document.getElementById('anrekeningext').value = data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function getketerangan() {
    // supplier = document.getElementById('supplier').value;
    // param = 'method=getketerangan&supplier=' + supplier;
    // tujuan = 'keu_kasir_slave.php';
    // post_response_text(tujuan, param, respog);
    // function respog() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // data = con.responseText.split("####");
                    // document.getElementById('namabank').value = data[0];
                    // document.getElementById('rekeningext').value = data[1];
                    // document.getElementById('anrekeningext').value = data[2];
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }

// }

function deletenotransaksi(notransaksi) {
    param = 'method=deletenotransaksi&notransaksi=' + notransaksi;
    tujuan = 'keu_kasir_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    listtransaksi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function listtransaksi(notransaksi) {
    param = 'method=listtransaksi';
    tujuan = 'keu_kasir_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data = con.responseText.split("####");
                    document.getElementById('listnotransaksi').innerHTML = data[0];
                    document.getElementById('notransaksi').innerHTML = data[1];
                    document.getElementById('notransaksi').selectedIndex = 0;
					if(notransaksi!=''){
						getPage();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



/*
function bayar(notransaksi, kodeorg, noakun, tipetransaksi,no,ev) {
    content = "<div id=formbayar style=\"height:100%;width:100%;\"></div>";
    title = 'Bayar';
    // height = '';
    // width = '';
    // showDialog4(title, content, width, height, ev);
    // pos = new Array();
    // pos = getMouseP(ev);
    // document.getElementById('dynamic4').style.top = pos[1] + 'px';
    // document.getElementById('dynamic4').style.left = (pos[0] - 500) + 'px';

    var param = "notransaksi=" + notransaksi + "&kodeorg=" + kodeorg + "&noakun=" + noakun +
        "&tipetransaksi=" + tipetransaksi;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					
					n = document.getElementById('no_' + no);
					n.checked = true;
		
                    // document.getElementById('formbayar').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','85%');
                    listtransaksi(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_kasir_slave.php?method=showformbayar', param, respon);
}
*/


function gantibukti(notransaksi) {
    content = "<div id=formgantibukti style=\"height:100%;width:100%;\"></div>";
    title = 'Ganti Bukti';
    var param = "notransaksi=" + notransaksi;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					
		
                    // document.getElementById('formbayar').innerHTML = con.responseText;
					alertify.popup("Ganti bukti bayar",con.responseText).set({'resizable':true,'maximizable':false}).resizeTo('400px','300px');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_kasir_slave.php?method=showformgantibukti', param, respon);
}

function bayar(notransaksi, kodeorg, noakun, tipetransaksi,no,ev) {
    content = "<div id=formbayar style=\"height:100%;width:100%;\"></div>";
    title = 'Bayar';
    height = '';
    width = '';
    showDialog4(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic4').style.top = (pos[1] - 200) + 'px';
    document.getElementById('dynamic4').style.left = (pos[0] - 500) + 'px';

    var param = "notransaksi=" + notransaksi + "&kodeorg=" + kodeorg + "&noakun=" + noakun +
        "&tipetransaksi=" + tipetransaksi;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					n = document.getElementById('no_' + no);
					n.checked = true;
		
                    document.getElementById('formbayar').innerHTML = con.responseText;
                    listtransaksi(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_kasir_slave.php?method=showformbayar', param, respon);
}

function getRencBayar(notransaksi) {
    var param = "notransaksi=" + notransaksi;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    alertify.popup(con.responseText).set({'resizable':true,'maximizable':false,'title':'Rencana Pembayaran'}).resizeTo('20%','430px');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_kasir_slave.php?method=getRencBayar', param, respon);
}

function updateRencBayar(notransaksi) {
	tglbayar   = document.getElementById('tglrencbayar').value;
    rekening   = document.getElementById('rekeningrencbyr').value;
    saldoakhir = document.getElementById('saldoakhir').value;
	
    var param = "notransaksi=" + notransaksi;
	param += '&tglbayar=' + tglbayar;
	param += '&rekening=' + rekening;
	
	if(parseFloat(remove_comma_var(saldoakhir))<0){		
		alertify.confirm("Warning","Saldo anda kurang dari nol, Anda yakin untuk melanjutkan ???",
			function(){
					post_response_text('keu_kasir_slave.php?method=updateRencBayar', param, respon);
				},
				function(){
					return;
				}
		).set('resizable',false).resizeTo(100,250);
	}else{		
		post_response_text('keu_kasir_slave.php?method=updateRencBayar', param, respon);
	}
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
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

function displaylist() {
    document.getElementById('notransaksisch').value = '';
    document.getElementById('tanggalsch1').value = '';
    document.getElementById('tanggalsch2').value = '';
    document.getElementById('noakunsch').value = '';
    document.getElementById('tipetransaksisch').value = '';
    document.getElementById('novouchersch').value = '';
    document.getElementById('bayarkesch').value = '';
    loaddata(0);
}

function loaddata(num) {
    // thnsch = document.getElementById('thnsch');
    // thnsch = thnsch.options[thnsch.selectedIndex].value;
	
    notransaksi = document.getElementById('notransaksisch').value;
    novoucher = document.getElementById('novouchersch').value;
    tanggal1 = document.getElementById('tanggalsch1').value;
    tanggal2 = document.getElementById('tanggalsch2').value;
    noakun = document.getElementById('noakunsch').value;
    bayarke = document.getElementById('bayarkesch').value;
    tipetransaksi = document.getElementById('tipetransaksisch').value;

    nocek = document.getElementById('noceksch').value;
    supplier = document.getElementById('suppliersch').value;
    pembayaran = document.getElementById('pembayaransch').value;
    cgttu = document.getElementById('cgttusch').value;
	
    kodeorg = document.getElementById('kodeorgsch').value;
    rekening = document.getElementById('rekeningsch').value;
    keterangan = document.getElementById('catatansch').value;
    jumlah = document.getElementById('jumlahsch').value;

    param = 'method=loaddata&page=' + num;	
    param += '&jumlah=' + jumlah;
    param += '&keterangan=' + keterangan;
    param += '&nocek=' + nocek;
    param += '&supplier=' + supplier;
    param += '&pembayaran=' + pembayaran;

    param += '&cgttu=' + cgttu;
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg;
    param += '&rekening=' + rekening;
    param += '&novoucher=' + novoucher;
    param += '&tanggal1=' + tanggal1;
    param += '&tanggal2=' + tanggal2;
    param += '&noakun=' + noakun;
    param += '&bayarke=' + bayarke;
    param += '&tipetransaksi=' + tipetransaksi;
    tujuan = 'keu_kasir_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					leftFixedTable();
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

// function pdfkasbank(notransaksi, kodeorg, noakun, tipetransaksi, ev) {
//     param = "proses=pdfnew&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
//         "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun;
//     // showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
//         // " src='keu_slave_kasbank_print_detail.php?" + param + "'></iframe>", '800', '400', ev);
//     // var dialog = document.getElementById('dynamic5');
//     // dialog.style.top = '50px';
//     // dialog.style.left = '15%';
// 	    alertify.popuppdf("Print PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_slave_kasbank_print_detail.php?" + param + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
// }

function pdfkasbank(notransaksi, kodeorg, noakun, tipetransaksi, ev) {
    param = "proses=pdfpalma&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
        "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun;

	    alertify.popuppdf("Print PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_kasdanbank_print.php?" + param + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}

function pdfkasbankauto(notransaksi, kodeorg, noakun, tipetransaksi, ev, cgttu, rek) {
    param = "proses=pdfpalma&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
        "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun;
    showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='keu_kasdanbank_print.php?" + param + "'></iframe>", '800', '400', ev);
    var dialog = document.getElementById('dynamic5');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';

    if(cgttu != 'Cash' && kodeorg != 'PPHO') {
        printbayar(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, 'event');
    }

}

// function pdfkasbankauto(notransaksi, kodeorg, noakun, tipetransaksi, ev, cgttu, rek) {
//     param = "proses=pdfnew&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
//         "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun;
//     showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
//         " src='keu_slave_kasbank_print_detail.php?" + param + "'></iframe>", '800', '400', ev);
//     var dialog = document.getElementById('dynamic5');
//     // dialog.style.top = '50px';
//     // dialog.style.left = '15%';
//     printbayar(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, 'event');

// }


function detailkasbank(notransaksi,page){
	method = 'formajukan';
	param='';
	param += '&notransaksi=' + notransaksi + '&page=FROMKASIR';
	param += '&method=' + method;
	tujuan = 'keu_kasdanbank_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi',con.responseText);
                } else {
                   // document.getElementById('formpost').innerHTML=con.responseText;
				   // alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','80%'); 
				   alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				   // loaddata(0);
					showandhide(0);
					document.getElementById('tombolshow').style.display="";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
} 

function showandhide(jenis){
	t = document.getElementById("tempjumlahrow").value;
	col = document.getElementsByName("col0[]");
	
	if(jenis=='1'){
		for(e=0; e<col.length; e++){
			for (i=0; i<=t; i++){
				colomH = document.getElementsByName("col0[]");
				colom = document.getElementsByName("col"+i+"[]");

				colom[e].style.display='';
				colomH[e].style.display='';
			}
		}
		
		document.getElementById("tombolshow").innerHTML='Hide Column';
		document.getElementById("tombolshow").setAttribute('onclick','showandhide(0);');
		
	}else{		
		var isi = [];
		for(e=0; e<col.length; e++){
			total=0;
			for (i=0; i<=t; i++){
				if(i>0 && e>7){		
					colom = document.getElementsByName("col"+i+"[]");
					value = trim(colom[e].innerHTML);
					if(value=="-" || value=="-  -"){
						value="";
					}
					if(value==""){
						total = total + 1;
					}
				}
			}
			for (i=0; i<=t; i++){
				if(i>0 && e>7){	
					colomH = document.getElementsByName("col0[]");
					colom = document.getElementsByName("col"+i+"[]");
					if(total==t){
						colom[e].style.display='none';
						colomH[e].style.display='none';
					}
				}
			}
		}
		document.getElementById("tombolshow").innerHTML='Show Column';
		document.getElementById("tombolshow").setAttribute('onclick','showandhide(1);');
		// dev_input.setAttribute("onclick","add_device_input(event, this);");

	}
}

/*
function detailkasbank(notransaksi, kodeorg, noakun, tipetransaksi, ev) {
    param = "proses=html&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
        "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun;
    title = "Data Detail";
    // showDialog1(title, "<iframe frameborder=0 style='width:795px;height:400px'" +
        // " src='keu_slave_kasbank_print_detail.php?" + param + "'></iframe>", '800', '400', ev);
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
	alertify.popuppdf("Print PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_slave_kasbank_print_detail.php?" + param + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}
*/

// function printbayar(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, ev) {
//     param = "proses=pdfnew&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
//         "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun + "&cgttu=" + cgttu + "&rek=" + rek;
//     showDialog5('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
//         " src='keu_slave_kasbank_print_bayar.php?" + param + "'></iframe>", '800', '400', ev);
//     var dialog = document.getElementById('dynamic1');
//     // dialog.style.top = '50px';
//     // dialog.style.left = '15%';
// }

// function printbayarall(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, nocekx, ev) {
//     param = "proses=pdfnewall&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
//         "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun + "&cgttu=" + cgttu + "&rek=" + rek + "&nocekx=" + nocekx;
        
//     showDialog5('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
//         " src='keu_slave_kasbank_print_bayar.php?" + param + "'></iframe>", '800', '400', ev);
//     var dialog = document.getElementById('dynamic5');
//     dialog.style.top = ((window.innerHeight/2) - (dialog.offsetHeight/2))+'px';
//   dialog.style.left = ((window.innerWidth/2) - (dialog.offsetWidth/2))+'px';
// }

// function printkasir(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, nocekx, ev) {
//     param = "proses=pdfkasir&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
//         "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun + "&cgttu=" + cgttu + "&rek=" + rek + "&nocekx=" + nocekx;
        
//     showDialog5('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
//         " src='keu_kasdanbank_print.php?" + param + "'></iframe>", '800', '400', ev);
//     var dialog = document.getElementById('dynamic5');
//     dialog.style.top = ((window.innerHeight/2) - (dialog.offsetHeight/2))+'px';
//   dialog.style.left = ((window.innerWidth/2) - (dialog.offsetWidth/2))+'px';
// }

function printbayar(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, nocekx, ev) {

    param = "proses=pdfnew&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
        "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun + "&cgttu=" + cgttu + "&rek=" + rek;

    alertify
    .popuppdf(
        "title",
        "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_slave_kasbank_print_bayar.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
.resizeTo("90%", "80%");
}

function printbayarall(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, nocekx, ev) {

    param = "proses=pdfnewall&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
    "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun + "&cgttu=" + cgttu + "&rek=" + rek + "&nocekx=" + nocekx;

    alertify
    .popuppdf(
        "title",
        "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_slave_kasbank_print_bayar.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
.resizeTo("90%", "80%");
}

function printkasir(notransaksi, kodeorg, noakun, tipetransaksi, cgttu, rek, nocekx, ev) {

    param = "proses=pdfkasir&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg +
    "&tipetransaksi=" + tipetransaksi + "&noakun=" + noakun + "&cgttu=" + cgttu + "&rek=" + rek + "&nocekx=" + nocekx;

    alertify
    .popuppdf(
        "title",
        "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_kasdanbank_print.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
.resizeTo("90%", "80%");
}

function savegantibukti() {
	notransaksi=document.getElementById('notransaksi').value;
	cgttu=document.getElementById('cgttu').value;
	nocek=document.getElementById('nocek').value;
	param = 'notransaksi='+notransaksi+'&method=savegantibukti'+'&cgttu='+cgttu+'&nocek='+nocek;
	if(cgttu==''){
		alert('Dibayar dengan masih kosong');return;
	}
	alertify.confirm("Informasi","Proses penggantian bukti pembayaran : "+notransaksi+" ???",
		function(){
			post_response_text('keu_kasir_slave.php', param, respon);
		},
		function(){
			return;
		}
	);
	function respon(){
		if (con.readyState == 4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    alert('Nomor bukti pembayaran sudah dirubah.');
					getPage();
                }
            }else{
				busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getbuktibayarkasir()
{
	kodeorg=document.getElementById('kodeorg').value;
	rekening=document.getElementById('rekening').value;
	cgttu=document.getElementById('cgttu').value;
	tglbayar=document.getElementById('tglbayar').value;
	
	param = 'rekening='+rekening+'&proses=getbuktibayarkasir'+'&cgttu='+cgttu+'&kodeorg='+kodeorg+'&tglbayar='+tglbayar;
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
					// alert(con.responseText);
                    if(cgttu == 'Transfer') {
                        document.getElementById('nocek').disabled = true;
                        document.getElementById('nocekInput').disabled = false;
                    } else {
                        document.getElementById('nocek').disabled = false;
                        document.getElementById('nocekInput').disabled = true;
                        document.getElementById('nocekInput').value = '';
                        document.getElementById('nocek').innerHTML = con.responseText;
                    }
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


function getbuktibayar() {
    kodeorg = document.getElementById('kodeorg').value;
    rekening = document.getElementById('rekening').value;
    cgttu = document.getElementById('cgttu').value;
    param = 'rekening=' + rekening + '&proses=getbuktibayar' + '&cgttu=' + cgttu + '&kodeorg=' + kodeorg;
    post_response_text('keu_slave_kasbank_detail.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // === Success Response
                    // document.getElementById('nocek').value = con.responseText;

                    document.getElementById('nocek').innerHTML = con.responseText;
                    // if(cgttu=='Transfer'){
                    // document.getElementById('nocek').disabled=false;

                    // }else{
                    // document.getElementById('nocek').disabled=true;
                    // }
                    // getmatauang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatapembayaran() {
    nocek = document.getElementById('nocek').value;
    rekening = document.getElementById('rekening').value;
    cgttu = document.getElementById('cgttu').value;

    param = 'rekening=' + rekening + '&method=loaddatapembayaran' + '&cgttu=' + cgttu + '&nocek=' + nocek;
    post_response_text('keu_slave_kasbank_detail.php', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // === Success Response
                    document.getElementById('containdatapembayaran').innerHTML = con.responseText;
                    // getmatauang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function kasbank(notrans, kodeorg, noakun, tipetransaksi, novoucher, numRow, efill,autokb) {

    var tglpost = trim(document.getElementById('tglbayar').value);
    var file = document.getElementById("upload").files[0];
    // var noakun2a = document.getElementById("noakun2a").files[0];
    // var rekening = document.getElementById("rekening").files[0];

    var noakun2a = trim(document.getElementById('noakun2a').value);
    var rekening = trim(document.getElementById('rekening').value);

    var cgttu = trim(document.getElementById('cgttu').value);
    var nocek = trim(document.getElementById('nocek').value);

    var nocekinput = trim(document.getElementById('nocekInput').value);

    var namabank = trim(document.getElementById('namabank').value);
    var rekeningext = trim(document.getElementById('rekeningext').value);
    var anrekeningext = trim(document.getElementById('anrekeningext').value);

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

    formdata.append("namabank", namabank);
    formdata.append("rekeningext", rekeningext);
    formdata.append("anrekeningext", anrekeningext);

    formdata.append("cgttu", cgttu);
    formdata.append("nocek", nocek);
    formdata.append("nocekinput", nocekinput);
    formdata.append("autokb", autokb);

    formdata.append("efill", efill);

    // alert('a');return;

    if (tglpost == '') {
        alert("Tanggal wajib diisi !!!");
        return;
    }

    // if(efill=='1'){
    // if (getValue('upload') == "") {
    // alert("warning : Upload file harus dilengkapi.");
    // return false;
    // }
    // }

    document.getElementById('tombolsavekasir').disabled = true;

    document.getElementsByClassName("mybutton").disabled = true;
    busy_on();
    var con = createXMLHttpRequest();
	
	
	// jika autokasbank (autokb) = 1 
	if(autokb==1){
		con.open("POST", "keu_slave_kasbank_posting_autokb.php?x=x", true);
	}else{
		con.open("POST", "keu_slave_kasbank_posting.php?x=x", true);
	}
    
	
	con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('tombolsavekasir').disabled = false;
                    closeDialog4();
                    // x=document.getElementById('tr_'+numRow);
                    // x.cells[16].innerHTML='';
                    // loaddata(0);
					// alertify.popup().destroy();
                    
                    pdfkasbankauto(notrans, kodeorg, noakun, tipetransaksi, 'event', cgttu, rekening);

                    getPage();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


// fungsi untuk progress bar
function progressHandler(event) {
    document.getElementById("progressBar").style.display = "block";
    document.getElementById("loaded_n_total").innerHTML =
        "Uploaded " +
        numberFormat(Math.round(event.loaded / 1024)) +
        " KB of " +
        numberFormat(Math.round(event.total / 1024)) +
        " KB";
    var percent = (event.loaded / event.total) * 100;
    document.getElementById("progressBar").value = Math.round(percent);
    document.getElementById("statusbar").innerHTML =
        Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
    document.getElementById("progressBar").style.display = "none";
    document.getElementById("statusbar").innerHTML = event.target.responseText;
    document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
    document.getElementById("statusbar").innerHTML = "Upload Failed";
}
function abortHandler(event) {
    document.getElementById("statusbar").innerHTML = "Upload Aborted";
}

function submitfile() {
    var notransaksi = document.getElementById("notransaksi").value;
    var kriteriaefil = document.getElementById("kriteriaefil").value;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue("upload"));
    formdata.append("notransaksi", trim(notransaksi));
    formdata.append("kriteriaefil", kriteriaefil);
    if (getValue("upload") == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    document.getElementsByClassName("mybutton").disabled = true;
    var con = createXMLHttpRequest();
    //tambahan progress bar
    con.upload.addEventListener("progress", progressHandler, false);
    con.addEventListener("load", completeHandler, false);
    con.addEventListener("error", errorHandler, false);
    con.addEventListener("abort", abortHandler, false);
    //tambahan progress bar -end-
    con.open("POST", "keu_kasdanbank_slave.php?method=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    //=== Success Response
                    document.getElementsByClassName("mybutton").disabled = false;
                    alert("Uploaded Success.");
                    document.getElementById("upload").value = "";
                    detailkasbank(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getInfoKasir(e){
	alertify.alert("Informasi", e);
}