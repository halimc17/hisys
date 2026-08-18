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

function getkegiatan() {
	// form();
	param  = 'proses=getKegiatan';
	param += '&klp=' + getValue('klp');
	
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('keg').innerHTML = "";
					document.getElementById('keg').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

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
function Excel() {
	param  = 'proses=preview';
	param += '&pt=' + getValue('pt');
	param += '&kdorg=' + getValue('kdorg');
	param += '&tt=' + getValue('tt');
	param += '&divisi=' + getValue('divisi');
	param += '&prd=' + getValue('prd');
	param += '&klp=' + getValue('klp');
	param += '&keg=' + $('#keg').val();
	
	param = "proses=excel&tipe=PNN" + "&notransaksi=" + notransaksi;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_2panenvsrawat.php?" + param + "'></iframe>", '900', '400', ev);
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
function preview(tipe) {
	param  = 'proses='+tipe;
	param += '&pt=' + getValue('pt');
	param += '&kdorg=' + getValue('kdorg');
	param += '&tt=' + getValue('tt');
	param += '&divisi=' + getValue('divisi');
	param += '&prd=' + getValue('prd');
	param += '&klp=' + getValue('klp');
	param += '&tampil=' + getValue('tampil');
	param += '&bulanini=' + getValue('bulanini');
	param += '&jenis=' + getValue('jenis');
	param += '&ip=' + getValue('ip');
	param += '&keg=' + $('#keg').val();
	param += '&blok=' + getValue('blok');
	tujuan = 'kebun_slave_2panenvsrawat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='excel'){
						e=tujuan+'?'+ param;
						printnopopup(e);
					}else{						
						document.getElementById('printContainer').innerHTML = con.responseText;
						leftFixedTable();
						setTimeout(function(){
							showheader();
						}, 100);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getisikegiatan(e){
	if(e.value=='fisik'){
		$('#keg').val(["621010108","621011301","621010106","621010125","621010111"]).trigger("change");
		$('#bulanini').val("hide").trigger("change");
		$('#tampil').val("3").trigger("change");
	}
}

function getDetail(kegiatan, blok, periode,tipe){
	jenis = document.getElementById('jenis').value;
	if(jenis=='fisik'){
		title="Detail";
		param  = 'proses=preview';
		param += '&kegiatan1=' + kegiatan;
		param += '&kdOrg1=' + blok.substr(0,4);
		param += '&kdAfd1=' + blok;
		param += '&tahun1=' + periode.substr(0,4);
		param += '&periode=' + periode;
		param += '&tipe=' + tipe;
		tujuan = 'kebun_slave_2pemeliharaan1.php';
		
	}else{		
		title="Detail Jurnal";
		param  = 'proses=getdetail';
		param += '&kegiatan=' + kegiatan;
		param += '&blok=' + blok;
		param += '&prd=' + periode;
		param += '&tipe=' + tipe;
		tujuan = 'kebun_slave_2panenvsrawat.php';
	}
	
	
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

function Excel() {
	param  = 'proses=excel';
	param += '&pt=' + getValue('pt');
	param += '&kdorg=' + getValue('kdorg');
	param += '&tt=' + getValue('tt');
	param += '&divisi=' + getValue('divisi');
	param += '&prd=' + getValue('prd');
	param += '&klp=' + getValue('klp');
	param += '&keg=' + $('#keg').val();
	
	tujuan = 'kebun_slave_2panenvsrawat.php?';
	printnopopup(tujuan+ param);
	// post_response_text(tujuan, param, respog);
	
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

function getBlokBig(){
	param  = 'proses=getBlokBig';
	param += '&divisi=' + getValue('divisi');
	param += '&tt=' + getValue('tt');
	
	tujuan = 'kebun_slave_2panenvsrawat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('blok').innerHTML = con.responseText
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}