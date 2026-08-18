function cancel() {
  closeDialog();
  document.getElementById("unit").value = "";
  document.getElementById("noakun").value = "";
  document.getElementById("bank").value = "";
  document.getElementById("tgl1").value = "";
  document.getElementById("tgl2").value = "";
  document.getElementById("printContainer").innerHTML = "";
}

function clearopt() {
  document.getElementById("noakun").selectedIndex = 0;
  document.getElementById("bank").selectedIndex = 0;
  document.getElementById("pembayaran").selectedIndex = 0;
  getbank();
}

function preview() {
  unit = document.getElementById("unit").value;
  noakun = document.getElementById("noakun").value;
  bank = document.getElementById("bank").value;
  tgl1 = document.getElementById("tgl1").value;
  tgl2 = document.getElementById("tgl2").value;
  pembayaran = document.getElementById("pembayaran").value;
  group = document.getElementById("group").value;

  if (unit == "" || noakun == "" || tgl1 == "" || tgl2 == "") {
    alertify.alert(
      "Informasi",
      "Unit, Nomor Akun, Tanggal mulai dan Tanggal sampai harus diisi."
    );
    return;
  }

  // if(noakun=='1110101' || noakun=='1111101'){
  // 	if(bank==''){
  // 		alertify.alert("Informasi",'bank harus diisi');return;
  // 	}
  // }

  param = "method=preview";
  param += "&unit=" + unit + "&noakun=" + noakun + "&bank=" + bank;
  param +=
    "&tgl1=" +
    tgl1 +
    "&tgl2=" +
    tgl2 +
    "&pembayaran=" +
    pembayaran +
    "&group=" +
    group;
  tujuan = "keu_2kasharianv2_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("printContainer").innerHTML =
            con.responseText;
          // leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pdf(ev) {
  unit = document.getElementById("unit").value;
  noakun = document.getElementById("noakun").value;
  bank = document.getElementById("bank").value;
  tgl1 = document.getElementById("tgl1").value;
  tgl2 = document.getElementById("tgl2").value;
  param = "method=preview";
  param += "&unit=" + unit + "&noakun=" + noakun + "&bank=" + bank;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&tipe=pdf";
  //   tujuan = "keu_2kasharianv2_slave.php";
  //   judul = "Report PDF";
  //   printFile(param, tujuan, judul, ev);

  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_2kasharianv2_slave.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("90%", "80%");
}

function excel(ev) {
  unit = document.getElementById("unit").value;
  noakun = document.getElementById("noakun").value;
  bank = document.getElementById("bank").value;
  tgl1 = document.getElementById("tgl1").value;
  tgl2 = document.getElementById("tgl2").value;
  pembayaran = document.getElementById("pembayaran").value;
  group = document.getElementById("group").value;
  param = "method=preview";
  param +=
    "&unit=" +
    unit +
    "&noakun=" +
    noakun +
    "&bank=" +
    bank +
    "&pembayaran=" +
    pembayaran +
    "&group=" +
    group;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&tipe=excel";
  tujuan = "keu_2kasharianv2_slave.php";
  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}

