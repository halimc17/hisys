function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loadData(paged);
}
function loadData(page) {
  const tgldari = document.querySelector("#tgldari").value;
  const tglsmp = document.querySelector("#tglsmp").value;
  const kodeorg = document.querySelector("#kodeorg").value;
  const divisi = document.querySelector("#divisi").value;

  // param = 'proses=loadData&page=' + page;
  // param += '&tanggal=' + tanggal;
  let param = `proses=loadData&type=html&page=${page}&tgldari=${tgldari}&tglsmp=${tglsmp}&kodeorg=${kodeorg}&divisi=${divisi}`;

  tujuan = "kebun_slave_taksasi.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("dataList").style.display = "block";
          document.getElementById("formData").style.display = "none";
          document.getElementById("container").innerHTML = con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailExcel(ev) {
  const tgldari = document.querySelector("#tgldari").value;
  const tglsmp = document.querySelector("#tglsmp").value;
  const kodeorg = document.querySelector("#kodeorg").value;
  const divisi = document.querySelector("#divisi").value;
  judul = "Report Ms.Excel";
  let param = `proses=loadData&type=excel&tgldari=${tgldari}&tglsmp=${tglsmp}&kodeorg=${kodeorg}&divisi=${divisi}`;

  printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "300";
  height = "100";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function getDivisi() {
  const kodeorg = document.querySelector("#kodeorg").value;

  let param = `proses=getDivisi&kodeorg=${kodeorg}`;
  let tujuan = "kebun_slave_taksasi.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.querySelector("#divisi").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveData(fileTarget, passParam) {
  var elor = "";
  var passP = passParam.split("##");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    if (i == 1) {
      if (getValue(passP[i]) == "") {
        alertify.alert("tanggal tidak boleh kosong.");
        elor = "eror";
      }
      param += passP[i] + "=" + getValue(passP[i]);
    }
    if (i == 3) {
      if (getValue(passP[i]) == "") {
        alertify.alert("blok tidak boleh kosong.");
        elor = "eror";
      }
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
    if (i == 11) {
      if (getValue(passP[i]) == "" || getValue(passP[i]) == "0") {
        alertify.alert("Jjg Output tidak boleh kosong.");
        elor = "eror";
      }
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
    if (i == 14) {
      if (getValue(passP[i]) == "" || getValue(passP[i]) == "0") {
        alertify.alert("Mandor tidak boleh kosong.");
        elor = "eror";
      }
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
    if (i == 8) {
      if (getValue(passP[i]) == "") {
        alertify.alert(
          "jumlah pokok tidak boleh kosong.\n silakan mengisi luas dan melengkapi data di SETUP - BLOK."
        );
        elor = "eror";
      }
      if (getValue(passP[i]) == "0") {
        alertify.alert(
          "jumlah pokok tidak boleh kosong.\n silakan mengisi luas dan melengkapi data di SETUP - BLOK."
        );
        elor = "eror";
      }
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    } else {
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
  }
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // Success Response
          selesaiIsi();
          alertify.alert("Done.");
          loaddatadetail(getValue("afdeling"));
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  if (elor == "") post_response_text(fileTarget + ".php", param, respon);
}
function cancelIsi() {
  document.getElementById("afdeling").value = "";
  document.getElementById("tanggal").value = "";
  document.getElementById("tanggal").disabled = false;
  document.getElementById("loaddatadetail").innerHTML = "";
  // document.getElementById("blok").value = "";
  setValue2("blok",null);
  setValue2("mandor",null);
  document.getElementById("seksi").value = "";
  document.getElementById("hasisa").value = "";
  document.getElementById("haesok").value = "";
  document.getElementById("jmlhpokok").value = "";
  document.getElementById("persenbuahmatang").value = "";
  document.getElementById("jjgmasak").value = "";
  document.getElementById("jjgoutput").value = "";
  document.getElementById("hkdigunakan").value = "";
  document.getElementById("bisapanen").value = "";
  document.getElementById("bjr").value = "";
  document.getElementById("luas").value = "";
  document.getElementById("pokok").value = "";
  document.getElementById("sph").value = "";
  document.getElementById("tt").value = "";
  document.getElementById("kgmasak").value = "";
  document.getElementById("kgoutput").value = "";
  document.getElementById("rotasi").value = "";
  document.getElementById("tanggal").disabled = false;
  document.getElementById("afdeling").disabled = false;
  document.getElementById("proses").value = "insert";
  document.getElementById("kebundt").value = "";
  document.getElementById("kebundt").disabled = false;
}
function selesaiIsi() {
  document.getElementById("tanggal").disabled = true;
  document.getElementById("kebundt").disabled = true;
  document.getElementById("afdeling").disabled = true;
  document.getElementById("blok").value = "";
  document.getElementById("seksi").value = "";
  document.getElementById("hasisa").value = "";
  document.getElementById("haesok").value = "";
  document.getElementById("jmlhpokok").value = "";
  document.getElementById("persenbuahmatang").value = "";
  document.getElementById("jjgmasak").value = "";
  document.getElementById("jjgoutput").value = "";
  document.getElementById("hkdigunakan").value = "";
  document.getElementById("bisapanen").value = "";
  document.getElementById("kgmasak").value = "";
  document.getElementById("kgoutput").value = "";
  document.getElementById("bjr").value = "";
  document.getElementById("sph").value = "";
  document.getElementById("luas").value = "";
  document.getElementById("pokok").value = "";
  document.getElementById("tt").value = "";
  document.getElementById("rotasi").value = "";
  document.getElementById("mandor").value = "";
  setValue2("mandor",null);
}
function showAdd() {
  document.getElementById("dataList").style.display = "none";
  document.getElementById("formData").style.display = "block";
  cancelIsi();
}
function showEdit(notrans, tgl, blok) {
  param = "proses=getData" + "&afdeling=" + notrans + "&tanggal=" + tgl;
  param += "&blok=" + blok;
  fileTarget = "kebun_slave_taksasi";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("dataList").style.display = "none";
          document.getElementById("formData").style.display = "block";
          cancelIsi();
          isiDt = con.responseText.split("###");
          document.getElementById("afdeling").value = notrans;
          document.getElementById("tanggal").value = isiDt[1];
          //document.getElementById('blok').value = isiDt[2];
          setValue2("blok", isiDt[2]);
          setValue2("mandor", isiDt[16]);
          document.getElementById("seksi").value = isiDt[3];
          document.getElementById("hasisa").value = isiDt[4];
          document.getElementById("haesok").value = isiDt[5];
          document.getElementById("jmlhpokok").value = isiDt[6];
          document.getElementById("persenbuahmatang").value = isiDt[7];
          document.getElementById("jjgmasak").value = isiDt[8];
          document.getElementById("jjgoutput").value = isiDt[9];
          document.getElementById("hkdigunakan").value = isiDt[10];
          document.getElementById("bisapanen").value = isiDt[10];
          document.getElementById("bjr").value = isiDt[11];
          document.getElementById("kgmasak").value = numberFormat(
            isiDt[8] * isiDt[11],
            0
          );
          document.getElementById("kgoutput").value = numberFormat(
            isiDt[9] * isiDt[11],
            0
          );
          document.getElementById("luas").value = isiDt[12];
          document.getElementById("pokok").value = isiDt[13];
          document.getElementById("sph").value = isiDt[14];
          document.getElementById("tt").value = isiDt[15];
          document.getElementById("mandor").value = isiDt[16];
          document.getElementById("rotasi").value = isiDt[17];

          document.getElementById("jjgoutput").disabled = false;
          document.getElementById("tanggal").disabled = true;
          document.getElementById("afdeling").disabled = true;
          document.getElementById("kebundt").disabled = true;
          kbn = isiDt[0].substring(0, 4);
          document.getElementById("kebundt").value = kbn;
          //getAfdeling(kbn, isiDt[0], isiDt[2]);
          loaddatadetail(notrans);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(fileTarget + ".php", param, respon);
}

function loaddatadetail(divisi) {
  tanggal = document.getElementById("tanggal").value;
  param = "proses=loaddatadetail";
  param += "&divisi=" + divisi;
  param += "&tanggal=" + tanggal;

  tujuan = "kebun_slave_taksasi.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alertify.alert("Info", con.responseText);
        } else {
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

function deleteData(notrans, tgl, blok) {
  param = "proses=delete" + "&afdeling=" + notrans + "&tanggal=" + tgl;
  param += "&blok=" + blok;
  fileTarget = "kebun_slave_taksasi.php";
  if (confirm("Anda Yakin Ingin Menghapus Data Ini?")) {
    post_response_text(fileTarget, param, respon);
  }
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // Success Response
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getAfdeling(kbn, afd, blok) {
  if (kbn == 0 || afd == 0 || blok == 0) {
    dr =
      document.getElementById("kebundt").options[
        document.getElementById("kebundt").selectedIndex
      ].value;
    kbn = dr;
    param = "proses=getAfd" + "&kebun=" + kbn;
  } else {
    param = "proses=getAfd" + "&kebun=" + kbn + "&afdeling=" + afd;
    //param+='&mandor='+kary;
    param += "&blok=" + blok;
  }
  fileTarget = "kebun_slave_taksasi";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // Success Response
          // dr=con.responseText.split("###");
          // document.getElementById('afdeling').innerHTML=dr[0];
          // document.getElementById('blok').innerHTML=dr[1];
          //alertify.alert(con.responseText);
          document.getElementById("afdeling").innerHTML = con.responseText;
          // if(kary!=0){
          // document.getElementById('blok').value=kary;
          // }
          //document.getElementById('blok').value=blok;
          getblok(kbn, afd, blok);
          //getSPH();
          //alertify.alert('masuk');
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(fileTarget + ".php", param, respon);
}
function getSPH() {
  var disablekah = document.getElementById("hkdigunakan").disabled;
  blok =
    document.getElementById("blok").options[
      document.getElementById("blok").selectedIndex
    ].value;
  tanggal = document.getElementById("tanggal").value;
  param = "proses=getSPH" + "&blok=" + blok + "&tanggal=" + tanggal;
  fileTarget = "kebun_slave_taksasi";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // Success Response
          dr = con.responseText.split("###");
          jjgout = parseFloat(dr[1]) / parseFloat(dr[2]);
          if (isNaN(jjgout) == true) {
            jjgout = 0;
          }
          document.getElementById("sph").value = dr[0];
          document.getElementById("jjgoutput").value = jjgout.toFixed(0);
          document.getElementById("kgoutput").value = dr[1];
          document.getElementById("bjr").value = dr[2];
          document.getElementById("luas").value = dr[3];
          document.getElementById("pokok").value = dr[4];
          document.getElementById("tt").value = dr[5];
          getPokok();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  if (blok != "" && tanggal != "") {
    if (disablekah == true) {
      post_response_text(fileTarget + ".php", param, respon);
    }
  }
}
function getjjgkg(tipe, val) {
  bjr = document.getElementById("bjr").value;
  if (tipe == "jjg") {
    jjgoutput = parseFloat(val) / parseFloat(bjr);
    document.getElementById("jjgoutput").value = jjgoutput.toFixed(0);
  } else {
    kgoutput = parseFloat(val) * parseFloat(bjr);
    document.getElementById("kgoutput").value = kgoutput.toFixed(0);
  }
  getPokok();
}

function getPokok() {
  hasisa = document.getElementById("hasisa").value;
  haesok = document.getElementById("haesok").value;
  sph = document.getElementById("sph").value;
  jmlhpokok = sph * (+hasisa + +haesok);
  jmlhpokok = jmlhpokok.toFixed(0);
  document.getElementById("jmlhpokok").value = jmlhpokok;
  getMasak();
}
function getMasak() {
  persenbuahmatang = document.getElementById("persenbuahmatang").value;
  jmlhpokok = document.getElementById("jmlhpokok").value;
  bjr = document.getElementById("bjr").value;
  jjgmasak = (+persenbuahmatang / 100) * +jmlhpokok;
  jjgmasak = jjgmasak.toFixed(0);
  kgmasak = jjgmasak * parseFloat(bjr);
  document.getElementById("jjgmasak").value = jjgmasak;
  document.getElementById("kgmasak").value = kgmasak.toFixed(0);
  getHK();
}
function getHK() {
  jjgmasak = document.getElementById("jjgmasak").value;
  jjgoutput = document.getElementById("jjgoutput").value;
  hasisa = document.getElementById("hasisa").value;
  haesok = document.getElementById("haesok").value;
  luas = +hasisa + +haesok;
  hkdigunakan = Math.ceil(jjgmasak / jjgoutput);
  //        hkdigunakan=hkdigunakan.toFixed(0);
  if (isNaN(hkdigunakan) == true) {
    hkdigunakan = 0;
  }
  document.getElementById("hkdigunakan").value = hkdigunakan;
  if (luas / hkdigunakan <= 6) {
    bisapanen = hkdigunakan;
  } else {
    bisapanen = Math.ceil(luas / 6);
  }
  if (isFinite(bisapanen) == false) bisapanen = 0;
  document.getElementById("bisapanen").value = bisapanen;

  loaddatadetail(getValue("afdeling"));
}
function getblok(kbn, afdeling, blok) {
  //alertify.alert(kbn);alertify.alert(afd);alertify.alert(blok);
  if (blok == "" || blok == 0) {
    afdeling =
      document.getElementById("afdeling").options[
        document.getElementById("afdeling").selectedIndex
      ].value;
  }
  param = "afdeling=" + afdeling;
  //param+='&mandor='+kary;
  param += "&kbn=" + kbn;
  param += "&blok=" + blok;
  param += "&proses=getblok";
  tujuan = "kebun_slave_taksasi.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //alertify.alert(con.responseText);
          document.getElementById("blok").innerHTML = con.responseText;
          getSPH();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
