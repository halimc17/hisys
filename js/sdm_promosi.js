function getdata(id, val) {
  if (id == "#tanggungjawab") {
    e = val.options[val.selectedIndex].text;
  } else {
    e = val;
  }

  p = document.getElementById("paragraf2").value;
  r = p.replace(id, e);
  document.getElementById("paragraf2").value = r;
}
function add_new_data() {
  document.getElementById("inputdata").style.display = "block";
  document.getElementById("listdata").style.display = "none";
}

function displayList() {
  document.getElementById("inputdata").style.display = "none";
  document.getElementById("listdata").style.display = "block";
  loadList();
}

function hapusKomponen(kompon) {
  var komponen = kompon.replace("###", "");
  var n = kompon.indexOf("###");
  var komponenx = document.getElementById("newkomponx").innerHTML;
  var z = komponenx.indexOf("###");
  //alert(komponenx);
  //alert(n);
  if (n == -1 && z > -1) {
    kompon = kompon + "###";
  }
  //alert(kompon);
  var komponenxz = komponenx.replace(kompon, "");
  //alert(komponenxz);
  document.getElementById("newkomponx").innerHTML = komponenxz;
  var thead = document.getElementById("trid_" + komponen);
  thead.parentNode.removeChild(thead);
}

function tambahKomponen() {
  var komponen = document.getElementById("komponen").value;
  komponenarr = komponen.split("###");
  var komponenx = document.getElementById("newkomponx").innerHTML;
  var komponenxz;
  var jumlah = document.getElementById("jumlah").value;
  var komponjumlah = "";
  var x4 = document.getElementById("x4");
  var cekkompon = 0;
  if (komponen == "") {
    alert("Komponen Gaji tidak boleh kosong");
  } else if (jumlah == 0) {
    alert("Jumlah komponen gaji tidak boleh kosong");
  } else {
    if (komponenx != "") {
      arrkompon = komponenx.split("###");
      for (i = 0; i < arrkompon.length; i++) {
        if (arrkompon[i] == komponenarr[0]) {
          cekkompon = 1;
        }

        if (i == 0) {
          komponenxz = arrkompon[i];
          komponjumlah +=
            '<tr id="trid_' +
            arrkompon[i] +
            '"><td hidden id="newid_' +
            arrkompon[i] +
            '">' +
            arrkompon[i] +
            '</td><td id="newkomponen_' +
            arrkompon[i] +
            '">' +
            document.getElementById("newkomponen_" + arrkompon[i]).innerHTML +
            '</td><td>:</td><td id="newkomponenjml_' +
            arrkompon[i] +
            '" >' +
            document.getElementById("newkomponenjml_" + arrkompon[i])
              .innerHTML +
            ' </td><td><img src=images/minus.gif class=resicon  title="Del" onclick=hapusKomponen("' +
            arrkompon[i] +
            '")></td></tr>';
        } else {
          komponenxz += "###" + arrkompon[i];
          komponjumlah +=
            '<tr id="trid_' +
            arrkompon[i] +
            '"><td hidden id="newid_' +
            arrkompon[i] +
            '">' +
            arrkompon[i] +
            '</td><td id="newkomponen_' +
            arrkompon[i] +
            '">' +
            document.getElementById("newkomponen_" + arrkompon[i]).innerHTML +
            '</td><td>:</td><td id="newkomponenjml_' +
            arrkompon[i] +
            '" >' +
            document.getElementById("newkomponenjml_" + arrkompon[i])
              .innerHTML +
            ' </td><td><img src=images/minus.gif class=resicon  title="Del" onclick=hapusKomponen("###' +
            arrkompon[i] +
            '")></td></tr>';
        }
      }
    }

    if (cekkompon == 0) {
      if (komponenx == "") {
        komponenxz = komponenarr[0];
        komponjumlah =
          '<tr id="trid_' +
          komponenarr[0] +
          '"><td hidden id="newid_' +
          komponenarr[0] +
          '">' +
          komponenarr[0] +
          '</td><td id="newkomponen_' +
          komponenarr[0] +
          '">' +
          komponenarr[1] +
          '</td><td>:</td><td id="newkomponenjml_' +
          komponenarr[0] +
          '" >' +
          jumlah +
          ' </td><td><img src=images/minus.gif class=resicon  title="Del" onclick=hapusKomponen("' +
          komponenarr[0] +
          '")></td></tr>';
      } else {
        komponenxz += "###" + komponenarr[0];
        komponjumlah +=
          '<tr id="trid_' +
          komponenarr[0] +
          '"><td hidden id="newid_' +
          komponenarr[0] +
          '">' +
          komponenarr[0] +
          '</td><td id="newkomponen_' +
          komponenarr[0] +
          '">' +
          komponenarr[1] +
          '</td><td>:</td><td id="newkomponenjml_' +
          komponenarr[0] +
          '" >' +
          jumlah +
          '</td><td><img src=images/minus.gif class=resicon  title="Del" onclick=hapusKomponen("###' +
          komponenarr[0] +
          '")></td></tr>';
      }
    } else {
      alert("Komponen gaji " + komponenarr[1] + " sudah ada");
    }

    document.getElementById("newkomponx").innerHTML = komponenxz;
    //alert(komponjumlah);
    x4.innerHTML = "";
    x4.innerHTML = komponjumlah;
  }
}

