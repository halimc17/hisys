function detailData(notransaksi, ev, jenis) {
  width = 1024;
  height = 400;

  content =
    '<fieldset style=width:98%><div id=containerd style="height:385px;width:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "Preview";
  //showDialog4(title, content, width, height, ev);

  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_2pjdinasx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //document.getElementById('containerd').innerHTML = con.responseText;
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

function detailPDF(notransaksi, ev, jenis) {
  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_2pjdinasx.php" + "?" + param;
  width = 1024;
  height = 400;
  ev = "event";
  title = "Preview";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  //showDialog1(title, content, width, height, ev);

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function detailExcel(notransaksi, ev, jenis) {
  judul = "Report Ms.Excel";
  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  printFile(param, tujuan, judul, ev);
}

function batallist() {
  $("#unit").val("").trigger("change");
  $("#notransaksilist").val("");
  $("#namakarylist").val("").trigger("change");
  $("#tanggaldari").val("").trigger("change");
  $("#tanggalsampai").val("").trigger("change");
  $("#departemen").val("").trigger("change");
  $("#jabatan").val("").trigger("change");

  document.querySelector("#contain").innerHTML = "";
  document.querySelector("#footData").innerHTML = "";
}

function loaddata(page) {
  notransaksilist = document.getElementById("notransaksilist").value;
  namakarylist = document.getElementById("namakarylist").value;
  unit = document.getElementById("unit").value;
  tanggaldari = document.getElementById("tanggaldari").value;
  tanggalsampai = document.getElementById("tanggalsampai").value;
  departemen = document.getElementById("departemen").value;
  jabatan = document.getElementById("jabatan").value;

  if (tanggaldari == "") return alertify.alert("Tanggal dari harus diisi");
  if (tanggalsampai == "") return alertify.alert("Tanggal sampai harus diisi");

  param = "method=loaddata&page=" + page;
  param += "&notransaksi=" + notransaksilist;
  param += "&namakarylist=" + namakarylist;
  param += "&unit=" + unit;
  param += "&tanggaldari=" + tanggaldari;
  param += "&tanggalsampai=" + tanggalsampai;
  param += "&departemen=" + departemen;
  param += "&jabatan=" + jabatan;

  tujuan = "sdm_slave_2pjdinasx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contain").innerHTML = con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function excel() {
  notransaksilist = document.getElementById("notransaksilist").value;
  namakarylist = document.getElementById("namakarylist").value;
  unit = document.getElementById("unit").value;
  tanggaldari = document.getElementById("tanggaldari").value;
  tanggalsampai = document.getElementById("tanggalsampai").value;
  departemen = document.getElementById("departemen").value;
  jabatan = document.getElementById("jabatan").value;

  if (tanggaldari == "") return alertify.alert("Tanggal dari harus diisi");
  if (tanggalsampai == "") return alertify.alert("Tanggal sampai harus diisi");

  param = "method=excel";
  param += "&notransaksi=" + notransaksilist;
  param += "&namakarylist=" + namakarylist;
  param += "&unit=" + unit;
  param += "&tanggaldari=" + tanggaldari;
  param += "&tanggalsampai=" + tanggalsampai;
  param += "&departemen=" + departemen;
  param += "&jabatan=" + jabatan;

  printnopopup("sdm_slave_2pjdinasx.php?" + param);
}
