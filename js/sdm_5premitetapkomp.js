// Setup Komponen Tetap (PT PPP): Buat Baru + List Data (per organisasi dan per komponen), header (organisasi + komponen) lalu detail (karyawan)
var slaveKT = "sdm_slave_5premitetapkomp.php";

function kirimKT(param, sukses) {
  post_response_text(slaveKT, param, respon);
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

function tampilBagianKT(list, form, detail) {
  document.getElementById("listDataKT").style.display = list ? "block" : "none";
  document.getElementById("listcariKT").style.display = list ? "block" : "none";
  document.getElementById("formnewKT").style.display = form ? "block" : "none";
  document.getElementById("detailKT").style.display = detail ? "block" : "none";
}

// ---------- List Data + Cari Data ----------
function loaddataKT() {
  var param =
    "method=loadData&unit=" + encodeURIComponent(document.getElementById("unitCariKT").value) +
    "&idkomponen=" + encodeURIComponent(document.getElementById("komponenCariKT").value) +
    "&aktifcari=" + encodeURIComponent(document.getElementById("aktifCariKT").value) +
    "&cari=" + encodeURIComponent(document.getElementById("txtCariKT").value);
  kirimKT(param, function (hasil) {
    document.getElementById("containKT").innerHTML = hasil;
  });
}

// satu sumber filter untuk list, Excel, dan PDF
function getFilterParamKT() {
  return (
    "unit=" + encodeURIComponent(document.getElementById("unitCariKT").value) +
    "&idkomponen=" + encodeURIComponent(document.getElementById("komponenCariKT").value) +
    "&aktifcari=" + encodeURIComponent(document.getElementById("aktifCariKT").value) +
    "&cari=" + encodeURIComponent(document.getElementById("txtCariKT").value.trim())
  );
}

function tampilTarikanKT(judul, tujuan) {
  alertify
    .popup(judul, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>")
    .set({ resizable: true, maximizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function exportExcelKT() {
  tampilTarikanKT("Report Ms.Excel", slaveKT + "?method=excel&" + getFilterParamKT());
}

function exportPdfKT() {
  tampilTarikanKT("Report PDF", slaveKT + "?method=pdf&" + getFilterParamKT());
}

function batalcariKT() {
  setValue2("unitCariKT", "");
  setValue2("komponenCariKT", "");
  setValue2("aktifCariKT", "");
  document.getElementById("txtCariKT").value = "";
  loaddataKT();
}

function displayListKT() {
  tampilBagianKT(true, false, false);
  loaddataKT();
}

// pratinjau hanya-baca (ikon kaca pembesar): daftar karyawan yang sudah disetup pada organisasi + komponen itu
function previewKT(unit, komponen, ev) {
  kirimKT("method=preview&unit=" + encodeURIComponent(unit) + "&idkomponen=" + encodeURIComponent(komponen), function (hasil) {
    showDialog1("Preview Setup Komponen Tetap", hasil, "760", "460", ev);
  });
}

// ---------- Header ----------
function formbaruKT() {
  document.getElementById("unitAktifKT").value = "";
  document.getElementById("komponenAktifKT").value = "";
  setValue2("unitKT", "");
  setValue2("komponenKT", "");
  document.getElementById("unitKT").disabled = false;
  document.getElementById("komponenKT").disabled = false;
  document.getElementById("tombolBukaKT").style.display = "";
  tampilBagianKT(false, true, false);
}

function bukaHeaderKT() {
  var unit = document.getElementById("unitKT").value;
  var komponen = document.getElementById("komponenKT").value;
  if (unit == "") {
    alert("Warning : Organisasi wajib dipilih.");
    return;
  }
  if (komponen == "") {
    alert("Warning : Komponen wajib dipilih.");
    return;
  }
  bukaKT(unit, komponen);
}

// membuka detail untuk satu organisasi + komponen (dari List Data atau dari header baru)
function bukaKT(unit, komponen) {
  document.getElementById("unitAktifKT").value = unit;
  document.getElementById("komponenAktifKT").value = komponen;
  setValue2("unitKT", unit);
  setValue2("komponenKT", komponen);
  document.getElementById("unitKT").disabled = true;
  document.getElementById("komponenKT").disabled = true;
  document.getElementById("tombolBukaKT").style.display = "none";
  var teksUnit = document.getElementById("unitKT");
  var teksKomp = document.getElementById("komponenKT");
  document.getElementById("legendDetailKT").innerHTML =
    "Detail Karyawan - " +
    teksUnit.options[teksUnit.selectedIndex].text +
    " | " +
    teksKomp.options[teksKomp.selectedIndex].text;
  document.getElementById("cariDetailKT").value = "";
  document.getElementById("hanyaSetupKT").checked = false;
  tampilBagianKT(false, true, true);
  loadDetailKT();
}

// ---------- Detail: tabel semua karyawan organisasi ----------
function loadDetailKT() {
  var unit = document.getElementById("unitAktifKT").value;
  var komponen = document.getElementById("komponenAktifKT").value;
  if (unit == "" || komponen == "") {
    return;
  }
  kirimKT("method=loadDetail&unit=" + encodeURIComponent(unit) + "&idkomponen=" + encodeURIComponent(komponen), function (hasil) {
    var bagian = hasil.split("##");
    document.getElementById("containDetailKT").innerHTML = bagian[0];
    document.getElementById("ringkasDetailKT").innerHTML = bagian[1] || "";
    saringDetailKT();
  });
}

// saring baris menurut kata kunci dan "hanya yang sudah disetup"
function saringDetailKT() {
  var kata = document.getElementById("cariDetailKT").value.toLowerCase();
  var hanya = document.getElementById("hanyaSetupKT").checked;
  var baris = document.getElementById("containDetailKT").getElementsByTagName("tr");
  for (var i = 0; i < baris.length; i++) {
    var cari = baris[i].getAttribute("data-cari");
    if (cari === null) {
      continue;
    }
    var cocok = (kata == "" || cari.indexOf(kata) >= 0) && (!hanya || baris[i].getAttribute("data-setup") == "1");
    baris[i].style.display = cocok ? "" : "none";
  }
}

// pemisah ribuan saat mengetik nominal
function formatNominalKT(el) {
  var angka = el.value.replace(/[^0-9]/g, "");
  el.value = angka == "" ? "" : angka.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// simpan semua baris yang nominalnya terisi (yang kosong dilewati)
function simpanSemuaKT() {
  var unit = document.getElementById("unitAktifKT").value;
  var komponen = document.getElementById("komponenAktifKT").value;
  if (unit == "" || komponen == "") {
    alert("Warning : Pilih organisasi dan komponen terlebih dahulu.");
    return;
  }
  var baris = document.getElementById("containDetailKT").getElementsByTagName("tr");
  var data = [];
  for (var i = 0; i < baris.length; i++) {
    var input = baris[i].querySelector("input.myinputtextnumber");
    var centang = baris[i].querySelector("input[type=checkbox]");
    if (!input) {
      continue;
    }
    var nominal = input.value.replace(/,/g, "");
    if (nominal == "") {
      continue;
    }
    if (isNaN(nominal) || parseFloat(nominal) <= 0) {
      alert("Warning : Nominal harus angka lebih dari 0 (baris " + (i + 1) + ").");
      return;
    }
    if (parseFloat(nominal) > 100000000) {
      alert("Warning : Nominal terlalu besar (maksimal 100.000.000 per bulan), baris " + (i + 1) + ".");
      return;
    }
    data.push({ id: input.getAttribute("data-kid"), nominal: nominal, aktif: centang && centang.checked ? "1" : "0" });
  }
  if (data.length == 0) {
    alert("Warning : Belum ada nominal yang diisi.");
    return;
  }
  var param =
    "method=saveBanyak&unit=" + encodeURIComponent(unit) +
    "&idkomponen=" + encodeURIComponent(komponen) +
    "&data=" + encodeURIComponent(JSON.stringify(data));
  kirimKT(param, function (hasil) {
    alert(hasil);
    loadDetailKT();
  });
}

function hapusKT(karyawan) {
  if (!confirm("Hapus karyawan ini dari setup?")) {
    return;
  }
  var param =
    "method=delete&unit=" + encodeURIComponent(document.getElementById("unitAktifKT").value) +
    "&idkomponen=" + encodeURIComponent(document.getElementById("komponenAktifKT").value) +
    "&karyawanid=" + encodeURIComponent(karyawan);
  kirimKT(param, function () {
    loadDetailKT();
  });
}