// function lihatDetail(notransaksi, novoucher, keterangan, ev) {
function lihatDetail(notransaksi, ev) {
  // param = 'notransaksi=' + notransaksi + '&novoucher=' + novoucher + '&keterangan=' + keterangan + '&method=detail';
  param = "notransaksi=" + notransaksi + "&method=detail";
  tujuan = "keu_2kasharianv2_slave.php" + "?" + param;
  // width = '950';
  // height = '400';

  //content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'><fieldset><legend>Submission Form</legend></fieldset></iframe>";

  alertify
    .popuppdf(
      "Detail",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");

  //showDialog1('Detail Kas Harian ' + novoucher, content, width, height, ev);
}

function getbank() {
  unit = document.getElementById("unit").value;
  noakun = document.getElementById("noakun").value;
  bank = document.getElementById("bank").value;
  tgl1 = document.getElementById("tgl1").value;
  tgl2 = document.getElementById("tgl2").value;
  param = "method=getbank";
  param += "&unit=" + unit + "&noakun=" + noakun + "&bank=" + bank;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2;
  tujuan = "keu_2kasharianv2_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("bank").innerHTML = con.responseText;
          getgroup();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getgroup() {
  bank = document.getElementById("bank").value;

  document.getElementById("group").selectedIndex = 0;
  if (bank != "") {
    document.getElementById("group").disabled = true;
  } else {
    document.getElementById("group").disabled = false;
  }
}

function getrekening() {
  unit = document.getElementById("unitsum").value;
  tgl1 = document.getElementById("tgl1sum").value;
  tgl2 = document.getElementById("tgl2sum").value;
  param = "method=getrekening";
  param += "&unit=" + unit;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2;
  tujuan = "keu_2kasharianv2_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("rek").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

// #========================================================================================================================#

function previewkk() {
  unit = document.getElementById("unitkk").value;
  noakun = document.getElementById("noakunkk").value;
  tgl1 = document.getElementById("tgl1kk").value;
  tgl2 = document.getElementById("tgl2kk").value;

  if (
    unitkk == "" ||
    noakunkk == "" ||
    tgl1kk == "" ||
    tgl1kk == "" ||
    tgl2kk == ""
  ) {
    alertify.alert("Informasi", "Lengkapi pengisian");
    return;
  }

  param = "method=previewkk";
  param += "&unit=" + unit + "&noakun=" + noakun;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2;
  tujuan = "keu_2kasharianv2_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("printContainerkk").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pdfkk(ev) {
  unit = document.getElementById("unitkk").value;
  noakun = document.getElementById("noakunkk").value;
  tgl1 = document.getElementById("tgl1kk").value;
  tgl2 = document.getElementById("tgl2kk").value;
  param = "method=previewkk";
  param += "&unit=" + unit + "&noakun=" + noakun + "&bank=" + bank;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&tipe=pdf";
  tujuan = "keu_2kasharianv2_slave.php";
  judul = "Report PDF";
  printFile(param, tujuan, judul, ev);
}

function excelkk(ev) {
  unit = document.getElementById("unitkk").value;
  noakun = document.getElementById("noakunkk").value;
  tgl1 = document.getElementById("tgl1kk").value;
  tgl2 = document.getElementById("tgl2kk").value;
  param = "method=previewkk";
  param += "&unit=" + unit + "&noakun=" + noakun;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&tipe=excel";
  tujuan = "keu_2kasharianv2_slave.php";
  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}

function cancelkk() {
  closeDialog();
  document.getElementById("unitkk").value = "";
  document.getElementById("noakunkk").value = "";
  document.getElementById("tgl1kk").value = "";
  document.getElementById("tgl2kk").value = "";
  document.getElementById("printContainerkk").innerHTML = "";
}

function previewsum() {
  unit = document.getElementById("unitsum").value;
  rek = document.getElementById("rek").value;
  tgl1 = document.getElementById("tgl1sum").value;
  tgl2 = document.getElementById("tgl2sum").value;

  if (unitsum == "" || tgl1sum == "" || tgl2sum == "") {
    alertify.alert("Informasi", "Lengkapi pengisiannn");
    return;
  }

  param = "method=previewsum";
  param += "&unit=" + unit + "&rek=" + rek;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2;
  tujuan = "keu_2kasharianv2_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("printContainersum").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelsum() {
  closeDialog();
  document.getElementById("unitsum").value = "";
  document.getElementById("rek").value = "";
  document.getElementById("tgl1sum").value = "";
  document.getElementById("tgl2sum").value = "";
  document.getElementById("printContainersum").innerHTML = "";
}

function pdfsum(ev) {
  unit = document.getElementById("unitsum").value;
  rek = document.getElementById("rek").value;
  tgl1 = document.getElementById("tgl1sum").value;
  tgl2 = document.getElementById("tgl2sum").value;
  param = "method=previewsum";
  param += "&unit=" + unit + "&rek=" + rek;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&tipe=pdf";
  tujuan = "keu_2kasharianv2_slave.php";
  judul = "Report PDF";
  printFile(param, tujuan, judul, ev);
}

function excelsum(ev) {
  unit = document.getElementById("unitsum").value;
  rek = document.getElementById("rek").value;
  tgl1 = document.getElementById("tgl1sum").value;
  tgl2 = document.getElementById("tgl2sum").value;
  param = "method=previewsum";
  param += "&unit=" + unit + "&rek=" + rek;
  param += "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&tipe=excel";
  tujuan = "keu_2kasharianv2_slave.php";
  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}

// ==================================================================================================
//kas

function previewkas() {
  unit = document.getElementById("unitkas").value;
  noakun = document.getElementById("noakunkas").value;
  tglvoc1 = document.getElementById("tglvoc1kas").value;
  tglvoc2 = document.getElementById("tglvoc2kas").value;
  tglinput1 = document.getElementById("tglinput1kas").value;
  tglinput2 = document.getElementById("tglinput2kas").value;
  posting = document.getElementById("postingkas").value;
  tipetransaksi = document.getElementById("tipetransaksikas").value;
  pembayaran = document.getElementById("pembayarankas").value;
  param = "method=previewkas";
  param += "&unit=" + unit + "&noakun=" + noakun;
  param += "&tglvoc1=" + tglvoc1 + "&tglvoc2=" + tglvoc2;
  param += "&tglinput1=" + tglinput1 + "&tglinput2=" + tglinput2;
  param +=
    "&posting=" +
    posting +
    "&tipetransaksi=" +
    tipetransaksi +
    "&pembayaran=" +
    pembayaran;
  tujuan = "keu_2kasharianv2_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("printContainerkas").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelkas() {
  closeDialog();
  document.getElementById("unitkas").value = "";
  document.getElementById("noakunkas").value = "";
  document.getElementById("tglvoc1kas").value = "";
  document.getElementById("tglvoc2kas").value = "";
  document.getElementById("tglinput1kas").value = "";
  document.getElementById("tglinput2kas").value = "";
  document.getElementById("postingkas").value = "";
  document.getElementById("tipetransaksikas").value = "";
  document.getElementById("printContainerkas").innerHTML = "";
}

function pdfkas(ev) {
  unit = document.getElementById("unitkas").value;
  noakun = document.getElementById("noakunkas").value;
  tglvoc1 = document.getElementById("tglvoc1kas").value;
  tglvoc2 = document.getElementById("tglvoc2kas").value;
  tglinput1 = document.getElementById("tglinput1kas").value;
  tglinput2 = document.getElementById("tglinput2kas").value;
  posting = document.getElementById("postingkas").value;
  tipetransaksi = document.getElementById("tipetransaksikas").value;
  pembayaran = document.getElementById("pembayarankas").value;
  param = "method=previewkas";
  param += "&unit=" + unit + "&noakun=" + noakun;
  param += "&tglvoc1=" + tglvoc1 + "&tglvoc2=" + tglvoc2;
  param += "&tglinput1=" + tglinput1 + "&tglinput2=" + tglinput2 + "&tipe=pdf";
  param +=
    "&posting=" +
    posting +
    "&tipetransaksi=" +
    tipetransaksi +
    "&pembayaran=" +
    pembayaran;
  tujuan = "keu_2kasharianv2_slave.php";
  judul = "Report PDF";
  printFile(param, tujuan, judul, ev);
}

function excelkas(ev) {
  posting = document.getElementById("postingkas").value;
  unit = document.getElementById("unitkas").value;
  noakun = document.getElementById("noakunkas").value;
  tglvoc1 = document.getElementById("tglvoc1kas").value;
  tglvoc2 = document.getElementById("tglvoc2kas").value;
  tglinput1 = document.getElementById("tglinput1kas").value;
  tglinput2 = document.getElementById("tglinput2kas").value;
  tipetransaksi = document.getElementById("tipetransaksikas").value;
  pembayaran = document.getElementById("pembayarankas").value;
  param = "method=previewkas";
  param += "&unit=" + unit + "&noakun=" + noakun;
  param += "&tglvoc1=" + tglvoc1 + "&tglvoc2=" + tglvoc2;
  param +=
    "&tglinput1=" + tglinput1 + "&tglinput2=" + tglinput2 + "&tipe=excel";
  param +=
    "&posting=" +
    posting +
    "&tipetransaksi=" +
    tipetransaksi +
    "&pembayaran=" +
    pembayaran;
  tujuan = "keu_2kasharianv2_slave.php";
  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}
