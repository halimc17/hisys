function isifile(doc, ev) {
    param = 'method=isifile' + '&doc=' + doc;
    title = "";
    showDialog4(title, "<iframe frameborder=0 style='width:795px;height:395px'" +
        " src='vhc_slave_save_jenisvhc.php?" + param + "'></iframe>", '800', '400', ev);
    var dialog = document.getElementById('dynamic4');
    dialog.style.top = '50px';
    dialog.style.left = '15%';

}

function loaddata() {
	kelompokvhc= document.getElementById('kelompokvhc').value;
	jenisvhc   = document.getElementById('jenisvhc').value;
	kodeorg    = document.getElementById('kodeorg').value;

    param = 'method=loaddata' + '&kelompokvhc=' + kelompokvhc + '&jenisvhc=' + jenisvhc + '&kodeorg=' + kodeorg;
    tujuan = 'vhc_slave_save_vhc.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
                    getimagevhc();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getimagevhc() {
    jenisvhc = document.getElementById('jenisvhc').options[document.getElementById('jenisvhc').selectedIndex].value;

    param = '&method=getimagevhc&jenisvhc=' + jenisvhc;
    tujuan = 'vhc_slave_save_vhc.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('divimage').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
    var kvhc = document.getElementById("kelompokvhc").value;
    var jvhc = document.getElementById("jenisvhc").value;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    formdata.append("kvhc", kvhc);
    formdata.append("jvhc", jvhc);

    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }

    var con = createXMLHttpRequest();
    con.open("POST", "vhc_slave_save_jenisvhc.php?method=submitfile", true);
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
                    loaddata();
                    //loadfiles(nopp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanVhc() {
    jenisvhc = document.getElementById('jenisvhc').value;
    namajenisvhc = document.getElementById('namajenisvhc').value;
    noakun = document.getElementById('noakun').value;
    kelompok = document.getElementById('kelompokvhc').options[document.getElementById('kelompokvhc').selectedIndex].value;
    met = document.getElementById('method').value;
    if (trim(jenisvhc) == '') {
        alert('Type is empty');
        document.getElementById('jenisvhc').focus();
    } else {
        if (confirm('Saving..?')) {
            jenisvhc = trim(jenisvhc);
            namajenisvhc = trim(namajenisvhc);
            param = 'jenisvhc=' + jenisvhc + '&namajenisvhc=' + namajenisvhc + '&method=' + met;
            param += '&kelompok=' + kelompok + '&noakun=' + noakun;
            tujuan = 'vhc_slave_save_jenisvhc.php';
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
                    //alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
                    submitfile();
                    // location.reload();


                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function fillField(kode, nama, noakun, kelompok) {
    ob = document.getElementById('kelompokvhc');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == kelompok) {
            ob.options[x].selected = true;
        }
    }
    document.getElementById('jenisvhc').value = kode;
    document.getElementById('jenisvhc').disabled = true;
    document.getElementById('namajenisvhc').value = nama;
    document.getElementById('noakun').value = noakun;
    document.getElementById('method').value = 'update';
}

function cancelVhc() {
    document.getElementById('jenisvhc').disabled = false;
    document.getElementById('jenisvhc').value = '';
    document.getElementById('namajenisvhc').value = '';
    //	document.getElementById('noakun').value='';
    document.getElementById('method').value = 'insert';
}

///==============master VHC============================================
function getList(kodeasset) {
    if (typeof kodeasset == 'undefined'){
        kodeasset = "";
	}
	kodeorg    = document.getElementById('kodeorg').value;
	kelompokvhc= document.getElementById('kelompokvhc').value;
	jenisvhc   = document.getElementById('jenisvhc').value;
    param = 'kelompokvhc=' + kelompokvhc + '&jenisvhc=' + jenisvhc + '&kodeorg=' + kodeorg + '&method=getList';

    if (kodeasset != ''){
        param += '&kodeasset=' + kodeasset;
	}
    tujuan = 'vhc_slave_save_vhc.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kodeasset').innerHTML = con.responseText;
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillMasterField(kodeorg, kelompokvhc, jenisvhc, kodevhc, beratkosong, nomorrangka, nobpkb, nomormesin, tahunperolehan,
    kodebarang, kepemilikan, kodetraksi, tglakhirstnk, tglakhirkir, tglakhirijinbm, tglakhirijinang, kodeasset, detailvhc, nopol, tahunproduksi, warna, tglakhirleasing, tglakhirasuransi) {
    ob = document.getElementById('kodeorg');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == kodeorg) {
            ob.options[x].selected = true;
        }
    }
    ob = document.getElementById('kelompokvhc');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == kelompokvhc) {
            ob.options[x].selected = true;
        }
    }
    ob = document.getElementById('jenisvhc');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == jenisvhc) {
            ob.options[x].selected = true;
        }
    }
    ob = document.getElementById('kodebarang');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == kodebarang) {
            ob.options[x].selected = true;
        }
    }
    ob = document.getElementById('kepemilikan');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == kepemilikan) {
            ob.options[x].selected = true;
        }
    }

    ob = document.getElementById('kodetraksi');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == kodetraksi) {
            ob.options[x].selected = true;
        }
    }

    ob = document.getElementById('kodeasset');
    for (x = 0; x < ob.length; x++) {
        if (ob.options[x].value == kodeasset) {
            ob.options[x].selected = true;
        }
    }

    // document.getElementById('kodeasset').innerHTML="<option value='"+ kodeasset +"'>"+ kodeasset +"</option>"

    document.getElementById('kodevhc').disabled = true;
    document.getElementById('kodevhc').value = kodevhc;
    document.getElementById('tahunperolehan').value = tahunperolehan;
    document.getElementById('beratkosong').value = beratkosong;
    document.getElementById('nomorrangka').value = nomorrangka;
    document.getElementById('nobpkb').value = nobpkb;
    document.getElementById('nomormesin').value = nomormesin;
    document.getElementById('detailvhc').value = '';
    document.getElementById('method').value = 'update';
    document.getElementById('tglakhirstnk').value = tglakhirstnk;
    document.getElementById('tglakhirkir').value = tglakhirkir;
    document.getElementById('tglakhirijinbm').value = tglakhirijinbm;
    document.getElementById('tglakhirijinang').value = tglakhirijinang;
    document.getElementById('detailvhc').value = detailvhc;
    document.getElementById('nopol').value = nopol;
    document.getElementById('tahunproduksi').value = tahunproduksi;
    document.getElementById('warna').value = warna;
    document.getElementById('tglakhirleasing').value = tglakhirleasing;
    document.getElementById('tglakhirasuransi').value = tglakhirasuransi;
    getList(kodeasset);
}

