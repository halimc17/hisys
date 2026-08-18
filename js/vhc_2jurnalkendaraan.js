function ambilPeriode(gudang) {
    param = 'gudang=' + gudang;
    tujuan = 'log_slave_getPeriode.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('periode').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function getBiayaTotalPerKendaraan() {
    unit = document.getElementById('unit');
    //periode =document.getElementById('periode');
    unit = unit.options[unit.selectedIndex].value;
    tglAwl = document.getElementById('tglAwal').value;
    tglAkhr = document.getElementById('tglAkhir').value;
    // periode	=periode.options[periode.selectedIndex].value;
    //param='unit='+unit+'&periode='+periode;
    param = 'unit=' + unit + '&tglAkhir=' + tglAkhr + '&tglAwal=' + tglAwl;
    tujuan = 'vhc_slave_2jurnalkendaraan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //showById('printPanel');
                    document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getBiayaTotalPerKendaraanexcel() {
    unit = document.getElementById('unit');
    unit = unit.options[unit.selectedIndex].value;
    tglAwl = document.getElementById('tglAwal').value;
    tglAkhr = document.getElementById('tglAkhir').value;
    
    param = 'unit=' + unit + '&tglAkhir=' + tglAkhr + '&tglAwal=' + tglAwl+ '&jenis=excel';
    tujuan = 'vhc_slave_2jurnalkendaraan.php';
	printnopopup(tujuan+"?"+param);
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}

// function viewDetail(ev, kodevhc, tanggalmulai, tanggalsampai, unit, periode, noakunawal, noakunakhir) {
    // param = 'kodevhc=' + kodevhc + '&tanggalmulai=' + tanggalmulai + '&tanggalsampai=' + tanggalsampai + '&unit=' + unit + '&periode=' + periode;
    // param += '&noakunawal=' + noakunawal + '&noakunakhir=' + noakunakhir;
    // /* tujuan = 'vhc_slave_2biayatotalperkendaraandetail.php' + "?" + param;
    // width = '800';
    // height = '400';

    // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        // showDialog1('Cost Detai By Unit ' + kodevhc, content, width, height, ev);
	 // */	
	// alertify.popuppdf("Detail","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_2biayatotalperkendaraandetail.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
// }

function viewDetail(ev, kodevhc, tanggalmulai, tanggalsampai, unit, periode, noakunawal, noakunakhir) {
	param = 'kodevhc=' + kodevhc + '&tanggalmulai=' + tanggalmulai + '&tanggalsampai=' + tanggalsampai + '&unit=' + unit + '&periode=' + periode;
    param += '&noakunawal=' + noakunawal + '&noakunakhir=' + noakunakhir;
	tujuan = 'vhc_slave_2biayatotalperkendaraandetail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else{
					alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','80%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}




// function detailAlokasi(ev, kdvhc, hrgsat) {
    // tglAwl = document.getElementById('tglAwal').value;
    // tglAkhr = document.getElementById('tglAkhir').value;
    // param = 'kodevhc=' + kdvhc + '&hrgaSatuan=' + hrgsat;
    // param += '&tglAkhir=' + tglAkhr + '&tglAwal=' + tglAwl;
    
// /* 	tujuan = 'vhc_slave_2biayaalokasiperkendaraandetail.php' + "?" + param;
	// width = '800';
	// height = '500';
	// //alert(param);

	// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog2('Allocation Detail' + kdvhc, content, width, height, ev);
 // */	
	// alertify.popuppdf("Detail","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_2biayaalokasiperkendaraandetail.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
// }


function detailAlokasi(tipe) {
    unit    = document.getElementById('company_id').value;
    tglAwl  = document.getElementById('tglAwal').value;
    tglAkhr = document.getElementById('tglAkhir').value;
    alokasi = document.getElementById('alokasi').value;
    kegiatan= document.getElementById('akun').value;
    param = 'unit=' + unit;
    param += '&kodevhc=' + getValue('kdVhc');
    param += '&alokasi=' + alokasi;
    param += '&kegiatan=' + kegiatan;
    param += '&tglAkhir=' + tglAkhr + '&tglAwal=' + tglAwl;
    param += '&jenis=' + tipe;
	tujuan = 'vhc_slave_2jurnalkendaraan.php';
    if(tipe == 'excel'){
        printnopopup(tujuan+'?'+param)
    }else{
        post_response_text(tujuan, param, respog);
    }
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else{
                    document.getElementById('container').innerHTML = con.responseText
                    leftFixedTable()
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}




function detailExcel(ev) {
    width = '300';
    height = '100';
    kodevhc = document.getElementById('kodevhc').value;
    tanggalmulai = document.getElementById('tanggalmulai').value;
    tanggalsampai = document.getElementById('tanggalsampai').value;
    noakunawal = document.getElementById('noakunawal').value;
    noakunakhir = document.getElementById('noakunakhir').value;
    unit = document.getElementById('unit').value;
    param = 'kodevhc=' + kodevhc + '&tanggalmulai=' + tanggalmulai + '&tanggalsampai=' + tanggalsampai + '&unit=' + unit;
    param += '&noakunawal=' + noakunawal + '&noakunakhir=' + noakunakhir + '&type=excel';
    tujuan = 'vhc_slave_2biayatotalperkendaraandetail.php' + "?" + param;
    // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1('Detail Cost By Vehicle', content, width, height, ev);
	
	tujuan=tujuan+"?"+param;
	printnopopup(tujuan);
	
	//alertify.popuppdf("Detail","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_2biayatotalperkendaraandetail.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}

function detailData(ev, tujuan) {
    width = '5px';
    height = '5px';
	
	
	printnopopup(tujuan);
	
    // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1('Allocation Detail', content, width, height, ev);
	
	// alertify.popuppdf("Detail","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
}

function printnopopup(url) {
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}

function biayaTotalPerKendaraanKeExcel(ev, tujuan) {
    unit = document.getElementById('unit');
    tglAwl = document.getElementById('tglAwal').value;
    tglAkhr = document.getElementById('tglAkhir').value;
    //	periode =document.getElementById('periode');
    unit = unit.options[unit.selectedIndex].value;
    //        periode	=periode.options[periode.selectedIndex].value;
    judul = 'Report Ms.Excel';
    //param='unit='+unit+'&periode='+periode;
    param = 'unit=' + unit + '&tglAkhir=' + tglAkhr + '&tglAwal=' + tglAwl;
    printFile(param, tujuan, judul, ev)
}

function ambilPeriode2(unit) {
    param = 'unit=' + unit;
    tujuan = 'sdm_slave_getPeriode.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('periode').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function ganti_pil() {
    document.getElementById('company_id').disabled = false;
    document.getElementById('kdVhc').disabled = false;
    document.getElementById('jnsVhc').disabled = false;
    document.getElementById('alokasi').disabled = false;
    setValue2('company_id','');
    setValue2('kdVhc','');
    setValue2('alokasi','');
    setValue2('jnsVhc','');
    setValue2('akun','');
    document.getElementById('tglAwal').value = '';
    document.getElementById('tglAkhir').value = '';
    document.getElementById('akun').disabled = false;
    //document.getElementById('period').disabled=false;
    document.getElementById('contain').innerHTML = '';
    //document.getElementById('nmKry').value='';
    //document.getElementById('nm_goods').value='';
}
function get_jnsVhc() {
    comId = document.getElementById('company_id').options[document.getElementById('company_id').selectedIndex].value;
    param = 'comId=' + comId + '&proses=getJnsVhc';
    //alert(param);
    tujuan = 'vhc_slave_laporanKerjaKendaraan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    // document.getElementById('company_id').disabled = true;
                    document.getElementById('jnsVhc').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getKdVhc() {
    //jnsVhc=document.getElementById('jnsVhc').value;
    jnsVhc = document.getElementById('jnsVhc').options[document.getElementById('jnsVhc').selectedIndex].value;
    param = 'jnsVhc=' + jnsVhc + '&proses=getKdvhc';
    //alert(param);
    tujuan = 'vhc_slave_laporanKerjaKendaraan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    // document.getElementById('jnsVhc').disabled = true;
                    document.getElementById('kdVhc').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}