function preview(kodeorg, tanggal, notransaksi) {
    param = 'proses=preview';
    param += "&kodeorg=" + kodeorg;
    param += "&tanggal=" + tanggal;
    // param += "&notransaksiupload=" + notransaksi;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup().set({ 'resizable': true, 'maximizable': true, 'startMaximized': true, 'message': con.responseText }).resizeTo('80%', '70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getalokasi(noakun, divisi, idhasil) {
    param = 'proses=getalokasi';
    param += "&noakun=" + noakun;
    param += "&divisi=" + divisi;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById(idhasil).innerHTML = con.responseText;
                    //getsessionlembur();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function updtjam(no, row) {
    if (document.getElementById('jamlmbr_' + no) != undefined) {
        Jam = document.getElementById('jamlmbr_' + no).value;
    } else {
        Jam = document.getElementById('jam').value;
    }
    jammulai = document.getElementById('jam_mulai_pop').value;
    ttljampop = document.getElementById('ttljampop').value;

    param = 'Jam=' + Jam + '&proses=updtjam';
    param += "&jammulai=" + jammulai;
    param += "&ttljampop=" + ttljampop;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //alertify.alert(con.responseText);
                    if (row == '1') {
                        document.getElementById('jam_selesai_pop').value = con.responseText;
                    }
                    hitungjam(no);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    // jammulai = jammulai.split(":");

    // jam1=jammulai[0];
    // jam2=jammulai[1];


    // jam2=jam2/60;

    // jmbaru=parseFloat(jam1)+parseFloat(jam2);

    // jmtot=parseFloat(jmbaru)+parseFloat(jamlembur);


    // jmtot = jmtot.split(".");


    // jmbr=jmtot[0];
    // mntbr=jmtot[1];

    // if(jmbr>=24){
    // jmbr=24-parseFloat(jambr);
    // }

    // document.getElementById('jam_selesai').value=jmbr+':'+mntbr;


    //alert(jmtot);
    //alert(jamlembur);
}

function add_new_data() {
    document.getElementById('headher').style.display = "block";
    document.getElementById('listData').style.display = "none";
    document.getElementById('detailEntry').style.display = "none";
    document.getElementById('tmbLheader').innerHTML = '<button class=mybutton id=dtlAbn onclick=detailAbsn()>' + nmTmblSave + '</button><button class=mybutton id=cancelAbn onclick=cancelAbsn()>' + nmTmblCancel + '</button>';
    unlockForm();
    document.getElementById('contentDetail').innerHTML = '';
    statFrm = 0;
}
function displayList() {
    document.getElementById('listData').style.display = 'block';
    document.getElementById('headher').style.display = 'none';
    document.getElementById('detailEntry').style.display = 'none';
    document.getElementById('kdOrgCr').value = '';
    document.getElementById('tgl_cari').value = '';
    loadData();
}

function cancelAbsn() {
    document.getElementById('kdOrg').value = '';
    document.getElementById('tglAbsen').value = '';
    document.getElementById('jabatan').value = '';
    document.getElementById('tipekar').value = '';
}

