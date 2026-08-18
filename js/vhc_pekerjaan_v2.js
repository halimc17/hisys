function getsubunit() {
  kodeorg = document.getElementById("kodeorg").value;

  param = "proses=getsubunit&kodeorg=" + kodeorg;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("kodetraksi").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function displayList() {
  // cancel_kepala_form();
  document.getElementById("formnew").style.display = "none";
  document.getElementById("detail").style.display = "none";
  document.getElementById("listcari").style.display = "block";
  document.getElementById("listData").style.display = "block";
  loaddata(0);
}

function formbaru() {
  document.getElementById("listData").style.display = "none";
  document.getElementById("formnew").style.display = "block";
  document.getElementById("detail").style.display = "none";
  document.getElementById("listcari").style.display = "none";
  document.getElementById("proses").value = "insert_header";
  clear_form();
  unlock_header_form();
  // bersih_form_pekerjaan();
}

function unlock_header_form() {
  document.getElementById("kodeorg").disabled = false;
  document.getElementById("kodetraksi").disabled = false;
  document.getElementById("jenisvhc").disabled = false;
  document.getElementById("tglpekerjaan").disabled = false;
  document.getElementById("kodevhc").disabled = false;
  document.getElementById("jenisbbm").disabled = false;
  document.getElementById("jmlh_bbm").disabled = false;
  document.getElementById("kontanan").disabled = false;
  document.getElementById("save_kepala").disabled = false;
  document.getElementById("cancel_kepala").disabled = false;
  document.getElementById("mandor").disabled = false;
  document.getElementById("save_kepala").style.visibility = "visible";
  document.getElementById("cancel_kepala").style.visibility = "visible";
}

function lock_header_form() {
  document.getElementById("kodeorg").disabled = true;
  document.getElementById("kodetraksi").disabled = true;
  document.getElementById("jenisvhc").disabled = true;
  document.getElementById("tglpekerjaan").disabled = true;
  document.getElementById("kodevhc").disabled = true;
  document.getElementById("jenisbbm").disabled = true;
  document.getElementById("jmlh_bbm").disabled = true;
  document.getElementById("kontanan").disabled = true;
  document.getElementById("save_kepala").disabled = true;
  document.getElementById("cancel_kepala").disabled = true;
  document.getElementById("mandor").disabled = true;
  document.getElementById("save_kepala").style.visibility = "hidden";
  document.getElementById("cancel_kepala").style.visibility = "hidden";
}

function clear_form() {
  setValue2("kodeorg", "");
  setValue2("kodetraksi", "");
  setValue2("jenisbbm", "");
  document.getElementById("no_trans").value = "";
  document.getElementById("tglpekerjaan").value = "";
  document.getElementById("jmlh_bbm").value = "0";
  document.getElementById("save_kepala").value = "";
  document.getElementById("cancel_kepala").value = "";
  document.getElementById("jenisvhc").innerHTML =
    "<option value=''>Pilih Data</option>";
  document.getElementById("kodevhc").innerHTML =
    "<option value=''>Pilih Data</option>";
}

function clear_operator() {
  document.getElementById("uphOprt").value = 0;
  document.getElementById("uphHelp").value = 0;
  document.getElementById("uphHelp2").value = 0;
  document.getElementById("prmiOprt").value = 0;
  document.getElementById("prmiHelp").value = 0;
  document.getElementById("prmiHelp2").value = 0;
  document.getElementById("prmiHelp3").value = 0;
  document.getElementById("pnltyOprt").value = 0;
  document.getElementById("ketOprt").value = "";
  // document.getElementById('prosesOpt').value = 'insert_operator';
}

function batalcariDataTransaksi() {
  document.getElementById("tgl_cari").value = "";
  document.getElementById("tgl_carisd").value = "";
  document.getElementById("txtCari").value = "";
  setValue2("kodevhc_cari", null);
  setValue2("kontanan_cari", "%");
  loaddata(0);
}

function bersih_form_pekerjaan() {
  document.getElementById("proses_pekerjaan").value = "insert_pekerjaan";
  setValue2("jns_kerja", null);
  setValue2("lokasi_kerja", null);
  document.getElementById("jns_kerja").disabled = false;
  document.getElementById("lokasi_kerja").disabled = false;

  document.getElementById("kode_karyawan").disabled = false;
  document.getElementById("kode_helper").disabled = false;
  document.getElementById("kode_helper2").disabled = false;

  document.getElementById("uphHelp").disabled = false;
  document.getElementById("prmiHelp").disabled = false;

  document.getElementById("uphHelp2").disabled = false;
  document.getElementById("prmiHelp2").disabled = false;
  // document.getElementById('lokasi_kerja3').disabled = false;

  // document.getElementById('uphOprt').disabled = false;
  document.getElementById("blok").disabled = false;
  document.getElementById("brt_muatan").value = 0;
  document.getElementById("jmlh_rit").value = 1;
  document.getElementById("ket").value = "";
  document.getElementById("blok").value =
    "<option value=''>" + dataKdvhc + "</options>";
  document.getElementById("blok").selectedIndex = 0;
  setValue2("blok", null);
  document.getElementById("kmhm_awal").disabled = false;
  document.getElementById("kmhm_akhir").value = 0;
  document.getElementById("prmiOprt").value = 0;
  document.getElementById("prmiOprt_old").value = 0;
  document.getElementById("prmiHelp").value = 0;
  document.getElementById("prmiHelp2").value = 0;
  document.getElementById("prmiHelp3").value = 0;
  document.getElementById("prmiHelp_old").value = 0;
  document.getElementById("uphOprt").value = 0;
  document.getElementById("uphHelp").value = 0;
  document.getElementById("uphHelp2").value = 0;
  document.getElementById("uphHelp3").value = 0;
  document.getElementById("uphHelp_old").value = 0;
  document.getElementById("uphHelp_old2").value = 0;
  document.getElementById("uphHelp_old3").value = 0;
  document.getElementById("uphOprt_libur").value = 0;
  document.getElementById("uphHelp_libur").value = 0;
  document.getElementById("uphHelp_libur2").value = 0;
  document.getElementById("uphHelp_libur3").value = 0;
  document.getElementById("prmiOprtTambahan").value = 0;
  document.getElementById("kode_karyawan_old").value = "";
  document.getElementById("kode_helper_old").value = "";
  document.getElementById("kode_helper_old2").value = "";
  document.getElementById("kode_helper_old3").value = "";
  document.getElementById("jlhhm").value = 0;
  document.getElementById("pnltyOprt").value = 0;
  document.getElementById("oldbrt_muatan").value = "";
  document.getElementById("satuan").innerHTML = "";
  document.getElementById("jnsstn").value = "";
  document.getElementById("jnsstnhelp").value = "";
  document.getElementById("basisborong").value = "";
  document.getElementById("basisboronghelp").value = "";
  document.getElementById("lebihdarisatupekerjaan").value = "";
  document.getElementById("checklembur").checked = false;
  setValue("kodesegment", "");
  setValue("kodesegment_name", "");
  setValue2("jns_kerja", "");
}

function enter(e) {
  key = getKey(e);
  if (key == 13) {
    loaddata(0);
    return true;
  } else {
    return tanpa_kutip_dan_sepasi(e);
  }
}

function adadenda() {
  if (
    document.getElementById("checkdenda").checked == true &&
    document.getElementById("pnltyOprt").disabled == true
  ) {
    document.getElementById("pnltyOprt").value = 0;
    document.getElementById("pnltyOprt").disabled = false;
  } else {
    document.getElementById("pnltyOprt").disabled = true;
  }
}

function adalembur() {
  if (
    document.getElementById("checklembur").checked == true &&
    document.getElementById("prmiOprt").value != ""
  ) {
    document.getElementById("prmiOprt").value = 0;
  } else {
    document.getElementById("prmiOprt").value = getValue("prmiOprt_old");
  }
}

function doneEntry() {
  if (confirm("Are you sure..?")) {
    cancel_kepala_form();
    setTimeout(function () {
      bersih_form_pekerjaan();
      getKmAkhir();
      setTimeout(function () {
        clear_operator();
      }, 200);
    }, 100);
  } else {
    return;
  }
}

function cancel_kepala_form() {
  document.getElementById("save_kepala").disabled = true;
  document.getElementById("cancel_kepala").disabled = true;
  unlock_header_form();
  setTimeout(function () {
    clear_form();
  }, 100);
}

function getjumlah(sumber) {
  awal = document.getElementById("kmhm_awal").value;
  akhir = document.getElementById("kmhm_akhir").value;
  jlhhm = document.getElementById("jlhhm").value;

  if (sumber == "jumlah") {
    hmakhir = parseFloat(awal) + parseFloat(jlhhm);
    document.getElementById("kmhm_akhir").value = hmakhir.toFixed(2);
  } else {
    jumlah = parseFloat(akhir) - parseFloat(awal);
    if (isNaN(jumlah) == true) {
      document.getElementById("jlhhm").value = 0;
    } else {
      document.getElementById("jlhhm").value = jumlah.toFixed(2);
    }
  }
}

async function getKodeVhc() {
  document.getElementById("kodevhc").innerHTML = await optkodevhc();
}

function getPremi() {
  kodetraksi = getValue("kodetraksi");
  notrans = getValue("no_trans");
  tglTrans = getValue("tglpekerjaan");
  karyawan = getValue("kode_karyawan");
  kode_helper = getValue("kode_helper");
  kode_helper2 = getValue("kode_helper2");
  kode_helper3 = getValue("kode_helper3");
  jenisvhc = getValue("jenisvhc");
  jns_kerja = getValue("jns_kerja");
  brtmuatan = getValue("brt_muatan");
  blok = getValue("blok");
  jmlhRit = getValue("jmlh_rit");
  stn = getValue("stn");
  jlhhm = getValue("jlhhm");
  kodevhc = getValue("kodevhc");
  proskerja = getValue("proses_pekerjaan");
  oldbrtmuatan = getValue("oldbrt_muatan");
  lokasi_kerja = getValue("lokasi_kerja");
  kodeorg = getValue("kodeorg");

  if (jmlhRit == "") {
    jmlhRit = 0;
  }

  param = "proses=getPremi" + "&tglpekerjaan=" + tglTrans;
  param += "&no_trans=" + notrans;
  param += "&kodetraksi=" + kodetraksi;
  param += "&kode_karyawan=" + karyawan;
  param += "&jenisvhc=" + jenisvhc;
  param += "&jns_kerja=" + jns_kerja;
  param += "&brt_muatan=" + brtmuatan;
  param += "&blok=" + blok;
  param += "&jmlh_rit=" + jmlhRit;
  param += "&stn=" + stn;
  param += "&jlhhm=" + jlhhm;
  param += "&kode_helper=" + kode_helper;
  param += "&kode_helper2=" + kode_helper2;
  param += "&kode_helper3=" + kode_helper3;
  param += "&kde_vhc=" + kodevhc;
  param += "&proses_pekerjaan=" + proskerja;
  param += "&oldbrt_muatan=" + oldbrtmuatan;
  param += "&lokasi_kerja=" + lokasi_kerja;
  param += "&kodeorg=" + kodeorg;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (document.getElementById("jmlh_rit").value == "") {
            document.getElementById("jmlh_rit").value = 1;
          }
          if (document.getElementById("brt_muatan").value == "") {
            document.getElementById("brt_muatan").value = 0;
          }

          hsl = JSON.parse(con.responseText);
          uphoprtlibur = document.getElementById("uphOprt_libur").value;
          onemoreactivity = document.getElementById(
            "lebihdarisatupekerjaan",
          ).value;

          document.getElementById("prmiOprt").value = hsl.preminya;
          document.getElementById("prmiOprt_old").value = hsl.preminya;

          document.getElementById("prmiHelp").value = hsl.premihelp;
          document.getElementById("prmiHelp_old").value = hsl.premihelp;

          document.getElementById("prmiHelp2").value = hsl.premihelp2;
          document.getElementById("prmiHelp_old2").value = hsl.premihelp2;

          document.getElementById("jnsstn").value = hsl.jnsprmi;
          document.getElementById("basisborong").value = hsl.basis;
          getjumlah();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getkegiatan() {
  return new Promise((resolve) => {
    jenis_vhc = document.getElementById("jenisvhc").value;
    kde_vhc = document.getElementById("kodevhc").value;
    no_trans = document.getElementById("no_trans").value;
    param = "jenisvhc=" + jenis_vhc + "&proses=getkodekegiatan";
    param += "&kodevhc=" + kde_vhc;
    param += "&no_trans=" + no_trans;

    tujuan = "vhc_slave_pekerjaan_v2.php";
    post_response_text(tujuan, param, respog);

    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            resolve(con.responseText);
            // dt = con.responseText.split('###');
            // document.getElementById('jns_kerja').innerHTML = dt[0];
            // setTimeout(function() {
            //     setValue2('jns_kerja',trim(dt[1]));
            // }, 300);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
}

function getUmr() {
  kodeorg = getValue("kodeorg");
  kdkry = getValue("kode_karyawan");
  kode_helper = getValue("kode_helper");
  kode_helper2 = getValue("kode_helper2");
  kode_helper3 = getValue("kode_helper3");
  tanggal = getValue("tglpekerjaan");
  no_trans = getValue("no_trans");
  jns_kerja = getValue("jns_kerja");
  lokasi_kerja = getValue("lokasi_kerja");
  proskerja = getValue("proses_pekerjaan");
  uphOprt = getValue("uphOprt");
  uphHelp = getValue("uphHelp");
  uphHelp2 = getValue("uphHelp2");
  uphHelp3 = getValue("uphHelp3");
  tahun = tanggal.substr(6, 4);

  param =
    "proses=getUmr" +
    "&kode_karyawan=" +
    kdkry +
    "&kode_helper=" +
    kode_helper +
    "&kode_helper2=" +
    kode_helper2 +
    "&kode_helper3=" +
    kode_helper3;
  param +=
    "&tahun=" +
    tahun +
    "&tglpekerjaan=" +
    tanggal +
    "&no_trans=" +
    no_trans +
    "&kodeorg=" +
    kodeorg;
  param += "&jns_kerja=" + jns_kerja;
  param += "&lokasi_kerja=" + lokasi_kerja;
  param += "&proses_pekerjaan=" + proskerja;
  param += "&uphOprt=" + uphOprt;
  param += "&uphHelp=" + uphHelp;
  param += "&uphHelp2=" + uphHelp2;
  param += "&uphHelp3=" + uphHelp3;
  tujuan = "vhc_slave_pekerjaan_v2.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText.split("####");

          document.getElementById("uphOprt").value = trim(data[0]);
          document.getElementById("uphHelp").value = trim(data[1]);
          document.getElementById("uphHelp2").value = trim(data[2]);
          document.getElementById("uphHelp3").value = trim(data[3]);

          document.getElementById("lebihdarisatupekerjaan").value = trim(
            data[10],
          );

          // Jika pakek setup premi 1 maka upah pakek flow PALMA
          if (trim(data[11]) == 1) {
            if (trim(data[4]) == 1 && trim(data[9]) == "libur") {
              //kalo non staff
              document.getElementById("uphOprt").value = 0;
              document.getElementById("uphOprt_libur").value = trim(data[0]);
            }

            if (trim(data[5]) == 1 && trim(data[9]) == "libur") {
              //kalo non staff
              document.getElementById("uphHelp").value = 0;
              document.getElementById("uphHelp_libur").value = trim(data[1]);
            }

            if (trim(data[6]) == 1 && trim(data[9]) == "libur") {
              //kalo non staff
              document.getElementById("uphHelp2").value = 0;
              document.getElementById("uphHelp_libur2").value = trim(data[2]);
            }

            if (trim(data[7]) == 1 && trim(data[9]) == "libur") {
              //kalo non staff
              document.getElementById("uphHelp3").value = 0;
              document.getElementById("uphHelp_libur3").value = trim(data[3]);
            }
          }

          // else{
          // document.getElementById('uphOprt').disabled = false;
          // document.getElementById('uphHelp').disabled = false;
          // }
          // if(trim(data[3]) != ''){
          // 	alertify.alert('Informasi',data[3]);nonaktifkan dulu sementara bikin tampilan kurang menarik
          // }
          // getKmAkhir();

          getPremi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getSatuan(jns_pekerjan, kdkbn, kdblok) {
  param =
    "jns_kerja=" +
    jns_pekerjan +
    "&proses=getSatuan" +
    "&kodeOrg=" +
    getValue("kodeorg") +
    "&kdkbn=" +
    kdkbn +
    "&kdblok=" +
    kdblok;
  tujuan = "vhc_slave_pekerjaan_v2.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          dtIsi = con.responseText.split("####");
          document.getElementById("satuan").innerHTML = dtIsi[0];
          document.getElementById("lokasi_kerja").innerHTML = dtIsi[1];
          getBlok(kdkbn, kdblok, jns_pekerjan);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getBlok(kdkbn, kdblok, jns_pekerjan, kodedept) {
  if (document.getElementById("jns_kerja").value == "") {
    // alert("Jenis Pekerjaan harus diisi terlebih dahulu!");
    document.getElementById("lokasi_kerja").selectedIndex = 0;
    return false;
  }

  if (kdkbn == "" && kdblok == "") {
    locationKerja =
      document.getElementById("lokasi_kerja").options[
        document.getElementById("lokasi_kerja").selectedIndex
      ].value;
    jnsPekerjaan =
      document.getElementById("jns_kerja").options[
        document.getElementById("jns_kerja").selectedIndex
      ].value;
    param =
      "lokasi_kerja=" +
      locationKerja +
      "&jns_kerja=" +
      jnsPekerjaan +
      "&proses=getBlok";
  } else {
    locationKerja = kdkbn;
    Blok = kdblok;
    jnsPekerjaan =
      document.getElementById("jns_kerja").options[
        document.getElementById("jns_kerja").selectedIndex
      ].value;
    param =
      "lokasi_kerja=" +
      locationKerja +
      "&jns_kerja=" +
      jnsPekerjaan +
      "&blok=" +
      Blok +
      "&proses=getBlok";
  }

  param += "&no_trans=" + document.getElementById("no_trans").value;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          at = con.responseText.split("###");
          document.getElementById("blok").innerHTML = at[0];
          document.getElementById("old_blok").value = at[1];
          if (
            document.getElementById("proses_pekerjaan").value == "update_kerja"
          ) {
            setValue2("blok", kdblok);
          }
          // $('#blok').select2();
          // getdept(jns_pekerjan,kodedept);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getdept(jns_pekerjan, kodedept) {
  param = "jns_kerja=" + jns_pekerjan + "&proses=getdept";
  tujuan = "vhc_slave_pekerjaan_v2.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (kodedept == undefined) {
            document.getElementById("dept").innerHTML = con.responseText;
          } else {
            setValue2("dept", kodedept);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getKmAkhir() {
  return new Promise((resolve) => {
    var kodevhc = getValue("kodevhc"),
      param = "proses=getKmAkhir&kodevhc=" + kodevhc;
    tujuan = "vhc_slave_pekerjaan_v2.php";
    post_response_text(tujuan, param, respog);

    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            // if(document.getElementById('proses_pekerjaan').value != 'update_kerja'){
            // 	setValue('kmhm_awal', con.responseText);
            // }
            if (parseFloat(con.responseText) > 0) {
              getById("kmhm_awal").disabled = false;
            } else {
              getById("kmhm_awal").disabled = false;
            }
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
}

// function getUmrHelp(posisi) {
// 	help = document.getElementById('kode_helper').options[document.getElementById('kode_helper').selectedIndex].value;
// 	help2= document.getElementById('kode_helper2').options[document.getElementById('kode_helper2').selectedIndex].value;
// 	help3= document.getElementById('kode_helper3').options[document.getElementById('kode_helper3').selectedIndex].value;
// 	tanggal = document.getElementById('tglpekerjaan').value;
// 	tahun = tanggal.substr(6, 4);
// 	param = 'method=geUtmr' + '&helper=' + help + '&tahun=' + tahun + '&tglpekerjaan=' + tanggal;
// 	tujuan = 'vhc_slave_pekerjaan_v2.php';
// 	post_response_text(tujuan, param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alertify.alert("Info",con.responseText);
// 					document.getElementById('kode_helper').value = "";
// 					document.getElementById('kode_helper2').value = "";
// 					document.getElementById('kode_helper3').value = "";
// 				} else {
// 					getPremiHelp(posisi);
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }

function optkodevhc(notrans) {
  return new Promise((resolve) => {
    proses = document.getElementById("proses").value;
    if (notrans == undefined) {
      jns_id = document.getElementById("jenisvhc").value;
      kodetraksi = document.getElementById("kodetraksi").value;
      param =
        "jenisvhc=" +
        jns_id +
        "&kodetraksi=" +
        kodetraksi +
        "&proses=getKodeVhc";
    } else {
      param = "no_trans=" + notrans;
      param += "&proses=getKodeVhc";
    }
    tujuan = "vhc_slave_pekerjaan_v2.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            resolve(con.responseText);
            // data = con.responseText.split("####");
            // document.getElementById('kodevhc').innerHTML = data[0];
            // loaddetail();
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
}

function optjenisvhc() {
  return new Promise((resolve) => {
    kodetraksi = document.getElementById("kodetraksi").value;

    param = "proses=getjenisvhc";
    param += "&kodetraksi=" + kodetraksi;

    tujuan = "vhc_slave_pekerjaan_v2.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            dt = con.responseText.split("###");

            resolve([dt[0], dt[1]]);
            // isi = con.responseText.split("##");
            // document.getElementById('jenisvhc').innerHTML=trim(isi[0]);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
}

async function getjenisvhc() {
  const [jenis, mandor] = await optjenisvhc();

  document.getElementById("jenisvhc").innerHTML = jenis;
  document.getElementById("mandor").innerHTML = mandor;
}

function fillField(noTrans, Thn) {
  document.getElementById("formnew").style.display = "block";
  document.getElementById("detail").style.display = "block";
  document.getElementById("listcari").style.display = "none";
  document.getElementById("listData").style.display = "none";
  notrn = noTrans;
  param = "no_trans=" + notrn + "&proses=getData";
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("proses").value = "update_head";
          ar = con.responseText.split("####");
          document.getElementById("no_trans").value = ar[0];
          setValue2("kodeorg", ar[1]);
          setValue2("kodetraksi", ar[2]);

          document.getElementById("tglpekerjaan").value = ar[4];
          document.getElementById("mandor").innerHTML = ar[10];
          document.getElementById("kode_karyawan").innerHTML = ar[5];
          document.getElementById("kode_helper").innerHTML = ar[11];
          document.getElementById("kode_helper2").innerHTML = ar[11];
          document.getElementById("kode_helper3").innerHTML = ar[11];
          setValue2("jenisbbm", ar[6]);
          document.getElementById("jmlh_bbm").value = ar[7];
          if (ar[8] == 1) {
            document.getElementById("kontanan").checked = true;
          } else {
            document.getElementById("kontanan").checked = false;
          }
          document.getElementById("tglpekerjaan").disabled = true;
          document.getElementById("kodeorg").disabled = true;

          document.getElementById("jenisvhc").innerHTML = ar[3];

          lock_header_form();
          document.getElementById("kodevhc").innerHTML = await optkodevhc(
            ar[0],
          );
          document.getElementById("jns_kerja").innerHTML = await getkegiatan();

          bersih_form_pekerjaan();
          if (
            document.getElementById("proses_pekerjaan").value != "update_kerja"
          ) {
            document.getElementById("kmhm_awal").value = await getKmAkhir();
          }
          loaddetail();

          window.scrollTo({
            top: 0,
            left: 0,
            behavior: "smooth",
          });
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function save_header() {
  jenis_vhc = getValue("jenisvhc");
  jenisbbm = getValue("jenisbbm");
  kdOrg = getValue("kodeorg");
  tgl_kerja = getValue("tglpekerjaan");
  jmlh = getValue("jmlh_bbm");
  no_trans = getValue("no_trans");
  kdVhc = getValue("kodevhc");
  pro = getValue("proses");
  mandor = getValue("mandor");

  if (kdOrg == "") {
    alertify.alert("Informasi ", "Kode organisasi harus dipilih.");
    return false;
  } else if (kodetraksi == "") {
    alertify.alert("Informasi ", "Kode traksi harus dipilih.");
    return false;
  } else if (jenis_vhc == "") {
    alertify.alert("Informasi ", "Jenis kendaraan harus dipilih.");
    return false;
  } else if (tgl_kerja == "") {
    alertify.alert("Informasi ", "Tanggal tidak boleh kosong.");
    return false;
  } else if (kdVhc == "") {
    alertify.alert("Informasi ", "Kode kendaraan harus dipilih.");
    return false;
  }

  if (document.getElementById("kontanan").checked == true) {
    kontanan = "KONTAN";
  } else {
    kontanan = "";
  }

  param =
    "jenisvhc=" +
    jenis_vhc +
    "&kodevhc=" +
    kdVhc +
    "&tglpekerjaan=" +
    tgl_kerja +
    "&kodeorg=" +
    kdOrg +
    "&jenisbbm=" +
    jenisbbm +
    "&jmlh_bbm=" +
    jmlh +
    "&proses=" +
    pro +
    "&no_trans=" +
    no_trans +
    "&kontanan=" +
    kontanan +
    "&mandor=" +
    mandor;
  tujuan = "vhc_slave_pekerjaan_v2.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          isidt = con.responseText.split("####");

          setValue("kmhm_awal", isidt[0]);
          setValue("no_trans", isidt[1]);
          document.getElementById("jns_kerja").innerHTML = isidt[2];
          document.getElementById("kode_karyawan").innerHTML = isidt[3];
          document.getElementById("kode_helper").innerHTML = isidt[4];
          document.getElementById("kode_helper2").innerHTML = isidt[4];
          document.getElementById("kode_helper3").innerHTML = isidt[4];
          document.getElementById("detail").style.display = "block";
          lock_header_form();
          loaddetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function loaddata(num) {
  txtTgl = document.getElementById("tgl_cari").value;
  tgl_carisd = document.getElementById("tgl_carisd").value;
  txtCari = document.getElementById("txtCari").value;
  kodevhc_cari = document.getElementById("kodevhc_cari").value;
  kontan_cari = document.getElementById("kontanan_cari").value;
  param = "proses=loaddata&page=" + num;
  param +=
    "&tgl_cari=" +
    txtTgl +
    "&txtCari=" +
    txtCari +
    "&kodevhc_cari=" +
    kodevhc_cari +
    "&tgl_carisd=" +
    tgl_carisd +
    "&kontanan_cari=" +
    kontan_cari;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          data = con.responseText.split("##");
          document.getElementById("contain").innerHTML = data[0];
          document.getElementById("containfoot").innerHTML = data[1];
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function load_data_exc(data) {
  var contain = document.getElementById("contain");
  var foot = document.getElementById("containfoot");
  //html modification  - author : Atwal
  html = "";
  if (data.tbody.length > 0) {
    for (i = 0; i < data.tbody.length; i++) {
      html += "<tr class=rowcontent>";
      html += "<td align=center>" + data.tbody[i].no + "</td>";
      html += "<td align=center>" + data.tbody[i].notransaksi + "</td>";
      html += "<td align=center>" + data.tbody[i].jenisvhc + "</td>";
      html += "<td align=center>" + data.tbody[i].kodevhc + "</td>";
      html += "<td align=center>" + data.tbody[i].tanggal + "</td>";
      html += "<td align=center>" + data.tbody[i].namabarang + "</td>";
      html += " <td align=center>" + data.tbody[i].jlhbbm + "</td>";
      html += " <td align=center>" + data.tbody[i].img + "</td>";
    }

    htmlfoot = "<tr class=rowheader><td colspan=8 align=center>";
    htmlfoot +=
      parseInt(data.tfoot.page) * parseInt(data.tfoot.limit) +
      1 +
      " to " +
      (parseInt(data.tfoot.page) + 1) * parseInt(data.tfoot.limit) +
      " Of " +
      data.tfoot.jlhbrs;
    htmlfoot +=
      "<br /><button class=mybutton onclick=cariBast(" +
      (parseInt(data.tfoot.page) - 1) +
      ");>" +
      data.tfoot.pref +
      "</button>";
    htmlfoot +=
      "<button class=mybutton onclick=cariBast(" +
      (parseInt(data.tfoot.page) + 1) +
      ");>" +
      data.tfoot.lanjut +
      "</button>";
    htmlfoot += "</td></tr>";
    //Mengirim HTML by ID  - author : Atwal
    contain.innerHTML = html;
    foot.innerHTML = htmlfoot;
  } else {
    contain.innerHTML =
      "<tr class=rowheader><td colspan=8 align=left>Data Kosong</td></tr>";
  }
}

function load_data_operator() {
  if (document.getElementById("no_trans_opt").value != "") {
    no_tans = document.getElementById("no_trans_opt").value;
    param = "proses=load_data_opt";
    param += "&no_trans=" + no_tans;
    tujuan = "vhc_slave_pekerjaan_v2.php";
    async function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("containOperator").innerHTML =
              con.responseText;
            noTrans = document.getElementById("no_trans_opt").value;
            document.getElementById("kmhm_awal").value = await getKmAkhir();
            // getKmAkhir();
            //  getKntrk(thn,nokntrak);
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

function loaddetail() {
  if (document.getElementById("no_trans").value != "") {
    no_trans = getValue("no_trans");
    param = "no_trans=" + no_trans;
    param += "&proses=loaddetail";
    tujuan = "vhc_slave_pekerjaan_v2.php";

    async function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("containdetail").innerHTML =
              con.responseText;
            leftFixedTable();
            if (
              document.getElementById("proses_pekerjaan").value !=
              "update_kerja"
            ) {
              document.getElementById("kmhm_awal").value = await getKmAkhir();
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
}

function cariData(num) {
  txtTgl = document.getElementById("tgl_cari").value;
  txtCari = document.getElementById("txtCari").value;
  kontan_cari = document.getElementById("kontanan_cari").value;
  kodevhc_cari = document.getElementById("kodevhc_cari").value;
  param =
    "tgl_cari=" +
    txtTgl +
    "&txtCari=" +
    txtCari +
    "&kodevhc_cari=" +
    kodevhc_cari +
    "&kontanan_cari=" +
    kontan_cari;

  param += "&proses=cariTransaksi";
  param += "&page=" + num;
  tujuan = "vhc_slave_pekerjaan_v2.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("contain").innerHTML = con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cariDataTransaksi() {
  txtTgl = document.getElementById("tgl_cari").value;
  txtCari = document.getElementById("txtCari").value;
  kodevhc_cari = document.getElementById("kodevhc_cari").value;
  kontan_cari = document.getElementById("kontanan_cari").value;
  param =
    "tgl_cari=" +
    txtTgl +
    "&txtCari=" +
    txtCari +
    "&kodevhc_cari=" +
    kodevhc_cari +
    "&kontanan_cari=" +
    kontan_cari;
  param += "&proses=cariTransaksi";
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
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
}

function cariBast(num) {
  param = "proses=load_data_header";
  param += "&page=" + num;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //Get data convert to json - author : Atwal
          data = JSON.parse(con.responseText);
          //Go to function create html  - author : Atwal
          load_data_exc(data);
          //document.getElementById('contain').innerHTML=con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cariBastKrj(num) {
  param = "proses=loaddetail";
  param += "&page=" + num;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containdetail").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cariBastOpt(num) {
  param = "proses=load_data_opt";
  param += "&page=" + num;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containOperator").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function fillFieldKrj(
  jnsKrj,
  lokKrj,
  brtMuat,
  jmlhRit,
  ktr,
  bya,
  kmawl,
  kmakhr,
  stn,
  segment,
  nmSegment,
  kodedept,
  operator,
  premiopt,
  helper,
  helper2,
  helper3,
  premihelp,
  premihelp2,
  premihelp3,
  upah,
  upahhelp,
  upahhelp2,
  upahhelp3,
  denda,
  basisborong,
  checklembur,
  premidarisetup,
  premitambahan,
) {
  if (lokKrj.length > 4) {
    kdkbn = lokKrj.substr(0, 4);
    kdblok = lokKrj;
  } else {
    kdkbn = lokKrj;
    kdblok = "";
  }

  // getSatuan(jns_pekerjan,kdkbn,kdblok)
  setTimeout(function () {
    getSatuan(jnsKrj, kdkbn, kdblok);
  }, 200);

  // if(helper == '0000000000' || helper2 == '0000000000' || helper3 == '0000000000'){
  // 	helper = '';
  // 	helper2 = '';
  // 	helper3 = '';
  // }

  document.getElementById("jns_kerja").value = jnsKrj;
  document.getElementById("old_jnskerja").value = jnsKrj;
  document.getElementById("brt_muatan").value = brtMuat;
  document.getElementById("jmlh_rit").value = jmlhRit;
  document.getElementById("biaya").value = bya;
  document.getElementById("ket").value = ktr;
  document.getElementById("uphOprt").value = upah;
  document.getElementById("uphHelp").value = upahhelp;
  document.getElementById("uphHelp2").value = upahhelp2;
  document.getElementById("uphHelp3").value = upahhelp3;
  document.getElementById("uphOprt_old").value = upah;
  document.getElementById("uphHelp_old").value = upahhelp;
  document.getElementById("prmiHelp_old").value = premihelp;
  document.getElementById("prmiHelp_old2").value = premihelp2;
  document.getElementById("prmiHelp_old3").value = premihelp3;
  document.getElementById("pnltyOprt").value = denda;
  document.getElementById("kmhm_awal").value = kmawl;
  document.getElementById("kmhm_akhir").value = kmakhr;
  document.getElementById("proses_pekerjaan").value = "update_kerja";
  document.getElementById("old_jnskerja").value = jnsKrj;
  document.getElementById("oldbrt_muatan").value = brtMuat;
  document.getElementById("kode_karyawan_old").value = operator;
  document.getElementById("kode_helper_old").value = helper;
  document.getElementById("kode_helper_old2").value = helper2;
  document.getElementById("kode_helper_old3").value = helper3;
  document.getElementById("basisborong").value = basisborong;
  document.getElementById("basisboronghelp").value = basisborong;
  document.getElementById("prmiOprtTambahan").value = premitambahan;

  setValue("kodesegment", segment);
  setValue("kodesegment_name", nmSegment);
  setValue2("jns_kerja", jnsKrj);
  setValue2("stn", stn);

  setValue2("kode_karyawan", operator);
  setValue2("kode_helper", helper);
  setValue2("kode_helper2", helper2);
  setValue2("kode_helper3", helper3);

  setValue2("prmiOprt", premiopt);
  setValue2("prmiHelp", premihelp);
  setValue2("prmiHelp2", premihelp2);
  setValue2("prmiHelp3", premihelp3);

  jumlah = parseFloat(kmakhr) - parseFloat(kmawl);
  document.getElementById("jlhhm").value = jumlah.toFixed(2);

  document.getElementById("jns_kerja").disabled = true;
  document.getElementById("lokasi_kerja").disabled = true;
  document.getElementById("blok").disabled = true;
  document.getElementById("kmhm_awal").disabled = true;

  document.getElementById("kode_karyawan").disabled = true;
  document.getElementById("kode_helper").disabled = true;
  document.getElementById("kode_helper2").disabled = true;
  document.getElementById("kode_helper3").disabled = true;

  if (helper === "") {
    document.getElementById("uphHelp").disabled = true;
    document.getElementById("prmiHelp").disabled = true;
  }

  if (helper2 === "") {
    document.getElementById("uphHelp2").disabled = true;
    document.getElementById("prmiHelp2").disabled = true;
  }

  if (lokKrj.length > 4) {
    kd = lokKrj;
    //document.getElementById('lokasi_kerja').value = kd.substring(0, 4);
    getBlok(kd.substring(0, 4), kd, jnsKrj, kodedept);

    //setValue2('lokasi_kerja',kd.substring(0,4));
    document.getElementById("old_lokkerja").value = kd;

    document.getElementById("dept").value = kodedept;
  } else {
    document.getElementById("old_lokkerja").value = lokKrj;
    //document.getElementById('lokasi_kerja').value = lokKrj;
    setValue2("lokasi_kerja", lokKrj);
    getBlok(0, 0, 0, kodedept);
    // document.getElementById('blok').innerHTML="<option value=''>"+dataKdvhc+"</option>";
  }

  if (denda != 0) {
    document.getElementById("pnltyOprt").disabled = false;
    document.getElementById("checkdenda").checked = true;
  } else {
    document.getElementById("pnltyOprt").disabled = true;
    document.getElementById("checkdenda").checked = false;
  }

  if (checklembur != 0) {
    console.log(premidarisetup);

    document.getElementById("checklembur").checked = true;
    document.getElementById("prmiOprt_old").value = premidarisetup;
  } else {
    console.log(premidarisetup);
    document.getElementById("prmiOprt_old").value = premiopt;
    document.getElementById("checklembur").checked = false;
  }
}

function save_pekerjaan() {
  notrans = document.getElementById("no_trans").value;

  if (notrans == "") {
    alert("No transaksi kosong silahkan klik Buat baru");
    return;
  }

  jns_pekerjan = getValue("jns_kerja");
  if (getValue("old_jnskerja") == "") {
    setValue2("old_jnskerja", jns_pekerjan);
  }

  jlhhm = getValue("jlhhm");
  kmhm_aw = getValue("kmhm_awal");
  kmhm_ak = getValue("kmhm_akhir");
  satuan = getValue("stn");
  oldkerja = getValue("old_jnskerja");
  locationKerj = getValue("lokasi_kerja");
  brtmuatan = getValue("brt_muatan");
  jmlh_rit = getValue("jmlh_rit");
  keterangan = getValue("ket");
  kdKry = getValue("kode_karyawan");
  kode_helper = getValue("kode_helper");
  kode_helper2 = getValue("kode_helper2");
  kode_helper3 = getValue("kode_helper3");
  pnltyOprt = getValue("pnltyOprt");
  pro = getValue("proses_pekerjaan");
  bya = getValue("biaya");
  tglTrans = getValue("tglpekerjaan");
  Blok = getValue("blok");
  kodesegment = getValue("kodesegment");
  uphoprt = getValue("uphOprt");
  checklembur = getValue("checklembur");
  prmiOprtTambahan = getValue("prmiOprtTambahan");

  param =
    "no_trans=" +
    notrans +
    "&jns_kerja=" +
    jns_pekerjan +
    "&lokasi_kerja=" +
    locationKerj +
    "&biaya=" +
    bya +
    "&kode_karyawan=" +
    kdKry +
    "&kode_helper=" +
    kode_helper +
    "&kode_helper2=" +
    kode_helper2 +
    "&kode_helper3=" +
    kode_helper3 +
    "&pnltyOprt=" +
    pnltyOprt;
  param +=
    "&brt_muatan=" +
    brtmuatan +
    "&jmlh_rit=" +
    jmlh_rit +
    "&ket=" +
    keterangan +
    "&proses=" +
    pro +
    "&old_jnskerja=" +
    oldkerja +
    "&uphOprt=" +
    uphoprt +
    "&checklembur=" +
    checklembur +
    "&prmiOprtTambahan=" +
    prmiOprtTambahan;
  param +=
    "&kmhm_awal=" +
    kmhm_aw +
    "&kmhm_akhir=" +
    kmhm_ak +
    "&stn=" +
    satuan +
    "&tglpekerjaan=" +
    tglTrans +
    "&kodesegment=" +
    kodesegment +
    "&oldSegment=" +
    getValue("oldSegment");

  if (
    kode_helper == kode_helper2 ||
    kode_helper == kode_helper3 ||
    kode_helper2 == kode_helper3
  ) {
    if (kode_helper != "" && kode_helper2 != "") {
      alert("Helper tidak boleh sama dari helper-helper yang dipilih.");
      return false;
    }
    if (kode_helper != "" && kode_helper3 != "") {
      alert("Helper tidak boleh sama dari helper-helper yang dipilih.");
      return false;
    }
    if (kode_helper2 != "" && kode_helper3 != "") {
      alert("Helper tidak boleh sama dari helper-helper yang dipilih.");
      return false;
    }
  }

  if (document.getElementById("oldbrt_muatan").value != "") {
    oldbrt_muatan = document.getElementById("oldbrt_muatan").value;
    param += "&oldbrt_muatan=" + oldbrt_muatan;
  }

  if (document.getElementById("old_lokkerja").value != "") {
    old_lokKerja = document.getElementById("old_lokkerja").value;
    param += "&old_lokkerja=" + old_lokKerja;
  }
  if (document.getElementById("old_blok").value != "") {
    oldBlok = document.getElementById("old_blok").value;
    param += "&old_blok=" + oldBlok;
  }

  if (Blok != "") {
    param += "&blok=" + Blok;
  }

  tujuan = "vhc_slave_pekerjaan_v2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          isidt = 0;
          if (con.responseText != "salah") {
            isidt = parseInt(con.responseText);
            save_operator();
            setTimeout(() => {
              document.getElementById("kmhm_awal").disabled = false;
              document.getElementById("jns_kerja").disabled = false;
              document.getElementById("lokasi_kerja").disabled = false;
              document.getElementById("blok").disabled = false;
              // document.getElementById('kmhm_awal').value = isidt;
            }, 700);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function delHead(noTran, page) {
  notrans = noTran;
  param = "no_trans=" + notrans + "&proses=deleteHead";
  tujuan = "vhc_slave_pekerjaan_v2.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loaddata(page);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  if (
    confirm(
      "Data Header dan detail pada notransaksi = " +
        notrans +
        " akan dihapus, apakah anda yakin ?",
    )
  ) {
    post_response_text(tujuan, param, respog);
  } else {
    return;
  }
}

function delDataKrj(
  noTrans,
  jnsKerja,
  blok,
  segment,
  beratmuatan,
  kdkry,
  kode_helper,
  kode_helper2,
  kode_helper3,
  kodevhc,
  kmhmAwal,
) {
  param =
    "no_trans=" +
    noTrans +
    "&jns_kerja=" +
    jnsKerja +
    "&blok=" +
    blok +
    "&kodesegment=" +
    segment +
    "&brt_muatan=" +
    beratmuatan +
    "&kode_karyawan=" +
    kdkry +
    "&kode_helper=" +
    kode_helper +
    "&kode_helper2=" +
    kode_helper2 +
    "&kode_helper3=" +
    kode_helper3 +
    "&kde_vhc=" +
    kodevhc +
    "&kmhm_awal=" +
    kmhmAwal +
    "&proses=deleteKrj";
  tujuan = "vhc_slave_pekerjaan_v2.php";
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          delData(noTrans, kdkry, kode_helper, kode_helper2, kode_helper3);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  if (confirm("Delete, are you sure?")) {
    post_response_text(tujuan, param, respog);
  } else {
    return;
  }
}

function delData(noTrans, Kdkry, kode_helper, kode_helper2, kode_helper3) {
  no_trans = document.getElementById("no_trans").value = noTrans;
  kdKry = document.getElementById("kode_karyawan").value = Kdkry;
  pros = document.getElementById("prosesOpt");
  //pros.value=;
  param =
    "no_trans=" +
    no_trans +
    "&kode_karyawan=" +
    kdKry +
    "&kode_helper=" +
    kode_helper +
    "&kode_helper2=" +
    kode_helper2 +
    "&kode_helper3=" +
    kode_helper3 +
    "&proses=delete_opt";
  tujuan = "vhc_slave_pekerjaan_v2.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          bersih_form_pekerjaan();
          loaddetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function save_operator() {
  notrans = getValue("no_trans");
  jenisvhc = getValue("jenisvhc");
  kdKry = getValue("kode_karyawan");
  kode_helper = getValue("kode_helper");
  kode_helper2 = getValue("kode_helper2");
  kode_helper3 = getValue("kode_helper3");
  pnltyOprt = getValue("pnltyOprt");
  uphoprt = getValue("uphOprt");
  uphhelp = getValue("uphHelp");
  uphhelp2 = getValue("uphHelp2");
  uphhelp3 = getValue("uphHelp3");
  prmiOprt = getValue("prmiOprt");
  prmiOprtold = getValue("prmiOprt_old");
  prmiHelp = getValue("prmiHelp");
  prmiHelp2 = getValue("prmiHelp2");
  prmiHelp3 = getValue("prmiHelp3");
  prmiHelpold = getValue("prmiHelp_old");
  prmiHelpold2 = getValue("prmiHelp_old2");
  prmiHelpold3 = getValue("prmiHelp_old3");
  tglTrans = getValue("tglpekerjaan");
  ketOprt = getValue("ketOprt");
  jns_kerja = getValue("jns_kerja");
  blok = getValue("blok");
  olbrtrmuatan = getValue("oldbrt_muatan");
  jnsstn = getValue("jnsstn");
  jnsstnhelp = getValue("jnsstnhelp");
  basisborong = getValue("basisborong");
  basisborongh = getValue("basisboronghelp");
  kmhmAwal = getValue("kmhm_awal");
  proskerja = getValue("proses_pekerjaan");
  locationKerj = getValue("lokasi_kerja");
  karyold = getValue("kode_karyawan_old");
  prmiOprtTambahan = getValue("prmiOprtTambahan");
  // if (kdKry == '') {
  // alertify.alert('Informasi','Operator wajib di pilih !');
  // return;
  // }

  param =
    "no_trans=" +
    notrans +
    "&kode_karyawan=" +
    kdKry +
    "&jenisvhc=" +
    jenisvhc +
    "&proses_pekerjaan=" +
    proskerja +
    "&prmiOprt_old=" +
    prmiOprtold +
    "&prmiHelp_old=" +
    prmiHelpold +
    "&prmiHelp_old2=" +
    prmiHelpold2 +
    "&prmiHelp_old3=" +
    prmiHelpold3;
  param +=
    "&proses=insert_operator" +
    "&pnltyOprt=" +
    pnltyOprt +
    "&prmiOprt=" +
    prmiOprt +
    "&prmiHelp=" +
    prmiHelp +
    "&prmiHelp2=" +
    prmiHelp2 +
    "&prmiHelp3=" +
    prmiHelp3 +
    "&uphOprt=" +
    uphoprt +
    "&uphHelp=" +
    uphhelp +
    "&uphHelp2=" +
    uphhelp2 +
    "&uphHelp3=" +
    uphhelp3 +
    "&tglpekerjaan=" +
    tglTrans +
    "&ketOprt=" +
    ketOprt +
    "&jns_kerja=" +
    jns_kerja +
    "&blok=" +
    blok +
    "&kode_helper=" +
    kode_helper +
    "&kode_helper2=" +
    kode_helper2 +
    "&kode_helper3=" +
    kode_helper3 +
    "&oldbrt_muatan=" +
    olbrtrmuatan +
    "&lokasikerja=" +
    locationKerj +
    "&jnsstn=" +
    jnsstn +
    "&jnsstnhelp=" +
    jnsstnhelp +
    "&basisborong=" +
    basisborong +
    "&basisboronghelp=" +
    basisborongh +
    "&kmhm_awal=" +
    kmhmAwal +
    "&idkaryawanlama=" +
    karyold +
    "&prmiOprtTambahan=" +
    prmiOprtTambahan;
  tujuan = "vhc_slave_pekerjaan_v2.php";

  post_response_text(tujuan, param, respog);
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          bersih_form_pekerjaan();
          if (
            document.getElementById("proses_pekerjaan").value != "update_kerja"
          ) {
            document.getElementById("kmhm_awal").value = await getKmAkhir();
          }
          loaddetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingdata(notrans, kdvhc, tgl, page) {
  param = "proses=postingdata";
  param += "&no_trans=" + notrans + "&kdVhc=" + kdvhc + "&tgl=" + tgl;
  tujuan = "vhc_slave_pekerjaan_v2.php";
  if (
    confirm(
      "Apakah anda yakin ingin memposting data dengan notransaksi = " +
        notrans +
        "?",
    )
  ) {
    post_response_text(tujuan, param, respog);
  } else {
    return;
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddata(page);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