function cekKomponenGaji(karid) {
  var x2 = document.getElementById("x2");
  tp = document.getElementById("tipetransaksi").value;

  if (
    document.getElementById("tanggalberlaku").value == "" ||
    document.getElementById("tanggalsk").value == "" ||
    karid == ""
  ) {
    document.getElementById("x2").innerHTML = "";
    document.getElementById("karyawanid").value = "";
    alert("Input tanggal terlebih dahulu");
  } else {
    param =
      "karid=" +
      karid +
      "&tanggal=" +
      document.getElementById("tanggalberlaku").value;
    tujuan = "sdm_slave_getKomponenGaji.php";
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          x2.innerHTML = con.responseText;
          //alert(con.responseText);
          getKarStat(karid, tp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getKarStat(karid, tp) {
  if (karid == "") {
  } else {
    param =
      "karid=" +
      karid +
      "&tanggal=" +
      document.getElementById("tanggalberlaku").value;
    tujuan = "sdm_slave_getPromosiCurStatus.php";
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
          parseDong(con.responseText, tp, karid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function parseDong(tex, tp, karid) {
  xml = tex.toString();
  xmlobject = new DOMParser().parseFromString(xml, "text/xml");

  // Data Karyawan
  kodejabatan =
    xmlobject.getElementsByTagName("kodejabatan")[0].firstChild.nodeValue;
  kodejabatan = kodejabatan.replace("*", "");
  kodegolongan =
    xmlobject.getElementsByTagName("kodegolongan")[0].firstChild.nodeValue;
  kodegolongan = kodegolongan.replace("*", "");
  lokasitugas =
    xmlobject.getElementsByTagName("lokasitugas")[0].firstChild.nodeValue;
  lokasitugas = lokasitugas.replace("*", "");
  tipekaryawan =
    xmlobject.getElementsByTagName("tipekaryawan")[0].firstChild.nodeValue;
  tipekaryawan = tipekaryawan.replace("*", "");
  bagian = xmlobject.getElementsByTagName("bagian")[0].firstChild.nodeValue;
  bagian = bagian.replace("*", "");
  subbagian =
    xmlobject.getElementsByTagName("subbagian")[0].firstChild.nodeValue;
  subbagian = subbagian.replace("*", "");

  setValue("oldokasitugas", lokasitugas);
  setValue("oldjabatan", kodejabatan);
  setValue("oldtipekaryawan", tipekaryawan);
  setValue("oldgolongan", kodegolongan);
  setValue("olddepartemen", bagian);
  setValue("oldsubbagian", subbagian);

  if (tp == "Mutasi") {
    setValue("newgolongan", kodegolongan);
    document.getElementById("newgolongan").disabled = true;
  } else {
    /*setValue('newgolongan','');
        document.getElementById('newgolongan').disabled=false;*/

    // getgrade(tp, karid);
  }

  // Gaji
  // kompon = xmlobject.getElementsByTagName("kompon")[0].firstChild.nodeValue;
  // document.getElementById("oldkomponx").innerHTML = kompon;

  // arrkompon = kompon.split("###");
  // ///alert(kompon);
  // for (var i = 0; i < arrkompon.length; i++) {
  //   if (
  //     xmlobject.getElementsByTagName("komponen_" + arrkompon[i])[0].firstChild
  //       .nodeValue
  //   ) {
  //     jumlah = xmlobject.getElementsByTagName("komponen_" + arrkompon[i])[0]
  //       .firstChild.nodeValue;
  //     jumlah = jumlah.replace("*", "");
  //     //alert(jumlah);
  //     setValue("oldkomponenjml_" + arrkompon[i], jumlah);
  //   }
  // }
}

function getgrade(tp, karid) {
  param = "karyawanid=" + karid + "&tp=" + tp + "&method=getgrade";
  tujuan = "sdm_slave_savePromosi.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("newgolongan").disabled = false;
          document.getElementById("newgolongan").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editSK(notransaksi, karyawanid) {
  param = "karid=" + karyawanid + "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_getPromosiForEdit.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.popup(con.responseText);
        } else {
          //console.log(con.responseText);
          parseEdit(con.responseText);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function parseEdit(tex) {
  xml = tex.toString();
  xmlobject = new DOMParser().parseFromString(xml, "text/xml");

  karyawanid =
    xmlobject.getElementsByTagName("karyawanid")[0].firstChild.nodeValue;
  karyawanid = karyawanid.replace("*", "");
  jk = document.getElementById("karyawanid");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == karyawanid) {
      jk.options[x].selected = true;
    }
  }

  darisubbagian =
    xmlobject.getElementsByTagName("darisubbagian")[0].firstChild.nodeValue;
  darisubbagian = darisubbagian.replace("*", "");
  jk = document.getElementById("oldsubbagian");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == darisubbagian) {
      jk.options[x].selected = true;
    }
  }

  darikodeorg =
    xmlobject.getElementsByTagName("darikodeorg")[0].firstChild.nodeValue;
  darikodeorg = darikodeorg.replace("*", "");
  jk = document.getElementById("oldokasitugas");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == darikodeorg) {
      jk.options[x].selected = true;
    }
  }

  darikodejabatan =
    xmlobject.getElementsByTagName("darikodejabatan")[0].firstChild.nodeValue;
  darikodejabatan = darikodejabatan.replace("*", "");
  jk = document.getElementById("oldjabatan");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == darikodejabatan) {
      jk.options[x].selected = true;
    }
  }

  daritipe = xmlobject.getElementsByTagName("daritipe")[0].firstChild.nodeValue;
  daritipe = daritipe.replace("*", "");
  jk = document.getElementById("oldtipekaryawan");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == daritipe) {
      jk.options[x].selected = true;
    }
  }

  tanggungjawab = xmlobject.getElementsByTagName("tanggungjawab")[0].firstChild.nodeValue;
  tanggungjawab = tanggungjawab.replace("*", "");
  jk = document.getElementById("tanggungjawab");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == tanggungjawab) {
      jk.options[x].selected = true;
    }
  }

  tipesk = xmlobject.getElementsByTagName("tipesk")[0].firstChild.nodeValue;
  tipesk = tipesk.replace("*", "");

  jk = document.getElementById("tipetransaksi");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == tipesk) {
      jk.options[x].selected = true;
    }
  }

  darikodegolongan =
    xmlobject.getElementsByTagName("darikodegolongan")[0].firstChild.nodeValue;
  darikodegolongan = darikodegolongan.replace("*", "");
  jk = document.getElementById("oldgolongan");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == darikodegolongan) {
      jk.options[x].selected = true;
    }
  }

  kekodeorg =
    xmlobject.getElementsByTagName("kekodeorg")[0].firstChild.nodeValue;
  kekodeorg = kekodeorg.replace("*", "");
  jk = document.getElementById("newlokasitugas");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == kekodeorg) {
      jk.options[x].selected = true;
    }
  }

  kesubbagian =
    xmlobject.getElementsByTagName("kesubbagian")[0].firstChild.nodeValue;
  kesubbagian = kesubbagian.replace("*", "");
  jk = document.getElementById("newsubbagian");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == kesubbagian) {
      jk.options[x].selected = true;
    }
  }

  kekodejabatan =
    xmlobject.getElementsByTagName("kekodejabatan")[0].firstChild.nodeValue;
  kekodejabatan = kekodejabatan.replace("*", "");
  jk = document.getElementById("newjabatan");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == kekodejabatan) {
      jk.options[x].selected = true;
    }
  }

  ketipekaryawan =
    xmlobject.getElementsByTagName("ketipekaryawan")[0].firstChild.nodeValue;
  ketipekaryawan = ketipekaryawan.replace("*", "");
  jk = document.getElementById("newtipekaryawan");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == ketipekaryawan) {
      jk.options[x].selected = true;
    }
  }

  kekodegolongan =
    xmlobject.getElementsByTagName("kekodegolongan")[0].firstChild.nodeValue;
  kekodegolongan = kekodegolongan.replace("*", "");
  jk = document.getElementById("newgolongan");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == kekodegolongan) {
      jk.options[x].selected = true;
    }
  }

  bagian = xmlobject.getElementsByTagName("bagian")[0].firstChild.nodeValue;
  bagian = bagian.replace("*", "");
  jk = document.getElementById("olddepartemen");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == bagian) {
      jk.options[x].selected = true;
    }
  }

  kebagian = xmlobject.getElementsByTagName("kebagian")[0].firstChild.nodeValue;
  kebagian = kebagian.replace("*", "");
  jk = document.getElementById("newdepartemen");
  for (x = 0; x < jk.length; x++) {
    if (jk.options[x].value == kebagian) {
      jk.options[x].selected = true;
    }
  }

  //=====================update flag=============================================
  document.getElementById("method").value = "update";
  nomorsk = xmlobject.getElementsByTagName("nomorsk")[0].firstChild.nodeValue;
  nomorsk = nomorsk.replace("*", "");
  document.getElementById("nosk").value = nomorsk;
  //==============================================================

  tanggalsk =
    xmlobject.getElementsByTagName("tanggalsk")[0].firstChild.nodeValue;
  tanggalsk = tanggalsk.replace("*", "");
  document.getElementById("tanggalsk").value = tanggalsk;

  tanggalpen =
    xmlobject.getElementsByTagName("tanggalpengajuan")[0].firstChild.nodeValue;
  tanggalpen = tanggalpen.replace("*", "");
  document.getElementById("tanggalpen").value = tanggalpen;

  mulaiberlaku =
    xmlobject.getElementsByTagName("mulaiberlaku")[0].firstChild.nodeValue;
  mulaiberlaku = mulaiberlaku.replace("*", "");
  document.getElementById("tanggalberlaku").value = mulaiberlaku;

  namadireksi =
    xmlobject.getElementsByTagName("namadireksi")[0].firstChild.nodeValue;
  namadireksi = namadireksi.replace("*", "");
  document.getElementById("penandatangan").value = namadireksi;

  tembusan1 =
    xmlobject.getElementsByTagName("tembusan1")[0].firstChild.nodeValue;
  tembusan1 = tembusan1.replace("*", "");
  document.getElementById("tembusan1").value = tembusan1;

  tembusan2 =
    xmlobject.getElementsByTagName("tembusan2")[0].firstChild.nodeValue;
  tembusan2 = tembusan2.replace("*", "");
  document.getElementById("tembusan2").value = tembusan2;

  tembusan3 =
    xmlobject.getElementsByTagName("tembusan3")[0].firstChild.nodeValue;
  tembusan3 = tembusan3.replace("*", "");
  document.getElementById("tembusan3").value = tembusan3;

  tembusan4 =
    xmlobject.getElementsByTagName("tembusan4")[0].firstChild.nodeValue;
  tembusan4 = tembusan4.replace("*", "");
  document.getElementById("tembusan4").value = tembusan4;

  tembusan5 =
    xmlobject.getElementsByTagName("tembusan5")[0].firstChild.nodeValue;
  tembusan5 = tembusan5.replace("*", "");
  document.getElementById("tembusan5").value = tembusan5;

  namajabatan =
    xmlobject.getElementsByTagName("namajabatan")[0].firstChild.nodeValue;
  namajabatan = namajabatan.replace("*", "");
  document.getElementById("namajabatan").value = namajabatan;

  paragraf1 =
    xmlobject.getElementsByTagName("paragraf1")[0].firstChild.nodeValue;
  paragraf1 = paragraf1.replace("*", "");
  document.getElementById("paragraf1").value = paragraf1;

  paragraf2 =
    xmlobject.getElementsByTagName("paragraf2")[0].firstChild.nodeValue;
  paragraf2 = paragraf2.replace("*", "");
  document.getElementById("paragraf2").value = paragraf2;

  paragraf3 =
    xmlobject.getElementsByTagName("paragraf3")[0].firstChild.nodeValue;
  paragraf3 = paragraf3.replace("*", "");
  document.getElementById("paragraf3").value = paragraf3;

  paragraf4 =
    xmlobject.getElementsByTagName("paragraf4")[0].firstChild.nodeValue;
  paragraf4 = paragraf4.replace("*", "");
  document.getElementById("paragraf4").value = paragraf4;

  paragraf5 =
    xmlobject.getElementsByTagName("paragraf5")[0].firstChild.nodeValue;
  paragraf5 = paragraf5.replace("*", "");
  document.getElementById("paragraf5").value = paragraf5;

  paragraf6 =
    xmlobject.getElementsByTagName("paragraf6")[0].firstChild.nodeValue;
  paragraf6 = paragraf6.replace("*", "");
  document.getElementById("paragraf6").value = paragraf6;

  menimbang =
    xmlobject.getElementsByTagName("menimbang")[0].firstChild.nodeValue;
  menimbang = menimbang.replace("*", "");
  document.getElementById("menimbang").value = menimbang;

  mengingat =
    xmlobject.getElementsByTagName("mengingat")[0].firstChild.nodeValue;
  mengingat = mengingat.replace("*", "");
  document.getElementById("mengingat").value = mengingat;

  olddataid = "";
  olddataid =
    xmlobject.getElementsByTagName("olddataid")[0].firstChild.nodeValue;
  //olddataid=olddataid.replace("*","");
  document.getElementById("oldkomponx").innerHTML = olddataid;

  olddata = xmlobject.getElementsByTagName("olddata")[0].firstChild.nodeValue;
  while (olddata.indexOf("***") > -1) {
    olddata = olddata.replace("***", ">");
  }
  while (olddata.indexOf("###") > -1) {
    olddata = olddata.replace("###", "<");
  }
  document.getElementById("x2").innerHTML = olddata;

  newdataid =
    xmlobject.getElementsByTagName("newdataid")[0].firstChild.nodeValue;
  newdataid = newdataid.replace("*", "");
  document.getElementById("newkomponx").innerHTML = newdataid;

  newdata = xmlobject.getElementsByTagName("newdata")[0].firstChild.nodeValue;
  while (newdata.indexOf("***") > -1) {
    newdata = newdata.replace("***", ">");
  }
  while (newdata.indexOf("###") > -1) {
    newdata = newdata.replace("###", "<");
  }
  while (newdata.indexOf("ZZZ") > -1) {
    newdata = newdata.replace("ZZZ", "###");
  }
  document.getElementById("x4").innerHTML = newdata;
  add_new_data();
  //tabAction(document.getElementById("tabFRM0"), 0, "FRM", 1); //jangan tanya darimana
}

