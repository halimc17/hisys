function loadRFQChat(nomor, ev) {
  title = "Chat:" + nomor;
  content =
    "<iframe frameborder=0 style='width:510px;height:290px;' src='log_slaveChatRFQ.php?nomor=" +
    nomor +
    "'></iframe>";
  width = "";
  height = "";
  showDialog2(title, content, width, height, ev);
}

function loadUploadPo(notransaksi, ev) {
  const title = "Upload PO";
  const content = `<iframe frameborder=0 style='width:800px;height:400px;' src='log_slaveUploadPO.php?method=formUploadPo&notransaksi=${notransaksi}' id='iframe_uploadpo'></iframe>`;
  showDialog2(title, content, "", "", ev);
}

function getkurs(no) {
  no_permintaan = document.getElementById("no_prmntan_" + no).value;
  mtUang = document.getElementById("mtUang_" + no).options[
    document.getElementById("mtUang_" + no).selectedIndex
  ].value;
  if (mtUang != "IDR") {
    param =
      "method=getkurs" +
      "&mtUang=" +
      mtUang +
      "&no_permintaan=" +
      no_permintaan;
    tujuan = "log_slave_pnwrharga.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            if (con.responseText == "") {
              alert("Kurs Belum di-input");
              document.getElementById("mtUang_" + no).value = "IDR";
              document.getElementById("Kurs_" + no).value = "1";
            } else {
              document.getElementById("Kurs_" + no).value = con.responseText;
            }
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  } else {
    document.getElementById("mtUang_" + no).value = "IDR";
    document.getElementById("Kurs_" + no).value = "1";
  }
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function displaylist() {
  document.getElementById("formPP").style.display = "none";
  document.getElementById("formPP2").style.display = "none";
  document.getElementById("list_permintaan").style.display = "block";
  document.getElementById("txtsearch").value = "";
  document.getElementById("tgl_cari").value = "";
  document.getElementById("txtnopp").value = "";
  document.getElementById("formEditData2").style.display = "none";
  loaddata(0);
}

