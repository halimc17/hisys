function newdata() {
    cancelht();
    document.getElementById("header").style.display = "block";
    document.getElementById("listdata").style.display = "none";
    document.getElementById("detail").style.display = "none";
}

function getkgterima(no) {
    // kgselisih=document.getElementById('kgselisih'+no).innerHTML;

    kgkirim = document.getElementById("kgkirim" + no).innerHTML;
    kgterimaawal = document.getElementById("kgterimaawal" + no).innerHTML;
    kgtoleransi = document.getElementById("kgtoleransi" + no).innerHTML;
    kgtonbag = document.getElementById("kgtonbag" + no).value;
    kgterimaawal = remove_comma_var(kgterimaawal);
    kgtonbag = remove_comma_var(kgtonbag);
    kgkirim = remove_comma_var(kgkirim);
    kgtoleransi = remove_comma_var(kgtoleransi);

    kgterima = parseFloat(kgterimaawal) + parseFloat(kgtonbag);
    document.getElementById("kgterima" + no).innerHTML = numberFormat(kgterima);

    kgselisih = parseFloat(kgterima) - parseFloat(kgkirim);
    document.getElementById("kgselisih" + no).innerHTML = numberFormat(kgselisih);

    kgclaim = parseFloat(kgselisih) - parseFloat(kgtoleransi);
    document.getElementById("kgclaim" + no).innerHTML = numberFormat(kgclaim);
}

function cancelht() {
    setValue2("notransaksi", "");
    setValue2("unit", "");
    setValue2("komoditi", "");
    setValue2("nokontrak", "");
    setValue2("nodo", "");
    setValue2("jenisba", "0");
    setValue2("tipe", "");
    setValue2("noinvoice", "");
    setValue2("persentlrsusut", "");
    setValue2("nospk", "");
    setValue2("persenpph", "0.25");
    setValue2("persenppn", "11");
    setValue2("tanggal", "");
    setValue2("tanggalkirim1", "");
    setValue2("tanggalkirim2", "");
    setValue2("keterangan", "");

    document.getElementById("unit").disabled = false;
    document.getElementById("tipe").disabled = false;
    document.getElementById("nospk").disabled = false;
    document.getElementById("tanggal").disabled = false;
    document.getElementById("tanggalkirim1").disabled = false;
    document.getElementById("tanggalkirim2").disabled = false;
    document.getElementById("keterangan").disabled = false;

    document.getElementById("detail").style.display = "none";
    document.getElementById("detail").value = "";
    getnospk();
}

function form_ajukan(notransaksi) {
    let content =
        '<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
    let title = "Ajukan : " + notransaksi;

    alertify
        .popup(title, content)
        .set({ resizable: true, maximizable: true })
        .resizeTo("20%", "10%");

    let param = "method=form_ajukan";
    param += "&notransaksi=" + notransaksi;
    let tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, function () {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("containeraju").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    });
}

function saveajukan(notransaksi, tipe, maxaproval, karyawanid) {
    param = "";
    tanggalpengajuan = document.getElementById("tanggalpengajuan").value;
    // if(tanggalpengajuan=='') {
    // alert('Tanggal pengajuan tidak boleh kosong');
    // return;
    // }
    strper = "";
    for (i = 1; i <= maxaproval; i++) {
        strper +=
            "&persetujuan[" +
            i +
            "]=" +
            trim(document.getElementById("persetujuan" + i).value);
    }
    param +=
        "&notransaksi=" +
        notransaksi +
        "&tanggalpengajuan=" +
        tanggalpengajuan +
        "&tipe=" +
        tipe;
    param += "&maxaproval=" + maxaproval + "&karyawanid= " + karyawanid;
    param += "&method=saveajukan";
    param += strper;
    //console.log(param)
    tujuan = "pmn_batransport_slavev2.php";
    // if(confirm('Ajukan transaksi : '+notransaksi+' ?')) {
    // post_response_text(tujuan, param, respon);
    // }

    alertify.confirm(
        "Informasi",
        "Ajukan transaksi : " + notransaksi + " ???",
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
                    alertify.alert("Informasi", con.responseText);
                } else {
                    alertify.popup().destroy();
                    loaddata();
                    //getBastList(gudang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function form_ajukan(notransaksi){
//     let content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
//     let title   = "Ajukan : " + notransaksi;

//     alertify.popup(title, content).set({'resizable':true,'maximizable':true}).resizeTo('20%','10%');

//     let param = "method=form_ajukan";
//         param += "&notransaksi=" + notransaksi;
//     let tujuan = "pmn_batransport_slavev2.php";
//     post_response_text(tujuan, param, function(){
//         if (con.readyState == 4) {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alert(con.responseText);
//                 } else {
//                     document.getElementById('containeraju').innerHTML = con.responseText;
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     });
// }

