function pdf(nodo) {

    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    param = 'proses=pdf' + '&nodo=' + nodo;
    tujuan = tujuan + '?' + param;
    // alert(param);
    // alert(tujuan);
    //content = document.getElementById('test');
    content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
    width = '820';
    height = '500';
    title = "";
    // showDialog5(title, content, width, height, 'event');
    alertify.popuppdf("PDF", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_slave_suratperintahpengiriman.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}


function form_ajukan(nodo) {
    param = "proses=form_ajukan" + "&nodo=" + nodo;
    console.log(param);

    tujuan = "pmn_slave_suratperintahpengiriman.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('containeraju').innerHTML = con.responseText;
                    alertify
                        .popup("Approval", con.responseText)
                        .set({ resizable: true, overflow: false })
                        .resizeTo("400px", "300px");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukan() {
    jumlahlevel = document.getElementById("numrow").value;
    kepada = "";
    for (var i = 1; i <= jumlahlevel; i++) {
        if (kepada == "") {
            kepada = document.getElementById("kepada" + i).value;
        } else {
            kepada += "###" + document.getElementById("kepada" + i).value;
        }
    }
    nodo = document.getElementById("notran_aju").innerHTML;
    jenispersetujuanx = document.getElementById("jenispersetujuanx").value;
    param =
        "proses=ajukan" +
        "&nodo=" +
        nodo +
        "&kepada=" +
        kepada +
        "&jenispersetujuanx=" +
        jenispersetujuanx;
    if (kepada == "") {
        alert("Isikan nama penyetuju.");
        return;
    }

    tujuan = "pmn_slave_suratperintahpengiriman.php";

    alertify.confirm('Konfirmasi', 'Ajukan Transaksi ini ??', function () {
        post_response_text(tujuan, param, respog);
    }, function () {
        // Cancelled
    });
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                    closeDialog();
                    alertify.popup().destroy();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}





function posting(nodo) {
    param = 'proses=posting' + '&nodo=' + nodo;
    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    if (confirm('Anda yakin ingin memposting No. DO ' + nodo + ' ??')) {
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

function loadData(page) {
    ntrs = document.getElementById('txtsearch').value;
    tglcr = document.getElementById('tgl_cari').value;
    produksch = document.getElementById('produksch').value;
    param = 'proses=loadData' + '&page=' + page;
    if (ntrs != '') {
        param += '&noinvoice=' + ntrs;
    }
    if (tglcr != '') {
        param += '&tanggalCr=' + tglcr;
    }
    if (produksch != '') {
        param += '&produksch=' + produksch;
    }
    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('formInput').style.display = 'none';
                    document.getElementById('listData').style.display = 'block';
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                    clearData();
                    // closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancelData() {
    document.getElementById('formInput').style.display = 'none';
    document.getElementById('listData').style.display = 'block';
    clearData();
}

function clearData() {
    document.getElementById('nokontrak').value = '';
    document.getElementById('nokontrak').disabled = false;
    //document.getElementById('nokontrakInternal').disabled=true;
    //document.getElementById('nokontrakInternal').value='';
    document.getElementById('nodo').value = '';
    document.getElementById('kodecustomer').value = '';
    document.getElementById('kepada').value = '';
    document.getElementById('tanggalsurat').value = '';
    document.getElementById('waktupenyerahan').value = '';
    document.getElementById('tglberangkat').value = '';
    document.getElementById('tempatpenyerahan').value = '';
    document.getElementById('dibuat').value = '';
    document.getElementById('lain').value = '';
    document.getElementById('jabatan').value = '';
    document.getElementById('ttd1').value = '';
    document.getElementById('ttd2').value = '';
    document.getElementById('namaponton').value = '';
    document.getElementById('spkpmuat').value = '';
    document.getElementById('qty').value = '0';
    document.getElementById('subsidi').value = '0.25';
    document.getElementById('lokasido').value = '';
    document.getElementById('lokasido').disabled = false;
    document.getElementById('transportir').value = '';
    document.getElementById('kondisi').value = '';
    document.getElementById('harga').value = '';
    document.getElementById('nmkpl').value = '';
    document.getElementById('plbmuat').value = '';
    document.getElementById('tgltiba2').value = '';
    document.getElementById('tgltiba1').value = '';
    document.getElementById('plbbongkar').value = '';
    document.getElementById('penyerahan').value = '';
    document.getElementById('statTimbangan').value = '';
    document.getElementById('noakun').value = '';
    document.getElementById('kodebarang').value = '';
    document.getElementById('kodebarang').disabled = false;
}

function searchKontrak(title, status, content, ev) {
    width = '';
    height = '';
    showDialog1(title, content, width, height, ev);
    getFormNosibp(status);
}

function getFormNosibp(status) {
    param = 'status=' + status + '&proses=getFormNosipb';
    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    post_response_text(tujuan + '?' + '', param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formPencariandata').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findNosipb(status) {
    txt = trim(document.getElementById('nosipbcr').value);
    param = 'txtfind=' + txt + '&status=' + status + '&proses=getnosibp';
    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    if (txt == '') {
        alert("Nosipb is obligatory");
    } else {
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
                    //alert(con.responseText);
                    document.getElementById('container2').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setData(nokontrak, kdcust, kodept, kodeorg, dari, sampai, kuantitaskontrak, status, franco, lokasikontrak) {
    document.getElementById('nokontrak').value = nokontrak;
    document.getElementById('lokasido').value = lokasikontrak;
    kridit = document.getElementById('kodecustomer');
    for (a = 0; a < kridit.length; a++) {
        if (kridit.options[a].value == kdcust) {
            kridit.options[a].selected = true;
        }
    }
    kridit.disabled = true;
    document.getElementById('kepada').value = kodept;
    if (sampai == '00-00-0000') {
        document.getElementById('waktupenyerahan').value = dari;
    } else {
        document.getElementById('waktupenyerahan').value = dari + ' s/d ' + sampai;
    }

    document.getElementById('tempatpenyerahan').value = franco;

    param = 'proses=getQty' + '&nokontrak=' + nokontrak + '&kuantitaskontrak=' + kuantitaskontrak;
    tujuan = 'pmn_slave_suratperintahpengiriman.php';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('qty').value = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    post_response_text(tujuan, param, respog);
    closeDialog();
}


function saveData(fileTarget, passParam) {
    // if(document.getElementById('nokontrak').value=='')
    // {
    // alert('No Kontrak harus diisi.');
    // return false;
    // }
    if(document.getElementById('transportir').value=='')
    {
    	alert('Transportir harus diisi.');
    	return false;
    }
    if (document.getElementById('tanggalsurat').value == '') {
        alert('Tanggal surat harus diisi.');
        return false;
    }
    if (document.getElementById('ttd1').value == '') {
        alert('Tanda Tangan harus diisi.');
        return false;
    }

    if (document.getElementById('qty').value == '' || document.getElementById('qty').value == 0) {
        alert('Qty harus lebih besar dari 0.');
        return false;
    }
    // if(document.getElementById('harga').value==''||document.getElementById('harga').value==0)
    // {
    // // alert('Harga harus lebih besar dari 0.');
    // // return false;
    // }
    var passP = passParam.split('##');
    var param = "";
    for (i = 1; i < passP.length; i++) {
        var tmp = document.getElementById(passP[i]);
        if (i == 1) {
            param += passP[i] + "=" + getValue(passP[i]);
        } else {
            param += "&" + passP[i] + "=" + getValue(passP[i]);
        }


        /*
         if(i==1) {
            param += passP[i]+"="+remove_comma_var(getValue(passP[i]));
        } else {
            param += "&"+passP[i]+"="+remove_comma_var(getValue(passP[i]));
        }
        */
    }
    param += '&proses=insert';

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                    cancelData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget + '.php', param, respon);

}

function fillField(nodo) {
    param = 'proses=getData' + '&nodo=' + nodo;
    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('formInput').style.display = 'block';
                    document.getElementById('listData').style.display = 'none';
                    isis = con.responseText.split("###");
                    document.getElementById('nokontrak').value = isis[0];
                    document.getElementById('nokontrak').disabled = true;
                    document.getElementById('nodo').value = isis[1];
                    kdcst = document.getElementById('kodecustomer');
                    for (a = 0; a < kdcst.length; a++) {
                        if (kdcst.options[a].value == isis[2]) {
                            kdcst.options[a].selected = true;
                        }
                    }

                    document.getElementById('kepada').value = isis[10];
                    document.getElementById('tanggalsurat').value = isis[4];
                    document.getElementById('waktupenyerahan').value = isis[5];
                    document.getElementById('tempatpenyerahan').value = isis[6];
                    document.getElementById('dibuat').value = isis[7];
                    document.getElementById('lain').value = isis[8];
                    document.getElementById('jabatan').value = isis[9];
                    document.getElementById('ttd1').value = isis[11];
                    document.getElementById('qty').value = isis[12];
                    //document.getElementById('nokontrakInternal').value=isis[13];
                    // document.getElementById('statpph').value=isis[14];
                    // document.getElementById('subsidi').value=isis[15];
                    statpph = document.getElementById('statpph');
                    for (a = 0; a < statpph.length; a++) {
                        if (statpph.options[a].value == isis[14]) {
                            statpph.options[a].selected = true;
                        }
                    }
                    document.getElementById('subsidi').value = isis[15];

                    document.getElementById('harga').value = isis[16];



                    statTimbangan = document.getElementById('statTimbangan');
                    for (a = 0; a < statTimbangan.length; a++) {
                        if (statTimbangan.options[a].value == isis[17]) {
                            statTimbangan.options[a].selected = true;
                        }
                    }


                    document.getElementById('transportir').value = isis[18];
                    document.getElementById('lokasido').value = isis[19];
                    document.getElementById('lokasido').disabled = true;


                    document.getElementById('nmkpl').value = isis[20];
                    document.getElementById('tgltiba1').value = isis[21];
                    document.getElementById('tgltiba2').value = isis[22];
                    document.getElementById('plbbongkar').value = isis[23];
                    document.getElementById('plbmuat').value = isis[24];
                    document.getElementById('kondisi').value = isis[25];
                    document.getElementById('pt').value = isis[26];
                    document.getElementById('namaponton').value = isis[27];
                    document.getElementById('ttd2').value = isis[28];
                    document.getElementById('spkpmuat').value = isis[29];
                    document.getElementById('penyerahan').value = isis[30];
                    document.getElementById('tglberangkat').value = isis[31];
                    document.getElementById('toleransi').value = isis[32];
                    document.getElementById('kgtoleransi').value = isis[33];
                    document.getElementById('noakun').value = isis[34];
                    document.getElementById('kodebarang').value = isis[35];
                    document.getElementById('kodebarang').disabled = true;

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function delData(nodo) {
    param = 'nodo=' + nodo + '&proses=delData';
    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    if (confirm("Anda yakin menghapus no do ini? " + nodo)) {
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
                    //alert(con.responseText);
                    getPage();
                }
            }
            else {
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

function displayFormInput() {
    clearData();
    document.getElementById('formInput').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
}

function cariData(pg) {

    nokontrak = document.getElementById('txtsearchkontrak').value;

    ntrs = document.getElementById('txtsearch').value;
    tglcr = document.getElementById('tgl_cari').value;
    produksch = document.getElementById('produksch').value;
    param = 'proses=loadData' + '&page=' + pg;
    if (ntrs != '') {
        param += '&nodo=' + ntrs;
    }
    if (tglcr != '') {
        param += '&tanggalCr=' + tglcr;
    }
    if (nokontrak != '') {
        param += '&nokontrak=' + nokontrak;
    }
    if (produksch != '') {
        param += '&produksch=' + produksch;
    }

    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('formInput').style.display = 'none';
                    document.getElementById('listData').style.display = 'block';
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function empty1() {
    document.getElementById('kgtoleransi').value = 0;
}

function empty2() {
    document.getElementById('toleransi').value = 0;
}

function clearSearch() {
    setValue('txtsearchkontrak', '');
    setValue('txtsearch', '');
    setValue('tgl_cari', '');
    setValue('produksch', '');
    cariData(0);
}