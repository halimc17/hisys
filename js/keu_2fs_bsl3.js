function getbulanytd(idsumber,idhasil){
	sumber = document.getElementById(idsumber).value;
	document.getElementById(idhasil).value = sumber;
}
function getEstate6() {
	pt = document.getElementById('pt6').options[document.getElementById('pt6').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getEstate';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('kdorg6').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getEstate5() {
	pt = document.getElementById('pt5').options[document.getElementById('pt5').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getEstate';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('kdorg5').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getEstate4() {
	pt = document.getElementById('pt4').options[document.getElementById('pt4').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getEstate';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('kdorg4').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getEstate3() {
	pt = document.getElementById('pt3').options[document.getElementById('pt3').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getEstate';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('kdorg3').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getEstate2() {
	pt = document.getElementById('pt2').options[document.getElementById('pt2').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getEstate';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('kdorg2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function form() {
	width = 'auto';
	height = 'auto';
	content = "<fieldset><div id=container_detail></div></fieldset>";
	ev = 'event';
	title = 'Detail';
	showDialog1(title, content, width, height, ev);
}
function viewdetail(nourut,pt,kdorg, periode1,periode2,periode3,periodesd1,periodesd2,periodesd3,tipe,jenis,periode4,periodesd4,periodeytd,periodesdytd) {
	param = 'pt=' + pt + '&nourut=' + nourut + '&kdorg=' + kdorg+ '&tipe=' + tipe;
	param += '&periode1=' + periode1 + '&periode2=' + periode2 + '&periode3=' + periode3;
	param += '&periodesd1=' + periodesd1 + '&periodesd2=' + periodesd2 + '&periodesd3=' + periodesd3;
	param += '&periode4=' + periode4 + '&periodesd4=' + periodesd4;
	param += '&periodeytd=' + periodeytd + '&periodesdytd=' + periodesdytd;
	if(jenis=='bsl3'){
		param += '&proses=viewdetail_bsl3';
	}else if(jenis=='bsl4'){
		param += '&proses=viewdetail_bsl4';
	}else if(jenis=='pl'){
		param += '&proses=viewdetail_pl';
	}else if(jenis=='cogs'){
		param += '&proses=viewdetail_cogs';
	}else if(jenis=='ga'){
		param += '&proses=viewdetail_ga';
	}else if(jenis=='no'){
		param += '&proses=viewdetail_no';
	}
	tujuan = 'keu_slave_2fs_detail.php';
	ev = 'event';
	judul = 'Detail';
	if(tipe=='excel'){
		printFile(param, tujuan, judul, ev);
	}else{
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						form();
						document.getElementById('container_detail').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}
function pdf(nourut,pt,kdorg, periode1,periode2,periode3,periodesd1,periodesd2,periodesd3,tipe,jenis){
	param = 'pt=' + pt + '&nourut=' + nourut + '&kdorg=' + kdorg+ '&tipe=' + tipe;
	param += '&periode1=' + periode1 + '&periode2=' + periode2 + '&periode3=' + periode3;
	param += '&periodesd1=' + periodesd1 + '&periodesd2=' + periodesd2 + '&periodesd3=' + periodesd3;
	if(jenis=='bsl3'){
		param += '&proses=viewdetail_bsl3';
	}else if(jenis=='bsl4'){
		param += '&proses=viewdetail_bsl4';
	}else if(jenis=='pl'){
		param += '&proses=viewdetail_pl';
	}else if(jenis=='cogs'){
		param += '&proses=viewdetail_cogs';
	}else if(jenis=='ga'){
		param += '&proses=viewdetail_ga';
	}else if(jenis=='no'){
		param += '&proses=viewdetail_no';
	}
	tujuan = 'keu_slave_2fs_detail.php?' + param;
	title = '';
	width = '1000';
	height = '400';
	ev = 'event';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}