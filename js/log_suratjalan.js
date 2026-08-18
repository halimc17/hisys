function showalllist(pg) {
  document.getElementById("srcnosj").value = "";
  document.getElementById("srcnopl").value = "";
  document.getElementById("srcnopp").value = "";
  document.getElementById("srcnopo").value = "";
  document.getElementById("listdata").style.display = "block";
  document.getElementById("forminput").style.display = "none";
  loaddata(pg);
}

function displayFormInput() {
  document.getElementById("listdata").style.display = "none";
  document.getElementById("forminput").style.display = "block";
  clear_all_data();
}

function clear_all_data() {
  document.getElementById("nosj").value = "";
  document.getElementById("unit").selectedIndex = 0;
  document.getElementById("unit").disabled = false;
  document.getElementById("expeditor").selectedIndex = 0;
  document.getElementById("nopol").value = "";
  document.getElementById("jeniskedaraan").selectedIndex = 0;
  document.getElementById("supir").value = "";
  document.getElementById("hpsupir").value = "";
  document.getElementById("pengirim").selectedIndex = 0;
  document.getElementById("cek").value = "";
  document.getElementById("gudangtujuan").selectedIndex = 0;
  document.getElementById("transportasi").selectedIndex = 0;
  document.getElementById("listdt").style.display = "none";
  showDetail();
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function cariData(pg) {
  document.getElementById("listdata").style.display = "block";
  document.getElementById("forminput").style.display = "none";
  loaddata(pg);
}

function getPage2(pg) {
  loaddata(pg);
}

function loaddata(pg) {
  srcnosj = document.getElementById("srcnosj").value;
  srcnopl = document.getElementById("srcnopl").value;
  srcnopp = document.getElementById("srcnopp").value;
  srcnopo = document.getElementById("srcnopo").value;
  param =
    "method=loaddata" +
    "&page=" +
    pg +
    "&srcnosj=" +
    srcnosj +
    "&srcnopl=" +
    srcnopl +
    "&srcnopp=" +
    srcnopp +
    "&srcnopo=" +
    srcnopo;
  tujuan = "log_slave_suratjalan.php";
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

function cleardt() {
  param = "method=cleardt";
  tujuan = "log_slave_transit.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listpo").innerHTML = "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpan() {
  nosj = document.getElementById("nosj").value;
  unit = document.getElementById("unit").value;
  tanggal = document.getElementById("tanggal").value;
  tanggalkirim = document.getElementById("tanggalkirim").value;
  expeditor = document.getElementById("expeditor").value;
  nopol = document.getElementById("nopol").value;
  jeniskedaraan = document.getElementById("jeniskedaraan").value;
  supir = document.getElementById("supir").value;
  hpsupir = document.getElementById("hpsupir").value;
  pengirim = document.getElementById("pengirim").value;
  cek = document.getElementById("cek").value;
  gudangtujuan = document.getElementById("gudangtujuan").value;
  transportasi = document.getElementById("transportasi").value;

  param =
    "method=simpan&nosj=" +
    nosj +
    "&unit=" +
    unit +
    "&tanggal=" +
    tanggal +
    "&tanggalkirim=" +
    tanggalkirim +
    "&expeditor=" +
    expeditor +
    "&nopol=" +
    nopol +
    "&jeniskedaraan=" +
    jeniskedaraan +
    "&supir=" +
    supir +
    "&hpsupir=" +
    hpsupir +
    "&pengirim=" +
    pengirim +
    "&cek=" +
    cek +
    "&gudangtujuan=" +
    gudangtujuan +
    "&transportasi=" +
    transportasi;
  tujuan = "log_slave_suratjalan.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("nosj").value = con.responseText;
          document.getElementById("listdt").style.display = "";
          document.getElementById("unit").disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteData(nosj) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;

  param = "nosj=" + nosj + "&method=delete";

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          getPage2(paged);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  if (confirm("No Surat Jalan : " + nosj + " akan dihapus.\nAnda Yakin?"))
    post_response_text("log_slave_suratjalan.php", param, respon);
}

function fillField(
  nosj,
  unit,
  tanggal,
  tanggalkirim,
  expeditor,
  nopol,
  jeniskedaraan,
  supir,
  hpsupir,
  pengirim,
  cek,
  gudangtujuan,
  transportasi
) {
  document.getElementById("listdata").style.display = "none";
  document.getElementById("forminput").style.display = "block";
  document.getElementById("listdt").style.display = "";

  document.getElementById("nosj").value = nosj;
  document.getElementById("unit").value = unit;
  document.getElementById("tanggal").value = tanggal;
  document.getElementById("tanggalkirim").value = tanggalkirim;
  document.getElementById("expeditor").value = expeditor;
  document.getElementById("nopol").value = nopol;
  document.getElementById("jeniskedaraan").value = jeniskedaraan;
  document.getElementById("supir").value = supir;
  document.getElementById("hpsupir").value = hpsupir;
  document.getElementById("pengirim").value = pengirim;
  document.getElementById("cek").value = cek;
  document.getElementById("gudangtujuan").value = gudangtujuan;
  document.getElementById("transportasi").value = transportasi;
  showDetail();
}

function showPO(ev) {
  param =
    "method=showPO&tipe=PO&nosj=" +
    getValue("nosj") +
    "&unit=" +
    getValue("unit");
  tujuan = "log_slave_suratjalan.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          showDialog1("Find PO", "<div id='popupCont'></div>", "", "", ev);
          document.getElementById("popupCont").innerHTML = con.responseText;
          var dialog = document.getElementById("dynamic1");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findPO() {
  nosj = document.getElementById("nosj").value;
  unit = document.getElementById("unit").value;
  nopodt = document.getElementById("nopodt").value;
  gudangtujuan = document.getElementById("gudangtujuan").value;

  param =
    "nosj=" +
    nosj +
    "&unit=" +
    unit +
    "&nopodt=" +
    nopodt +
    "&gudangtujuan=" +
    gudangtujuan +
    "&method=findPO";
  tujuan = "log_slave_suratjalan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("hasilCari").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findPL() {
  nosj = document.getElementById("nosj").value;
  unit = document.getElementById("unit").value;
  nopodt = document.getElementById("nopodt").value;

  param =
    "nosj=" + nosj + "&unit=" + unit + "&nopodt=" + nopodt + "&method=findPL";
  tujuan = "log_slave_suratjalan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("hasilCari").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function add2detail(tipe) {
  nosj = document.getElementById("nosj").value;
  unit = document.getElementById("unit").value;

  param =
    "nosj=" + nosj + "&unit=" + unit + "&jenis=" + tipe + "&method=add2detail";
  body = document.getElementById("bodySearch");
  tujuan = "log_slave_suratjalan.php";

  var j = 0;
  for (var i = 0; i < body.rows.length; i++) {
    if (getById(tipe + "_" + i).checked) {
      if (tipe == "po") {
        param += "&data[" + j + "][nopo]=" + getInner("nopo_" + i);
        param += "&data[" + j + "][kodebarang]=" + getInner("kodebarang_" + i);
        param += "&data[" + j + "][namabarang]=" + getInner("namabarang_" + i);
        param += "&data[" + j + "][nopp]=" + getInner("nopp_" + i);
        param += "&data[" + j + "][jumlah]=" + getInner("jumlah_" + i);
        param += "&data[" + j + "][satuan]=" + getInner("satuan_" + i);
        param += "&data[" + j + "][noref]=" + getValue("noref_" + i);
      } else if (tipe == "pl") {
        param += "&data[" + j + "][kodebarang]=" + getInner("notransaksi_" + i);
      } else {
        param += "&data[" + j + "][kodebarang]=" + getInner("kodebarang_" + i);
        param += "&data[" + j + "][namabarang]=" + getInner("namabarang_" + i);
        param += "&data[" + j + "][satuan]=" + getInner("satuan_" + i);
      }
      j++;
    }
  }
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          showDetail(tipe);
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showDetail(tipe) {
  nosj = document.getElementById("nosj").value;
  unit = document.getElementById("unit").value;
  detailField = document.getElementById("containdt");

  param = "nosj=" + nosj + "&unit=" + unit + "&method=showDetail";

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          detailField.innerHTML = con.responseText;

          if (typeof tipe != "undefined") {
            if (tipe == "po") {
              findPO();
            } else if (tipe == "pl") {
              findPL();
            } else {
              findMat();
            }
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text("log_slave_suratjalan.php", param, respon);
}

function deleteDetail(nosj, nopo, nopp, kodebarang, jumlah, noref) {
  param =
    "method=deleteDetail&nosj=" +
    nosj +
    "&nopo=" +
    nopo +
    "&nopp=" +
    nopp +
    "&kodebarang=" +
    kodebarang +
    "&jumlah=" +
    jumlah +
    "&noref=" +
    noref;
  tujuan = "log_slave_suratjalan.php";

  if (confirm("Anda yakin hapus item ini " + kodebarang + "?")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          showDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showPL(ev) {
  param =
    "method=showPL&tipe=PL&nosj=" +
    getValue("nosj") +
    "&unit=" +
    getValue("unit");
  tujuan = "log_slave_suratjalan.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          showDialog1(
            "Find Package List",
            "<div id='popupCont'></div>",
            "",
            "",
            ev
          );
          document.getElementById("popupCont").innerHTML = con.responseText;
          var dialog = document.getElementById("dynamic1");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingData(nosj) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;

  param = "nosj=" + nosj + "&method=posting";
  tujuan = "log_slave_suratjalan.php";

  if (confirm("Anda yakin dokumen telah lengkap..?"))
    post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          getPage2(paged);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailPDF(numRow, ev) {
  var nosj = document.getElementById("nosj_" + numRow).getAttribute("value"),
    pt = document.getElementById("kodept_" + numRow).getAttribute("value"),
    param = "proses=pdf&nosj=" + nosj + "&pt=" + pt;

  showDialog1(
    "Print PDF",
    "<iframe frameborder=0 style='width:795px;height:400px'" +
      " src='log_slave_suratjalan_print_detail.php?" +
      param +
      "'></iframe>",
    "800",
    "400",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}

function isNumber(evt) {
  var p = new RegExp(/^[0-9]?$/);
  return evt.charCode === 0 || p.test(String.fromCharCode(evt.charCode));
}

function changejumlah(no) {
  valpar = document.getElementById("valpar_" + no).value;
  jlh = document.getElementById("jlh_" + no).value;

  param = "valpar=" + valpar + "&jlh=" + jlh + "&method=changejumlah";
  tujuan = "log_slave_suratjalan.php";
  xx = document.getElementById("valpar_" + no);
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          xx.value = con.responseText.trim();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
