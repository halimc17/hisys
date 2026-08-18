function createNew() {
  getnotransaksi();
  document.getElementById("addNew").style.display = "block";
  document.getElementById("listData").style.display = "none";
  document.getElementById("method").value = "insert";
  hapus();
}

function displayList() {
  hapus();
  document.getElementById("addNew").style.display = "none";
  document.getElementById("listData").style.display = "block";
  loadData(0);
}

function hapus() {
  document.getElementById("notransaksisch").value = "";
  document.getElementById("namajabatan").value = "";
  document.getElementById("jumlahpekerjasekarang").value = "";
  document.getElementById("jumlahpekerjadibutuhkan").value = "";
  document.getElementById("departemen").value = "";
  document.getElementById("alasan").value = "";
  document.getElementById("statuskaryawan").value = "";
  document.getElementById("mulaibekerja").value = "";
  document.getElementById("golongan").value = "";
  document.getElementById("pendidikanminimal").value = "";
  document.getElementById("pengalamanminimal").value = "";
  document.getElementById("uraiankerja").value = "";
  document.getElementById("kualifikasi").value = "";
  document.getElementById("sertifikasi").value = "";
  document.getElementById("jeniskelamin").value = "";
  document.getElementById("statuspernikahan").value = "";
  document.getElementById("bidangpengalaman").value = "";
  document.getElementById("jeniskelamin").value = "";
  document.getElementById("note").value = "";
  document.getElementById("usiamin").value = "";
  document.getElementById("usiamax").value = "";
  document.getElementById("method").value = "insert";

  document.getElementById("alasanganti").value = "";
  document.getElementById("alasanganti").style.display = "none";

  document.querySelectorAll('input[name="jenis_tes[]"]').forEach(cb => cb.checked = false);
  document.querySelectorAll('input[name="jenis_interview[]"]').forEach(cb => cb.checked = false);

  closeDialog();
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loadData(paged);
}

