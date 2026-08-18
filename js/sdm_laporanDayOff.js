function loadkaryawan() {
  lokasitugas = document.getElementById("lokasitugas");
  lokasitugas = lokasitugas.options[lokasitugas.selectedIndex].value;
  tujuan = "sdm_slave_getLaporanDayOff.php";
  param = "kodeorg=" + lokasitugas + "&method=loadkaryawan";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("karyawan").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadLaporan() {
  lokasitugas = document.getElementById("lokasitugas");
  lokasitugas = lokasitugas.options[lokasitugas.selectedIndex].value;
  periode = document.getElementById("periode");
  periode = periode.options[periode.selectedIndex].value;
  karyawan = document.getElementById("karyawan");
  karyawan = karyawan.options[karyawan.selectedIndex].value;

  param =
    "kodeorg=" +
    lokasitugas +
    "&periode=" +
    periode +
    "&karyawan=" +
    karyawan +
    "&method=preview";
  tujuan = "sdm_slave_getLaporanDayOff.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerlist1").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cutiToExcel(kodeorg, periode, karyawan, ev) {
  param =
    "kodeorg=" +
    kodeorg +
    "&periode=" +
    periode +
    "&karyawan=" +
    karyawan +
    "&method=excel";
  tujuan = "sdm_slave_dayoff_Excel.php?" + param;
  //display window
  title = "Download";
  width = "500";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function previewPdfstaff(tgl, karywn, ev) {
  tglijin = tgl;
  krywnId = karywn;
  param = "proses=prevPdf" + "&tglijin=" + tglijin + "&krywnId=" + krywnId;
  tujuan = "sdm_slave_laporan_ijin_meninggalkan_kantor.php?" + param;
  //display window
  title = "Print PDF";
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
  // alert(param);
}

function previewPdfnonstaff(tgl, karywn, ev) {
  tglijin = tgl;
  krywnId = karywn;
  param = "proses=prevPdf" + "&tglijin=" + tglijin + "&krywnId=" + krywnId;
  tujuan = "sdm_slave_laporan_ijin_meninggalkan_kantornonstaff.php?" + param;
  //display window
  title = "Print PDF";
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
  // alert(param);
}

function viewpdf(notransaksi) {
  param = "method=viewpdf" + "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_getLaporanDayOff.php?" + param;
  content =
    "<iframe frameborder=0 style='width:100%;height:99%' src='" +
    tujuan +
    "'></iframe>";
  width = "820";
  height = "500";
  title = "";
  showDialog5(title, content, width, height, "event");
}
