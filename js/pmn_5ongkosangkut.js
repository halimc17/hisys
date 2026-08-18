function simpanmaster() {
  param = "";
  kodeunit = document.getElementById("kodeunitmaster").value;
  trpcode = document.getElementById("trpcode").value;
  lokasi = document.getElementById("lokasi").value;
  lokasi2 = document.getElementById("lokasi2").value;
  komoditi = document.getElementById("komoditi").value;
  iddet = document.getElementById("idht").value;
  kdtrans = document.getElementById("kdtrans").value;
  method = document.getElementById("methodmaster").value;

  if (kodeunit == "") {
    alert("Field Was Empty");
    return false;
  }

  param +=
    "kodeunit=" +
    kodeunit +
    "&trpcode=" +
    trpcode +
    "&lokasi=" +
    lokasi +
    "&lokasi2=" +
    lokasi2 +
    "&iddet=" +
    iddet +
    "&kdtrans=" +
    kdtrans +
    "&komoditi=" +
    komoditi +
    "&method=" +
    method;
  // console.log(param)

  tujuan = "pmn_slave_5ongkosangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // bentuknotrans(kodeunit)
          document.getElementById("notransaksicari").value = kdtrans;
          batalmaster();
          loaddatamaster(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function bentuknotrans() {
  kodeunit = document.getElementById("kodeunitmaster").value;
  // param = "method=createnotrans";
  // param += "&kodeunit=" + kodeunit;
  param = "kodeunit=" + kodeunit + "&method=getnotrans";

  // alert(param)

  tujuan = "pmn_slave_5ongkosangkut.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          data = con.responseText;
          document.getElementById("kdtrans").value = data;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpandetail() {
  param = "";
  nodetail = document.getElementById("nodetail").value;
  iddet = document.getElementById("iddet").value;
  tgl1 = document.getElementById("tgl1").value;
  tgl2 = document.getElementById("tgl2").value;
  hargapotongan = document.getElementById("hargapotongan").value;
  harga = document.getElementById("harga").value;
  method = document.getElementById("methoddetail").value;

  if (tgl1 == "" || tgl2 == "" || harga == "") {
    alert("Field Was Empty");
    return false;
  }

  param +=
    "tgl1=" +
    tgl1 +
    "&tgl2=" +
    tgl2 +
    "&nodetail=" +
    nodetail +
    "&iddet=" +
    iddet +
    "&harga=" +
    harga +
    "&hargapotongan=" +
    hargapotongan +
    "&method=" +
    method;
  // console.log(param)

  tujuan = "pmn_slave_5ongkosangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          bataldetail();
          loaddatadetail(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalmaster() {
  setValue2("kodeunitmaster", "");
  document.getElementById("kodeunitmaster").disabled = false;
  setValue2("lokasi", "");
  document.getElementById("lokasi").disabled = false;
  setValue2("lokasi2", "");
  document.getElementById("lokasi2").disabled = false;
  setValue2("trpcode", "");
  document.getElementById("trpcode").disabled = false;
  setValue2("komoditi", "");
  document.getElementById("komoditi").disabled = false;
  setValue2("methodmaster", "insertmaster");
  document.getElementById("methodmaster").disabled = false;
  setValue2("idht", "");
  document.getElementById("idht").disabled = false;
  setValue2("kdtrans", "");
}

function bataldetail() {
  setValue2("tgl1", "");
  document.getElementById("tgl1").disabled = false;
  setValue2("tgl2", "");
  document.getElementById("tgl2").disabled = false;
  setValue2("harga", "");
  document.getElementById("harga").disabled = false;
  setValue2("hargapotongan", "");
  document.getElementById("hargapotongan").disabled = false;
  setValue2("methoddetail", "insertdetail");
  document.getElementById("methoddetail").disabled = false;
  // setValue2('idht','');
  // document.getElementById('idht').disabled = false;
}

function batalcarimaster() {
  setValue2("kodeunitmastercari", "");
  loaddatamaster(0);
}

function loaddatamaster(num) {
  kodeunitcari = document.getElementById("kodeunitmastercari").value;
  komoditicari = document.getElementById("komoditicari").value;
  notransaksicari = document.getElementById("notransaksicari").value;

  param = "method=loaddatamaster";
  param += "&page=" + num + "&kodeunitcari=" + kodeunitcari;
  param += "&komoditicari=" + komoditicari;
  param += "&notransaksicari=" + notransaksicari;
  tujuan = "pmn_slave_5ongkosangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containermaster").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getpagedetail() {
  pg = document.getElementById("pagedetail");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddatadetail(paged);
}

function loaddatadetail(num) {
  // kodeunitcari = document.getElementById('kodeunitdetailcari').value;
  iddet = document.getElementById("iddet").value;
  param = "method=loaddatadetail";
  param +=
    "&pagedt=" + num + "&kodeunitcari=" + kodeunitcari + "&iddet=" + iddet;
  // console.log(param)
  tujuan = "pmn_slave_5ongkosangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerdetail").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editmaster(
  iddet,
  kodeunitmaster,
  lokasi,
  lokasi2,
  trpcode,
  komoditi,
  method
) {
  // document.getElementById('kodeunitmaster').disabled = true;
  // document.getElementById('suppliermaster').disabled = true;

  //   setValue2("lokasi", lokasi);
  setValue2("trpcode", trpcode);
  setValue2("lokasi2", lokasi2);
  setValue2("komoditi", komoditi);
  setValue2("kodeunitmaster", kodeunitmaster);
  setValue2("idht", iddet);
  setValue2("methodmaster", method);
  // setTimeout(function(){ setValue2('kodeunitmaster',kodeunitmaster) }, 100);
}

function editdetail(nodetail, tgl1, tgl2, harga, hargapotongan, method) {
  setValue2("tgl1", tgl1);
  setValue2("tgl2", tgl2);
  setValue2("harga", harga);
  setValue2("hargapotongan", hargapotongan);
  setValue2("nodetail", nodetail);
  setValue2("methoddetail", method);

  // setTimeout(function(){ setValue2('kodeunitmaster',kodeunitmaster) }, 100);
}

function formdetail(iddet, lokasi, posting) {
  param = "method=formdetail" + "&iddet=" + iddet + "&posting=" + posting;
  tujuan = "pmn_slave_5ongkosangkut.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify
            .popup2("Detail Lokasi " + lokasi, con.responseText)
            .set({ resizable: true, maximizable: false })
            .resizeTo("50%", "80%");
          bataldetail();
          loaddatadetail(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getlokasi() {
  lokasi = document.getElementById("lokasi").value;
  lokasi2 = document.getElementById("lokasi2").value;

  if (lokasi != "") {
    document.getElementById("lokasi2").disabled = true;
    document.getElementById("lokasi").disabled = false;
  } else if (lokasi2 != "") {
    document.getElementById("lokasi").disabled = true;
    document.getElementById("lokasi2").disabled = false;
  } else {
    document.getElementById("lokasi").disabled = false;
    document.getElementById("lokasi2").disabled = false;
  }
}

function posting(idks) {
  param = "method=posting" + "&idks=" + idks;
  tujuan = "pmn_slave_5ongkosangkut.php";
  alertify.confirm(
    "Posting Harga?",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    }
  );

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddatamaster(0);
          alertify.popup().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.success("Berhasil");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function unposting(idks) {
  param = "method=unposting" + "&idks=" + idks;
  tujuan = "pmn_slave_5ongkosangkut.php";
  alertify.confirm(
    "Unposting Harga?",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    }
  );

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddatamaster(0);
          alertify.popup().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.success("Berhasil Unposting Data");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletedetail(nourutid, iddet) {
  param = "method=deldetail" + "&nourutid=" + nourutid;
  tujuan = "pmn_slave_5ongkosangkut.php";
  alertify.confirm(
    "Delete Harga?",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    }
  );

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          formdetail(iddet);
          // alertify.popup().destroy();
          // alertify.set("notifier", "position", "top-right");
          // alertify.success("Berhasil");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingdt(nourutid, iddet) {
  param = "method=postingdt" + "&nourutid=" + nourutid;
  tujuan = "pmn_slave_5ongkosangkut.php";
  alertify.confirm(
    "Anda yakin posting harga ongkos angkut?",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    }
  );

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          formdetail(iddet);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getPagemaster() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddatamaster(paged);
}
