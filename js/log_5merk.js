function batal() {
  document.getElementById("idmerk").value = "";
  document.getElementById("idmerk").disabled = true;
  document.getElementById("merk").value = "";
  document.getElementById("merk").disabled = false;
  document.getElementById("find_merk").value = "";
  document.getElementById("status").checked = true;
  document.getElementById("method").value = "insert";
}

function loaddata(num) {
  find_merk = document.getElementById("find_merk").value;
  param = "method=loaddata";
  param += "&page=" + num + "&find_merk=" + find_merk;
  tujuan = "log_slave_5merk.php";
  post_response_text(tujuan, param, respog);

  function respog() {
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

function simpan() {
  idmerk = document.getElementById("idmerk").value;
  merk = document.getElementById("merk").value;
  aktif = document.getElementById("status");

  if (aktif.checked == true) {
    aktif = 1;
  } else {
    aktif = 0;
  }
  method = document.getElementById("method").value;

  if (merk == "") {
    alert("Field Was Empty");
    return false;
  }

  param =
    "idmerk=" +
    idmerk +
    "&merk=" +
    merk +
    "&status=" +
    aktif +
    "&method=" +
    method;
  tujuan = "log_slave_5merk.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          batal();
          loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function edit(idmerk, merk, aktif) {
  document.getElementById("idmerk").value = idmerk;
  document.getElementById("idmerk").disabled = true;
  document.getElementById("merk").value = merk;

  if (aktif == "1") {
    document.getElementById("status").checked = true;
  } else {
    document.getElementById("status").checked = false;
  }
  document.getElementById("method").value = "update";
}

function getPage(pg) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function formdetmerk(title, wdth, heig) {
  width = "";
  height = "";
  if (wdth != "") {
    width = wdth;
  }
  if (heig != "") {
    height = heig;
  }
  content = "<div id=containerData></div>";
  ev = "event";
  showDialog4(title, content, width, height, ev);
}

function detaildt(title, idmerk, merk) {
  title = title + " " + merk;
  width = "";
  height = "";
  formdetmerk(title, width, height);
  param = "idmerk=" + idmerk + "&merk=" + merk;
  tujuan = "log_slave_5merkdetail.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerData").innerHTML = con.responseText;
          loadDataBarang(idmerk);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadDataBarang(idmerk) {
  param = "method=loadData";
  param += "&idmerk=" + idmerk;
  tujuan = "log_slave_save_5merkdetail.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerbarang").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveBarang(idmerk_det) {
  //alert('masukk');
  idmerk_det = document.getElementById("idmerk_det").value;
  kodebarang_det = document.getElementById("kodebarang_det").value;
  method = document.getElementById("methoddetail").value;

  if (idmerk_det == "" || kodebarang_det == "") {
    alert("Field Was Empty");
    return;
  }
  param =
    "idmerk_det=" +
    idmerk_det +
    "&kodebarang_det=" +
    kodebarang_det +
    "&method=" +
    method;
  tujuan = "log_slave_save_5merkdetail.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          cancelBarang();
          loadDataBarang(idmerk_det);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelBarang() {
  document.getElementById("kodebarang_det").value = "";
  document.getElementById("method").value = "insert";
}

function del(idmerk_det, kodebarang_det) {
  param =
    "method=delete" +
    "&idmerk_det=" +
    idmerk_det +
    "&kodebarang_det=" +
    kodebarang_det;
  tujuan = "log_slave_save_5merkdetail.php";
  if (confirm(" Anda yakin ???")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerbarang").innerHTML =
            con.responseText;
          loadDataBarang(idmerk_det);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