function cancelMasterVhc() {
    document.getElementById('kodevhc').disabled = true;
    document.getElementById('kodevhc').value = '';
    document.getElementById('tahunperolehan').value = '';
    document.getElementById('kodeasset').value = '';
    document.getElementById('kelompokvhc').value = '';
    document.getElementById('kodeorg').value = '';
    document.getElementById('kodebarang').value = '';
    document.getElementById('jenisvhc').value = '';
    document.getElementById('tglakhirstnk').value = '';
    document.getElementById('tglakhirkir').value = '';
    document.getElementById('tglakhirijinbm').value = '';
    document.getElementById('tglakhirijinang').value = '';
    //document.getElementById('kepemilikan').value='';

    //	 document.getElementById('noakun').value='';
    document.getElementById('beratkosong').value = '';
    document.getElementById('nomorrangka').value = '';
    document.getElementById('nobpkb').value = '';
    document.getElementById('nomormesin').value = '';
    document.getElementById('detailvhc').value = '';

    //document.getElementById('kodetraksi').value='';
    document.getElementById('nopol').value = '';
    document.getElementById('tahunproduksi').value = '';
    document.getElementById('warna').value = '';
    document.getElementById('tglakhirleasing').value = '';
    document.getElementById('tglakhirasuransi').value = '';
    document.getElementById('method').value = 'insert';
    loaddata();
}

