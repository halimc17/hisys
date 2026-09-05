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

function excelJurnalmemorial(nojurnal) {
  // alert(param);
  param = "method=excelJurnalmemorial&nojurnal=" + nojurnal;
  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_jurnalmemorial_slave.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("90%", "80%");
}

function displaylist() {
  cancelht();
  document.getElementById("listdata").style.display = "block";
  document.getElementById("header").style.display = "none";
  document.getElementById("detail").style.display = "none";
  document.getElementById("nojurnalsch").value = "";
  document.getElementById("tanggalmulaisch").value = "";
  document.getElementById("tanggalselesaisch").value = "";
  document.getElementById("kodeorgsch").value = "";
  document.getElementById("tipetransaksisch").value = "";
  document.getElementById("revisisch").value = "";
  document.getElementById("noreferensisch").value = "";
  document.getElementById("statsch").value = "";
  loaddata(0);
}

function getpage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function getkodekegiatanalokasi(kodekegiatan, kodeblok) {
  // nojurnal = document.getElementById("nojurnal").value;
  // nourut = document.getElementById("nourut").value;
  kodeorg = document.getElementById("kodeorg").value;
  noakun = document.getElementById("noakun").value;
  method = "getkodekegiatanalokasi";
  param = "";
  // param +="&nojurnal=" +nojurnal +"&method=" + method+"&nourut=" + nourut+"&kodeorg=" + kodeorg+"&noakun=" + noakun+"&kodekegiatan=" + kodekegiatan+"&kodeblok=" + kodeblok;
  param +=
    "&kodekegiatan=" +
    kodekegiatan +
    "&method=" +
    method +
    "&kodeblok=" +
    kodeblok +
    "&kodeorg=" +
    kodeorg +
    "&noakun=" +
    noakun;
  // alert(param);
  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          ar = con.responseText.split("###");
          document.getElementById("kodekegiatan").innerHTML = ar[0];
          document.getElementById("kodeblok").innerHTML = ar[1];
          loadfiles();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddata(num) {
  nojurnal = document.getElementById("nojurnalsch").value;
  kodeorg = document.getElementById("kodeorgsch").value;
  tanggalmulai = document.getElementById("tanggalmulaisch").value;
  tanggalselesai = document.getElementById("tanggalselesaisch").value;
  tanggalselesai = document.getElementById("tanggalselesaisch").value;
  noreferensi = document.getElementById("noreferensisch").value;
  statsch = document.getElementById("statsch").value;
  revisi = document.getElementById("revisisch").value;
  tipetransaksi = document.getElementById("tipetransaksisch").value;
  param = "method=loaddata&page=" + num;
  param +=
    "&nojurnal=" +
    nojurnal +
    "&tanggalmulai=" +
    tanggalmulai +
    "&tanggalselesai=" +
    tanggalselesai;
  param +=
    "&kodeorg=" +
    kodeorg +
    "&noreferensi=" +
    noreferensi +
    "&statsch=" +
    statsch +
    "&revisi=" +
    revisi +
    "&tipetransaksi=" +
    tipetransaksi;
  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listdata").style.display = "block";
          document.getElementById("header").style.display = "none";
          document.getElementById("detail").style.display = "none";
          isdt = con.responseText.split("####");
          document.getElementById("contain").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
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
    document.getElementById("matauang").value = "IDR";
    document.getElementById("kurs").value = "1";
    return;
  }
  tujuan = "keu_jurnalmemorial_slave.php";
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

function getrevisi(revisi) {
  tipetransaksi = document.getElementById("tipetransaksi").value;
  if (tipetransaksi == "JM") {
    document.getElementById("revisi").value = "0";
    document.getElementById("revisi").disabled = true;
  } else {
    document.getElementById("revisi").disabled = false;
  }
}

function saveht(parameter) {
  method = "saveht";
  tujuan = "keu_jurnalmemorial_slave.php";
  var passP = parameter.split("###");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    if (i == 1) {
      //jumlah ditaro di awal agar di removecomma
      param += passP[i] + "=" + remove_comma_var(getValue(passP[i]));
    } else if (i == 2) {
      param += "&" + passP[i] + "=" + remove_comma_var(getValue(passP[i]));
    } else {
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
  }
  param += "&method=" + method;
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("nojurnal").value = con.responseText;
          document.getElementById("detail").style.display = "block";
          document.getElementById("nojurnal").disabled = true;
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

function newdata() {
  document.getElementById("header").style.display = "block";
  document.getElementById("listdata").style.display = "none";
  document.getElementById("detail").style.display = "none";

  document.getElementById("inputdata").style.display = "none";
  document.getElementById("contdetail").style.display = "none";
  cancelht();
  // document.getElementById('detailhead').style.display='none';
}

function showformupload() {
  document.getElementById("header").style.display = "none";
  document.getElementById("listdata").style.display = "none";
  document.getElementById("detail").style.display = "none";

  document.getElementById("inputdata").style.display = "block";
  document.getElementById("contdetail").style.display = "block";
  document.getElementById("listData").style.display = "none";

  // setValue2('periode',null);
  // setValue2('kodeorg',null);
  // setValue2('tipekary',null);
  document.getElementById("upload").value = "";
  document.getElementById("contdetail").innerHTML = "";
}

function cancelht() {
  document.getElementById("detail").style.display = "none";
  document.getElementById("matauang").value = "IDR";
  document.getElementById("kurs").value = "1";
  document.getElementById("nojurnal").value = "";
  document.getElementById("kodeorg").value = "";
  document.getElementById("tanggal").value = "";
  document.getElementById("tipetransaksi").value = "";
  document.getElementById("noreferensi").value = "";
  document.getElementById("revisi").value = "0";
  document.getElementById("kodeorg").disabled = false;
  document.getElementById("revisi").disabled = false;
}

function editht(nojurnal) {
  param = "method=editht" + "&nojurnal=" + nojurnal;
  tujuan = "keu_jurnalmemorial_slave.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          // document.getElementById('method').value = 'update';
          // alert(con.responseText.split);
          document.getElementById("listdata").style.display = "none";
          document.getElementById("header").style.display = "block";
          document.getElementById("detail").style.display = "block";
          ar = con.responseText.split("###");
          document.getElementById("nojurnal").value = ar[0];
          document.getElementById("kodeorg").value = ar[1];
          document.getElementById("kodeorg").disabled = false;
          document.getElementById("matauang").disabled = false;
          document.getElementById("tanggal").value = ar[2];
          document.getElementById("matauang").value = ar[3];
          document.getElementById("kurs").value = ar[4];
          document.getElementById("noreferensi").value = ar[5];
          document.getElementById("revisi").value = ar[6];

          if (ar[6] == "0") {
            document.getElementById("tipetransaksi").value = "JM";
            document.getElementById("revisi").disabled = true;
          } else {
            document.getElementById("tipetransaksi").value = "JA";
          }

          loaddatadt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function deletedt(nojurnal, nourut) {
  param = "method=deletedt";
  param += "&nojurnal=" + nojurnal + "&nourut=" + nourut;
  tujuan = "keu_jurnalmemorial_slave.php";
  // post_response_text(tujuan, param, respog);

  alertify.confirm(
    "Informasi",
    "Anda yakin menghapus data detail",
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
          alert(con.responseText);
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

function editdt(nojurnal, nourut) {
  param = "method=editdt" + "&nojurnal=" + nojurnal + "&nourut=" + nourut;
  tujuan = "keu_jurnalmemorial_slave.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          // document.getElementById('method').value = 'update';
          // alert(con.responseText.split);
          ar = con.responseText.split("###");
          document.getElementById("nodok").value = ar[0];
          document.getElementById("noakun").value = ar[1];
          document.getElementById("jumlah").value = ar[2];
          document.getElementById("keterangan").value = ar[3];
          document.getElementById("kodekegiatan").value = ar[4];
          document.getElementById("kodeasset").value = ar[5];
          document.getElementById("nik").value = ar[6];
          document.getElementById("kodecustomer").value = ar[7];
          document.getElementById("kodesupplier").value = ar[8];
          document.getElementById("kodevhc").value = ar[9];
          document.getElementById("kodeblok").value = ar[10];
          document.getElementById("nourut").value = ar[11];
          document.getElementById("methoddt").value = "update";
          getoptdetail();
          // getpemilikhutang(ar[3],ar[5]);
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
  tujuan = "keu_jurnalmemorial_slave.php";
  // var passP = parameter.split('###');
  // var param = "";
  // for(i=1;i<passP.length;i++) {
  // var tmp = document.getElementById(passP[i]);
  // if(i==1) {//jumlah ditaro di awal agar di removecomma
  // param += passP[i]+"="+remove_comma_var(getValue(passP[i]));
  // } else {
  // param += "&"+passP[i]+"="+getValue(passP[i]);
  // }
  // }

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
          alert(con.responseText);
        } else {
          canceldt();
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

function canceldt() {
  document.getElementById("nodok").value = "";
  document.getElementById("noakun").value = "";
  document.getElementById("keterangan").value = "";
  document.getElementById("kodekegiatan").value = "";
  document.getElementById("kodeasset").value = "";
  document.getElementById("nik").value = "";
  document.getElementById("kodecustomer").value = "";
  document.getElementById("kodesupplier").value = "";
  document.getElementById("kodevhc").value = "";
  document.getElementById("kodeblok").value = "";
  document.getElementById("jumlah").value = "0";
  document.getElementById("keterangan").value = "";
  document.getElementById("methoddt").value = "insert";
  document.getElementById("nourut").value = "";
}

function loaddatadt() {
  nojurnal = document.getElementById("nojurnal").value;
  param = "method=loaddatadt";
  param += "&nojurnal=" + nojurnal;
  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listdatadt").innerHTML = con.responseText;
          getoptdetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteht(nojurnal) {
  param = "method=deleteht";
  param += "&nojurnal=" + nojurnal;
  tujuan = "keu_jurnalmemorial_slave.php";
  // post_response_text(tujuan, param, respog);
  alertify.confirm(
    "Informasi",
    "Anda yakin menghapus data detail : " + nojurnal + " ???",
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
          alert(con.responseText);
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

function getoptdetail() {
  nojurnal = document.getElementById("nojurnal").value;
  nourut = document.getElementById("nourut").value;
  kodeorg = document.getElementById("kodeorg").value;
  method = "getoptdetail";
  param = "";
  param +=
    "&kodeorg=" + kodeorg + "&nojurnal=" + nojurnal + "&nourut=" + nourut;
  param += "&method=" + method;
  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          ar = con.responseText.split("###");
          document.getElementById("kodeasset").innerHTML = ar[0];
          document.getElementById("nik").innerHTML = ar[1];
          document.getElementById("kodevhc").innerHTML = ar[2];
          document.getElementById("kodeblok").innerHTML = ar[3];
          document.getElementById("noakun").innerHTML = ar[4];
          getkodekegiatanalokasi(ar[6], ar[7]);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*******************************************************************************************/
/*******************************************************************************************/
/*******************************************************************************************/

function submitfile() {
  var nojurnal = document.getElementById("nojurnal").value;
  var kriteriaefil = document.getElementById("kriteriaefil").value;
  var file = document.getElementById("upload_file").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload_file"));
  formdata.append("nojurnal", trim(nojurnal));
  formdata.append("kriteriaefil", kriteriaefil);
  if (getValue("upload_file") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "keu_jurnalmemorial_slave.php?method=submitfile", true);
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
          document.getElementsByClassName("mybutton").disabled = false;
          alert("Uploaded Success.");
          document.getElementById("upload_file").value = "";
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
  nojurnal = document.getElementById("nojurnal").value;
  param = "method=loadfiles&nojurnal=" + trim(nojurnal);
  // alert(param);
  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // if (document.getElementById('listfiles') !== null) {
          // document.getElementById('listfiles').innerHTML = con.responseText;
          // }
          document.getElementById("listfiles").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(nojurnal, namafile) {
  param = "method=deletefile&nojurnal=" + nojurnal + "&namafile=" + namafile;
  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

/**********************************************************************************/

function ajukan(nojurnal, page) {
  content = '<div id=formpost  style="height:100%;width:800px;"></div>';
  title = "Ajukan Persetujuan";
  height = "";
  width = "800";
  // showDialog4(title,content,width,height,'event');
  formajukan(nojurnal, page);
}

function formajukan(nojurnal, unit, page) {
  method = "formajukan";
  param = "";
  param += "&nojurnal=" + nojurnal + "&page=" + page;
  param += "&kodeorg=" + unit;
  param += "&method=" + method;
  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
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
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

// #= diubah menjadi persetujuan
function saveposting(nojurnal, maxaproval, page) {
  param = "";
  method = "saveposting";
  strper = "";
  // for(i=1;i<=maxaproval;i++){
  // strper += '&persetujuan['+i+']='+trim(document.getElementById('persetujuan'+i).value)
  // }
  param += "&nojurnal=" + nojurnal + "&maxaproval=" + maxaproval;
  param += "&method=" + method;
  // param+=strper;
  // alert(param);
  tujuan = "keu_jurnalmemorial_slave.php";
  // if(confirm('Ajukan No Jurnal : '+nojurnal+' ?')) {
  // post_response_text(tujuan, param, respon);
  // }

  alertify.confirm(
    "Informasi",
    "Posting transaksi : " + nojurnal + " ???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    }
  );

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

// #= diubah menjadi persetujuan
function saveajukan(nojurnal, maxaproval, page) {
  param = "";
  method = "saveajukan";
  strper = "";
  for (i = 1; i <= maxaproval; i++) {
    strper +=
      "&persetujuan[" +
      i +
      "]=" +
      trim(document.getElementById("persetujuan" + i).value);
  }
  param += "&nojurnal=" + nojurnal + "&maxaproval=" + maxaproval;
  param += "&method=" + method;
  param += strper;
  // alert(param);
  tujuan = "keu_jurnalmemorial_slave.php";
  // if(confirm('Ajukan No Jurnal : '+nojurnal+' ?')) {
  // post_response_text(tujuan, param, respon);
  // }

  alertify.confirm(
    "Informasi",
    "Ajukan transaksi : " + nojurnal + " ???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    }
  );

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // closeDialog4();
          // closeDialog();
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

function html(nojurnal, page) {
  ev = "event";
  content = '<div id=detailhtml style="height:100%;width:100%;"></div>';
  title = "detail";
  height = "300";
  width = "1000";
  showDialog1(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic1").style.top = pos[1] + "px";
  document.getElementById("dynamic1").style.left = pos[0] - 500 + "px";
  param = "";
  param += "&nojurnal=" + nojurnal + "&page=" + page;
  // alert(param);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("detailhtml").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text("keu_jurnalmemorial_slave.php?method=html", param, respon);
}

function pdf(nojurnal) {
  param = "method=pdf&nojurnal=" + nojurnal;
  alertify
    .popuppdf(
      "title",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_jurnalmemorial_slave.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("90%", "80%");
}

function thpapproval(rowthp) {
  var select = document.getElementById("thpapproval");
  var selectedValue = select.value;

  if (selectedValue == "a") {
    document.getElementById("thpb").setAttribute("hidden", true);

    for (x = 1; x <= rowthp; x++) {
      document.getElementById("thpa" + x).removeAttribute("hidden");
    }
  } else if (selectedValue == "b") {
    for (x = 1; x <= rowthp; x++) {
      document.getElementById("thpa" + x).setAttribute("hidden", true);
    }

    document.getElementById("thpb").removeAttribute("hidden");
  } else {
    for (x = 1; x <= rowthp; x++) {
      document.getElementById("thpa" + x).setAttribute("hidden", true);
    }

    document.getElementById("thpb").setAttribute("hidden", true);
  }
}

function saveajukan(rowthp) {
  thpapprov = document.getElementById("thpapproval").value;
  tanggal = document.getElementById("tanggalapprov").value;
  kodeorg = document.getElementById("kodeunit").value;
  notransaksi = document.getElementById("notransaksi").value;
  jenispersetujuan = document.getElementById("jenispersetujuan").value;

  param = "method=saveajukan";
  param += "&notransaksi=" + notransaksi;
  param += "&kodeorg=" + kodeorg;
  param += "&jenispersetujuan=" + jenispersetujuan;
  param += "&tanggal=" + tanggal;
  param += "&thpapproval=" + thpapprov;

  if (thpapprov == "a") {
    for (x = 1; x <= rowthp; x++) {
      if (document.getElementById("approval" + x).value == "") {
        alert("Nama Persetujuan ke " + x + " wajib di pilih!");
        return false;
      }

      param +=
        "&karyidapproval[" +
        x +
        "]=" +
        document.getElementById("approval" + x).value;
    }
  } else if (thpapprov == "b") {
    if (document.getElementById("approvalb").value == "") {
      alert("Nama Persetujuan ke 1 wajib di pilih!");
      return false;
    }

    param += "&karyidapproval=" + document.getElementById("approvalb").value;
  }

  tujuan = "keu_jurnalmemorial_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.popup().destroy();
          alertify.alert("Berhasil Mengajukan");

          loaddata();
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

  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("jenis", jenis);
  // formdata.append("kodeorg", kodeorg);

  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "keu_jurnalmemorial_slave.php?method=fileSelected", true);
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
    }
  );
}
function savedetail(currRow, maxRow) {
  nojurnal = document.getElementById("nojurnal_" + currRow).innerHTML;
  tanggal = document.getElementById("tanggal_" + currRow).innerHTML;
  kodeorg = document.getElementById("kodeorg_" + currRow).value;
  kodejurnal = document.getElementById("kodejurnal_" + currRow).value;
  totaldebet = document.getElementById("totaldebet").innerHTML;
  totalkredit = document.getElementById("totalkredit").innerHTML;
  nourut = document.getElementById("nourut_" + currRow).innerHTML;
  noakun = document.getElementById("noakun_" + currRow).innerHTML;
  keterangan = document.getElementById("keterangan_" + currRow).innerHTML;
  jumlah = document.getElementById("jumlah_" + currRow).innerHTML;
  matauang = document.getElementById("matauang_" + currRow).innerHTML;
  kurs = document.getElementById("kurs_" + currRow).innerHTML;
  kodekegiatan = document.getElementById("kodekegiatan_" + currRow).innerHTML;
  kodeasset = document.getElementById("kodeasset_" + currRow).innerHTML;
  kodebarang = document.getElementById("kodebarang_" + currRow).innerHTML;
  nik = document.getElementById("nik_" + currRow).innerHTML;
  kodecustomer = document.getElementById("kodecustomer_" + currRow).innerHTML;
  kodesupplier = document.getElementById("kodesupplier_" + currRow).innerHTML;
  kodebarang = document.getElementById("kodebarang_" + currRow).innerHTML;
  noreferensi = document.getElementById("noreferensi_" + currRow).innerHTML;
  kodevhc = document.getElementById("kodevhc_" + currRow).innerHTML;
  kodeblok = document.getElementById("kodeblok_" + currRow).innerHTML;
  revisi = document.getElementById("revisi_" + currRow).innerHTML;
  kodesegment = document.getElementById("kodesegment_" + currRow).innerHTML;

  method = document.getElementById("method_" + currRow).value;

  param = "";
  param += "method=" + method;
  param += "&nojurnal=" + nojurnal;
  param += "&tanggalupload=" + tanggal;
  param += "&kodeorg=" + kodeorg;
  param += "&nourut=" + nourut;
  param +=
    "&noakun=" +
    noakun +
    "&keterangan=" +
    keterangan +
    "&jumlah=" +
    jumlah +
    "&matauang=" +
    matauang;
  param += "&kurs=" + kurs;
  param += "&kodekegiatan=" + kodekegiatan;
  param += "&kodeasset=" + kodeasset;
  param += "&kodebarang=" + kodebarang;
  param += "&nik=" + nik;
  param += "&kodevhc=" + kodevhc;
  param += "&kodecustomer=" + kodecustomer;
  param += "&kodesupplier=" + kodesupplier;
  param += "&kodebarang=" + kodebarang;
  param += "&noreferensi=" + noreferensi;
  param += "&kodeblok=" + kodeblok;
  param += "&revisi=" + revisi;
  param += "&kodesegment=" + kodesegment;
  param += "&kodejurnal=" + kodejurnal;
  param += "&totaldebet=" + totaldebet;
  param += "&totalkredit=" + totalkredit;
  param += "&jalanke=" + currRow;
  // param += '&keterangan=UPLOAD';

  // alert(param);
  console.log(param);

  tujuan = "keu_jurnalmemorial_slave.php";
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
              "validasi_" + currRow
            ).style.backgroundColor = "red";
          }
        } else {
          if (currRow != undefined) {
            document.getElementById(
              "validasi_" + currRow
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
