// joki
function cekTipe() {
  unit = document.getElementById("unitId").value;
  periode = document.getElementById("periodeId").value;
  tipe = document.getElementById("tipe").value;
  akunpersediaan = document.getElementById("akunpersediaan").value;

  if (tipe == "8" || tipe == "8.1") {
    tipe = "GetDivisiBarang";
  }

  if (tipe == "GetDivisiBarang") {
    param =
      "unitId=" +
      unit +
      "&proses=" +
      tipe +
      "&periodeId=" +
      periode +
      "&akunpersediaan=" +
      akunpersediaan;
    post_response_text("log_slave_3cekselisihgudang.php", param, respon);
    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            data = con.responseText.split("####");
            document.getElementById("divisiId").disabled = false;
            document.getElementById("divisiId").innerHTML = data[0];

            if (document.getElementById("tipe").value == "8.1") {
              document.getElementById("barangId").disabled = false;
              document.getElementById("barangId").innerHTML = data[1];
            } else {
              document.getElementById("barangId").disabled = true;
              document.getElementById("barangId").innerHTML =
                "<option value=''></option>";
            }
            //getKar();
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  } else {
    document.getElementById("barangId").disabled = true;
    document.getElementById("divisiId").disabled = true;
    document.getElementById("divisiId").innerHTML =
      "<option value=''></option>";
    document.getElementById("barangId").innerHTML =
      "<option value=''></option>";
  }
}
function preview() {
  unit = document.getElementById("unitId").value;
  periode = document.getElementById("periodeId").value;
  tipe = document.getElementById("tipe").value;
  akunpersediaan = document.getElementById("akunpersediaan").value;
  divisiId = document.getElementById("divisiId").value;
  barangId = document.getElementById("barangId").value;

  if (tipe == "" || periode == "" || unit == "") {
    alert("Form Tidak Boleh Kosong...");
    return false;
  }

  if (tipe == "8" || tipe == "8.1") {
    if (divisiId == "") {
      alert("Divisi dan barang tidak boleh kosong");
      return false;
    }
  }

  param =
    "unitId=" +
    unit +
    "&proses=" +
    tipe +
    "&periodeId=" +
    periode +
    "&akunpersediaan=" +
    akunpersediaan +
    "&divisiId=" +
    divisiId +
    "&barangId=" +
    barangId;
  post_response_text("log_slave_3cekselisihgudang.php", param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // === Success Response
          document.getElementById("printContainer").innerHTML =
            con.responseText;

          infoBisaCek(unit, periode);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function exportExcel() {
  unit = document.getElementById("unitId").value;
  periode = document.getElementById("periodeId").value;
  tipe = document.getElementById("tipe").value;
  akunpersediaan = document.getElementById("akunpersediaan").value;
  divisiId = document.getElementById("divisiId").value;
  barangId = document.getElementById("barangId").value;

  if (tipe == "" || periode == "" || unit == "") {
    alert("Form Tidak Boleh Kosong...");
    return false;
  }

  if (tipe == "8" || tipe == "8.1") {
    if (divisiId == "") {
      alert("Divisi dan barang tidak boleh kosong");
      return false;
    }
  }

  param =
    "unitId=" +
    unit +
    "&proses=" +
    tipe +
    "&periodeId=" +
    periode +
    "&akunpersediaan=" +
    akunpersediaan +
    "&divisiId=" +
    divisiId +
    "&barangId=" +
    barangId +
    "&export=1";
  post_response_text("log_slave_3cekselisihgudang.php", param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          window.location = "tempExcel/" + con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showDetail(title, ev) {
  content =
    "<fieldset><legend>" +
    title +
    "</legend><div id=contDetail ></div></fieldset>";
  width = "";
  height = "";
  showDialog1(title, content, width, height, ev);
}
function infoBisaCek(gudang, periode) {
  param =
    "gudang=" +
    gudang +
    "&proses=infoBisaCek" +
    "&periodeId=" +
    periode +
    "&unitId=" +
    unit;
  tujuan = "log_slave_3cekselisihgudang.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (con.responseText == "") {
            document.getElementById("infoBisaCek").innerHTML =
              '<span style="color:green;font-weight:bold">' +
              "Saldo Digudang Divisi di unit " +
              unit +
              " Sudah di Mutasi<br>" +
              "Transaksi KM sudah posting di unit " +
              unit +
              "<br>" +
              "Transaksi Gudang sudah posting di unit " +
              unit +
              "<br>" +
              "Transaksi mutasi sudah terimakan di unit " +
              unit +
              "<br>" +
              "Saldo awal barang sudah sesuai" +
              "</span>";
          } else {
            document.getElementById("infoBisaCek").innerHTML = con.responseText;
          }
          praTutupBuku(gudang, periode);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function praTutupBuku(gudang, periode) {
  param =
    "gudang=" +
    gudang +
    "&proses=praTutupBuku" +
    "&periodeId=" +
    periode +
    "&unitId=" +
    unit;
  tujuan = "log_slave_3cekselisihgudang.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("praTutupBuku").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function updateData(noakun, prd, unit, ev) {
  title = "Data " + noakun;
  //showDetail(title,ev);
  param =
    "noakun=" +
    noakun +
    "&proses=cekAwal2" +
    "&periodeId=" +
    prd +
    "&unitId=" +
    unit;
  tujuan = "log_slave_3cekselisihgudang.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
          // document.getElementById('contDetail').innerHTML=con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveAllHarga(currRowMdr, currRowDet, maxrowmdr) {
  currRowMdr = parseFloat(currRowMdr);
  currRowDet = parseFloat(currRowDet);
  maxrowmdr = parseFloat(maxrowmdr);

  periode = document.getElementById("periodeId").value;
  akunpersediaan = document.getElementById("akunpersediaan").value;
  divisiId = document.getElementById("divisiId").value;

  notransaksi = document.getElementById("notransaksi_" + currRowMdr).innerHTML;
  notransaksireferensi = document.getElementById(
    "notransaksireferensi_" + currRowMdr
  ).innerHTML;
  hargaratabenar = document.getElementById(
    "hargaratabenar_" + currRowMdr
  ).innerHTML;
  jumlah = document.getElementById("jumlah_" + currRowMdr).innerHTML;
  hargarataawal = document.getElementById(
    "hargarataawal_" + currRowMdr
  ).innerHTML;
  baris = document.getElementById("baris" + currRowMdr).innerHTML;

  kodeblok = document.getElementById("kodeblok_" + currRowMdr).innerHTML;
  kodekegiatan = document.getElementById(
    "kodekegiatan_" + currRowMdr
  ).innerHTML;

  masukxharga_perbaiki = document.getElementById(
    "masukxharga_perbaiki"
  ).innerHTML;
  keluarxharga_perbaiki = document.getElementById(
    "keluarxharga_perbaiki"
  ).innerHTML;
  nilaisaldoakhir_perbaiki = document.getElementById(
    "nilaisaldoakhir_perbaiki"
  ).innerHTML;

  barangId = document.getElementById("barangId").value;

  if (notransaksi == "") {
    alert("Notransaksi wajib diisi.");
    return;
  }
  if (notransaksireferensi == "") {
    // alert("notransaksireferensi wajib diisi.");return;
  }

  param = "&notransaksi=" + notransaksi;
  param += "&barangId=" + barangId;
  param += "&notransaksireferensi=" + notransaksireferensi;
  param += "&kodeblok=" + kodeblok;
  param += "&kodekegiatan=" + kodekegiatan;
  param += "&hargarataawal=" + hargarataawal;
  param += "&hargaratabenar=" + hargaratabenar;
  param += "&jumlah=" + jumlah;
  param += "&divisiId=" + divisiId;
  param += "&akunpersediaan=" + akunpersediaan;
  param += "&periode=" + periode;
  param += "&baris=" + baris;
  param += "&masukxharga_perbaiki=" + masukxharga_perbaiki;
  param += "&keluarxharga_perbaiki=" + keluarxharga_perbaiki;
  param += "&nilaisaldoakhir_perbaiki=" + nilaisaldoakhir_perbaiki;

  param += "&proses=savedataHarga";
  tujuan = "log_slave_3cekselisihgudang.php";
  post_response_text(tujuan, param, respog);
  // alert(param);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
          document.getElementById("baris" + currRowMdr).style.backgroundColor =
            "red";
          unlockScreen();
        } else {
          document.getElementById("baris" + currRowMdr).style.backgroundColor =
            "cyan";

          currRowMdr += 1;
          // if(currRowDet>maxrowmdr){
          // currRowMdr += 1;
          // currRowDet = 1;
          // }

          if (currRowMdr > maxrowmdr) {
            alert("Done");
            document.getElementById("printContainer").innerHTML = "";
          } else {
            saveAllHarga(currRowMdr, currRowDet, maxrowmdr);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
// a

// posting gudang
function previewPosting(tipe, notransaksi, gudang, tgls, tgle, ev) {
  param =
    "notransaksi=" +
    notransaksi +
    "&tipe=" +
    tipe +
    "&gudang=" +
    gudang +
    "&statussaldo=ya";
  tujuan = "log_slave_posting_gudang.php";
  //if (confirm('Posting ' + notransaksi + ', Are you sure..?')) {
  post_response_text(tujuan, param, respog);
  lockScreen("wait");
  //}
  function respog() {
    if (con.readyState == 4) {
      unlockScreen();
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          title = notransaksi;
          width = "auto";
          height = "auto";
          content = con.responseText;
          //alert(content);
          //showDialog1(title, content, width, height, ev);
          // Tambahkan input hidden/readonly untuk gudang
          content =
            "<div style='margin-bottom:10px;'>" +
            "<input type='hidden' id='gudang_posting' value='" +
            gudang +
            "' readonly disabled style='font-weight:bold;color:blue;' /><input type='hidden' id='" +
            gudang +
            "_start' value='" +
            tgls +
            "'><input type='hidden' id='" +
            gudang +
            "_end' value='" +
            tgle +
            "'>" +
            "</div>" +
            con.responseText;

          alertify.popup().destroy();
          alertify
            .popup(title, content)
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

function prosesPosting(maxRow, tipetrx, notransaksi) {
  if (confirm("Are you sure?")) {
    doPostingmaterial(maxRow, tipetrx, 1, notransaksi);
  } else {
    alertify.popup().destroy();
    closeDialog();
  }
}

function doPostingmaterial(maxRow, tipetrx, currentRow, notransaksi) {
  tipetransaksi = tipetrx;
  // gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
  gudang = document.getElementById("gudang_posting").value; // ambil dari popup
  if (gudang == "") {
    alert("Gudang masih kosong...");
    return false;
  }
  try {
    tanggal = trim(document.getElementById("tanggal" + currentRow).innerHTML);
  } catch (e) {
    if (currentRow == 1) {
      setPosting(gudang, notransaksi, 1); //tidak ada detail transaksi, bisa saja karena sudah diposting, tetapi flag header belum terupdate
    }
  }
  kodebarang = trim(
    document.getElementById("kodebarang" + currentRow).innerHTML
  );
  satuan = trim(document.getElementById("satuan" + currentRow).innerHTML);
  jumlah = trim(document.getElementById("jumlah" + currentRow).innerHTML);
  kodept = trim(document.getElementById("kodept" + currentRow).innerHTML);
  try {
    kodeblok = trim(document.getElementById("kodeblok" + currentRow).innerHTML);
  } catch (e) {
    kodeblok = "";
  }
  gudangx = "";
  untukunit = "";
  untukpt = "";
  supplier = "";
  nopo = "";
  nopp = "";
  hargasatuan = "0";
  kodekegiatan = "";
  kodemesin = "";
  switch (tipetrx) {
    case "3":
      gudangx = trim(
        document.getElementById("gudangx" + currentRow).innerHTML.split("-")[0]
      );
      hargasatuan = trim(
        document.getElementById("hargasatuan" + currentRow).innerHTML
      );
      break;
    case "5":
    case "8":
      kodesegment = trim(
        document.getElementById("kodesegment" + currentRow).innerHTML
      );
      untukpt = trim(document.getElementById("untukpt" + currentRow).innerHTML);
      untukunit = trim(
        document.getElementById("untukunit" + currentRow).innerHTML
      );
      kodekegiatan = trim(
        document.getElementById("kodekegiatan" + currentRow).innerHTML
      );
      kodemesin = trim(
        document.getElementById("kodemesin" + currentRow).innerHTML
      );
      break;
    case "2":
      untukunit = trim(
        document.getElementById("untukunit" + currentRow).innerHTML
      );
      kodekegiatan = trim(
        document.getElementById("kodekegiatan" + currentRow).innerHTML
      );
      kodemesin = trim(
        document.getElementById("kodemesin" + currentRow).innerHTML
      );
      break;
    case "7":
      gudangx = trim(
        document.getElementById("gudangx" + currentRow).innerHTML.split("-")[0]
      );
      break;
    case "1":
      supplier = trim(
        document.getElementById("supplier" + currentRow).innerHTML
      );
      nopo = trim(document.getElementById("nopo" + currentRow).innerHTML);
      hargasatuan = trim(
        document.getElementById("hargasatuan" + currentRow).innerHTML
      );
      nopp = trim(document.getElementById("nopp" + currentRow).innerHTML);
      break;
    case "6":
      hargasatuan = trim(
        document.getElementById("hargasatuan" + currentRow).innerHTML
      );
      supplier = trim(
        document.getElementById("supplier" + currentRow).innerHTML
      );
      nopo = trim(document.getElementById("nopo" + currentRow).innerHTML);
      break;
    case "0":
      kdpabrikasi = trim(
        document.getElementById("kdpabrikasi" + currentRow).innerHTML
      );
      hargasatuan = trim(
        document.getElementById("hargasatuan" + currentRow).innerHTML
      );
      break;
    case "4":
      hargasatuan = trim(
        document.getElementById("hargasatuan" + currentRow).innerHTML
      );
      kodekegiatan = trim(
        document.getElementById("kodekegiatan" + currentRow).innerHTML
      );
      kodemesin = trim(
        document.getElementById("kodemesin" + currentRow).innerHTML
      );
      break;
  }
  //periksa tanggal=====================================================================
  // gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
  gudang = document.getElementById("gudang_posting").value; // ambil dari popup
  if (gudang == "") {
    alert("Gudang masih kosong...");
    return false;
  }
  x = tanggal;
  _start = document.getElementById(gudang + "_start").value;
  _end = document.getElementById(gudang + "_end").value;
  while (x.lastIndexOf("-") > -1) {
    x = x.replace("-", "");
  }
  while (x.lastIndexOf("-") > -1) {
    x = x.replace("/", "");
  }

  curdateY = x.substr(4, 4).toString();
  curdateM = x.substr(2, 2).toString();
  curdateD = x.substr(0, 2).toString();
  curdate = curdateY + curdateM + curdateD;
  curdate = parseInt(curdate);
  if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
    alert("Date out of range");
  } else {
    //====================================================================================
    if ((tipetransaksi == 3 || tipetransaksi == 7) && gudangx == "") {
      alert("Data component (Source or Destination) is missing");
    } else if (tipetransaksi == 5 && untukpt == "") {
      alert("Data component (Destination Company) is missing");
    } else {
      document.getElementById("row" + currentRow).style.backgroundColor =
        "orange";
      param = "tipetransaksi=" + tipetransaksi + "&tanggal=" + tanggal;
      param +=
        "&kodebarang=" + kodebarang + "&satuan=" + satuan + "&jumlah=" + jumlah;
      param +=
        "&kodept=" + kodept + "&gudangx=" + gudangx + "&untukpt=" + untukpt;
      param +=
        "&gudang=" +
        gudang +
        "&kodeblok=" +
        kodeblok +
        "&notransaksi=" +
        notransaksi;
      param +=
        "&nopo=" +
        nopo +
        "&supplier=" +
        supplier +
        "&hargasatuan=" +
        hargasatuan +
        "&untukunit=" +
        untukunit;
      param +=
        "&kodekegiatan=" +
        kodekegiatan +
        "&kodemesin=" +
        kodemesin +
        "&nopp=" +
        nopp;
      param += "&currentRow=" + currentRow;
      if (tipetransaksi == 0) {
        param += "&kdpabrikasi=" + kdpabrikasi;
      }
      tujuan = "log_slave_savePosting_selisihgudang.php";
      post_response_text(tujuan, param, respog);
      lockScreen("wait");
    }
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
          document.getElementById("row" + currentRow).style.backgroundColor =
            "red";
          unlockScreen();
        } else {
          document.getElementById("row" + currentRow).style.backgroundColor =
            "green";
          currentRow += 1;
          if (currentRow > maxRow) {
            setPosting(gudang, notransaksi, 1); //beri flag 1 pada kolom post
          } else {
            doPostingmaterial(maxRow, tipetrx, currentRow, notransaksi);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
        unlockScreen();
      }
    }
  }
}

function setPosting(gudang, notransaksi, status) {
  // param = 'notransaksi=' + notransaksi + '&status=' + status + '&gudang=' + gudang;
  // tujuan = 'log_slave_ubahFlagPosting.php';
  // post_response_text(tujuan, param, respog);
  // function respog() {
  // 	if (con.readyState == 4) {

  // 		if (con.status == 200) {
  // 			busy_off();
  // 			if (!isSaveResponse(con.responseText)) {
  // 				alert(con.responseText);
  // 				document.getElementById('indukrow' + currentRow).style.backgroundColor = 'red';
  // 				unlockScreen();
  // 			} else {
  unlockScreen();
  alert("Done");
  alertify.popup().destroy();
  preview();
  // 				// closeDialog();
  // 			}
  // 		} else {
  // 			busy_off();
  // 			error_catch(con.status);
  // 			unlockScreen();
  // 		}
  // 	}
  // }
}
// akhir posting gudang

function PerbaikiSaldoBulanan(gudang, periode) {
  param =
    "gudang=" + gudang + "&proses=PerbaikiSaldoBulanan" + "&periode=" + periode;
  tujuan = "log_slave_3cekselisihgudang.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Done");
          alertify.popup().destroy();
          preview();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getperiodegudang() {
  unit = document.getElementById("unitId").value;
  param = "unitId=" + unit + "&proses=getperiodegudang";
  tujuan = "log_slave_3cekselisihgudang.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("periodeId").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewInfo(tipe, ev) {
  //showDetail(nopp, ev);
  unit = document.getElementById("unitId").value;
  periode = document.getElementById("periodeId").value;
  akunpersediaan = document.getElementById("akunpersediaan").value;
  divisiId = document.getElementById("divisiId").value;
  barangId = document.getElementById("barangId").value;

  if (tipe == "" || periode == "" || unit == "") {
    alert("Form Tidak Boleh Kosong...");
    return false;
  }

  if (tipe == "8" || tipe == "8.1") {
    if (divisiId == "") {
      alert("Divisi dan barang tidak boleh kosong");
      return false;
    }
  }

  param =
    "unitId=" +
    unit +
    "&proses=" +
    tipe +
    "&periodeId=" +
    periode +
    "&akunpersediaan=" +
    akunpersediaan +
    "&divisiId=" +
    divisiId +
    "&barangId=" +
    barangId;

  tujuan = "log_slave_3cekselisihgudang.php";
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
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ProsesSemuaGudangDivisi(periode) {
  let rows = document.querySelectorAll("[id^='getidgudangdivisi_']");
  let data = {};

  rows.forEach(function (row) {
    let parts = row.id.split("_");
    let gudang = parts[1];
    let barang = parts[2];

    if (!data[gudang]) data[gudang] = [];
    data[gudang].push(barang);
  });

  let param =
    "proses=ProsesSemuaGudangDivisi" +
    "&periode=" +
    periode +
    "&data=" +
    JSON.stringify(data);

  let tujuan = "log_slave_3cekselisihgudang.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Proses selesai untuk semua gudang divisi");
          alertify.popup().destroy();
          preview();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}


function prosesSaldoAwal(noakun,saldogudang,periode,unit) {
    param='unitId='+unit+'&proses=prosesSaldoAwal'+'&periodeId='+periode+'&noakun='+noakun+'&saldogudang='+saldogudang  ;
    post_response_text('log_slave_3cekselisihgudang.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // === Success Response
                    alert('Done');
                    preview();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// end joki