function loadJenis(kelompok) {
    param = 'kelompok=' + kelompok;
    tujuan = 'vhc_slave_get_jenis.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('jenisvhc').innerHTML = con.responseText;
                    getList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanMasterVhc() {
    ob = document.getElementById('kodeorg');
    kodeorg = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kelompokvhc');
    kelompokvhc = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('jenisvhc');
    jenisvhc = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kodebarang');
    kodebarang = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kepemilikan');
    kepemilikan = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kodetraksi');
    kodetraksi = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kodeasset');
    kodeasset = ob.options[ob.selectedIndex].value;

    kodevhc = trim(document.getElementById('kodevhc').value);
    tahunperolehan = trim(document.getElementById('tahunperolehan').value);
    beratkosong = trim(document.getElementById('beratkosong').value);
    nomorrangka = trim(document.getElementById('nomorrangka').value);
    nomormesin = trim(document.getElementById('nomormesin').value);
    detailvhc = trim(document.getElementById('detailvhc').value);
    method = trim(document.getElementById('method').value);
    tglakhirstnk = document.getElementById('tglakhirstnk').value;
    tglakhirkir = document.getElementById('tglakhirkir').value;
    tglakhirijinbm = document.getElementById('tglakhirijinbm').value;
    tglakhirijinang = document.getElementById('tglakhirijinang').value;
    nopol = document.getElementById('nopol').value;
    tahunproduksi = document.getElementById('tahunproduksi').value;
    warna = document.getElementById('warna').value;
    tglakhirleasing = document.getElementById('tglakhirleasing').value;
    tglakhirasuransi = document.getElementById('tglakhirasuransi').value;
    nobpkb = document.getElementById('nobpkb').value;

    // if(trim(kodevhc)==''){
    // alert('Kode vhc tidak boleh kosong');
    // document.getElementById('kodevhc').focus();
    // return;
    // }
    // || trim(kodeorg)=='' || trim(kelompokvhc)=='' || trim(jenisvhc)=='' || kodebarang=='')
    if (trim(kelompokvhc) == '') {
        alert('Kelompok Vhc tidak boleh kosong');
        document.getElementById('kelompokvhc').focus();
        return;
    }
    if (trim(kodeorg) == '') {
        alert('Kodeorg tidak boleh kosong');
        document.getElementById('kodeorg').focus();
        return;
    }
    if (trim(jenisvhc) == '') {
        alert('Jenis vhc tidak boleh kosong');
        document.getElementById('jenisvhc').focus();
        return;
    }
    //  if(kepemilikan==1){
    // if(trim(kodeasset)==''){
    // 	alert('Kode asset tidak boleh kosong');
    // 	document.getElementById('kodeasset').focus();
    // 	return;
    //   }
    //  }
    if (tahunperolehan.length != 4) {
        alert('Tahun Perolehan Harus 4 Digit');
        document.getElementById('tahunperolehan').focus();
        return;
    }

    if (confirm('Saving..?')) {
        param = 'kodeorg=' + kodeorg + '&kelompokvhc=' + kelompokvhc + '&method=' + method;
        param += '&jenisvhc=' + jenisvhc + '&kodevhc=' + kodevhc;
        param += '&tahunperolehan=' + tahunperolehan;
        param += '&kodeasset=' + kodeasset;
        param += '&nobpkb=' + nobpkb;
        param += '&beratkosong=' + beratkosong + '&nomorrangka=' + nomorrangka;
        param += '&nomormesin=' + nomormesin + '&detailvhc=' + detailvhc;
        param += '&kodebarang=' + kodebarang + '&kepemilikan=' + kepemilikan + '&kodetraksi=' + kodetraksi;
        param += '&tglakhirstnk=' + tglakhirstnk + '&tglakhirkir=' + tglakhirkir;
        param += '&tglakhirijinbm=' + tglakhirijinbm + '&tglakhirijinang=' + tglakhirijinang;
        param += '&nopol=' + nopol + '&tahunproduksi=' + tahunproduksi + '&warna=' + warna + '&tglakhirleasing=' + tglakhirleasing + '&tglakhirasuransi=' + tglakhirasuransi;
        tujuan = 'vhc_slave_save_vhc.php';
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // res = con.responseText;
                    // res = res.split('#####');
                    // opt = JSON.parse(res[1]);
                    document.getElementById('container').innerHTML = con.responseText;
                    cancelMasterVhc();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteMasterVhc(kodeorg, kelompokvhc, jenisvhc, kodevhc) {
    method = 'delete';
    if (confirm('Deleting ' + kodevhc + ' ..?')) {
        if (confirm('Are you sure..?')) {
            param = 'kodeorg=' + kodeorg + '&kelompokvhc=' + kelompokvhc + '&method=' + method;
            param += '&jenisvhc=' + jenisvhc + '&kodevhc=' + kodevhc;
            tujuan = 'vhc_slave_save_vhc.php';
            //alert(param);
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
                    res = con.responseText;
                    res = res.split('#####');
                    opt = JSON.parse(res[1]);
                    document.getElementById('container').innerHTML = res[0];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function dataKeExcel(ev, tujuan) {
    kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    kodebarang = document.getElementById('kodebarang').options[document.getElementById('kodebarang').selectedIndex].value;
    kodetraksi = document.getElementById('kodetraksi').options[document.getElementById('kodetraksi').selectedIndex].value;
    kodevhc = document.getElementById('kodevhc').value;
    method = trim(document.getElementById('method').value);
    judul = 'Report Ms.Excel';
    param = 'kodeorg=' + kodeorg + '&kodevhc=' + kodevhc + '&kodebarang=' + kodebarang + '&kodetraksi=' + kodetraksi + '&method=excel';
    printFile(param, tujuan, judul, ev)
}
function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '600';
    height = '300';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}
function deAktif(kdvhc, stat) {
    method = 'deactive';
    dert = "";
    if (stat == '1') {
        dert = "Deactivate";
    } else {
        dert = "Actived";
    }
    if (confirm(dert + ' ' + kdvhc + ' ..?')) {
        if (confirm('Are you sure..?')) {
            param = 'method=' + method + '&kodevhc=' + kdvhc;
            param += '&status=' + stat;
            tujuan = 'vhc_slave_save_vhc.php';
            //alert(param);
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

function getNotransaksi() {
    jenisvhc = document.getElementById('jenisvhc').options[document.getElementById('jenisvhc').selectedIndex].value;
    kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    param = 'kodeorg=' + kodeorg + '&jenisvhc=' + jenisvhc + '&method=getNotransaksi';
    tujuan = 'vhc_slave_save_vhc.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kodevhc').value = trim(con.responseText)
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}