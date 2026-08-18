function checkdatall(totrow) {
    checkall = document.getElementById('checkdatall');
    if (checkall.checked == true) {
        // checkall.checked = false;
        for (i = 0; i < totrow; i++) {
            checkitem = document.getElementById('cekdata' + i);
            checkitem.checked = true;
        }
    } else {
        for (i = 0; i < totrow; i++) {
            checkitem = document.getElementById('cekdata' + i);
            checkitem.checked = false;
        }
    }
}

function checkitems(idrow, totrow) {
    no = 0;
    for (i = 0; i < totrow; i++) {
        checkitem = document.getElementById('cekdata' + i);
        if (checkitem.checked == true) {
            no = no + 1;
        }
    }

    checknewitem = document.getElementById('cekdata' + idrow);
    checkall = document.getElementById('checkdatall');
    if (no == totrow) {
        checkall.checked = true;
    } else {
        checkall.checked = false;
    }
}

function prosesAwal() {
    lokasitugas = document.getElementById('lokasitugas');
    lokasitugas = lokasitugas.options[lokasitugas.selectedIndex].value;
    periode = document.getElementById('periode');
    periode = periode.options[periode.selectedIndex].value;
    tipekaryawan = document.getElementById('tipekaryawan');
    tipekaryawan = tipekaryawan.options[tipekaryawan.selectedIndex].value;
    tujuan = 'sdm_slave_5cutiGetAwalList.php';
    param = 'lokasitugas=' + lokasitugas + '&periode=' + periode + '&tipekaryawan=' + tipekaryawan;
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist1').innerHTML = con.responseText;
                    // tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

MAX_ROW = 0;
function simpanAwal(max) {
    MAX_ROW = (max - 1);
    if (confirm('Are you sure..?')) {
        doLooping(0);
    }
}

function doLooping(x) {
    if (x <= MAX_ROW) {
        karyawanid = document.getElementById('karyawanid' + x).innerHTML;
        periode = document.getElementById('periode' + x).innerHTML;
        kodeorg = document.getElementById('kodeorg' + x).innerHTML;
        hak = document.getElementById('myeditcuti' + x).value;
        keterangan = document.getElementById('keterangan' + x).value;


        cekdata = document.getElementById('cekdata' + x);
        if (cekdata.checked == true){
            cekdata = 1;
        }else{
            cekdata = 0;
        }

        param =  'karyawanid=' + karyawanid;
        param += '&periode=' + periode + '&hak=' + hak+ '&keterangan=' + keterangan;
        param += '&lokasitugas=' + kodeorg + '&cekdata=' + cekdata;
        tujuan = 'sdm_slave_save5AwalCuti.php';
        post_response_text(tujuan, param, respog);
    } else {
        alert('Finish');
        loadList(kodeorg, periode);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('baris' + x).style.backgroundColor = '#FF4444'
                } else {
                    document.getElementById('baris' + x).style.display = 'none';
                    z = x + 1;
                    doLooping(z);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadList(kodeorg, periode) {
    tipekaryawan = document.getElementById('tipekaryawan');
    tipekaryawan = tipekaryawan.options[tipekaryawan.selectedIndex].value;
    param = 'kodeorg=' + kodeorg + '&periode=' + periode + '&tipekaryawan=' + tipekaryawan;
    tujuan = 'sdm_slave_getCutiHeaderForm.php';
    post_response_text(tujuan, param, respog);
    // document.getElementById('containerlist2').innerHTML = '';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist1').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function Listadjs(kodeorg, periode) {
    tipekaryawan = document.getElementById('tipekaryawan');
    tipekaryawan = tipekaryawan.options[tipekaryawan.selectedIndex].value;
    param = 'kodeorg=' + kodeorg + '&periode=' + periode + '&tipekaryawan=' + tipekaryawan;
    tujuan = 'sdm_slave_getCutiAdjs.php';
    post_response_text(tujuan, param, respog);
    // document.getElementById('containerlist2').innerHTML = '';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist1').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteAdjsmen(id,kodeorg,karid,periodecuti,hakcutiadj){

    param = 'id=' + id + '&kodeorg=' + kodeorg + '&karid=' + karid + '&periodecuti=' + periodecuti+ '&hakcutiadj=' + hakcutiadj;
    tujuan = 'sdm_slave_deleteCutiAdjs.php';

    alertify.confirm("Warning"," Anda yakin ingin menghapus ? ",
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
                    alert(con.responseText);
                } else {
                    Listadjs(kodeorg,periodecuti);
                    alert("Data berhasil dihapus");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function updateSisa(periode, karyawanid, kodeorg, idsisa) {
    sisa = trim(document.getElementById(idsisa).value);
    if (sisa == '') {
        sisa = 0;
    }

    param = 'kodeorg=' + kodeorg + '&karyawanid=' + karyawanid + '&periode=' + periode + '&sisa=' + sisa;
    tujuan = 'sdm_slave_updateSisaCuti.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById(idsisa).style.backgroundColor = '#dedede';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function updatehakcuti(periode, karyawanid, kodeorg, hakcuti, idno) {
    diambil = trim(document.getElementById('diambil' + idno).innerHTML);
    hakcuti2 = trim(document.getElementById(hakcuti).value);

    if (hakcuti2 == '') {
        hakcuti2 = 0;
    }

    param = 'kodeorg=' + kodeorg + '&karyawanid=' + karyawanid + '&periode=' + periode + '&hakcuti=' + hakcuti2 + '&diambil=' + diambil + '&method=updatehakcuti';
    tujuan = 'sdm_slave_updateSisaCuti.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById(hakcuti).style.backgroundColor = '#dedede';
                    document.getElementById('sisa' + idno).value = con.responseText;
                    alert("Update hak cuti berhasil");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showByUser(karyawanid, ev) {
    param = 'karyawanid=' + karyawanid;
    tujuan = 'sdm_slave_getHeaderCutiByUser.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    title = karyawanid;
                    width = '';
                    height = '';
                    content = "<div style='max-height:350px;overflow:auto;'>" + con.responseText + "</div>";
                    showDialog1(title, content, width, height, ev);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function tambahData(periode, karyawanid, kodeorg, namakaryawan, outstanding) {

    param = 'periode=' + periode + '&karyawanid=' + karyawanid + '&kodeorg=' + kodeorg + '&outstanding=' + outstanding;
    param += '&namakaryawan=' + namakaryawan;
    tujuan = 'sdm_slave_getCutiDetailForm.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('containerlist2').innerHTML = con.responseText;
                    tabAction(document.getElementById('tabFRM0'), 1, 'FRM', 0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function simpanJ() {

    outstanding = document.getElementById('outstanding').value;
    periode = document.getElementById('periode').value;
    kodeorgJ = document.getElementById('kodeorgJ').value;
    karyawanidJ = document.getElementById('karyawanidJ').value;
    periodeJ = document.getElementById('periodeJ').value;
    dariJ = document.getElementById('dariJ').value;
    sampaiJ = document.getElementById('sampaiJ').value;
    diambilJ = remove_comma(document.getElementById('diambilJ'));
    keteranganJ = document.getElementById('keteranganJ').value;

    if (trim(dariJ) == '' || trim(sampaiJ) == '' || diambilJ == '') {
        alert('Each Field are obligatory');
        document.getElementById('kodeorgJ').focus();
    } else {
        if ((outstanding - diambilJ) < 0) {
            alert("Gagal. Jumlah hari cuti sudah melebihi hak cuti untuk periode ini.");
            return false;
        }
        param = 'kodeorgJ=' + kodeorgJ + '&karyawanidJ=' + karyawanidJ + '&periodeJ=' + periodeJ;
        param += '&dariJ=' + dariJ + '&sampaiJ=' + sampaiJ + '&method=insert';
        param += '&diambilJ=' + diambilJ + '&keteranganJ=' + keteranganJ;
        tujuan = 'sdm_slave_save_cutiDetail.php';
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert('Saved');
                    loadList(kodeorgJ, periode);
                    tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function hapusData(periode, karyawanid, kodeorg, daritanglal, sampaitanglal, nobaris, jlhcuti) {
    loadPeriode = document.getElementById('periode').value;
    param = 'kodeorgJ=' + kodeorg + '&karyawanidJ=' + karyawanid + '&periodeJ=' + periode;
    param += '&dariJ=' + daritanglal + '&sampaiJ=' + sampaitanglal + '&method=delete';
    tujuan = 'sdm_slave_save_cutiDetail.php';
    if (confirm('Deleting, are you sure..?'))
        post_response_text(tujuan, param, respog);

    ttl = parseFloat(document.getElementById('cellttl').innerHTML);
    ttl = ttl - jlhcuti;
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById(nobaris).style.display = 'none';
                    document.getElementById('cellttl').innerHTML = ttl;
                    alert('deleted');
                    loadList(kodeorg, loadPeriode);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//=================laporan
function loadLaporan() {
    lokasitugas = document.getElementById('lokasitugas');
    lokasitugas = lokasitugas.options[lokasitugas.selectedIndex].value;
    periode = document.getElementById('periode');
    periode = periode.options[periode.selectedIndex].value;
    karyawan = document.getElementById('karyawan');
    karyawan = karyawan.options[karyawan.selectedIndex].value;

    param = 'kodeorg=' + lokasitugas + '&periode=' + periode + '&karyawan=' + karyawan + '&method=preview';
    tujuan = 'sdm_slave_getLaporanCuti.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist1').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cutiToExcel(kodeorg, periode, karyawan, ev) {
    param = 'kodeorg=' + kodeorg + '&periode=' + periode + '&karyawan=' + karyawan + '&method=excel';
    tujuan = 'sdm_slave_cuti_Excel.php?' + param;
    //display window
    title = 'Download';
    width = '500';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}

function loadkaryawan() {
    lokasitugas = document.getElementById('lokasitugas');
    lokasitugas = lokasitugas.options[lokasitugas.selectedIndex].value;
    periode     = document.getElementById('periode');
    periode     = periode.options[periode.selectedIndex].value;
    tujuan = 'sdm_slave_getLaporanCuti.php';
    param = 'kodeorg=' + lokasitugas + '&periode='+periode+'&method=loadkaryawan';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('karyawan').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdetail(karyawanid, periode, ev) {
    width = '';
	height = '';
	content = "<fieldset style='height:96%;width:97%';><div id=detailcuti style='overflow:auto;max-height:400px;max-width:900px';></div></fieldset>";
	title = "Detail "+periode;
	showDialog1(title, content, width, height, ev);
	param = 'method=getdetail'+'&karyawanid='+karyawanid+'&periode='+periode;
	tujuan = 'sdm_slave_getLaporanCuti.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailcuti').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewPdf(tgl, karywn, tpkary, ev) {
    tglijin = tgl;
    krywnId = karywn;
    param = 'proses=prevPdf' + '&tglijin=' + tglijin + '&krywnId=' + krywnId;
    if(tpkary == 'STAFF'){
        tujuan = 'sdm_slave_laporan_ijin_meninggalkan_kantor.php?' + param;
    }else{
        tujuan = 'sdm_slave_laporan_ijin_meninggalkan_kantornonstaff.php?' + param;
    }
    //display window
    title = 'Print PDF';
    width = '1000';
    height = '700';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog2(title, content, width, height, ev);

}