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

function updatecetak(nopo) {
  param = "nopo=" + nopo;
  param += "&method=updatecetak";
  tujuan = "log_slave_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          load_new_data(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function tambahsj() {
  sjx = document.getElementById("noreferensix").value;
  nopo = document.getElementById("no_po").value;
  param = "sjx=" + sjx + "&nopo=" + nopo;
  param += "&method=tambahsj";
  tujuan = "log_slave_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          load_new_data(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function deletenosjnopo() {
  idxz = document.getElementById("idxz").innerHTML;
  param = "idxz=" + idxz;
  param += "&method=deletenosjnopo";
  tujuan = "log_slave_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog2();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function showsj(nopo, title, ev) {
  width = "";
  height = "";
  content =
    "<fieldset><legend>" +
    nopo +
    "</legend><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=nopoxz name=nopoxz value=" +
    nopo +
    " />";
  showDialog2(title, content, width, height, ev);
}

function previewsj(title, ev) {
  //alert(param);
  nopo = document.getElementById("no_po").value;
  showsj(nopo, title, ev);
  param = "nopo=" + nopo + "&method=previewsj";
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contDetail").innerHTML = con.responseText;
          // loadfiles(nopP);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function fillField(
  nopo,
  tgl_po,
  supplier_id,
  sub_tot,
  disc,
  nil_pbbkb,
  nil_pphfinal,
  nil_pph,
  chkppn,
  nil_ppn,
  grnd_tot,
  diskon_nilai,
  stat,
  tglKrm,
  matauang,
  angKurs,
  loKrm,
  addcost,
  delivtime,
  noref,
  persenppn,
  persenpph,
  nil_pph22
) {
  document.getElementById("btncancel").value = "batal";

  if (stat == 3) {
    alert("This PO has been released");
    return;
  } else {
    status_inputan = 1;
    document.getElementById("dataAtas").style.display = "none";
    rproses = document.getElementById("proses").value = "edit_po";
    dnopp = nopo;

    param = "nopo=" + nopo + "&method=" + rproses;
    tujuan = "log_slave_po.php";
    post_response_text(tujuan, param, respog);

    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            show_form_po();
            document.getElementById("ppDetailTable").innerHTML =
              con.responseText;

            document.getElementById("tgl_po").value = tgl_po;
            document.getElementById("no_po").value = nopo;
            document.getElementById("Kurs").value = angKurs;
            document.getElementById("addcost").value = numberFormat(addcost, 2);

            getpoheader(nopo, matauang, supplier_id);

            document.getElementById("ppN").value = persenppn;
            document.getElementById("ppH").value = persenpph;

            document.getElementById("hslPPn").innerHTML = numberFormat(
              nil_ppn,
              2
            );
            document.getElementById("hslPPh").innerHTML = numberFormat(
              persenpph,
              2
            );
            document.getElementById("ppn").value = numberFormat(nil_ppn, 2);
            document.getElementById("pph").value = numberFormat(nil_pph, 2);
            document.getElementById("pph22").value = numberFormat(nil_pph22, 2);
            document.getElementById("total_harga_po").value = sub_tot;
            if (chkppn == 0) {
              document.getElementById("chkPpn").checked = false;
              document.getElementById("ppN").disabled = false;
            } else {
              document.getElementById("chkPpn").checked = true;
              document.getElementById("ppN").disabled = true;
            }
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }
}

function getpoheader(nopo, matauang, supplier_id) {
  param =
    "nopo=" + nopo + "&matauang=" + matauang + "&supplierid=" + supplier_id;
  param += "&method=getpoheader";
  tujuan = "log_slave_po.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          split = con.responseText.split("####");
          document.getElementById("npwporg").innerHTML = split[0];
          document.getElementById("mtUang").innerHTML = split[1];
          document.getElementById("supplier_id").innerHTML = split[2];
          document.getElementById("alamat_sup").innerHTML = split[3];
          document.getElementById("bank_acc").innerHTML = split[4];
          document.getElementById("npwp_sup").innerHTML = split[5];
          document.getElementById("tmpt_krm").innerHTML = split[6];
          document.getElementById("term_pay").innerHTML = split[7];
          document.getElementById("tdlistapproval").innerHTML = split[8];
          document.getElementById("delivtime").innerHTML = split[9];
          //document.getElementById('noreferensix').innerHTML=split[10];
          document.getElementById("ketUraian").value = split[11];
          document.getElementById("invc_krm").innerHTML = split[12];
          document.getElementById("subsupplier_id").innerHTML = split[13];
          loadtermin(nopo);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text(tujuan, param, respog);
}

function show_form_po() {
  document.getElementById("list_po").style.display = "none";
  document.getElementById("list_pp").style.display = "none";
  document.getElementById("form_po").style.display = "block";
}

function getbank(rek) {
  id_sup =
    document.getElementById("supplier_id").options[
      document.getElementById("supplier_id").selectedIndex
    ].value;

  param = "supplier_id=" + id_sup;
  param += "&rek=" + rek;
  param += "&proses=cek_supplier_bank";
  tujuan = "log_slave_save_po.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("bank_acc").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text(tujuan, param, respog);
}

function getnpwp(npwp) {
  nopo = document.getElementById("no_po").value;

  param = "nopo=" + nopo;
  param += "&npwporg=" + npwp;
  param += "&proses=getnpwp";
  tujuan = "log_slave_save_po.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("npwporg").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text(tujuan, param, respog);
}

