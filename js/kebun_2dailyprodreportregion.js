function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
	}	
}

function getdetail(code, periode, regional, kodeorg){	
	title  = "Detail";
	param  = 'proses=getdetailprdvsact';
	param += '&code=' + code;
	param += '&prd=' + periode;
	param += '&regional=' + regional;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_2panenvsrawat.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function showbaris(name){
	var e = document.getElementsByName(name+'[]');
	for (i=0; i<e.length; i++){
		if(e[i].style.display=='none'){
			e[i].style.display='';
		}else{
			e[i].style.display='none';
		}
	}
}

function getkapasitas(value){
	if(value=='KAPUAS'){
		document.getElementById('kapasitas').value='60';
	}else if(value!=''){
		document.getElementById('kapasitas').value='40';
	}else{
		document.getElementById('kapasitas').value='140';
	}
}

function detailpnnkg(notransaksi, kegiatan, blok, karyawanid,nojurnal){
	param  = 'proses=detailpnnkg';
	param += '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	param += '&notransaksi=' + notransaksi;
	param += '&karyawanid=' + karyawanid;
	param += '&nojurnal=' + nojurnal;
	
	tujuan = 'kebun_slave_2panenvsrawat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detailpnnjjg(notransaksi, kegiatan, blok, karyawanid){
	
	param = "proses=html&tipe=PNN&notransaksi=" + notransaksi+ "&nobkm=" + notransaksi;
	param += '&blok=' + blok;
	param += '&karyawanid=' + karyawanid;
	param += '&tampil=1';
	
	tujuan = 'kebun_slave_operasional_print_detail_panenxn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					alertify.popup2().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailData(notransaksi,kegiatan,blok,tipe,jenis){
	param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis="+jenis;
	param += '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	tujuan = 'kebun_slave_operasional_print_detailx.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
                    alertify.popup2(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function detailVhc(kodevhc, kegiatan, blok, tanggal){
	param  = 'proses=detailvhc';
	param += '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	param += '&tanggal=' + tanggal;
	param += '&kodevhc=' + kodevhc;
	
	tujuan = 'kebun_slave_2panenvsrawat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Detail VHC",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getproduksi(periodetahun, unittahun, intiplasmatahun){
	param  = 'proses=preview';
	param += '&unittahun=' + unittahun;
	param += '&intiplasmatahun=' + intiplasmatahun;
	param += '&periodetahun=' + periodetahun;
	
	tujuan = 'kebun_slave_3laporanProduksi3.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Detail VHC",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettonperha(pt2, kdorg2, per2,ip){
	param  = 'proses=preview';
	param += '&pt2=' + pt2;
	param += '&kdorg2=' + kdorg2;
	param += '&per2=' + per2;
	param += '&ip=' + ip;
	
	tujuan = 'kebun_slave_2rpp_v2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Detail VHC",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function getdetailprd(blok, periode){
	title  = "Detail";
	param  = 'proses=detail';
	param += '&blok=' + blok;
	param += '&periode=' + periode;
	tujuan = 'kebun_slave_3laporanProduksi_detail.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

// function getdetail(pt,kdorg,tt,ip,divisi,prd,tipe,akun,jenis,bi,real) {
	// // form();
	// param  = 'method=html';
	// param += '&pt=' + pt;
	// param += '&kdorg=' + kdorg;
	// param += '&tt=' + tt;
	// param += '&ip=' + ip;
	// param += '&divisi=' + divisi;
	// param += '&prd=' + prd;
	// param += '&tipe=' + tipe;
	// param += '&akun=' + akun;
	// param += '&jenis=' + jenis;
	// param += '&bi=' + bi;
	// param += '&real=' + real;
	// tujuan = 'kebun_slave_consolbyyproduksi_popup.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alertify.alert(con.responseText);
				// } else {
					// // document.getElementById('containerd').innerHTML = con.responseText;
					// alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }
function getdetailexcel(pt,kdorg,tt,ip,divisi,prd,tipe,akun,jenis,bi,real) {
	ev='event';
	param  = 'method=excel';
	param += '&pt=' + pt;
	param += '&kdorg=' + kdorg;
	param += '&tt=' + tt;
	param += '&ip=' + ip;
	param += '&divisi=' + divisi;
	param += '&prd=' + prd;
	param += '&tipe=' + tipe;
	param += '&akun=' + akun;
	param += '&jenis=' + jenis;
	param += '&bi=' + bi;
	param += '&real=' + real;
	
	printnopopup("kebun_slave_consolbyyproduksi_popup.php?" + param);
}