function savePromosi() {
  tipetransaksi = document.getElementById("tipetransaksi");
  tipetransaksi = tipetransaksi.options[tipetransaksi.selectedIndex].value;
  karyawanid = document.getElementById("karyawanid");
  karyawanid = karyawanid.options[karyawanid.selectedIndex].value;
  oldokasitugas = document.getElementById("oldokasitugas");
  oldokasitugas = oldokasitugas.options[oldokasitugas.selectedIndex].value;
  oldjabatan = document.getElementById("oldjabatan");
  oldjabatan = oldjabatan.options[oldjabatan.selectedIndex].value;
  oldtipekaryawan = document.getElementById("oldtipekaryawan");
  oldtipekaryawan = oldtipekaryawan.options[oldtipekaryawan.selectedIndex].value;
  olddepartemen = document.getElementById("olddepartemen");
  olddepartemen = olddepartemen.options[olddepartemen.selectedIndex].value;
  oldgolongan = document.getElementById("oldgolongan");
  oldgolongan = oldgolongan.options[oldgolongan.selectedIndex].value;
  oldsubbagian = document.getElementById("oldsubbagian");
  oldsubbagian = oldsubbagian.options[oldsubbagian.selectedIndex].value;
  newlokasitugas = document.getElementById("newlokasitugas");
  newlokasitugas = newlokasitugas.options[newlokasitugas.selectedIndex].value;
  newjabatan = document.getElementById("newjabatan");
  newjabatan = newjabatan.options[newjabatan.selectedIndex].value;
  newtipekaryawan = document.getElementById("newtipekaryawan");
  newtipekaryawan = newtipekaryawan.options[newtipekaryawan.selectedIndex].value;
  newgolongan = document.getElementById("newgolongan");
  newgolongan = newgolongan.options[newgolongan.selectedIndex].value;

  newsubbagian = document.getElementById("newsubbagian");
  newsubbagian = newsubbagian.options[newsubbagian.selectedIndex].value;

  newdepartemen = document.getElementById("newdepartemen");
  newdepartemen = newdepartemen.options[newdepartemen.selectedIndex].value;

  tanggalsk = trim(document.getElementById("tanggalsk").value);
  tanggalberlaku = trim(document.getElementById("tanggalberlaku").value);
  tanggalpen = trim(document.getElementById("tanggalpen").value);

  penandatangan = trim(document.getElementById("penandatangan").value);
  namajabatan = trim(document.getElementById("namajabatan").value);
  tembusan1 = document.getElementById("tembusan1").value;
  tembusan2 = document.getElementById("tembusan2").value;
  tembusan3 = document.getElementById("tembusan3").value;
  tembusan4 = document.getElementById("tembusan4").value;
  tembusan5 = document.getElementById("tembusan5").value;
  method = document.getElementById("method").value;
  noskedit = document.getElementById("nosk").value;

  paragraf1 = document.getElementById("paragraf1").value;
  paragraf2 = document.getElementById("paragraf2").value;
  paragraf3 = document.getElementById("paragraf3").value;
  paragraf4 = document.getElementById("paragraf4").value;
  paragraf5 = document.getElementById("paragraf5").value;
  paragraf6 = document.getElementById("paragraf6").value;

  menimbang = document.getElementById("menimbang").value;
  mengingat = document.getElementById("mengingat").value;
  tanggungjawab = document.getElementById("tanggungjawab").value;


  if (
    tipetransaksi == "" ||
    karyawanid == "" ||
    tanggalsk == "" ||
    tanggalberlaku == "" ||
    menimbang == "" ||
    mengingat == "" ||
    penandatangan == "" ||
    newlokasitugas == "" ||
    newjabatan == "" ||
    newtipekaryawan == "" ||
    newdepartemen == "" ||
    newgolongan == ""
  ) {
    alert(
      "Transaction type, Employee, Doc.Date, Effective Date, Considering, Signer, Location, Function, Employee Type, Departement and Grade are obligatory"
    );
  } else {
    param = "tanggalsk=" + tanggalsk + "&tanggalberlaku=" + tanggalberlaku;
    param += "&penandatangan=" + penandatangan;
    param +=
      "&tembusan1=" +
      tembusan1 +
      "&tembusan2=" +
      tembusan2 +
      "&tembusan3=" +
      tembusan3;
    param += "&tembusan4=" + tembusan4 + "&tipetransaksi=" + tipetransaksi;
    param += "&karyawanid=" + karyawanid + "&oldokasitugas=" + oldokasitugas;
    param +=
      "&oldjabatan=" + oldjabatan + "&oldtipekaryawan=" + oldtipekaryawan;
    param +=
      "&oldgolongan=" + oldgolongan + "&newlokasitugas=" + newlokasitugas;
    param += "&newjabatan=" + newjabatan + "&newgolongan=" + newgolongan;
    param += "&method=" + method + "&newtipekaryawan=" + newtipekaryawan;
    param += "&nosk=" + noskedit + "&namajabatan=" + namajabatan;
    param += "&tembusan5=" + tembusan5;
    param += "&tanggalpen=" + tanggalpen;
    param += "&tanggungjawab=" + tanggungjawab;
    param += "&oldsubbagian=" + oldsubbagian + "&newsubbagian=" + newsubbagian;
    param +=
      "&paragraf1=" +
      paragraf1 +
      "&paragraf2=" +
      paragraf2 +
      "&paragraf3=" +
      paragraf3 +
      "&paragraf4=" +
      paragraf4 +
      "&paragraf5=" +
      paragraf5 +
      "&paragraf6=" +
      paragraf6;
    param +=
      "&menimbang=" +
      menimbang +
      "&mengingat=" +
      mengingat +
      "&olddepartemen=" +
      olddepartemen +
      "&newdepartemen=" +
      newdepartemen;

    if (confirm("Saving, are you sure..?")) {
      tujuan = "sdm_slave_savePromosi.php";
      post_response_text(tujuan, param, respog);
    }
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Saved");
          clearForm();
          loadList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function clearForm() {
  tipetransaksi = document.getElementById("tipetransaksi");
  tipetransaksi.options[0].selected = true;
  karyawanid = document.getElementById("karyawanid");
  karyawanid.options[0].selected = true;
  oldokasitugas = document.getElementById("oldokasitugas");
  oldokasitugas.options[0].selected = true;
  oldjabatan = document.getElementById("oldjabatan");
  oldjabatan.options[0].selected = true;
  oldtipekaryawan = document.getElementById("oldtipekaryawan");
  oldtipekaryawan.options[0].selected = true;
  olddepartemen = document.getElementById("olddepartemen");
  olddepartemen.options[0].selected = true;

  oldgolongan = document.getElementById("oldgolongan");
  oldgolongan.options[0].selected = true;
  newlokasitugas = document.getElementById("newlokasitugas");
  newlokasitugas.options[0].selected = true;
  newjabatan = document.getElementById("newjabatan");
  newjabatan.options[0].selected = true;
  newtipekaryawan = document.getElementById("newtipekaryawan");
  newtipekaryawan.options[0].selected = true;
  newdepartemen = document.getElementById("newdepartemen");
  newdepartemen.options[0].selected = true;
  newgolongan = document.getElementById("newgolongan");
  newgolongan.options[0].selected = true;

  document.getElementById("tanggalsk").value = "";
  document.getElementById("tanggalpen").value = "";
  document.getElementById("tanggalberlaku").value = "";

  document.getElementById("menimbang").value =
    "1. Bahwa dalam rangka memperlancar kegiatan operasional perusahaan, dipandang perlu untuk melakukan perubahan status karyawan.\n2. Bahwa dalam rangka tertib administrasi, maka perubahan status tersebut perlu dituangkan dalam Surat Keputusan Direksi.";
  document.getElementById("mengingat").value =
    "1. Kebijakan dan Prosedur Mutasi/Promosi.\n2.Struktur Organisasi dan Standard Tenaga Kerja.";


  document.getElementById("penandatangan").value = "";
  document.getElementById("namajabatan").value = "";

  document.getElementById("tembusan1").value = "";
  document.getElementById("tembusan2").value = "";
  document.getElementById("tembusan3").value = "";
  document.getElementById("tembusan4").value = "";
  document.getElementById("tembusan5").value = "";

  document.getElementById("method").value = "insert";
  document.getElementById("nosk").value = "";
}


function loadList() {
  num = 0;
  param = "&page=" + num;
  tujuan = "sdm_slave_getPromosiList.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerlist").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cariSK(num) {
  tex = trim(document.getElementById("txtbabp").value);
  nama = trim(document.getElementById("namasch").value);
  const lokasitugas = document.querySelector("#lokasitugas").value;
  const jnstransaksi = document.querySelector("#jnstransaksi").value;
  const blnberlaku = document.querySelector("#blnberlaku").value;
  const tipekaryawan = document.querySelector("#tipekaryawan").value;

  param = "&page=" + num;
  param += "&tex=" + tex;
  param += "&nama=" + nama;
  param += `&lokasitugas=${lokasitugas}&jnstransaksi=${jnstransaksi}&blnberlaku=${blnberlaku}&tipekaryawan=${tipekaryawan}`;
  tujuan = "sdm_slave_getPromosiList.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerlist").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function delSK(nosk, karid) {
  param = "nosk=" + nosk + "&method=delete&karyawanid=" + karid;
  tujuan = "sdm_slave_savePromosi.php";
  if (confirm("Deleting Document " + nosk + ", are you sure..?"))
    post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postSK(nosk, karid) {
  param = "nosk=" + nosk + "&method=post&karyawanid=" + karid;
  tujuan = "sdm_slave_savePromosi.php";
  if (confirm("Posting Document " + nosk + ", are you sure..?"))
    post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function unpostSK(nosk, karid) {
  param = "nosk=" + nosk + "&method=unpost&karyawanid=" + karid;
  tujuan = "sdm_slave_savePromosi.php";
  if (confirm("Posting Document " + nosk + ", are you sure..?"))
    post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewSK(nosk, ev) {
  param = "nosk=" + nosk;
  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_printSK_pdf.php?" +
      param +
      "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .maximize();
}

function previewSKPengajuan(nosk, ev) {
  param = "nosk=" + nosk;
  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_printPengajuanSK_pdf.php?" +
      param +
      "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .maximize();
}

function pengajuansk(notransaksi, ev) {
  param = "notransaksi=" + notransaksi + "&method=pengajuansk";
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


function memotypeChange() {
  tipetransaksi =
    document.getElementById("tipetransaksi").options[
      document.getElementById("tipetransaksi").selectedIndex
    ].value;
  getFormatLetter();
}

function getFormatLetter() {
  tipetransaksi =
    document.getElementById("tipetransaksi").options[
      document.getElementById("tipetransaksi").selectedIndex
    ].value;

  param = "tipetransaksi=" + tipetransaksi + "&method=selecttipe";
  tujuan = "sdm_slave_savePromosi.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          isis = con.responseText.split("###");

          document.getElementById("menimbang").value = isis[0];
          document.getElementById("mengingat").value = isis[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function form_ajukan(nosk, karyawanid, kodeorg, tipesk) {
  // width = '300';
  // height = '';
  // content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
  // ev = 'event';
  // title = "";
  // showDialog1(title, content, width, height, ev);

  param =
    "method=form_ajukan" +
    "&nosk=" +
    nosk +
    "&karyawanid=" +
    karyawanid +
    "&kodeorg=" +
    kodeorg +
    "&tipesk=" +
    tipesk;
  tujuan = "sdm_slave_savePromosi.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // document.getElementById('containeraju').innerHTML = con.responseText;
          alertify
            .popup("Approval", con.responseText)
            .set({ resizable: true, overflow: false })
            .resizeTo("400px", "300px");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function ajukan() {
  jumlahlevel = document.getElementById("numrow").value;
  kepada = "";
  for (var i = 1; i <= jumlahlevel; i++) {
    if (kepada == "") {
      kepada = document.getElementById("kepada" + i).value;
    } else {
      kepada += "###" + document.getElementById("kepada" + i).value;
    }
  }
  notransaksi = document.getElementById("notran_aju").innerHTML;
  jenispersetujuanx = document.getElementById("jenispersetujuanx").value;
  param =
    "method=ajukan" +
    "&notransaksi=" +
    notransaksi +
    "&kepada=" +
    kepada +
    "&jenispersetujuanx=" +
    jenispersetujuanx;
  if (kepada == "") {
    alert("Isikan nama penyetuju.");
    return;
  }
  tujuan = "sdm_slave_savePromosi.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadList();
          closeDialog();
          alertify.popup().destroy();

        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