function save_headher() {
  var tblpersetujuan = document.getElementById("tblpersetujuan");
  var rowpersetujuan = tblpersetujuan.rows.length;
  strUrl3 = "";

  for (i = 1; i <= rowpersetujuan; i++) {
    strUrl3 +=
      "&persetujuan" +
      i +
      "=" +
      document.getElementById("persetujuan_" + i).options[
        document.getElementById("persetujuan_" + i).selectedIndex
      ].value;
  }

  var tbl = document.getElementById("ppDetailTable");
  var row = tbl.rows.length;
  row = row - 6;
  strUrl2 = "";

  for (i = 0; i < row; i++) {
    try {
      spk = document.getElementById("spk_" + i).checked;
      if (spk == true) {
        valspk = "1";
      } else {
        valspk = "0";
      }
      if (strUrl2 != "") {
        strUrl2 +=
          "&nopp[]=" +
          trim(document.getElementById("rnopp_" + i).value) +
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("rkdbrg_" + i).value)
          ) +
          "&spekBrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("spek_brg_" + i).value)
          ) +
          "&rjmlh_psn[]=" +
          encodeURIComponent(
            trim(document.getElementById("jmlhDiminta_" + i).value)
          ) +
          "&rhrg_sat[]=" +
          document.getElementById("harga_satuan_" + i).value +
          "&rsatuan_unit[]=" +
          encodeURIComponent(trim(document.getElementById("sat_" + i).value)) +
          "&merk[]=" +
          document.getElementById("merk_" + i).options[
            document.getElementById("merk_" + i).selectedIndex
          ].value +
          "&rspk[]=" +
          encodeURIComponent(trim(valspk));
      } else {
        strUrl2 +=
          "&nopp[]=" +
          trim(document.getElementById("rnopp_" + i).value) +
          "&kdbrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("rkdbrg_" + i).value)
          ) +
          "&spekBrg[]=" +
          encodeURIComponent(
            trim(document.getElementById("spek_brg_" + i).value)
          ) +
          "&rjmlh_psn[]=" +
          encodeURIComponent(
            trim(document.getElementById("jmlhDiminta_" + i).value)
          ) +
          "&rhrg_sat[]=" +
          document.getElementById("harga_satuan_" + i).value +
          "&rsatuan_unit[]=" +
          encodeURIComponent(trim(document.getElementById("sat_" + i).value)) +
          "&merk[]=" +
          document.getElementById("merk_" + i).options[
            document.getElementById("merk_" + i).selectedIndex
          ].value +
          "&rspk[]=" +
          encodeURIComponent(trim(valspk));
      }
    } catch (e) {}
  }

  // hslPPn = document.getElementById('hslPPn').innerHTML;
  hslPPn = document.getElementById("ppn").value;
  hslPPh = document.getElementById("ppH").value;

  rproses = "save_headher";
  tgl_po = document.getElementById("tgl_po").value;
  nopo = document.getElementById("no_po").value;
  npwporg =
    document.getElementById("npwporg").options[
      document.getElementById("npwporg").selectedIndex
    ].value;
  mataUang =
    document.getElementById("mtUang").options[
      document.getElementById("mtUang").selectedIndex
    ].value;
  krs = trim(document.getElementById("Kurs").value);
  supplier_id =
    document.getElementById("supplier_id").options[
      document.getElementById("supplier_id").selectedIndex
    ].value;
  alamat_sup =
    document.getElementById("alamat_sup").options[
      document.getElementById("alamat_sup").selectedIndex
    ].value;
  npwp = document.getElementById("npwp_sup").value;
  rek = document.getElementById("bank_acc").value;
  subkelompok = document.getElementById("subsupplier_id").value;

  sub_tot = document.getElementById("total_harga_po");
  sub_tot.value = remove_comma(sub_tot);
  sub_tot = sub_tot.value;

  // Ambil nilai value2
  //   sub_tot = sub_tot.getAttribute("value2").replaceAll(",", "");
  // alert(sub_tot)

  angDiskon = document.getElementById("angDiskon");
  angDiskon.value = remove_comma(angDiskon);
  angDiskon = angDiskon.value;

  disc = document.getElementById("diskon").value;
  nil_pbbkb = document.getElementById("pbbkb");
  nil_pbbkb.value = remove_comma(nil_pbbkb);
  nil_pbbkb = nil_pbbkb.value;

  //pph final 
  nil_pphfinal = document.getElementById("pphfinal");
  nil_pphfinal.value = remove_comma(nil_pphfinal);
  nil_pphfinal = nil_pphfinal.value;

  nil_ppn = document.getElementById("ppN").value;
  nil_pph = document.getElementById("pph").value;
  nil_pph22 = document.getElementById("pph22").value;
  chkPpn = document.getElementById("chkPpn").checked;
  if (chkPpn == true) {
    valChkPpn = 1;
  } else {
    valChkPpn = 0;
  }
  addcost = document.getElementById("addcost");
  addcost.value = remove_comma(addcost);
  addcost = addcost.value;
  grnd_tot = document.getElementById("grand_total");
  grnd_tot.value = remove_comma(grnd_tot);
  grnd_tot = grnd_tot.value;

  delivtime =
    document.getElementById("delivtime").options[
      document.getElementById("delivtime").selectedIndex
    ].value;
  delivery_loc =
    document.getElementById("tmpt_krm").options[
      document.getElementById("tmpt_krm").selectedIndex
    ].value;
  delivery_invc =
    document.getElementById("invc_krm").options[
      document.getElementById("invc_krm").selectedIndex
    ].value;
  cara_pem =
    document.getElementById("term_pay").options[
      document.getElementById("term_pay").selectedIndex
    ].value;
  ketUrai = trim(document.getElementById("ketUraian").value);
  purchaser_id = trim(document.getElementById("purchaser_id").value);

  notecost = trim(document.getElementById("notecost").value);

  param = "method=" + rproses;

  param +=
    "&nopo=" +
    nopo +
    "&tglpo=" +
    tgl_po +
    "&npwporg=" +
    npwporg +
    "&mtUang=" +
    mataUang +
    "&Kurs=" +
    krs +
    "&supplier_id=" +
    supplier_id +
    "&alamat_sup=" +
    alamat_sup +
    "&npwp=" +
    npwp +
    "&rek=" +
    rek +
    "&subkelompok=" +
    subkelompok;

  param +=
    "&subtot=" +
    sub_tot +
    "&angDiskon=" +
    angDiskon +
    "&diskon=" +
    disc +
    "&pbbkb=" +
    nil_pbbkb +
    "&pphfinal=" +
    nil_pphfinal +
    "&ppn=" +
    nil_ppn +
    "&chkppn=" +
    valChkPpn +
    "&pph=" +
    nil_pph +
    "&pph22=" +
    nil_pph22 +
    "&addcost=" +
    addcost +
    "&grand_total=" +
    grnd_tot +
    "&hslPPn=" +
    hslPPn +
    "&hslPPh=" +
    hslPPh +
    "&notecost=" +
    notecost;

  param +=
    "&delivtime=" +
    delivtime +
    "&lok_kirim=" +
    delivery_loc +
    "&lok_invc=" +
    delivery_invc +
    "&cara_pembayarn=" +
    cara_pem +
    "&ketUraian=" +
    ketUrai +
    "&purchaser_id=" +
    purchaser_id +
    "&countpersetujuan=" +
    rowpersetujuan;

  param += strUrl2;
  param += strUrl3;
  tujuan = "log_slave_po.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("dataAtas").style.display = "block";
          displayList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  // if(confirm("Saving on :"+mataUang+' currency, are you sure?'))
  // {
  post_response_text(tujuan, param, respog);
  // }
}

function displayList() {
  document.getElementById("dataAtas").style.display = "block";
  document.getElementById("list_po").style.display = "block";
  document.getElementById("list_pp").style.display = "none";
  document.getElementById("form_po").style.display = "none";
  clear_all_data();
  load_new_data(0);
}

function clear_all_data() {
  status_inputan = 0;
  document.getElementById("tgl_po").value = "";
  document.getElementById("no_po").value = "";
  document.getElementById("npwporg").innerHTML = "";
  document.getElementById("mtUang").innerHTML = "";
  document.getElementById("Kurs").value = "";
  document.getElementById("supplier_id").innerHTML = "";
  document.getElementById("alamat_sup").innerHTML = "";
  document.getElementById("npwp_sup").innerHTML = "";
  document.getElementById("bank_acc").innerHTML = "";

  document.getElementById("delivtime").selectedIndex = 0;
  document.getElementById("tmpt_krm").innerHTML = "";
  document.getElementById("term_pay").innerHTML = "";
  document.getElementById("ketUraian").innerHTML = "";
  document.getElementById("tdlistapproval").innerHTML = "";

  document.getElementById("txtsearch").value = "";
  document.getElementById("tgl_cari").value = "";
  document.getElementById("tglrilis_cari").value = "";
  document.getElementById("txtnamsupsch").value = "";
  document.getElementById("txtsearch_nopp").value = "";

  // document.getElementById('supplier_id').value='';
  //document.getElementById('tgl_krm').value='';
  //document.getElementById('tmpt_krm').value='';
  // document.getElementById('bank_acc').value='';
  // document.getElementById('npwp_sup').value='';
  // document.getElementById('txtsearch').value='';
  // document.getElementById('tgl_cari').value='';
  // document.getElementById('proses').value='insert';
  // document.getElementById('tgl_krm').value='';
  // document.getElementById('term_pay').value='';
  // document.getElementById('ketUraian').value='';
  // document.getElementById('mtUang').value='';
  // document.getElementById('persetujuan_id').value='';
  // document.getElementById('fileupload').value='';
  // document.getElementById('delivtime').value='';
  // document.getElementById('npwporg').value='';
}

