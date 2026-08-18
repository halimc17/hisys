function formdetail(notransaksi, kball) {
    if (kball == '') {
        kball == 0;
    }

    // width = '';
    // height = '';
    // // content = "<fieldset><div id=containerd style=\"height:100%;width:100%;\"></div></fieldset>";
    // content = "<fieldset><div id=containerd style=\"height:650px;width:1000px;\"></div></fieldset>";
    // ev = 'event';
    // title = "";
    // showDialog1(title, content, width, height, ev);
    datadetail(notransaksi, 'popup', kball);
}

function datadetail(notransaksi, sumber, kball = 0) {
    if (notransaksi == '') {
        notransaksi = document.getElementById('notransaksi').value;
    }
    param = 'method=datadetail';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    if (sumber == 'popup') {
                        alertify.popup().set({ 'resizable': true, 'maximizable': true, 'startMaximized': false, 'message': con.responseText }).resizeTo('80%', '70%').show();
                        // document.getElementById('containerd').innerHTML = con.responseText;
                    } else {
                        document.getElementById('isidetail').innerHTML = con.responseText;
                    }
                    if (kball == 1) {
                        var select = document.getElementById('kelas');
                        var opt = document.createElement('option');
                        opt.value = 'S';
                        opt.innerHTML = 'Seluruh Buah';
                        select.appendChild(opt);

                        var select2 = document.getElementById('klsbuah');
                        var opt2 = document.createElement('option');
                        opt2.value = 'S';
                        opt2.innerHTML = 'Seluruh Buah';
                        select2.appendChild(opt2);
                    }
                    loaddatadt(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showhideinfo() {
    var row = document.getElementById('forminfo');
    if (row !== null) {
        if (row.style.display == '') {
            row.style.display = 'none';
        } else {
            row.style.display = '';
        }
    }
}


function getdisabled() {
    jenis = document.getElementById('jenis').value;
    if (jenis == 'vol') {
        document.getElementById('tanggaldari').disabled = true;
        document.getElementById('tanggalsampai').disabled = true;
        document.getElementById('tanggaldari').value = '';
        document.getElementById('tanggalsampai').value = '';
        document.getElementById('tanggalsampai').disabled = true;
        document.getElementById('volume').disabled = false;
        document.getElementById('batasbawah').disabled = false;
        document.getElementById('batasatas').disabled = false;
        document.getElementById('kadaluwarsa').value = '';
        document.getElementById('kadaluwarsa').disabled = true;
    } else {
        document.getElementById('tanggaldari').disabled = false;
        document.getElementById('tanggalsampai').disabled = false;
        document.getElementById('volume').disabled = true;
        document.getElementById('batasbawah').disabled = true;
        document.getElementById('batasatas').disabled = true;


        document.getElementById('volume').value = 0;
        document.getElementById('batasbawah').value = 0;
        document.getElementById('batasatas').value = 0;
        document.getElementById('kadaluwarsa').disabled = false;
    }
}

function displaylist() {
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('isidetail').innerHTML = '';
    document.getElementById('notransaksisch').disabled = false;

    setValue2('notransaksisch', '');
    setValue2('kodesuppliersch', '');
    setValue2('kodeunitsch', '');
    setValue2('kodebarangsch', '');
    setValue2('tanggalsch', '');
    setValue2('jenissch', '');

    loaddata(0);
}

function getpage() {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loaddata(paged);
}



function loaddata(num) {
    notransaksi = document.getElementById('notransaksisch').value;
    kodeunit = document.getElementById('kodeunitsch').value;
    tanggal = document.getElementById('tanggalsch').value;

    kodesupplier = document.getElementById('kodesuppliersch').value;
    kodebarang = document.getElementById('kodebarangsch').value;
    jenis = document.getElementById('jenissch').value;

    param = 'method=loaddata&page=' + num;
    param += '&notransaksi=' + notransaksi;

    param += '&kodeunit=' + kodeunit + '&tanggal=' + tanggal + '&kodesupplier=' + kodesupplier + '&kodebarang=' + kodebarang + '&jenis=' + jenis;

    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function saveht(parameter) {
    method = document.getElementById('methodht').value;
    tujuan = 'pmn_kontrakbeli_slave.php';
    var passP = parameter.split('###');
    var param = "";
    for (i = 1; i < passP.length; i++) {
        var tmp = document.getElementById(passP[i]);
        param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
    param += '&method=' + method;
    // alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    if (method == 'saveht') {
                        document.getElementById('notransaksi').value = con.responseText;
                    }

                    document.getElementById('saveht').disabled = true;
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('notransaksi').disabled = true;

                    if (document.getElementById('kball').checked == true) {
                        kball = '1';
                    } else {
                        kball = '0';
                    }
                    datadetail(notransaksi, '', kball);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function newdata() {
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('isidetail').innerHTML = '';
    cancelht();
    // document.getElementById('detailhead').style.display='none';
}

function cancelht() {
    document.getElementById('saveht').disabled = false;
    document.getElementById('detail').style.display = 'none';
    document.getElementById('kodebarang').value = '';
    document.getElementById('kodesupplier').value = '';
    document.getElementById('jenis').value = 'prd';
    document.getElementById('kodeunit').value = '';
    document.getElementById('tanggal').value = '';
    document.getElementById('tanggaldari').value = '';
    document.getElementById('tanggalsampai').value = '';
    document.getElementById('volume').value = '0';
    document.getElementById('batasbawah').value = '0';
    document.getElementById('batasatas').value = '0';
    document.getElementById('kadaluwarsa').value = '';
    document.getElementById('keterangan').value = '';
    document.getElementById('reffharga').value = '';
    document.getElementById('yesno').innerHTML = 'Tidak';
    document.getElementById('yesno2').innerHTML = 'Tidak';
    // document.getElementById('notransaksi').disabled=false;
    document.getElementById('notransaksi').value = '';
    document.getElementById('dropship').checked = false;
    // document.getElementById('kball').checked=false;
    document.getElementById('kball').checked = true;
    document.getElementById('tanggal').disabled = false;
    document.getElementById('kodebarang').disabled = false;
    document.getElementById('kodesupplier').disabled = false;
    document.getElementById('kodeunit').disabled = false;
}



function editht(notransaksi) {
    param = 'method=editht' + '&notransaksi=' + notransaksi;
    tujuan = 'pmn_kontrakbeli_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('listdata').style.display = 'none';
                    document.getElementById('header').style.display = 'block';
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('kodeunit').disabled = true;
                    document.getElementById('kodesupplier').disabled = true;
                    document.getElementById('kodebarang').disabled = true;
                    document.getElementById('tanggal').disabled = true;

                    ar = con.responseText.split("###");
                    document.getElementById('notransaksi').value = ar[0];
                    document.getElementById('notransaksi').disabled = true;
                    document.getElementById('kodeunit').value = ar[1];
                    document.getElementById('jenis').value = ar[2];
                    document.getElementById('kodesupplier').value = ar[3];
                    document.getElementById('kodebarang').value = ar[4];
                    document.getElementById('tanggal').value = ar[5];
                    document.getElementById('tanggaldari').value = ar[6];
                    document.getElementById('tanggalsampai').value = ar[7];
                    document.getElementById('volume').value = ar[8];
                    document.getElementById('batasbawah').value = ar[9];
                    document.getElementById('batasatas').value = ar[10];
                    document.getElementById('kadaluwarsa').value = ar[11];
                    document.getElementById('reffharga').value = ar[12];
                    document.getElementById('keterangan').value = ar[13];
                    getdisabled();
                    if (ar[14] == '1') {
                        document.getElementById('dropship').checked = true;
                        document.getElementById('yesno').innerHTML = 'Ya';
                    } else {
                        document.getElementById('dropship').checked = false;
                        document.getElementById('yesno').innerHTML = 'Tidak';
                    }
                    if (ar[15] == '1') {
                        document.getElementById('kball').checked = true;
                        document.getElementById('yesno2').innerHTML = 'Ya';
                    } else {
                        document.getElementById('kball').checked = false;
                        document.getElementById('yesno2').innerHTML = 'Tidak';
                    }
                    document.getElementById('methodht').value = 'updateht';
                    datadetail(ar[0], '', ar[15]);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}



function deletedt(id, notransaksi) {
    param = 'method=deletedt';
    param += '&id=' + id;
    tujuan = 'pmn_kontrakbeli_slave.php';

    alertify.confirm("Informasi", "Hapus data",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    alertify.set('notifier', 'position', 'top-right');
                    alertify.success('Berhasil');
                    loaddatadt(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postinght(notransaksi) {
    param = 'method=postinght';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'pmn_kontrakbeli_slave.php';

    alertify.confirm("Informasi", "Posting data transaksi " + notransaksi + "???",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.set('notifier', 'position', 'top-right');
                    alertify.success('Berhasil');
                    getpage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletedtfix(id, notransaksi) {
    param = 'method=deletedtfix';
    param += '&id=' + id;
    tujuan = 'pmn_kontrakbeli_slave.php';
    // if(confirm("Anda Yakin ???")){		
    // post_response_text(tujuan, param, respog);
    // }

    alertify.confirm("Informasi", "Hapus data",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    loaddatadt(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletedtinsentif(id, notransaksi) {
    param = 'method=deletedtinsentif';
    param += '&id=' + id;
    tujuan = 'pmn_kontrakbeli_slave.php';
    // if(confirm("Anda Yakin ???")){		
    // post_response_text(tujuan, param, respog);
    // }
    alertify.confirm("Informasi", "Hapus data",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    loaddatadt(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editdt(id) {
    param = 'method=editdt' + '&id=' + id;
    tujuan = 'pmn_kontrakbeli_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('iddt').value = id;
                    ar = con.responseText.split("###");
                    document.getElementById('tanggaldaridt').value = ar[0];
                    document.getElementById('tanggalsampaidt').value = ar[1];
                    document.getElementById('kelas').value = ar[2];
                    document.getElementById('harga').value = ar[3];
                    document.getElementById('ppn').value = ar[4];
                    document.getElementById('pph').value = ar[5];
                    document.getElementById('tahuntanam').value = ar[6];
                    document.getElementById('hargabrondolan').value = ar[7];
                    document.getElementById('methoddt').value = 'updatedt';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function editdtfix(id) {
    param = 'method=editdtfix' + '&id=' + id;
    tujuan = 'pmn_kontrakbeli_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('iddtfix').value = id;
                    ar = con.responseText.split("###");
                    document.getElementById('tanggaldaridtfix').value = ar[0];
                    document.getElementById('tanggalsampaidtfix').value = ar[1];
                    document.getElementById('batasbawahfix').value = ar[2];
                    document.getElementById('batasatasfix').value = ar[3];
                    document.getElementById('fixgrading').value = ar[4];
                    document.getElementById('methoddtfix').value = 'updatedtfix';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}


function editdtinsentif(id) {
    param = 'method=editdtinsentif' + '&id=' + id;
    tujuan = 'pmn_kontrakbeli_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('iddtinsentif').value = id;
                    ar = con.responseText.split("###");
                    document.getElementById('tanggaldaridtinsentif').value = ar[0];
                    document.getElementById('tanggalsampaidtinsentif').value = ar[1];
                    document.getElementById('batasbawahinsentif').value = ar[2];
                    document.getElementById('batasatasinsentif').value = ar[3];
                    document.getElementById('rpkginsentif').value = ar[4];
                    document.getElementById('methoddtinsentif').value = 'updatedtinsentif';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}



function savedt(parameter) {
    notransaksi = document.getElementById('notransaksidt').value;
    method = document.getElementById('methoddt').value;
    tujuan = 'pmn_kontrakbeli_slave.php';
    var passP = parameter.split('###');
    var param = "";
    for (i = 1; i < passP.length; i++) {
        var tmp = document.getElementById(passP[i]);
        param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
    param += '&notransaksi=' + notransaksi;
    param += '&method=' + method;
    // alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                    // alert(param);
                } else {
                    canceldt();
                    loaddatadt(notransaksi);
                    // alert(param);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function savedtfix(parameter) {
    notransaksi = document.getElementById('notransaksidt').value;
    method = document.getElementById('methoddtfix').value;
    tujuan = 'pmn_kontrakbeli_slave.php';
    var passP = parameter.split('###');
    var param = "";
    for (i = 1; i < passP.length; i++) {
        var tmp = document.getElementById(passP[i]);
        param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
    param += '&notransaksi=' + notransaksi;
    param += '&method=' + method;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    canceldtfix();
                    loaddatadt(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}


function savedtinsentif(parameter) {
    notransaksi = document.getElementById('notransaksidt').value;
    method = document.getElementById('methoddtinsentif').value;
    tujuan = 'pmn_kontrakbeli_slave.php';
    var passP = parameter.split('###');
    var param = "";
    for (i = 1; i < passP.length; i++) {
        var tmp = document.getElementById(passP[i]);
        param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
    param += '&notransaksi=' + notransaksi;
    param += '&method=' + method;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    canceldtinsentif();
                    loaddatadt(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function showform() {
    // width = '620';
    // height = '';
    // content = "<fieldset><div id=containerd style=\"width:600px;max-height:700px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // title = "";
    // showDialog4(title, content, width, height, ev);

    notransaksi = document.getElementById('notransaksidt').value;
    param = 'method=formupload';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                    alertify.alert('Informasi', con.responseText);
                } else {
                    // document.getElementById('containerd').innerHTML = con.responseText;
                    alertify.popup(con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('55%', '75%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function uploadcsv() {
    var notransaksi = document.getElementById('notransaksidt').value;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "pmn_kontrakbeli_slave.php?method=uploadcsv", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                    alertify.alert('Informasi', con.responseText);
                } else {
                    showform();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function canceldt() {
    document.getElementById('tanggaldaridt').value = '';
    document.getElementById('tanggalsampaidt').value = '';
    document.getElementById('kelas').value = '';
    document.getElementById('harga').value = '';
    document.getElementById('ppn').value = '';
    document.getElementById('pph').value = '';
    document.getElementById('tahuntanam').value = '';
    document.getElementById('hargabrondolan').value = '';
    document.getElementById('methoddt').value = 'savedt';
}
function canceldtfix() {
    document.getElementById('tanggaldaridtfix').value = '';
    document.getElementById('tanggalsampaidtfix').value = '';
    document.getElementById('batasatasfix').value = '';
    document.getElementById('batasbawahfix').value = '';
    document.getElementById('fixgrading').value = '';
    document.getElementById('methoddtfix').value = 'savedtfix';
}
function canceldtinsentif() {
    document.getElementById('tanggaldaridtinsentif').value = '';
    document.getElementById('tanggalsampaidtinsentif').value = '';
    document.getElementById('batasatasinsentif').value = '';
    document.getElementById('batasbawahinsentif').value = '';
    document.getElementById('rpkginsentif').value = '';
    document.getElementById('methoddtinsentif').value = 'savedtinsentif';
}


function yesno(span, cekbox) {
    ch = document.getElementById(cekbox);
    if (ch.checked == true) {
        document.getElementById(span).innerHTML = 'Ya';
    } else {
        document.getElementById(span).innerHTML = 'Tidak';
    }
}

function loaddatadt(notransaksi, num) {
    if (notransaksi == undefined) {
        notransaksi = document.getElementById('notransaksi').value;
    }

    tgl1 = document.getElementById('tgl1').value;
    tgl2 = document.getElementById('tgl2').value;
    klsbuah = document.getElementById('klsbuah').value;

    param = 'method=loaddatadt&page=' + num;
    param += '&notransaksi=' + notransaksi;
    param += '&tgl1=' + tgl1;
    param += '&tgl2=' + tgl2;
    param += '&klsbuah=' + klsbuah;

    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('listdatadt').innerHTML = con.responseText;
                    // loaddatadtfix(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loaddatadtfix(notransaksi) {
    if (notransaksi == undefined) {
        notransaksi = document.getElementById('notransaksi').value;
    }

    param = 'method=loaddatadtfix';
    param += '&notransaksi=' + notransaksi;

    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('listdatadtfix').innerHTML = con.responseText;
                    loaddatadtinsentif(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadtinsentif(notransaksi) {
    if (notransaksi == undefined) {
        notransaksi = document.getElementById('notransaksi').value;
    }

    param = 'method=loaddatadtinsentif';
    param += '&notransaksi=' + notransaksi;

    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('listdatadtinsentif').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteht(notransaksi) {
    param = 'method=deleteht';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'pmn_kontrakbeli_slave.php';
    // if(confirm("Anda Yakin ???")){		
    // post_response_text(tujuan, param, respog);
    // }

    alertify.confirm("Informasi", "Hapus transaksi : " + notransaksi + " ???",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );


    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}




function getoptdetail() {
    //saat ini untuk VHC, alokasi/blok, ADK, karyawan
    kodeorg = document.getElementById('kodeorg').value;
    // notransaksi= document.getElementById('notransaksi').value;
    // nourut= document.getElementById('nourut').value;

    method = 'getoptdetail';
    param = '';
    param += '&kodeorg=' + kodeorg;
    param += '&method=' + method;
    // param += '&notransaksi=' + notransaksi+'&nourut=' + nourut;
    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById('kodeasset').innerHTML = ar[0];
                    document.getElementById('nik').innerHTML = ar[1];
                    document.getElementById('kodevhc').innerHTML = ar[2];
                    document.getElementById('kodeblok').innerHTML = ar[3];
                    document.getElementById('noakun').innerHTML = ar[4];
                    loadfiles();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}






/*******************************************************************************************/
/*******************************************************************************************/
/*******************************************************************************************/

function submitfile() {
    var notransaksi = document.getElementById("notransaksi").value;
    var kriteriaefil = document.getElementById("kriteriaefil").value;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    formdata.append("notransaksi", trim(notransaksi));
    formdata.append("kriteriaefil", kriteriaefil);
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    document.getElementsByClassName("mybutton").disabled = true;
    busy_on();
    var con = createXMLHttpRequest();
    con.open("POST", "pmn_kontrakbeli_slave.php?method=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    //=== Success Response
                    document.getElementsByClassName("mybutton").disabled = false;
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfiles();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loadfiles() {
    notransaksi = document.getElementById('notransaksi').value;
    param = 'method=loadfiles&notransaksi=' + trim(notransaksi);
    // alert(param);
    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {

                    // if (document.getElementById('listfiles') !== null) {
                    // document.getElementById('listfiles').innerHTML = con.responseText;
                    // }
                    document.getElementById('listfiles').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(notransaksi, namafile) {
    param = 'method=deletefile&notransaksi=' + notransaksi + '&namafile=' + namafile;
    tujuan = 'pmn_kontrakbeli_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    loadfiles();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/**********************************************************************************/

function ajukan(notransaksi, page) {
    content = "<div id=formpost  style=\"height:100%;width:800px;\"></div>";
    title = 'Ajukan Persetujuan';
    height = '';
    width = '800';
    showDialog4(title, content, width, height, 'event');
    formajukan(notransaksi, page);
}

function formajukan(notransaksi, page) {
    method = 'formajukan';
    param = '';
    param += '&notransaksi=' + notransaksi + '&page=' + page;
    param += '&method=' + method;
    post_response_text(tujuan, param, respon);
    tujuan = 'pmn_kontrakbeli_slave.php';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('formpost').innerHTML = con.responseText;
                    // loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



// #= diubah menjadi persetujuan
function saveajukan(notransaksi, maxaproval, page) {
    param = '';
    method = 'saveajukan';
    strper = '';
    for (i = 1; i <= maxaproval; i++) {
        strper += '&persetujuan[' + i + ']=' + trim(document.getElementById('persetujuan' + i).value)
    }
    param += '&notransaksi=' + notransaksi + '&maxaproval=' + maxaproval;
    param += '&method=' + method;
    param += strper;
    // alert(param);
    tujuan = 'pmn_kontrakbeli_slave.php';

    // if(confirm('Ajukan No Jurnal : '+notransaksi+' ?')) {
    // post_response_text(tujuan, param, respon);
    // }

    alertify.confirm("Informasi", "Ajukan transaksi : " + notransaksi + " ???",
        function () {
            post_response_text(tujuan, param, respon);
        },
        function () {
            return;
        }
    );


    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    closeDialog4();
                    closeDialog();
                    loaddata(page);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function html(notransaksi, page) {
    ev = 'event';
    content = "<div id=detailhtml style=\"height:100%;width:100%;\"></div>";
    title = 'detail';
    height = '300';
    width = '1000';
    showDialog1(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic1').style.top = pos[1] + 'px';
    document.getElementById('dynamic1').style.left = (pos[0] - 500) + 'px';
    param = '';
    param += '&notransaksi=' + notransaksi + '&page=' + page;
    // alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi', con.responseText);
                } else {
                    document.getElementById('detailhtml').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('pmn_kontrakbeli_slave.php?method=html', param, respon);
}

//Umar
function form_ajukan(notransaksi) {
    let content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    let title = "Ajukan Pesangon : " + notransaksi;

    alertify.popup(title, content).set({ 'resizable': true, 'maximizable': true }).resizeTo('20%', '10%');

    let param = "method=form_ajukan";
    param += "&notransaksi=" + notransaksi;
    let tujuan = "pmn_kontrakbeli_slave.php";
    post_response_text(tujuan, param, function () {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containeraju').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    });
}

function ajukan() {
    let notransaksi = document.getElementById("notransaksi_ajukan");
    let jlh = document.getElementById("jlh");

    if (jlh.value == 0) {
        alertify.alert("Warning: Approval kosong");
        return;
    }

    let param = "method=ajukan";
    param += "&notransaksi=" + notransaksi.value;
    param += "&jlh=" + jlh.value;

    for (i = 1; i <= jlh.value; i++) {
        param += "&" + "kepada" + i + "=" + document.getElementById("kepada" + i).value;
    }

    let tujuan = "pmn_kontrakbeli_slave.php";
    post_response_text(tujuan, param, () => {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.alert('Info', 'Success');
                    loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    });
}
//End Umar




