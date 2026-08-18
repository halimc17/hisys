function detailcvmm(id) {
  param = "method=detail";
  param += "&id=" + id;
  param += "&tipeprint=html";
  tujuan = "sdm_slave_coreman.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          title = "Data Detail";
          tujuan = tujuan + "?" + param;
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
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pdfcvmm(id) {
  param = "method=detail";
  param += "&id=" + id;
  param += "&tipeprint=pdf";
  tujuan = "sdm_slave_coreman.php";

  tujuan = tujuan + "?" + param;
  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function approvedkpi(
  proses,
  jenispersetujuan,
  notransaksi,
  level,
  hasilpersetujuan,
  fromdata
) {
  alasan = trim(document.getElementById("alasan").value);
  param =
    "proses=" +
    proses +
    "&jenispersetujuan=" +
    jenispersetujuan +
    "&notransaksi=" +
    notransaksi +
    "&level=" +
    level +
    "&hasilpersetujuan=" +
    hasilpersetujuan +
    "&alasan=" +
    alasan;
  if (hasilpersetujuan == 1) {
    param += "&method=approved";
  }
  if (hasilpersetujuan == 2 || hasilpersetujuan == 3) {
    param += "&method=rejected";
  }
  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify.closeAll();
          getdetail(jenispersetujuan);
          alert("Success");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function formalasankpi(jenispersetujuan, notransaksi, hasilpersetujuan) {
  param =
    "method=formalasan&proses=" +
    jenispersetujuan +
    "&jenispersetujuan=" +
    jenispersetujuan +
    "&notransaksi=" +
    notransaksi +
    "&hasilpersetujuan=" +
    hasilpersetujuan;

  tujuan = "log_slave_approval.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify.popup().destroy();
          alertify
            .popup("", "<center>" + con.responseText + "</center>")
            .set({ resizable: true, maximizable: false })
            .resizeTo("500px", "200px");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function formrejectpas(id) {
  alertify.popup().destroy();
  content = "<table>";
  content += "<tr><td>Komentar :</td></tr>";
  content +=
    "<tr><td><textarea class=myinputtext style='width:300px;height:100px;' id=komentar></textarea></td></tr>";
  content +=
    "<tr><td align=center><button style=color:red;border-color:red; class=mybutton title='Reject' onclick=\"rejectpas(" +
    id +
    ');">Reject</button></td></tr>';
  content += "</table>";

  alertify
    .popup()
    .set({
      resizable: true,
      maximizable: false,
      message: content,
      title: "Reject ?",
    })
    .resizeTo("400px", "300px")
    .show();
}

function rejectpas(id) {
  komentar = document.getElementById("komentar").value;

  param = "method=reject";
  param += "&id=" + id;
  param += "&komentar=" + komentar;
  tujuan = "sdm_slave_pas.php";
  alertify.confirm(
    "Reject",
    "Anda yakin ???",
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
          alertify.popup().destroy();
          getdetail("PAS");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function approvepas(id) {
  param = "method=approve";
  param += "&id=" + id;
  tujuan = "sdm_slave_pas.php";
  alertify.confirm(
    "Approve",
    "Anda yakin ???",
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
          alertify.popup().destroy();
          getdetail("PAS");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailpas(karyawanid, thnnilai, penilaian) {
  param = "method=detail";
  param += "&tipeprint=html";
  param += "&karyawanid=" + karyawanid + "&tahun=" + thnnilai;
  param += "&penilaian=" + penilaian;
  tujuan = "sdm_slave_pas.php";

  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
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
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pdfpas(karyawanid, thnnilai, penilaian) {
  param = "method=detail";
  param += "&tipeprint=pdf";
  param += "&karyawanid=" + karyawanid + "&tahun=" + thnnilai;
  param += "&penilaian=" + penilaian;
  tujuan = "sdm_slave_pas.php";
  tujuan = tujuan + "?" + param;
  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function formrejectcvmm(id) {
  alertify.popup().destroy();
  content = "<table>";
  content += "<tr><td>Komentar :</td></tr>";
  content +=
    "<tr><td><textarea class=myinputtext style='width:300px;height:100px;' id=komentar></textarea></td></tr>";
  content +=
    "<tr><td align=center><button style=color:red;border-color:red; class=mybutton title='Reject' onclick=\"rejectcvmm(" +
    id +
    ');">Reject</button></td></tr>';
  content += "</table>";

  alertify
    .popup()
    .set({
      resizable: true,
      maximizable: false,
      message: content,
      title: "Reject ?",
    })
    .resizeTo("400px", "300px")
    .show();
}

function rejectcvmm(id) {
  komentar = document.getElementById("komentar").value;

  param = "method=reject";
  param += "&id=" + id;
  param += "&komentar=" + komentar;
  tujuan = "sdm_slave_coreman.php";
  alertify.confirm(
    "Reject",
    "Anda yakin ???",
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
          alertify.popup().destroy();
          getdetail("CVMM");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function approvecvmm(id) {
  param = "method=approve";
  param += "&id=" + id;
  tujuan = "sdm_slave_coreman.php";
  alertify.confirm(
    "Approve",
    "Anda yakin ???",
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
          getdetail("CVMM");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailcvmm(id) {
  param = "method=detail";
  param += "&id=" + id;
  param += "&tipeprint=html";
  tujuan = "sdm_slave_coreman.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          title = "Data Detail";
          tujuan = tujuan + "?" + param;
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
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pdfcvmm(id) {
  param = "method=detail";
  param += "&id=" + id;
  param += "&tipeprint=pdf";
  tujuan = "sdm_slave_coreman.php";

  tujuan = tujuan + "?" + param;
  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function formrejectkpi(idkpi) {
  alertify.popup().destroy();
  content = "<table>";
  content += "<tr><td>Komentar :</td></tr>";
  content +=
    "<tr><td><textarea class=myinputtext style='width:300px;height:100px;' id=komentar></textarea></td></tr>";
  content +=
    "<tr><td align=center><button style=color:red;border-color:red; class=mybutton title='Reject' onclick=\"rejectkpi(" +
    idkpi +
    ');">Reject</button></td></tr>';
  content += "</table>";

  //alertify.popup("Detail",content).set({'resizable':true,'maximizable':true}).resizeTo('400px','300px');

  alertify
    .popup()
    .set({
      resizable: true,
      maximizable: false,
      message: content,
      title: "Reject ?",
    })
    .resizeTo("400px", "300px")
    .show();
}

function rejectkpi(idkpi) {
  komentar = document.getElementById("komentar").value;

  param = "method=reject";
  param += "&idkpi=" + idkpi;
  param += "&komentar=" + komentar;
  tujuan = "sdm_slave_2kpi.php";
  alertify.confirm(
    "Reject",
    "Anda yakin ???",
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
          alertify.popup().destroy();
          getdetail("KPI");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function approvekpi(idkpi) {
  param = "method=approve";
  param += "&idkpi=" + idkpi;
  tujuan = "sdm_slave_2kpi.php";
  alertify.confirm(
    "Approve",
    "Anda yakin ???",
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
          alertify.popup().destroy();
          getdetail("KPI");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewfilekasbank(ev, namafile) {
  ext = namafile.split(".");
  if (
    trim(ext[1]) == "jpg" ||
    trim(ext[1]) == "jpeg" ||
    trim(ext[1]) == "png" ||
    trim(ext[1]) == "pdf"
  ) {
    param = "method=viewfilekasbank&namafile=" + namafile;
    tujuan = "keu_kasdanbank_slave.php";
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
          // document.getElementById('contview').innerHTML = con.responseText;
          alertify
            .popup2("Hasil Gambar", con.responseText)
            .set({ resizable: true, overflow: false })
            .resizeTo("400px", "400px");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewfile(idfile, sumber) {
  //formupload();
  if (sumber == "KASBANK") {
    param = "method=viewfile&idfile=" + idfile;
    tujuan = "keu_kasdanbank_slave.php";
  } else {
    param = "proses=viewfile&idfile=" + idfile;
    tujuan = "sdm_slave_lembur.php";
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

function detaillembur(periode, kary) {
  param = "proses=detaillembur" + "&periode=" + periode + "&kary=" + kary;
  tujuan = "sdm_slave_2laporanLembur_rekap.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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
function previewlbr(kodeorg, tanggal, id, kolom, kodeapproval) {
  param = "proses=preview";
  param += "&kodeorg=" + kodeorg;
  param += "&tanggal=" + tanggal;
  param += "&notransaksi=" + id;
  param += "&kolom=" + kolom;
  param += "&kodeapproval=" + kodeapproval;
  param += "&kodeapproval=" + kodeapproval;
  tujuan = "sdm_slave_lembur.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
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
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getdatapengajuanpnn(nopengajuan) {
  param = "";
  param += "&nopengajuan=" + nopengajuan;
  param += "&method=getdatapengajuan";

  tujuan = "kebun_slave_5premibasis.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.set("notifier", "position", "top-center");
          alertify.warning(con.responseText);
        } else {
          alertify
            .popup("Detail Approval nomor : " + nopengajuan, con.responseText)
            .set({
              resizable: true,
              maximizable: true,
            })
            .resizeTo("70%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getdatapengajuanpnnbr(nopengajuan) {
  param = "";
  param += "&nopengajuan=" + nopengajuan;
  param += "&method=getdatapengajuan";

  tujuan = "kebun_slave_5premikutipbrondolansaja.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.set("notifier", "position", "top-center");
          alertify.warning(con.responseText);
        } else {
          alertify
            .popup("Detail Approval nomor : " + nopengajuan, con.responseText)
            .set({
              resizable: true,
              maximizable: true,
            })
            .resizeTo("70%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getdatapengajuaneod(nopengajuan) {
  param = "";
  param += "&nopengajuan=" + nopengajuan;
  param += "&method=getdatapengajuan";

  tujuan = "setup_slave_validasiinput.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.set("notifier", "position", "top-center");
          alertify.warning(con.responseText);
        } else {
          alertify
            .popup("Detail", con.responseText)
            .set({
              resizable: true,
              maximizable: true,
            })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getdatapengajuan(nopengajuan) {
  param = "";
  param += "&nopengajuan=" + nopengajuan;
  param += "&method=getdatapengajuan";

  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.set("notifier", "position", "top-center");
          alertify.warning(con.responseText);
        } else {
          alertify
            .popup("Detail", con.responseText)
            .set({
              resizable: true,
              maximizable: true,
            })
            .resizeTo("830px", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function inserttolak_atbs(klm) {
  notransaksi = trim(document.getElementById("notransaksi").value);
  kodeapproval = document.getElementById("kodeapproval").value;
  met = "rejected";
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
    param += "&proses=" + kodeapproval;
    tujuan = "log_slave_approval.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
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

function tolak_atbs(id, kolom, kodeapproval) {
  prpopup(id, "Reject Form", "event");
  notransaksi = id;
  met = "form_rejected";
  param =
    "method=" +
    met +
    "&notransaksi=" +
    notransaksi +
    "&kolom=" +
    kolom +
    "&kodeapproval=" +
    kodeapproval;
  param += "&proses=" + kodeapproval;
  tujuan = "log_slave_approval.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

function nextapproval_atbs(tipe) {
  kolom = document.getElementById("kolom").value;
  kodeapproval = document.getElementById("kodeapproval").value;
  if (kodeapproval == "PNN" || kodeapproval == "PNNBR") {
    kodeorg = document.getElementById("kodeorgx").value;
  }
  comment = document.getElementById("comment_fr").value;
  notransaksi = document.getElementById("notransaksi").value;
  if (tipe != "approved") {
    userid = trim(document.getElementById("user_id").value);
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
  met = met.value = "approved";
  param =
    "comment=" +
    comment +
    "&method=" +
    met +
    "&notransaksi=" +
    notransaksi +
    "&kolom=" +
    kolom;
  if (kodeapproval == "PNN" || kodeapproval == "PNNBR") {
    param += "&kodeorg=" + kodeorg;
  }
  param += "&proses=" + kodeapproval;
  if (tipe != "approved") {
    param += "&userid=" + userid;
  }
  param += "&kodeapproval=" + kodeapproval;
  tujuan = "log_slave_approval.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Done");
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

function getdata_atbs(id, kolom, kodeapproval, kodeorg) {
  prpopup(id, "Approval Form", "event");
  notransaksi = id;
  met = "formalasan";
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
    kodeorg +
    "&proses=" +
    kodeapproval;
  tujuan = "log_slave_approval.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify.popup().destroy();
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

//Umar
function previewgrni(notransaksi, ev) {
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
}
function previewkontrak(notransaksi, ev) {
  param = "method=pdfpanjang&nokontrak=" + notransaksi;
  tujuan = "pmn_kontrakjual_slave.php";

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_kontrakjual_slave.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}
function previewbast(notransaksi, ev) {
	param = 'method=pdf&notransaksi=' + notransaksi;
	tujuan = 'pmn_bast_slave.php';
	alertify.popuppdf("PDF", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_bast_slave.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}

function previewdo(nodo, ev) {
  param = "method=pdf&nodo=" + nodo;
  tujuan = "pmn_slave_suratperintahpengiriman.php";

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_slave_suratperintahpengiriman.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function detailgrni(nodok, ev, kodebarang) {
  showformuploadgrni(ev);
  param =
    "method=showupload&notransaksi=" +
    nodok +
    "&kodebarang=" +
    kodebarang +
    "&fromapp=approval";
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

function showformuploadgrni(ev) {
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
  param =
    "method=loadfiles&notransaksi=" +
    nodok +
    "&kodebarang=" +
    kodebarang +
    "&fromapp=approval";
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
//End Umar

function loadPOChat(nopo, ev) {
  title = "Chat:" + nopo;
  content =
    "<iframe frameborder=0 style='width:510px;height:290px;' src='log_slaveChatPO.php?nopo=" +
    nopo +
    "'></iframe>";
  width = "";
  height = "";
  showDialog2(title, content, width, height, ev);
}
function pengajuansk(notransaksi, ev) {
  param = "notransaksi=" + notransaksi + "&method=pengajuansk";
  tujuan = "sdm_slave_savePromosi.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify
            .popuppdf(
              "PDF",
              "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_savePromosi.php?" +
                param +
                "'></iframe>"
            )
            .set({ resizable: true, overflow: false })
            .resizeTo("80%", "70%");
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
  document.getElementById("dynamic2").style.left = pos[0] + "px";
  document.getElementById("dynamic2").style.display = "";
}

function showimages(table, notransaksi, folder) {
  ev = "event";
  showformupload(ev);
  param =
    "method=showimages&notransaksi=" +
    notransaksi +
    "&folder=" +
    folder +
    "&table=" +
    table;
  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contUpload").innerHTML = con.responseText;
          loadfiles(table, notransaksi, folder);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewPdfcutistaff(notransaksi) {
  param = "method=pdf";
  param += "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_cutistaff.php?" + param;
  judul = "Report PDF " + notransaksi;
  ev = "event";
  closeDialog();
  alertify
    .popuppdf(
      judul,
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function previewcbs(tanggal, idjenis, kodeorg, tipekar) {
  param = "method=preview";
  param += "&tanggalx=" + tanggal;
  param += "&kom=" + idjenis;
  param += "&org=" + kodeorg;
  param += "&tipekar=" + tipekar;
  tujuan = "sdm_slave_3ctbs.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify.popup().destroy();
          alertify
            .popup("PREVIEW", "<center>" + con.responseText + "</center>")
            .set({ resizable: true, maximizable: false })
            .resizeTo("65%", "80%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewPdfcutinonstaff(notransaksi) {
  param = "method=pdf";
  param += "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_cutinonstaff.php?" + param;
  judul = "Report PDF " + notransaksi;
  ev = "event";
  closeDialog();
  alertify
    .popuppdf(
      judul,
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function showupload(ev, notransaksi, hidevalue) {
  showformupload(ev);
  param = "";
  param += "notransaksi=" + notransaksi + "&hidevalue=" + hidevalue;
  param += "&method=showupload";
  tujuan = "sdm_slave_cutistaff.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contUpload").innerHTML = con.responseText;
          loadfilescuti(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilescuti(notransaksi) {
  valuehidden = document.getElementById("valuehidden").value;

  param =
    "method=loadfiles&notransaksi=" +
    notransaksi +
    "&valuehidden=" +
    valuehidden;
  tujuan = "sdm_slave_cutistaff.php";
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

function loadfiles(table, notransaksi, folder) {
  param =
    "method=loadfiles&notransaksi=" +
    notransaksi +
    "&folder=" +
    folder +
    "&table=" +
    table;
  // alert(param);
  tujuan = "log_slave_approval.php";
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
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function form() {
  width = "";
  height = "";
  content =
    '<fieldset style="width:97%;"><div id=contview style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "View";
  showDialog5(title, content, width, height, ev);
}

function showalllist() {
  document.getElementById("crjenispersetujuan").selectedIndex = 0;
  loaddata();
}
function loaddata() {
  crjenispersetujuan =
    document.getElementById("crjenispersetujuan").options[
      document.getElementById("crjenispersetujuan").selectedIndex
    ].value;
  param = "method=loaddata&crjenispersetujuan=" + crjenispersetujuan;
  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("container").innerHTML = con.responseText;
          kembali();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function caripoxz() {
  crjenispersetujuan = "PO";
  nopo = document.getElementById("caripox").value;
  param = "method=getdetail&proses=" + crjenispersetujuan + "&nopoxz=" + nopo;
  tujuan = "log_slave_approval.php";
  //alert(param);
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("body1").style.display = "none";
          document.getElementById("body2").style.display = "";
          document.getElementById("body2").innerHTML = con.responseText;
          showontop();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getdetail(jenispersetujuan, num) {
  param = "method=getdetail&proses=" + jenispersetujuan;
  param += "&page=" + num;
  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("body1").style.display = "none";
          document.getElementById("body2").style.display = "";
          document.getElementById("body2").innerHTML = con.responseText;
          showontop();
          if (jenispersetujuan == "KASBANK") {
            historykasbank();
          }
          if (jenispersetujuan == "LBR") {
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

function getdetailsch(jenispersetujuan, num) {
  param = "method=getdetail&proses=" + jenispersetujuan;
  param += "&page=" + num;

  notransaksi = document.getElementById("notransaksisch");
  tanggal1 = document.getElementById("tanggalsch1");
  tanggal2 = document.getElementById("tanggalsch2");
  noakun = document.getElementById("noakunsch");
  tipetransaksi = document.getElementById("tipetransaksisch");
  supplier = document.getElementById("suppliersch");

  if (notransaksi) {
    param += "&notransaksi=" + notransaksi.value;
  }
  if (tanggal1) {
    param += "&tanggal1=" + tanggal1.value;
  }
  if (tanggal2) {
    param += "&tanggal2=" + tanggal2.value;
  }
  if (noakun) {
    param += "&noakun=" + noakun.value;
  }
  if (tipetransaksi) {
    param += "&tipetransaksi=" + tipetransaksi.value;
  }
  if (supplier) {
    param += "&supplier=" + supplier.value;
  }

  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("body1").style.display = "none";
          document.getElementById("body2").style.display = "";
          document.getElementById("body2").innerHTML = con.responseText;
          showontop();
          if (jenispersetujuan == "KASBANK") {
            historykasbank();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getPage(jenispersetujuan) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  getdetail(jenispersetujuan, paged);
}

function kembali() {
  document.getElementById("body1").style.display = "";
  document.getElementById("body2").style.display = "none";
}

//#### BEGIN ACTION PR ####
function prpreviewDetail(nopp, ev) {
  //prpopup(nopp, 'Purchase Request detail', ev);
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
          // document.getElementById('prcontainer').innerHTML = con.responseText;
          title = "Detail";
          alertify
            .popup(title, con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
          loadfilespr(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loadfilespr(nopp) {
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
          document.getElementById("listfilesview").innerHTML = con.responseText;
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
  width = "";
  height = "";
  content =
    "<fieldset style='height:96%;width:98%';><div id=detailpengirmanblok  style='overflow:auto;height:100%;width:100%';></div></fieldset>";
  ev = "event";
  title = "Detail";
  showDialog2(title, content, width, height, ev);
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
          document.getElementById("detailpengirmanblok").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function showdocpakaibarang(prddari, prdsampai, kdorg, barang, ev) {
  // width = '';
  // height = '';
  // content = "<fieldset style='height:96%;width:97%';><legend>Pemakaian Material periode " + prddari + " s/d " + prdsampai + "</legend><div id=detailpakaibarang  style='overflow:auto;max-height:400px;max-width:900px';></div></fieldset>";
  // ev = 'event';
  // title = "Detail";
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

          title = "Detail";
          alertify
            .popup2(title, con.responseText)
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
function close_pp() {
  kolom = document.getElementById("kolom").value;
  rnopp = trim(document.getElementById("rnopp").value);
  met = "insert_close_pp";
  comment_cls = trim(document.getElementById("note").value);
  param =
    "nopp=" +
    rnopp +
    "&method=" +
    met +
    "&comment=" +
    comment_cls +
    "&kolom=" +
    kolom;
  tujuan = "log_slave_persetujuan.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          getdetail("PR");
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
function prcek_status_pp(id) {
  var stat_pp = id;
  if (stat_pp == "" || stat_pp == 0) {
    alert("No decision");
    return false;
  } else if (stat_pp == 1) {
    alert("Approved");
    return false;
  } else if (stat_pp == 3) {
    alert("Rejected");
    return false;
  }
}
function prget_data_pp(id, kolom) {
  prpopup(id, "Approval Form", event);
  rnopp = id;
  met = "get_form_approval";
  param = "method=" + met + "&nopp=" + rnopp + "&kolom=" + kolom;
  tujuan = "log_slave_persetujuan.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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
function prrejected_pp(id, kolom) {
  prpopup(id, "Rejection Form", "event");
  rnopp = id;
  met = "get_form_rejected";
  param = "method=" + met + "&nopp=" + rnopp + "&kolom=" + kolom;
  tujuan = "log_get_detail_pp_persetujuan_pp.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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
function prrejected_some_proses(id, kolom) {
  prpopup(id, "Rejection Form", "event");
  rnopp = id;
  met = "get_form_rejected_some";
  param = "method=" + met + "&nopp=" + rnopp + "&kolom=" + kolom;
  tujuan = "log_slave_persetujuan.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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
function checkAlasan(bars) {
  txtstr = document.getElementById("alsnDtolak_" + bars).value;
  chkdt = document.getElementById("tolak_chk_" + bars);
  if (txtstr == "") {
    alert("Reason for rejection is obligatory!!");
    chkdt.checked = false;
    return;
  }
}
function rejected_some_done(id, kolom, totRow) {
  dnopp = id;
  strUrl2 = "";
  for (i = 1; i <= totRow; i++) {
    try {
      ckh = document.getElementById("tolak_chk_" + i);
      if (ckh.checked == true) {
        if (strUrl2 != "") {
          strUrl2 +=
            "&kode_brg[]=" +
            trim(document.getElementById("kd_brg_" + i).innerHTML) +
            "&alsan[]=" +
            encodeURIComponent(
              trim(document.getElementById("alsnDtolak_" + i).value)
            );
        } else {
          strUrl2 +=
            "&kode_brg[]=" +
            trim(document.getElementById("kd_brg_" + i).innerHTML) +
            "&alsan[]=" +
            encodeURIComponent(
              trim(document.getElementById("alsnDtolak_" + i).value)
            );
        }
      }
    } catch (e) {}
  }
  if (strUrl2 == "") {
    alert("please choose one item of material");
    return;
  }
  param = "nopp=" + dnopp + "&method=tolakBeberapa" + "&kolom=" + kolom;
  param += strUrl2;
  tujuan = "log_slave_persetujuan.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog();
          prget_data_pp(id, kolom);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function rejected_pp_proses(klm) {
  rnopp = trim(document.getElementById("rnopp").value);
  met = "rejected_pp_ex";
  kolom = klm;
  comment = trim(document.getElementById("cmnt_tolak").value);
  if (comment == "") {
    alert("Please leave a note");
  } else {
    param =
      "nopp=" +
      rnopp +
      "&method=" +
      met +
      "&comment=" +
      comment +
      "&kolom=" +
      kolom;
    tujuan = "log_slave_persetujuan.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            closeDialog();
            getdetail("PR");
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
function forward_pp() {
  kolom = document.getElementById("kolom").value;
  nik = trim(
    document.getElementById("user_id").options[
      document.getElementById("user_id").selectedIndex
    ].value
  );
  cmnt_hsl = document.getElementById("comment_fr").value;
  rnopp = document.getElementById("nopp").value;
  met = document.getElementById("method");
  if (cmnt_hsl == "" || nik == "") {
    alert("Please complete the form !");
    return;
  }
  document.getElementById("Ajukan").disabled = true;
  met = met.value = "insert_forward_pp";
  param =
    "userid=" +
    nik +
    "&comment=" +
    cmnt_hsl +
    "&method=" +
    met +
    "&nopp=" +
    rnopp +
    "&kolom=" +
    kolom;
  tujuan = "log_slave_persetujuan.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog();
          getdetail("PR");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function tambahBarang(nopp, title, ev) {
  content =
    '<div id=formBarang style="max-height:500px;width:100%;overflow:auto;"></div>';
  title = "Nopp : " + nopp;
  width = "";
  height = "";
  showDialog5(title, content, width, height, event);
  getListBarang(nopp);
}

function getListBarang(nopp) {
  param = "method=getListBarang" + "&nopp=" + nopp;
  tujuan = "log_slave_persetujuan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("formBarang").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveFormBarang(nopp, kodebarang, no) {
  jumlah = document.getElementById("jumlah" + no).value;
  kodebarangbaru = document.getElementById("kodebarangbaru" + no).value;
  jumlahbaru = document.getElementById("jumlahbaru" + no).value;

  hargaposebelumnyalama = document.getElementById(
    "hargaposebelumnyalama" + no
  ).value;
  hargaposebelumnyabaru = document.getElementById(
    "hargaposebelumnyabaru" + no
  ).value;

  param =
    "method=saveFormBarang" +
    "&jumlah=" +
    jumlah +
    "&nopp=" +
    nopp +
    "&kodebarang=" +
    kodebarang;
  param += "&kodebarangbaru=" + kodebarangbaru + "&jumlahbaru=" + jumlahbaru;
  param +=
    "&hargaposebelumnyalama=" +
    hargaposebelumnyalama +
    "&hargaposebelumnyabaru=" +
    hargaposebelumnyabaru;
  tujuan = "log_slave_persetujuan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          getListBarang(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//#### END ACTION PR ####
//### Daftar Supplier ####
function detailsupp(suppid) {
  width = "";
  height = "";
  content =
    '<div id=containerdsup style="width:100%;height:100%;overflow:auto"></div>';
  ev = "event";
  title = "View Supplier";
  showDialog1(title, content, width, height, ev);
  param = "method=detailsupp&idsupplier=" + suppid;
  tujuan = "log_slave_save_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerdsup").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//#### BEGIN ACTION MB ####
function viewDetailbarang(kodebarang, ev) {
  tujuan = "log_slave_material_picture_detail.php?kodebarang=" + kodebarang;
  content =
    "<iframe name=disPhotobarang src=" +
    tujuan +
    " frameborder=0 width=680px height=380px></iframe>";
  showDialog1("Detail:" + kodebarang, content, "700", "400", ev);
}
//#### END ACTION MB ####
//#### BEGIN ACTION CB ####
function previewcb(kode, ev) {
  param = "method=printpdf&kode=" + kode;
  showDialog1(
    "Print PDF",
    "<iframe frameborder=0 style='width:795px;height:395px' src='vhc_slave_capex.php?" +
      param +
      "'></iframe>",
    "",
    "",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}
function appeditcapex(notransaksi, ev) {
  width = "920";
  height = "";
  content =
    '<div id=containercb style="width:920px;max-height:700px;overflow:auto"></div>';
  ev = "event";
  title = "View Capex Bangunan";
  showDialog1(title, content, width, height, ev);
  param = "method=appeditcapex&kode=" + notransaksi;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containercb").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function checktender(level) {
  kodetender = document.getElementById("kodetender").value;
  var tbl = document.getElementById("containertender");
  var row = tbl.rows.length;
  ttlchk = 0;
  for (i = 1; i <= row; i++) {
    chk = document.getElementById("chk_" + i);
    if (chk.checked == true) {
      ttlchk = parseFloat(ttlchk) + 1;
      suppid = chk.value;
    }
  }
  if (ttlchk < 1) {
    alert("Pemenang belum ditentukan.");
    return false;
  }
  setujucapex("CB", "CB", kodetender, suppid, level, "1");
}
function setujucapex(
  proses,
  jenispersetujuan,
  notransaksi,
  suppid,
  level,
  hasilpersetujuan
) {
  alasan = trim(document.getElementById("alasan").value);
  if (alasan == "") {
    alert("Komentar harus diisi");
    return;
  }
  param =
    "proses=" +
    proses +
    "&jenispersetujuan=" +
    jenispersetujuan +
    "&notransaksi=" +
    notransaksi +
    "&level=" +
    level +
    "&hasilpersetujuan=" +
    hasilpersetujuan +
    "&alasan=" +
    alasan +
    "&suppid=" +
    suppid;
  if (hasilpersetujuan == 1) {
    param += "&method=approved";
    if (proses == "DISPO" && level == 4) {
      tanggaldispo = trim(document.getElementById("tanggaldispo").value);
      param += "&tanggaldispo=" + tanggaldispo;
    }
  }
  if (hasilpersetujuan == 2) {
    param += "&method=rejected";
  }
  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (con.responseText != "") {
            data = con.responseText.split("####");
            if (data[0] == "KASBANK") {
              //untuk membuat jurnal
              closeDialog();
              getdetail(jenispersetujuan);
              //kasbank(jenispersetujuan, data[1], data[2], data[3], data[4], data[5], data[6]);
            }
          } else {
            closeDialog();
            getdetail(jenispersetujuan);
            alert("Success");
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function addtendercapex2(notransaksi, ev) {
  width = "920";
  height = "";
  content =
    '<div id=containercb style="width:920px;max-height:700px;overflow:auto"></div>';
  ev = "event";
  title = "Penentuan Pemenang Tender Capex Bangunan";
  showDialog1(title, content, width, height, ev);
  param = "method=addtendercapex2&kode=" + notransaksi;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containercb").innerHTML = con.responseText;
          loadtender("loadtender2");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function addtendercapex(notransaksi, ev) {
  width = "920";
  height = "";
  content =
    '<div id=containercb style="width:920px;max-height:700px;overflow:auto"></div>';
  ev = "event";
  title = "Add Tender Capex Bangunan";
  showDialog1(title, content, width, height, ev);
  param = "method=addtendercapex&kode=" + notransaksi;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containercb").innerHTML = con.responseText;
          loadtender("loadtender");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function appshowcapex(notransaksi, suppid, ev) {
  width = "920";
  height = "";
  content =
    '<div id=containercbxx style="width:920px;max-height:700px;overflow:auto"></div>';
  ev = "event";
  title = "View Capex Bangunan";
  showDialog2(title, content, width, height, ev);
  param = "method=appshowcapex&kode=" + notransaksi + "&suppid=" + suppid;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containercbxx").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loadtender(method) {
  kodetender = document.getElementById("kodetender").value;
  param = "method=" + method + "&kodetender=" + kodetender;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containertender").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function simpantender() {
  kontraktor =
    document.getElementById("kontraktor").options[
      document.getElementById("kontraktor").selectedIndex
    ].value;
  kodetender = document.getElementById("kodetender").value;
  param =
    "method=simpantender&kontraktor=" +
    kontraktor +
    "&kodetender=" +
    kodetender;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containertender").innerHTML =
            con.responseText;
          loadtender();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function simpanappcapex(kode) {
  hargasatuan = document.getElementById("hargasatuan_" + kode).value;
  hk = document.getElementById("hk_" + kode).value;
  rupiahhk = document.getElementById("rupiahhk_" + kode).value;
  param =
    "method=simpanappcapex&kode=" +
    kode +
    "&hargasatuan=" +
    hargasatuan +
    "&hk=" +
    hk +
    "&rupiahhk=" +
    rupiahhk;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Success");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function hapusappcapex(kode, nama) {
  param = "method=hapusappcapex&kode=" + kode;
  tujuan = "vhc_slave_capex.php";
  if (confirm("Deleting item " + nama + ", are you sure..?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("trdet_" + kode).style.display = "none";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function saveappmat(kode, kodebarang) {
  jumlahmat = document.getElementById(
    "jumlahmat_" + kode + "_" + kodebarang
  ).value;
  hargamat = document.getElementById(
    "hargamat_" + kode + "_" + kodebarang
  ).value;
  param =
    "method=saveappmat&kode=" +
    kode +
    "&jumlahmat=" +
    jumlahmat +
    "&hargamat=" +
    hargamat +
    "&kodebarang=" +
    kodebarang;
  tujuan = "vhc_slave_capex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Success");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function hapusappmat(kode, kodebarang, nama) {
  param = "method=hapusappmat&kode=" + kode;
  tujuan = "vhc_slave_capex.php";
  if (confirm("Deleting item " + nama + ", are you sure..?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById(
            "trmat_" + kode + "_" + kodebarang
          ).style.display = "none";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//#### END ACTION CB ####
//#### BEGIN ACTION SCR ####
function previewscr(kode, ev) {
  param = "proses=printpdf&notransaksi=" + kode;
  showDialog1(
    "Print PDF",
    "<iframe frameborder=0 style='width:795px;height:395px' src='pmn_slave_scr.php?" +
      param +
      "'></iframe>",
    "",
    "",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}
//#### END ACTION SCR ####
//#### BEGIN ACTION GR ####
function previewgr(notransaksi, ev) {
  param = "notransaksi=" + notransaksi;
  tujuan = "log_slave_print_bapb_pdf.php?" + param;
  title = notransaksi;
  width = "800";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}
function detailgr(transaksi_detail, ev) {
  title = "Detail : " + transaksi_detail;
  width = "800";
  height = "400";
  formlistgr(title, width, height, ev);
  param = "transaksi_detail=" + transaksi_detail;
  tujuan = "log_penerimaanBarangDetail.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerAkun").innerHTML = con.responseText;
          loadfilesgr(transaksi_detail);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function formlistgr(title, wdth, heig, ev) {
  width = "";
  height = "";
  if (wdth != "") {
    width = wdth;
  }
  if (heig != "") {
    height = heig;
  }
  content = "<div id=containerAkun></div>";
  showDialog1(title, content, width, height, ev);
}
function loadfilesgr(notransaksi) {
  param = "method=loadfiles&notransaksi=" + notransaksi;
  tujuan = "log_slave_penerimaanUpload.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerAkundetail").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//#### END ACTION GR ####
//## START CU - PERMINTAAN BARANG ##
function form() {
  width = "920";
  height = "";
  content =
    '<div id=containerd style="width:100%;max-height:700px;overflow:auto;"></div>';
  ev = "event";
  title = "View";
  showDialog5(title, content, width, height, ev);
}
function viewdetailcu(nodok) {
  form();
  param = "method=view" + "&nodok=" + nodok;
  tujuan = "log_slave_pemakaianbarang.php";
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
// ## END CU - PERMINTAAN BARANG ##
function agree(ev, jenispersetujuan, hasilpersetujuan) {
  width = "";
  height = "";
  content = "<div id=containerform></div>";
  vtitle = "";
  if (
    jenispersetujuan == "JM" ||
    jenispersetujuan == "PKSMAINTENANCE" ||
    jenispersetujuan == "PKSCUCITANGKI"
  ) {
    if (hasilpersetujuan == "1") {
      vtitle = "(Setuju)";
    } else if (hasilpersetujuan == "2") {
      vtitle = "(Koreksi)";
    } else {
      vtitle = "(Tolak)";
    }
  } else {
    if (hasilpersetujuan == "1") {
      vtitle = "(Setuju)";
    } else if (hasilpersetujuan == "3") {
      vtitle = "(Koreksi)";
    } else {
      vtitle = "(Tolak)";
    }
  }
  title = "Approval Form " + vtitle;
  showDialogx(title, content, width, height, ev);
  document.getElementById("dynamicx").style.top = pos[1] + 10 + "px";
  document.getElementById("dynamicx").style.left = pos[0] + "px";
  document.getElementById("dynamicx").style.display = "";
}
function formalasan(
  proses,
  jenispersetujuan,
  notransaksi,
  level,
  hasilpersetujuan,
  ev,
  fromdata
) {
  agree(ev, jenispersetujuan, hasilpersetujuan);
  param =
    "method=formalasan&proses=" +
    proses +
    "&jenispersetujuan=" +
    jenispersetujuan +
    "&notransaksi=" +
    notransaksi +
    "&level=" +
    level +
    "&hasilpersetujuan=" +
    hasilpersetujuan;
  if (jenispersetujuan == "SPL") {
    param += "&fromdata=" + fromdata;
  }

  tujuan = "log_slave_approval.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerform").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function approved(
  proses,
  jenispersetujuan,
  notransaksi,
  level,
  hasilpersetujuan,
  fromdata,
  maxuntukpersetujuanphp
) {
  var Submit = document.getElementById("Submit");
  if (Submit) {
    document.getElementById("Submit").disabled = true;
  }

  alasan = trim(document.getElementById("alasan").value);
  param =
    "proses=" +
    proses +
    "&jenispersetujuan=" +
    jenispersetujuan +
    "&notransaksi=" +
    notransaksi +
    "&level=" +
    level +
    "&hasilpersetujuan=" +
    hasilpersetujuan +
    "&alasan=" +
    alasan;
  if (hasilpersetujuan == 1) {
    param += "&method=approved";
    if (proses == "DISPO" && level == 4) {
      tanggaldispo = trim(document.getElementById("tanggaldispo").value);
      param += "&tanggaldispo=" + tanggaldispo;
    }
    if (proses == "SPL") {
      param += "&fromdata=" + fromdata;
    }
    if (proses == "CPX") {
      totRow = document.getElementById("totrows").value;
      var allData = "";
      for (dwc = 0; dwc < totRow; dwc++) {
        allData +=
          "&kdbrg[" +
          dwc +
          "]=" +
          document.getElementById("kdbrg_" + dwc).value;
        allData +=
          "&kdasset[" +
          dwc +
          "]=" +
          document.getElementById("kdasset_" + dwc).value;
        allData +=
          "&subasset[" +
          dwc +
          "]=" +
          document.getElementById("subasset_" + dwc).value;
        allData +=
          "&nama[" + dwc + "]=" + document.getElementById("nama_" + dwc).value;
        allData +=
          "&jbiaya[" +
          dwc +
          "]=" +
          document.getElementById("jbiaya_" + dwc).value;
      }
      param += allData + "&totRow=" + totRow;
    }
    if (proses == "RFQ" || proses == "BAJS" || proses == "ADJ") {
      user_id = document.getElementById("user_id").value;
      nextlevelapp = trim(document.getElementById("nextlevelapp").value);
      param += "&user_id=" + user_id + "&nextlevelapp=" + nextlevelapp;
    }
    if (proses == "PHP") {
      ada = false;
      for (i = 1; i <= maxuntukpersetujuanphp; i++) {
        pilihradio = document.getElementById("pilihpemenang" + i);
        if (pilihradio.checked == true) {
          ada = true;
          param += "&pilihpemenang=" + i;
        }
      }
      if (ada == false) {
        alertify.alert("Informasi", "Harap memilih pemenang");
        return;
        false;
      }
    }
  }
  if (hasilpersetujuan == 2 || hasilpersetujuan == 3) {
    param += "&method=rejected";
  }
  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (trim(con.responseText) != "") {
            closeDialogx();
            loaddata();
            data = con.responseText.split("####");
            if (data[0] == "KASBANK") {
              //untuk membuat jurnal
              closeDialogx();
              getdetail(jenispersetujuan);
              //kasbank(jenispersetujuan, data[1], data[2], data[3], data[4], data[5], data[6]);
            }
          } else {
            closeDialogx();
            getdetail(jenispersetujuan);
            alert("Success");
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function previewPJD(nosk, ev) {
  param = "notransaksi=" + nosk;
  tujuan = "sdm_slave_printPJD_pdf.php?" + param;
  //display window
  title = nosk;
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}
function form() {
  width = "";
  height = "";
  content =
    "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
  ev = "event";
  title = "Detail HTML";
  showDialog1(title, content, width, height, ev);
}
function viewdetail(notrans, event, tipe) {
  form();
  param = "method=viewdetail" + "&notrans_cek=" + notrans + "&tipe=" + tipe;
  tujuan = "keu_slave_bukucek.php";
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

function viewdetailcapex(notrans) {
  form();
  param = "method=viewdetail" + "&notrans=" + notrans;
  tujuan = "log_slave_pengajuan_formcapex.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerd").innerHTML = con.responseText;
          loadfilescapex(notrans);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilescapex(notrans) {
  param = "method=loadfiles&notrans=" + notrans;
  tujuan = "log_slave_pengajuan_formcapex.php";
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
//pengajuan Training
function previewDetailForAll(file, paramtujuan, param) {
  if (paramtujuan !== "") {
    paramtujuan = "?" + paramtujuan;
  }
  if (param && param.nodeType === Node.ELEMENT_NODE) {
    var formElem = param.elements;
    param = "";
    for (i = 0; i < formElem.length; i++) {
      if (formElem[i].name !== "") {
        param += formElem[i].name + "=" + formElem[i].value + "&";
      }
    }
    param = param.slice(0, -1);
    console.log(param);
  }
  tujuan = file + paramtujuan;
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          try {
            var dataArr = JSON.parse(con.responseText);
            if (dataArr.err == "redirect") {
              eval(dataArr.redirect);
            } else {
              alert(dataArr.err);
            }
          } catch (e) {
            title = "Detail";
            width = "700";
            height = "400";
            showDialog2(title, con.responseText, width, height, event);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//#= untuk fungsi kasbank
function kasbank(
  jenispersetujuan,
  notrans,
  kodeorg,
  noakun,
  tipetransaksi,
  novoucher,
  tglpost
) {
  param =
    "notransaksi=" +
    notrans +
    "&kodeorg=" +
    kodeorg +
    "&noakun=" +
    noakun +
    "&tipetransaksi=" +
    tipetransaksi +
    "&novoucher=" +
    novoucher +
    "&tglpost=" +
    tglpost;
  post_response_text("keu_slave_kasbank_posting.php", param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog();
          getdetail(jenispersetujuan);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function pdfkasbank(notransaksi, kodeorg, noakun, tipetransaksi, ev) {
  param =
    "proses=pdfnew&notransaksi=" +
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
  // dialog.style.top = '50px';
  // dialog.style.left = '15%';
}

function detailkasbank(notransaksi, kodeorg, noakun, tipetransaksi, ev) {
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

function viewdetailsp(nopengajuan) {
  form();
  param = "method=viewdetail" + "&nopengajuan=" + nopengajuan;
  tujuan = "sdm_slave_pengajuansp.php";
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
function detailPDF(nojurnal, ev) {
  param = "mode=pdf" + "&nojurnal_=" + nojurnal + "&level=1";
  tujuan = "keu_slave_jurnal_print.php?" + param;
  title = nojurnal;
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog2(title, content, width, height, ev);
}
function detailPDFv2(nojurnal, kodeorg, ev) {
  param =
    "method=pdf" + "&nojurnal=" + nojurnal + "&level=1" + "&kodeorg=" + kodeorg;
  tujuan = "keu_jurnalmemorial_slave.php?" + param;
  title = nojurnal;
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog2(title, content, width, height, ev);
}
//======================== IOPS =============================//
function form() {
  width = "720";
  height = "";
  //nopp=document.getElementById('nopp_'+id).value;
  content =
    '<fieldset><div id=containerd align=center style="width:700px;max-height:700px;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "Detail HTML";
  showDialog5(title, content, width, height, ev);
}
function htmliops(notransaksi, kodeorg, tgl) {
  form();
  param =
    "method=html" +
    "&kodeorg=" +
    kodeorg +
    "&tgl=" +
    tgl +
    "&notransaksi=" +
    notransaksi;
  tujuan = "vhc_slave_byyijinops.php";
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

function getdataiops(id, kolom) {
  prpopup(id, "Approval Form", "event");
  notransaksi = id;
  met = "get_form_approval";
  param = "method=" + met + "&notransaksi=" + notransaksi + "&kolom=" + kolom;
  tujuan = "vhc_slave_approval_byyijinops.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

function nextapproval(tipe) {
  kolom = document.getElementById("kolom").value;
  comment = document.getElementById("comment_fr").value;
  notransaksi = document.getElementById("notransaksi").value;
  if (tipe != "approved") {
    userid = trim(
      document.getElementById("user_id").options[
        document.getElementById("user_id").selectedIndex
      ].value
    );
    if (comment == "" || userid == "") {
      alert("Please compleate the form !");
      return;
    }
  } else {
    if (comment == "") {
      alert("Please compleate the form !");
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
  tujuan = "vhc_slave_approval_byyijinops.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog();
          getdetail("IOPS");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function tolakiops(id, kolom) {
  prpopup(id, "Rejection Form", "event");
  notransaksi = id;
  met = "tolak";
  param = "method=" + met + "&notransaksi=" + notransaksi + "&kolom=" + kolom;
  tujuan = "vhc_slave_approval_byyijinops.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

function inserttolak(klm) {
  notransaksi = trim(document.getElementById("notransaksi").value);
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
      kolom;
    tujuan = "vhc_slave_approval_byyijinops.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            closeDialog();
            getdetail("IOPS");
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
//======================== IOPS =============================//

//========================PJDINAS============================//

function previewUMPJD(notransaksi, ev) {
  param = "notransaksi=" + notransaksi;
  tujuan = "sdm_slave_getPersetujuanPJDPreview.php?" + param;
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //content= "<div id=formBarang style=\"height:250px;width:350;overflow:scroll;\"></div>";
          title = notransaksi;
          width = "1000";
          height = "400";
          content = con.responseText;
          showDialog2(title, content, width, height, ev);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function byganti(notransaksi, bykel, bydet, no) {
  byrp = document.getElementById("byrp" + no).value;
  byrp = remove_comma_var(byrp);
  param =
    "method=byganti" +
    "&notransaksi=" +
    notransaksi +
    "&bykel=" +
    bykel +
    "&bydet=" +
    bydet +
    "&byrp=" +
    byrp;
  tujuan = "sdm_slave_bypersetujuanpjd.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // document.getElementById('container').innerHTML=con.responseText;
          // byloaddata();
          closeDialog();
          ev = "event";
          previewUMPJD(notransaksi, ev);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveUpdateValPJD() {
  newVal = document.getElementById("newvalpjd").value;
  notransaksi = document.getElementById("nitransaksipjd").value;

  param = "newvalpjd=" + newVal + "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_saveUpdateValPJD.php?" + param;
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("oldval").innerHTML = con.responseText;
          alert("Done");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//========================PJDINAS============================//

//========================SPL===============================//

function form() {
  width = "";
  height = "";
  content =
    "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
  ev = "event";
  title = "Detail HTML";
  showDialog1(title, content, width, height, ev);
}

function editfromapproval(kdorg, tgl, notransaksi, level) {
  form();
  param =
    "kdorg=" +
    kdorg +
    "&tgl=" +
    tgl +
    "&notransaksi=" +
    notransaksi +
    "&level=" +
    level;
  param += "&proses=editfromapproval";
  tujuan = "sdm_slave_splembur.php";
  function respon() {
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
  post_response_text(tujuan, param, respon);
}

function getUangLemulang(no) {
  basis = document.getElementById("jamlmbr_" + no).options[
    document.getElementById("jamlmbr_" + no).selectedIndex
  ].value;
  idKry = document.getElementById("kar_" + no).value;
  kodeOrg = document.getElementById("orgapp").value;
  tpeLmbr = document.getElementById("tpLembur_" + no).options[
    document.getElementById("tpLembur_" + no).selectedIndex
  ].value;
  tanggal = document.getElementById("tglapp").value;
  tahun = tanggal.substr(6, 4);
  param =
    "basisJam=" +
    basis +
    "&proses=getUang" +
    "&krywnId=" +
    idKry +
    "&kodeOrg=" +
    kodeOrg +
    "&tpLmbr=" +
    tpeLmbr +
    "&tahun=" +
    tahun +
    "&tgl=" +
    tanggal;
  tujuan = "sdm_slave_splembur.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("uang_lbh_" + no).value = con.responseText;
          updtjamulang(no);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function updtjamulang(no) {
  Jam = document.getElementById("jamlmbr_" + no).value;
  jammulai = document.getElementById("jam_mulai_" + no).value;

  param = "Jam=" + Jam + "&proses=updtjam";
  param += "&jammulai=" + jammulai;
  tujuan = "sdm_slave_splembur.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("jam_selesai_" + no).value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function checkAll() {
  totrow = document.getElementById("totrows").value;
  btn = document.getElementById("btnall");
  if (btn.checked == true) {
    chk = true;
  } else {
    chk = false;
  }

  for (i = 0; i < totrow; i++) {
    document.getElementById("no_" + i).checked = chk;
  }
}

function editdt(jenispersetujuan, notransaksi, level) {
  kdorg = document.getElementById("orgapp").value;
  tgl = document.getElementById("tglapp").value;
  totRow = document.getElementById("totrows").value;
  var allData = "";

  cekpil = 0;
  for (dwc = 0; dwc < totRow; dwc++) {
    allData += "&kar[]=" + document.getElementById("kar_" + dwc).value;
    allData +=
      "&tpLembur[]=" + document.getElementById("tpLembur_" + dwc).value;
    allData += "&jamlmbr[]=" + document.getElementById("jamlmbr_" + dwc).value;
    allData +=
      "&uang_lbh[]=" + document.getElementById("uang_lbh_" + dwc).value;
    allData +=
      "&jam_mulai[]=" + document.getElementById("jam_mulai_" + dwc).value;
    allData +=
      "&jam_selesai[]=" + document.getElementById("jam_selesai_" + dwc).value;
    allData +=
      "&keterangan[]=" + document.getElementById("keterangan_" + dwc).value;
    if (document.getElementById("no_" + dwc).checked == true) {
      statlembur = 1;
      cekpil += 1;
    } else {
      statlembur = 0;
    }
    allData += "&statlembur[]=" + statlembur;
  }

  if (cekpil == 0) {
    alert("Karyawan lembur belum terpilih.");
    return;
  }

  param =
    "kdorg=" + kdorg + "&tgl=" + tgl + "&proses=editdt" + "&totRow=" + totRow;
  param += allData;
  tujuan = "sdm_slave_splembur.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog();
          hasilpersetujuan = 1;
          fromdata = 1;
          formalasan(
            jenispersetujuan,
            jenispersetujuan,
            notransaksi,
            level,
            hasilpersetujuan,
            fromdata
          );
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//========================SPL===============================//

//========================EFIL===============================//
function viewefill(notransaksi, showhideefil, ev) {
  content = '<div id=formviewefill  style="height:100%;"></div>';
  title = "View Efilling System";
  height = "";
  width = "";
  showDialog5(title, content, width, height, ev);
  showefil(notransaksi, showhideefil);
}

function showefil(notransaksi, showhideefil) {
  param =
    "method=viewefill&notransaksi=" +
    notransaksi +
    "&showhideefil=" +
    showhideefil;
  tujuan = "keu_slave_efill.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

//========================HISTORY APPROVAL KASBANK===============================//

function cancelhistorykasbank() {
  document.getElementById("notransaksihis").value = "";
  document.getElementById("tanggal1his").value = "";
  document.getElementById("tanggal2his").value = "";
  document.getElementById("noakunhis").value = "";
  document.getElementById("tipetransaksihis").value = "";
  document.getElementById("supplierhis").value = "";
  historykasbank(0);
}

function historykasbank(pg) {
  param = "method=historykasbank";
  param += "&page=" + pg;

  notransaksihis = document.getElementById("notransaksihis");
  tanggal1his = document.getElementById("tanggal1his");
  tanggal2his = document.getElementById("tanggal2his");
  noakunhis = document.getElementById("noakunhis");
  tipetransaksihis = document.getElementById("tipetransaksihis");
  supplierhis = document.getElementById("supplierhis");

  if (notransaksihis) {
    param += "&notransaksihis=" + notransaksihis.value;
  }
  if (tanggal1his) {
    param += "&tanggal1his=" + tanggal1his.value;
  }
  if (tanggal2his) {
    param += "&tanggal2his=" + tanggal2his.value;
  }
  if (noakunhis) {
    param += "&noakunhis=" + noakunhis.value;
  }
  if (tipetransaksihis) {
    param += "&tipetransaksihis=" + tipetransaksihis.value;
  }
  if (supplierhis) {
    param += "&supplierhis=" + supplierhis.value;
  }

  tujuan = "log_slave_approval.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("historykasbank").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getPageHistoryKasbank() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  historykasbank(paged);
}

//========================DETAIL BONUS TBS===============================//
function detailbonustbs(notransaksi, kdmill, tgl, suppId, ev) {
  param = "method=detaildata" + "&notransaksi=" + notransaksi;
  param += "&millcode=" + kdmill + "&tglnormal=" + tgl + "&suppId=" + suppId;
  title = "Data Detail";
  showDialog1(
    title,
    "<iframe frameborder=0 style='width:1045px;height:395px'" +
      " src='keu_slave_penbytbs.php?" +
      param +
      "'></iframe>",
    "1050",
    "400",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}

//GRL
function html(notransaksi, kodeorg, periode) {
  form();
  param =
    "method=html" +
    "&kodeorg=" +
    kodeorg +
    "&periode=" +
    periode +
    "&notransaksi=" +
    notransaksi;
  tujuan = "lgl_slave_pengajuanpembebasanlahan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerd").innerHTML = con.responseText;
          loadfilesgrl(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilesgrl(notransaksi) {
  param = "method=loadfiles&notransaksi=" + notransaksi;
  tujuan = "lgl_slave_pengajuanpembebasanlahan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          console.log(con.responseText);
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

function previewERF(notransaksi, ev) {
  param = "method=preview&notransaksi=" + notransaksi;
  tujuan = "sdm_req_employee_slave.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          title = "Detail Permintaan Karyawan";
          width = "";
          height = "";
          ev = "event";
          content =
            "<fieldset style=max-width:600px><legend><b>" +
            notransaksi +
            "</b></legend>" +
            con.responseText +
            "</fieldset>";
          closeDialog();
          showDialog2(title, content, width, height, ev);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewbajs(notransaksi, ev, sumber, noba) {
  width = "";
  height = "";
  content =
    "<fieldset><div id=popuppreview style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
  title = "&nbsp;&nbsp;Preview";
  showDialog1(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic1").style.top = pos[1] + "px";
  document.getElementById("dynamic1").style.left = pos[0] - 800 + "px";

  param =
    "method=preview&notransaksi=" +
    notransaksi +
    "&sumber=" +
    sumber +
    "&noba=" +
    noba;
  tujuan = "log_slave_bakontrakjasa.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("popuppreview").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function pdf(notransaksi) {
  param = "method=pdf" + "&notransaksi=" + notransaksi;
  tujuan = "log_slave_approval.php";
  tujuan = tujuan + "?" + param;
  content =
    "<iframe frameborder=0 style='width:100%;height:99%' src='" +
    tujuan +
    "'></iframe>";
  width = "820";
  height = "500";
  title = "";
  showDialog5(title, content, width, height, "event");
}

function pdf2(notransaksi) {
  param = "method=pdf2" + "&notransaksi=" + notransaksi;
  tujuan = "log_slave_approval.php";
  tujuan = tujuan + "?" + param;
  content =
    "<iframe frameborder=0 style='width:100%;height:99%' src='" +
    tujuan +
    "'></iframe>";
  width = "820";
  height = "500";
  title = "";
  showDialog5(title, content, width, height, "event");
}

function pdfFTBS(notrans) {
  param = "method=pdf";
  param += "&notrans=" + notrans;
  tujuan = "pmn_slave_feetbs.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          title = "Data Detail : " + notrans;
          tujuan = tujuan + "?" + param;
          alertify
            .popup(
              title,
              "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
                tujuan +
                "'></iframe>"
            )
            .set({ resizable: true, overflow: false })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function htmlsvc(notransaksi) {
  param = "proses=html" + "&trans_no=" + notransaksi;
  tujuan = "vhc_slave_service.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Info", con.responseText);
        } else {
          alertify
            .popup()
            .set({
              title: "Detail",
              resizable: true,
              maximizable: true,
              startMaximized: false,
              message: con.responseText,
            })
            .resizeTo("65%", "90%")
            .show();
          // loadfilesspksvc(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function phitungtax(max) {
  pajak = getValue("ptax");
  luas = getValue("pluas");
  rpsat = remove_comma_var(getValue("prpsat"));

  taxrpsat = (parseFloat(rpsat) * parseFloat(pajak)) / 100;
  for (let i = 1; i <= max; i++) {
    rpsatoff = remove_comma_var(getValue("prpsatoff_" + i));
    rpoff = remove_comma_var(getValue("prupiahoff_" + i));
    rptaxoff = parseFloat(rpsatoff) * parseFloat(luas);
    rupiahtaxoff = (parseFloat(rptaxoff) * parseFloat(pajak)) / 100;
    fixrupiahoff = parseFloat(rpoff) - parseFloat(rupiahtaxoff);
    fixrpsatoff = parseFloat(fixrupiahoff) / parseFloat(luas);

    if (pajak == "" || pajak == 0) {
      setValue("ptaxrupiahoff_" + i, "");
      setValue("pfixrupiahoff_" + i, "");
      setValue("pfixrpsatoff_" + i, "");
    } else {
      if (
        isNaN(rupiahtaxoff) == true ||
        isNaN(fixrupiahoff) == true ||
        isNaN(fixrpsatoff) == true
      ) {
        rupiahtaxoff = "";
        fixrupiahoff = "";
        fixrpsatoff = "";
      } else {
        rupiahtaxoff = rupiahtaxoff;
        fixrupiahoff = fixrupiahoff;
        fixrpsatoff = fixrpsatoff;
      }
      setValue("ptaxrupiahoff_" + i, numberFormat(rupiahtaxoff));
      setValue("pfixrupiahoff_" + i, numberFormat(fixrupiahoff));
      setValue("pfixrpsatoff_" + i, numberFormat(fixrpsatoff));
    }

    rpsatnego = remove_comma_var(getValue("prpsatnego_" + i));
    rpnego = remove_comma_var(getValue("prupiahnego_" + i));
    rptaxnego = parseFloat(rpsatnego) * parseFloat(luas);
    rupiahtaxnego = (parseFloat(rptaxnego) * parseFloat(pajak)) / 100;

    fixrupiahnego = parseFloat(rpnego) - parseFloat(rupiahtaxnego);
    fixrpsatnego = parseFloat(fixrupiahnego) / parseFloat(luas);

    if (pajak == "" || pajak == 0) {
      setValue("ptaxrupiahnego_" + i, "");
      setValue("pfixrupiahnego_" + i, "");
      setValue("pfixrpsatnego_" + i, "");
    } else {
      if (
        isNaN(rupiahtaxnego) == true ||
        isNaN(fixrupiahnego) == true ||
        isNaN(fixrpsatnego) == true
      ) {
        rupiahtaxnego = "";
        fixrupiahnego = "";
        fixrpsatnego = "";
      }
      setValue("ptaxrupiahnego_" + i, numberFormat(rupiahtaxnego));
      setValue("pfixrupiahnego_" + i, numberFormat(fixrupiahnego));
      setValue("pfixrpsatnego_" + i, numberFormat(fixrpsatnego));
    }

    //Perhitungan Varian
    varian = 0;
    varian = parseFloat(rpoff) - parseFloat(rpnego);
    if (isNaN(varian) == true) {
      varian = "";
    } else {
      varian = numberFormat(varian);
    }
    document.getElementById("pvarrp_" + i).innerHTML = varian;

    //Perhitungan Varian sebelumpajak
    taxvarian = 0;
    taxvarian = parseFloat(fixrupiahoff) - parseFloat(fixrupiahnego);
    if (isNaN(taxvarian) == true) {
      taxvarian = "";
    } else {
      taxvarian = numberFormat(taxvarian);
    }
    document.getElementById("pfixvarrp_" + i).innerHTML = taxvarian;
  }
}
