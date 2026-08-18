function viewfile(idfile, sumber) {
  //formupload();
  param = "method=viewfile&idfile=" + idfile;

  if (sumber == "KASBANK") {
    tujuan = "keu_kasdanbank_slave.php";
  }

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //document.getElementById('contviewupload').innerHTML = con.responseText;
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showandhideht(jenis) {
  t = document.getElementById("tempjumlahrowht").value;
  if (jenis == "1") {
    for (e = 0; e < 13; e++) {
      for (i = 0; i <= t; i++) {
        colom = document.getElementsByName("colht" + i + "[]");

        if (colom[e] != undefined) {
          colom[e].style.display = "";
        }
      }
    }

    // document.getElementById("tombolshowdt").innerHTML='Hide Column';
    // document.getElementById("tombolshowdt").setAttribute('onclick','showandhidedt(0);');
  } else {
    var isi = [];
    for (e = 0; e < 13; e++) {
      for (i = 0; i <= t; i++) {
        colom = document.getElementsByName("colht" + i + "[]");
        if (colom[e] != undefined) {
          colom[e].style.display = "none";
        }
      }
    }
    // document.getElementById("tombolshowdt").innerHTML='Show Column';
    // document.getElementById("tombolshowdt").setAttribute('onclick','showandhidedt(1);');
    // // dev_input.setAttribute("onclick","add_device_input(event, this);");
  }
}

function showandhidedt(jenis) {
  t = document.getElementById("tempjumlahrowdt").value;
  col = document.getElementsByName("coldt0[]");

  if (jenis == "1") {
    for (e = 0; e < col.length; e++) {
      for (i = 0; i <= t; i++) {
        colomH = document.getElementsByName("coldt0[]");
        colom = document.getElementsByName("coldt" + i + "[]");

        colom[e].style.display = "";
        colomH[e].style.display = "";

        console.log(e);
      }
    }

    document.getElementById("tombolshowdt").innerHTML = "Hide Column";
    document
      .getElementById("tombolshowdt")
      .setAttribute("onclick", "showandhidedt(0);");
  } else {
    var isi = [];
    for (e = 0; e < col.length; e++) {
      total = 0;
      for (i = 0; i <= t; i++) {
        if (i > 0) {
          colom = document.getElementsByName("coldt" + i + "[]");
          value = trim(colom[e].innerHTML);
          if (value == "-" || value == "-  -") {
            value = "";
          }
          if (value == "") {
            total = total + 1;
          }
        }
      }
      for (i = 0; i <= t; i++) {
        if (i > 0) {
          colomH = document.getElementsByName("coldt0[]");
          colom = document.getElementsByName("coldt" + i + "[]");
          if (total == t) {
            colom[e].style.display = "none";
            colomH[e].style.display = "none";
          }
        }
      }
    }
    document.getElementById("tombolshowdt").innerHTML = "Show Column";
    document
      .getElementById("tombolshowdt")
      .setAttribute("onclick", "showandhidedt(1);");
    // dev_input.setAttribute("onclick","add_device_input(event, this);");
  }
}

/***************************************************************************************************************/
function showandhide(jenis) {
  t = document.getElementById("tempjumlahrow").value;
  col = document.getElementsByName("col0[]");

  if (jenis == "1") {
    for (e = 0; e < col.length; e++) {
      for (i = 0; i <= t; i++) {
        colomH = document.getElementsByName("col0[]");
        colom = document.getElementsByName("col" + i + "[]");

        colom[e].style.display = "";
        colomH[e].style.display = "";
      }
    }

    document.getElementById("tombolshow").innerHTML = "Hide Column";
    document
      .getElementById("tombolshow")
      .setAttribute("onclick", "showandhide(0);");
  } else {
    var isi = [];
    for (e = 0; e < col.length; e++) {
      total = 0;
      for (i = 0; i <= t; i++) {
        if (i > 0 && e > 7) {
          colom = document.getElementsByName("col" + i + "[]");
          value = trim(colom[e].innerHTML);
          if (value == "-" || value == "-  -") {
            value = "";
          }
          if (value == "") {
            total = total + 1;
          }
        }
      }
      for (i = 0; i <= t; i++) {
        if (i > 0 && e > 7) {
          colomH = document.getElementsByName("col0[]");
          colom = document.getElementsByName("col" + i + "[]");
          if (total == t) {
            colom[e].style.display = "none";
            colomH[e].style.display = "none";
          }
        }
      }
    }
    document.getElementById("tombolshow").innerHTML = "Show Column";
    document
      .getElementById("tombolshow")
      .setAttribute("onclick", "showandhide(1);");
    // dev_input.setAttribute("onclick","add_device_input(event, this);");
  }
}

function proseskk(maxrow) {
  kodeorg = document.getElementById("kodeorg").value;
  noakun = document.getElementById("noakun").value;
  param = "method=proseskk";
  param += "&maxrow=" + maxrow + "&kodeorg=" + kodeorg + "&noakun=" + noakun;
  strparam = "";
  countcheck = 0;
  for (i = 1; i <= maxrow; i++) {
    checklist = document.getElementById("checkboxdt" + i);
    // alertify.alert("Informasi",checklist);
    if (checklist.checked == true) {
      countcheck++;
      strparam +=
        "&notransaksikk[" +
        i +
        "]=" +
        document.getElementById("notransaksikk" + i).innerHTML;
      strparam +=
        "&novoucherkk[" +
        i +
        "]=" +
        document.getElementById("novoucherkk" + i).innerHTML;
      strparam +=
        "&jumlahkk[" +
        i +
        "]=" +
        document.getElementById("jumlahkk" + i).innerHTML;
      strparam +=
        "&kodeorgkk[" +
        i +
        "]=" +
        document.getElementById("kodeorgkk" + i).innerHTML;
      strparam +=
        "&noakunkk[" +
        i +
        "]=" +
        document.getElementById("noakunkk" + i).innerHTML;
    }
  }

  param += strparam;

  if (countcheck < 1) {
    alertify.alert("Informasi", "Tidak ada transaksi detail yang dicheck");
    return;
  }
  // alertify.alert("Informasi",param);return;
  tujuan = "keu_kasdanbank_slave.php";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify.popup().destroy();
          arr = con.responseText.split("###");
          document.getElementById("jumlah").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function getkk(title, content) {
  ev = "event";
  // content='<div id=formpencarian></div>';
  width = "";
  height = "";
  kodeorg = document.getElementById("kodeorg").value;
  noakun2 = document.getElementById("noakun2").value;
  namapenerima = document.getElementById("namapenerima").value;
  autokb = document.getElementById("autokb").checked;
  if (autokb == false) {
    alert("Auto Kas/Bank harus dicentang");
    return;
  }
  if (noakun2 == "") {
    alert("Nomor akun Penerima tidak boleh kosong");
    return;
  }
  if (namapenerima == "") {
    alert("Unit penerima tidak boleh kosong");
    return;
  }
  // if(centang.checked!=true){

  param = "method=getkk";
  param += "&kodeorg=" + kodeorg;
  param += "&noakun2=" + noakun2;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpencarian').innerHTML=con.responseText;
          /*
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','85%'); 
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
					});
					*/

          alertify
            .popup()
            .set({
              resizable: true,
              maximizable: true,
              startMaximized: true,
              message: con.responseText,
            })
            .resizeTo("70%", "70%")
            .show();

          // findap();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findkk() {
  noakun2 = document.getElementById("noakun2").value;
  kodeorg = document.getElementById("kodeorg").value;
  namapenerima = document.getElementById("namapenerima").value;
  tanggalkk1 = document.getElementById("tanggalkk1").value;
  tanggalkk2 = document.getElementById("tanggalkk2").value;
  param = "method=findkk";
  param +=
    "&tanggalkk1=" +
    tanggalkk1 +
    "&tanggalkk2=" +
    tanggalkk2 +
    "&kodeorg=" +
    kodeorg +
    "&noakun2=" +
    noakun2 +
    "&namapenerima=" +
    namapenerima;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          leftFixedTable();
          document.getElementById("formpencariantampil").innerHTML =
            con.responseText;
          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/***************************************************************************************************************/

function getrekening() {
  notransaksi = document.getElementById("notransaksi").value;
  noakun = document.getElementById("noakun").value;
  kodeorg = document.getElementById("kodeorg").value;
  method = "getrekening";
  param = "";
  param +=
    "&noakun=" + noakun + "&kodeorg=" + kodeorg + "&notransaksi=" + notransaksi;
  param += "&method=" + method;
  // alert(param);
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("rekening").innerHTML = con.responseText;

          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getrekeningsch() {
  noakun = document.getElementById("noakunsch").value;
  kodeorg = document.getElementById("kodeorgsch").value;

  method = "getrekeningsch";
  param = "";
  param += "&noakun=" + noakun + "&kodeorg=" + kodeorg;
  param += "&method=" + method;
  // alert(param);
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("rekeningsch").disabled = false;
          document.getElementById("rekeningsch").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showhideinfo() {
  var row = document.getElementById("forminfo");
  if (row !== null) {
    if (row.style.display == "") {
      row.style.display = "none";
    } else {
      row.style.display = "";
    }
  }
}

function ajukan(notransaksi, page) {
  content = '<div id=formpost  style="height:100%;width:800px;"></div>';
  title = "Ajukan Persetujuan";
  height = "";
  width = "800";
  // showDialog4(title,content,width,height,'event');
  formajukan(notransaksi, page);
}

function formajukan(notransaksi, page) {
  method = "formajukan";
  param = "";
  param += "&notransaksi=" + notransaksi + "&page=" + page;
  param += "&method=" + method;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpost').innerHTML=con.responseText;
          // alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','80%');
          alertify
            .popup()
            .set({
              resizable: true,
              maximizable: true,
              startMaximized: true,
              message: con.responseText,
            })
            .resizeTo("70%", "70%")
            .show();
          // loaddata(0);
          showandhide(0);
          document.getElementById("tombolshow").style.display = "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

// #= diubah menjadi persetujuan
function saveajukan(notransaksi, kasbank, page, maxaproval) {
  param = "";
  method = "saveajukan";
  tanggalpengajuan = document.getElementById("tanggalpengajuan").value;
  // if(tanggalpengajuan=='') {
  // alert('Tanggal pengajuan tidak boleh kosong');
  // return;
  // }
  strper = "";
  for (i = 1; i <= maxaproval; i++) {
    strper +=
      "&persetujuan[" +
      i +
      "]=" +
      trim(document.getElementById("persetujuan" + i).value);
  }
  param +=
    "&notransaksi=" +
    notransaksi +
    "&tanggalpengajuan=" +
    tanggalpengajuan +
    "&kasbank=" +
    kasbank;
  param += "&maxaproval=" + maxaproval;
  param += "&method=" + method;
  param += strper;
  tujuan = "keu_kasdanbank_slave.php";
  // if(confirm('Ajukan transaksi : '+notransaksi+' ?')) {
  // post_response_text(tujuan, param, respon);
  // }

  alertify.confirm(
    "Informasi",
    "Ajukan transaksi : " + notransaksi + " ???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify.popup().destroy();
          loaddata(page);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pilihautokb() {
  param = "method=pilihautokb";
  tujuan = "keu_kasdanbank_slave.php";

  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("namapenerima").innerHTML = con.responseText;

          tipetransaksi = document.getElementById("tipetransaksi").value;
          if (tipetransaksi == "") {
            alertify.alert("Tipe transaksi tidak boleh kosong");
            document.getElementById("autokb").checked = false;
            return;
          }

          if (tipetransaksi == "M") {
            alertify.alert(
              "Hanya tipe transaksi keluar yang diperbolehkan menggunakan auto kas/bank",
            );
            document.getElementById("autokb").checked = false;
            return;
          }
          var centang = document.getElementById("autokb");
          if (centang.checked != true) {
            document.getElementById("norekpenerima").disabled = true;
            document.getElementById("namapenerima").disabled = true;
            document.getElementById("noakun2").disabled = true;
            document.getElementById("jumlah").disabled = true;
            setValue2("norekpenerima", "");
            setValue2("namapenerima", "");
            setValue2("noakun2", "");
            setValue2("noakun", "");
            setValue2("rekening", "");
            document.getElementById("jumlah").value = 0;
          } else {
            document.getElementById("norekpenerima").disabled = false;
            document.getElementById("namapenerima").disabled = false;
            document.getElementById("noakun2").disabled = false;
            document.getElementById("jumlah").disabled = false;
          }
          getbank();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelakunkb() {
  setValue2("noakun2", "");
  setValue2("norekpenerima", "");
}

function getbank() {
  notransaksi = document.getElementById("notransaksi").value;
  noakun = document.getElementById("noakun2").value;
  kodeorg = document.getElementById("namapenerima").value;
  method = "getbank";
  param = "";
  param +=
    "&noakun=" + noakun + "&kodeorg=" + kodeorg + "&notransaksi=" + notransaksi;
  param += "&method=" + method;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("norekpenerima").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveht(parameter) {
  method = "saveht";
  tujuan = "keu_kasdanbank_slave.php";
  var passP = parameter.split("###");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    param += "&" + passP[i] + "=" + getValue(passP[i]);
  }

  tipetransaksi = document.getElementById("tipetransaksi").value;

  document.getElementById("buttonar").style.display = "block";
  document.getElementById("buttonap").style.display = "block";
  document.getElementById("buttonapmasuk").style.display = "block";
  document.getElementById("buttonlain").style.display = "block";

  if (tipetransaksi == "M") {
    document.getElementById("buttonap").style.display = "none";
    // document.getElementById('buttonlain').style.display='none';
  }
  if (tipetransaksi == "K") {
    document.getElementById("buttonar").style.display = "none";
    document.getElementById("buttonapmasuk").style.display = "none";
  }
  // validate([
  // ["per","Periode tidak boleh kosong."],
  // ["unit","Lokasi tidak boleh kosong."]
  // ]);

  param += "&method=" + method;
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          // alertify.alert('Informasi',con.responseText);
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("notransaksi").value = con.responseText;
          document.getElementById("detail").style.display = "block";
          document.getElementById("notransaksi").disabled = true;

          // Disabled
          document.getElementById("tipetransaksi").disabled = true;
          document.getElementById("kodeorg").disabled = true;
          document.getElementById("noakun").disabled = true;
          document.getElementById("rekening").disabled = true;
          document.getElementById("autokb").disabled = true;
          document.getElementById("namapenerima").disabled = true;
          document.getElementById("noakun2").disabled = true;
          document.getElementById("norekpenerima").disabled = true;

          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function getkurs() {
  matauang = document.getElementById("matauang").value;
  tanggal = document.getElementById("tanggal").value;
  method = "getkurs";
  param = "";
  param += "&matauang=" + matauang + "&tanggal=" + tanggal;
  param += "&method=" + method;
  if (tanggal == "" || matauang == "") {
    alertify.alert("tanggal atau matauang masih kosong");
    setValue2("matauang", "IDR");
    setValue2("kurs", "1");
    document.getElementById("tanggal").value = "";
    return;
  }
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("kurs").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelht() {
  document.getElementById("detail").style.display = "none";
  document.getElementById("notransaksi").value = "";
  setValue2("tipetransaksi", "");
  setValue2("matauang", "IDR");
  document.getElementById("kurs").value = "1";
  setValue2("kodeorg", "");
  setValue2("noakun", "");
  document.getElementById("bayarkepada").value = "";
  document.getElementById("keterangan").value = "";
  document.getElementById("jumlah").value = "0";
  document.getElementById("autokb").checked = false;
  setValue2("namapenerima", "");
  setValue2("noakun2", "");
  setValue2("rekening", "");
  setValue2("norekpenerima", "");
  document.getElementById("tipetransaksi").disabled = false;
  document.getElementById("kodeorg").disabled = false;
  document.getElementById("noakun").disabled = false;
  document.getElementById("rekening").disabled = false;
  document.getElementById("autokb").disabled = false;
  document.getElementById("tanggal").disabled = false;
  // document.getElementById('kodeunit').disabled=false;
}

function editht(notransaksi) {
  param = "method=geteditht" + "&notransaksi=" + notransaksi;
  tujuan = "keu_kasdanbank_slave.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alertify.alert('Informasi',con.responseText);
          // document.getElementById('method').value = 'update';
          // alert(con.responseText.split);
          ar = con.responseText.split("###");

          document.getElementById("autokb").disabled = true;
          document.getElementById("tipetransaksi").disabled = true;
          document.getElementById("kodeorg").disabled = true;
          document.getElementById("noakun").disabled = true;
          document.getElementById("rekening").disabled = true;

          document.getElementById("notransaksi").value = ar[0];
          setValue2("tipetransaksi", ar[1]);
          setValue2("kodeorg", ar[2]);
          setValue2("noakun", ar[3]);
          document.getElementById("tanggal").value = ar[4];
          document.getElementById("bayarkepada").value = ar[5];
          document.getElementById("keterangan").value = ar[6];
          setValue2("matauang", ar[7]);
          document.getElementById("kurs").value = ar[8];
          document.getElementById("jumlah").value = ar[9];

          setValue2("namapenerima", ar[11]);
          setValue2("noakun2", ar[12]);
          setValue2("norekpenerima", ar[13]);
          document.getElementById("listdata").style.display = "none";
          document.getElementById("header").style.display = "block";
          document.getElementById("detail").style.display = "block";

          if (ar[10] == 1) {
            // Auto KB
            document.getElementById("autokb").checked = true;
            document.getElementById("norekpenerima").disabled = true;
            document.getElementById("namapenerima").disabled = true;
            document.getElementById("noakun2").disabled = true;
            getbank();
          } else {
            document.getElementById("autokb").checked = false;
            document.getElementById("norekpenerima").disabled = true;
            document.getElementById("namapenerima").disabled = true;
            document.getElementById("noakun2").disabled = true;
          }

          tipetransaksi = document.getElementById("tipetransaksi").value;
          document.getElementById("tanggal").disabled = true;

          document.getElementById("buttonar").style.display = "block";
          document.getElementById("buttonap").style.display = "block";
          document.getElementById("buttonapmasuk").style.display = "block";
          document.getElementById("buttonlain").style.display = "block";

          if (ar[1] == "M") {
            document.getElementById("buttonap").style.display = "none";
          }
          if (ar[1] == "K") {
            document.getElementById("buttonar").style.display = "none";
            document.getElementById("buttonapmasuk").style.display = "none";
          }

          setTimeout(() => {
            getakunpengirim();
            setTimeout(() => {
              getrekening();
            }, 500);
          }, 500);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function displaylist() {
  cancelht();
  document.getElementById("listdata").style.display = "block";
  document.getElementById("header").style.display = "none";
  document.getElementById("detail").style.display = "none";
  document.getElementById("dibuatsch").value = "";
  document.getElementById("keterangansch").value = "";
  document.getElementById("notransaksisch").value = "";
  document.getElementById("tanggalselesaisch").value = "";
  document.getElementById("tanggalmulaisch").value = "";
  document.getElementById("jumlahsch").value = "";
  document.getElementById("noinvoicesch").value = "";
  document.getElementById("bayarkesch").value = "";
  setValue2("noakunsch", "");
  setValue2("rekeningsch", "");
  setValue2("tipetransaksisch", "");
  setValue2("pembayaransch", "");
  setValue2("kodeorgsch", "");
  setValue2("appsch", "");
  setValue2("kodesuppliersch", "");

  document.getElementById("rekeningsch").disabled = true;

  loaddata(0);
}

function getpage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function loaddata(num) {
  if (document.getElementById("listdata") !== null) {
    document.getElementById("listdata").style.display = "block";
  }
  if (document.getElementById("header") !== null) {
    document.getElementById("header").style.display = "none";
  }
  if (document.getElementById("detail") !== null) {
    document.getElementById("detail").style.display = "none";
  }
  document.getElementById("rekeningsch").disabled = true;

  noakun = document.getElementById("noakunsch").value;
  if (noakun == "1110101" || noakun == "1111101") {
    document.getElementById("rekeningsch").disabled = false;
  }
  showhide = document.getElementById("showandhideht").value;
  rekening = document.getElementById("rekeningsch").value;
  dibuat = document.getElementById("dibuatsch").value;
  keterangan = document.getElementById("keterangansch").value;
  notransaksi = document.getElementById("notransaksisch").value;
  tanggalmulai = document.getElementById("tanggalmulaisch").value;
  tanggalselesai = document.getElementById("tanggalselesaisch").value;
  kodeorg = document.getElementById("kodeorgsch").value;
  tipetransaksi = document.getElementById("tipetransaksisch").value;
  appstatus = document.getElementById("appstatus").value;
  jumlah = document.getElementById("jumlahsch").value;
  noinvoice = document.getElementById("noinvoicesch").value;
  kodesupplier = document.getElementById("kodesuppliersch").value;
  bayarke = document.getElementById("bayarkesch").value;
  pembayaran = document.getElementById("pembayaransch").value;
  param = "method=loaddata&page=" + num;
  param +=
    "&notransaksi=" +
    notransaksi +
    "&tanggalmulai=" +
    tanggalmulai +
    "&tanggalselesai=" +
    tanggalselesai;
  param += "&kodeorg=" + kodeorg + "&tipetransaksi=" + tipetransaksi;
  param += "&dibuat=" + dibuat + "&keterangan=" + keterangan;
  param += "&noakun=" + noakun + "&rekening=" + rekening;
  param +=
    "&jumlah=" +
    jumlah +
    "&noinvoice=" +
    noinvoice +
    "&kodesupplier=" +
    kodesupplier;
  param +=
    "&appstatus=" +
    appstatus +
    "&bayarke=" +
    bayarke +
    "&pembayaran=" +
    pembayaran;
  // alert(param);
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("contain").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
          showandhideht(showhide);
          setTimeout(function () {
            leftFixedTable();
          }, 200);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddatapdf() {
  if (document.getElementById("listdata") !== null) {
    document.getElementById("listdata").style.display = "block";
  }
  if (document.getElementById("header") !== null) {
    document.getElementById("header").style.display = "none";
  }
  if (document.getElementById("detail") !== null) {
    document.getElementById("detail").style.display = "none";
  }
  document.getElementById("rekeningsch").disabled = true;

  noakun = document.getElementById("noakunsch").value;
  if (noakun == "1110101" || noakun == "1111101") {
    document.getElementById("rekeningsch").disabled = false;
  }
  rekening = document.getElementById("rekeningsch").value;
  dibuat = document.getElementById("dibuatsch").value;
  keterangan = document.getElementById("keterangansch").value;
  notransaksi = document.getElementById("notransaksisch").value;
  tanggalmulai = document.getElementById("tanggalmulaisch").value;
  tanggalselesai = document.getElementById("tanggalselesaisch").value;
  kodeorg = document.getElementById("kodeorgsch").value;
  tipetransaksi = document.getElementById("tipetransaksisch").value;
  appstatus = document.getElementById("appstatus").value;
  jumlah = document.getElementById("jumlahsch").value;
  noinvoice = document.getElementById("noinvoicesch").value;
  kodesupplier = document.getElementById("kodesuppliersch").value;
  bayarke = document.getElementById("bayarkesch").value;
  pembayaran = document.getElementById("pembayaransch").value;
  param = "method=loaddatapdf";
  param +=
    "&notransaksi=" +
    notransaksi +
    "&tanggalmulai=" +
    tanggalmulai +
    "&tanggalselesai=" +
    tanggalselesai;
  param += "&kodeorg=" + kodeorg + "&tipetransaksi=" + tipetransaksi;
  param += "&dibuat=" + dibuat + "&keterangan=" + keterangan;
  param += "&noakun=" + noakun + "&rekening=" + rekening;
  param +=
    "&jumlah=" +
    jumlah +
    "&noinvoice=" +
    noinvoice +
    "&kodesupplier=" +
    kodesupplier;
  param +=
    "&appstatus=" +
    appstatus +
    "&bayarke=" +
    bayarke +
    "&pembayaran=" +
    pembayaran;
  // alert(param);
  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_kasdanbank_slave.php?" +
        param +
        "'></iframe>",
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("90%", "80%");
}

function loaddataexcel() {
  if (document.getElementById("listdata") !== null) {
    document.getElementById("listdata").style.display = "block";
  }
  if (document.getElementById("header") !== null) {
    document.getElementById("header").style.display = "none";
  }
  if (document.getElementById("detail") !== null) {
    document.getElementById("detail").style.display = "none";
  }
  document.getElementById("rekeningsch").disabled = true;

  noakun = document.getElementById("noakunsch").value;
  if (noakun == "1110101" || noakun == "1111101") {
    document.getElementById("rekeningsch").disabled = false;
  }
  rekening = document.getElementById("rekeningsch").value;
  dibuat = document.getElementById("dibuatsch").value;
  keterangan = document.getElementById("keterangansch").value;
  notransaksi = document.getElementById("notransaksisch").value;
  tanggalmulai = document.getElementById("tanggalmulaisch").value;
  tanggalselesai = document.getElementById("tanggalselesaisch").value;
  kodeorg = document.getElementById("kodeorgsch").value;
  tipetransaksi = document.getElementById("tipetransaksisch").value;
  appstatus = document.getElementById("appstatus").value;
  jumlah = document.getElementById("jumlahsch").value;
  noinvoice = document.getElementById("noinvoicesch").value;
  kodesupplier = document.getElementById("kodesuppliersch").value;
  bayarke = document.getElementById("bayarkesch").value;
  param = "method=loaddataexcel";
  param +=
    "&notransaksi=" +
    notransaksi +
    "&tanggalmulai=" +
    tanggalmulai +
    "&tanggalselesai=" +
    tanggalselesai;
  param += "&kodeorg=" + kodeorg + "&tipetransaksi=" + tipetransaksi;
  param += "&dibuat=" + dibuat + "&keterangan=" + keterangan;
  param += "&noakun=" + noakun + "&rekening=" + rekening;
  param +=
    "&jumlah=" +
    jumlah +
    "&noinvoice=" +
    noinvoice +
    "&kodesupplier=" +
    kodesupplier;
  param += "&appstatus=" + appstatus + "&bayarke=" + bayarke;
  // alert(param);
  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_kasdanbank_slave.php?" +
        param +
        "'></iframe>",
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("90%", "80%");
}

function posting(notransaksi) {
  param = "method=posting" + "&notransaksi=" + notransaksi;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          closeDialog2();
          loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteht(notransaksi) {
  param = "method=deleteht";
  param += "&notransaksi=" + notransaksi;
  tujuan = "keu_kasdanbank_slave.php";
  // if(confirm('Hapus transaksi : '+notransaksi+' ?')) {
  // post_response_text(tujuan, param, respon);
  // }

  alertify.confirm(
    "Informasi",
    "Hapus transaksi : " + notransaksi + " ???",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    },
  );

  // post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*
#===================================================================================================================
#================= detail FILE =====================================================================================
#===================================================================================================================
*/

// fungsi untuk progress bar
function progressHandler(event) {
  document.getElementById("progressBar").style.display = "block";
  document.getElementById("loaded_n_total").innerHTML =
    "Uploaded " +
    numberFormat(Math.round(event.loaded / 1024)) +
    " KB of " +
    numberFormat(Math.round(event.total / 1024)) +
    " KB";
  var percent = (event.loaded / event.total) * 100;
  document.getElementById("progressBar").value = Math.round(percent);
  document.getElementById("statusbar").innerHTML =
    Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
  document.getElementById("progressBar").style.display = "none";
  document.getElementById("statusbar").innerHTML = event.target.responseText;
  document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Failed";
}
function abortHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Aborted";
}

function submitfile() {
  var notransaksi = document.getElementById("notransaksi").value;
  var kriteriaefil = document.getElementById("kriteriaefil").value;
  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  formdata.append("notransaksi", trim(notransaksi));
  formdata.append("kriteriaefil", kriteriaefil);
  if (getValue("upload") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  var con = createXMLHttpRequest();
  //tambahan progress bar
  con.upload.addEventListener("progress", progressHandler, false);
  con.addEventListener("load", completeHandler, false);
  con.addEventListener("error", errorHandler, false);
  con.addEventListener("abort", abortHandler, false);
  //tambahan progress bar -end-
  con.open("POST", "keu_kasdanbank_slave.php?method=submitfile", true);
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
          alert("Uploaded Success.");
          document.getElementById("upload").value = "";
          loadfiles();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfiles() {
  // alert('masuk');
  notransaksi = document.getElementById("notransaksi").value;
  param = "method=loadfiles&notransaksi=" + trim(notransaksi);
  // alert(param);
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // if (document.getElementById('listfiles') !== null) {
          // document.getElementById('listfiles').innerHTML = con.responseText;
          // }
          document.getElementById("listfiles").innerHTML = con.responseText;
          getoptdetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(notransaksi, namafile) {
  param =
    "method=deletefile&notransaksi=" + notransaksi + "&namafile=" + namafile;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          loadfiles();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*
#===================================================================================================================
#================= detail ==========================================================================================
#===================================================================================================================
*/

function canceldt() {
  document.getElementById("keterangan1").value = "";
  document.getElementById("nodok").value = "";
  setValue2("hutangunit1", 0);
  setValue2("pemilikhutang1", "");
  setValue2("noaruskas", "");
  setValue2("noakundt", "");
  document.getElementById("keterangan2").value = "";
  setValue2("kodekegiatan", "");
  setValue2("kodeasset", "");
  setValue2("nik", "");
  setValue2("kodecustomer", "");
  setValue2("kodesupplier", "");
  setValue2("kodevhc", "");
  setValue2("orgalokasi", "");
  document.getElementById("jumlahdt").value = "0";

  document.getElementById("methoddt").value = "insert";
  document.getElementById("nourut").value = "";
  loaddatadt();
}

function editdt(notransaksi, nourut) {
  param =
    "method=geteditdt" + "&notransaksi=" + notransaksi + "&nourut=" + nourut;
  tujuan = "keu_kasdanbank_slave.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alertify.alert('Informasi',con.responseText);
          // document.getElementById('method').value = 'update';
          // alert(con.responseText.split);
          ar = con.responseText.split("###");
          document.getElementById("keterangan1").value = ar[0];
          document.getElementById("nodok").value = ar[1];
          setValue2("hutangunit1", ar[2]);
          setValue2("pemilikhutang1", ar[3]);
          setValue2("noaruskas", ar[4]);
          setValue2("noakundt", ar[5]);
          document.getElementById("jumlahdt").value = ar[6];
          document.getElementById("keterangan2").value = ar[7];
          setValue2("kodekegiatan", ar[8]);
          setValue2("kodeasset", ar[9]);
          setValue2("nik", ar[10]);
          setValue2("kodecustomer", ar[11]);
          setValue2("kodesupplier", ar[12]);
          setValue2("kodevhc", ar[13]);
          setValue2("orgalokasi", ar[14]);
          document.getElementById("nourut").value = ar[15];
          // alert(ar[15]);
          setValue2("departemen", ar[16]);
          setValue2("keterangan3", ar[17]);
          document.getElementById("methoddt").value = "update";
          getpemilikhutang(ar[3]);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function savedt(parameter) {
  method = "savedt";
  tujuan = "keu_kasdanbank_slave.php";
  var passP = parameter.split("###");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    param += "&" + passP[i] + "=" + getValue(passP[i]);
  }
  param += "&method=" + method;
  // alert(param);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('notransaksi').value=con.responseText;
          // document.getElementById('detail').style.display='block';
          // document.getElementById('notransaksi').disabled=true;
          // loaddatadt();
          canceldt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function savedtpembulatan() {
  notransaksi = document.getElementById("notransaksi").value;
  jumlahdt = document.getElementById("jumlahdt").value;
  method = "savedtpembulatan";
  param = "";
  param += "&notransaksi=" + notransaksi + "&jumlahdt=" + jumlahdt;
  param += "&method=" + method;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          canceldt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getpemilikhutang(pemilikhutang1) {
  // alert(pemilikhutang1);
  //saat ini untuk VHC, alokasi/blok, ADK, karyawan
  hutangunit1 = document.getElementById("hutangunit1").value;
  kodeorg = document.getElementById("kodeorg").value;
  notransaksi = document.getElementById("notransaksi").value;
  method = "getpemilikhutang";
  param = "";
  param +=
    "&hutangunit1=" +
    hutangunit1 +
    "&kodeorg=" +
    kodeorg +
    "&pemilikhutang1=" +
    pemilikhutang1;
  param += "&method=" + method;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("pemilikhutang1").innerHTML =
            con.responseText;
          getoptdetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getoptdetail() {
  //saat ini untuk VHC, alokasi/blok, ADK, karyawan
  hutangunit1 = document.getElementById("hutangunit1").value;
  pemilikhutang1 = document.getElementById("pemilikhutang1").value;
  kodeorg = document.getElementById("kodeorg").value;

  notransaksi = document.getElementById("notransaksi").value;
  nourut = document.getElementById("nourut").value;
  method = "getoptdetail";
  param = "";
  param +=
    "&pemilikhutang1=" +
    pemilikhutang1 +
    "&hutangunit1=" +
    hutangunit1 +
    "&kodeorg=" +
    kodeorg;
  param += "&method=" + method;
  param += "&notransaksi=" + notransaksi + "&nourut=" + nourut;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          ar = con.responseText.split("###");
          document.getElementById("kodeasset").innerHTML = ar[0];
          document.getElementById("nik").innerHTML = ar[1];
          document.getElementById("kodevhc").innerHTML = ar[2];
          document.getElementById("orgalokasi").innerHTML = ar[3];
          document.getElementById("noakundt").innerHTML = ar[4];
          // document.getElementById('noakundt').innerHTML=ar[4];
          // document.getElementById('kodekegiatan').innerHTML=ar[4];
          // loadfiles();
          // alert(ar[4]);
          // alert(ar[5]);
          // alert(ar[6]);
          getaruskaskegiatan(ar[5], ar[6], ar[7]);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getaruskaskegiatan(noaruskas, kodekegiatan, departemen) {
  tipetransaksi = document.getElementById("tipetransaksi").value;
  noakundt = document.getElementById("noakundt").value;
  method = "getaruskaskegiatan";
  param = "";
  param +=
    "&noakundt=" +
    noakundt +
    "&method=" +
    method +
    "&noaruskas=" +
    noaruskas +
    "&kodekegiatan=" +
    kodekegiatan +
    "&departemen=" +
    departemen +
    "&tipetransaksi=" +
    tipetransaksi;
  // alert(param);
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          ar = con.responseText.split("###");
          document.getElementById("noaruskas").innerHTML = ar[0];
          document.getElementById("kodekegiatan").innerHTML = ar[1];
          document.getElementById("departemen").innerHTML = ar[2];
          await triggerBebanBiaya(noakundt);
          //loadfiles();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddatadt() {
  notransaksi = document.getElementById("notransaksi").value;
  tipetransaksi = document.getElementById("tipetransaksi").value;
  param = "method=loaddatadt";
  param += "&notransaksi=" + notransaksi;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          ar = con.responseText.split("###");
          document.getElementById("listdatadt").innerHTML = ar[0];
          document.getElementById("jumlah").value = ar[1];
          if (tipetransaksi == "M") {
            document.getElementById("buttonap").disabled = true;
            document.getElementById("buttonar").disabled = false;
            document.getElementById("buttonapmasuk").disabled = false;
          } else {
            document.getElementById("buttonar").disabled = true;
            document.getElementById("buttonapmasuk").disabled = true;
            document.getElementById("buttonap").disabled = false;
          }
          // loadfiles();
          // showandhidedt("1");
          // showandhidedt("0");
          // setTimeout(function () {
          // }, 300);
          getoptdetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function newdata() {
  document.getElementById("header").style.display = "block";
  document.getElementById("listdata").style.display = "none";
  document.getElementById("detail").style.display = "none";
  cancelht();
  // document.getElementById('detailhead').style.display='none';
}

function deletedt(notransaksi, nourut) {
  param = "method=deletedt";
  param += "&notransaksi=" + notransaksi + "&nourut=" + nourut;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getlain(title, content) {
  ev = "event";
  content = "<div id=formpencarian></div>";
  // content='';
  width = "";
  height = "";
  // showDialog6(title,content,width,height,ev);
  kodeorg = document.getElementById("kodeorg").value;
  param = "method=getlain";
  param += "&kodeorg=" + kodeorg;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpencarian').innerHTML=con.responseText;
          alertify.popup().destroy();
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("90%", "80%");
          findlain();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findlain() {
  kodeorglain = document.getElementById("kodeorglain").value;
  nodoklain = document.getElementById("nodoklain").value;
  sumberlain = document.getElementById("sumberlain").value;
  kodeorg = document.getElementById("kodeorg").value;
  tipetransaksi = document.getElementById("tipetransaksi").value;

  param = "method=findlain";
  param += "&kodeorg=" + kodeorg;
  param += "&tipetransaksi=" + tipetransaksi;
  param += "&kodeorglain=" + kodeorglain + "&nodoklain=" + nodoklain;
  param += "&sumberlain=" + sumberlain;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("formpencariantampil").innerHTML =
            con.responseText;
          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function savelain(
  pemilikhutang1,
  noaruskaslain,
  noakundt,
  keterangan2,
  nodok,
  kodesupplier,
  nik,
  jumlahdt,
  nourut,
  uangmukalain,
  lainnya,
) {
  sumberlain = document.getElementById("sumberlain").value;
  notransaksi = document.getElementById("notransaksi").value;
  kodeorg = document.getElementById("kodeorg").value;
  // jumlahdt=remove_comma_var(jumlahdt);
  // alert(sumberlain);
  param = "method=savelain";
  param += "&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg;
  param +=
    "&noakundt=" + noakundt + "&keterangan2=" + keterangan2 + "&nodok=" + nodok;
  param +=
    "&kodesupplier=" +
    kodesupplier +
    "&nik=" +
    nik +
    "&pemilikhutang1=" +
    pemilikhutang1 +
    "&jumlahdt=" +
    jumlahdt +
    "&sumberlain=" +
    sumberlain +
    "&lainnya=" +
    lainnya;
  if (
    sumberlain == "umpjdinas" ||
    sumberlain == "realpjdinas" ||
    sumberlain == "claimpjdinas" ||
    sumberlain == "batalpjd"
  ) {
    noaruskaslain = document.getElementById("noaruskaslain" + nourut).value;
    param += "&noaruskas=" + noaruskaslain;
  } else {
    param += "&noaruskas=" + noaruskaslain;
  }
  tujuan = "keu_kasdanbank_slave.php";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          findlain();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

/************** A **************/
/************** P **************/

function getap(title, content) {
  ev = "event";
  content = "<div id=formpencarian></div>";
  // content='';
  width = "";
  height = "";
  // showDialog6(title,content,width,height,ev);
  kodeorg = document.getElementById("kodeorg").value;
  param = "method=getap";
  param += "&kodeorg=" + kodeorg;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpencarian').innerHTML=con.responseText;
          alertify.popup().destroy();
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("90%", "85%");
          $(document).ready(function () {
            $(".select2").select2({
              dropdownAutoWidth: false,
            });
          });
          // findap();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getapmasuk(title, content) {
  ev = "event";
  content = "<div id=formpencarian></div>";
  // content='';
  width = "";
  height = "";
  // showDialog6(title,content,width,height,ev);
  kodeorg = document.getElementById("kodeorg").value;
  param = "method=getapmasuk";
  param += "&kodeorg=" + kodeorg;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpencarian').innerHTML=con.responseText;
          alertify.popup().destroy();
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("90%", "85%");
          $(document).ready(function () {
            $(".select2").select2({
              dropdownAutoWidth: false,
            });
          });
          // findap();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findap() {
  noinvoiceap = document.getElementById("noinvoiceap").value;
  kodeorg = document.getElementById("kodeorg").value;
  kodeorgap = document.getElementById("kodeorgap").value;
  nodokap = document.getElementById("nodokap").value;
  kodesupplierap = document.getElementById("kodesupplierap").value;
  kodeorght = document.getElementById("kodeorg").value;

  nilaiinvoiceap = document.getElementById("nilaiinvoiceap").value;
  tipeinvoiceap = document.getElementById("tipeinvoiceap").value;
  noinvoicesupplierap = document.getElementById("noinvoicesupplierap").value;

  param = "method=findap";
  param +=
    "&noinvoiceap=" +
    noinvoiceap +
    "&kodeorgap=" +
    kodeorgap +
    "&kodeorg=" +
    kodeorg;
  param += "&nodokap=" + nodokap + "&kodesupplierap=" + kodesupplierap;
  param +=
    "&nilaiinvoiceap=" +
    nilaiinvoiceap +
    "&tipeinvoiceap=" +
    tipeinvoiceap +
    "&noinvoicesupplierap=" +
    noinvoicesupplierap;
  param += "&kodeorght=" + kodeorght;
  // param += '&notransaksi=' + notransaksi+'&noakun=' + noakun+'&keterangan1=' + keterangan1+'&keterangan2=' + keterangan2;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          leftFixedTable();
          document.getElementById("formpencariantampil").innerHTML =
            con.responseText;
          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveap(
  pemilikhutang1,
  noakundt,
  keterangan1,
  nodok,
  kodesupplier,
  noht,
  maxrow,
  noaruskasuangmuka,
  noakunuangmuka,
  nilaiuangmuka,
  sisainv,
  tipearuskasht,
) {
  strparam = "";
  notransaksi = document.getElementById("notransaksi").value;
  kodeorg = document.getElementById("kodeorg").value;
  param = "method=saveap";
  param += "&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg;
  param +=
    "&noakundt=" + noakundt + "&keterangan1=" + keterangan1 + "&nodok=" + nodok;
  param +=
    "&kodesupplier=" + kodesupplier + "&pemilikhutang1=" + pemilikhutang1;
  param +=
    "&maxrow=" +
    maxrow +
    "&nilaiuangmuka=" +
    nilaiuangmuka +
    "&noakunuangmuka=" +
    noakunuangmuka +
    "&noaruskasuangmuka=" +
    noaruskasuangmuka +
    "&sisainv=" +
    sisainv +
    "&tipearuskasht=" +
    tipearuskasht;
  for (i = 1; i <= maxrow; i++) {
    strparam +=
      "&keterangan3[" +
      i +
      "]=" +
      trim(document.getElementById("keterangan3" + noht + i).innerHTML);
    strparam +=
      "&noaruskas[" +
      i +
      "]=" +
      trim(document.getElementById("noaruskas" + noht + i).innerHTML);
    strparam +=
      "&jumlahdt[" +
      i +
      "]=" +
      trim(document.getElementById("sisadetail" + noht + i).innerHTML);
    strparam +=
      "&noakuninvoice[" +
      i +
      "]=" +
      trim(document.getElementById("noakuninvoice" + noht + i).innerHTML);
    strparam +=
      "&tipearuskasdt[" +
      i +
      "]=" +
      trim(document.getElementById("tipearuskasdt" + noht + i).innerHTML);
  }
  param += strparam;
  // alert(param);return;
  tujuan = "keu_kasdanbank_slave.php";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          if (con.responseText != "") {
            document.getElementById("bayarkepada").value = con.responseText;
          }
          findap();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

/*
function saveaplama(pemilikhutang1,noakundt,keterangan1,nodok,kodesupplier,noht,tnodt,uangmuka){
	for (i = 1; i <= tnodt; i++) {
		notransaksi= document.getElementById('notransaksi').value;
		kodeorg= document.getElementById('kodeorg').value;
		param = 'method=saveap';
		param += '&notransaksi=' + notransaksi+'&kodeorg=' + kodeorg;
		param += '&noakundt=' + noakundt+'&keterangan1=' + keterangan1+'&nodok=' + nodok;
		param += '&kodesupplier=' + kodesupplier+'&pemilikhutang1=' + pemilikhutang1+'&uangmuka=' + uangmuka;
		
		noaruskas= document.getElementById('noaruskas'+noht+i).innerHTML;
		jumlahdt= document.getElementById('sisadetail'+noht+i).innerHTML;jumlahdt=remove_comma_var(jumlahdt);
		param += '&noaruskas=' + noaruskas+'&jumlahdt=' + jumlahdt;
		
		// alert(param);
		
		tujuan = 'keu_kasdanbank_slave.php';
		function respon() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert('Informasi',con.responseText);
					} else {
						// alert('save');
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respon);
	}
	findap();
}
*/

function showdetail(noht, tnodt) {
  for (i = 1; i <= tnodt; i++) {
    var row = document.getElementById("detaildataap" + noht + i);
    if (row !== null) {
      if (row.style.display == "") {
        row.style.display = "none";
      } else {
        row.style.display = "";
      }
    }
  }
}

/************** A **************/
/************** R **************/

function getar(title, content) {
  ev = "event";
  content = "<div id=formpencarian></div>";
  // content='';
  width = "";
  height = "";
  // showDialog6(title,content,width,height,ev);
  kodeorg = document.getElementById("kodeorg").value;
  param = "method=getar";
  param += "&kodeorg=" + kodeorg;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpencarian').innerHTML=con.responseText;
          alertify.popup().destroy();
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("90%", "80%");
          // findar();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findar() {
  noinvoicear = document.getElementById("noinvoicear").value;
  kodecustomerar = document.getElementById("kodecustomerar").value;
  kodeorg = document.getElementById("kodeorg").value;
  nokontrakar = document.getElementById("nokontrakar").value;
  param = "method=findar";
  param +=
    "&noinvoicear=" +
    noinvoicear +
    "&kodeorg=" +
    kodeorg +
    "&kodecustomerar=" +
    kodecustomerar +
    "&nokontrakar=" +
    nokontrakar;
  // param += '&notransaksi=' + notransaksi+'&noakun=' + noakun+'&keterangan1=' + keterangan1+'&keterangan2=' + keterangan2;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          leftFixedTable();
          document.getElementById("formpencariantampil").innerHTML =
            con.responseText;
          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function savear(
  pemilikhutang1,
  noaruskas,
  noaruskas2,
  noaruskas3,
  noakundt,
  noakundt2,
  noakundt3,
  keterangan1,
  nodok,
  kodecustomer,
  jumlahdt,
  jumlahdt2,
  jumlahdt3,
) {
  notransaksi = document.getElementById("notransaksi").value;
  kodeorg = document.getElementById("kodeorg").value;
  param = "method=savear";
  param += "&notransaksi=" + notransaksi + "&kodeorg=" + kodeorg;
  param +=
    "&noakundt=" + noakundt + "&keterangan1=" + keterangan1 + "&nodok=" + nodok;
  param +=
    "&kodecustomer=" +
    kodecustomer +
    "&jumlahdt=" +
    jumlahdt +
    "&pemilikhutang1=" +
    pemilikhutang1;
  param +=
    "&noakundt2=" +
    noakundt2 +
    "&noakundt3=" +
    noakundt3 +
    "&jumlahdt2=" +
    jumlahdt2 +
    "&jumlahdt3=" +
    jumlahdt3;
  param +=
    "&noaruskas=" +
    noaruskas +
    "&noaruskas2=" +
    noaruskas2 +
    "&noaruskas3=" +
    noaruskas3;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          findar();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*
#========================================
#================= printout =============
#========================================
*/

function html(notransaksi, noakun, tipetransaksi, kodeorg) {
  param =
    "proses=html&notransaksi=" +
    notransaksi +
    "&kodeorg=" +
    kodeorg +
    "&tipetransaksi=" +
    tipetransaksi +
    "&noakun=" +
    noakun;
  title = "Data Detail";
  showDialog1(
    title,
    "<iframe frameborder=0 style='width:795px;height:400px'" +
      " src='keu_kasdanbank_print.php?" +
      param +
      "'></iframe>",
    "800",
    "400",
    "event",
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}

function pdf(notransaksi, noakun, tipetransaksi, kodeorg, paramproses = "") {
  // param =
  //   "proses=pdfnew&notransaksi=" +
  //   notransaksi +
  //   "&kodeorg=" +
  //   kodeorg +
  //   "&tipetransaksi=" +
  //   tipetransaksi +
  //   "&noakun=" +
  //   noakun;

  if (paramproses == "") {
    param = "proses=pdfpalma";
  } else {
    param = "proses=" + paramproses + "";
  }

  param +=
    "&notransaksi=" +
    notransaksi +
    "&kodeorg=" +
    kodeorg +
    "&tipetransaksi=" +
    tipetransaksi +
    "&noakun=" +
    noakun;

  // showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
  // " src='keu_kasdanbank_print.php?"+param+"'></iframe>",'800','400','event');
  // var dialog = document.getElementById('dynamic1');
  // dialog.style.top = '50px';
  // dialog.style.left = '15%';

  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_kasdanbank_print.php?" +
        param +
        "'></iframe>",
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("90%", "80%");
}

function pdfvoucher(novoucher) {
  param = "proses=pdfvoucher&novoucher=" + novoucher;
  // showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
  // " src='keu_kasdanbank_print.php?"+param+"'></iframe>",'800','400','event');
  // var dialog = document.getElementById('dynamic1');
  // dialog.style.top = '50px';
  // dialog.style.left = '15%';

  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_kasdanbank_print.php?" +
        param +
        "'></iframe>",
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function formposting(notransaksi) {
  content =
    '<div id=formposting style="height:100%;width:100%;background-color:#FFFFFF;border:#777777 solid 2px;"></div>';
  title = "Form Posting";
  height = "";
  width = "";
  ev = "event";
  tujuan = "keu_kasdanbank_slave.php";
  param = "method=formposting" + "&notransaksi=" + notransaksi;
  showDialog2(title, content, width, height, ev);
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("formposting").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function fileSelected(jenis) {
  // kodeorg = document.getElementById('kodeorg').value;

  var file = document.getElementById("uploaddt").files[0];
  var kodeorg = document.getElementById("kodeorg").value;
  var tanggal = document.getElementById("tanggal").value;
  var notransaksi = document.getElementById("notransaksi").value;
  var tipetransaksi = document.getElementById("tipetransaksi").value;
  var noakun = document.getElementById("noakun").value;
  var rekening = document.getElementById("rekening").value;
  var matauang = document.getElementById("matauang").value;
  var kurs = document.getElementById("kurs").value;

  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("jenis", jenis);
  // formdata.append("kodeorg", kodeorg);
  // Header
  formdata.append("kodeorg", kodeorg);
  formdata.append("tanggal", tanggal);
  formdata.append("notransaksi", notransaksi);
  formdata.append("tipetransaksi", tipetransaksi);
  formdata.append("noakun", noakun);
  formdata.append("rekening", rekening);
  formdata.append("matauang", matauang);
  formdata.append("kurs", kurs);

  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "keu_kasdanbank_slave.php?method=fileSelected", true);
  con.onreadystatechange = eval(respon);
  console.log(formdata);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (jenis == "simpan") {
            document.getElementById("contdetail").innerHTML = "";
            alertify.alert("Done");
          } else {
            document.getElementById("contdetail").style.display = "block";
            document.getElementById("contdetail").innerHTML = con.responseText;
            leftFixedTable();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpanupload(maxRow) {
  if (maxRow == "" || maxRow == 0) {
    alertify.alert("Info", "Data tidak ditemukan, proses dibatalkan !");
    return;
  }
  alertify.confirm(
    "Warning",
    "Proses ini akan me-replace data yg sudah ada, anda yakin ?",
    function () {
      savedetail(1, maxRow);
    },
    function () {
      return;
    },
  );
}
function savedetail(currRow, maxRow) {
  // Header
  kodeorg = document.getElementById("kodeorg").value;
  tanggal = document.getElementById("tanggal").value;
  notransaksi = document.getElementById("notransaksi").value;
  tipetransaksi = document.getElementById("tipetransaksi").value;
  noakunbank = document.getElementById("noakun").value;
  rekeningbank = document.getElementById("rekening").value;
  matauang = document.getElementById("matauang").value;
  kurs = document.getElementById("kurs").value;

  // Detail
  nourut = document.getElementById("nourut_" + currRow).innerHTML;
  jumlahdt = document.getElementById("jumlah_" + currRow).innerHTML;
  noakun = document.getElementById("noakun_" + currRow).innerHTML;
  nodok = document.getElementById("nodok_" + currRow).innerHTML;
  keterangan1 = document.getElementById("nodok_" + currRow).innerHTML;
  keterangan2 = document.getElementById("ket2_" + currRow).innerHTML;
  keterangan3 = document.getElementById("jenisrincian_" + currRow).innerHTML;
  jenisrincian = document.getElementById("jenisrincian_" + currRow).innerHTML;
  kodecustomer = document.getElementById("kodecustomer_" + currRow).innerHTML;
  kodesupplier = document.getElementById("kodesupplier_" + currRow).innerHTML;
  kodekegiatan = document.getElementById("kodekegiatan_" + currRow).innerHTML;
  kodeasset = document.getElementById("kodeasset_" + currRow).innerHTML;
  nik = document.getElementById("nik_" + currRow).innerHTML;
  kodevhc = document.getElementById("kodevhc_" + currRow).innerHTML;
  orgalokasi = document.getElementById("kodeblok_" + currRow).innerHTML;
  nodok = document.getElementById("nodok_" + currRow).innerHTML;
  hutangunit1 = document.getElementById("hutangunit1_" + currRow).innerHTML;
  pemilikhutang1 = document.getElementById(
    "pemilikhutang1_" + currRow,
  ).innerHTML;
  departemen = document.getElementById("departemen_" + currRow).innerHTML;
  noaruskas = document.getElementById("noaruskas_" + currRow).innerHTML;

  // Method
  method = document.getElementById("method_" + currRow).value;
  methoddt = document.getElementById("methoddt_" + currRow).value;

  param = "";
  param += "method=" + method;
  param += "&methoddt=" + methoddt;

  param += "&notransaksi=" + notransaksi;
  param += "&noakundt=" + noakun;
  param += "&tipetransaksi=" + tipetransaksi;
  param += "&tanggal=" + tanggal;
  param += "&jumlahdt=" + jumlahdt;
  param += "&noakun=" + noakunbank;
  param += "&keterangan1=" + keterangan1;
  param += "&keterangan2=" + keterangan2;
  param += "&matauang=" + matauang;
  param += "&kurs=" + kurs;
  param += "&noaruskas=" + noaruskas;
  param += "&kodekegiatan=" + kodekegiatan;
  param += "&kodeasset=" + kodeasset;
  param += "&nik=" + nik;
  param += "&kodecustomer=" + kodecustomer;
  param += "&kodesupplier=" + kodesupplier;
  param += "&kodevhc=" + kodevhc;
  param += "&orgalokasi=" + orgalokasi;
  param += "&nodok=" + nodok;
  param += "&hutangunit1=" + hutangunit1;
  param += "&pemilikhutang1=" + pemilikhutang1;
  param += "&nourut=" + nourut;
  param += "&keterangan3=" + jenisrincian;
  param += "&departemen=" + departemen;

  console.log(param);

  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  if (currRow != undefined) {
    document.getElementById("baris_" + currRow).style.backgroundColor = "cyan";
    document.getElementById("baris_" + currRow).style.display = "none";
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Info", con.responseText);
          if (currRow != undefined) {
            document.getElementById(
              "validasi_" + currRow,
            ).style.backgroundColor = "red";
          }
        } else {
          if (currRow != undefined) {
            document.getElementById(
              "validasi_" + currRow,
            ).style.backgroundColor = "";
          }
          currRow += 1;
          if (currRow > maxRow || maxRow == undefined) {
            alertify.alert("Done");
            location.reload();
          } else {
            savedetail(currRow, maxRow);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function norefautokb(novoucher, notransaksi) {
  param = "method=getdataautokb";
  param += "&novoucher=" + novoucher + "&notransaksi=" + notransaksi;

  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify
            .popup2("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getakunpengirim() {
  notransaksi = document.getElementById("notransaksi").value;
  noakun = document.getElementById("noakun").value;
  kodeorg = document.getElementById("kodeorg").value;

  namapenerima = document.getElementById("namapenerima").value;
  noakun2 = document.getElementById("noakun2").value;

  method = "getakunpengirim";
  param = "";
  param += "&kodeorg=" + kodeorg + "&notransaksi=" + notransaksi;
  param += "&noakun=" + noakun;
  param += "&namapenerima=" + namapenerima;
  param += "&noakun2=" + noakun2;
  param += "&method=" + method;
  tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("noakun").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getakunpenerima() {
  notransaksi = document.getElementById("notransaksi").value;
  noakun = document.getElementById("noakun").value;
  kodeorg = document.getElementById("namapenerima").value;
  method = "getakunpenerima";
  param = "";
  param += "&kodeorg=" + kodeorg + "&notransaksi=" + notransaksi;
  param += "&noakun=" + noakun;
  param += "&method=" + method;
  tujuan = "keu_kasdanbank_slave.php";
  // console.log("OK OK OK OK OK");

  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('norekpenerima').disabled = false;
          // document.getElementById("novoucher").value = "";
          document.getElementById("noakun2").innerHTML = con.responseText;

          // cancelakunkb();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

const triggerBebanBiaya = (noakun) => {
  return new Promise((resolve, reject) => {
    let param = `method=getValidasiAkun&noakun=${noakun}`;
    let tujuan = "keu_kasdanbank_slave.php";
    post_response_text(tujuan, param, respon);

    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert(
              "Informasi",
              "ERROR TRANSACTION,\n" + con.responseText,
            );
            reject("Rejected");
          } else {
            resolve("Success");
            const data = JSON.parse(con.responseText)[0];
            for (const key in data) {
              if (data.hasOwnProperty(key)) {
                const element = document.getElementById(key);
                if (element) {
                  if (data[key] == 1) {
                    element.disabled = false;
                  } else {
                    element.disabled = true;
                  }
                }
              }
            }
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

function showDetailFromPDO() {
  let param = "method=showDetailFromPDO";
  let tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify.popup().destroy();
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("90%", "80%");

          findDetailFromPDO();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

const findDetailFromPDO = () => {
  let kodeorg = document.getElementById("kodeorg").value;
  let tanggal = document.getElementById("tanggal").value;
  let noakun = document.getElementById("noakun").value;

  let param = "method=findDetailFromPDO";
  param += "&kodeorg=" + kodeorg;
  param += "&tanggal=" + tanggal;
  param += "&noakun=" + noakun;
  let tujuan = "keu_kasdanbank_slave.php";
  post_response_text(tujuan, param, respon);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("tbodyDataPdo").innerHTML = con.responseText;
          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const saveDetailFromPDO = (el) => {
  // Data Header
  let notransaksi = document.getElementById("notransaksi").value;
  let param = `method=saveDetailFromPDO&notransaksi=${notransaksi}`;
  let tujuan = "keu_kasdanbank_slave.php";

  // Data Detail
  let trDetail = el.closest("tr");
  let dataDetail = [];

  let noakun = trDetail.querySelector(".noakun").dataset.noakun;
  let rincian = trDetail.querySelector(".rincian").dataset.rincian;
  let rupiah = trDetail.querySelector(".rupiah").dataset.rupiah;
  let nodok = trDetail.querySelector(".nodok").dataset.nodok;
  let nopdo = trDetail.querySelector(".nopdo").dataset.nopdo;
  let notransaksiPdo =
    trDetail.querySelector(".notransaksi").dataset.notransaksi;

  dataDetail.push({ noakun, rincian, rupiah, nodok, nopdo, notransaksiPdo });
  param += `&dataDetailPdo=${JSON.stringify(dataDetail)}`;

  post_response_text(tujuan, param, respon);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 5);
          alertify.success("Berhasil menyimpan detail dari PDO");

          findDetailFromPDO();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};
