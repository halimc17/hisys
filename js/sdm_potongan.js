// JavaScript Document

////////excel material

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "600";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog2(title, content, width, height, ev);
}
function excel(ev, kodeorg, periodegaji, tipepotongan) {
  param =
    "method=excel" +
    "&kodeorg=" +
    kodeorg +
    "&periodegaji=" +
    periodegaji +
    "&tipepotongan=" +
    tipepotongan;
  //alert(param);
  tujuan = "sdm_slave_potonganExcel.php";
  judul = "Print Excel";
  printFile(param, tujuan, judul, ev);
}

function getPrd() {
  kdOrg = document.getElementById("kdOrg");
  kdOrg = kdOrg.options[kdOrg.selectedIndex].value;
  prd = document.getElementById("tglAbsen");
  prd = prd.options[prd.selectedIndex].value;

  param = "periode=" + prd + "&proses=getPrd" + "&kdOrg=" + kdOrg;
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("tglAbsen").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getPrd2() {
  kdOrg = document.getElementById("kodeorg2");
  kdOrg = kdOrg.options[kdOrg.selectedIndex].value;
  prd = document.getElementById("periode2");
  prd = prd.options[prd.selectedIndex].value;

  param = "periode=" + prd + "&proses=getPrd2" + "&kdOrg=" + kdOrg;
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("periode2").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function displayList() {
  document.getElementById("listData").style.display = "block";
  document.getElementById("headher").style.display = "none";
  document.getElementById("detailEntry").style.display = "none";
  document.getElementById("kdOrgCr").value = "";
  document.getElementById("tgl_cari").value = "";
  document.getElementById("tpPotCr").value = "";
  hapusfill();
  loadData(0);
}

function cariOrg(title, content, ev) {
  width = "500";
  height = "400";
  showDialog1(title, content, width, height, ev);
  //alert('asdasd');
}
function findOrg() {
  txt = trim(document.getElementById("fnOrg").value);
  if (txt == "") {
    alert("Text is obligatory");
  } else if (txt.length < 3) {
    alert("Text too short");
  } else {
    param = "txtfind=" + txt + "&proses=cariOrg";
    tujuan = "sdm_slave_potongan.php";
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function setOrg(kdOrg, nmOrg) {
  document.getElementById("kdOrg").value = kdOrg;
  document.getElementById("nmOrg").value = nmOrg;
  closeDialog();
}
function findOrg2() {
  txt = trim(document.getElementById("crOrg").value);
  if (txt == "") {
    alert("Text is obligatory");
  } else if (txt.length < 3) {
    alert("Text too short");
  } else {
    param = "txtfind=" + txt + "&proses=cariOrg2";
    tujuan = "sdm_slave_potongan.php";
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function setOrg2(kdOrg, nmOrg) {
  document.getElementById("kdOrg").value = kdOrg;
  document.getElementById("txtsearch").value = nmOrg;
  closeDialog();
}
function add_detail() {
  kdOrg = document.getElementById("kdOrg");
  kdOrg = kdOrg.options[kdOrg.selectedIndex].value;
  prd = document.getElementById("tglAbsen");
  prd = prd.options[prd.selectedIndex].value;
  tpPot = document.getElementById("tpPotongan");
  tpPot = tpPot.options[tpPot.selectedIndex].value;
  param = "kdOrg=" + kdOrg + "&proses=createTable";
  param += "&periode=" + prd + "&tipePot=" + tpPot;
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("detailEntry").style.display = "block";
          document.getElementById("detailIsi").innerHTML = con.responseText;
          // document.getElementById('tmbLheader').innerHTML = '';
          lockForm();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function lockForm() {
  document.getElementById("kdOrg").disabled = true;
  document.getElementById("tglAbsen").disabled = true;
  document.getElementById("tpPotongan").disabled = true;
  document.getElementById("tombolHeader").style.display = "none";
}
function unlockForm() {
  document.getElementById("kdOrg").disabled = false;
  document.getElementById("tglAbsen").disabled = false;
  document.getElementById("tpPotongan").disabled = false;
  document.getElementById("kdOrg").value = "";
  document.getElementById("tglAbsen").value = "";
  document.getElementById("tpPotongan").value = "";
  document.getElementById("tombolHeader").style.display = "block";
}
status_inputan = 0;
function addDetail() {
  if (status_inputan == 0) {
    if (confirm("Are you sure..?")) {
      saveData();
    }
  } else if (status_inputan != 0) {
    saveData();
  }
}
function saveData() {
  kdOrg = document.getElementById("kdOrg");
  kdOrg = kdOrg.options[kdOrg.selectedIndex].value;
  prd = document.getElementById("tglAbsen");
  prd = prd.options[prd.selectedIndex].value;
  tpPot = document.getElementById("tpPotongan");
  tpPot = tpPot.options[tpPot.selectedIndex].value;
  karyId = document.getElementById("krywnId");
  karyId = karyId.options[karyId.selectedIndex].value;
  rpPot = document.getElementById("rpPot").value;
  ketpot = document.getElementById("ketPot").value;

  pros = document.getElementById("proses").value;
  if (pros != "updateDetail") {
    param = "proses=saveData";
  } else {
    param = "proses=updateDetail";
  }
  param += "&kdOrg=" + kdOrg;
  param += "&periode=" + prd + "&tipePot=" + tpPot + "&krywnId=" + karyId;
  param += "&rupPot=" + rpPot + "&ketPot=" + ketpot;
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          status_inputan = 1;
          lockForm();
          showTmbl();
          bersihFormDet();
          loadDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function editDetail(karyawn, rppot, ketrng) {
  document.getElementById("krywnId").value = karyawn;
  document.getElementById("krywnId").disabled = true;
  document.getElementById("rpPot").value = rppot;
  document.getElementById("ketPot").value = ketrng;
  document.getElementById("proses").value = "updateDetail";
}
statFrm = 0;
function showTmbl() {
  if (statFrm == 0) {
    document.getElementById("tombol").innerHTML =
      "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button>";
  } else if (statFrm == 1) {
    document.getElementById("tombol").innerHTML =
      "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button>";
  }
}

function bersihFormDet() {
  document.getElementById("krywnId").disabled = false;
  document.getElementById("ketPot").value = "";
  document.getElementById("krywnId").value = "";
  document.getElementById("proses").value = "saveData";
}

function delDetail(kdorg, period, krywn, tppot) {
  param += "&kdOrg=" + kdorg;
  param += "&periode=" + period + "&tipePot=" + tppot + "&krywnId=" + krywn;
  param += "&proses=delDetail";
  tujuan = "sdm_slave_potongan.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  if (confirm("Deleting, are you sure..?"))
    post_response_text(tujuan, param, respog);
}

function loadData(num) {
  kdorg = document.getElementById("kdOrgCr");
  kdorg = kdorg.options[kdorg.selectedIndex].value;
  tgl = document.getElementById("tgl_cari").value;
  tppot = document.getElementById("tpPotCr");
  tppot = tppot.options[tppot.selectedIndex].value;

  param = "proses=loadNewData" + "&kdOrgCr=" + kdorg;
  param += "&periodecr=" + tgl + "&tipePotCr=" + tppot;
  param += "&page=" + num;
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contain").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loadDetail() {
  kdOrg = document.getElementById("kdOrg");
  kdOrg = kdOrg.options[kdOrg.selectedIndex].value;
  prd = document.getElementById("tglAbsen");
  prd = prd.options[prd.selectedIndex].value;
  tpPot = document.getElementById("tpPotongan");
  tpPot = tpPot.options[tpPot.selectedIndex].value;
  param = "kdOrg=" + kdOrg + "&periode=" + prd + "&tipePot=" + tpPot;
  param += "&proses=loadDetail";
  //alert(param);
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contentDetail").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function fillField(kdorg, prder, potong) {
  kdOrg = document.getElementById("kdOrg");
  for (x = 0; x < kdOrg.length; x++) {
    if (kdOrg.options[x].value == kdorg) {
      kdOrg.options[x].selected = true;
    }
  }
  prd = document.getElementById("tglAbsen");
  for (x = 0; x < prd.length; x++) {
    if (prd.options[x].value == prder) {
      prd.options[x].selected = true;
    }
  }
  tppot = document.getElementById("tpPotongan");
  for (x = 0; x < tppot.length; x++) {
    if (tppot.options[x].value == potong) {
      tppot.options[x].selected = true;
    }
  }
  param =
    "kdOrg=" +
    kdorg +
    "&periode=" +
    prder +
    "&tipePotongan=" +
    potong +
    "&statUpdate=1";
  param += "&proses=createTable";
  //alert(param);
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          lockForm();
          document.getElementById("listData").style.display = "none";
          document.getElementById("headher").style.display = "block";
          document.getElementById("detailEntry").style.display = "block";
          var detailDiv = document.getElementById("detailIsi");
          detailDiv.innerHTML = con.responseText;
          status_inputan = 1;
          statFrm = 1;
          showTmbl();
          loadDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function delData(kdorg, prder, potong) {
  param += "&kdOrg=" + kdorg;
  param += "&periode=" + prder + "&tipePot=" + potong;
  param += "&proses=delData";
  tujuan = "sdm_slave_potongan.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          displayList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  if (confirm("Deleteing, are you sure..?"))
    post_response_text(tujuan, param, respog);
}
function frm_aju() {
  if (statFrm == 0) {
    if (confirm("Done, are you sure..?")) {
      displayList();
    }
  } else if (statFrm == 1) {
    if (confirm("Done, are you sure..?")) {
      displayList();
    }
  }
}
function reset_data() {
  if (statFrm == 0) {
    if (confirm("Canceling, are you sure..?")) {
      kdorg = document.getElementById("kdOrg").value;
      tgl = document.getElementById("tglAbsen").value;
      delDataAll(kdorg, tgl);
    }
  }
}

function getKary(title, pil, ev) {
  utkUnit = document.getElementById("kdOrg");
  utkUnit = utkUnit.options[utkUnit.selectedIndex].value;
  prd = document.getElementById("tglAbsen").value;
  tpPot = document.getElementById("tpPotongan");
  tpPot = tpPot.options[tpPot.selectedIndex].value;

  if (pil == 1) {
    content = "<div style='width:100%;'>";
    content +=
      "<fieldset>" +
      title +
      "<input type=hidden id=unit value=" +
      utkUnit +
      " /><input type=hidden id=tppot value=" +
      tpPot +
      " /><input type=hidden id=periode value=" +
      prd +
      " /><input type=text placeholder='Nama Karyawan' id=txtnamabarang class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariKary(" +
      pil +
      ")>Go</button> </fieldset>";
    content +=
      "<fieldset><legend><i>Result<i></legend><div id=containercari style='overflow:auto;max-height:300px'></div></div></fieldset>";
  }

  //display window
  width = "";
  height = "";
  showDialog1(title, content, width, height, ev);
}
function goCariKary(pil) {
  //keu_slave_2globalfungsi
  lokTgs = document.getElementById("unit").value;
  tppotongan = document.getElementById("tppot").value;
  prd = document.getElementById("periode").value;
  nmkary = document.getElementById("txtnamabarang").value;
  param =
    "unit=" +
    lokTgs +
    "&tppot=" +
    tppotongan +
    "&periode=" +
    prd +
    "&nmkary=" +
    nmkary;

  if (pil == 1) {
    param += "&proses=getKary";
  }
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containercari").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function setKary(karyid) {
  kar = document.getElementById("krywnId");
  for (x = 0; x < kar.length; x++) {
    if (kar.options[x].value == karyid) {
      kar.options[x].selected = true;
    }
  }
  closeDialog();
}

function showupload(ev, org, per, kom) {
  showformupload(ev);
  param = "";
  param += "per=" + per;
  param += "&kom=" + kom;
  param += "&org=" + org;
  param += "&proses=showupload";
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contUpload").innerHTML = con.responseText;
          loadfiles(org, per, kom);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showformupload(ev) {
  title = "UPLOAD FILES";
  width = "";
  height = "";
  content =
    "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
  showDialog2(title, content, width, height, ev);

  pos = new Array();
  pos = getMouseP(ev);

  document.getElementById("dynamic2").style.top = pos[1] + "px";
  document.getElementById("dynamic2").style.left = pos[0] - 500 + "px";
  document.getElementById("dynamic2").style.display = "";
}

function submitfile() {
  var file = document.getElementById("upload").files[0];
  var org = document.getElementById("kodeorg").innerHTML;
  var per = document.getElementById("periode").innerHTML;
  var kom = document.getElementById("komponen").innerHTML;
  var formdata = new FormData();
  formdata.append("org", org);
  formdata.append("per", per);
  formdata.append("kom", kom);
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));

  if (getValue("upload") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "sdm_slave_potongan.php?method=submitfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //=== Success Response
          alert("Uploaded Success.");
          document.getElementById("upload").value = "";
          loadfiles(org, per, kom);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfiles(org, per, kom) {
  param = "proses=loadfiles&org=" + org + "&per=" + per + "&kom=" + kom;
  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (document.getElementById("listfiles") !== null) {
            document.getElementById("listfiles").innerHTML = con.responseText;
          }
          if (document.getElementById("loadfilesdetail") !== null) {
            document.getElementById("loadfilesdetail").innerHTML =
              con.responseText;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(org, per, kom, namafile) {
  param = "proses=deletefile";
  param += "&org=" + org;
  param += "&per=" + per;
  param += "&kom=" + kom;
  param += "&namafile=" + namafile;

  tujuan = "sdm_slave_potongan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfiles(org, per, kom);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getkaryawanid() {
  org = document.getElementById("kodeorg2").value;
  per = document.getElementById("periode2").value;
  kom = document.getElementById("potongan2").value;
  param =
    "method=getkaryawanid" + "&org=" + org + "&per=" + per + "&kom=" + kom;

  ev = "event";
  judul = "excel";
  tujuan = "sdm_slave_potongan.php";

  printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "";
  height = "";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function hapusfill() {
  document.getElementById("kodeorg2").value = "";
  document.getElementById("periode2").value = "";
  document.getElementById("potongan2").value = "";
  document.getElementById("filex").value = "";
}

function previewInsertfile() {
  var file = document.getElementById("filex").files[0];
  org = document.getElementById("kodeorg2").value;
  per = document.getElementById("periode2").value;
  kom = document.getElementById("potongan2").value;

  if (org == "") {
    alert("Warning : Harap Unit Kerja Diisikan.");
    return false;
  }
  if (per == "") {
    alert("Warning : Harap Periode Diisikan.");
    return false;
  }
  if (kom == "") {
    alert("Warning : Harap Potongan Diisikan.");
    return false;
  }
  if (getValue("filex") == "") {
    alert("Warning : Tidak ada data yang di upload !");
    return false;
  }

  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("filex"));
  formdata.append("org", org);
  formdata.append("per", per);
  formdata.append("kom", kom);
  formdata.append("previewonly", "1");

  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "sdm_slave_potongan.php?method=insertfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          var content =
            "<div style='background-color:#FFFFFF;'>" +
            con.responseText +
            "</div>" +
            "<div style='text-align:center;margin-top:10px;'>" +
            "<button class=mybutton onclick=\"closeDialog5();insertfile();\">Lanjutkan Simpan</button>&nbsp;" +
            "<button class=mybutton onclick=closeDialog5()>Batal</button>" +
            "</div>";
          showDialog5(
            "Preview Data Potongan",
            content,
            "700",
            "auto",
            "event"
          );
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function insertfile() {
  var file = document.getElementById("filex").files[0];
  org = document.getElementById("kodeorg2").value;
  per = document.getElementById("periode2").value;
  kom = document.getElementById("potongan2").value;

  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("filex"));
  formdata.append("org", org);
  formdata.append("per", per);
  formdata.append("kom", kom);

  if (org == "") {
    alert("Warning : Harap Unit Kerja Diisikan.");
    return false;
  }
  if (per == "") {
    alert("Warning : Harap Periode Diisikan.");
    return false;
  }
  if (kom == "") {
    alert("Warning : Harap Potongan Diisikan.");
    return false;
  }

  if (getValue("filex") == "") {
    alert("Warning : Tidak ada data yang di upload !");
    return false;
  }

  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "sdm_slave_potongan.php?method=insertfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Data berhasil di simpan.");
          hapusfill();
          displayList();
          reloadframe();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function reloadframe() {
  window.location.reload();
}
