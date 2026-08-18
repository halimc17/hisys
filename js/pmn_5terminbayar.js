//JS 



function simpan() {
    kode = trim(document.getElementById('kode').value);
    satu = trim(document.getElementById('satu').value);
    dua = trim(document.getElementById('dua').value);
    tiga = trim(document.getElementById('tiga').value);
    empat = trim(document.getElementById('empat').value);
    lima = trim(document.getElementById('lima').value);
    method = document.getElementById('method').value;

    if (kode == '' || satu == '' || dua == '') {
        alert('Please complete the form'); return;
    }

    param = 'kode=' + kode + '&satu=' + satu + '&dua=' + dua + '&tiga=' + tiga + '&empat=' + empat + '&lima=' + lima + '&method=' + method;
    tujuan = 'pmn_slave_5terminbayar.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    hapus();
                    loadData();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
// function cekTotalTermin(e) {

function cekTotalTermin(elemen) {
    const idList = ['satu', 'dua', 'tiga', 'empat', 'lima'];
    let total = 0;

    // Hitung total sementara tanpa elemen yang sedang diblur
    idList.forEach(id => {
        if (document.getElementById(id) !== elemen) {
            total += parseFloat(document.getElementById(id).value) || 0;
        }
    });

    // Ambil nilai elemen yang sedang diblur
    let nilaiSekarang = parseFloat(elemen.value) || 0;

    // Periksa apakah total melebihi 100
    if ((total + nilaiSekarang) > 100) {
        alert("Total nilai tidak boleh lebih dari 100. Input ini akan di-reset.");
        elemen.value = ""; // reset input terakhir
        elemen.focus();    // arahkan kembali ke input tersebut
    }
}


function hapus() {
    document.getElementById('kode').value = '';
    document.getElementById('satu').value = '';
    document.getElementById('dua').value = '';
    document.getElementById('tiga').value = '';
    document.getElementById('empat').value = '';
    document.getElementById('lima').value = '';
    document.getElementById('method').value = 'insert';
    document.getElementById('kode').disabled = false;
    //method=document.getElementById('method').value;
}

function loadData() {
    param = 'method=loadData';
    tujuan = 'pmn_slave_5terminbayar.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    // alert(con.responseText);
                    document.getElementById('container').innerHTML = con.responseText;

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(kode, satu, dua, tiga, empat, lima) {
    document.getElementById('kode').value = kode;
    document.getElementById('satu').value = satu;
    document.getElementById('dua').value = dua;
    document.getElementById('tiga').value = tiga;
    document.getElementById('empat').value = empat;
    document.getElementById('lima').value = lima;
    document.getElementById('kode').disabled = true;
    document.getElementById('method').value = 'update';
}

function del(kode) {
    param = 'method=delete' + '&kode=' + kode;
    //alert(param);
    tujuan = 'pmn_slave_5terminbayar.php';
    //if(confirm("Delete data for "+kdorg+" period "+kodesupplier+" ?"))
    if (confirm("Delete data?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('container').innerHTML = con.responseText;
                    loadData();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //alert("Data telah terhapus !!!");	
}
