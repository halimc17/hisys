function prpopup(noPP, title, ev) {
  width = "";
  height = "";
  content =
    "<fieldset><legend>" +
    noPP +
    "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" +
    noPP +
    " />";
  showDialog1(title, content, width, height, ev);
}

function viewlistfile(pt, jenis, kodeijin, noijin) {
  width = "";
  height = "";
  content =
    '<fieldset style="width:97%;"><div id=contviewz style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "View";
  showDialog4(title, content, width, height, ev);

  param =
    "method=viewlistfile&jenis=" +
    jenis +
    "&pt=" +
    pt +
    "&kodeijin=" +
    kodeijin +
    "&noijin=" +
    noijin;
  tujuan = "log_slave_approval_perjalanan_dinas.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          document.getElementById("contviewz").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewlistfile1(pt, jenisakta, noskhakim, kelompok, tanggalakta) {
  width = "";
  height = "";
  content =
    '<fieldset style="width:97%;"><div id=contviewz style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "View";
  showDialog4(title, content, width, height, ev);

  param =
    "method=viewlistfile&jenisakta=" +
    jenisakta +
    "&pt=" +
    pt +
    "&noskhakim=" +
    noskhakim +
    "&kelompok=" +
    kelompok;
  tujuan = "lgl_slave_anggarandasar.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          document.getElementById("contviewz").innerHTML = con.responseText;
          loadfiles1(pt, jenisakta, noskhakim, kelompok, tanggalakta);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfiles1(pt, jenisakta, noskhakim, kelompok, tanggalakta) {
  param = "method=loadfiles&pt=" + pt;
  param += "&jenisakta=" + jenisakta;
  param += "&kelompok=" + kelompok;
  param += "&noskhakim=" + noskhakim;
  param += "&tanggalakta=" + tanggalakta;
  tujuan = "lgl_slave_anggarandasar.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
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

function detaildatapjdinas(notransaksi, ev, jenis) {
  width = 1024;
  height = 400;

  content =
    '<fieldset style=width:98%><div id=containerd style="height:385px;width:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "Preview";
  showDialog4(title, content, width, height, ev);

  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

function viewfilepjdinas(ev, namafile) {
  ext = namafile.split(".");
  if (
    trim(ext[1]) == "jpg" ||
    trim(ext[1]) == "jpeg" ||
    trim(ext[1]) == "png"
  ) {
    form();
    param = "method=viewfilepjdinas&namafile=" + namafile;
    tujuan = "sdm_slave_pjdx.php";
    post_response_text(tujuan, param, respog);
  } else {
    alertify.alert(
      "File tidak dapat di tampilkan, silahkan download untuk melihat isi file."
    );
    return;
  }
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

function detailpdfpjdinas(notransaksi, ev, jenis) {
  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php" + "?" + param;
  width = 1024;
  height = 400;
  ev = "event";
  title = "Preview";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function getdataperjalanandinas(id, kolom, kodeapproval, kodeorg) {
  prpopup(id, "Approval Form", "event");
  notransaksi = id;
  met = "get_form_approval";
  param =
    "method=" +
    met +
    "&notransaksi=" +
    notransaksi +
    "&kolom=" +
    kolom +
    "&kodeapproval=" +
    kodeapproval +
    "&kodeorg=" +
    kodeorg;
  tujuan = "log_slave_approval_perjalanan_dinas.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          if (con.responseText == "") {
            document.getElementById("prcontainer").innerHTML =
              "You are not registred in the list";
          } else {
            document.getElementById("prcontainer").innerHTML =
              "<input type=hidden id=kolom value=" +
              kolom +
              ">" +
              con.responseText;
            return con.responseText;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function nextapprovalperjalanandinas(tipe) {
  kolom = document.getElementById("kolom").value;
  comment = document.getElementById("comment_fr").value;
  notransaksi = document.getElementById("notransaksi").value;
  kodeapproval = document.getElementById("kodeapproval").value;
  if (tipe != "approved") {
    userid = trim(
      document.getElementById("user_id").options[
        document.getElementById("user_id").selectedIndex
      ].value
    );
    if (comment == "" || userid == "") {
      alert("Nama karyawan dan catatan wajib diisi.");
      return;
    }
  } else {
    if (comment == "") {
      alert("Catatan wajib diisi.");
      return;
    }
  }
  document.getElementById("Ajukan").disabled = true;
  met = met.value = "insert_nextapproval";
  param =
    "comment=" +
    comment +
    "&method=" +
    met +
    "&notransaksi=" +
    notransaksi +
    "&kolom=" +
    kolom;
  if (tipe != "approved") {
    param += "&userid=" + userid;
  }
  param += "&kodeapproval=" + kodeapproval;
  tujuan = "log_slave_approval_perjalanan_dinas.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          alert("Done");
          getdetail(kodeapproval);
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function tolakperjalanandinas(id, kolom, kodeapproval) {
  prpopup(id, "Rejection Form", "event");
  notransaksi = id;
  met = "tolak";
  param =
    "method=" +
    met +
    "&notransaksi=" +
    notransaksi +
    "&kolom=" +
    kolom +
    "&kodeapproval=" +
    kodeapproval;
  tujuan = "log_slave_approval_perjalanan_dinas.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          document.getElementById("prcontainer").innerHTML =
            "<input type=hidden id=kolom value=" +
            kolom +
            ">" +
            con.responseText;
          return con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function inserttolakperjalanandinas(klm) {
  notransaksi = trim(document.getElementById("notransaksi").value);
  kodeapproval = document.getElementById("kodeapproval").value;
  met = "inserttolak";
  kolom = klm;
  comment = trim(document.getElementById("cmnt_tolak").value);
  if (comment == "") {
    alert("Please leave a note");
  } else {
    param =
      "notransaksi=" +
      notransaksi +
      "&method=" +
      met +
      "&comment=" +
      comment +
      "&kolom=" +
      kolom +
      "&kodeapproval=" +
      kodeapproval;
    tujuan = "log_slave_approval_perjalanan_dinas.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            closeDialog();
            getdetail(kodeapproval);
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
