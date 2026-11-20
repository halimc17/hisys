// Setup Alokasi Komponen Gaji per PT (satu setup: komponen semua jabatan dan komponen + jabatan tertentu)
var slave5alokasi = "keu_slave_5alokasikomponen.php";

function loadData5alokasi() {
  post_response_text(slave5alokasi, "method=loadData", respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpan5alokasi() {
  var kodejabatan = document.getElementById("kodejabatan").value;
  var idkomponen = document.getElementById("idkomponen").value;
  var jenisbiaya = document.getElementById("jenisbiaya").value;
  var noakundebet = document.getElementById("noakundebet").value;
  var aktif = document.getElementById("aktif").value;

  if (idkomponen == "") {
    alert("Warning : Komponen wajib dipilih.");
    return;
  }
  if (idkomponen == "0") {
    if (kodejabatan == "0") {
      alert("Warning : Aturan Semua komponen harus dipilihkan jabatan tertentu.");
      return;
    }
    if (jenisbiaya != "") {
      alert("Warning : Aturan Semua komponen hanya mengatur Akun Debet; kosongkan Jenis Biaya.");
      return;
    }
    if (noakundebet == "") {
      alert("Warning : Aturan Semua komponen wajib mengisi Akun Debet.");
      return;
    }
  } else if (kodejabatan == "0" && jenisbiaya == "") {
    alert("Warning : Jenis Biaya wajib dipilih untuk aturan semua jabatan.");
    return;
  } else if (kodejabatan != "0" && jenisbiaya == "" && noakundebet == "") {
    alert("Warning : Aturan per jabatan harus mengisi Jenis Biaya atau Akun Debet.");
    return;
  }
  if (noakundebet != "" && noakundebet.charAt(0) != "7") {
    alert("Warning : Akun Debet harus berawalan 7.");
    return;
  }

  var param =
    "method=save&kodejabatan=" +
    encodeURIComponent(kodejabatan) +
    "&idkomponen=" +
    encodeURIComponent(idkomponen) +
    "&jenisbiaya=" +
    encodeURIComponent(jenisbiaya) +
    "&noakundebet=" +
    encodeURIComponent(noakundebet) +
    "&aktif=" +
    encodeURIComponent(aktif);
  post_response_text(slave5alokasi, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          batal5alokasi();
          loadData5alokasi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function edit5alokasi(kodejabatan, idkomponen, jenisbiaya, noakundebet, aktif) {
  setValue2("kodejabatan", String(kodejabatan));
  setValue2("idkomponen", String(idkomponen));
  document.getElementById("kodejabatan").disabled = true;
  document.getElementById("idkomponen").disabled = true;
  setValue2("jenisbiaya", jenisbiaya);
  setValue2("noakundebet", noakundebet);
  setValue2("aktif", String(aktif));
}

function batal5alokasi() {
  document.getElementById("kodejabatan").disabled = false;
  document.getElementById("idkomponen").disabled = false;
  setValue2("kodejabatan", "0");
  setValue2("idkomponen", "");
  setValue2("jenisbiaya", "");
  setValue2("noakundebet", "");
  setValue2("aktif", "1");
}

function hapus5alokasi(kodejabatan, idkomponen) {
  if (!confirm("Hapus aturan ini?")) {
    return;
  }
  post_response_text(
    slave5alokasi,
    "method=delete&kodejabatan=" +
      encodeURIComponent(kodejabatan) +
      "&idkomponen=" +
      encodeURIComponent(idkomponen),
    respon
  );
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadData5alokasi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//pratinjau akun default (dari parameter jurnal) untuk jenis biaya yang dipilih, per bagian
function tampilDefault5alokasi() {
  var el = document.getElementById("infoDefault");
  if (!el) {
    return;
  }
  var jenis = document.getElementById("jenisbiaya").value;
  if (jenis == "" || typeof defaultJenis5 === "undefined" || !defaultJenis5[jenis]) {
    el.innerHTML = "";
    return;
  }
  var baris = defaultJenis5[jenis];
  var html = "<b>Akun default parameter jurnal:</b><br>";
  for (var i = 0; i < baris.length; i++) {
    html += baris[i].replace(/&/g, "&amp;").replace(/</g, "&lt;") + "<br>";
  }
  el.innerHTML = html;
}