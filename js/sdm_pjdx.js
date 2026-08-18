function postingconfirm(notransaksi) {
  param = "method=postingconfirm&notransaksi=" + notransaksi;
  if (confirm("Anda yakin ???")) {
    tujuan = "sdm_slave_pjdx.php";
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

function detailData(notransaksi, ev, jenis) {
  // width = 1024;
  // height = 400;

  // content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
  // ev = 'event';
  // title = "Preview";
  // showDialog4(title, content, width, height, ev);

  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // document.getElementById('containerd').innerHTML = con.responseText;
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

function detailPDF(notransaksi, ev, jenis) {
  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php" + "?" + param;
  // width = 1024;
  // height = 400;
  // ev = 'event';
  // title = "Preview";
  // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
  // showDialog1(title, content, width, height, ev);

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_pjdx.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function detailExcel(notransaksi, ev, jenis) {
  judul = "Report Ms.Excel";
  param =
    "method=previewdata" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  //printFile(param, tujuan, judul, ev);
  printnopopup(tujuan + "?" + param);
}

function detailData2(notransaksi, ev, jenis) {
  // width = 1024;
  // height = 400;

  // content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
  // ev = 'event';
  // title = "Preview";
  // showDialog4(title, content, width, height, ev);

  param =
    "method=previewdata2" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // document.getElementById('containerd').innerHTML = con.responseText;
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

function detailPDF2(notransaksi, ev, jenis) {
  param =
    "method=previewdata2" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php" + "?" + param;
  // width = 1024;
  // height = 400;
  // ev = 'event';
  // title = "Preview";
  // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
  // showDialog1(title, content, width, height, ev);

  alertify
    .popuppdf(
      "PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_pjdx.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function detailExcel2(notransaksi, ev, jenis) {
  judul = "Report Ms.Excel";
  param =
    "method=previewdata2" + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
  //printFile(param, tujuan, judul, ev);
  printnopopup(tujuan + "?" + param);
}

function add_new_data() {
  document.getElementById("detail").style.display = "block";
  document.getElementById("listData").style.display = "none";
  document.getElementById("formpencarianheader").style.display = "none";
  batalheader();
}

function displayList() {
  document.getElementById("listData").style.display = "block";
  document.getElementById("formpencarianheader").style.display = "";
  document.getElementById("detail").style.display = "none";
  batallist();
}

function batallist() {
  document.getElementById("notransaksilist").value = "";
  document.getElementById("namakarylist").value = "";
  loaddata(0);
}

function loaddata(page) {
  notransaksilist = document.getElementById("notransaksilist").value;
  namakarylist = document.getElementById("namakarylist").value;

  param = "method=loaddata&page=" + page;
  param += "&notransaksilist=" + notransaksilist;
  param += "&namakarylist=" + namakarylist;

  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("contain").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loaddatabiaya(notransaksi) {
  stsawal = document.getElementById("stsawal").value;
  notransaksi = document.getElementById("notransaksi").value;
  tglawal = document.getElementById("tglawal").value;
  tglakhir = document.getElementById("tglakhir").value;
  jenistampilan = document.getElementById("jenistampilan").value;
  tglawalreal = document.getElementById("tglawalreal").value;
  tglakhirreal = document.getElementById("tglakhirreal").value;

  param = "method=loaddatabiaya";
  param += "&jenistampilan=" + jenistampilan;
  param += "&stsawal=" + stsawal;
  param += "&notransaksi=" + notransaksi;
  param += "&tglawal=" + tglawal;
  param += "&tglawalreal=" + tglawalreal;
  param += "&tglakhir=" + tglakhir;
  param += "&tglakhirreal=" + tglakhirreal;

  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("loaddatabiaya").innerHTML = con.responseText;
          window.scrollTo(0, 500);
          loaddatarenckegiatan();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getjlh(id, no) {
  notransaksi = document.getElementById("notransaksi").value;
  karyawanid = document.getElementById("karyawanid").value;
  pttujuan = document.getElementById("pttujuan").value;
  unittujuan1 = document.getElementById("unittujuan1").value;
  unittujuan2 = document.getElementById("unittujuan2").value;
  tglawal = document.getElementById("tglawal").value;
  tglakhir = document.getElementById("tglakhir").value;
  ketdinas = document.getElementById("ketdinas").value;
  stsawal = document.getElementById("stsawal").value;
  lokasitugas = document.getElementById("lokasitugas").value;
  tujubiayadriver = document.getElementById("tujubiayadriver").value;
  jenisbiayadriver = document.getElementById("jenisbiayadriver").value;
  jenisbiaya = document.getElementById("jenisbiaya").value;
  jabatan = document.getElementById("jabatan").value;
  tipekary = document.getElementById("tipekary").value;
  golongan = document.getElementById("golongan").value;
  levelkaryawan = document.getElementById("levelkaryawan").value;
  regiontujuan = document.getElementById("regiontujuan").value;

  param = "method=getjlh";
  param += "&tujubiayadriver=" + tujubiayadriver;
  param += "&jenisbiayadriver=" + jenisbiayadriver;
  param += "&jenisbiaya=" + jenisbiaya;
  param += "&jabatan=" + jabatan;
  param += "&tipekary=" + tipekary;
  param += "&golongan=" + golongan;
  param += "&levelkaryawan=" + levelkaryawan;
  param += "&regiontujuan=" + regiontujuan;
  param += "&lokasitugas=" + lokasitugas;
  param += "&stsawal=" + stsawal;
  param += "&notransaksi=" + notransaksi;
  param += "&karyawanid=" + karyawanid;
  param += "&pttujuan=" + pttujuan;
  param += "&unittujuan=" + unittujuan;
  param += "&tglawal=" + tglawal;
  param += "&tglakhir=" + tglakhir;
  param += "&ketdinas=" + ketdinas;
  param += "&no=" + no;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          n = document.getElementById(id);
          n.checked = false;
          document.getElementById("jumlah" + no).value = "";
        } else {
          n = document.getElementById(id);
          data = con.responseText.split("##");
          if (trim(data[0]) < 1) {
            n.checked = false;
            alertify.alert(trim(data[2]));
            return;
          } else {
            if (n.checked == true) {
              document.getElementById("jumlah" + no).value = trim(data[1]);
              document.getElementById("jlhplafon" + no).value = trim(data[1]);
              document.getElementById("jumlah" + no).style.display = "";
              if (trim(data[1]) == "0") {
                document.getElementById("jumlah" + no).disabled = false;
              } else {
                if (
                  tipekary == "0" ||
                  tipekary == "1" ||
                  tipekary == "2" ||
                  tipekary == "3" ||
                  tipekary == "4" ||
                  tipekary == "9"
                ) {
                  document.getElementById("jumlah" + no).disabled = true;
                } else {
                  document.getElementById("jumlah" + no).disabled = false;
                }
              }
            } else {
              document.getElementById("jlhplafon" + no).value = "0";
              document.getElementById("jumlah" + no).value = "";
              document.getElementById("jumlah" + no).style.display = "none";
            }

            //cek and ricek lagi
            if (trim(data[3]) == "sdm_confirmpjdx") {
              document.getElementById("jumlah" + no).disabled = false;
            }
            if (
              trim(data[3]) == "sdm_pengajuanpjdstaffx" ||
              trim(data[3]) == "sdm_pengajuanpjdnonstaffx" ||
              trim(data[3]) == "sdm_verifikasiptjpjdx"
            ) {
              document.getElementById("jumlah" + no).disabled = false;
            }
          }
          ttlastbyy();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function checkallbiayareal(no, maxrow) {
  getjlhreal(no, 1, maxrow);
}
function getjlhreal(no, col, maxrow) {
  notransaksi = document.getElementById("notransaksi").value;
  karyawanid = document.getElementById("karyawanid").value;
  pttujuan = document.getElementById("pttujuan").value;
  unittujuan1 = document.getElementById("unittujuan1").value;
  unittujuan2 = document.getElementById("unittujuan2").value;
  tglawal = document.getElementById("tglawal").value;
  tglakhir = document.getElementById("tglakhir").value;
  ketdinas = document.getElementById("ketdinas").value;
  stsawal = document.getElementById("stsawal").value;
  lokasitugas = document.getElementById("lokasitugas").value;
  tanggal = document.getElementById("tglreal" + no + "_" + col).value;
  tujubiayadriver = document.getElementById("tujdriverreal" + no).value;
  jenisbiayadriver = document.getElementById("umdriverreal" + no).value;
  jenisbiaya = document.getElementById("jenisbiayareal" + no).value;
  jabatan = document.getElementById("jabatan").value;
  tipekary = document.getElementById("tipekary").value;
  regiontujuan = document.getElementById("regiontujuan").value;

  param = "method=getjlhreal";
  param += "&tujubiayadriver=" + tujubiayadriver;
  param += "&jenisbiayadriver=" + jenisbiayadriver;
  param += "&jenisbiaya=" + jenisbiaya;
  param += "&jabatan=" + jabatan;
  param += "&tipekary=" + tipekary;
  param += "&regiontujuan=" + regiontujuan;
  param += "&lokasitugas=" + lokasitugas;
  param += "&stsawal=" + stsawal;
  param += "&notransaksi=" + notransaksi;
  param += "&karyawanid=" + karyawanid;
  param += "&pttujuan=" + pttujuan;
  param += "&unittujuan=" + unittujuan;
  param += "&tglawal=" + tglawal;
  param += "&tglakhir=" + tglakhir;
  param += "&ketdinas=" + ketdinas;
  param += "&tanggal=" + tanggal;
  param += "&no=" + no;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          userlain = document.getElementById("userlain" + no + "_" + col).value;
          n = document.getElementById("statusreal" + no + "_" + col);
          if (n.checked == true && userlain == "0") {
            document.getElementById("jumlahreal" + no + "_" + col).value =
              con.responseText;
            document.getElementById(
              "jumlahreal" + no + "_" + col
            ).style.display = "";
          } else if (n.checked == false && userlain == "0") {
            document.getElementById("jumlahreal" + no + "_" + col).value = "";
            document.getElementById(
              "jumlahreal" + no + "_" + col
            ).style.display = "none";
          }
          col = parseFloat(col) + parseFloat(1);
          if (col > maxrow || maxrow == undefined) {
            ttlrealbyy(no, col - 1);
          } else {
            getjlhreal(no, col, maxrow);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ttlrealbyy(no, col) {
    menu = document.getElementById("stsawal").value;
    e = document.getElementsByName("tglreal" + no);
    t = 0;
    for (i = 1; i <= e.length; i++) {
        if (document.getElementById("jumlahreal" + no + "_" + i) != undefined) {
            isi = document.getElementById("jumlahreal" + no + "_" + i).value;

            statusreal = document.getElementById("statusreal" + no + "_" + i);
            if (statusreal.checked == true) {
                cekplafon(no, col);
                rpplafon = document.getElementById("plafonreal" + no + "_" + i).value;
                if (parseFloat(isi) > parseFloat(rpplafon) &&  parseFloat(rpplafon) > 0 && menu != "sdm_confirmpjdx") {
					
					jenisbiaya = document.getElementById("jenisbiayareal" + no).value;
					lokasitugas = document.getElementById("lokasitugas").value;
					if(menu=='sdm_verifikasiptjpjdx' && (jenisbiaya=='7' || jenisbiaya=='8' || jenisbiaya=='9') && lokasitugas.substr(2)=='HO'){
						
					}else{						
						document.getElementById("jumlahreal" + no + "_" + i).value = parseFloat(rpplafon);
						alertify.alert( "Jumlah tidak boleh melebihi dari Plafon sebesar " + rpplafon + ".");
					}
                }
            }
            n = document.getElementById("jumlahreal" + no + "_" + i).value;
            n = parseFloat(n);
            if (isNaN(n) == true) {
                n = 0;
            }
            t = parseFloat(t) + n;
        }
    }
    document.getElementById("totalrealbyy" + no).value = t;
    document.getElementById("kolomsavereal" + no).style.backgroundColor = "red";
}

function cekplafon(no, col) {
  notransaksi = document.getElementById("notransaksi").value;
  karyawanid = document.getElementById("karyawanid").value;
  tujubiayadriver = document.getElementById("tujdriverreal" + no).value;
  jenisbiayadriver = document.getElementById("umdriverreal" + no).value;
  jenisbiaya = document.getElementById("jenisbiayareal" + no).value;
  jabatan = document.getElementById("jabatan").value;
  tipekary = document.getElementById("tipekary").value;
  regiontujuan = document.getElementById("regiontujuan").value;

  param = "method=getjlh";
  param += "&tujubiayadriver=" + tujubiayadriver;
  param += "&jenisbiayadriver=" + jenisbiayadriver;
  param += "&jenisbiaya=" + jenisbiaya;
  param += "&jabatan=" + jabatan;
  param += "&tipekary=" + tipekary;
  param += "&notransaksi=" + notransaksi;
  param += "&karyawanid=" + karyawanid;
  param += "&regiontujuan=" + regiontujuan;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          data = con.responseText.split("##");
          document.getElementById("plafonreal" + no + "_" + col).value = trim(
            data[1]
          );
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cekrealisasiover(idtujuan) {
  document.getElementById(idtujuan).style.backgroundColor = "cyan";
}
function cekrealisasiout(idtujuan) {
  document.getElementById(idtujuan).style.backgroundColor = "";
}

function checkallreal(no) {
  e = document.getElementsByName("tglreal" + no);
  for (i = 1; i <= e.length; i++) {
    if (document.getElementById("statusrenc" + no + "_" + i) != undefined) {
      r = document.getElementById("statusrenc" + no + "_" + i).innerHTML;
      userlain = document.getElementById("userlain" + no + "_" + i).value;
      cek = document.getElementById("ceckboxrealall" + no);
      if (r != "" && userlain == "0" && cek.checked == true) {
        document.getElementById("statusreal" + no + "_" + i).checked = true;
      } else if (r != "" && userlain == "0" && cek.checked == false) {
        document.getElementById("statusreal" + no + "_" + i).checked = false;
      }
    }
  }
  checkallbiayareal(no, e.length);
}

function simpanallagenda(maxrow) {
  if (maxrow == "" || maxrow == 0) {
    alertify.alert("Data tidak ditemukan, proses dibatalkan.");
    return;
  }
  simpanagenda(1, maxrow);
}
function simpanagenda(currrow, maxrow) {
  notransaksi = document.getElementById("notransaksi").value;
  tgl = document.getElementById("tglagenda" + currrow).value;
  tgl2 = document.getElementById("tglagenda2" + currrow).value;
  lokasi = document.getElementById("lokasiagenda" + currrow).value;
  keterangan = document.getElementById("renckeg" + currrow).value;
  koordinasidengan = document.getElementById("picagenda" + currrow).value;
  method = document.getElementById("methodagenda").value;
  jenisagenda = document.getElementById("jenisagenda").value;

  param = "";
  param += "&tgl=" + tgl;
  param += "&tgl2=" + tgl2;

  param += "&notransaksi=" + notransaksi;
  param += "&lokasi=" + lokasi;
  param += "&keterangan=" + keterangan;
  param += "&koordinasidengan=" + koordinasidengan;
  param += "&method=" + method;
  param += "&jenisagenda=" + jenisagenda;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          clearagenda(currrow);
          currrow += 1;
          if (currrow > maxrow || maxrow == undefined) {
            loaddatarenckegiatan(notransaksi);
          } else {
            simpanagenda(currrow, maxrow);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editagenda(notransaksi, tanggal,tanggal2 ,jenis) {
  param = "method=fillfieldagenda&notransaksi=" + notransaksi;
  param += "&tanggal=" + tanggal;
  param += "&tanggal2=" + tanggal2;
  param += "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          data = con.responseText.split("##");
          currrow = 1;
          document.getElementById("renckeg" + currrow).value = data[0];
          document.getElementById("lokasiagenda" + currrow).value = data[1];
          document.getElementById("picagenda" + currrow).value = data[2];
          document.getElementById("tglagenda" + currrow).value = data[3];
          document.getElementById("tglagenda" + currrow).disabled = true;
          document.getElementById("tglagenda2" + currrow).value = data[4];
          document.getElementById("tglagenda2" + currrow).disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function clearagenda(currrow) {
  document.getElementById("tglagenda" + currrow).value = "";
  document.getElementById("tglagenda" + currrow).disabled = false;

  document.getElementById("tglagenda2" + currrow).value = "";
  document.getElementById("tglagenda2" + currrow).disabled = false;

  document.getElementById("lokasiagenda" + currrow).value = "";
  document.getElementById("renckeg" + currrow).value = "";
  document.getElementById("picagenda" + currrow).value = "";
  document.getElementById("methodagenda").value = "insertagenda";
}

function loaddatarenckegiatan(notransaksi) {
  stsawal = document.getElementById("stsawal").value;
  notransaksi = document.getElementById("notransaksi").value;

  param = "method=loaddatarenckegiatan";
  param += "&stsawal=" + stsawal;
  param += "&notransaksi=" + notransaksi;

  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("loaddatarenckegiatan").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function delagenda(notransaksi, jenis, tanggal,tanggal2) {
  param = "method=delagenda";
  param += "&jenis=" + jenis;
  param += "&tanggal=" + tanggal;
  param += "&tanggal2=" + tanggal2;
  param += "&notransaksi=" + notransaksi;

  tujuan = "sdm_slave_pjdx.php";
  if (confirm("Anda yakin ?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddatarenckegiatan();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpanest(maxrow, sumber, baris) {
  if (maxrow == "" || maxrow == 0) {
    alertify.alert("Data tidak ditemukan, proses dibatalkan.");
    return;
  }
  saveestbyy(1, maxrow, sumber, baris);
}
function saveestbyy(currrow, maxrow, sumber, baris) {
  notransaksi = document.getElementById("notransaksi").value;
  stsawal = document.getElementById("stsawal").value;
  method = document.getElementById("methodbyy").value;

  if (sumber == "renc") {
    jenisbiaya = document.getElementById("jenisbiaya").value;
    tempatkunjungan = document.getElementById("tempatkunjungan").value;
    totalestbyy = document.getElementById("totalestbyy").value;
    ketestbyy = document.getElementById("ketestbyy").value;
    pic = document.getElementById("pic").value;
    jenisbiayadriver = document.getElementById("jenisbiayadriver").value;
    tujubiayadriver = document.getElementById("tujubiayadriver").value;
    tgl = document.getElementById("tgl" + currrow).value;
    jumlah = document.getElementById("jumlah" + currrow).value;
    stat = document.getElementById("status" + currrow);
    if (stat.checked == true) {
      check = 1;
    } else {
      check = 0;
    }
    var namabyy = document.getElementById("jenisbiaya");
    var optnamabyy = namabyy.options[namabyy.selectedIndex].text;
    var nama = document.getElementById("pic");
    var optnama = nama.options[nama.selectedIndex].text;
  } else if (sumber == "real") {
    jenisbiaya = document.getElementById("jenisbiayareal" + baris).value;
    tempatkunjungan = document.getElementById(
      "tempatkunjungan" + baris
    ).innerHTML;
    totalestbyy = document.getElementById("totalrealbyy" + baris).value;
    ketestbyy = document.getElementById("ketrealbyy" + baris).value;
    pic = document.getElementById("picreal" + baris).value;
    jenisbiayadriver = document.getElementById("umdriverreal" + baris).value;
    tujubiayadriver = document.getElementById("tujdriverreal" + baris).value;
    tgl = document.getElementById("tglreal" + baris + "_" + currrow).value;
    jumlah = document.getElementById(
      "jumlahreal" + baris + "_" + currrow
    ).value;
    stat = document.getElementById("statusreal" + baris + "_" + currrow);
    if (stat.checked == true) {
      check = 1;
    } else {
      check = 0;
    }
  }

  if (sumber == "renc") {
    sumbertrans = 0;
  } else if (sumber == "real") {
    sumbertrans = 1;
  }
  param = "";
  param += "&tgl=" + tgl;
  param += "&check=" + check;
  param += "&sumbertrans=" + sumbertrans;
  param += "&jenisbiayadriver=" + jenisbiayadriver;
  param += "&tujubiayadriver=" + tujubiayadriver;
  param += "&jumlah=" + jumlah;
  param += "&method=" + method;
  param += "&notransaksi=" + notransaksi;
  param += "&jenisbiaya=" + jenisbiaya;
  param += "&stsawal=" + stsawal;
  param += "&tempatkunjungan=" + tempatkunjungan;
  param += "&totalestbyy=" + totalestbyy;
  param += "&ketestbyy=" + ketestbyy;
  param += "&pic=" + pic;
  tujuan = "sdm_slave_pjdx.php";
  /* if(currrow==1 && sumber=='renc'){
		if(confirm("Kontak Person / PIC untuk\njenis biaya : "+optnamabyy+"\nadalah : "+optnama+"\ningin tetap melanjutkan ???")){
			post_response_text(tujuan, param, respog);
		}
	}else{		
		post_response_text(tujuan, param, respog);
	} */
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          currrow += 1;
          if (currrow > maxrow || maxrow == undefined) {
            clearestbyy(sumber);
            alertify.alert("Done");
            if (sumber == "real") {
              document.getElementById(
                "kolomsavereal" + baris
              ).style.backgroundColor = "";
            }
            if (sumber == "renc") {
              loaddatabiaya(notransaksi);
              loadinputdetail();
            } else {
              loadinputdetail();
            }
          } else {
            saveestbyy(currrow, maxrow, sumber, baris);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function clearestbyy(sumber) {
  if (sumber == "renc") {
    document.getElementById("jenisbiaya").value = "";
    document.getElementById("totalestbyy").value = "0";
    document.getElementById("ketestbyy").value = "";
    t = document.getElementsByName("stat");
    for (i = 0; i < t.length; i++) {
      t[i].checked = false;
    }
    t = document.getElementsByName("jlhbyy");
    for (i = 0; i < t.length; i++) {
      t[i].value = "";
      t[i].style.display = "none";
    }
  }
}

function ttlastbyy() {
  jlhtgl = document.getElementById("jlhtgl").value;
  menu = document.getElementById("stsawal").value;
  t = 0;
  for (i = 1; i <= jlhtgl; i++) {
    isi = document.getElementById("jumlah" + i).value;
    pla = document.getElementById("jlhplafon" + i).value;
    if (
      parseFloat(isi) > parseFloat(pla) &&
      parseFloat(pla) > 0 &&
      menu != "sdm_confirmpjdx"
    ) {
      document.getElementById("jumlah" + i).value = parseFloat(pla);
      alertify.alert(
        "Jumlah tidak boleh melebihi dari Plafon sebesar " + pla + "."
      );
    }

    n = document.getElementById("jumlah" + i).value;
    n = parseFloat(n);
    if (isNaN(n) == true) {
      n = 0;
    }
    t = parseFloat(t) + n;
  }
  document.getElementById("totalestbyy").value = t;
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function batalheader() {
  document.getElementById("notransaksi").value = "";
  document.getElementById("karyawanid").value = "";
  document.getElementById("pttujuan").value = "";
  document.getElementById("unittujuan1").value = "";
  document.getElementById("unittujuan2").value = "";
  document.getElementById("tglawal").value = "";
  document.getElementById("tglakhir").value = "";
  document.getElementById("ketdinas").value = "";
  document.getElementById("methodheader").value = "insertheader";
  document.getElementById("lokasitugas").value = "";
  document.getElementById("tipekary").value = "";
  document.getElementById("jabatan").value = "";
  document.getElementById("golongan").value = "";
  document.getElementById("dept").value = "";
  document.getElementById("contrute").innerHTML = "";
  document.getElementById("karyawanid").disabled = false;
  document.getElementById("tglawal").disabled = false;
  document.getElementById("tglakhir").disabled = false;
  document.getElementById("tipekary").disabled = false;
  t = document.getElementsByName("tiket");
  for (i = 0; i < t.length; i++) {
    t[i].checked = false;
  }
  document.getElementById("contdetail").innerHTML = "";

  document.getElementById("pttujuan").disabled = false;
  document.getElementById("unittujuan2").disabled = false;
  document.getElementById("unittujuan1").disabled = false;
  document.getElementById("regiontujuan").disabled = false;
  document.getElementById("tiketno").disabled = false;
  document.getElementById("tiketya").disabled = false;
  document.getElementById("ketdinas").disabled = false;
  document.getElementById("barisinputrute").style.display = "";
}

function simpanheader(sumbertrans) {
  notransaksi = document.getElementById("notransaksi").value;
  karyawanid = document.getElementById("karyawanid").value;
  pttujuan = document.getElementById("pttujuan").value;
  unittujuan1 = document.getElementById("unittujuan1").value;
  unittujuan2 = document.getElementById("unittujuan2").value;
  tglawal = document.getElementById("tglawal").value;
  tglakhir = document.getElementById("tglakhir").value;
  if (
    getValue("tglawalreal") == "" ||
    getValue("tglawalreal") == "00-00-0000"
  ) {
    document.getElementById("tglawalreal").value = tglawal;
  }
  if (
    getValue("tglakhirreal") == "" ||
    getValue("tglakhirreal") == "00-00-0000"
  ) {
    document.getElementById("tglakhirreal").value = tglakhir;
  }
  tglawalreal = document.getElementById("tglawalreal").value;
  tglakhirreal = document.getElementById("tglakhirreal").value;

  ketdinas = document.getElementById("ketdinas").value;
  stsawal = document.getElementById("stsawal").value;
  method = document.getElementById("methodheader").value;
  lokasitugas = document.getElementById("lokasitugas").value;
  regiontujuan = document.getElementById("regiontujuan").value;
  jumlahlevel = document.getElementById("jumlahlevel").value;
  tipekary = document.getElementById("tipekary").value;
  levelkaryawan = document.getElementById("levelkaryawan").value;
  
  if (tipekary == "") {
    alertify.alert("Warning : Level wajib diisi.");
    return;
  }

  var nama = document.getElementById("tipekary");
  var optnama = nama.options[nama.selectedIndex].text;

  param = "";
  t = document.getElementsByName("tiket");
  for (i = 0; i < t.length; i++) {
    if (t[i].checked == true) {
      param += "&tiket=" + t[i].value;
    }
  }
  if (pttujuan == "OTH") {
    unittujuan = unittujuan2;
  } else {
    unittujuan = unittujuan1;
  }

  param += "&method=" + method;
  param += "&regiontujuan=" + regiontujuan;
  param += "&lokasitugas=" + lokasitugas;
  param += "&levelkaryawan=" + levelkaryawan;
  param += "&stsawal=" + stsawal;
  param += "&notransaksi=" + notransaksi;
  param += "&karyawanid=" + karyawanid;
  param += "&pttujuan=" + pttujuan;
  param += "&unittujuan=" + unittujuan;
  param += "&tglawal=" + tglawal;
  param += "&tglakhir=" + tglakhir;
  param += "&ketdinas=" + ketdinas;
  param += "&tipekary=" + tipekary;
  param += "&tglawalreal=" + tglawalreal;
  param += "&tglakhirreal=" + tglakhirreal;
  tujuan = "sdm_slave_pjdx.php";

  if (jumlahlevel > 1 && method == "insertheader") {
    if (
      confirm(
        "Level yang anda pilih adalah " +
          optnama +
          ", apakah sudah benar ?\nIngin melanjutkan ???"
      )
    ) {
      post_response_text(tujuan, param, respog);
    }
  } else {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {

          if(method == 'insertheader'){
            document.getElementById("notransaksi").value = trim(con.responseText);
          }

          document.getElementById("methodheader").value = "updateheader";
          document.getElementById("karyawanid").disabled = true;
          document.getElementById("tglawal").disabled = true;
          document.getElementById("tglakhir").disabled = true;
          document.getElementById("tipekary").disabled = true;
          if (sumbertrans != "editloaddata") {
            alertify.alert("Done");
          }
          loadinputdetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadinputdetail() {
  notransaksi = document.getElementById("notransaksi").value;
  karyawanid = document.getElementById("karyawanid").value;
  pttujuan = document.getElementById("pttujuan").value;
  unittujuan1 = document.getElementById("unittujuan1").value;
  unittujuan2 = document.getElementById("unittujuan2").value;
  tglawal = document.getElementById("tglawal").value;
  tglakhir = document.getElementById("tglakhir").value;
  tglawalreal = document.getElementById("tglawalreal").value;
  tglakhirreal = document.getElementById("tglakhirreal").value;
  ketdinas = document.getElementById("ketdinas").value;
  stsawal = document.getElementById("stsawal").value;
  method = document.getElementById("methodheader").value;
  lokasitugas = document.getElementById("lokasitugas").value;
  regiontujuan = document.getElementById("regiontujuan").value;
  jenistampilan = document.getElementById("jenistampilan").value;

  param = "";
  t = document.getElementsByName("tiket");
  for (i = 0; i < t.length; i++) {
    if (t[i].checked == true) {
      param += "&tiket=" + t[i].value;
    }
  }
  if (pttujuan == "OTH") {
    unittujuan = unittujuan2;
  } else {
    unittujuan = unittujuan1;
  }

  param += "&method=loadinputdetail";
  param += "&regiontujuan=" + regiontujuan;
  param += "&lokasitugas=" + lokasitugas;
  param += "&stsawal=" + stsawal;
  param += "&notransaksi=" + notransaksi;
  param += "&karyawanid=" + karyawanid;
  param += "&pttujuan=" + pttujuan;
  param += "&unittujuan=" + unittujuan;
  param += "&tglawal=" + tglawal;
  param += "&tglawalreal=" + tglawalreal;
  param += "&tglakhir=" + tglakhir;
  param += "&tglakhirreal=" + tglakhirreal;
  param += "&ketdinas=" + ketdinas;
  param += "&jenistampilan=" + jenistampilan;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contdetail").style.display = "block";
          document.getElementById("contdetail").innerHTML = con.responseText;
          loaddatabiaya(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddatarute(notransaksi) {
  param = "method=loaddatarute";
  param += "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contrute").innerHTML = trim(
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

function deleterute(no, notransaksi) {
  methodheader = document.getElementById("methodheader").value;
  param = "no=" + no + "&method=deleterute";
  param += "&notransaksi=" + notransaksi;
  param += "&methodheader=" + methodheader;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contrute").innerHTML = trim(
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
function addrute() {
  keyrute = document.getElementById("keyrute").innerHTML;
  karyawanid = document.getElementById("karyawanid").value;
  notransaksi = document.getElementById("notransaksi").value;
  dari = document.getElementById("dari").value;
  rutetujuan = document.getElementById("tujuan").value;
  tglrute = document.getElementById("tglrute").value;
  time = document.getElementById("time").value;
  transport = document.getElementById("transport").value;
  method = document.getElementById("methodrute").value;
  methodheader = document.getElementById("methodheader").value;
  //document.getElementById('methodheader').value='updateheader';

  param = "";
  param += "&method=" + method;
  param += "&methodheader=" + methodheader;
  param += "&karyawanid=" + karyawanid;
  param += "&keyrute=" + keyrute;
  param += "&notransaksi=" + notransaksi;
  param += "&dari=" + dari;
  param += "&rutetujuan=" + rutetujuan;
  param += "&tglrute=" + tglrute;
  param += "&time=" + time;
  param += "&transport=" + transport;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contrute").innerHTML = trim(
            con.responseText
          );
          document.getElementById("methodrute").value = "addrute";
          document.getElementById("keyrute").value = "#";
          document.getElementById("dari").value = "";
          document.getElementById("tujuan").value = "";
          document.getElementById("tglrute").value = "";
          document.getElementById("time").value = "";
          document.getElementById("transport").value = "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editrute(key, notransaksi, dari, tujuan, tgl, jam, transport) {
  document.getElementById("keyrute").innerHTML = key;
  document.getElementById("dari").value = dari;
  document.getElementById("tujuan").value = tujuan;
  document.getElementById("tglrute").value = tgl;
  document.getElementById("time").value = jam;
  document.getElementById("transport").value = transport;
  document.getElementById("methodrute").value = "editrute";
}

function findSelection() {
  t = document.getElementsByName("tiket");
  for (i = 0; i < t.length; i++) {
    if (t[i].checked == true) {
      alertify.alert(t[i].value + " you got a value");
      return t[i].value;
    }
  }
}

function getdata() {
  karyawanid = document.getElementById("karyawanid").value;

  param = "method=getdata";
  param += "&karyawanid=" + karyawanid;

  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          data = con.responseText.split("##");
          document.getElementById("jabatan").value = data[0];
          document.getElementById("golongan").value = data[1];
          document.getElementById("dept").value = data[2];
          document.getElementById("lokasitugas").value = data[3];
          // document.getElementById("notransaksi").value = data[8];
          document.getElementById("tipekary").innerHTML = data[9];
          document.getElementById("jumlahlevel").value = data[10];
          document.getElementById("levelkaryawan").value = data[11];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getunit() {
  pttujuan = document.getElementById("pttujuan").value;

  param = "method=getunit";
  param += "&pttujuan=" + pttujuan;

  if (pttujuan == "OTH") {
    document.getElementById("unittujuan1").style.display = "none";
    document.getElementById("unittujuan2").style.display = "block";
    document.getElementById("unittujuan1").value = "";
  } else {
    tujuan = "sdm_slave_pjdx.php";
    post_response_text(tujuan, param, respog);

    document.getElementById("unittujuan1").style.display = "block";
    document.getElementById("unittujuan2").style.display = "none";
    document.getElementById("unittujuan2").value = "";
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert(con.responseText);
          } else {
            document.getElementById("unittujuan1").innerHTML = con.responseText;
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }
}
function getregion() {
  unittujuan1 = document.getElementById("unittujuan1").value;
  pttujuan = document.getElementById("pttujuan").value;

  // if (pttujuan != "OTH" && unittujuan1 != "") {
  //   document.getElementById("regiontujuan").disabled = true;
  // } else {
  //   document.getElementById("regiontujuan").disabled = false;
  // }

  param = "method=getregion";
  param += "&unit=" + unittujuan1;

  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("regiontujuan").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getdetailjnsbyy() {
  jenisbiaya = document.getElementById("jenisbiaya").value;
  jabatan = document.getElementById("jabatan").value;
  tipekary = document.getElementById("tipekary").value;
  regiontujuan = document.getElementById("regiontujuan").value;

  param = "method=getdetailjnsbyy";
  param += "&jenisbiaya=" + jenisbiaya;
  param += "&jabatan=" + jabatan;
  param += "&tipekary=" + tipekary;
  param += "&regiontujuan=" + regiontujuan;

  t = document.getElementsByName("stat");
  for (i = 0; i < t.length; i++) {
    t[i].checked = false;
  }
  t = document.getElementsByName("jlhbyy");
  for (i = 0; i < t.length; i++) {
    t[i].value = "";
    t[i].style.display = "none";
  }
  document.getElementById("totalestbyy").value = 0;

  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          data = con.responseText.split("##");
          if (trim(data[0]) > 0) {
            t = document.getElementsByName("fielddriver");
            for (i = 0; i < t.length; i++) {
              t[i].style.display = "";
            }
          } else {
            t = document.getElementsByName("fielddriver");
            for (i = 0; i < t.length; i++) {
              t[i].style.display = "none";
            }
          }
          document.getElementById("tujubiayadriver").innerHTML = data[1];
          document.getElementById("jenisbiayadriver").innerHTML = data[2];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function clearcheck() {
  t = document.getElementsByName("stat");
  for (i = 0; i < t.length; i++) {
    t[i].checked = false;
  }
  t = document.getElementsByName("jlhbyy");
  for (i = 0; i < t.length; i++) {
    t[i].value = "";
    t[i].style.display = "none";
  }
  document.getElementById("totalestbyy").value = 0;
}

function delbyy(
  notransaksi,
  jenisbiaya,
  sumber,
  umdriver,
  tujdriver,
  piclokasi
) {
  param = "method=delbyy";
  param += "&notransaksi=" + notransaksi;
  param += "&jenisbiaya=" + jenisbiaya;
  param += "&sumber=" + sumber;
  param += "&umdriver=" + umdriver;
  param += "&tujdriver=" + tujdriver;
  param += "&piclokasi=" + piclokasi;
  tujuan = "sdm_slave_pjdx.php";
  if (confirm("Anda yakin ?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddatabiaya(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpantglreal() {
  notransaksi = document.getElementById("notransaksi").value;
  tglawalreal = document.getElementById("tglawalreal").value;
  tglakhirreal = document.getElementById("tglakhirreal").value;

  param = "method=simpantglreal";
  param += "&notransaksi=" + notransaksi;
  param += "&tglawalreal=" + tglawalreal;
  param += "&tglakhirreal=" + tglakhirreal;

  tujuan = "sdm_slave_pjdx.php";
  if (confirm("Anda yakin ?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loadinputdetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function fillfield(notransaksi, sumbermenu) {
  document.getElementById("contdetail").innerHTML = "";
  param = "method=fillfield";
  param += "&notransaksi=" + notransaksi;
  param += "&sumbermenu=" + sumbermenu;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          data = con.responseText.split("##");
          document.getElementById("detail").style.display = "block";
          document.getElementById("listData").style.display = "none";
          document.getElementById("formpencarianheader").style.display = "none";

          document.getElementById("notransaksi").value = data[0];
          document.getElementById("karyawanid").value = data[1];
          document.getElementById("pttujuan").value = data[7];
          if (data[7] == "OTH") {
            document.getElementById("unittujuan2").value = data[8];
            document.getElementById("unittujuan1").style.display = "none";
            document.getElementById("unittujuan2").style.display = "";
          } else {
            document.getElementById("unittujuan1").style.display = "";
            document.getElementById("unittujuan2").style.display = "none";
            document.getElementById("unittujuan1").value = data[8];
          }
          document.getElementById("tglawal").value = data[3];
          document.getElementById("tglakhir").value = data[4];
          document.getElementById("tglawalreal").value = data[15];
          document.getElementById("tglakhirreal").value = data[16];

          document.getElementById("lokasitugas").value = data[5];
          document.getElementById("ketdinas").value = data[6];
          document.getElementById("regiontujuan").value = data[9];

          document.getElementById("jabatan").value = data[11];
          document.getElementById("golongan").value = data[12];
          document.getElementById("dept").value = data[13];
          document.getElementById("tipekary").value = data[14];

          document.getElementById("levelkaryawan").value = data[17];

          document.getElementById("karyawanid").disabled = false;
          document.getElementById("tglawal").disabled = false;
          document.getElementById("tglakhir").disabled = false;
          if (data[10] == 1) {
            document.getElementById("tiketya").checked = true;
          } else {
            document.getElementById("tiketno").checked = true;
          }
          document.getElementById("methodheader").value = "updateheader";
          document.getElementById("contrute").innerHTML = data[18];
          if (
            sumbermenu != "sdm_pengajuanpjdstaffx" &&
            sumbermenu != "sdm_pengajuanpjdnonstaffx"
          ) {
            document.getElementById("pttujuan").disabled = true;
            document.getElementById("unittujuan2").disabled = true;
            document.getElementById("unittujuan1").disabled = true;
            document.getElementById("regiontujuan").disabled = true;
            document.getElementById("tiketno").disabled = true;
            document.getElementById("tiketya").disabled = true;
            document.getElementById("ketdinas").disabled = true;
            document.getElementById("barisinputrute").style.display = "none";
          }

          simpanheader("editloaddata");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function del(notransaksi, no) {
  param = "method=delete";
  param += "&notransaksi=" + notransaksi;

  tujuan = "sdm_slave_pjdx.php";
  if (confirm("Anda yakin ?")) {
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

function showformupload(ev) {
  title = "UPLOAD FILES";
  width = "";
  height = "";
  content =
    "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
  showDialog6(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic6").style.top = 600 + "px";
  document.getElementById("dynamic6").style.left = pos[0] + "px";
  document.getElementById("dynamic6").style.display = "";
}

function showformuploadperhari(ev, no) {
  title = "UPLOAD FILES HARI KE-" + no;
  width = "";
  height = "";
  content =
    "<fieldset style=width:96%><legend>Form</legend><div id=contUploadPerhari style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
  showDialog6(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic6").style.top = 600 + "px";
  document.getElementById("dynamic6").style.left = pos[0] + "px";
  document.getElementById("dynamic6").style.display = "";
}

function showupload(ev, notransaksi, jenisupload) {
  if (notransaksi == "") {
    alertify.alert("warning : Notransaksi wajib diisi.");
    return false;
  }
  showformupload(ev);
  param = "method=showupload&notransaksi=" + notransaksi;
  param += "&jenisupload=" + jenisupload;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contUpload").innerHTML = con.responseText;
          loadfiles(notransaksi, jenisupload);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showuploadperhari(ev, notransaksi, no) {
  if (notransaksi == "") {
    alertify.alert("warning : Notransaksi wajib diisi.");
    return false;
  }
  const tanggal = document.querySelector("#tanggalPdJUpload_" + no).value;
  showformuploadperhari(ev, no);
  param =
    "method=showuploadperhari&notransaksi=" +
    notransaksi +
    "&tanggal=" +
    tanggal;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contUploadPerhari").innerHTML =
            con.responseText;
          loadfilesperhari(notransaksi, tanggal);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function submitfile(notransaksi, jenisupload) {
  var file = document.getElementById("upload").files[0];
  var jenisupload = document.getElementById("jenisupload").value;
  var formdata = new FormData();

  formdata.append("fileupload", getValue("upload"));
  formdata.append("file", file);
  formdata.append("notransaksi", notransaksi);
  formdata.append("jenisupload", jenisupload);
  if (getValue("upload") == "") {
    alertify.alert("warning : Upload file has been empty.");
    return false;
  }

  if (notransaksi == "") {
    alertify.alert("warning : Notransaksi wajib diisi.");
    return false;
  }
  if (jenisupload == "") {
    alertify.alert("warning : Jenis Biaya wajib diisi.");
    return false;
  }

  var con = createXMLHttpRequest();
  document.getElementById("btnsubmit").disabled = true;
  document.getElementById("btnsubmit").style.display = "none";
  busy_on();
  con.open("POST", "sdm_slave_pjdx.php?method=submitfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //=== Success Response
          alertify.alert("Uploaded Success.");
          document.getElementById("btnsubmit").disabled = false;
          document.getElementById("btnsubmit").style.display = "";
          document.getElementById("upload").value = "";
          loadfiles(notransaksi, jenisupload);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function submitfileperhari(notransaksi, tanggal) {
  var file = document.getElementById("uploadPerhari").files[0];
  var formdata = new FormData();

  formdata.append("fileupload", getValue("uploadPerhari"));
  formdata.append("file", file);
  formdata.append("notransaksi", notransaksi);
  if (getValue("uploadPerhari") == "") {
    alertify.alert("warning : Upload file has been empty.");
    return false;
  }

  if (notransaksi == "") {
    alertify.alert("warning : Notransaksi wajib diisi.");
    return false;
  }
  var con = createXMLHttpRequest();
  document.getElementById("btnsubmit").disabled = true;
  document.getElementById("btnsubmit").style.display = "none";
  busy_on();
  param = "method=submitfileperhari";
  param += "&tanggal=" + tanggal;
  con.open("POST", "sdm_slave_pjdx.php?" + param, true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //=== Success Response
          alertify.alert("Uploaded Success.");
          document.getElementById("btnsubmit").disabled = false;
          document.getElementById("btnsubmit").style.display = "";
          document.getElementById("uploadPerhari").value = "";
          loadfilesperhari(notransaksi, tanggal);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function enabletombol() {
  document.getElementById("btnsubmit").disabled = false;
  document.getElementById("btnsubmit").style.display = "";
  busy_off();
}
function loadfiles(notransaksi, jenisupload) {
  param = "method=loadfiles&notransaksi=" + notransaksi;
  param += "&jenisupload=" + jenisupload;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (document.getElementById("listfiles") !== null) {
            document.getElementById("listfiles").innerHTML = con.responseText;
          }
          if (document.getElementById("loadfilesdetail") !== null) {
            document.getElementById("loadfilesdetail").innerHTML = con.responseText;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilesperhari(notransaksi, tanggal) {
  param = "method=loadfilesperhari&notransaksi=" + notransaksi;
  param += "&tanggal=" + tanggal;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
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

function popupfile(notransaksi, jenisupload, jenis) {
  ev = "event";
  showformupload(ev);
  param = "method=loadfiles&notransaksi=" + notransaksi;
  param += "&jenisupload=" + jenisupload;
  param += "&jenis=" + jenis;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (document.getElementById("contUpload") !== null) {
            document.getElementById("contUpload").innerHTML = con.responseText;
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
    '<fieldset style="width:97%;"><div id=contview style="width:50%;height:50%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "View";
  showDialog5(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  
  document.getElementById("dynamic5").style.top = 500 + "px";
  document.getElementById("dynamic5").style.left = pos[0] + "px";
  document.getElementById("dynamic5").style.display = "";
  
}
function viewfile(ev, namafile) {
  ext = namafile.split(".");
  if (
    trim(ext[1]) == "jpg" ||
    trim(ext[1]) == "jpeg" ||
    trim(ext[1]) == "png"
  ) {
    form();
    param = "method=viewfile&namafile=" + namafile;
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
          document.getElementById("contview").innerHTML = con.responseText;
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
    trim(ext[1]) == "png" ||
    trim(ext[1]) == "pdf"
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
function deletefile(notransaksi, namafile, jenisupload) {
  param = "method=deletefile";
  param += "&notransaksi=" + notransaksi;
  param += "&namafile=" + namafile;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loadfilesperhari(notransaksi, jenisupload);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function deletefileperhari(notransaksi, namafile, jenisupload) {
  param = "method=deletefile";
  param += "&notransaksi=" + notransaksi;
  param += "&namafile=" + namafile;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loadfilesperhari(notransaksi, jenisupload);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function form_ajukan(notransaksi, kodeapproval, jenis, numrow) {
  width = "350";
  height = "";
  content =
    '<fieldset><legend>Submission Form</legend><div id=containeraju align=center style="width:320px;max-height:150px;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "";
  showDialog5(title, content, width, height, ev);

  param =
    "method=form_ajukan" +
    "&kodeapproval=" +
    kodeapproval +
    "&jenis=" +
    jenis +
    "&numrow=" +
    numrow;
  param += "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
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
  kepada = document.getElementById("kepada").value;
  notransaksi = document.getElementById("notran_aju").innerHTML;
  jenis = document.getElementById("jenisaju").value;
  kodeapproval = document.getElementById("kodeapprovalaju").value;
  level = document.getElementById("levelaju").value;

  param = "method=ajukan" + "&notransaksi=" + notransaksi + "&kepada=" + kepada;
  param += "&jenis=" + jenis;
  param += "&kodeapproval=" + kodeapproval;
  param += "&level=" + level;

  if (kepada == "") {
    alertify.alert("Isikan nama penyetuju.");
    return;
  }
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.alert("Sucses");
          closeDialog5();
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function rubahtampilan(idtombol) {
  jenistampil = document.getElementById("jenistampilan").value;
  if (jenistampil == "tampilansimple") {
    document.getElementById("jenistampilan").value = "tampilandetail";
  } else {
    document.getElementById("jenistampilan").value = "tampilansimple";
  }
  loadinputdetail();
}

function form_batal(notransaksi) {
  width = "350";
  height = "";
  content =
    '<fieldset><legend>Batal</legend><div id=containerbatal align=center style="width:320px;max-height:150px;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "";
  showDialog5(title, content, width, height, ev);

  param = "method=form_batal";
  param += "&notransaksi=" + notransaksi;
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("containerbatal").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalkan() {
  notransaksi = document.getElementById("notran_batal").innerHTML;
  keterangan = document.getElementById("ketbatal").value;
  param =
    "method=batalkan" +
    "&notransaksi=" +
    notransaksi +
    "&keterangan=" +
    keterangan;
  if (keterangan == "") {
    alertify.alert("Isikan keterangan.");
    return;
  }
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.alert("Sucses");
          closeDialog5();
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getjudulconf(no) {
  conf = document.getElementById("confirmstat" + no);
  if (conf.checked == true) {
    document.getElementById("judulconf" + no).innerHTML = "Ya";
  } else {
    document.getElementById("judulconf" + no).innerHTML = "Tidak";
  }
}

function clearconfirm(no) {
  document.getElementById("judulconf" + no).innerHTML = "Tidak";
  document.getElementById("confirmstat" + no).checked = false;
  document.getElementById("ketconfirm" + no).value = "";
  document.getElementById("tglconfirm" + no).value = "";
}

function simpanconfirm(no, tgl, jenis) {
  notransaksi = document.getElementById("notransaksi").value;
  ketconfirm = document.getElementById("ketconfirm" + no).value;
  tglconfirm = document.getElementById("tglconfirm" + no).value;
  conf = document.getElementById("confirmstat" + no);
  if (conf.checked == true) {
    stat = "1";
  } else {
    stat = "0";
  }

  param =
    "method=simpanconfirm" +
    "&notransaksi=" +
    notransaksi +
    "&ketconfirm=" +
    ketconfirm;
  param += "&tglconfirm=" + tglconfirm;
  param += "&tgl=" + tgl;
  param += "&jenis=" + jenis;
  param += "&stat=" + stat;
  if (ketconfirm == "") {
    alertify.alert("Isikan keterangan.");
    return;
  }
  if (tglconfirm == "") {
    alertify.alert("Isikan tanggal.");
    return;
  }
  tujuan = "sdm_slave_pjdx.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddatarenckegiatan();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editconfirm(no, tgl, statusconfrim, keterangan, updatetime, jenis) {
  document.getElementById("ketconfirm" + no).value = keterangan;
  document.getElementById("tglconfirm" + no).value = updatetime;
  if (statusconfrim == "1") {
    document.getElementById("confirmstat" + no).checked = true;
    document.getElementById("judulconf" + no).innerHTML = "Ya";
  } else {
    document.getElementById("confirmstat" + no).checked = false;
    document.getElementById("judulconf" + no).innerHTML = "Tidak";
  }
}
