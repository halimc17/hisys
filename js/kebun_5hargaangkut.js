function add_new_data() {
  document.getElementById("listinput").style.display = "block";
  document.getElementById("forminput").style.display = "block";
  document.getElementById("container").style.display = "none";
}

function displayList() {
  document.getElementById("container").style.display = "block";
  document.getElementById("forminput").style.display = "none";
  document.getElementById("listinput").style.display = "none";
  batalcari();
  loaddata(0);
}

function edit(blok, jenisvhc, pkstujuan, tt, tanggalberlaku, nospk) {
  document.getElementById("unit").value = blok.substr(0, 4);
  document.getElementById("divisi").value = blok.substr(0, 6);
  document.getElementById("blok").value = blok;
  document.getElementById("pkstujuanht").value = pkstujuan;
  document.getElementById("jnskendht").value = jenisvhc;
  document.getElementById("tahuntanam").value = tt;
  document.getElementById("tanggalberlaku").value = tanggalberlaku;
  document.getElementById("nospk").value = nospk;
  document.getElementById("tanggalberlaku").disabled = true;
  document.getElementById("nospk").disabled = true;

  setValue2("unit", blok.substr(0, 4));
  setValue2("divisi", blok.substr(0, 6));
  setValue2("blok", blok);
  setValue2("pkstujuanht", pkstujuan);
  setValue2("jnskendht", jenisvhc);
  setValue2("tahuntanam", tt);
  setValue2("nospk", nospk);

  document.getElementById("listinput").style.display = "block";
  document.getElementById("forminput").style.display = "block";
  document.getElementById("container").style.display = "none";
  previewdetail();
}

