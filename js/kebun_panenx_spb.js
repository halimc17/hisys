function showformupload(ev) {
  title = "UPLOAD FILES";
  width = "";
  height = "";
  content =
    "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
  showDialog2(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic2").style.top = pos[1] + "px";
  document.getElementById("dynamic2").style.left = pos[0] + "px";
  document.getElementById("dynamic2").style.display = "";
}

function showupload(notransaksi) {
  ev = "event";
  //showformupload(ev);
  param = "method=showupload&notransaksi=" + notransaksi;

  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // document.getElementById('contUpload').innerHTML=con.responseText;
          alertify.popup().destroy();
          alertify
            .popup("Upload", con.responseText)
            .set({ resizable: true, overflow: false })
            .resizeTo("600px", "400px");
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
  document.getElementById("loaded_n_total").innerHTML =
    "Uploaded " +
    numberFormat(Math.round(event.loaded / 1024)) +
    " KB of " +
    numberFormat(Math.round(event.total / 1024)) +
    " KB";
  var percent = (event.loaded / event.total) * 100;
  document.getElementById("progressBar").value = Math.round(percent);
  document.getElementById("statusbar").innerHTML =
    Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
  document.getElementById("progressBar").style.display = "none";
  document.getElementById("statusbar").innerHTML = event.target.responseText;
  document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Failed";
}
function abortHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Aborted";
}

