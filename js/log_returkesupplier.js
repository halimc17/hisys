function setSloc(x) {
    gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    //set value display periode
    tglstart = document.getElementById(gudang + '_start').value;
    tglend = document.getElementById(gudang + '_end').value;
    tglstart = tglstart.substr(6, 2) + "-" + tglstart.substr(4, 2) + "-" + tglstart.substr(0, 4);
    tglend = tglend.substr(6, 2) + "-" + tglend.substr(4, 2) + "-" + tglend.substr(0, 4);
    document.getElementById('displayperiod').innerHTML = tglstart + " - " + tglend;

    if (gudang != '') {
        if (x == 'simpan') {
            document.getElementById('sloc').disabled = true;
            document.getElementById('btnsloc').disabled = true;
            tujuan = 'log_slave_getBastNumber.php';
			param = 'simpan=' + 'simpan';
			param += '&gudang=' + gudang;
            post_response_text(tujuan, param, respog);
        } else {
            document.getElementById('sloc').disabled = false;
            document.getElementById('sloc').options[0].selected = true;
            document.getElementById('btnsloc').disabled = false;
            bersihkan();
            document.getElementById('nodok').value = '';
        }

    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('nodok').value = trim(con.responseText);
                    getBapbList(gudang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getBapbList(gudang) {
    param = 'gudang=' + gudang;
    tujuan = 'log_slave_getReturSupplierList.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cariBapb(num) {
    tex = trim(document.getElementById('txtbabp').value);
    gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    if (gudang == '') {
        alert('Storage Location  is obligatory')
    } else {
        param = 'gudang=' + gudang;
        param += '&page=' + num;
        if (tex != '')
            param += '&tex=' + tex;
        tujuan = 'log_slave_getReturSupplierList.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function Fverify() {
    nomorlama = trim(document.getElementById('nomorlama').value);
    kodebarang = trim(document.getElementById('kodebarang').value);
    gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    if (nomorlama == '' || kodebarang == '' || gudang == '') {
        alert('Storage location,Old.Document and material code are obligatory');
    } else {
        param = 'nomorlama=' + nomorlama + '&kodebarang=' + kodebarang + '&kodegudang=' + gudang;
        tujuan = 'log_slave_getOldReturSupplier.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    parseDong(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function parseDong(tex) {
    xml = tex.toString();
    xmlobject = (new DOMParser()).parseFromString(xml, "text/xml");

    namabarang = xmlobject.getElementsByTagName('namabarang')[0].firstChild.nodeValue;
    namabarang = namabarang.replace("*", "");
    satuan = xmlobject.getElementsByTagName('satuan')[0].firstChild.nodeValue;
    satuan = satuan.replace("*", "");
    hargasatuan = xmlobject.getElementsByTagName('hargasatuan')[0].firstChild.nodeValue;
    hargasatuan = hargasatuan.replace("*", "");
    jumlah = xmlobject.getElementsByTagName('jumlah')[0].firstChild.nodeValue;
    jumlah = jumlah.replace("*", "");
    kodept = xmlobject.getElementsByTagName('kodept')[0].firstChild.nodeValue;
    kodept = kodept.replace("*", "");
    untukpt = xmlobject.getElementsByTagName('untukpt')[0].firstChild.nodeValue;
    untukpt = untukpt.replace("*", "");
    untukunit = xmlobject.getElementsByTagName('untukunit')[0].firstChild.nodeValue;
    untukunit = untukunit.replace("*", "");

    nopo = xmlobject.getElementsByTagName('nopo')[0].firstChild.nodeValue;
    nopo = nopo.replace("*", "");
    namasupplier = xmlobject.getElementsByTagName('namasupplier')[0].firstChild.nodeValue;
    namasupplier = namasupplier.replace("*", "");

    kodesupplier = xmlobject.getElementsByTagName('kodesupplier')[0].firstChild.nodeValue;
    kodesupplier = kodesupplier.replace("*", "");

    document.getElementById('namabarang').value = namabarang;
    document.getElementById('satuan').value = satuan;
    document.getElementById('jlhlama').value = jumlah;
    document.getElementById('hargasatuan').value = hargasatuan;
    document.getElementById('kodept').value = kodept;
    document.getElementById('untukpt').value = untukpt;
    document.getElementById('untukunit').value = untukunit;
    document.getElementById('nopo').value = nopo;
    document.getElementById('namasupplier').value = namasupplier;
    document.getElementById('supplierid').value = kodesupplier;

    //enable button and field
    document.getElementById('savebutton').disabled = false;
    document.getElementById('jlhretur').disabled = false;
    document.getElementById('nomorlama').disabled = true;
    document.getElementById('kodebarang').disabled = true;
}

function simpanRetur() {
    nodok = document.getElementById('nodok').value;
    nomorlama = trim(document.getElementById('nomorlama').value);
    kodebarang = trim(document.getElementById('kodebarang').value);
    gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    hargasatuan = document.getElementById('hargasatuan').value;
    jlhretur = document.getElementById('jlhretur').value;
    keterangan = document.getElementById('keterangan').value.toUpperCase();
    tanggal = document.getElementById('tanggal').value;
    satuan = document.getElementById('satuan').value;
    kodept = document.getElementById('kodept').value;
    untukpt = document.getElementById('untukpt').value;
    untukunit = document.getElementById('untukunit').value;

    nopo = document.getElementById('nopo').value;
    supplierid = document.getElementById('supplierid').value;

    param = 'nodok=' + nodok + '&nomorlama=' + nomorlama;
    param += '&kodebarang=' + kodebarang + '&gudang=' + gudang;
    param += '&hargasatuan=' + hargasatuan + '&jlhretur=' + jlhretur;
    param += '&keterangan=' + keterangan + '&tanggal=' + tanggal;
    param += '&satuan=' + satuan + '&kodept=' + kodept;
    param += '&untukunit=' + untukunit + '&untukpt=' + untukpt;
    param += '&nopo=' + nopo + '&supplierid=' + supplierid;

    jlhlama = document.getElementById('jlhlama').value;

    x = tanggal;
    _start = document.getElementById(gudang + '_start').value;
    _end = document.getElementById(gudang + '_end').value;
    while (x.lastIndexOf("-") > -1) {
        x = x.replace("-", "");
    }
    while (x.lastIndexOf("-") > -1) {
        x = x.replace("/", "");
    }

    curdateY = x.substr(4, 4).toString();
    curdateM = x.substr(2, 2).toString();
    curdateD = x.substr(0, 2).toString();
    curdate = curdateY + curdateM + curdateD;
    curdate = parseInt(curdate);
    if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
        alert('Date out of range')
    } else if (parseFloat(jlhlama) < parseFloat(jlhretur)) {
        alert('Return value larger than original');
    } else {
        if (nodok == '' || jlhretur == '' || tanggal == '') {
            alert('Document number, Qty and date are obligatory');
        } else {
            tujuan = 'log_slave_saveReturSupplier.php';
            if (confirm('Saving, are you sure..?'))
                post_response_text(tujuan, param, respog);
        }
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert('Saved');
                    bersihkan();
                    setSloc('simpan');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function bersihkan() {
    document.getElementById('savebutton').disabled = true;
    document.getElementById('jlhretur').value = '0';
    document.getElementById('jlhretur').disabled = true;
    document.getElementById('nomorlama').value = '';
    document.getElementById('kodebarang').value = '';
    document.getElementById('namabarang').value = '';
    document.getElementById('hargasatuan').value = 0;
    document.getElementById('jlhlama').value = 0;
    document.getElementById('satuan').value = '';
    document.getElementById('keterangan').value = '';
    document.getElementById('kodept').value = '';
    document.getElementById('untukunit').value = '';
    document.getElementById('untukpt').value = '';
    document.getElementById('nomorlama').disabled = false;
    document.getElementById('kodebarang').disabled = false;
    document.getElementById('nopo').value = '';
    document.getElementById('namasupplier').value = '';
    document.getElementById('supplierid').value = '';

}

function previewBapb(notransaksi, ev) {
    param = 'notransaksi=' + notransaksi;
    tujuan = 'log_slave_print_retur_supplier_pdf.php?' + param;
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_print_retur_supplier_pdf.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
    // //display window
    // title = notransaksi;
    // width = '700';
    // height = '400';
    // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        // showDialog1(title, content, width, height, ev);

}

function delBapb(notransaksi) {
    param = 'notransaksi=' + notransaksi;
    tujuan = 'log_slave_deleteBapb.php';
    if (confirm('Deleting Document ' + notransaksi + ', are you sure..?'))
        post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    setSloc('simpan');
                    //getBapbList(gudang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// Umar
function showupload(ev) {
    nodok = document.getElementById('nodok').value;
    kodebarang = document.getElementById('kodebarang').value;

    if (nodok == '') {
        alert('Pilih Gudang Terlebih Dahulu!');
        return;
    }

    // if (kodebarang == '') {
    //     alert('Isi Kode Barang Terlebih Dahulu!');
    //     return;
    // }
    
    showformupload(ev);
    param = 'method=showupload&notransaksi='+nodok+'&kodebarang='+kodebarang;
    tujuan = 'log_slave_penerimaanUpload.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfilesx(nodok,kodebarang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showformupload(ev) {
    title = "UPLOAD FILES";
    width = '';
    height = '';
    content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function loadfilesx(nodok,kodebarang) {
    param = 'method=loadfiles&notransaksi='+nodok+'&kodebarang='+kodebarang;
    tujuan = 'log_slave_penerimaanUpload.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfilestop') !== null) {
                        document.getElementById('listfilestop').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfilesview') !== null) {
                        document.getElementById('listfilesview').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function save_filex(){
    var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('nodok').value;
    var kodebarang = document.getElementById('kodebarangupload').innerHTML;
    var jenisupload = document.getElementById('kriteriaefil').value;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("kodebarang", kodebarang);
    formdata.append("jenisupload", jenisupload);
    formdata.append("file", file);
    formdata.append("fileupload", document.getElementById("upload").value);
    //alert(document.getElementById("filex").value);
    if (document.getElementById("upload").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "log_slave_penerimaanUpload.php?method=submitfilex", true);
    busy_on();
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfilesx(notransaksi,kodebarang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//End Umar

// joki
// this is
function searchNoLama(title, content, ev) {
	// kdbrg = document.getElementById('kd_brg').value;
	width = '';
	height = '';
	showDialog1(title, content, width, height, ev);
}

function nomorlama2() {
	txt3 = trim(document.getElementById('no_nomorlama').value);
    sup = 'supplier';
    gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	// rkd_bag = document.getElementById('kd_bag').options[document.getElementById('kd_bag').selectedIndex].value;
	if (txt3 == '') {
		alert('Text is obligatory');
	} else {
		// param = 'txtfind3=' + txt3 + '&rkd_bag=' + rkd_bag;
		param = 'gudang=' + gudang;
		param += '&for=' + sup;
		param += '&txtfind3=' + txt3;
		tujuan = 'log_slave_get_nodok_lama.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setNodokLama(noLama) {
	document.getElementById('nomorlama').value = noLama;
	// kd_brg = document.getElementById('kd_brg').value;
	// if (kd_brg != '') {
	// 	document.getElementById('showdocument').style.display = "";
	// }
	closeDialog();
}

function searchBARANGLama(title, content, ev) {
    nolama2 = document.getElementById('nomorlama').value;
    if(nolama2 == ''){
        alert('No.Dokumen.Lama Belum Terisi');
        return false;
    }
	width = '';
	height = '';
	showDialog1(title, content, width, height, ev);
    txt3 = 'kodebarang';
    sup = 'kodebarang';
	if (txt3 == '') {
		alert('Text is obligatory');
	} else {
		param = 'for=' + sup;
		param += '&nolama2=' + nolama2;
		param += '&txtfind3=' + txt3;
		tujuan = 'log_slave_get_nodok_lama.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setKodebarangLama(kodebarang) {
	document.getElementById('kodebarang').value = kodebarang;
	// kd_brg = document.getElementById('kd_brg').value;
	// if (kd_brg != '') {
	// 	document.getElementById('showdocument').style.display = "";
	// }
	closeDialog();
}