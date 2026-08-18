function createNew() {
    document.getElementById('addNew').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    document.getElementById('method').value = 'insert';
    hapus();
}

function displayList() {
    hapus();
    document.getElementById('addNew').style.display = 'none';
    document.getElementById('listData').style.display = 'block';
    loadData(0);
}

function hapus() {
    document.getElementById('notransaksisch').value = '';
    closeDialog();
}


function getPage() {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loadData(paged);
}

function loadData(num) {
    notransaksisch = document.getElementById('notransaksisch').value;

    param = 'method=loadData&page=' + num;
    param += '&notransaksisch=' + notransaksisch;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    dataSlave = con.responseText.split("####");
                    document.getElementById('addNew').style.display = 'none';
                    document.getElementById('listData').style.display = 'block';
                    document.getElementById('container').innerHTML = dataSlave[0];
                    document.getElementById('footData').innerHTML = dataSlave[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function hapus() {
    document.getElementById('notransaksisch').value = '';
    document.getElementById('notransaksi').value = '';
    document.getElementById('method').value = 'insert';

    document.getElementById('karyawanid').disabled = false;
    document.getElementById('periodecuti').disabled = false;
    document.getElementById('keperluan').value = '';
    document.getElementById('ket').value = '';
    document.getElementById('tglAwal').value = '';
    document.getElementById('tglEnd').value = '';
    setValue2('karyawanid', '');
    setValue2('idjenis', '');
    document.getElementById('pengganti').value = '';
    document.getElementById('jumlahhk').value = '';
    document.getElementById('tanggalkerja').value = '';
    document.getElementById('tglIzin').value = '';
    document.getElementById('tglMasuk').value = '';
    document.getElementById('tglPengangkatan').value = '';
    document.getElementById('alamatcuti').value = '';
    document.getElementById('pengganti').value = '';
    document.getElementById('nohp').value = '';
    //  document.getElementById('apppengganti').value='';
    document.getElementById('hometrip').value = '';
    document.getElementById('tglberangkat').value = '';
    document.getElementById('rutekeberangkatan').value = '';
    document.getElementById('tglpulang').value = '';
    document.getElementById('rutekepulangan').value = '';
    document.getElementById('sis').innerHTML = '0';
    // closeDialog();
}

function loadperiodecuti() {
    karyawanid = document.getElementById('karyawanid').value;

    param = 'method=loadperiodecuti';
    param += '&karyawanid=' + karyawanid;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('periodecuti').innerHTML = con.responseText;
                    clearinputan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function clearinputan() {
    document.getElementById('notransaksisch').value = '';

    document.getElementById('keperluan').value = '';
    document.getElementById('ket').value = '';
    document.getElementById('tglAwal').value = '';
    document.getElementById('jumlahhk').value = '';
    document.getElementById('tglEnd').value = '';
    setValue2('idjenis', '');
    document.getElementById('pengganti').value = '';
    document.getElementById('tanggalkerja').value = '';
    document.getElementById('tglIzin').value = '';
    document.getElementById('tglMasuk').value = '';
    document.getElementById('tglPengangkatan').value = '';
    document.getElementById('alamatcuti').value = '';
    document.getElementById('nohp').value = '';
    document.getElementById('hometrip').value = '';
    document.getElementById('tglberangkat').value = '';
    document.getElementById('rutekeberangkatan').value = '';
    document.getElementById('tglpulang').value = '';
    document.getElementById('rutekepulangan').value = '';
    document.getElementById('sis').innerHTML = '0';
    // closeDialog();
}

function loadSisaCuti(x) {
    periodecuti = document.getElementById('periodecuti').value;
    karyawanid = document.getElementById('karyawanid').value;
    idjenis = document.getElementById('idjenis').value;
    notransaksi = document.getElementById('notransaksi').value;

    param = 'method=loadSisaCuti';
    param += '&periodecuti=' + periodecuti;
    param += '&notransaksi=' + notransaksi;
    param += '&karyawanid=' + karyawanid;
    param += '&idjenis=' + idjenis;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("sis").innerHTML = con.responseText;
                    if (x == 1) {
                        document.getElementById("tglAwal").value = '';
                        document.getElementById("tglEnd").value = '';
                        document.getElementById("jam1").value = '00';
                        document.getElementById("mnt1").value = '00';
                        document.getElementById("jam2").value = '00';
                        document.getElementById("mnt2").value = '00';
                    }

                    loaddatakar();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatakar() {
    karyawanid = document.getElementById('karyawanid').value;

    param = 'method=loaddatakar';
    param += '&karyawanid=' + karyawanid;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data = con.responseText.split("###");
                    document.getElementById("tglMasuk").value = data[0];
                    document.getElementById("tglPengangkatan").value = data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function checkhometrip(cb) {

    cb = document.getElementById('hometrip');
    if (cb.checked == true) {
        document.getElementById('trtanggalberangkat').style.display = '';
        document.getElementById('trrutekeberangkatan').style.display = '';
        document.getElementById('trtanggalpulang').style.display = '';
        document.getElementById('trrutekepulangan').style.display = '';
    } else {
        document.getElementById('tglberangkat').value = '';
        document.getElementById('rutekeberangkatan').value = '';
        document.getElementById('tglpulang').value = '';
        document.getElementById('rutekepulangan').value = '';
        document.getElementById('trtanggalberangkat').style.display = 'none';
        document.getElementById('trrutekeberangkatan').style.display = 'none';
        document.getElementById('trtanggalpulang').style.display = 'none';
        document.getElementById('trrutekepulangan').style.display = 'none';
    }
}

function getjumlahcuti() {
    periodecuti = document.getElementById('periodecuti').value;
    tglAwal = document.getElementById('tglAwal').value;
    tglEnd = document.getElementById('tglEnd').value;
    idjenis = document.getElementById('idjenis').value;
    karyawanid = document.getElementById('karyawanid').value;
    notransaksi = document.getElementById('notransaksi').value;

    param = 'method=getjumlahcuti';
    param += '&periodecuti=' + periodecuti;
    param += '&tglAwal=' + tglAwal;
    param += '&tglEnd=' + tglEnd;
    param += '&idjenis=' + idjenis;
    param += '&karyawanid=' + karyawanid;
    param += '&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_cutistaff.php';

    if (tglEnd != '') {
        if (idjenis == '') {
            document.getElementById('tglAwal').value = '';
            document.getElementById('tglEnd').value = '';
            alert('Waring : Harap inputkan dari jenis ijin terlebih dahulu'); return;
        }
        else if (tglAwal == '') {
            document.getElementById('tglAwal').value = '';
            document.getElementById('tglEnd').value = '';
            alert('Waring : Harap inputkan dari tanggal terlebih dahulu'); return;
        }
        post_response_text(tujuan, param, respog);
    }
    else {
        if (idjenis == '') {
            document.getElementById('tglAwal').value = '';
            document.getElementById('tglEnd').value = '';
            alert('Waring : Harap inputkan dari jenis ijin terlebih dahulu'); return;
        }
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("jumlahhk").value = con.responseText;
                    loadSisaCuti();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function del(notransaksi) {
    param = 'method=delete' + '&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_cutistaff.php';

    if (confirm("Hapus data dengan notransaksi = " + notransaksi + "?")) {
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    hapus();
                    document.getElementById('container').innerHTML = con.responseText;
                    alert("Data Berhasil dihapus !!!");
                    loadData(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
                hapus();
            }
        }
    }
}

function saveForm() {
    karyawanid = document.getElementById('karyawanid').value;
    notransaksi = document.getElementById('notransaksi').value;
    tglijin = document.getElementById('tglIzin').value;
    periodecuti = document.getElementById('periodecuti').value;
    idjenis = document.getElementById('idjenis').value;
    tanggalkerja = document.getElementById('tanggalkerja').value;
    tglAwal = document.getElementById('tglAwal').value;
    jam1 = document.getElementById('jam1').value;
    mnt1 = document.getElementById('mnt1').value;
    tglEnd = document.getElementById('tglEnd').value;
    jam2 = document.getElementById('jam2').value;
    mnt2 = document.getElementById('mnt2').value;
    jumlahhk = document.getElementById('jumlahhk').value;
    sisa = document.getElementById('sis').innerHTML;
    nohp = document.getElementById('nohp').value;
    keperluan = document.getElementById('keperluan').value;
    ket = document.getElementById('ket').value;
    alamatcuti = document.getElementById('alamatcuti').value;
    pengganti = document.getElementById('pengganti').value;

    cb = 0;
    if (document.getElementById('hometrip').checked == true) {
        cb = 1;
    }
    tglberangkat = document.getElementById('tglberangkat').value;
    rutekeberangkatan = document.getElementById('rutekeberangkatan').value;
    tglpulang = document.getElementById('tglpulang').value;
    rutekepulangan = document.getElementById('rutekepulangan').value;
    apppengganti = document.getElementById('apppengganti').value;


    method = document.getElementById('method').value;

    if (notransaksi == '' && method != 'insert') {
        alert('Notransaksi Kosong'); return;
    }
    else if (karyawanid == '') {
        alert('Harap Mengisi Karyawan'); return;
    }
    else if (tglAwal == '') {
        alert('Harap Mengisi Nama Tanggal Awal'); return;
    }
    else if (tglEnd == '') {
        alert('Harap Mengisi Nama Tanggal Akhir'); return;
    }
    else if (nohp == '') {
        alert('Harap mengisi No HP'); return;
    }
    else if (keperluan == '') {
        alert('Harap pilih Keperluan'); return;
    }
    else if (idjenis == '') {
        alert('Harap pilih Jenis Izin/Cuti'); return;
    }

    else if (cb == 1) {
        if ((tglberangkat == '' || rutekeberangkatan == '') && (rutekepulangan == '' || tglpulang == '')) {

            alert('Harap mengisi salah satu Tanggal dan Rute Keberangkatan/Kepulangan'); return;

        } else {

            if (tglberangkat == '' && cb == 1) {
                rutekeberangkatan = '';
            }

            if (rutekeberangkatan == '' && cb == 1) {
                tglberangkat = '';
            }

            if (tglpulang == '' && cb == 1) {
                rutekepulangan = '';
            }

            if (rutekepulangan == '' && cb == 1) {
                tglpulang = '';
            }

            param = 'notransaksi=' + notransaksi;
            param += '&karyawanid=' + karyawanid;
            param += '&tglijin=' + tglijin;
            param += '&idjenis=' + idjenis;
            param += '&periodecuti=' + periodecuti;
            param += '&tanggalkerja=' + tanggalkerja;
            param += '&tglAwal=' + tglAwal;
            param += '&tglEnd=' + tglEnd;
            param += '&jumlahhk=' + jumlahhk;
            param += '&sisa=' + sisa;
            param += '&nohp=' + nohp;
            param += '&keperluan=' + keperluan;
            param += '&ket=' + ket;
            param += '&alamatcuti=' + alamatcuti;
            param += '&pengganti=' + pengganti;
            param += '&cb=' + cb;
            param += '&tglpulang=' + tglpulang;
            param += '&tglberangkat=' + tglberangkat;
            param += '&rutekepulangan=' + rutekepulangan;
            param += '&rutekeberangkatan=' + rutekeberangkatan;
            param += '&method=' + method;
            param += '&jam1=' + jam1;
            param += '&mnt1=' + mnt1;
            param += '&jam2=' + jam2;
            param += '&mnt2=' + mnt2;
            param += '&apppengganti=' + apppengganti;
            tujuan = 'sdm_slave_cutistaff.php';
            post_response_text(tujuan, param, respon);
        }
    }
    else {
        //pengganti tidak boleh kosong di batasi di Slave
        param = 'notransaksi=' + notransaksi;
        param += '&karyawanid=' + karyawanid;
        param += '&tglijin=' + tglijin;
        param += '&idjenis=' + idjenis;
        param += '&periodecuti=' + periodecuti;
        param += '&tanggalkerja=' + tanggalkerja;
        param += '&tglAwal=' + tglAwal;
        param += '&tglEnd=' + tglEnd;
        param += '&jumlahhk=' + jumlahhk;
        param += '&sisa=' + sisa;
        param += '&nohp=' + nohp;
        param += '&keperluan=' + keperluan;
        param += '&ket=' + ket;
        param += '&alamatcuti=' + alamatcuti;
        param += '&pengganti=' + pengganti;
        param += '&cb=' + cb;
        param += '&tglpulang=' + tglpulang;
        param += '&tglberangkat=' + tglberangkat;
        param += '&rutekepulangan=' + rutekepulangan;
        param += '&rutekeberangkatan=' + rutekeberangkatan;
        param += '&apppengganti=' + apppengganti;
        param += '&method=' + method;
        param += '&jam1=' + jam1;
        param += '&mnt1=' + mnt1;
        param += '&jam2=' + jam2;
        param += '&mnt2=' + mnt2;
        tujuan = 'sdm_slave_cutistaff.php';
        post_response_text(tujuan, param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function changetipe() {

    document.getElementById('tanggalcutiakhir').disabled = true;
    if (document.getElementById('tipebatal').value == 1) {
        document.getElementById('tanggalcutiakhir').disabled = false;
    }
    document.getElementById('tanggalcutiakhir').value = '';
}

function previewDetail(notransaksi) {
    param = 'method=preview';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // title   = 'Detail Cuti';
                    // width   = '';
                    // height  = '';
                    // ev      = 'event';
                    // content = "<fieldset style=max-width:600px><legend><b>"+ notransaksi +"</b></legend>"+con.responseText+"</fieldset>";
                    // closeDialog();
                    // showDialog2(title, content, width, height, ev);
                    alertify.popup().destroy();
                    alertify.popup(notransaksi, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('65%', '80%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function edit(notransaksi, karyawanid) {
    document.getElementById('addNew').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    document.getElementById('method').value = 'update';

    param = 'method=getedit';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    data = con.responseText.split("###");
                    document.getElementById('notransaksi').value = trim(notransaksi);
                    setValue2('karyawanid', trim(karyawanid));
                    document.getElementById('tglIzin').value = data[0];
                    document.getElementById('keperluan').value = data[1];
                    document.getElementById('ket').value = data[2];
                    document.getElementById('tglAwal').value = data[3];
                    document.getElementById('tglEnd').value = data[4];
                    setValue2('idjenis', data[5]);
                    document.getElementById('alamatcuti').value = data[6];
                    document.getElementById('pengganti').value = data[7];
                    document.getElementById('nohp').value = data[8];
                    if (data[9] == 1) {
                        document.getElementById('hometrip').checked = true;
                    }
                    if (data[10] == '00-00-0000') {
                        data[10] = '';
                    }
                    if (data[12] == '00-00-0000') {
                        data[12] = '';
                    }
                    document.getElementById('tglberangkat').value = data[10];
                    document.getElementById('rutekeberangkatan').value = data[11];
                    document.getElementById('tglpulang').value = data[12];
                    document.getElementById('rutekepulangan').value = data[13];
                    document.getElementById('tanggalkerja').value = data[14];
                    document.getElementById('periodecuti').innerHTML = data[15];
                    document.getElementById('jam1').value = data[16];
                    document.getElementById('mnt1').value = data[17];
                    document.getElementById('jam2').value = data[18];
                    document.getElementById('mnt2').value = data[19];
                    document.getElementById('apppengganti').value = data[20];
                    document.getElementById('karyawanid').disabled = true;
                    document.getElementById('periodecuti').disabled = true;
                    getjumlahcuti();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdf(notransaksi) {
    param = 'method=pdf';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_cutistaff.php?' + param;
    judul = 'Report PDF ' + notransaksi;
    ev = 'event';
    closeDialog();
    alertify.popuppdf(judul, "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}

function form_ajukan(notransaksi) {
    width = '300';
    height = '';
    content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog1(title, content, width, height, ev);

    param = 'method=form_ajukan&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('containeraju').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukan() {
    notransaksi = document.getElementById('notransaksi_ajukan').value;
    jlh = document.getElementById('jlh').value;
    var param = 'method=ajukan';
    param += '&notransaksi=' + notransaksi;
    param += '&jlh=' + jlh;
    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada' + i + "=" + document.getElementById('kepada' + i).value;
    }
    if (jlh == 0) {
        alertify.alert("Warning: Approval kosong");
        return;
    }
    tujuan = 'sdm_slave_cutistaff.php';
    closeDialog();
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert('Success....');
                    loadData(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukanbatalcuti() {
    notransaksi = document.getElementById('notransaksi_ajukan').value;
    jlh = document.getElementById('jlh').value;
    tipebatal = document.getElementById('tipebatal').value;
    tanggalcutiakhir = document.getElementById('tanggalcutiakhir').value;
    alasanbatal = document.getElementById('alasanbatal').value;
    var param = 'method=ajukanbatalcuti';
    param += '&notransaksi=' + notransaksi;
    param += '&jlh=' + jlh;
    param += '&tipebatal=' + tipebatal;
    param += '&tanggalcutiakhir=' + tanggalcutiakhir;
    param += '&alasanbatal=' + alasanbatal;
    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada' + i + "=" + document.getElementById('kepada' + i).value;;
    }
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert('Success....');
                    alertify.popup().destroy();
                    loadData(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function showupload(ev, notransaksi) {
    showformupload(ev);
    param = "";
    param += "notransaksi=" + notransaksi;
    param += '&method=showupload';
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(notransaksi);
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
    content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);

    pos = new Array();
    pos = getMouseP(ev);

    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function submitfile() {

    var file = document.getElementById("upload").files[0];
    var notrans = document.getElementById('notrans').innerHTML;
    var formdata = new FormData();

    formdata.append("notrans", notrans);
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));

    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }

    busy_on();
    var con = createXMLHttpRequest();
    con.open("POST", "sdm_slave_cutistaff.php?method=submitfile", true);
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
                    loadfiles(notrans);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loadfiles(notransaksi) {
    valuehidden = document.getElementById('valuehidden').value;

    param = 'method=loadfiles&notransaksi=' + notransaksi + '&valuehidden=' + valuehidden;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('loadfilesdetail') !== null) {
                        document.getElementById('loadfilesdetail').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function viewfile(sumber, idfile) {
    param = 'method=viewfile&idfile=' + idfile;
    tujuan = 'sdm_slave_cutistaff.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    // document.getElementById('contviewupload').innerHTML = con.responseText;
                    alertify.popup().destroy();
                    alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('80%', '70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}