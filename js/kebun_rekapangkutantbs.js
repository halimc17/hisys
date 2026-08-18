function getnopol() {
  spk = document.getElementById("spk").value;

  param = "method=getnopol";
  param += "&spk=" + spk;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("tempnopol").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnospk() {
  kodeorg = document.getElementById("kodeorg").value;
  periode = document.getElementById("periode").value;
  periodebyr = document.getElementById("periodebyr").value;

  param = "method=getnospk";
  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&periodebyr=" + periodebyr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("spk").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getdetailjurnal(notransaksi, nobapp, kodeorg, tanggal) {
  param =
    "method=getdetailjurnal&notransaksi=" +
    notransaksi +
    "&kodeorg=" +
    kodeorg +
    "&tanggal=" +
    tanggal +
    "&nobapp=" +
    nobapp;

  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function formpostingDataAll(
  nopengajuan,
  notransaksi,
  nobapp,
  kodeorg,
  tanggal,
  termin,
  numRow,
) {
  param =
    "method=formpostingDataAll&notransaksi=" +
    notransaksi +
    "&nopengajuan=" +
    nopengajuan +
    "&kodeorg=" +
    kodeorg +
    "&tanggal=" +
    tanggal +
    "&termin=" +
    termin +
    "&nobapp=" +
    nobapp;
  // width = '';
  // height = '';
  // ev = 'event';
  // content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
  // title = "Posting All";
  // showDialog2(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic2').style.top = pos[1] + 'px';
  // // document.getElementById('dynamic2').style.right = (80) + 'px';
  // document.getElementById('dynamic2').style.display = '';

  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('contviewx').innerHTML = con.responseText;
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingDataAll(maxRow) {
  if (maxRow == "" || maxRow == 0) {
    alertify.alert("Data tidak ditemukan, proses dibatalkan !");
    return;
  }
  if (confirm("Posting semua ???")) {
    savepostingDataAll(1, maxRow);
  }
}
function savepostingDataAll(currRow, maxRow) {
  keg = document.getElementById("kegpost" + currRow).innerHTML;
  blok = document.getElementById("blokpost" + currRow).innerHTML;
  nobapppost = document.getElementById("nobapppost" + currRow).innerHTML;
  tanggal = document.getElementById("tglpost" + currRow).innerHTML;
  jumlahrealisasi = document.getElementById("realpost" + currRow).innerHTML;
  termin = document.getElementById("termin" + currRow).innerHTML;
  notransaksi = document.getElementById("notrpost" + currRow).value;
  kodeorg = document.getElementById("kdorgpost" + currRow).value;
  koderekanan = document.getElementById("kdrekpost" + currRow).value;
  nobapp = document.getElementById("nobapppost" + currRow).innerHTML;

  ev = "event";

  var segment = "0000000001";
  var kodeblok = blok;
  var unit = kodeorg;

  var param =
    "kodeorg=" + kodeorg + "&koderekanan=" + koderekanan + "&termin=" + termin;
  param +=
    "&notransaksi=" +
    notransaksi +
    "&kodeblok=" +
    blok +
    "&kodesegment=" +
    segment +
    "&kodekegiatan=" +
    keg;

  param += "&nobapp=" + nobapp;
  param += "&blokalokasi=" + kodeblok;
  param += "&nobapppost=" + nobapppost;
  param += "&tanggal=" + tanggal;
  param += "&jumlahrealisasi=" + remove_comma_var(jumlahrealisasi);
  param +=
    "&hasilkerjarealisasi=" +
    remove_comma_var(
      document.getElementById("hslkerjapost" + currRow).innerHTML,
    );
  tujuan = "log_slave_realisasispk_posting.php";
  post_response_text(tujuan, param, respog);
  document.getElementById("tr_" + currRow).style.backgroundColor = "cyan";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          document.getElementById("tr_" + currRow).style.backgroundColor =
            "red";
          //unlockScreen();
        } else {
          if (currRow != undefined) {
            document.getElementById("tr_" + currRow).style.backgroundColor =
              "cyan";
          }
          currRow += 1;
          if (currRow > maxRow || maxRow == undefined) {
            //tipeview='viewhtml';
            //viewdetail(notransaksi,unit,tipeview,ev)
            // closeDialog();
            // closeDialog2();
            alertify.popup().destroy();
            alertify.popup2().destroy();
            // getpage();
            alertify.alert("Done");
            loaddata(0);
          } else {
            savepostingDataAll(currRow, maxRow);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getNonNegativeNumber(value) {
  value = remove_comma_var(value);
  value = parseFloat(value);
  if (isNaN(value) || value < 0) {
    return 0;
  }
  return value;
}

function gettotal(currRow) {
  rp_muat = getNonNegativeNumber(
    document.getElementById("rp_muat_" + currRow).value,
  );
  rp_muat2 = getNonNegativeNumber(
    document.getElementById("rp_muat2_" + currRow).value,
  );
  rp_muat3 = getNonNegativeNumber(
    document.getElementById("rp_muat3_" + currRow).value,
  );
  rp_muat4 = getNonNegativeNumber(
    document.getElementById("rp_muat4_" + currRow).value,
  );
  rp_muat5 = getNonNegativeNumber(
    document.getElementById("rp_muat5_" + currRow).value,
  );
  rp_muat6 = getNonNegativeNumber(
    document.getElementById("rp_muat6_" + currRow).value,
  );
  rp_muat7 = getNonNegativeNumber(
    document.getElementById("rp_muat7_" + currRow).value,
  );
  rp_angkut = getNonNegativeNumber(
    document.getElementById("rp_angkut_" + currRow).value,
  );
  rp_angkut2 = getNonNegativeNumber(
    document.getElementById("rp_angkut2_" + currRow).value,
  );
  rp_angkut3 = getNonNegativeNumber(
    document.getElementById("rp_angkut3_" + currRow).value,
  );
  rp_angkut4 = getNonNegativeNumber(
    document.getElementById("rp_angkut4_" + currRow).value,
  );
  rp_angkut5 = getNonNegativeNumber(
    document.getElementById("rp_angkut5_" + currRow).value,
  );
  rp_angkut6 = getNonNegativeNumber(
    document.getElementById("rp_angkut6_" + currRow).value,
  );
  rp_angkut7 = getNonNegativeNumber(
    document.getElementById("rp_angkut7_" + currRow).value,
  );
  addrp_muat = getNonNegativeNumber(
    document.getElementById("addrp_muat_" + currRow).value,
  );
  addrp_angkut = getNonNegativeNumber(
    document.getElementById("addrp_angkut_" + currRow).value,
  );

  totalMuat =
    rp_muat +
    rp_muat2 +
    rp_muat3 +
    rp_muat4 +
    rp_muat5 +
    rp_muat6 +
    rp_muat7 +
    addrp_muat;

  totalAngkut =
    rp_angkut +
    rp_angkut2 +
    rp_angkut3 +
    rp_angkut4 +
    rp_angkut5 +
    rp_angkut6 +
    rp_angkut7 +
    addrp_angkut;

  totalSebelumPotongan = totalMuat + totalAngkut;

  potonganInput = document.getElementById("potonganrp_" + currRow);
  potonganRaw = remove_comma_var(potonganInput.value);
  potonganAngka = parseFloat(potonganRaw);

  if (isNaN(potonganAngka) || potonganAngka < 0) {
    potonganrp = 0;
    potonganInput.value = 0;
  } else {
    potonganrp = potonganAngka;
  }

  // Potongan disimpan pada baris angkut, jadi nilainya tidak boleh melebihi
  // total angkut agar rupiah angkut maupun total netto tidak menjadi minus.
  if (potonganrp > totalAngkut) {
    potonganrp = totalAngkut;
    potonganInput.value = numberFormat(potonganrp, 2);
  }

  netto = Math.max(0, totalSebelumPotongan - potonganrp);
  document.getElementById("ttlrp_" + currRow).value = numberFormat(netto, 2);
}

function deletedetail(kodeorg, periode, nospb) {
  param =
    "method=deletedetail" +
    "&kodeorg=" +
    kodeorg +
    "&periode=" +
    periode +
    "&nospb=" +
    nospb;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  if (confirm(" Anda yakin ")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          detail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddatadetail() {
  kodeorg = document.getElementById("kodeorg").value;
  periode = document.getElementById("periode").value;
  periodebyr = document.getElementById("periodebyr").value;
  spk = document.getElementById("spk").value;

  param = "method=loaddatadetail";
  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&periodebyr=" + periodebyr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("loaddatadetail").innerHTML =
            con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveAll(maxRow) {
  if (maxRow == "" || maxRow == 0) {
    alertify.alert("Data tidak ditemukan, proses dibatalkan !");
    return;
  }
  if (confirm("Simpan semua ???")) {
    if (confirm("Hanya data yg memiliki nilai rupiah yg akan disimpan.")) {
      savedetail(1, maxRow);
    }
  }
}
function savedetail(currRow, maxRow) {
  param = "";
  method = trim(document.getElementById("method").value);
  kodeorg = trim(document.getElementById("kodeorg").value);
  periode = trim(document.getElementById("periode").value);
  spk = trim(document.getElementById("spk").value);
  divisi = trim(document.getElementById("divisi").value);
  kgwb = trim(document.getElementById("kgwb").value);
  tanggal = trim(document.getElementById("tanggal").value);
  periodebyr = trim(document.getElementById("periodebyr").value);
  tglmulai = trim(document.getElementById("tglmulai").value);
  tglselesai = trim(document.getElementById("tglselesai").value);

  nospb = trim(document.getElementById("nospb_" + currRow).innerHTML);
  kegmuat = trim(document.getElementById("kegmuat_" + currRow).value);
  kegangkut = trim(document.getElementById("kegangkut_" + currRow).value);
  blok = trim(document.getElementById("blok_" + currRow).value);
  tujuan = trim(document.getElementById("tujuan_" + currRow).value);
  kgwbdet = trim(document.getElementById("kgwb_" + currRow).value);
  rp_muat = trim(document.getElementById("rp_muat_" + currRow).value);
  rp_muat2 = trim(document.getElementById("rp_muat2_" + currRow).value);
  rp_muat3 = trim(document.getElementById("rp_muat3_" + currRow).value);
  rp_muat4 = trim(document.getElementById("rp_muat4_" + currRow).value);
  rp_muat5 = trim(document.getElementById("rp_muat5_" + currRow).value);
  rp_muat6 = trim(document.getElementById("rp_muat6_" + currRow).value);
  rp_muat7 = trim(document.getElementById("rp_muat7_" + currRow).value);
  rp_angkut = trim(document.getElementById("rp_angkut_" + currRow).value);
  rp_angkut2 = trim(document.getElementById("rp_angkut2_" + currRow).value);
  rp_angkut3 = trim(document.getElementById("rp_angkut3_" + currRow).value);
  rp_angkut4 = trim(document.getElementById("rp_angkut4_" + currRow).value);
  rp_angkut5 = trim(document.getElementById("rp_angkut5_" + currRow).value);
  rp_angkut6 = trim(document.getElementById("rp_angkut6_" + currRow).value);
  rp_angkut7 = trim(document.getElementById("rp_angkut7_" + currRow).value);

  addrp_muat = trim(document.getElementById("addrp_muat_" + currRow).value);
  addrp_angkut = trim(document.getElementById("addrp_angkut_" + currRow).value);

  potonganrp = trim(document.getElementById("potonganrp_" + currRow).value);

  kgwbpks = trim(document.getElementById("kgwbpks_" + currRow).value);
  kgbrd = trim(document.getElementById("kgbrd_" + currRow).value);
  pkstujuan = document.getElementById("pkstujuan_" + currRow).value;
  jnskend = document.getElementById("jnskend_" + currRow).value;

  if (tujuan == "") {
    alertify.alert("Jenis tidak boleh kosong");
    return;
  }

  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&nospb=" + nospb;
  param += "&divisi=" + divisi;
  param += "&kegmuat=" + kegmuat;
  param += "&kegangkut=" + kegangkut;
  param += "&blok=" + blok;
  param += "&tujuan=" + tujuan;
  param += "&kgwbdet=" + remove_comma_var(kgwbdet);
  param += "&addrp_muat=" + remove_comma_var(addrp_muat);
  param += "&rp_muat=" + remove_comma_var(rp_muat);
  param += "&rp_muat2=" + remove_comma_var(rp_muat2);
  param += "&rp_muat3=" + remove_comma_var(rp_muat3);
  param += "&rp_muat4=" + remove_comma_var(rp_muat4);
  param += "&rp_muat5=" + remove_comma_var(rp_muat5);
  param += "&rp_muat6=" + remove_comma_var(rp_muat6);
  param += "&rp_muat7=" + remove_comma_var(rp_muat7);
  param += "&rp_angkut=" + remove_comma_var(rp_angkut);
  param += "&rp_angkut2=" + remove_comma_var(rp_angkut2);
  param += "&rp_angkut3=" + remove_comma_var(rp_angkut3);
  param += "&rp_angkut4=" + remove_comma_var(rp_angkut4);
  param += "&rp_angkut5=" + remove_comma_var(rp_angkut5);
  param += "&rp_angkut6=" + remove_comma_var(rp_angkut6);
  param += "&rp_angkut7=" + remove_comma_var(rp_angkut7);
  param += "&addrp_angkut=" + remove_comma_var(addrp_angkut);
  param += "&kgwb=" + remove_comma_var(kgwb);
  param += "&tanggal=" + tanggal;
  param += "&tglmulai=" + tglmulai;
  param += "&tglselesai=" + tglselesai;
  param += "&periodebyr=" + periodebyr;
  param += "&pkstujuan=" + pkstujuan;
  param += "&jnskend=" + jnskend;
  param += "&kgwbpks=" + remove_comma_var(kgwbpks);
  param += "&kgbrd=" + remove_comma_var(kgbrd);
  param += "&potonganrp=" + remove_comma_var(potonganrp);
  param += "&method=" + method;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          document.getElementById("tr_" + currRow).style.backgroundColor =
            "red";
          //unlockScreen();
        } else {
          if (currRow != undefined) {
            document.getElementById("tr_" + currRow).style.backgroundColor =
              "cyan";
          }
          document.getElementById("tr_" + currRow).style.display = "none";
          currRow += 1;
          if (currRow > maxRow || maxRow == undefined) {
            alertify.alert("Done");
            loaddatadetail();
            //detail();
          } else {
            savedetail(currRow, maxRow);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detail() {
  kodeorg = document.getElementById("kodeorg").value;
  periode = document.getElementById("periode").value;
  periodebyr = document.getElementById("periodebyr").value;
  tglmulai = document.getElementById("tglmulai").value;
  tglselesai = document.getElementById("tglselesai").value;
  spk = document.getElementById("spk").value;
  tgl = document.getElementById("tgl").value;
  if (kodeorg == "" || periode == "" || spk == "" || periodebyr == "") {
    alertify.alert(
      "Kode Organisasi, Periode Bulan, Periode Bayar dan SPK Wajib diisi !",
    );
    return;
  }
  document.getElementById("tomboldetail").disabled = true;
  document.getElementById("kodeorg").disabled = true;
  document.getElementById("spk").disabled = true;
  document.getElementById("periode").disabled = true;
  document.getElementById("tgl").disabled = true;
  document.getElementById("periodebyr").disabled = true;
  document.getElementById("tglmulai").disabled = true;
  document.getElementById("tglselesai").disabled = true;
  param = "method=detail";
  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&tgl=" + tgl;
  param += "&periodebyr=" + periodebyr;
  param += "&tglmulai=" + tglmulai;
  param += "&tglselesai=" + tglselesai;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detail").style.display = "block";
          document.getElementById("detail").innerHTML = con.responseText;
          leftFixedTable();
          loaddatadetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getjnskendall(no) {
  j = document.getElementsByName("jnskendcr[]");
  e = document.getElementById("jnskendcr_" + no).value;
  for (i = no; i <= j.length; i++) {
    document.getElementById("jnskendcr_" + i).value = e;
  }
}

function getdetailspb(no) {
  kodeorg = document.getElementById("kodeorg").value;
  tglheader = document.getElementById("tgl").value;
  periode = document.getElementById("periode").value;
  periodebyr = document.getElementById("periodebyr").value;
  spk = document.getElementById("spk").value;

  param = "method=getdetailspb";
  t = document.getElementsByName("tiket[]");
  n = document.getElementsByName("nospb[]");
  j = document.getElementsByName("jnskendcr[]");
  e = document.getElementsByName("click[]");
  if (e.length == 0) {
    alertify.alert("Silahkan checked nospb/tiket terlebih dahulu.");
    return;
  }

  for (i = 0; i < e.length; i++) {
    if (e[i].checked == true) {
      param += "&tiket[" + i + "]=" + t[i].innerHTML;
      param += "&nospb[" + i + "]=" + n[i].innerHTML;
      param += "&jnskend[" + i + "]=" + j[i].value;
      param += "&jeniskend[" + t[i].innerHTML + "]=" + j[i].value;

      if (j[i].value == "") {
        alertify.alert("Jenis kendaraan tidak boleh kosong.");
        return;
      }
    }
  }

  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&tglheader=" + tglheader;
  param += "&periodebyr=" + periodebyr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isi = con.responseText.split("##");
          // document.getElementById('notiket').value=trim(isi[0]);
          // document.getElementById('sopir').value=trim(isi[1]);
          // document.getElementById('divisi').value=trim(isi[2]);
          // document.getElementById('tanggal').value=trim(isi[3]);
          // document.getElementById('nopol').value=trim(isi[4]);
          // document.getElementById('jjg').value=trim(isi[5]);
          // document.getElementById('kgwb').value=trim(isi[6]);
          // document.getElementById('kgwbkebun').value=trim(isi[7]);
          document.getElementById("inputharga").innerHTML = trim(isi[7]);
          //closeDialog();
          alertify.popup().destroy();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getdetailspbold(jenisangkt) {
  kodeorg = document.getElementById("kodeorg").value;
  tglheader = document.getElementById("tgl").value;
  periode = document.getElementById("periode").value;
  periodebyr = document.getElementById("periodebyr").value;
  spk = document.getElementById("spk").value;
  nospb = document.getElementById("nospb").value;
  jnskend = document.getElementById("jeniskend").value;

  param = "method=getdetailspb";
  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&tglheader=" + tglheader;
  param += "&nospb=" + nospb;
  param += "&jnskend=" + jnskend;
  param += "&periodebyr=" + periodebyr;
  param += "&jenisangkt=" + jenisangkt;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isi = con.responseText.split("##");
          document.getElementById("notiket").value = trim(isi[0]);
          document.getElementById("sopir").value = trim(isi[1]);
          document.getElementById("divisi").value = trim(isi[2]);
          document.getElementById("tanggal").value = trim(isi[3]);
          document.getElementById("nopol").value = trim(isi[4]);
          document.getElementById("jjg").value = trim(isi[5]);
          document.getElementById("kgwb").value = trim(isi[6]);
          document.getElementById("inputharga").innerHTML = trim(isi[7]);
          if (jenisangkt != undefined) {
            getharga("1", "", "");
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function hitungrupiah(tipe, i) {
  kgwbpksInput = document.getElementById("kgwbpks_" + i);
  kgbrdInput = document.getElementById("kgbrd_" + i);
  kgwbInput = document.getElementById("kgwb_" + i);

  kgwbpks = getNonNegativeNumber(kgwbpksInput.value);

  kgbrdRaw = remove_comma_var(kgbrdInput.value);
  kgbrdAngka = parseFloat(kgbrdRaw);
  if (isNaN(kgbrdAngka) || kgbrdAngka < 0) {
    kgbrd = 0;
    kgbrdInput.value = 0;
  } else {
    kgbrd = kgbrdAngka;
  }

  // Brondolan tidak boleh membuat KG hasil perhitungan menjadi minus.
  if (kgbrd > kgwbpks) {
    kgbrd = kgwbpks;
    kgbrdInput.value = kgbrd;
  }

  if (tipe == "kg") {
    kgwbRaw = remove_comma_var(kgwbInput.value);
    kgwbAngka = parseFloat(kgwbRaw);
    if (isNaN(kgwbAngka) || kgwbAngka < 0) {
      kgwb = 0;
      kgwbInput.value = 0;
    } else {
      kgwb = kgwbAngka;
    }
  } else {
    kgwb = Math.max(0, kgwbpks - kgbrd);
    kgwbInput.value = kgwb;
  }

  if (kgwb > kgwbpks) {
    alertify.alert("Kg tidak boleh lebih besar dari KG PKS.");
    kgwb = kgwbpks;
    kgwbInput.value = kgwbpks;
    kgbrd = 0;
    kgbrdInput.value = 0;
  }

  rpmuat = getNonNegativeNumber(
    document.getElementById("harga_muat_" + i).value,
  );
  rpmuat2 = getNonNegativeNumber(
    document.getElementById("harga_muat2_" + i).value,
  );
  rpmuat3 = getNonNegativeNumber(
    document.getElementById("harga_muat3_" + i).value,
  );
  rpmuat4 = getNonNegativeNumber(
    document.getElementById("harga_muat4_" + i).value,
  );
  rpmuat5 = getNonNegativeNumber(
    document.getElementById("harga_muat5_" + i).value,
  );
  rpmuat6 = getNonNegativeNumber(
    document.getElementById("harga_muat6_" + i).value,
  );
  rpmuat7 = getNonNegativeNumber(
    document.getElementById("harga_muat7_" + i).value,
  );
  rpangkut = getNonNegativeNumber(
    document.getElementById("harga_angkut_" + i).value,
  );
  rpangkut2 = getNonNegativeNumber(
    document.getElementById("harga_angkut2_" + i).value,
  );
  rpangkut3 = getNonNegativeNumber(
    document.getElementById("harga_angkut3_" + i).value,
  );
  rpangkut4 = getNonNegativeNumber(
    document.getElementById("harga_angkut4_" + i).value,
  );
  rpangkut5 = getNonNegativeNumber(
    document.getElementById("harga_angkut5_" + i).value,
  );
  rpangkut6 = getNonNegativeNumber(
    document.getElementById("harga_angkut6_" + i).value,
  );
  rpangkut7 = getNonNegativeNumber(
    document.getElementById("harga_angkut7_" + i).value,
  );

  // Harga tambahan per KG dari case getharga juga diamankan agar tidak negatif.
  addrpmuat = getNonNegativeNumber(
    document.getElementById("addharga_muat_" + i).value,
  );
  addrpangkut = getNonNegativeNumber(
    document.getElementById("addharga_angkut_" + i).value,
  );

  totrpmuat = Math.max(0, rpmuat * kgwb);
  totrpmuat2 = Math.max(0, rpmuat2 * kgwb);
  totrpmuat3 = Math.max(0, rpmuat3 * kgwb);
  totrpmuat4 = Math.max(0, rpmuat4 * kgwb);
  totrpmuat5 = Math.max(0, rpmuat5 * kgwb);
  totrpmuat6 = Math.max(0, rpmuat6 * kgwb);
  totrpmuat7 = Math.max(0, rpmuat7 * kgwb);
  totrpangkut = Math.max(0, rpangkut * kgwb);
  totrpangkut2 = Math.max(0, rpangkut2 * kgwb);
  totrpangkut3 = Math.max(0, rpangkut3 * kgwb);
  totrpangkut4 = Math.max(0, rpangkut4 * kgwb);
  totrpangkut5 = Math.max(0, rpangkut5 * kgwb);
  totrpangkut6 = Math.max(0, rpangkut6 * kgwb);
  totrpangkut7 = Math.max(0, rpangkut7 * kgwb);

  // Total biaya tambahan mengikuti KG terbaru dan tidak boleh negatif.
  addtotrpmuat = Math.max(0, addrpmuat * kgwb);
  addtotrpangkut = Math.max(0, addrpangkut * kgwb);

  document.getElementById("rp_muat_" + i).value = totrpmuat;
  document.getElementById("rp_muat2_" + i).value = totrpmuat2;
  document.getElementById("rp_muat3_" + i).value = totrpmuat3;
  document.getElementById("rp_muat4_" + i).value = totrpmuat4;
  document.getElementById("rp_muat5_" + i).value = totrpmuat5;
  document.getElementById("rp_muat6_" + i).value = totrpmuat6;
  document.getElementById("rp_muat7_" + i).value = totrpmuat7;
  document.getElementById("rp_angkut_" + i).value = totrpangkut;
  document.getElementById("rp_angkut2_" + i).value = totrpangkut2;
  document.getElementById("rp_angkut3_" + i).value = totrpangkut3;
  document.getElementById("rp_angkut4_" + i).value = totrpangkut4;
  document.getElementById("rp_angkut5_" + i).value = totrpangkut5;
  document.getElementById("rp_angkut6_" + i).value = totrpangkut6;
  document.getElementById("rp_angkut7_" + i).value = totrpangkut7;
  document.getElementById("addrp_muat_" + i).value = addtotrpmuat;
  document.getElementById("addrp_angkut_" + i).value = addtotrpangkut;

  // Gunakan gettotal agar potongan tetap ikut dihitung dan hasil netto tidak minus.
  gettotal(i);
}

function getharga(no, maxrow, nospbold) {
  spk = document.getElementById("spk").value;
  blok = document.getElementById("blok_" + no).value;
  nospb = trim(document.getElementById("nospb_" + no).innerHTML);
  tujuanx = document.getElementById("tujuan_" + no).value;
  pkstujuan = document.getElementById("pkstujuan_" + no).value;
  jnskend = document.getElementById("jnskend_" + no).value;
  ttlrow = document.getElementById("jumlahrow").value;
  jlspb = document.getElementsByName(nospbold + "[]");
  jlhspb = jlspb.length;
  if (maxrow == "" || maxrow == undefined) {
    maxrow = ttlrow;
  }

  nox = parseFloat(no);
  n = parseFloat(no) + 1;
  if (n <= ttlrow) {
    // for (i = n; i <= ttlrow; i++) {
    // document.getElementById('tujuan_' + i).value = tujuanx;
    // document.getElementById('jnskend_' + i).value = jnskend;
    // }

    for (i = no; i <= jlhspb; i++) {
      if (jlspb[i] != undefined && nospb == nospbold) {
        jlspb[i].value = tujuanx;
      }
    }
  }

  param = "method=getharga";
  param += "&blok=" + blok;
  param += "&spk=" + spk;
  param += "&nospb=" + nospb;
  param += "&tujuan=" + tujuanx;
  param += "&pkstujuan=" + pkstujuan;
  param += "&jnskend=" + jnskend;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isi = con.responseText.split("##");
          rpmuat = getNonNegativeNumber(trim(isi[0]));
          rpangkut = getNonNegativeNumber(trim(isi[1]));
          kegmuat = trim(isi[2]);
          kegangkut = trim(isi[3]);
          addrpmuat = getNonNegativeNumber(trim(isi[4]));
          addrpangkut = getNonNegativeNumber(trim(isi[5]));

          if (nospb == nospbold) {
            if (tujuanx == "tphpks1") {
              document.getElementById("harga_muat_" + no).value = numberFormat(
                rpmuat,
                2,
              );
              document.getElementById("harga_muat2_" + no).value = "";
              document.getElementById("harga_muat3_" + no).value = "";
              document.getElementById("harga_muat4_" + no).value = "";
              document.getElementById("harga_muat5_" + no).value = "";
              document.getElementById("harga_muat6_" + no).value = "";
              document.getElementById("harga_muat7_" + no).value = "";
              document.getElementById("addharga_muat_" + no).value = addrpmuat;
              document.getElementById("harga_angkut_" + no).value =
                numberFormat(rpangkut, 2);
              document.getElementById("harga_angkut2_" + no).value = "";
              document.getElementById("harga_angkut3_" + no).value = "";
              document.getElementById("harga_angkut4_" + no).value = "";
              document.getElementById("harga_angkut5_" + no).value = "";
              document.getElementById("harga_angkut6_" + no).value = "";
              document.getElementById("harga_angkut7_" + no).value = "";
              document.getElementById("addharga_angkut_" + no).value =
                addrpangkut;
              document.getElementById("kegmuat_" + no).value = kegmuat;
              document.getElementById("kegangkut_" + no).value = kegangkut;

              kgwb = getNonNegativeNumber(
                document.getElementById("kgwb_" + no).value,
              );

              totrpmuat = parseFloat(rpmuat) * parseFloat(kgwb);
              totrpangkut = parseFloat(rpangkut) * parseFloat(kgwb);

              addtotrpmuat = parseFloat(addrpmuat) * parseFloat(kgwb);
              addtotrpangkut = parseFloat(addrpangkut) * parseFloat(kgwb);

              if (isNaN(totrpmuat)) {
                totrpmuat = 0;
              }
              if (isNaN(addtotrpmuat)) {
                addtotrpmuat = 0;
              }
              if (isNaN(totrpangkut)) {
                totrpangkut = 0;
              }
              if (isNaN(addtotrpangkut)) {
                addtotrpangkut = 0;
              }

              document.getElementById("rp_muat_" + no).value = totrpmuat;
              document.getElementById("rp_muat2_" + no).value = "";
              document.getElementById("rp_muat3_" + no).value = "";
              document.getElementById("rp_muat4_" + no).value = "";
              document.getElementById("rp_muat5_" + no).value = "";
              document.getElementById("rp_muat6_" + no).value = "";
              document.getElementById("rp_muat7_" + no).value = "";
              document.getElementById("addrp_muat_" + no).value = addtotrpmuat;
              document.getElementById("rp_angkut_" + no).value = numberFormat(
                // // parseFloat(totrpangkut) + parseFloat(totrpmuat),
                parseFloat(totrpangkut),
                2,
              );
              document.getElementById("rp_angkut2_" + no).value = "";
              document.getElementById("rp_angkut3_" + no).value = "";
              document.getElementById("rp_angkut4_" + no).value = "";
              document.getElementById("rp_angkut5_" + no).value = "";
              document.getElementById("rp_angkut6_" + no).value = "";
              document.getElementById("rp_angkut7_" + no).value = "";
              document.getElementById("addrp_angkut_" + no).value =
                addtotrpangkut;
              document.getElementById("ttlrp_" + no).value = numberFormat(
                totrpmuat + totrpangkut + addtotrpangkut + addtotrpmuat,
                2,
              );
            }
            if (tujuanx == "tphpks2") {
              document.getElementById("harga_muat_" + no).value = "";
              document.getElementById("harga_muat2_" + no).value = numberFormat(
                rpmuat,
                1,
              );
              document.getElementById("harga_muat3_" + no).value = "";
              document.getElementById("harga_muat4_" + no).value = "";
              document.getElementById("harga_muat5_" + no).value = "";
              document.getElementById("harga_muat6_" + no).value = "";
              document.getElementById("harga_muat7_" + no).value = "";
              document.getElementById("addharga_muat_" + no).value = addrpmuat;
              document.getElementById("harga_angkut_" + no).value = "";
              document.getElementById("harga_angkut2_" + no).value =
                numberFormat(rpangkut, 2);
              document.getElementById("harga_angkut3_" + no).value = "";
              document.getElementById("harga_angkut4_" + no).value = "";
              document.getElementById("harga_angkut5_" + no).value = "";
              document.getElementById("harga_angkut6_" + no).value = "";
              document.getElementById("harga_angkut7_" + no).value = "";
              document.getElementById("addharga_angkut_" + no).value =
                addrpangkut;
              document.getElementById("kegmuat_" + no).value = kegmuat;
              document.getElementById("kegangkut_" + no).value = kegangkut;

              kgwb = getNonNegativeNumber(
                document.getElementById("kgwb_" + no).value,
              );

              totrpmuat = parseFloat(rpmuat) * parseFloat(kgwb);
              totrpangkut = parseFloat(rpangkut) * parseFloat(kgwb);

              addtotrpmuat = parseFloat(addrpmuat) * parseFloat(kgwb);
              addtotrpangkut = parseFloat(addrpangkut) * parseFloat(kgwb);

              if (isNaN(totrpmuat)) {
                totrpmuat = 0;
              }
              if (isNaN(addtotrpmuat)) {
                addtotrpmuat = 0;
              }
              if (isNaN(totrpangkut)) {
                totrpangkut = 0;
              }
              if (isNaN(addtotrpangkut)) {
                addtotrpangkut = 0;
              }

              document.getElementById("rp_muat_" + no).value = "";
              document.getElementById("rp_muat2_" + no).value = totrpmuat;
              document.getElementById("rp_muat3_" + no).value = "";
              document.getElementById("rp_muat4_" + no).value = "";
              document.getElementById("rp_muat5_" + no).value = "";
              document.getElementById("rp_muat6_" + no).value = "";
              document.getElementById("rp_muat7_" + no).value = "";
              document.getElementById("addrp_muat_" + no).value = addtotrpmuat;
              document.getElementById("rp_angkut_" + no).value = "";
              document.getElementById("rp_angkut2_" + no).value = numberFormat(
                // // parseFloat(totrpangkut) + parseFloat(totrpmuat),
                parseFloat(totrpangkut),
                2,
              );
              document.getElementById("rp_angkut3_" + no).value = "";
              document.getElementById("rp_angkut4_" + no).value = "";
              document.getElementById("rp_angkut5_" + no).value = "";
              document.getElementById("rp_angkut6_" + no).value = "";
              document.getElementById("rp_angkut7_" + no).value = "";
              document.getElementById("addrp_angkut_" + no).value =
                addtotrpangkut;
              document.getElementById("ttlrp_" + no).value = numberFormat(
                totrpmuat + totrpangkut + addtotrpangkut + addtotrpmuat,
                2,
              );
            }
            if (tujuanx == "tphpks3") {
              document.getElementById("harga_muat_" + no).value = "";
              document.getElementById("harga_muat2_" + no).value = "";
              document.getElementById("harga_muat3_" + no).value = numberFormat(
                rpmuat,
                1,
              );
              document.getElementById("harga_muat4_" + no).value = "";
              document.getElementById("harga_muat5_" + no).value = "";
              document.getElementById("harga_muat6_" + no).value = "";
              document.getElementById("harga_muat7_" + no).value = "";
              document.getElementById("addharga_muat_" + no).value = addrpmuat;
              document.getElementById("harga_angkut_" + no).value = "";
              document.getElementById("harga_angkut2_" + no).value = "";
              document.getElementById("harga_angkut3_" + no).value =
                numberFormat(rpangkut, 2);
              document.getElementById("harga_angkut4_" + no).value = "";
              document.getElementById("harga_angkut5_" + no).value = "";
              document.getElementById("harga_angkut6_" + no).value = "";
              document.getElementById("harga_angkut7_" + no).value = "";
              document.getElementById("addharga_angkut_" + no).value =
                addrpangkut;
              document.getElementById("kegmuat_" + no).value = kegmuat;
              document.getElementById("kegangkut_" + no).value = kegangkut;

              kgwb = getNonNegativeNumber(
                document.getElementById("kgwb_" + no).value,
              );

              totrpmuat = parseFloat(rpmuat) * parseFloat(kgwb);
              totrpangkut = parseFloat(rpangkut) * parseFloat(kgwb);

              addtotrpmuat = parseFloat(addrpmuat) * parseFloat(kgwb);
              addtotrpangkut = parseFloat(addrpangkut) * parseFloat(kgwb);

              if (isNaN(totrpmuat)) {
                totrpmuat = 0;
              }
              if (isNaN(addtotrpmuat)) {
                addtotrpmuat = 0;
              }
              if (isNaN(totrpangkut)) {
                totrpangkut = 0;
              }
              if (isNaN(addtotrpangkut)) {
                addtotrpangkut = 0;
              }

              document.getElementById("rp_muat_" + no).value = "";
              document.getElementById("rp_muat2_" + no).value = "";
              document.getElementById("rp_muat3_" + no).value = totrpmuat;
              document.getElementById("rp_muat4_" + no).value = "";
              document.getElementById("rp_muat5_" + no).value = "";
              document.getElementById("rp_muat6_" + no).value = "";
              document.getElementById("rp_muat7_" + no).value = "";
              document.getElementById("addrp_muat_" + no).value = addtotrpmuat;
              document.getElementById("rp_angkut_" + no).value = "";
              document.getElementById("rp_angkut2_" + no).value = "";
              document.getElementById("rp_angkut3_" + no).value = numberFormat(
                // parseFloat(totrpangkut) + parseFloat(totrpmuat),
                parseFloat(totrpangkut),
                2,
              );
              document.getElementById("rp_angkut4_" + no).value = "";
              document.getElementById("rp_angkut5_" + no).value = "";
              document.getElementById("rp_angkut6_" + no).value = "";
              document.getElementById("rp_angkut7_" + no).value = "";
              document.getElementById("addrp_angkut_" + no).value =
                addtotrpangkut;
              document.getElementById("ttlrp_" + no).value = numberFormat(
                totrpmuat + totrpangkut + addtotrpangkut + addtotrpmuat,
                2,
              );
            }
            if (tujuanx == "tphpks4") {
              document.getElementById("harga_muat_" + no).value = "";
              document.getElementById("harga_muat2_" + no).value = "";
              document.getElementById("harga_muat3_" + no).value = "";
              document.getElementById("harga_muat4_" + no).value = numberFormat(
                rpmuat,
                1,
              );
              document.getElementById("harga_muat5_" + no).value = "";
              document.getElementById("harga_muat6_" + no).value = "";
              document.getElementById("harga_muat7_" + no).value = "";
              document.getElementById("addharga_muat_" + no).value = addrpmuat;
              document.getElementById("harga_angkut_" + no).value = "";
              document.getElementById("harga_angkut2_" + no).value = "";
              document.getElementById("harga_angkut3_" + no).value = "";
              document.getElementById("harga_angkut4_" + no).value =
                numberFormat(rpangkut, 2);
              document.getElementById("harga_angkut5_" + no).value = "";
              document.getElementById("harga_angkut6_" + no).value = "";
              document.getElementById("harga_angkut7_" + no).value = "";
              document.getElementById("addharga_angkut_" + no).value =
                addrpangkut;
              document.getElementById("kegmuat_" + no).value = kegmuat;
              document.getElementById("kegangkut_" + no).value = kegangkut;

              kgwb = getNonNegativeNumber(
                document.getElementById("kgwb_" + no).value,
              );

              totrpmuat = parseFloat(rpmuat) * parseFloat(kgwb);
              totrpangkut = parseFloat(rpangkut) * parseFloat(kgwb);

              addtotrpmuat = parseFloat(addrpmuat) * parseFloat(kgwb);
              addtotrpangkut = parseFloat(addrpangkut) * parseFloat(kgwb);

              if (isNaN(totrpmuat)) {
                totrpmuat = 0;
              }
              if (isNaN(addtotrpmuat)) {
                addtotrpmuat = 0;
              }
              if (isNaN(totrpangkut)) {
                totrpangkut = 0;
              }
              if (isNaN(addtotrpangkut)) {
                addtotrpangkut = 0;
              }

              document.getElementById("rp_muat_" + no).value = "";
              document.getElementById("rp_muat2_" + no).value = "";
              document.getElementById("rp_muat3_" + no).value = "";
              document.getElementById("rp_muat4_" + no).value = totrpmuat;
              document.getElementById("rp_muat5_" + no).value = "";
              document.getElementById("rp_muat6_" + no).value = "";
              document.getElementById("rp_muat7_" + no).value = "";
              document.getElementById("addrp_muat_" + no).value = addtotrpmuat;
              document.getElementById("rp_angkut_" + no).value = "";
              document.getElementById("rp_angkut2_" + no).value = "";
              document.getElementById("rp_angkut3_" + no).value = "";
              document.getElementById("rp_angkut4_" + no).value = numberFormat(
                // parseFloat(totrpangkut) + parseFloat(totrpmuat),
                parseFloat(totrpangkut),
                2,
              );
              document.getElementById("rp_angkut5_" + no).value = "";
              document.getElementById("rp_angkut6_" + no).value = "";
              document.getElementById("rp_angkut7_" + no).value = "";
              document.getElementById("addrp_angkut_" + no).value =
                addtotrpangkut;
              document.getElementById("ttlrp_" + no).value = numberFormat(
                totrpmuat + totrpangkut + addtotrpangkut + addtotrpmuat,
                2,
              );
            }
            if (tujuanx == "tphpks5") {
              document.getElementById("harga_muat_" + no).value = "";
              document.getElementById("harga_muat2_" + no).value = "";
              document.getElementById("harga_muat3_" + no).value = "";
              document.getElementById("harga_muat4_" + no).value = "";
              document.getElementById("harga_muat5_" + no).value = numberFormat(
                rpmuat,
                1,
              );
              document.getElementById("harga_muat6_" + no).value = "";
              document.getElementById("harga_muat7_" + no).value = "";
              document.getElementById("addharga_muat_" + no).value = addrpmuat;

              document.getElementById("harga_angkut_" + no).value = "";
              document.getElementById("harga_angkut2_" + no).value = "";
              document.getElementById("harga_angkut3_" + no).value = "";
              document.getElementById("harga_angkut4_" + no).value = "";
              document.getElementById("harga_angkut5_" + no).value =
                numberFormat(rpangkut, 2);
              document.getElementById("harga_angkut6_" + no).value = "";
              document.getElementById("harga_angkut7_" + no).value = "";

              document.getElementById("addharga_angkut_" + no).value =
                addrpangkut;
              document.getElementById("kegmuat_" + no).value = kegmuat;
              document.getElementById("kegangkut_" + no).value = kegangkut;

              kgwb = getNonNegativeNumber(
                document.getElementById("kgwb_" + no).value,
              );

              totrpmuat = parseFloat(rpmuat) * parseFloat(kgwb);
              totrpangkut = parseFloat(rpangkut) * parseFloat(kgwb);

              addtotrpmuat = parseFloat(addrpmuat) * parseFloat(kgwb);
              addtotrpangkut = parseFloat(addrpangkut) * parseFloat(kgwb);

              if (isNaN(totrpmuat)) {
                totrpmuat = 0;
              }
              if (isNaN(addtotrpmuat)) {
                addtotrpmuat = 0;
              }
              if (isNaN(totrpangkut)) {
                totrpangkut = 0;
              }
              if (isNaN(addtotrpangkut)) {
                addtotrpangkut = 0;
              }

              document.getElementById("rp_muat_" + no).value = "";
              document.getElementById("rp_muat2_" + no).value = "";
              document.getElementById("rp_muat3_" + no).value = "";
              document.getElementById("rp_muat4_" + no).value = "";
              document.getElementById("rp_muat5_" + no).value = totrpmuat;
              document.getElementById("rp_muat6_" + no).value = "";
              document.getElementById("rp_muat7_" + no).value = "";

              document.getElementById("addrp_muat_" + no).value = addtotrpmuat;

              document.getElementById("rp_angkut_" + no).value = "";
              document.getElementById("rp_angkut2_" + no).value = "";
              document.getElementById("rp_angkut3_" + no).value = "";
              document.getElementById("rp_angkut4_" + no).value = "";
              document.getElementById("rp_angkut5_" + no).value = numberFormat(
                // parseFloat(totrpangkut) + parseFloat(totrpmuat),
                parseFloat(totrpangkut),
                2,
              );
              document.getElementById("rp_angkut6_" + no).value = "";
              document.getElementById("rp_angkut7_" + no).value = "";

              document.getElementById("addrp_angkut_" + no).value =
                addtotrpangkut;
              document.getElementById("ttlrp_" + no).value = numberFormat(
                totrpmuat + totrpangkut + addtotrpangkut + addtotrpmuat,
                2,
              );
            }
            if (tujuanx == "tphpks6") {
              document.getElementById("harga_muat_" + no).value = "";
              document.getElementById("harga_muat2_" + no).value = "";
              document.getElementById("harga_muat3_" + no).value = "";
              document.getElementById("harga_muat4_" + no).value = "";
              document.getElementById("harga_muat5_" + no).value = "";
              document.getElementById("harga_muat6_" + no).value = numberFormat(
                rpmuat,
                1,
              );
              document.getElementById("harga_muat7_" + no).value = "";
              document.getElementById("addharga_muat_" + no).value = addrpmuat;

              document.getElementById("harga_angkut_" + no).value = "";
              document.getElementById("harga_angkut2_" + no).value = "";
              document.getElementById("harga_angkut3_" + no).value = "";
              document.getElementById("harga_angkut4_" + no).value = "";
              document.getElementById("harga_angkut5_" + no).value = "";
              document.getElementById("harga_angkut6_" + no).value =
                numberFormat(rpangkut, 2);
              document.getElementById("harga_angkut7_" + no).value = "";

              document.getElementById("addharga_angkut_" + no).value =
                addrpangkut;
              document.getElementById("kegmuat_" + no).value = kegmuat;
              document.getElementById("kegangkut_" + no).value = kegangkut;

              kgwb = getNonNegativeNumber(
                document.getElementById("kgwb_" + no).value,
              );

              totrpmuat = parseFloat(rpmuat) * parseFloat(kgwb);
              totrpangkut = parseFloat(rpangkut) * parseFloat(kgwb);

              addtotrpmuat = parseFloat(addrpmuat) * parseFloat(kgwb);
              addtotrpangkut = parseFloat(addrpangkut) * parseFloat(kgwb);

              if (isNaN(totrpmuat)) {
                totrpmuat = 0;
              }
              if (isNaN(addtotrpmuat)) {
                addtotrpmuat = 0;
              }
              if (isNaN(totrpangkut)) {
                totrpangkut = 0;
              }
              if (isNaN(addtotrpangkut)) {
                addtotrpangkut = 0;
              }

              document.getElementById("rp_muat_" + no).value = "";
              document.getElementById("rp_muat2_" + no).value = "";
              document.getElementById("rp_muat3_" + no).value = "";
              document.getElementById("rp_muat4_" + no).value = "";
              document.getElementById("rp_muat5_" + no).value = "";
              document.getElementById("rp_muat6_" + no).value = totrpmuat;
              document.getElementById("rp_muat7_" + no).value = "";

              document.getElementById("addrp_muat_" + no).value = addtotrpmuat;

              document.getElementById("rp_angkut_" + no).value = "";
              document.getElementById("rp_angkut2_" + no).value = "";
              document.getElementById("rp_angkut3_" + no).value = "";
              document.getElementById("rp_angkut4_" + no).value = "";
              document.getElementById("rp_angkut5_" + no).value = "";
              document.getElementById("rp_angkut6_" + no).value = numberFormat(
                // parseFloat(totrpangkut) + parseFloat(totrpmuat),
                parseFloat(totrpangkut),
                2,
              );
              document.getElementById("rp_angkut7_" + no).value = "";

              document.getElementById("addrp_angkut_" + no).value =
                addtotrpangkut;
              document.getElementById("ttlrp_" + no).value = numberFormat(
                totrpmuat + totrpangkut + addtotrpangkut + addtotrpmuat,
                2,
              );
            }
            if (tujuanx == "tphpks7") {
              document.getElementById("harga_muat_" + no).value = "";
              document.getElementById("harga_muat2_" + no).value = "";
              document.getElementById("harga_muat3_" + no).value = "";
              document.getElementById("harga_muat4_" + no).value = "";
              document.getElementById("harga_muat5_" + no).value = "";
              document.getElementById("harga_muat6_" + no).value = "";
              document.getElementById("harga_muat7_" + no).value = numberFormat(
                rpmuat,
                1,
              );
              document.getElementById("addharga_muat_" + no).value = addrpmuat;

              document.getElementById("harga_angkut_" + no).value = "";
              document.getElementById("harga_angkut2_" + no).value = "";
              document.getElementById("harga_angkut3_" + no).value = "";
              document.getElementById("harga_angkut4_" + no).value = "";
              document.getElementById("harga_angkut5_" + no).value = "";
              document.getElementById("harga_angkut6_" + no).value = "";
              document.getElementById("harga_angkut7_" + no).value =
                numberFormat(rpangkut, 2);

              document.getElementById("addharga_angkut_" + no).value =
                addrpangkut;
              document.getElementById("kegmuat_" + no).value = kegmuat;
              document.getElementById("kegangkut_" + no).value = kegangkut;

              kgwb = getNonNegativeNumber(
                document.getElementById("kgwb_" + no).value,
              );

              totrpmuat = parseFloat(rpmuat) * parseFloat(kgwb);
              totrpangkut = parseFloat(rpangkut) * parseFloat(kgwb);

              addtotrpmuat = parseFloat(addrpmuat) * parseFloat(kgwb);
              addtotrpangkut = parseFloat(addrpangkut) * parseFloat(kgwb);

              if (isNaN(totrpmuat)) {
                totrpmuat = 0;
              }
              if (isNaN(addtotrpmuat)) {
                addtotrpmuat = 0;
              }
              if (isNaN(totrpangkut)) {
                totrpangkut = 0;
              }
              if (isNaN(addtotrpangkut)) {
                addtotrpangkut = 0;
              }

              document.getElementById("rp_muat_" + no).value = "";
              document.getElementById("rp_muat2_" + no).value = "";
              document.getElementById("rp_muat3_" + no).value = "";
              document.getElementById("rp_muat4_" + no).value = "";
              document.getElementById("rp_muat5_" + no).value = "";
              document.getElementById("rp_muat6_" + no).value = "";
              document.getElementById("rp_muat7_" + no).value = totrpmuat;

              document.getElementById("addrp_muat_" + no).value = addtotrpmuat;

              document.getElementById("rp_angkut_" + no).value = "";
              document.getElementById("rp_angkut2_" + no).value = "";
              document.getElementById("rp_angkut3_" + no).value = "";
              document.getElementById("rp_angkut4_" + no).value = "";
              document.getElementById("rp_angkut5_" + no).value = "";
              document.getElementById("rp_angkut6_" + no).value = "";
              document.getElementById("rp_angkut7_" + no).value = numberFormat(
                // parseFloat(totrpangkut) + parseFloat(totrpmuat),
                parseFloat(totrpangkut),
                2,
              );

              document.getElementById("addrp_angkut_" + no).value =
                addtotrpangkut;
              document.getElementById("ttlrp_" + no).value = numberFormat(
                totrpmuat + totrpangkut + addtotrpangkut + addtotrpmuat,
                2,
              );
            }

            if (tujuanx == "") {
              document.getElementById("harga_muat_" + no).value = "";
              document.getElementById("harga_muat2_" + no).value = "";
              document.getElementById("harga_muat3_" + no).value = "";
              document.getElementById("harga_muat4_" + no).value = "";
              document.getElementById("harga_muat5_" + no).value = "";
              document.getElementById("harga_muat6_" + no).value = "";
              document.getElementById("harga_muat7_" + no).value = "";

              document.getElementById("harga_angkut_" + no).value = "";
              document.getElementById("harga_angkut2_" + no).value = "";
              document.getElementById("harga_angkut3_" + no).value = "";
              document.getElementById("harga_angkut4_" + no).value = "";
              document.getElementById("harga_angkut5_" + no).value = "";
              document.getElementById("harga_angkut6_" + no).value = "";
              document.getElementById("harga_angkut7_" + no).value = "";

              document.getElementById("rp_muat_" + no).value = "";
              document.getElementById("rp_muat2_" + no).value = "";
              document.getElementById("rp_muat3_" + no).value = "";
              document.getElementById("rp_muat4_" + no).value = totrpmuat;
              document.getElementById("rp_muat5_" + no).value = "";
              document.getElementById("rp_muat6_" + no).value = "";
              document.getElementById("rp_muat7_" + no).value = "";

              document.getElementById("rp_angkut_" + no).value = "";
              document.getElementById("rp_angkut2_" + no).value = "";
              document.getElementById("rp_angkut3_" + no).value = "";
              document.getElementById("rp_angkut4_" + no).value = "";
              document.getElementById("rp_angkut5_" + no).value = "";
              document.getElementById("rp_angkut6_" + no).value = "";
              document.getElementById("rp_angkut7_" + no).value = "";

              document.getElementById("addharga_muat_" + no).value = "";
              document.getElementById("addharga_angkut_" + no).value = "";
              document.getElementById("addrp_muat_" + no).value = "";
              document.getElementById("addrp_angkut_" + no).value = "";
              document.getElementById("ttlrp_" + no).value = "";
            }
          }
          no = parseFloat(no) + 1;
          if (no > maxrow || maxrow == undefined) {
          } else {
            if (nospb == nospbold) {
              getharga(no, maxrow, nospbold);
            }
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

// function getharga(no) {
// blok = document.getElementById('blok_'+no).value;
// tujuan = document.getElementById('tujuan_'+no).value;
// pkstujuan = document.getElementById('pkstujuan_'+no).value;
// jnskend = document.getElementById('jnskend_'+no).value;
// ttlrow = document.getElementById('jumlahrow').value;

// nox = parseFloat(no);
// n = (parseFloat(no)+1);
// if(n<=ttlrow){
// for (i = n; i <= ttlrow; i++) {
// document.getElementById('tujuan_' + i).value = tujuan;
// document.getElementById('jnskend_' + i).value = jnskend;
// }
// }

// param = 'method=getharga';
// param += '&blok=' + blok;
// param += '&tujuan=' + tujuan;
// param += '&pkstujuan=' + pkstujuan;
// param += '&jnskend=' + jnskend;
// tujuan = 'kebun_slave_rekapangkutantbs.php';
// post_response_text(tujuan, param, respog);
// function respog() {
// if (con.readyState == 4) {
// if (con.status == 200) {
// busy_off();
// if (!isSaveResponse(con.responseText)) {
// alertify.alert(con.responseText);
// } else {
// isi = con.responseText.split("##");
// rpmuat = value=trim(isi[0]);
// rpangkut = value=trim(isi[1]);
// kegmuat = value=trim(isi[2]);
// kegangkut = value=trim(isi[3]);
// i = "";
// for (i = nox; i <= ttlrow; i++) {
// document.getElementById('harga_muat_'+i).value=rpmuat;
// document.getElementById('harga_angkut_'+i).value=rpangkut;
// document.getElementById('kegmuat_'+i).value=kegmuat;
// document.getElementById('kegangkut_'+i).value=kegangkut;

// kgwb = document.getElementById('kgwb_'+i).value;
// kgwb=remove_comma_var(kgwb);

// totrpmuat = parseFloat(rpmuat)*parseFloat(kgwb);
// totrpangkut = parseFloat(rpangkut)*parseFloat(kgwb);

// if(isNaN(totrpmuat)){totrpmuat=0;}
// if(isNaN(totrpangkut)){totrpangkut=0;}

// document.getElementById('rp_muat_'+i).value=numberFormat(totrpmuat);
// document.getElementById('rp_angkut_'+i).value=numberFormat(totrpangkut);
// document.getElementById('ttlrp_'+i).value=numberFormat(totrpmuat+totrpangkut);
// }

// }
// } else {
// busy_off();
// error_catch(con.status);
// }
// }
// }
// }

function getkg() {
  jjgpnn = document.getElementById("jjgpnn").value;
  bjr = document.getElementById("bjr").value;
  kg = parseFloat(jjgpnn) * parseFloat(bjr);
  kg = parseFloat(kg).toFixed(0);
  if (kg == "NaN") {
    kg = 0;
  }
  document.getElementById("kgkebun").value = kg;
}
function excel(ev, tujuan) {
  unitexp = document.getElementById("unitexp").value;
  perexp = document.getElementById("perexp").value;
  judul = "Report Ms.Excel";
  param = "method=excel" + "&unitexp=" + unitexp + "&perexp=" + perexp;
  printFile(param, tujuan, judul, ev);
}
function add_new_data() {
  //indra
  document.getElementById("header").style.display = "block";
  document.getElementById("listData").style.display = "none";
  //cancelHead();
  //cancel();
  cancel();
}
function form() {
  width = "";
  height = "";
  content =
    '<fieldset><div id=containerd align=center style="width:100%;max-height:400px;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "Detail HTML";
  showDialog1(title, content, width, height, ev);
}
function viewdetailx(kodeorg, periode, nospb, tipenya) {
  // width = '720';
  // height = '';
  // content = "<fieldset><div id=container align=center style=\"width:700px;max-height:400px;overflow:auto;\"></div></fieldset>";
  // ev = 'event';
  // title = "Detail HTML";
  // showDialog5(title, content, width, height, ev);

  param =
    "method=viewdetailx" +
    "&kodeorg=" +
    kodeorg +
    "&periode=" +
    periode +
    "&nospb=" +
    nospb +
    "&tipenya=" +
    tipenya;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  if (tipenya != "html") {
    printnopopup(tujuan + "?" + param);
  } else {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('container').innerHTML = con.responseText;
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewexcel(kodeorg, periode, spk, periodebyr, jenis) {
  param =
    "method=html" +
    "&kodeorg=" +
    kodeorg +
    "&periode=" +
    periode +
    "&spk=" +
    spk +
    "&periodebyr=" +
    periodebyr +
    "&jenis=" +
    jenis;
  tujuan = "kebun_slave_rekapangkutantbs.php" + "?" + param;
  width = "";
  height = "";
  ev = "event";
  title = "Preview";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  // printFile(param,tujuan,title,ev);
  printnopopup(tujuan);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "300";
  height = "100";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}
function html(kodeorg, periode, spk, periodebyr, jenis) {
  // width = '';
  // height = '';
  // content = "<fieldset><div id=containerd align=center style=\"max-height:400px;overflow:auto;\"></div></fieldset>";
  // ev = 'event';
  // title = "Detail HTML";
  // showDialog1(title, content, width, height, ev);

  param =
    "method=html" +
    "&kodeorg=" +
    kodeorg +
    "&periode=" +
    periode +
    "&spk=" +
    spk +
    "&periodebyr=" +
    periodebyr +
    "&jenis=" +
    jenis;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('containerd').innerHTML = con.responseText;
          //alertify.alert(con.responseText);
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function displayList() {
  //document.getElementById('divsch').value = '';
  document.getElementById("tglsch").value = "";
  document.getElementById("listData").style.display = "block";
  document.getElementById("header").style.display = "none";
  document.getElementById("detail").style.display = "none";
  setValue2("tglsch", null);
  loaddata(0);
}
function edit(kodeorg, periode, spk, periodebyr, tgldr, tglsd) {
  document.getElementById("kodeorg").value = kodeorg;
  document.getElementById("periode").value = periode;
  document.getElementById("periodebyr").value = periodebyr;
  document.getElementById("tglmulai").value = tgldr;
  document.getElementById("tglselesai").value = tglsd;
  document.getElementById("spk").value = spk;
  document.getElementById("listData").style.display = "none";
  document.getElementById("header").style.display = "block";
  //document.getElementById('detail').style.display='block';

  setValue2("kodeorg", kodeorg);
  setValue2("periode", periode);
  setValue2("periodebyr", periodebyr);
  setValue2("spk", spk);
  detail();
}

function del(kodeorg, periode, spk, periodebyr) {
  param = "method=delete" + "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&periodebyr=" + periodebyr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  if (confirm(" Anda yakin ingin menghapus nomor transaksi")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contain").innerHTML = con.responseText;
          loaddata();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function posting(kodeorg, periode, spk, periodebyr, numrow) {
  param = "method=posting" + "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&periodebyr=" + periodebyr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  if (confirm("Anda yakin ingin ???")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function unposting(kodeorg, periode, spk, nobapp, periodebyr, numrow) {
  param = "method=unposting" + "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&nobapp=" + nobapp;
  param += "&periodebyr=" + periodebyr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  if (confirm("Anda yakin ingin unposting ???")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}
function loaddata(page) {
  divsch = document.getElementById("divsch").value;
  tglsch = document.getElementById("tglsch").value;
  nospkcr = document.getElementById("nospkcr").value;
  kontrakcr = document.getElementById("kontrakcr").value;
  nospb = document.getElementById("nospbcr").value;
  bapp = document.getElementById("bappcr").value;
  param = "method=loaddata&page=" + page;
  param += "&nospb=" + nospb;
  if (divsch != "") {
    param += "&divsch=" + divsch;
  }
  if (tglsch != "") {
    param += "&tglsch=" + tglsch;
  }
  param += "&bapp=" + bapp;
  param += "&nospkcr=" + nospkcr;
  param += "&kontrakcr=" + kontrakcr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
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
function cancel() {
  document.getElementById("detail").style.display = "none";
  document.getElementById("tomboldetail").disabled = false;
  document.getElementById("periode").value = "";
  document.getElementById("spk").value = "";
  document.getElementById("periodebyr").value = "";
  document.getElementById("kodeorg").disabled = false;
  document.getElementById("periodebyr").disabled = false;
  document.getElementById("spk").disabled = false;
  document.getElementById("periode").disabled = false;
  document.getElementById("tgl").disabled = false;
  document.getElementById("tglmulai").disabled = false;
  document.getElementById("tglselesai").disabled = false;
  document.getElementById("tgl").value = "";
  document.getElementById("tglmulai").value = "";
  document.getElementById("tglselesai").value = "";

  setValue2("periode", null);
  setValue2("spk", null);
  setValue2("periodebyr", null);
  setValue2("kodeorg", null);
}

function getdata() {
  blok = document.getElementById("blok").value;
  tgl = document.getElementById("tgl").value;
  param = "method=getdata" + "&blok=" + blok + "&tgl=" + tgl;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //
          //
          isi = con.responseText.split("##");
          document.getElementById("thntnm").value = trim(isi[0]);
          document.getElementById("luasaresta").value = trim(isi[1]);
          document.getElementById("bjr").value = trim(isi[2]);
          getkg();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cleardetail() {
  document.getElementById("blok").value = "";
  document.getElementById("blok").disabled = false;
  document.getElementById("thntnm").value = "";
  document.getElementById("luasaresta").value = "";
  document.getElementById("luaspnn").value = "";
  document.getElementById("tk").value = "";
  document.getElementById("jjgpnn").value = "";
  document.getElementById("afkirjjg").value = "";
  document.getElementById("afkirket").value = "";
  document.getElementById("bjr").value = "";
  document.getElementById("kgkebun").value = "";
}

function editdetail(nospb, jeniskend, jenisangkt) {
  document.getElementById("nospb").innerHTML =
    "<option value='" + nospb + "'>" + nospb + "</option>";
  document.getElementById("jeniskend").value = jeniskend;
  getdetailspb(jenisangkt);
}

function viewdetailbapp(notransaksi, kodeorg, tipeview, ev, nobapp) {
  // width = '';
  // height = '';
  // content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
  // title = "";
  // showDialog1(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic1').style.top = pos[1] + 'px';
  // // document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
  // document.getElementById('dynamic1').style.display = '';

  var param =
    "notransaksi=" +
    notransaksi +
    "&kodeorg=" +
    kodeorg +
    "&tipeview=" +
    tipeview +
    "&nobapp=" +
    nobapp;

  param += "&method=rekapbapp";
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // document.getElementById('contRekap').innerHTML = con.responseText;
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function view(
  nopengajuan,
  notransaksi,
  kodeorg,
  tanggal,
  termin,
  numRow,
  ev,
  tipe,
  bapp,
) {
  param =
    "method=preview&tipe=" +
    tipe +
    "&notransaksi=" +
    notransaksi +
    "&nopengajuan=" +
    nopengajuan +
    "&kodeorg=" +
    kodeorg +
    "&tanggal=" +
    tanggal +
    "&termin=" +
    termin +
    "&baspk=" +
    bapp;
  // width = '';
  // height = '';
  // content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
  // title = "View";
  // showDialog2(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic2').style.top = pos[1] + 'px';
  // // document.getElementById('dynamic2').style.right = (80) + 'px';
  // document.getElementById('dynamic2').style.display = '';

  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('contviewx').innerHTML = con.responseText;
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function showhidedetail(rows, total) {
  for (var i = 1; i <= total; i++) {
    key = document.getElementById("tr_dt2_" + rows + "_" + i).style.display;
    if (key == "none") {
      document.getElementById("tr_dt2_" + rows + "_" + i).style.display = "";
    } else {
      document.getElementById("tr_dt2_" + rows + "_" + i).style.display =
        "none";
    }
  }
}

function UploadFile(notransaksi, tanggal, termin, numRow, ev, nopengajuan) {
  title = "List File";
  //formajukan(title,ev);
  param =
    "method=UploadFile" +
    "&notransaksi=" +
    notransaksi +
    "&tanggal=" +
    tanggal +
    "&termin=" +
    termin +
    "&nopengajuan=" +
    nopengajuan;
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('containervoid').innerHTML = con.responseText;
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("35%", "50%");
          loadfiles(notransaksi, termin, nopengajuan);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function formajukan(title, ev) {
  width = "";
  height = "";
  content = "<div id=containervoid ></div>";
  showDialog2(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic2").style.top = pos[1] + "px";
  // document.getElementById('dynamic2').style.right = (80) + 'px';
  document.getElementById("dynamic2").style.display = "";
}

function loadfiles(notransaksi, termin, nopengajuan) {
  param =
    "method=loadfiles&notransaksi=" +
    notransaksi +
    "&termin=" +
    termin +
    "&nopengajuan=" +
    nopengajuan;
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (document.getElementById("listfiles") !== null) {
            document.getElementById("listfiles").innerHTML = con.responseText;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function submitfile() {
  var kriteriaefil = document.getElementById("kriteriaefil").value;
  var file = document.getElementById("upload").files[0];
  var notransaksi = document.getElementById("notransaksi").innerHTML;
  var pengajuanspk = document.getElementById("pengajuanspk").innerHTML;
  var tanggal = document.getElementById("tanggal").innerHTML;
  var termin = document.getElementById("terminup").innerHTML;
  var formdata = new FormData();
  formdata.append("fileupload", getValue("upload"));
  formdata.append("file", file);
  formdata.append("notransaksi", notransaksi);
  formdata.append("pengajuanspk", pengajuanspk);
  formdata.append("kriteriaefil", kriteriaefil);
  formdata.append("termin", termin);
  formdata.append("tanggal", tanggal);
  if (getValue("upload") == "") {
    alertify.alert("warning : Upload file has been empty.");
    return false;
  }
  if (notransaksi == "" || pengajuanspk == "") {
    alertify.alert("warning : Nomor Transaksi di Perlukan !");
    return false;
  }
  var con = createXMLHttpRequest();
  document.getElementById("btnsubmit").disabled = true;
  busy_on();
  con.open("POST", "log_slave_realisasispkx.php?method=submitfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          document.getElementById("btnsubmit").disabled = false;
        } else {
          //=== Success Response
          alertify.alert("Uploaded Success.");
          document.getElementById("btnsubmit").disabled = false;
          document.getElementById("upload").value = "";
          loadfiles(notransaksi, termin, pengajuanspk);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(notransaksi, namafile, termin, nopengajuan) {
  param = "method=deletefile";
  param += "&notransaksi=" + notransaksi;
  param += "&namafile=" + namafile;
  param += "&nopengajuan=" + nopengajuan;
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loadfiles(notransaksi, termin, nopengajuan);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function form_ajukan(
  kodeorg,
  notransaksi,
  tanggal,
  termin,
  numrow,
  jlhrealisasi,
  nobapp,
) {
  if (jlhrealisasi == 0) {
    alertify.alert("Gagal, Jumlah Realisasi masih 0");
    return false;
  }
  // width = '300';
  // height = '';
  // content = "<fieldset style=width:280px><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:180px;overflow:auto;\"></div></fieldset>";
  // ev = 'event';
  // title = "";
  // showDialog5(title, content, width, height, ev);
  param =
    "method=form_ajukan" +
    "&notransaksi=" +
    notransaksi +
    "&tanggal=" +
    tanggal +
    "&termin=" +
    termin +
    "&numrow=" +
    numrow +
    "&kodeorg=" +
    kodeorg;
  param += "&nobapp=" + nobapp;
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('containeraju').innerHTML = con.responseText;
          alertify
            .popup2("Ajukan", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("30%", "40%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ajukan(ev) {
  kepada = document.getElementById("kepada").value;
  notransaksi = document.getElementById("notran_aju").innerHTML;
  tanggal = document.getElementById("tgljurnal").value;
  unit = document.getElementById("unitdt2").value;
  termin = document.getElementById("termin_aju").innerHTML;
  nopengajuan = document.getElementById("nopengajuan_aju").innerHTML;
  numrow = document.getElementById("numrow").value;
  bappdt2 = document.getElementById("bappdt2").value;
  param = "method=ajukan" + "&notransaksi=" + notransaksi + "&kepada=" + kepada;
  param += "&tanggal=" + tanggal;
  param += "&termin=" + termin;
  param += "&nopengajuan=" + nopengajuan;
  param += "&nobapp=" + bappdt2;

  if (kepada == "") {
    alertify.alert("Isikan nama penyetuju.");
    return;
  }
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          tipeview = "viewhtml";
          alertify.popup().destroy();
          alertify.popup2().destroy();
          //viewdetail(notransaksi,unit,tipeview,ev)
          //closeDialog5();
          //alertify.alert('Sucses');
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewdetail(notransaksi, kodeorg, tipeview, ev) {
  // width = '';
  // height = '';
  // content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
  // title = "";
  // showDialog1(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic1').style.top = pos[1] + 'px';
  // // document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
  // document.getElementById('dynamic1').style.display = '';

  var param =
    "notransaksi=" +
    notransaksi +
    "&kodeorg=" +
    kodeorg +
    "&tipeview=" +
    tipeview;

  param += "&method=rekapbapp";
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('contRekap').innerHTML = con.responseText;
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getapprovaldetail(nopengajuan, kodeorg, ev) {
  // width = '';
  // height = '';
  // content = "<fieldset style=width:96%><legend>Detail Approval</legend><div id=contapp style=\"overflow:auto;width:100%;\"></div></fieldset>";
  // title = "";
  // showDialog4(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic4').style.top = pos[1] + 'px';
  // // document.getElementById('dynamic4').style.left = (pos[0]-width) + 'px';
  // document.getElementById('dynamic4').style.display = '';
  param =
    "method=getapprovaldetail" +
    "&nopengajuan=" +
    nopengajuan +
    "&kodeorg=" +
    kodeorg;
  tujuan = "log_slave_realisasispkx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('contapp').innerHTML = con.responseText;
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("35%", "50%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getformnospb() {
  kodeorg = document.getElementById("kodeorg").value;
  periode = document.getElementById("periode").value;
  periodebyr = document.getElementById("periodebyr").value;
  spk = document.getElementById("spk").value;
  tgl = document.getElementById("tglmulai").value;
  tgl2 = document.getElementById("tglselesai").value;

  param = "method=getformnospb";
  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&tglmulai=" + tgl;
  param += "&tglselesai=" + tgl2;
  param += "&periodebyr=" + periodebyr;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // width = '';
          // height = '';
          // content = "<fieldset><div id=containerd style=\"max-height:500px;width:100%;overflow:auto;\"></div></fieldset>";
          // ev = 'event';
          // title = "";
          // showDialog1(title, content, width, height, ev);
          // document.getElementById('containerd').innerHTML = con.responseText;
          alertify.popup().destroy();
          alertify
            .popup(con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("70%", "80%");
          getnospb();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function clickall() {
  e = document.getElementsByName("click[]");
  h = document.getElementsByName("cekharga[]");
  for (i = 0; i < e.length; i++) {
    if (document.getElementById("clickall").checked == true) {
      if (e[i].checked == false) {
        e[i].checked = true;
      } else {
        e[i].checked = true;
      }
    } else {
      if (e[i].checked == true) {
        e[i].checked = false;
      } else {
        e[i].checked = false;
      }
    }
  }
}
function hitungclick() {
  e = document.getElementsByName("click[]");
  no = 0;
  for (i = 0; i < e.length; i++) {
    if (e[i].checked == true) {
      no += 1;
    }
  }
  if (parseInt(no) == e.length) {
    document.getElementById("clickall").checked = true;
  } else {
    document.getElementById("clickall").checked = false;
  }
}
function getnospb() {
  kodeorg = document.getElementById("kodeorg").value;
  periode = document.getElementById("periode").value;
  periodebyr = document.getElementById("periodebyr").value;
  spk = document.getElementById("spk").value;
  tgl = document.getElementById("tglmulai").value;
  tgl2 = document.getElementById("tglselesai").value;

  tiket = document.getElementById("tiketcrx").value;
  nospb = document.getElementById("nospbcrx").value;
  unit = document.getElementById("unitcrx").value;
  tanggal = document.getElementById("tanggalcr").value;
  nopol = document.getElementById("nopolcrx").value;
  sopir = document.getElementById("sopircrx").value;

  param = "method=getnospb";
  param += "&kodeorg=" + kodeorg + "&periode=" + periode;
  param += "&spk=" + spk;
  param += "&tglmulai=" + tgl;
  param += "&tglselesai=" + tgl2;
  param += "&nopol=" + nopol;
  param += "&sopir=" + sopir;
  param += "&periodebyr=" + periodebyr;
  param += "&tiket=" + tiket;
  param += "&nospb=" + nospb;
  param += "&unit=" + unit;
  param += "&tanggal=" + tanggal;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("formnospb").innerHTML = con.responseText;
          document.getElementById("clickall").checked = false;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailPDF2(notransaksi, kodeorg, tipeview, ev) {
  param =
    "method=rekapbapp&notransaksi=" +
    notransaksi +
    "&kodeorg=" +
    kodeorg +
    "&tipeview=" +
    tipeview;

  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_realisasispkx.php?" +
        param +
        "'></iframe>",
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");

  // showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
  // " src='log_slave_realisasispkx.php?"+param+"'></iframe>",'800','400',ev);
  // var dialog = document.getElementById('dynamic1');
  // dialog.style.top = '50px';
  // dialog.style.left = '15%';
}

function previewexcelall(
  nopengajuan,
  notransaksi,
  nobapp,
  kodeorg,
  tanggal,
  termin,
  numrow,
  nospk,
  tgldr,
  tglsd,
) {
  param =
    "method=previewexcelall&notransaksi=" +
    notransaksi +
    "&nopengajuan=" +
    nopengajuan +
    "&kodeorg=" +
    kodeorg +
    "&tanggal=" +
    tanggal +
    "&termin=" +
    termin +
    "&nobapp=" +
    nobapp +
    "&spk=" +
    nospk +
    "&tglmulai=" +
    tgldr +
    "&tglselesai=" +
    tglsd;
  tujuan = "kebun_slave_rekapangkutantbs.php";
  printnopopup(tujuan + "?" + param);
}
