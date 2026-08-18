function copy() {
	prdawal = document.getElementById('prdawal').value;
	afd = document.getElementById('unitcopy').value;
	prdakhir = document.getElementById('prdakhir').value;
	
	param = 'method=copy';
	param += '&afd=' + afd;
	param += '&prdawal=' + prdawal;
	param += '&prdakhir=' + prdakhir;
	tujuan = 'kebun_slave_5premikutipbrondolansaja.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert("Proses is Successful");
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariBast(num) {
	afd       = document.getElementById('unitsrc').value;
	tahun     = document.getElementById('tahunsrc').value;
	tt        = document.getElementById('ttsrc').value;
	jenispremi= document.getElementById('jenissrc').value;
	topografi = document.getElementById('topografisrc').value;
	
	param = 'method=loadData';
	param += '&page=' + num;
	param += '&afd=' + afd;
	param += '&tahun=' + tahun;
	param += '&tt=' + tt;
	param += '&jenispremi=' + jenispremi;
	param += '&topografi=' + topografi;
	tujuan = 'kebun_slave_5premikutipbrondolansaja.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function simpan(fileTarget, passParam) {
	param = '';
	var passP = passParam.split('##');
	for (i = 1; i < passP.length; i++) {
		var tmp = document.getElementById(passP[i]);
		if (i == 1) {
			param += passP[i] + "=" + getValue(passP[i]);
		} else {
			param += "&" + passP[i] + "=" + getValue(passP[i]);
		}
	}

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadData();
					cancelIsi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(fileTarget + '.php', param, respon);
}

function loadData() {
	afd       	= document.getElementById('unitsrc').value;
	tahun     	= document.getElementById('tahunsrc').value;
		
	param = 'method=loadData';
	param += '&afd=' + afd;
	param += '&tahun=' + tahun;

	tujuan = 'kebun_slave_5premikutipbrondolansaja';
	post_response_text(tujuan + '.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function fillField(afd, tahun, basiskg, brondol) {
	document.getElementById('afd').value = afd;
	document.getElementById('afd').disabled = true;
	setValue2('afd',afd);
	document.getElementById('tahun').value = tahun;
	document.getElementById('tahun').disabled = true;
	document.getElementById('basiskg').value = basiskg;
	document.getElementById('brondol').value = brondol;
	document.getElementById('method').value = "update";
}

function del(afd,tahun) {
	param = 'afd=' + afd; 
	param += '&tahun=' + tahun;
	param += '&method=deletedata';
	tujuan = 'kebun_slave_5premikutipbrondolansaja.php';
	if (confirm("Are You Sure Want Delete Data?"))
		post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadData();
					cancelIsi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancelIsi() {
	document.getElementById('afd').disabled = false;
	document.getElementById('basiskg').value = 0;
	document.getElementById('brondol').value = 0;
	document.getElementById('tahun').disabled = false;
	document.getElementById('method').value = "insert";
	setValue2('afd',null);
}

function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function form_ajukan(unit,periode,kodeorg) {
	param = 'method=form_ajukan' + '&unit=' + unit + '&periode=' + periode + '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_5premikutipbrondolansaja.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('find_stat').value=stats;
					//alertify.popup().set({onshow:function(){loaddata()}}); 
					alertify.popup("Approval, Unit "+unit,con.responseText).set({
						'resizable':true,
						'maximizable':false
					}).resizeTo('300px','290px'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan(jenispersetujuan,periode,kodeorg){	
	unit       = document.getElementById('unitajukan').value;
	nopengajuan= document.getElementById('nopengajuan').innerHTML;
	kepada     = document.getElementById('kepada').value;
	komentar   = document.getElementById('komentar').value;
	
	param = "";
	param += '&unit=' + unit;
	param += '&periode=' + periode;
	param += '&kodeorg=' + kodeorg;
	param += '&jenispersetujuan=' + jenispersetujuan;
	param += '&nopengajuan=' + nopengajuan;
	param += '&kepada=' + kepada;
	param += '&komentar=' + komentar;
	param += '&method=ajukan';
	
	tujuan = 'kebun_slave_5premikutipbrondolansaja.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.set('notifier','position', 'top-center');
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					//document.getElementById('find_stat').value='9';
					//document.getElementById('find_nope').value=nopengajuan;
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdatapengajuan(nopengajuan){	
	param = "";
	param += '&nopengajuan=' + nopengajuan;
	param += '&method=getdatapengajuan';
	
	tujuan = 'kebun_slave_5premikutipbrondolansaja.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.set('notifier','position', 'top-center');
					alertify.warning(con.responseText);
				} else {
					alertify.popup("Detail Approval nomor : "+nopengajuan,con.responseText).set({
						'resizable':true,
						'maximizable':true
					}).resizeTo('70%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}