function deletefee(key, blok, namafee, jenisfee, jenisfeex, no) {
  param = "namafee=" + namafee;
  param += "&jenisfeex=" + jenisfeex;
  param += "&jenisfee=" + jenisfee;
  param += "&blok=" + blok;
  param += "&key=" + key;
  param += "&no=" + no;
  param += "&method=deletefee";
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listnamafee" + no).innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addfee(no) {
  namafee = trim(document.getElementById("namafee" + no).value);
  jenisfeex = document.getElementById("jenisfeex" + no).value;
  jenisfee = document.getElementById("jenisfee" + no).value;
  rpfee = document.getElementById("rpfee_" + no).value;
  blok = document.getElementById("blok_" + no).innerHTML;

  param = "namafee=" + namafee;
  param += "&jenisfeex=" + jenisfeex;
  param += "&jenisfee=" + jenisfee;
  param += "&rpfee=" + rpfee;
  param += "&blok=" + blok;
  param += "&no=" + no;
  param += "&method=addfee";
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listnamafee" + no).innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getfindtt() {
  find_blok = document.getElementById("find_blok").value;

  param = "find_blok=" + find_blok;
  param += "&method=getfindtt";
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("find_tt").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getblok() {
  find_divisi = document.getElementById("find_divisi").value;

  param = "find_divisi=" + find_divisi;
  param += "&method=getblok";
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isi = con.responseText.split("####");
          document.getElementById("find_blok").innerHTML = isi[0];
          document.getElementById("find_tt").innerHTML = isi[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function gettahuntanam(e) {
  unit = document.getElementById("unit").value;
  divisi = document.getElementById("divisi").value;
  tahuntanam = document.getElementById("tahuntanam").value;
  tanggalberlaku = document.getElementById("tanggalberlaku").value;

  param = "unit=" + unit;
  param += "&divisi=" + divisi;
  param += "&tanggalberlaku=" + tanggalberlaku;
  param += "&tahuntanam=" + tahuntanam;
  param += "&method=gettahuntanam";
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          i = con.responseText.split("####");
          if (e == "unit") {
            document.getElementById("divisi").innerHTML = i[0];
            document.getElementById("tahuntanam").innerHTML = i[1];
            document.getElementById("blok").innerHTML = i[2];
          } else if (e == "divisi") {
            document.getElementById("tahuntanam").innerHTML = i[1];
            document.getElementById("blok").innerHTML = i[2];
          } else {
            document.getElementById("blok").innerHTML = i[2];
          }
          document.getElementById("nospk").innerHTML = i[3];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function previewdetail() {
  unit = document.getElementById("unit").value;
  tahuntanam = document.getElementById("tahuntanam").value;
  divisi = document.getElementById("divisi").value;
  pkstujuanht = document.getElementById("pkstujuanht").value;
  jnskendht = document.getElementById("jnskendht").value;
  blok = document.getElementById("blok").value;
  tanggalberlaku = document.getElementById("tanggalberlaku").value;
  nospk = document.getElementById("nospk").value;

  param = "unit=" + unit;
  param += "&tahuntanam=" + tahuntanam;
  param += "&divisi=" + divisi;
  param += "&pkstujuanht=" + pkstujuanht;
  param += "&jnskendht=" + jnskendht;
  param += "&blok=" + blok;
  param += "&tanggalberlaku=" + tanggalberlaku;
  param += "&nospk=" + nospk;
  param += "&method=previewdetail";
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailinput").innerHTML = con.responseText;
          $(document).ready(function () {
            $(".select2").select2({
              dropdownAutoWidth: true,
            });
          });

          $(document).on(
            "focus",
            ".select2-selection.select2-selection--single",
            function (e) {
              $(this)
                .closest(".select2-container")
                .siblings("select:enabled")
                .select2("open");
            },
          );
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveAll(maxRow) {
  if (maxRow == "" || maxRow == 0) {
    alertify.alert("Data tidak ditemukan, proses dibatalkan !");
    return;
  }
  if (confirm("Simpan semua ???")) {
    savedetail(1, maxRow);
  }
}
function savedetail(currRow, maxRow) {
  nospk = document.getElementById("nospk").value;
  tanggalberlaku = document.getElementById("tanggalberlaku").value;
  method = document.getElementById("method").value;
  kegiatan = document.getElementById("kegiatan").value;
  keg_muat_tphpks1 = document.getElementById("muattphpks1").value;
  keg_muat_tphpks2 = document.getElementById("muattphpks2").value;
  keg_muat_tphpks3 = document.getElementById("muattphpks3").value;
  keg_muat_rampks = document.getElementById("muatramppks").value;
  keg_muat_tphpks5 = document.getElementById("muattphpks5").value;
  keg_muat_tphpks6 = document.getElementById("muattphpks6").value;
  keg_muat_tphpks7 = document.getElementById("muattphpks7").value;
  keg_angkut_tphpks1 = document.getElementById("angkuttphpks1").value;
  keg_angkut_tphpks2 = document.getElementById("angkuttphpks2").value;
  keg_angkut_tphpks3 = document.getElementById("angkuttphpks3").value;
  keg_angkut_rampks = document.getElementById("angkutramppks").value;
  keg_angkut_tphpks5 = document.getElementById("angkuttphpks5").value;
  keg_angkut_tphpks6 = document.getElementById("angkuttphpks6").value;
  keg_angkut_tphpks7 = document.getElementById("angkuttphpks7").value;
  blok = document.getElementById("blok_" + currRow).innerHTML;
  muat_tphpks1 = document.getElementById("muat_tphpks1_" + currRow).value;
  muat_tphpks2 = document.getElementById("muat_tphpks2_" + currRow).value;
  muat_tphpks3 = document.getElementById("muat_tphpks3_" + currRow).value;
  muat_rampks = document.getElementById("muat_rampks_" + currRow).value;
  muat_tphpks5 = document.getElementById("muat_tphpks5_" + currRow).value;
  muat_tphpks6 = document.getElementById("muat_tphpks6_" + currRow).value;
  muat_tphpks7 = document.getElementById("muat_tphpks7_" + currRow).value;
  angkut_tphpks1 = document.getElementById("angkut_tphpks1_" + currRow).value;
  angkut_tphpks2 = document.getElementById("angkut_tphpks2_" + currRow).value;
  angkut_tphpks3 = document.getElementById("angkut_tphpks3_" + currRow).value;
  angkut_rampks = document.getElementById("angkut_rampks_" + currRow).value;
  angkut_tphpks5 = document.getElementById("angkut_tphpks5_" + currRow).value;
  angkut_tphpks6 = document.getElementById("angkut_tphpks6_" + currRow).value;
  angkut_tphpks7 = document.getElementById("angkut_tphpks7_" + currRow).value;
  pkstujuan = document.getElementById("pkstujuan" + currRow).value;
  jenisvhc = document.getElementById("jenisvhc" + currRow).value;

  param = "";
  param += "&nospk=" + nospk;
  param += "&tanggalberlaku=" + tanggalberlaku;
  param += "&blok=" + blok;
  param += "&kegiatan=" + kegiatan;
  param += "&muat_tphpks1=" + muat_tphpks1;
  param += "&muat_tphpks2=" + muat_tphpks2;
  param += "&muat_tphpks3=" + muat_tphpks3;
  param += "&muat_rampks=" + muat_rampks;
  param += "&muat_tphpks5=" + muat_tphpks5;
  param += "&muat_tphpks6=" + muat_tphpks6;
  param += "&muat_tphpks7=" + muat_tphpks7;
  param += "&angkut_tphpks1=" + angkut_tphpks1;
  param += "&angkut_tphpks2=" + angkut_tphpks2;
  param += "&angkut_tphpks3=" + angkut_tphpks3;
  param += "&angkut_rampks=" + angkut_rampks;
  param += "&angkut_tphpks5=" + angkut_tphpks5;
  param += "&angkut_tphpks6=" + angkut_tphpks6;
  param += "&angkut_tphpks7=" + angkut_tphpks7;
  param += "&pkstujuan=" + pkstujuan;
  param += "&jenisvhc=" + jenisvhc;
  param += "&keg_muat_tphpks1=" + keg_muat_tphpks1;
  param += "&keg_muat_tphpks2=" + keg_muat_tphpks2;
  param += "&keg_muat_tphpks3=" + keg_muat_tphpks3;
  param += "&keg_muat_rampks=" + keg_muat_rampks;
  param += "&keg_muat_tphpks5=" + keg_muat_tphpks5;
  param += "&keg_muat_tphpks6=" + keg_muat_tphpks6;
  param += "&keg_muat_tphpks7=" + keg_muat_tphpks7;
  param += "&keg_angkut_tphpks1=" + keg_angkut_tphpks1;
  param += "&keg_angkut_tphpks2=" + keg_angkut_tphpks2;
  param += "&keg_angkut_tphpks3=" + keg_angkut_tphpks3;
  param += "&keg_angkut_rampks=" + keg_angkut_rampks;
  param += "&keg_angkut_tphpks5=" + keg_angkut_tphpks5;
  param += "&keg_angkut_tphpks6=" + keg_angkut_tphpks6;
  param += "&keg_angkut_tphpks7=" + keg_angkut_tphpks7;
  param += "&method=" + method;
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);
  document.getElementById("tr_" + currRow).style.backgroundColor = "cyan";
  // document.getElementById('rowfee_'+currRow).style.backgroundColor = 'cyan';
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          document.getElementById("tr_" + currRow).style.backgroundColor =
            "red";
          //unlockScreen();
        } else {
          if (currRow != undefined) {
            document.getElementById("tr_" + currRow).style.backgroundColor =
              "cyan";
          }
          currRow += 1;
          if (currRow > maxRow || maxRow == undefined) {
            alertify.alert("Done");
            loaddata();
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

function bataldetail() {
  document.getElementById("unit").value = "";
  document.getElementById("tahuntanam").value = "";
  document.getElementById("divisi").value = "";
  document.getElementById("detailinput").innerHTML = "";
  document.getElementById("tanggalberlaku").disabled = false;
  document.getElementById("method").value = "insert";
}
function batalcari() {
  setValue2("find_divisi", "");
  setValue2("find_blok", "");
  setValue2("find_tt", "");
  setValue2("find_tanggalberlaku", "");
  loaddata();
}
function loaddata(num) {
  find_divisi = document.getElementById("find_divisi").value;
  find_blok = document.getElementById("find_blok").value;
  find_tt = document.getElementById("find_tt").value;
  find_stat = document.getElementById("find_stat").value;
  find_nope = document.getElementById("find_nope").value;
  find_tanggalberlaku = document.getElementById("find_tanggalberlaku").value;
  param = "method=loaddata";
  param +=
    "&page=" + num + "&find_divisi=" + find_divisi + "&find_blok=" + find_blok;
  param += "&find_tt=" + find_tt;
  param += "&find_nope=" + find_nope;
  param += "&find_stat=" + find_stat;
  param += "&find_tanggalberlaku=" + find_tanggalberlaku;
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("container").innerHTML = con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddatatoexcel(num) {
  find_divisi = document.getElementById("find_divisi").value;
  find_blok = document.getElementById("find_blok").value;
  find_tt = document.getElementById("find_tt").value;
  find_stat = document.getElementById("find_stat").value;
  find_nope = document.getElementById("find_nope").value;
  param = "method=loaddata";
  param +=
    "&page=" + num + "&find_divisi=" + find_divisi + "&find_blok=" + find_blok;
  param += "&find_tt=" + find_tt;
  param += "&find_nope=" + find_nope;
  param += "&find_stat=" + find_stat;
  param += "&jenis=excel";
  tujuan = "kebun_slave_5hargaangkut.php";

  printnopopup(tujuan + "?" + param);
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function del(blok, pkstujuan, jenisvhc, tanggalberlaku, nospk) {
  param = "method=delete" + "&blok=" + blok;
  param += "&pkstujuan=" + pkstujuan;
  param += "&jenisvhc=" + jenisvhc;
  param += "&tanggalberlaku=" + tanggalberlaku;
  param += "&nospk=" + nospk;
  tujuan = "kebun_slave_5hargaangkut.php";
  if (confirm(" Anda yakin ???")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function tambahbiaya(blok, pkstujuan, jenisvhc, tanggalberlaku, nospk) {
  param = "blok=" + blok;
  param += "&pkstujuan=" + pkstujuan;
  param += "&nospk=" + nospk;
  param += "&jenisvhc=" + jenisvhc;
  param += "&tanggalberlaku=" + tanggalberlaku;
  param += "&method=tambahbiaya";
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          sp = "";
          if (nospk != "") {
            sp = ", No SPK " + nospk;
          }
          alertify
            .popup("Biaya tambahan, blok " + blok + sp, con.responseText)
            .set({
              resizable: true,
              maximizable: true,
              onclose: function () {
                loaddata();
              },
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

function deladd(blok, pkstujuan, jenisvhc, tanggalberlaku, tglawal, nospk) {
  param = "method=deladd" + "&blok=" + blok;
  param += "&pkstujuan=" + pkstujuan;
  param += "&jenisvhc=" + jenisvhc;
  param += "&tanggalberlaku=" + tanggalberlaku;
  param += "&tglawal=" + tglawal;
  param += "&nospk=" + nospk;
  tujuan = "kebun_slave_5hargaangkut.php";
  if (confirm(" Anda yakin ???")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          tambahbiaya(blok, pkstujuan, jenisvhc, tanggalberlaku, nospk);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function editadd(
  blok,
  jenisvhc,
  pkstujuan,
  tanggalberlaku,
  isimtp1,
  isimtp2,
  isimtp3,
  isimrp,
  isimtp5,
  isimtp6,
  isimtp7,
  isiatp1,
  isiatp2,
  isiatp3,
  isiarp,
  isiatp5,
  isiatp6,
  isiatp7,
  awal,
  akhir,
) {
  document.getElementById("jenisvhcadd").value = jenisvhc;
  document.getElementById("pkstujuanadd").value = pkstujuan;
  document.getElementById("blokadd").value = blok;
  document.getElementById("tglmulai").value = awal;
  document.getElementById("tglakhir").value = akhir;
  document.getElementById("muattphpks1add").value = isimtp1;
  document.getElementById("muattphpks2add").value = isimtp2;
  document.getElementById("muattphpks3add").value = isimtp3;
  document.getElementById("muatramppksadd").value = isimrp;
  document.getElementById("muattphpks5add").value = isimtp5;
  document.getElementById("muattphpks6add").value = isimtp6;
  document.getElementById("muattphpks7add").value = isimtp7;
  document.getElementById("angpkspks1add").value = isiatp1;
  document.getElementById("angpkspks2add").value = isiatp2;
  document.getElementById("angpkspks3add").value = isiatp3;
  document.getElementById("angramppksadd").value = isiarp;
  document.getElementById("angpkspks5add").value = isiatp5;
  document.getElementById("angpkspks6add").value = isiatp6;
  document.getElementById("angpkspks7add").value = isiatp7;
  document.getElementById("tanggalberlakuadd").value = tanggalberlaku;
  document.getElementById("tglawallamaadd").value = awal;
  document.getElementById("modeadd").value = "edit";
}

function simpanadd() {
  method = document.getElementById("methodadd").value;
  jenisvhc = document.getElementById("jenisvhcadd").value;
  pkstujuan = document.getElementById("pkstujuanadd").value;
  blok = document.getElementById("blokadd").value;
  tglmulai = document.getElementById("tglmulai").value;
  tglakhir = document.getElementById("tglakhir").value;
  muat_tphpks1 = document.getElementById("muattphpks1add").value;
  muat_tphpks2 = document.getElementById("muattphpks2add").value;
  muat_tphpks3 = document.getElementById("muattphpks3add").value;
  muat_rampks = document.getElementById("muatramppksadd").value;
  muat_tphpks5 = document.getElementById("muattphpks5add").value;
  muat_tphpks6 = document.getElementById("muattphpks6add").value;
  muat_tphpks7 = document.getElementById("muattphpks7add").value;
  angkut_tphpks1 = document.getElementById("angpkspks1add").value;
  angkut_tphpks2 = document.getElementById("angpkspks2add").value;
  angkut_tphpks3 = document.getElementById("angpkspks3add").value;
  angkut_rampks = document.getElementById("angramppksadd").value;
  angkut_tphpks5 = document.getElementById("angpkspks5add").value;
  angkut_tphpks6 = document.getElementById("angpkspks6add").value;
  angkut_tphpks7 = document.getElementById("angpkspks7add").value;
  tanggalberlaku = document.getElementById("tanggalberlakuadd").value;
  modeadd = document.getElementById("modeadd").value;
  tglawallama = document.getElementById("tglawallamaadd").value;
  nospk = document.getElementById("nospksdd").value;

  param = "";
  param += "&blok=" + blok;
  param += "&modeadd=" + modeadd;
  param += "&tglawallama=" + tglawallama;
  param += "&muat_tphpks1=" + muat_tphpks1;
  param += "&muat_tphpks2=" + muat_tphpks2;
  param += "&muat_tphpks3=" + muat_tphpks3;
  param += "&muat_rampks=" + muat_rampks;
  param += "&muat_tphpks5=" + muat_tphpks5;
  param += "&muat_tphpks6=" + muat_tphpks6;
  param += "&muat_tphpks7=" + muat_tphpks7;
  param += "&angkut_tphpks1=" + angkut_tphpks1;
  param += "&angkut_tphpks2=" + angkut_tphpks2;
  param += "&angkut_tphpks3=" + angkut_tphpks3;
  param += "&angkut_rampks=" + angkut_rampks;
  param += "&angkut_tphpks5=" + angkut_tphpks5;
  param += "&angkut_tphpks6=" + angkut_tphpks6;
  param += "&angkut_tphpks7=" + angkut_tphpks7;
  param += "&pkstujuan=" + pkstujuan;
  param += "&jenisvhc=" + jenisvhc;
  param += "&tglmulai=" + tglmulai;
  param += "&tglakhir=" + tglakhir;
  param += "&tanggalberlaku=" + tanggalberlaku;
  param += "&nospk=" + nospk;
  param += "&method=" + method;

  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          // alertify.set('notifier','position', 'top-center');
          // alertify.warning(con.responseText);
        } else {
          //alertify.alert('Done');
          tambahbiaya(blok, pkstujuan, jenisvhc, tanggalberlaku, nospk);
          document.getElementById("modeadd").value = "new";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function form_ajukan(unit, stats) {
  param = "method=form_ajukan" + "&unit=" + unit + "&stats=" + stats;
  tujuan = "kebun_slave_5hargaangkut.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("find_stat").value = stats;
          alertify.popup().set({
            onshow: function () {
              loaddata();
            },
          });
          alertify
            .popup("Approval, Unit " + unit, con.responseText)
            .set({
              resizable: true,
              maximizable: false,
            })
            .resizeTo("300px", "290px");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ajukan(jenispersetujuan, stats) {
  unit = document.getElementById("unitajukan").value;
  nopengajuan = document.getElementById("nopengajuan").innerHTML;
  kepada = document.getElementById("kepada").value;
  komentar = document.getElementById("komentar").value;

  param = "";
  param += "&unit=" + unit;
  param += "&stats=" + stats;
  param += "&jenispersetujuan=" + jenispersetujuan;
  param += "&nopengajuan=" + nopengajuan;
  param += "&kepada=" + kepada;
  param += "&komentar=" + komentar;
  param += "&method=ajukan";

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
          alertify.popup().destroy();
          document.getElementById("find_stat").value = "9";
          document.getElementById("find_nope").value = nopengajuan;
          loaddata();
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
            .popup("Detail Approval nomor : " + nopengajuan, con.responseText)
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
function ceknospk(nospk) {
  param = "";
  param += "&notransaksi=" + nospk;
  param += "&method=html";
  param += "&tipe=html";

  tujuan = "lgl_slave_pengajuanspk.php";
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

function munculupload() {
  document.getElementById("formupload").style.display = "block";
}

function unduhformat() {
  unit = document.getElementById("unit").value;
  pkstujuan = document.getElementById("pkstujuanht").value;
  tahuntanam = document.getElementById("tahuntanam").value;
  divisi = document.getElementById("divisi").value;
  blok = document.getElementById("blok").value;
  tanggalberlaku = document.getElementById("tanggalberlaku").value;
  jnskendht = document.getElementById("jnskendht").value;
  param =
    "method=unduhformat" +
    "&unit=" +
    unit +
    "&pkstujuan=" +
    pkstujuan +
    "&tahuntanam=" +
    tahuntanam +
    "&divisi=" +
    divisi +
    "&blok=" +
    blok +
    "&tanggalberlaku=" +
    tanggalberlaku +
    "&jeniskendaraan=" +
    jnskendht;

  ev = "event";
  judul = "excel";
  tujuan = "kebun_slave_5hargaangkut.php";
  printnopopup(tujuan + "?" + param);
}

function fileSelected(jenis) {
  // kodeorg = document.getElementById('kodeorg').value;

  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("jenis", jenis);
  formdata.append("pkstujuanht", getValue("pkstujuanht"));
  formdata.append("jnskendht", getValue("jnskendht"));
  // formdata.append("kodeorg", kodeorg);

  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "kebun_slave_5hargaangkut.php?method=fileSelected", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (jenis == "simpan") {
            document.getElementById("contdetailex").innerHTML = "";
            alertify.alert("Done");
          } else {
            document.getElementById("detailinput").innerHTML = con.responseText;
            $(document).ready(function () {
              $(".select2").select2({
                dropdownAutoWidth: true,
              });
            });

            $(document).on(
              "focus",
              ".select2-selection.select2-selection--single",
              function (e) {
                $(this)
                  .closest(".select2-container")
                  .siblings("select:enabled")
                  .select2("open");
              },
            );
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
