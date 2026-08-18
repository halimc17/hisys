function caridata() {
  method = "loadData";
  nmCar = document.getElementById("nmCar").value;
  BlnCr = document.getElementById("BlnCr").value;
  jnsSkCar =
    document.getElementById("jnsSkCar").options[
      document.getElementById("jnsSkCar").selectedIndex
    ].value;
  param =
    "nmCar=" +
    nmCar +
    "&BlnCr=" +
    BlnCr +
    "&jnsSkCar=" +
    jnsSkCar +
    "&method=" +
    method;
  tujuan = "sdm_slave_pesangon.php";

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("container").innerHTML = con.responseText;
          batal();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respog);
}

function formajukan(noid) {
  param = "method=form_ajukan";
  param += "&noid=" + noid;
  tujuan = "sdm_slave_pesangon.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.popup().destroy();
          alertify
            .popup("Ajukan ?", "<center>" + con.responseText + "</center>")
            .set({ resizable: true, maximizable: false })
            .resizeTo("300px", "230px");
          $(document).ready(function () {
            $(".select2").select2({
              dropdownAutoWidth: false,
            });
            $(".select2-selection--single").height(30).css({
              cursor: "auto",
            });
            $(".select2-selection__arrow b").css({
              top: "70%",
            });
            $(".select2-selection__rendered").css({
              "line-height": "31px",
            });
          });
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getkodeunit(karyawanid) {
  param = "method=getkodeunit" + "&karyawanid=" + karyawanid;
  tujuan = "sdm_slave_pesangon.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          pisah = con.responseText.split("###");
          // console.log(pisah[2]);
          document.getElementById("kodeunit").value = pisah[0];
          document.getElementById("tglmasuk").value = pisah[1];
          document.getElementById("gajipokok").value = pisah[2];
          // getmasakerja();
          document.getElementById("tglberhenti").value = "";
          document.getElementById("masakerjatahun").value = "";
          document.getElementById("masakerjabulan").value = "";
          document.getElementById("masakerjahari").value = "";
          // document.getElementById('gajipokok').value          = '0';
          // document.getElementById('tunjanganjabatan').value   = '0';
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function hitungtotalcuti() {
  cutitahunan = document.getElementById("cutitahunan").value;
  pembagigajicuti = document.getElementById("pembagigajicuti").value;
  gajicuti = document.getElementById("gajicuti").value;
  rupiahcutitahunan = document
    .getElementById("rupiahcutitahunan")
    .value.replace(/,/g, "");
  rupiahtotal1 = document
    .getElementById("rupiahtotal1")
    .value.replace(/,/g, "");
  rupiahditerima = document
    .getElementById("rupiahditerima")
    .value.replace(/,/g, "");

  rupahcutix =
    (parseFloat(gajicuti) * parseFloat(cutitahunan)) /
    parseFloat(pembagigajicuti);
  rupiahtotal1 =
    parseFloat(rupiahtotal1) - parseFloat(rupiahcutitahunan) + rupahcutix;
  rupiahditerima =
    parseFloat(rupiahditerima) - parseFloat(rupiahcutitahunan) + rupahcutix;
  document.getElementById("rupiahcutitahunan").value = numberFormat(
    rupahcutix,
    0
  );
  document.getElementById("rupiahtotal1").value = numberFormat(rupiahtotal1, 0);
  document.getElementById("rupiahditerima").value =
    numberFormat(rupiahditerima);
}

function hitungtotalkesehatan() {
  proporsikesehatan = document.getElementById("proporsikesehatan").value;
  pengalikesehatan = document.getElementById("pengalikesehatan").value;
  gajikesehatan = document.getElementById("gajikesehatan").value;
  rupiahkesehatan = document
    .getElementById("rupiahkesehatan")
    .value.replace(/,/g, "");
  rupiahtotal1 = document
    .getElementById("rupiahtotal1")
    .value.replace(/,/g, "");
  rupiahditerima = document
    .getElementById("rupiahditerima")
    .value.replace(/,/g, "");

  rupiahx =
    ((parseFloat(gajikesehatan) * parseFloat(proporsikesehatan)) / 12) *
    parseFloat(pengalikesehatan);
  rupiahtotal1 =
    parseFloat(rupiahtotal1) - parseFloat(rupiahkesehatan) + rupiahx;
  rupiahditerima =
    parseFloat(rupiahditerima) - parseFloat(rupiahkesehatan) + rupiahx;
  document.getElementById("rupiahkesehatan").value = numberFormat(rupiahx, 0);
  document.getElementById("rupiahtotal1").value = numberFormat(rupiahtotal1, 0);
  document.getElementById("rupiahditerima").value =
    numberFormat(rupiahditerima);
}

function hitungtotalnilaitambahan() {
  rupiahcutitahunan = document
    .getElementById("rupiahcutitahunan")
    .value.replace(/,/g, "");
  rupiahkesehatan = document
    .getElementById("rupiahkesehatan")
    .value.replace(/,/g, "");
  nilaitambahan1 = document
    .getElementById("nilaitambahan1")
    .value.replace(/,/g, "");
  nilaitambahan2 = document
    .getElementById("nilaitambahan2")
    .value.replace(/,/g, "");
  nilaitambahan3 = document
    .getElementById("nilaitambahan3")
    .value.replace(/,/g, "");
  nilaitambahan4 = document
    .getElementById("nilaitambahan4")
    .value.replace(/,/g, "");

  nilaijenispengembalian1 = document
    .getElementById("nilaijenispengembalian1")
    .value.replace(/,/g, "");
  nilaijenispengembalian2 = document
    .getElementById("nilaijenispengembalian2")
    .value.replace(/,/g, "");
  nilaijenispengembalian3 = document
    .getElementById("nilaijenispengembalian3")
    .value.replace(/,/g, "");
  nilaijenispengembalian4 = document
    .getElementById("nilaijenispengembalian4")
    .value.replace(/,/g, "");
  nilaijenispengembalian4 = document
    .getElementById("nilaijenispengembalian5")
    .value.replace(/,/g, "");
  nilaijenispengembalian4 = document
    .getElementById("nilaijenispengembalian6")
    .value.replace(/,/g, "");

  if (nilaijenispengembalian1 == "") {
    nilaijenispengembalian1 = 0;
  }
  if (nilaijenispengembalian2 == "") {
    nilaijenispengembalian2 = 0;
  }
  if (nilaijenispengembalian3 == "") {
    nilaijenispengembalian3 = 0;
  }
  if (nilaijenispengembalian4 == "") {
    nilaijenispengembalian4 = 0;
  }

  if (nilaitambahan1 == "") {
    nilaitambahan1 = 0;
  }
  if (nilaitambahan2 == "") {
    nilaitambahan2 = 0;
  }
  if (nilaitambahan3 == "") {
    nilaitambahan3 = 0;
  }
  if (nilaitambahan4 == "") {
    nilaitambahan4 = 0;
  }

  nilaipajak = document.getElementById("nilaipajak").value.replace(/,/g, "");

  rupiahtotal1 =
    parseFloat(rupiahcutitahunan) +
    parseFloat(rupiahkesehatan) +
    parseFloat(nilaitambahan1) +
    parseFloat(nilaitambahan2) +
    parseFloat(nilaitambahan3) +
    parseFloat(nilaitambahan4);
  rupiahtotalpotongan =
    parseFloat(nilaipajak) +
    parseFloat(nilaijenispengembalian1) +
    parseFloat(nilaijenispengembalian2) +
    parseFloat(nilaijenispengembalian3) +
    parseFloat(nilaijenispengembalian4) +
    parseFloat(nilaijenispengembalian5) +
    parseFloat(nilaijenispengembalian6);
  rupiahditerima = parseFloat(rupiahtotal1) - parseFloat(rupiahtotalpotongan);
  document.getElementById("rupiahtotal1").value = numberFormat(rupiahtotal1, 0);
  document.getElementById("rupiahditerima").value =
    numberFormat(rupiahditerima);
}

function hitungtotaljenispengembalian() {
  nilaijenispengembalian1 = document
    .getElementById("nilaijenispengembalian1")
    .value.replace(/,/g, "");
  nilaijenispengembalian2 = document
    .getElementById("nilaijenispengembalian2")
    .value.replace(/,/g, "");
  nilaijenispengembalian3 = document
    .getElementById("nilaijenispengembalian3")
    .value.replace(/,/g, "");
  nilaijenispengembalian4 = document
    .getElementById("nilaijenispengembalian4")
    .value.replace(/,/g, "");
  nilaijenispengembalian5 = document
    .getElementById("nilaijenispengembalian5")
    .value.replace(/,/g, "");
  nilaijenispengembalian6 = document
    .getElementById("nilaijenispengembalian6")
    .value.replace(/,/g, "");
  if (nilaijenispengembalian1 == "") {
    nilaijenispengembalian1 = 0;
  }
  if (nilaijenispengembalian2 == "") {
    nilaijenispengembalian2 = 0;
  }
  if (nilaijenispengembalian3 == "") {
    nilaijenispengembalian3 = 0;
  }
  if (nilaijenispengembalian4 == "") {
    nilaijenispengembalian4 = 0;
  }
  if (nilaijenispengembalian5 == "") {
    nilaijenispengembalian5 = 0;
  }
  if (nilaijenispengembalian6 == "") {
    nilaijenispengembalian6 = 0;
  }
  //alert(nilaijenispengembalian1+'='+nilaijenispengembalian2+'='+nilaijenispengembalian3+'='+nilaijenispengembalian4);
  nilaipajak = document.getElementById("nilaipajak").value.replace(/,/g, "");
  rupiahditerima = document
    .getElementById("rupiahditerima")
    .value.replace(/,/g, "");
  rupiahtotal1 = document
    .getElementById("rupiahtotal1")
    .value.replace(/,/g, "");
  rupiahtotalpotongan =
    parseFloat(nilaipajak) +
    parseFloat(nilaijenispengembalian1) +
    parseFloat(nilaijenispengembalian2) +
    parseFloat(nilaijenispengembalian3) +
    parseFloat(nilaijenispengembalian4) +
    parseFloat(nilaijenispengembalian5) +
    parseFloat(nilaijenispengembalian6);
  rupiahditerima = parseFloat(rupiahtotal1) - parseFloat(rupiahtotalpotongan);
  document.getElementById("rupiahtotalpotongan").value = numberFormat(
    rupiahtotalpotongan,
    0
  );
  document.getElementById("rupiahditerima").value =
    numberFormat(rupiahditerima);
}

function getmasakerja(tglberhenti) {
  tglmasuk = document.getElementById("tglmasuk").value;
  tglberhenti = document.getElementById("tglberhenti").value;
  param =
    "method=getmasakerja" +
    "&tglberhenti=" +
    tglberhenti +
    "&tglmasuk=" +
    tglmasuk;
  tujuan = "sdm_slave_pesangon.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          pisah = con.responseText.split("###");
          document.getElementById("masakerjatahun").value = pisah[0];
          document.getElementById("masakerjabulan").value = pisah[1];
          document.getElementById("masakerjahari").value = pisah[2];
          getgapoktunjangan();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getgapoktunjangan() {
  tglberhenti = document.getElementById("tglberhenti").value;
  karyawanid = document.getElementById("karyawanid").value;
  param =
    "method=getgapoktunjangan" +
    "&karyawanid=" +
    karyawanid +
    "&tglberhenti=" +
    tglberhenti;
  tujuan = "sdm_slave_pesangon.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("gajipokok").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getdetail() {
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  gajipokok = document.getElementById("gajipokok").value;
  gajipokok = remove_comma(document.getElementById("gajipokok"));
  masakerjatahun = document.getElementById("masakerjatahun").value;
  masakerjabulan = document.getElementById("masakerjabulan").value;
  masakerjahari = document.getElementById("masakerjahari").value;
  tglberhenti = document.getElementById("tglberhenti").value;
  jenis = document.getElementById("jenis").value;

  param = "method=createTable&jenis=" + jenis;

  param += "&masakerjatahun=" + getValue("masakerjatahun");
  param += "&masakerjabulan=" + getValue("masakerjabulan");
  param += "&masakerjahari=" + getValue("masakerjahari");
  param += "&tglmasuk=" + getValue("tglmasuk");

  param +=
    "&gajipokok=" +
    gajipokok +
    "&karyawanid=" +
    karyawanid +
    "&tglberhenti=" +
    tglberhenti;

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          var detailDiv = document.getElementById("detailTable");
          detailDiv.innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text("sdm_slave_pesangon.php", param, respon);
}

function getdetail() {
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  gajipokok = document.getElementById("gajipokok").value;
  gajipokok = remove_comma(document.getElementById("gajipokok"));
  masakerjatahun = document.getElementById("masakerjatahun").value;
  masakerjabulan = document.getElementById("masakerjabulan").value;
  masakerjahari = document.getElementById("masakerjahari").value;
  tglberhenti = document.getElementById("tglberhenti").value;
  jenis = document.getElementById("jenis").value;

  param = "method=insert";

  param = "method=createTable&jenis=" + jenis;

  param += "&masakerjatahun=" + getValue("masakerjatahun");
  param += "&masakerjabulan=" + getValue("masakerjabulan");
  param += "&masakerjahari=" + getValue("masakerjahari");
  param += "&tglmasuk=" + getValue("tglmasuk");

  param +=
    "&gajipokok=" +
    gajipokok +
    "&karyawanid=" +
    karyawanid +
    "&tglberhenti=" +
    tglberhenti;

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          var detailDiv = document.getElementById("detailTable");
          detailDiv.innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text("sdm_slave_pesangon.php", param, respon);
}

function calculateUangPisah() {
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  gajipokok = remove_comma(document.getElementById("gajipokok"));
  HKK = document.getElementById("HKK").innerHTML;
  tunjanganjabatan = remove_comma(document.getElementById("tunjanganjabatan"));
  p1564a = remove_comma(document.getElementById("p1564a"));
  p1564b = remove_comma(document.getElementById("p1564b"));
  banyaknya = remove_comma(document.getElementById("banyaknya"));
  tot_uangpisah = parseFloat(banyaknya) * parseFloat(gajipokok);
  gaji = parseFloat(gajipokok) + parseFloat(tunjanganjabatan);
  jmlh_p1564a = 0;
  if (parseInt(HKK) != 0) {
    //    jmlh_p1564a =parseFloat(gaji)*(parseFloat(p1564a)/parseInt(HKK));
    jmlh_p1564a = HKK * (parseFloat(gajipokok) / parseInt(25));
  }

  document.getElementById("tot_uangpisah").value = tot_uangpisah;
  change_number(document.getElementById("tot_uangpisah"));

  document.getElementById("jmlh_p1564a").value = jmlh_p1564a;
  change_number(document.getElementById("jmlh_p1564a"));

  ongkospulang = parseFloat(gaji) * parseFloat(p1564b);
  document.getElementById("jmlh_p1564b").value = ongkospulang;
  change_number(document.getElementById("jmlh_p1564b"));

  ttl =
    parseFloat(jmlh_p1564a) +
    parseFloat(ongkospulang) +
    parseFloat(tot_uangpisah);
  document.getElementById("tot_sblm_pajak").value = ttl;
  change_number(document.getElementById("tot_sblm_pajak"));

  totsblmpajak = ttl;
  param = "&karyawanid=" + karyawanid + "&totsblmpajak=" + totsblmpajak;
  //    alert(param);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          pisah = con.responseText.split("###");
          document.getElementById("pajakprogresif1").value = pisah[0];
          document.getElementById("pajakprogresif2").value = pisah[1];
          document.getElementById("pajakprogresif3").value = pisah[2];
          document.getElementById("tot_pajak").value = pisah[3];
          document.getElementById("tot_pesangon").value = pisah[4];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  // post_response_text('sdm_slave_pesangon_progresif.php', param, respon);
}

function calculatePesangon() {
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  gajipokok = remove_comma(document.getElementById("gajipokok"));
  HKK = document.getElementById("HKK").innerHTML;
  tunjanganjabatan = remove_comma(document.getElementById("tunjanganjabatan"));
  p1562 = remove_comma(document.getElementById("p1562"));
  p1563 = remove_comma(document.getElementById("p1563"));
  p1564a = remove_comma(document.getElementById("p1564a"));
  p1564b = remove_comma(document.getElementById("p1564b"));
  p1564c = remove_comma(document.getElementById("p1564c"));

  jml_pesangon = remove_comma(document.getElementById("jml_pesangon"));
  tot_penghargaan = remove_comma(document.getElementById("tot_penghargaan"));
  gaji = parseFloat(gajipokok) + parseFloat(tunjanganjabatan);

  // alert(HKK);
  jmlh_p1562 = parseFloat(gaji) * parseFloat(p1562);
  document.getElementById("jml_pesangon").value = jmlh_p1562;
  change_number(document.getElementById("jml_pesangon"));

  jmlh_p1563 = parseFloat(gaji) * parseFloat(p1563);
  document.getElementById("tot_penghargaan").value = jmlh_p1563;
  change_number(document.getElementById("tot_penghargaan"));
  jmlh_p1564a = 0;
  if (parseInt(HKK) != 0) {
    //    jmlh_p1564a =parseFloat(gaji)*(parseFloat(p1564a)/parseInt(HKK));
    jmlh_p1564a = HKK * (parseFloat(gajipokok) / parseInt(25));
  }

  document.getElementById("jmlh_p1564a").value = jmlh_p1564a;
  change_number(document.getElementById("jmlh_p1564a"));

  jmlh_p1564b = parseFloat(gaji) * parseFloat(p1564b);
  document.getElementById("jmlh_p1564b").value = jmlh_p1564b;
  change_number(document.getElementById("jmlh_p1564b"));

  jmlh_p1564c =
    (parseFloat(jml_pesangon) + parseFloat(tot_penghargaan)) *
    parseFloat(p1564c);
  document.getElementById("jmlh_p1564c").value = jmlh_p1564c;
  change_number(document.getElementById("jmlh_p1564c"));

  ttl =
    parseFloat(remove_comma(document.getElementById("jml_pesangon"))) +
    parseFloat(remove_comma(document.getElementById("tot_penghargaan"))) +
    parseFloat(jmlh_p1564a) +
    parseFloat(jmlh_p1564b) +
    parseFloat(jmlh_p1564c);
  document.getElementById("tot_sblm_pajak").value = ttl;
  change_number(document.getElementById("tot_sblm_pajak"));

  totsblmpajak = ttl;
  param = "&karyawanid=" + karyawanid + "&totsblmpajak=" + totsblmpajak;
  //    alert(jmlh_p1564a);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          pisah = con.responseText.split("###");
          document.getElementById("pajakprogresif1_").value = pisah[0];
          document.getElementById("pajakprogresif2_").value = pisah[1];
          document.getElementById("pajakprogresif3_").value = pisah[2];
          document.getElementById("tot_pajak_").value = pisah[3];
          document.getElementById("tot_pesangon").value = pisah[4];
          //                    alert(pisah[1]);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  // post_response_text('sdm_slave_pesangon_progresif.php', param, respon);
}

function savePesangon() {
  nosurat = document.getElementById("nosurat").value;
  tanggal = document.getElementById("tanggal").value;
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  pihakpertama =
    document.getElementById("pihakpertama").options[
      document.getElementById("pihakpertama").selectedIndex
    ].value;
  kodeunit = document.getElementById("kodeunit").value;
  tglberhenti = document.getElementById("tglberhenti").value;
  masakerjatahun = document.getElementById("masakerjatahun").value;
  masakerjabulan = document.getElementById("masakerjabulan").value;
  masakerjahari = document.getElementById("masakerjahari").value;
  gajipokok = document.getElementById("gajipokok").value;
  gajipokok = remove_comma_var(gajipokok);
  tunjanganjabatan = document.getElementById("tunjanganjabatan").value;
  tunjanganjabatan = remove_comma_var(tunjanganjabatan);
  jenissk =
    document.getElementById("jenissk").options[
      document.getElementById("jenissk").selectedIndex
    ].value;

  tot_uangpisah = document.getElementById("tot_uangpisah").value;
  tot_uangpisah = remove_comma_var(tot_uangpisah);
  p1564a = document.getElementById("p1564a").value;
  p1564a = remove_comma_var(p1564a);
  jmlh_p1564a = document.getElementById("jmlh_p1564a").value;
  jmlh_p1564a = remove_comma_var(jmlh_p1564a);
  p1564b = document.getElementById("p1564b").value;
  p1564b = remove_comma_var(p1564b);
  jmlh_p1564b = document.getElementById("jmlh_p1564b").value;
  jmlh_p1564b = remove_comma_var(jmlh_p1564b);
  tot_sblm_pajak = document.getElementById("tot_sblm_pajak").value;
  tot_sblm_pajak = remove_comma_var(tot_sblm_pajak);
  pajakprogresif1 = document.getElementById("pajakprogresif1").value;
  pajakprogresif2 = document.getElementById("pajakprogresif2").value;
  pajakprogresif3 = document.getElementById("pajakprogresif3").value;
  tot_pajak = document.getElementById("tot_pajak").value;
  tot_pesangon = document.getElementById("tot_pesangon").value;
  tot_pesangon = remove_comma_var(tot_pesangon);

  param =
    "nosurat=" +
    nosurat +
    "&tanggal=" +
    tanggal +
    "&karyawanid=" +
    karyawanid +
    "&pihakpertama=" +
    pihakpertama +
    "&kodeunit=" +
    kodeunit +
    "&tglberhenti=" +
    tglberhenti;
  param +=
    "&masakerjatahun=" +
    masakerjatahun +
    "&masakerjabulan=" +
    masakerjabulan +
    "&masakerjahari=" +
    masakerjahari +
    "&gajipokok=" +
    gajipokok +
    "&tunjanganjabatan=" +
    tunjanganjabatan;
  param +=
    "&jenissk=" +
    jenissk +
    "&tot_uangpisah=" +
    tot_uangpisah +
    "&p1564a=" +
    p1564a +
    "&jmlh_p1564a=" +
    jmlh_p1564a +
    "&p1564b=" +
    p1564b +
    "&jmlh_p1564b=" +
    jmlh_p1564b;
  param +=
    "&tot_sblm_pajak=" +
    tot_sblm_pajak +
    "&pajakprogresif1=" +
    pajakprogresif1 +
    "&pajakprogresif2=" +
    pajakprogresif2 +
    "&pajakprogresif3=" +
    pajakprogresif3 +
    "&tot_pajak=" +
    tot_pajak +
    "&tot_pesangon=" +
    tot_pesangon +
    "&method=insert";

  tujuan = "sdm_slave_pesangon.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Done.");
          loadData(0);
          document.getElementById("nosurat").value = "";
          document.getElementById("nosurat").disabled = false;
          document.getElementById("tanggal").value = "";
          document.getElementById("karyawanid").value = "";
          document.getElementById("kodeunit").value = "";
          document.getElementById("tglberhenti").value = "";
          document.getElementById("masakerjatahun").value = "";
          document.getElementById("masakerjabulan").value = "";
          document.getElementById("masakerjahari").value = "";
          document.getElementById("gajipokok").value = "";
          document.getElementById("tunjanganjabatan").value = "";
          document.getElementById("jenissk").value = "";
          document.getElementById("tot_uangpisah").value = "";
          document.getElementById("p1564a").value = "";
          document.getElementById("jmlh_p1564a").value = "";
          document.getElementById("p1564b").value = "";
          document.getElementById("jmlh_p1564b").value = "";
          document.getElementById("tot_sblm_pajak").value = "";
          document.getElementById("pajakprogresif1").value = "";
          document.getElementById("pajakprogresif2").value = "";
          document.getElementById("pajakprogresif3").value = "";
          document.getElementById("tot_pajak").value = "";
          document.getElementById("tot_pesangon").value = "";
          document.getElementById("banyaknya").value = "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function savePesangon2() {
  nosurat = document.getElementById("nosurat").value;
  tanggal = document.getElementById("tanggal").value;
  karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  pihakpertama =
    document.getElementById("pihakpertama").options[
      document.getElementById("pihakpertama").selectedIndex
    ].value;
  kodeunit = document.getElementById("kodeunit").value;
  tglberhenti = document.getElementById("tglberhenti").value;
  masakerjatahun = document.getElementById("masakerjatahun").value;
  masakerjabulan = document.getElementById("masakerjabulan").value;
  masakerjahari = document.getElementById("masakerjahari").value;
  gajipokok = document.getElementById("gajipokok").value;
  gajipokok = remove_comma_var(gajipokok);
  tunjanganjabatan = document.getElementById("tunjanganjabatan").value;
  tunjanganjabatan = remove_comma_var(tunjanganjabatan);
  jenissk =
    document.getElementById("jenissk").options[
      document.getElementById("jenissk").selectedIndex
    ].value;

  p1562 = document.getElementById("p1562").value;
  jml_pesangon = document.getElementById("jml_pesangon").value;
  jml_pesangon = remove_comma_var(jml_pesangon);
  p1563 = document.getElementById("p1563").value;
  tot_penghargaan = document.getElementById("tot_penghargaan").value;
  tot_penghargaan = remove_comma_var(tot_penghargaan);
  p1564a = document.getElementById("p1564a").value;
  jmlh_p1564a = document.getElementById("jmlh_p1564a").value;
  jmlh_p1564a = remove_comma_var(jmlh_p1564a);
  p1564b = document.getElementById("p1564b").value;
  jmlh_p1564b = document.getElementById("jmlh_p1564b").value;
  jmlh_p1564b = remove_comma_var(jmlh_p1564b);
  p1564c = document.getElementById("p1564c").value;
  jmlh_p1564c = document.getElementById("jmlh_p1564c").value;
  jmlh_p1564c = remove_comma_var(jmlh_p1564c);
  tot_sblm_pajak = document.getElementById("tot_sblm_pajak").value;
  tot_sblm_pajak = remove_comma_var(tot_sblm_pajak);
  pajakprogresif1 = document.getElementById("pajakprogresif1_").value;
  pajakprogresif2 = document.getElementById("pajakprogresif2_").value;
  pajakprogresif3 = document.getElementById("pajakprogresif3_").value;
  tot_pajak_ = document.getElementById("tot_pajak_").value;
  tot_pajak_ = remove_comma_var(tot_pajak_);
  tot_pesangon = document.getElementById("tot_pesangon").value;
  tot_pesangon = remove_comma_var(tot_pesangon);
  param =
    "nosurat=" +
    nosurat +
    "&tanggal=" +
    tanggal +
    "&karyawanid=" +
    karyawanid +
    "&pihakpertama=" +
    pihakpertama +
    "&kodeunit=" +
    kodeunit +
    "&tglberhenti=" +
    tglberhenti;
  param +=
    "&masakerjatahun=" +
    masakerjatahun +
    "&masakerjabulan=" +
    masakerjabulan +
    "&masakerjahari=" +
    masakerjahari +
    "&gajipokok=" +
    gajipokok +
    "&tunjanganjabatan=" +
    tunjanganjabatan;
  param +=
    "&jenissk=" +
    jenissk +
    "&p1562=" +
    p1562 +
    "&jml_pesangon=" +
    jml_pesangon +
    "&p1563=" +
    p1563 +
    "&tot_penghargaan=" +
    tot_penghargaan +
    "&p1564a=" +
    p1564a;
  param +=
    "&jmlh_p1564a=" +
    jmlh_p1564a +
    "&p1564b=" +
    p1564b +
    "&jmlh_p1564b=" +
    jmlh_p1564b +
    "&p1564c=" +
    p1564c +
    "&jmlh_p1564c=" +
    jmlh_p1564c +
    "&tot_sblm_pajak=" +
    tot_sblm_pajak;
  param +=
    "&pajakprogresif1_=" +
    pajakprogresif1_ +
    "&pajakprogresif2_=" +
    pajakprogresif2_ +
    "&pajakprogresif3_=" +
    pajakprogresif3_ +
    "&tot_pajak_=" +
    tot_pajak_ +
    "&tot_pesangon=" +
    tot_pesangon +
    "&method=insert2";
  //    alert(param);
  tujuan = "sdm_slave_pesangon.php";

  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alert("Done.");
          loadData(0);
          document.getElementById("nosurat").value = "";
          //document.getElementById('nosurat').disabled=false
          document.getElementById("tanggal").value = "";
          document.getElementById("karyawanid").value = "";
          document.getElementById("pihakpertama").value = "";
          document.getElementById("kodeunit").value = "";
          document.getElementById("tglberhenti").value = "";
          document.getElementById("masakerjatahun").value = "";
          document.getElementById("masakerjabulan").value = "";
          document.getElementById("masakerjahari").value = "";
          document.getElementById("gajipokok").value = "";
          document.getElementById("tunjanganjabatan").value = "";
          document.getElementById("jenissk").value = "";
          document.getElementById("p1562").value = "";
          document.getElementById("jml_pesangon").value = "";
          document.getElementById("p1563").value = "";
          document.getElementById("tot_penghargaan").value = "";
          document.getElementById("p1564a").value = "";
          document.getElementById("jmlh_p1564a").value = "";
          document.getElementById("p1564b").value = "";
          document.getElementById("jmlh_p1564b").value = "";
          document.getElementById("p1564c").value = "";
          document.getElementById("jmlh_p1564c").value = "";
          document.getElementById("tot_sblm_pajak").value = "";
          document.getElementById("pajakprogresif1_").value = "";
          document.getElementById("pajakprogresif2_").value = "";
          document.getElementById("pajakprogresif3_").value = "";
          document.getElementById("tot_pajak_").value = "";
          document.getElementById("tot_pesangon").value = "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancelIsi(passParam) {
  document.getElementById("nosurat").value = "";
  //document.getElementById('nosurat').disabled=false
  document.getElementById("tanggal").value = "";
  document.getElementById("karyawanid").value = "";
  document.getElementById("pihakpertama").value = "";
  document.getElementById("kodeunit").value = "";
  document.getElementById("tglberhenti").value = "";
  document.getElementById("masakerjatahun").value = "";
  document.getElementById("masakerjabulan").value = "";
  document.getElementById("masakerjahari").value = "";
  document.getElementById("gajipokok").value = "";
  document.getElementById("tunjanganjabatan").value = "";
  document.getElementById("jenissk").value = "";
  document.getElementById("tot_uangpisah").value = "";
  document.getElementById("p1564a").value = "";
  document.getElementById("jmlh_p1564a").value = "";
  document.getElementById("p1564b").value = "";
  document.getElementById("jmlh_p1564b").value = "";
  document.getElementById("tot_sblm_pajak").value = "";
  document.getElementById("pajakprogresif1").value = "";
  document.getElementById("pajakprogresif2").value = "";
  document.getElementById("pajakprogresif3").value = "";
  document.getElementById("tot_pajak").value = "";
  document.getElementById("tot_pesangon").value = "";
  document.getElementById("banyaknya").value = "";
}
function cancelIsi2(passParam) {
  document.getElementById("nosurat").value = "";
  //document.getElementById('nosurat').disabled=false
  document.getElementById("tanggal").value = "";
  document.getElementById("karyawanid").value = "";
  document.getElementById("pihakpertama").value = "";
  document.getElementById("kodeunit").value = "";
  document.getElementById("tglberhenti").value = "";
  document.getElementById("masakerjatahun").value = "";
  document.getElementById("masakerjabulan").value = "";
  document.getElementById("masakerjahari").value = "";
  document.getElementById("gajipokok").value = "";
  document.getElementById("tunjanganjabatan").value = "";
  document.getElementById("jenissk").value = "";
  document.getElementById("p1562").value = "";
  document.getElementById("jml_pesangon").value = "";
  document.getElementById("p1563").value = "";
  document.getElementById("tot_penghargaan").value = "";
  document.getElementById("p1564a").value = "";
  document.getElementById("jmlh_p1564a").value = "";
  document.getElementById("p1564b").value = "";
  document.getElementById("jmlh_p1564b").value = "";
  document.getElementById("p1564c").value = "";
  document.getElementById("jmlh_p1564c").value = "";
  document.getElementById("tot_sblm_pajak").value = "";
  document.getElementById("pajakprogresif1_").value = "";
  document.getElementById("pajakprogresif2_").value = "";
  document.getElementById("pajakprogresif3_").value = "";
  document.getElementById("tot_pajak_").value = "";
  document.getElementById("tot_pesangon").value = "";
}

function loadData(num) {
  nmcr = document.getElementById("nmCar").value;
  jns = document.getElementById("jnsSkCar");
  jns = jns.options[jns.selectedIndex].value;
  BlnCr = document.getElementById("BlnCr");
  BlnCr = BlnCr.options[BlnCr.selectedIndex].value;
  param =
    "method=loadData" +
    "&nmCar=" +
    nmcr +
    "&jnsSkCar=" +
    jns +
    "&BlnCr=" +
    BlnCr;
  param += "&page=" + num;
  tujuan = "sdm_slave_pesangon.php";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("isi").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function gedit(
  noid,
  tanggal,
  karyawanid,
  kodeunit,
  tglmasuk,
  tglberhenti,
  masakerjatahun,
  masakerjabulan,
  masakerjahari,
  gajipokok,
  jenis
) {
  document.getElementById("tanggal").value = tanggal;
  document.getElementById("karyawanid").value = karyawanid;
  document.getElementById("karyawanid").disabled = true;
  document.getElementById("kodeunit").value = kodeunit;
  document.getElementById("tglmasuk").value = tglmasuk;
  document.getElementById("tglberhenti").value = tglberhenti;
  document.getElementById("masakerjatahun").value = masakerjatahun;
  document.getElementById("masakerjabulan").value = masakerjabulan;
  document.getElementById("masakerjahari").value = masakerjahari;
  document.getElementById("gajipokok").value = gajipokok;
  document.getElementById("jenis").value = jenis;
  document.getElementById("noid").value = noid;

  param = "method=editTable&jenis=" + jenis;

  param += "&masakerjatahun=" + masakerjatahun;
  param += "&masakerjabulan=" + masakerjabulan;
  param += "&masakerjahari=" + masakerjahari;
  param += "&tglmasuk=" + tglmasuk;
  param += "&noid=" + noid;

  param +=
    "&gajipokok=" +
    gajipokok +
    "&karyawanid=" +
    karyawanid +
    "&tglberhenti=" +
    tglberhenti;

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // Success Response
          var detailDiv = document.getElementById("detailTable");
          detailDiv.innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text("sdm_slave_pesangon.php", param, respon);
}

function edit(
  nosurat,
  tanggal,
  karyawanid,
  kodeunit,
  tglmasuk,
  tglberhenti,
  masakerjatahun,
  masakerjabulan,
  masakerjahari,
  gajipokok,
  tunjanganjabatan,
  jenissk,
  p1562,
  jml_pesangon,
  p1563,
  tot_penghargaan,
  p1564a,
  jmlh_p1564a,
  p1564b,
  jmlh_p1564b,
  p1564c,
  jmlh_p1564c,
  tot_sblm_pajak,
  tot_pesangon,
  pihakpertama,
  hasilKaliGaji1,
  hasilKaliGaji2,
  jenisphk,
  judulphk,
  xpp
) {
  document.getElementById("nosurat").value = nosurat;
  document.getElementById("nosurat").disabled = true;
  document.getElementById("tanggal").value = tanggal;
  document.getElementById("karyawanid").value = karyawanid;
  document.getElementById("karyawanid").disabled = true;
  //alert(pihakpertama);
  document.getElementById("pihakpertama").value = pihakpertama;
  document.getElementById("kodeunit").value = kodeunit;
  document.getElementById("tglberhenti").value = tglberhenti;
  document.getElementById("masakerjatahun").value = masakerjatahun;
  document.getElementById("masakerjabulan").value = masakerjabulan;
  document.getElementById("masakerjahari").value = masakerjahari;
  document.getElementById("gajipokok").value = gajipokok;
  document.getElementById("tunjanganjabatan").value = tunjanganjabatan;
  document.getElementById("jenissk").value = jenissk.replace(/%20/g, " ");
  document.getElementById("jenissk").disabled = true;
  document.getElementById("jenisphk").value = jenisphk;
  document.getElementById("judulphk").value = judulphk;

  document.getElementById("p1564a").value = p1564a;
  document.getElementById("jmlh_p1564a").value = jmlh_p1564a;
  document.getElementById("p1564b").value = p1564b;
  document.getElementById("jmlh_p1564b").value = jmlh_p1564b;

  if (jenissk.replace(/%20/g, " ") != "Uang Pisah") {
    document.getElementById("p1562").value = p1562;
    document.getElementById("jml_pesangon").value = jml_pesangon;
    document.getElementById("p1563").value = p1563;
    document.getElementById("tot_penghargaan").value = tot_penghargaan;
    document.getElementById("p1564c").value = p1564c;
    document.getElementById("jmlh_p1564c").value = jmlh_p1564c;
  }

  document.getElementById("tot_sblm_pajak").value = tot_sblm_pajak;
  document.getElementById("tot_pesangon").value = tot_pesangon;
  document.getElementById("method").value = "updateNew";

  param =
    "nosurat=" +
    nosurat +
    "&tanggal=" +
    tanggal +
    "&karyawanid=" +
    karyawanid +
    "&pihakpertama=" +
    pihakpertama +
    "&kodeunit=" +
    kodeunit +
    "&tglberhenti=" +
    tglberhenti;
  param +=
    "&masakerjatahun=" +
    masakerjatahun +
    "&masakerjabulan=" +
    masakerjabulan +
    "&masakerjahari=" +
    masakerjahari +
    "&gajipokok=" +
    gajipokok +
    "&tunjanganjabatan=" +
    tunjanganjabatan +
    "&jenissk=" +
    jenissk.replace(/%20/g, " ");
  param +=
    "&p1562=" +
    p1562 +
    "&jml_pesangon=" +
    jml_pesangon +
    "&p1563=" +
    p1563 +
    "&tot_penghargaan=" +
    tot_penghargaan +
    "&p1564a=" +
    p1564a +
    "&jmlh_p1564a=" +
    jmlh_p1564a +
    "&p1564b=" +
    p1564b +
    "&jmlh_p1564b=" +
    jmlh_p1564b +
    "&p1564c=" +
    p1564c +
    "&jmlh_p1564c=" +
    jmlh_p1564c;
  param +=
    "&tot_sblm_pajak=" +
    tot_sblm_pajak +
    "&tot_pesangon=" +
    tot_pesangon +
    "&method=" +
    getValue("method");
  //    alert(param);

  //Umar
  if (jenissk == "Pesangon" || jenissk == "Kompensasi") {
    let totalPesangon = document.getElementById("jmlPesangonReal").value;
    let pengaliPesangon = document.getElementById("inputPengaliPesangon").value;
    let totalPMK = document.getElementById("jmlPMKReal").value;
    let pengaliPMK = document.getElementById("inputPengaliPMK").value;

    param += "&totalPesangon=" + totalPesangon;
    param += "&pengaliPesangon=" + pengaliPesangon;
    param += "&totalPMK=" + totalPMK;
    param += "&pengaliPMK=" + pengaliPMK;
  } else {
    let totalPisah = document.getElementById("jmlPisahReal").value;
    let pengaliPisah = document.getElementById("inputPengaliPisah").value;

    param += "&totalPisah=" + totalPisah;
    param += "&pengaliPisah=" + pengaliPisah;
  }

  let totalCuti = document.getElementById("ttlCutiReal").value;
  let pengaliCuti = document.getElementById("inputPengaliCuti").value;
  let totalPP = document.getElementById("subTotalPPReal").value;
  // let totalPHK         = document.getElementById("gTotalPPReal").value;
  // let totalPajak       = document.getElementById("ttlTaxPHKReal").value;

  param += "&tglmasuk=" + tglmasuk;
  param += "&totalCuti=" + totalCuti;
  param += "&pengaliCuti=" + pengaliCuti;
  param += "&totalPP=" + totalPP;
  param += "&pengaliPP=0.15";
  param += "&xPP=" + xpp;
  // param   += "&totalPHK=" + totalPHK;
  // param   += "&totalPajak=" + totalPajak;
  param += "&hasilKaliGaji1=" + hasilKaliGaji1;
  param += "&hasilKaliGaji2=" + hasilKaliGaji2;
  //End Umar

  tujuan = "sdm_slave_pesangon.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // if(jenissk.replace(/%20/g, " ")=='Uang Pisah'){
          //     calculateUangPisah();
          // } else {
          //     calculatePesangon();
          // }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function del(noid) {
  param = "noid=" + noid + "&method=deletedata";
  tujuan = "sdm_slave_pesangon.php";
  if (confirm("Are You Sure Want Delete Data?"))
    post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showPDF(nosurat, nama, ev) {
  param = "nosurat=" + nosurat;
  tujuan = "sdm_pesangon_pdf.php?" + param;
  //display window
  title = nama;
  width = "700";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}
function clearPil() {
  document.getElementById("nmCar").value = "";
  document.getElementById("jnsSkCar").value = "";
  document.getElementById("BlnCr").value = "";
  loadData(0);
}

function posting(noid) {
  param = "method=posting";
  param += "&noid=" + noid;
  tujuan = "sdm_slave_pesangon.php";
  alertify.confirm(
    "Posting",
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
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function unposting(noid) {
  param = "method=unposting";
  param += "&noid=" + noid;
  tujuan = "sdm_slave_pesangon.php";
  alertify.confirm(
    "Posting",
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
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//Umar
function changeKali(jumlah, tipe, gaji, tunjangan, pengkali) {
  let ele = document.querySelectorAll(".hasilInput" + tipe);
  let xPP = document.getElementById("inputPP");

  for (let index = 0; index < ele.length; index++) {
    ele[index].innerText = jumlah;
  }

  let ele1 = document.querySelectorAll(".jml" + tipe);
  if (tipe == "Pesangon" || tipe == "PMK") {
    for (let index = 0; index < ele1.length; index++) {
      ele1[index].innerText = numberFormat(
        parseInt(jumlah) *
          ((parseInt(gaji) + parseInt(tunjangan)) * parseInt(pengkali))
      );
    }
    document.getElementById("jml" + tipe + "Real").value =
      parseInt(jumlah) *
      ((parseInt(gaji) + parseInt(tunjangan)) * parseInt(pengkali));

    document.getElementById("subTotalPP").innerText = numberFormat(
      (xPP.value / 100) *
        (parseInt(document.getElementById("jmlPesangonReal").value) +
          parseInt(document.getElementById("jmlPMKReal").value))
    );
    document.getElementById("subTotalPPReal").value =
      (xPP.value / 100) *
      (parseInt(document.getElementById("jmlPesangonReal").value) +
        parseInt(document.getElementById("jmlPMKReal").value));
  } else {
    for (let index = 0; index < ele1.length; index++) {
      ele1[index].innerText = numberFormat(
        parseInt(jumlah) * (parseInt(gaji) + parseInt(tunjangan))
      );
    }
    document.getElementById("jml" + tipe + "Real").value =
      parseInt(jumlah) * (parseInt(gaji) + parseInt(tunjangan));

    document.getElementById("subTotalPP").innerText = numberFormat(
      (xPP.value / 100) *
        parseInt(document.getElementById("jmlPisahReal").value)
    );
    document.getElementById("subTotalPPReal").value =
      (xPP.value / 100) *
      parseInt(document.getElementById("jmlPisahReal").value);
  }

  // setTimeout(() => {
  getTotalPHK(tipe);
  // }, 1000);
}

function calculatePP(jumlah, tipe) {
  let ele = document.querySelectorAll(".inputXPP");
  for (let index = 0; index < ele.length; index++) {
    ele[index].innerText = jumlah + " %";
  }

  if (jumlah < 0 || jumlah > 100) {
    alertify.alert("info", "Tidak Bisa Kurang dari 0 Atau Lebih dari 100");
    document.getElementById("inputPP").value = 15;
    for (let index = 0; index < ele.length; index++) {
      ele[index].innerText = 15 + " %";
    }

    return;
  }

  let jumlahTemp = 0;
  if (tipe == "Pesangon" || tipe == "PMK") {
    let ttlPesangon = document.getElementById("jmlPesangonReal").value;
    let ttlPMK = document.getElementById("jmlPMKReal").value;

    jumlahTemp = (jumlah / 100) * (parseInt(ttlPesangon) + parseInt(ttlPMK));
  } else {
    let ttlPisah = document.getElementById("jmlPisahReal").value;

    jumlahTemp = (jumlah / 100) * parseInt(ttlPisah);
  }

  document.getElementById("subTotalPP").innerText = numberFormat(jumlahTemp);
  document.getElementById("subTotalPPReal").value = jumlahTemp;

  getTotalPHK(tipe);
}

function calculateCuti(gaji, cuti, tipe) {
  document.getElementById("ttlCuti").innerText = numberFormat(
    parseInt(gaji * cuti)
  );
  document.getElementById("ttlCutiReal").value = parseInt(gaji * cuti);

  getTotalPHK(tipe);
}

function getTotalPHK(tipe) {
  let ttlCuti = document.getElementById("ttlCutiReal").value;
  let ttlPP = document.getElementById("subTotalPPReal").value;
  let jumlahTemp = 0;
  if (tipe == "Pesangon" || tipe == "PMK") {
    let ttlPesangon = document.getElementById("jmlPesangonReal").value;
    let ttlPMK = document.getElementById("jmlPMKReal").value;

    jumlahTemp = jumlahTemp + parseInt(ttlPesangon) + parseInt(ttlPMK);
  } else {
    let ttlPisah = document.getElementById("jmlPisahReal").value;

    jumlahTemp = jumlahTemp + parseInt(ttlPisah);
  }

  let jumlah = jumlahTemp + parseInt(ttlCuti) + parseInt(ttlPP);

  document.getElementById("gTotalPP").innerText = numberFormat(jumlah);
  document.getElementById("gTotalPPReal").value = jumlah;

  getPPH21PHK(jumlah);
}

function getPPH21PHK(jumlah) {
  // let hitunganpph = JSON.parse(document.getElementById('jsonMines').innerText);
  // let tempJumlah  = parseInt(jumlah);
  // let tampil      = 0;
  // let tempArray   = new Array();
  // let ttlTax      = 0;
  // let tampilMines = document.querySelectorAll(".tampilMines");
  // let mines       = document.querySelectorAll(".minesClass");
  // let minesReal   = document.querySelectorAll(".minesReal");
  // for (const key in hitunganpph) {
  //     tempPPH     = parseInt(hitunganpph[key].penghasilan);
  //     tempPersen  = parseFloat(hitunganpph[key].persentase);

  //     if (tampilMines[key] == undefined) {
  //         continue;
  //     }

  //     if (tempJumlah > tempPPH) {
  //         tampil          = tempPPH;
  //         tempJumlah      = tempJumlah - tempPPH;
  //         tempArray[key]  = tempPersen * tempPPH;
  //     } else {
  //         tampil          = tempJumlah;
  //         tempArray[key]  = tempPersen * tempJumlah;
  //     }

  //     tampilMines[key].innerText  = numberFormat(tampil);
  //     mines[key].innerText        = numberFormat(tempArray[key]);
  //     minesReal[key].value        = tempArray[key];

  //     ttlTax = ttlTax + tempArray[key];
  // }

  // document.getElementById('ttlTaxPHK').innerText = numberFormat(ttlTax);
  // document.getElementById('ttlTaxPHKReal').value = ttlTax;

  // document.getElementById('phkDibayar').innerText = numberFormat(jumlah - ttlTax);
  // document.getElementById('phkDibayarReal').value = jumlah - ttlTax;

  // document.getElementById('terbilang').innerText = pembilang(jumlah - ttlTax) + " Rupiah";

  let tujuan = "sdm_slave_pesangon.php";
  let param = "method=hitungPPH21";
  param += "&ttlGPP=" + jumlah;
  param += "&karyawanid=" + getValue("karyawanid");

  post_response_text(tujuan, param, function () {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          let data = con.responseText.split("||");
          document.getElementById("pphContainer").innerHTML = data[0];
          document.getElementById("terbilang").innerHTML =
            "Terbilang : " + pembilang(data[1]) + " Rupiah";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  });
}

function savePesangonNew(tipe) {
  //Header
  let noid = document.getElementById("noid").value;
  let tanggal = document.getElementById("tanggal").value;
  let karyawanid =
    document.getElementById("karyawanid").options[
      document.getElementById("karyawanid").selectedIndex
    ].value;
  // let pihakpertama     = document.getElementById('pihakpertama').options[document.getElementById('pihakpertama').selectedIndex].value;
  let kodeunit = document.getElementById("kodeunit").value;
  let tglmasuk = document.getElementById("tglmasuk").value;
  let tglberhenti = document.getElementById("tglberhenti").value;
  let masakerjatahun = document.getElementById("masakerjatahun").value;
  let masakerjabulan = document.getElementById("masakerjabulan").value;
  let masakerjahari = document.getElementById("masakerjahari").value;
  let gajipokok = document.getElementById("gajipokok").value.replace(/,/g, "");
  let jenis = document.getElementById("jenis").value;

  let uangpisah = 0;
  let textuangpisah = "";
  if (
    typeof document.getElementById("uangpisah") !== "undefined" &&
    document.getElementById("uangpisah") !== null
  ) {
    uangpisah = document.getElementById("uangpisah").value.replace(/,/g, "");
    textuangpisah = document.getElementById("textuangpisah").innerHTML;
  }

  let upmk = 0;
  let textupmk = "";
  if (
    typeof document.getElementById("upmk") !== "undefined" &&
    document.getElementById("upmk") !== null
  ) {
    upmk = document.getElementById("upmk").value.replace(/,/g, "");
    textupmk = document.getElementById("textupmk").innerHTML;
  }

  let gajicuti = document.getElementById("gajicuti").value.replace(/,/g, "");
  let cutitahunan = document
    .getElementById("cutitahunan")
    .value.replace(/,/g, "");
  let pembagigajicuti = document
    .getElementById("pembagigajicuti")
    .value.replace(/,/g, "");
  let rupiahcutitahunan = document
    .getElementById("rupiahcutitahunan")
    .value.replace(/,/g, "");

  let gajikesehatan = document
    .getElementById("gajikesehatan")
    .value.replace(/,/g, "");
  let proporsikesehatan = document
    .getElementById("proporsikesehatan")
    .value.replace(/,/g, "");
  let pengalikesehatan = document
    .getElementById("pengalikesehatan")
    .value.replace(/,/g, "");
  let rupiahkesehatan = document
    .getElementById("rupiahkesehatan")
    .value.replace(/,/g, "");

  let tambahan1 = document.getElementById("tambahan1").value.replace(/,/g, "");
  let tambahan2 = document.getElementById("tambahan2").value.replace(/,/g, "");
  let tambahan3 = document.getElementById("tambahan3").value.replace(/,/g, "");
  let tambahan4 = document.getElementById("tambahan4").value.replace(/,/g, "");

  let nilaitambahan1 = document
    .getElementById("nilaitambahan1")
    .value.replace(/,/g, "");
  let nilaitambahan2 = document
    .getElementById("nilaitambahan2")
    .value.replace(/,/g, "");
  let nilaitambahan3 = document
    .getElementById("nilaitambahan3")
    .value.replace(/,/g, "");
  let nilaitambahan4 = document
    .getElementById("nilaitambahan4")
    .value.replace(/,/g, "");

  let rupiahtotal1 = document
    .getElementById("rupiahtotal1")
    .value.replace(/,/g, "");

  jenispengembalian1 = document
    .getElementById("jenispengembalian1")
    .value.replace(/,/g, "");
  jenispengembalian2 = document
    .getElementById("jenispengembalian2")
    .value.replace(/,/g, "");
  jenispengembalian3 = document
    .getElementById("jenispengembalian3")
    .value.replace(/,/g, "");
  jenispengembalian4 = document
    .getElementById("jenispengembalian4")
    .value.replace(/,/g, "");
  jenispengembalian5 = document
    .getElementById("jenispengembalian5")
    .value.replace(/,/g, "");
  jenispengembalian6 = document
    .getElementById("jenispengembalian6")
    .value.replace(/,/g, "");

  nilaijenispengembalian1 = document
    .getElementById("nilaijenispengembalian1")
    .value.replace(/,/g, "");
  nilaijenispengembalian2 = document
    .getElementById("nilaijenispengembalian2")
    .value.replace(/,/g, "");
  nilaijenispengembalian3 = document
    .getElementById("nilaijenispengembalian3")
    .value.replace(/,/g, "");
  nilaijenispengembalian4 = document
    .getElementById("nilaijenispengembalian4")
    .value.replace(/,/g, "");
  nilaijenispengembalian5 = document
    .getElementById("nilaijenispengembalian5")
    .value.replace(/,/g, "");
  nilaijenispengembalian6 = document
    .getElementById("nilaijenispengembalian6")
    .value.replace(/,/g, "");

  if (nilaijenispengembalian1 == "") {
    jenispengembalian1 = "";
  }
  if (nilaijenispengembalian2 == "") {
    jenispengembalian2 = "";
  }
  if (nilaijenispengembalian3 == "") {
    jenispengembalian3 = "";
  }
  if (nilaijenispengembalian4 == "") {
    jenispengembalian4 = "";
  }

  if (jenispengembalian1 == "") {
    nilaijenispengembalian1 = 0;
  }
  if (jenispengembalian2 == "") {
    nilaijenispengembalian2 = 0;
  }
  if (jenispengembalian3 == "") {
    nilaijenispengembalian3 = 0;
  }
  if (jenispengembalian4 == "") {
    nilaijenispengembalian4 = 0;
  }

  if (nilaitambahan1 == "") {
    tambahan1 = "";
  }
  if (nilaitambahan2 == "") {
    tambahan2 = "";
  }
  if (tambahan1 == "") {
    nilaitambahan1 = 0;
  }
  if (tambahan2 == "") {
    nilaitambahan2 = 0;
  }

  let nilaipajak = document
    .getElementById("nilaipajak")
    .value.replace(/,/g, "");
  let rupiahtotalpotongan = document
    .getElementById("rupiahtotalpotongan")
    .value.replace(/,/g, "");
  let rupiahditerima = document
    .getElementById("rupiahditerima")
    .value.replace(/,/g, "");

  let tujuan = "sdm_slave_pesangon.php";
  let param = "method=" + tipe;

  //Header
  param += "&noid=" + noid;
  param += "&tanggal=" + tanggal;
  param += "&karyawanid=" + karyawanid;
  param += "&kodeunit=" + kodeunit;
  param += "&tglmasuk=" + tglmasuk;
  param += "&tglberhenti=" + tglberhenti;
  param += "&masakerjatahun=" + masakerjatahun;
  param += "&masakerjabulan=" + masakerjabulan;
  param += "&masakerjahari=" + masakerjahari;
  param += "&gajipokok=" + gajipokok;
  param += "&jenis=" + jenis;

  //Detail

  param += "&uangpisah=" + uangpisah;
  param += "&textuangpisah=" + textuangpisah;
  param += "&upmk=" + upmk;
  param += "&textupmk=" + textupmk;
  param += "&gajicuti=" + gajicuti;
  param += "&cutitahunan=" + cutitahunan;
  param += "&pembagigajicuti=" + pembagigajicuti;
  param += "&rupiahcutitahunan=" + rupiahcutitahunan;

  param += "&gajikesehatan=" + gajikesehatan;
  param += "&proporsikesehatan=" + proporsikesehatan;
  param += "&pengalikesehatan=" + pengalikesehatan;
  param += "&rupiahkesehatan=" + rupiahkesehatan;
  param += "&tambahan1=" + tambahan1;
  param += "&tambahan2=" + tambahan2;
  param += "&tambahan3=" + tambahan3;
  param += "&tambahan4=" + tambahan4;
  param += "&nilaitambahan1=" + nilaitambahan1;
  param += "&nilaitambahan2=" + nilaitambahan2;
  param += "&nilaitambahan3=" + nilaitambahan3;
  param += "&nilaitambahan4=" + nilaitambahan4;

  param += "&rupiahtotal1=" + rupiahtotal1;

  param += "&jenispengembalian1=" + jenispengembalian1;
  param += "&jenispengembalian2=" + jenispengembalian2;
  param += "&jenispengembalian3=" + jenispengembalian3;
  param += "&jenispengembalian4=" + jenispengembalian4;
  param += "&jenispengembalian5=" + jenispengembalian5;
  param += "&jenispengembalian6=" + jenispengembalian6;

  param += "&nilaijenispengembalian1=" + nilaijenispengembalian1;
  param += "&nilaijenispengembalian2=" + nilaijenispengembalian2;
  param += "&nilaijenispengembalian3=" + nilaijenispengembalian3;
  param += "&nilaijenispengembalian4=" + nilaijenispengembalian4;
  param += "&nilaijenispengembalian5=" + nilaijenispengembalian5;
  param += "&nilaijenispengembalian6=" + nilaijenispengembalian6;

  param += "&nilaipajak=" + nilaipajak;
  param += "&rupiahtotalpotongan=" + rupiahtotalpotongan;
  param += "&rupiahditerima=" + rupiahditerima;

  // console.log(tujuan, param);

  post_response_text(tujuan, param, function () {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadData(0);
          cancelIsiNew();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  });
}

function cancelIsiNew() {
  document.getElementById("tanggal").value = "";
  document.getElementById("karyawanid").value = "";
  document.getElementById("pihakpertama").value = "";
  document.getElementById("kodeunit").value = "";
  document.getElementById("tglmasuk").value = "";
  document.getElementById("tglberhenti").value = "";
  document.getElementById("masakerjatahun").value = "";
  document.getElementById("masakerjabulan").value = "";
  document.getElementById("masakerjahari").value = "";
  document.getElementById("gajipokok").value = "";
  document.getElementById("jenis").value = "";

  document.getElementById("detailTable").innerHTML = "";
}

function showPDFNew(noid, jenisx, ev) {
  let param = "noid=" + noid + "&jenisx=" + jenisx + "&method=showPDFNewv2";
  let tujuan = "sdm_slave_pesangon.php?" + param;
  let title = "Pesangon";
  let content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";

  alertify
    .popup(title, content)
    .set({ resizable: true, maximizable: true })
    .resizeTo("80%", "70%");
}

function ajukan() {
  notransaksi = document.getElementById("notransaksi_ajukan").value;
  jlh = document.getElementById("jlh").value;
  var param = "method=ajukan";
  param += "&notransaksi=" + notransaksi;
  param += "&jlh=" + jlh;
  for (i = 1; i <= jlh; i++) {
    param +=
      "&" + "kepada" + i + "=" + document.getElementById("kepada" + i).value;
  }
  if (jlh == 0) {
    alertify.alert("Warning: Approval kosong");
    return;
  }
  tujuan = "sdm_slave_pesangon.php";
  post_response_text(tujuan, param, () => {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify.popup().destroy();
          alertify.alert("Info", "Success");
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  });
}
//End Umar
