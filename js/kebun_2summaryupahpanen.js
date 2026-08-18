// Mode freeze untuk tabel laporan:
// - "header" : hanya header tabel yang dibekukan
// - "table"  : seluruh tabel dibekukan
var summaryUpahPanenFreezeMode = "header";

function applySummaryUpahPanenFreeze(container) {
  var target = document.getElementById(container);

  if (!target || typeof freezeTable != "function") {
    return;
  }

  freezeTable(target, summaryUpahPanenFreezeMode);
}

function clearSummaryUpahPanenFreeze(container) {
  var target = document.getElementById(container);

  if (!target || typeof unfreezeTable != "function") {
    return;
  }

  unfreezeTable(target);
}

function getDivisi(tabIndex) {
  kodeorg = document.getElementById("unit" + tabIndex).value;

  param = "proses=getDivisi";
  param += "&unit=" + kodeorg;
  tujuan = "kebun_slave_2summaryupahpanen.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("div" + tabIndex).innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadData(tabIndex, mode) {
  var unit = document.getElementById("unit" + tabIndex).value;
  var div = document.getElementById("div" + tabIndex).value;
  var tgl = document.getElementById("tgl" + tabIndex).value;
  var tglx = document.getElementById("tglx" + tabIndex).value;

  if (tgl == "" || tglx == "") {
    alertify.alert("Warning: Tanggal wajib diisi!");
    return;
  }

  var proses = "";
  switch (tabIndex) {
    case 0:
      proses = "previewPanen";
      break;
    case 1:
      proses = "previewRawat";
      break;
    case 2:
      proses = "previewBMTBS";
      break;
    case 3:
      proses = "previewTraksi";
      break;
    case 4:
      proses = "previewUmum";
      break;
    case 5:
      proses = "preview";
      break;
  }

  var param =
    "proses=" +
    proses +
    "&unit=" +
    unit +
    "&div=" +
    div +
    "&tgl=" +
    tgl +
    "&tglx=" +
    tglx;

  if (mode == "excel") {
    zExcel(
      "event",
      "kebun_slave_2summaryupahpanen.php",
      "##unit" +
        tabIndex +
        "##div" +
        tabIndex +
        "##tgl" +
        tabIndex +
        "##tglx" +
        tabIndex,
    );
    return;
  }

  var tujuan = "kebun_slave_2summaryupahpanen.php";
  var container = "printContainer" + tabIndex;

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          clearSummaryUpahPanenFreeze(container);
          document.getElementById(container).innerHTML = con.responseText;
          applySummaryUpahPanenFreeze(container);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
