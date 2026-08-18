function loadData(num) {
	param = 'method=loadData';
	param += '&page=' + num;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPage(pg) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loadData(paged);
}
//============ SIMPAN Arus Kas ============
function simpan() {
	noarus = document.getElementById('noarus').value;
	namaarus = document.getElementById('namaarus').value;
	tipetrans = document.getElementById('tipetrans').value;
	pemilik = document.getElementById('pemilik').value;
	level = document.getElementById('level').value;
	indukkas = document.getElementById('indukkas').value;
	tipExpn = document.getElementById('tpExpns');
	tipExpn = tipExpn.options[tipExpn.selectedIndex].value;
	aksesRek = document.getElementById('aksesRek').value;
	status1 = document.getElementById('status1');
	if (status1.checked == true)
		status1 = 1;
	else
		status1 = 0;
	method = document.getElementById('method').value;
	if (level == '' || namaarus == '' || tipetrans == '' || pemilik == '') {
		alertify.alert("Informasi",'Field Was Empty');
		return;
	}
	if (tipetrans == 'K' && level == 3) {
		if (tipExpn == '') {
			alertify.alert("Informasi",'Field Was Empty');
			return;
		}
	}
	param = 'noarus=' + noarus + '&namaarus=' + namaarus + '&tipetrans=' + tipetrans + '&pemilik=' + pemilik + '&method=' + method + '&indukkas=' + indukkas;
	param += '&status1=' + status1 + '&level=' + level + '&tpExpns=' + tipExpn + '&aksesRek=' + aksesRek;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					cancel();
					loadData(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//==========CANCEL / RESET FORM ==================//
function cancel() {
	document.getElementById('noarus').value = '';
	document.getElementById('namaarus').value = '';
	document.getElementById('tipetrans').value = '';
	document.getElementById('tipetrans').disabled = false;
	document.getElementById('pemilik').value = '';
	document.getElementById('level').value = '';
	document.getElementById('level').disabled = false;
	document.getElementById('status1').checked = true;
	document.getElementById('indukkas').value = '';
	document.getElementById('indukkas').disabled = true;
	document.getElementById('tpExpns').value = '';
	document.getElementById('tpExpns').disabled = false;
	document.getElementById('aksesRek').value = '';
	document.getElementById('method').value = 'insert';
}
//==========EDIT FORM ==================//
function edit(noarus, namaarus, tipetrans, pemilik, status1, level, tpexpn, aksesRek) {
	document.getElementById('noarus').value = noarus;
	document.getElementById('noarus').disabled = true;
	document.getElementById('namaarus').value = namaarus;
	document.getElementById('tipetrans').value = tipetrans;
	document.getElementById('tipetrans').disabled = true;
	document.getElementById('pemilik').value = pemilik;
	document.getElementById('level').value = level;
	document.getElementById('level').disabled = true;
	document.getElementById('aksesRek').value = aksesRek;
	if (status1 == '1')
		document.getElementById('status1').checked = true;
	else
		document.getElementById('status1').checked = false;
	document.getElementById('method').value = 'update';
	l = document.getElementById('tpExpns');
	for (a = 0; a < l.length; a++) {
		if (l.options[a].value == tpexpn) {
			l.options[a].selected = true;
		}
	}
}
function formListPP(title, wdth, heig) {
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
function detaildt(noarus_detail) {
	title = "Detail : " + noarus_detail;
	width = '';
	height = '';
	formListPP(title, width, height);
	param = 'noarus_detail=' + noarus_detail;
	tujuan = 'keu_5aruskas_detail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('containerAkun').innerHTML = con.responseText;
					loadDataDetail(noarus_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadDataDetail(noarus_detail) {
	param = 'method=loadDataDetail';
	param += '&noarus_detail=' + noarus_detail;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('containerAkundetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpandetail(noarus_detail) {
	noinduk = document.getElementById('noinduk').value;
	no_arus = document.getElementById('no_arus').value;
	nama_arus = document.getElementById('nama_arus').value;
	tipe_trans = document.getElementById('tipe_trans').value;
	pemilik2 = document.getElementById('pemilik2').value;
	status2 = document.getElementById('status2');
	if (status2.checked == true)
		status2 = 1;
	else
		status2 = 0;
	method = document.getElementById('methodAkun').value;
	if (nama_arus == '' || tipe_trans == '' || pemilik2 == '') {
		alertify.alert("Informasi",'Field Was Empty');
		return;
	}
	param = 'noinduk=' + noinduk + '&no_arus=' + no_arus + '&nama_arus=' + nama_arus + '&tipe_trans=' + tipe_trans + '&pemilik2=' + pemilik2 + '&method=' + method;
	param += '&status2=' + status2;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					canceldetail();
					loadDataDetail(noinduk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editDetail(noinduk, no_arus, nama_arus, tipe_trans, pemilik2, status2) {
	document.getElementById('noinduk').value = noinduk;
	document.getElementById('noinduk').disabled = true;
	document.getElementById('no_arus').value = no_arus;
	document.getElementById('no_arus').disabled = true;
	document.getElementById('nama_arus').value = nama_arus;
	document.getElementById('tipe_trans').value = tipe_trans;
	document.getElementById('pemilik2').value = pemilik2;
	if (status2 == '1')
		document.getElementById('status2').checked = true;
	else
		document.getElementById('status2').checked = false;
	document.getElementById('methodAkun').value = 'updateDetail';
}
function canceldetail() {
	document.getElementById('noinduk').disabled = true;
	document.getElementById('no_arus').disabled = true;
	document.getElementById('nama_arus').value = '';
	document.getElementById('status2').checked = true;
	document.getElementById('methodAkun').value = 'insertDetail';
}
function detailAkun(noakun_detail) {
	title = "Detail Akun Arus Kas : " + noakun_detail;
	// width = '';
	// height = '';
	// formListPP(title, width, height);
	
	param = 'noakun_detail=' + noakun_detail;
	tujuan = 'keu_5aruskas_detail_noakun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//document.getElementById('containerAkun').innerHTML = con.responseText;
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('700px','500px');
					loadDataAkun(noakun_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadDataAkun(noakun_detail) {
	param = 'method=loadDataAkun';
	param += '&noakun_detail=' + noakun_detail;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('containerNoakun').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



/*

function simpanNoakun(noakun_detail) {
	nomorarus = document.getElementById('nomorarus').value;
	method = document.getElementById('methodNoakun').value;
	totrow = document.getElementById('totrow').value;
	if (nomorarus == '') {
		alert('Field May Not Empty.');
		return;
	}
	var allData = '';
	for (dwc = 1; dwc <= totrow; dwc++) {
		allData += "&noakundt[" + dwc + "]=" + document.getElementById('noakundt_' + dwc).innerHTML;
		if (document.getElementById('checkakun_' + dwc).checked == true) {
			allData += "&checkakun[" + dwc + "]=" + 1;
		} else {
			allData += "&checkakun[" + dwc + "]=" + 0;
		}
	}
	param = 'nomorarus=' + nomorarus + '&method=' + method + '&totrow=' + totrow;
	param += allData;
	
	
	console.log(param);
	
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					detailAkun(nomorarus);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

*/



//###############################################################################

// simpanNoakun

function simpanNoakunx(no){
	//noakundt =trim(document.getElementById('noakundt_'+no).innerHTML);
	// checkakun=document.getElementById('checkakun_'+no);
	nomorarus= document.getElementById('nomorarus').value;
	method   = document.getElementById('methodNoakun').value;
	// if (checkakun.checked == true){
		// checkakun = 1;
	// } else {
		// checkakun = 0;
	// }
	if (nomorarus == '') {
		alertify.alert("Informasi",'Field May Not Empty.');
		return;
	}
	
	param = 'nomorarus=' + nomorarus + '&method=simpandata';
	for (i=1;i<=no;i++){
		checkakun = document.getElementById('checkakun_'+i);
		if (checkakun.checked == true){
			noakundt =trim(document.getElementById('noakundt_'+i).innerHTML);
			param+='&noakundt['+i+']='+noakundt;
		}
	}
	
	
	//param+='&noakundt='+noakundt;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
                }else {
					//alertify.alert("Informasi",'Done');
					detailAkun(nomorarus);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }
    }			
}









maxf=0
sekarang=1;
function simpanNoakun(maxRow){ 
    maxf=maxRow;	    
	loopsave(1,maxRow);
}




function loopsave(currRow,maxRow){
    //alert(currRow);return;
	noakundt =trim(document.getElementById('noakundt_'+currRow).innerHTML);
	checkakun=document.getElementById('checkakun_'+currRow);
	nomorarus= document.getElementById('nomorarus').value;
	method   = document.getElementById('methodNoakun').value;
	if (checkakun.checked == true){
		checkakun = 1;
	} else {
		checkakun = 0;
	}
	if (nomorarus == '') {
		alertify.alert("Informasi",'Field May Not Empty.');
		return;
	}
	
	param = 'nomorarus=' + nomorarus + '&method=' + method;
	param+='&noakundt='+noakundt+'&checkakun='+checkakun;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
	//lockScreen('wait');
   
    function respog(){
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert("Informasi",con.responseText);
                        document.getElementById('row'+currRow).style.backgroundColor='red';
                   unlockScreen();
                }
                else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow)
                    {
						alertify.alert("Informasi",'Done');
					   detailAkun(nomorarus);
                    }  
                    else
                    {
						loopsave(currRow,maxRow);
                    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
               // document.getElementById('lanjut').style.display='';
                //unlockScreen();
            }
        }
    }		
	
}






//###############################################################################








function delDataakun(noakun_detail, noakundt) {
	param = 'method=deleteakundt' + '&noakun_detail=' + noakun_detail + '&noakundt=' + noakundt;
	tujuan = 'keu_slave_5aruskas.php';
	// if (confirm(' Anda yakin ingin menghapus noakun ini?')) {
	// 	post_response_text(tujuan, param, respog);
	// }
	alertify.confirm("Infomation","Anda yakin ingin menghapus noakun ini?",
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
					alertify.alert("Informasi",con.responseText);
				} else {
					detailAkun(noakun_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editNoakun(nomorarus, noakun) {
	document.getElementById('nomorarus').value = noinduk;
	document.getElementById('nomorarus').disabled = true;
	document.getElementById('noakun').value = no_arus;
	document.getElementById('noakun').disabled = true;
	document.getElementById('methodNoakun').value = 'updateAkun';
}
function forminduk() {
	level = document.getElementById('level').value;
	tipetrans = document.getElementById('tipetrans').value;
	param = 'method=forminduk';
	param += '&level=' + level + '&tipetrans=' + tipetrans;
	tujuan = 'keu_slave_5aruskas.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					if (level == 3) {
						document.getElementById('indukkas').innerHTML = con.responseText;
						document.getElementById('indukkas').disabled = false;
						if (tipetrans == 'K') {
							document.getElementById('tpExpns').disabled = false;
							document.getElementById('tpExpns').value = '';
						}
					} else {
						document.getElementById('indukkas').disabled = true;
						document.getElementById('tpExpns').disabled = true;
						document.getElementById('tpExpns').value = '';
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}