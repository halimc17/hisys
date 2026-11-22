// Setup Alokasi Komponen Gaji per PT: tab 1 Komponen Upah (per jabatan), tab 2 Pendapatan Lain (per komponen, semua jabatan atau jabatan tertentu)
var slave5alokasi = "keu_slave_5alokasikomponen.php";

// satu koneksi global (con): permintaan dikirim satu per satu; fungsi sesudah dipanggil bila ada
function kirim5(param, sukses) {
  post_response_text(slave5alokasi, param, respon);
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

function tampilTab5(tab) {
  var upah = tab == "upah";
  document.getElementById("tabUpah5").style.display = upah ? "block" : "none";
  document.getElementById("tabLain5").style.display = upah ? "none" : "block";
  document.getElementById("btnTabUpah5").className = "mybutton" + (upah ? " aktif5" : "");
  document.getElementById("btnTabLain5").className = "mybutton" + (upah ? "" : " aktif5");
}

function loadUpah5(sesudah) {
  kirim5("method=loadUpah", function (hasil) {
    document.getElementById("containUpah5").innerHTML = hasil;
    if (sesudah) {
      sesudah();
    }
  });
}

function loadLain5() {
  kirim5("method=loadLain", function (hasil) {
    document.getElementById("containLain5").innerHTML = hasil;
  });
}

function muatSemua5() {
  loadUpah5(loadLain5);
}

// ---------- Tab 1: Komponen Upah ----------
function simpanUpah5() {
  var jabatan = document.getElementById("jabatanUpah5").value;
  var akun = document.getElementById("akunUpah5").value;
  var aktif = document.getElementById("aktifUpah5").value;
  if (jabatan == "") {
    alert("Warning : Jabatan wajib dipilih.");
    return;
  }
  if (akun == "") {
    alert("Warning : Akun Debet wajib dipilih.");
    return;
  }
  if (akun.charAt(0) != "7") {
    alert("Warning : Akun Debet harus berawalan 7.");
    return;
  }
  var param =
    "method=saveUpah&kodejabatan=" + encodeURIComponent(jabatan) +
    "&noakundebet=" + encodeURIComponent(akun) +
    "&aktif=" + encodeURIComponent(aktif);
  kirim5(param, function () {
    batalUpah5();
    loadUpah5();
  });
}

function editUpah5(jabatan, akun, aktif) {
  setValue2("jabatanUpah5", String(jabatan));
  document.getElementById("jabatanUpah5").disabled = true;
  setValue2("akunUpah5", akun);
  setValue2("aktifUpah5", String(aktif));
}

function batalUpah5() {
  document.getElementById("jabatanUpah5").disabled = false;
  setValue2("jabatanUpah5", "");
  setValue2("akunUpah5", "");
  setValue2("aktifUpah5", "1");
}

// ---------- Tab 2: Pendapatan Lain ----------
function simpanLain5() {
  var komponen = document.getElementById("komponenLain5").value;
  var jabatan = document.getElementById("jabatanLain5").value;
  var jenis = document.getElementById("jenisLain5").value;
  var akun = document.getElementById("akunLain5").value;
  var aktif = document.getElementById("aktifLain5").value;
  if (komponen == "") {
    alert("Warning : Komponen wajib dipilih.");
    return;
  }
  if (jabatan == "") {
    jabatan = "0";
  }
  if (jabatan == "0" && jenis == "") {
    alert("Warning : Jenis Biaya wajib dipilih untuk aturan semua jabatan.");
    return;
  }
  if (jabatan != "0" && jenis == "" && akun == "") {
    alert("Warning : Aturan per jabatan harus mengisi Jenis Biaya atau Akun Debet.");
    return;
  }
  if (akun != "" && akun.charAt(0) != "7") {
    alert("Warning : Akun Debet harus berawalan 7.");
    return;
  }
  var param =
    "method=saveLain&kodejabatan=" + encodeURIComponent(jabatan) +
    "&idkomponen=" + encodeURIComponent(komponen) +
    "&jenisbiaya=" + encodeURIComponent(jenis) +
    "&noakundebet=" + encodeURIComponent(akun) +
    "&aktif=" + encodeURIComponent(aktif);
  kirim5(param, function () {
    batalLain5();
    loadLain5();
  });
}

function editLain5(jabatan, komponen, jenis, akun, aktif) {
  setValue2("komponenLain5", String(komponen));
  setValue2("jabatanLain5", String(jabatan));
  document.getElementById("komponenLain5").disabled = true;
  document.getElementById("jabatanLain5").disabled = true;
  setValue2("jenisLain5", jenis);
  setValue2("akunLain5", akun);
  setValue2("aktifLain5", String(aktif));
  tampilDefault5alokasi();
}

function batalLain5() {
  document.getElementById("komponenLain5").disabled = false;
  document.getElementById("jabatanLain5").disabled = false;
  setValue2("komponenLain5", "");
  setValue2("jabatanLain5", "0");
  setValue2("jenisLain5", "");
  setValue2("akunLain5", "");
  setValue2("aktifLain5", "1");
  tampilDefault5alokasi();
}

// ---------- Hapus (kedua tab) ----------
function hapus5(jabatan, komponen, tab) {
  if (!confirm("Hapus aturan ini?")) {
    return;
  }
  kirim5(
    "method=delete&kodejabatan=" + encodeURIComponent(jabatan) + "&idkomponen=" + encodeURIComponent(komponen),
    function () {
      if (tab == "upah") {
        loadUpah5();
      } else {
        loadLain5();
      }
    }
  );
}

// pratinjau akun default (dari parameter jurnal) untuk jenis biaya yang dipilih, per bagian
function tampilDefault5alokasi() {
  var el = document.getElementById("infoDefault");
  if (!el) {
    return;
  }
  var jenis = document.getElementById("jenisLain5").value;
  if (jenis == "" || typeof defaultJenis5 === "undefined" || !defaultJenis5[jenis]) {
    el.innerHTML = "";
    return;
  }
  var baris = defaultJenis5[jenis];
  var html = "<b>Akun bawaan jenis biaya ini:</b><br>";
  for (var i = 0; i < baris.length; i++) {
    html += baris[i].replace(/&/g, "&amp;").replace(/</g, "&lt;") + "<br>";
  }
  el.innerHTML = html;
}
