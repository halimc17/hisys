function add_new_data() {
  document.getElementById("detail").style.display = "block";
  document.getElementById("listData").style.display = "none";
  document.getElementById("displayupload").style.display = "none";
  document.getElementById("formpencarianheader").style.display = "none";
}

function add_upload() {
  param = "method=displayupload";
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listData").style.display = "none";
          document.getElementById("detail").style.display = "none";
          document.getElementById("formpencarianheader").style.display = "none";
          document.getElementById("displayupload").style.display = "block";
          document.getElementById("formuploaddata").innerHTML =
            con.responseText;
          $("#formuploaddata .select2").select2({ dropdownAutoWidth: true });
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
  kom = document.getElementById("kom").value;
  param =
    "method=getkaryawanid" + "&org=" + org + "&per=" + per + "&kom=" + kom;

  ev = "event";
  judul = "excel";
  tujuan = "sdm_slave_3pl.php";

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

function previewSaveFile() {
  var file = document.getElementById("filex").files[0];
  org = document.getElementById("kodeorg2").value;
  per = document.getElementById("periode2").value;
  kom = document.getElementById("kom2").value;

  if (org == "") {
    alert("Warning : Harap Kodeorg Diisikan.");
    return false;
  }
  if (per == "") {
    alert("Warning : Harap Periode Diisikan.");
    return false;
  }
  if (kom == "") {
    alert("Warning : Harap jenis Pendapatan Diisikan.");
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
  con.open("POST", "sdm_slave_3pl.php?method=saveFile", true);
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
            "<button class=mybutton onclick=\"closeDialog5();saveFile();\">Lanjutkan Simpan</button>&nbsp;" +
            "<button class=mybutton onclick=closeDialog5()>Batal</button>" +
            "</div>";
          showDialog5(
            "Preview Data Pendapatan Lain",
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

function saveFile() {
  var file = document.getElementById("filex").files[0];
  org = document.getElementById("kodeorg2").value;
  per = document.getElementById("periode2").value;
  kom = document.getElementById("kom2").value;

  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("filex"));
  formdata.append("org", org);
  formdata.append("per", per);
  formdata.append("kom", kom);

  if (org == "") {
    alert("Warning : Harap Kodeorg Diisikan.");
    return false;
  }
  if (per == "") {
    alert("Warning : Harap Periode Diisikan.");
    return false;
  }
  if (kom == "") {
    alert("Warning : Harap jenis Pendapatan Diisikan.");
    return false;
  }

  if (getValue("filex") == "") {
    alert("Warning : Tidak ada data yang di upload !");
    return false;
  }

  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "sdm_slave_3pl.php?method=saveFile", true);
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

function displayList() {
  document.getElementById("listData").style.display = "block";
  document.getElementById("formpencarianheader").style.display = "";
  document.getElementById("displayupload").style.display = "none";
  document.getElementById("detail").style.display = "none";
  batallist();
}

function batallist() {
  document.getElementById("perSch").value = "";
  document.getElementById("orgSch").value = "";
  document.getElementById("komSch").value = "";
  document.getElementById("postSch").value = "";
  document.getElementById("org").value = "";
  document.getElementById("jabatan").value = "";
  document.getElementById("per").value = "";
  document.getElementById("kom").value = "";
  document.getElementById("tipekar").value = "";
  loadData(0);
  reloadframe();
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "";
  height = "";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog2(title, content, width, height, ev);
}

function excel(ev, per, kom, org) {
  param = "method=excel" + "&per=" + per + "&kom=" + kom + "&org=" + org;
  //alert(param);
  tujuan = "sdm_slave_3plExcel.php";
  judul = "Print Excel";
  printFile(param, tujuan, judul, ev);
}

function PDF(ev, per, kom, org) {
  param = "method=PDF" + "&per=" + per + "&kom=" + kom + "&org=" + org;
  tujuan = "sdm_slave_3pl.php";
  tujuan = tujuan + "?" + param;

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>",
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function bataldetail() {
  setValue2("kar", "");
  document.getElementById("kar").disabled = false;
  document.getElementById("jum").value = "";
  document.getElementById("ket").value = "";
  document.getElementById("saveDetail").value = "saveDetail";
}

function getPrd() {
  org = document.getElementById("org").value;

  param = "method=getPrd" + "&org=" + org;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("per").innerHTML = con.responseText;
          $("#per").trigger("change.select2");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getPrd2() {
  org = document.getElementById("kodeorg2").value;

  param = "method=getPrd2" + "&org=" + org;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("periode2").innerHTML = con.responseText;
          $("#periode2").trigger("change.select2");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveDetail() {
  per = document.getElementById("per").value;
  kom = document.getElementById("kom").value;
  kar = document.getElementById("kar").value;
  jum = remove_comma_var(document.getElementById("jum").value);
  org = document.getElementById("org").value;
  ket = document.getElementById("ket").value;
  met = document.getElementById("saveDetail").value;

  if (kar == "") {
    alert("Warning : Karyawan wajib dipilih.");
    return;
  }
  if (jum == "" || isNaN(jum) || parseFloat(jum) <= 0) {
    alert("Warning : Jumlah wajib diisi dengan angka lebih dari 0.");
    return;
  }

  param =
    "method=" +
    met +
    "&per=" +
    per +
    "&kom=" +
    kom +
    "&kar=" +
    kar +
    "&jum=" +
    jum +
    "&org=" +
    org +
    "&ket=" +
    ket;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          bataldetail();
          loadDataDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelFormBarang() {
  document.getElementById("nobpb").value = "";
  document.getElementById("nopo").value = "";
  document.getElementById("nopp").value = "";
  document.getElementById("kodebarang").value = "";
  document.getElementById("kurs").value = "";
  document.getElementById("namabarang").value = "";
  document.getElementById("jumlah").value = "";
  document.getElementById("satuan").value = "";
  document.getElementById("matauang").value = "IDR";
  document.getElementById("hargasatuan").value = "";
}

function loadDataDetail() {
  org = document.getElementById("org").value;
  per = document.getElementById("per").value;
  kom = document.getElementById("kom").value;
  param = "method=loadDetail" + "&per=" + per + "&kom=" + kom + "&org=" + org;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          //return;
          //document.getElementById('contentDetail').innerHTML=con.responseText;
          document.getElementById("loaddatadetail").style.display = "block";
          document.getElementById("loaddatadetail").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancel() {
  document.location.reload();
  document.getElementById("jabatan").value = "";
  document.getElementById("tipekar").value = "";
}
function editdetail(kar, jum, ket) {
  setValue2("kar", kar);
  document.getElementById("kar").disabled = true;
  document.getElementById("jum").value = jum;
  if (jum !== "") {
    z.numberFormat("jum", 2);
  }
  document.getElementById("ket").value = ket;
  document.getElementById("saveDetail").value = "updatedetail";
}

function edit(per, kom, org) {
  document.getElementById("displayinsert").style.display = "none";
  document.getElementById("inputdetail").style.display = "block";

  document.getElementById("detail").style.display = "block";
  document.getElementById("listData").style.display = "none";
  document.getElementById("formpencarianheader").style.display = "none";

  setValue2("per", per);
  setValue2("kom", kom);
  //org punya onchange getPrd() yang mengganti isi periode, jadi dimatikan sebentar saat nilai diisi
  elOrg = document.getElementById("org");
  handlerOrg = elOrg.onchange;
  elOrg.onchange = null;
  setValue2("org", org);
  elOrg.onchange = handlerOrg;
  // document.getElementById('displayall').style.display = 'block';
  // document.getElementById('detailForm').style.display='block';
  lockHeader(org, "", "");
}

function saveHeader() {
  jabatan = document.getElementById("jabatan").value;
  tipekar = document.getElementById("tipekar").value;
  org = document.getElementById("org").value;
  per = document.getElementById("per").value;
  kom = document.getElementById("kom").value;

  if (org == "" || per == "" || kom == "") {
    alert("Warning : Kode Organisasi, Periode Gaji, Jenis wajib diisi !");
    return;
  }

  param =
    "per=" +
    per +
    "&kom=" +
    kom +
    "&org=" +
    org +
    "&method=cekHeader" +
    "&jabatan=" +
    jabatan +
    "&tipekar=" +
    tipekar;

  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          detail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detail() {
  org = document.getElementById("org").value;
  per = document.getElementById("per").value;
  kom = document.getElementById("kom").value;
  jabatan = document.getElementById("jabatan").value;
  tipekar = document.getElementById("tipekar").value;
  param = "per=" + per + "&kom=" + kom + "&org=" + org + "&method=detail";
  if (jabatan != "") {
    param += "&jabatan=" + jabatan;
  }
  if (tipekar != "") {
    param += "&tipekar=" + tipekar;
  }
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("displayinsert").style.display = "block";
          document.getElementById("displayinsert").innerHTML = con.responseText;
          lockHeader(org, jabatan, tipekar);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function savedt() {
  org = document.getElementById("org").value;
  per = document.getElementById("per").value;
  kom = document.getElementById("kom").value;
  totRow = document.getElementById("totrows").value;
  var adaIsi = false;
  for (dwc = 0; dwc < totRow; dwc++) {
    nilai = remove_comma_var(document.getElementById("jum_" + dwc).value);
    if (nilai == "") {
      continue;
    }
    if (isNaN(nilai) || parseFloat(nilai) < 0) {
      alert("Warning : Jumlah tidak valid pada baris ke-" + (dwc + 1) + ". Isi dengan angka.");
      return;
    }
    if (parseFloat(nilai) > 0) {
      adaIsi = true;
    }
  }
  if (!adaIsi) {
    alert("Warning : Isi Jumlah minimal untuk satu karyawan.");
    return;
  }
  var allData = "";
  for (dwc = 0; dwc < totRow; dwc++) {
    allData +=
      "&kar[" + dwc + "]=" + document.getElementById("kar_" + dwc).value;
    allData +=
      "&jum[" + dwc + "]=" + remove_comma_var(document.getElementById("jum_" + dwc).value);
    allData +=
      "&ket[" + dwc + "]=" + document.getElementById("ket_" + dwc).value;
  }

  param =
    "per=" +
    per +
    "&kom=" +
    kom +
    "&org=" +
    org +
    "&method=savedt" +
    "&totRow=" +
    totRow;
  param += allData;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("inputdetail").style.display = "block";
          document.getElementById("displayinsert").style.display = "none";
          document.getElementById("displayinsert").innerHTML = "";
          loadDataDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function delHead(per, kom, org) {
  param = "method=delHead" + "&per=" + per + "&kom=" + kom + "&org=" + org;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cariBast(num) {
  perSch = document.getElementById("perSch").value;

  param = "method=loadData" + "&perSch=" + perSch + "&page=" + num;

  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //displayList();

          document.getElementById("container").innerHTML = con.responseText;
          //loadData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//filter pencarian yang aktif, dipakai oleh list, Excel, dan PDF supaya hasilnya sama
function getFilterParam3pl() {
  var p = "";
  p += "&perSch=" + encodeURIComponent(document.getElementById("perSch").value);
  p += "&orgSch=" + encodeURIComponent(document.getElementById("orgSch").value);
  p += "&komSch=" + encodeURIComponent(document.getElementById("komSch").value);
  p += "&postSch=" + document.getElementById("postSch").value;
  return p;
}

function exportExcel3pl() {
  param = "method=excellist" + getFilterParam3pl();
  tujuan = "sdm_slave_3pl.php?" + param;
  alertify
    .popup(
      "Report Ms.Excel",
      "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" +
        tujuan +
        "'></iframe>",
    )
    .set({ resizable: true, maximizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function exportPdf3pl() {
  tujuan = "sdm_3pl_pdf.php?" + getFilterParam3pl().substring(1);
  alertify
    .popup(
      "Report PDF",
      "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" +
        tujuan +
        "'></iframe>",
    )
    .set({ resizable: true, maximizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loadData(paged);
}

function loadData(page) {
  perSch = document.getElementById("perSch").value;
  param = "method=loadData" + "&page=" + page + getFilterParam3pl();
  //alert(param);
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("container").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function lockHeader(org, jabatan, tipekar) {
  document.getElementById("saveHeader").disabled = true;
  document.getElementById("tipekar").disabled = true;
  document.getElementById("jabatan").disabled = true;
  document.getElementById("per").disabled = true;
  document.getElementById("kom").disabled = true;
  document.getElementById("org").disabled = true;
  if (jabatan == "" || tipekar == "") {
    loadKar(org);
  }
}

function cancelHeader(org) {
  document.getElementById("saveHeader").disabled = false;
  document.getElementById("per").disabled = false;
  document.getElementById("kom").disabled = false;
  document.getElementById("org").disabled = false;
  document.getElementById("jum").value;
  cancel();
}

//load kary
function loadKar(org, jabatan, tipekar) {
  param =
    "method=loadKar" +
    "&org=" +
    org +
    "&jabatan=" +
    jabatan +
    "&tipekar=" +
    tipekar;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // document.getElementById('kar').innerHTML = con.responseText;
          loadDataDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function DelDetail(per, kar, kom) {
  param = "method=deleteDetail" + "&kar=" + kar + "&per=" + per + "&kom=" + kom;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadDataDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingData(per, kom, org) {
  param = "method=posting" + "&org=" + org + "&per=" + per + "&kom=" + kom;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function unposting(per, kom, org) {
  param = "method=unposting" + "&org=" + org + "&per=" + per + "&kom=" + kom;
  tujuan = "sdm_slave_3pl.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showupload(ev, per, kom, org) {
  showformupload(ev);
  param = "";
  param += "per=" + per;
  param += "&kom=" + kom;
  param += "&org=" + org;
  param += "&method=showupload";
  tujuan = "sdm_slave_3pl.php";
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
  con.open("POST", "sdm_slave_3pl.php?method=submitfile", true);
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
  param = "method=loadfiles&org=" + org + "&per=" + per + "&kom=" + kom;
  tujuan = "sdm_slave_3pl.php";
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
  param = "method=deletefile";
  param += "&org=" + org;
  param += "&per=" + per;
  param += "&kom=" + kom;
  param += "&namafile=" + namafile;

  tujuan = "sdm_slave_3pl.php";
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
