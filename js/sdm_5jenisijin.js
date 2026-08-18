/**
 * @author repindra.ginting
 */
function loadData1() {
    // alert('masukk');
    param = 'method=loadData';
    // param+='&supplierid='+idsupplier_detail;
    tujuan = 'sdm_slave_5jenisijin.php';
    // alert(param);
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // alert(con.responseText);

                    document.getElementById('container').innerHTML = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan() {
    idjenis = document.getElementById('idjenis').value;
    jenis = document.getElementById('jenis').value;
    umakan = document.getElementById('umakan').value;
    utransport = document.getElementById('utransport').value;
    statuspot = document.getElementById('statuspot').value;
    jumlahhk = remove_comma(document.getElementById('jumlahhk'));
    potonganhk = remove_comma(document.getElementById('potonganhk'));
    method = document.getElementById('method').value;
    if (jumlahhk == '' || jenis == '') {
        alert('Each Field are obligatory');
        return;
    }
    param = 'idjenis=' + idjenis + '&jenis=' + jenis + '&jumlahhk=' + jumlahhk + '&method=' + method + '&potonganhk=' + potonganhk;
    param += '&umakan=' + umakan + '&utransport=' + utransport + '&statuspot=' + statuspot;
    tujuan = 'sdm_slave_5jenisijin.php';
    // alert(param);

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    cancel();
                    loadData1(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function edit(idjenis, jenis, jumlahhk, potonganhk, umakan, utransport, statuspotongan) {
    document.getElementById('idjenis').value = idjenis;
    document.getElementById('idjenis').disabled = true;
    document.getElementById('jenis').value = jenis;
    document.getElementById('jumlahhk').value = jumlahhk;
    document.getElementById('potonganhk').value = potonganhk;
    document.getElementById('umakan').value = umakan;
    document.getElementById('utransport').value = utransport;
    //document.getElementById('statuspotongan').value = statuspotongan;
    document.getElementById('method').value = 'update';
}

function cancel() {
    document.getElementById('idjenis').value = '';
    document.getElementById('jenis').value = '';
    // document.getElementById('namasupplier').value='';
    document.getElementById('jumlahhk').value = 0;
    document.getElementById('potonganhk').value = 0;
    document.getElementById('umakan').value = 0;
    document.getElementById('utransport').value = 0;
    //document.getElementById('statuspotongan').value = 0;
    document.getElementById('method').value = 'insert';
}