// function ajukan(){
//     let notransaksi = document.getElementById("notransaksi_ajukan");
//     let jlh         = document.getElementById("jlh");

//     if(jlh.value == 0){
//         alertify.alert("Warning: Approval kosong");
//         return;
//     }

//     let param = "method=ajukan";
//         param += "&notransaksi=" + notransaksi.value;
//         param += "&jlh=" + jlh.value;

//         for (i = 1; i <= jlh.value; i++) {
//             param += "&" + "kepada"+ i + "=" + document.getElementById("kepada" + i).value;
//         }

//     let tujuan = "pmn_batransport_slavev2.php";
//     post_response_text(tujuan, param, () => {
//         if (con.readyState == 4) {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alert(con.responseText);
//                 } else {
// 					alertify.popup().destroy();
//                     alertify.alert('Info', 'Success');
//                     loaddata(0);
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     });
// }

function saveht() {
    param = "";
    tanggal = document.getElementById("tanggal").value;
    unit = document.getElementById("unit").value;
    tipe = document.getElementById("tipe").value;
    tanggalkirim1 = document.getElementById("tanggalkirim1").value;
    tanggalkirim2 = document.getElementById("tanggalkirim2").value;
    nospk = document.getElementById("nospk").value;
    komoditi = document.getElementById("komoditi").value;
    nokontrak = document.getElementById("nokontrak").value;
    persenppn = document.getElementById("persenppn").value;
    noinvoice = document.getElementById("noinvoice").value;
    jenisba = document.getElementById("jenisba").value;

    validate([
        // ["unit","Unit harus dipilih."],
        ["tipe", "Transportir harus dipilih."],
        ["noinvoice", "No. Invoice harus diisi."],
        ["tanggal", "Tanggal dokumen harus diisi."],
        ["tanggalkirim1", "Tanggal mulai harus diisi."],
        ["tanggalkirim2", "Tanggal sampai harus diisi."],
        ["keterangan", "Keterangan harus diisi."],
        ["komoditi", "Komoditi harus diisi."],
    ]);

    method = "saveht";
    param += "&unit=" + unit + "&tanggal=" + tanggal;
    param +=
        "&tanggalkirim1=" + tanggalkirim1 + "&tanggalkirim2=" + tanggalkirim2;
    param += "&tipe=" + tipe + "&nospk=" + nospk + "&komoditi=" + komoditi + "&nokontrak=" + nokontrak + "&persenppn=" + persenppn + "&noinvoice=" + noinvoice + "&jenisba=" + jenisba;
    param += "&method=" + method;

    tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("info", con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.set("notifier", "position", "top-right");
                    alertify.success("Berhasil");

                    const data = JSON.parse(con.responseText);

                    document.getElementById("notransaksi").value = data.notransaksi;
                    // document.getElementById("persenpph").value = data.persenpph;
                    // document.getElementById("persenppn").value = data.persenppn;
                    // console.log('persenpph: ' + data.persenpph);
                    // console.log('persenppn: ' + data.persenppn);
                    document.getElementById("detail").style.display = "block";
                    loaddatadt();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadt() {
    notransaksi = document.getElementById("notransaksi").value;
    tanggal = document.getElementById("tanggal").value;
    unit = document.getElementById("unit").value;
    tipe = document.getElementById("tipe").value;
    jenisba = document.getElementById("jenisba").value;
    tanggalkirim1 = document.getElementById("tanggalkirim1").value;
    tanggalkirim2 = document.getElementById("tanggalkirim2").value;
    nospk = document.getElementById("nospk").value;
    nokontrak = document.getElementById("nokontrak").value;
    nodo = document.getElementById("nodo").value;
    komoditi = document.getElementById("komoditi").value;
    persenpph = document.getElementById("persenpph").value;
    persenppn = document.getElementById("persenppn").value;
    noinvoice = document.getElementById("noinvoice").value;
    persentlrsusut = document.getElementById("persentlrsusut").value;

    method = "loaddatadt";
    param =
        "unit=" +
        unit +
        "&tanggal=" +
        tanggal +
        "&notransaksi=" +
        notransaksi +
        "&tanggalkirim1=" +
        tanggalkirim1 +
        "&tanggalkirim2=" +
        tanggalkirim2 +
        "&tipe=" +
        tipe +
        "&jenisba=" +
        jenisba +
        "&nospk=" +
        nospk +
        "&komoditi=" +
        komoditi +
        "&nokontrak=" +
        nokontrak +
        "&persenppn=" +
        persenppn +
        "&noinvoice=" +
        noinvoice +
        "&persenpph=" +
        persenpph +
        "&persentlrsusut=" +
        persentlrsusut +
        "&nodo=" +
        nodo;
    param += "&method=" + method;
    // alert(param);
    // return;
    tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("listdatadt").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnospk(nospk) {
    unit = document.getElementById("unit").value;
    tipe = document.getElementById("tipe").value;
    // console.log(tipe);
    if (nospk == undefined) {
        nospk = document.getElementById("nospk").value;
    }
    method = "getnospk";
    param = "";
    param += "&unit=" + unit + "&tipe=" + tipe + "&nospk=" + nospk;
    param += "&method=" + method;

    tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById("nospk").innerHTML = con.responseText;
                    if (nospk != "") {
                        loaddatadt();
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getpphpersen() {
    transportir = document.getElementById("tipe").value;

    method = "getpphpersen";
    param = "";
    param += "&transportir=" + transportir;
    param += "&method=" + method;

    tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();

                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById("persenpph").value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getTransportir() {
    komoditi = document.getElementById("komoditi").value;

    method = "getTransportir";
    param = "";
    param += "&komoditi=" + komoditi;
    param += "&method=" + method;

    tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById("tipe").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getrpclaim(no) {
    kgclaim = document.getElementById("kgclaim" + no).innerHTML;
    rpkgclaim = document.getElementById("rpkgclaim" + no).value;
    kgclaim = remove_comma_var(kgclaim);
    rpkgclaim = remove_comma_var(rpkgclaim);
    rpclaim = parseFloat(kgclaim) * parseFloat(rpkgclaim);
    document.getElementById("rpclaim" + no).innerHTML = numberFormat(rpclaim);
}

/********************************************** detail *********************************/
/********************************************** detail *********************************/

maxf = 0;
sekarang = 1;
function savedt(maxRow) {
    maxf = maxRow;
    loopsave(1, maxRow);
}

function loopsave(currRow, maxRow) {
    param = "";

    notransaksi = trim(document.getElementById("notransaksi").value);
    tanggal = trim(document.getElementById("tanggal").value);
    tanggalkirim1 = trim(document.getElementById("tanggalkirim1").value);
    tanggalkirim2 = trim(document.getElementById("tanggalkirim2").value);
    nospk = trim(document.getElementById("nospk").value);
    unit = trim(document.getElementById("unit").value);
    tipe = trim(document.getElementById("tipe").value);
    keterangan = trim(document.getElementById("keterangan").value);
    nokontrak = trim(document.getElementById("nokontrak").value);
    noinvoice = trim(document.getElementById("noinvoice").value);
    jenisba = trim(document.getElementById("jenisba").value);
    persentlrsusut = trim(document.getElementById("persentlrsusut").value);
    rpdenda = trim(document.getElementById("rpdenda").value);
    kgdenda = trim(document.getElementById("kgdenda").value);
    ttotalrp = trim(document.getElementById("ttotalrp").value);

    tanggalkirimpks = trim(
        document.getElementById("tanggalkirimpks" + currRow).innerHTML
    );
    nokendaraan = trim(
        document.getElementById("nokendaraan" + currRow).innerHTML
    );

    notiket = trim(document.getElementById("notiket" + currRow).innerHTML);
    kgmasuk = trim(document.getElementById("kgmasuk" + currRow).innerHTML);
    kgkeluar = trim(document.getElementById("kgkeluar" + currRow).innerHTML);
    kgkirim = trim(document.getElementById("kgkirim" + currRow).innerHTML);
    kgkiriminternal = trim(document.getElementById("kgkiriminternal" + currRow).innerHTML);
    kgtonbag = trim(document.getElementById("kgtonbag" + currRow).value);
    kgterimaawal = trim(
        document.getElementById("kgterimaawal" + currRow).innerHTML
    );
    kgterima = trim(document.getElementById("kgterima" + currRow).innerHTML);
    kgselisih = trim(document.getElementById("kgselisih" + currRow).innerHTML);
    rpkg = trim(document.getElementById("rpkg" + currRow).innerHTML);
    rpjumlah = trim(document.getElementById("rpjumlah" + currRow).innerHTML);

    persentoleransi = trim(
        document.getElementById("persentoleransi" + currRow).innerHTML
    );
    kgtoleransi = trim(
        document.getElementById("kgtoleransi" + currRow).innerHTML
    );
    kgclaim = trim(document.getElementById("kgclaim" + currRow).innerHTML);
    rpkgclaim = trim(document.getElementById("rpkgclaim" + currRow).value);
    rpclaim = trim(document.getElementById("rpclaim" + currRow).innerHTML);
    transportir = trim(
        document.getElementById("transportir" + currRow).innerHTML
    );
    noakundebet = trim(
        document.getElementById("noakundebet" + currRow).innerHTML
    );
    kodebarang = trim(document.getElementById("kodebarang" + currRow).innerHTML);
    kodecustomer = trim(
        document.getElementById("kodecustomer" + currRow).innerHTML
    );
    dttiketref = trim(
        document.getElementById("dttiketref" + currRow).innerHTML
    );
    kodesupplier = trim(
        document.getElementById("kodesupplier" + currRow).innerHTML
    );
    idharga = trim(document.getElementById("idharga" + currRow).innerHTML);

    if (jenisba == 1) {
        var e;
        e = document.getElementById("id_tiketawal" + currRow);
        id_tiketawal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dtnokendaraan_awal" + currRow);
        dtnokendaraan_awal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dtkgmasuk_awal" + currRow);
        dtkgmasuk_awal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dtkgkeluar_awal" + currRow);
        dtkgkeluar_awal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dtkgkiriminternal_awal" + currRow);
        dtkgkiriminternal_awal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dtselisih_awal" + currRow);
        dtselisih_awal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("ongkosrpreal" + currRow);
        ongkosrpreal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dttotalrp_awal" + currRow);
        dttotalrp_awal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dttiketref_awal" + currRow);
        dttiketref_awal = e ? trim(e.innerHTML) : "";
        e = document.getElementById("dttotalrpdibayar_awal" + currRow);
        dttotalrpdibayar_awal = e ? trim(e.innerHTML) : "";
    } else {
        id_tiketawal = "";
        dtnokendaraan_awal = "";
        dtkgmasuk_awal = "";
        dtkgkeluar_awal = "";
        dtkgkiriminternal_awal = "";
        dtselisih_awal = "";
        ongkosrpreal = "";
        dttotalrp_awal = "";
        dttiketref_awal = "";
        dttotalrpdibayar_awal = "";
    }

    // PPH
    persenpph = remove_comma_var(document.getElementById("persenpph").value);
    persenppn = document.getElementById("persenppn").value;

    nilaipph = remove_comma_var(document.getElementById("ttlpph").value);
    nilaippn = remove_comma_var(document.getElementById("ttlppn").value);

    if (unit == "" || tanggal == "") {
        alertify.alert("Validasi", "Data tidak lengkap");
        return;
    } else {
        param +=
            "&method=savedt" +
            "&unit=" +
            unit +
            "&tipe=" +
            tipe +
            "&tanggal=" +
            tanggal +
            "&notransaksi=" +
            notransaksi;
        param +=
            "&tanggalkirim1=" +
            tanggalkirim1 +
            "&tanggalkirim2=" +
            tanggalkirim2 +
            "&keterangan=" +
            keterangan +
            "&nospk=" +
            nospk;
        param += "&notiket=" + notiket + "&nokontrak=" + nokontrak + "&noinvoice=" + noinvoice + "&jenisba=" + jenisba;
        param +=
            "&nokendaraan=" + nokendaraan + "&tanggalkirimpks=" + tanggalkirimpks;
        param +=
            "&kgmasuk=" +
            kgmasuk +
            "&kgkeluar=" +
            kgkeluar +
            "&kgkirim=" +
            kgkirim +
            "&kgkiriminternal=" +
            kgkiriminternal +
            "&kgselisih=" +
            kgselisih +
            "&kgtonbag=" +
            kgtonbag +
            "&kgterima=" +
            kgterima +
            "&kgterimaawal=" +
            kgterimaawal;
        param += "&rpkg=" + rpkg + "&rpjumlah=" + rpjumlah;
        param +=
            "&persentoleransi=" +
            persentoleransi +
            "&kgtoleransi=" +
            kgtoleransi +
            "&kgclaim=" +
            kgclaim +
            "&rpkgclaim=" +
            rpkgclaim +
            "&rpclaim=" +
            rpclaim +
            "&kodesupplier=" +
            kodesupplier +
            "&kodecustomer=" +
            kodecustomer +
            "&dttiketref=" +
            dttiketref +
            "&currRow=" +
            currRow;
        param +=
            "&transportir=" +
            transportir +
            "&noakundebet=" +
            noakundebet +
            "&kodebarang=" +
            kodebarang +
            "&idharga=" +
            idharga;
        param += "&persenpph=" + persenpph + "&persenppn=" + persenppn + "&nilaipph=" + nilaipph + "&nilaippn=" + nilaippn + "&persentlrsusut=" + persentlrsusut;
        param += "&rpdenda=" + rpdenda + "&kgdenda=" + kgdenda + "&ttotalrp=" + ttotalrp;
        param +=
            "&id_tiketawal=" + id_tiketawal +
            "&dtnokendaraan_awal=" + dtnokendaraan_awal +
            "&dtkgmasuk_awal=" + dtkgmasuk_awal +
            "&dtkgkeluar_awal=" + dtkgkeluar_awal +
            "&dtkgkiriminternal_awal=" + dtkgkiriminternal_awal +
            "&dtselisih_awal=" + dtselisih_awal +
            "&ongkosrpreal=" + ongkosrpreal +
            "&dttotalrp_awal=" + dttotalrp_awal +
            "&dttiketref_awal=" + dttiketref_awal +
            "&dttotalrpdibayar_awal=" + dttotalrpdibayar_awal;
        tujuan = "pmn_batransport_slavev2.php";

        post_response_text(tujuan, param, respog);
        // document.getElementById('row'+currRow).style.backgroundColor='';
        // document.getElementById('row'+currRow).style.backgroundColor='';
        // document.getElementById('row'+currRow).style.display='none';
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById("row" + currRow).style.backgroundColor =
                        "red";
                    unlockScreen();
                } else {
                    // document.getElementById('row'+currRow).style.display='none';
                    document.getElementById("row" + currRow).style.backgroundColor =
                        "blue";
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alertify.popup().destroy();
                        alertify.set("notifier", "position", "top-right");
                        alertify.success("Berhasil");
                        loaddatadt();
                    } else {
                        loopsave(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function htggrandtotal() {
    const getNumeric = (id) => {
        const el = document.getElementById(id);
        if (!el) return 0;
        let v = (typeof el.value !== "undefined" && el.value !== null && el.value !== "") ? el.value : el.innerHTML;
        v = remove_comma_var(v);
        return parseFloat(v) || 0;
    };

    const ttotalrp = getNumeric("ttotalrp");
    const ttlppn = getNumeric("ttlppn");
    const ttlpph = getNumeric("ttlpph");
    // const grand = ttotalrp + ttlppn - ttlpph;
    const grand = ttotalrp + ttlppn - ttlpph;

    const out = document.getElementById("grdttl");
    if (!out) return;
    if (typeof out.value !== "undefined") {
        out.value = numberFormat(grand);
    } else {
        out.innerHTML = numberFormat(grand);
    }
}

function adjustdpp(hargasatuan) {


    const kgdenda = document.getElementById("kgdenda").value;
    // const ttlawal = document.getElementById("ttlawal").value;
    const ttlawal = remove_comma_var(document.getElementById("ttlawal").value);

    ttladjust = parseFloat(kgdenda) * parseFloat(hargasatuan);
    ttlnilai = parseFloat(ttlawal) - parseFloat(ttladjust);


    document.getElementById("ttotalrp").value = numberFormat(ttlnilai);
    document.getElementById("rpdenda").value = numberFormat(ttladjust);

    adjshtggrandtotal();

}

function adjshtggrandtotal() {

    const ttotalrp = Number(remove_comma_var(document.getElementById("ttotalrp").value));
    const persenpph = Number(remove_comma_var(document.getElementById("persenpph").value));
    const persenppn = Number(remove_comma_var(document.getElementById("persenppn").value));

    const ttlpph = Math.round((persenpph / 100) * ttotalrp);
    const ttlppn = Math.round((persenppn / 100) * ttotalrp);


    const grand = Math.round(ttotalrp + ttlppn - ttlpph);


    document.getElementById("ttlpph").value = numberFormat(ttlpph);
    document.getElementById("ttlppn").value = numberFormat(ttlppn);
    document.getElementById("grdttl").value = numberFormat(grand);

}

function getPage() {
    pg = document.getElementById("pages");
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loaddata(paged);
}

function displaylist() {
    cancelht();
    document.getElementById("listdata").style.display = "block";
    document.getElementById("header").style.display = "none";
    document.getElementById("notransaksisch").value = "";
    document.getElementById("tanggalmulaisch").value = "";
    document.getElementById("tanggalselesaisch").value = "";
    loaddata(0);
}

function loaddata(num) {
    document.getElementById("listdata").style.display = "block";
    header = document.getElementById("header");
    if (header) {
        document.getElementById("header").style.display = "none";
        document.getElementById("detail").style.display = "none";
    }
    notransaksi = document.getElementById("notransaksisch").value;
    tanggalmulai = document.getElementById("tanggalmulaisch").value;
    tanggalselesai = document.getElementById("tanggalselesaisch").value;
    transportir = document.getElementById("transportirsch").value;
    komoditi = document.getElementById("komoditisch").value;
    param = "method=loaddata&page=" + num;
    param += "&notransaksi=" + notransaksi;
    param +=
        "&tanggalmulai=" + tanggalmulai + "&tanggalselesai=" + tanggalselesai;
    param += "&transportirsch=" + transportir;
    param += "&komoditisch=" + komoditi;
    tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById("contain").innerHTML = isdt[0];
                    document.getElementById("footData").innerHTML = isdt[1];
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editht(notransaksi) {
    param = "method=geteditht" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_batransport_slavev2.php";
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    var arrlist = new Array();
                    arrlist = JSON.parse(con.responseText);

                    setValue2("notransaksi", arrlist["notransaksi"]);
                    setValue2("unit", arrlist["unit"]);
                    setValue2("komoditi", arrlist["komoditi"]);
                    setValue2("tipe", arrlist["transportir"]);
                    setValue2("tanggal", arrlist["tanggal"]);
                    setValue2("tanggalkirim1", arrlist["tanggalkirim1"]);
                    setValue2("tanggalkirim2", arrlist["tanggalkirim2"]);
                    setValue2("keterangan", arrlist["keterangan"]);
                    setValue2("persenpph", arrlist["persenpph"]);
                    setValue2("persenppn", arrlist["persenppn"]);
                    setValue2("nokontrak", arrlist["nokontrak"]);
                    setValue2("nodo", arrlist["nodo"]);
                    setValue2("persentlrsusut", arrlist["persentoleransi"]);
                    setValue2("noinvoice", arrlist["noinvoice"]);

                    document.getElementById("notransaksi").disabled = true;
                    document.getElementById("unit").disabled = true;
                    document.getElementById("tipe").disabled = true;
                    document.getElementById("nospk").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggalkirim1").disabled = true;
                    document.getElementById("tanggalkirim2").disabled = true;
                    document.getElementById("keterangan").disabled = true;
                    document.getElementById("saveht").disabled = true;
                    document.getElementById("listdata").style.display = "none";
                    document.getElementById("header").style.display = "block";
                    document.getElementById("detail").style.display = "block";
                    getnospk(arrlist["notransaksi"]);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function posting(notransaksi, unit, tanggalpost) {
    param = "method=posting";
    param += "&notransaksi=" + notransaksi;
    param += "&unit=" + unit;
    param += "&tanggalpost=" + tanggalpost;
    tujuan = "pmn_batransport_slavev2.php";
    alertify.confirm(
        "Informasi",
        "Anda yakin posting BA " + notransaksi + "???",
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
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.set("notifier", "position", "top-right");
                    alertify.success("Berhasil");
                    getPage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteht(notransaksi) {
    param = "method=deleteht";
    param += "&notransaksi=" + notransaksi;
    tujuan = "pmn_batransport_slavev2.php";
    alertify.confirm(
        "Informasi",
        "Anda yakin hapus no transaksi BA " + notransaksi + "???",
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
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.set("notifier", "position", "top-right");
                    alertify.success("Berhasil");
                    getPage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdfclaim(notransaksi) {
    param = "method=pdfclaim&notransaksi=" + notransaksi;
    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_batransport_slavev2.php?" +
            param +
            "'></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function pdfinvoice(notransaksi) {
    param = "method=pdfinvoice&notransaksi=" + notransaksi;
    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_batransport_slavev2.php?" +
            param +
            "'></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function pdfba(notransaksi) {
    param = "method=pdfba&notransaksi=" + notransaksi;
    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_batransport_slavev2.php?" +
            param +
            "'></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function pdf(notransaksi) {
    param = "method=geteditht" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_batransport_slavev2.php";
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    var arrlist = new Array();
                    arrlist = JSON.parse(con.responseText);

                    var notransaksi = arrlist["notransaksi"];
                    var unit = arrlist["unit"];
                    var tipe = arrlist["transportir"];
                    var nospk = arrlist["nospk"];
                    var tanggal = arrlist["tanggal"];
                    var tanggalkirim1 = arrlist["tanggalkirim1"];
                    var tanggalkirim2 = arrlist["tanggalkirim2"];

                    printpdfbatrans(
                        notransaksi,
                        unit,
                        tipe,
                        nospk,
                        tanggal,
                        tanggalkirim1,
                        tanggalkirim2
                    );
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function printpdfbatrans(
    notransaksi,
    unit,
    tipe,
    nospk,
    tanggal,
    tanggalkirim1,
    tanggalkirim2
) {
    ev = "event";

    method = "export";
    param =
        "unit=" +
        unit +
        "&tanggal=" +
        tanggal +
        "&notransaksi=" +
        notransaksi +
        "&tanggalkirim1=" +
        tanggalkirim1 +
        "&tanggalkirim2=" +
        tanggalkirim2 +
        "&tipe=" +
        tipe +
        "&nospk=" +
        nospk;
    param += "&method=" + method;
    param += "&print=pdf";
    // alert(param);
    // return;
    tujuan = "pmn_batransport_slavev2.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    title = "Report PDF";
                    tujuan = tujuan + "?" + param;
                    alertify
                        .popuppdf(
                            title,
                            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
                            tujuan +
                            "'></iframe>"
                        )
                        .set({ resizable: true, overflow: false })
                        .resizeTo("80%", "70%");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

const excel = (notransaksi) => {
    const param = "method=geteditht" + "&notransaksi=" + notransaksi;
    const tujuan = "pmn_batransport_slavev2.php";

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    let arrlist = new Array();
                    arrlist = JSON.parse(con.responseText);

                    const notransaksi = arrlist["notransaksi"];
                    const unit = arrlist["unit"];
                    const tipe = arrlist["transportir"];
                    const nospk = arrlist["nospk"];
                    const tanggal = arrlist["tanggal"];
                    const tanggalkirim1 = arrlist["tanggalkirim1"];
                    const tanggalkirim2 = arrlist["tanggalkirim2"];

                    exportExcel(
                        notransaksi,
                        unit,
                        tipe,
                        nospk,
                        tanggal,
                        tanggalkirim1,
                        tanggalkirim2
                    );
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
};

const exportExcel = (
    notransaksi,
    unit,
    tipe,
    nospk,
    tanggal,
    tanggalkirim1,
    tanggalkirim2
) => {
    const method = "export";
    const param =
        "unit=" +
        unit +
        "&tanggal=" +
        tanggal +
        "&notransaksi=" +
        notransaksi +
        "&tanggalkirim1=" +
        tanggalkirim1 +
        "&tanggalkirim2=" +
        tanggalkirim2 +
        "&tipe=" +
        tipe +
        "&nospk=" +
        nospk +
        "&method=" +
        method +
        "&print=excel";

    const tujuan = "pmn_batransport_slavev2.php";

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    printnopopup(tujuan + "?" + param);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
};