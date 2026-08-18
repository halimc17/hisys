function copymandor(no) {
    mandor = document.getElementById('mandor' + no).value;
    ttlrow = document.getElementById('ttlrow').value;

    for (i = no; i <= ttlrow; i++) {
        document.getElementById('mandor' + i).value = mandor;
    }
}
function lihatdatakary() {
    divisi = document.getElementById('divisi').value;

    param = 'method=lihatdatakary';
    param += '&divisi=' + divisi;
    tujuan = 'kebun_slave_5mandor';
    post_response_text(tujuan + '.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('previewdatatabular').style.display = '';
                    document.getElementById('previewdatatabular').innerHTML = con.responseText;

                    // $(document).ready(function() {
                    // $('.select2').select2({
                    // dropdownAutoWidth:true
                    // });
                    // });
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function batal() {
    procces = document.getElementById('procces').value;
    // if(procces=='tambahkaryawan'){
    // document.getElementById('mandor').value='';
    // document.getElementById('anggota').style.display='none';
    // }
    document.getElementById('urut').value = '';
    document.getElementById('status').value = 1;
    document.getElementById('status').disabled = true;
    document.getElementById('mandor').disabled = false;
    document.getElementById('karyawan').disabled = false;
    document.getElementById('procces').value = 'tambahkaryawan';
    updatekaryawan();
}


function tampilmandor() {
    param = 'method=tampilmandor';
    tujuan = 'kebun_slave_5mandor';
    post_response_text(tujuan + '.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                    pilihmandor();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnourut() {
    // mandor = document.getElementById('mandor');
    mandor = document.getElementById('mandor').value;

    param = 'method=getnourut&mandor=' + mandor;
    tujuan = 'kebun_slave_5mandor';
    post_response_text(tujuan + '.php', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('urut').value = trim(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function updatekaryawan() {
    // mandor = document.getElementById('mandor');
    mandor = document.getElementById('mandor').value;


    param = 'method=tampilkaryawan&mandor=' + mandor;
    tujuan = 'kebun_slave_5mandor';
    if (mandor != '')
        post_response_text(tujuan + '.php', param, respon);
    else
        document.getElementById('karyawan').innerHTML = '<option value=\'\'></option>';

    function respon() {
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

function updatemandor(mandor) {
    param = 'method=syntampilmandor&mandor=' + mandor;
    tujuan = 'kebun_slave_5mandor';

    post_response_text(tujuan + '.php', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('mandor').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pilihmandor(pilihanmandor) {
    // mandor = document.getElementById('mandor');
    mandor = document.getElementById('mandor').value;

    if (typeof pilihanmandor !== 'undefined' && pilihanmandor != null && pilihanmandor != '') {
        mandor = pilihanmandor;
        document.getElementById('mandor').value = pilihanmandor;
    }

    param = 'method=pilihmandor&mandor=' + mandor;
    tujuan = 'kebun_slave_5mandor';
    post_response_text(tujuan + '.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (mandor == '') {
                        document.getElementById('anggota').style.display = 'none';
                    } else {
                        document.getElementById('anggota').style.display = '';
                        document.getElementById('anggota').innerHTML = con.responseText;
                    }
                    updatekaryawan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function tambahkaryawan() {
    mandor = document.getElementById('mandor');
    mandor = mandor.options[mandor.selectedIndex].value;
    karyawan = document.getElementById('karyawan');
    karyawan = karyawan.options[karyawan.selectedIndex].value;
    urut = document.getElementById('urut').value;
    status = document.getElementById('status').value;
    process = document.getElementById('procces').value;

    param = 'method=' + process + '&mandor=' + mandor + '&karyawan=' + karyawan + '&urut=' + urut + '&status=' + status;
    tujuan = 'kebun_slave_5mandor';

    if (karyawan == '' || urut == '') {
        alert('Karyawan dan No. harus diisi');
    } else {
        post_response_text(tujuan + '.php', param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    batal();
                    // alert('Berhasil simpan data mandor dan karyawan.');
                    pilihmandor(mandor);
                    tampilmandor();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function hapuskaryawan(karyawan) {
    mandor = document.getElementById('mandor');
    mandor = mandor.options[mandor.selectedIndex].value;

    param = 'method=hapuskaryawan&mandor=' + mandor + '&karyawan=' + karyawan;
    tujuan = 'kebun_slave_5mandor';

    if (confirm('Yakin hapus data karyawan?'))
        post_response_text(tujuan + '.php', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    batal();
                    alert('Berhasil hapus data karyawan.');
                }
                pilihmandor(mandor);
                tampilmandor();
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function hapusmandor(mandor) {
    param = 'method=hapusmandor&mandor=' + mandor;
    tujuan = 'kebun_slave_5mandor';

    if (confirm('Yakin hapus data mandor?'))
        post_response_text(tujuan + '.php', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    batal();
                    alert('Berhasil hapus data mandor.');
                    document.getElementById('mandor').value = '';
                    tampilmandor();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editkaryawan(karyawanid, namakaryawan, status, nourut) {
    document.getElementById('mandor').disabled = true;
    document.getElementById('karyawan').disabled = true;
    document.getElementById('urut').value = nourut;
    document.getElementById('karyawan').innerHTML = '<option value=' + karyawanid + '>' + namakaryawan + ' [' + karyawanid + ']</option>';
    document.getElementById('status').disabled = false;
    document.getElementById('status').value = status;
    document.getElementById('procces').value = 'editkaryawan';
}

function savealldetail(maxRow) {
    if (maxRow == '' || maxRow == 0) {
        alert('Data tidak ditemukan, proses dibatalkan.');
        return;
    }
    if (confirm("Simpan semua ???")) {
        sumber = 'simpanall';
        savedetail(1, maxRow, sumber);
    }
}

function savedetail(currRow, maxRow, sumber) {
    divisi = document.getElementById('divisi').value;
    karyawan = document.getElementById('kary' + currRow).innerHTML;
    urut = document.getElementById('urut' + currRow).value;
    mandor = document.getElementById('mandor' + currRow).value;

    if (karyawan == '' || urut == '') {
        alert('Karyawan dan No. harus diisi');
    }

    param = "";
    param += "&divisi=" + divisi;
    param += "&karyawan=" + karyawan;
    param += "&urut=" + urut;
    param += "&mandor=" + mandor;
    param += "&method=simpankary";

    tujuan = 'kebun_slave_5mandor.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    unlockScreen();
                } else {
                    if (document.getElementById('row' + currRow) != undefined) {
                        if (document.getElementById('row' + currRow).style.backgroundColor == 'cyan') {
                            document.getElementById('row' + currRow).style.backgroundColor = '';
                        } else {
                            document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
                        }
                    }
                    document.getElementById('row' + currRow).style.display = 'none';

                    currRow += 1;
                    if ((currRow > maxRow) || (maxRow == undefined)) {
                        alert("Done");
                        if (sumber == 'simpanall') {
                            multiinput();
                            tampilmandor;
                        }
                    } else {
                        savedetail(currRow, maxRow, sumber);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function multiinput(jenis) {
    if (jenis == 'multi') {
        document.getElementById('forminputtabular').style.display = '';
        document.getElementById('previewdatatabular').style.display = '';
        document.getElementById('forminputdetail').style.display = 'none';
        document.getElementById('listdatadetail').style.display = 'none';
    } else {
        document.getElementById('forminputtabular').style.display = 'none';
        document.getElementById('previewdatatabular').style.display = 'none';
        document.getElementById('forminputdetail').style.display = '';
        document.getElementById('listdatadetail').style.display = '';
    }
}