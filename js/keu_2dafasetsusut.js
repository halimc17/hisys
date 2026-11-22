// Laporan Daftar Aset dan Penyusutan: filter, tampil, Excel, PDF
var slaveAD = "keu_slave_2dafasetsusut.php";

// satu sumber filter untuk tampil, Excel, dan PDF
function getFilterParamAD() {
  return (
    "pt=" + encodeURIComponent(document.getElementById("ptAD").value) +
    "&unit=" + encodeURIComponent(document.getElementById("unitAD").value) +
    "&periode=" + encodeURIComponent(document.getElementById("periodeAD").value) +
    "&tipe=" + encodeURIComponent(document.getElementById("tipeAD").value) +
    "&status=" + encodeURIComponent(document.getElementById("statusAD").value) +
    "&cari=" + encodeURIComponent(document.getElementById("cariAD").value.trim())
  );
}

function validasiAD() {
  if (document.getElementById("ptAD").value == "") {
    alert("Warning : PT wajib dipilih.");
    return false;
  }
  if (document.getElementById("periodeAD").value == "") {
    alert("Warning : Periode wajib dipilih.");
    return false;
  }
  return true;
}

function kirimAD(param, sukses) {
  post_response_text(slaveAD, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          sukses(con.responseText);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

// PT dan periode yang dipakai pada tampilan terakhir (untuk popup jurnal), supaya tidak ikut berubah bila filter diubah sebelum klik Cari
var ptTampilAD = "";
var periodeTampilAD = "";

function cariAD() {
  if (!validasiAD()) {
    return;
  }
  var pt = document.getElementById("ptAD").value;
  var periode = document.getElementById("periodeAD").value;
  kirimAD("method=list&" + getFilterParamAD(), function (hasil) {
    ptTampilAD = pt;
    periodeTampilAD = periode;
    document.getElementById("containAD").innerHTML = hasil;
  });
}

// klik baris aset: popup jurnal penyusutan aset itu sampai periode laporan
function jurnalAD(kodeAset, ev) {
  if (ptTampilAD == "" || periodeTampilAD == "") {
    return;
  }
  kirimAD(
    "method=jurnal&pt=" + encodeURIComponent(ptTampilAD) +
      "&periode=" + encodeURIComponent(periodeTampilAD) +
      "&kodeasset=" + encodeURIComponent(kodeAset),
    function (hasil) {
      showDialog1("Jurnal Penyusutan " + kodeAset, hasil, "900", "460", ev);
    }
  );
}

// pilihan unit mengikuti PT; fungsi sesudah dipanggil bila ada
function ubahPtAD(sesudah) {
  var pt = document.getElementById("ptAD").value;
  kirimAD("method=unit&pt=" + encodeURIComponent(pt), function (hasil) {
    document.getElementById("unitAD").innerHTML = hasil;
    setValue2("unitAD", "");
    if (typeof sesudah === "function") {
      sesudah();
    }
  });
}

// saat halaman dibuka hanya pilihan unit yang dimuat; laporan tampil setelah klik Cari
function mulaiAD() {
  ubahPtAD();
}

function tampilTarikanAD(judul, tujuan) {
  alertify
    .popup(judul, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>")
    .set({ resizable: true, maximizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function exportExcelAD() {
  if (!validasiAD()) {
    return;
  }
  tampilTarikanAD("Report Ms.Excel", slaveAD + "?method=excellist&" + getFilterParamAD());
}

function exportPdfAD() {
  if (!validasiAD()) {
    return;
  }
  tampilTarikanAD("Report PDF", slaveAD + "?method=pdflist&" + getFilterParamAD());
}
