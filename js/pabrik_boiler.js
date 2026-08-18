// JavaScript Document
function clearForm() {
    document.getElementById('jenis').value = '';
    document.getElementById('tgl').value = '';
    document.getElementById('dataIsian').innerHTML = '';
    //form cari
    //    document.getElementById('jnsCr').value='';
    document.getElementById('tglCr').value = '';
    document.getElementById('tglCr2').value = '';
}
function displayList() {
    document.getElementById('listData').style.display = 'block';
    document.getElementById('headher').style.display = 'none';
    clearForm();
    loadData(0);
    alertify.popup().destroy();
}
function lockForm() {
    document.getElementById('jenis').disabled = true;
    document.getElementById('tgl').disabled = true;
    document.getElementById('tombolHeader').style.display = "none";
}
function unlockForm() {
    document.getElementById('jenis').disabled = false;
    document.getElementById('tgl').disabled = false;
    document.getElementById('tombolHeader').style.display = "block";
    clearForm();
}

function loadData(num) {
    // pilJns=document.getElementById('jnsCr');
    // pilJns=pilJns.options[pilJns.selectedIndex].value;
    tgl = document.getElementById('tglCr').value;
    tgl2 = document.getElementById('tglCr2').value;

    param = 'proses=loadNewData&page=' + num;
    param += '&tgl=' + tgl + '&tgl2=' + tgl2;
    tujuan = 'pabrik_slave_boiler.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPage() {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loadData(paged);
}

function getTable() {
    jns = document.getElementById('jenis');
    jns = jns.options[jns.selectedIndex].value;
    tujuan = 'pabrik_slave_boiler.php';
    param = 'proses=getTable' + '&jenis=' + jns;
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('dataIsian').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function saveDt() {
    tanggal = document.getElementById('tgl').value;
    jns = document.getElementById('jenis');
    jns = jns.options[jns.selectedIndex].value;
    totDt = document.getElementById('totRow').value;
    var datNil = '';
    for (itung = 0; itung < totDt; itung++) {
        var nil = document.getElementById('nil_' + itung).value;
        var paramDt = document.getElementById('param_' + itung).value;
        datNil += "&nilai[" + itung + "]=" + nil;
        datNil += "&paramDt[" + itung + "]=" + paramDt;
    }
    tujuan = 'pabrik_slave_boiler.php';
    param = 'proses=saveDt' + '&jenis=' + jns + '&tanggal=' + tanggal + '&totRow=' + totDt;
    param += datNil;
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //displayList();
                    document.getElementById('dataIsian').innerHTML = '';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function upDt() {
    tanggal = document.getElementById('tgl2').value;
    totDt = document.getElementById('totRow2').value;
    var datNil = '';
    for (itung = 0; itung < totDt; itung++) {
        var nil = document.getElementById('nil_' + itung).value;
        var paramDt = document.getElementById('param_' + itung).value;
        datNil += "&nilai[" + itung + "]=" + nil;
        datNil += "&paramDt[" + itung + "]=" + paramDt;
    }
    tujuan = 'pabrik_slave_boiler.php';
    param = 'proses=update' + '&tanggal=' + tanggal + '&totRow=' + totDt;
    param += datNil;
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deletehead(notrans) {
    param = 'tanggal=' + notrans + '&proses=deletehead';
    tujuan = 'pabrik_slave_boiler.php';
    if (confirm("Anda Yakin Menghapus?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //document.getElementById('tmbLheader').innerHTML='';
                    loadData(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function detaildt(title, notransaksi) {
    title = title + " " + notransaksi;
    // width = '450px';
    // height = '650px';
    // formListPP(title, width, height);
    param = 'tgl=' + notransaksi + '&proses=htmlDetail';
    tujuan = 'pabrik_slave_boiler.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('containerData').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('800px','500px');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function formListPP(title, wdth, heig) {
    //closeDialog();
    width = '';
    height = '';
    if (wdth != '') {
        width = wdth;
    }
    if (heig != '') {
        height = heig;
    }

    content = "<div id=containerData></div>";
    ev = 'event';
    showDialog4(title, content, width, height, ev);
}