function cariOrg(title, content, ev) {
    width = '500';
    height = '400';
    showDialog1(title, content, width, height, ev);
    //alert('asdasd');
}
function findOrg() {
    txt = trim(document.getElementById('fnOrg').value);
    if (txt == '') {
        alert('Text is obligatory');
    } else if (txt.length < 3) {
        alert('Text too short');
    } else {
        param = 'txtfind=' + txt + '&proses=cariOrg';
        tujuan = 'sdm_slave_lembur.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //alertify.alert(con.responseText);
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function setOrg(kdOrg, nmOrg) {
    document.getElementById('kdOrg').value = kdOrg;
    document.getElementById('nmOrg').value = nmOrg;
    closeDialog();
}
function findOrg2() {
    txt = trim(document.getElementById('crOrg').value);
    if (txt == '') {
        alert('Text is obligatory');
    } else if (txt.length < 3) {
        alert('Text too short');
    } else {
        param = 'txtfind=' + txt + '&proses=cariOrg2';
        tujuan = 'sdm_slave_lembur.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //alertify.alert(con.responseText);
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function setOrg2(kdOrg, nmOrg) {
    document.getElementById('kdOrg').value = kdOrg;
    document.getElementById('txtsearch').value = nmOrg;
    closeDialog();
}

function detailAbsn() {
    kdorg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    tgl = document.getElementById('tglAbsen').value;
    if ((kdorg == '') || (tgl == '')) {
        alert("Date and organization code are obligatory");
        return;
    }

    id = kdorg + "###" + tgl;
    tujuan = 'sdm_slave_lembur.php';
    param = 'absnId=' + id + '&proses=cekHeader';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    numrow = con.responseText;
                    add_detail(numrow);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function postingx(kdorg, tgl, posted) {
    kdtmp = kdorg;
    tgltmp = tgl;
    absnId = kdtmp + "###" + tgltmp;
    param = 'absnId=' + absnId + '&proses=postingx';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (posted != 1) {
                        alertify.popup().destroy();
                        alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': false }).resizeTo('400px', '300px');
                    } else {
                        getPage();
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function save_persetujuan() {
    nopengajuan = document.getElementById('nopengajuan').value;
    karyawanid = document.getElementById('karywn_id').value;
    kodeorg = document.getElementById('kodeorgapp').value;
    tanggal = document.getElementById('tanggalapp').value;

    param = 'nopengajuan=' + nopengajuan + '&proses=save_persetujuan';
    param += '&karyawanid=' + karyawanid;
    param += '&kodeorg=' + kodeorg;
    param += '&tanggal=' + tanggal;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    getPage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function detaillembur(periode, kary) {
    param = 'proses=detaillembur' + '&periode=' + periode + '&kary=' + kary;
    tujuan = 'sdm_slave_2laporanLembur_rekap.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup2("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('80%', '70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getapprovaldetail(nopengajuan, kodeorg, ev) {
    param = 'proses=getapprovaldetail' + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alertify.alert(con.responseText);
                } else {
                    alertify.popup2("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('35%', '50%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function add_detail(numrow) {
    if (numrow > 0) {
        alert("Tanggal yang dipilih adalah hari libur.");
    }
    kdorg = document.getElementById('kdOrg').value;
    tgl = document.getElementById('tglAbsen').value;
    // alert(tgl);
    // return;
    jabatan = document.getElementById('jabatan').value;
    tipekar = document.getElementById('tipekar').value;
    id = kdorg + "###" + tgl;
    param = 'absnId=' + id + '&jabatan=' + jabatan + '&tipekar=' + tipekar + '&tanggal=' + tgl;
    param += "&proses=createTableall";
    tujuan = 'sdm_slave_lembur.php';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('detailEntry').style.display = 'block';
                    document.getElementById('detailIsi').innerHTML = con.responseText;
                    $(document).ready(function () {
                        $('.select2').select2({
                            dropdownAutoWidth: true
                        });
                    });

                    $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
                        $(this).closest(".select2-container").siblings('select:enabled').select2('open');
                    });
                    document.getElementById('loaddetail').style.display = 'none';
                    document.getElementById('tmbLheader').innerHTML = '';
                    document.getElementById('tombol').innerHTML = '';
                    lockForm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}
function lockForm() {
    document.getElementById('kdOrg').disabled = true;
    document.getElementById('tglAbsen').disabled = true;
    document.getElementById('tipekar').disabled = true;
    document.getElementById('jabatan').disabled = true;
}

function unlockForm() {
    document.getElementById('kdOrg').disabled = false;
    document.getElementById('tglAbsen').disabled = false;
    document.getElementById('tipekar').disabled = false;
    document.getElementById('jabatan').disabled = false;
    document.getElementById('kdOrg').value = '';
    document.getElementById('tglAbsen').value = '';
    document.getElementById('tipekar').value = '';
    document.getElementById('jabatan').value = '';
}

status_inputan = 0;
function addDetail() {
    tipetransaksi = document.getElementById('proses').value;
    // if (tipetransaksi == 'insert') {
    // 	alert("Silahkan input Pengajuan Lembur terlebih dahulu.");
    // 	return false;
    // }
    if (status_inputan == 0) {
        if (confirm('Are you sure..?')) {
            cek_data();
        }
    } else if (status_inputan != 0) {
        cek_data();
    }
}

function editDetail(krywn, tplmbr, jmaktl, ungmkn, ungtrans, unglbhjm, jammulai, jamselesai, ket, noakun, alokasi, nama) {
    document.getElementById('krywnId').value = krywn;
    document.getElementById('krywnId').disabled = true;
    document.getElementById('tpLmbr').value = tplmbr;
    document.getElementById('uang_mkn').value = ungmkn;
    document.getElementById('uang_trnsprt').value = ungtrans;
    document.getElementById('uang_lbhjm').value = unglbhjm;
    document.getElementById('jam_mulai').value = jammulai;
    document.getElementById('jam_selesai').value = jamselesai;
    document.getElementById('keterangan').value = ket;
    setValue2('noakun', noakun)
    document.getElementById('jam').value = jmaktl;
    document.getElementById('alokasi').value = alokasi;
    document.getElementById('proses').value = "updateDetail";
    getLembur(tplmbr, jmaktl);

    document.getElementById('krywnId').innerHTML = "<option value='" + krywn + "'>" + nama + "</option>";
    // document.getElementById('noakun').innerHTML="<option value='"+ noakun+"'>"+ noakun +"</option>";
    // setValue2('krywnId',krywn);
    // setValue2('noakun',noakun);
}

/* Function deleteDelete(id)
 * Fungsi untuk menghapus data Detail
 * I : id row (urutan row pada table Detail)
 * P : Menghapus data pada tabel Detail
 * O : Menghapus baris pada tabel Detail
 */
function deleteDetail(id) {
    kdorg = document.getElementById('kdOrg').value;
    tgl = document.getElementById('tglAbsen').value;

    var detKode = kdorg + "###" + tgl;
    var rkrywn = document.getElementById('krywnId_' + id);
    param = "proses=detail_delete";
    param += "&absnId=" + detKode;
    param += "&krywnId=" + rkrywn.value;
    //alert(param);
    tujuan = 'sdm_slave_detail_lembur.php';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    // Success Response
                    row = document.getElementById("detail_tr_" + id);
                    if (row) {
                        row.style.display = "none";
                    } else {
                        alert("Row undetected");
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if (confirm('Deleting, are you sure..?')) {
        post_response_text(tujuan, param, respon);
    } else {
        return;
    }
}
/* Function addNewRow
 * Fungsi untuk menambah row baru ke dalam table
 * I : id dari tbody tabel
 * P : Persiapan row dalam bentuk HTML
 * O : Tambahan row pada akhir tabel (append)
 */
function addNewRow(body, onDetail) {
    //alert(body);
    var tabBody = document.getElementById(body);
    if (onDetail) {
        var detail = onDetail;

    } else {
        var detail = false;
    }

    // Search Available numRow
    var numRow = 0;
    if (!detail) {
        while (document.getElementById('tr_' + numRow)) {
            numRow++;
        }
    } else {
        while (document.getElementById('detail_tr_' + numRow)) {
            numRow++;
        }
    }

    // Add New Row
    var newRow = document.createElement("tr");
    tabBody.appendChild(newRow);
    if (!detail) {
        newRow.setAttribute("id", "tr_" + numRow);
    } else {
        newRow.setAttribute("id", "detail_tr_" + numRow);
    }
    newRow.setAttribute("class", "rowcontent");

    if (!detail) {
        newRow.innerHTML += "<td><input id='kode_" + numRow +
            "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='matauang_" + numRow +
            "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='simbol_" + numRow +
            "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='kodeiso_" + numRow +
            "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><img id='add_" + numRow +
            "' title='Tambah' class=zImgBtn onclick=\"addMain('" + numRow + "')\" src='images/plus.png'/>" +
            "&nbsp;<img id='delete_" + numRow + "' />" +
            "&nbsp;<img id='pass_" + numRow + "' />" +
            "</td>";
    } else {
        // Create Row
        newRow.innerHTML += "<td><select id='krywnId_" + numRow + "' type='text' style='width:150px' />" + optIsi + "</select></td><td>" + "<select id='tpLmbr_" + numRow + "' />" + optLmbr + "</select></td>" + "<td><select id='jmId_" + numRow + "' type='text' />" + optJm + "</select>:<select id='mntId_" + numRow + "' type='text' />" + optMnt + "</select></td>" + "<td><input type='text' onfocus=\"normal_number_1('" + numRow + "')\" onblur=\"chngeFormat('" + numRow + "')\" maxlength='10' onkeypress='return angka_doang(event)' style='width: 100px;' value='0' class='myinputtextnumber' name=uang_mkn_" + numRow + " id=uang_mkn_" + numRow + "></td>" + "<td><input type='text' onfocus=\"normal_number_1('" + numRow + "')\" onblur=\"chngeFormat('" + numRow + "')\" maxlength='10' onkeypress='return angka_doang(event)' style='width: 100px;' value='0' class='myinputtextnumber' name=uang_trnsprt_" + numRow + " id=uang_trnsprt_" + numRow + "></td>" + "<td><input type='text' onfocus=\"normal_number_1('" + numRow + "')\" onblur=\"chngeFormat('" + numRow + "')\" maxlength='10' onkeypress='return angka_doang(event)' style='width: 100px;' value='0' class='myinputtextnumber' name=uang_lbhjm_" + numRow + " id=uang_lbhjm_" + numRow + "></td>" + "<td><img id='detail_add_" + numRow + "' title='Tambah' class=zImgBtn onclick=\"addDetail('" + numRow + "')\" src='images/save.png'/>" + "&nbsp;<img id='detail_delete_" + numRow + "' />" + "&nbsp;<img id='detail_pass_" + numRow + "' />" + "</td>";
    }
}
/* Function switchEditAdd
 * Fungsi untuk mengganti image add menjadi edit dan keroconya
 * I : id nomor row
 * P : Image Add menjadi Edit
 * O : Image Edit
 */
function switchEditAdd(id, main) {

    if (main == 'main') {
        var idField = document.getElementById('add_' + id);
        var delImg = document.getElementById('delete_' + id);
        var passImg = document.getElementById('pass_' + id);
        var kode = document.getElementById('kode_' + id);
    } else {
        //alert(id);
        var idField = document.getElementById('detail_add_' + id);
        var delImg = document.getElementById('detail_delete_' + id);
    }
    if (idField) {
        idField.removeAttribute('id');
        idField.removeAttribute('name');
        idField.removeAttribute('onclick');
        idField.removeAttribute('src');
        idField.removeAttribute('title');

        // Set Edit Image Attr
        idField.setAttribute('title', 'Edit');
        if (main == 'main') {
            idField.setAttribute('id', 'edit_' + id);
            idField.setAttribute('name', 'edit_' + id);
            idField.setAttribute('onclick', 'editMain(\'' + id + '\',\'kode\',\'' + kode.value + '\')');
        } else {
            //alert(id);
            idField.setAttribute('id', 'detail_edit_' + id);
            idField.setAttribute('name', 'detail_edit_' + id);
            idField.setAttribute('onclick', 'editDetail(\'' + id + '\')');
        }
        idField.setAttribute('src', 'images/001_45.png');

        // Set Delete Image Attr
        delImg.setAttribute('class', 'zImgBtn');
        delImg.setAttribute('title', 'Hapus');
        if (main == 'main') {
            delImg.setAttribute('name', 'delete_' + id);
            delImg.setAttribute('onclick', 'deleteMain(\'' + id + '\',\'kode\',\'' + kode.value + '\')');
        } else {
            //alert(id);
            delImg.setAttribute('name', 'detail_delete_' + id);
            delImg.setAttribute('onclick', 'deleteDetail(\'' + id + '\')');
            document.getElementById('krywnId_' + id).disabled = true;
        }
        delImg.setAttribute('src', 'images/delete_32.png');

    } else {
        alert('DOM Definition Error');
    }
}
statFrm = 0;
function showTmbl() {
    if (statFrm == 0) {
        document.getElementById('tombol').innerHTML = "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button><button class=mybutton onclick=reset_data()>" + nmTmblCancel + "</button>";
    } else if (statFrm == 1) {
        document.getElementById('tombol').innerHTML = "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button>";
    }
}
function cek_data() {

    //var detKode = document.getElementById('detail_kode');
    kdorg = document.getElementById('kdOrg').value;
    tgl = document.getElementById('tglAbsen').value;

    var detKode = kdorg + "###" + tgl;
    var rkrywn = document.getElementById('krywnId').value;
    var rtpLmbr = document.getElementById('tpLmbr').value;
    var rungMkn = document.getElementById('uang_mkn').value;
    var jam = document.getElementById('jam').value;
    var rungTrans = document.getElementById('uang_trnsprt').value;
    var rungLbhjm = document.getElementById('uang_lbhjm').value;
    var jammulai = document.getElementById('jam_mulai').value;
    var jamselesai = document.getElementById('jam_selesai').value;
    var ket = document.getElementById('keterangan').value;
    var noakun = document.getElementById('noakun').value;
    var alokasi = document.getElementById('alokasi').value;
    pros = document.getElementById('proses').value;

    if (pros != "updateDetail") {
        param = "proses=cekData";
    } else {
        param = "proses=updateDetail";
    }
    if (ket == '') {
        alertify.alert("Keterangan tidak boleh kosong.");
        return;
    }
    param += "&absnId=" + detKode;
    param += "&tpLmbr=" + rtpLmbr;
    param += "&krywnId=" + rkrywn;
    param += "&ungTrans=" + rungTrans;
    param += "&ungLbhjm=" + rungLbhjm;
    param += "&ungMkn=" + rungMkn;
    param += "&Jam=" + jam;
    param += "&jammulai=" + jammulai;
    param += "&jamselesai=" + jamselesai;
    param += "&ket=" + ket;
    param += "&noakun=" + noakun;
    param += "&alokasi=" + alokasi;

    tujuan = 'sdm_slave_lembur.php';
    //alert(param);
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (con.responseText != '') {
                        alertify.alert(con.responseText);
                    }
                    //cekLembur(tgl,krywnId);
                    status_inputan = 1;
                    showTmbl();
                    bersihFormDet();
                    loadDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function popupjam(no) {
    kdorg = document.getElementById('kdOrg').value;
    tgl = document.getElementById('tglAbsen').value;
    if (document.getElementById('krywnId') == undefined) {
        karyawanid = document.getElementById('kar_' + no).value;
    } else {
        karyawanid = document.getElementById('krywnId').value;
    }

    param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&karyawanid=' + karyawanid + '&no=' + no + '&proses=popupjam';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': false }).resizeTo('750px', '80%');
                    loaddatasession(kdorg, tgl, karyawanid);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatasession(kdorg, tgl, karyawanid) {
    param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&karyawanid=' + karyawanid + '&proses=loaddatasession';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('listdatasession').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savesession(no) {
    kdorg = document.getElementById('kdOrg').value;
    tgl = document.getElementById('tglAbsen').value;
    mulai = document.getElementById('jam_mulai_pop').value;
    selesai = document.getElementById('jam_selesai_pop').value;
    jumlah = document.getElementById('jumlah').value;
    ket = document.getElementById('ket_pop').value;
    if (document.getElementById('krywnId') == undefined) {
        karyawanid = document.getElementById('kar_' + no).value;
    } else {
        karyawanid = document.getElementById('krywnId').value;
    }

    param = 'mulai=' + mulai + '&ket=' + ket + '&tgl=' + tgl + '&karyawanid=' + karyawanid + '&jumlah=' + jumlah + '&selesai=' + selesai + '&kdorg=' + kdorg + '&proses=savesession';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    data = con.responseText.split("##");
                    basis = data[3];
                    if (document.getElementById('krywnId') == undefined) {
                        document.getElementById('jam_mulai_' + no).value = data[0];
                        document.getElementById('jam_selesai_' + no).value = data[1];
                        document.getElementById('ttljam_' + no).value = data[3];
                        document.getElementById('keterangan_' + no).value = data[4];

                        ttljam = getValue('jamlmbr_' + no);
                        if (ttljam == '') { ttljam = 0; }
                        if (parseFloat(ttljam) > parseFloat(basis)) {
                            document.getElementById('jam_mulai_' + no).style.backgroundColor = "yellow";
                            document.getElementById('jam_selesai_' + no).style.backgroundColor = "yellow";
                        } else {
                            document.getElementById('jam_mulai_' + no).style.backgroundColor = "";
                            document.getElementById('jam_selesai_' + no).style.backgroundColor = "";
                        }

                    } else {
                        document.getElementById('jam_mulai').value = data[0];
                        document.getElementById('jam_selesai').value = data[1];
                        document.getElementById('ttljam').value = data[3];
                        document.getElementById('keterangan').value = data[4];
                        ttljam = getValue('jam');
                        if (ttljam == '') { ttljam = 0; }
                        if (parseFloat(ttljam) > parseFloat(basis)) {
                            document.getElementById('jam_mulai').style.backgroundColor = "yellow";
                            document.getElementById('jam_selesai').style.backgroundColor = "yellow";
                        } else {
                            document.getElementById('jam_mulai').style.backgroundColor = "";
                            document.getElementById('jam_selesai').style.backgroundColor = "";
                        }
                    }
                    //loaddatasession(kdorg,tgl,karyawanid);
                    document.getElementById('listdatasession').innerHTML = data[2];

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function hitungjam(no) {
    tgl = document.getElementById('tglAbsen').value;
    mulai = document.getElementById('jam_mulai_pop').value;
    selesai = document.getElementById('jam_selesai_pop').value;
    if (document.getElementById('krywnId') == undefined) {
        karyawanid = document.getElementById('kar_' + no).value;
    } else {
        karyawanid = document.getElementById('krywnId').value;
    }

    param = 'mulai=' + mulai + '&tgl=' + tgl + '&no=' + no + '&karyawanid=' + karyawanid + '&selesai=' + selesai + '&proses=hitungjam';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('jumlah').value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getsessionlembur() {
    kdorg = document.getElementById('kdOrg').value;
    tgl = document.getElementById('tglAbsen').value;
    if (document.getElementById('krywnId') == undefined) {
        karyawanid = document.getElementById('kar_' + no).value;
    } else {
        karyawanid = document.getElementById('krywnId').value;
    }

    param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&karyawanid=' + karyawanid + '&proses=getsessionlembur';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deletesession(key, kdorg, tgl, karyawanid) {
    param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&key=' + key + '&karyawanid=' + karyawanid + '&proses=deletesession';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('listdatasession').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savedtall(maxRow) {
    if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	maxrow = document.getElementById('totrows').value;
    maxf = maxrow;
    savedt(0, maxrow);
}

function savedt(currRow, maxrow) {
    kdorg = document.getElementById('kdOrg').value;
    tgl = document.getElementById('tglAbsen').value;
    var detKode = kdorg + "###" + tgl;
    totRow = document.getElementById('totrows').value;

    var allData = '';
    allData += "&kar[" + currRow + "]=" + document.getElementById('kar_' + currRow).value;
    allData += "&tpLembur[" + currRow + "]=" + document.getElementById('tpLembur_' + currRow).value;
    allData += "&jamlmbr[" + currRow + "]=" + document.getElementById('jamlmbr_' + currRow).value;
    allData += "&uang_lbh[" + currRow + "]=" + document.getElementById('uang_lbh_' + currRow).value;
    allData += "&jam_mulai[" + currRow + "]=" + document.getElementById('jam_mulai_' + currRow).value;
    allData += "&jam_selesai[" + currRow + "]=" + document.getElementById('jam_selesai_' + currRow).value;
    allData += "&keterangan[" + currRow + "]=" + document.getElementById('keterangan_' + currRow).value;
    allData += "&noakun[" + currRow + "]=" + document.getElementById('noakun_' + currRow).value;
    allData += "&alokasi[" + currRow + "]=" + document.getElementById('alokasi_' + currRow).value;
    allData += "&ttljam[" + currRow + "]=" + document.getElementById('ttljam_' + currRow).value;

    param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&absnId=' + detKode + '&proses=savedt' + '&totRow=' + totRow;
    param += allData;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row_' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row_' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow >= maxrow) {
						displayList();
					} else {
						savedt(currRow, maxrow);
					}
				}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function cekLembur() { }

function bersihFormDet() {
    document.getElementById('krywnId').value = '';
    document.getElementById('krywnId').disabled = false;
    document.getElementById('tpLmbr').value = '';
    document.getElementById('uang_mkn').value = '0';
    document.getElementById('uang_trnsprt').value = '0';
    document.getElementById('uang_lbhjm').value = '0';
    document.getElementById('jam').value = '';
    document.getElementById('proses').value = "";
    document.getElementById('jam_mulai').value = "00:00";
    document.getElementById('jam_selesai').value = "00:00";
    document.getElementById('keterangan').value = "";
    document.getElementById('ttljam').value = "";
}
function delDetail(kdorg, tgl, krywn) {
    kdtmp = kdorg;
    tgltmp = tgl;
    krywnId = krywn;
    absnId = kdtmp + "###" + tgltmp;
    param = 'absnId=' + absnId + '&proses=delDetail' + '&krywnId=' + krywnId;
    tujuan = 'sdm_slave_lembur.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    loadDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if (confirm("Deleting, are you sure..?"))
        post_response_text(tujuan, param, respog);
}

function getPage() {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loadData(paged);
}

function loadData(page) {
    kdorg = document.getElementById('kdOrgCr').value;
    tgl = document.getElementById('tgl_cari').value;
    id = kdorg + "###" + tgl;

    param = 'proses=loadNewData';
    param += '&page=' + page;
    param += '&absnId=' + id;

    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('contain').innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loadDetail() {
    tgl = document.getElementById('tglAbsen').value;
    kdrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    param = 'tgl=' + tgl + '&kdOrg=' + kdrg + '&proses=loadDetail';
    //alert(param);
    tujuan = 'sdm_slave_detail_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('contentDetail').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function cariBast(num) {
    param = 'proses=loadNewData';
    param += '&page=' + num;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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
function fillField(kdorg, tgl) {
    document.getElementById('kdOrg').value = kdorg;
    document.getElementById('tglAbsen').value = tgl;
    tmp = kdorg + "###" + tgl;
    param = 'absnId=' + tmp;
    param += "&proses=createTable";
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    // Success Response
                    lockForm();
                    document.getElementById('listData').style.display = 'none';
                    document.getElementById('headher').style.display = 'block';
                    document.getElementById('detailEntry').style.display = 'block';
                    var detailDiv = document.getElementById('detailIsi');
                    detailDiv.innerHTML = con.responseText;
                    $(document).ready(function () {
                        $('.select2').select2({
                            dropdownAutoWidth: true
                        });
                    });

                    $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
                        $(this).closest(".select2-container").siblings('select:enabled').select2('open');
                    });
                    status_inputan = 1;
                    statFrm = 1;
                    showTmbl();
                    document.getElementById('tmbLheader').innerHTML = '';
                    document.getElementById('loaddetail').style.display = 'block';
                    loadDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function delData(kdorg, tgl) {
    kdtmp = kdorg;
    tgltmp = tgl;
    absnId = kdtmp + "###" + tgltmp;
    param = 'absnId=' + absnId + '&proses=delData';
    tujuan = 'sdm_slave_lembur.php';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if (confirm("Deleteing, are you sure..?"))
        post_response_text(tujuan, param, respog);
}
function delDataAll(kdorg, tgl) {
    kdtmp = kdorg;
    tgltmp = tgl;
    absnId = kdtmp + "###" + tgltmp;
    param = 'absnId=' + absnId + '&proses=delData';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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
function frm_aju() {

    if (statFrm == 0) {
        if (confirm("Done, are you sure..?")) {
            displayList();
        }
    } else if (statFrm == 1) {
        if (confirm("Done, are you sure..?")) {
            displayList();
        }
    }
}
function reset_data() {
    if (statFrm == 0) {
        if (confirm("Canceling, are you sure..?")) {
            kdorg = document.getElementById('kdOrg').value;
            tgl = document.getElementById('tglAbsen').value;
            delDataAll(kdorg, tgl);
        }
    }

}
function cariData(num) {

    kdorg = document.getElementById('kdOrgCr').value;
    tgl = document.getElementById('tgl_cari').value;
    id = kdorg + "###" + tgl;
    param = 'absnId=' + id + '&proses=cariAbsn';
    param += '&page=' + num;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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
function cariAsbn() {
    kdorg = document.getElementById('kdOrgCr').value;
    tgl = document.getElementById('tgl_cari').value;
    id = kdorg + "###" + tgl;
    param = 'absnId=' + id + '&proses=cariAbsn';
    //alert(param);
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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
function normal_number_1() {

    satu = document.getElementById('uang_mkn');
    satu.value = remove_comma(satu);
}
function normal_number_2() {
    dua = document.getElementById('uang_trnsprt');
    dua.value = remove_comma(dua);
}
function normal_number_3() {
    tiga = document.getElementById('uang_lbhjm');
    tiga.value = remove_comma(tiga);
}
function chngeFormat() {
    if (document.getElementById('uang_mkn').value != 0) {
        sat = document.getElementById('uang_mkn');
        change_number(sat);
    }
    if (document.getElementById('uang_trnsprt').value != 0) {
        dua = document.getElementById('uang_trnsprt');
        change_number(dua);
    }
    if (document.getElementById('uang_lbhjm').value != 0) {
        tiga = document.getElementById('uang_lbhjm');
        change_number(tiga);
    }
}
function getLembur(tplmbr, basisjam) {

    if ((tplmbr == '') && (basisjam == '')) {
        tipeLembur = document.getElementById('tpLmbr').value;
        param = 'tpLembur=' + tipeLembur + '&proses=getBasis';
    } else {
        tipeLembur = tplmbr;
        bsisJam = basisjam;
        param = 'tpLembur=' + tipeLembur + '&proses=getBasis' + '&basisJam=' + bsisJam;

    }
    krywnId = document.getElementById('krywnId').value;
    kdorg = document.getElementById('kdOrg').value;
    param += '&kdorg=' + kdorg;
    param += '&krywnId=' + krywnId;

    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('jam').innerHTML = con.responseText;
                    getsessionlembur();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function getUangLem() {
    basis = document.getElementById('jam').value;
    idKry = document.getElementById('krywnId').value;
    kodeOrg = document.getElementById('kdOrg').value;
    tpeLmbr = document.getElementById('tpLmbr').value;
    tanggal = document.getElementById('tglAbsen').value;
    tahun = tanggal.substr(6, 4);

    // Pisahkan tanggal berdasarkan tanda "-"
    // parts = tanggal.split("-");

    // Ambil bagian tahun dan bulan dari tanggal
    // day = parts[0];
    // month = parts[1];
    // year = parts[2];

    // formattedDate = `${year}-${month}`;
    // tahun = formattedDate;
    param = 'basisJam=' + basis + '&proses=getUang' + '&krywnId=' + idKry + '&kodeOrg=' + kodeOrg + '&tpLmbr=' + tpeLmbr + '&tahun=' + tahun + '&tanggal=' + tanggal;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('uang_lbhjm').value = con.responseText;
                    //updtjam();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getLemburulang(tplmbr, basisjam, no, maxrow) {
    kdorg = document.getElementById('kdOrg').value;
    sumber = document.getElementById('tpLembur_' + no).value;
    if ((tplmbr == '') && (basisjam == '')) {
        tipeLembur = document.getElementById('tpLembur_' + no).options[document.getElementById('tpLembur_' + no).selectedIndex].value;
        param = 'tpLembur=' + tipeLembur + '&proses=getBasis';
    } else {
        tipeLembur = tplmbr;
        bsisJam = basisjam;
        param = 'tpLembur=' + tipeLembur + '&proses=getBasis' + '&basisJam=' + bsisJam;
    }
    param += "&kdorg=" + kdorg;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('jamlmbr_' + no).innerHTML = con.responseText;
                    no += 1;
                    if ((no > maxrow) || (maxrow == undefined)) { }
                    else {
                        document.getElementById('tpLembur_' + no).value = sumber;
                        getLemburulang(tplmbr, basisjam, no, maxrow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getUangLemulang(no) {
    basis = document.getElementById('jamlmbr_' + no).value;
    idKry = document.getElementById('kar_' + no).value;
    kodeOrg = document.getElementById('kdOrg').value;
    tpeLmbr = document.getElementById('tpLembur_' + no).value;
    tanggal = document.getElementById('tglAbsen').value;
    tahun = tanggal.substr(6, 4);

    // // Pisahkan tanggal berdasarkan tanda "-"
    // parts = tanggal.split("-");

    // // Ambil bagian tahun dan bulan dari tanggal
    // day = parts[0];
    // month = parts[1];
    // year = parts[2];

    // formattedDate = `${year}-${month}`;
    // tahun = formattedDate;
    param = 'basisJam=' + basis + '&proses=getUang' + '&krywnId=' + idKry + '&kodeOrg=' + kodeOrg + '&tpLmbr=' + tpeLmbr + '&tahun=' + tahun + '&tanggal=' + tanggal;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('uang_lbh_' + no).value = con.responseText;
                    //updtjamulang(no);
                    ttljam = getValue('ttljam_' + no);
                    if (ttljam == '') { ttljam = 0; }
                    if (basis > ttljam) {
                        document.getElementById('jam_mulai_' + no).style.backgroundColor = "yellow";
                        document.getElementById('jam_selesai_' + no).style.backgroundColor = "yellow";
                    } else {
                        document.getElementById('jam_mulai_' + no).style.backgroundColor = "";
                        document.getElementById('jam_selesai_' + no).style.backgroundColor = "";
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function updtjamulang(no) {

    Jam = document.getElementById('jamlmbr_' + no).value;
    jammulai = document.getElementById('jam_mulai_' + no).value;

    param = 'Jam=' + Jam + '&proses=updtjam';
    param += "&jammulai=" + jammulai;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //alertify.alert(con.responseText);
                    document.getElementById('jam_selesai_' + no).value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function searchspl(title, content, ev) {
    width = '';
    height = '';
    showDialog4(title, content, width, height, ev);
    getformspl();
}

function getformspl() {
    kdorg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    tglabsen = document.getElementById('tglAbsen').value;
    param = '';
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan + '?' + 'proses=getformspl&kdorg=' + kdorg + '&tglabsen=' + tglabsen, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('formPencariandata').innerHTML = con.responseText;
                    findspl();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findspl() {
    kdorg = trim(document.getElementById('kdOrg').value);
    tgl = trim(document.getElementById('tglAbsen').value);
    kdOrgspl = trim(document.getElementById('kdOrgspl').value);
    tglspl = trim(document.getElementById('tglspl').value);

    id = kdorg + "###" + tgl;
    param = 'absnId=' + id + '&kdOrgspl=' + kdorg + '&tglspl=' + tgl;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan + '?' + 'proses=getdataspl', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('container2').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function checkAll() {
    drt = document.getElementById('checkall');
    if (drt.checked == true) {
        chk = true;
    } else {
        chk = false;
    }
    var tbl = document.getElementById("splTbody");
    var row = tbl.rows.length;
    row = row - 1;
    for (i = 0; i <= row; i++) {
        document.getElementById('checkdata_' + i).checked = chk;
    }
}

function addtodetail(kdorg, tgl) {
    totRow = document.getElementById('totadd').value;
    var allData = '';

    var hitungdata = 0;
    for (dwc = 0; dwc < totRow; dwc++) {
        if (document.getElementById('checkdata_' + dwc).checked) {
            allData += "&notransp[]=" + document.getElementById('notransp_' + dwc).innerHTML;
            allData += "&kar[]=" + document.getElementById('karspl_' + dwc).value;
            allData += "&tpLembur[]=" + document.getElementById('tpLemburspl_' + dwc).value;
            allData += "&jamlmbr[]=" + document.getElementById('jamlmbrspl_' + dwc).innerHTML;
            allData += "&uang_lbh[]=" + document.getElementById('uangspl_' + dwc).innerHTML;
            allData += "&jam_mulai[]=" + document.getElementById('mulaispl_' + dwc).innerHTML;
            allData += "&jam_selesai[]=" + document.getElementById('selesaispl_' + dwc).innerHTML;
            allData += "&keterangan[]=" + document.getElementById('ketspl_' + dwc).innerHTML;
            hitungdata += 1;
        }
    }

    id = kdorg + "###" + tgl;
    param = 'absnId=' + id + '&proses=savedt' + '&totRow=' + hitungdata;
    param += allData;
    // alert(param);return;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    closeDialog4();
                    displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showupload(notransaksi, kodeorg, tanggal) {
    param = 'proses=showupload&notransaksi=' + notransaksi;
    param += "&kodeorg=" + kodeorg;
    param += "&tanggal=" + tanggal;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alertify.alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.popup("Upload", con.responseText).set({ 'resizable': true, 'overflow': false, 'maximizable': false }).resizeTo('400px', '400px');

                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// fungsi untuk progress bar
function progressHandler(event) {
    document.getElementById("progressBar").style.display = "block";
    document.getElementById("loaded_n_total").innerHTML = "Uploaded " + numberFormat(Math.round(event.loaded / 1024)) + " KB of " + numberFormat(Math.round(event.total / 1024)) + " KB";
    var percent = (event.loaded / event.total) * 100;
    document.getElementById("progressBar").value = Math.round(percent);
    document.getElementById("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
    document.getElementById("progressBar").style.display = "none";
    document.getElementById("status").innerHTML = event.target.responseText;
    document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
    document.getElementById("status").innerHTML = "Upload Failed";
}
function abortHandler(event) {
    document.getElementById("status").innerHTML = "Upload Aborted";
}

function submitfile(notransaksi) {
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("fileupload", getValue('upload'));
    formdata.append("file", file);
    formdata.append("notransaksi", notransaksi);
    if (getValue('upload') == "") {
        alertify.alert("Upload file has been empty.");
        return false;
    }
    if (notransaksi == '') {
        alertify.alert("Nomor transaksi tidak ditemukan.");
        return false;
    }

    var con = createXMLHttpRequest();
    document.getElementById('btnsubmit').style.display = "none";
    //tambahan progress bar
    con.upload.addEventListener("progress", progressHandler, false);
    con.addEventListener("load", completeHandler, false);
    con.addEventListener("error", errorHandler, false);
    con.addEventListener("abort", abortHandler, false);
    //tambahan progress bar -end-
    con.open("POST", "sdm_slave_lembur.php?proses=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alertify.alert(con.responseText);
                } else {
                    //=== Success Response
                    alertify.alert('Uploaded Success.');
                    document.getElementById('btnsubmit').style.display = "";
                    document.getElementById("upload").value = "";
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
    param = 'proses=loadfiles&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alertify.alert(con.responseText);
                } else {
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('loadfilesdetail') !== null) {
                        document.getElementById('loadfilesdetail').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(notransaksi, namafile) {
    param = "proses=deletefile";
    param += "&notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alertify.alert(con.responseText);
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


function formupload() {
    width = '';
    height = '';
    content = "<fieldset style=\"width:97%;\"><div id=contviewupload style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev);
}
function viewfile(idfile, sumber) {
    //formupload();
    param = 'proses=viewfile&idfile=' + idfile;
    tujuan = 'sdm_slave_lembur.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alertify.alert(con.responseText);
                } else {
                    //document.getElementById('contviewupload').innerHTML = con.responseText;
                    alertify.popup2("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('80%', '70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}