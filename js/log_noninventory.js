function displayforminput() {
  document.getElementById("listdata").style.display = "none";
  document.getElementById("forminput").style.display = "block";
  clearform();
}

function clearform() {
  document.getElementById("notransaksi").value = "";
  document.getElementById("unit").disabled = false;
  document.getElementById("imgunit").disabled = false;
  document.getElementById("tanggal").disabled = false;
  document.getElementById("nopo").value = "";
  document.getElementById("imgnopo").style.display = "";
  document.getElementById("listitempo").style.display = "none";
  document.getElementById("databarang").innerHTML = "";

  document.getElementsByClassName("tdtermin")[0].style.display = "none";
  document.getElementsByClassName("tdtermin")[1].style.display = "none";
  document.getElementsByClassName("tdtermin")[2].style.display = "none";
  document.getElementById("termin").innerHTML = "";
}

function displaylist() {
  document.getElementById("listdata").style.display = "block";
  document.getElementById("forminput").style.display = "none";
  document.getElementById("scnotransaksi").value = "";
  document.getElementById("sctanggal").value = "";
  document.getElementById("crnopo").value = "";
  loaddata(0);
}

function getpage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function cekJumlah(nopp, no) {
  const jumlahterima = document.querySelector(
    `#sudahditerima_${no}`
  ).textContent;
  const saldo = document.querySelector(`#jumlahpesan_${no}`).textContent;
  const inpt = document.querySelector(`#diterima_${no}`);
  const result = parseInt(saldo) - parseInt(jumlahterima);

  if (inpt.value > result) {
    inpt.value = 0;
    return alertify.alert("Melebihi jumlah PO");
  }
}

