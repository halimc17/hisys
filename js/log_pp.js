stat_input = 0;
stat_inputb = 0;
stat_inputc = 0;
function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loadData(paged);
}
function getPage2(pg) {
  loadData(pg);
  clear_all_data();
}
function cariData(pg) {
  document.getElementById("form_pp").style.display = "none";
  document.getElementById("list_pp").style.display = "block";
  document.getElementById("persetujuan").style.display = "none";
  loadData(pg);
}
function showalllist(pg) {
  document.getElementById("txtsearch").value = "";
  document.getElementById("tgl_cari").value = "";
  document.getElementById("form_pp").style.display = "none";
  document.getElementById("list_pp").style.display = "block";
  document.getElementById("persetujuan").style.display = "none";
  loadData(pg);
}
function loadData(pg) {
  carinopppr = document.getElementById("txtsearch").value;
  caritanggalpppr = document.getElementById("tgl_cari").value;
  param =
    "method=loaddata" +
    "&page=" +
    pg +
    "&carinopppr=" +
    carinopppr +
    "&caritanggalpppr=" +
    caritanggalpppr;
  tujuan = "log_slave_pp.php";
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
function displayFormInput() {
  clear_all_data();
  document.getElementById("list_pp").style.display = "none";
  document.getElementById("form_pp").style.display = "block";
  document.getElementById("dtl_pem").style.display = "block";
}
function clear_all_data() {
  document.getElementById("kd_bag").disabled = false;
  document.getElementById("tipe").disabled = false;
  document.getElementById("nopp").disabled = true;
  document.getElementById("nopp").value = "";
  // document.getElementById("tgl_pp").disabled = true;
  document.getElementById("tgl_pp").value = getdatenow();
  document.getElementById("tmbl_all").style.display = "none";
  document.getElementById("detailTable").style.display = "none";
  document.getElementById("dtl_pem").disabled = false;
  document.getElementById("requester").disabled = false;
  document.getElementById("form_pp").style.display = "none";
  document.getElementById("persetujuan").style.display = "none";
  document.getElementById("list_pp").style.display = "block";
  document.getElementById("method").value = "insert";
  cleardt();
}
function fillField(nopp, tipepp, tgl, kd_org, stat, statTgl, requester) {
  if (statTgl != "0") {
    if (stat == 1) {
      alert("This PR (" + nopp + ") being on submission process");
      return false;
    } else if (stat == 2) {
      alert("This PR (" + nopp + ") has been passed submission process");
      return false;
    }
  } else if (statTgl == "0") {
    document.getElementById("list_pp").style.display = "none";
    document.getElementById("form_pp").style.display = "block";
    document.getElementById("detailTable").style.display = "block";
    document.getElementById("persetujuan").style.display = "none";
    document.getElementById("method").value = "update";
    document.getElementById("kd_bag").value = kd_org;
    document.getElementById("kd_bag").disabled = true;
    document.getElementById("requester").disabled = true;
    document.getElementById("tipe").value = tipepp;
    document.getElementById("tipe").disabled = true;
    document.getElementById("nopp").value = nopp;
    document.getElementById("nopp").disabled = true;
    document.getElementById("tgl_pp").value = tgl;
    document.getElementById("dtl_pem").style.display = "none";
    document.getElementById("detail_kode").innerHTML = nopp;
    document.getElementById("tmbl_all").style.display = "block";
    stat_input = 1;
    stat_inputb = 0;
    var kode = document.getElementById("nopp");
    param = "id=" + kode.value;
    param += "&method=createheader";
    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            // Success Response
            document.getElementById("dtBatal").style.display = "none";
            getrequester(requester, loadlistprsr, nopp);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    post_response_text("log_slave_pp.php", param, respon);
  }
}
function delPp(nopp, stat, statTgl) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  if (statTgl != 0) {
    if (stat == 1) {
      alert("No.PP" + nopp + ". being on submission process");
      return false;
    } else if (stat == 2) {
      alert("No.PP" + nopp + ". has been passed submission process");
      return false;
    }
  } else {
    a = confirm("Delete,  Are you sure?");
    if (a) {
      param = "nopp=" + nopp;
      param += "&method=delete";
      tujuan = "log_slave_pp.php";
      function respog() {
        if (con.readyState == 4) {
          if (con.status == 200) {
            busy_off();
            if (!isSaveResponse(con.responseText)) {
              alert(con.responseText);
            } else {
              //alert(con.responseText);
              document.getElementById("contain").innerHTML = con.responseText;
              alert("Deleted");
              getPage2(paged);
            }
          } else {
            busy_off();
            error_catch(con.status);
          }
        }
      }
      post_response_text(tujuan, param, respog);
    } else {
      getPage2(paged);
    }
  }
}
function previewDetail(nopp, ev) {
  //showDetail(nopp, ev);
  param = "nopp=" + nopp + "&method=getDetailPP";
  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          //document.getElementById('contDetail').innerHTML = con.responseText;
          alertify
            .popup()
            .set({
              resizable: true,
              maximizable: true,
              startMaximized: true,
              message: con.responseText,
            })
            .resizeTo("80%", "70%")
            .show();
          loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewDetail2(nopp, kodebarang, ev) {
  showDetail(nopp, ev);
  param = "nopp=" + nopp + "&method=getDetailPP2&kd_brg=" + kodebarang;
  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("contDetail").innerHTML = con.responseText;
          loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showupload(ev) {
  showformupload(ev);
  nopp = document.getElementById("detail_kode").innerHTML;
  param = "method=showupload&nopp=" + nopp;
  tujuan = "log_slave_pp.php";
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
          loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loadfiles(nopp) {
  param = "method=loadfiles&nopp=" + nopp;
  tujuan = "log_slave_pp.php";
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
function submitfile() {
  var nopp = document.getElementById("detail_kode").innerHTML;
  var kriteriaefil = document.getElementById("kriteriaefil").value;
  var file = document.getElementById("upload").files[0];

  alert(file);
  return false;

  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  formdata.append("nopp", nopp);
  formdata.append("kriteriaefil", kriteriaefil);
  if (getValue("upload") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_pp.php?method=submitfile", true);
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
          document.getElementById("upload").value = "";
          loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function submitfilex(nopp) {
  var kriteriaefil = document.getElementById("kriteriaefil").value;
  var file = document.getElementById("upload").files[0];

  alert(file);
  return false;


  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  formdata.append("nopp", nopp);
  formdata.append("kriteriaefil", kriteriaefil);
  if (getValue("upload") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_pp.php?method=submitfile", true);
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
          document.getElementById("upload").value = "";
          loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function deletefile(nopp, namafile) {
  param = "method=deletefile&nopp=" + nopp + "&namafile=" + namafile;
  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function downloadfile(path, filename) {
  param = "path=" + path + "&filename=" + filename;
  tujuan = "download.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
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
function showDetail(noPP, ev) {
  title = "Purchase/Service Request detail";
  width = "";
  height = "";
  content =
    "<fieldset><legend>" +
    noPP +
    "</legend><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=datPP name=datPP value=" +
    noPP +
    " />";
  showDialog1(title, content, width, height, ev);
}
function get_isi() {
  kdorg = getValue("kd_bag");
  tgl_pp = getValue("tgl_pp");
  tipe = getValue("tipe");
  requester = getValue("requester");

  if (requester == "") {
    alert("Requester harus dipilih.");
    return false;
  }

  param = "kdorg=" + kdorg + "&tipe=" + tipe + "&method=get_isi&tgl_pp="+tgl_pp;
  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("nopp").value = trim(con.responseText);
          document.getElementById("detail_kode").innerHTML = trim(
            con.responseText
          );
          document.getElementById("dtl_pem").style.display = "none";
          document.getElementById("dtBatal").style.display = "";
          detailPembelian();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getrequester(requester, newFunc, nopp) {
  kdorg = getValue("kd_bag");
  param = "kdorg=" + kdorg + "&requester=" + requester + "&method=getrequester";
  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("requester").innerHTML = trim(
            con.responseText
          );
          console.log(typeof newFunc);
          if (typeof newFunc !== "undefined" && typeof newFunc == "function") {
            eval(newFunc(nopp));
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailPembelian() {
  kd_bag = trim(document.getElementById("kd_bag").value);
  tgl_ppr = trim(document.getElementById("tgl_pp").value);
  if (kd_bag == "") {
    alert("Nama Organisasi harus dipilih");
  } else {
    document.getElementById("detailTable").style.display = "block";
    document.getElementById("tmbl_all").style.display = "block";
    document.getElementById("nopp").disabled = true;
    // document.getElementById("tgl_pp").disabled = true;
    document.getElementById("tipe").disabled = true;
    document.getElementById("kd_bag").disabled = true;
    document.getElementById("dtl_pem").disabled = true;
    createheader();
  }
}
function createheader() {
  var kode = document.getElementById("nopp");
  param = "id=" + kode.value;
  param += "&tipe=" + getValue("tipe");
  param += "&rtgl_pp=" + getValue("tgl_pp");
  param += "&rkd_bag=" + getValue("kd_bag");
  param += "&requester=" + getValue("requester");
  param += "&method=createheader";

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          var detailDiv = document.getElementById("detailTable");
          detailDiv.innerHTML = con.responseText;
          loadlistprsr(kode.value);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text("log_slave_pp.php", param, respon);
}
/* Function pass2detail
 * Fungsi untuk menampilkan tabel detail dari tabel Main yang dimaksud
 * I : numRow dari tabel Main
 * P : Ajax untuk extract data dan persiapan tabel dalam bentuk HTML
 * O : Tampilan tabel detail
 */
function pass2detail() {
  var kode = document.getElementById("nopp");
  param = "id=" + kode.value;
  param += "&tipe=" + getValue("tipe");
  param += "&rtgl_pp=" + getValue("tgl_pp");
  param += "&rkd_bag=" + getValue("kd_bag");
  param += "&method=createTable";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          var detailDiv = document.getElementById("detailTable");
          detailDiv.innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text("log_slave_pp.php", param, respon);
}
//bagian cari data barang dan kode anggaran, dari log_5masterbarang, keu_anggaran
function searchBrg(ev) {
  width = "auto";
  height = "auto";
  title = "Form Mencari Item";
  content = `<font style=font-style:italic>Find : </font><input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button><div style=clear:both></div><div id=container></div>`;
  alertify.popup().destroy();
  alertify
    .popup(title, content)
    .set({ resizable: true, maximizable: true })
    .resizeTo("80%", "70%");
}

function findBrg(kodebarang, noakun) {
  txt = trim(document.getElementById("no_brg").value);
  rtgl_pp = trim(document.getElementById("tgl_pp").value);
  nopp = trim(document.getElementById("nopp").value);
  rkd_bag = document.getElementById("kd_bag").value;
  tipe = getValue("tipe");
  if (txt == "") {
    alert("Text is obligatory");
  } else if (txt.length < 1) {
    alert("Too short words");
  } else {
    param =
      "txtfind=" +
      txt +
      "&tipe=" +
      tipe +
      "&method=cariBarangDlmDtBs&rtgl_pp=" +
      rtgl_pp +
      "&rkd_bag=" +
      rkd_bag +
      "&nopp=" +
      nopp;
    param += "&kodebarang=" + kodebarang;
    param += "&noakun=" + noakun;
    tujuan = "log_slave_pp.php";
    post_response_text(tujuan, param, respog);
  }
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
function setBrg(
  no_brg,
  namabrg,
  satuan,
  stock,
  hargabarang,
  realisasi,
  qtybudget,
  baris
) {
  document.getElementById("infodt").style.display = "";
  document.getElementById("kd_brg").value = no_brg;
  document.getElementById("lblnamabarang").innerHTML = namabrg;
  document.getElementById("lblsatuan").innerHTML = satuan;
  document.getElementById("lblstok").innerHTML = stock;
  document.getElementById("lblhargasat").innerHTML = hargabarang;
  document.getElementById("lblrealisasi").innerHTML = realisasi;
  document.getElementById("lblbudget").innerHTML = qtybudget;
  document.getElementById("lblakunbgt").innerHTML = trim(
    document.getElementById("noakun" + baris).value
  );
  kd_vhc = document.getElementById("kd_vhc").value;
  if (kd_vhc != "") {
    document.getElementById("showdocument").style.display = "";
  }
  alertify.popup().destroy();
  // closeDialog();
  // getvhc(no_brg, nomor);
}
function reset_data() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  op = document.getElementById("method");
  if (stat_inputb == 0) {
    clear_all_data();
  } else if (stat_inputb == 1) {
    nopp = document.getElementById("detail_kode");
    nopp = nopp.innerHTML;
    param = "nopp=" + nopp;
    param += "&method=delete";
    tujuan = "log_slave_pp.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            clear_all_data();
            getPage2(paged);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    post_response_text(tujuan, param, respog);
  }
}
function cekdokumen() {
  nopp = document.getElementById("detail_kode").innerHTML;
  param = "nopp=" + nopp + "&method=cekdokumen";
  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText.split("####");
          if (data[0] > 0 && data[2] == 0) {
            alert(
              "Barang : " + data[1] + " harus ada upload dokument pendukung !"
            );
            return;
          }
          frm_aju();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function frm_aju() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  var tbl = document.getElementById("ppDetailTable");
  // var row = tbl.rows.length;
  // row=row-3;
  // min=-1;
  if (tbl === null) {
    alert("Please input the details");
    return false;
  } else {
    if (confirm("Process submission ??")) {
      document.getElementById("list_pp").style.display = "none";
      document.getElementById("form_pp").style.display = "none";
      document.getElementById("persetujuan").style.display = "block";
      nopp = document.getElementById("detail_kode").innerHTML;
      param = "nopp=" + nopp + "&method=formPersetujuan";
      tujuan = "log_slave_pp.php";
      function respog() {
        if (con.readyState == 4) {
          if (con.status == 200) {
            busy_off();
            if (!isSaveResponse(con.responseText)) {
              alert(con.responseText);
            } else {
              document.getElementById("persetujuandata").innerHTML =
                con.responseText;
            }
          } else {
            busy_off();
            error_catch(con.status);
          }
        }
      }
      post_response_text(tujuan, param, respog);
    } else {
      clear_all_data();
      getPage2(paged);
    }
  }
}
function searchVhc(title, content, ev) {
  kdbrg = document.getElementById("kd_brg").value;
  width = "";
  height = "";
  // showDialog1(title, content, width, height, ev);
  alertify.popup().destroy();
  alertify.popup(title,content).set({'resizable':true,'maximizable':false}).resizeTo('60%','70%');
  findVhc();
}
function addDetail() {
  methoddt = document.getElementById("methoddt").value;

  nopp = document.getElementById("detail_kode").innerHTML;
  kodebarang = document.getElementById("kd_brg").value;

  namabarang = document.getElementById("lblnamabarang").innerHTML;
  satuan = document.getElementById("lblsatuan").innerHTML;
  stok = document.getElementById("lblstok").innerHTML;
  hargasatuan = document.getElementById("lblhargasat").innerHTML;
  realisasi = document.getElementById("lblrealisasi").innerHTML;
  budget = document.getElementById("lblbudget").innerHTML;

  jmlhdiminta = document.getElementById("jmlhDiminta").value;
  prioritas = document.getElementById("prioritas").value;
  tglsdt = document.getElementById("tgl_sdt").value;
  kodevhc = document.getElementById("kd_vhc").value;
  kmhm = document.getElementById("kmhm").value;
  keterangan = document.getElementById("ket").value;
  kd_project = document.getElementById("kd_project").value;

  tglheader = trim(document.getElementById("tgl_pp").value);
  unit = trim(document.getElementById("kd_bag").value);
  tipe = trim(document.getElementById("tipe").value);
  noakunbudget = trim(document.getElementById("lblakunbgt").innerHTML);

  param = "method=" + methoddt;
  param += "&nopp=" + nopp;
  param += "&kodebarang=" + kodebarang;
  param += "&namabarang=" + namabarang;
  param += "&satuan=" + satuan;
  param += "&stok=" + stok;
  param += "&hargasatuan=" + hargasatuan;
  param += "&realisasi=" + realisasi;
  param += "&budget=" + budget;
  param += "&jmlhdiminta=" + jmlhdiminta;
  param += "&prioritas=" + prioritas;
  param += "&tglsdt=" + tglsdt;
  param += "&kodevhc=" + kodevhc;
  param += "&kmhm=" + kmhm;
  param += "&keterangan=" + keterangan;
  param += "&tglheader=" + tglheader;
  param += "&unit=" + unit;
  param += "&tipe=" + tipe;
  param += "&kd_project=" + kd_project;
  param += "&noakunbudget=" + noakunbudget;

  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respon);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          // swal(" ",con.responseText, "info");
          alert(con.responseText);
        } else {
          // Success Response
          loadlistprsr(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editdt(
  nopp,
  kdbrg,
  namabrg,
  satuan,
  stok,
  hargasatuan,
  realisasi,
  anggaran,
  jumlah,
  prioritas,
  tgl_sdt,
  kd_vhc,
  kmhm,
  ket,
  kd_project
) {
  document.getElementById("infodt").style.display = "";

  document.getElementById("kd_brg").value = kdbrg;
  document.getElementById("lblnamabarang").innerHTML = namabrg;
  document.getElementById("lblsatuan").innerHTML = satuan;
  document.getElementById("lblstok").innerHTML = stok;
  document.getElementById("lblhargasat").innerHTML = hargasatuan;
  document.getElementById("lblrealisasi").innerHTML = realisasi;
  document.getElementById("lblbudget").innerHTML = anggaran;

  document.getElementById("jmlhDiminta").value = jumlah;
  document.getElementById("kd_vhc").value = kd_vhc;
  document.getElementById("tgl_sdt").value = tgl_sdt;
  document.getElementById("ket").value = ket;
  document.getElementById("kmhm").value = kmhm;
  document.getElementById("kd_project").value = kd_project;

  document.getElementById("prioritas").value = prioritas;

  document.getElementById("imgbarang").style.display = "none";
  document.getElementById("methoddt").value = "updatedt";
  if (kdbrg != "" && kd_vhc != "") {
    //document.getElementById("showdocument").style.display = "";
  } else {
    document.getElementById("showdocument").style.display = "none";
  }
}
function cleardt() {
  document.getElementById("kd_brg").value = "";

  document.getElementById("lblnamabarang").innerHTML = "";
  document.getElementById("lblsatuan").innerHTML = "";
  document.getElementById("lblstok").innerHTML = "";
  document.getElementById("lblhargasat").innerHTML = "";
  document.getElementById("lblrealisasi").innerHTML = "";
  document.getElementById("lblbudget").innerHTML = "";
  document.getElementById("infodt").style.display = "none";

  document.getElementById("jmlhDiminta").value = "";
  document.getElementById("prioritas").selectedIndex = 0;
  // document.getElementById("tgl_sdt").value = getdatenow(2);
  document.getElementById("tgl_sdt").value = getdatenow();
  document.getElementById("kd_vhc").value = "";
  document.getElementById("kmhm").value = "";
  document.getElementById("ket").value = "";
  document.getElementById("kd_project").value = "";

  document.getElementById("imgbarang").style.display = "";
  document.getElementById("showdocument").style.display = "none";
  document.getElementById("methoddt").value = "insertdt";
  //alert(param);
}
function showdocuments(kd_vhc) {
  if (kd_vhc === undefined) {
    kd_vhc = document.getElementById("kd_vhc").value;
  } else {
    kd_vhc = kd_vhc;
  }
  alert(kd_vhc);
}
function deletedt(nopp, kd_brg) {
  param = "method=deletedt";
  param += "&nopp=" + nopp + "&kodebarang=" + kd_brg;
  tujuan = "log_slave_pp.php";
  if (confirm("Are You Sure Delete This Data?")) {
    post_response_text(tujuan, param, respon);
  }
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          loadlistprsr(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loadlistprsr(nopp) {
  param = "method=listprsr";
  param += "&nopp=" + nopp;
  tujuan = "log_slave_pp.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          document.getElementById("listprsr").innerHTML = con.responseText;
          cleardt();
          loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
/* Function switchEditAdd
 * Fungsi untuk mengganti image add menjadi edit dan keroconya
 * I : id nomor row
 * P : Image Add menjadi Edit
 * O : Image Edit
 */
function switchEditAdd(id, main) {
  if (typeof main != "undefined" && main == "main") {
    var idField = document.getElementById("add_" + id);
    var delImg = document.getElementById("delete_" + id);
    var passImg = document.getElementById("pass_" + id);
    var kode = document.getElementById("kode_" + id);
  } else {
    var idField = document.getElementById("detail_add_" + id);
    var delImg = document.getElementById("detail_delete_" + id);
  }
  if (idField) {
    idField.removeAttribute("id");
    idField.removeAttribute("name");
    idField.removeAttribute("onclick");
    idField.removeAttribute("src");
    idField.removeAttribute("title");
    // Set Edit Image Attr
    idField.setAttribute("title", "Edit");
    if (main == "main") {
      idField.setAttribute("id", "edit_" + id);
      idField.setAttribute("name", "edit_" + id);
      idField.setAttribute(
        "onclick",
        "editMain('" + id + "','kode','" + kode.value + "')"
      );
    } else {
      idField.setAttribute("id", "detail_edit_" + id);
      idField.setAttribute("name", "detail_edit_" + id);
      idField.setAttribute("onclick", "editDetail('" + id + "')");
    }
    idField.setAttribute("src", "images/save.png");
    delImg.setAttribute("class", "zImgBtn");
    delImg.setAttribute("title", "Hapus");
    if (main == "main") {
      delImg.setAttribute("name", "delete_" + id);
      delImg.setAttribute(
        "onclick",
        "deleteMain('" + id + "','kode','" + kode.value + "')"
      );
    } else {
      delImg.setAttribute("name", "detail_delete_" + id);
      delImg.setAttribute("onclick", "deleteDetail('" + id + "')");
    }
    delImg.setAttribute("src", "images/delete_32.png");
  } else {
    alert("DOM Definition Error: " + id);
  }
}
/* Function addNewRow
 * Fungsi untuk menambah row baru ke dalam table
 * I : id dari tbody tabel
 * P : Persiapan row dalam bentuk HTML
 * O : Tambahan row pada akhir tabel (append)
 */
function addNewRow(body, onDetail) {
  var tabBody = document.getElementById(body);
  if (onDetail) {
    var detail = onDetail;
  } else {
    var detail = false;
  }
  // Search Available numRow
  var numRow = 0;
  if (!detail) {
    while (document.getElementById("tr_" + numRow)) {
      numRow++;
    }
  } else {
    while (document.getElementById("detail_tr_" + numRow)) {
      numRow++;
    }
  }
  // Add New Row
  var newRow = document.createElement("tr");
  tabBody.appendChild(newRow);
  if (!detail) {
    newRow.setAttribute("id", "tr_" + numRow);
  } else {
    newRow.setAttribute("id", "detail_tr_" + numRow);
  }
  newRow.setAttribute("class", "rowcontent");
  if (!detail) {
    newRow.innerHTML +=
      "<td><input id='kode_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='matauang_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='simbol_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='kodeiso_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><img id='add_" +
      numRow +
      "' title='Tambah' class=zImgBtn onclick=\"addMain('" +
      numRow +
      "')\" src='images/plus.png'/>" +
      "&nbsp;<img id='delete_" +
      numRow +
      "' />" +
      "&nbsp;<img id='pass_" +
      numRow +
      "' />" +
      "</td>";
  } else {
    // Create Row
    newRow.innerHTML +=
      "<td><input id='kd_brg_" +
      numRow +
      "' type='text' class='myinputtext' style='width:120px' disabled='disabled' value='' /></td><td>" +
      "<input id='nm_brg_" +
      numRow +
      "' type='text' class='myinputtext' style='width:120px' disabled='disabled' value='' /><img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title='" +
      jdl_ats_0 +
      "' onclick=\"searchBrg('" +
      jdl_ats_1 +
      "','" +
      content_0 +
      "<input id=nomor type=hidden value=" +
      numRow +
      " />',event)\";></td><td><input id='sat_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px'disabled='disabled' value='' /></td>" +
      "<td style='display:none'><input id='kd_angrn_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' disabled='disabled' value='' /><input type=hidden id=oldKdbrg_" +
      numRow +
      " name=oldKdbrg_" +
      numRow +
      ">" +
      "<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title='" +
      jdl_bwh_0 +
      "' onclick=\"searchAngrn('" +
      jdl_bwh_1 +
      "','" +
      content_1 +
      "<input id=nomor type=hidden value=" +
      numRow +
      " />',event)\";>" +
      "<td><input id='kd_vhc_" +
      numRow +
      "' type='text' class='myinputtext' style='width:70px' disabled='disabled' value='' /><img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title='" +
      jdl_bwh_0 +
      "' onclick=\"searchVhc('" +
      jdl_bwhv_1 +
      "','" +
      contentv_1 +
      "<input id=nomor type=hidden value=" +
      numRow +
      " />','" +
      numRow +
      "',event)\";></td><td><input id='jmlhDiminta_" +
      numRow +
      "' type='text' class='myinputtextnumber' style='width:70px' value='' onkeypress='return angka_doang(event)' /></td>" +
      "<td><input type='text' style='width:70px' id='tgl_sdt_" +
      numRow +
      "' class='myinputtext' name='tgl_sdt_" +
      numRow +
      '\' maxlength="10" onmousemove="setCalendar(this.id)" onkeypress="return false;" ></td><td><input id=\'ket_' +
      numRow +
      "' type='text' class='myinputtext' style='width:130px' onkeypress='return tanpa_kutip(event)' value='' /></td>" +
      "<td><img id='detail_add_" +
      numRow +
      "' title='Tambah' class=zImgBtn onclick=\"addDetail('" +
      numRow +
      "')\" src='images/save.png'/>" +
      "&nbsp;<img id='detail_delete_" +
      numRow +
      "' />" +
      "&nbsp;<img id='detail_pass_" +
      numRow +
      "' />" +
      "</td>";
  }
}
function setVhc(no_vhc) {
	i   = document.getElementsByName("nama[]");
	e   = document.getElementsByName("check[]");
	val = document.getElementsByName("jlhpervhc[]");
	
	dtnm=""; jlh=0;
	data="";
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){
			//data[i[n].innerHTML]=val[n].value;
			
			data+=i[n].innerHTML+"="+val[n].value+",";
			jlh=jlh+parseFloat(val[n].value);
			if(parseFloat(val[n].value)==0 || parseFloat(val[n].value)==''){				
				alertify.alert("Ada kendaraan dengan jumlah masih kosong, proses dibatalkan."); return;
			}
		}
	}
	jmlhDiminta = trim(document.getElementById("jmlhDiminta").value);
	if(parseFloat(jmlhDiminta)!=jlh){
		alertify.alert("Jumlah tidak sesuai, proses dibatalkan."); return;
	}
	//mydata = JSON.stringify(Object.assign({}, data));
	//document.getElementById('kd_vhc').value = mydata;
	//console.log(mydata);
	alertify.popup().destroy();
	document.getElementById('kd_vhc').value = data.substr(0,data.length-1);
	// kd_brg = document.getElementById("kd_brg").value;
	// if (kd_brg != "") {
		// //document.getElementById("showdocument").style.display = "";
	// }
	// closeDialog();
}
function setpjct(no_project) {
  document.getElementById("kd_project").value = no_project;

  // closeDialog();
  alertify.popup().destroy();
}
function save_persetujuan() {
  nopp = trim(document.getElementById("fnopp").value);
  stat = trim(document.getElementById("cls_stat").value);
  kary = trim(document.getElementById("karywn_id").value);
  if (kary == "") {
    alert("Please verify  your selection");
  } else {
    method = "insert_persetujuan";
    param =
      "nopp=" +
      nopp +
      "&usr_id=" +
      kary +
      "&method=" +
      method +
      "&stat=" +
      stat;
    tujuan = "log_slave_pp.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("contain").innerHTML = con.responseText;
            showalllist();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  var answer = confirm("Are you sure?");
  if (answer) {
    post_response_text(tujuan, param, respog);
  } else {
    reset_data_setuju();
  }
}
function reset_data_setuju() {
  document.getElementById("persetujuan").style.display = "none";
  document.getElementById("form_pp").style.display = "none";
  document.getElementById("fnopp").value = "";
  document.getElementById("karywn_id").value = "";
  document.getElementById("list_pp").style.display = "block";
}
function frm_ajun(nopp, stat, totrow) {
  if (totrow == 0) {
    alert("Can`t update, No item in detail.");
    return false;
  }
  if (stat > 0) {
    alert("Can`t edit or delete while waiting for approval");
    return false;
  } else {
    document.getElementById("list_pp").style.display = "none";
    document.getElementById("form_pp").style.display = "none";
    document.getElementById("persetujuan").style.display = "block"; //ind
    document.getElementById("nopp").value = nopp;
  }
  param = "method=formPersetujuan" + "&nopp=" + nopp;
  tujuan = "log_slave_pp.php"; //alert(param);
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("persetujuandata").innerHTML =
            con.responseText;
          //getKar();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//search anggaran
function searchAngrn(title, content, ev) {
  width = "auto";
  height = "auto";
  showDialog1(title, content, width, height, ev);
  //alert('asdasd');
}
function findAngrn() {
  txt2 = trim(document.getElementById("no_angrn").value);
  if (txt2 == "") {
    alert("Text is obligatory");
  } else {
    param = "txtfind2=" + txt2;
    tujuan = "log_slave_get_brg.php";
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
function setAngrn(no_angrn) {
  var nomor = document.getElementById("nomor").value;
  document.getElementById("kd_angrn_" + nomor).value = no_angrn;
  //document.getElementById('nm_angrn_'+nomor).value=no_angrn;
  closeDialog();
}
function findVhc() {
  kdvhc = trim(document.getElementById("kd_vhc").value);
  txt3 = trim(document.getElementById("no_vhc").value);
  jumlah = trim(document.getElementById("jmlhDiminta").value);
  rkd_bag =document.getElementById("kd_bag").options[document.getElementById("kd_bag").selectedIndex].value;
  // if (txt3 == "") {
    // alert("Text is obligatory");
  // } else {
    param = "txtfind3=" + txt3 + "&rkd_bag=" + rkd_bag+ "&jumlah=" + jumlah+ "&kdvhc=" + kdvhc;
    tujuan = "log_slave_get_kodevhc.php";
    post_response_text(tujuan, param, respog);
  // }
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
function findproject() {
  txt3 = trim(document.getElementById("no_project").value);
  rkd_bag =
    document.getElementById("kd_bag").options[
      document.getElementById("kd_bag").selectedIndex
    ].value;
  if (txt3 == "") {
    alert("Text is obligatory");
  } else {
    param = "txtfind3=" + txt3 + "&rkd_bag=" + rkd_bag;
    tujuan = "log_slave_get_kodeproject.php";
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
function clear_data(id) {
  document.getElementById("nopp").value = "";
  document.getElementById("tgl_pp").value = "";
  document.getElementById("detail_pp").style.display = "none";
  document.getElementById("nopp").disabled = false;
  document.getElementById("tgl_pp").disabled = false;
  document.getElementById("kd_bag").disabled = false;
  document.getElementById("dtl_pem").disabled = false;
  stat_inputb = 0;
  stat_input = 0;
}
//Simpan data header
function simpanPerpem() {
  nopp = trim(document.getElementById("nopp").value);
  rtgl_pp = trim(document.getElementById("tgl_pp").value);
  rkd_bag = trim(
    document.getElementById("kd_bag").options[
      document.getElementById("kd_bag").sectionRowIndex
    ].value
  );
  id_user = trim(document.getElementById("user_id").value);
  method = document.getElementById("method").value;
  param =
    "nopp=" +
    nopp +
    "&rtgl_pp=" +
    rtgl_pp +
    "&rkd_bag=" +
    rkd_bag +
    "&usr_id=" +
    id_user; //+'&rkd_org='+rkd_org;
  param += "&method=" + method;
  //param+=strUrl;
  tujuan = "log_slave_save_log_pp.php";
  //alert(param);
  //alert(param);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          //document.getElementById('contain').innerHTML=con.responseText;
          //alert('Saved succeed !!');
          //clear_all_data();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function edit_header() {
  //alert(strUrl);
  stats = document.getElementById("method");
  if (stat_input == 1) {
    //	alert('edit');
    nopp = trim(document.getElementById("nopp").value);
    rtgl_pp = trim(document.getElementById("tgl_pp").value);
    rkd_bag = trim(document.getElementById("kd_bag").value);
    id_user = trim(document.getElementById("user_id").value);
    //rkd_org = trim(document.getElementById('kode_org').value);
    method = document.getElementById("method").value;
    param =
      "nopp=" +
      nopp +
      "&rtgl_pp=" +
      rtgl_pp +
      "&rkd_bag=" +
      rkd_bag +
      "&usr_id=" +
      id_user; //+'&rkd_org='+rkd_org;
    param += "&method=" + method;
    //param+=strUrl;
    tujuan = "log_slave_save_log_pp.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            //alert(con.responseText);
            document.getElementById("contain").innerHTML = con.responseText;
            //alert('Saved succeed !!');
            clear_all_data();
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    //post_response_text(tujuan, param, respog);
    var answer = confirm("Are you sure, Edit Header?");
    if (answer) {
      post_response_text(tujuan, param, respog);
    } else {
      clear_all_data();
    }
  } else if (stat_input == 0) {
    //alert('insert');
    if (stat_inputc == 0) {
      cek_data();
    } else {
      displayList();
    }
  }
}
function cek_data() {
  nopp = document.getElementById("detail_kode").value;
  rtgl_pp = trim(document.getElementById("tgl_pp").value);
  rkd_bag = trim(document.getElementById("kd_bag").value);
  id_user = trim(document.getElementById("user_id").value);
  met = document.getElementById("method").value = "cek_data_header";
  var tbl = document.getElementById("ppDetailTable");
  var row = tbl.rows.length;
  strUrl = "";
  for (i = 0; i < row; i++) {
    try {
      if (strUrl != "") {
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).value)
          ) +
          "&ketrng[]=" +
          encodeURIComponent(trim(document.getElementById("ket_" + i).value)) +
          "&rkd_angrn[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_angrn_" + i).value)
          ) +
          "&rkd_vhc[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_vhc_" + i).value)
          ) +
          "&rjmlhDiminta[]=" +
          encodeURIComponent(
            trim(document.getElementById("jmlhDiminta_" + i).value)
          ) +
          "&rtgl_sdt[]=" +
          encodeURIComponent(
            trim(document.getElementById("tgl_sdt_" + i).value)
          );
      } else {
        strUrl +=
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_brg_" + i).value)
          ) +
          "&ketrng[]=" +
          encodeURIComponent(trim(document.getElementById("ket_" + i).value)) +
          "&rkd_angrn[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_angrn_" + i).value)
          ) +
          "&rkd_vhc[]=" +
          encodeURIComponent(
            trim(document.getElementById("kd_vhc_" + i).value)
          ) +
          "&rjmlhDiminta[]=" +
          encodeURIComponent(
            trim(document.getElementById("jmlhDiminta_" + i).value)
          ) +
          "&rtgl_sdt[]=" +
          encodeURIComponent(
            trim(document.getElementById("tgl_sdt_" + i).value)
          );
      }
    } catch (e) {}
  }
  param =
    "cknopp=" +
    nopp +
    "&tgl_pp=" +
    rtgl_pp +
    "&kd_org=" +
    rkd_bag +
    "&user_id=" +
    id_user +
    "&proses=" +
    met;
  param += strUrl;
  tujuan = "log_slave_get_user_id_log_pp.php";
  //alert(param);
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
          var id = con.responseText;
          if (id == parseInt(id)) id = id - 1;
          else id = 0;
          switchEditAdd(id, "detail");
          addNewRow("detailBody", true);
          stat_inputc = 1;
          //document.getElementById('contain').innerHTML=con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  /*alert(param);
	return;*/
}
/* Function editDetail(id,primField,primVal)
 * Fungsi untuk mengubah data Detail
 * I : id row (urutan row pada table Detail)
 * P : Mengubah data pada tabel Detail
 * O : Notifikasi data telah berubah
 */
function editDetail(id) {
  //	alert('test');
  var detKode = document.getElementById("detail_kode");
  var rkd_brg = document.getElementById("kd_brg_" + id);
  var rkd_angrn = document.getElementById("kd_angrn_" + id);
  var rkd_vhc = document.getElementById("kd_vhc_" + id);
  var rjmlhDiminta = document.getElementById("jmlhDiminta_" + id);
  var rtgl_sdt = document.getElementById("tgl_sdt_" + id);
  var rket = document.getElementById("ket_" + id);
  a = rket.value;
  //if(a.length>50)
  //{
  //    alert('Keterangan melebihi 50 karakter');
  //    return;
  //}
  param = "proses=detail_edit";
  param += "&nopp=" + detKode.value;
  param += "&kd_brg=" + rkd_brg.value;
  param += "&kd_angrn=" + rkd_angrn.value;
  param += "&kd_vhc=" + rkd_vhc.value;
  param += "&jmlhDiminta=" + rjmlhDiminta.value;
  param += "&tgl_sdt=" + rtgl_sdt.value;
  param += "&ket=" + rket.value;
  if (document.getElementById("oldKdbrg_" + id).value != "") {
    var roldKdbrg = document.getElementById("oldKdbrg_" + id);
    param += "&oldKdbrg=" + roldKdbrg.value;
  }
  //alert(param);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          alert("Edit succeed");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text("log_slave_pp_detail.php", param, respon);
}
/* Function deleteDelete(id)
 * Fungsi untuk menghapus data Detail
 * I : id row (urutan row pada table Detail)
 * P : Menghapus data pada tabel Detail
 * O : Menghapus baris pada tabel Detail
 */
function deleteDetail(id) {
  var detKode = document.getElementById("detail_kode");
  var rkd_brg = document.getElementById("kd_brg_" + id);
  var rkd_angrn = document.getElementById("kd_angrn_" + id);
  var rkd_vhc = document.getElementById("kd_vhc_" + id);
  var rjmlhDiminta = document.getElementById("jmlhDiminta_" + id);
  var rtgl_sdt = document.getElementById("tgl_sdt_" + id);
  var rket = document.getElementById("ket_" + id);
  param = "proses=detail_delete";
  param += "&nopp=" + detKode.value;
  param += "&kd_brg=" + rkd_brg.value;
  param += "&kd_angrn=" + rkd_angrn.value;
  param += "&kd_vhc=" + rkd_vhc.value;
  param += "&jmlhDiminta=" + rjmlhDiminta.value;
  param += "&tgl_sdt=" + rtgl_sdt.value;
  param += "&ket=" + rket.value;
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          row = document.getElementById("detail_tr_" + id);
          if (row) {
            row.style.display = "none";
          } else {
            alert("Row undetected");
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  a = confirm("Are You Sure Delete This Data!!!");
  if (a) {
    //alert(param);
    //	return;
    post_response_text("log_slave_pp_detail.php", param, respon);
  } else {
    return;
  }
}
function cek_pembuat(nopp) {
  param = "nopp=" + nopp + "&method=cek_pembuat_pp";
  tujuan = "log_slave_save_log_pp.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
/*function frm_ajun(nopp,stat){
if(stat>0){
alert('Can`t edit or delete while waiting for approval');
return;
}
else{
document.getElementById('list_pp').style.display='none';
document.getElementById('form_pp').style.display='none';
document.getElementById('persetujuan').style.display='block';
document.getElementById('fnopp').value=nopp;
document.getElementById('cls_stat').value=stat;
}
}*/
function displayList() {
  document.getElementById("form_pp").style.display = "none";
  document.getElementById("list_pp").style.display = "block";
  document.getElementById("persetujuan").style.display = "none";
  document.getElementById("txtsearch").value = "";
  document.getElementById("tgl_cari").value = "";
  stat_input = 0;
  loadData();
}
function cariNopp() {
  document.getElementById("persetujuan").style.display = "none";
  txtSearch = trim(document.getElementById("txtsearch").value);
  tglCari = trim(document.getElementById("tgl_cari").value);
  met = document.getElementById("method");
  met = met.value = "cari_pp";
  met = trim(met);
  param = "txtSearch=" + txtSearch + "&tglCari=" + tglCari + "&method=" + met;
  tujuan = "log_slave_get_user_id_log_pp.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("form_pp").style.display = "none";
          document.getElementById("list_pp").style.display = "block";
          document.getElementById("contain").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
// function cariData(num)
// {
// document.getElementById('persetujuan').style.display='none';
// txtSearch=trim(document.getElementById('txtsearch').value);
// tglCari=trim(document.getElementById('tgl_cari').value);
// met=document.getElementById('method');
// met=met.value='cari_pp';
// met=trim(met);
// param='txtSearch='+txtSearch+'&tglCari='+tglCari+'&method='+met;
// param+='&page='+num;
// tujuan = 'log_slave_get_user_id_log_pp.php';
// post_response_text(tujuan, param, respog);
// function respog(){
// if (con.readyState == 4) {
// if (con.status == 200) {
// busy_off();
// if (!isSaveResponse(con.responseText)) {
// alert(con.responseText);
// }
// else {
// document.getElementById('contain').innerHTML=con.responseText;
// }
// }
// else {
// busy_off();
// error_catch(con.status);
// }
// }
// }
// }
function loadEmployeeList() {
  met = document.getElementById("method");
  met = met.value = "cari_pp";
  param = "method=" + met;
  tujuan = "log_slave_get_user_id_log_pp.php";
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
function cariBast(num) {
  param = "method=refresh_data";
  param += "&page=" + num;
  tujuan = "log_slave_save_log_pp.php";
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
function detailAnggaran(kdbrg, thnangrn, unit) {
  param =
    "method=getAnggaran" +
    "&kdBarang=" +
    kdbrg +
    "&thnAnggaran=" +
    thnangrn +
    "&unit=" +
    unit;
  tujuan = "log_slave_save_log_pp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("dtFormDetail").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function showdocsparepart(prddari, prdsampai, kdorg, kdvhc, ev) {
  if (kdvhc == "") {
    alert("Kode kendaraan tidak boleh kosong");
    return;
  }
  // width = "";
  // height = "";
  // content =
    // "<fieldset style='height:96%;width:98%';><div id=detailpengirmanblok  style='overflow:auto;height:100%;width:100%';></div></fieldset>";
  // ev = "event";
  // title = "Detail";
  // showDialog2(title, content, width, height, ev);
  param =
    "proses=preview" +
    "&prddari=" +
    prddari +
    "&prdsampai=" +
    prdsampai +
    "&kdorg=" +
    kdorg +
    "&kdvhc=" +
    kdvhc;
  tujuan = "vhc_slave_2pakaisparepart.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // document.getElementById("detailpengirmanblok").innerHTML = con.responseText;
		  alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function showdocpakaibarang(prddari, prdsampai, kdorg, barang, ev) {
  width = "";
  height = "";
  content =
    "<fieldset style='height:96%;width:97%';><legend>Pemakaian Material periode " +
    prddari +
    " s/d " +
    prdsampai +
    "</legend><div id=detailpakaibarang  style='overflow:auto;max-height:400px;max-width:900px';></div></fieldset>";
  title = "Detail";
  //showDialog2(title, content, width, height, ev);
  param =
    "proses=preview" +
    "&tgl1=" +
    prddari +
    "&tgl2=" +
    prdsampai +
    "&unit=" +
    kdorg +
    "&barang=" +
    barang;
  tujuan = "log_slave_2pemakaianbarang.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //document.getElementById('detailpakaibarang').innerHTML = con.responseText;
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
function showdocpembelianterakhir(prddari, prdsampai, kdorg, barang, ev) {
  width = "";
  height = "";
  content =
    "<fieldset style='height:96%;width:98.5%';><legend>Pembelian Terakhir Material periode " +
    prddari +
    " s/d " +
    prdsampai +
    "</legend><div id=detailpakaibarang  style='overflow:auto;max-height:400px';></div></fieldset>";
  title = "Detail";
  //showDialog(title, content, width, height, ev);
  param =
    "proses=preview" +
    "&tglDr=" +
    prddari +
    "&tanggalSampai=" +
    prdsampai +
    "&unit=" +
    kdorg +
    "&kdBrg=" +
    barang;
  tujuan = "log_slave_2detail_pembelian_brg.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //document.getElementById('detailpakaibarang').innerHTML = con.responseText;
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
function loadPPChat(nopp, kodebarang, ev) {
  title = "Chat:" + nopp + " - " + kodebarang;
  content =
    "<iframe frameborder=0 style='width:590px;height:490px;' src='log_slaveChatPP.php?nopp=" +
    nopp +
    "&kodebarang=" +
    kodebarang +
    "'></iframe>";
  width = "600";
  height = "450";
  showDialog2(title, content, width, height, ev);
}

function getjumlahpervhc(no){
	jumlah = trim(document.getElementById("jmlhDiminta").value);
	
	e = document.getElementById("check_"+no);
	if(e.checked==true){		
		document.getElementById("jlhpervhc"+no).disabled=false;
	}else{
		document.getElementById("jlhpervhc"+no).disabled=true;
		document.getElementById("jlhpervhc"+no).value="";
	}
}