function loadData(num) {
  notransaksisch = document.getElementById("notransaksisch").value;
  // tanggalsch      = document.getElementById('tanggalsch').value;
  // kodeorgsch      = document.getElementById('kodeorgsch').value;
  // statussch       = document.getElementById('statussch').value;

  param = "method=loadData&page=" + num;
  // if(notransaksisch != '' && tanggalsch != '' && kodeorgsch != '' && statussch != ''){
  param += "&notransaksisch=" + notransaksisch;
  //     param  +='&tanggalsch=' + tanggalsch;
  //     param  +='&kodeorgsch=' + kodeorgsch;
  //     param  +='&statussch=' + statussch;
  // }else if(tanggalsch != ''){
  //     param  +='&tanggalsch=' + tanggalsch;
  // }else if(kodeorgsch != ''){
  //     param  +='&kodeorgsch=' + kodeorgsch;
  // }else if(statussch != ''){
  //     param  +='&statussch=' + statussch;
  // }
  tujuan = "sdm_req_employee_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          dataSlave = con.responseText.split("####");
          document.getElementById("addNew").style.display = "none";
          document.getElementById("listData").style.display = "block";
          document.getElementById("container").innerHTML = dataSlave[0];
          document.getElementById("footData").innerHTML = dataSlave[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function edit(notransaksi) {
  document.getElementById("addNew").style.display = "block";
  document.getElementById("listData").style.display = "none";
  document.getElementById("notransaksi").value = trim(notransaksi);
  document.getElementById("method").value = "update";

  param = "method=getedit";
  param += "&notransaksi=" + notransaksi;

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          closeDialog();
          data = con.responseText.split("###");

          document.getElementById("namajabatan").value = data[1];
          document.getElementById("jumlahpekerjasekarang").value = data[2];
          document.getElementById("jumlahpekerjadibutuhkan").value = data[3];
          document.getElementById("departemen").value = data[4];
          document.getElementById("alasan").value = data[5];
          document.getElementById("statuskaryawan").value = data[6];
          document.getElementById("mulaibekerja").value = data[7];
          document.getElementById("golongan").value = data[8];
          document.getElementById("pendidikanminimal").value = data[9];
          document.getElementById("pengalamanminimal").value = data[10];
          document.getElementById("uraiankerja").value = data[11];
          document.getElementById("kualifikasi").value = data[12];
          document.getElementById("sertifikasi").value = data[13];
          document.getElementById("jeniskelamin").value = data[14];
          document.getElementById("statuspernikahan").value = data[15];
          document.getElementById("bidangpengalaman").value = data[16];
          document.getElementById("note").value = data[18];

          document.getElementById("alasanganti").value = data[17];
          if (data[17] == "undefined" || data[17] == "") {
            document.getElementById("alasanganti").style.display = "none";
          }

          assignCheckboxValues(data[19], "jenis_tes[]");
          assignCheckboxValues(data[20], "jenis_interview[]");
          document.getElementById("divisi").value = data[21];

        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpan() {
  notransaksi = document.getElementById("notransaksi").value;
  namajabatan = document.getElementById("namajabatan").value;
  jumlahpekerjasekarang = document.getElementById(
    "jumlahpekerjasekarang"
  ).value;
  jumlahpekerjadibutuhkan = document.getElementById(
    "jumlahpekerjadibutuhkan"
  ).value;
  departemen = document.getElementById("departemen").value;
  alasan = document.getElementById("alasan").value;
  statuskaryawan = document.getElementById("statuskaryawan").value;
  mulaibekerja = document.getElementById("mulaibekerja").value;
  golongan = document.getElementById("golongan").value;
  pendidikanminimal = document.getElementById("pendidikanminimal").value;
  pengalamanminimal = document.getElementById("pengalamanminimal").value;
  lokasikerja = document.getElementById("lokasikerja").value;
  uraiankerja = document.getElementById("uraiankerja").value;
  kualifikasi = document.getElementById("kualifikasi").value;
  sertifikasi = document.getElementById("sertifikasi").value;
  statuspernikahan = document.getElementById("statuspernikahan").value;
  bidangpengalaman = document.getElementById("bidangpengalaman").value;
  jeniskelamin = document.getElementById("jeniskelamin").value;
  alasanganti = document.getElementById("alasanganti").value;
  usiamin = document.getElementById("usiamin").value;
  usiamax = document.getElementById("usiamax").value;
  note = document.getElementById("note").value;
  divisi = document.getElementById("divisi").value;

  jenis_tes = getCheckboxValues("jenis_tes[]");
  jenis_interview = getCheckboxValues("jenis_interview[]");

  method = document.getElementById("method").value;

  if (notransaksi == "") {
    alert("Notransaksi Kosong");
    return;
  } else if (namajabatan == "") {
    alert("Harap Mengisi Nama Jabatan");
    return;
  } else if (jumlahpekerjasekarang == "") {
    alert("Jumlah Pekerja Saat ini Kosong");
    return;
  } else if (jumlahpekerjadibutuhkan == "") {
    alert("Harap mengisi Jumlah Pekerja Dibutuhkan");
    return;
  } else if (departemen == "") {
    alert("Harap pilih Departemen");
    return;
  } else if (alasan == "") {
    alert("Harap pilih Alasan Permintaan");
    return;
  } else if (statuskaryawan == "") {
    alert("Harap pilih Status Karyawan");
    return;
  } else if (mulaibekerja == "") {
    alert("Harap pilih Mulai Bekerja");
    return;
  } else if (golongan == "") {
    alert("Harap pilih Golongan");
    return;
  } else if (pendidikanminimal == "") {
    alert("Harap mengisi Pendidikan Minimal");
    return;
  } else if (pengalamanminimal == "") {
    alert("Harap mengisi Pengalaman Minimal");
    return;
  } else if (lokasikerja == "") {
    alert("Harap mengisi lokasikerja");
    return;
  } else if (uraiankerja == "") {
    alert("Harap mengisi Uraian Kerja");
    return;
  } else if (kualifikasi == "") {
    alert("Harap mengisi Kualifikasi");
    return;
  } else {
    param = "notransaksi=" + notransaksi;
    param += "&namajabatan=" + namajabatan;
    param += "&jumlahpekerjasekarang=" + jumlahpekerjasekarang;
    param += "&jumlahpekerjadibutuhkan=" + jumlahpekerjadibutuhkan;
    param += "&departemen=" + departemen;
    param += "&alasan=" + alasan;
    param += "&statuskaryawan=" + statuskaryawan;
    param += "&mulaibekerja=" + mulaibekerja;
    param += "&golongan=" + golongan;
    param += "&pendidikanminimal=" + pendidikanminimal;
    param += "&pengalamanminimal=" + pengalamanminimal;
    param += "&lokasikerja=" + lokasikerja;
    param += "&uraiankerja=" + uraiankerja;
    param += "&kualifikasi=" + kualifikasi;
    param += "&sertifikasi=" + sertifikasi;
    param += "&statuspernikahan=" + statuspernikahan;
    param += "&bidangpengalaman=" + bidangpengalaman;
    param += "&jeniskelamin=" + jeniskelamin;
    param += "&alasanganti=" + alasanganti;
    param += "&usiamin=" + usiamin;
    param += "&usiamax=" + usiamax;
    param += "&note=" + note;
    param += "&divisi=" + divisi;
    param += "&method=" + method;
    tujuan = "sdm_req_employee_slave.php";

    for (var i = 0; i < jenis_tes.length; i++) {
      param += "&jenis_tes[]=" + encodeURIComponent(jenis_tes[i]);
    }
    for (var i = 0; i < jenis_interview.length; i++) {
      param += "&jenis_interview[]=" + encodeURIComponent(jenis_interview[i]);
    }

    post_response_text(tujuan, param, respon);
  }

  function respon() {
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
}

function getCheckboxValues(name) {
  var checkboxes = document.querySelectorAll(
    'input[name="' + name + '"]:checked'
  );
  var values = [];
  for (var i = 0; i < checkboxes.length; i++) {
    values.push(checkboxes[i].value);
  }
  return values;
}

function del(notransaksi) {
  param = "method=delete" + "&notransaksi=" + notransaksi;
  tujuan = "sdm_req_employee_slave.php";

  if (confirm("Hapus data dengan notransaksi = " + notransaksi + "?")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          hapus();
          document.getElementById("container").innerHTML = con.responseText;
          alert("Data Berhasil dihapus !!!");
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
        hapus();
      }
    }
  }
}

function hitungpekerja() {
  namajabatan = document.getElementById("namajabatan").value;
  param = "namajabatan=" + namajabatan;
  param += "&method=hitungpekerjasekarang";
  tujuan = "sdm_req_employee_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("jumlahpekerjasekarang").value = trim(
            con.responseText
          );
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnotransaksi() {
  param = "method=getnotransaksi";
  tujuan = "sdm_req_employee_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("notransaksi").value = trim(con.responseText);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingData(notransaksi) {
  param = "notransaksi=" + notransaksi + "&method=postingData";
  tujuan = "sdm_req_employee_slave.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          form_ajukan(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  if (confirm("Apakah anda yakin ajukan data ?")) {
    post_response_text(tujuan, param, respog);
  } else {
    return;
  }
}

function form_ajukan(notransaksi) {
  width = "400";
  height = "";
  content =
    '<fieldset><legend>Submission Form</legend><div id=containeraju align=center style="width:100%;max-height:100px;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "";
  showDialog1(title, content, width, height, ev);

  param = "method=form_ajukan&notransaksi=" + notransaksi;
  tujuan = "sdm_req_employee_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containeraju").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ajukan() {
  notrans = document.getElementById("notransaksi_ajukan").value;
  jlh = document.getElementById("jlh").value;
  var param = "method=ajukan";
  param += "&notransaksi=" + notrans;
  param += "&jlh=" + jlh;
  for (i = 1; i <= jlh; i++) {
    param +=
      "&" + "kepada" + i + "=" + document.getElementById("kepada" + i).value;
  }
  tujuan = "sdm_req_employee_slave.php";
  closeDialog();
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Sucses");
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewDetail(notransaksi) {
  param = "method=preview";
  param += "&notransaksi=" + notransaksi;
  tujuan = "sdm_req_employee_slave.php";
  post_response_text(tujuan, param, respon);
  function respon() {
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

function pdf(notransaksi) {
  param = "method=previewPDF2";
  param += "&notransaksi=" + notransaksi;
  param += "&tipe=pdf";
  tujuan = "sdm_req_employee_slave.php";
  judul = "Report PDF " + notransaksi;
  ev = "event";
  closeDialog();
  printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "900";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function getAlasan() {
  let alasanOpt = document.getElementById("alasan").value;

  if (alasanOpt == "Replacement") {
    document.getElementById("alasanganti").style.display = "block";
  } else {
    document.getElementById("alasanganti").style.display = "none";
  }
}


function assignCheckboxValues(jsonString, checkboxName) {
  try {
    const values = JSON.parse(jsonString);

    const checkboxes = document.querySelectorAll(`input[name="${checkboxName}"]`);

    checkboxes.forEach(checkbox => {
      checkbox.checked = false;
    });

    checkboxes.forEach(checkbox => {
      if (values.includes(checkbox.value)) {
        checkbox.checked = true;
      }
    });
  } catch (error) {
    console.error('Error parsing JSON:', error);
  }
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = '900';
  height = '400';
  content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
  showDialog1(title, content, width, height, ev);
}