function loaddata(pg) {
  crnotransaksi = document.getElementById("txtsearch").value;
  crnopp = document.getElementById("txtnopp").value;
  crtanggal = document.getElementById("tgl_cari").value;

  param =
    "method=loaddata&page=" +
    pg +
    "&crnotransaksi=" +
    crnotransaksi +
    "&crtanggal=" +
    crtanggal +
    "&crnopp=" +
    crnopp;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contain").innerHTML = con.responseText;
          loadNotifikasi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadNotifikasi() {
  proses = "getNotifikasi";
  param = "method=" + proses;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("notifikasiKerja").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getDtPP(kdunit) {
  displayFormInput();
  param = "method=getBarangPP" + "&kdPt=" + kdunit;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText.split("###");
          document.getElementById("listSupplier").style.display = "none";
          document.getElementById("formEditData2").style.display = "none";
          document.getElementById("listBrgPP").style.display = "block";
          document.getElementById("dataBarang").innerHTML = data[0];
          document.getElementById("schunit").innerHTML = data[1];
          document.getElementById("schpt").value = data[2];
          document.getElementById("noUrut").value = 1;
          document.getElementById("notransaksi").value = "";
          // document.getElementById('dtSemua').checked=false;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function schgetDtPP() {
  schnopp = document.getElementById("schnopp").value;
  schjenis = document.getElementById("schjenis").value;
  schunit = document.getElementById("schunit").value;
  schpt = document.getElementById("schpt").value;
  schklbrg = document.getElementById("schklbrg").value;
  schkdbrg = document.getElementById("schkdbrg").value;

  param =
    "method=schgetDtPP" +
    "&schnopp=" +
    schnopp +
    "&schjenis=" +
    schjenis +
    "&schunit=" +
    schunit;
  param +=
    "&schklbrg=" + schklbrg + "&schpt=" + schpt + "&schkdbrg=" + schkdbrg;

  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("dataBarang").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function lanjutAdd() {
  var tbl = document.getElementById("dataBarang");
  var row = tbl.rows.length;
  row = row - 1;

  strUrl = "";
  nokontrak = "";
  var hasPR = false;
  var hasSR = false;
  
  for (i = 1; i <= row; i++) {
    ar = document.getElementById("pilBrg_" + i);
    if (ar && ar.checked == true) {
      var nopp = trim(document.getElementById("nopplst_" + i).innerHTML);
      if (nopp.indexOf("/SR/") !== -1) {
        hasSR = true;
      } else {
        hasPR = true;
      }
      
      nokontrak = document.getElementById("nokontrak_" + i).innerHTML;
      try {
        if (strUrl != "") {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML) +
            "&lstkontrak[]=" +
            trim(document.getElementById("nokontrak_" + i).innerHTML);
        } else {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML) +
            "&lstkontrak[]=" +
            trim(document.getElementById("nokontrak_" + i).innerHTML);
        }
      } catch (e) {}
    }
  }

  if (hasPR && hasSR) {
    alert("Tidak boleh menggabungkan PR dan SR dalam satu transaksi!");
    return;
  }

  if (strUrl == "") {
    alert("Choose one");
    return;
  }

  if (nokontrak != "") {
    param = "method=createrfq" + "&baris=" + i;
  } else {
    param = "method=cekBarang" + "&baris=" + i;
  }
  param += strUrl;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (nokontrak != "") {
            zPreview2(
              "log_slave_pnwrharga",
              con.responseText,
              "printContainer2"
            );
          } else {
            document.getElementById("listBrgPP").style.display = "none";
            document.getElementById("listSupplier").style.display = "none";
            document.getElementById("supplierForm").style.display = "block";
            document.getElementById("id_supplier").value = "";
            document.getElementById("alamat").value = "";
            document.getElementById("detailbarang").innerHTML =
              con.responseText;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addfileupload(nopp, kodebarang, ev) {
  showpopupupload(nopp, kodebarang, ev);
  param = "nopp=" + nopp + "&method=addfileupload&kodebarang=" + kodebarang;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contDetaildt").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewdetailupload(notransaksi, supplierid, kodebarang, nopp, ev) {
  showpopupupload(nopp, kodebarang, ev);
  param =
    "method=viewdetailupload&notransaksi=" +
    notransaksi +
    "&supplierid=" +
    supplierid +
    "&kodebarang=" +
    kodebarang +
    "&nopp=" +
    nopp;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contDetaildt").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilesupload(nopp, kodebarang) {
  param = "method=loadfilesupload&kodebarang=" + kodebarang + "&nopp=" + nopp;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById(
            "listfiles_" + notransaksi + "_" + supplierid
          ).innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showpopupupload(kodebarang, nopp, ev) {
  title = "Add File Upload";
  width = "";
  height = "";
  content =
    "<fieldset><legend>" +
    nopp +
    "</legend><div id=contDetaildt style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=nopp name=nopp value=" +
    nopp +
    " />";
  showDialog4(title, content, width, height, ev);
}

function insertimage(kodebarang, nopp) {
  chkimage = document.getElementsByName("chkimage[]");
  var vals = "";
  var countimage = 0;
  for (var i = 0; i < chkimage.length; i++) {
    if (chkimage[i].checked) {
      vals += "|" + chkimage[i].value;
      countimage = countimage + 1;
    }
  }
  myimage = vals.substring(1);

  param =
    "method=insertimage" +
    "&myimage=" +
    myimage +
    "&kodebarang=" +
    kodebarang +
    "&nopp=" +
    nopp;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog4();
          loadfilesuploaddt(kodebarang, nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilesuploaddt(kodebarang, nopp) {
  param =
    "method=loadfilesuploaddt" + "&kodebarang=" + kodebarang + "&nopp=" + nopp;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById(kodebarang + "_" + nopp).innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefileupload(kodebarang, nopp, namafile) {
  param =
    "method=deletefileupload" +
    "&kodebarang=" +
    kodebarang +
    "&nopp=" +
    nopp +
    "&namafile=" +
    namafile;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfilesuploaddt(kodebarang, nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function skiprph() {
  var schpt = document.getElementById("schpt").value;
  var tbl = document.getElementById("dataBarang");
  var row = tbl.rows.length;
  row = row - 1;

  strUrl = "";
  for (i = 1; i <= row; i++) {
    ar = document.getElementById("pilBrg_" + i);
    if (ar.checked == true) {
      try {
        if (strUrl != "") {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML);
        } else {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML);
        }
      } catch (e) {}
    }
  }

  if (strUrl == "") {
    alert("Choose one");
    return;
  }

  param = "method=skiprph" + "&baris=" + i;
  param += strUrl;
  tujuan = "log_slave_pnwrharga.php";

  if (confirm("Are you sure skip this item?"))
    post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadNotifikasi2(schpt);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadNotifikasi2(schpt) {
  proses = "getNotifikasi";
  param = "method=" + proses;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("notifikasiKerja").innerHTML =
            con.responseText;
          getDtPP(schpt);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addDataSma() {
  idsp =
    document.getElementById("id_supplier").options[
      document.getElementById("id_supplier").selectedIndex
    ].value;

  obj = document.getElementById("alamat");

  keterangan = document.getElementById("keterangan").value;
  lokasipengiriman = document.getElementById("lokasipengiriman").value;

  if (obj.value !== "") {
    id_alamat_supplier =
      document.getElementById("alamat").options[
        document.getElementById("alamat").selectedIndex
      ].value;
  } else {
    id_alamat_supplier = "";
  }

  var tbl = document.getElementById("dataBarang");
  var row = tbl.rows.length;
  row = row - 1;

  strUrl = "";
  for (i = 1; i <= row; i++) {
    ar = document.getElementById("pilBrg_" + i);
    if (ar.checked == true) {
      try {
        if (strUrl != "") {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML) +
            "&jmlh[]=" +
            trim(document.getElementById("jumlah_" + i).innerHTML);
        } else {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML) +
            "&jmlh[]=" +
            trim(document.getElementById("jumlah_" + i).innerHTML);
        }
      } catch (e) {}
    }
  }
  nor = document.getElementById("noUrut").value;
  notran = document.getElementById("notransaksi").value;
  param =
    "method=addData" +
    "&id_supplier=" +
    idsp +
    "&norurut=" +
    nor +
    "&id_alamat_supplier=" +
    id_alamat_supplier;
  param +=
    "&notransaksi=" +
    notran +
    "&keterangan=" +
    keterangan +
    "&lokasipengiriman=" +
    lokasipengiriman;
  param += strUrl;

  tujuan = "log_slave_pnwrharga.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          isiTran = con.responseText.split("###");
          document.getElementById("notransaksi").value = isiTran[0];
          document.getElementById("noUrut").value = isiTran[1];
          document.getElementById("keterangan").value = "";
          document.getElementById("lokasipengiriman").value = "";
          loadSupplier();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function zPreview2(fileTarget, notrans, idCont) {
  if (notrans == "") {
    notrans = document.getElementById("notransaksi").value;
  }
  param = "method=preview2" + "&notransaksi=" + notrans;

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          document.getElementById("formPP").style.display = "none";
          document.getElementById("listBrgPP").style.display = "none";
          document.getElementById("list_permintaan").style.display = "none";
          document.getElementById("listSupplier").style.display = "none";
          document.getElementById("supplierForm").style.display = "none";
          document.getElementById("formEditData2").style.display = "block";
          var res = document.getElementById(idCont);
          res.innerHTML = con.responseText;

          tipepo = document.getElementById("tipepo").value;
          if (tipepo == "SO") {
            loadmaterialso();
          }
          leftFixedTable(6);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(fileTarget + ".php", param, respon);
}

function simpanSemua2(brs, totRow) {
  no_prmntan = document.getElementById("no_prmntan_" + brs).value;
  nilDiskon = document.getElementById("angDiskon_" + brs).value;
  diskonPersen = document.getElementById("diskon_" + brs).value;
  supplierid = document.getElementById("supplierId_" + brs).value;
  ongkir = document.getElementById("ongkir_" + brs).value;
  totalongkir = document.getElementById("totalongkir_" + brs).value;
  nilPPn = document.getElementById("ppN_" + brs).value;
  nilPPh = document.getElementById("ppH_" + brs).value;
  nilPPh22 = document.getElementById("ppH_22_" + brs).value;
  pbbkb = document.getElementById("pbbkb_" + brs).value;
  //pphfinal
   pphfinal = document.getElementById("ppH_final_" + brs).value;
  nilaiPermintaan = document.getElementById("grand_total_" + brs).innerHTML;
  subTotal = document.getElementById("total_harga_po_" + brs).innerHTML;
  termPay = document.getElementById("term_pay_" + brs).options[
    document.getElementById("term_pay_" + brs).selectedIndex
  ].value;
  idFranco = document.getElementById("tmpt_krm_" + brs).options[
    document.getElementById("tmpt_krm_" + brs).selectedIndex
  ].value;
  stockId = document.getElementById("stockId_" + brs).options[
    document.getElementById("stockId_" + brs).selectedIndex
  ].value;
  ketUraian = document.getElementById("ketUraian_" + brs).value;
  mtng = document.getElementById("mtUang_" + brs).options[
    document.getElementById("mtUang_" + brs).selectedIndex
  ].value;
  krs = document.getElementById("Kurs_" + brs).value;
  tgl_batas_pnwharga = document.getElementById(
    "tgl_batas_pnwharga_" + brs
  ).value;
  waktu_penyerahan = document.getElementById("waktu_penyerahan_" + brs).value;
  tempo_pembayaran = document.getElementById(
    "tgl_tempo_pembayaran_" + brs
  ).value;

  tgldari = "";
  tglsmp = "";
  // tgldari=document.getElementById('tgl_dari_'+brs).value;
  // tglsmp=document.getElementById('tgl_smp_'+brs).value;
  nilai1s = document.getElementById("score_" + brs).value;
  nilai2s = document.getElementById("availability_" + brs).value;
  nilai3s = document.getElementById("quality_" + brs).value;
  nilai4s = document.getElementById("service_" + brs).value;
  nilai5s = document.getElementById("others_" + brs).value;
  nilai1f = document.getElementById("weightfactor1").value;
  nilai2f = document.getElementById("weightfactor2").value;
  nilai3f = document.getElementById("weightfactor3").value;
  nilai4f = document.getElementById("weightfactor4").value;
  nilai5f = document.getElementById("weightfactor5").value;

  tender_yn = document.getElementById("tender_yn");
  if (tender_yn.checked == true) {
    tender_yn_h = "1";
  } else {
    tender_yn_h = "0";
  }

  var masterial_jasa = document.getElementById("masterial_jasa");
  var masterial_jasa_h;

  if (masterial_jasa) {
    if (masterial_jasa.checked == true) {
      masterial_jasa_h = "1";
    } else {
      masterial_jasa_h = "0";
    }
  } else {
    masterial_jasa_h = "0";
  }

  var penambahpph22 = document.getElementById("penambahpph22");
  var penambahpph22_h;

  if (penambahpph22) {
    if (penambahpph22.checked == true) {
      penambahpph22_h = "1";
    } else {
      penambahpph22_h = "0";
    }
  } else {
    penambahpph22_h = "0";
  }

  durasipengiriman = document.getElementById("durasipengiriman_" + brs).value;
  durasipekerjaan = document.getElementById("durasipekerjaan_" + brs).value;
  garansiprodukjasa = document.getElementById("garansiprodukjasa_" + brs).value;
  posisistokbarang = document.getElementById("posisistokbarang_" + brs).value;
  asuransi = document.getElementById("asuransi_" + brs).value;
  if (subTotal == "0" || subTotal == "") {
    subTotal = nilDiskon = diskonPersen = nilPPn = nilPPh = nilPPh22 = pphfinal = 0;
  }

  var row = totRow + 1;
  strUrl = "";
  for (i = 1; i < row; i++) {
    try {
      if (strUrl != "") {
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).innerHTML)
          ) +
          "&merk[]=" +
          document.getElementById("merk_" + i + "_" + brs).value +
          "&qty[]=" +
          document.getElementById("qty_" + i + "_" + brs).value +
          "&price[]=" +
          document.getElementById("price_" + i + "_" + brs).value +
          "&hargaterakhir[]=" +
          document.getElementById("hargaterakhir_" + i).innerHTML +
          "&hargaestimasi[]=" +
          document.getElementById("hargaestimasi_" + i).value;
      } else {
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).innerHTML)
          ) +
          "&merk[]=" +
          document.getElementById("merk_" + i + "_" + brs).value +
          "&qty[]=" +
          document.getElementById("qty_" + i + "_" + brs).value +
          "&price[]=" +
          document.getElementById("price_" + i + "_" + brs).value +
          "&hargaterakhir[]=" +
          document.getElementById("hargaterakhir_" + i).innerHTML +
          "&hargaestimasi[]=" +
          document.getElementById("hargaestimasi_" + i).value;
      }
    } catch (e) {}
  }
  param =
    "ckno_permintaan=" +
    no_prmntan +
    "&nourut=" +
    brs +
    "&method=updateTransaksi";
  param += "&tender_yn_h=" + tender_yn_h;
  param += "&masterial_jasa_h=" + masterial_jasa_h;
  param += "&penambahpph22=" + penambahpph22_h;
  param += "&ongkir=" + ongkir + "&totalongkir=" + totalongkir;
  param +=
    "&nilDiskon=" +
    nilDiskon +
    "&diskonPersen=" +
    diskonPersen +
    "&nilPPh=" +
    nilPPh +
    "&nilPPh22=" +
    nilPPh22 +
    "&pphfinal=" +
    pphfinal +
    "&nilPPn=" +
    nilPPn +
    "&pbbkb=" +
    pbbkb +
    "&nilaiPermintaan=" +
    nilaiPermintaan;
  param +=
    "&subTotal=" +
    subTotal +
    "&termPay=" +
    termPay +
    "&idFranco=" +
    idFranco +
    "&stockId=" +
    stockId +
    "&ketUraian=" +
    ketUraian;
  param +=
    "&tglDari=" +
    tgldari +
    "&tglSmp=" +
    tglsmp +
    "&mtUang=" +
    mtng +
    "&kurs=" +
    krs +
    "&supplierid=" +
    supplierid;
  param +=
    "&durasipengiriman=" +
    durasipengiriman +
    "&durasipekerjaan=" +
    durasipekerjaan +
    "&garansiprodukjasa=" +
    garansiprodukjasa +
    "&posisistokbarang=" +
    posisistokbarang +
    "&asuransi=" +
    asuransi;
  param +=
    "&nilai1s=" +
    nilai1s +
    "&nilai2s=" +
    nilai2s +
    "&nilai3s=" +
    nilai3s +
    "&nilai4s=" +
    nilai4s +
    "&nilai5s=" +
    nilai5s;
  param +=
    "&nilai1f=" +
    nilai1f +
    "&nilai2f=" +
    nilai2f +
    "&nilai3f=" +
    nilai3f +
    "&nilai4f=" +
    nilai4f +
    "&nilai5f=" +
    nilai5f +
    "&tgl_batas_pnwharga=" +
    tgl_batas_pnwharga +
    "&tempo_pembayaran=" +
    tempo_pembayaran +
    "&waktu_penyerahan=" +
    waktu_penyerahan;
  param += strUrl;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.alert("Done");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//### BEGIN FORMAT NUMBER ###
nilPpn = 0;
function normal_number(id) {
  satu = document.getElementById("harga_satuan_" + id);
  satu.value = remove_comma(satu);
}

function calculate(id, row, totRow) {
  jlhtender = document.getElementById("jlhtender").value;
  jlhbrg = document.getElementById("qty_" + id + "_" + row).value;

  jmlh_brg = remove_comma(document.getElementById("qty_" + id + "_" + row));

  for (var i = 1; i <= jlhtender; i++) {
    document.getElementById("qty_" + id + "_" + i).value = jlhbrg;
    harga = remove_comma(document.getElementById("price_" + id + "_" + i));

    jmlh_sub = parseFloat(jmlh_brg) * parseFloat(harga);

    if (isNaN(jmlh_sub)) {
      document.getElementById("total_" + id + "_" + i).value = 0;
    } else {
      as = document.getElementById("total_" + id + "_" + i);
      as.value = jmlh_sub;
      change_number(as);
    }

    grnd_total(i, totRow);
    calculate_varianharga(i, totRow);
  }
}
function calculate_varianharga(brs, totRow) {
  row = totRow + 1;
  total = 0;
  harga = 0;
  jmlh_sub = 0;
  hargaterakhir = 0;

  for (i = 1; i < row; i++) {
    // jlhitem=parseFloat(remove_comma(document.getElementById('qty_'+i+'_'+brs)));
    // jmlh_sub = parseFloat(jlhitem) * parseFloat(harga);
    harga = remove_comma(document.getElementById("price_" + i + "_" + brs));
    jmlh_sub = parseFloat(harga);
    if (isNaN(jmlh_sub)) {
      jmlh_sub = 0;
    }

    // b=document.getElementById('hargaterakhir_'+brs);
    // b.value=remove_comma_var(b.value);
    // hargaterakhir=remove_comma(document.getElementById('hargaterakhir_'+i));
    // change_number(hargaterakhir);
    // return false;
    hargaterakhir = remove_comma(
      document.getElementById("hargaterakhirv2_" + i)
    );
    // hargaterakhir=remove_comma(document.getElementById('hargaterakhir_1'));
    if (isNaN(hargaterakhir)) {
      hargaterakhir = 0;
    }
    total = jmlh_sub - parseFloat(hargaterakhir);
    if (isNaN(total)) {
      total = 0;
    }
    document.getElementById("varianharga_" + i + "_" + brs).value =
      total.formatMoney(2);
  }
  calculate_all_j(brs);
}

function calculate_all_j(brs) {
  // CEK TIPE PO
  tipepo = document.getElementById("tipepo").value;

  if (brs == "kosong") {
    masterial_jasa = document.getElementById("masterial_jasa");
    if (
      document.getElementById("masterial_jasa") &&
      masterial_jasa.checked == false
    ) {
      tipepo = "SOO";
    }

    // ambil nilai material
    var elementsppN_ = document.querySelectorAll('[id^="ppN_"]');
    var jumlahpppN_ = elementsppN_.length;
    for (let index = 1; index <= jumlahpppN_; index++) {
      document.getElementById("ppn_" + index).value = "0";
      document.getElementById("ppN_" + index).value = "0";
    }
  }

  masterial_jasa = document.getElementById("masterial_jasa");
  if (
    document.getElementById("masterial_jasa") &&
    masterial_jasa.checked == false
  ) {
    tipepo = "SOO";
  }
  var penambahpph22 = document.getElementById("penambahpph22");
  var pph_penambah;

  if (penambahpph22) {
    if (penambahpph22.checked == true) {
      pph_penambah = "1";
    } else {
      pph_penambah = "0";
    }
  } else {
    pph_penambah = "0";
  }

  if (brs == "kosong_pph22") {
    // ambil nilai material
    var elementsppN_ = document.querySelectorAll('[id^="ppH_22_"]');
    var jumlahpppN_ = elementsppN_.length;
    for (let index = 1; index <= jumlahpppN_; index++) {
      document.getElementById("ppH_22_" + index).value = "0";
      document.getElementById("pph_22_" + index).value = "0";
    }
  }

  // subtotal
  sb_tot = document.getElementById("total_harga_po_" + brs).innerHTML;
  sb_tot = sb_tot.replace(/,/g, "");
  if (sb_tot == "") {
    sb_tot = 0;
  }
  // ambil nilai material
  var elementspajak = document.querySelectorAll('[id^="dataSO_"]');
  var jumlahpajak = elementspajak.length;
  materialSO_harga = 0;
  for (let index = 1; index <= jumlahpajak; index++) {
    // cek material
    materialSO = document.getElementById("totalso_" + index + "_" + brs);
    materialSO.value = remove_comma_var(materialSO.value);
    materialSO_harga += parseFloat(materialSO.value);

    if (isNaN(materialSO_harga)) {
      materialSO_harga = 0;
    }
  }

  // angka nilai diskon
  disc = 0;
  angDiskon = 0;
  nil_dis = 0;
  subTot_dikurangMaterial = 0;
  nil_dis = document.getElementById("diskon_" + brs).value;
  angDiskon = document.getElementById("angDiskon_" + brs).value;
  subTot_dikurangMaterial = parseFloat(sb_tot) - parseFloat(materialSO_harga);

  if (nil_dis == 0 || angDiskon == 0) {
    document.getElementById("angDiskon_" + brs).disabled = false;
    document.getElementById("diskon_" + brs).disabled = false;
  }

  if (nil_dis != 0 || angDiskon != 0) {
    document.getElementById("angDiskon_" + brs).disabled = false;
    if (nil_dis > 100) {
      alert("Discount must lower than 100%");
      document.getElementById("diskon_" + brs).value = "";
      document.getElementById("angDiskon_" + brs).value = "";
      document.getElementById("angDiskon_" + brs).disabled = false;
      disc = 0;
    } else {
      disc = (nil_dis * parseFloat(subTot_dikurangMaterial)) / 100;
    }
    nilaiDis = document.getElementById("angDiskon_" + brs);
    nilaiDis.value = disc;
    change_number(nilaiDis);
    z.numberFormat("angDiskon_" + brs, 2);
  }

  // angDiskon=document.getElementById('angDiskon_'+brs).value;
  // angDiskon = angDiskon.replace(/,/g, "");
  // if(angDiskon=='')
  // {
  // 	angDiskon=0;
  // }
  if (typeof nilaiDis !== "undefined" && nilaiDis !== null) {
    // Jika nilaiDis didefinisikan
    nilaiDis.value = nilaiDis.value.replace(/,/g, "");
    if (nilaiDis.value === "") {
      nilaiDis.value = 0;
    }
  } else {
    // Jika nilaiDis tidak didefinisikan, berikan nilai default 0
    nilaiDis = document.createElement("input");
    nilaiDis.type = "text";
    nilaiDis.value = 0;
  }

  // PPN
  ppn = document.getElementById("ppn_" + brs).value;
  ppn = ppn.replace(/,/g, "");
  if (ppn == "") {
    ppn = 0;
  }
  // var reg = /^[0-9]{1,2}$/;
  var reg = /^[0-9]{1,3}(,[0-9]{3})*(\.[0-9]{1,2})?$/;
  nilPpn = document.getElementById("ppN_" + brs).value;

  if (reg.test(nilPpn)) {
    if (tipepo == "SOO") {
      if (nilPpn == 10) {
        pn1 = (parseFloat(subTot_dikurangMaterial - nilaiDis.value) * 10) / 100;
        if (isNaN(pn1)) {
          pn1 = 0;
        }

        as = document.getElementById("ppn_" + brs);
        as.value = pn1;
        change_number(as);
      } else if (nilPpn == 0) {
        pn1 =
          (parseFloat(subTot_dikurangMaterial - nilaiDis.value) * nilPpn) / 100;
        as = document.getElementById("ppn_" + brs);
        as.value = pn1;
        change_number(as);
      } else if (nilPpn == 11) {
        pn1 =
          (parseFloat(subTot_dikurangMaterial - nilaiDis.value) * nilPpn) / 100;
        if (isNaN(pn1)) {
          pn1 = 0;
        }
        as = document.getElementById("ppn_" + brs);
        as.value = pn1;
        change_number(as);
      } else {
        pn1 =
          (parseFloat(subTot_dikurangMaterial - nilaiDis.value) * nilPpn) / 100;
        if (isNaN(pn1)) {
          pn1 = 0;
        }
        as = document.getElementById("ppn_" + brs);
        as.value = pn1;
        change_number(as);
      }
    } else {
      pn1 = (parseFloat(sb_tot - nilaiDis.value) * nilPpn) / 100;
      if (isNaN(pn1)) {
        pn1 = 0;
      }
      as = document.getElementById("ppn_" + brs);
      as.value = pn1;
      change_number(as);
    }
  } else {
    document.getElementById("ppn_" + brs).value = "0";
    document.getElementById("ppN_" + brs).value = "0";
  }

  // PPH 22
  pph = document.getElementById("pph_22_" + brs).value;
  pph = pph.replace(/,/g, "");
  if (pph == "") {
    pph = 0;
  }
  // var reg = /^[0-9]{1,2}$/;
  var reg = /^[0-9]{1,3}(,[0-9]{3})*(\.[0-9]{1,2})?$/;
  nilP = document.getElementById("ppH_22_" + brs).value;

  if (reg.test(nilP)) {
    pn_22 = (parseFloat(subTot_dikurangMaterial - nilaiDis.value) * nilP) / 100;
    if (isNaN(pn_22)) {
      pn_22 = 0;
    }
    as = document.getElementById("pph_22_" + brs);
    as.value = pn_22;
    change_number(as);
  } else {
    document.getElementById("pph_22_" + brs).value = "0";
    document.getElementById("ppH_22_" + brs).value = "0";
  }

  // PPH
  pph = document.getElementById("pph_" + brs).value;
  pph = pph.replace(/,/g, "");
  if (pph == "") {
    pph = 0;
  }
  // var reg = /^[0-9]{1,2}$/;
  var reg = /^[0-9]{1,3}(,[0-9]{3})*(\.[0-9]{1,2})?$/;
  nilP = document.getElementById("ppH_" + brs).value;

  if (reg.test(nilP)) {
    pn = (parseFloat(subTot_dikurangMaterial - nilaiDis.value) * nilP) / 100;
    if (isNaN(pn)) {
      pn = 0;
    }
    as = document.getElementById("pph_" + brs);
    as.value = pn;
    change_number(as);
  } else {
    document.getElementById("pph_" + brs).value = "0";
    document.getElementById("ppH_" + brs).value = "0";
  }

  pbbkb = document.getElementById("pbbkb_" + brs).value;
  pbbkb = pbbkb.replace(/,/g, "");
  if (pbbkb == "") {
    pbbkb = 0;
  }

  //PPH FINAL
  pphfinal = document.getElementById("ppH_final_" + brs).value;
  pphfinal = pphfinal.replace(/,/g, "");
  if (pphfinal == "") {
    pphfinal = 0;
  }

  if (pph_penambah == "1") {
    total =
      parseFloat(sb_tot) -
      parseFloat(nilaiDis.value) +
      parseFloat(pn1) +
      parseFloat(pbbkb) +
      parseFloat(pphfinal) -
      parseFloat(pn_22) -
      parseFloat(pn);
  } else {
    total =
      parseFloat(sb_tot) -
      parseFloat(nilaiDis.value) +
      parseFloat(pn1) +
      parseFloat(pbbkb) -
      parseFloat(pn_22) -
      parseFloat(pphfinal) -
      parseFloat(pn);
  }

  document.getElementById("grand_total_" + brs).innerHTML =
    total.formatMoney(2);
}
function calculate_all(brs) {
  sb_tot = document.getElementById("total_harga_po_" + brs).innerHTML;
  sb_tot = sb_tot.replace(/,/g, "");
  if (sb_tot == "") {
    sb_tot = 0;
  }

  calculate_angDiskon2(brs);
  angDiskon = document.getElementById("angDiskon_" + brs).value;
  angDiskon = angDiskon.replace(/,/g, "");
  if (angDiskon == "") {
    angDiskon = 0;
  }

  ppn = document.getElementById("ppn_" + brs).value;
  ppn = ppn.replace(/,/g, "");
  if (ppn == "") {
    ppn = 0;
  }
  calculatePpn(brs);

  pph = document.getElementById("pph_" + brs).value;
  pph = pph.replace(/,/g, "");
  if (pph == "") {
    pph = 0;
  }
  calculatePph(brs);

  pbbkb = document.getElementById("pbbkb_" + brs).value;
  pbbkb = pbbkb.replace(/,/g, "");
  if (pbbkb == "") {
    pbbkb = 0;
  }

  total =
    parseFloat(sb_tot) -
    parseFloat(angDiskon) +
    parseFloat(ppn) +
    parseFloat(pbbkb) -
    parseFloat(pph);

  document.getElementById("grand_total_" + brs).innerHTML =
    total.formatMoney(2);
  calculate_all_j(brs);
}

function calculate_all2(brs) {
  sb_tot = document.getElementById("total_harga_po_" + brs).innerHTML;
  sb_tot = sb_tot.replace(/,/g, "");
  if (sb_tot == "") {
    sb_tot = 0;
  }

  angDiskon = document.getElementById("angDiskon_" + brs).value;
  angDiskon = angDiskon.replace(/,/g, "");
  if (angDiskon == "") {
    angDiskon = 0;
  }

  ppn = document.getElementById("ppn_" + brs).value;
  ppn = ppn.replace(/,/g, "");
  if (ppn == "") {
    ppn = 0;
  }
  calculatePpn(brs);

  pbbkb = document.getElementById("pbbkb_" + brs).value;
  pbbkb = pbbkb.replace(/,/g, "");
  if (pbbkb == "") {
    pbbkb = 0;
  }

  total =
    parseFloat(sb_tot) -
    parseFloat(angDiskon) +
    parseFloat(ppn) +
    parseFloat(pbbkb);

  document.getElementById("grand_total_" + brs).innerHTML =
    total.formatMoney(2);
}

function grnd_total(brs, totRow) {
  row = totRow + 1;
  total = 0;
  jlhitem = 0;

  var elementspajak = document.querySelectorAll('[id^="dataSO_"]');
  var jumlahpajak = elementspajak.length;
  materialSO_harga = 0;
  for (i = 1; i < row; i++) {
    jlhitem += parseFloat(
      remove_comma(document.getElementById("qty_" + i + "_" + brs))
    );
    if (isNaN(jlhitem)) {
      jlhitem = 0;
    }

    // ambil nilai material
    for (let index = 1; index <= jumlahpajak; index++) {
      // cek material
      materialSO = document.getElementById("totalso_" + index + "_" + brs);
      materialSO.value = remove_comma_var(materialSO.value);
      materialSO_harga += parseFloat(materialSO.value);

      if (isNaN(materialSO_harga)) {
        materialSO_harga = 0;
      }
    }

    b = document.getElementById("total_" + i + "_" + brs);
    b.value = remove_comma_var(b.value);
    total += parseFloat(b.value);
    change_number(b);
    if (isNaN(total)) {
      total = 0;
    }
  }

  ongkir = remove_comma(document.getElementById("ongkir_" + brs));
  totalongkir = parseFloat(jlhitem) * parseFloat(ongkir);
  if (totalongkir > 0) {
    document.getElementById("totalongkir_" + brs).value =
      totalongkir.formatMoney(2);
  } else {
    document.getElementById("totalongkir_" + brs).value = 0;
  }

  total = parseFloat(total) + parseFloat(totalongkir) + materialSO_harga;
  document.getElementById("total_harga_po_" + brs).innerHTML =
    total.formatMoney(2);
  // calculate_all(brs);
  calculate_all_j(brs);
}

function grandTotal(brs) {
  sb_tot = document.getElementById("total_harga_po_" + brs).innerHTML;
  sb_tot = sb_tot.replace(/,/g, "");
  grnd_tot = sb_tot;

  total = document.getElementById("grand_total_" + brs);
  total.innerHTML = grnd_tot;
}

function calculate_angDiskon2(brs) {
  nilDis = document.getElementById("angDiskon_" + brs).value;
  nilDis = nilDis.replace(/,/g, "");

  document.getElementById("diskon_" + brs).disabled = false;
  subTot = document.getElementById("total_harga_po_" + brs).innerHTML;
  subTot = subTot.replace(/,/g, "");
  if (nilDis != subTot) {
    persenDis = parseFloat(nilDis / subTot) * 100;
  }

  if (persenDis < 100) {
    document.getElementById("diskon_" + brs).value = persenDis;
    z.numberFormat("diskon_" + brs, 2);
  } else {
    // alert("Discount too large");
    document.getElementById("angDiskon_" + brs).value = "0";
    document.getElementById("diskon_" + brs).value = "0";
    document.getElementById("diskon_" + brs).disabled = false;
  }
}

function calculate_diskon(brs) {
  var reg = /^[0-9]{1,2}$/;
  sb_tot = document.getElementById("total_harga_po_" + brs).innerHTML;
  sb_tot = sb_tot.replace(/,/g, "");
  nil_dis = document.getElementById("diskon_" + brs).value;
  angk = document.getElementById("angDiskon_" + brs).value;

  // if(reg.test(nil_dis))
  // {
  if (nil_dis == 0 || angk == 0) {
    document.getElementById("angDiskon_" + brs).disabled = false;
    document.getElementById("diskon_" + brs).disabled = false;
  }

  if (nil_dis != 0 || angk != 0) {
    document.getElementById("angDiskon_" + brs).disabled = false;
    if (nil_dis > 100) {
      alert("Discount must lower than 100%");
      document.getElementById("diskon_" + brs).value = "";
      document.getElementById("angDiskon_" + brs).value = "";
      document.getElementById("angDiskon_" + brs).disabled = false;
      disc = 0;
    } else {
      disc = (nil_dis * parseFloat(sb_tot)) / 100;
    }

    nilaiDis = document.getElementById("angDiskon_" + brs);
    nilaiDis.value = disc;
    change_number(nilaiDis);
    z.numberFormat("angDiskon_" + brs, 2);
  }
  // }
  // else
  // {
  // alert("Valid 0 to 100 only");
  // document.getElementById('diskon_'+brs).value='0';
  // document.getElementById('angDiskon_'+brs).value='0';
  // }
  calculate_all2(brs);
}

function calculate_angDiskon(brs) {
  nilDis = document.getElementById("angDiskon_" + brs).value;
  nilDis = nilDis.replace(/,/g, "");
  if (nilDis != 0) {
    document.getElementById("diskon_" + brs).disabled = false;
    subTot = document.getElementById("total_harga_po_" + brs).innerHTML;
    subTot = subTot.replace(/,/g, "");
    if (nilDis != subTot) {
      persenDis = parseFloat(nilDis / subTot) * 100;
    }

    if (persenDis < 100) {
      // persen=Math.ceil(persenDis);
      document.getElementById("diskon_" + brs).value = persenDis;
      z.numberFormat("diskon_" + brs, 2);
    } else {
      alert("Discount too large");
      document.getElementById("angDiskon_" + brs).value = "";
      document.getElementById("diskon_" + brs).value = "";
      document.getElementById("diskon_" + brs).disabled = false;
    }
  } else if (nilDis == 0) {
    document.getElementById("diskon_" + brs).disabled = false;
  }
  calculate_all(brs);
}

function calculatePpn(brs) {
  var reg = /^[0-9]{1,2}$/;
  nilP = document.getElementById("ppN_" + brs).value;
  dis = document.getElementById("angDiskon_" + brs).value;
  dis = dis.replace(/,/g, "");
  subTot = document.getElementById("total_harga_po_" + brs).innerHTML;
  subTot = subTot.replace(/,/g, "");

  if (reg.test(nilP)) {
    if (nilP == 10) {
      pn = (parseFloat(subTot - dis) * 10) / 100;
      if (isNaN(pn)) {
        pn = 0;
      }

      as = document.getElementById("ppn_" + brs);
      as.value = pn;
      change_number(as);
    } else if (nilP == 0) {
      pn = (parseFloat(subTot - dis) * nilP) / 100;
      as = document.getElementById("ppn_" + brs);
      as.value = pn;
      change_number(as);
    } else if (nilP == 2) {
      pn = (parseFloat(subTot - dis) * nilP) / 100;
      if (isNaN(pn)) {
        pn = 0;
      }
      as = document.getElementById("ppn_" + brs);
      as.value = pn;
      change_number(as);
    } else {
      pn = (parseFloat(subTot - dis) * nilP) / 100;
      if (isNaN(pn)) {
        pn = 0;
      }
      as = document.getElementById("ppn_" + brs);
      as.value = pn;
      change_number(as);
    }
  } else {
    document.getElementById("ppn_" + brs).value = "0";
    document.getElementById("ppN_" + brs).value = "0";
  }
}

function validasippn(brs) {
  ppn = document.getElementById("ppN_" + brs).value;
  if (ppn <= 0) {
    // if(ppn!=11)
    // {
    // 	if(ppn!=10)
    // 	{
    document.getElementById("ppn_" + brs).value = "0";
    document.getElementById("ppN_" + brs).value = "0";
    // 	}
    // }
  }
  // calculate_all(brs);
  calculate_all_j(brs);
}

function calculatePph(brs) {
  var reg = /^[0-9]{1,2}$/;
  nilP = document.getElementById("ppH_" + brs).value;
  dis = document.getElementById("angDiskon_" + brs).value;
  dis = dis.replace(/,/g, "");
  subTot = document.getElementById("total_harga_po_" + brs).innerHTML;
  subTot = subTot.replace(/,/g, "");

  if (reg.test(nilP)) {
    if (nilP == 10) {
      pn = (parseFloat(subTot - dis) * 10) / 100;
      if (isNaN(pn)) {
        pn = 0;
      }

      as = document.getElementById("pph_" + brs);
      as.value = pn;
      change_number(as);
    } else if (nilP == 0) {
      pn = (parseFloat(subTot - dis) * nilP) / 100;
      as = document.getElementById("pph_" + brs);
      as.value = pn;
      change_number(as);
    } else if (nilP == 2) {
      pn = (parseFloat(subTot - dis) * nilP) / 100;
      if (isNaN(pn)) {
        pn = 0;
      }
      as = document.getElementById("pph_" + brs);
      as.value = pn;
      change_number(as);
    }
  } else {
    document.getElementById("pph_" + brs).value = "0";
    document.getElementById("ppH_" + brs).value = "0";
  }
}

function validasipph(brs) {
  pph = document.getElementById("ppH_" + brs).value;
  if (pph != 0) {
    if (pph != 2) {
      if (pph != 10) {
        document.getElementById("pph_" + brs).value = "0";
        document.getElementById("ppH_" + brs).value = "0";
      }
    }
  }
  // calculate_all(brs);
  calculate_all_j(brs);
}
//### ENDFORMAT NUMBER ###

function addfile(notransaksi, supplierid) {
  var kriteriaefil = document.getElementById(
    "kriteriaefil_" + notransaksi + "_" + supplierid
  ).value;
  var file = document.getElementById("upload_" + notransaksi + "_" + supplierid)
    .files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append(
    "fileupload",
    getValue("upload_" + notransaksi + "_" + supplierid)
  );
  formdata.append("notransaksi", notransaksi);
  formdata.append("supplierid", supplierid);
  formdata.append("kriteriaefil", kriteriaefil);

  if (getValue("upload_" + notransaksi + "_" + supplierid) == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_pnwrharga.php?method=submitfile", true);
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
          alert("Uploaded Success.");
          loadfilesrph(notransaksi, supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addfile2(notransaksi, supplierid) {
  var kriteriaefil = document.getElementById(
    "kriteriaefil_" + notransaksi + "_" + supplierid
  ).value;
  var file = document.getElementById("upload_" + notransaksi + "_" + supplierid)
    .files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append(
    "fileupload",
    getValue("upload_" + notransaksi + "_" + supplierid)
  );
  formdata.append("notransaksi", notransaksi);
  formdata.append("supplierid", supplierid);
  formdata.append("kriteriaefil", kriteriaefil);

  if (getValue("upload_" + notransaksi + "_" + supplierid) == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "log_slaveUploadPO.php?method=submitfile", true);
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
          alert("Uploaded Success.");
          loadfilesrph2(notransaksi, supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilesrph(notransaksi, supplierid) {
  param =
    "method=loadfiles&notransaksi=" + notransaksi + "&supplierid=" + supplierid;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          console.log(document.getElementById("iframe_uploadpo"));
          document.getElementById(
            "listfiles_" + notransaksi + "_" + supplierid
          ).innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilesrph2(notransaksi, supplierid) {
  param =
    "method=loadfiles&notransaksi=" + notransaksi + "&supplierid=" + supplierid;
  tujuan = "log_slaveUploadPO.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById(
            "listfiles_" + notransaksi + "_" + supplierid
          ).innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile2(notransaksi, supplierid, namafile) {
  param =
    "method=deletefile&notransaksi=" +
    notransaksi +
    "&supplierid=" +
    supplierid +
    "&namafile=" +
    namafile;
  tujuan = "log_slaveUploadPO.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfilesrph2(notransaksi, supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(notransaksi, supplierid, namafile) {
  param =
    "method=deletefile&notransaksi=" +
    notransaksi +
    "&supplierid=" +
    supplierid +
    "&namafile=" +
    namafile;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfilesrph(notransaksi, supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addSupplierPlus(notr, nour) {
  document.getElementById("formPP").style.display = "block";
  document.getElementById("listBrgPP").style.display = "none";
  param = "method=listBarangDetail";

  param += "&notransaksi=" + notr + "&nourut=" + nour;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("dataBarang").innerHTML = con.responseText;
          document.getElementById("formPP").style.display = "block";
          document.getElementById("listBrgPP").style.display = "none";
          document.getElementById("list_permintaan").style.display = "none";
          document.getElementById("listSupplier").style.display = "none";
          document.getElementById("supplierForm").style.display = "block";
          document.getElementById("notransaksi").value = notr;
          nour = parseInt(nour) + 1;
          document.getElementById("noUrut").value = nour;
          loadSupplier();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function delPer1(no_per, nourut) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;

  param = "no_permintaan=" + no_per + "&nourut=" + nourut;
  param += "&method=deleted";
  tujuan = "log_slave_pnwrharga.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Delete successfull");
          loaddata(paged);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  if (confirm("Delete, are you sure?"))
    post_response_text(tujuan, param, respog);
}

function alasan_batal(nomor, nourut) {
  param =
    "no_permintaan=" + nomor + "&nourut=" + nourut + "&method=get_alasan_batal";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (con.responseText == "") {
            postingrfq(nomor, nourut, "0");
          } else {
            if (
              confirm(
                "Kuantitas item berikut tidak sesuai dengan PR/SR : " +
                  con.responseText +
                  "\nAkan menjadi Out Standing RFQ, apakah Anda yakin tetap mengajukan RFQ?"
              )
            ) {
              postingrfq(nomor, nourut, "1");
            }
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);
}

function postingrfq(nomor, nourut, createpr) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;

  param =
    "no_permintaan=" +
    nomor +
    "&nourut=" +
    nourut +
    "&createpr=" +
    createpr +
    "&method=postingrfq";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Berhasil diajukan.");
          loaddata(paged);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);
}

function loadSupplier() {
  notrans = document.getElementById("notransaksi").value;
  param = "method=loadSuppier" + "&notrans=" + notrans;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listHasilSave").innerHTML = con.responseText;
          lanjutAddx();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function lanjutAddx() {
  var tbl = document.getElementById("dataBarang");
  var row = tbl.rows.length;
  row = row - 1;

  strUrl = "";
  for (i = 1; i <= row; i++) {
    ar = document.getElementById("pilBrg_" + i);
    if (ar.checked == true) {
      try {
        if (strUrl != "") {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML);
        } else {
          strUrl +=
            "&kdbrg[]=" +
            trim(document.getElementById("kodebrg_" + i).innerHTML) +
            "&lstnopp[]=" +
            trim(document.getElementById("nopplst_" + i).innerHTML);
        }
      } catch (e) {}
    }
  }

  if (strUrl == "") {
    alert("Choose one");
    return;
  }

  param = "method=cekBarang" + "&baris=" + i;
  param += strUrl;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listBrgPP").style.display = "none";
          document.getElementById("listSupplier").style.display = "none";
          document.getElementById("supplierForm").style.display = "block";
          setValue2("id_supplier", "");
          setValue2("alamat", "");
          document.getElementById("detailbarang").innerHTML = con.responseText;
          loadNotifikasi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function delPer(no_per, nourut) {
  param = "no_permintaan=" + no_per + "&nourut=" + nourut;
  param += "&method=deletedsup";
  tujuan = "log_slave_pnwrharga.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Delete successfull");
          loadSupplier();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  if (confirm("Delete, are you sure?"))
    post_response_text(tujuan, param, respog);
}

function sendEmailPer(no_per, nourut) {
  param = "no_permintaan=" + no_per + "&nourut=" + nourut;
  param += "&method=sendemailper";
  tujuan = "log_slave_pnwrharga.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert('Kirim email ke supplier berhasil');
          //loadSupplier();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  if (confirm("Kirim Email, Anda yakin?"))
    post_response_text(tujuan, param, respog);
}
function kirimPer(no_per, nourut) {
  document.getElementById("sttkrmk_" + no_per + "_" + nourut).style.display =
    "none";
  param = "no_permintaan=" + no_per + "&nourut=" + nourut;
  param += "&method=kirimPer";
  tujuan = "log_slave_pnwrharga.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          try {
            alert("Berhasil, kirim email");
            // var ev = this.event || window.event;
            // var data = JSON.parse(con.responseText);
            // title = "Send email";
            // width='500';
            // height='400';
            // content = "<fieldset><legend>" + no_per + "</legend><div id=contDetaildt style='overflow:auto;width:auto;height:auto;' ><ul><li><a href='"+data.src+"' download>"+data.namefile+"</a></li></ul><button onclick=\"sendEmailPer('"+no_per+"','"+nourut+"');\">Send Email</button></div></fieldset>";
            // showDialog1(title, content, width, height, ev);
          } catch (e) {
            console.log(e);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function getBarangCari() {
  klmpKbrg =
    document.getElementById("schklbrg").options[
      document.getElementById("schklbrg").selectedIndex
    ].value;
  param = "method=loadBarang" + "&klmpKbrg=" + klmpKbrg;
  tujuan = "log_slave_save_verivikasi.php";
  //alert(param);
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("schkdbrg").innerHTML = con.responseText;
          //return con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function displayFormInput() {
  document.getElementById("formPP").style.display = "block";
  document.getElementById("list_permintaan").style.display = "none";
  //document.getElementById('formEditData2').style.display='none';
  //document.getElementById('nopp').value='';
  document.getElementById("listBrgPP").style.display = "none";
  //document.getElementById('tmblGetpp').disabled=false;
  document.getElementById("formPP2").style.display = "none";
  //document.getElementById('printContainer').innerHTML='';
  document.getElementById("listBrgPP").style.display = "none";
  document.getElementById("listSupplier").style.display = "none";
  document.getElementById("dataBarang").innerHTML = "";
  document.getElementById("supplierForm").style.display = "none";
  document.getElementById("listHasilSave").innerHTML = "";
  //document.getElementById('excelBtn').style.display='none';
  document.getElementById("noUrut").value = "";
}

function displayFormEdit() {
  document.getElementById("formPP2").style.display = "block";
  document.getElementById("list_permintaan").style.display = "none";
  //document.getElementById('nopp').value='';
  document.getElementById("listBrgPP").style.display = "none";
  //document.getElementById('tmblGetpp').disabled=false;
  //document.getElementById('formEditData').style.display='none';
  document.getElementById("printContainer").innerHTML = "";
  document.getElementById("formPP").style.display = "none";
  document.getElementById("formEditData2").style.display = "none";
}
function searchSupplier(title, content, ev) {
  width = "";
  height = "";
  showDialog1(title, content, width, height, ev);
}
function searchNopp(title, content, ev) {
  width = "500";
  height = "400";
  showDialog2(title, content, width, height, ev);
}

function clikcAll() {
  drt = document.getElementById("dtSemua");
  if (drt.checked == true) {
    chk = true;
  } else {
    chk = false;
  }
  var tbl = document.getElementById("dataBarang");
  var row = tbl.rows.length;
  row = row - 1;

  for (i = 1; i <= row; i++) {
    var cb = document.getElementById("pilBrg_" + i);
    if (cb) {
      if (chk) {
        var nopp = document.getElementById("nopplst_" + i).innerHTML;
        if (nopp.indexOf("/SR/") === -1) {
          cb.checked = true;
        } else {
          cb.checked = false;
        }
      } else {
        cb.checked = false;
      }
    }
  }
}
function findSupplier() {
  nmSupplier = document.getElementById("nmSupplier").value;
  param = "method=getSupplierNm" + "&nmSupplier=" + nmSupplier;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerSupplier").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function lanjutAdd2() {
  document.getElementById("listSupplier").style.display = "none";
  document.getElementById("supplierForm").style.display = "block";
}

function slsiSma() {
  if (confirm("Finish, are you sure?"))
    document.getElementById("listBrgPP").style.display = "none";
  document.getElementById("listSupplier").style.display = "none";
  document.getElementById("tmblGetpp").disabled = false;
  document.getElementById("supplierForm").style.display = "none";
  zPreview(
    "log_slave_2perbandingan_harga",
    "##nopp##formPil",
    "printContainer"
  );
  //displayList();
}
function findNopp() {
  kdNopp = document.getElementById("kdNopp").value;
  param = "method=getNopp" + "&kdNopp=" + kdNopp;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerNopp").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function setData(kdSupp) {
  l = document.getElementById("id_supplier");

  for (a = 0; a < l.length; a++) {
    if (l.options[a].value == kdSupp) {
      l.options[a].selected = true;
    }
  }
  closeDialog();
  getalamat();
}
function findNopp2() {
  kdNopp = document.getElementById("kdNopp").value;
  param = "method=getNopp2" + "&kdNopp=" + kdNopp;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerNopp").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function setDataNopp(brNopp) {
  document.getElementById("nopp").value = brNopp;
  closeDialog2();
}

function headher_permintaan() {
  nm_supp =
    document.getElementById("id_supplier").options[
      document.getElementById("id_supplier").selectedIndex
    ].value;
  //nopp=document.getElementById('nopp').options[document.getElementById('nopp').selectedIndex].value;

  nopp = document.getElementById("nopp").value;
  term_pay =
    document.getElementById("term_pay").options[
      document.getElementById("term_pay").selectedIndex
    ].value;
  tmpt_krm =
    document.getElementById("tmpt_krm").options[
      document.getElementById("tmpt_krm").selectedIndex
    ].value;
  stockId =
    document.getElementById("stockId").options[
      document.getElementById("stockId").selectedIndex
    ].value;

  if (nm_supp == "") {
    alert("Please select supplier");
    return;
  } else if (nopp == "") {
    alert("PR no is empty");
    return;
  } else if (term_pay == "") {
    alert("Payment term is empty");
    return;
  } else if (tmpt_krm == "") {
    alert("Delivery location required");
    return;
  } else if (stockId == "") {
    alert("Stock is empty");
    return;
  }
  //tmpt_krm
  //stockId
  else {
    document.getElementById("dtHeader").style.display = "none";
    //document.getElementById('tmbl_save').disabled=true;
    //document.getElementById('tmbl_cancel').disabled=true;
    document.getElementById("form_permintaan").style.display = "block";
    document.getElementById("tmbl_all").style.display = "block";
    met = document.getElementById("method").value = "create_no";
    param = "method=" + met;
    //alert(param);
    tujuan = "log_slave_save_permintaan_harga.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            //alert(con.responseText);
            document.getElementById("no_prmntan").value = con.responseText;
            document.getElementById("method").value = "insert";
            document.getElementById("detailTable").style.display = "block";

            document.getElementById("formDetailIsian").style.display = "block";

            pass2detail(1);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    post_response_text(tujuan, param, respog);
  }
}

function pass2detail(c) {
  if (c == 1) {
    var kode = document.getElementById("nopp");
    idPer = document.getElementById("no_prmntan").value;
    param = "id=" + kode.value + "&saveStat=" + c + "&idPer=" + idPer;
    param += "&proses=createTable";
  } else {
    var kode = document.getElementById("no_prmntan");
    param = "id=" + kode.value;
    param += "&proses=createTable";
  }
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          var detailDiv = document.getElementById("detailTable");
          detailDiv.innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text("log_slave_permintaan_detail.php", param, respon);
}
function searchBrg(title, content, ev) {
  width = "500";
  height = "400";
  showDialog1(title, content, width, height, ev);
  //alert('asdasd');
}
function findBrg() {
  txt = trim(document.getElementById("no_brg").value);
  if (txt == "") {
    alert("Text is obligatory");
  } else if (txt.length < 3) {
    alert("Too short");
  } else {
    param = "txtfind=" + txt;
    tujuan = "log_slave_get_brg.php";
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
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function setBrg(no_brg, namabrg, satuan, nomor) {
  nomor = document.getElementById("nomor").value;
  document.getElementById("kd_brg_" + nomor).value = no_brg;
  if (document.getElementById("oldKdbrg_" + nomor).value == "") {
    document.getElementById("oldKdbrg_" + nomor).value = no_brg;
  }
  document.getElementById("nm_brg_" + nomor).value = namabrg;
  document.getElementById("sat_" + nomor).value = satuan;
  getSpek(no_brg, nomor);
  closeDialog();
}
function getSpek(kodebarang, id) {
  kdBrg = kodebarang;
  param = "method=getSpek" + "&kdbrg=" + kdBrg;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("spek_" + id).value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function clear_all_data() {
  document.getElementById("form_permintaan").style.display = "none";
  document.getElementById("list_permintaan").style.display = "block";
  document.getElementById("no_prmntan").value = "";
  //document.getElementById('nm_supplier').value='';
  document.getElementById("id_supplier").value = "";
  stat_input = 0;
}
stat_input = 0;
stat_inputc = 0;
function edit_header() {
  //alert(strUrl);

  stats = document.getElementById("method");
  if (stat_input == 1) {
    no_per = trim(document.getElementById("no_prmntan").value);
    supplier_id = trim(document.getElementById("id_supplier").value);
    method = document.getElementById("method").value;
    method = "update";
    param = "no_permintaan=" + no_per + "&id_supplier=" + supplier_id; //+'&rkd_org='+rkd_org;
    param += "&method=" + method;
    //param+=strUrl;
    /*alert(param);
                return;*/
    tujuan = "log_slave_save_permintaan_harga.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            //alert(con.responseText);
            clear_all_data();
            displayList();
            //document.getElementById('contain').innerHTML=con.responseText;
            //alert('Saved succeed !!');
            //clear_all_data();
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    //post_response_text(tujuan, param, respog);
    var answer = confirm("Edit header, are you sure ?");
    if (answer) {
      post_response_text(tujuan, param, respog);
    } else {
      clear_all_data();
    }
  } else if (stat_input == 0) {
    //alert('insert');
    if (stat_inputc == 0) {
      cek_data();
    } else {
      displayList();
    }
  }
}

function fillField(
  nomor,
  tgl,
  purchase,
  supplier_id,
  npp,
  trmPayment,
  lokKrm,
  stock,
  nilaiPPn
) {
  document.getElementById("form_permintaan").style.display = "block";
  document.getElementById("list_permintaan").style.display = "none";
  document.getElementById("method").value = "update";
  document.getElementById("no_prmntan").value = nomor;
  document.getElementById("tgl_prmntan").value = tgl;
  document.getElementById("purchser_id").value = purchase;
  document.getElementById("id_supplier").value = supplier_id;
  document.getElementById("nopp").value = npp;
  //
  document.getElementById("term_pay").value = trmPayment;
  document.getElementById("tmpt_krm").value = lokKrm;
  document.getElementById("stockId").value = stock;
  document.getElementById("formDetailIsian").style.display = "block";
  document.getElementById("dtHeader").style.display = "none";
  document.getElementById("detailTable").style.display = "block";
  document.getElementById("tmbl_all").style.display = "block";
  document.getElementById("tmbl_save").disabled = true;
  document.getElementById("tmbl_cancel").disabled = true;
  document.getElementById("criTmbl").style.display = "none";

  stat_input = 1;
  stat_inputb = 0;
  stat_inputc = 1;
  var kode = document.getElementById("no_prmntan");
  //var sup_id= documnet.getElementById('id_supplier');
  param = "id=" + kode.value;
  //param += "id_supplier="+sup_id.value;
  param += "&proses=createTable";

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          dt = con.responseText.split("###");
          var detailDiv = document.getElementById("detailTable");
          document.getElementById("ketUraian").value = dt[1];
          detailDiv.innerHTML = dt[0]; // con.responseText;
          document.getElementById("ppN").value = nilaiPPn;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text("log_slave_permintaan_detail.php", param, respon);
}

function cek_data() {
  no_prmntan = document.getElementById("detail_kode").value;
  rid_supplier = trim(document.getElementById("id_supplier").value);
  id_user = trim(document.getElementById("purchser_id").value);
  rtgl = trim(document.getElementById("tgl_prmntan").value);
  met = document.getElementById("method").value = "cek_data_header";
  var tbl = document.getElementById("ppDetailTable");
  var row = tbl.rows.length;
  strUrl = "";
  for (i = 0; i < row; i++) {
    try {
      if (strUrl != "") {
        //								ar=document.getElementById('jmlhKurs_'+i);
        //								ar.value=remove_comma(ar);
        //								jmlh=ar.value;
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).value)
          ) +
          "&price[]=" +
          document.getElementById("price_" + i).value +
          "&rspek[]=" +
          encodeURIComponent(trim(document.getElementById("spek_" + i).value)) +
          "&jmlh[]=" +
          document.getElementById("jumlah_" + i).value +
          "&jmlhKurs[]=" +
          document.getElementById("jmlhKurs_" + i).value;
        +"&kurs[]=" +
          document.getElementById("kurs_" + i).options[
            document.getElementById("kurs_" + i).selectedIndex
          ].value +
          "&tglDari[]=" +
          document.getElementById("tgl_dari_" + i).value +
          "&tglSamp[]=" +
          document.getElementById("tgl_smp_" + i).value;
      } else {
        //								ar=document.getElementById('jmlhKurs_'+i);
        //								ar.value=remove_comma(ar);
        //								jmlh=ar.value;
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).value)
          ) +
          "&price[]=" +
          document.getElementById("price_" + i).value +
          "&rspek[]=" +
          encodeURIComponent(trim(document.getElementById("spek_" + i).value)) +
          "&jmlh[]=" +
          document.getElementById("jumlah_" + i).value +
          "&kurs[]=" +
          document.getElementById("kurs_" + i).options[
            document.getElementById("kurs_" + i).selectedIndex
          ].value +
          "&jmlhKurs[]=" +
          document.getElementById("jmlhKurs_" + i).value +
          "&tglDari[]=" +
          document.getElementById("tgl_dari_" + i).value +
          "&tglSamp[]=" +
          document.getElementById("tgl_smp_" + i).value;
      }
    } catch (e) {}
  }
  param =
    "ckno_permintaan=" +
    no_prmntan +
    "&id_supplier=" +
    rid_supplier +
    "&tgl=" +
    rtgl +
    "&user_id=" +
    id_user +
    "&method=" +
    met;
  param += strUrl;
  tujuan = "log_slave_save_permintaan_harga.php";
  //alert(param);
  //  return;
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          /*  alert(con.responseText);
                                                    return;*/
          var id = con.responseText;
          id = id - 1;
          switchEditAdd(id, "detail");
          addNewRow("detailBody", true);
          stat_inputc = 1;
          document.getElementById("tmbl_all").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  /*alert(param);
    return;*/
}
function simpanSemua() {
  //alert("masuk");
  no_prmntan = document.getElementById("detail_kode").value;
  rid_supplier = trim(document.getElementById("id_supplier").value);
  id_user = trim(document.getElementById("purchser_id").value);
  rtgl = trim(document.getElementById("tgl_prmntan").value);

  nilDiskon = document.getElementById("angDiskon").value;
  //    nilDiskon.value=remove_comma(nilDiskon);
  //    nilDiskon=nilDiskon.value;

  diskonPersen = document.getElementById("diskon").value;
  nilPPn = document.getElementById("hslPPn").innerHTML;

  nilaiPermintaan = document.getElementById("grand_total").value;
  //    nilaiPermintaan.value=remove_comma(nilaiPermintaan);
  //    nilaiPermintaan=nilaiPermintaan.value;

  subTotal = document.getElementById("total_harga_po").value;
  //    subTotal.value=remove_comma(subTotal);
  //    subTotal=subTotal.value;

  noPP = document.getElementById("nopp").value;
  termPay =
    document.getElementById("term_pay").options[
      document.getElementById("term_pay").selectedIndex
    ].value;
  idFranco =
    document.getElementById("tmpt_krm").options[
      document.getElementById("tmpt_krm").selectedIndex
    ].value;
  stockId =
    document.getElementById("stockId").options[
      document.getElementById("stockId").selectedIndex
    ].value;
  ketUraian = document.getElementById("ketUraian").value;
  met = document.getElementById("method").value;
  if (subTotal == "0" || subTotal == "") {
    subTotal = nilDiskon = diskonPersen = nilPPn = 0;
  }
  var tbl = document.getElementById("ppDetailTable");
  var row = tbl.rows.length - 5;
  strUrl = "";
  for (i = 0; i < row; i++) {
    try {
      if (strUrl != "") {
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).value)
          ) +
          "&price[]=" +
          document.getElementById("price_" + i).value +
          "&rspek[]=" +
          encodeURIComponent(trim(document.getElementById("spek_" + i).value)) +
          "&jmlh[]=" +
          document.getElementById("jumlah_" + i).value +
          "&kurs[]=" +
          document.getElementById("kurs_" + i).options[
            document.getElementById("kurs_" + i).selectedIndex
          ].value +
          "&jmlhKurs[]=" +
          document.getElementById("jmlhKurs_" + i).value +
          "&tglDari[]=" +
          document.getElementById("tgl_dari_" + i).value +
          "&tglSamp[]=" +
          document.getElementById("tgl_smp_" + i).value;
      } else {
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).value)
          ) +
          "&price[]=" +
          document.getElementById("price_" + i).value +
          "&rspek[]=" +
          encodeURIComponent(trim(document.getElementById("spek_" + i).value)) +
          "&jmlh[]=" +
          document.getElementById("jumlah_" + i).value +
          "&kurs[]=" +
          document.getElementById("kurs_" + i).options[
            document.getElementById("kurs_" + i).selectedIndex
          ].value +
          "&jmlhKurs[]=" +
          document.getElementById("jmlhKurs_" + i).value +
          "&tglDari[]=" +
          document.getElementById("tgl_dari_" + i).value +
          "&tglSamp[]=" +
          document.getElementById("tgl_smp_" + i).value;
      }
    } catch (e) {}
  }
  param =
    "ckno_permintaan=" +
    no_prmntan +
    "&id_supplier=" +
    rid_supplier +
    "&tgl=" +
    rtgl +
    "&user_id=" +
    id_user +
    "&method=" +
    met;
  param +=
    "&nilDiskon=" +
    nilDiskon +
    "&diskonPersen=" +
    diskonPersen +
    "&nilPPn=" +
    nilPPn +
    "&nilaiPermintaan=" +
    nilaiPermintaan;
  param +=
    "&subTotal=" +
    subTotal +
    "&kdNopp=" +
    noPP +
    "&termPay=" +
    termPay +
    "&idFranco=" +
    idFranco +
    "&stockId=" +
    stockId +
    "&ketUraian=" +
    ketUraian;
  param += strUrl;
  tujuan = "log_slave_save_permintaan_harga.php";
  //   alert(param);
  //  return;
  post_response_text(tujuan, param, respog);
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
  /*alert(param);
    return;*/
}
function addDetail(id) {
  crt = document.getElementById("method");
  var detKode = document.getElementById("detail_kode");
  var rkd_brg = document.getElementById("kd_brg_" + id);
  var rprice = document.getElementById("price_" + id);
  var rspek = document.getElementById("spek_" + id);
  var jumlah = document.getElementById("jumlah_" + id);
  var kurs = document.getElementById("kurs_" + id).options[
    document.getElementById("kurs_" + id).selectedIndex
  ].value;
  var jmhKurs = document.getElementById("jmlhKurs_" + id);
  var tglDari = document.getElementById("tgl_dari_" + id).value;
  var tglSamp = document.getElementById("tgl_smp_" + id).value;

  var id_user = trim(document.getElementById("purchser_id").value);
  //var nopp = document.getElementById('nopp_'+id).value;
  rid_supplier = trim(document.getElementById("id_supplier").value);
  rtgl = trim(document.getElementById("tgl_prmntan").value);

  if (stat_inputc == 0) {
    var a = confirm("Edit detail, are you sure ?");
    if (a) {
      cek_data();
    }
  } else {
    //alert('test');
    param = "proses=detail_add";
    param += "&kode=" + detKode.value;
    param += "&kdbrg=" + rkd_brg.value;
    rprice.value = remove_comma(rprice);
    rprice = rprice.value;
    param += "&price=" + rprice;
    param += "&rspek=" + rspek.value;
    jumlah.value = remove_comma(jumlah);
    jumlah = jumlah.value;
    param += "&jmlh=" + jumlah;
    param += "&kurs=" + kurs;
    param += "&no_permintaan=" + detKode.value;
    param += "&tgl=" + rtgl;
    param += "&supplier_id=" + rid_supplier;
    param += "&user_id=" + id_user;
    jmhKurs.value = remove_comma(jmhKurs);
    jmlhKurs = jmhKurs.value;
    param += "&jmlhKurs=" + jmlhKurs;
    param += "&tglDari=" + tglDari;
    param += "&tglSamp=" + tglSamp;
    //param += "&nopp="+nopp;
    tujuan = "log_slave_permintaan_detail.php";
    //alert(param);
    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            // Success Response
            //alert(con.responseText);
            stat_inputc = 1;

            switchEditAdd(id, "detail");
            addNewRow("detailBody", true);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    post_response_text(tujuan, param, respon);
  }
}
/* Function editDetail(id,primField,primVal)
 * Fungsi untuk mengubah data Detail
 * I : id row (urutan row pada table Detail)
 * P : Mengubah data pada tabel Detail
 * O : Notifikasi data telah berubah
 */
