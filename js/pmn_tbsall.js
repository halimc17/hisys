function displaylist() {
    cancelht2();
    document.getElementById("listdata").style.display = "block";
    document.getElementById("header").style.display = "none";
    document.getElementById("notransaksisch").value = "";
    document.getElementById("tanggalmulaisch").value = "";
    document.getElementById("tanggalselesaisch").value = "";
    document.getElementById("suppliersch").value = "";
    document.getElementById("jenissch").value = "";
    loaddata(0);
}

function getpage() {
    pg = document.getElementById("pages");
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loaddata(paged);
}

function loaddata(num, tp = "html") {
    notransaksisch = document.getElementById("notransaksisch").value;
    tanggalmulaisch = document.getElementById("tanggalmulaisch").value;
    tanggalselesaisch = document.getElementById("tanggalselesaisch").value;
    unitsch = document.getElementById("unitsch").value;
    suppliersch = document.getElementById("suppliersch").value;
    jenissch = document.getElementById("jenissch").value;
    postingsch = document.getElementById("postingsch").value;
    param = "method=loaddata&page=" + num;
    param += "&notransaksisch=" + notransaksisch;
    param +=
        "&tanggalmulaisch=" +
        tanggalmulaisch +
        "&tanggalselesaisch=" +
        tanggalselesaisch;
    param +=
        "&unitsch=" +
        unitsch +
        "&suppliersch=" +
        suppliersch +
        "&jenissch=" +
        jenissch +
        "&postingsch=" +
        postingsch +
        "&tp=" +
        tp;
    tujuan = "pmn_tbsall_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (tp == "excel") {
                        tujuan = tujuan + "?" + param;
                        printnopopup(tujuan);
                    } else {
                        isdt = con.responseText.split("####");
                        document.getElementById("contain").innerHTML = isdt[0];
                        document.getElementById("footData").innerHTML = isdt[1];
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//Umar
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
    let tujuan = "pmn_tbsall_slave.php";
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
        param +=
            "&" + "kepada" + i + "=" + document.getElementById("kepada" + i).value;
    }

    let tujuan = "pmn_tbsall_slave.php";
    post_response_text(tujuan, param, () => {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.alert("Info", "Success");
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

function newdata() {
    cancelht();
    document.getElementById("header").style.display = "block";
    document.getElementById("listdata").style.display = "none";
    document.getElementById("detailexternal").style.display = "none";
    document.getElementById("detailafiliasi").style.display = "none";
    document.getElementById("detailkud").style.display = "none";
    // document.getElementById('detailhead').style.display='none';
}

function cancelht() {
    setValue2("notransaksi", "");
    setValue2("jenisx", "");
    setValue2("unit", "");
    setValue2("nokontrak", "");
    setValue2("divisi", "");
    setValue2("tanggal", "");
    setValue2("tanggaltbs1", "");
    setValue2("tanggaltbs2", "");
    setValue2("persenppn", "11");
    setValue2("persenpph", "");
    setValue2("noafiliasi", "");
    setValue2("dibuat", "");
    setValue2("disetujui", "");
    setValue2("diperiksa", "");
    setValue2("nokontrak", "");
    setValue2("keteranganht", "");

    document.getElementById("jenisx").disabled = false;
    document.getElementById("unit").disabled = false;
    document.getElementById("divisi").disabled = false;
    document.getElementById("tanggal").disabled = false;
    document.getElementById("tanggaltbs1").disabled = false;
    document.getElementById("tanggaltbs2").disabled = false;
    document.getElementById("persenppn").disabled = false;
    document.getElementById("persenpph").disabled = false;
    document.getElementById("dibuat").disabled = false;
    document.getElementById("disetujui").disabled = false;
    document.getElementById("diperiksa").disabled = false;
    document.getElementById("nokontrak").disabled = false;
    document.getElementById("nokontrak").disabled = false;
    document.getElementById("keteranganht").disabled = false;

    document.getElementById("saveht").disabled = false;
}

function cancelht2() {
    document.getElementById("unit").disabled = false;
    document.getElementById("divisi").disabled = false;
    document.getElementById("tanggal").disabled = false;
    document.getElementById("tanggaltbs1").disabled = false;
    document.getElementById("tanggaltbs2").disabled = false;
    document.getElementById("persenppn").disabled = false;
    document.getElementById("persenpph").disabled = false;
    document.getElementById("keteranganht").disabled = false;
    document.getElementById("saveht").disabled = false;
    document.getElementById("dibuat").disabled = false;

    document.getElementById("nokontrak").disabled = false;
    document.getElementById("disetujui").disabled = false;
    document.getElementById("diperiksa").disabled = false;

    document.getElementById("nokontrak").value = "";
    document.getElementById("dibuat").value = "";
    document.getElementById("disetujui").value = "";
    document.getElementById("diperiksa").value = "";

    // document.getElementById('notransaksi').value='';
    // document.getElementById('noafiliasi').value='';
    // document.getElementById('unit').value='';
    // document.getElementById('divisi').value='';
    // document.getElementById('tanggal').value='';
    // document.getElementById('tanggaltbs1').value='';
    // document.getElementById('tanggaltbs2').value='';
    // document.getElementById('persenppn').value='11';
    // document.getElementById('persenpph').value='';
    // document.getElementById('keteranganht').value='';
    document.getElementById("detailexternal").style.display = "none";
    document.getElementById("detailafiliasi").style.display = "none";
    document.getElementById("detailkud").style.display = "none";
    document.getElementById("detailinternal").style.display = "none";
}

function getVendor() {
    unit = getValue("unit");
    jenisx = getValue("jenisx");

    param = "method=getVendor&unit=" + unit + "&jenisx=" + jenisx;
    tujuan = "pmn_tbsall_slave.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    // document.getElementById("unithutang").disabled = true;
                    setValue2("unithutang", null);
                    if (jenisx == "KUD") {
                        document.getElementById("unithutang").disabled = false;
                    }
                    document.getElementById("divisi").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getNokontrak() {
    divisi = getValue("divisi");
    unit = getValue("unit");

    param = "method=getNokontrak&divisi=" + divisi + "&unit=" + unit;
    tujuan = "pmn_tbsall_slave.php";

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById("nokontrak").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function saveht() {
    let param = "";
    const tanggal = document.getElementById("tanggal").value;
    const jenisx = document.getElementById("jenisx").value;
    const unit = document.getElementById("unit").value;
    const divisi = document.getElementById("divisi").value;
    const tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    const tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    const persenppn = document.getElementById("persenppn").value;
    const persenpph = document.getElementById("persenpph").value;
    const unithutang = document.getElementById("unithutang").value;

    if (tanggal == "") {
        alertify.alert("validasi", "Tanggal tidak boleh kosong");
        return;
    }
    if (unit == "") {
        alertify.alert("validasi", "unit tidak boleh kosong");
        return;
    }
    if (divisi == "") {
        alertify.alert("validasi", "Assignment tidak boleh kosong");
        return;
    }

    param = "method=saveht";
    param += "&unit=" + unit + "&tanggal=" + tanggal;
    param += "&tanggaltbs1=" + tanggaltbs1 + "&tanggaltbs2=" + tanggaltbs2;
    param += "&divisi=" + divisi;
    param += "&persenppn=" + persenppn;
    param += "&persenpph=" + persenpph;
    param += "&jenisx=" + jenisx;
    param += "&unithutang=" + unithutang;
    // alert(param);
    tujuan = "pmn_tbsall_slave.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("noafiliasi").value = ar[1];

                    document.getElementById("saveht").disabled = true;
                    document.getElementById("detailexternal").style.display = "block";

                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("keteranganht").disabled = true;

                    document.getElementById("saveht").disabled = false;

                    if (jenisx != "KUD") {
                        loaddatadt();
                    } else {
                        loaddatadtkud();
                    }
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
    tujuan = "pmn_tbsall_slave.php";

    alertify.confirm(
        "Informasi",
        "Hapus transaksi : " + notransaksi + " ???",
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
                    alertify.alert("Informasi", con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.set("notifier", "position", "top-right");
                    alertify.success("Berhasil");
                    getpage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formdetail(notransaksi) {
    tipe = "html";
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    tujuan = "pmn_tbsall_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    alertify
                        .popup("Detail", con.responseText)
                        .set({ resizable: true, maximizable: true })
                        .resizeTo("90%", "80%");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdf(notransaksi, tipe) {
    tipe = "pdf";
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_tbsall_slave.php?" +
            param +
            "'></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function excel(notransaksi) {
    param = "method=excel" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsall_slave.php";
    tujuan = tujuan + "?" + param;
    printnopopup(tujuan);
}

function exceldt() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    param = "method=exceldt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak;
    tujuan = "pmn_tbsall_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    tujuan = tujuan + "?" + param;
                    printnopopup(tujuan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

maxf = 0;
sekarang = 1;
function savedt(maxRow) {
    maxf = maxRow;
    loopsave(1, maxRow);
}

function loopsave(currRow, maxRow) {
    param = "";
    notransaksi = trim(document.getElementById("notransaksi").value);
    unit = trim(document.getElementById("unit").value);
    unithutang = trim(document.getElementById("unithutang").value);
    divisi = trim(document.getElementById("divisi").value);
    tanggal = trim(document.getElementById("tanggal").value);
    tanggaltbs1 = trim(document.getElementById("tanggaltbs1").value);
    tanggaltbs2 = trim(document.getElementById("tanggaltbs2").value);
    persenppn = trim(document.getElementById("persenppn").value);
    persenpph = trim(document.getElementById("persenpph").value);
    keteranganht = trim(document.getElementById("keteranganht").value);
    noafiliasi = trim(document.getElementById("noafiliasi").value);
    jenisx = trim(document.getElementById("jenisx").value);

    kelasbuah = trim(document.getElementById("kelasbuah" + currRow).innerHTML);
    tahuntanam = trim(document.getElementById("tahuntanam" + currRow).innerHTML);
    tanggalspb = trim(document.getElementById("tanggalspb" + currRow).innerHTML);
    tanggalpks = trim(document.getElementById("tanggalpks" + currRow).innerHTML);

    kodevhc = trim(document.getElementById("kodevhc" + currRow).innerHTML); //baru
    driver = trim(document.getElementById("driver" + currRow).innerHTML); //baru
    jjg = trim(document.getElementById("jjg" + currRow).innerHTML); //baru
    jjgsortasi = trim(document.getElementById("jjgsortasi" + currRow).innerHTML); //baru
    bjr = trim(document.getElementById("bjr" + currRow).innerHTML); //baru
    kgmasuk = trim(document.getElementById("kgmasuk" + currRow).innerHTML); //baru
    kgkeluar = trim(document.getElementById("kgkeluar" + currRow).innerHTML); //baru

    nospb = trim(document.getElementById("nospb" + currRow).innerHTML);
    notiket = trim(document.getElementById("notiket" + currRow).innerHTML);
    kodeblok = trim(document.getElementById("kodeblok" + currRow).innerHTML);
    kgbruto = trim(document.getElementById("kgbruto" + currRow).innerHTML);
    kgpotongan = trim(document.getElementById("kgpotongan" + currRow).innerHTML);
    kgnetto = trim(document.getElementById("kgnetto" + currRow).innerHTML);
    rpkg = trim(document.getElementById("rpkg" + currRow).innerHTML);
    totalrp = trim(document.getElementById("totalrp" + currRow).innerHTML);

    potpersenaktual = trim(
        document.getElementById("potpersenaktual" + currRow).innerHTML
    ); //baru
    potpersensetup = trim(
        document.getElementById("potpersensetup" + currRow).innerHTML
    ); //baru
    potpersen = trim(document.getElementById("potpersen" + currRow).innerHTML); //baru
    kgadjust = trim(document.getElementById("kgadjust" + currRow).innerHTML); //baru
    kgnettoadjust = trim(
        document.getElementById("kgnettoadjust" + currRow).innerHTML
    ); //baru
    rpadjust = trim(document.getElementById("rpadjust" + currRow).innerHTML); //baru
    jumrpadjust = trim(
        document.getElementById("jumrpadjust" + currRow).innerHTML
    ); //baru

    nokontrak = document.getElementById("nokontrak").value;
    dibuat = document.getElementById("dibuat").value;
    disetujui = document.getElementById("disetujui").value;
    diperiksa = document.getElementById("diperiksa").value;

    potpersenaktual = remove_comma_var(potpersenaktual);
    potpersensetup = remove_comma_var(potpersensetup);
    potpersen = remove_comma_var(potpersen);
    kgadjust = remove_comma_var(kgadjust);
    kgnettoadjust = remove_comma_var(kgnettoadjust);
    rpadjust = remove_comma_var(rpadjust);
    jumrpadjust = remove_comma_var(jumrpadjust);

    kgmasuk = remove_comma_var(kgmasuk);
    kgkeluar = remove_comma_var(kgkeluar);

    kgbruto = remove_comma_var(kgbruto);
    kgpotongan = remove_comma_var(kgpotongan);
    kgnetto = remove_comma_var(kgnetto);
    rpkg = remove_comma_var(rpkg);
    totalrp = remove_comma_var(totalrp);

    if (unit == "" || tanggal == "") {
        alert("Data tidak lengkap");
        return;
    } else {
        param +=
            "&method=savedt" +
            "&unit=" +
            unit +
            "&unithutang=" +
            unithutang +
            "&divisi=" +
            divisi +
            "&tanggal=" +
            tanggal +
            "&notransaksi=" +
            notransaksi +
            "&jenisx=" +
            jenisx;
        param +=
            "&tanggaltbs1=" +
            tanggaltbs1 +
            "&tanggaltbs2=" +
            tanggaltbs2 +
            "&persenppn=" +
            persenppn +
            "&persenpph=" +
            persenpph +
            "&keteranganht=" +
            keteranganht;
        param += "&tanggalspb=" + tanggalspb + "&tanggalpks=" + tanggalpks;

        param += "&kodevhc=" + kodevhc + "&driver=" + driver;
        param += "&jjg=" + jjg + "&jjgsortasi=" + jjgsortasi + "&bjr=" + bjr;
        param += "&kgmasuk=" + kgmasuk + "&kgkeluar=" + kgkeluar;

        param +=
            "&kodeblok=" +
            kodeblok +
            "&notiket=" +
            notiket +
            "&nospb=" +
            nospb +
            "&tahuntanam=" +
            tahuntanam +
            "&kelasbuah=" +
            kelasbuah;
        param +=
            "&kgbruto=" +
            kgbruto +
            "&kgpotongan=" +
            kgpotongan +
            "&kgnetto=" +
            kgnetto;
        param +=
            "&rpkg=" + rpkg + "&totalrp=" + totalrp + "&noafiliasi=" + noafiliasi;

        param +=
            "&potpersenaktual=" +
            potpersenaktual +
            "&potpersensetup=" +
            potpersensetup +
            "&potpersen=" +
            potpersen;
        param +=
            "&kgadjust=" +
            kgadjust +
            "&kgnettoadjust=" +
            kgnettoadjust +
            "&rpadjust=" +
            rpadjust +
            "&jumrpadjust=" +
            jumrpadjust;
        param += "&currRow=" + currRow;

        param +=
            "&nokontrak=" +
            nokontrak +
            "&dibuat=" +
            dibuat +
            "&disetujui=" +
            disetujui +
            "&diperiksa=" +
            diperiksa;

        tujuan = "pmn_tbsall_slave.php";
        post_response_text(tujuan, param, respog);
        document.getElementById("row" + currRow).style.backgroundColor = "";
        document.getElementById("row" + currRow).style.backgroundColor = "cyan";
        // document.getElementById('row'+currRow).style.backgroundColor='';
        // document.getElementById('row'+currRow).style.display='none';
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById("row" + currRow).style.backgroundColor =
                        "red";
                    unlockScreen();
                } else {
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alertify.popup().destroy();
                        alertify.set("notifier", "position", "top-right");
                        alertify.success("Berhasil");
                        loaddatadt();
                        /*
                                        canceldt();
                            	
                                    */
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

function loaddatadt() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    jenisx = document.getElementById("jenisx").value;
    param = "method=loaddatadt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak +
        "&jenisx=" +
        jenisx;
    tujuan = "pmn_tbsall_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById("listdatadtexternal").innerHTML =
                        con.responseText;
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
    tujuan = "pmn_tbsall_slave.php";
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    var arrlist = new Array();
                    arrlist = JSON.parse(con.responseText);

                    setValue2("notransaksi", arrlist["notransaksi"]);
                    setValue2("jenisx", arrlist["tipetbs"]);
                    setTimeout(function () {
                        setValue2("unit", arrlist["unit"]);
                        setTimeout(function () {
                            setValue2("divisi", arrlist["supplier"]);
                            setTimeout(function () {
                                setValue2("nokontrak", arrlist["nokontrak"]);
                                loaddatadt();
                            }, 500);
                        }, 500);
                    }, 500);

                    setValue2("tanggal", arrlist["tanggal"]);
                    setValue2("tanggaltbs1", arrlist["tanggaltbs1"]);
                    setValue2("tanggaltbs2", arrlist["tanggaltbs2"]);
                    setValue2("persenppn", arrlist["persenppn"]);
                    setValue2("persenpph", arrlist["persenpph"]);
                    setValue2("dibuat", arrlist["dibuat"]);
                    setValue2("disetujui", arrlist["disetujui"]);
                    setValue2("diperiksa", arrlist["diperiksa"]);
                    setValue2("keteranganht", arrlist["keteranganht"]);

                    document.getElementById("notransaksi").disabled = true;
                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("nokontrak").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("saveht").disabled = true;
                    document.getElementById("jenisx").disabled = true;
                    document.getElementById("jenisx").value = "EXT";

                    document.getElementById("listdata").style.display = "none";
                    document.getElementById("header").style.display = "block";
                    document.getElementById("detailexternal").style.display = "block";
                    document.getElementById("detailafiliasi").style.display = "none";
                    document.getElementById("detailinternal").style.display = "none";
                    document.getElementById("detailkud").style.display = "none";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function savehtkud() {
    param = "";
    tanggal = document.getElementById("tanggal").value;
    unit = document.getElementById("unit").value;
    divisi = document.getElementById("divisi").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;

    nokontrak = document.getElementById("nokontrak").value;
    dibuat = document.getElementById("dibuat").value;
    disetujui = document.getElementById("disetujui").value;
    diperiksa = document.getElementById("diperiksa").value;

    if (tanggal == "") {
        alert("Tanggal tidak boleh kosong");
        return;
    }
    if (unit == "") {
        alert("unit tidak boleh kosong");
        return;
    }
    if (divisi == "") {
        alert("Assignment tidak boleh kosong");
        return;
    }

    method = "notransaksi";
    param += "&unit=" + unit + "&tanggal=" + tanggal;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&divisi=" +
        divisi;
    param += "&persenppn=" + persenppn + "&persenpph=" + persenpph;
    param +=
        "&nokontrak=" +
        nokontrak +
        "&dibuat=" +
        dibuat +
        "&disetujui=" +
        disetujui +
        "&diperiksa=" +
        diperiksa;
    param += "&method=" + method;
    // alert(param);
    tujuan = "pmn_tbskud_slave.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("noafiliasi").value = ar[1];
                    // document.getElementById('notransaksi').value=con.responseText;

                    document.getElementById("saveht").disabled = true;
                    document.getElementById("detailkud").style.display = "block";

                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("keteranganht").disabled = true;

                    document.getElementById("nokontrak").disabled = true;
                    // document.getElementById('dibuat').disabled=true;
                    document.getElementById("disetujui").disabled = true;
                    document.getElementById("diperiksa").disabled = true;

                    document.getElementById("saveht").disabled = false;

                    loaddatadtkud();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savehtafiliasi() {
    param = "";
    tanggal = document.getElementById("tanggal").value;
    unit = document.getElementById("unit").value;
    divisi = document.getElementById("divisi").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;

    nokontrak = document.getElementById("nokontrak").value;
    dibuat = document.getElementById("dibuat").value;
    disetujui = document.getElementById("disetujui").value;
    diperiksa = document.getElementById("diperiksa").value;

    if (tanggal == "") {
        alert("Tanggal tidak boleh kosong");
        return;
    }
    if (unit == "") {
        alert("unit tidak boleh kosong");
        return;
    }
    if (divisi == "") {
        alert("Assignment tidak boleh kosong");
        return;
    }

    method = "notransaksi";
    param += "&unit=" + unit + "&tanggal=" + tanggal;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&divisi=" +
        divisi;
    param += "&persenppn=" + persenppn + "&persenpph=" + persenpph;
    param +=
        "&nokontrak=" +
        nokontrak +
        "&dibuat=" +
        dibuat +
        "&disetujui=" +
        disetujui +
        "&diperiksa=" +
        diperiksa;
    param += "&method=" + method;
    // alert(param);
    tujuan = "pmn_tbsafiliasi_slave.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("noafiliasi").value = ar[1];
                    // document.getElementById('notransaksi').value=con.responseText;

                    document.getElementById("saveht").disabled = true;
                    document.getElementById("detailafiliasi").style.display = "block";

                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("keteranganht").disabled = true;

                    document.getElementById("nokontrak").disabled = true;
                    document.getElementById("dibuat").disabled = true;
                    document.getElementById("disetujui").disabled = true;
                    document.getElementById("diperiksa").disabled = true;

                    document.getElementById("saveht").disabled = false;

                    loaddatadtafiliasi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savehtexternal() {
    param = "";
    tanggal = document.getElementById("tanggal").value;
    unit = document.getElementById("unit").value;
    divisi = document.getElementById("divisi").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;

    if (tanggal == "") {
        alert("Tanggal tidak boleh kosong");
        return;
    }
    if (unit == "") {
        alert("unit tidak boleh kosong");
        return;
    }
    if (divisi == "") {
        alert("Assignment tidak boleh kosong");
        return;
    }

    method = "notransaksi";
    param += "&unit=" + unit + "&tanggal=" + tanggal;
    param += "&tanggaltbs1=" + tanggaltbs1 + "&tanggaltbs2=" + tanggaltbs2;
    param += "&divisi=" + divisi;
    param += "&persenppn=" + persenppn;
    param += "&persenpph=" + persenpph;
    param += "&method=" + method;
    tujuan = "pmn_tbsexternal_slave.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("noafiliasi").value = ar[1];
                    // document.getElementById('notransaksi').value=con.responseText;

                    document.getElementById("saveht").disabled = true;
                    document.getElementById("detailexternal").style.display = "block";

                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("keteranganht").disabled = true;

                    document.getElementById("saveht").disabled = false;
                    loaddatadtexternal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savehtinternal() {
    param = "";
    tanggal = document.getElementById("tanggal").value;
    unit = document.getElementById("unit").value;
    divisi = document.getElementById("divisi").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;

    if (tanggal == "") {
        alert("Tanggal tidak boleh kosong");
        return;
    }
    if (unit == "") {
        alert("unit tidak boleh kosong");
        return;
    }
    if (divisi == "") {
        alert("Assignment tidak boleh kosong");
        return;
    }

    method = "notransaksi";
    param += "&unit=" + unit + "&tanggal=" + tanggal;
    param += "&tanggaltbs1=" + tanggaltbs1 + "&tanggaltbs2=" + tanggaltbs2;
    param += "&divisi=" + divisi;
    param += "&persenppn=" + persenppn;
    param += "&persenpph=" + persenpph;
    param += "&method=" + method;
    tujuan = "pmn_tbsinternal_slave.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("noafiliasi").value = ar[1];
                    // document.getElementById('notransaksi').value=con.responseText;

                    document.getElementById("saveht").disabled = true;
                    document.getElementById("detailinternal").style.display = "block";

                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("keteranganht").disabled = true;

                    document.getElementById("saveht").disabled = false;
                    loaddatadtinternal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadtkud() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    jenisx = document.getElementById("jenisx").value;
    param = "method=loaddatadt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak +
        "&jenisx=" +
        jenisx;
    tujuan = "pmn_tbskud_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("saveht").disabled = true;
                    document.getElementById("detailexternal").style.display = "none";
                    document.getElementById("detailkud").style.display = "block";

                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("keteranganht").disabled = true;

                    document.getElementById("saveht").disabled = false;

                    document.getElementById("listdatadtkud").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function exceldtkud() {
    notransaksi = document.getElementById("notransaksi").value;
    nokontrak = document.getElementById("nokontrak").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    param = "method=exceldt";
    param +=
        "&notransaksi=" + notransaksi + "&unit=" + unit + "&nokontrak=" + nokontrak;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi;
    // alert(param);
    tujuan = "pmn_tbskud_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    tujuan = tujuan + "?" + param;
                    printnopopup(tujuan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadtexternal() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    param = "method=loaddatadt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak;
    tujuan = "pmn_tbsexternal_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("listdatadtexternal").innerHTML =
                        con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function exceldtext() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    param = "method=exceldt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak;
    tujuan = "pmn_tbsexternal_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    tujuan = tujuan + "?" + param;
                    printnopopup(tujuan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadtafiliasi() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    param = "method=loaddatadt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak;
    tujuan = "pmn_tbsafiliasi_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("listdatadtafiliasi").innerHTML =
                        con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function exceldtafi() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    param = "method=exceldt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak;
    tujuan = "pmn_tbsafiliasi_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    tujuan = tujuan + "?" + param;
                    printnopopup(tujuan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadtinternal() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    param = "method=loaddatadt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak;
    tujuan = "pmn_tbsinternal_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("listdatadtinternal").innerHTML =
                        con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function exceldtint() {
    notransaksi = document.getElementById("notransaksi").value;
    unit = document.getElementById("unit").value;
    tanggaltbs1 = document.getElementById("tanggaltbs1").value;
    tanggaltbs2 = document.getElementById("tanggaltbs2").value;
    persenppn = document.getElementById("persenppn").value;
    persenpph = document.getElementById("persenpph").value;
    divisi = document.getElementById("divisi").value;
    nokontrak = document.getElementById("nokontrak").value;
    param = "method=exceldt";
    param += "&notransaksi=" + notransaksi + "&unit=" + unit;
    param +=
        "&tanggaltbs1=" +
        tanggaltbs1 +
        "&tanggaltbs2=" +
        tanggaltbs2 +
        "&persenppn=" +
        persenppn +
        "&persenpph=" +
        persenpph +
        "&divisi=" +
        divisi +
        "&nokontrak=" +
        nokontrak;
    tujuan = "pmn_tbsinternal_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    tujuan = tujuan + "?" + param;
                    printnopopup(tujuan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savedtx(maxRow) {
    if (document.getElementById("jenisx").value == "kud") {
        savedtkud(maxRow);
    } else if (document.getElementById("jenisx").value == "external") {
        savedtexternal(maxRow);
    } else if (document.getElementById("jenisx").value == "afiliasi") {
        savedtafiliasi(maxRow);
    } else if (document.getElementById("jenisx").value == "internal") {
        savedtinternal(maxRow);
    }
}

maxf = 0;
sekarang = 1;
function savedtkud(maxRow) {
    maxf = maxRow;
    loopsavekud(1, maxRow);
}

function loopsavekud(currRow, maxRow) {
    param = "";
    notransaksi = trim(document.getElementById("notransaksi").value);
    unit = trim(document.getElementById("unit").value);
    divisi = trim(document.getElementById("divisi").value);
    tanggal = trim(document.getElementById("tanggal").value);
    tanggaltbs1 = trim(document.getElementById("tanggaltbs1").value);
    tanggaltbs2 = trim(document.getElementById("tanggaltbs2").value);
    persenppn = trim(document.getElementById("persenppn").value);
    persenpph = trim(document.getElementById("persenpph").value);
    keteranganht = trim(document.getElementById("keteranganht").value);
    noafiliasi = trim(document.getElementById("noafiliasi").value);
    jenisx = trim(document.getElementById("jenisx").value);

    nokontrak = document.getElementById("nokontrak").value;
    dibuat = document.getElementById("dibuat").value;
    disetujui = document.getElementById("disetujui").value;
    diperiksa = document.getElementById("diperiksa").value;

    tahuntanam = trim(document.getElementById("tahuntanam" + currRow).innerHTML);
    tanggalspb = trim(document.getElementById("tanggalspb" + currRow).innerHTML);
    tanggalpks = trim(document.getElementById("tanggalpks" + currRow).innerHTML);

    kodevhc = trim(document.getElementById("kodevhc" + currRow).innerHTML); //baru
    driver = trim(document.getElementById("driver" + currRow).innerHTML); //baru
    jjg = trim(document.getElementById("jjg" + currRow).innerHTML); //baru
    brondolan = trim(document.getElementById("brondolan" + currRow).innerHTML); //baru
    jjgsortasi = trim(document.getElementById("jjgsortasi" + currRow).innerHTML); //baru
    kgmasuk = trim(document.getElementById("kgmasuk" + currRow).innerHTML); //baru
    kgkeluar = trim(document.getElementById("kgkeluar" + currRow).innerHTML); //baru
    bjr = trim(document.getElementById("bjr" + currRow).innerHTML); //baru

    nospb = trim(document.getElementById("nospb" + currRow).innerHTML);
    notiket = trim(document.getElementById("notiket" + currRow).innerHTML);
    kodeblok = trim(document.getElementById("kodeblok" + currRow).innerHTML);
    kgbruto = trim(document.getElementById("kgbruto" + currRow).innerHTML);
    kgpotongan = trim(document.getElementById("kgpotongan" + currRow).innerHTML);
    kgnetto = trim(document.getElementById("kgnetto" + currRow).innerHTML);
    rpkg = trim(document.getElementById("rpkg" + currRow).innerHTML);
    totalrp = trim(document.getElementById("totalrp" + currRow).innerHTML);

    potpersenaktual = trim(
        document.getElementById("potpersenaktual" + currRow).innerHTML
    ); //baru
    potpersensetup = trim(
        document.getElementById("potpersensetup" + currRow).innerHTML
    ); //baru
    potpersen = trim(document.getElementById("potpersen" + currRow).innerHTML); //baru
    kgadjust = trim(document.getElementById("kgadjust" + currRow).innerHTML); //baru
    kgnettoadjust = trim(
        document.getElementById("kgnettoadjust" + currRow).innerHTML
    ); //baru
    rpadjust = trim(document.getElementById("rpadjust" + currRow).innerHTML); //baru
    jumrpadjust = trim(
        document.getElementById("jumrpadjust" + currRow).innerHTML
    ); //baru

    potpersenaktual = remove_comma_var(potpersenaktual);
    potpersensetup = remove_comma_var(potpersensetup);
    potpersen = remove_comma_var(potpersen);
    kgadjust = remove_comma_var(kgadjust);
    kgnettoadjust = remove_comma_var(kgnettoadjust);
    rpadjust = remove_comma_var(rpadjust);
    jumrpadjust = remove_comma_var(jumrpadjust);

    kgmasuk = remove_comma_var(kgmasuk);
    kgkeluar = remove_comma_var(kgkeluar);
    bjr = remove_comma_var(bjr);

    kgbruto = remove_comma_var(kgbruto);
    kgpotongan = remove_comma_var(kgpotongan);
    kgnetto = remove_comma_var(kgnetto);
    rpkg = remove_comma_var(rpkg);
    totalrp = remove_comma_var(totalrp);

    if (unit == "" || tanggal == "") {
        alert("Data tidak lengkap");
        return;
    } else {
        param +=
            "&method=savedt" +
            "&unit=" +
            unit +
            "&divisi=" +
            divisi +
            "&tanggal=" +
            tanggal +
            "&notransaksi=" +
            notransaksi;
        param +=
            "&tanggaltbs1=" +
            tanggaltbs1 +
            "&tanggaltbs2=" +
            tanggaltbs2 +
            "&persenppn=" +
            persenppn +
            "&persenpph=" +
            persenpph +
            "&keteranganht=" +
            keteranganht;
        param += "&tanggalspb=" + tanggalspb + "&tanggalpks=" + tanggalpks;

        param += "&kodevhc=" + kodevhc + "&driver=" + driver;
        param += "&jjg=" + jjg + "&jjgsortasi=" + jjgsortasi;
        param += "&kgmasuk=" + kgmasuk + "&kgkeluar=" + kgkeluar;
        param += "&bjr=" + bjr;
        param += "&jenisx=" + jenisx;
        param += "&brondolan=" + brondolan;

        param +=
            "&notiket=" +
            notiket +
            "&nospb=" +
            nospb +
            "&kodeblok=" +
            kodeblok +
            "&tahuntanam=" +
            tahuntanam;
        param +=
            "&kgbruto=" +
            kgbruto +
            "&kgpotongan=" +
            kgpotongan +
            "&kgnetto=" +
            kgnetto;
        param +=
            "&rpkg=" + rpkg + "&totalrp=" + totalrp + "&noafiliasi=" + noafiliasi;

        param +=
            "&potpersenaktual=" +
            potpersenaktual +
            "&potpersensetup=" +
            potpersensetup +
            "&potpersen=" +
            potpersen;
        param +=
            "&kgadjust=" +
            kgadjust +
            "&kgnettoadjust=" +
            kgnettoadjust +
            "&rpadjust=" +
            rpadjust +
            "&jumrpadjust=" +
            jumrpadjust;

        param +=
            "&nokontrak=" +
            nokontrak +
            "&dibuat=" +
            dibuat +
            "&disetujui=" +
            disetujui +
            "&diperiksa=" +
            diperiksa;

        param += "&currRow=" + currRow;

        // alert(param);return;
        // tujuan = 'pmn_tbskud_slave.php';
        tujuan = "pmn_tbsall_slave.php";
        post_response_text(tujuan, param, respog);
        document.getElementById("row" + currRow).style.backgroundColor = "";
        document.getElementById("row" + currRow).style.backgroundColor = "cyan";
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
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alert("Done");
                        loaddatadtkud();
                        /*
                                        canceldt();
                            	
                                    */
                    } else {
                        loopsavekud(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

maxf = 0;
sekarang = 1;
function savedtafiliasi(maxRow) {
    maxf = maxRow;
    loopsaveafiliasi(1, maxRow);
}

function loopsaveafiliasi(currRow, maxRow) {
    param = "";
    notransaksi = trim(document.getElementById("notransaksi").value);
    unit = trim(document.getElementById("unit").value);
    divisi = trim(document.getElementById("divisi").value);
    tanggal = trim(document.getElementById("tanggal").value);
    tanggaltbs1 = trim(document.getElementById("tanggaltbs1").value);
    tanggaltbs2 = trim(document.getElementById("tanggaltbs2").value);
    persenppn = trim(document.getElementById("persenppn").value);
    persenpph = trim(document.getElementById("persenpph").value);
    keteranganht = trim(document.getElementById("keteranganht").value);
    noafiliasi = trim(document.getElementById("noafiliasi").value);

    tahuntanam = "";
    if (document.getElementById("tahuntanam" + currRow)) {
        tahuntanam = trim(
            document.getElementById("tahuntanam" + currRow).innerHTML
        );
    }
    tanggalspb = "";
    if (document.getElementById("tanggalspb" + currRow)) {
        tanggalspb = trim(
            document.getElementById("tanggalspb" + currRow).innerHTML
        );
    }
    tanggalpks = "";
    if (document.getElementById("tanggalpks" + currRow)) {
        tanggalpks = trim(
            document.getElementById("tanggalpks" + currRow).innerHTML
        );
    }

    nokontrak = "";
    if (document.getElementById("nokontrak")) {
        nokontrak = document.getElementById("nokontrak").value;
    }
    dibuat = document.getElementById("dibuat").value;
    disetujui = document.getElementById("disetujui").value;
    diperiksa = document.getElementById("diperiksa").value;

    kodevhc = "";
    if (document.getElementById("kodevhc" + currRow)) {
        kodevhc = trim(document.getElementById("kodevhc" + currRow).innerHTML);
    } //baru
    driver = "";
    if (document.getElementById("driver" + currRow)) {
        driver = trim(document.getElementById("driver" + currRow).innerHTML);
    } //baru
    jjg = "";
    if (document.getElementById("jjg" + currRow)) {
        jjg = trim(document.getElementById("jjg" + currRow).innerHTML);
    } //baru
    jjgsortasi = "";
    if (document.getElementById("jjgsortasi" + currRow)) {
        jjgsortasi = trim(
            document.getElementById("jjgsortasi" + currRow).innerHTML
        );
    } //baru
    kgmasuk = "";
    if (document.getElementById("kgmasuk" + currRow)) {
        kgmasuk = trim(document.getElementById("kgmasuk" + currRow).innerHTML);
    } //baru
    kgkeluar = "";
    if (document.getElementById("kgkeluar" + currRow)) {
        kgkeluar = trim(document.getElementById("kgkeluar" + currRow).innerHTML);
    } //baru
    bjr = "";
    if (document.getElementById("bjr" + currRow)) {
        bjr = trim(document.getElementById("bjr" + currRow).innerHTML);
    } //baru

    nospb = "";
    if (document.getElementById("nospb" + currRow)) {
        nospb = trim(document.getElementById("nospb" + currRow).innerHTML);
    }
    notiket = "";
    if (document.getElementById("notiket" + currRow)) {
        notiket = trim(document.getElementById("notiket" + currRow).innerHTML);
    }
    kodeblok = "";
    if (document.getElementById("kodeblok" + currRow)) {
        kodeblok = trim(document.getElementById("kodeblok" + currRow).innerHTML);
    }
    kgbruto = "";
    if (document.getElementById("kgbruto" + currRow)) {
        kgbruto = trim(document.getElementById("kgbruto" + currRow).innerHTML);
    }
    kgpotongan = "";
    if (document.getElementById("kgpotongan" + currRow)) {
        kgpotongan = trim(
            document.getElementById("kgpotongan" + currRow).innerHTML
        );
    }
    kgnetto = "";
    if (document.getElementById("kgnetto" + currRow)) {
        kgnetto = trim(document.getElementById("kgnetto" + currRow).innerHTML);
    }
    rpkg = "";
    if (document.getElementById("rpkg" + currRow)) {
        rpkg = trim(document.getElementById("rpkg" + currRow).innerHTML);
    }
    totalrp = "";
    if (document.getElementById("totalrp" + currRow)) {
        totalrp = trim(document.getElementById("totalrp" + currRow).innerHTML);
    }

    potpersenaktual = "";
    if (document.getElementById("potpersenaktual" + currRow)) {
        potpersenaktual = trim(
            document.getElementById("potpersenaktual" + currRow).innerHTML
        );
    } //baru
    potpersensetup = "";
    if (document.getElementById("potpersensetup" + currRow)) {
        potpersensetup = trim(
            document.getElementById("potpersensetup" + currRow).innerHTML
        );
    } //baru
    potpersen = "";
    if (document.getElementById("potpersen" + currRow)) {
        potpersen = trim(document.getElementById("potpersen" + currRow).innerHTML);
    } //baru
    kgadjust = "";
    if (document.getElementById("kgadjust" + currRow)) {
        kgadjust = trim(document.getElementById("kgadjust" + currRow).innerHTML);
    } //baru
    kgnettoadjust = "";
    if (document.getElementById("kgnettoadjust" + currRow)) {
        kgnettoadjust = trim(
            document.getElementById("kgnettoadjust" + currRow).innerHTML
        );
    } //baru
    rpadjust = "";
    if (document.getElementById("rpadjust" + currRow)) {
        rpadjust = trim(document.getElementById("rpadjust" + currRow).innerHTML);
    } //baru
    jumrpadjust = "";
    if (document.getElementById("jumrpadjust" + currRow)) {
        jumrpadjust = trim(
            document.getElementById("jumrpadjust" + currRow).innerHTML
        );
    } //baru

    potpersenaktual = remove_comma_var(potpersenaktual);
    potpersensetup = remove_comma_var(potpersensetup);
    potpersen = remove_comma_var(potpersen);
    kgadjust = remove_comma_var(kgadjust);
    kgnettoadjust = remove_comma_var(kgnettoadjust);
    rpadjust = remove_comma_var(rpadjust);
    jumrpadjust = remove_comma_var(jumrpadjust);

    kgmasuk = remove_comma_var(kgmasuk);
    kgkeluar = remove_comma_var(kgkeluar);
    bjr = remove_comma_var(bjr);

    kgbruto = remove_comma_var(kgbruto);
    kgpotongan = remove_comma_var(kgpotongan);
    kgnetto = remove_comma_var(kgnetto);
    rpkg = remove_comma_var(rpkg);
    totalrp = remove_comma_var(totalrp);

    if (unit == "" || tanggal == "") {
        alert("Data tidak lengkap");
        return;
    } else {
        param +=
            "&method=savedt" +
            "&unit=" +
            unit +
            "&divisi=" +
            divisi +
            "&tanggal=" +
            tanggal +
            "&notransaksi=" +
            notransaksi;
        param +=
            "&tanggaltbs1=" +
            tanggaltbs1 +
            "&tanggaltbs2=" +
            tanggaltbs2 +
            "&persenppn=" +
            persenppn +
            "&persenpph=" +
            persenpph +
            "&keteranganht=" +
            keteranganht;
        param += "&tanggalspb=" + tanggalspb + "&tanggalpks=" + tanggalpks;

        param += "&kodevhc=" + kodevhc + "&driver=" + driver;
        param += "&jjg=" + jjg + "&jjgsortasi=" + jjgsortasi;
        param += "&kgmasuk=" + kgmasuk + "&kgkeluar=" + kgkeluar;
        param += "&bjr=" + bjr;

        param +=
            "&notiket=" +
            notiket +
            "&nospb=" +
            nospb +
            "&kodeblok=" +
            kodeblok +
            "&tahuntanam=" +
            tahuntanam;
        param +=
            "&kgbruto=" +
            kgbruto +
            "&kgpotongan=" +
            kgpotongan +
            "&kgnetto=" +
            kgnetto;
        param +=
            "&rpkg=" + rpkg + "&totalrp=" + totalrp + "&noafiliasi=" + noafiliasi;

        param +=
            "&potpersenaktual=" +
            potpersenaktual +
            "&potpersensetup=" +
            potpersensetup +
            "&potpersen=" +
            potpersen;
        param +=
            "&kgadjust=" +
            kgadjust +
            "&kgnettoadjust=" +
            kgnettoadjust +
            "&rpadjust=" +
            rpadjust +
            "&jumrpadjust=" +
            jumrpadjust;
        param += "&currRow=" + currRow;

        param +=
            "&nokontrak=" +
            nokontrak +
            "&dibuat=" +
            dibuat +
            "&disetujui=" +
            disetujui +
            "&diperiksa=" +
            diperiksa;

        // alert(param);return;
        tujuan = "pmn_tbsafiliasi_slave.php";
        post_response_text(tujuan, param, respog);
        document.getElementById("row" + currRow).style.backgroundColor = "";
        document.getElementById("row" + currRow).style.backgroundColor = "cyan";
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
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alert("Done");
                        loaddatadtafiliasi();
                        /*
                                        canceldt();
                            	
                                    */
                    } else {
                        loopsaveafiliasi(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

maxf = 0;
sekarang = 1;
function savedtexternal(maxRow) {
    maxf = maxRow;
    loopsaveexternal(1, maxRow);
}

function loopsaveexternal(currRow, maxRow) {
    param = "";
    notransaksi = trim(document.getElementById("notransaksi").value);
    unit = trim(document.getElementById("unit").value);
    divisi = trim(document.getElementById("divisi").value);
    tanggal = trim(document.getElementById("tanggal").value);
    tanggaltbs1 = trim(document.getElementById("tanggaltbs1").value);
    tanggaltbs2 = trim(document.getElementById("tanggaltbs2").value);
    persenppn = trim(document.getElementById("persenppn").value);
    persenpph = trim(document.getElementById("persenpph").value);
    keteranganht = trim(document.getElementById("keteranganht").value);
    noafiliasi = trim(document.getElementById("noafiliasi").value);

    kelasbuah = trim(document.getElementById("kelasbuah" + currRow).innerHTML);
    tahuntanam = trim(document.getElementById("tahuntanam" + currRow).innerHTML);
    tanggalspb = trim(document.getElementById("tanggalspb" + currRow).innerHTML);
    tanggalpks = trim(document.getElementById("tanggalpks" + currRow).innerHTML);

    kodevhc = trim(document.getElementById("kodevhc" + currRow).innerHTML); //baru
    driver = trim(document.getElementById("driver" + currRow).innerHTML); //baru
    jjg = trim(document.getElementById("jjg" + currRow).innerHTML); //baru
    jjgsortasi = trim(document.getElementById("jjgsortasi" + currRow).innerHTML); //baru
    bjr = trim(document.getElementById("bjr" + currRow).innerHTML); //baru
    kgmasuk = trim(document.getElementById("kgmasuk" + currRow).innerHTML); //baru
    kgkeluar = trim(document.getElementById("kgkeluar" + currRow).innerHTML); //baru

    nospb = trim(document.getElementById("nospb" + currRow).innerHTML);
    notiket = trim(document.getElementById("notiket" + currRow).innerHTML);
    kodeblok = trim(document.getElementById("kodeblok" + currRow).innerHTML);
    kgbruto = trim(document.getElementById("kgbruto" + currRow).innerHTML);
    kgpotongan = trim(document.getElementById("kgpotongan" + currRow).innerHTML);
    kgnetto = trim(document.getElementById("kgnetto" + currRow).innerHTML);
    rpkg = trim(document.getElementById("rpkg" + currRow).innerHTML);
    totalrp = trim(document.getElementById("totalrp" + currRow).innerHTML);

    potpersenaktual = trim(
        document.getElementById("potpersenaktual" + currRow).innerHTML
    ); //baru
    potpersensetup = trim(
        document.getElementById("potpersensetup" + currRow).innerHTML
    ); //baru
    potpersen = trim(document.getElementById("potpersen" + currRow).innerHTML); //baru
    kgadjust = trim(document.getElementById("kgadjust" + currRow).innerHTML); //baru
    kgnettoadjust = trim(
        document.getElementById("kgnettoadjust" + currRow).innerHTML
    ); //baru
    rpadjust = trim(document.getElementById("rpadjust" + currRow).innerHTML); //baru
    jumrpadjust = trim(
        document.getElementById("jumrpadjust" + currRow).innerHTML
    ); //baru

    nokontrak = document.getElementById("nokontrak").value;
    dibuat = document.getElementById("dibuat").value;
    disetujui = document.getElementById("disetujui").value;
    diperiksa = document.getElementById("diperiksa").value;

    potpersenaktual = remove_comma_var(potpersenaktual);
    potpersensetup = remove_comma_var(potpersensetup);
    potpersen = remove_comma_var(potpersen);
    kgadjust = remove_comma_var(kgadjust);
    kgnettoadjust = remove_comma_var(kgnettoadjust);
    rpadjust = remove_comma_var(rpadjust);
    jumrpadjust = remove_comma_var(jumrpadjust);

    kgmasuk = remove_comma_var(kgmasuk);
    kgkeluar = remove_comma_var(kgkeluar);

    kgbruto = remove_comma_var(kgbruto);
    kgpotongan = remove_comma_var(kgpotongan);
    kgnetto = remove_comma_var(kgnetto);
    rpkg = remove_comma_var(rpkg);
    totalrp = remove_comma_var(totalrp);

    if (unit == "" || tanggal == "") {
        alert("Data tidak lengkap");
        return;
    } else {
        param +=
            "&method=savedt" +
            "&unit=" +
            unit +
            "&divisi=" +
            divisi +
            "&tanggal=" +
            tanggal +
            "&notransaksi=" +
            notransaksi;
        param +=
            "&tanggaltbs1=" +
            tanggaltbs1 +
            "&tanggaltbs2=" +
            tanggaltbs2 +
            "&persenppn=" +
            persenppn +
            "&persenpph=" +
            persenpph +
            "&keteranganht=" +
            keteranganht;
        param += "&tanggalspb=" + tanggalspb + "&tanggalpks=" + tanggalpks;

        param += "&kodevhc=" + kodevhc + "&driver=" + driver;
        param += "&jjg=" + jjg + "&jjgsortasi=" + jjgsortasi + "&bjr=" + bjr;
        param += "&kgmasuk=" + kgmasuk + "&kgkeluar=" + kgkeluar;

        param +=
            "&kodeblok=" +
            kodeblok +
            "&notiket=" +
            notiket +
            "&nospb=" +
            nospb +
            "&tahuntanam=" +
            tahuntanam +
            "&kelasbuah=" +
            kelasbuah;
        param +=
            "&kgbruto=" +
            kgbruto +
            "&kgpotongan=" +
            kgpotongan +
            "&kgnetto=" +
            kgnetto;
        param +=
            "&rpkg=" + rpkg + "&totalrp=" + totalrp + "&noafiliasi=" + noafiliasi;

        param +=
            "&potpersenaktual=" +
            potpersenaktual +
            "&potpersensetup=" +
            potpersensetup +
            "&potpersen=" +
            potpersen;
        param +=
            "&kgadjust=" +
            kgadjust +
            "&kgnettoadjust=" +
            kgnettoadjust +
            "&rpadjust=" +
            rpadjust +
            "&jumrpadjust=" +
            jumrpadjust;
        param += "&currRow=" + currRow;

        param +=
            "&nokontrak=" +
            nokontrak +
            "&dibuat=" +
            dibuat +
            "&disetujui=" +
            disetujui +
            "&diperiksa=" +
            diperiksa;
        // alert(param);return;
        tujuan = "pmn_tbsexternal_slave.php";
        post_response_text(tujuan, param, respog);
        document.getElementById("row" + currRow).style.backgroundColor = "";
        document.getElementById("row" + currRow).style.backgroundColor = "cyan";
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
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alert("Done");
                        loaddatadtexternal();
                        /*
                                        canceldt();
                            	
                                    */
                    } else {
                        loopsaveexternal(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

maxf = 0;
sekarang = 1;
function savedtinternal(maxRow) {
    maxf = maxRow;
    loopsaveinternal(1, maxRow);
}

function hitungtotalrp(no, nomax) {
    kgnetto = document.getElementById("kgnetto" + no).innerHTML;
    rpkg = document.getElementById("rpkg" + no).value;
    kgnetto = remove_comma_var(kgnetto);
    rpkg = remove_comma_var(rpkg);
    totalrp = parseFloat(kgnetto) * parseFloat(rpkg);
    document.getElementById("totalrp" + no).value = numberFormat(totalrp, 2);
    hitunggrandtotal(nomax);
}

function hitunggrandtotal(nomax) {
    ttotalrp = 0;
    for (i = 1; i <= nomax; i++) {
        totalrp = document.getElementById("totalrp" + i).value;
        totalrp = remove_comma_var(totalrp);
        ttotalrp = parseFloat(ttotalrp) + parseFloat(totalrp);
    }
    // alert(ttotalrp);
    document.getElementById("ttotalrp").innerHTML = numberFormat(ttotalrp, 2);
}

function loopsaveinternal(currRow, maxRow) {
    param = "";
    notransaksi = trim(document.getElementById("notransaksi").value);
    unit = trim(document.getElementById("unit").value);
    divisi = trim(document.getElementById("divisi").value);
    tanggal = trim(document.getElementById("tanggal").value);
    tanggaltbs1 = trim(document.getElementById("tanggaltbs1").value);
    tanggaltbs2 = trim(document.getElementById("tanggaltbs2").value);
    persenppn = trim(document.getElementById("persenppn").value);
    persenpph = trim(document.getElementById("persenpph").value);
    keteranganht = trim(document.getElementById("keteranganht").value);
    noafiliasi = trim(document.getElementById("noafiliasi").value);

    kelasbuah = trim(document.getElementById("kelasbuah" + currRow).innerHTML);
    tahuntanam = trim(document.getElementById("tahuntanam" + currRow).innerHTML);
    tanggalspb = trim(document.getElementById("tanggalspb" + currRow).innerHTML);
    tanggalpks = trim(document.getElementById("tanggalpks" + currRow).innerHTML);

    kodevhc = trim(document.getElementById("kodevhc" + currRow).innerHTML); //baru
    driver = trim(document.getElementById("driver" + currRow).innerHTML); //baru
    jjg = trim(document.getElementById("jjg" + currRow).innerHTML); //baru
    jjgsortasi = trim(document.getElementById("jjgsortasi" + currRow).innerHTML); //baru
    bjr = trim(document.getElementById("bjr" + currRow).innerHTML); //baru
    kgmasuk = trim(document.getElementById("kgmasuk" + currRow).innerHTML); //baru
    kgkeluar = trim(document.getElementById("kgkeluar" + currRow).innerHTML); //baru

    nospb = trim(document.getElementById("nospb" + currRow).innerHTML);
    notiket = trim(document.getElementById("notiket" + currRow).innerHTML);
    kodeblok = trim(document.getElementById("kodeblok" + currRow).innerHTML);
    kgbruto = trim(document.getElementById("kgbruto" + currRow).innerHTML);
    kgpotongan = trim(document.getElementById("kgpotongan" + currRow).innerHTML);
    kgnetto = trim(document.getElementById("kgnetto" + currRow).innerHTML);
    rpkg = trim(document.getElementById("rpkg" + currRow).innerHTML);
    totalrp = trim(document.getElementById("totalrp" + currRow).innerHTML);

    potpersenaktual = trim(
        document.getElementById("potpersenaktual" + currRow).innerHTML
    ); //baru
    potpersensetup = trim(
        document.getElementById("potpersensetup" + currRow).innerHTML
    ); //baru
    potpersen = trim(document.getElementById("potpersen" + currRow).innerHTML); //baru
    kgadjust = trim(document.getElementById("kgadjust" + currRow).innerHTML); //baru
    kgnettoadjust = trim(
        document.getElementById("kgnettoadjust" + currRow).innerHTML
    ); //baru
    rpadjust = trim(document.getElementById("rpadjust" + currRow).innerHTML); //baru
    jumrpadjust = trim(
        document.getElementById("jumrpadjust" + currRow).innerHTML
    ); //baru

    nokontrak = document.getElementById("nokontrak").value;
    dibuat = document.getElementById("dibuat").value;
    disetujui = document.getElementById("disetujui").value;
    diperiksa = document.getElementById("diperiksa").value;

    potpersenaktual = remove_comma_var(potpersenaktual);
    potpersensetup = remove_comma_var(potpersensetup);
    potpersen = remove_comma_var(potpersen);
    kgadjust = remove_comma_var(kgadjust);
    kgnettoadjust = remove_comma_var(kgnettoadjust);
    rpadjust = remove_comma_var(rpadjust);
    jumrpadjust = remove_comma_var(jumrpadjust);

    kgmasuk = remove_comma_var(kgmasuk);
    kgkeluar = remove_comma_var(kgkeluar);

    kgbruto = remove_comma_var(kgbruto);
    kgpotongan = remove_comma_var(kgpotongan);
    kgnetto = remove_comma_var(kgnetto);
    rpkg = remove_comma_var(rpkg);
    totalrp = remove_comma_var(totalrp);

    if (unit == "" || tanggal == "") {
        alert("Data tidak lengkap");
        return;
    } else {
        param +=
            "&method=savedt" +
            "&unit=" +
            unit +
            "&divisi=" +
            divisi +
            "&tanggal=" +
            tanggal +
            "&notransaksi=" +
            notransaksi;
        param +=
            "&tanggaltbs1=" +
            tanggaltbs1 +
            "&tanggaltbs2=" +
            tanggaltbs2 +
            "&persenppn=" +
            persenppn +
            "&persenpph=" +
            persenpph +
            "&keteranganht=" +
            keteranganht;
        param += "&tanggalspb=" + tanggalspb + "&tanggalpks=" + tanggalpks;

        param += "&kodevhc=" + kodevhc + "&driver=" + driver;
        param += "&jjg=" + jjg + "&jjgsortasi=" + jjgsortasi + "&bjr=" + bjr;
        param += "&kgmasuk=" + kgmasuk + "&kgkeluar=" + kgkeluar;

        param +=
            "&kodeblok=" +
            kodeblok +
            "&notiket=" +
            notiket +
            "&nospb=" +
            nospb +
            "&tahuntanam=" +
            tahuntanam +
            "&kelasbuah=" +
            kelasbuah;
        param +=
            "&kgbruto=" +
            kgbruto +
            "&kgpotongan=" +
            kgpotongan +
            "&kgnetto=" +
            kgnetto;
        param +=
            "&rpkg=" + rpkg + "&totalrp=" + totalrp + "&noafiliasi=" + noafiliasi;

        param +=
            "&potpersenaktual=" +
            potpersenaktual +
            "&potpersensetup=" +
            potpersensetup +
            "&potpersen=" +
            potpersen;
        param +=
            "&kgadjust=" +
            kgadjust +
            "&kgnettoadjust=" +
            kgnettoadjust +
            "&rpadjust=" +
            rpadjust +
            "&jumrpadjust=" +
            jumrpadjust;
        param += "&currRow=" + currRow;

        param +=
            "&nokontrak=" +
            nokontrak +
            "&dibuat=" +
            dibuat +
            "&disetujui=" +
            disetujui +
            "&diperiksa=" +
            diperiksa;
        // alert(param);return;
        tujuan = "pmn_tbsinternal_slave.php";
        post_response_text(tujuan, param, respog);
        document.getElementById("row" + currRow).style.backgroundColor = "cyan";
        document.getElementById("notiket" + currRow).style.backgroundColor = "cyan";
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
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alert("Done");
                        loaddatadtinternal();
                        /*
                                        canceldt();
                            	
                                    */
                    } else {
                        loopsaveinternal(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function edithtkud(notransaksi) {
    param = "method=geteditht" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbskud_slave.php";
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('method').value = 'update';
                    // alert(con.responseText.split);
                    ar = con.responseText.split("###");
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("unit").value = ar[1];
                    document.getElementById("divisi").innerHTML = ar[2];
                    document.getElementById("tanggal").value = ar[3];
                    document.getElementById("tanggaltbs1").value = ar[4];
                    document.getElementById("tanggaltbs2").value = ar[5];
                    document.getElementById("keteranganht").value = ar[6];
                    document.getElementById("noafiliasi").value = ar[7];
                    document.getElementById("persenppn").value = ar[8];
                    document.getElementById("persenpph").value = ar[9];

                    document.getElementById("nokontrak").innerHTML = ar[10];
                    document.getElementById("dibuat").value = ar[11];
                    document.getElementById("disetujui").value = ar[12];
                    document.getElementById("diperiksa").value = ar[13];

                    document.getElementById("notransaksi").disabled = true;
                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    // document.getElementById('keteranganht').disabled=true;
                    document.getElementById("saveht").disabled = true;

                    document.getElementById("nokontrak").disabled = true;
                    document.getElementById("dibuat").disabled = true;
                    document.getElementById("jenisx").disabled = true;
                    document.getElementById("jenisx").value = "kud";
                    // document.getElementById('disetujui').disabled=true;
                    // document.getElementById('diperiksa').disabled=true;

                    document.getElementById("listdata").style.display = "none";
                    document.getElementById("header").style.display = "block";

                    document.getElementById("detailexternal").style.display = "none";
                    document.getElementById("detailafiliasi").style.display = "none";
                    document.getElementById("detailinternal").style.display = "none";
                    document.getElementById("detailkud").style.display = "block";

                    loaddatadtkud();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    post_response_text(tujuan, param, respog);
}

function edithtexternal(notransaksi) {
    param = "method=geteditht" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsexternal_slave.php";
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('method').value = 'update';
                    ar = con.responseText.split("###");
                    // alert(ar[2]);
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("unit").value = ar[1];
                    document.getElementById("divisi").innerHTML = ar[2];
                    document.getElementById("tanggal").value = ar[3];
                    document.getElementById("tanggaltbs1").value = ar[4];
                    document.getElementById("tanggaltbs2").value = ar[5];
                    document.getElementById("keteranganht").value = ar[6];
                    document.getElementById("noafiliasi").value = ar[7];
                    document.getElementById("persenppn").value = ar[8];
                    document.getElementById("persenpph").value = ar[9];

                    document.getElementById("nokontrak").innerHTML = ar[10];
                    document.getElementById("dibuat").value = ar[11];
                    document.getElementById("disetujui").value = ar[12];
                    document.getElementById("diperiksa").value = ar[13];

                    document.getElementById("notransaksi").disabled = true;
                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    // document.getElementById('keteranganht').disabled=true;
                    document.getElementById("saveht").disabled = true;
                    document.getElementById("jenisx").disabled = true;
                    document.getElementById("jenisx").value = "external";

                    // document.getElementById('nokontrak').disabled=true;
                    // document.getElementById('dibuat').disabled=true;
                    // document.getElementById('disetujui').disabled=true;
                    // document.getElementById('diperiksa').disabled=true;

                    document.getElementById("listdata").style.display = "none";
                    document.getElementById("header").style.display = "block";
                    document.getElementById("detailexternal").style.display = "block";
                    document.getElementById("detailafiliasi").style.display = "none";
                    document.getElementById("detailinternal").style.display = "none";
                    document.getElementById("detailkud").style.display = "none";
                    loaddatadtexternal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function edithtafiliasi(notransaksi) {
    param = "method=geteditht" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsafiliasi_slave.php";
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('method').value = 'update';
                    // alert(con.responseText.split);
                    ar = con.responseText.split("###");
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("unit").value = ar[1];
                    document.getElementById("divisi").innerHTML = ar[2];
                    document.getElementById("tanggal").value = ar[3];
                    document.getElementById("tanggaltbs1").value = ar[4];
                    document.getElementById("tanggaltbs2").value = ar[5];
                    document.getElementById("keteranganht").value = ar[6];
                    document.getElementById("noafiliasi").value = ar[7];
                    document.getElementById("persenppn").value = ar[8];
                    document.getElementById("persenpph").value = ar[9];

                    document.getElementById("nokontrak").innerHTML = ar[10];
                    document.getElementById("dibuat").value = ar[11];
                    document.getElementById("disetujui").value = ar[12];
                    document.getElementById("diperiksa").value = ar[13];

                    document.getElementById("notransaksi").disabled = true;
                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    document.getElementById("keteranganht").disabled = true;
                    document.getElementById("saveht").disabled = true;
                    document.getElementById("nokontrak").disabled = true;
                    document.getElementById("jenisx").disabled = true;
                    document.getElementById("jenisx").value = "afiliasi";

                    document.getElementById("listdata").style.display = "none";
                    document.getElementById("header").style.display = "block";
                    document.getElementById("detailexternal").style.display = "none";
                    document.getElementById("detailafiliasi").style.display = "block";
                    document.getElementById("detailinternal").style.display = "none";
                    document.getElementById("detailkud").style.display = "none";
                    loaddatadtafiliasi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function edithtinternal(notransaksi) {
    param = "method=geteditht" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsinternal_slave.php";
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('method').value = 'update';
                    ar = con.responseText.split("###");
                    // alert(ar[2]);
                    document.getElementById("notransaksi").value = ar[0];
                    document.getElementById("unit").value = ar[1];
                    document.getElementById("divisi").innerHTML = ar[2];
                    document.getElementById("tanggal").value = ar[3];
                    document.getElementById("tanggaltbs1").value = ar[4];
                    document.getElementById("tanggaltbs2").value = ar[5];
                    document.getElementById("keteranganht").value = ar[6];
                    document.getElementById("noafiliasi").value = ar[7];
                    document.getElementById("persenppn").value = ar[8];
                    document.getElementById("persenpph").value = ar[9];

                    document.getElementById("nokontrak").innerHTML = ar[10];
                    document.getElementById("dibuat").value = ar[11];
                    document.getElementById("disetujui").value = ar[12];
                    document.getElementById("diperiksa").value = ar[13];

                    document.getElementById("notransaksi").disabled = true;
                    document.getElementById("unit").disabled = true;
                    document.getElementById("divisi").disabled = true;
                    document.getElementById("tanggal").disabled = true;
                    document.getElementById("tanggaltbs1").disabled = true;
                    document.getElementById("tanggaltbs2").disabled = true;
                    document.getElementById("persenppn").disabled = true;
                    document.getElementById("persenpph").disabled = true;
                    // document.getElementById('keteranganht').disabled=true;
                    document.getElementById("saveht").disabled = true;
                    document.getElementById("jenisx").disabled = true;
                    document.getElementById("jenisx").value = "internal";

                    // document.getElementById('nokontrak').disabled=true;
                    // document.getElementById('dibuat').disabled=true;
                    // document.getElementById('disetujui').disabled=true;
                    // document.getElementById('diperiksa').disabled=true;

                    document.getElementById("listdata").style.display = "none";
                    document.getElementById("header").style.display = "block";
                    document.getElementById("detailinternal").style.display = "block";
                    document.getElementById("detailexternal").style.display = "none";
                    document.getElementById("detailafiliasi").style.display = "none";
                    document.getElementById("detailkud").style.display = "none";
                    loaddatadtinternal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function deletehtkud(notransaksi) {
    param = "method=deleteht";
    param += "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbskud_slave.php";
    alertify.confirm(
        "Informasi",
        "Hapus transaksi : " + notransaksi + " ???",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );
    // post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
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

function deletehtexternal(notransaksi) {
    param = "method=deleteht";
    param += "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsexternal_slave.php";

    // if (confirm("Anda yakin?")) {
    // 	post_response_text(tujuan, param, respog);
    // }

    alertify.confirm(
        "Informasi",
        "Hapus transaksi : " + notransaksi + " ???",
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
                    alertify.alert("Informasi", con.responseText);
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

function deletehtafiliasi(notransaksi) {
    param = "method=deleteht";
    param += "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsafiliasi_slave.php";
    alertify.confirm(
        "Informasi",
        "Posting transaksi : " + notransaksi + " ???",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );
    // post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
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

function deletehtinternal(notransaksi) {
    param = "method=deleteht";
    param += "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsinternal_slave.php";

    // if (confirm("Anda yakin?")) {
    // 	post_response_text(tujuan, param, respog);
    // }

    alertify.confirm(
        "Informasi",
        "Hapus transaksi : " + notransaksi + " ???",
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
                    alertify.alert("Informasi", con.responseText);
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

function formdetailkud(notransaksi) {
    width = "";
    height = "";
    // content = "<fieldset><div id=containerd style=\"height:100%;width:100%;\"></div></fieldset>";
    content =
        '<fieldset><div id=containview style="overflow:auto;height:350px;width:1000px;"></div></fieldset>';

    ev = "event";
    title = "";
    showDialog1(title, content, width, height, ev);
    htmlkud(notransaksi, "html");
}

function htmlkud(notransaksi, tipe) {
    // param = 'method=viewdetail';
    // param += '&notransaksi=' + notransaksi+'&tipe=' + tipe;
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    tujuan = "pmn_tbskud_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById("containview").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formdetailexternal(notransaksi) {
    width = "";
    height = "";
    // content = "<fieldset><div id=containerd style=\"height:100%;width:100%;\"></div></fieldset>";
    content =
        '<fieldset><div id=containview style="overflow:auto;height:350px;width:1000px;"></div></fieldset>';

    ev = "event";
    title = "";
    // showDialog1(title, content, width, height, ev);
    htmlexternal(notransaksi, "html");
}

function htmlexternal(notransaksi, tipe) {
    // param = 'method=viewdetail';
    // param += '&notransaksi=' + notransaksi+'&tipe=' + tipe;
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    tujuan = "pmn_tbsexternal_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    // document.getElementById('containview').innerHTML = con.responseText;
                    alertify
                        .popup("Detail", con.responseText)
                        .set({ resizable: true, maximizable: true })
                        .resizeTo("90%", "80%");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formdetailafiliasi(notransaksi) {
    width = "";
    height = "";
    // content = "<fieldset><div id=containerd style=\"height:100%;width:100%;\"></div></fieldset>";
    content =
        '<fieldset><div id=containview style="overflow:auto;height:350px;width:1000px;"></div></fieldset>';

    ev = "event";
    title = "";
    showDialog1(title, content, width, height, ev);
    htmlafiliasi(notransaksi, "html");
}

function htmlafiliasi(notransaksi, tipe) {
    // param = 'method=viewdetail';
    // param += '&notransaksi=' + notransaksi+'&tipe=' + tipe;
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    tujuan = "pmn_tbsafiliasi_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById("containview").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formdetailinternal(notransaksi) {
    width = "";
    height = "";
    // content = "<fieldset><div id=containerd style=\"height:100%;width:100%;\"></div></fieldset>";
    content =
        '<fieldset><div id=containview style="overflow:auto;height:350px;width:1000px;"></div></fieldset>';

    ev = "event";
    title = "";
    // showDialog1(title, content, width, height, ev);
    htmlinternal(notransaksi, "html");
}

function htmlinternal(notransaksi, tipe) {
    // param = 'method=viewdetail';
    // param += '&notransaksi=' + notransaksi+'&tipe=' + tipe;
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    tujuan = "pmn_tbsinternal_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    // document.getElementById('containview').innerHTML = con.responseText;
                    alertify
                        .popup("Detail", con.responseText)
                        .set({ resizable: true, maximizable: true })
                        .resizeTo("90%", "80%");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdfkud(notransaksi, tipe) {
    tipe = "pdf";
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    tujuan = "pmn_tbskud_slave.php";
    tujuan = tujuan + "?" + param;
    content =
        "<iframe frameborder=0 style='width:100%;height:99%' src='" +
        tujuan +
        "'></iframe>";
    width = "820";
    height = "500";
    title = "";
    // showDialog5(title, content, width, height, 'event');
    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_tbskud_slave.php?" +
            param +
            "'></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function excelkud(notransaksi) {
    param = "method=excel" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbskud_slave.php";
    tujuan = tujuan + "?" + param;
    content =
        "<iframe frameborder=0 style='width:100%;height:99%' src='" +
        tujuan +
        "'></iframe>";
    width = "820";
    height = "500";
    title = "";
    showDialog5(title, content, width, height, "event");
}

function pdfexternal(notransaksi, tipe) {
    tipe = "pdf";
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    // tujuan='pmn_tbsexternal_slave.php';
    // tujuan = tujuan+'?' + param;
    // content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
    // width = '820';
    // height = '500';
    // title = "";
    // showDialog5(title, content, width, height, 'event');

    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_tbsexternal_slave.php?" +
            param +
            "'></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function excelexternal(notransaksi) {
    param = "method=excel" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsexternal_slave.php";
    tujuan = tujuan + "?" + param;
    content =
        "<iframe frameborder=0 style='width:100%;height:99%' src='" +
        tujuan +
        "'></iframe>";
    width = "820";
    height = "500";
    title = "";
    showDialog5(title, content, width, height, "event");
}

function pdfafiliasi(notransaksi) {
    tipe = "pdf";
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    tujuan = "pmn_tbsafiliasi_slave.php";
    tujuan = tujuan + "?" + param;
    content =
        "<iframe frameborder=0 style='width:100%;height:99%' src='" +
        tujuan +
        "'></iframe>";
    width = "820";
    height = "500";
    title = "";
    // showDialog5(title, content, width, height, 'event');
    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src=" +
            tujuan +
            "></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function excelafiliasi(notransaksi) {
    param = "method=excel" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsafiliasi_slave.php";
    tujuan = tujuan + "?" + param;
    content =
        "<iframe frameborder=0 style='width:100%;height:99%' src='" +
        tujuan +
        "'></iframe>";
    width = "820";
    height = "500";
    title = "";
    showDialog5(title, content, width, height, "event");
}

function pdfinternal(notransaksi, tipe) {
    tipe = "pdf";
    param = "method=pdf" + "&notransaksi=" + notransaksi + "&tipe=" + tipe;
    alertify
        .popuppdf(
            "title",
            "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_tbsinternal_slave.php?" +
            param +
            "'></iframe>"
        )
        .set({ resizable: true, overflow: false })
        .resizeTo("90%", "80%");
}

function excelinternal(notransaksi) {
    param = "method=excel" + "&notransaksi=" + notransaksi;
    tujuan = "pmn_tbsinternal_slave.php";
    tujuan = tujuan + "?" + param;
    content =
        "<iframe frameborder=0 style='width:100%;height:99%' src='" +
        tujuan +
        "'></iframe>";
    width = "820";
    height = "500";
    title = "";
    showDialog5(title, content, width, height, "event");
}

function saveajukan(notransaksi, tipeapp, maxaproval) {
    param = "";
    method = "saveajukan";
    tanggalpengajuan = document.getElementById("tglpengajuan").value;

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
        "&tipeapp=" +
        tipeapp;
    param += "&maxaproval=" + maxaproval;
    param += "&method=" + method;
    param += strper;
    tujuan = "pmn_tbsall_slave.php";

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
                    // formdetail(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function posting(notransaksi) {
    param = "";
    method = "posting";
    param += "notransaksi=" + notransaksi;
    param += "&method=" + method;
    tujuan = "pmn_tbsall_slave.php";

    alertify.confirm(
        "Informasi",
        "Anda yakin posting transaksi : " + notransaksi + " ???",
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
                    alertify.set("notifier", "position", "top-right");
                    alertify.success("Berhasil");
                    getpage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printnopopup(url) {
    // alert(url);
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = "none";
    document.body.appendChild(ifrm);
}
