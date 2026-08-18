function fisikKeExcel2(ev, tujuan) {
	pt = document.getElementById('pt');
	unit = document.getElementById('gudang');
	periode = document.getElementById('periode');
	pt = pt.options[pt.selectedIndex].value;
	unit = unit.options[unit.selectedIndex].value;
	periode = periode.options[periode.selectedIndex].value;
	akundari = document.getElementById('akundari');

	param = 'pt=' + pt + '&unit=' + unit + '&periode=' + periode;
	judul = 'Report Ms.Excel';
	//param='pt='+pt+'&gudang='+gudang+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
	printFile(param, tujuan, judul, ev)
}

function fisikKePDF(ev, tujuan) {
	pt = document.getElementById('pt');

	gudang = document.getElementById('gudang');
	periode = document.getElementById('periode');
	revisi = '';
	try {
		periode1 = document.getElementById('periode1').options[document.getElementById('periode1').selectedIndex].value;
		revisi = document.getElementById('revisi');
		revisi = revisi.options[revisi.selectedIndex].value;
	} catch (err) {
		periode1 = '';
	}
	pt = pt.options[pt.selectedIndex].value;
	gudang = gudang.options[gudang.selectedIndex].value;
	periode = periode.options[periode.selectedIndex].value;

	regional = document.getElementById('regional');
	if (regional)
		regional = regional.options[regional.selectedIndex].value;

	kdKel = document.getElementById('kdKel');
	ref = document.getElementById('ref');
	ket = document.getElementById('ket');
	ref = (ref) ? ref.value : ref = '';
	ket = (ket) ? ket.value : ket = '';
	nojurnal = document.getElementById('nojurnal');

	if (pt == '') {
		alertify.alert("Informasi",'Field PT empty!');
		return;
	}

	tampilanId = document.getElementById('tampilanId').options[document.getElementById('tampilanId').selectedIndex].value;
	judul = 'Report PDF';
	param = 'pt=' + pt + '&gudang=' + gudang + '&periode=' + periode + '&periode1=' + periode1 + '&revisi=' + revisi;
	param += '&ref=' + ref + '&ket=' + ket + '&tampilanId=' + tampilanId;

	param += '&regional=' + regional;
	if (kdKel) {
		param += '&kdKel=' + kdKel.value;
	}
	if (nojurnal) {
		param += '&nojurnal=' + nojurnal.value;
	}

	akundari = document.getElementById('akundari');
	if (akundari) {
		param += '&akundari=' + akundari.value;
	}
	akunsampai = document.getElementById('akunsampai');
	if (akunsampai) {
		param += '&akunsampai=' + akunsampai.value;
	}

	printFile(param, tujuan, judul, ev)
}

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	// width = '900';
	// height = '400';
	// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1(title, content, width, height, ev);
	alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}




function getLaporanKeuanganLabaRugiv1() {
	pt = document.getElementById('pt');
	unit = document.getElementById('gudang');
	periode = document.getElementById('periode');
	pt = pt.options[pt.selectedIndex].value;
	unit = unit.options[unit.selectedIndex].value;
	periode = periode.options[periode.selectedIndex].value;
	param = 'pt=' + pt + '&unit=' + unit + '&periode=' + periode;
	tujuan = 'keu_slave_2laporankeuanganLabaRugiv1.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					showById('printPanel');
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getLaporanKeuanganLabaRugiv1() {
	pt = document.getElementById('pt');
	unit = document.getElementById('gudang');
	periode = document.getElementById('periode');
	pt = pt.options[pt.selectedIndex].value;
	unit = unit.options[unit.selectedIndex].value;
	periode = periode.options[periode.selectedIndex].value;
	param = 'pt=' + pt + '&unit=' + unit + '&periode=' + periode;
	tujuan = 'keu_slave_2laporankeuanganLabaRugiv1.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					showById('printPanel');
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getLaporanKeuanganDetailv2(nourut, tipe) {
	no_nourut='no_'+nourut;

	pt = document.getElementById('pt');
	unit = document.getElementById('gudang');
	periode = document.getElementById('periode');
	pt = pt.options[pt.selectedIndex].value;
	unit = unit.options[unit.selectedIndex].value;
	periode = periode.options[periode.selectedIndex].value;

	param = 'pt=' + pt + '&unit=' + unit + '&periode=' + periode + '&nourut=' + nourut + '&tipe=' + tipe;
	tujuan = 'keu_slave_2laporankeuangan_detailv2.php';

	document.getElementById(no_nourut).innerHTML = '';
	status = document.getElementById(no_nourut).style.display;
	if (status == 'none') {
		document.getElementById(no_nourut).style.display = 'block';
		post_response_text(tujuan, param, respog);
	} else {
		document.getElementById(no_nourut).style.display = 'none';
	}
	//    post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					//                    showById('printPanel');
					document.getElementById(no_nourut).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function previewhpp(){
    unit=trim(document.getElementById('pt').value);
    per=trim(document.getElementById('periode').value);
	tipe='html';
	laporan='1';
	param='method=preview'+'&unit='+unit+'&per='+per+'&tipe='+tipe+'&laporan='+laporan;
	tujuan='keu_slave_3hpp.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {	
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}
