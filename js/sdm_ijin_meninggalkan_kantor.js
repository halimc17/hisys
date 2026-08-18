function prosesbatalcuti() {
  notrans = document.getElementById("notrbatal").value;
  persetujuan1 = document.getElementById("persetujuan1batal").value;
  persetujuan2 = document.getElementById("persetujuan2batal").value;
  persetujuan3 = document.getElementById("persetujuan3batal").value;
  alasanbatalcuti = document.getElementById("alasanbatalcuti").value;

  param = "proses=batalcuti" + "&notrans=" + notrans;
  param += "&persetujuan1=" + persetujuan1;
  param += "&persetujuan2=" + persetujuan2;
  param += "&persetujuan3=" + persetujuan3;
  param += "&alasanbatalcuti=" + alasanbatalcuti;
  if (alasanbatalcuti == "") {
    alert("Alasan pembatalan wajib diisi !");
    return;
  }
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  if (confirm("Anda ingin membatalkan cuti ini, anda yakin ???")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Done");
          alertify.popup().destroy();
          loadNData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalcuti(notrans, persetujuan1, persetujuan2, persetujuan3) {
  width = "";
  height = "";
  content =
    '<div id=contbatalcuti style="width:300px;max-height:700px;overflow:auto;"></div>';
  ev = "event";
  title = "Batalkan Cuti";
  //showDialog5(title, content, width, height, ev);

  param = "proses=formbatalcuti" + "&notrans=" + notrans;
  param += "&persetujuan1=" + persetujuan1;
  param += "&persetujuan2=" + persetujuan2;
  param += "&persetujuan3=" + persetujuan3;
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //document.getElementById('contbatalcuti').innerHTML=con.responseText;
          alertify
            .popup(title, con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("500px", "300px");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewPdf(tgl, karywn, ev) {
  tglijin = tgl;
  krywnId = karywn;
  param = "proses=prevPdf" + "&tglijin=" + tglijin + "&krywnId=" + krywnId;
  tujuan = "sdm_slave_laporan_ijin_meninggalkan_kantor.php?" + param;
  //display window
  title = "Print PDF";
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  //showDialog1(title, content, width, height, ev);
  // alert(param);
  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_laporan_ijin_meninggalkan_kantor.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function cancelForm() {
  document.getElementById("tglIzin").disabled = true;
  // document.getElementById('tglIzin').value='';
  document.getElementById("jam1").value = 00;
  q = document.getElementById("jam1");
  for (a = 0; a < q.length; a++) {
    if (q.options[a].value == 00) {
      q.options[a].selected = true;
    }
  }
  q2 = document.getElementById("mnt1");
  for (a2 = 0; a2 < q2.length; a2++) {
    if (q2.options[a2].value == 00) {
      q2.options[a2].selected = true;
    }
  }
  qjm2 = document.getElementById("jam2");
  for (aqjm2 = 0; aqjm2 < qjm2.length; aqjm2++) {
    if (qjm2.options[aqjm2].value == 00) {
      qjm2.options[aqjm2].selected = true;
    }
  }
  qmnt2 = document.getElementById("mnt2");
  for (aqmnt2 = 0; aqmnt2 < qmnt2.length; aqmnt2++) {
    if (qmnt2.options[aqmnt2].value == 00) {
      qmnt2.options[aqmnt2].selected = true;
    }
  }
  document.getElementById("jnsIjin").value = "";
  document.getElementById("tglAwal").value = "";
  document.getElementById("tglEnd").value = "";
  document.getElementById("keperluan").value = "";
  document.getElementById("ket").value = "";
  document.getElementById("atsSblm").value = "";
  document.getElementById("atsSblm2").value = "";
  document.getElementById("persetujuan1").value = "";
  document.getElementById("persetujuan2").value = "";
  document.getElementById("persetujuan3").value = "";
  document.getElementById("persetujuan4").value = "";
  document.getElementById("jumlahhk").value = "";

  document.getElementById("tanggalkerja").value = "";
  document.getElementById("alamatcuti").value = "";
  document.getElementById("pengganti").value = "";
  document.getElementById("nohp").value = "";

  cb = document.getElementById("hometrip");
  cb.checked = false;
  cb.disabled = false;
  checkhometrip(cb);

  document.getElementById("tdkanan").rowSpan = "8";

  param = "proses=cancelForm";
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfiles();
          document.getElementById("proses").value = "insert";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveForm(jumlahlevel) {
  pros = document.getElementById("proses").value;
  param = "proses=" + pros;

  tglijin = document.getElementById("tglIzin").value;
  tglAwal = document.getElementById("tglAwal").value;
  tglEnd = document.getElementById("tglEnd").value;
  jnsIjin =
    document.getElementById("jnsIjin").options[
      document.getElementById("jnsIjin").selectedIndex
    ].value;
  jam1 =
    document.getElementById("jam1").options[
      document.getElementById("jam1").selectedIndex
    ].value;
  mnt1 =
    document.getElementById("mnt1").options[
      document.getElementById("mnt1").selectedIndex
    ].value;
  jam2 =
    document.getElementById("jam2").options[
      document.getElementById("jam2").selectedIndex
    ].value;
  mnt2 =
    document.getElementById("mnt2").options[
      document.getElementById("mnt2").selectedIndex
    ].value;
  keperluan = document.getElementById("keperluan").value;
  ket = document.getElementById("ket").value;
  persetujuan1 =
    document.getElementById("persetujuan1").options[
      document.getElementById("persetujuan1").selectedIndex
    ].value;
  persetujuan2 =
    document.getElementById("persetujuan2").options[
      document.getElementById("persetujuan2").selectedIndex
    ].value;
  persetujuan3 =
    document.getElementById("persetujuan3").options[
      document.getElementById("persetujuan3").selectedIndex
    ].value;
  persetujuan4 =
    document.getElementById("persetujuan4").options[
      document.getElementById("persetujuan4").selectedIndex
    ].value;
  jamDr = jam1 + ":" + mnt1;
  jamSmp = jam2 + ":" + mnt2;
  hk = document.getElementById("jumlahhk").value;
  periodec =
    document.getElementById("periodec").options[
      document.getElementById("periodec").selectedIndex
    ].value;

  cb = document.getElementById("hometrip");
  nohp = document.getElementById("nohp").value;
  tglberangkat = document.getElementById("tglberangkat").value;
  rutekeberangkatan = document.getElementById("rutekeberangkatan").value;
  tglpulang = document.getElementById("tglpulang").value;
  rutekepulangan = document.getElementById("rutekepulangan").value;

  param += "&hometrip=" + cb.checked;
  param += "&nohp=" + nohp;
  param += "&tglberangkat=" + tglberangkat;
  param += "&rutekeberangkatan=" + rutekeberangkatan;
  param += "&tglpulang=" + tglpulang;
  param += "&rutekepulangan=" + rutekepulangan;

  if (pros == "update") {
    atsSblm = document.getElementById("atsSblm").value;
    atsSblm2 = document.getElementById("atsSblm2").value;
    param += "&atsSblm=" + atsSblm + "&atsSblm2=" + atsSblm2;
  }

  if (periodec == "") {
    return alert("Periode harus diisi");
  }

  alamatcuti = document.getElementById("alamatcuti").value;
  tanggalkerja = document.getElementById("tanggalkerja").value;
  pengganti = document.getElementById("pengganti").value;

  param += "&alamatcuti=" + alamatcuti;
  param += "&tanggalkerja=" + tanggalkerja;
  param += "&pengganti=" + pengganti;
  param += "&tglijin=" + tglijin;
  param += "&tglberangkat=" + tglberangkat;
  param += "&jnsIjin=" + jnsIjin;
  param += "&jamDr=" + jamDr;
  param += "&jamSmp=" + jamSmp;
  param += "&keperluan=" + keperluan;
  param += "&ket=" + ket;
  param += "&persetujuan1=" + persetujuan1;
  param += "&persetujuan2=" + persetujuan2;
  param += "&persetujuan3=" + persetujuan3;
  param += "&persetujuan4=" + persetujuan4;
  param += "&tglAwal=" + tglAwal;
  param += "&tglEnd=" + tglEnd;
  param += "&jumlahhk=" + hk;
  param += "&periodec=" + periodec;
  param += "&jumlahlevel=" + jumlahlevel;

  if (
    (jnsIjin == "CUTI" ||
      jnsIjin == "MELAHIRKAN" ||
      jnsIjin == "KAWIN/SUNATAN/WISUDA" ||
      jnsIjin == "CUTIPOTONGGAJI" ||
      jnsIjin == "TERLAMBATPOTONGGAJI" ||
      jnsIjin == "PULANGAWALPOTONGGAJI") &&
    (hk == "0" || hk == "")
  ) {
    alert("Number of day(s) required");
  } else {
    tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (con.responseText.length > 0) {
            alert(con.responseText);
          }
          loadNData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadNData() {
  param = "proses=loadData";
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  //alert(tujuan);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          //alert(con.responseText);
          document.getElementById("contain").innerHTML = con.responseText;
          cancelForm();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}
function cariBast(num) {
  param = "proses=loadData";
  param += "&page=" + num;
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
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

function fillField(
  keprlan,
  tanggal,
  jnsijin,
  perstjan1,
  perstjan2,
  perstjan3,
  drjam,
  smpjam,
  hk,
  periodec,
  keterangan,
  alamatcuti,
  tanggalkerja,
  pengganti,
  nohp,
  karyawanid
) {
  document.getElementById("proses").value = "update";
  document.getElementById("tglIzin").disabled = true;
  document.getElementById("tglIzin").value = tanggal;
  document.getElementById("keperluan").value = keprlan;
  document.getElementById("tglAwal").value = drjam;
  document.getElementById("tglEnd").value = smpjam;
  document.getElementById("jumlahhk").value = hk;

  jns = document.getElementById("jnsIjin");
  for (ajns = 0; ajns < jns.length; ajns++) {
    if (jns.options[ajns].value == jnsijin) {
      jns.options[ajns].selected = true;
    }
  }
  atsn = document.getElementById("persetujuan1");
  for (aatsn = 0; aatsn < atsn.length; aatsn++) {
    if (atsn.options[aatsn].value == perstjan1) {
      atsn.options[aatsn].selected = true;
    }
  }
  atsn = document.getElementById("persetujuan2");
  for (aatsn = 0; aatsn < atsn.length; aatsn++) {
    if (atsn.options[aatsn].value == perstjan2) {
      atsn.options[aatsn].selected = true;
    }
  }
  x = document.getElementById("persetujuan3");
  for (j = 0; j < x.length; j++) {
    if (x.options[j].value == perstjan3) {
      x.options[j].selected = true;
    }
  }
  x = document.getElementById("periodec");
  for (j = 0; j < x.length; j++) {
    if (x.options[j].value == periodec) {
      x.options[j].selected = true;
    }
  }
  document.getElementById("atsSblm").value = "";
  document.getElementById("atsSblm").value = perstjan1;
  document.getElementById("atsSblm2").value = "";
  document.getElementById("atsSblm2").value = perstjan2;
  document.getElementById("ket").value = keterangan;
  document.getElementById("alamatcuti").value = alamatcuti;
  document.getElementById("tanggalkerja").value = tanggalkerja;
  document.getElementById("pengganti").value = pengganti;
  document.getElementById("nohp").value = nohp;

  loadSisaCuti(karyawanid);
}

function cariTransaksi() {
  txtSearch = document.getElementById("txtsearch").value;
  txtTgl = document.getElementById("tgl_cari").value;

  param =
    "txtSearch=" + txtSearch + "&txtTgl=" + txtTgl + "&proses=cariTransaksi";
  //alert(param);
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("list_ganti").style.display = "block";
          document.getElementById("headher").style.display = "none";
          document.getElementById("detail_ganti").style.display = "none";
          document.getElementById("contain").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function dataKePDF(notrans, ev) {
  noTrans = notrans;
  tujuan = "vhc_DetailPenggantianKomponen_pdf.php";
  judul = noTrans;
  param = "noTrans=" + noTrans;
  //alert(param);
  printFile(param, tujuan, judul, ev);
}
function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}
function delData(tgl) {
  tglijin = tgl;
  param = "tglijin=" + tglijin + "&proses=deleteData";
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  if (confirm("Deleting, are you sure !!"))
    post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadNData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function form_ajukan(notrans){
  width = '300';
  height = '';
  content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
  ev = 'event';
  title = "";
  showDialog1(title, content, width, height, ev);
  
  param = 'proses=form_ajukan&notrans='+notrans;
  tujuan = 'sdm_slave_ijin_meninggalkan_kantor.php';
  post_response_text(tujuan, param, respog);
  function respog()
  {
      if (con.readyState == 4)
      {
          if (con.status == 200)
          {
              busy_off();
              if (!isSaveResponse(con.responseText))
              {
                  alert(con.responseText);
              }
              else
              {
                  document.getElementById('containeraju').innerHTML = con.responseText;
              }
          }
          else
          {
              busy_off();
              error_catch(con.status);
          }
      }
  }
}

function ajukan(){
  notrans     =document.getElementById('notransaksi_ajukan').value;
  jlh         =document.getElementById('jlh').value;
  var param   = 'proses=ajukan';
  param       += '&notrans=' + notrans;
  param       += '&jlh=' + jlh;

  for (i = 1; i <= jlh; i++) {
      param += "&" + 'kepada'+ i + "=" + document.getElementById('kepada'+i).value;
  }

  if(jlh==0){ 
      alertify.alert("Warning: Approval kosong");
      return;
  }
  
  tujuan = 'sdm_slave_ijin_meninggalkan_kantor.php';
  closeDialog();
  post_response_text(tujuan, param, respog);
  function respog(){
      if (con.readyState == 4){
          if (con.status == 200){
              busy_off();
              if (!isSaveResponse(con.responseText)){
                  alert(con.responseText);
              }else{
                  alert('Berhasil diajukan.');
                  cariBast(0);
              }
          }else{
              busy_off();
              error_catch(con.status);
          }
      }
  }
}

function loadSisaCuti(karyawanid) {
  jnsIjin = document.getElementById("jnsIjin").value;
  periode = document.getElementById("periodec").value;
  tglAwal = document.getElementById("tglAwal").value;
  tglEnd = document.getElementById("tglEnd").value;
  tglijin = document.getElementById("tglIzin").value;
  sisa = document.getElementById('sis').innerHTML;
  sisa = sisa.split(' ');
  sisa = sisa[0];
  // console.log(sisa);
  param =
    "sisa=" +
    sisa +
    "&periode=" +
    periode +
    "&karyawanid=" +
    karyawanid +
    "&jnsIjin=" +
    jnsIjin +
    "&tglAwal=" +
    tglAwal +
    "&tglEnd=" +
    tglEnd +
    "&tglijin=" +
    tglijin;
  tujuan = "sdm_slave_ijin_getSisaCuti.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText;
          // console.log(con.responseText);
          period = data.substr(0, 4);
          datanya = data.substr(4);
          // console.log(datanya);
          if (datanya == "A0") {
            alert(
              "Sisa Cuti anda 0 tidak bisa ambil cuti di periode " +
                period +
                "."
            );
            document.getElementById("periodec").value = "";
          } else {
            // alert(con.responseText);
            document.getElementById("sis").innerHTML = con.responseText.trim();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function checkhometrip(cb) {
  document.getElementById("tglberangkat").value = "";
  document.getElementById("rutekeberangkatan").value = "";
  document.getElementById("tglpulang").value = "";
  document.getElementById("rutekepulangan").value = "";

  if (cb.checked == true) {
    document.getElementById("trtanggalberangkat").style.display = "";
    document.getElementById("trrutekeberangkatan").style.display = "";
    document.getElementById("trtanggalpulang").style.display = "";
    document.getElementById("trrutekepulangan").style.display = "";
    document.getElementById("tdkanan").rowSpan = "12";
  } else {
    document.getElementById("trtanggalberangkat").style.display = "none";
    document.getElementById("trrutekeberangkatan").style.display = "none";
    document.getElementById("trtanggalpulang").style.display = "none";
    document.getElementById("trrutekepulangan").style.display = "none";
    document.getElementById("tdkanan").rowSpan = "8";
  }
}

function submitfile() {
  document.getElementById("btnsubmit").disabled = true;
  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  if (getValue("upload") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  var con = createXMLHttpRequest();
  con.open(
    "POST",
    "sdm_slave_ijin_meninggalkan_kantor.php?proses=submitfile",
    true
  );
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          document.getElementById("btnsubmit").disabled = false;
          alert(con.responseText);
        } else {
          //=== Success Response
          document.getElementById("btnsubmit").disabled = false;
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
  param = "proses=loadfiles";
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listfiles").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(namafile) {
  param = "proses=deletefile&namafile=" + namafile;
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
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

function getjumlahcuti() {
  periodec = document.getElementById("periodec").value;
  tglAwal = document.getElementById("tglAwal").value;
  tglEnd = document.getElementById("tglEnd").value;
  jnsIjin = document.getElementById("jnsIjin").value;
  jam1 =
    document.getElementById("jam1").options[
      document.getElementById("jam1").selectedIndex
    ].value;
  mnt1 =
    document.getElementById("mnt1").options[
      document.getElementById("mnt1").selectedIndex
    ].value;
  jam2 =
    document.getElementById("jam2").options[
      document.getElementById("jam2").selectedIndex
    ].value;
  mnt2 =
    document.getElementById("mnt2").options[
      document.getElementById("mnt2").selectedIndex
    ].value;
  jamDr = jam1 + ":" + mnt1;
  jamSmp = jam2 + ":" + mnt2;
  param =
    "proses=getjumlahcuti&tglAwal=" +
    tglAwal +
    "&tglEnd=" +
    tglEnd +
    "&jamDr=" +
    jamDr +
    "&jamSmp=" +
    jamSmp;
  param += "&periodec=" + periodec + "&jnsIjin=" + jnsIjin;
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("jumlahhk").value = con.responseText;
          loadSisaCuti();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getjumlahcutireal() {
  periodec = document.getElementById("periodec").value;
  tglAwalreal = document.getElementById("tglAwalreal").value;
  tglEndreal = document.getElementById("tglEndreal").value;
  jnsIjin = document.getElementById("jnsIjin").value;
  jam1 =
    document.getElementById("jam1").options[
      document.getElementById("jam1").selectedIndex
    ].value;
  mnt1 =
    document.getElementById("mnt1").options[
      document.getElementById("mnt1").selectedIndex
    ].value;
  jam2 =
    document.getElementById("jam2").options[
      document.getElementById("jam2").selectedIndex
    ].value;
  mnt2 =
    document.getElementById("mnt2").options[
      document.getElementById("mnt2").selectedIndex
    ].value;
  jamDr = jam1 + ":" + mnt1;
  jamSmp = jam2 + ":" + mnt2;
  param =
    "proses=getjumlahcutireal&tglAwalreal=" +
    tglAwalreal +
    "&tglEndreal=" +
    tglEndreal +
    "&jamDr=" +
    jamDr +
    "&jamSmp=" +
    jamSmp;
  param += "&periodec=" + periodec + "&jnsIjin=" + jnsIjin;
  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("jumlahhk").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function frm_real(tanggal, darijam, sampaijam, karyawanid) {
  if (confirm("Process submission ??")) {
    document.getElementById("list_ganti").style.display = "none";

    document.getElementById("realisasi").style.display = "block";

    param =
      "tanggal=" +
      tanggal +
      "&jamDr=" +
      darijam +
      "&jamSmp=" +
      sampaijam +
      "&karyawanid=" +
      karyawanid +
      "&proses=formreal";

    tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("realisasidata").innerHTML =
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
    displayList();
  }
  //}
}

function save_real(tanggal) {
  tglreal = document.getElementById("tglreal").value;
  tglAwalreal = document.getElementById("tglAwalreal").value;
  tglEndreal = document.getElementById("tglEndreal").value;
  hk = document.getElementById("jumlahhk").value;

  /* jam1real=document.getElementById('jam1real').value;
	mnt1real=document.getElementById('mnt1real').value;
	jam2real=document.getElementById('jam2real').value;
	mnt2real=document.getElementById('mnt2real').value;*/

  param = "proses=savereal";
  param += "&tglreal=" + tglreal;
  param += "&tglAwalreal=" + tglAwalreal;
  param += "&tglEndreal=" + tglEndreal;
  param += "&jumlahhk=" + hk;

  /*    param += "&jam1real="+jam1real;
	param += "&mnt1real="+mnt1real;
	param += "&jam2real="+jam2real;
	param += "&mnt2real="+jam2real;*/

  param += "&tanggal=" + tanggal;

  tujuan = "sdm_slave_ijin_meninggalkan_kantor.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadNData();
          document.getElementById("realisasi").style.display = "none";
          document.getElementById("list_ganti").style.display = "block";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