function ajukan(nopo) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;

  param = "method=ajukan" + "&nopo=" + nopo;
  tujuan = "log_slave_po.php";
  if (confirm("Anda yakin ingin mengajukan transaksi ini ??")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          load_new_data(paged);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function load_new_data(pg) {
  crnopo = document.getElementById("txtsearch").value;
  crtanggal = document.getElementById("tgl_cari").value;
  tglrilis_cari = document.getElementById("tglrilis_cari").value;
  txtnamsupsch = document.getElementById("txtnamsupsch").value;
  filterId = document.getElementById("filterId").value;
  txtsearch_nopp = document.getElementById("txtsearch_nopp").value;

  param =
    "method=loaddata&crnopo=" +
    crnopo +
    "&crtanggal=" +
    crtanggal +
    "&page=" +
    pg +
    "&txtnamsupsch=" +
    txtnamsupsch +
    "&tglrilis_cari=" +
    tglrilis_cari +
    "&filterId=" +
    filterId +
    "&txtsearch_nopp=" +
    txtsearch_nopp;
  tujuan = "log_slave_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText.split("####");
          document.getElementById("contain").innerHTML = data[0];
          document.getElementById("contx").innerHTML = data[1];
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function loadData(pg) {
  crnopo = document.getElementById("txtsearch").value;
  crtanggal = document.getElementById("tgl_cari").value;
  txtsearch_nopp = document.getElementById("txtsearch_nopp").value;

  param =
    "method=loaddata&crnopo=" +
    crnopo +
    "&crtanggal=" +
    crtanggal +
    "&page=" +
    pg +
    "&txtsearch_nopp=" +
    txtsearch_nopp;
  tujuan = "log_slave_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText.split("####");
          document.getElementById("contain").innerHTML = data[0];
          document.getElementById("contx").innerHTML = data[1];
          // loadNotifikasi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function getPage(pg) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loadData(paged);
  // cariBast(pg-1);
}

function getsuprph() {
  nodph = trim(document.getElementById("nodph").value);
  param = "proses=getsuprph" + "&nodph=" + nodph;
  tujuan = "log_slave_save_po.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("suprph").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function numberFormat(number, digit) {
  number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
  //Seperates the components of the number
  var components = parseFloat(number).toFixed(digit).split(".");
  //Comma-fies the first part
  components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  //Combines the two sections
  return components.join(".");
}

function clearfile() {
  document.getElementById("fileupload").value = "";
}

function deletefile(nopo) {
  param = "proses=deletefile" + "&nopo=" + nopo;
  tujuan = "log_slave_save_po.php";
  post_response_text(tujuan, param, respog);
  function respog() {
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

function savefile() {
  var fileup = document.getElementById("fileupload").files[0];
  var formdata = new FormData();
  formdata.append("fileup", fileup);
  formdata.append("nopo", getValue("no_po"));
  formdata.append("fileupload", getValue("fileupload"));
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_save_po.php?proses=savefile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          save_headher();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function adddph() {
  kode_pt = trim(document.getElementById("kode_pt").value);
  nodph = trim(document.getElementById("nodph").value);
  suprph = trim(document.getElementById("suprph").value);
  param =
    "proses=adddph" +
    "&nodph=" +
    nodph +
    "&kode_pt=" +
    kode_pt +
    "&suprph=" +
    suprph;
  tujuan = "log_slave_save_po.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //document.getElementById('dataAtas').style.display='block';
          //displayList();
          // alert(con.responseText);
          ar = con.responseText.split(",");
          fillField(
            ar[0],
            ar[1],
            ar[2],
            ar[3],
            ar[4],
            ar[5],
            ar[6],
            ar[7],
            ar[8],
            ar[9],
            ar[10],
            ar[11],
            ar[12],
            ar[13],
            ar[14],
            ar[15],
            ar[16],
            ar[17],
            ar[18],
            ar[19],
            ar[20],
            ar[21],
            ar[22],
            ar[23]
          );
          document.getElementById("nodph").value = "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function lihatfile(doc, ev) {
  param = "proses=lihatfile" + "&doc=" + doc;
  title = "Data Detail";
  showDialog2(
    title,
    "<iframe frameborder=0 style='width:795px;height:395px'" +
      " src='log_slave_save_po.php?" +
      param +
      "'></iframe>",
    "800",
    "400",
    ev
  );
  var dialog = document.getElementById("dynamic2");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}

/*tutup tambahan*/

function addCommas(nStr) {
  nStr += "";
  x = nStr.split(".");
  x1 = x[0];
  x2 = x.length > 1 ? "." + x[1] : "";
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, "$1" + "," + "$2");
  }
  return x1 + x2;
}

function show_list_pp() {
  clear_all_data();

  document.getElementById("container_pp").innerHTML = "";
  document.getElementById("list_po").style.display = "none";
  document.getElementById("list_pp").style.display = "block";
  document.getElementById("form_po").style.display = "none";
  document.getElementById("kode_pt").value = "";

  var tbl = document.getElementById("list_pp_table");
  var row = tbl.rows.length;
  row = row - 2;
  for (i = 1; i <= row; i++) {
    document.getElementById("plh_pp_" + i).checked = false;
  }
}

function cek_pp_pt(kdpt) {
  clear_all_data();

  if (kdpt == "") {
    kode_pt =
      document.getElementById("kode_pt").options[
        document.getElementById("kode_pt").selectedIndex
      ].value;
  } else {
    show_list_pp();
    kode_pt = kdpt;
    document.getElementById("kode_pt").disabled = true;
    document.getElementById("kode_pt").value = kdpt;
  }
  user_id = trim(document.getElementById("user_id").value);
  param = "kodept=" + kode_pt + "&id_user=" + user_id;
  param += "&proses=listPp";
  // alert(param);
  //    return;
  tujuan = "log_slave_po_detail.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          //show_form_po();
          document.getElementById("container_pp").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function display_number(id) {
  if (id != "") {
    sat = document.getElementById("harga_satuan_" + id);
    if (
      document.getElementById("mtUang").options[
        document.getElementById("mtUang").selectedIndex
      ].value == "IDR"
    ) {
      change_number(sat);
    }
    grnd_total();
  } else {
    nilDis = document.getElementById("angDiskon");
    change_number(nilDis);
  }
}

function display_number_so(id, tipe) {
  if (tipe == "1") {
    const format = (num) =>
      String(num).replace("/(?<!..*)(d)(?=(?:d{3})+(?:.|$))/g", "$1,");
    val = document.getElementById(id).value;
    hasil = document.getElementById(id);
    hasil.value = format(val);
  } else if (tipe == "2") {
    hasil = document.getElementById(id);
    change_number(hasil);
  }
}

function normal_number(id) {
  satu = document.getElementById("harga_satuan_" + id);
  satu.value = remove_comma_var(satu.value);
}

function calculate(id) {
  //alert(row);
  defult_tot = document.getElementById("realisasi_" + id).value;
  jmlh_brg = document.getElementById("jmlhDiminta_" + id).value;
  harga = document.getElementById("harga_satuan_" + id).value;
  document.getElementById("hidden_harga_satuan_" + id).value = harga;

  if (parseFloat(jmlh_brg) >= parseFloat(defult_tot) + 1) {
    alert("Quantity must equal or lower then total requested");
    document.getElementById("jmlhDiminta_" + id).value = "";
    return;
  } else {
    if (jmlh_brg == "" || harga == "") {
      a = document.getElementById("total_" + id);
      a.value = "";
      a = parseFloat(a.value);
    } else {
      harg = document.getElementById("harga_satuan_" + id);
      harg.value = remove_comma_var(harg.value);
      jmlh_sub = jmlh_brg * harg.value;

      if (jmlh_sub == 0) {
        document.getElementById("total_" + id).value = 0;
      } else {
        as = document.getElementById("total_" + id);
        as.value = jmlh_sub;
        if (
          document.getElementById("mtUang").options[
            document.getElementById("mtUang").selectedIndex
          ].value == "IDR"
        ) {
          change_number(as);
        }
      }
    }
  }
  grnd_total();
}

function calculateso() {
  jmlh_brg = document.getElementById("jmlhDimintaso").value;
  harga = document.getElementById("harga_satuan_so").value;

  if (jmlh_brg == "" || harga == "") {
    a = document.getElementById("total_so");
    a.value = "";
    a = parseFloat(a.value);
  } else {
    var jlhbarang = jmlh_brg.replace(/,/g, "");
    var hargabarang = harga.replace(/,/g, "");
    hasil = parseFloat(jlhbarang) * parseFloat(hargabarang);
    as = document.getElementById("total_so");
    as.value = hasil;
    if (
      document.getElementById("mtUang").options[
        document.getElementById("mtUang").selectedIndex
      ].value == "IDR"
    ) {
      change_number(as);
    }
  }
}

function grnd_total() {
  var tbl = document.getElementById("detailBody");
  var row = tbl.rows.length;
  row = row - 8;
  total = 0;
  for (i = 0; i < row; i++) {
    b = document.getElementById("total_" + i);
    b.value = remove_comma_var(b.value);
    total += parseFloat(b.value);
    if (
      document.getElementById("mtUang").options[
        document.getElementById("mtUang").selectedIndex
      ].value == "IDR"
    ) {
      change_number(b);
    }
    if (isNaN(total)) {
      total = 0;
    }
  }

  tot = document.getElementById("total_harga_po");
  tot.value = total;
  if (
    document.getElementById("mtUang").options[
      document.getElementById("mtUang").selectedIndex
    ].value == "IDR"
  ) {
    change_number(tot);
  }
  grandTotal();
}

function plusAll(id) {
  isiData = document.getElementById("detailBody");
  barisIsi = isiData.rows.length;
  barisIsi = barisIsi - 7;
  total = 0;
  for (i = 0; i < barisIsi; i++) {
    b = document.getElementById("total_" + i);
    b.value = remove_comma_var(b.value);
    total += parseFloat(b.value);
    change_number(b);
    // alert(b+"------"+total);
    //alert(b.value);
    //change_number(b);
    if (isNaN(total)) {
      total = 0;
    }
  }
  document.getElementById("total_harga_po").value = total;
  tot = document.getElementById("total_harga_po");
  tot.value = total;
  change_number(tot);

  //hitung diskon
  //nilPpn=document.getElementById('ppn').value;
  nil_dis = document.getElementById("diskon").value;
  angk = document.getElementById("angDiskon").value;
  if (nil_dis != "") {
    disc = (nil_dis * total) / 100;
    nilaiDis = document.getElementById("angDiskon");
    nilaiDis.value = disc;
    change_number(nilaiDis);
    document.getElementById("nilai_diskon").value = disc;
  } else {
    document.getElementById("diskon").value = 0;
    disc = (nil_dis * total) / 100;
    nilaiDis = document.getElementById("angDiskon");
    nilaiDis.value = disc;
    change_number(nilaiDis);
    document.getElementById("nilai_diskon").value = disc;
    /*document.getElementById('ppN').value=0;
                document.getElementById('ppn').value=0;
                nilPpn=0;*/
  }

  //ppn
  nPPn = document.getElementById("ppN").value;
  if (nPPn != "") {
    //nilP=document.getElementById('ppN').value;
    //dis=document.getElementById('nilai_diskon');
    //subTot=document.getElementById('total_harga_po');
    //dis.value=remove_comma(dis);
    //subTot.value=remove_comma(subTot);
    nilPpn = (parseFloat(total - disc) * nPPn) / 100;
    document.getElementById("hslPPn").innerHTML = nilPpn;
    document.getElementById("ppn").value = nilPpn;
  } else {
    document.getElementById("ppN").value = 0;
    document.getElementById("ppn").value = 0;
    nilPpn = 0;
  }
  //alert(total+"__"+disc+"___"+nilPpn);
  grnd_tot = parseFloat(total - disc) + parseFloat(nilPpn);
  test = document.getElementById("grand_total");
  test.value = grnd_tot;
  change_number(sb_tot);
  change_number(nilPpn);
  change_number(total);
}
function getZero() {
  dis = document.getElementById("diskon");
  if (dis.value == "") {
    dis.value = 0;
  }
  nPpn = document.getElementById("ppN");
  if (nPpn.value == "") {
    nPpn.value = 0;
  }
  angdis = document.getElementById("angDiskon");
  //angdis.value=remove_comma(angdis);
  if (angdis.value == "") {
    angdis.value = 0;
  }
}
function periksa_isi(obj) {
  if (trim(obj.value) == "") {
    alert("Please complete the form");
    obj.focus();
    return;
  }
}
function cek_isi(obj) {
  if (trim(obj.value) != "") {
    change_number(obj.value);
  } else {
    change_number(obj.value);
  }
}
function calculate_diskon() {
  sb_tot = document.getElementById("total_harga_po");
  sb_tot.value = remove_comma_var(sb_tot.value);
  nil_dis = document.getElementById("diskon").value;
  angk = document.getElementById("angDiskon").value;
  if (nil_dis == 0 || angk == 0) {
    document.getElementById("angDiskon").disabled = false;
    document.getElementById("diskon").disabled = false;
  }
  if (nil_dis != 0 || angk != 0) {
    document.getElementById("angDiskon").disabled = true;
    if (nil_dis > 100) {
      alert("Discount must lower than 100%");
      document.getElementById("diskon").value = "";
      document.getElementById("angDiskon").disabled = false;
    } else {
      disc = (nil_dis * sb_tot.value) / 100;
    }
    //  	grnd_tot=(sb_tot.value-disc)+pn;
    //document.getElementById('angDiskon').value=disc;
    nilaiDis = document.getElementById("angDiskon");
    nilaiDis.value = disc;
    document.getElementById("nilai_diskon").value = disc;
    change_number(nilaiDis);
    calculatePpn();
    grandTotal();
  }

  /*	document.getElementById('ppn').value=pn;
        pn=document.getElementById('ppn');
        change_number(pn);
*/
  /*document.getElementById('grand_total').value=grnd_tot;
        total=document.getElementById('grand_total');
        change_number(total);
*/
}

function calculate_angDiskon() {
  nilDis = document.getElementById("angDiskon");
  nilDis.value = remove_comma(nilDis);
  if (nilDis.value != 0) {
    document.getElementById("diskon").disabled = true;
    subTot = document.getElementById("total_harga_po");
    subTot.value = remove_comma(subTot);
    if (nilDis.value != subTot.value) {
      persenDis = parseFloat(nilDis.value / subTot.value) * 100;
    }
    if (persenDis < 100) {
      persen = persenDis;
      // alert(persen);
      document.getElementById("nilai_diskon").value = nilDis.value;
      document.getElementById("diskon").value = persen;
      //sbTot=document.getElementById('total_harga_po').value
    } else {
      alert("Discount value is wrong");
      document.getElementById("angDiskon").value = "";
      document.getElementById("diskon").value = "";
      document.getElementById("nilai_diskon").value = "";
      document.getElementById("diskon").disabled = false;
    }

    //nilDiskon=document.getElementById('angDiskon').value;
    calculatePpn(); 
    grandTotal();
  } else if (nilDis.value == 0) {
    document.getElementById("diskon").disabled = false;
    document.getElementById("diskon").value = 0;
  }
}
function calculatePbbkb() {
  NilPbbkb = document.getElementById("pbbkb").value;
  if (NilPbbkb == "") {
    document.getElementById("pbbkb").value = 0;
  }
  grandTotal();
}

//PPH FINAL 

function calculatePphfinal() {
  NilaiPphfinal = document.getElementById("pphfinal").value;
  if (NilaiPphfinal == "") {
    document.getElementById("pphfinal").value = 0;
  }
  grandTotal();
}


function checkChkPpn() {
  chkPpn = document.getElementById("chkPpn").checked;
  ppN = document.getElementById("ppN");
  var tbl2 = document.getElementById("detailBody");
  var rowChk = tbl2.rows.length;
  rowChk = rowChk - 8;

  for (j = 0; j < rowChk; j++) {
    b = document.getElementById("harga_satuan_" + j);
    c = document.getElementById("hidden_harga_satuan_" + j).value;
    if (chkPpn != true) {
      ppN.disabled = false;
      ppN.value = 0;
      b.disabled = true;
      if (b.value == c) b.value = remove_comma(b) * 1.1;
      else b.value = c;
      change_number(b);
      //tambahan
      document.getElementById("ppN").value = 10;
      calculatePpn();
      //tutup tambahan
    } else {
      ppN.disabled = true;
      ppN.value = 10;
      b.disabled = true;
      b.value = remove_comma(b) / 1.1;
      change_number(b);
    }
  }

  var tbl3 = document.getElementById("detailBody");
  var rowChk3 = tbl3.rows.length;
  rowChk3 = rowChk3 - 8;
  for (k = 0; k < rowChk3; k++) {
    // calculate(k);

    defult_tot = document.getElementById("realisasi_" + k).value;
    jmlh_brg = document.getElementById("jmlhDiminta_" + k).value;
    harga = document.getElementById("harga_satuan_" + k).value;

    if (parseFloat(jmlh_brg) >= parseFloat(defult_tot) + 1) {
      alert("Quantity must equal or lower then total requested");
      document.getElementById("jmlhDiminta_" + k).value = "";
      return;
    } else {
      if (jmlh_brg == "" || harga == "") {
        a = document.getElementById("total_" + k);
        a.value = "";
        a = parseFloat(a.value);
      } else {
        harg = document.getElementById("harga_satuan_" + k);
        harg.value = remove_comma_var(harg.value);
        jmlh_sub = jmlh_brg * harg.value;

        if (jmlh_sub == 0) {
          document.getElementById("total_" + k).value = 0;
        } else {
          as = document.getElementById("total_" + k);
          as.value = jmlh_sub;
          if (
            document.getElementById("mtUang").options[
              document.getElementById("mtUang").selectedIndex
            ].value == "IDR"
          ) {
            change_number(as);
          }
        }
      }
    }
  }

  var tbl4 = document.getElementById("detailBody");
  var row4 = tbl4.rows.length;
  row4 = row4 - 8;
  total = 0;

  for (l = 0; l < row4; l++) {
    b = document.getElementById("total_" + l);
    b.value = remove_comma_var(b.value);
    total += parseFloat(b.value);
    if (
      document.getElementById("mtUang").options[
        document.getElementById("mtUang").selectedIndex
      ].value == "IDR"
    ) {
      change_number(b);
    }
    if (isNaN(total)) {
      total = 0;
    }
  }

  tot = document.getElementById("total_harga_po");
  tot.value = total;

  if (
    document.getElementById("mtUang").options[
      document.getElementById("mtUang").selectedIndex
    ].value == "IDR"
  ) {
    // change_number(tot.value);
    tot.value = total;
  }
  calculatePpn();
}
function calculatePpn() {
  sb_tot = document.getElementById("total_harga_po");
  nilDiskon = document.getElementById("angDiskon");
  ppn = document.getElementById("ppN");

  sb_tot.value = remove_comma(sb_tot);
  nilDiskon.value = remove_comma(nilDiskon);
  ppn.value = remove_comma(ppn);

  if (ppn.value != 0 || ppn.value != "") {
    nilPpn =
      (parseFloat(sb_tot.value) - parseFloat(nilDiskon.value)) *
      (parseFloat(ppn.value) / 100);
    document.getElementById("hslPPn").innerHTML = numberFormat(nilPpn, 2);
    document.getElementById("ppn").value = numberFormat(nilPpn, 2);
  } else {
    document.getElementById("ppN").value = 0;
    document.getElementById("ppn").value = 0;
    document.getElementById("hslPPn").innerHTML = 0;
    nilPpn = 0;
  }

  if (ppn.value > 100) {
    alert("PPN harus lebih kecil atau sama dengan 100%");
    document.getElementById("ppN").value = "0";
    document.getElementById("ppn").value = "0";
    document.getElementById("hslPPn").innerHTML = "0";
  }

  grandTotal();
}

nilPpn = 0;
function calculatePph() {
  sb_tot = document.getElementById("total_harga_po");
  nilDiskon = document.getElementById("angDiskon");
  pph = document.getElementById("ppH");
  pph22 = document.getElementById("pph22");

  sb_tot.value = remove_comma(sb_tot);
  nilDiskon.value = remove_comma(nilDiskon);
  pph.value = remove_comma(pph);
  pph22.value = remove_comma(pph22);

  // if(pph.value==10 || pph.value==2){
  pn =
    (parseFloat(sb_tot.value) - parseFloat(nilDiskon.value)) *
    (parseFloat(pph.value) / 100);
  document.getElementById("hslPPh").innerHTML = numberFormat(pn, 2);
  if (pph22>0) {
    document.getElementById("pph22").value = numberFormat(pn, 2);
  }else{
    document.getElementById("pph").value = numberFormat(pn, 2);
  }
  // }else if(pph.value==''){
  // 	document.getElementById('ppH').value='0';
  // 	document.getElementById('pph').value='0';
  // 	document.getElementById('hslPPh').innerHTML='0';
  // }else{
  // 	document.getElementById('hslPPh').innerHTML=0;
  // 	document.getElementById('pph').value=0;
  // }

  if (pph.value > 100) {
    alert("PPH harus lebih kecil atau sama dengan 100%");
    document.getElementById("ppH").value = "0";
    document.getElementById("pph").value = "0";
    document.getElementById("pph22").value = "0";
    document.getElementById("hslPPh").innerHTML = "0";
  }

  grandTotal();
}

function calculatepph() {
  sb_tot = document.getElementById("total_harga_po");
  // nilDiskon=document.getElementById('angDiskon');
  pphrp = document.getElementById("pph");

  sb_tot.value = remove_comma(sb_tot);
  // nilDiskon.value=remove_comma(nilDiskon);
  pphrp.value = remove_comma(pphrp);
  if (pphrp<=0) {
    pphrp = document.getElementById("pph22");
    pphrp.value = remove_comma(pphrp);
  }

  //     if(pph.value==10 || pph.value==2){
  pn = (parseFloat(pphrp.value) / parseFloat(sb_tot.value)) * 100;
  document.getElementById("hslPPh").innerHTML = numberFormat(pn, 2);
  document.getElementById("ppH").value = numberFormat(pn, 2);
  // }else if(pph.value==''){
  // 	document.getElementById('ppH').value='0';
  // 	document.getElementById('pph').value='0';
  // 	document.getElementById('hslPPh').innerHTML='0';
  // }else{
  // 	document.getElementById('hslPPh').innerHTML=0;
  // 	document.getElementById('pph').value=0;
  // }
  //     }

  // if(pph.value > 100){
  // 	alert('PPH harus lebih kecil atau sama dengan 100%');
  // 	document.getElementById('ppH').value='0';
  // 	document.getElementById('pph').value='0';
  // 	document.getElementById('hslPPh').innerHTML='0';
  // }

  grandTotal();
}

nilPph = 0;
function grandTotal() {
  sb_tot = document.getElementById("total_harga_po");
  nilDiskon = document.getElementById("angDiskon");
  nilPbbkb = document.getElementById("pbbkb");
  nilPphfinal = document.getElementById("pphfinal");
  pph = document.getElementById("pph");
  ppH = document.getElementById("ppH");
  ppn = document.getElementById("ppn");
  pph22 = document.getElementById("pph22");

  sb_tot.value = remove_comma(sb_tot);
  nilDiskon.value = remove_comma(nilDiskon);
  nilPbbkb.value = remove_comma(nilPbbkb);
  nilPphfinal.value = remove_comma(nilPphfinal);
  pph.value = remove_comma(pph);
  ppn.value = remove_comma(ppn);
  pph22.value = remove_comma(pph22);


  grnd_tot =
    parseFloat(sb_tot.value) -
    parseFloat(nilDiskon.value) +
    parseFloat(ppn.value) +
    parseFloat(nilPbbkb.value) -
    parseFloat(nilPphfinal.value) -
    parseFloat(pph.value) -
    parseFloat(pph22.value);

  addcost = document.getElementById("addcost").value; //tambahan
  if (addcost == "" || addcost == "-") {
    document.getElementById("addcost").value = 0;
    addcost = 0;
  }

  grnd_tot = parseFloat(grnd_tot) + parseFloat(addcost); //tambahan
  if (isNaN(grnd_tot)) {
    grnd_tot = 0;
  }

  total = document.getElementById("grand_total");
  total.value = grnd_tot;

  if (
    document.getElementById("mtUang").options[
      document.getElementById("mtUang").selectedIndex
    ].value == "IDR"
  ) {
    change_number(sb_tot);
    change_number(nilDiskon);
    change_number(nilPbbkb);
    change_number(nilPphfinal);
    change_number(pph);
    change_number(pph22);
    change_number(ppn);
    change_number(total);
  }
}

function grandTotalpph() {
  sb_tot = document.getElementById("total_harga_po");
  sb_tot.value = remove_comma(sb_tot);
  nilDiskon = document.getElementById("angDiskon");
  nilPbbkb = document.getElementById("pbbkb");
  nilPbbkb.value = remove_comma(nilPbbkb);

  //pph final 
  nilPphfinal = document.getElementById("pphfinal");
  nilPphfinal.value = remove_comma(nilPphfinal);

  xpph = document.getElementById("ppH");
  ppn = document.getElementById("ppN");
  if (nilDiskon.value != "" || nilDiskon.value != 0) {
    nilDiskon.value = remove_comma(nilDiskon);
  } else {
    document.getElementById("diskon").value = 0;
    nilDiskon.value = 0;
  }

  // if(ppn.value!=0||ppn.value!=''){
  // nilPpn=(parseFloat((sb_tot.value-nilDiskon.value))*ppn.value)/100;
  // document.getElementById('hslPPn').innerHTML=numberFormat(nilPpn,2);
  // document.getElementById('ppn').value=numberFormat(nilPpn,2);

  // }else{
  // document.getElementById('ppN').value=0;
  // document.getElementById('ppn').value=0;
  // document.getElementById('hslPPn').innerHTML=0;
  // nilPpn=0;
  // }

  // if(xpph.value!=0||xpph.value!=''){
  // xnilPph = document.getElementById('pph').value;
  // nilPph = remove_comma_var(xnilPph);
  // // // nilPph=(parseFloat((sb_tot.value-nilDiskon.value))*pph.value)/100;
  // document.getElementById('hslPPh').innerHTML=nilPph;
  // document.getElementById('pph').value=nilPph;
  // }else{
  // document.getElementById('ppH').value=0;
  // document.getElementById('ppH').value=0;
  // document.getElementById('hslPPh').innerHTML=0;
  // nilPph=0;
  // }

  nilPpn = document.getElementById("ppn");
  nilPph = document.getElementById("pph");
  nilPph22 = document.getElementById("pph22");
  nilPpn.value = remove_comma(nilPpn);
  nilPph.value = remove_comma(nilPph);
  nilPph22.value = remove_comma(nilPph22);

  grnd_tot =
    parseFloat(sb_tot.value - nilDiskon.value) +
    parseFloat(nilPpn.value) +
    parseFloat(nilPbbkb.value) -
    parseFloat(nilPphfinal.value) -
    parseFloat(nilPph.value) -
    parseFloat(nilPph22.value);

  addcost = document.getElementById("addcost").value; //tambahan
  if (addcost == "" || addcost == "-") {
    document.getElementById("addcost").value = 0;
    addcost = 0;
  }

  grnd_tot = parseFloat(grnd_tot) + parseFloat(addcost); //tambahan
  if (isNaN(grnd_tot)) {
    grnd_tot = 0;
  }
  total = document.getElementById("grand_total");
  total.value = grnd_tot;

  if (
    document.getElementById("mtUang").options[
      document.getElementById("mtUang").selectedIndex
    ].value == "IDR"
  ) {
    change_number(sb_tot);
    change_number(total);
  }
  xxnilPph = document.getElementById("pph");
  change_number(xxnilPph);
  change_number(nilPpn);
  change_number(nilPph);
  change_number(nilPph22);
}

function grandTotalppn() {
  sb_tot = document.getElementById("total_harga_po");
  nilDiskon = document.getElementById("angDiskon");
  nilPbbkb = document.getElementById("pbbkb");
  nilPphfinal = document.getElementById("pphfinal");
  ppn = document.getElementById("ppn");
  pph = document.getElementById("pph");
  pph22 = document.getElementById("pph22");

  sb_tot.value = remove_comma(sb_tot);
  nilDiskon.value = remove_comma(nilDiskon);
  nilPbbkb.value = remove_comma(nilPbbkb);
  nilPphfinal.value = remove_comma(nilPphfinal);
  ppn.value = remove_comma(ppn);
  pph.value = remove_comma(pph);
  pph22.value = remove_comma(pph22);

  grnd_tot =
    parseFloat(sb_tot.value) -
    parseFloat(nilDiskon.value) +
    parseFloat(ppn.value) +
    parseFloat(nilPbbkb.value) -
    parseFloat(nilPphfinal.value) -
    parseFloat(pph.value) -
    parseFloat(pph22.value);

  total = document.getElementById("grand_total");
  total.value = grnd_tot;

  if (
    document.getElementById("mtUang").options[
      document.getElementById("mtUang").selectedIndex
    ].value == "IDR"
  ) {
    change_number(sb_tot);
    change_number(total);
  }
  change_number(ppn);
}

function process() {
  clear_all_data();
  document.getElementById("btncancel").value = "hapus";
  var tbl = document.getElementById("container_pp");
  var row = tbl.rows.length;
  row = row - 1;
  //alert(row);
  strUrl = "";
  for (i = 1; i <= row; i++) {
    ar = document.getElementById("plh_pp_" + i);
    if (ar.checked == true) {
      //alert(i);
      try {
        if (strUrl != "") {
          strUrl +=
            "&nopp[]=" +
            trim(document.getElementById("nopp_x" + i).innerHTML) +
            "&kdbrg[]=" +
            trim(document.getElementById("kdbrg_" + i).innerHTML);
        } else {
          strUrl +=
            "&nopp[]=" +
            trim(document.getElementById("nopp_x" + i).innerHTML) +
            "&kdbrg[]=" +
            trim(document.getElementById("kdbrg_" + i).innerHTML);
        }
      } catch (e) {}
    }
  }

  //return;
  if (strUrl == "") {
    alert("Choose one");
    return;
  } else {
    kodePt =
      document.getElementById("kode_pt").options[
        document.getElementById("kode_pt").selectedIndex
      ].value;
    param = "proses=createTable" + "&baris=" + row + "&kode_pt=" + kodePt;
    param += strUrl;
    //alert(param);
    tujuan = "log_slave_po_detail.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            // document.getElementById('detail_content').innerHTML=con.responseText;
            //generate_nopo();
            document.getElementById("dataAtas").style.display = "none";
            show_form_po();
            var a = con.responseText.split("###");
            // window.alert(a[0] + " " + a[1]);

            document.getElementById("no_po").value = a[0];
            //  alert(con.responseText);
            document.getElementById("ppDetailTable").innerHTML = a[1];
            getnpwp();
            // loadNotifikasi2();
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
    post_response_text(tujuan, param, respog);
  }
  //alert(strUrl);
}

function get_supplier() {
  id_sup =
    document.getElementById("supplier_id").options[
      document.getElementById("supplier_id").selectedIndex
    ].value;
  param = "supplier_id=" + id_sup;
  param += "&proses=cek_supplier";
  tujuan = "log_slave_save_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          /*
                                                                var a=con.responseText.split(",");
                                                                        // window.alert(a[0] + " " + a[1]);
                                                                document.getElementById('bank_acc').value=a[0];
                                                                //  alert(con.responseText);
                                                                document.getElementById('npwp_sup').value=a[1];
																*/

          //  document.getElementById('npwp_sup').value=con.responseText;
          const data = JSON.parse(con.responseText);
          // console.log(data);
          document.getElementById("npwp_sup").innerHTML = data.npwp;
          document.getElementById("subsupplier_id").innerHTML =
            data.subkelompok;
          getbank();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function loadNotifikasi() {
  proses = "getNotifikasi";
  param = "proses=" + proses;
  tujuan = "log_slave_save_po.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //displayList();
          document.getElementById("notifikasiKerja").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancel_headher() {
  status_batal = document.getElementById("btncancel").value;
  if (status_batal == "batal") {
    displayList();
  } else {
    nopo = document.getElementById("no_po").value;
    // alert(nopo);
    document.getElementById("proses").value = "";
    ar = document.getElementById("proses");
    ar.value = "delete_all";
    /*alert(document.getElementById('proses').value);
                return;*/
    ar = ar.value;
    param = "nopo=" + nopo + "&proses=" + ar;
    /*alert(param);
                return;*/
    tujuan = "log_slave_save_po.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            displayList();
            //document.getElementById('contain').innerHTML=con.responseText;
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
function delPoDetail(nopo, stat, StatIns) {
  if (stat == 1) {
    alert("Waiting Approval");
    return;
  } else {
    if (StatIns == 0) {
      if (confirm("Deleting, Are you sure?")) {
        // alert("berhasil");
        displayList();
      } else {
        return;
      }
    } else {
      document.getElementById("proses").value = "";
      ar = document.getElementById("proses");
      ar.value = "delete_all";
      /*alert(document.getElementById('proses').value);
                                return;*/
      ar = ar.value;
      param = "nopo=" + nopo + "&proses=" + ar;
      /*alert(param);
                                return;*/
      tujuan = "log_slave_save_po.php";

      function respog() {
        if (con.readyState == 4) {
          if (con.status == 200) {
            busy_off();
            if (!isSaveResponse(con.responseText)) {
              alert(con.responseText);
            } else {
              //document.getElementById('contain').innerHTML=con.responseText;
              displayList();
            }
          } else {
            busy_off();
            error_catch(con.status);
          }
        }
      }
      if (confirm("Deleting, are you sure")) {
        post_response_text(tujuan, param, respog);
      } else {
        return;
      }
    }
  }
}

function alasan_batal(nopo, stat) {
  param = "nopo=" + nopo + "&stat=" + stat + "&proses=get_alasan_batal";
  //    alert(param);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          width = "400";
          height = "200";
          content = "<div id=form_batal></div>";
          ev = "event";
          title = "Form Alasan Pembatalan PO";
          showDialog1(title, content, width, height, ev);
          //                    alert(con.responseText);
          document.getElementById("form_batal").innerHTML = con.responseText;
          return con.responseText;

          //                    displayList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  tujuan = "log_slave_save_po.php";
  post_response_text(tujuan, param, respog);
}

function delPo(nopo, stat, batal) {
  batal = document.getElementById("batal").value;
  /*	if(stat==1)
        {
                alert('Menunggu Persetujuan');
                return;
        }
        else if(stat==2)
        {
                alert("Porses Persetujuan Sudah Selesai");
                return;
        }
*/
  if (stat == 2) {
    alert("Being on correction progress");
    return;
  } else {
    document.getElementById("proses").value = "";
    ar = document.getElementById("proses");
    ar.value = "delete_all";
    //	alert(document.getElementById('proses').value);
    //			return;
    ar = ar.value;
    param = "nopo=" + nopo + "&batal=" + batal + "&proses=" + ar;
    //alert(param);
    //			return;
    tujuan = "log_slave_save_po.php";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            displayList();
            //document.getElementById('contain').innerHTML=con.responseText;
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }

    if (confirm("Are you sure delete this PO and it`s items")) {
      post_response_text(tujuan, param, respog);
      closeDialog();
    } else {
      return;
    }
  }
}
function agree_po() {
  width = "400";
  height = "200";
  //nopp=document.getElementById('nopp_'+id).value;
  content = "<div id=container></div>";
  ev = "event";
  title = "Persetujuan Atau Penolakan Form";
  showDialog1(title, content, width, height, ev);
  //get_data_pp();
}
function koreksiForm(npo) {
  width = "400";
  height = "160";
  //nopp=document.getElementById('nopp_'+id).value;
  content = "<div id=isi></div>";
  ev = "event";
  title = " Koreksi No PO  :" + npo;
  showDialog1(title, content, width, height, ev);
  //get_data_pp();
}
function getKoreksi(npo) {
  //met=document.getElementById('proses').value;
  //rnopo=document.getElementById('no_po').value;
  rnopo = npo;
  met = "getKoreksi";
  param = "proses=" + met + "&nopo=" + rnopo;
  tujuan = "log_slave_save_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          /*alert(con.responseText);
                                                                        return;*/
          koreksiForm(npo);
          document.getElementById("isi").innerHTML = con.responseText;
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
function get_data_pp(npo) {
  /*tbl=document.getElementById('ppDetailTable');
        row=tbl.rows.length;
        row=row-6;
       //alert(row);
        for(i=0;i<row;i++)
            {
                jmlh=document.getElementById('jmlhDiminta_'+i).value;
                harg_satuan=document.getElementById('harga_satuan_'+i).value;
                disk=document.getElementById('diskon').value;
                supp_id=document.getElementById('supplier_id').value;
                tgl_krm=document.getElementById('tlg_krm').value;
                loc_kirim=document.getElementById('tmpt_krm').value;
                paym_term=document.getElementById('term_pay').value;
                realis=document.getElementById('realisasi_'+i).value;
                kd_brg=document.getElementById('rkdbrg_'+i).value;
                if((jmlh=='')||(harg_satuan=='')||(disk=='')||(supp_id=='')||(tgl_kirim='')||(paym_term==''))
                    {
                        alert('Please Complete The Form First');
                        return;
                    }
            }*/

  met = document.getElementById("proses").value;
  //rnopo=document.getElementById('no_po').value;
  rnopo = npo;
  met = "get_form_approval";
  param = "proses=" + met + "&nopo=" + rnopo;
  tujuan = "log_slave_save_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          /*alert(con.responseText);
                                                                        return;*/
          agree_po();
          document.getElementById("container").innerHTML = con.responseText;
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
function forward_po() {
  nik = document.getElementById("persetujuan_id").value;
  snopo = document.getElementById("rnopp").value;
  met = document.getElementById("proses");
  met = met.value = "insert_forward_po";
  param = "id_user=" + nik + "&proses=" + met + "&nopo=" + snopo;
  tujuan = "log_slave_save_po.php";
  //alert(param);
  //return;
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          closeDialog();
          displayList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function close_po_a() {
  document.getElementById("close_po").style.display = "block";
  document.getElementById("test").style.display = "none";
}
function proses_release_po() {
  //document.getElementById('snopo').value=nopo;
  rnopo = document.getElementById("snopo").value;
  param = "nopo=" + rnopo + "&proses=proses_release_po";
  tujuan = "log_slave_save_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //document.getElementById('close_container').innerHTML=con.responseText;
          displayList();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}
function cancel_po() {
  closeDialog();
  displayList();
}
function clearRow(id) {
  /*document.getElementById('harga_satuan_'+id).value=0;
        document.getElementById('total_'+id).value=0;	
        document.getElementById('rnopp_'+id).value='';
        document.getElementById('rkdbrg_'+id).value='';
        document.getElementById('jmlhDiminta_'+id).value='';*/
  //alert(id);
  tabel = document.getElementById("detailBody");
  tabel.removeChild(tabel.rows[id]);
}
/* Function deleteDelete(id)
 * Fungsi untuk menghapus data Detail
 * I : id row (urutan row pada table Detail)
 * P : Menghapus data pada tabel Detail
 * O : Menghapus baris pada tabel Detail
 */
pengurang = 7;
function deleteDetail(id) {
  var tbl = document.getElementById("detailBody");
  var baris = tbl.rows.length;
  baris = baris - 7;
  //	alert(baris);
  //return;
  if (baris == 1) {
    nopo = document.getElementById("no_po").value;
    stat = 0;
    StatIns = 1;
    delPoDetail(nopo, stat, StatIns);
  } else if (baris > 1) {
    //alert(baris);

    //alert(tabel.rows[id]);

    //tabel.removeChild(tabel.rows[id]);
    //elem.parentNode.removeChild(elem);
    var detKode = document.getElementById("no_po");
    var rkd_brg = document.getElementById("rkdbrg_" + id);
    var nopp = document.getElementById("rnopp_" + id);
    var purchas = document.getElementById("user_id");

    param = "proses=detail_delete";
    param += "&nopo=" + detKode.value;
    param += "&kd_brg=" + rkd_brg.value;
    param += "&nopp=" + nopp.value;
    param += "&purchaser=" + purchas.value;

    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            // Success Response
            //alert(id);
            //baris=row;
            //tabel=document.getElementById("detailBody");
            //tabel.removeChild(tabel.rows[id]);

            row = document.getElementById("detail_tr_" + id);
            if (row) {
              //
              document.getElementById("harga_satuan_" + id).value = 0;
              document.getElementById("total_" + id).value = 0;
              document.getElementById("dtNopp_" + id).innerHTML = "";
              document.getElementById("dtKdbrg_" + id).innerHTML = "";
              document.getElementById("jmlhDiminta_" + id).value = "";
              row.style.display = "none";
              //pengurang+=1;
              plusAll();
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

    if (confirm("Deleting, are you sure?")) {
      post_response_text("log_slave_po_lokal_detail.php", param, respon);
    } else {
      return;
    }
  }
}
function cariNopo() {
  txtSearch = trim(document.getElementById("txtsearch").value);
  tglCari = trim(document.getElementById("tgl_cari").value);
  met = document.getElementById("proses");
  met = met.value = "update_data";
  met = trim(met);

  param = "txtSearch=" + txtSearch + "&tglCari=" + tglCari + "&proses=" + met;
  tujuan = "log_slave_save_po.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
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
function cek_pembuat(nopo) {
  rnop = nopo;
  //alert(rnop);
  param = "nopo=" + rnop + "&proses=cek_pembuat_po";
  tujuan = "log_slave_save_po.php";
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
function cariBast(num) {
  param = "proses=update_data";
  param += "&page=" + num;
  tujuan = "log_slave_save_po.php";
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
function getKurs() {
  mtung =
    document.getElementById("mtUang").options[
      document.getElementById("mtUang").selectedIndex
    ].value;
  tgl = document.getElementById("tgl_po").value;
  param = "mtUang=" + mtung + "&proses=getKurs" + "&tglpo=" + tgl;
  tujuan = "log_slave_save_po.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (con.responseText > 0) {
            //alert('ERROR TRANSACTION,\n test');
            document.getElementById("Kurs").value = con.responseText;
            document.getElementById("btnSaveHeader").disabled = false;
          } else {
            document.getElementById("Kurs").value = 0;
            document.getElementById("btnSaveHeader").disabled = true;
            alert(
              "ERROR TRANSACTION,\n Kurs " +
                mtung +
                " untuk tanggal " +
                tgl +
                " belum ada"
            );
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function checkIt(idid, count, nopp) {}

/*
function checkIt(idid,count,nopp)
{
	// Check jika ada nopp yang masih tercentang
	clean = true;
	for(a=0;a<count;a++){
		var valueA = a + 1,
			checkbox = document.getElementById('plh_pp_'+valueA).checked;
		if(checkbox){
			clean = false;
		}
	}
	
	for(a=0;a<count;a++){
		var valueA = a + 1,
			valueNoPP = document.getElementById('hiddennopp'+valueA).value;
		
		if(clean) {
			document.getElementById('tr_'+valueA).style.display = '';
		} else if(valueNoPP==nopp){
			document.getElementById('tr_'+valueA).style.display = '';
		} else {
			document.getElementById('tr_'+valueA).style.display = 'none';
		}
	}
}
*/

/*function checkStat(id)
{
        ar=document.getElementById('plh_pp_'+id);
        if(ar.checked==true)
        {
                ar.checked==true;
        }
        else if(ar.checked!=true)
        {
                ar.checked==false;
        }
}
*/
function doneKoreksi() {
  rnopo = document.getElementById("rnopp").value;
  param = "nopo=" + rnopo + "&proses=updateKoreksi";
  tujuan = "log_slave_save_po.php";
  if (confirm("Correction confirmation?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          displayList();
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function searchSupplier(title, content, ev) {
  width = "";
  height = "";
  showDialog1(title, content, width, height, ev);
  //alert('asdasd');
}
function findSupplier() {
  nmSupplier = document.getElementById("nmSupplier").value;
  param = "proses=getSupplierNm" + "&nmSupplier=" + nmSupplier;
  tujuan = "log_slave_save_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerSupplier").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function setData(kdSupp) {
  l = document.getElementById("supplier_id");

  for (a = 0; a < l.length; a++) {
    if (l.options[a].value == kdSupp) {
      l.options[a].selected = true;
    }
  }

  closeDialog();
  get_supplier();
}

function getnilaitermin() {
  subtotal = document.getElementById("total_harga_po");
  subtotal.value = remove_comma(subtotal);
  subtotal = subtotal.value;

  persentermin = document.getElementById("persentermin");
  persentermin.value = remove_comma(persentermin);
  persentermin = persentermin.value;

  hasil = (subtotal * persentermin) / 100;

  document.getElementById("nilaitermin").value = numberFormat(hasil, 2);
}

function getpersentermin() {
  subtotal = document.getElementById("total_harga_po");
  subtotal.value = remove_comma(subtotal);
  subtotal = subtotal.value;

  nilaitermin = document.getElementById("nilaitermin");
  nilaitermin.value = remove_comma(nilaitermin);
  nilaitermin = nilaitermin.value;

  hasil = (nilaitermin / subtotal) * 100;
  hasil = hasil.toFixed(2);

  document.getElementById("persentermin").value = numberFormat(hasil, 2);
}

function tambahtermin() {
  persentermin = document.getElementById("persentermin").value;
  nilaitermin = document.getElementById("nilaitermin").value;
  nopo = document.getElementById("no_po").value;
  param =
    "method=tambahtermin&nopo=" +
    nopo +
    "&nilaitermin=" +
    nilaitermin +
    "&persentermin=" +
    persentermin;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("nilaitermin").value = "";
          document.getElementById("persentermin").value = "";
          loadtermin(nopo);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletetermin(termin) {
  nopo = document.getElementById("no_po").value;
  param = "method=deletetermin&nopo=" + nopo + "&termin=" + termin;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadtermin(nopo);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadtermin(nopo) {
  param = "method=loadtermin&nopo=" + nopo;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("termindt").innerHTML = con.responseText;
          loadrefrensi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function carinoref(ev) {
  title = "Cari No. PO";
  width = "";
  height = "200";
  content =
    "<fieldset><div><table width=100%><tr><td style='min-width:70px;'>Cari No. PO</td><td>:</td><td><input class='myinputtext' style='width:120px' id='carinopodt'></td></tr><tr><td colspan=2></td><td><button class='mybutton' onclick='caripodt()'>Cari</button></td></tr></table><div id='listpodt' style='overflow:auto;width:auto;height:140px'></div></div></fieldset>";
  showDialog2(title, content, width, height, ev);
  var dialog = document.getElementById("dynamic2");
  dialog.style.top = "300px";
  dialog.style.left = "40%";

  // param = 'method=carinoref';

  // width = '';
  // height = '';
  // title = 'Cari No. PO';

  // showDialog2(title,"<iframe frameborder=0 src='log_slave_po.php?"+param+"'></iframe>",'','',ev);
  // var dialog = document.getElementById('dynamic2');
  // // dialog.style.top = '50px';
  // dialog.style.left = '25%';
}

function caripodt() {
  carinopodt = document.getElementById("carinopodt").value;

  param = "method=caripodt&nopo=" + carinopodt;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("listpodt").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function enterpo(nopo) {
  param = "method=enterpo&nopo=" + nopo;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          closeDialog2();
          loadrefrensi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadrefrensi() {
  param = "method=loadrefrensi";
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("refrensidt").innerHTML = con.responseText;
          loadmaterialso();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletesorefrensi(nopo, kodebarang) {
  param = "method=deletesorefrensi&nopo=" + nopo + "&kodebarang=" + kodebarang;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadrefrensi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

// function updatesorefrensi(nopo,kodebarang,jumlahmax,jumlah){
// 	if(parseFloat(jumlah) > parseFloat(jumlahmax)){
// 		alert('Jumlah barang lebih besar dari jumlah barang di PO => Kuantitas max = '+jumlahmax+'.');
// 		document.getElementById('jumlah_'+nopo+'_'+kodebarang).value = jumlahmax;
// 		return false;
// 	}
// 	param='method=updatesorefrensi&nopo='+nopo+'&kodebarang='+kodebarang+'&jumlah='+jumlah;
// 	tujuan='log_slave_po.php';
// 	post_response_text(tujuan, param, respog);

// 	function respog(){
// 		if(con.readyState == 4){
// 			if(con.status == 200){
// 				busy_off();
// 				if(!isSaveResponse(con.responseText)){
// 					alert(con.responseText);
// 				}else{
// 					loadrefrensi();
// 				}
// 			}else{
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
function updatesorefrensi(nopo, kodebarang, jumlahmax, jumlah, hargasatuan) {
  jumlah = document.getElementById("jumlah_" + nopo + "_" + kodebarang).value;
  if (parseFloat(jumlah) > parseFloat(jumlahmax)) {
    alert(
      "Jumlah barang lebih besar dari jumlah barang di PO => Kuantitas max = " +
        jumlahmax +
        "."
    );
    jumlah = document.getElementById(
      "jumlah_" + nopo + "_" + kodebarang
    ).value = jumlahmax;
    // return false;
  }
  param =
    "method=updatesorefrensi&nopo=" +
    nopo +
    "&kodebarang=" +
    kodebarang +
    "&jumlah=" +
    jumlah +
    "&hargasatuan=" +
    hargasatuan;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadrefrensi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addmaterialso() {
  // if(parseFloat(jumlah) > parseFloat(jumlahmax)){
  // alert('Jumlah barang lebih besar dari jumlah barang di PO => Kuantitas max = '+jumlahmax+'.');
  // document.getElementById('jumlah_'+nopo+'_'+kodebarang).value = jumlahmax;
  // return false;
  // }
  namabarangso = document.getElementById("nm_brg_so").value;
  jlhpesanso = document.getElementById("jmlhDimintaso").value;
  hargasatuanso = document.getElementById("harga_satuan_so").value;

  param =
    "method=addmaterialso&namabarangso=" +
    namabarangso +
    "&jlhpesanso=" +
    jlhpesanso +
    "&hargasatuanso=" +
    hargasatuanso;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          cancelmaterialso();
          loadmaterialso();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelmaterialso() {
  document.getElementById("nm_brg_so").value = "";
  document.getElementById("jmlhDimintaso").value = "";
  document.getElementById("harga_satuan_so").value = "";
  document.getElementById("total_so").value = "";
}

function loadmaterialso() {
  nopo = document.getElementById("no_po").value;
  param = "method=loadmaterialso&nopo=" + nopo;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          split = con.responseText.split("####");
          document.getElementById("listmaterialso").innerHTML = split[0];
          document.getElementById("total_harga_po").value = split[1];
          grandTotalpph();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletesomaterial(namabarang) {
  param = "method=deletesomaterial&namabarang=" + namabarang;
  tujuan = "log_slave_po.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadmaterialso();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
