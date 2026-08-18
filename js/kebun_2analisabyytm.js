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
function detailExcel(notransaksi, numRow, ev) {
	param = "proses=excel&tipe=PNN" + "&notransaksi=" + notransaksi;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_operasional_print_detail_panen.php?" + param + "'></iframe>", '900', '400', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
function detailData(notransaksi, numRow, ev, tipe) {
	param = "proses=html&tipe=" + tipe + "&notransaksi=" + notransaksi;
	title = "Data Detail";
	showDialog1(title, "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_operasional_print_detail_panen.php?" + param + "'></iframe>", '', '', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}

function form() {
	width = '';
	height = '';
	content = "<fieldset><div id=containerd style=\"max-height:450px;max-width:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}

function getdetail(pt,kdorg,tt,ip,divisi,prd,tipe,akun,jenis,bi,real) {
	// form();
	param  = 'method=html';
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
	tujuan = 'kebun_slave_2analisabyytm_popup.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
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
	
	printnopopup("kebun_slave_2analisabyytm_popup.php?" + param);
	
	//showDialog1('Report Ms.Excel', "<iframe frameborder=0 style='width:895px;height:400px'" +" src='kebun_slave_2analisabyytm_popup.php?" + param + "'></iframe>", '900', '400', ev);
}

function clickdetailreff(jenis, noref, nojurnal, kodeorg, tgldari, tglsampai, kodeblok){
	param = "";
	pdf   = false;
	switch(jenis){
		case 'SPK':
			param  = "notransaksi=" + noref;
			param += "&sumber=popupdet";
			param += "&kodeorg=" + kodeorg;
			param += "&tanggal=" + tgldari;
			param += "&kodekegiatan=" + tglsampai;
			param += "&kodeblok=" + kodeblok;
			param += "&method=preview";
			tujuan = 'log_slave_realisasispkx.php';
			title  = 'BA SPK';
		break;
		case 'PNN19':
			if(tgldari.substr(8,2)=='01'){
				periodebyr=1;
			}else{
				periodebyr=2;
			}
			param  = "notransaksi=" + noref;
			param += "&periodebyr=" + periodebyr;
			param += "&kodeorg=" + kodeorg;
			param += "&divisi=" + kodeblok.substr(0,6);
			param += "&kodeblok=" + kodeblok;
			param += "&periode=" + tgldari.substr(0,7);
			param += "&method=html";
			param += "&jenis=html";
			tujuan = 'kebun_slave_3fee.php';
			title  = 'Kebun - Proses - Biaya Admin Panen';
		break;
		case 'GI':
			param  = "notransaksi=" + noref;
			tujuan = 'log_slave_print_bast.php';
			title  = 'Bukti Pengeluaran Barang';
		break;
		case 'KB':
			param  = "notransaksi=" + noref;
			param += "&method=formajukan";
			tujuan = 'keu_kasdanbank_slave.php';
			title  = 'Keuangan - Transaksi - Kas dan Bank';
		break;
		case 'JM':
			param  = "nojurnal_0=" + nojurnal;
			param += "&mode=pdf";
			param += "&level=1";
			tujuan = 'keu_slave_jurnal_print.php';
			title  = 'Keuangan - Transaksi - Jurnal Memorial';
			pdf    = true;
			
		break;
	}
	
	if(pdf==true){
		alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"?"+param+"'></iframe>").set({'title':title,'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
	}else if(pdf==false){		
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert('Info',con.responseText);
					} else {
						if(pdf==true){
							
						}else{							
							alertify.popup2().destroy();
							alertify.popup2().set({'resizable':true,'maximizable':true,'startMaximized':false,'title':title,'message':con.responseText}).resizeTo('80%','70%').show();
						}
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}


function viewDetail1(kegiatan, blok, periode,ev){
	title  = "Detail";
	param  = 'type=preview';
	param += '&kodekegiatan=' + kegiatan;
	param += '&kodeorg=' + blok;
	param += '&bulan=' + periode;
	tujuan = 'kebun_slave_2pemeliharaan1_detail.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
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
