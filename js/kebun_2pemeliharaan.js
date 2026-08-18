function getbarang(kegiatan) {
    param = '';
    param += '&kegiatan=' + kegiatan;
    param += '&proses=getbarang';
    tujuan = 'kebun_slave_2pemeliharaan.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('kdbarang').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function viewDetail1(kodekegiatan, kodeorg, bulan, ev) {
    // param = 'kodekegiatan=' + kodekegiatan + '&kodeorg=' + kodeorg + '&bulan=' + bulan;

    // tujuan = 'kebun_slave_2pemeliharaan1_detail.php' + "?" + param;
    // width = '800';
    // height = '400';

    // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1('Detail Pekerjaan ' + kodekegiatan + ' ' + kodeorg + ' ' + bulan, content, width, height, ev);

// }

function viewDetail1(kodekegiatan, kodeorg, bulan, ev){
	param = 'kodekegiatan=' + kodekegiatan + '&kodeorg=' + kodeorg + '&bulan=' + bulan;

    tujuan = 'kebun_slave_2pemeliharaan1_detail.php';
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

function getAfd() {
    kdOrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    //	kdUnit=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    param = '&kdOrg=' + kdOrg;
    tujuan = 'kebun_slave_2pemeliharaan';

    post_response_text(tujuan + '.php?proses=getAfdAll', param, respon);
    //	alert(tujuan+'.php?proses=getAfd'+param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    if (kdOrg == '') {
                        document.getElementById('kdAfd').innerHTML = '';
                    } else {
                        document.getElementById('kdAfd').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);
}

function getAfd1() {
    kdOrg = document.getElementById('kdOrg1').options[document.getElementById('kdOrg1').selectedIndex].value;
    //	kdUnit=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    param = '&kdOrg=' + kdOrg;
    tujuan = 'kebun_slave_2pemeliharaan';
    post_response_text(tujuan + '.php?proses=getAfdAll', param, respon);
    //	alert(tujuan+'.php?proses=getAfd'+param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response


                    if (kdOrg == '') {
                        document.getElementById('kdAfd1').innerHTML = '';
                    } else {
                        document.getElementById('kdAfd1').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);
}

function Clear0() {
    document.getElementById('tgl1').value = '';
    document.getElementById('tgl2').value = '';
    //	document.getElementById('tgl1').disabled=false;
    //	document.getElementById('tgl2').disabled=false;
    document.getElementById('kdOrg').value = ''; //getAfd();
    document.getElementById('kdAfd').innerHTML = '';
    document.getElementById('kdAfd').value = '';
    document.getElementById('kegiatan').value = '';
    document.getElementById('printContainer').innerHTML = '';
}

function Clear1() {
    document.getElementById('tahun1').value = '';
    //	document.getElementById('tgl1').disabled=false;
    //	document.getElementById('tgl2').disabled=false;
    document.getElementById('kdOrg1').value = ''; //getAfd1();
    document.getElementById('kdAfd1').innerHTML = '';
    document.getElementById('kdAfd1').value = '';
    document.getElementById('kegiatan1').value = '';
    document.getElementById('printContainer').innerHTML = '';
}

function viewDetail(notransaksi, ev) {
    param = 'notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_2pemeliharaandetail.php' + "?" + param;
    width = '400';
    height = '200';

    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1('Detail Pemeliharaan ' + notransaksi, content, width, height, ev);

}

function viewDetail2(notransaksi, kdOrg, tanggal, ev) {
    param = 'notransaksi=' + notransaksi + '&kdOrg=' + kdOrg + '&tanggal=' + tanggal;
    tujuan = 'kebun_slave_2pemeliharaanbarang.php' + "?" + param;
    width = '400';
    height = '200';

    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1('Detail Pemeliharaan ' + notransaksi, content, width, height, ev);

}

function detailExcel(ev, tujuan) {
    width = '200';
    height = '100';
    //   alert(tujuan);
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1('Detail Pemeliharaan Excel', content, width, height, ev);
}