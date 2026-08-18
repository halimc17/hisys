function preview1() {
	perusahaan = document.getElementById('perusahaan1').options[document.getElementById('perusahaan1').selectedIndex].value;
	periode = document.getElementById('periode1').options[document.getElementById('periode1').selectedIndex].value;

	param = 'method=preview1&perusahaan=' + perusahaan + '&periode=' + periode;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container1').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview2() {
	perusahaan = document.getElementById('perusahaan2').options[document.getElementById('perusahaan2').selectedIndex].value;
	periode = document.getElementById('periode2').options[document.getElementById('periode2').selectedIndex].value;

	param = 'method=preview2&perusahaan=' + perusahaan + '&periode=' + periode;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container2').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview3() {
	perusahaan = document.getElementById('perusahaan3').options[document.getElementById('perusahaan3').selectedIndex].value;
	periode = document.getElementById('periode3').options[document.getElementById('periode3').selectedIndex].value;
	tipe = document.getElementById('tipe3').options[document.getElementById('tipe3').selectedIndex].value;

	param = 'method=preview3&perusahaan=' + perusahaan + '&periode=' + periode + '&tipe=' + tipe;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container3').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview4() {
	perusahaan = document.getElementById('perusahaan4').options[document.getElementById('perusahaan4').selectedIndex].value;
	periode = document.getElementById('periode4').options[document.getElementById('periode4').selectedIndex].value;

	param = 'method=preview4&perusahaan=' + perusahaan + '&periode=' + periode;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container4').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview5() {
	perusahaan = document.getElementById('perusahaan5').options[document.getElementById('perusahaan5').selectedIndex].value;
	periode = document.getElementById('periode5').options[document.getElementById('periode5').selectedIndex].value;

	param = 'method=preview5&perusahaan=' + perusahaan + '&periode=' + periode;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container5').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview6() {
	perusahaan = document.getElementById('perusahaan6').options[document.getElementById('perusahaan6').selectedIndex].value;
	periode = document.getElementById('periode6').options[document.getElementById('periode6').selectedIndex].value;

	param = 'method=preview6&perusahaan=' + perusahaan + '&periode=' + periode;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container6').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview7() {
	perusahaan = document.getElementById('perusahaan7').options[document.getElementById('perusahaan7').selectedIndex].value;
	periode = document.getElementById('periode7').options[document.getElementById('periode7').selectedIndex].value;
	tipe = document.getElementById('tipe7').options[document.getElementById('tipe7').selectedIndex].value;

	param = 'method=preview7&perusahaan=' + perusahaan + '&periode=' + periode + '&tipe=' + tipe;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container7').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview8() {
	perusahaan = document.getElementById('perusahaan8').options[document.getElementById('perusahaan8').selectedIndex].value;
	periode = document.getElementById('periode8').options[document.getElementById('periode8').selectedIndex].value;

	param = 'method=preview8&perusahaan=' + perusahaan + '&periode=' + periode;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container8').innerHTML = con.responseText;
					// //leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

// Umar
function preview9() {
	perusahaan = document.getElementById('perusahaan9').options[document.getElementById('perusahaan9').selectedIndex].value;
	periode = document.getElementById('periode9').options[document.getElementById('periode9').selectedIndex].value;

	param = 'method=preview9&perusahaan=' + perusahaan + '&periode=' + periode;
	tujuan = 'slave_monitoring_transaksi.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container9').innerHTML = con.responseText;
					//leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

// function detail3(unit, tgl, tipe, posting, proses, ev) {
	// param = 'unit=' + unit + '&tgl=' + tgl + '&proses=' + proses + '&posting=' + posting + '&tipe=' + tipe;
	// tujuan = 'slave_monitoring_transaksi_popup.php' + "?" + param;
	// // width = '700';
	// // height = '350';
	// // content = "<fieldset style='height:95%;width:97%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	// // showDialog5('Detail Transaksi ' + proses, content, width, height, ev);
	
	// alertify.popuppdf("Detail "+ proses,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='slave_monitoring_transaksi_popup.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
// }

// function detail4(unit, tgl, tipe, posting, proses, ev) {
	// param = 'unit=' + unit + '&tgl=' + tgl + '&proses=' + proses + '&posting=' + posting + '&tipe=' + tipe;
	// tujuan = 'slave_monitoring_transaksi_popup.php' + "?" + param;
	// width = '700';
	// height = '350';
	// content = "<fieldset style='height:95%;width:97%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	// //showDialog5('Detail Transaksi ' + proses, content, width, height, ev);
	
	// alertify.popuppdf("Detail "+ proses,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='slave_monitoring_transaksi_popup.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');	
// }

// function popup(unit,tgl,proses,posting){
	// param = 'unit=' + unit + '&tgl=' + tgl + '&proses=' + proses + '&posting=' + posting;
	// tujuan = 'slave_monitoring_transaksi_popup.php' + "?" + param;
	// // width = '1100';
	// // height = '350';
	// // ev = 'event';
	// // content = "<fieldset style='height:96%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	// // showDialog1('Detail Transaksi ' + proses, content, width, height, ev);
	
	// alertify.popuppdf("Detail "+ proses,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='slave_monitoring_transaksi_popup.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
// }

function popup(unit,tgl,proses,posting){
	param = 'unit=' + unit + '&tgl=' + tgl + '&proses=' + proses + '&posting=' + posting;
	tujuan = 'slave_monitoring_transaksi_popup.php' + "?" + param;
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detail4(unit, tgl, tipe, posting, proses, ev) {
	param = 'unit=' + unit + '&tgl=' + tgl + '&proses=' + proses + '&posting=' + posting + '&tipe=' + tipe;
	tujuan = 'slave_monitoring_transaksi_popup.php' + "?" + param;
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detail3(unit, tgl, tipe, posting, proses, ev) {
	param = 'unit=' + unit + '&tgl=' + tgl + '&proses=' + proses + '&posting=' + posting + '&tipe=' + tipe;
	tujuan = 'slave_monitoring_transaksi_popup.php' + "?" + param;
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}