function editDetail(id) {
  //	alert('test');
  var detKode = document.getElementById("detail_kode");
  var rkd_brg = document.getElementById("kd_brg_" + id);
  var rprice = document.getElementById("price_" + id);
  var rspek = document.getElementById("spek_" + id);
  var jumlah = document.getElementById("jumlah_" + id);
  var kurs = document.getElementById("kurs_" + id);
  var oldKdbrg = document.getElementById("oldKdbrg_" + id).value;
  var jmhKurs = document.getElementById("jmlhKurs_" + id).value;
  var tglDari = document.getElementById("tgl_dari_" + id).value;
  var tglSamp = document.getElementById("tgl_smp_" + id).value;

  param = "proses=detail_edit";
  param += "&kode=" + detKode.value;
  param += "&kdbrg=" + rkd_brg.value;
  rprice.value = remove_comma(rprice);
  rprice = rprice.value;
  param += "&price=" + rprice;
  param += "&rspek=" + rspek.value;
  jumlah.value = remove_comma(jumlah);
  jumlah = jumlah.value;
  param += "&jmlh=" + jumlah;
  param += "&krs=" + kurs.value;
  param += "&oldKdbrg=" + oldKdbrg;
  param += "&jmlhKurs=" + jmhKurs;
  param += "&tglDari=" + tglDari;
  param += "&tglSamp=" + tglSamp;
  //alert(param);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          document.getElementById("oldKdbrg_" + id).value = rkd_brg.value;
          alert("Successfull edited");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text("log_slave_permintaan_detail.php", param, respon);
}

