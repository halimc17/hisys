function selesaidetail() {
  document.getElementById("detail").style.display = "none";
  clearData();
  loadData();
}

function showdetaildata2() {
  document.getElementById("detail2").style.display = "block";
}

function hidedetaildata2() {
  document.getElementById("detail2").style.display = "none";
}

function cekalldetail() {
  drt = document.getElementById("cekalldetail");
  if (drt.checked == true) {
    chk = true;
  } else {
    chk = false;
  }
  var tbl = document.getElementById("contentdetail");
  var row = tbl.rows.length;
  row = row - 1;
  for (i = 1; i <= row; i++) {
    document.getElementById("cekdetail" + i).checked = chk;
  }
}

function loaddatadetail() {
  noinvoice = document.getElementById("noinvoice").value;
  param = "proses=loaddatadetail" + "&noinvoice=" + noinvoice;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listdatadetail").innerHTML =
            con.responseText;
          // getnpwpunit(npwpunit);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewdetail() {
  noinvoice = document.getElementById("noinvoice").value;
  tanggal1detail = document.getElementById("tanggal1detail").value;
  tanggal2detail = document.getElementById("tanggal2detail").value;
  kodecustomer = document.getElementById("kodecustomer").value;
  kodeorganisasi = document.getElementById("kodeorganisasi").value;
  tipetbsdetail = document.getElementById("tipetbsdetail").value;
  tahuntanam = document.getElementById("tahuntanam").value;
  param =
    "proses=previewdetail" +
    "&tanggal1detail=" +
    tanggal1detail +
    "&tanggal2detail=" +
    tanggal2detail;
  param +=
    "&kodecustomer=" +
    kodecustomer +
    "&kodeorganisasi=" +
    kodeorganisasi +
    "&noinvoice=" +
    noinvoice +
    "&tipetbsdetail=" +
    tipetbsdetail +
    "&tahuntanam=" +
    tahuntanam;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("datadetail").style.display = "block";
          document.getElementById("datadetail").innerHTML = con.responseText;
          // loaddatadetail(noinvoice);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

maxf = 0;
sekarang = 1;
function savealldetail(maxRow) {
  maxf = maxRow;
  loopsave(1, maxRow);
}

function loopsave(currRow, maxRow) {
  param = "";

  tanggal = trim(document.getElementById("tanggal").value);
  noinvoice = trim(document.getElementById("noinvoice").value);
  tipetbsdetail = trim(document.getElementById("tipetbsdetail").value);
  notransaksidetail = trim(
    document.getElementById("notransaksidetail" + currRow).innerHTML
  );
  notiketdetail = trim(
    document.getElementById("notiketdetail" + currRow).innerHTML
  );
  nospbdetail = trim(
    document.getElementById("nospbdetail" + currRow).innerHTML
  );
  blokdetail = trim(document.getElementById("blokdetail" + currRow).innerHTML);
  tahuntanamdetail = trim(
    document.getElementById("tahuntanamdetail" + currRow).innerHTML
  );
  tanggaltbs1detail = trim(
    document.getElementById("tanggaltbs1detail" + currRow).innerHTML
  );
  tanggaltbs2detail = trim(
    document.getElementById("tanggaltbs2detail" + currRow).innerHTML
  );
  tanggalspbdetail = trim(
    document.getElementById("tanggalspbdetail" + currRow).innerHTML
  );
  tanggalpksdetail = trim(
    document.getElementById("tanggalpksdetail" + currRow).innerHTML
  );
  kgbrutodetail = trim(
    document.getElementById("kgbrutodetail" + currRow).innerHTML
  );
  kgpotongandetail = trim(
    document.getElementById("kgpotongandetail" + currRow).innerHTML
  );
  kgnettodetail = trim(
    document.getElementById("kgnettodetail" + currRow).innerHTML
  );
  rpkgdetail = trim(document.getElementById("rpkgdetail" + currRow).innerHTML);
  totalrpdetail = trim(
    document.getElementById("totalrpdetail" + currRow).innerHTML
  );
  kodesupplierdetail = trim(
    document.getElementById("kodesupplierdetail" + currRow).innerHTML
  );
  noreferensidetail = trim(
    document.getElementById("noreferensidetail" + currRow).innerHTML
  );

  periodedetail = trim(
    document.getElementById("periodedetail" + currRow).innerHTML
  );
  tanggalreferensidetail = trim(
    document.getElementById("tanggalreferensidetail" + currRow).innerHTML
  );

  kgbrutodetail = remove_comma_var(kgbrutodetail);
  kgpotongandetail = remove_comma_var(kgpotongandetail);
  kgnettodetail = remove_comma_var(kgnettodetail);
  rpkgdetail = remove_comma_var(rpkgdetail);
  totalrpdetail = remove_comma_var(totalrpdetail);

  if (document.getElementById("cekdetail" + currRow).checked == true) {
    cekdetail = 1;
  } else {
    cekdetail = 0;
  }

  param +=
    "&proses=savedetail" + "&noinvoice=" + noinvoice + "&tanggal=" + tanggal;
  param +=
    "&notransaksidetail=" +
    notransaksidetail +
    "&notiketdetail=" +
    notiketdetail +
    "&nospbdetail=" +
    nospbdetail;
  param +=
    "&tahuntanamdetail=" +
    tahuntanamdetail +
    "&tanggaltbs1detail=" +
    tanggaltbs1detail +
    "&tanggaltbs2detail=" +
    tanggaltbs2detail;
  param +=
    "&tanggalspbdetail=" +
    tanggalspbdetail +
    "&tanggalpksdetail=" +
    tanggalpksdetail;
  param +=
    "&kgbrutodetail=" +
    kgbrutodetail +
    "&kgpotongandetail=" +
    kgpotongandetail +
    "&kgnettodetail=" +
    kgnettodetail;
  param +=
    "&rpkgdetail=" +
    rpkgdetail +
    "&totalrpdetail=" +
    totalrpdetail +
    "&cekdetail=" +
    cekdetail;
  param +=
    "&kodesupplierdetail=" +
    kodesupplierdetail +
    "&noreferensidetail=" +
    noreferensidetail;
  param +=
    "&tanggalreferensidetail=" +
    tanggalreferensidetail +
    "&periodedetail=" +
    periodedetail;
  param +=
    "&tipetbsdetail=" +
    tipetbsdetail +
    "&blokdetail=" +
    blokdetail +
    "&currRow=" +
    currRow;
  // alert(param);return;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  document.getElementById("rowdetail" + currRow).style.backgroundColor = "";
  document.getElementById("rowdetail" + currRow).style.backgroundColor = "cyan";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          document.getElementById("rowdetail" + currRow).style.backgroundColor =
            "red";
          unlockScreen();
        } else {
          currRow += 1;
          sekarang = currRow;
          if (currRow > maxRow) {
            document.getElementById("datadetail").style.display = "none";
            document.getElementById("tanggal1detail").value = "";
            document.getElementById("tanggal2detail").value = "";
            document.getElementById("tipetbsdetail").value = "";
            isdt = con.responseText.split("####");
            document.getElementById("nilaiinvoice").value = isdt[0];
            document.getElementById("nilaippn").value = isdt[1];
            document.getElementById("kuantitas").value = isdt[2];
            alert("Done");
            loaddatadetail();
          } else {
            loopsave(currRow, maxRow);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function formkapalponton(titledt) {
  width = "780px";
  height = "auto";
  //nopp=document.getElementById('nopp_'+id).value;
  content = "<fieldset><div id=containerd style=width:400px></div></fieldset>";
  ev = "event";
  title = titledt; //"Detail HTML";
  showDialog1(title, content, width, height, ev);
}

function getaddkapalponton() {
  proses = "getaddkapalponton";
  jeniskapalponton = document.getElementById("jeniskapalponton").value;
  param = "jeniskapalponton=" + jeniskapalponton;
  param += "&proses=" + proses;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("namakapalponton").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addkapalponton(noinvoice) {
  titl = "Invoce no :" + noinvoice;
  formkapalponton(titl);
  param = "proses=addkapalponton" + "&noinvoice=" + noinvoice;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          document.getElementById("containerd").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteaddkapalponton(noinvoice, jeniskapalponton, namakapalponton) {
  proses = "deleteaddkapalponton";
  param = "noinvoice=" + noinvoice;
  param += "&namakapalponton=" + namakapalponton;
  param += "&jeniskapalponton=" + jeniskapalponton;
  param += "&proses=" + proses;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          addkapalponton(noinvoice);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveaddkapalponton(noinvoice) {
  proses = "saveaddkapalponton";
  namakapalponton = document.getElementById("namakapalponton").value;
  jeniskapalponton = document.getElementById("jeniskapalponton").value;
  param = "noinvoice=" + noinvoice;
  param += "&namakapalponton=" + namakapalponton;
  param += "&jeniskapalponton=" + jeniskapalponton;
  param += "&proses=" + proses;
  // alert(param);
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          addkapalponton(noinvoice);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function searchnotransaksikasbank(title, ev) {
  // txtfind=document.getElementById('txtfind').value;
  kodeorganisasi = document.getElementById("kodeorganisasi").value;

  if (kodeorganisasi == "") {
    alert("Unit masih kosong");
    return;
    closeDialog;
  }
  content =
    '<div id=formnotransaksikasbank style="max-height:350px;width:max-350;overflow:auto;"></div>';
  height = "";
  width = "";
  showDialog1(title, content, width, height, ev);

  // param='proses=searchnotransaksikasbank'+'&kodeorganisasi='+kodeorganisasi+'&txtfind='+txtfind;
  param =
    "proses=searchnotransaksikasbank" + "&kodeorganisasi=" + kodeorganisasi;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("formnotransaksikasbank").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function movenotransaksikasbank(notransaksikasbank) {
  document.getElementById("notransaksikasbank").value = notransaksikasbank;
  closeDialog();
}

function viewpdf(noinvoice) {
  param = "proses=viewpdf" + "&noinvoice=" + noinvoice;
  tujuan = "keu_slave_penagihan.php?" + param;
  //content = document.getElementById('test');
  content =
    "<iframe frameborder=0 style='width:100%;height:99%' src='" +
    tujuan +
    "'></iframe>";
  width = "820";
  height = "500";
  title = "";
  showDialog5(title, content, width, height, "event");
}
function detailtbs(noinvoice, ev) {
  param = "proses=detailtbs" + "&noinvoice=" + noinvoice;
  tujuan = "keu_slave_penagihan.php?" + param;
  //content = document.getElementById('test');
  content =
    "<iframe frameborder=0 style='width:100%;height:99%' src='" +
    tujuan +
    "'></iframe>";
  width = "820";
  height = "500";
  title = "";
  showDialog5(title, content, width, height, ev);
}

function saveData(fileTarget, passParam) {
  kodebarang = document.getElementById("kodebarang").value;
  var passP = passParam.split("##");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    if (i == 1) {
      param += passP[i] + "=" + getValue(passP[i]);
    } else {
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
  }
  // alert(param);return;
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // Success Response
          // redirectefill(con.responseText);

          // if(kodebarang=='40000003'){
          // document.getElementById('detail').style.display = 'block';
          // loaddatadetail();
          // }else{
          // loadData();
          // clearData();
          // }
          proses = document.getElementById("proses").value;
          // clearData();
          if (proses == "update") {
            alertify.alert("Informasi", "Data Berhasil di ubah");
          }

          document.getElementById("detail").style.display = "block";
          loadfiles();

          // if(proses == 'update') {
          // }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(fileTarget + ".php", param, respon);
}

function redirectefill(noinvoice) {
  param = "method=insertefilltgh&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loadData();
          clearData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnodok() {
  kodebarang = document.getElementById("kodebarang").value;
  document.getElementById("noorder").disabled = false;
  if (kodebarang == "40000003") {
    // document.getElementById('noorder').disabled=true;
    // document.getElementById("nilaiinvoice").disabled = true;
    // document.getElementById("nilaippn").disabled = true;
    // document.getElementById("kuantitas").disabled = true;
  }
}

function getdis() {
  jenis = document.getElementById("jenis").value;
  // if (jenis=='OT') {
  // document.getElementById('kodeorganisasi').disabled=false;
  // document.getElementById('kodecustomer').disabled=false;
  // document.getElementById('keterangantambahan').disabled=false;
  // }else{
  // document.getElementById('kodeorganisasi').disabled=true;
  // document.getElementById('kodecustomer').disabled=true;
  // document.getElementById('keterangantambahan').disabled=true;
  // document.getElementById('keterangantambahan').value='';
  // }
  document.getElementById("kodeorganisasi").disabled = false;
  document.getElementById("kodecustomer").disabled = false;
  document.getElementById("keterangantambahan").disabled = true;
  document.getElementById("keterangantambahan").value = "";
}

function displayFormInput() {
  clearData();
  param = "proses=genNo";
  tujuan = "keu_slave_penagihan";
  post_response_text(tujuan + ".php", param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // Success Response
          document.getElementById("formInput").style.display = "block";
          document.getElementById("listData").style.display = "none";
          document.getElementById("noinvoice").value = "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//indra
function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  cariData(paged);
}

function cariData(pg) {
  ntrs = document.getElementById("txtsearch").value;
  tglcr = document.getElementById("tgl_cari");
  tglcr = tglcr.options[tglcr.selectedIndex].value;
  statId = document.getElementById("statId");
  statId = statId.options[statId.selectedIndex].value;
  param = "proses=loadData" + "&page=" + pg;
  if (ntrs != "") {
    param += "&noinvoice=" + ntrs;
  }
  if (tglcr != "") {
    param += "&tanggalCr=" + tglcr;
  }
  if (statId != "") {
    param += "&statId=" + statId;
  }
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdt = con.responseText.split("####");
          // document.getElementById('formInput').style.display = 'none';
          // document.getElementById('listData').style.display = 'block';
          // document.getElementById('continerlist').innerHTML = isdt[0];
          // document.getElementById('footData').innerHTML = isdt[1];

          document.getElementById("detail").style.display = "none";
          document.getElementById("formInput").style.display = "none";
          document.getElementById("listData").style.display = "block";
          document.getElementById("continerlist").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
          clearData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadData(page) {
  // document.getElementById('txtsearch').value='';
  ntrs = document.getElementById("txtsearch").value;
  tglcr = document.getElementById("tgl_cari");
  tglcr = tglcr.options[tglcr.selectedIndex].value;
  statId = document.getElementById("statId");
  statId = statId.options[statId.selectedIndex].value;
  unitkerjasch = document.getElementById("unitkerjasch");
  unitkerjasch = unitkerjasch.options[unitkerjasch.selectedIndex].value;

  nokontrak = document.getElementById("nokontraksch").value;
  customer = document.getElementById("customersch").value;

  param = "proses=loadData" + "&page=" + page;
  if (ntrs != "") {
    param += "&noinvoice=" + ntrs;
  }
  if (tglcr != "") {
    param += "&tanggalCr=" + tglcr;
  }
  if (statId != "") {
    param += "&statId=" + statId;
  }
  if (unitkerjasch != "") {
    param += "&unitkerjasch=" + unitkerjasch;
  }

  param += "&nokontrak=" + nokontrak;
  param += "&customer=" + customer;

  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("detail").style.display = "none";
          document.getElementById("formInput").style.display = "none";
          document.getElementById("listData").style.display = "block";
          document.getElementById("continerlist").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
          clearData();
          // clearSearch();
          // closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loadData2(page) {
  // document.getElementById('txtsearch').value='';
  clearSearch();
  ntrs = document.getElementById("txtsearch").value;
  tglcr = document.getElementById("tgl_cari");
  tglcr = tglcr.options[tglcr.selectedIndex].value;
  statId = document.getElementById("statId");
  statId = statId.options[statId.selectedIndex].value;
  unitkerjasch = document.getElementById("unitkerjasch");
  unitkerjasch = unitkerjasch.options[unitkerjasch.selectedIndex].value;

  nokontrak = document.getElementById("nokontraksch").value;
  customer = document.getElementById("customersch").value;

  param = "proses=loadData" + "&page=" + page;
  if (ntrs != "") {
    param += "&noinvoice=" + ntrs;
  }
  if (tglcr != "") {
    param += "&tanggalCr=" + tglcr;
  }
  if (statId != "") {
    param += "&statId=" + statId;
  }
  if (unitkerjasch != "") {
    param += "&unitkerjasch=" + unitkerjasch;
  }

  param += "&nokontrak=" + nokontrak;
  param += "&customer=" + customer;

  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("detail").style.display = "none";
          document.getElementById("formInput").style.display = "none";
          document.getElementById("listData").style.display = "block";
          document.getElementById("continerlist").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
          clearData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function clearSearch() {
  document.getElementById("txtsearch").value = "";
  document.getElementById("tgl_cari").value = "";
  document.getElementById("customersch").value = "";
  document.getElementById("nokontraksch").value = "";
  document.getElementById("statId").value = "";
  document.getElementById("unitkerjasch").value = "";
}

function fillField(noinv) {
  param = "proses=getData" + "&noinvoice=" + noinv;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("formInput").style.display = "block";
          document.getElementById("listData").style.display = "none";
          document.getElementById("detail").style.display = "block";
          isis = con.responseText.split("###");
          document.getElementById("noinvoice").value = isis[0];
          document.getElementById("kodeorganisasi").value = isis[1];
          document.getElementById("tanggal").value = isis[2];
          document.getElementById("noorder").value = isis[3];
          document.getElementById("noorder").disabled = true;
          kdcst = document.getElementById("kodecustomer");
          for (a = 0; a < kdcst.length; a++) {
            if (kdcst.options[a].value == isis[4]) {
              kdcst.options[a].selected = true;
            }
          }
          kdcst.disabled = true;
          document.getElementById("nilaiinvoice").value = isis[5];
          document.getElementById("nofakturpajak").value = isis[11];
          document.getElementById("nilaippn").value = isis[6];
          document.getElementById("jatuhtempo").value = isis[7];
          byrke = document.getElementById("bayarke");
          for (a = 0; a < byrke.length; a++) {
            if (byrke.options[a].value == isis[8]) {
              byrke.options[a].selected = true;
            }
          }
          dbt = document.getElementById("debet");
          for (a = 0; a < dbt.length; a++) {
            if (dbt.options[a].value == isis[9]) {
              dbt.options[a].selected = true;
            }
          }
          kridit = document.getElementById("kredit");
          for (a = 0; a < kridit.length; a++) {
            if (kridit.options[a].value == isis[10]) {
              kridit.options[a].selected = true;
            }
          }

          // Disabled
          document.getElementById("noinvoice").disabled = true;
          document.getElementById("jenis").disabled = true;
          document.getElementById("tipeinvoice").disabled = true;
          document.getElementById("kodebarang").disabled = true;
          document.getElementById("kodeorganisasi").disabled = true;

          if (isis[47] == "40000003")
            document.getElementById("nilaiinvoice").disabled = true;
          document.getElementById("nilaippn").disabled = true;
          document.getElementById("kuantitas").disabled = true;

          document.getElementById("keterangan1").value = isis[12];
          document.getElementById("keterangan2").value = isis[13];
          document.getElementById("keterangan3").value = isis[14];
          document.getElementById("keterangan4").value = isis[15];
          document.getElementById("keterangan5").value = isis[16];
          document.getElementById("rupiah1").value = isis[17];
          document.getElementById("rupiah2").value = isis[18];
          document.getElementById("rupiah3").value = isis[19];
          document.getElementById("rupiah4").value = isis[20];
          document.getElementById("rupiah5").value = isis[21];
          document.getElementById("matauang").value = isis[22];
          document.getElementById("kurs").value = isis[23];
          document.getElementById("keterangan6").value = isis[24];
          document.getElementById("rupiah6").value = isis[25];
          document.getElementById("keterangan7").value = isis[26];
          document.getElementById("rupiah7").value = isis[27];
          document.getElementById("keterangan8").value = isis[28];
          document.getElementById("rupiah8").value = isis[29];
          document.getElementById("ttd").value = isis[30];
          document.getElementById("jenis").value = isis[31];
          document.getElementById("jenis").disabled = true;
          document.getElementById("kuantitas").value = isis[32];
          document.getElementById("periode").value = isis[33];
          document.getElementById("nobuktipotong").value = isis[34];
          if (isis[35] == "0000-00-00") {
            isis[35] = "";
          }
          document.getElementById("tglbuktipotong").value = isis[35];
          document.getElementById("jenispph").value = isis[36];
          document.getElementById("jenispph").disabled = true;
          // document.getElementById('jenispph').innerHTML="<option value='"+ isis[36] +"'>"+ isis[42] +"</option>"

          // jnspph = document.getElementById('jenispph');
          // for (a = 0; a < jnspph.length; a++) {
          // if (jnspph.options[a].value == isis[36]) {
          // jnspph.options[a].selected = true;
          // }
          // }
          document.getElementById("pphrupiah").value = isis[37];
          nilaiinvoice = isis[5].replace(/,/g, "");
          pphrupiah = isis[37].replace(/,/g, "");
          persen = (parseFloat(pphrupiah) / parseFloat(nilaiinvoice)) * 100;
          document.getElementById("pphpersen").value = numberWithCommas(
            persen.toFixed(2)
          );
          document.getElementById("pphpersen").disabled = true;
          document.getElementById("pphrupiah").disabled = true;
          document.getElementById("jenispenghasilan").value = isis[38];
          document.getElementById("jenispenghasilan").disabled = true;
          // document.getElementById('jenispenghasilan').innerHTML="<option value='"+ isis[38] +"'>"+ isis[43] +"</option>"

          document.getElementById("carabayar").disabled = true;
          crbyr = document.getElementById("carabayar");
          for (a = 0; a < crbyr.length; a++) {
            if (crbyr.options[a].value == isis[39]) {
              crbyr.options[a].selected = true;
            }
          }
          document.getElementById("npwp").value = isis[40];
          document.getElementById("keterangantambahan").value = isis[44];
          if (isis[31] == "OT") {
            document.getElementById("keterangantambahan").disabled = false;
          } else {
            document.getElementById("keterangantambahan").disabled = true;
          }
          //document.getElementById('berikat').value = isis[41];
          if (isis[41] == 1) {
            document.getElementById("berikat").checked = true;
          } else {
            document.getElementById("berikat").checked = false;
          }

          document.getElementById("notransaksikasbank").value = isis[45];
          document.getElementById("jenisinvoice").value = isis[46];
          document.getElementById("kodebarang").value = isis[47];
          // if(isis[47]=='40000003'){
          // // document.getElementById('detail').style.display = 'block';
          // loaddatadetail(isis[49]);
          // }else{
          // getnpwpunit(isis[49]);
          // } 
          document.getElementById("transport").value = isis[48];
          document.getElementById("proses").value = "update";

          setTimeout(() => {
            getnpwpunit(isis[49], isis[8]);
            setTimeout(() => {
              document.getElementById("tipeinvoice").value = isis[50];
              getBarang("update", isis[47]);
            }, 1000);
          }, 500);
          document.getElementById("nilaipph").value = isis[51]; n
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//jamhari
/*
function searchNosibp(title, content, ev) {
  jenis=document.getElementById('jenis').value;
  kodebarang=document.getElementById('kodebarang').value;
  if (kodebarang=='40000003') {
    document.getElementById('noorder').disabled=true;
  }else{
    width = '800';
    height = '300';
    showDialog1(title, content, width, height, ev);
    getFormNosibp();
  }
}

function getFormNosibp() {
  kodecustomer = document.getElementById('kodecustomer').value;
  param = 'proses=getFormNosipb&kodecustomer=' + kodecustomer;
  tujuan = 'keu_slave_penagihan.php';
  post_response_text(tujuan + '?' + '', param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById('formPencariandata').innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
*/

function searchNosibp(title, content, ev) {
  jenis = document.getElementById("jenis").value;
  kodebarang = document.getElementById("kodebarang").value;
  if (kodebarang === "40000003") {
    // Batalkan fungsi jika TBS
    return;
  }
  // if (kodebarang=='40000003') {
  // 	document.getElementById('noorder').disabled=true;
  // }else{
  // width = '800';
  // height = '300';
  kodecustomer = document.getElementById("kodecustomer").value;
  param = "proses=getFormNosipb&kodecustomer=" + kodecustomer;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan + "?" + "", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          // alertify.alert(con.responseText);
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert(con.responseText);
          // document.getElementById('formPencariandata').innerHTML = con.responseText;
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "80%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  // }
}

function popUpPosting(title, noinvoice, ev) {
  width = "";
  height = "";
  content =
    "<div id=formaAfiliasi style='overflow:auto;max-width:700px;max-height:400px;' ></div>";
  showDialog1(title, content, width, height, ev);
  getFormAfiliasi(noinvoice);
  //alert('asdasd');
}

function getBayarKe() {
  debet =
    document.getElementById("debet").options[
      document.getElementById("debet").selectedIndex
    ].value;
  bayarke = document.getElementById("bayarke");
  param = "proses=getBayarKe&debet=" + debet;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan + "?" + "", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          bayarke.options.length = 0;
          eval(con.responseText);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getKursInvoice() {
  noorder = document.getElementById("noorder").value;
  matauang = document.getElementById("matauang").value;
  tanggal = document.getElementById("tanggal").value;
  param =
    "proses=getKursInvoice&noorder=" +
    noorder +
    "&matauang=" +
    matauang +
    "&tanggal=" +
    tanggal;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan + "?" + "", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          document.getElementById("tanggal").value = "";
          document.getElementById("matauang").value = "IDR";
        } else {
          // alertify.alert(con.responseText);
          document.getElementById("kurs").value = con.responseText;
          getNilai();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getNilai() {
  nilaippn = document.getElementById("nilaippn").value;
  //nilaiinvoice=document.getElementById('nilaiinvoice').value;
  nilaiinvoice = document.getElementById("nilaiinvoice");
  nilaiinvoice.value = remove_comma_var(nilaiinvoice.value);
  nilaiinvoice = nilaiinvoice.value;
  kurs = document.getElementById("kurs").value;
  matauang = document.getElementById("matauang").value;
  param =
    "proses=getNilai&nilaippn=" +
    nilaippn +
    "&nilaiinvoice=" +
    nilaiinvoice +
    "&kurs=" +
    kurs +
    "&matauang=" +
    matauang;
  tujuan = "keu_slave_penagihan.php";
  if (confirm("anda yankin mengganti mata uang??"))
    post_response_text(tujuan + "?" + "", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // alertify.alert(con.responseText);
          ar = con.responseText.split("###");
          document.getElementById("nilaippn").value = ar[0];
          document.getElementById("nilaiinvoice").value = ar[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getFormAfiliasi(noinvoice) {
  param = "proses=getFormAfiliasi&noinvoice=" + noinvoice;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan + "?" + "", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("formaAfiliasi").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findNosipb() {
  jenis = trim(document.getElementById("jenis").value);
  txt = trim(document.getElementById("nosipbcr").value);
  jenisinvoice = trim(document.getElementById("jenisinvoice").value);
  kodecustomer = trim(document.getElementById("kodecustomer").value);
  kodebarang = trim(document.getElementById("kodebarang").value);
  kodeorganisasi = trim(document.getElementById("kodeorganisasi").value);
  idcust = document.getElementById("custId");
  idcust = idcust.options[idcust.selectedIndex].value;

  tanggal = document.getElementById("tanggal").value;

  if (jenis == "") {
    alert("Jenis Penagihan tidak boleh kosong.");
    return;
  }

  param =
    "txtfind=" +
    txt +
    "&proses=getnosibp" +
    "&custId=" +
    idcust +
    "&jenisdok=" +
    jenis +
    "&tanggal=" +
    tanggal +
    "&kodecustomer=" +
    kodecustomer +
    "&kodebarang=" +
    kodebarang +
    "&kodeorganisasi=" +
    kodeorganisasi +
    "&jenisinvoice=" +
    jenisinvoice;
  tujuan = "keu_slave_penagihan.php";
  if (txt == "") {
    alert("No. Dokumen tidak boleh kosong");
  } else {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("container2").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detaildata(
  nokontrak,
  kdcust,
  kdHo,
  matauang,
  noaknbyr,
  ppn,
  npwp,
  berikat,
  kodebarang,
  jenispph,
  pphpersen,
  carabayar,
  jenispenghasilan
) {
  jenis = document.getElementById("jenis").value;
  tanggal = document.getElementById("tanggal").value;
  if (berikat == "1") {
    document.getElementById("berikat").checked = true;
  } else {
    document.getElementById("berikat").checked = false;
  }
  if (kodebarang == "40000003") {
    document.getElementById("jenispph").value = jenispph;
    document.getElementById("pphpersen").value = pphpersen;
    document.getElementById("carabayar").value = carabayar;
    document.getElementById("jenispenghasilan").value = jenispenghasilan;
    document.getElementById("jenispph").disabled = false;
    document.getElementById("pphpersen").disabled = false;
    document.getElementById("pphrupiah").disabled = false;
    document.getElementById("jenispenghasilan").disabled = false;
  } else {
    document.getElementById("jenispph").value = "";
    document.getElementById("pphpersen").value = "";
    document.getElementById("carabayar").value = "";
    document.getElementById("pphrupiah").value = "";
    document.getElementById("jenispenghasilan").value = "";
    document.getElementById("nilaippn").value = ppn;
    document.getElementById("jenispph").disabled = true;
    document.getElementById("pphpersen").disabled = true;
    document.getElementById("pphrupiah").disabled = true;
    document.getElementById("jenispenghasilan").disabled = true;
  }
  document.getElementById("npwp").value = npwp;
  document.getElementById("noorder").value = nokontrak;
  document.getElementById("matauang").value = matauang;
  document.getElementById("kodeorganisasi").value = kdHo;
  kridit = document.getElementById("kodecustomer");
  for (a = 0; a < kridit.length; a++) {
    if (kridit.options[a].value == kdcust) {
      kridit.options[a].selected = true;
    }
  }
  kridit.disabled = true;

  bayar = document.getElementById("bayarke");
  for (a = 0; a < bayar.length; a++) {
    if (bayar.options[a].value == noaknbyr) {
      bayar.options[a].selected = true;
    }
  }
  // bayar.disabled = true;

  param =
    "proses=detaildata" +
    "&nokontrak=" +
    nokontrak +
    "&kodebarang=" +
    kodebarang +
    "&kdcust=" +
    kdcust +
    "&tanggal=" +
    tanggal;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("container2").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function checkAll() {
  totrow = document.getElementById("totrow").value;
  btn = document.getElementById("btnall");
  if (btn.checked == true) {
    chk = true;
  } else {
    chk = false;
  }

  for (i = 1; i <= totrow; i++) {
    document.getElementById("no_" + i).checked = chk;
  }
}

function adddetail(nokontrak, kodebarang, kdcust) {
  totrow = trim(document.getElementById("totrow").value);
  tanggal = trim(document.getElementById("tanggal").value);

  var allData = "";
  var cekpil = 0;
  var nilinvoice = 0;
  var nilinvoice2 = 0;
  var kgkirimvl = 0;
  var kgkirimv2 = 0;
  for (dwc = 1; dwc <= totrow; dwc++) {
    if (document.getElementById("no_" + dwc).checked == true) {
      allData +=
        "&tglpengakuan[]=" +
        document.getElementById("tglpengakuan_" + dwc).innerHTML;
      allData +=
        "&kgkirim[]=" + document.getElementById("kgkirim_" + dwc).innerHTML;
      allData +=
        "&totrp[]=" + document.getElementById("totrp_" + dwc).innerHTML;
      nilinvoice = parseInt(
        remove_comma_var(document.getElementById("totrp_" + dwc).innerHTML)
      );
      nilinvoice2 = nilinvoice2 + nilinvoice;
      kgkirimvl = parseInt(
        remove_comma_var(document.getElementById("kgkirim_" + dwc).innerHTML)
      );
      kgkirimv2 = kgkirimv2 + kgkirimvl;
      cekpil += 1;
    }
  }

  if (cekpil == 0) {
    alert("Data belum terpilih.");
    return;
  }
  document.getElementById("nilaiinvoice").value = nilinvoice2;
  document.getElementById("kuantitas").value = kgkirimv2;
  param =
    "totrow=" +
    cekpil +
    "&nokontrak=" +
    nokontrak +
    "&tanggal=" +
    tanggal +
    "&kdcust=" +
    kdcust +
    "&proses=adddetail";
  param += allData;

  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdt = con.responseText.split("##");
          document.getElementById("noinvoice").value = isdt[0];
          document.getElementById("nilaippn").value = isdt[1];
          document.getElementById("noorder").disabled = true;
          document.getElementById("tanggal").disabled = true;
          document.getElementById("jenis").disabled = true;
          closeDialog();
          if (kodebarang == "40000003") {
            setpph(kdcust);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function inputAfiliasi(noinvoice) {
  noafiliasi = trim(document.getElementById("noafiliasi").value);
  param =
    "noafiliasi=" +
    noafiliasi +
    "&noinvoice=" +
    noinvoice +
    "&proses=inputNoAfiliasi";
  tujuan = "keu_slave_penagihan.php";
  if (noafiliasi == "") {
    alert(notifnoinvoiceafiliasi);
  } else {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          getPage();
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function setDataTbs(
  nosibp,
  kdcust,
  kdHo,
  matauang,
  nilInvoice,
  noaknbyr,
  ppn,
  hKuantitas,
  npwp,
  berikat,
  kodebarang,
  jenispph,
  pphpersen,
  carabayar,
  jenispenghasilan
) {
  jenis = document.getElementById("jenis").value;
  if (berikat == "1") {
    document.getElementById("berikat").checked = true;
  } else {
    document.getElementById("berikat").checked = false;
  }
  document.getElementById("berikat").disabled = true;

  document.getElementById("jenispph").value = jenispph;
  document.getElementById("pphpersen").value = pphpersen;
  document.getElementById("carabayar").value = carabayar;
  document.getElementById("jenispenghasilan").value = jenispenghasilan;
  document.getElementById("jenispph").disabled = false;
  document.getElementById("pphpersen").disabled = false;
  document.getElementById("pphrupiah").disabled = false;
  document.getElementById("jenispenghasilan").disabled = false;

  document.getElementById("npwp").value = npwp;
  document.getElementById("noorder").value = nosibp;
  document.getElementById("matauang").value = matauang;

  bayar = document.getElementById("bayarke");
  for (a = 0; a < bayar.length; a++) {
    if (bayar.options[a].value == noaknbyr) {
      bayar.options[a].selected = true;
    }
  }

  alertify.popup().destroy();
  if (kodebarang == "40000003") {
    setpph(kdcust);
    getrppph();
  }
}

function setData(
  nosibp,
  kdcust,
  kdHo,
  matauang,
  nilInvoice,
  noaknbyr,
  ppn,
  hKuantitas,
  npwp,
  berikat,
  kodebarang,
  jenispph,
  pphpersen,
  carabayar,
  jenispenghasilan
) {
  jenis = document.getElementById("jenis").value;
  if (berikat == "1") {
    document.getElementById("berikat").checked = true;
  } else {
    document.getElementById("berikat").checked = false;
  }
  document.getElementById("berikat").disabled = true;
  if (kodebarang == "40000003") {
    document.getElementById("jenispph").value = jenispph;
    document.getElementById("pphpersen").value = pphpersen;
    document.getElementById("carabayar").value = carabayar;
    document.getElementById("jenispenghasilan").value = jenispenghasilan;
    document.getElementById("jenispph").disabled = false;
    document.getElementById("pphpersen").disabled = false;
    document.getElementById("pphrupiah").disabled = false;
    document.getElementById("jenispenghasilan").disabled = false;
  } else {
    document.getElementById("jenispph").value = "";
    document.getElementById("pphpersen").value = "";
    document.getElementById("carabayar").value = carabayar;
    document.getElementById("pphrupiah").value = "";
    document.getElementById("jenispenghasilan").value = jenispenghasilan;
    document.getElementById("nilaippn").value = ppn;
    document.getElementById("jenispph").disabled = true;
    document.getElementById("pphpersen").disabled = true;
    document.getElementById("pphrupiah").disabled = true;
    document.getElementById("jenispenghasilan").disabled = true;
  }
  document.getElementById("npwp").value = npwp;
  document.getElementById("noorder").value = nosibp;
  document.getElementById("matauang").value = matauang;
  document.getElementById("nilaiinvoice").value = nilInvoice;
  // document.getElementById('kodeorganisasi').value = kdHo;
  document.getElementById("kuantitas").value = hKuantitas;
  // kridit = document.getElementById('kodecustomer');
  // for (a = 0; a < kridit.length; a++) {
  // if (kridit.options[a].value == kdcust) {
  // kridit.options[a].selected = true;
  // }
  // }
  bayar = document.getElementById("bayarke");
  for (a = 0; a < bayar.length; a++) {
    if (bayar.options[a].value == noaknbyr) {
      bayar.options[a].selected = true;
    }
  }

  // if (jenis == 'OT' || jenis == 'DS') {
  // kridit.disabled = false;
  // bayar.disabled = false;
  // } else {
  // kridit.disabled = true;
  // bayar.disabled = true;
  // }

  // closeDialog();
  alertify.popup().destroy();
  if (kodebarang == "40000003") {
    setpph(kdcust);
    getrppph();
  }
}

function cancelData() {
  document.getElementById("formInput").style.display = "none";
  document.getElementById("listData").style.display = "block";
  clearData();
}

function clearData() {
  document.getElementById("jenispph").disabled = false;
  document.getElementById("pphpersen").disabled = false;
  document.getElementById("pphrupiah").disabled = false;
  document.getElementById("jenis").disabled = false;
  document.getElementById("jatuhtempo").value = "";
  document.getElementById("nofakturpajak").value = "";
  document.getElementById("tanggal").value = "";
  document.getElementById("tanggal").disabled = false;
  document.getElementById("bayarke").disabled = false;
  document.getElementById("bayarke").value = "";
  document.getElementById("kodecustomer").value = "";
  document.getElementById("kodeorganisasi").value = "";
  document.getElementById("matauang").value = "IDR";
  document.getElementById("kurs").value = "1";
  document.getElementById("noorder").value = "";
  document.getElementById("noorder").disabled = false;
  document.getElementById("nilaippn").value = "0";
  document.getElementById("nilaipph").value = "0";
  document.getElementById("nilaiinvoice").value = "0";
  document.getElementById("debet").value = "";
  document.getElementById("kredit").value = "";
  // document.getElementById('txtsearch').value = "";
  document.getElementById("tgl_cari").value = "";
  document.getElementById("rupiah1").value = "0";
  document.getElementById("rupiah2").value = "0";
  document.getElementById("rupiah3").value = "0";
  document.getElementById("rupiah4").value = "0";
  document.getElementById("rupiah5").value = "0";
  document.getElementById("rupiah6").value = "0";
  document.getElementById("rupiah7").value = "0";
  document.getElementById("rupiah8").value = "0";
  // setValue('ttd', "Rizki Daslia");
  document.getElementById("jenis").value = "";
  document.getElementById("kuantitas").value = "0";
  document.getElementById("periode").value = "";
  document.getElementById("nobuktipotong").value = "";
  document.getElementById("tglbuktipotong").value = "";
  document.getElementById("jenispph").value = "";
  document.getElementById("pphpersen").value = "";
  document.getElementById("pphrupiah").value = "";
  document.getElementById("jenispenghasilan").value = "";
  document.getElementById("carabayar").value = "";
  document.getElementById("npwp").value = "";
  document.getElementById("keterangantambahan").value = "";
  document.getElementById("berikat").checked = false;
  document.getElementById("jenispph").disabled = false;
  document.getElementById("pphpersen").disabled = false;
  document.getElementById("pphrupiah").disabled = false;
  document.getElementById("jenispenghasilan").disabled = false;
  document.getElementById("carabayar").disabled = false;
  document.getElementById("keterangantambahan").disabled = true;
}

function delData(notrans, nofakturpajak) {
  param =
    "nofakturpajak=" +
    nofakturpajak +
    "&noinvoice=" +
    notrans +
    "&proses=delData";
  tujuan = "keu_slave_penagihan.php";
  alertify.confirm(
    "Informasi",
    "Anda yakin ingin menghapus data ini " + notrans + " ?",
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
          //alertify.alert(con.responseText);
          updatefaktur(notrans, nofakturpajak);
          // deletefileall(notrans);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function updatefaktur(notrans, nofakturpajak) {
  param =
    "nofakturpajak=" +
    nofakturpajak +
    "&noinvoice=" +
    notrans +
    "&proses=updatefaktur";
  tujuan = "keu_slave_penagihan.php";
  if (
    confirm(
      "Anda ingin menggunakan faktur " +
      nofakturpajak +
      " untuk transaksi lain ???"
    )
  ) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          alert("Faktur telah di update.");
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingData(notrans) {
  param = "noinvoice=" + notrans + "&proses=postingData";
  tujuan = "keu_slave_penagihan.php";
  alertify.confirm(
    "Informasi",
    "Anda yakin ingin memposting data ini " + notrans + " ?",
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
          //alertify.alert(con.responseText);
          redirectefill2(notrans);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewefill(noinvoice, ev) {
  content = '<div id=formviewefill  style="height:100%;"></div>';
  title = "View Efilling System";
  height = "";
  width = "";
  showDialog5(title, content, width, height, "event");
  showefil(noinvoice);

  var dialog = document.getElementById("dynamic5");
  clientWidth = document.getElementById("dynamic5").clientWidth;
  clientHeight = document.getElementById("dynamic5").clientHeight;
  pos = new Array();
  pos = getMouseP(ev);

  dialog.style.top = pos[1] + "px";
  dialog.style.left = pos[0] - clientWidth - 500 + "px";
}

function showefil(noinvoice) {
  param = "method=viewefilltgh&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("formviewefill").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addfiledata(noinvoice, criteria) {
  uploadfile = document.getElementById("upload_" + criteria);
  var file = uploadfile.files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", uploadfile.value);
  formdata.append("noinvoice", noinvoice);
  formdata.append("criteria", criteria);
  if (uploadfile.value == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }

  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_efill.php?method=uploadfilepgh", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //=== Success Response
          document.getElementsByClassName("mybutton").disabled = false;
          alert("Uploaded Success.");
          document.getElementById("upload_" + criteria).value = "";
          document.getElementById("bodyefil").innerHTML = "";
          document.getElementById("bodyefil").innerHTML = con.responseText;
          // loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteefil(noinvoice, namafile) {
  param =
    "method=deleteefilpgh&namafile=" + namafile + "&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  if (confirm("Anda yakin hapus item/file ini : " + namafile + " ?")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alert("Success");
          document.getElementById("bodyefil").innerHTML = "";
          document.getElementById("bodyefil").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function redirectefill2(noinvoice) {
  param = "method=insertefilltgh&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
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

function detailPDF(numRow, ev) {
  // Prep Param
  var notransaksi = document
    .getElementById("notransaksi_" + numRow)
    .getAttribute("value");
  var noakun = document
    .getElementById("noakun_" + numRow)
    .getAttribute("value");
  var tipetransaksi = document
    .getElementById("tipetransaksi_" + numRow)
    .getAttribute("value");
  var kodeorg = document
    .getElementById("kodeorg_" + numRow)
    .getAttribute("value");
  param =
    "proses=pdf&notransaksi=" +
    notransaksi +
    "&kodeorg=" +
    kodeorg +
    "&tipetransaksi=" +
    tipetransaksi +
    "&noakun=" +
    noakun;
  showDialog1(
    "Print PDF",
    "<iframe frameborder=0 style='width:795px;height:400px'" +
    " src='keu_slave_kasbank_print_detail.php?" +
    param +
    "'></iframe>",
    "800",
    "400",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}

function getdate30() {
  tanggal = document.getElementById("tanggal").value;
  param = "tanggal=" + tanggal + "&proses=getdate30";
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          // document.getElementById('jatuhtempo').value = con.responseText;

          isi = con.responseText.split("####");
          document.getElementById("jatuhtempo").value = isi[0];
          document.getElementById("periode").value = isi[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getrppph() {
  nilaiinvoice = document.getElementById("nilaiinvoice").value;
  nilaiinvoice = nilaiinvoice.replace(/,/g, "");
  pphpersen = document.getElementById("pphpersen").value;
  rp = parseFloat(nilaiinvoice) * parseFloat(pphpersen / 100);
  document.getElementById("pphrupiah").value = numberWithCommas(rp.toFixed(2));
}

function getpersenpph() {
  nilaiinvoice = document.getElementById("nilaiinvoice").value;
  nilaiinvoice = nilaiinvoice.replace(/,/g, "");
  pphrupiah = document.getElementById("pphrupiah").value;
  pphrupiah = pphrupiah.replace(/,/g, "");
  persen = (parseFloat(pphrupiah) / parseFloat(nilaiinvoice)) * 100;
  document.getElementById("pphpersen").value = numberWithCommas(
    persen.toFixed(2)
  );
}

function getrpppn() {
  nilaiinvoice = document.getElementById("nilaiinvoice").value;
  nilaiinvoice = nilaiinvoice.replace(/,/g, "");
  rpppn = parseFloat(nilaiinvoice) * 0.1;
  document.getElementById("nilaippn").value = numberWithCommas(
    rpppn.toFixed(0)
  );
  getrppph();
}

function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function searchfaktur(ev) {
  kodeorganisasi = document.getElementById("kodeorganisasi").value;
  if (kodeorganisasi == "") {
    alert("Error : Silahkan pilih nomor kontrak terlebih dahulu.");
    return;
  }
  title = "";
  width = "";
  height = "";
  content =
    "<div id=containersearch style='max-width:700px;max-height:400px;' ></div>";
  showDialog1(title, content, width, height, ev);
  param = "proses=getFormFaktur" + "&kodeorganisasi=" + kodeorganisasi;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan + "?" + "", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("containersearch").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findnofaktur() {
  fakturcari = trim(document.getElementById("fakturcari").value);
  kodeorgcari = document.getElementById("kodeorgcari").value;
  param =
    "kodeorgcari=" +
    kodeorgcari +
    "&fakturcari=" +
    fakturcari +
    "&proses=getnofaktur";
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("containerfaktur").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function setFaktur(no, faktur) {
  if (no != 1) {
    if (
      confirm(
        "Nomor faktur " +
        faktur +
        " bukan nomor terkecil, ingin tetap memakai faktur ini ???"
      )
    ) {
      document.getElementById("nofakturpajak").value = faktur;
      document.getElementById("kodeorgcari").value = "";
      closeDialog();
    }
  } else {
    document.getElementById("nofakturpajak").value = faktur;
    document.getElementById("kodeorgcari").value = "";
    closeDialog();
  }
}

function changefaktur(noinvoice, pt, ev) {
  title = "No : " + noinvoice;
  width = "";
  height = "";
  content =
    "<div id=contchangefaktur style='overflow:auto;max-width:700px;max-height:400px;' ></div>";
  showDialog1(title, content, width, height, ev);
  param = "proses=changefaktur" + "&noinvoice=" + noinvoice + "&pt=" + pt;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan + "?" + "", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("contchangefaktur").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function savechfaktur() {
  noinvoice = document.getElementById("tempnoinvoice").value;
  fakturlama = document.getElementById("fakturlama").value;
  fakturbaru = document.getElementById("fakturbaru").value;
  bupotlama = trim(document.getElementById("bupotlama").value);
  bupotbaru = trim(document.getElementById("bupotbaru").value);
  param =
    "noinvoice=" +
    noinvoice +
    "&fakturbaru=" +
    fakturbaru +
    "&bupotbaru=" +
    bupotbaru +
    "&proses=savechfaktur";
  if (fakturbaru == "" && bupotbaru == "") {
    alert("Error : No Faktur Baru atau Bukti Potong Baru tidak boleh kosong.");
    return;
  }
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          alert("Update success...");
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelchfaktur() {
  closeDialog();
}

function form() {
  width = "";
  height = "";
  content =
    '<fieldset style="width:97%;"><div id=contview style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "View";
  showDialog5(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic5").style.top = pos[1] - 300 + "px";
  document.getElementById("dynamic5").style.left = pos[0] - 200 + "px";
  document.getElementById("dynamic5").style.display = "";
}

function setpph(kdcust) {
  param = "kodecustomer=" + kdcust + "&proses=setpph";
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isi = con.responseText.split("####");

          document.getElementById("carabayar").value = isi[4];
          document.getElementById("jenispph").innerHTML =
            "<option value='" + isi[0] + "'>" + isi[1] + "</option>";
          document.getElementById("jenispenghasilan").innerHTML =
            "<option value='" + isi[2] + "'>" + isi[3] + "</option>";
          document.getElementById("pphpersen").value = isi[5];
          document.getElementById("carabayar").disabled = true;
          getrppph();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnpwp() {
  kdcust = document.getElementById("kodecustomer").value;
  param = "kodecustomer=" + kdcust + "&proses=getnpwp";
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("npwp").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getnpwpunit(npwpunit, bayarke) {
  kodeorganisasi = document.getElementById("kodeorganisasi").value;
  param = "kodeorganisasi=" + kodeorganisasi + "&proses=getnpwpunit";
  param += "&npwpunit=" + npwpunit;
  param += "&bayarke=" + bayarke;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // document.getElementById('npwpunit').innerHTML = con.responseText;
          isdt = con.responseText.split("####");
          document.getElementById("npwpunit").innerHTML = isdt[0];
          document.getElementById("bayarke").innerHTML = isdt[1];
          loadfiles();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function submitfile() {
  var noinvoice = document.getElementById("noinvoice").value;
  var kriteriaefil = document.getElementById("kriteriaefil").value;
  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  formdata.append("noinvoice", noinvoice);
  formdata.append("kriteriaefil", kriteriaefil);
  if (getValue("upload") == "") {
    alertify.alert("Informasi", "warning : Upload file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "keu_slave_penagihan.php?proses=submitfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //=== Success Response
          document.getElementsByClassName("mybutton").disabled = false;
          alertify.alert("Informasi", "Uploaded Success.");
          document.getElementById("upload").value = "";
          loadfiles(noinvoice);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(noinvoice, namafile) {
  param = "proses=deletefile&noinvoice=" + noinvoice + "&namafile=" + namafile;
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          loadfiles(noinvoice);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfiles() {
  noinvoice = document.getElementById("noinvoice").value;
  param = "proses=loadfiles&noinvoice=" + trim(noinvoice);
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          if (document.getElementById("listfiles") !== null) {
            document.getElementById("listfiles").innerHTML = con.responseText;
          }
          loaddatadetail();
          // document.getElementById('listfiles').innerHTML=con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewlistfile(ev, noinvoice) {
  param = "proses=viewlistfile&noinvoice=" + trim(noinvoice);
  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          if (document.getElementById("listfiles") !== null) {
            // document.getElementById('listfiles').innerHTML = con.responseText;
            alertify
              .popup("Detail", con.responseText)
              .set({ resizable: true, maximizable: true })
              .resizeTo("80%", "80%");
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getBarang(ev = "insert", kodebarang = "") {
  tipe = document.getElementById("tipeinvoice").value;

  param = "proses=getbarang&tipeinvoice=" + tipe;
  if (ev == "update") {
    param += "&kodebarang=" + kodebarang;
  }

  tujuan = "keu_slave_penagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          const data = JSON.parse(con.responseText);
          document.getElementById("kodebarang").innerHTML = data.optKodebarang;
          document.getElementById("kodecustomer").innerHTML =
            data.optKodecustomer;

          // Get Data
          let nodokumen = document.getElementById("noorder");

          document.getElementById("berikat").disabled = false;
          document.getElementById("berikat").checked = false;
          document.getElementById("kuantitas").disabled = false;
          document.getElementById("kuantitas").value = 0;
          if (tipe == "OTPI") {
            nodokumen.removeAttribute("onclick");
            document.getElementById("jenis").disabled = true;
            document.getElementById("jenisinvoice").disabled = true;
          } else if (tipe == "OT") {
            nodokumen.removeAttribute("onclick");
            document.getElementById("jenis").disabled = false;
            document.getElementById("jenisinvoice").disabled = false;
          } else if (tipe == "FEM") {
            nodokumen.removeAttribute("onclick");
            document.getElementById("jenis").disabled = true;
            document.getElementById("jenis").value = "";
            document.getElementById("jenisinvoice").disabled = true;
            document.getElementById("jenisinvoice").value = "PL";
            document.getElementById("berikat").disabled = true;
            document.getElementById("berikat").checked = false;
            document.getElementById("kuantitas").disabled = true;
            document.getElementById("kuantitas").value = 1;
          } else {
            nodokumen.setAttribute(
              "onclick",
              'searchNosibp("Cari","<div id=formPencariandata></div>","event")'
            );
            document.getElementById("jenis").disabled = false;
            document.getElementById("jenisinvoice").disabled = false;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
