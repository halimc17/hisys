var checkedValue = "";

function displayFormInput() {
  document.getElementById("formInput").style.display = "block";
  document.getElementById("listData").style.display = "none";

  clearForm1();
}

function displaylist() {
  document.getElementById("crjmldayoff").value = "";
  document.getElementById("listData").style.display = "block";
  document.getElementById("formInput").style.display = "none";
  clearForm1();
  loadData(0);
}

function loadData(num) {
  tglpengajuansch = trim(document.getElementById("tglpengajuansch").value);
  tgldarisch = trim(document.getElementById("tgldarisch").value);
  crjmldayoff = trim(document.getElementById("crjmldayoff").value);
  notransaksi = trim(document.getElementById("notransaksi").value);

  param = "method=loadData";

  param +=
    "&tglpengajuansch=" +
    tglpengajuansch +
    "&tgldarisch=" +
    tgldarisch +
    "&notransaksi=" +
    notransaksi +
    "&crjmldayoff=" +
    crjmldayoff;
  param += "&page=" + num;

  // alert(crjmldayoff);
  // return;

  tujuan = "sdm_slave_dayoff_nonstaff.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("continerlist").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalcari() {
  document.getElementById("tglpengajuansch").value = "";
  document.getElementById("tgldarisch").value = "";
  document.getElementById("tglsampaisch").value = "";
  document.getElementById("crjmldayoff").value = "";
  document.getElementById("notransaksi").value = "";
  loadData();
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.ounitions[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loadData(paged);
}

function saveData() {
  tglpengajuan = trim(document.getElementById("tglpengajuan").value);
  notransaksi = trim(document.getElementById("notransaksi2").value);
  tglAwal = trim(document.getElementById("tglAwal").value);
  // tglEnd = trim(document.getElementById("tglEnd").value);
  // tanggalkerja = trim(document.getElementById("tanggalkerja").value);
  jmldayoff = trim(document.getElementById("jmldayoff").value);
  karyawanid = trim(document.getElementById("karyawanid").value);
  keterangan = trim(document.getElementById("keterangan").value);

  method = trim(document.getElementById("method").value);

  param =
    "notransaksi=" +
    notransaksi +
    "&tglpengajuan=" +
    tglpengajuan +
    "&tglAwal=" +
    tglAwal +
    // "&tglEnd=" +
    // tglEnd +
    // "&tanggalkerja=" +
    // tanggalkerja +
    "&jmldayoff=" +
    jmldayoff +
    "&karyawanid=" +
    karyawanid +
    "&keterangan=" +
    keterangan +
    "&method=" +
    method;

  trapproval = document.getElementById("trapproval").innerHTML;
  if (trapproval == "") {
    alert("Please contact administrator to setup Approval.");
    return;
  }

  //Approval
  var tbl = document.getElementById("trapproval");
  var row = parseFloat(tbl.rows.length) + 1;
  strUrl = "";
  for (i = 1; i < row; i++) {
    if (document.getElementById("persetujuan" + i).innerHTML == "") {
      alert("Please contact administrator to setup Approval.");
      return false;
    }
    persetujuan = document.getElementById("persetujuan" + i).options[
      document.getElementById("persetujuan" + i).selectedIndex
    ].value;
    if (persetujuan == "") {
      alert("Please compelete Approval");
      return;
    }
    strUrl += "&persetujuan[" + i + "]=" + persetujuan;
  }
  param += strUrl;
  if (keterangan == "") {
    alert("Keterangan wajib diisi");
    return;
  } else {
    tujuan = "sdm_slave_dayoff_nonstaff.php";
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          clearForm1();
          displaylist();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewpdf(notransaksi) {
  param = "method=viewpdf" + "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_dayoff_nonstaff.php?" + param;
  content =
    "<iframe frameborder=0 style='width:100%;height:99%' src='" +
    tujuan +
    "'></iframe>";
  width = "820";
  height = "500";
  title = "";
  showDialog5(title, content, width, height, "event");
}

function editdt(
  notransaksi,
  karyawanid,
  tanggalpengajuan,
  tanggalmulai,
  jumlahharidayoff,
  keterangan
) {
  //show hide tombol ubah
  document.getElementById("tombolsave").style.display = "none"; //save
  document.getElementById("tomboledit").style.display = "inline"; //edit

  document.getElementById("notransaksi2").value = notransaksi;
  document.getElementById("karyawanid").value = karyawanid;
  document.getElementById("karyawanid").disabled = true;
  document.getElementById("tglpengajuan").value = tanggalpengajuan;
  document.getElementById("tglpengajuan").disabled = true;
  document.getElementById("tglAwal").value = tanggalmulai;
  document.getElementById("jmldayoff").value = jumlahharidayoff;
  document.getElementById("keterangan").value = keterangan;

  document.getElementById("method").value = "updateht";
  document.getElementById("formInput").style.display = "block";
  document.getElementById("listData").style.display = "none";

  // alert (jumlahharidayoff);
  // return;
}

function clearForm1() {
  document.getElementById("karyawanid").disabled = false;
  document.getElementById("tombolsave").style.display = "inline"; //save
  document.getElementById("tomboledit").style.display = "none"; //edit
  document.getElementById("notransaksi2").value = "";
  document.querySelector("#tglAwal").value = "";
  document.querySelector("#jmldayoff").value = 0;
  document.querySelector("#keterangan").value = "";

  document.getElementById("method").value = "insertht";
}

function deleteht(notrans) {
  param = "method=deleteht" + "&notransaksi=" + notrans;
  tujuan = "sdm_slave_dayoff_nonstaff.php";
  if (confirm(" Anda yakin ingin menghapus data ini?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          displaylist();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getjmldayoff() {
  tglAwal = document.getElementById("tglAwal").value;
  // tglEnd = document.getElementById("tglEnd").value;

  param = "method=getjmldayoff&tglAwal=" + tglAwal;
  // alert(param);

  tujuan = "sdm_slave_dayoff_nonstaff.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          // document.getElementById("tglAwal").value = tglEnd;
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("jmldayoff").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