/* Function deleteDelete(id)
 * Fungsi untuk menghapus data Detail
 * I : id row (urutan row pada table Detail)
 * P : Menghapus data pada tabel Detail
 * O : Menghapus baris pada tabel Detail
 */
function deleteDetail(id) {
  var detKode = document.getElementById("detail_kode");
  var rkd_brg = document.getElementById("kd_brg_" + id);
  var rprice = document.getElementById("price_" + id);
  var rspek = document.getElementById("spek_" + id);

  param = "proses=detail_delete";
  param += "&kode=" + detKode.value;
  param += "&kdbrg=" + rkd_brg.value;
  param += "&price=" + rprice.value;
  param += "&rspek=" + rspek.value;

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          row = document.getElementById("detail_tr_" + id);
          if (row) {
            //row.style.display="none";
            row = document.getElementById("detail_tr_" + id);
            if (row) {
              //
              document.getElementById("price_" + id).value = 0;
              document.getElementById("total_" + id).value = 0;
              document.getElementById("dtKdbrg_" + id).innerHTML = "";
              //document.getElementById('dtKdbrg_'+id).innerHTML="";
              // document.getElementById('jmlhDiminta_'+id).value="";
              row.style.display = "none";
              //pengurang+=1;
              plusAll();
            } else {
              alert("Row undetected");
            }
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
  a = confirm("Delete item, are you sure?");
  if (a) {
    post_response_text("log_slave_permintaan_detail.php", param, respon);
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
    //alert('test 1');
    var detail = false;
  }

  // Search Available numRow
  var numRow = 0;
  if (!detail) {
    while (document.getElementById("tr_" + numRow)) {
      numRow++;
    }
  } else {
    //	alert('test 2');
    while (document.getElementById("detail_tr_" + numRow)) {
      numRow++;
    }
  }

  // Add New Row
  var newRow = document.createElement("tr");
  tabBody.appendChild(newRow);
  if (!detail) {
    newRow.setAttribute("id", "tr_" + numRow);
  } else {
    //alert('test 4');
    newRow.setAttribute("id", "detail_tr_" + numRow);
  }
  newRow.setAttribute("class", "rowcontent");

  if (!detail) {
    newRow.innerHTML +=
      "<td><input id='kode_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='matauang_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='simbol_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='kodeiso_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><img id='add_" +
      numRow +
      "' title='Tambah' class=zImgBtn onclick=\"addMain('" +
      numRow +
      "')\" src='images/save.png'/>" +
      "&nbsp;<img id='delete_" +
      numRow +
      "' />" +
      "&nbsp;<img id='pass_" +
      numRow +
      "' />" +
      "</td>";
  } else {
    //	alert('test 5');
    // Create Row
    newRow.innerHTML +=
      "<td><input id='kd_brg_" +
      numRow +
      "' type='text' class='myinputtext' style='width:120px' disabled='disabled' value='' /></td><td>" +
      "<input id='nm_brg_" +
      numRow +
      "' type='text' class='myinputtext' style='width:120px' disabled='disabled' value='' /></td><td><input id='sat_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px'disabled='disabled' value='' /><img src=images/search.png class=dellicon title='" +
      jdl_ats_0 +
      "' onclick=\"searchBrg('" +
      jdl_ats_1 +
      "','" +
      content_0 +
      "<input id=nomor type=hidden value=" +
      numRow +
      " />',event)\";> <input type=hidden id=oldKdbrg_" +
      numRow +
      " name=oldKdbrg_" +
      numRow +
      "  /></td>" +
      "<td><input id='spek_" +
      numRow +
      "' type='text' class='myinputtext' style='width:230px' onkeypress='return tanpa_kutip(event)' value='' maxlength=100 /></td>" +
      "<td><input id='jumlah_" +
      numRow +
      "' type='text' class='myinputtextnumber' style='width:70px' onkeypress='return angka_doang(event)' value='' onfocus=\"normal_number('" +
      numRow +
      "')\"  onblur=\"display_number('" +
      numRow +
      "')\" /></td>" +
      "<td><input id='price_" +
      numRow +
      "' type='text' class='myinputtextnumber' style='width:70px' onkeypress='return angka_doang(event)' onfocus=\"normal_number('" +
      numRow +
      "')\"  onblur=\"display_number('" +
      numRow +
      "')\" value='' /></td>" +
      "<td><select id='kurs_" +
      numRow +
      "' name='kurs_" +
      numRow +
      "'  style='width:70px' onchange='getKurs(" +
      numRow +
      ")'>" +
      Option_Isi +
      "</select><input type=hidden id=jmlhKurs_" +
      numRow +
      " name=jmlhKurs_" +
      numRow +
      " /></td>" +
      "<td><input type='text' style='width:70px' id='tgl_dari_" +
      numRow +
      "' class='myinputtext' name='tgl_dari_" +
      numRow +
      '\' maxlength="10" onmousemove="setCalendar(this.id)" onkeypress="return false;" ></td>' +
      "<td><input type='text' style='width:70px' id='tgl_smp_" +
      numRow +
      "' class='myinputtext' name='tgl_smp_" +
      numRow +
      '\' maxlength="10" onmousemove="setCalendar(this.id)" onkeypress="return false;" ></td>' +
      "<td><img id='detail_add_" +
      numRow +
      "' title='Tambah' class=zImgBtn onclick=\"addDetail('" +
      numRow +
      "')\" src='images/save.png'/>" +
      "&nbsp;<img id='detail_delete_" +
      numRow +
      "' />" +
      "&nbsp;<img id='detail_pass_" +
      numRow +
      "' />" +
      "</td>";
  }
}
/* Function switchEditAdd
 * Fungsi untuk mengganti image add menjadi edit dan keroconya
 * I : id nomor row
 * P : Image Add menjadi Edit
 * O : Image Edit
 */
function switchEditAdd(id, main) {
  if (main == "main") {
    var idField = document.getElementById("add_" + id);
    var delImg = document.getElementById("delete_" + id);
    var passImg = document.getElementById("pass_" + id);
    var kode = document.getElementById("kode_" + id);
  } else {
    //alert(id);
    var idField = document.getElementById("detail_add_" + id);
    var delImg = document.getElementById("detail_delete_" + id);
  }
  if (idField) {
    idField.removeAttribute("id");
    idField.removeAttribute("name");
    idField.removeAttribute("onclick");
    idField.removeAttribute("src");
    idField.removeAttribute("title");

    // Set Edit Image Attr
    idField.setAttribute("title", "Edit");
    if (main == "main") {
      idField.setAttribute("id", "edit_" + id);
      idField.setAttribute("name", "edit_" + id);
      idField.setAttribute(
        "onclick",
        "editMain('" + id + "','kode','" + kode.value + "')"
      );
    } else {
      //alert(id);
      idField.setAttribute("id", "detail_edit_" + id);
      idField.setAttribute("name", "detail_edit_" + id);
      idField.setAttribute("onclick", "editDetail('" + id + "')");
    }
    idField.setAttribute("src", "images/save.png");

    // Set Delete Image Attr
    delImg.setAttribute("class", "zImgBtn");
    delImg.setAttribute("title", "Hapus");
    if (main == "main") {
      delImg.setAttribute("name", "delete_" + id);
      delImg.setAttribute(
        "onclick",
        "deleteMain('" + id + "','kode','" + kode.value + "')"
      );
    } else {
      //alert(id);
      delImg.setAttribute("name", "detail_delete_" + id);
      delImg.setAttribute("onclick", "deleteDetail('" + id + "')");
    }
    delImg.setAttribute("src", "images/delete_32.png");
  } else {
    alert("DOM Definition Error");
  }
}
stat_inputb = 0;
function reset_data() {
  op = document.getElementById("method");
  if (stat_inputb == 0) {
    clear_all_data();
  } else if (stat_inputb == 1) {
    nomor = document.getElementById("detail_kode");
    //nomor = nomor.value;
    param = "no_permintaan=" + nomor;
    param += "&method=delete";
    tujuan = "log_slave_save_permintaan_harga.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            //alert(con.responseText);
            //document.getElementById('contain').innerHTML=con.responseText;

            //alert('Delete Data Succeed');
            clear_all_data();
            displayList();
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    post_response_text(tujuan, param, respog);
  }
}
function cariPnwrn() {
  txtSearch = trim(document.getElementById("txtsearch").value);
  tglCari = trim(document.getElementById("tgl_cari").value);
  param =
    "txtSearch=" +
    txtSearch +
    "&tglCari=" +
    tglCari +
    "&method=cari_permintaan";

  tujuan = "log_slave_save_permintaan_harga.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("contain").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function cariBast(num) {
  txtSearch = trim(document.getElementById("txtsearch").value);
  tglCari = trim(document.getElementById("tgl_cari").value);
  if (txtSearch != "" || tglCari != "") {
    param =
      "txtSearch=" +
      txtSearch +
      "&tglCari=" +
      tglCari +
      "&method=cari_permintaan";
  } else {
    //param='method=cari_pp';
    param = "method=cari_permintaan";
  }
  param += "&page=" + num;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contain").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function get_nopp() {
  //alert('masuk');
  id = document.getElementById("nomor").value;
  kd_brg = document.getElementById("kd_brg_" + id).value;
  param = "method=get_nopp" + "&kdbrg=" + kd_brg;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert('nopp_'+id);
          //alert(con.responseText);
          //document.getElementById('nopp_'+id).createElement('option')=con.responseText;
          document.getElementById("nopp_" + id).innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "1024";
  height = "600";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog2(title, content, width, height, ev);
}

function datakeExcel(ev, nmr) {
  param = "method=printExcelComparison" + "&no_permintaan=" + nmr;
  //alert(param);
  tujuan = "log_slave_pnwrharga.php?" + param;
  judul = "RFQ convert spreadsheet";
  // printFile(param,tujuan,judul,ev)
  printnopopup(tujuan);
}

function zPreview(fileTarget, passParam, idCont) {
  var passP = passParam.split("##");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    if (i == 1) {
      param += passP[i] + "=" + getValue(passP[i]);
    } else {
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
  }
  param += "&proses=preview2";
  //alert(param);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          var res = document.getElementById(idCont);
          res.innerHTML = con.responseText;
          document.getElementById("formEditData").style.display = "block";
          tipepo = document.getElementById("tipepo").value;
          if (tipepo == "SO") {
            loadmaterialso();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
  post_response_text(fileTarget + ".php", param, respon);
}

function zExcel(ev, tujuan, passParam) {
  judul = "Spreadsheet";
  //alert(param);
  var passP = passParam.split("##");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    if (i == 1) {
      param += passP[i] + "=" + getValue(passP[i]);
    } else {
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
  }
  nourut = document.getElementById("noUrut").value;
  param += "&proses=excel" + "&noUrut=" + nourut;
  //alert(param);
  printFile(param, tujuan, judul, ev);
}

function display_number(id) {
  price = document.getElementById("price_" + id);
  change_number(price);
  jmlh = document.getElementById("jumlah_" + id);
  change_number(jmlh);
}

function getZero(brs) {
  dis = document.getElementById("diskon_" + brs);
  if (dis.value == "") {
    dis.value = 0;
  }
  nPpn = document.getElementById("ppN_" + brs);
  if (nPpn.value == "") {
    nPpn.value = 0;
  }
  angdis = document.getElementById("angDiskon_" + brs);
  //angdis.value=remove_comma(angdis);
  if (angdis.value == "") {
    angdis.value = 0;
  }
}
function periksa_isi(obj) {
  if (trim(obj.value) == "") {
    alert("Please complete the form");
    obj.focus();
    return;
  }
}
function cek_isi(obj) {
  if (trim(obj.value) != "") {
    change_number(obj.value);
  } else {
    change_number(obj.value);
  }
}

function postIni(nomor, nourut) {
  alasan = document.getElementById("alasan").value;
  param =
    "method=postingData" +
    "&nomor=" +
    nomor +
    "&nourut=" +
    nourut +
    "&alasan=" +
    alasan;

  tujuan = "log_slave_save_permintaan_harga.php";
  if (confirm("Anda Yakin Ingin Memposting Nomor :" + nomor + " ")) {
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
          closeDialog();
          displayList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function flagdt(nodph, nourutdph, kdbrg, nourutbrg) {
  ckbrg = document.getElementById("ckbrg" + nourutbrg);
  if (ckbrg.checked == true) ckbrg = 1;
  else ckbrg = 0;

  param =
    "method=flagdt" +
    "&nodph=" +
    nodph +
    "&nourutdph=" +
    nourutdph +
    "&kdbrg=" +
    kdbrg +
    "&ckbrg=" +
    ckbrg;
  tujuan = "log_slave_save_permintaan_harga.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          //    closeDialog();
          //  displayList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getalamat() {
  id_supplier =
    document.getElementById("id_supplier").options[
      document.getElementById("id_supplier").selectedIndex
    ].value;

  param = "method=getalamat" + "&id_supplier=" + id_supplier;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("alamat").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cekchklist(no, count) {
  pilBrg = document.getElementById("pilBrg_" + no).checked;
  nopp = document.getElementById("nopplst_" + no).innerHTML;
  nokontrak = document.getElementById("nokontrak_" + no).innerHTML;
  if (pilBrg == true) {
    if (nokontrak != "") {
      for (i = 1; i <= count; i++) {
        ar = document.getElementById("pilBrg_" + i);
        if (i != no) {
          ar.checked = false;
        }
      }
    } else {
      for (i = 1; i <= count; i++) {
        ar = document.getElementById("pilBrg_" + i);
        as = document.getElementById("nopplst_" + i).innerHTML;
        at = document.getElementById("nokontrak_" + i).innerHTML;
        if (as == nopp && at == "") {
          ar.checked = true;
        } else {
          ar.checked = false;
        }
      }
    }
  }
}

function xExcel(ev, tujuan, xnomor, xnourut) {
  param = "xmethod=excel" + "&xnourut=" + xnourut + "&xnomor=" + xnomor;
  tujuan = tujuan + "?" + param;
  printnopopup(tujuan);
}

function xPdf(ev, tujuan, xnomor, xnourut) {
  param = "xmethod=pdf" + "&xnourut=" + xnourut + "&xnomor=" + xnomor;
  tujuan = tujuan + "?" + param;
  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function uploadCSV(fileattach, button) {
  readFileCSV(fileattach, isiform, button);

  function isiform(data, button) {
    var sNum = button.getAttribute("supplier-num");
    if (data.length > 0) {
      var dataBarang = new Array();
      var dataAttribute = new Array();
      var position;
      for (i = 0; i < data.length; i++) {
        firstColom = data[i][0];
        if (
          firstColom.toLowerCase() == "kode" ||
          firstColom.toLowerCase() == "attribute"
        ) {
          position = firstColom.toLowerCase();
          continue;
        }
        if (position == "kode") {
          dataBarang.push(data[i]);
        } else if (position == "attribute") {
          dataAttribute.push(data[i]);
        }
      }
      //item
      console.log(dataBarang);
      for (i = 0; i < dataBarang.length; i++) {
        kodebarang = dataBarang[i][0];
        namabarang = dataBarang[i][1];
        if (document.getElementById("row_" + kodebarang)) {
          tr = document.getElementById("row_" + kodebarang);
          if (tr.getAttribute("row")) {
            row = tr.getAttribute("row");
            document.getElementById("merk_" + row + "_" + sNum).value =
              dataBarang[i][2];
            document.getElementById("qty_" + row + "_" + sNum).value =
              dataBarang[i][3];
            document.getElementById("price_" + row + "_" + sNum).value =
              dataBarang[i][4];
            if (
              document
                .getElementById("price_" + row + "_" + sNum)
                .getAttribute("onkeyup")
            ) {
              func = document
                .getElementById("price_" + row + "_" + sNum)
                .getAttribute("onkeyup");
              if (func != "") {
                eval(func);
              }
            }
          }
        } else {
          alert("Kode barang " + kodebarang + " tidak ditemukan.");
          return false;
        }
      }

      //Attribute
      document.getElementById("angDiskon_" + sNum).value = dataAttribute[0][1]; //Diskon (Rp)
      if (
        document.getElementById("angDiskon_" + sNum).getAttribute("onkeyup")
      ) {
        funcDsc = document
          .getElementById("angDiskon_" + sNum)
          .getAttribute("onkeyup");
        if (funcDsc != "") {
          eval(funcDsc);
        }
      }
      document.getElementById("pbbkb_" + sNum).value = dataAttribute[1][1]; //PBBKB
      document.getElementById("tgl_dari_" + sNum).value = dataAttribute[2][1]; //Tgl Dari
      document.getElementById("tgl_smp_" + sNum).value = dataAttribute[3][1]; //Tgl. Sampai
      document.getElementById("durasipengiriman_" + sNum).value =
        dataAttribute[4][1]; //Durasi Pengiriman
      document.getElementById("durasipekerjaan_" + sNum).value =
        dataAttribute[5][1]; //Durasi Pekerjaan
      document.getElementById("garansiprodukjasa_" + sNum).value =
        dataAttribute[6][1]; //Garansi Produk/Jasa
      document.getElementById("posisistokbarang_" + sNum).value =
        dataAttribute[7][1]; //Posisi Stok Barang
      document.getElementById("asuransi_" + sNum).value = dataAttribute[8][1]; //Asuransi
      document.getElementById("ketUraian_" + sNum).value = dataAttribute[9][1]; //Keterangan
    }
  }
}

function scorevalidate(inputtxt) {
  x = document.getElementById(inputtxt);
  if (x.value > 5) {
    x.value = 0;
    x.select();
    return false;
  } else {
    x.focus();
    return true;
  }
}

function factorvalidate(inputtxt) {
  x = document.getElementById(inputtxt);
  if (x.value > 100) {
    x.value = 0;
    x.select();
    return false;
  } else {
    x.focus();
    return true;
  }
}

function isNumber(evt) {
  var p = new RegExp(/^[0-9]?$/);
  return evt.charCode === 0 || p.test(String.fromCharCode(evt.charCode));
}

function delrfq(kodebarang, notransaksi) {
  param =
    "method=delrfq" +
    "&kodebarang=" +
    kodebarang +
    "&notransaksi=" +
    notransaksi;
  tujuan = "log_slave_pnwrharga.php";

  if (confirm("Are you sure delete this item?"))
    post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadNotifikasi3(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadNotifikasi3(notransaksi) {
  proses = "getNotifikasi";
  param = "method=" + proses;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("notifikasiKerja").innerHTML =
            con.responseText;
          zPreview2("log_slave_pnwrharga", notransaksi, "printContainer2");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showdetail(pt, unit, kodebrg) {
  param =
    "pt=" + pt + "&unit=" + unit + "&kodebrg=" + kodebrg + "&method=showdetail";
  fileTarget = "log_slave_5hargaterakhir.php";
  post_response_text(fileTarget, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify
            .popup2("History Harga Pembelian", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("95%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

jlhtender = document.getElementById("jlhtender").value;
jlhbrg = document.getElementById("qty_" + id + "_" + row).value;

jmlh_brg = remove_comma(document.getElementById("qty_" + id + "_" + row));

for (var i = 1; i <= jlhtender; i++) {
  document.getElementById("qty_" + id + "_" + i).value = jlhbrg;
  harga = remove_comma(document.getElementById("price_" + id + "_" + i));

  jmlh_sub = parseFloat(jmlh_brg) * parseFloat(harga);

  if (isNaN(jmlh_sub)) {
    document.getElementById("total_" + id + "_" + i).value = 0;
  } else {
    as = document.getElementById("total_" + id + "_" + i);
    as.value = jmlh_sub;
    change_number(as);
  }

  grnd_total(i, totRow);
  calculate_varianharga(i, totRow);
}
// joki
function addmaterialso() {
  strUrl = "";
  jlhtender = document.getElementById("jlhtender").value;
  namabarangso = document.getElementById("nm_brg_so").value;

  for (var i = 1; i <= jlhtender; i++) {
    strUrl +=
      "&supplier[]=" +
      trim(document.getElementById("supplierId_" + i).value) +
      "&nourut[]=" +
      i +
      "&no_prmntan[]=" +
      trim(document.getElementById("no_prmntan_" + i).value) +
      "&jlhpesanso[]=" +
      trim(document.getElementById("jmlhDimintaso_" + i).value) +
      "&hargasatuanso[]=" +
      trim(document.getElementById("harga_satuan_so_" + i).value);
    notransaksi = trim(document.getElementById("no_prmntan_" + i).value);
  }
  param = "method=addmaterialso&namabarangso=" + namabarangso;
  param += strUrl;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // cancelmaterialso();
          loadmaterialso();
          // zPreview2('log_slave_pnwrharga',notransaksi,'printContainer2');
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function deletesomaterial(namabarang) {
  param = "namabarang=" + namabarang;
  param += "&method=deletesomaterial";
  tujuan = "log_slave_pnwrharga.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadmaterialso();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function loadmaterialso() {
  norfq = document.getElementById("no_prmntan_1").value;

  param = "method=loadmaterialso&norfq=" + norfq;
  tujuan = "log_slave_pnwrharga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // document.getElementById('listmaterialso').innerHTML = split[0];
          tipepo = document.getElementById("tipepo").value;
          if (tipepo == "SO") {
            document.getElementById("listmaterialso").innerHTML =
              con.responseText;
          }
          calculate(1, 1, 1);
          leftFixedTable(6);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
// function loadmaterialso(row, totalrow, totalcell) {
//     norfq = document.getElementById("no_prmntan").value;

//     param = "method=loadmaterialso&norfq=" + norfq;
//     tujuan = "log_slave_pnwrharga.php";

//     function respog() {
//       if (con.readyState == 4) {
//         if (con.status == 200) {
//           busy_off();
//           if (!isSaveResponse(con.responseText)) {
//             alert(con.responseText);
//           } else {
//             document.getElementById("trmaterialso").innerHTML = con.responseText;
//             if (row != "") {
//               deletesocal(row, totalrow, totalcell);
//             }
//             leftFixedTable(5);
//           }
//         } else {
//           busy_off();
//           error_catch(con.status);
//         }
//       }
//     }
//     post_response_text(tujuan, param, respog);
//   }
// end joki
