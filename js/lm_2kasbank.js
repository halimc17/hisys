function preview(tipe) {
  kodept = document.getElementById("kodept").value;
  kodeorg = document.getElementById("kodeorg").value;
  noakun = document.getElementById("noakun").value;
  periode = document.getElementById("periode").value;
  tipelaporan = document.getElementById("tipelaporan").value;
  proses = document.getElementById("proses").value;

  param = "kodept=" + kodept + "&kodeorg=" + kodeorg + "&proses=preview";
  param += "&periode=" + periode;
  param += "&tipe=" + tipe;
  param += "&noakun=" + noakun;
  param += "&tipelaporan=" + tipelaporan;
  tujuan = "lm_2kasbank_slave.php";

  if (kodept == "") {
    alertify.alert("Informasi", "Perusahaan Tidak Boleh Kosong!");
  }

  if (kodeorg == "") {
    alertify.alert("Informasi", "Unit Tidak Boleh Kosong!");
  }

  if (noakun == "") {
    alertify.alert("Informasi", "No Akun Kas Tidak Boleh Kosong!");
  }

  if (periode == "") {
    alertify.alert("Informasi", "Periode Tidak Boleh Kosong!");
  }

  if (tipe == "excel") {
    judul = "Report Ms.Excel";
    ev = "event";
    printFile(param, tujuan, judul, ev);
  } else {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          if (tipe == "html") {
            document.getElementById("container").innerHTML = con.responseText;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getUnit(pt) {
  param = "proses=getUnit&kodept=" + pt;
  tujuan = "lm_2kasbank_slave.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("kodeorg").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getAkun(kodeorg) {
  param = "proses=getAkun&kodeorg=" + kodeorg;
  tujuan = "lm_2kasbank_slave.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("noakun").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function excelDetail(
  unitinti,
  unitplasma,
  arrdata,
  tipelaporan,
  periode,
  tipedetail,
) {
  param = "proses=detaildata";
  param += "&arrdata=" + arrdata;
  param += "&unitinti=" + unitinti;
  param += "&unitplasma=" + unitplasma;
  param += "&tipelaporan=" + tipelaporan;
  param += "&periode=" + periode;
  param += "&tipe=" + tipedetail;

  tujuan = "lm_2kasbank_slave.php";

  judul = "Report Ms.Excel";
  ev = "event";
  printFile(param, tujuan, judul, ev);
}

function detaildata(
  unitinti,
  unitplasma,
  arrdata,
  tipelaporan,
  periode,
  tipedetail,
) {
  param = "proses=detaildata";
  param += "&arrdata=" + arrdata;
  param += "&unitinti=" + unitinti;
  param += "&unitplasma=" + unitplasma;
  param += "&tipelaporan=" + tipelaporan;
  param += "&periode=" + periode;

  tujuan = "lm_2kasbank_slave.php";

  if (tipedetail == "excel") {
    judul = "Report Ms.Excel";
    ev = "event";
    printFile(param, tujuan, judul, ev);
  } else {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify
            .popup("Detail Jurnal", con.responseText)
            .set({ resizable: true, overflow: true })
            .resizeTo("80%", "100%");
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
  width = "300";
  height = "100";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}
