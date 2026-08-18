function simpan(e) {
    e.disabled = true;
    kodeorg = document.getElementById('kodeorg').value;
    kodeorght = document.getElementById('kodeorght').value;
    tanggal = document.getElementById('tanggal').value;
    tangga1 = document.getElementById('tanggal1').value;
    tanggalht = document.getElementById('tanggalht').value;
    jenis = document.getElementById('jenis').value;
    //jenis1=document.getElementById('jenis1').value;
    met = document.getElementById('method').value;

    /*if(trim(tanggal)==''){
    alert('Code is empty');
    document.getElementById('tanggal').focus();
    }
    else{	*/
    kodeorg = kodeorg;
    kodeorght = kodeorght;
    tanggal = tanggal;
    tanggal1 = tanggal1;
    tanggalht = tanggalht;
    jenis = jenis;
    //jenis1=jenis1;

    param = 'kodeorg=' + kodeorg + '&kodeorght=' + kodeorght + '&tanggalht=' + tanggalht + '&tanggal=' + tanggal + '&tanggal1=' + tanggal1 + '&jenis=' + jenis + '&method=' + met;
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';
    //alert(param);
    post_response_text(tujuan, param, respog);

    //}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    e.disabled = false;
                } else {
                    loaddetail(con.responseText); //ngirim id setelah insert parent

                }
            } else {
                e.disabled = false;
                busy_off();
                error_catch(con.status);
            }
        } else {
            e.disabled = false;
        }
    }
}
function loaddetail(id) {
    if (isNaN(id) == true) {
        return false;
    }
    id_parent = id;
    kodeorg = document.getElementById('kodeorg').value;
    tanggal = document.getElementById('tanggal').value;
    jenis = document.getElementById('jenis').value;

    met = "loaddetail";

    kodeorg = kodeorg;
    tanggal = tanggal;
    jenis = jenis;
    param = 'id_parent=' + id_parent + '&kodeorg=' + kodeorg + '&tanggal=' + tanggal + '&jenis=' + jenis + '&method=' + met;
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    var data = con.responseText.split("#####");
                    document.getElementById('containerdetail').innerHTML = data[0];
                    document.getElementById('container').innerHTML = data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpandtl() {
    var jam = document.getElementsByClassName('jam');
    var menit = document.getElementsByClassName('menit');
    var tekanan = document.getElementsByClassName('tekanan');
    strjam = "";
    for (i = 0; i < jam.length; i++) {
        strjam += "&jam[]=" + jam[i].value;
    }

    strtekanan = "";
    for (i = 0; i < tekanan.length; i++) {
        strtekanan += "&tekanan[]=" + tekanan[i].value;
    }
    met = "insertdtl";
    parentid = document.getElementById('parentid').value;

    strmenit = "";
    for (i = 0; i < menit.length; i++) {
        strmenit += "&menit[]=" + menit[i].value;
    }
    met = "insertdtl";
    parentid = document.getElementById('parentid').value;

    jam = jam;
    tekanan = tekanan;
    menit = menit;
    param = 'id_parent=' + id_parent + strjam + strtekanan + strmenit + '&method=' + met;
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';
    //alert(param);
    post_response_text(tujuan, param, respog);

    //}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    loaddetail(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function displayList() {
    document.getElementById('header').style.display = 'none';
    document.getElementById('listData').style.display = 'block';
    //document.getElementById('listDatadt').style.display = 'none';
    //document.getElementById('listDatadt1').style.display = 'none';
    //loaddata(0);
}

function add_new_data() {
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    //document.getElementById('listDatadt').style.display = 'none';
    //document.getElementById('listDatadt1').style.display = 'none';
    cancel();
}

function priview() {
    kodeorg = document.getElementById('kodeorg').value;
    tanggal1 = document.getElementById('tanggal1').value;
    tanggal2 = document.getElementById('tanggal2').value;

    // met=document.getElementById('method').value;
    //

    param = 'kodeorg=' + kodeorg + '&tanggal1=' + tanggal1 + '&tanggal2=' + tanggal2 + '&method=preview';
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';
    post_response_text(tujuan, param, callback);

    function callback() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contain').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function priview1(num) {

    kodeorg1 = document.getElementById('kodeorg1').value;
    tanggal = document.getElementById('tanggal').value;
    //jenis1=document.getElementById('jenis1').value;

    // met=document.getElementById('method').value;
    //

    param = 'kodeorg1=' + kodeorg1 + '&tanggal=' + tanggal + '&method=preview1';
	param += '&page=' + num;
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';
    post_response_text(tujuan, param, callback);

    function callback() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contain').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function excel(ev, tujuan) {
    kodeorg = document.getElementById('kodeorg').value;
    tanggal1 = document.getElementById('tanggal1').value;
    tanggal2 = document.getElementById('tanggal2').value;

    if (kodeorg == '' || tanggal1 == '') {
        alert('Lengkapi Kode dan periode');
        return;
    }
    judul = 'Report Ms.Excel';
    param = 'kodeorg=' + kodeorg + '&tanggal1=' + tanggal1 + '&tanggal2=' + tanggal2 + '&method=preview&toexcel=yes';
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';
    printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '600';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog2(title, content, width, height, ev);
}

function edit(id, kodeorg, tanggal, jenis) {
    //document.getElementById('id').value = id;
    document.getElementById('kodeorght').value = kodeorg;
    document.getElementById('tanggalht').value = tanggal;
    document.getElementById('jenis').value = jenis;
    document.getElementById('listData').style.display = 'none';
    document.getElementById('header').style.display = 'block';
    prosesdetail();
    loaddetail(id);

}

function del(id)
{

    parentid = document.getElementById('parentid').value;
    param = 'method=delete' + '&id=' + id;
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddetail(id_parent);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function cancel() {
    document.getElementById('kodeorg').value = '';
    document.getElementById('tanggal').value = '';
    document.getElementById('jenis').value = '';

    document.getElementById('kodeorght').disabled = false;
    document.getElementById('tanggalht').disabled = false;
    document.getElementById('jenis').disabled = false;
    if (document.getElementById('simpanht').disabled == true) {
        document.getElementById('simpanht').disabled = false;
    }
    document.getElementById('containerdetail').innerHTML = '';
    document.getElementById('container').innerHTML = '';
}

function prosesdetail() {
    document.getElementById('kodeorght').disabled = true;
    document.getElementById('tanggalht').disabled = true;
    document.getElementById('jenis').disabled = true;
    document.getElementById('simpanht').disabled = true;
}

function deletedata(id) {
    param = 'id=' + id + '&method=deletedata';
    tujuan = 'pabrik_slave_analisa_pengamatantekanan.php';

    if (confirm('Anda yakin hapus item ini???')) {
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
                    priview1();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}