function getpenerima(penerima, disetujui) {
  unit = document.getElementById("unit").value;
  notransaksi = document.getElementById("notransaksi").value;

  param =
    "method=getpenerima&unit=" +
    unit +
    "&notransaksi=" +
    notransaksi +
    "&penerima=" +
    penerima +
    "&disetujui=" +
    disetujui;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          split = con.responseText.split("####");
          document.getElementById("listitempo").style.display = "none";
          document.getElementById("databarang").innerHTML = "";
          document.getElementById("penerima").innerHTML = split[0];
          document.getElementById("disetujui").innerHTML = split[1];
          pickuppo("");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function formcarinopo(title, content, ev) {
  document.getElementById("nopo").value = "";
  document.getElementById("listitempo").style.display = "none";
  document.getElementById("databarang").innerHTML = "";
  document.getElementsByClassName("tdtermin")[0].style.display = "none";
  document.getElementsByClassName("tdtermin")[1].style.display = "none";
  document.getElementsByClassName("tdtermin")[2].style.display = "none";
  document.getElementById("termin").innerHTML = "";
  width = "auto";
  height = "auto";
  //showDialog6(title, content, width, height, ev);

  alertify
    .popup("Detail", content)
    .set({ resizable: true, maximizable: true })
    .resizeTo("40%", "70%");
}

function carinopo() {
  unit = document.getElementById("unit").value;
  scnopo = document.getElementById("scnopo").value;
  nosj=document.getElementById('nosj').value;
	
	param='method=carinopo&scnopo='+scnopo+'&unit='+unit+'&nosj='+nosj;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("popuplistpo").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pickuppo(nopo) {
  xnopo = document.getElementById("nopo").value;
  notransaksi = document.getElementById("notransaksi").value;
  unit = document.getElementById("unit").value;
  if (nopo == "") {
    znopo = document.getElementById("nopo").value;
  } else {
    znopo = nopo;
  }

  param =
    "method=pickuppo&notransaksi=" +
    notransaksi +
    "&nopo=" +
    znopo +
    "&unit=" +
    unit;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("nopo").value = znopo;
          vsplt = con.responseText.split("#####");
          if (vsplt[1] != "") {
            document.getElementsByClassName("tdtermin")[0].style.display = "";
            document.getElementsByClassName("tdtermin")[1].style.display = "";
            document.getElementsByClassName("tdtermin")[2].style.display = "";
            document.getElementById("showtermin").value = "1";
            document.getElementById("termin").innerHTML = vsplt[1];
          } else {
            document.getElementById("showtermin").value = "0";
          }
          // alert(xnopo);
          if (nopo == "" && xnopo == "") {
          } else {
            // alert(vsplt[0]);
            document.getElementById("listitempo").style.display = "block";
            document.getElementById("databarang").innerHTML = vsplt[0];
            $(document).ready(function () {
              $(".select2").select2({
                dropdownAutoWidth: true,
              });
            });
            if (nopo != "") {
              // closeDialog5();
              alertify.popup().destroy();
            }
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getsubunitdt(nourut) {
  unit = document.getElementById("unit").value;
  subunit = document.getElementById("subunit_" + nourut).value;
  param = "method=getsubunitdt&unit=" + unit + "&subunit=" + subunit;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          vsplt = con.responseText.split("####");
          document.getElementById("subunitdt_" + nourut).innerHTML = vsplt[0];
          if (vsplt[1] == "1") {
            document.getElementById("subunitdt_" + nourut).disabled = true;
          } else {
            document.getElementById("subunitdt_" + nourut).disabled = false;
          }
          getkegiatan(nourut);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getkegiatan(nourut) {
  unit = document.getElementById("unit").value;
  subunit = document.getElementById("subunit_" + nourut).value;
  subunitdt = document.getElementById("subunitdt_" + nourut).value;
  param =
    "method=getkegiatan&unit=" +
    unit +
    "&subunit=" +
    subunit +
    "&subunitdt=" +
    subunitdt;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("kegiatan_" + nourut).innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpan(jlhbaris) {
  notransaksi = document.getElementById("notransaksi").value;
  unit = document.getElementById("unit").value;
  penerima = document.getElementById("penerima").value;
  disetujui = document.getElementById("disetujui").value;
  tanggal = document.getElementById("tanggal").value;
  nopo = document.getElementById("nopo").value;
  showtermin = document.getElementById("showtermin").value;
  termin = document.getElementById("termin").value;
  nosj=document.getElementById('nosj').value;

  strurl = "";
  for (i = 1; i <= jlhbaris; i++) {
    sttenbl = "0";
    if (document.getElementById("subunitdt_" + i).disabled == true) {
      sttenbl = "1";
    }

    strurl +=
      "&kodebarang[]=" +
      trim(document.getElementById("kodebarang_" + i).innerHTML) +
      "&satuan[]=" +
      trim(document.getElementById("satuan_" + i).innerHTML) +
      "&nopp[]=" +
      trim(document.getElementById("nopp_" + i).innerHTML) +
      "&sudahditerima[]=" +
      trim(document.getElementById("sudahditerima_" + i).innerHTML) +
      "&jumlahpo[]=" +
      trim(document.getElementById("jumlahpesan_" + i).innerHTML) +
      "&diterima[]=" +
      trim(document.getElementById("diterima_" + i).value) +
      "&subunit[]=" +
      trim(document.getElementById("subunit_" + i).value) +
      "&subunitdt[]=" +
      trim(document.getElementById("subunitdt_" + i).value) +
      "&kegiatan[]=" +
      trim(document.getElementById("kegiatan_" + i).value) +
      "&sttenbl[]=" +
      sttenbl;
  }

  param =
    "method=simpan&notransaksi=" +
    notransaksi +
    "&unit=" +
    unit +
    "&penerima=" +
    penerima +
    "&tanggal=" +
    tanggal +
    "&nopo=" +
    nopo +
    "&nosj="+
    nosj+
    "&showtermin=" +
    showtermin +
    "&termin=" +
    termin +
    "&disetujui=" +
    disetujui;
  param += strurl;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("scnotransaksi").value = notransaksi;
          alert("Sukses");
          loaddata();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddata(pg) {
  scnotransaksi = document.getElementById("scnotransaksi").value;
  sctanggal = document.getElementById("sctanggal").value;
  crnopo = document.getElementById("crnopo").value;

  param =
    "method=loaddata&page=" +
    pg +
    "&scnotransaksi=" +
    scnotransaksi +
    "&sctanggal=" +
    sctanggal +
    "&crnopo=" +
    crnopo;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText.split("####");
          document.getElementById("listdata").style.display = "block";
          document.getElementById("forminput").style.display = "none";
          //document.getElementById('contain').innerHTML=con.responseText;
          document.getElementById("contain").innerHTML = data[0];
          document.getElementById("footer").innerHTML = data[1];
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editgr(notransaksi) {
  param = "method=editgr&notransaksi=" + notransaksi;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);

  document.getElementById("listdata").style.display = "none";
  document.getElementById("forminput").style.display = "block";
  document.getElementById("databarang").style.display = "block";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //FILL Form
          split = con.responseText.split("####");
          document.getElementById("notransaksi").value = notransaksi;

          for (i = 0; i < document.getElementById("unit").length; i++) {
            if (
              document.getElementById("unit").options[i].value == trim(split[0])
            ) {
              document.getElementById("unit").selectedIndex = i;
            }
          }
          document.getElementById("unit").disabled = true;
          document.getElementById("imgunit").disabled = true;

          document.getElementById("tanggal").value = split[2];
          document.getElementById("tanggal").disabled = true;

          document.getElementById("nopo").value = split[3];
          document.getElementById("imgnopo").style.display = "none";
          getpenerima(split[1], split[4]);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletegr(notransaksi) {
  param = "method=deletegr&notransaksi=" + notransaksi;
  tujuan = "log_slave_noninventory.php";

  if (confirm("Anda yakin hapus no transaksi " + notransaksi + "?")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Sukses");
          getpage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postinggrx(notransaksi) {
  width = "";
  height = "";
  content =
    "<fieldset><legend>No GR " +
    notransaksi +
    "</legend><div id=popuppostinggr style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
  title = "&nbsp;&nbsp;Posting GR";
  showDialog5(title, content, width, height, "event");
  pos = new Array();
  pos = getMouseP("event");
  document.getElementById("dynamic5").style.top = pos[1] + "px";
  document.getElementById("dynamic5").style.left = pos[0] - 600 + "px";

  param = "method=postinggrx&notransaksi=" + notransaksi;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("popuppostinggr").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postinggr(notransaksi, tipe) {
  if (tipe == "SO") {
    tglselesai = document.getElementById("tanggalselesai").value;
    keterangan = document.getElementById("keterangan").value;
    if (tglselesai == "") {
      alert("Mohon Isi Tanggal !");
      return false;
    }
  }
  param = "method=postinggr&notransaksi=" + notransaksi;
  if (tipe == "SO") {
    param += "&tanggalselesai=" + tglselesai;
    param += "&keterangan=" + keterangan;
  }
  tujuan = "log_slave_noninventory.php";

  if (confirm("Anda yakin posting no transaksi " + notransaksi + "?")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // closeDialog5();
          getpage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewgr(ev, notransaksi, kodebarang) {
  // width = '';
  // height = '';
  // content = "<fieldset><legend>No GR "+notransaksi+"</legend><div id=popuppreviewgr style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
  // title = "&nbsp;&nbsp;Preview GR";
  // showDialog5(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic5').style.top = pos[1] + 'px';
  // document.getElementById('dynamic5').style.left = (pos[0]-600) + 'px';

  param =
    "method=previewgr&notransaksi=" + notransaksi + "&kodebarang=" + kodebarang;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //document.getElementById('popuppreviewgr').innerHTML=con.responseText;
          alertify
            .popup("Detail", con.responseText)
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

function previewpdfgr(ev, notransaksi) {
  param = "method=previewpdfgr&notransaksi=" + notransaksi;
  tujuan = "log_slave_noninventory.php";

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_noninventory.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");

  // title='Report PDF';
  // tujuan=tujuan+"?"+param;
  // width = 1024;
  // height = 500;
  // content = "<iframe frameborder=0 width=1024px height=500px src='" + tujuan + "'></iframe>"
  // showDialog5(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic5').style.top = pos[1] + 'px';
  // document.getElementById('dynamic5').style.left = (pos[0]-600) + 'px';
}

function previewpdfgrba(ev, notransaksi) {
  param = "method=previewpdfgrba&notransaksi=" + notransaksi;
  tujuan = "log_slave_noninventory.php";

  title = "Report PDF";
  tujuan = tujuan + "?" + param;
  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_noninventory.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");

  // width = 1024;
  // height = 500;
  // content = "<iframe frameborder=0 width=1024px height=500px src='" + tujuan + "'></iframe>"
  // showDialog5(title, content, width, height, ev);
  // pos = new Array();
  // pos = getMouseP(ev);
  // document.getElementById('dynamic5').style.top = pos[1] + 'px';
  // document.getElementById('dynamic5').style.left = (pos[0]-600) + 'px';
}

//Umar
// function ajukan(notransaksi, tipe, unit){
// content= "<div id=formpost  style=\"height:100%;width:800px;\"></div>";
// title='Ajukan Persetujuan';
// height='';
// width='800';
// // showDialog4(title,content,width,height,'event');
// formajukan(notransaksi, tipe, unit);
// }

function ajukan(notransaksi, tipe, unit) {
  method = "formajukan";
  param = "";
  param += "&notransaksi=" + notransaksi + "&tipe=" + tipe + "&unit=" + unit;
  param += "&method=" + method;
  tujuan = "log_slave_noninventory.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpost').innerHTML=con.responseText;
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("500px", "400px");
          // alertify.popup().set({'resizable':true,'maximizable':true,'Ajukan':con.responseText}).resizeTo('500px','400px').show();
          // loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveajukan(notransaksi, tipe, maxaproval) {
  param = "";
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
    "&tipe=" +
    tipe;
  param += "&maxaproval=" + maxaproval;
  param += "&method=saveajukan";
  param += strper;
  tujuan = "log_slave_noninventory.php";
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
    }
  );

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify.popup().destroy();
          loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showupload(ev, kodebarang, nodok) {
  showformupload(ev);
  nodok = document.getElementById("notransaksi").value;
  param =
    "method=showupload&notransaksi=" + nodok + "&kodebarang=" + kodebarang;
  tujuan = "log_slave_penerimaanUpload.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("contUpload").innerHTML = con.responseText;
          loadfilesx(nodok, kodebarang);
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
    "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
  showDialog2(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic2").style.top = pos[1] + "px";
  document.getElementById("dynamic2").style.left = pos[0] - 300 + "px";
  document.getElementById("dynamic2").style.display = "";
}

function loadfilesx(nodok, kodebarang) {
  param = "method=loadfiles&notransaksi=" + nodok + "&kodebarang=" + kodebarang;
  tujuan = "log_slave_penerimaanUpload.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (document.getElementById("listfilestop") !== null) {
            document.getElementById("listfilestop").innerHTML =
              con.responseText;
          }
          if (document.getElementById("listfiles") !== null) {
            document.getElementById("listfiles").innerHTML = con.responseText;
          }
          if (document.getElementById("listfilesview") !== null) {
            document.getElementById("listfilesview").innerHTML =
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

function save_filex() {
  var file = document.getElementById("upload").files[0];
  var notransaksi = document.getElementById("notransaksi").value;
  var kodebarang = document.getElementById("kodebarangupload").innerHTML;
  var jenisupload = document.getElementById("kriteriaefil").value;
  var formdata = new FormData();
  formdata.append("notransaksi", notransaksi);
  formdata.append("kodebarang", kodebarang);
  formdata.append("jenisupload", jenisupload);
  formdata.append("file", file);
  formdata.append("fileupload", document.getElementById("upload").value);
  //alert(document.getElementById("filex").value);
  if (document.getElementById("upload").value == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_penerimaanUpload.php?method=submitfilex", true);
  busy_on();
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
          loadfilesx(notransaksi, kodebarang);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefilex(notransaksi, namafile) {
  param = "notransaksi=" + notransaksi;
  param += "&namafile=" + namafile;
  post_response_text(
    "log_slave_penerimaanUpload.php?method=deletefilex",
    param,
    respog
  );
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfilesx(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