function submitfile(notransaksi) {
  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("fileupload", getValue("upload"));
  formdata.append("file", file);
  formdata.append("notransaksi", notransaksi);
  if (getValue("upload") == "") {
    alertify.alert("Upload file has been empty.");
    return false;
  }
  if (notransaksi == "") {
    alertify.alert("Nomor transaksi tidak ditemukan.");
    return false;
  }

  var con = createXMLHttpRequest();
  document.getElementById("btnsubmit").style.display = "none";
  //tambahan progress bar
  con.upload.addEventListener("progress", progressHandler, false);
  con.addEventListener("load", completeHandler, false);
  con.addEventListener("error", errorHandler, false);
  con.addEventListener("abort", abortHandler, false);
  //tambahan progress bar -end-
  con.open("POST", "kebun_slave_panenx_spb.php?method=submitfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //=== Success Response
          alertify.alert("Uploaded Success.");
          document.getElementById("btnsubmit").style.display = "";
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
  param = "method=loadfiles&notransaksi=" + notransaksi;
  tujuan = "kebun_slave_panenx_spb.php";
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
          if (document.getElementById("loadfilesdetail") !== null) {
            document.getElementById("loadfilesdetail").innerHTML =
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

function deletefile(notransaksi, namafile) {
  param = "method=deletefile";
  param += "&notransaksi=" + notransaksi;
  param += "&namafile=" + namafile;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
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
  width = "";
  height = "";
  content =
    '<fieldset style="width:97%;"><div id=contviewupload style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "View";
  showDialog5(title, content, width, height, ev);
}
function viewfile(idfile, sumber) {
  //formupload();
  param = "method=viewfile&idfile=" + idfile;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // document.getElementById('contviewupload').innerHTML = con.responseText;
          alertify.popup().destroy();
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

function unhideheader() {
  document.getElementById("header_trans").style.display = "block";
  document.getElementById("judul_header").style.display = "block";
  document.getElementById("hidebtn").style.display = "block";
  document.getElementById("unhidebtn").style.display = "none";
}
function hideheader() {
  document.getElementById("header_trans").style.display = "none";
  document.getElementById("judul_header").style.display = "none";
  document.getElementById("hidebtn").style.display = "none";
  document.getElementById("unhidebtn").style.display = "";
}
function detailExcel(notransaksi, numRow, ev) {
  param = "proses=excel&tipe=PNN" + "&notransaksi=" + notransaksi;
  showDialog1(
    "Print PDF",
    "<iframe frameborder=0 style='width:895px;height:400px'" +
      " src='kebun_slave_operasional_print_detail_panen.php?" +
      param +
      "'></iframe>",
    "900",
    "400",
    ev,
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}
// function detailData(notransaksi, numRow, ev, tipe) {
// param = "proses=html&tipe=" + tipe + "&notransaksi=" + notransaksi;
// title = "Data Detail";

// alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detail_panen.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');

// // showDialog1(title, "<iframe frameborder=0 style='width:895px;height:400px'" +
// // " src='kebun_slave_operasional_print_detail_panen.php?" + param + "'></iframe>", '', '', ev);
// // var dialog = document.getElementById('dynamic1');
// // dialog.style.top = '50px';
// // dialog.style.left = '15%';
// }
function detailData(notransaksi, numRow, ev, tipe, jenispanen) {
  param =
    "proses=html&tipe=" +
    tipe +
    "&notransaksi=" +
    notransaksi +
    "&jenispanen=" +
    jenispanen;

  tujuan = "kebun_slave_operasional_print_detail_panen.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify.popup().destroy();
          alertify
            .popup(con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function proporsitahuntanam(notransaksi, numRow, ev, tipe) {
  param =
    "proses=proporsitahuntanam&tipe=" + tipe + "&notransaksi=" + notransaksi;

  tujuan = "kebun_slave_operasional_print_detail_panen.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alertify.popup().destroy();
          alertify
            .popup(con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function addproporsijjg(nour, notransaksi, tanggal) {
  inputtahuntanam = document.getElementById("inputtahuntanam_" + nour).value;
  inputjjgproporsi = document.getElementById("inputjjgproporsi_" + nour).value;
  inputbrondolanproporsi = document.getElementById(
    "inputbrondolanproporsi_" + nour,
  ).value;
  inputblokkecil = document.getElementById("blokkecil_" + nour).value;
  banjir = document.getElementById("banjir_" + nour).value;

  kodeorg = document.getElementById("inputblok_" + nour).innerHTML;
  karyawanid = document.getElementById("inputkaryawanid_" + nour).innerHTML;

  param = "proses=addproporsijjg";
  param += "&notransaksi=" + notransaksi;
  param += "&tanggal=" + tanggal;
  param += "&kodeorg=" + kodeorg;
  param += "&karyawanid=" + karyawanid;
  param += "&inputjjgproporsi=" + inputjjgproporsi;
  param += "&inputbrondolanproporsi=" + inputbrondolanproporsi;
  param += "&inputtahuntanam=" + inputtahuntanam;
  param += "&inputblokkecil=" + inputblokkecil;
  param += "&banjir=" + banjir;
  param += "&tipe=PNN";

  tujuan = "kebun_slave_operasional_print_detail_panen.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          proporsitahuntanam(notransaksi, "", "event", "PNN");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteproporsijjg(noid, notransaksi) {
  param = "proses=deleteproporsijjg";
  param += "&noid=" + noid;
  param += "&notransaksi=" + notransaksi;
  param += "&tipe=PNN";

  tujuan = "kebun_slave_operasional_print_detail_panen.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          proporsitahuntanam(notransaksi, "", "event", "PNN");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveproporsi(notransaksi) {
  param = "proses=saveproporsi";
  param += "&notransaksi=" + notransaksi;
  param += "&tipe=PNN";

  tujuan = "kebun_slave_operasional_print_detail_panen.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          proporsitahuntanam(notransaksi, "", "event", "PNN");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function gettahuntanam(nour) {
  blok = document.getElementById("blokkecil_" + nour).value;

  param = "proses=gettahuntanam";
  param += "&blok=" + blok;
  param += "&tipe=PNN";
  tujuan = "kebun_slave_operasional_print_detail_panen.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("inputtahuntanam_" + nour).value = trim(
            con.responseText,
          );
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewData(notransaksi, tipe) {
  param = "proses=html&tipe=" + tipe + "&notransaksi=" + notransaksi;

  tujuan = "kebun_slave_preview_mobile_datapanen.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify.popup().destroy();
          alertify
            .popup(con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function detailPDF(notransaksi, numRow, ev) {
  param = "proses=pdf&tipe=PNN" + "&notransaksi=" + notransaksi;

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detail_panen.php?" +
        param +
        "'></iframe>",
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function postingData(notransaksi, numRow) {
  param = "notransaksi=" + notransaksi;
  param += "&method=posting";
  function respon() {
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

  alertify.confirm(
    "Info",
    "Akan dilakukan posting untuk transaksi " +
      notransaksi +
      "<br>Data tidak dapat diubah setelah ini. Anda yakin ?",
    function () {
      post_response_text("kebun_slave_panenx_spb.php", param, respon);
    },
    function () {
      return;
    },
  );
}
function unpostingData(notransaksi, numRow) {
  param = "notransaksi=" + notransaksi;
  param += "&method=unposting";
  function respon() {
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

  alertify.confirm(
    "Info",
    "Akan dilakukan unposting untuk transaksi " +
      notransaksi +
      ". Anda yakin ?",
    function () {
      post_response_text("kebun_slave_panenx_spb.php", param, respon);
    },
    function () {
      return;
    },
  );
}
function edit(
  notransaksi,
  tgl,
  kodeorg,
  nobkm,
  mandor,
  mandor1,
  kerani,
  no,
  divisi,
  kontan,
  jenispanen,
) {
  document.getElementById("notransaksi").value = notransaksi;
  document.getElementById("tgl").value = tgl;
  document.getElementById("kodeorg").value = kodeorg;
  document.getElementById("mandor").value = mandor;
  document.getElementById("mandor1").value = mandor1;
  document.getElementById("kerani").value = kerani;
  document.getElementById("divisi").value = divisi;
  document.getElementById("jenispanen").value = jenispanen;
  document.getElementById("listData").style.display = "none";
  document.getElementById("header").style.display = "block";
  document.getElementById("formpencarianheader").style.display = "none";
  document.getElementById("mode").value = "edit";

  if (kontan == "KONTAN") {
    document.getElementById("kontanan").checked = true;
  } else {
    document.getElementById("kontanan").checked = false;
  }

  setValue2("kodeorg", kodeorg);
  setValue2("mandor", mandor);
  setValue2("mandor1", mandor1);
  setValue2("kerani", kerani);
  setValue2("divisi", divisi);
  setValue2("jenispanen", jenispanen);

  addHeader(notransaksi);
}
function deletedetail(notransaksi, karyawanid, blok, notph, numrow) {
  param =
    "method=deletedetail" +
    "&notransaksi=" +
    notransaksi +
    "&karyawanid=" +
    karyawanid +
    "&blok=" +
    blok +
    "&notph=" +
    notph;
  tujuan = "kebun_slave_panenx_spb.php";
  alertify.confirm(
    "Delete",
    "Anda yakin ?",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    },
  );
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddatadetail(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function editdetail(
  notransaksi,
  karyawanid,
  blok,
  hapanen,
  jjgpanenbesar,
  jjgpanenkecil,
  jjgpanen,
  brdpanen,
  upah,
  basis,
  lbasis,
  denda_rp,
  kontan,
  notph,
  sesi,
  numrow,
  jlhdenda,
  denda,
) {
  row = document.getElementById("jlhbrs").value;
  if (row != "" || row != 0) {
    alertify.alert(
      "Silahkan uncheck Per Mandor untuk melakukan Edit !\n\nJika nama karyawan tidak muncul silahkan pilih Filter Divisi = Seluruhnya",
    );
    return;
  }
  document.getElementById("modedt").value = "edit";
  document.getElementById("notransaksi").value = notransaksi;
  document.getElementById("karyawanid").value = karyawanid;
  document.getElementById("karyawanid").disabled = true;
  document.getElementById("blok").value = blok;
  document.getElementById("blok").disabled = true;
  document.getElementById("notphold").value = notph;
  setValue2("karyawanid", karyawanid);
  setValue2("blok", blok);
  setValue2("sesi", sesi);

  document.getElementById("hapanen").value = hapanen;
  document.getElementById("jjgpanenbesar").value = jjgpanenbesar;
  document.getElementById("jjgpanenkecil").value = jjgpanenkecil;
  document.getElementById("jjgpanen").value = jjgpanen;
  document.getElementById("brdpanen").value = brdpanen;
  document.getElementById("upah").value = upah;
  document.getElementById("basis").value = basis;
  document.getElementById("lbasis").value = lbasis;
  document.getElementById("denda_rp").value = denda_rp;

  kodeiddenda = document.getElementById("kodeiddenda").value; // contoh: "1##2##4"
  idList = kodeiddenda.split("##"); // hasil: ["1", "2", "4"]
  isi = denda.split("####"); // hasil: ["", "1000", "2000", "3000"]

  for (i = 0; i < idList.length; i++) {
    id = "penalti" + idList[i]; // jadi: "penalti1", "penalti2", dst.
    nilai = isi[i + 1]; // isi dimulai dari index 1
    elemen = document.getElementById(id);
    if (elemen && nilai !== undefined) {
      elemen.value = nilai;
    }
  }

  getDataDetail("", notph);
}

function hidedendav2(id) {
  nama = document.getElementsByName(id);
  for (var i = 0; i < nama.length; i++) {
    dis = nama[i].getAttribute("style");
    if (
      dis != null &&
      (dis.includes("display:none") ||
        dis.includes("display:none;") ||
        dis.includes("display: none;"))
    ) {
      if (nama[i] != undefined) {
        nama[i].style.display = "";
      }
    } else {
      if (nama[i] != undefined) {
        nama[i].style.display = "none";
      }
    }
  }
  if (document.getElementById("infokarymandoran") != undefined) {
    document.getElementById("infokarymandoran").colSpan = "23";
  }
}

function cleardetail(baris) {
  document.getElementById("notphold").value = "";
  document.getElementById("modedt").value = "baru";
  row = document.getElementById("jlhbrs").value;
  e = document.getElementById("jumlahkolomdenda").value;

  jlhdenda = kodeiddenda.split("##");

  if (row == 0) {
    document.getElementById("karyawanid").value = "";
    document.getElementById("karyawanid").disabled = false;
    document.getElementById("blok").disabled = false;
    document.getElementById("notph").disabled = false;
    document.getElementById("blok").value = "";
    setValue2("sesi", null);
    document.getElementById("hapanen").value = "";
    document.getElementById("jjgpanenbesar").value = "";
    document.getElementById("jjgpanenkecil").value = "";
    document.getElementById("jjgpanen").value = "";
    document.getElementById("jjgpanen").disabled = true;
    document.getElementById("brdpanen").value = "";
    document.getElementById("upah").value = "";
    document.getElementById("basis").value = "";
    document.getElementById("lbasis").value = "";

    if (jlhdenda.length != "") {
      for (i = 0; i <= jlhdenda.length - 1; i++) {
        document.getElementById("penalti" + jlhdenda[i]).value = "";
      }
    }

    document.getElementById("denda_rp").value = "";

    setValue2("karyawanid", null);
    setValue2("blok", null);
    setValue2("notph", null);
  } else {
    //document.getElementById('karyawanid'+baris).value='';
    //document.getElementById('kary'+baris).innerHTML='';
    document.getElementById("blok" + baris).value = "";
    document.getElementById("hapanen" + baris).value = "";
    document.getElementById("jjgpanenbesar" + baris).value = "";
    document.getElementById("jjgpanenkecil" + baris).value = "";
    document.getElementById("jjgpanen" + baris).value = "";
    document.getElementById("jjgpanen" + baris).disabled = true;
    document.getElementById("brdpanen" + baris).value = "";
    document.getElementById("upah" + baris).value = "";
    document.getElementById("basis" + baris).value = "";
    document.getElementById("lbasis" + baris).value = "";
    document.getElementById("denda_rp" + baris).value = "";
    document.getElementById("notph" + baris).value = "";
    document.getElementById("sesi" + baris).value = "";
    if (jlhdenda.length != "") {
      for (i = 0; i <= jlhdenda.length - 1; i++) {
        document.getElementById("p" + jlhdenda[i]).value = "";
      }
    }
  }
}
function checkval(word, value) {
  if (value.value > 1 && word == "PERHARI") {
    alert("Value " + word + " maximal adalah 1");
    value.value = "";
    value.focus();
  }
}
maxf = 0;
sekarang = 1;
function saveAll(maxRow) {
  if (maxRow == "" || maxRow == 0) {
    alert("Data tidak ditemukan, proses dibatalkan !");
    return;
  }
  alertify.confirm(
    "Info",
    "Simpan Semua ?",
    function () {
      maxf = maxRow;
      savedetail(1, maxRow);
    },
    function () {
      return;
    },
  );
}
function savedetail(currRow, maxRow) {
  kontan = document.getElementById("kontanan");
  if (kontan.checked == true) {
    kontan = "KONTAN";
  } else {
    kontan = "";
  }

  row = document.getElementById("jlhbrs").value;
  notransaksi = document.getElementById("notransaksi").value;
  method = document.getElementById("method").value;
  kodeiddenda = document.getElementById("kodeiddenda").value;
  notphold = document.getElementById("notphold").value;
  modedt = document.getElementById("modedt").value;
  jlhdenda = kodeiddenda.split("##");

  param = "";
  if (row == 0) {
    karyawanid = document.getElementById("karyawanid").value;
    blok = document.getElementById("blok").value;
    luasblok = document.getElementById("luasblok").value;
    hapanen = document.getElementById("hapanen").value;
    sesi = document.getElementById("sesi").value;
    jjgpanenbesar = document.getElementById("jjgpanenbesar").value;
    jjgpanenkecil = document.getElementById("jjgpanenkecil").value;
    jjgpanen = document.getElementById("jjgpanen").value;
    brdpanen = document.getElementById("brdpanen").value;
    upah = document.getElementById("upah").value;
    basis = document.getElementById("basis").value;
    lbasis = document.getElementById("lbasis").value;
    notph = document.getElementById("notph").value;

    if (jlhdenda.length != "") {
      r = "";
      for (i = 0; i <= jlhdenda.length - 1; i++) {
        r = jlhdenda[i];
        r = document.getElementById("penalti" + jlhdenda[i]).value;
        param += "&penalti" + jlhdenda[i] + "=" + r;
      }
    }

    denda_rp = document.getElementById("denda_rp").value;
  } else {
    karyawanid = document.getElementById("karyawanid" + currRow).value;
    blok = document.getElementById("blok" + currRow).value;
    luasblok = document.getElementById("luasblok" + currRow).value;
    hapanen = document.getElementById("hapanen" + currRow).value;
    sesi = document.getElementById("sesi" + currRow).value;
    jjgpanenbesar = document.getElementById("jjgpanenbesar" + currRow).value;
    jjgpanenkecil = document.getElementById("jjgpanenkecil" + currRow).value;
    jjgpanen = document.getElementById("jjgpanen" + currRow).value;
    brdpanen = document.getElementById("brdpanen" + currRow).value;
    upah = document.getElementById("upah" + currRow).value;
    basis = document.getElementById("basis" + currRow).value;
    lbasis = document.getElementById("lbasis" + currRow).value;
    denda_rp = document.getElementById("denda_rp" + currRow).value;
    notph = document.getElementById("notph" + currRow).value;

    if (jlhdenda.length != "") {
      r = "";
      for (i = 0; i <= jlhdenda.length - 1; i++) {
        r = jlhdenda[i];
        r = document.getElementById("penalti" + jlhdenda[i] + currRow).value;
        param += "&penalti" + jlhdenda[i] + "=" + r;
      }
    }
  }

  param += "&modedt=" + modedt;
  param += "&notphold=" + notphold;
  param += "&notransaksi=" + notransaksi;
  param +=
    "&karyawanid=" +
    karyawanid +
    "&blok=" +
    blok +
    "&luasblok=" +
    luasblok +
    "&sesi=" +
    sesi;
  param +=
    "&hapanen=" +
    hapanen +
    "&jjgpanenbesar=" +
    jjgpanenbesar +
    "&jjgpanenkecil=" +
    jjgpanenkecil +
    "&jjgpanen=" +
    jjgpanen;
  param += "&brdpanen=" + brdpanen + "&upah=" + upah + "&basis=" + basis;
  param += "&lbasis=" + lbasis + "&denda_rp=" + denda_rp;
  param += "&kontan=" + kontan;
  param += "&notph=" + notph;
  param += "&method=" + method;
  param += "&kodeiddenda=" + kodeiddenda;
  param += "&jlhdenda=" + jlhdenda.length;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  // document.getElementById('row'+currRow).style.backgroundColor='cyan';
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          document.getElementById("row" + currRow).style.backgroundColor =
            "red";
          //unlockScreen();
        } else {
          cleardetail(currRow);
          loaddatadetail();
          if (currRow != undefined) {
            document.getElementById("row" + currRow).style.backgroundColor =
              "cyan";
          }
          currRow += 1;
          sekarang = currRow;
          if (currRow > maxRow || maxRow == undefined) {
            //alert('Done');
            loaddatadetail();
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
function getHitungDenda(brs, isi) {
  row = document.getElementById("jlhbrs").value;
  kodeorg = document.getElementById("kodeorg").value;
  filterdivisi = document.getElementById("filterdivisi").value;
  tgl = document.getElementById("tgl").value;
  kodeiddenda = document.getElementById("kodeiddenda").value;
  jlhdenda = kodeiddenda.split("##");

  param = "";
  if (row == 0) {
    karyawanid = document.getElementById("karyawanid").value;
    blok = document.getElementById("blok").value;
    jjgpanen = document.getElementById("jjgpanen").value;
  } else {
    karyawanid = document.getElementById("karyawanid" + brs).value;
    blok = document.getElementById("blok" + brs).value;
    jjgpanen = document.getElementById("jjgpanen" + brs).value;
  }
  if (row == 0) {
    if (jlhdenda.length != "") {
      r = "";
      for (i = 0; i <= jlhdenda.length - 1; i++) {
        r = jlhdenda[i];
        r = document.getElementById("penalti" + jlhdenda[i]).value;
        param += "&penalti[" + jlhdenda[i] + "]=" + r;
      }
    }
  } else {
    if (jlhdenda.length != "") {
      r = "";
      for (i = 0; i <= jlhdenda.length - 1; i++) {
        r = jlhdenda[i];
        r = document.getElementById("penalti" + jlhdenda[i] + brs).value;
        param += "&penalti[" + jlhdenda[i] + "]=" + r;
      }
    }
  }
  if (karyawanid == "" || blok == "" || jjgpanen == "") {
    alert("Error : Silahkan isi Karyawan, Blok dan Jjg Panen terlebih dahulu.");
    isi.value = 0;
    isi.focus();
    return;
  }
  param += "&method=getHitungDenda" + "&filterdivisi=" + filterdivisi;
  param +=
    "&tgl=" +
    tgl +
    "&karyawanid=" +
    karyawanid +
    "&blok=" +
    blok +
    "&jjgpanen=" +
    jjgpanen +
    "&kodeorg=" +
    kodeorg;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (row == 0) {
            document.getElementById("denda_rp").value = numberFormat(
              trim(con.responseText),
            );
          } else {
            document.getElementById("denda_rp" + brs).value = numberFormat(
              trim(con.responseText),
            );
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getDataDetail(baris, id) {
  row = document.getElementById("jlhbrs").value;
  kodeorg = document.getElementById("kodeorg").value;
  filterdivisi = document.getElementById("filterdivisi").value;
  tgl = document.getElementById("tgl").value;
  kontan = document.getElementById("kontanan");
  if (kontan.checked == true) {
    kontan = "KONTAN";
  } else {
    kontan = "";
  }

  if (row == 0) {
    karyawanid = document.getElementById("karyawanid").value;
    blok = document.getElementById("blok").value;
    jjgpanen = document.getElementById("jjgpanen").value;
    luasblok = document.getElementById("luasblok").value;
    if (id == "" || id == undefined) {
      id = document.getElementById("notph").value;
    }
  } else {
    karyawanid = document.getElementById("karyawanid" + baris).value;
    blok = document.getElementById("blok" + baris).value;
    jjgpanen = document.getElementById("jjgpanen" + baris).value;
    luasblok = document.getElementById("luasblok" + baris).value;
    if (id == "" || id == undefined) {
      id = document.getElementById("notph" + baris).value;
    }
  }
  param = "method=getDataDetail";
  param += "&filterdivisi=" + filterdivisi;
  param += "&tgl=" + tgl;
  param += "&karyawanid=" + karyawanid;
  param += "&blok=" + blok;
  param += "&jjgpanen=" + jjgpanen;
  param += "&kodeorg=" + kodeorg;
  param += "&luasblok=" + luasblok;
  param += "&kontan=" + kontan;
  param += "&notph=" + id;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdt = con.responseText.split("######");
          // kgpanen = trim(isdt[0]) * parseFloat(jjgpanen);
          /*buat lebih basis khusus kontan*/
          // lbasis = trim(isdt[2]) * parseFloat(kgpanen);

          kgpanen = 0;
          lbasis = 0;
          // if (isNaN(kgpanen) == true) {
          // 	kgpanen = 0;
          // }
          // if (isNaN(lbasis) == true) {
          // 	lbasis = 0;
          // }
          // if (isdt[3] == 'x' && isdt[2] != '') {
          // 	// alert('Data di menu : Kebun - Transaksi - Rekap Panen belum di input atau di posting.');
          // 	if (row == 0) {
          // 		document.getElementById('hapanen').disabled = true;
          // 		document.getElementById('jjgpanen').disabled = true;
          // 		document.getElementById('brdpanen').disabled = true;
          // 	} else {
          // 		document.getElementById('hapanen' + baris).disabled = true;
          // 		document.getElementById('jjgpanen' + baris).disabled = true;
          // 		document.getElementById('brdpanen' + baris).disabled = true;
          // 	}
          // 	return;
          // } else {
          if (row == 0) {
            document.getElementById("hapanen").disabled = false;
            document.getElementById("jjgpanen").disabled = true;
            document.getElementById("brdpanen").disabled = false;
          } else {
            document.getElementById("hapanen" + baris).disabled = false;
            document.getElementById("jjgpanen" + baris).disabled = true;
            document.getElementById("brdpanen" + baris).disabled = false;
          }
          // }
          if (row == 0) {
            // document.getElementById('kgpanen').value = numberFormat(kgpanen);
            if (kontan != "KONTAN") {
              document.getElementById("upah").value = numberFormat(
                trim(isdt[0]),
              );
              // document.getElementById('kgpanen').disabled = true;
            } else {
              document.getElementById("lbasis").value = numberFormat(lbasis);
              // document.getElementById('kgpanen').disabled = false;
            }
            document.getElementById("notph").innerHTML = isdt[3].trim();
            document.getElementById("luasblok").value = isdt[4].trim();
          } else {
            // document.getElementById('kgpanen' + baris).value = numberFormat(kgpanen);
            if (kontan != "KONTAN") {
              document.getElementById("upah" + baris).value = numberFormat(
                trim(isdt[1]),
              );
              // document.getElementById('kgpanen' + baris).disabled = true;
            } else {
              document.getElementById("lbasis" + baris).value =
                numberFormat(lbasis);
              // document.getElementById('kgpanen' + baris).disabled = false;
            }
            document.getElementById("notph" + baris).innerHTML = isdt[3].trim();
            document.getElementById("luasblok" + baris).value = isdt[4].trim();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getTotJjg(row) {
  if (row == "" || row == undefined) {
    jjgpanenbesar = document.getElementById("jjgpanenbesar").value;
    jjgpanenkecil = document.getElementById("jjgpanenkecil").value;

    if (jjgpanenbesar == "0" || jjgpanenbesar == 0) {
      jjgpanenbesar = 0;
    }
    if (jjgpanenkecil == "0" || jjgpanenkecil == 0) {
      jjgpanenkecil = 0;
    }

    document.getElementById("jjgpanen").value =
      parseFloat(jjgpanenbesar) + parseFloat(jjgpanenkecil);
  } else {
    jjgpanenbesar = document.getElementById("jjgpanenbesar" + row).value;
    jjgpanenkecil = document.getElementById("jjgpanenkecil" + row).value;

    if (jjgpanenbesar == "0" || jjgpanenbesar == 0) {
      jjgpanenbesar = 0;
    }
    if (jjgpanenkecil == "0" || jjgpanenkecil == 0) {
      jjgpanenkecil = 0;
    }

    document.getElementById("jjgpanen" + row).value =
      parseFloat(jjgpanenbesar) + parseFloat(jjgpanenkecil);
  }
}

function getdatamandor() {
  filterdivisi = document.getElementById("filterdivisi").value;
  mandor = document.getElementById("mandor").value;
  tgl = document.getElementById("tgl").value;
  kodeorg = document.getElementById("kodeorg").value;
  showpermandor = document.getElementById("showpermandor");
  kodeiddenda = document.getElementById("kodeiddenda").value;
  jlhdenda = kodeiddenda.split("##");

  e = document.getElementById("jumlahkolomdenda").value;
  if (showpermandor.checked == true) {
    method = "getdatamandor";
  } else {
    method = "inputdetail";
  }

  param =
    "method=" +
    method +
    "&filterdivisi=" +
    filterdivisi +
    "&mandor=" +
    mandor +
    "&tgl=" +
    tgl +
    "&kodeorg=" +
    kodeorg;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdtmdr = con.responseText.split("######");
          document.getElementById("inputdetail").innerHTML = isdtmdr[0];
          document.getElementById("phead").style.display = "none";
          document.getElementById("pfot").style.display = "none";

          if (jlhdenda.length != "") {
            for (i = 0; i <= jlhdenda.length - 1; i++) {
              document.getElementById("p" + jlhdenda[i]).style.display = "none";
            }
          }

          $(document).ready(function () {
            $(".select2").select2({
              dropdownAutoWidth: true,
            });
          });

          $(document).on(
            "focus",
            ".select2-selection.select2-selection--single",
            function (e) {
              $(this)
                .closest(".select2-container")
                .siblings("select:enabled")
                .select2("open");
            },
          );
          if (isdtmdr[1] != undefined) {
            row = trim(isdtmdr[1]);
          }
          getdata(row);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getdata(row) {
  row = document.getElementById("jlhbrs").value;
  filterdivisi = document.getElementById("filterdivisi").value;
  tgl = document.getElementById("tgl").value;
  kodeorg = document.getElementById("kodeorg").value;
  param =
    "method=getdata" +
    "&filterdivisi=" +
    filterdivisi +
    "&tgl=" +
    tgl +
    "&kodeorg=" +
    kodeorg;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (row == 0) {
            isdata = con.responseText.split("######");
            document.getElementById("karyawanid").innerHTML = isdata[0];
            document.getElementById("blok").innerHTML = isdata[1];
          } else {
            for (i = 1; i <= row; i++) {
              isdata = con.responseText.split("######");
              document.getElementById("blok" + i).innerHTML = isdata[1];
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
function getnotransaksi() {
  kodeorg =
    document.getElementById("kodeorg").options[
      document.getElementById("kodeorg").selectedIndex
    ].value;
  tgl = document.getElementById("tgl").value;
  document.getElementById("notransaksi").value = "";
  param = "tgl=" + tgl + "&kodeorg=" + kodeorg + "&method=getnotransaksi";
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("notransaksi").value = trim(con.responseText);
          document.getElementById("nobkm").value = trim(con.responseText);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function addHeader() {
  kodeorg = document.getElementById("kodeorg").value;
  divisi = document.getElementById("divisi").value;
  mandor = document.getElementById("mandor").value;
  mandor1 = document.getElementById("mandor1").value;
  asst = document.getElementById("asst").value;
  kerani = document.getElementById("kerani").value;
  nobkm = document.getElementById("nobkm").value;
  tgl = document.getElementById("tgl").value;
  jenispanen = document.getElementById("jenispanen").value;
  mode = document.getElementById("mode").value;
  notransaksi = document.getElementById("notransaksi").value;
  if (document.getElementById("kontanan").checked == true) {
    kontanan = "KONTAN";
  } else {
    kontanan = "";
  }

  document.getElementById("divisi").disabled = true;

  if (mode == "baru") {
    if (tgl == "" || kodeorg == "") {
      alert("Tanggal dan atau Kode Organisasi harus di isi !");
      return;
    }
    if (mandor == "") {
      alert("Mandor harus di isi !");
      return;
    }

    document.getElementById("tomboldetail").disabled = true;
  } else {
    document.getElementById("tomboldetail").disabled = false;
  }
  param = "method=detail";
  param +=
    "&tgl=" +
    tgl +
    "&kodeorg=" +
    kodeorg +
    "&nobkm=" +
    nobkm +
    "&mandor=" +
    mandor +
    "&mandor1=" +
    mandor1 +
    "&asst=" +
    asst +
    "&kerani=" +
    kerani +
    "&notransaksi=" +
    notransaksi +
    "&mode=" +
    mode +
    "&divisi=" +
    divisi +
    "&kontan=" +
    kontanan +
    "&jenispanen=" +
    jenispanen;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          data = con.responseText.split("####");
          document.getElementById("detail").style.display = "block";
          document.getElementById("detailx").style.display = "block";
          document.getElementById("detail").innerHTML = data[1];
          document.getElementById("notransaksi").value = data[0];
          document.getElementById("nobkm").value = data[0];
          inputdetail(data[0]);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function inputdetail(notransaksi) {
  kodeorg = document.getElementById("kodeorg").value;
  filterdivisi = document.getElementById("filterdivisi").value;
  showpermandor = document.getElementById("showpermandor");
  e = document.getElementById("jumlahkolomdenda").value;
  if (showpermandor.checked == true) {
    showpermandor = 1;
  } else {
    showpermandor = 0;
  }
  tgl = document.getElementById("tgl").value;
  notransaksi = document.getElementById("notransaksi").value;
  param = "method=inputdetail";
  param +=
    "&kodeorg=" +
    kodeorg +
    "&tgl=" +
    tgl +
    "&notransaksi=" +
    notransaksi +
    "&filterdivisi=" +
    filterdivisi +
    "&showpermandor=" +
    showpermandor;
  param += "&divisi=" + getValue("divisi");
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("inputdetail").innerHTML = con.responseText;
          document.getElementById("phead").style.display = "none";
          document.getElementById("pfot").style.display = "none";
          for (i = 1; i <= e; i++) {
            if (document.getElementById("p" + i) != undefined) {
              document.getElementById("p" + i).style.display = "none";
            }
          }

          $(document).ready(function () {
            $(".select2").select2({
              dropdownAutoWidth: true,
            });
          });

          $(document).on(
            "focus",
            ".select2-selection.select2-selection--single",
            function (e) {
              $(this)
                .closest(".select2-container")
                .siblings("select:enabled")
                .select2("open");
            },
          );

          loaddatadetail(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function add_new_data() {
  document.getElementById("header").style.display = "block";
  document.getElementById("formpencarianheader").style.display = "none";
  document.getElementById("listData").style.display = "none";
  document.getElementById("detailx").style.display = "none";
  cancel();
}
function del(notransaksi, numrow) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  param = "method=delete" + "&notransaksi=" + notransaksi;
  tujuan = "kebun_slave_panenx_spb.php";
  alertify.confirm(
    "Delete",
    "Anda yakin ?",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    },
  );
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddata(paged);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function displayList() {
  document.getElementById("mode").value = "baru";
  document.getElementById("notransaksisch").value = "";
  document.getElementById("tglmulai").value = "";
  document.getElementById("tglselesai").value = "";
  //document.getElementById('divsch').value = '';
  document.getElementById("postingsrc").value = "";
  document.getElementById("nospbsch").value = "";
  document.getElementById("listData").style.display = "block";
  document.getElementById("header").style.display = "none";
  document.getElementById("detail").style.display = "none";
  document.getElementById("detailx").style.display = "none";
  document.getElementById("header_trans").style.display = "block";
  document.getElementById("judul_header").style.display = "block";
  document.getElementById("formpencarianheader").style.display = "block";
  // document.getElementById('unhidebtn').style.display = 'none';

  setValue2("uploadsch", null);
  loaddata(0);
}
function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}
function loaddata(page) {
  //document.getElementById('listData').style.display = 'block';
  //document.getElementById('judul_header').style.display = 'block';

  notransaksisch = document.getElementById("notransaksisch").value;
  tglmulai = document.getElementById("tglmulai").value;
  tglselesai = document.getElementById("tglselesai").value;
  divsch = document.getElementById("divsch").value;
  postingsrc = document.getElementById("postingsrc").value;
  periodesch = document.getElementById("periodesch").value;
  nospb = document.getElementById("nospbsch").value;
  upload = document.getElementById("uploadsch").value;
  param = "method=loaddata&page=" + page;
  param += "&nospb=" + nospb;
  param += "&upload=" + upload;
  if (divsch != "") {
    param += "&divsch=" + divsch;
  }
  if (notransaksisch != "") {
    param += "&notransaksisch=" + notransaksisch;
  }
  if (tglmulai != "") {
    param += "&tglmulai=" + tglmulai;
  }
  if (tglselesai != "") {
    param += "&tglselesai=" + tglselesai;
  }
  if (postingsrc != "") {
    param += "&postingsrc=" + postingsrc;
  }
  if (periodesch != "") {
    param += "&periodesch=" + periodesch;
  }
  tujuan = "kebun_slave_panenx_spb.php";
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
function loaddataexcel() {
  //document.getElementById('listData').style.display = 'block';
  //document.getElementById('judul_header').style.display = 'block';

  notransaksisch = document.getElementById("notransaksisch").value;
  tglmulai = document.getElementById("tglmulai").value;
  tglselesai = document.getElementById("tglselesai").value;
  divsch = document.getElementById("divsch").value;
  postingsrc = document.getElementById("postingsrc").value;
  periodesch = document.getElementById("periodesch").value;
  nospb = document.getElementById("nospbsch").value;
  upload = document.getElementById("uploadsch").value;
  param = "method=loaddataexcel";
  if (nospb != "") {
    param += "&nospb=" + nospb;
  }
  if (upload != "") {
    param += "&upload=" + upload;
  }
  if (divsch != "") {
    param += "&divsch=" + divsch;
  }
  if (notransaksisch != "") {
    param += "&notransaksisch=" + notransaksisch;
  }
  if (tglmulai != "") {
    param += "&tglmulai=" + tglmulai;
  }
  if (tglselesai != "") {
    param += "&tglselesai=" + tglselesai;
  }
  if (postingsrc != "") {
    param += "&postingsrc=" + postingsrc;
  }
  if (periodesch != "") {
    param += "&periodesch=" + periodesch;
  }

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify
            .popuppdf(
              "title",
              "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_panenx_spb.php?" +
                param +
                "'></iframe>",
            )
            .set({ resizable: true, overflow: false })
            .resizeTo("90%", "80%");
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
  document.getElementById("detailx").style.display = "none";
  document.getElementById("tomboldetail").disabled = false;
  document.getElementById("tgl").disabled = false;
  document.getElementById("tgl").value = "";
  document.getElementById("kontanan").checked = false;
  document.getElementById("kodeorg").disabled = false;
  document.getElementById("divisi").disabled = false;
  document.getElementById("kodeorg").value = "";
  document.getElementById("notransaksi").value = "";
  document.getElementById("nobkm").value = "";
  document.getElementById("mandor").value = "";
  document.getElementById("mandor1").value = "";
  document.getElementById("kerani").value = "";
  document.getElementById("mode").value = "baru";

  setValue2("kodeorg", null);
  setValue2("mandor", null);
  setValue2("mandor1", null);
  setValue2("kerani", null);
  setValue2("divisi", null);
}
function loaddatadetail(notransaksi) {
  document.getElementById("kodeorg").disabled = true;
  document.getElementById("tgl").disabled = true;
  tgl = document.getElementById("tgl").value;
  kodeorg = document.getElementById("kodeorg").value;
  notransaksi = document.getElementById("notransaksi").value;
  namakary = document.getElementById("namakarydetsch").value;
  blok = document.getElementById("blokdetsch").value;
  tt = document.getElementById("ttdetsch").value;

  param = "method=loaddatadetail";
  param +=
    "&kodeorg=" + kodeorg + "&tgl=" + tgl + "&notransaksi=" + notransaksi;
  param += "&namakary=" + namakary;
  param += "&blok=" + blok;
  param += "&tt=" + tt;
  param += "&divisi=" + getValue("divisi");

  tujuan = "kebun_slave_panenx_spb.php";
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
function numberFormat(number, digit) {
  number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
  //Seperates the components of the number
  var components = parseFloat(number).toFixed(digit).split(".");
  //Comma-fies the first part
  components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  //Combines the two sections
  return components.join(".");
}
function form() {
  width = "720";
  height = "";
  //nopp=document.getElementById('nopp_'+id).value;
  content =
    '<fieldset><div id=containerd align=center style="width:700px;max-height:700px;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "Detail HTML";
  showDialog5(title, content, width, height, ev);
}
function html(notransaksi, kodeorg, tgl) {
  form();
  param =
    "method=html" +
    "&kodeorg=" +
    kodeorg +
    "&tgl=" +
    tgl +
    "&notransaksi=" +
    notransaksi;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("containerd").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function excel(ev, tujuan) {
  unitexp = document.getElementById("unitexp").value;
  perexp = document.getElementById("perexp").value;
  if (unitexp == "" || perexp == "") {
    alert("Lengkapi unit dan periode.");
    return;
  }
  judul = "Report Ms.Excel";
  param = "method=excel" + "&unitexp=" + unitexp + "&perexp=" + perexp;
  printFile(param, tujuan, judul, ev);
}

function getkontan(jenispremi) {
  row = document.getElementById("jlhbrs").value;
  kontan = document.getElementById("kontan");
  if (jenispremi == "LIBUR") {
    if (row == 0) {
      if (kontan.checked == true) {
        kontan = "KONTAN";
        document.getElementById("info_kontan").innerHTML = "Ya";
        document.getElementById("lbasis").disabled = true;
        document.getElementById("kgpanen").disabled = false;
        document.getElementById("upah").value = "";
        document.getElementById("mandor").value = "";
        document.getElementById("mandor1").value = "";
        document.getElementById("kerani").value = "";
      } else {
        kontan = 0;
        document.getElementById("lbasis").disabled = true;
        document.getElementById("kgpanen").disabled = true;
        document.getElementById("basis").value = "";
        document.getElementById("lbasis").value = "";
        document.getElementById("kgpanen").value = "";
        document.getElementById("info_kontan").innerHTML = "Tidak";
        //getDataDetail();
      }
    } else {
      if (kontan.checked == true) {
        kontan = "KONTAN";
        document.getElementById("info_kontan").innerHTML = "Ya";
        document.getElementById("mandor").value = "";
        document.getElementById("mandor1").value = "";
        document.getElementById("kerani").value = "";
        for (brs = 1; brs <= row; brs++) {
          document.getElementById("lbasis" + brs).disabled = false;
          document.getElementById("kgpanen" + brs).disabled = false;
          document.getElementById("upah" + brs).value = "";
        }
      } else {
        kontan = 0;
        document.getElementById("info_kontan").innerHTML = "Tidak";
        for (brs = 1; brs <= row; brs++) {
          document.getElementById("lbasis" + brs).disabled = true;
          document.getElementById("kgpanen" + brs).disabled = true;
          document.getElementById("basis" + brs).value = "";
          document.getElementById("lbasis" + brs).value = "";
          document.getElementById("kgpanen" + brs).value = "";
          document.getElementById("blok" + brs).value = "";
        }
      }
    }
  } else {
    document.getElementById("kontan").checked = false;
    document.getElementById("info_kontan").innerHTML = "Tidak";
    alert("Kontanan hanya di perbolehkan pada hari libur !!!");
    return;
  }
}

function getbasispnn() {
  // width = '';
  // height = '';
  // content = "<div id=contbsspnn align=center style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
  // ev = 'event';
  // title = "Basis Panen";
  // showDialog5(title, content, width, height, ev);

  kodeorg = document.getElementById("kodeorg").value;
  notransaksi = document.getElementById("notransaksi").value;
  tgl = document.getElementById("tgl").value;
  param =
    "method=getbasispnn" +
    "&kodeorg=" +
    kodeorg +
    "&tgl=" +
    tgl +
    "&notransaksi=" +
    notransaksi;
  tujuan = "kebun_slave_panenx_spb.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('contbsspnn').innerHTML = con.responseText;
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

function cariby(val, sumber) {
  if (sumber == "namakary") {
    if (getValue("namakarydetsch") == "") {
      document.getElementById("namakarydetsch").value = val;
    } else {
      document.getElementById("namakarydetsch").value = "";
    }
  }
  if (sumber == "blok") {
    if (getValue("blokdetsch") == "") {
      document.getElementById("blokdetsch").value = val;
    } else {
      document.getElementById("blokdetsch").value = "";
    }
  }
  if (sumber == "tt") {
    if (getValue("ttdetsch") == "") {
      document.getElementById("ttdetsch").value = val;
    } else {
      document.getElementById("ttdetsch").value = "";
    }
  }
  loaddatadetail();
}

function cancelcari() {
  document.getElementById("namakarydetsch").value = "";
  document.getElementById("blokdetsch").value = "";
  document.getElementById("ttdetsch").value = "";
  loaddatadetail();
}

function postingMobileERP(noreferensi) {
  param = "notransaksi=" + noreferensi;
  param += "&proses=panen";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.popup().destroy();
          loaddata(0);
          alertify.alert("Data Successfully Downloaded !");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  alertify.confirm(
    "Info",
    "Akan dilakukan download data untuk transaksi " +
      noreferensi +
      "<br>Apakah Anda yakin ?",
    function () {
      post_response_text("kebun_slave_download_from_mobile.php", param, respon);
    },
    function () {
      return;
    },
  );
}
