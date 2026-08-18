function hapus() {
    document.getElementById('kodeorg').value = '';
    document.getElementById('tanggal').value = '';
    document.getElementById('dari').value = '';
    document.getElementById('sampai').value = '';
    document.getElementById('harga').value = '';
    document.getElementById('method').value = 'simpan';
}

function simpan() {
    idpremi = document.getElementById('idpremi').value;
    kodeorg = document.getElementById('kodeorg').value;
    tanggal = document.getElementById('tanggal').value;
    dari = document.getElementById('dari').value;
    sampai = document.getElementById('sampai').value;
    harga = document.getElementById('harga').value;
    method = document.getElementById('method').value;

    validate([
        ["kodeorg", "Kode organisasi tidak boleh kosong."],
        ["tanggal", "Tanggal tidak boleh kosong"]
    ]);

    param =  'kodeorg=' + kodeorg;
    param += '&idpremi=' + idpremi;
    param += '&tanggal=' + tanggal;
    param += '&dari=' + remove_comma_var(dari);
    param += '&sampai=' + remove_comma_var(sampai);
    param += '&harga=' + remove_comma_var(harga);
    param += '&method=' + method;
    tujuan = 'kebun_slave_5tbhnpremi.php';
    post_response_text(tujuan, param, respog);
    // alert(param);

    function respog() {
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


function createNew() {
    // document.getElementById('addNew').style.display = 'block';
    // document.getElementById('listData').style.display = 'block';
    document.getElementById('method').value = 'simpan';
    // batalcari();
    hapus();
}

function displayList() {
    hapus();
    // // batalcari();
    // document.getElementById('addNew').style.display = 'block';
    // document.getElementById('listData').style.display = 'block';
    loadData(0);
}


function getPage() {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loadData(paged);
}

function loadData(num) {

    param = 'method=loadData&page=' + num;
    tujuan = 'kebun_slave_5tbhnpremi.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    dataSlave = con.responseText.split("####");
                    document.getElementById('addNew').style.display = 'block';
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

function del(id) {
    param = 'method=delete' + '&idpremi=' + id;
    tujuan = 'kebun_slave_5tbhnpremi.php';


    if (confirm("Hapus Data???")) {
        post_response_text(tujuan, param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    hapus();
                    document.getElementById('container').innerHTML = con.responseText;
                    // alert("Data telah dihapus !!!");
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

function fillField(id,kodeorg, tanggal, dari, sampai,  harga) {
 
    document.getElementById('idpremi').value = id;
    document.getElementById('kodeorg').value = kodeorg;
    document.getElementById('tanggal').value = tanggal;
    document.getElementById('dari').value = dari;
    document.getElementById('sampai').value = sampai;
    document.getElementById('harga').value = harga;
    document.getElementById('method').value = 'update';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('addNew').style.display = 'block';
    // param = 'method=ambildata';
    param = '&id=' + id;
    param += '&kodeorg=' + kodeorg;
    param += '&tanggal=' + tanggal;
    tujuan = 'kebun_slave_5tbhnpremi.php';
    // alert(param);
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    dataSlave = con.responseText.split("###");
                    // document.getElementById('unit').innerHTML      = con.responseText;
                    // document.getElementById('addNew').style.display ='none';
                    // document.getElementById('listData').style.display ='block';
                    // document.getElementById('unit').innerHTML = dataSlave[0];
                    // document.getElementById('karyawan').innerHTML      = dataSlave[1];
                    // document.getElementById('notransaksi').value       = dataSlave[2];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

 