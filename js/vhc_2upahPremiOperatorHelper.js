// JavaScript Document

function getTahunBulan(tanggal) {
  var bagian = tanggal.split(/[-\/]/);
  var tahun = "";
  var bulan = "";

  if (bagian.length != 3) {
    return "";
  }

  if (bagian[0].length == 4) {
    tahun = bagian[0];
    bulan = bagian[1];
  } else {
    tahun = bagian[2];
    bulan = bagian[1];
  }

  return tahun + "-" + parseInt(bulan, 10);
}

function getNilaiTanggal(tanggal) {
  var bagian = tanggal.split(/[-\/]/);
  var tahun = 0;
  var bulan = 0;
  var hari = 0;

  if (bagian.length != 3) {
    return 0;
  }

  if (bagian[0].length == 4) {
    tahun = parseInt(bagian[0], 10);
    bulan = parseInt(bagian[1], 10);
    hari = parseInt(bagian[2], 10);
  } else {
    hari = parseInt(bagian[0], 10);
    bulan = parseInt(bagian[1], 10);
    tahun = parseInt(bagian[2], 10);
  }

  return tahun * 10000 + bulan * 100 + hari;
}

function cekRangeBulan(tglAwal, tglAkhir) {
  return getTahunBulan(tglAwal) == getTahunBulan(tglAkhir);
}

function aturFreezeSummary() {
  var contain = getById("contain");
  var summary = getById("vhc_summary_freeze");
  var tinggiSummary = 0;

  if (!contain) {
    return;
  }

  if (summary) {
    tinggiSummary = summary.offsetHeight;
  }

  if (contain.style.setProperty) {
    contain.style.setProperty("--vhc-summary-height", tinggiSummary + "px");
  }
}

function save_pil() {
  comId =
    document.getElementById("company_id").options[
      document.getElementById("company_id").selectedIndex
    ].value;
  jnsVhc =
    document.getElementById("jnsVhc").options[
      document.getElementById("jnsVhc").selectedIndex
    ].value;
  kdVhc =
    document.getElementById("kdVhc").options[
      document.getElementById("kdVhc").selectedIndex
    ].value;
  alokasi =
    document.getElementById("alokasi").options[
      document.getElementById("alokasi").selectedIndex
    ].value;
  tglAwl = document.getElementById("tglAwal").value;
  tglAkhr = document.getElementById("tglAkhir").value;
  akun =
    document.getElementById("akun").options[
      document.getElementById("akun").selectedIndex
    ].value;
  tipeReport =
    document.getElementById("tipeReport").options[
      document.getElementById("tipeReport").selectedIndex
    ].value;

  if (comId == "") {
    alertify.alert("Unit Tidak Boleh Kosong");
    return false;
  }
  if (tglAwl == "" || tglAkhr == "") {
    alertify.alert("Tanggal Tidak Boleh Kosong");
    return false;
  }
  if (!cekRangeBulan(tglAwl, tglAkhr)) {
    alertify.alert("Range Tanggal Tidak Boleh Melewati Bulan");
    return false;
  }
  if (getNilaiTanggal(tglAwl) > getNilaiTanggal(tglAkhr)) {
    alertify.alert("Tanggal Awal Tidak Boleh Lebih Besar Dari Tanggal Akhir");
    return false;
  }

  param =
    "comId=" +
    comId +
    "&kdVhc=" +
    kdVhc +
    "&proses=get_result" +
    "&jnsVhc=" +
    jnsVhc +
    "&tglAkhir=" +
    tglAkhr +
    "&alokasi=" +
    alokasi;
  param += "&tglAwal=" + tglAwl + "&akun=" + akun + "&tipeReport=" + tipeReport;

  tujuan = "vhc_slave_2upahPremiOperatorHelper.php";
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
          aturFreezeSummary();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ganti_pil() {
  setValue2("company_id", "");
  setValue2("kdVhc", "");
  setValue2("alokasi", "");
  setValue2("jnsVhc", "");
  document.getElementById("tglAwal").value = "";
  document.getElementById("tglAkhir").value = "";
  setValue2("akun", "");
  setValue2("tipeReport", "rekap");
  document.getElementById("contain").innerHTML = "";
  aturFreezeSummary();
}

function get_jnsVhc() {
  comId =
    document.getElementById("company_id").options[
      document.getElementById("company_id").selectedIndex
    ].value;
  param = "comId=" + comId + "&proses=getJnsVhc";
  tujuan = "vhc_slave_2upahPremiOperatorHelper.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("jnsVhc").innerHTML = con.responseText;
          setValue2("jnsVhc", "");
          document.getElementById("kdVhc").innerHTML =
            "<option value=''>Semua</option>";
          setValue2("kdVhc", "");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getKdVhc() {
  comId =
    document.getElementById("company_id").options[
      document.getElementById("company_id").selectedIndex
    ].value;
  jnsVhc =
    document.getElementById("jnsVhc").options[
      document.getElementById("jnsVhc").selectedIndex
    ].value;
  param = "jnsVhc=" + jnsVhc + "&comId=" + comId + "&proses=getKdvhc";
  tujuan = "vhc_slave_2upahPremiOperatorHelper.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("kdVhc").innerHTML = con.responseText;
          setValue2("kdVhc", "");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function dataKeExcel(ev, tujuan) {
  comId =
    document.getElementById("company_id").options[
      document.getElementById("company_id").selectedIndex
    ].value;
  jnsVhc =
    document.getElementById("jnsVhc").options[
      document.getElementById("jnsVhc").selectedIndex
    ].value;
  kdVhc =
    document.getElementById("kdVhc").options[
      document.getElementById("kdVhc").selectedIndex
    ].value;
  alokasi =
    document.getElementById("alokasi").options[
      document.getElementById("alokasi").selectedIndex
    ].value;
  tglAwl = document.getElementById("tglAwal").value;
  tglAkhr = document.getElementById("tglAkhir").value;
  akun =
    document.getElementById("akun").options[
      document.getElementById("akun").selectedIndex
    ].value;
  tipeReport =
    document.getElementById("tipeReport").options[
      document.getElementById("tipeReport").selectedIndex
    ].value;

  if (comId == "") {
    alertify.alert("Unit Tidak Boleh Kosong");
    return false;
  }
  if (tglAwl == "" || tglAkhr == "") {
    alertify.alert("Tanggal Tidak Boleh Kosong");
    return false;
  }
  if (!cekRangeBulan(tglAwl, tglAkhr)) {
    alertify.alert("Range Tanggal Tidak Boleh Melewati Bulan");
    return false;
  }
  if (getNilaiTanggal(tglAwl) > getNilaiTanggal(tglAkhr)) {
    alertify.alert("Tanggal Awal Tidak Boleh Lebih Besar Dari Tanggal Akhir");
    return false;
  }

  judul = "Report Ms.Excel";
  param =
    "comId=" +
    comId +
    "&kdVhc=" +
    kdVhc +
    "&proses=excel" +
    "&jnsVhc=" +
    jnsVhc +
    "&tglAkhir=" +
    tglAkhr +
    "&alokasi=" +
    alokasi;
  param += "&tglAwal=" + tglAwl + "&akun=" + akun + "&tipeReport=" + tipeReport;

  printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

if (typeof jQuery != "undefined") {
  $(window).resize(function () {
    aturFreezeSummary();
  });
}
