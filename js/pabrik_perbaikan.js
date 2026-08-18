
function showformupload(ev) {
    title = "UPLOAD FILES";
    width = '';
    height = '';
    content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function showupload(notransaksi) {
    ev = 'event';
    showformupload(ev);
    param = 'method=showupload&notransaksi=' + notransaksi;
    tujuan = 'pabrik_slave_perbaikan.php';
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

function submitfile() {
    var file = document.getElementById("uploaddata").files[0];
    var notransaksi = document.getElementById('notransaksiupload').innerHTML;
    var formdata = new FormData();
    formdata.append("fileupload", getValue('uploaddata'));
    formdata.append("file", file);
    formdata.append("notransaksi", notransaksi);
    if (getValue('uploaddata') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }

    var con = createXMLHttpRequest();
    document.getElementById('btnsubmit').disabled = true;
    busy_on();
    con.open("POST", "pabrik_slave_perbaikan.php?method=submitfile", true);
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
                    document.getElementById('btnsubmit').disabled = false;
                    document.getElementById("uploaddata").value = "";
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadfiles(notransaksi) {

    param = 'method=loadfiles&notransaksi=' + notransaksi;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listdatafiles') !== null) {
                        document.getElementById('listdatafiles').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form() {
    width = '';
    height = '';
    content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev);
}
function viewfile(ev, namafile) {
    ext = namafile.split(".");
    if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
        form();
        param = 'method=viewfile&namafile=' + namafile;
        tujuan = 'pabrik_slave_perbaikan.php';
        post_response_text(tujuan, param, respog);
    } else {
        alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
        return;
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contview').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(notransaksi, namafile) {
    param = "method=deletefile";
    param += "&notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setData(nodok, tglOrder, jmOrder, mnOrder, namaPemohon, statusPemohon, pabrik, station, mesin, shift, tipePerbaikan,
    uraianKerusakan, tglMulai, jmMulai, mnMulai, tglSelesai, jmSelesai, mnSelesai, jumlahJamPerbaikan,
    statusKetuntasan, hasilKerja, namaMesin, komMain, komPros, dwnstat, jenisperbaikan) {
    var re = /<br *\/?>/gi;

    document.getElementById('listData').style.display = 'none';
    document.getElementById('headher').style.display = 'block';
    document.getElementById('detailEntry').style.display = 'block';

    document.getElementById('nodok').value = nodok;
    document.getElementById('tglOrder').value = tglOrder;
    document.getElementById('jmOrder').value = jmOrder;
    document.getElementById('mnOrder').value = mnOrder;
    document.getElementById('namaPemohon').value = namaPemohon;
    document.getElementById('statusPemohon').value = statusPemohon;
    document.getElementById('pabrik').value = pabrik;
    document.getElementById('station').value = station;
    document.getElementById('jenisperbaikan').value = jenisperbaikan;
    document.getElementById('shift').value = shift;
    document.getElementById('tipePerbaikan').value = tipePerbaikan;
    document.getElementById('uraianKerusakan').value = uraianKerusakan.replace(re, '\n');
    document.getElementById('tglMulai').value = tglMulai;
    document.getElementById('jmMulai').value = jmMulai;
    document.getElementById('mnMulai').value = mnMulai;
    document.getElementById('tglSelesai').value = tglSelesai;
    document.getElementById('jmSelesai').value = jmSelesai;
    document.getElementById('mnSelesai').value = mnSelesai;
    document.getElementById('jumlahJamPerbaikan').value = jumlahJamPerbaikan;
    document.getElementById('statusKetuntasan').value = statusKetuntasan;
    document.getElementById('hasilKerja').value = hasilKerja.replace(re, '\n');
    document.getElementById('komMain').value = komMain.replace(re, '\n');
    document.getElementById('komPros').value = komPros.replace(re, '\n');
    document.getElementById('mesin').innerHTML = "<option value='" + mesin + "'>" + namaMesin + "</option>";
    jk = document.getElementById('dwnStat');
    for (x = 0; x < jk.length; x++) {
        if (jk.options[x].value == dwnstat) {
            jk.options[x].selected = true;
        }
    }
    document.getElementById('method').value = 'update';
    var dtisi = true;
    loadDetailBarang(dtisi, mesin);

    closeDialog();
}

function findNo(status) {
    txt = trim(document.getElementById('nocr').value);
    param = 'txtfind=' + txt + '&status=' + status + '&method=getno';
    tujuan = 'pabrik_slave_perbaikan.php';
    if (txt == '') {
        alert("No is obligatory");
    } else {
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

function add_form(title, status, content, ev) {
    width = '600';
    height = '520';
    //showDialog1(title,content,width,height,ev);
    getFormNoso(status);
}

function getFormNoso(status) {
    pros = "getFormPerbaikan";
    param = 'status=' + status + '&method=' + pros;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan + '?' + '', param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // alert(con.responseText);
                    // document.getElementById('formPencariandata').innerHTML=con.responseText;
                    alertify.popup("Detail", con.responseText).set({
                        'resizable': true,
                        'maximizable': true
                    }).resizeTo('80%', '70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function listdatalalu(title, ev) {
    mesin = document.getElementById('mesin').value;
    tglOrder = document.getElementById('tglOrder').value;
    if (tglOrder == '') {
        alert('Date empty');
        return;
    }
    if (mesin == '') {
        alert('Mechine empty');
        return;
    }
    content = "<div id=formlistdatalalu style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title = 'Record Maintenance ' + mesin;
    height = '';
    width = '';
    showDialog1(title, content, width, height, ev);
    getlistdatalalu(mesin, tglOrder);
}

function getlistdatalalu(mesin, tglOrder) {

    param = 'method=getlistdatalalu';
    param += '&mesin=' + mesin + '&tglOrder=' + tglOrder;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formlistdatalalu').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//#################


function tambahBarang(title, ev) {
    content = "<div id=formBarang style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title = 'Add Material';
    height = '';
    width = '';
    showDialog1(title, content, width, height, ev);
    getListBarang();
}

function getListBarang() {
    param = 'method=getListBarang';
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariListBarang() {
    pabrik = document.getElementById('pabrik').value;
    tglOrder = document.getElementById('tglOrder').value;
    mesin = document.getElementById('mesin').value;
    namaBarangCari = document.getElementById('namaBarangCari').value;
    param = 'method=getListBarang' + '&namaBarangCari=' + namaBarangCari + '&pabrik=' + pabrik + '&tglOrder=' + tglOrder + '&mesin=' + mesin;

    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function moveDataBarang(nogudang, kodebarang, namabarang, satuanbarang, hargabarang, jumlahBarang) {
    document.getElementById('nogudang').value = nogudang;
    document.getElementById('kodeBarang').value = kodebarang;
    document.getElementById('namaBarang').value = namabarang;
    document.getElementById('jumlahBarang').value = jumlahBarang;
    document.getElementById('satuanBarang').value = satuanbarang;
    document.getElementById('hargabarang').value = hargabarang;
    //document.getElementById('').innerHTML=con.responseText;
    document.getElementById('listCariBarang').style.display = 'none';
    closeDialog();

}

function getMesin(station, mesin) {
    // document.getElementById('mesin').disabled = true;
    station = document.getElementById('station').value;
    param = 'method=getMesin' + '&station=' + station + '&mesin=' + mesin;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('mesin').innerHTML = con.responseText;
                    if(getValue('method')!='update'){
                        getNodok();
                    }
                    //.value=trim(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getNodok() {
    station = document.getElementById('station').value;
    tglOrder = document.getElementById('tglOrder').value;
    param = 'method=getNodok' + '&station=' + station + '&tglOrder=' + tglOrder;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('nodok').value = trim(con.responseText);
                    //.value=trim(con.responseText);
                    document.getElementById('mesin').disabled = false;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function get_isi(kdorg) {
    //param='kdorg='+kdorg'+;
    param = 'method=getnomor' + '&kdorg=' + kdorg;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    //alert(param);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('notran').value = trim(con.responseText);
                    //document.getElementById('dtl_pem').disabled=false;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBast(num) {
    param = 'method=loadData';
    param += '&page=' + num;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //displayList();

                    document.getElementById('contain').innerHTML = con.responseText;
                    //loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cari() {
    schNodok = trim(document.getElementById('schNodok').value);
    schTgl = trim(document.getElementById('schTgl').value);
    schdwnStat = trim(document.getElementById('schdwnStat').value);
    schstatusKetuntasan = trim(document.getElementById('schstatusKetuntasan').value);
    schstation = trim(document.getElementById('schstation').value);
    param = 'schNodok=' + schNodok;
    param += '&schTgl=' + schTgl;
    param += '&schdwnStat=' + schdwnStat;
    param += '&schstatusKetuntasan=' + schstatusKetuntasan;
    param += '&schstation=' + schstation;
    param += '&method=loadData'; //loadSch
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listData').style.display = 'block';
                    document.getElementById('headher').style.display = 'none';
                    document.getElementById('detailEntry').style.display = 'none';
                    document.getElementById('contain').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData(num) {
    param = 'method=loadData&page=' + num;
    tujuan = 'pabrik_slave_perbaikan.php';
    //alert(param);
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //displayList();

                    document.getElementById('contain').innerHTML = con.responseText;
                    //loadData();
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

function Lock(notran) {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;

    param = 'method=lock' + '&notran=' + notran;
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    if (confirm("Anda yakin ingin mengunci ??")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contain').innerHTML = con.responseText;
                    loadData(paged);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//function uuntuk delet headernya yg ada di list tampilan data
function Del(notran) {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;

    param = 'method=delete' + '&notran=' + notran;
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    if (confirm(' Anda yakin ingin menghapus nomor transaksi ' + notran + ' ')) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contain').innerHTML = con.responseText;
                    loadData(paged);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function add_new_data() { //indra
    //alert('MASUK COI');
    //alert(con.responseText);
    document.getElementById('headher').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    document.getElementById('detailEntry').style.display = 'none';
    cancelHead();
    document.getElementById('containListBarang').innerHTML = '';
    document.getElementById('containListPekerjaan').innerHTML = '';
    document.getElementById('containListKaryawan').innerHTML = '';
    //bukaform();

    document.getElementById('method').value = 'insert';

}

function displayList() {
    document.getElementById('listData').style.display = 'block';
    document.getElementById('headher').style.display = 'none';
    document.getElementById('detailEntry').style.display = 'none';
    document.getElementById('schTgl').value = '';
    document.getElementById('schNodok').value = '';

    document.getElementById('schdwnStat').value = '';
    document.getElementById('schstatusKetuntasan').value = '';
    document.getElementById('schstation').value = '';
    loadData(0);
}

function saveHeader() //save header + buka input detail di sini
{
    nodok = document.getElementById('nodok').value;
    tglOrder = document.getElementById('tglOrder').value;
    jmOrder = document.getElementById('jmOrder').value;
    mnOrder = document.getElementById('mnOrder').value;
    namaPemohon = document.getElementById('namaPemohon').value;
    statusPemohon = document.getElementById('statusPemohon').value;
    pabrik = document.getElementById('pabrik').value;
    station = document.getElementById('station').value;
    mesin = document.getElementById('mesin').value;
    shift = document.getElementById('shift').value;
    tipePerbaikan = document.getElementById('tipePerbaikan').value;
    uraianKerusakan = document.getElementById('uraianKerusakan').value;
    tglMulai = document.getElementById('tglMulai').value;
    jmMulai = document.getElementById('jmMulai').value;
    mnMulai = document.getElementById('mnMulai').value;
    tglSelesai = document.getElementById('tglSelesai').value;
    jmSelesai = document.getElementById('jmSelesai').value;
    mnSelesai = document.getElementById('mnSelesai').value;
    jumlahJamPerbaikan = document.getElementById('jumlahJamPerbaikan').value;
    statusKetuntasan = document.getElementById('statusKetuntasan').value;
    hasilKerja = document.getElementById('hasilKerja').value;
    komMain = document.getElementById('komMain').value;
    komPros = document.getElementById('komPros').value;
    method = document.getElementById('method').value;
    dwnStat = document.getElementById('dwnStat');
    dwnStat = dwnStat.options[dwnStat.selectedIndex].value;
    jenisperbaikan = document.getElementById('jenisperbaikan').value;
    if (nodok == '' || tglOrder == '' || pabrik == '' || station == '' || mesin == '') {
        alert('please compleate the form');
        return;
    }

    param = 'nodok=' + nodok + '&tglOrder=' + tglOrder + '&jmOrder=' + jmOrder + '&mnOrder=' + mnOrder + '&namaPemohon=' + namaPemohon;
    param += '&statusPemohon=' + statusPemohon + '&pabrik=' + pabrik + '&station=' + station + '&mesin=' + mesin + '&shift=' + shift;
    param += '&tipePerbaikan=' + tipePerbaikan + '&uraianKerusakan=' + uraianKerusakan;
    param += '&tglMulai=' + tglMulai + '&jmMulai=' + jmMulai + '&mnMulai=' + mnMulai;
    param += '&tglSelesai=' + tglSelesai + '&jmSelesai=' + jmSelesai + '&mnSelesai=' + mnSelesai;
    param += '&jumlahJamPerbaikan=' + jumlahJamPerbaikan + '&statusKetuntasan=' + statusKetuntasan + '&hasilKerja=' + hasilKerja;
    param += '&komMain=' + komMain + '&komPros=' + komPros;
    param += '&method=' + method + '&dwnStat=' + dwnStat + '&jenisperbaikan=' + jenisperbaikan;
    // alert(param);
    // return;


    tujuan = 'pabrik_slave_perbaikan.php';
    //if(confirm('Anda yakin menyimpan no. transaksi '+notran+' ?\nPeriksa kembali inputan anda\nkarena tidak bisa di edit untuk header ! '))
    //{
    post_response_text(tujuan, param, respon);
    //}
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('detailEntry').style.display = 'block';
                    if (mesin != '') {
                        getSubMsn(mesin, 0);
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function saveBarang() {
    nogudang = document.getElementById('nogudang').value;
    nodok = document.getElementById('nodok').value;
    kodeBarang = document.getElementById('kodeBarang').value;
    jumlahBarang = document.getElementById('jumlahBarang').value;
    satuanBarang = document.getElementById('satuanBarang').value;
    keteranganBarang = document.getElementById('keteranganBarang').value;
    hargabarang = document.getElementById('hargabarang').value;
    param = 'nodok=' + nodok + '&kodeBarang=' + kodeBarang + '&jumlahBarang=' + jumlahBarang + '&hargabarang=' + hargabarang + '&nogudang=' + nogudang;
    param += '&satuanBarang=' + satuanBarang + '&keteranganBarang=' + keteranganBarang + '&mnMulai=' + mnMulai;
    param += "&method=saveBarang";
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //bersihdetail();
                    bersihFormBarang();
                    loadDetailBarang();
                    //document.getElementById('containListBarang').style.display='block';
                    //document.getElementById('contentDetail').innerHTML=con.responseText;
                    // Success Response
                    //alert(con.responseText);
                    //document.getElementById('detailEntry').style.display='block';
                    //document.getElementById('detailIsi').innerHTML=con.responseText;
                    //document.getElementById('tmbLheader').innerHTML='';
                    //lockForm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function bersihFormBarang() {
    document.getElementById('nogudang').value = '';
    document.getElementById('kodeBarang').value = '';
    document.getElementById('jumlahBarang').value = '';
    document.getElementById('satuanBarang').value = '';
    document.getElementById('keteranganBarang').value = '';
    document.getElementById('namaBarang').value = '';
}

function deleteBarang(nodok, kodeBarang) {
    param = 'method=deleteBarang' + '&nodok=' + nodok + '&kodeBarang=' + kodeBarang;
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    //if(confirm(' Anda yakin ingin menghapus karyawan ini dari daftar lembur?? '))
    //{
    post_response_text(tujuan, param, respog);
    //}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadDetailBarang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loadDetailBarang(firstload, kdmesin) {
    if (typeof firstload == 'undefined') {
        firstload = false;
    }
    nodok = document.getElementById('nodok').value;
    param = 'nodok=' + nodok;
    param += '&method=loadDetailBarang';
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    //return;
                    //document.getElementById('contentDetail').innerHTML=con.responseText;
                    document.getElementById('containListBarang').innerHTML = con.responseText;
                    if (firstload)
                        loadDetailPekerjaan(firstload, kdmesin);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/*
//pekerjaan
function savePekerjaan(){
nodok=document.getElementById('nodok').value;
nomor=document.getElementById('nomor').value;
rincian=document.getElementById('rincian');
rincian=rincian.options[rincian.selectedIndex].value;
kondisi=document.getElementById('kondisi').value;
sbKd=document.getElementById('sbmesin');
sbKd=sbKd.options[sbKd.selectedIndex].value;
param='nodok='+nodok+'&nomor='+nomor+'&rincian='+rincian+'&kondisi='+kondisi;
param+="&method=savePekerjaan"+"&sbMesin="+sbKd;
// alert(param);
// return;
tujuan='pabrik_slave_perbaikan.php';
post_response_text(tujuan, param, respon);
function respon(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
} else {
loadDetailPekerjaan();
bersihFormPekerjaan();
}
} else {
busy_off();
error_catch(con.status);
}
}
}
}
 */

//pekerjaan
function savePekerjaan() {
    nodok = document.getElementById('nodok').value;
    nomor = document.getElementById('nomor').value;
    rincian = document.getElementById('rincian').value;
    kondisi = document.getElementById('kondisi').value;
    sbKd = document.getElementById('sbmesin');
    sbKd = sbKd.options[sbKd.selectedIndex].value;
    param = 'nodok=' + nodok + '&nomor=' + nomor + '&rincian=' + rincian + '&kondisi=' + kondisi;
    param += "&method=savePekerjaan" + "&sbMesin=" + sbKd;
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadDetailPekerjaan();
                    bersihFormPekerjaan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function bersihFormPekerjaan() {
    document.getElementById('nomor').value = '';
    document.getElementById('jumlahBarang').value = '';
    document.getElementById('rincian').value = '';
    document.getElementById('kondisi').value = '';
    document.getElementById('sbmesin').value = '';
}

function deletePekerjaan(nodok, nomor) {
    param = 'method=deletePekerjaan' + '&nodok=' + nodok + '&nomor=' + nomor;
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    //if(confirm(' Anda yakin ingin menghapus karyawan ini dari daftar lembur?? '))
    //{
    post_response_text(tujuan, param, respog);
    //}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadDetailPekerjaan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteHead(nodok) {
    // pg = document.getElementById('page');
    // pg = pg.options[pg.selectedIndex].value;
    // paged = parseFloat(pg) - 1;

    param = 'method=deleteHead' + '&nodok=' + nodok;
    tujuan = 'pabrik_slave_perbaikan.php';
    if (confirm(' Anda yakin ingin menghapus ' + nodok + ' ?? ')) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadDetailPekerjaan(firstload, kdmesin) {
    if (typeof firstload == 'undefined') {
        firstload = false;
    }
    nodok = document.getElementById('nodok').value;
    param = 'nodok=' + nodok;
    param += '&method=loadDetailPekerjaan';
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    document.getElementById('containListPekerjaan').innerHTML = con.responseText;
                    if (firstload)
                        loadDetailKaryawan(firstload, kdmesin);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//karyawan
function saveKaryawan() {
    nodok = document.getElementById('nodok').value;
    karyawan = document.getElementById('karyawan').value;
    param = 'nodok=' + nodok + '&karyawan=' + karyawan;
    param += "&method=saveKaryawan";
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    bersihFormKaryawan();
                    loadDetailKaryawan();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function bersihFormKaryawan() {
    document.getElementById('karyawan').value = '';
}

function deleteKaryawan(nodok, karyawan) {
    param = 'method=deleteKaryawan' + '&nodok=' + nodok + '&karyawan=' + karyawan;
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    //if(confirm(' Anda yakin ingin menghapus karyawan ini dari daftar lembur?? '))
    //{
    post_response_text(tujuan, param, respog);
    //}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadDetailKaryawan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loadDetailKaryawan(firstload, kdmesin) {
    nodok = document.getElementById('nodok').value;
    param = 'nodok=' + nodok;
    param += '&method=loadDetailKaryawan';
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    document.getElementById('containListKaryawan').innerHTML = con.responseText;
                    getSubMsn(kdmesin, 0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//JS untuk delete detailnya

function update() {}

function fillField(nodok, tglOrder, jmOrder, mnOrder, namaPemohon, statusPemohon, pabrik, station, mesin, shift, tipePerbaikan,
    uraianKerusakan, tglMulai, jmMulai, mnMulai, tglSelesai, jmSelesai, mnSelesai, jumlahJamPerbaikan,
    statusKetuntasan, hasilKerja, namaMesin, komMain, komPros, dwnstat, jenisperbaikan) {
    var re = /<br *\/?>/gi;
    document.getElementById('listData').style.display = 'none';
    document.getElementById('headher').style.display = 'block';
    document.getElementById('detailEntry').style.display = 'block';
    document.getElementById('nodok').value = nodok;
    document.getElementById('tglOrder').value = tglOrder;
    document.getElementById('jmOrder').value = jmOrder;
    document.getElementById('mnOrder').value = mnOrder;
    document.getElementById('namaPemohon').value = namaPemohon;
    document.getElementById('statusPemohon').value = statusPemohon;
    document.getElementById('pabrik').value = pabrik;
    getStation()
    document.getElementById('jenisperbaikan').value=jenisperbaikan;
    document.getElementById('shift').value = shift;
    document.getElementById('tipePerbaikan').value = tipePerbaikan;
    document.getElementById('uraianKerusakan').value = uraianKerusakan.replace(re, '\n');
    document.getElementById('tglMulai').value = tglMulai;
    document.getElementById('jmMulai').value = jmMulai;
    document.getElementById('mnMulai').value = mnMulai;
    document.getElementById('tglSelesai').value = tglSelesai;
    document.getElementById('jmSelesai').value = jmSelesai;
    document.getElementById('mnSelesai').value = mnSelesai;
    document.getElementById('jumlahJamPerbaikan').value = jumlahJamPerbaikan;
    document.getElementById('statusKetuntasan').value = statusKetuntasan;
    document.getElementById('hasilKerja').value = hasilKerja.replace(re, '\n');
    document.getElementById('komMain').value = komMain.replace(re, '\n');
    document.getElementById('komPros').value = komPros.replace(re, '\n');
    // document.getElementById('mesin').innerHTML = "<option value='" + mesin + "'>" + namaMesin + "</option>";
    document.getElementById('station').disabled = true;
    // document.getElementById('mesin').disabled = true;
    document.getElementById('tglOrder').disabled = true;
    document.getElementById('jmOrder').disabled = true;
    document.getElementById('mnOrder').disabled = true;
    jk = document.getElementById('dwnStat');
    for (x = 0; x < jk.length; x++) {
        if (jk.options[x].value == dwnstat) {
            jk.options[x].selected = true;
        }
    }
    document.getElementById('method').value = 'update';
    var dtisi = true;
    setTimeout(() => {
        document.getElementById('station').value = station;
        loadDetailBarang(dtisi, mesin);
        setTimeout(() => {
            getMesin(station,mesin)
        }, 1200);
    }, 900);
    //loadDetailPekerjaan();
    //loadDetailKaryawan();
    //document.getElementById('detailForm').style.display='block';
    //loadDataDetail();
}

function cancelHead() {
    document.getElementById('nodok').value = '';
    document.getElementById('tglOrder').value = '';
    document.getElementById('jmOrder').value = '00';
    document.getElementById('mnOrder').value = '00';
    document.getElementById('namaPemohon').value = '';
    document.getElementById('jenisperbaikan').value = '';
    document.getElementById('statusPemohon').value = 'P';
    document.getElementById('station').value = '';
    document.getElementById('mesin').value = '';
    document.getElementById('shift').value = '1';
    document.getElementById('tipePerbaikan').value = 'prev';
    document.getElementById('uraianKerusakan').value = '';
    document.getElementById('tglMulai').value = '';
    document.getElementById('jmMulai').value = '00';
    document.getElementById('mnMulai').value = '00';
    document.getElementById('tglSelesai').value = '';
    document.getElementById('jmSelesai').value = '00';
    document.getElementById('mnSelesai').value = '00';
    document.getElementById('jumlahJamPerbaikan').value = '';
    document.getElementById('statusKetuntasan').value = '';
    document.getElementById('hasilKerja').value = '';
    document.getElementById('komMain').value = '';
    document.getElementById('komPros').value = '';
    document.getElementById('station').disabled = false;
    document.getElementById('mesin').disabled = false;
    document.getElementById('tglOrder').disabled = false;
    document.getElementById('jmOrder').disabled = false;
    document.getElementById('mnOrder').disabled = false;
    document.getElementById('detailEntry').style.display = 'none';
}

function getStation() {
    pabrik = document.getElementById('pabrik').value;
    param = 'pabrik=' + pabrik + '&menu=pemeliharaan';
    param += '&proses=getStation';
    tujuan = 'pabrik_slave_2perbaikan.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('station').innerHTML = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getStationv() {
    pabrikv = document.getElementById('pabrikv').value;
    param = 'pabrikv=' + pabrikv;
    param += '&proses=getStationv';
    tujuan = 'pabrik_slave_2perbaikan_v2.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('stationv').innerHTML = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function detailBarang(nodok, ev) {
    content = "<div id=formBarang style=\"height:200px;width:500px;overflow:auto;\"></div>";
    title = 'No. Job Order : ' + nodok;
    height = '';
    width = '';
    showDialog1(title, content, width, height, ev);
    getListBarangLaporan(nodok);
}

function getListBarangLaporan(nodok) {
    param = 'proses=getListBarangLaporan' + '&nodok=' + nodok;
    //alert(param);
    tujuan = 'pabrik_slave_2perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function batalLaporan() {
    document.getElementById('unit').value = '';
    document.getElementById('tgl2').value = '';
    document.getElementById('tgl1').value = '';
    document.getElementById('printContainer').innerHTML = '';
}
/*
function getSubMsn(kdmesin,sbMsnCd){

param='method=getSbMsn';
if(kdmesin=='0'){
kdmesin=document.getElementById('mesin');
kdmesin=kdmesin.options[kdmesin.selectedIndex].value;
}
param+='&kdmesin='+kdmesin;
if(sbMsnCd!='0'){
param+='&sbMesin='+sbMsnCd;
}

//alert(param);
tujuan = 'pabrik_slave_perbaikan.php';
post_response_text(tujuan, param, respog);
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
//alert(con.responseText);
//document.getElementById('sbmesin').innerHTML=con.responseText;
isdt = con.responseText.split("####");
document.getElementById('sbmesin').innerHTML = isdt[0];
document.getElementById('rincian').innerHTML = isdt[1];
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}*/

function getSubMsn(kdmesin, sbMsnCd) {
    nodok = document.getElementById('nodok').value;
    param = 'method=getSbMsn';
    if (kdmesin == '0') {
        kdmesin = document.getElementById('mesin');
        kdmesin = kdmesin.options[kdmesin.selectedIndex].value;
    }
    param += '&kdmesin=' + kdmesin;
    if (sbMsnCd != '0') {
        param += '&sbMesin=' + sbMsnCd;
    }
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('sbmesin').innerHTML = con.responseText;
                    // loadfiles(nodok);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getactivity() {

    param = 'method=getactivity';
    rincian = document.getElementById('rincian');
    rincian = rincian.options[rincian.selectedIndex].value;
    param += '&rincian=' + rincian;
    //alert(param);
    tujuan = 'pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('activity').value = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postTrans(notran, paged, statusketuntasan) {
    if (statusketuntasan != 'Selesai') {
        alert('Posting Hanya Dapat dilakukan jika status perbaikan SELESAI');
        return;
    }
    param = 'method=postingDt' + '&nodok=' + notran;
    tujuan = 'pabrik_slave_perbaikan.php';
    if (confirm("Anda Yakin Posting Data :" + notran)) {
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
                    cariBast(paged);

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}