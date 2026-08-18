function newdata() {
  //indra
  document.getElementById("header").style.display = "block";
  document.getElementById("listdata").style.display = "none";
  document.getElementById("detail").style.display = "none";
  cancel();
}

function savehead(mode = "save") {
  unit = getValue("unit");
  per = getValue("per");
  sesi = getValue("sesi");
  nopdo = getValue("nopdo");
  if (per == "") {
    alertify.alert("Periode masih kosong");
    return false;
  }
  param =
    "method=getNotrans" +
    "&unit=" +
    unit +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&mode=" +
    mode +
    "&nopdo=" +
    nopdo;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  async function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          disableValue("savehead", "per", "sesi", "unit");
          document.getElementById("detail").style.display = "block";

          if (mode == "edit") {
            const data = JSON.parse(con.responseText);
            const noupah = data.UPAH ?? "";
            const nokas = data.KAS ?? "";
            const nokontraktor = data.KTRK ?? "";
            // const nosupplier = data.SUPP ?? "";
            const nohutangkas = data.HTGK ?? "";
            const nopjd = data.PJD ?? "";
            const noothers = data.OTH ?? "";
            const notanaman = data.TNM ?? "";
            const notraksi = data.TRK ?? "";

            /* INIT NOTRANSAKSI */
            document.querySelector(`#noupah`).value = noupah;
            document.querySelector(`#nokas`).value = nokas;
            document.querySelector(`#nokontraktor`).value = nokontraktor;
            // document.querySelector(`#nosupplier`).value = nosupplier;
            document.querySelector(`#nohutangkas`).value = nohutangkas;
            document.querySelector(`#nopjd`).value = nopjd;
            document.querySelector(`#noothers`).value = noothers;
            document.querySelector(`#notanaman`).value = notanaman;
            document.querySelector(`#notraksi`).value = notraksi;

            /* Disabled all button when editing, so user cant generate it again */
            if (noupah != "") {
              document.querySelector(`#prevupah`).disabled = true;
              /* UPAH - INPUTAN*/
              document.getElementById("detailupah").style.display = "block";
              document.getElementById("detailupah").innerHTML =
                await detailUpah(unit, per);

              /* UPAH - LIST */
              document.getElementById("listupah").style.display = "block";
              document.getElementById("listupah").innerHTML = await listUpah(
                nopdo,
                noupah,
              );
            }
            if (nokas != "") {
              document.querySelector(`#prevkas`).disabled = true;
              /* PENGELUARAN TUNAI - INPUTAN*/
              document.getElementById("detailkas").style.display = "block";
              document.getElementById("detailkas").innerHTML =
                await detailTunai(unit, per);

              /* PENGELUARAN TUNAI - LIST */
              document.getElementById("listkas").style.display = "block";
              document.getElementById("listkas").innerHTML = await listTunai(
                nopdo,
                nokas,
              );
            }
            if (nokontraktor != "") {
              document.querySelector(`#prevkontraktor`).disabled = true;
              /* KONTRAKTOR - INPUTAN*/
              document.getElementById("detailkontraktor").style.display =
                "block";
              document.getElementById("detailkontraktor").innerHTML =
                await detailKontraktor(unit, per);

              /* KONTRAKTOR - LIST */
              document.getElementById("listkontraktor").style.display = "block";
              document.getElementById("listkontraktor").innerHTML =
                await listKontraktor(nopdo, nokontraktor);
            }
            // if (nosupplier != "") {
            //   document.querySelector(`#prevsupplier`).disabled = true;
            //   /* SUPPLIER - INPUTAN*/
            //   document.getElementById("detailsupplier").style.display = "block";
            //   document.getElementById("detailsupplier").innerHTML =
            //     await detailSupplier(unit, per);

            //   /* SUPPLIER - LIST */
            //   document.getElementById("listsupplier").style.display = "block";
            //   document.getElementById("listsupplier").innerHTML =
            //     await listSupplier(nopdo, nosupplier);
            // }
            if (nohutangkas != "") {
              document.querySelector(`#prevhutangkas`).disabled = true;
              /* hutangkas - INPUTAN*/
              document.getElementById("detailhutangkas").style.display =
                "block";
              document.getElementById("detailhutangkas").innerHTML =
                await detailHutangkas(unit, per);

              /* hutangkas - LIST */
              document.getElementById("listhutangkas").style.display = "block";
              document.getElementById("listhutangkas").innerHTML =
                await listHutangkas(nopdo, nohutangkas);
            }
            if (nopjd != "") {
              document.querySelector(`#prevpjd`).disabled = true;
              /* PJD - INPUTAN*/
              document.getElementById("detailpjd").style.display = "block";
              document.getElementById("detailpjd").innerHTML = await detailPjd(
                unit,
                per,
              );

              /* PJD - LIST */
              document.getElementById("listpjd").style.display = "block";
              document.getElementById("listpjd").innerHTML = await listPjd(
                nopdo,
                nopjd,
              );
            }
            if (noothers != "") {
              document.querySelector(`#prevothers`).disabled = true;
              /* PMK Lainnya - INPUTAN*/
              document.getElementById("detailothers").style.display = "block";
              document.getElementById("detailothers").innerHTML =
                await detailOthers(unit, per);

              /* PMK Lainnya - LIST */
              document.getElementById("listothers").style.display = "block";
              document.getElementById("listothers").innerHTML =
                await listOthers(nopdo, noothers);
            }

            if (notanaman != "") {
              document.querySelector(`#prevtanaman`).disabled = true;
              /* PMK Lainnya - INPUTAN*/
              document.getElementById("detailtanaman").style.display = "block";
              document.getElementById("detailtanaman").innerHTML =
                await detailTanaman(unit, per);

              /* PMK Lainnya - LIST */
              document.getElementById("listtanaman").style.display = "block";
              document.getElementById("listtanaman").innerHTML =
                await listTanaman(nopdo, notanaman);
            }

            if (notraksi != "") {
              document.querySelector(`#prevtraksi`).disabled = true;
              /* PMK Traksi - INPUTAN*/
              document.getElementById("detailtraksi").style.display = "block";
              document.getElementById("detailtraksi").innerHTML =
                await detailTraksi(unit, per);

              /* PMK Traksi - LIST */
              document.getElementById("listtraksi").style.display = "block";
              document.getElementById("listtraksi").innerHTML =
                await listTraksi(nopdo, notraksi);
            }
            document.querySelectorAll(".btnBatal button").forEach((elem) => {
              elem.disabled = true;
            });
          }

          // listupah(nopdo, unit, per);
          // if (document.getElementById("sesi").value != "2") {
          //   document.getElementById("prevkontraktor").disabled = true;
          //   document.getElementById("btnBatalKontraktor").disabled = true;
          // } else {
          //   document.getElementById("prevkontraktor").disabled = false;
          //   document.getElementById("btnBatalKontraktor").disabled = false;
          // }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function edit(nopdo, unit, per, sesi) {
  document.getElementById("listdata").style.display = "none";
  document.getElementById("header").style.display = "block";
  document.getElementById("nopdo").value = nopdo;
  setValue2("unit", unit);
  setValue2("per", per);
  setValue2("sesi", sesi);

  document.getElementById("noupah").value = "";
  document.getElementById("nokas").value = "";
  document.getElementById("nokontraktor").value = "";
  // document.getElementById("nosupplier").value = "";
  document.getElementById("nohutangkas").value = "";
  document.getElementById("nopjd").value = "";
  document.getElementById("noothers").value = "";
  document.getElementById("notanaman").value = "";
  document.getElementById("notraksi").value = "";

  document.getElementById("prevupah").disabled = false;
  document.getElementById("detailupah").style.display = "none";
  document.getElementById("prevkas").disabled = false;
  document.getElementById("detailkas").style.display = "none";
  document.getElementById("prevkontraktor").disabled = false;
  document.getElementById("detailkontraktor").style.display = "none";
  // document.getElementById("prevsupplier").disabled = false;
  // document.getElementById("detailsupplier").style.display = "none";
  document.getElementById("prevhutangkas").disabled = false;
  document.getElementById("detailhutangkas").style.display = "none";
  document.getElementById("prevpjd").disabled = false;
  document.getElementById("detailpjd").style.display = "none";
  document.getElementById("prevothers").disabled = false;
  document.getElementById("detailothers").style.display = "none";
  document.getElementById("prevtanaman").disabled = false;
  document.getElementById("detailtanaman").style.display = "none";
  document.getElementById("prevtraksi").disabled = false;
  document.getElementById("detailtraksi").style.display = "none";

  savehead("edit");
}

function deletehead(nopdo, unit, per) {
  param =
    "method=deletehead" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loaddata(num) {
  // thnsch = document.getElementById('thnsch');
  // thnsch = thnsch.options[thnsch.selectedIndex].value;

  thnsch = document.getElementById("thnsch").value;
  notransaksisch = document.getElementById("notransaksisch").value;
  sesisch = document.getElementById("sesisch").value;
  persch = document.getElementById("persch").value;
  kodeorgsch = document.getElementById("kodeorgsch").value;

  param = "method=loaddata&page=" + num;
  param +=
    "&thnsch=" +
    thnsch +
    "&notransaksisch=" +
    notransaksisch +
    "&sesisch=" +
    sesisch +
    "&persch=" +
    persch +
    "&kodeorgsch=" +
    kodeorgsch;

  tujuan = "keu_slave_pdo3.php";
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
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancel() {
  const sch = document.querySelectorAll(".input-header");
  sch.forEach((el) => {
    setValue2(el.id, "");
  });
  document.getElementById("listdata").style.display = "none";
  document.getElementById("detail").style.display = "none";
  document.getElementById("savehead").disabled = false;
  document.getElementById("per").disabled = false;
  document.getElementById("sesi").disabled = false;
  document.getElementById("unit").disabled = false;
  document.getElementById("prevupah").disabled = false;

  document.querySelectorAll(".btnBatal button").forEach((elem) => {
    elem.disabled = false;
  });

  batalupah();
}

function totalkas(id, jenis) {
  //Umar
  let check = doc;
  ument.getElementById(id + "_checkkas").checked;
  //End Umar

  shi = parseFloat(
    document.getElementById("jlhkas_" + id).innerHTML.replace(/,/gi, ""),
  );
  // alertify.alert(document.getElementById(id+'_shi').innerHTML);
  // alertify.alert(document.getElementById(id+'_shi').innerHTML.replace(/,/gi,''));
  // alertify.alert(shi);
  estimasi = parseFloat(
    document.getElementById(id + "_estimasikas").value.replace(/,/gi, ""),
  );
  if (shi <= 0) {
    shi = 0;
  }

  if (estimasi <= 0) {
    estimasi = 0;
  }

  total = shi + estimasi;

  //Umar
  if (check) {
    total = shi - estimasi;
  }
  //End Umar
  document.getElementById(id + "_sdbikas").value = numberFormat(total, 2);
  hitungsbutotalkas(document.getElementById("maxdatakas").value, jenis);
}

function hitungsbutotalkas(arrid, jenis) {
  shi = 0;
  estimasi = 0;
  sdbi = 0;
  for (var i = 1; i <= arrid; i++) {
    if (
      typeof document.getElementById("jlhkas_" + i) != "undefined" &&
      document.getElementById("jlhkas_" + i) != null
    ) {
      shi += parseFloat(
        document.getElementById("jlhkas_" + i).innerHTML.replace(/,/gi, ""),
      );
    }

    //Umar
    if (
      typeof document.getElementById(i + "_checkkas") != "undefined" &&
      document.getElementById(i + "_checkkas") != null
    ) {
      check = document.getElementById(i + "_checkkas").checked;
    }
    //End Umar

    if (
      typeof document.getElementById(i + "_estimasikas") != "undefined" &&
      document.getElementById(i + "_estimasikas") != null
    ) {
      //Umar
      if (check) {
        estimasi =
          estimasi -
          parseFloat(
            document
              .getElementById(i + "_estimasikas")
              .value.replace(/,/gi, ""),
          );
      } else {
        estimasi =
          estimasi +
          parseFloat(
            document
              .getElementById(i + "_estimasikas")
              .value.replace(/,/gi, ""),
          );
      }
      //End Umar
    }

    if (
      typeof document.getElementById(i + "_sdbikas") != "undefined" &&
      document.getElementById(i + "_sdbikas") != null
    ) {
      sdbi += parseFloat(
        document.getElementById(i + "_sdbikas").value.replace(/,/gi, ""),
      );
    }
  }

  // if(shi<=0)
  // {
  // 	shi=0;
  // }

  // if(estimasi<=0)
  // {
  // 	estimasi=0;
  // }
  total = shi + estimasi;
  //document.getElementById(jenis+'_subtotal_shi').value=numberFormat(shi,3);
  document.getElementById(jenis + "_subtotal_estimasikas").innerHTML =
    numberFormat(estimasi, 2);
  document.getElementById(jenis + "_subtotal_sbikas").innerHTML = numberFormat(
    total,
    2,
  );
}

function totallnn(id, jenis) {
  //Umar
  let check = document.getElementById(id + "_check").checked;
  //End Umar

  shi = parseFloat(
    document.getElementById(id + "_shi").innerHTML.replace(/,/gi, ""),
  );
  // alertify.alert(document.getElementById(id+'_shi').innerHTML);
  // alertify.alert(document.getElementById(id+'_shi').innerHTML.replace(/,/gi,''));
  // alertify.alert(shi);
  estimasi = parseFloat(
    document.getElementById(id + "_estimasi").value.replace(/,/gi, ""),
  );
  if (shi <= 0) {
    shi = 0;
  }

  if (estimasi <= 0) {
    estimasi = 0;
  }

  total = shi + estimasi;

  //Umar
  if (check) {
    total = shi - estimasi;
  }
  //End Umar

  document.getElementById(id + "_sdbi").value = numberFormat(total, 2);
  hitungsbutotal(document.getElementById("maxdatagaji").value, jenis);
}

function hitungsbutotal(arrid, jenis) {
  shi = 0;
  estimasi = 0;
  sdbi = 0;
  for (var i = 1; i <= arrid; i++) {
    if (
      typeof document.getElementById(i + "_shi") != "undefined" &&
      document.getElementById(i + "_shi") != null
    ) {
      shi += parseFloat(
        document.getElementById(i + "_shi").innerHTML.replace(/,/gi, ""),
      );
    }

    //Umar
    if (
      typeof document.getElementById(i + "_check") != "undefined" &&
      document.getElementById(i + "_check") != null
    ) {
      check = document.getElementById(i + "_check").checked;
    }
    //End Umar

    if (
      typeof document.getElementById(i + "_estimasi") != "undefined" &&
      document.getElementById(i + "_estimasi") != null
    ) {
      //Umar
      if (check) {
        estimasi =
          estimasi -
          parseFloat(
            document.getElementById(i + "_estimasi").value.replace(/,/gi, ""),
          );
      } else {
        estimasi =
          estimasi +
          parseFloat(
            document.getElementById(i + "_estimasi").value.replace(/,/gi, ""),
          );
      }
      //End Umar
    }

    if (
      typeof document.getElementById(i + "_sdbi") != "undefined" &&
      document.getElementById(i + "_sdbi") != null
    ) {
      sdbi += parseFloat(
        document.getElementById(i + "_sdbi").value.replace(/,/gi, ""),
      );
    }
  }

  // if(shi<=0)
  // {
  // 	shi=0;
  // }

  // if(estimasi<=0)
  // {
  // 	estimasi=0;
  // }

  total = shi + estimasi;
  //document.getElementById(jenis+'_subtotal_shi').value=numberFormat(shi,3);
  document.getElementById(jenis + "_subtotal_estimasi").innerHTML =
    numberFormat(estimasi, 2);
  document.getElementById(jenis + "_subtotal_sbi").innerHTML = numberFormat(
    total,
    2,
  );
}

function detail(nopdo, unit, per, sesidet, tiperekap, ev) {
  tujuan = "keu_slave_pdo3.php";
  param =
    "method=htmlexcelrekap" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&per=" +
    per +
    "&tiperekap=" +
    tiperekap +
    "&sesidet=" +
    sesidet;
  tujuan = tujuan;
  post_response_text(tujuan, param, respon);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.popup2().destroy();
          alertify
            .popup2("Rekapitulasi PDO", con.responseText)
            .set({
              resizable: true,
              maximizable: true,
            })
            .resizeTo("70%", "80%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailexcel(nopdo, unit, per, tiperekap, ev) {
  param =
    "method=htmlexcelrekap" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&per=" +
    per +
    "&tiperekap=" +
    tiperekap;
  tujuan = "keu_slave_pdo3.php";
  title = "Report Ms.Excel";
  printFile(param, tujuan, title, ev);
}

function detailexcelAll(nopdo, unit, per, tiperekap, listPdo) {
  var parsedData = JSON.parse(listPdo);
  parsedData.forEach((idPdo) => {
    if (idPdo == "UPAH") {
      param = `method=detailupahv2&nopdo=${nopdo}&per=${per}&unit=${unit}`;
    } else {
      param = `method=detailAll&nopdo=${nopdo}&unit=${unit}&per=${per}&tipePdo=${idPdo}&tiperekap=${tiperekap}`;
    }
    tujuan = "keu_slave_pdo3.php";
    title = "Report Ms.Excel";
    printnopopup(`${tujuan}?${param}`);
  });
}

function detailpdf(nopdo, unit, per, tiperekap, ev) {
  tujuan = "keu_slave_pdo3.php";
  param =
    "method=htmlexcelrekap" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&per=" +
    per +
    "&tiperekap=" +
    tiperekap;
  tujuan = tujuan + "?" + param;
  alertify
    .popuppdf(
      "<iframe frameborder=0 width=100% height=90% src='" + tujuan + "'>",
    )
    .set({
      frameless: true,
      resizable: true,
      maximizable: true,
      overflow: false,
    })
    .resizeTo("80%", "70%");
}

function generateAllPdo(tiperekap, ev) {
  const per = document.querySelector(`#periodeGenerate`).value;
  param = "method=htmlexcelrekap" + "&per=" + per + "&tiperekap=" + tiperekap;
  tujuan = "keu_slave_pdo3.php";
  if (tiperekap != "pdf") {
    title = "Report Ms.Excel";
    printFile(param, tujuan, title, ev);
  } else {
    tujuan = tujuan + "?" + param;
    alertify
      .popuppdf(
        "<iframe frameborder=0 width=100% height=90% src='" + tujuan + "'>",
      )
      .set({
        frameless: true,
        resizable: true,
        maximizable: true,
        overflow: false,
      })
      .resizeTo("80%", "70%");
  }
}

function prevupah() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  noupah = document.getElementById("noupah").value;
  tkupah = document.getElementById("tkupah").value;
  param = "method=noupah" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&noupah=" + noupah + "&tkupah=" + tkupah;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("noupah").value = trim(con.responseText);
          detailupah();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevkas() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nokas = document.getElementById("nokas").value;

  param = "method=nokas" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nokas=" + nokas;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("nokas").value = trim(con.responseText);
          detailkas();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevsupplier() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nosupplier = document.getElementById("nosupplier").value;

  param =
    "method=nosupplier" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nosupplier=" + nosupplier;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  async function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("nosupplier").value = trim(con.responseText);
          document.getElementById("detailsupplier").innerHTML =
            await detailSupplier(unit, per);
          document.getElementById("detailsupplier").style.display = "block";
          con.responseText;
          document.getElementById("prevsupplier").disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevhutangkas() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nohutangkas = document.getElementById("nohutangkas").value;

  param =
    "method=nohutangkas" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nohutangkas=" + nohutangkas;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  async function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("nohutangkas").value = trim(con.responseText);
          document.getElementById("detailhutangkas").innerHTML =
            await detailHutangkas(unit, per);
          document.getElementById("detailhutangkas").style.display = "block";
          con.responseText;
          document.getElementById("prevhutangkas").disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevpjd() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nopjd = document.getElementById("nopjd").value;

  param = "method=nopjd" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nopjd=" + nopjd;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  async function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("nopjd").value = trim(con.responseText);
          document.getElementById("detailpjd").innerHTML = await detailPjd(
            unit,
            per,
          );
          document.getElementById("detailpjd").style.display = "block";
          con.responseText;
          document.getElementById("prevpjd").disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevothers() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  noothers = document.getElementById("noothers").value;

  param =
    "method=noothers" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&noothers=" + noothers;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  async function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("noothers").value = trim(con.responseText);
          document.getElementById("detailothers").innerHTML =
            await detailOthers(unit, per);
          document.getElementById("detailothers").style.display = "block";
          con.responseText;
          document.getElementById("prevothers").disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevtanaman() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  notanaman = document.getElementById("notanaman").value;

  param =
    "method=notanaman" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&notanaman=" + notanaman;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  async function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("notanaman").value = trim(con.responseText);
          document.getElementById("detailtanaman").innerHTML =
            await detailTanaman(unit, per);
          document.getElementById("detailtanaman").style.display = "block";
          con.responseText;
          document.getElementById("prevtanaman").disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevtraksi() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  notraksi = document.getElementById("notraksi").value;

  param =
    "method=notraksi" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&notraksi=" + notraksi;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  async function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("notraksi").value = trim(con.responseText);
          document.getElementById("detailtraksi").innerHTML =
            await detailTraksi(unit, per);
          document.getElementById("detailtraksi").style.display = "block";
          con.responseText;
          document.getElementById("prevtraksi").disabled = true;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevbyrsup() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nobyrsup = document.getElementById("nobyrsup").value;

  param =
    "method=nobyrsup" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nobyrsup=" + nobyrsup;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("nobyrsup").value = trim(con.responseText);
          detailbyrsup();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevkontraktor() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nokontraktor = document.getElementById("nokontraktor").value;

  param =
    "method=nokontraktor" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nokontraktor=" + nokontraktor;
  tujuan = "keu_slave_pdo3.php";

  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("nokontraktor").value = trim(
            con.responseText,
          );
          detailkontraktor();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function displaylist() {
  document.getElementById("listdata").style.display = "block";
  document.getElementById("header").style.display = "none";
  document.getElementById("detail").style.display = "none";
  batalupah();
  loaddata(0);
}

//Umar
function simpanupah(max, start) {
  prosessimpanupah(start, max);
}

function prosessimpanupah(start, max) {
  let id = start;

  let nopdo = document.getElementById("nopdo").value;
  let unit = document.getElementById("unit").value;
  let per = document.getElementById("per").value;
  let sesi = document.getElementById("sesi").value;
  let noupah = document.getElementById("noupah").value;

  let shi = parseFloat(
    document.getElementById(id + "_shi").innerHTML.replace(/,/gi, ""),
  );
  let check = document.getElementById(id + "_check").checked;
  let estimasi = parseFloat(
    document.getElementById(id + "_estimasi").value.replace(/,/gi, ""),
  );
  let sdbi = parseFloat(
    document.getElementById(id + "_sdbi").value.replace(/,/gi, ""),
  );
  let keterangan = document.getElementById(id + "_ket").value;
  // let uraian = document.getElementById(id + "_uraian").innerHTML;
  let tipekaryawan = document.getElementById(id + "_tkid").innerHTML;
  let kelkomponengaji = document.getElementById(id + "_kkgj").innerHTML;

  let param =
    "method=simpanupah" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  // param += "&uraian=" +uraian;
  param +=
    "&noupah=" +
    noupah +
    "&shi=" +
    shi +
    "&estimasi=" +
    estimasi +
    "&sdbi=" +
    sdbi +
    "&idpdo=" +
    id +
    "&keterangan=" +
    keterangan +
    "&tipekaryawan=" +
    tipekaryawan;
  param += "&check=" + check;
  param += "&kelkomponengaji=" + kelkomponengaji;
  param += "&sesi=" + sesi;
  let tujuan = "keu_slave_pdo3.php";

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          start = start + 1;
          if (start <= max) {
            prosessimpanupah(start, max);
          } else {
            // alertify.alertify.alert("done");
            alertify.alert("Tersimpan", function () {
              // listupah();
            });
            // listupah();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function simpankas(max, start) {
  prosessimpankas(start, max);
}

function prosessimpankas(start, max) {
  let barisnya = start;
  let nopdo = document.getElementById("nopdo").value;
  let unit = document.getElementById("unit").value;
  let per = document.getElementById("per").value;
  let sesi = document.getElementById("sesi").value;
  let nokas = document.getElementById("nokas").value;
  let noakun = document.getElementById("noakun_" + barisnya).value;
  let kodekeg = document.getElementById("kodekeg_" + barisnya).innerHTML;
  let novoucher = document.getElementById("novoucher_" + barisnya).innerHTML;
  // novoucher = "";
  let shi = parseFloat(
    document.getElementById("jlhkas_" + barisnya).innerHTML.replace(/,/gi, ""),
  );
  let check = document.getElementById(barisnya + "_checkkas").checked;
  let estimasi = parseFloat(
    document.getElementById(barisnya + "_estimasikas").value.replace(/,/gi, ""),
  );
  let sdbi = parseFloat(
    document.getElementById(barisnya + "_sdbikas").value.replace(/,/gi, ""),
  );

  let param =
    "method=simpankas" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nokas=" + nokas;
  param += "&sesi=" + sesi;
  param +=
    "&noakunkas=" +
    noakun +
    "&shi=" +
    shi +
    "&estimasi=" +
    estimasi +
    "&sdbi=" +
    sdbi +
    "&idpdo=" +
    barisnya +
    "&novoucher=" +
    novoucher +
    "&akhir=tidak";
  param += "&check=" + check;
  param += "&kodekeg=" + kodekeg;
  let tujuan = "keu_slave_pdo3.php";

  function response() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          start = start + 1;
          if (start <= max) {
            prosessimpankas(start, max);
          } else {
            listkas();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, response);
}

function simpankontraktor(max) {
  start = 1;
  prosessimpankontraktor(start, max);
}

function prosessimpankontraktor(start, max) {
  let barisnya = start;
  let nopdo = document.getElementById("nopdo").value;
  let unit = document.getElementById("unit").value;
  let per = document.getElementById("per").value;
  let sesi = document.getElementById("sesi").value;
  let nokontraktor = document.getElementById("nokontraktor").value;
  let tes = document.getElementById("jlhkontraktor_" + barisnya).innerHTML;
  let jlh = tes.replace(/,/gi, "");
  let notransaksi = document.getElementById(
    "notransaksikontraktor_" + barisnya,
  ).innerHTML;
  let termin = document.getElementById(
    "terminkontraktor_" + barisnya,
  ).innerHTML;
  let dpp = document.getElementById("dpp_" + barisnya).innerHTML;
  let djl = dpp.replace(/,/gi, "");
  let noinvoice = document.getElementById("invoice_" + barisnya).innerHTML;
  let supplier = document.getElementById("supplier_" + barisnya).value;
  let kegiatan = document.getElementById("kegiatan_" + barisnya).innerHTML;

  chk = document.getElementById("checklist_" + barisnya);
  if (chk.checked == true) {
    checklist = 1;
  } else {
    checklist = 0;
  }

  let param =
    "method=simpankontraktor" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&per=" +
    per;
  param += "&nokontraktor=" + nokontraktor;
  param += "&sesi=" + sesi;
  param +=
    "&idpdo=" +
    barisnya +
    "&jumlahkontraktorx=" +
    jlh +
    "&nobapp=" +
    notransaksi +
    "&termin=" +
    termin +
    "&akhir=tidak";
  param += "&dpp=" + djl;
  param += "&noinvoice=" + noinvoice;
  param += "&supplier=" + supplier;
  param += "&kegiatan=" + kegiatan;
  param += "&checklist=" + checklist;

  let tujuan = "keu_slave_pdo3.php";
  function response() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          start = start + 1;
          if (start <= max) {
            prosessimpankontraktor(start, max);
          } else {
            // alertify.alert('masuk');
            // listkontraktor();
            batalkontraktor();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, response);
}
//End Umar

function editupah(
  nopdo,
  noupah,
  tkupah,
  tglupah,
  noakunupah,
  rekeningbankupah,
) {
  //document.getElementById('listdata').style.display = 'none';
  //document.getElementById('header').style.display = 'block';
  document.getElementById("noupah").value = noupah;
  document.getElementById("tkupah").value = tkupah;
  //document.getElementById('tglupah').value = tglupah;
  document.getElementById("noakunupah").value = noakunupah;
  document.getElementById("rekeningbankupah").value = rekeningbankupah;
  detailupah();
}
function deleteupah(nopdo, noupah, nourutupah) {
  param =
    "method=deleteupah" +
    "&nopdo=" +
    nopdo +
    "&noupah=" +
    noupah +
    "&nourutupah=" +
    nourutupah;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // listupah(nopdo);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function detailupah(tipe = "html") {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  noupah = document.getElementById("noupah").value;
  tkupah = document.getElementById("tkupah").value;
  param =
    "method=detailupah" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&noupah=" + noupah + "&tkupah=" + tkupah + "&tipe=" + tipe;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailupah").style.display = "block";
          document.getElementById("detailupah").innerHTML = con.responseText;
          document.getElementById("prevupah").disabled = true;
          document.getElementById("tkupah").disabled = true;
          // listupah();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listupah(nopdo, unit, per) {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  noupah = document.getElementById("noupah").value;
  tkupah = document.getElementById("tkupah").value;
  //tglupah = document.getElementById('tglupah').value;
  param =
    "method=listupah" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&noupah=" + noupah + "&tkupah=" + tkupah;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listupah").style.display = "block";
          document.getElementById("listupah").innerHTML = con.responseText;
          listkas();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalupah() {
  document.getElementById("prevupah").disabled = false;
  document.getElementById("batalupah").disabled = false;
  document.getElementById("tkupah").disabled = false;
  document.getElementById("detailupah").style.display = "none";
  document.getElementById("listupah").style.display = "block";
  noupah = document.getElementById("noupah").value = "";
  document.getElementById("listupah").innerHTML = "";
  document.getElementById("detailupah").innerHTML = "";
  batalkas();
}

function editkas(nopdo, nokas, tkkas, tglkas, noakunkas, rekeningbankkas) {
  //document.getElementById('listdata').style.display = 'none';
  //document.getElementById('header').style.display = 'block';
  document.getElementById("nokas").value = nokas;
  document.getElementById("tkkas").value = tkkas;
  //document.getElementById('tglkas').value = tglkas;
  document.getElementById("noakunkas").value = noakunkas;
  document.getElementById("rekeningbankkas").value = rekeningbankkas;
  detailkas();
}

function deletekas(nopdo, nokas, nourutkas) {
  param =
    "method=deletekas" +
    "&nopdo=" +
    nopdo +
    "&notrankas=" +
    nokas +
    "&idpdo=" +
    nourutkas;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listkas(nopdo);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletesupplier(nopdo, nosupplier, nourutsupplier) {
  param =
    "method=deletesupplier" +
    "&nopdo=" +
    nopdo +
    "&notransupplier=" +
    nosupplier +
    "&idpdo=" +
    nourutsupplier;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listSupplier(nopdo, nosupplier);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletehutangkas(nopdo, nohutangkas, nouruthutangkas) {
  param =
    "method=deletehutangkas" +
    "&nopdo=" +
    nopdo +
    "&notranhutangkas=" +
    nohutangkas +
    "&idpdo=" +
    nouruthutangkas;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listhutangkas(nopdo, nohutangkas);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletepjd(nopdo, nopjd, nourutpjd) {
  param =
    "method=deletepjd" +
    "&nopdo=" +
    nopdo +
    "&notranpjd=" +
    nopjd +
    "&idpdo=" +
    nourutpjd;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listpjd(nopdo, nopjd);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteothers(nopdo, noothers, nourutothers) {
  param =
    "method=deleteothers" +
    "&nopdo=" +
    nopdo +
    "&notranothers=" +
    noothers +
    "&idpdo=" +
    nourutothers;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listothers(nopdo, noothers);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletetanaman(nopdo, notanaman, nouruttanaman) {
  param =
    "method=deletetanaman" +
    "&nopdo=" +
    nopdo +
    "&notrantanaman=" +
    notanaman +
    "&idpdo=" +
    nouruttanaman;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listtanaman(nopdo, notanaman);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletetraksi(nopdo, notraksi, nouruttraksi) {
  param =
    "method=deletetraksi" +
    "&nopdo=" +
    nopdo +
    "&notrantraksi=" +
    notraksi +
    "&idpdo=" +
    nouruttraksi;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  // post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listtraksi(nopdo, notraksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailkas() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nokas = document.getElementById("nokas").value;
  // tkkas = document.getElementById('tkkas').value;
  param =
    "method=detailkas" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nokas=" + nokas; //+ '&tkkas=' + tkkas
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailkas").style.display = "block";
          document.getElementById("detailkas").innerHTML = con.responseText;
          document.getElementById("prevkas").disabled = true;
          // document.getElementById('tkkas').disabled = true;
          $(".select2").select2();
          listkas();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listkas(nopdo, unit, per, tipe = "html") {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nokas = document.getElementById("nokas").value;
  // tkkas = document.getElementById('tkkas').value;
  //tglkas = document.getElementById('tglkas').value;
  param =
    "method=listkas" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nokas=" + nokas; //+ '&tkkas=' + tkkas
  param += "&tipe=" + tipe; //+ '&tkkas=' + tkkas
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listkas").style.display = "block";
          document.getElementById("listkas").innerHTML = con.responseText;
          listbyrsup();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailhutangkas() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nohutangkas = document.getElementById("nohutangkas").value;
  // tkkas = document.getElementById('tkkas').value;
  param =
    "method=detailhutangkas" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&per=" +
    per;
  param += "&nohutangkas=" + nohutangkas; //+ '&tkkas=' + tkkas
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailhutangkas").style.display = "block";
          document.getElementById("detailhutangkas").innerHTML =
            con.responseText;
          document.getElementById("prevhutangkas").disabled = true;
          listhutangkas();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listhutangkas(nopdo, unit, per, tipe = "excel") {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nohutangkas = document.getElementById("nohutangkas").value;
  // tkkas = document.getElementById('tkkas').value;
  //tglkas = document.getElementById('tglkas').value;
  param =
    "method=listhutangkas" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&tipe=" +
    tipe +
    "&per=" +
    per;
  param += "&nohutangkas=" + nohutangkas; //+ '&tkkas=' + tkkas
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listhutangkas").style.display = "block";
          document.getElementById("listhutangkas").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailpjd() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nopjd = document.getElementById("nopjd").value;
  // tkkas = document.getElementById('tkkas').value;
  param =
    "method=detailpjd" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nopjd=" + nopjd; //+ '&tkkas=' + tkkas
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailpjd").style.display = "block";
          document.getElementById("detailpjd").innerHTML = con.responseText;
          document.getElementById("prevpjd").disabled = true;
          listpjd();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listpjd(nopdo, unit, per, tipe = "html") {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nopjd = document.getElementById("nopjd").value;
  // tkkas = document.getElementById('tkkas').value;
  //tglkas = document.getElementById('tglkas').value;
  param =
    "method=listpjd" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nopjd=" + nopjd; //+ '&tkkas=' + tkkas
  param += "&tipe=" + tipe; //+ '&tkkas=' + tkkas
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listpjd").style.display = "block";
          document.getElementById("listpjd").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailothers() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  noothers = document.getElementById("noothers").value;
  param =
    "method=detailothers" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&noothers=" + noothers;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailothers").style.display = "block";
          document.getElementById("detailothers").innerHTML = con.responseText;
          document.getElementById("prevothers").disabled = true;
          $(".select2").select2();
          listothers();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listothers(nopdo, unit, per, tipe = "html") {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  noothers = document.getElementById("noothers").value;
  // tkkas = document.getElementById('tkkas').value;
  //tglkas = document.getElementById('tglkas').value;
  param =
    "method=listothers" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&noothers=" + noothers; //+ '&tkkas=' + tkkas
  param += "&tipe=" + tipe; //+ '&tkkas=' + tkkas
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listothers").style.display = "block";
          document.getElementById("listothers").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailtanaman() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  notanaman = document.getElementById("notanaman").value;
  param =
    "method=detailtanaman" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&per=" +
    per;
  param += "&notanaman=" + notanaman;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailtanaman").style.display = "block";
          document.getElementById("detailtanaman").innerHTML = con.responseText;
          document.getElementById("prevtanaman").disabled = true;
          $(".select2").select2();
          listtanaman();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listtanaman(nopdo, unit, per) {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  notanaman = document.getElementById("notanaman").value;
  // tkkas = document.getElementById('tkkas').value;
  //tglkas = document.getElementById('tglkas').value;
  param =
    "method=listtanaman" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&notanaman=" + notanaman; //+ '&tkkas=' + tkkas
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listtanaman").style.display = "block";
          document.getElementById("listtanaman").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailtraksi() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  notraksi = document.getElementById("notraksi").value;
  param =
    "method=detailtraksi" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&notraksi=" + notraksi;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailtraksi").style.display = "block";
          document.getElementById("detailtraksi").innerHTML = con.responseText;
          document.getElementById("prevtraksi").disabled = true;
          $(".select2").select2();
          listtraksi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listtraksi(nopdo, unit, per) {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  notraksi = document.getElementById("notraksi").value;
  // tkkas = document.getElementById('tkkas').value;
  //tglkas = document.getElementById('tglkas').value;
  param =
    "method=listtraksi" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&notraksi=" + notraksi; //+ '&tkkas=' + tkkas
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listtraksi").style.display = "block";
          document.getElementById("listtraksi").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalkas() {
  document.getElementById("prevkas").disabled = false;
  document.getElementById("batalkas").disabled = false;
  document.getElementById("detailkas").style.display = "none";
  document.getElementById("listkas").style.display = "block";
  nokas = document.getElementById("nokas").value = "";
  document.getElementById("listkas").innerHTML = "";
  document.getElementById("detailkas").innerHTML = "";
  batalkontraktor();
  // batalbyrsup();
}

function simpanbyrsup(ttlbrs) {
  for (var i = 1; i <= ttlbrs; i++) {
    if (i == ttlbrs) {
      prosessimpanbyrsup(i, ttlbrs);
    }
    if (
      typeof document.getElementById("jlh_" + i) != "undefined" &&
      document.getElementById("jlh_" + i) != null
    ) {
      prosessimpanbyrsup(i, ttlbrs);
    }
  }
}

function prosessimpanbyrsup(barisnya, ttl) {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nobyrsup = document.getElementById("nobyrsup").value;
  param =
    "method=simpanbyrsup" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nobyrsup=" + nobyrsup;
  if (barisnya == ttl) {
    byrlain = document.getElementById("byrlain").value;

    param += "&byrlain=" + byrlain + "&akhir=iya" + "&idpdo=" + ttl;
  } else {
    tes = document.getElementById("jlh_" + barisnya).innerHTML;
    jlh = tes.replace(/,/gi, "");
    noakun = document.getElementById("noakun_" + barisnya).innerHTML;

    param +=
      "&noakunbyrsup=" +
      noakun +
      "&idpdo=" +
      barisnya +
      "&jumlahbyrsupx=" +
      jlh +
      "&akhir=tidak";
  }
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          if (barisnya == ttl) {
            listbyrsup();
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editbyrsup(
  nopdo,
  nobyrsup,
  tkbyrsup,
  tglbyrsup,
  noakunbyrsup,
  rekeningbankbyrsup,
) {
  document.getElementById("nobyrsup").value = nobyrsup;
  document.getElementById("tkbyrsup").value = tkbyrsup;
  document.getElementById("noakunbyrsup").value = noakunbyrsup;
  document.getElementById("rekeningbankbyrsup").value = rekeningbankbyrsup;
  detailbyrsup();
}

function deletebyrsup(nopdo, nobyrsup, nourutbyrsup) {
  param =
    "method=deletebyrsup" +
    "&nopdo=" +
    nopdo +
    "&notranbyrsup=" +
    nobyrsup +
    "&idpdo=" +
    nourutbyrsup;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listbyrsup(nopdo);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function detailbyrsup() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nobyrsup = document.getElementById("nobyrsup").value;
  param =
    "method=detailbyrsup" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  param += "&nobyrsup=" + nobyrsup;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailbyrsup").style.display = "block";
          document.getElementById("detailbyrsup").innerHTML = con.responseText;
          document.getElementById("prevbyrsup").disabled = true;
          listbyrsup();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listbyrsup(nopdo, unit, per) {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  // nobyrsup = document.getElementById('nobyrsup').value;
  param =
    "method=listbyrsup" + "&nopdo=" + nopdo + "&unit=" + unit + "&per=" + per;
  // param += '&nobyrsup=' + nobyrsup ;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // document.getElementById('listbyrsup').style.display = 'block';
          // document.getElementById('listbyrsup').innerHTML = con.responseText;
          listkontraktor();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalbyrsup() {
  document.getElementById("prevbyrsup").disabled = false;
  document.getElementById("detailbyrsup").style.display = "none";
  document.getElementById("listbyrsup").style.display = "block";
  nobyrsup = document.getElementById("nobyrsup").value = "";
  document.getElementById("listbyrsup").innerHTML = "";
  document.getElementById("detailbyrsup").innerHTML = "";
  batalkontraktor();
}

function editkontraktor(
  nopdo,
  nokontraktor,
  tkkontraktor,
  tglkontraktor,
  noakunkontraktor,
  rekeningbankkontraktor,
) {
  document.getElementById("nokontraktor").value = nokontraktor;
  document.getElementById("tkkontraktor").value = tkkontraktor;
  document.getElementById("noakunkontraktor").value = noakunkontraktor;
  document.getElementById("rekeningbankkontraktor").value =
    rekeningbankkontraktor;
  detailkontraktor();
}

function deletekontraktor(nopdo, nokontraktor, nodocument, nourutkontraktor) {
  param =
    "method=deletekontraktor" +
    "&nopdo=" +
    nopdo +
    "&notransaksi=" +
    nokontraktor +
    "&idpdo=" +
    nourutkontraktor;
  param += "&document=" + nodocument;
  tujuan = "keu_slave_pdo3.php";
  alertify.confirm(
    "Informasi",
    "Anda Yakin Menghapus : " + nopdo + "???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          listkontraktor(nopdo);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function detailkontraktor() {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  sesi = document.getElementById("sesi").value;
  nokontraktor = document.getElementById("nokontraktor").value;
  param =
    "method=detailkontraktor" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&per=" +
    per;
  param += "&nokontraktor=" + nokontraktor;
  param += "&sesi=" + sesi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("detailkontraktor").style.display = "block";
          document.getElementById("detailkontraktor").innerHTML =
            con.responseText;
          document.getElementById("prevkontraktor").disabled = true;
          listkontraktor();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function listkontraktor(nopdo, unit, per, tipe = "html") {
  nopdo = document.getElementById("nopdo").value;
  unit = document.getElementById("unit").value;
  per = document.getElementById("per").value;
  nokontraktor = document.getElementById("nokontraktor").value;
  param =
    "method=listkontraktor" +
    "&nopdo=" +
    nopdo +
    "&unit=" +
    unit +
    "&tipe=" +
    tipe +
    "&per=" +
    per;
  param += "&nokontraktor=" + nokontraktor;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("listkontraktor").style.display = "block";
          document.getElementById("listkontraktor").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalkontraktor() {
  document.getElementById("prevkontraktor").disabled = false;
  document.getElementById("batalkontraktor").disabled = false;
  document.getElementById("prevkontraktor").disabled = false;
  document.getElementById("detailkontraktor").style.display = "none";
  document.getElementById("listkontraktor").style.display = "block";
  nokontraktor = document.getElementById("nokontraktor").value = "";
  document.getElementById("listkontraktor").innerHTML = "";
  document.getElementById("detailkontraktor").innerHTML = "";
  batalhutangkas();
}

function batalsupplier() {
  document.getElementById("prevsupplier").disabled = false;
  document.getElementById("detailsupplier").style.display = "none";
  document.getElementById("listsupplier").style.display = "block";
  nosupplier = document.getElementById("nosupplier").value = "";
  document.getElementById("listsupplier").innerHTML = "";
  document.getElementById("detailsupplier").innerHTML = "";
  batalhutangkas();
}

function batalhutangkas() {
  document.getElementById("prevhutangkas").disabled = false;
  document.getElementById("batalhutangkas").disabled = false;
  document.getElementById("detailhutangkas").style.display = "none";
  document.getElementById("listhutangkas").style.display = "block";
  nohutangkas = document.getElementById("nohutangkas").value = "";
  document.getElementById("listhutangkas").innerHTML = "";
  document.getElementById("detailhutangkas").innerHTML = "";
  batalpjd();
}

function batalpjd() {
  document.getElementById("prevpjd").disabled = false;
  document.getElementById("batalpjd").disabled = false;
  document.getElementById("detailpjd").style.display = "none";
  document.getElementById("listpjd").style.display = "block";
  nopjd = document.getElementById("nopjd").value = "";
  document.getElementById("listpjd").innerHTML = "";
  document.getElementById("detailpjd").innerHTML = "";

  batalothers();
}

function batalothers() {
  document.getElementById("prevothers").disabled = false;
  document.getElementById("batalothers").disabled = false;
  document.getElementById("detailothers").style.display = "none";
  document.getElementById("listothers").style.display = "block";
  noothers = document.getElementById("noothers").value = "";
  document.getElementById("listothers").innerHTML = "";
  document.getElementById("detailothers").innerHTML = "";

  bataltanaman();
}

function bataltanaman() {
  document.getElementById("prevtanaman").disabled = false;
  document.getElementById("detailtanaman").style.display = "none";
  document.getElementById("listtanaman").style.display = "block";
  notanaman = document.getElementById("notanaman").value = "";
  document.getElementById("listtanaman").innerHTML = "";
  document.getElementById("detailtanaman").innerHTML = "";

  bataltraksi();
}

function bataltraksi() {
  document.getElementById("prevtraksi").disabled = false;
  document.getElementById("detailtraksi").style.display = "none";
  document.getElementById("listtraksi").style.display = "block";
  notraksi = document.getElementById("notraksi").value = "";
  document.getElementById("listtraksi").innerHTML = "";
  document.getElementById("detailtraksi").innerHTML = "";
}

function form_ajukan(notransaksi) {
  param = "method=form_ajukan" + "&notransaksi=" + notransaksi;
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          // alertify.alert(con.responseText);
          alertify.alert(con.responseText);
        } else {
          alertify.popup2().destroy();
          alertify
            .popup2("Approval Form", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("40%", "50%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveajukan(notransaksi, jenisapprv, maxaproval) {
  param = "";
  method = "ajukan";
  strper = "";
  for (i = 1; i <= maxaproval; i++) {
    strper +=
      "&persetujuan[" +
      i +
      "]=" +
      trim(document.getElementById("kepada" + i).value);
  }
  param += "&notransaksi=" + notransaksi + "&jenisapprv=" + jenisapprv;
  param += "&maxaproval=" + maxaproval;
  param += "&method=" + method;
  param += strper;
  tujuan = "keu_slave_pdo3.php";

  alertify.confirm(
    "Informasi",
    "Ajukan transaksi : " + notransaksi + " ???",
    function () {
      post_response_text(tujuan, param, respon);
    },
    function () {
      return;
    },
  );

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify.popup2().destroy();
          getpage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getpage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}

function ajukan() {
  kepada = "";
  jumlahlevel = document.getElementById("numrow").value;
  for (var i = 1; i <= jumlahlevel; i++) {
    if (kepada == "") {
      kepada = document.getElementById("kepada" + i).value;
    } else {
      kepada += "###" + document.getElementById("kepada" + i).value;
    }
  }
  notransaksi = document.getElementById("notran_aju").innerHTML;
  jenisapprv = document.getElementById("jenisapprv").value;
  param =
    "method=ajukan" +
    "&notransaksi=" +
    notransaksi +
    "&kepada=" +
    kepada +
    "&jenisapprv=" +
    jenisapprv;
  if (kepada == "") {
    alertify.alert("Isikan nama penyetuju.");
    return;
  }
  tujuan = "keu_slave_pdo3.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Pengajuan PDO Sukses");
          loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//Umar
function showupload(ev, notransaksi) {
  showformupload(ev);

  let param = "method=showupload&notransaksi=" + notransaksi;
  let tujuan = "keu_slave_pdo3.php";

  function response() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contUpload").innerHTML = con.responseText;
          loadfiles(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text(tujuan, param, response);
}

function showformupload(ev) {
  let title = "UPLOAD FILES";
  let width = "";
  let height = "";
  let content =
    "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
  let pos = new Array();
  pos = getMouseP(ev);

  showDialog2(title, content, width, height, ev);

  document.getElementById("dynamic2").style.top = pos[1] + "px";
  document.getElementById("dynamic2").style.left = pos[0] - 300 + "px";
  document.getElementById("dynamic2").style.display = "";
}

function loadfiles(notransaksi) {
  let param = "method=loadfiles&nopdo=" + notransaksi;
  let tujuan = "keu_slave_pdo3.php";

  function response() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
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

  post_response_text(tujuan, param, response);
}

function submitfile(notransaksi) {
  let file = document.getElementById("upload").files[0];

  let formdata = new FormData();

  formdata.append("notransaksi", notransaksi);
  formdata.append("file", file);
  formdata.append("fileupload", document.getElementById("upload").value);

  if (document.getElementById("upload").value == "") {
    alertify.alert("warning : Upload file has been empty.");
    return false;
  }

  let con = createXMLHttpRequest();
  con.open("POST", "keu_slave_pdo3.php?method=submitfile", true);
  busy_on();
  con.onreadystatechange = eval(response);
  con.send(formdata);

  function response() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          //=== Success Response
          alertify.alert("Uploaded Success.");
          document.getElementById("upload").value = "";
          loadfiles(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(notransaksi, namafile) {
  let param =
    "method=deletefile&nopdo=" + notransaksi + "&namafile=" + namafile;
  let tujuan = "keu_slave_pdo3.php";

  function response() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          loadfiles(notransaksi);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }

  post_response_text(tujuan, param, response);
}
//End Umar

function showHideDetails(rows, supplier, total) {
  for (var i = 1; i <= total; i++) {
    key = document.querySelector(`#trdt_${supplier}_${i}`).style.display;
    if (key == "none") {
      document.querySelector(`#trdt_${supplier}_${i}`).style.display = "";
    } else {
      document.querySelector(`#trdt_${supplier}_${i}`).style.display = "none";
    }
  }
}

const detailUpah = (unit, per) => {
  return new Promise((resolve) => {
    param = "method=detailupah" + "&unit=" + unit + "&per=" + per;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listUpah = (nopdo, noupah) => {
  return new Promise((resolve) => {
    param = "method=listupah" + "&nopdo=" + nopdo + "&noupah=" + noupah;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            if (con.responseText != "") {
              document.querySelector(`#batalupah`).disabled = true;
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
};

const detailSupplier = (unit, per) => {
  return new Promise((resolve) => {
    param = "method=detailsupplier" + "&unit=" + unit + "&per=" + per;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listSupplier = (nopdo, nosupplier) => {
  return new Promise((resolve) => {
    param =
      "method=listsupplier" + "&nopdo=" + nopdo + "&nosupplier=" + nosupplier;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const detailTunai = (unit, per) => {
  return new Promise((resolve) => {
    param = "method=detailkas" + "&unit=" + unit + "&per=" + per;
    param += "&sesi=" + document.getElementById("sesi").value;
    param += "&nokas=" + document.getElementById("nokas").value;
    param += "&nopdo=" + document.getElementById("nopdo").value;

    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listTunai = (nopdo, notransaksi) => {
  return new Promise((resolve) => {
    param = "method=listkas" + "&nopdo=" + nopdo + "&nokas=" + notransaksi;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            if (con.responseText != "") {
              document.querySelector(`#batalkas`).disabled = true;
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
};

const detailKontraktor = (unit, per) => {
  return new Promise((resolve) => {
    param = "method=detailkontraktor" + "&unit=" + unit + "&per=" + per;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listKontraktor = (nopdo, notransaksi) => {
  return new Promise((resolve) => {
    param =
      "method=listkontraktor" +
      "&nopdo=" +
      nopdo +
      "&nokontraktor=" +
      notransaksi;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            if (con.responseText != "") {
              document.querySelector(`#batalkontraktor`).disabled = true;
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
};

const detailHutangkas = (unit, per) => {
  return new Promise((resolve) => {
    param = "method=detailhutangkas" + "&unit=" + unit + "&per=" + per;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listHutangkas = (nopdo, notransaksi) => {
  return new Promise((resolve) => {
    param =
      "method=listhutangkas" +
      "&nopdo=" +
      nopdo +
      "&nohutangkas=" +
      notransaksi;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            if (con.responseText != "") {
              document.querySelector(`#batalhutangkas`).disabled = true;
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
};

const detailPjd = (unit, per) => {
  return new Promise((resolve) => {
    param = "method=detailpjd" + "&unit=" + unit + "&per=" + per;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listPjd = (nopdo, notransaksi) => {
  return new Promise((resolve) => {
    param = "method=listpjd" + "&nopdo=" + nopdo + "&nopjd=" + notransaksi;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            if (con.responseText != "") {
              document.querySelector(`#batalpjd`).disabled = true;
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
};

const detailOthers = (unit, per) => {
  return new Promise((resolve) => {
    const noothers = document.querySelector(`#noothers`).value;
    const nopdo = document.querySelector(`#nopdo`).value;
    param =
      "method=detailothers" +
      "&unit=" +
      unit +
      "&per=" +
      per +
      "&noothers=" +
      noothers +
      "&nopdo=" +
      nopdo;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listOthers = (nopdo, notransaksi) => {
  return new Promise((resolve) => {
    param =
      "method=listothers" + "&nopdo=" + nopdo + "&noothers=" + notransaksi;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            if (con.responseText != "") {
              document.querySelector(`#batalothers`).disabled = true;
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
};

const detailTanaman = (unit, per) => {
  return new Promise((resolve) => {
    const notanaman = document.querySelector(`#notanaman`).value;
    const nopdo = document.querySelector(`#nopdo`).value;
    param =
      "method=detailtanaman" +
      "&unit=" +
      unit +
      "&per=" +
      per +
      "&notanaman=" +
      notanaman +
      "&nopdo=" +
      nopdo;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listTanaman = (nopdo, notransaksi) => {
  return new Promise((resolve) => {
    param =
      "method=listtanaman" + "&nopdo=" + nopdo + "&notanaman=" + notransaksi;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const detailTraksi = (unit, per) => {
  return new Promise((resolve) => {
    const notraksi = document.querySelector(`#notraksi`).value;
    const nopdo = document.querySelector(`#nopdo`).value;
    param =
      "method=detailtraksi" +
      "&unit=" +
      unit +
      "&per=" +
      per +
      "&notraksi=" +
      notraksi +
      "&nopdo=" +
      nopdo;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const listTraksi = (nopdo, notransaksi) => {
  return new Promise((resolve) => {
    param =
      "method=listtraksi" + "&nopdo=" + nopdo + "&notraksi=" + notransaksi;
    tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
          } else {
            resolve(con.responseText);
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const cancelsch = () => {
  const sch = document.querySelectorAll(".sch");
  sch.forEach((el) => {
    setValue2(el.id, "");
  });
};

const countBI = (subbagian, tipekaryawan) => {
  const tr = document.querySelector(
    `tr.rowcontent.input[data-subbagian='${subbagian}'][data-tipekaryawan='${tipekaryawan}']`,
  );

  const nilairealisasi = tr.querySelector(`td[data-nilairealisasi]`).dataset
    .nilairealisasi;
  const pengurang = tr.querySelector(`input.pengurang`).checked;
  const plusminus = remove_comma_var(tr.querySelector(`input.plusminus`).value);
  const nilaibi = tr.querySelector(`td.nilaibi`);

  if (pengurang === true) {
    nilaibi.textContent = numberFormat(
      parseFloat(nilairealisasi || 0) - parseFloat(plusminus || 0),
      2,
    );
    nilaibi.dataset.nilaibi =
      parseFloat(nilairealisasi || 0) - parseFloat(plusminus || 0);
  } else {
    nilaibi.textContent = numberFormat(
      parseFloat(nilairealisasi || 0) + parseFloat(plusminus || 0),
      2,
    );
    nilaibi.dataset.nilaibi =
      parseFloat(nilairealisasi || 0) + parseFloat(plusminus || 0);
  }

  totalPlusMinus(subbagian);
};

const totalPlusMinus = (subbagian) => {
  const trInput = document.querySelectorAll(
    `tr.rowcontent.input[data-subbagian='${subbagian}']`,
  );
  let totalPlusMinus = 0;
  trInput.forEach((el) => {
    const plusminus = remove_comma_var(
      el.querySelector(`input.plusminus`).value,
    );
    totalPlusMinus += parseFloat(plusminus || 0);
  });

  const trTotal = document.querySelector(
    `tr.rowcontent.total[data-subbagian='${subbagian}']`,
  );
  trTotal.querySelector(`td.totalpengurang`).textContent = numberFormat(
    totalPlusMinus,
    2,
  );

  totalBI(subbagian);
};

const totalBI = (subbagian) => {
  const trInput = document.querySelectorAll(
    `tr.rowcontent.input[data-subbagian='${subbagian}']`,
  );
  let totalNilaiBI = 0;
  trInput.forEach((el) => {
    const nilaibi = el.querySelector(`td.nilaibi`).dataset.nilaibi;
    totalNilaiBI += parseFloat(nilaibi || 0);
  });

  const trTotal = document.querySelector(
    `tr.rowcontent.total[data-subbagian='${subbagian}']`,
  );
  trTotal.querySelector(`td.totalnilaibi`).textContent = numberFormat(
    totalNilaiBI,
    2,
  );
};

const saveupah = () => {
  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailupah`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const noupah = getValue("noupah");

  let data = "";
  tr.forEach((el) => {
    const nilaibi = el.querySelector(`.nilaibi`).dataset.nilaibi.trim();
    const tipekaryawan = el.dataset.tipekaryawan.trim();
    const jlhHk = el.dataset.jlhhk.trim();
    const jlhTk = el.dataset.jlhtk.trim();
    const subbagian = el.dataset.subbagian.trim();
    const keterangan = el.querySelector(`.keterangan`).value;
    const nilairealisasi = el
      .querySelector(`.nilairealisasi`)
      .dataset.nilairealisasi.trim();
    const plusminus = el.querySelector(`.plusminus`).value;

    // Looping data biaya komponen
    const listKomponenGaji = el.querySelectorAll(`.komponengaji_rp`);
    const arrKomponenGaji = {};
    listKomponenGaji.forEach((element) => {
      const rpKomponenGaji = parseFloat(remove_comma_var(element.value));
      const idKomponenGaji = element.dataset.idkomponen;

      if (rpKomponenGaji != 0) {
        arrKomponenGaji[idKomponenGaji] = rpKomponenGaji;
      }
    });

    if (nilaibi != "") {
      data += `${subbagian}##${tipekaryawan}##${nilaibi}##${keterangan}##${nilairealisasi}##${plusminus}##${unit}##${jlhHk}##${jlhTk}##${JSON.stringify(
        arrKomponenGaji,
      )}$$`;
    }
  });

  param =
    "method=saveupah" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&noupah=" +
    noupah;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, uph] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("noupah").value = uph.trim();

          document.getElementById("listupah").style.display = "block";
          document.getElementById("listupah").innerHTML = await listUpah(
            npd.trim(),
            uph.trim(),
          );

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO Upah");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const cancelValue = (...params) => {
  params.forEach((param) => {
    const element = document.getElementById(param);
    if (element) {
      if (element.type == "checkbox") {
        element.checked = false;
      } else {
        setValue2(element.id, "");
      }
    } else {
      console.error(`Elemen dengan id ${param} tidak ditemukan.`);
    }
  });
};

const disableValue = (...params) => {
  params.forEach((param) => {
    const element = document.getElementById(param);
    if (element) {
      element.disabled = true;
    } else {
      console.error(`Elemen dengan id ${param} tidak ditemukan.`);
    }
  });
};

const unDisabledValue = (...params) => {
  params.forEach((param) => {
    const element = document.getElementById(param);
    if (element) {
      element.disabled = false;
    } else {
      console.error(`Elemen dengan id ${param} tidak ditemukan.`);
    }
  });
};

const checkPemby = () => {
  const checks = document.querySelectorAll(".checkPemby");
  let arrRows = [];
  checks.forEach((items) => {
    if (items.checked) {
      let rows = items.parentNode.closest(".rowcontent");
      let biayaBI = parseFloat(
        remove_comma_var(rows.querySelector(".sdbikas").value),
      );
      arrRows.push(biayaBI);
    }
  });

  countTotalKas(arrRows);
};

const countTotalKas = (arr) => {
  const totalKas = document.querySelector("#totalKas");
  var sum = arr.reduce((accumulator, currentValue) => {
    return accumulator + currentValue;
  }, 0);
  totalKas.value = parseFloat(sum);
};

const checkHutangkas = () => {
  const checks = document.querySelectorAll(".checkHutangkas");
  let arrRows = [];
  checks.forEach((items) => {
    if (items.checked) {
      let rows = items.parentNode.closest(".rowcontent");
      let biayaBI = parseFloat(
        remove_comma_var(rows.querySelector(".sdbihutangkas").value),
      );
      arrRows.push(biayaBI);
    }
  });

  countTotalHutangkas(arrRows);
};

const countTotalHutangkas = (arr) => {
  const totalKas = document.querySelector("#totalHutangkas");
  var sum = arr.reduce((accumulator, currentValue) => {
    return accumulator + currentValue;
  }, 0);
  totalKas.value = parseFloat(sum);
};

const addRows = async (button) => {
  var tr = button.closest("tr");
  var clone = tr.cloneNode(true);

  var inputs = clone.querySelectorAll("input");
  inputs.forEach(function (input) {
    input.value = "";
  });

  var parentNoakun = clone.querySelector(".noakun").parentNode;
  parentNoakun.innerHTML = "";

  var parentTipekasbank = clone.querySelector(".tipekasbank").parentNode;
  parentTipekasbank.innerHTML = "";

  var newOptionAkun = document.createElement("select");
  newOptionAkun.classList.add("noakun");
  newOptionAkun.style.width = "220px";

  var newOptionTipekasbank = document.createElement("select");
  newOptionTipekasbank.classList.add("tipekasbank");
  newOptionTipekasbank.style.width = "120px";

  // Populate Options
  await getAkunBiaya(newOptionAkun);
  await getTipekasbank(newOptionTipekasbank);

  parentNoakun.appendChild(newOptionAkun);
  parentTipekasbank.appendChild(newOptionTipekasbank);

  $(newOptionAkun).select2();
  $(newOptionTipekasbank).select2();

  tr.parentNode.insertBefore(clone, tr.nextSibling);
};

const getAkunBiaya = (select) => {
  return new Promise((resolve, reject) => {
    const unit = document.querySelector("#unit").value;
    let param = `method=getAkunBiaya&unit=${unit}`;
    let tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respon);

    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert(
              "Informasi",
              "ERROR TRANSACTION,\n" + con.responseText,
            );
            reject("Rejected");
          } else {
            select.innerHTML = con.responseText;
            resolve("Success");
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const getTipekasbank = (select) => {
  return new Promise((resolve, reject) => {
    let objTipeKasbank = { KAS: "KAS", BANK: "BANK" };
    select.innerHTML = `<option value="">Pilih Data</option>`;
    for (const [value, text] of Object.entries(objTipeKasbank)) {
      const option = document.createElement("option");
      option.value = value;
      option.textContent = text;
      select.appendChild(option);
    }
    resolve("Success");
  });
};

const deleteRows = (button) => {
  var tr = button.closest("tr");
  var rowCount = document.querySelectorAll("tr.manual-rows").length;
  if (rowCount > 1) {
    tr.parentNode.removeChild(tr);
  } else {
    alertify.alert("Informasi", "Tidak bisa hapus baris terakhir!");
  }
};

const savekas = () => {
  // Validasi jika belum ada yang dicentang
  let checkedKas = 0;
  const checkboxkas = document.querySelectorAll(".checkPemby");
  checkboxkas.forEach((el) => {
    if (el.checked) {
      checkedKas += 1;
    }
  });

  if (checkedKas == 0) {
    alertify.alert("Informasi", "Belum ada kasbank yang dicentang!");
    return;
  }

  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailkas`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const nokas = getValue("nokas");

  let data = "";
  tr.forEach((el) => {
    const rpreal = remove_comma_var(el.querySelector(`.rpreal`).innerHTML);
    const rupiah = remove_comma_var(el.querySelector(`.plusminus`).value);
    const nilaibi = remove_comma_var(el.querySelector(`.sdbikas`).value);
    const novoucher = el.querySelector(`.novoucher`).innerHTML.trim();
    const noakun = el.querySelector(`.noakun`).value;
    const keterangan = el.querySelector(`.keterangan`).value;
    const checkPemby = el.querySelector(`.checkPemby`);
    const tipekasbank = el.querySelector(`.tipekasbank`).value;
    const isManual = el.querySelector(`.ismanual`).value;

    if (el.classList.contains("input-manual")) {
      if (tipekasbank == "" || tipekasbank == null) {
        alertify.alert(
          "Informasi",
          "Tipe Kas/Bank tidak boleh kosong untuk input manual!",
        );
        return;
      }
    }

    if (nilaibi != 0 && checkPemby.checked) {
      if (keterangan == "") {
        alertify.alert("Keterangan tidak boleh kosong");
        return;
      }
      if (noakun == "") {
        alertify.alert("Nomor Akun tidak boleh kosong");
        return;
      }

      data += `${noakun}##${nilaibi}##${keterangan}##${rpreal}##${rupiah}##${novoucher}##${tipekasbank}##${isManual}$$`;
    }
  });

  param =
    "method=savekas" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&nokas=" +
    nokas;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, kas] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("nokas").value = kas.trim();

          document.getElementById("listkas").style.display = "block";
          document.getElementById("listkas").innerHTML = await listTunai(
            npd.trim(),
            kas.trim(),
          );

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO Kas");

          const checkboxes = document.querySelectorAll(`.checkPemby`);
          const check = false;

          checkboxes.forEach((el) => {
            el.checked = check;
          });

          div.querySelector("#checkAll").checked = false;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const savekontraktor = () => {
  // Validasi jika belum ada yang dicentang
  let checkedkontrak = 0;
  const checkboxkontrak = document.querySelectorAll(".checkKontrak");
  checkboxkontrak.forEach((el) => {
    if (el.checked) {
      checkedkontrak += 1;
    }
  });

  if (checkedkontrak == 0) {
    alertify.alert("Informasi", "Belum ada Kontrak yang dicentang!");
    return;
  }

  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailkontraktor`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const nokontraktor = getValue("nokontraktor");

  let data = "";
  tr.forEach((el) => {
    const noinvoice = el.querySelector(`.noinvoice`).innerHTML;
    const nopo = el.querySelector(`.nopo`).innerHTML;
    const nilaiinvoice = remove_comma_var(
      el.querySelector(`.nilaiinvoice`).innerHTML,
    );
    const tipeinvoice = el.querySelector(`.tipeinvoice`).value;
    const kodesupplier = el.querySelector(`.kodesupplier`).value;
    const kodekegiatan = el.querySelector(`.kodekegiatan`).value;
    const checkKontrak = el.querySelector(`.checkKontrak`);

    if ((nilaiinvoice != 0 || nilaiinvoice != "") && checkKontrak.checked) {
      data += `${noinvoice}##${nopo}##${tipeinvoice}##${kodesupplier}##${nilaiinvoice}##${kodekegiatan}$$`;
    }
  });

  param =
    "method=savekontraktor" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&nokontraktor=" +
    nokontraktor;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, ktrk] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("nokontraktor").value = ktrk.trim();

          document.getElementById("listkontraktor").style.display = "block";
          document.getElementById("listkontraktor").innerHTML =
            await listKontraktor(npd.trim(), ktrk.trim());

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO Kontraktor");

          const checkboxes = document.querySelectorAll(`.checkKontrak`);
          const check = false;

          checkboxes.forEach((el) => {
            el.checked = check;
          });

          div.querySelector("#checkAll").checked = false;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const savesupplier = () => {
  // Validasi jika belum ada yang dicentang
  let checkedSupplier = 0;
  const checkboxSupplier = document.querySelectorAll(".checkSupplier");
  checkboxSupplier.forEach((el) => {
    if (el.checked) {
      checkedSupplier += 1;
    }
  });

  if (checkedSupplier == 0) {
    alertify.alert("Informasi", "Belum ada Supplier yang dicentang!");
    return;
  }

  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailsupplier`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const nosupplier = getValue("nosupplier");

  let data = "";
  tr.forEach((el) => {
    const noinvoice = el.querySelector(`.noinvoice`).innerHTML;
    const nopo = el.querySelector(`.nopo`).innerHTML;
    const nilaiinvoice = remove_comma_var(
      el.querySelector(`.nilaiinvoice`).innerHTML,
    );
    const tipeinvoice = el.querySelector(`.tipeinvoice`).value;
    const kodesupplier = el.querySelector(`.kodesupplier`).value;
    const kodekegiatan = el.querySelector(`.kodekegiatan`).value;
    const nogrn = el.querySelector(`.nogrn`).innerHTML.trim();
    const checkSupplier = el.querySelector(`.checkSupplier`);

    if ((nilaiinvoice != 0 || nilaiinvoice != "") && checkSupplier.checked) {
      data += `${noinvoice}##${nopo}##${tipeinvoice}##${kodesupplier}##${nilaiinvoice}##${kodekegiatan}##${nogrn}$$`;
    }
  });

  param =
    "method=savesupplier" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&nosupplier=" +
    nosupplier;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, supp] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("nosupplier").value = supp.trim();

          document.getElementById("listsupplier").style.display = "block";
          document.getElementById("listsupplier").innerHTML =
            await listSupplier(npd.trim(), supp.trim());

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO Supplier");

          const checkboxes = document.querySelectorAll(`.checkSupplier`);
          const check = false;

          checkboxes.forEach((el) => {
            el.checked = check;
          });

          div.querySelector("#checkAll").checked = false;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const savehutangkas = () => {
  // Validasi jika belum ada yang dicentang
  let checkedKas = 0;
  const checkboxkas = document.querySelectorAll(".checkHutangkas");
  checkboxkas.forEach((el) => {
    if (el.checked) {
      checkedKas += 1;
    }
  });

  if (checkedKas == 0) {
    alertify.alert("Informasi", "Belum ada Hutang kas yang dicentang!");
    return;
  }

  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailhutangkas`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const nohutangkas = getValue("nohutangkas");

  let data = "";
  tr.forEach((el) => {
    const rpreal = remove_comma_var(el.querySelector(`.rpreal`).innerHTML);
    const rupiah = remove_comma_var(el.querySelector(`.plusminus`).value);
    const nilaibi = remove_comma_var(el.querySelector(`.sdbihutangkas`).value);
    const noakun = el.querySelector(`.noakun`).value;
    const keterangan = el.querySelector(`.keterangan`).value;
    const checkPemby = el.querySelector(`.checkHutangkas`);

    if (nilaibi != 0 && checkPemby.checked) {
      data += `${noakun}##${nilaibi}##${rpreal}##${rupiah}##${keterangan}$$`;
    }
  });

  param =
    "method=savehutangkas" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&nohutangkas=" +
    nohutangkas;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, kas] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("nohutangkas").value = kas.trim();

          document.getElementById("listhutangkas").style.display = "block";
          document.getElementById("listhutangkas").innerHTML =
            await listHutangkas(npd.trim(), kas.trim());

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO Hutang Kas");

          const checkboxes = document.querySelectorAll(`.checkHutangkas`);
          const check = false;

          checkboxes.forEach((el) => {
            el.checked = check;
          });

          div.querySelector("#checkAll").checked = false;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const savepjd = () => {
  // Validasi jika belum ada yang dicentang
  let checkedPjd = 0;
  const checkboxPjd = document.querySelectorAll(".checkPjd");
  checkboxPjd.forEach((el) => {
    if (el.checked) {
      checkedPjd += 1;
    }
  });

  if (checkedPjd == 0) {
    alertify.alert("Informasi", "Belum ada Perjalanan Dinas yang dicentang!");
    return;
  }

  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailpjd`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const nopjd = getValue("nopjd");

  let data = "";
  tr.forEach((el) => {
    const nilaibi = remove_comma_var(el.querySelector(`.sdbipjd`).value);
    const noakun = el.querySelector(`.noakun`).value;
    const tanggal = el.querySelector(`.tanggal`).innerHTML;
    const notransaksi = el.querySelector(`.notransaksi`).innerHTML;
    const checkPemby = el.querySelector(`.checkPjd`);
    const rpreal = remove_comma_var(el.querySelector(`.rpreal`).innerHTML);
    const rupiah = remove_comma_var(el.querySelector(`.plusminus`).value);

    if (nilaibi != 0 && checkPemby.checked) {
      data += `${noakun}##${nilaibi}##${rpreal}##${rupiah}##${tanggal}##${notransaksi}$$`;
    }
  });

  param =
    "method=savepjd" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&nopjd=" +
    nopjd;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, kas] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("nopjd").value = kas.trim();

          document.getElementById("listpjd").style.display = "block";
          document.getElementById("listpjd").innerHTML = await listPjd(
            npd.trim(),
            kas.trim(),
          );

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO Perjalanan Dinas");

          const checkboxes = document.querySelectorAll(`.checkPjd`);
          const check = false;

          checkboxes.forEach((el) => {
            el.checked = check;
          });

          div.querySelector("#checkAll").checked = false;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const addRowsOthers = async (button) => {
  var tr = button.closest("tr");
  var clone = tr.cloneNode(true);

  var inputs = clone.querySelectorAll("input");
  inputs.forEach(function (input) {
    input.value = "";
  });

  var selectParent = clone.querySelector(".noakun").parentNode;
  selectParent.innerHTML = "";

  var newSelect = document.createElement("select");
  newSelect.classList.add("noakun");
  newSelect.style.width = "98%";

  // Populate Nomor AKun
  await getAkunOthers(newSelect);

  selectParent.appendChild(newSelect);

  $(newSelect).select2();

  tr.parentNode.insertBefore(clone, tr.nextSibling);
};

const getAkunOthers = (select) => {
  return new Promise((resolve, reject) => {
    const unit = document.querySelector("#unit").value;
    let param = `method=getAkunOthers&unit=${unit}`;
    let tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respon);

    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert(
              "Informasi",
              "ERROR TRANSACTION,\n" + con.responseText,
            );
            reject("Rejected");
          } else {
            select.innerHTML = con.responseText;
            resolve("Success");
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const deleteRowsOthers = (button) => {
  var tr = button.closest("tr");
  var rowCount = document.querySelectorAll("tr.manual-rows2").length;
  if (rowCount > 1) {
    tr.parentNode.removeChild(tr);
  } else {
    alertify.alert("Informasi", "Tidak bisa hapus baris terakhir!");
  }
};

const saveothers = () => {
  // Validasi jika belum ada yang dicentang
  let checkedOthers = 0;
  const checkboxOthers = document.querySelectorAll(".checkPemby");
  checkboxOthers.forEach((el) => {
    if (el.checked) {
      checkedOthers += 1;
    }
  });

  if (checkedOthers == 0) {
    alertify.alert("Informasi", "Belum ada PMK Lainnya yang dicentang!");
    return;
  }

  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailothers`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const noothers = getValue("noothers");

  let data = "";
  tr.forEach((el) => {
    const nilaibi = remove_comma_var(el.querySelector(`.sdbiothers`).value);
    const noakun = el.querySelector(`.noakun`).value;
    const keterangan = el.querySelector(`.keterangan`).value;
    const checkOthers = el.querySelector(`.checkPemby`);

    if (nilaibi != 0 && checkOthers.checked) {
      if (keterangan == "") {
        alertify.alert("Keterangan tidak boleh kosong");
        return;
      }
      if (noakun == "") {
        alertify.alert("Nomor Akun tidak boleh kosong");
        return;
      }

      data += `${noakun}##${nilaibi}##${keterangan}$$`;
    }
  });

  param =
    "method=saveothers" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&noothers=" +
    noothers;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, others] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("noothers").value = others.trim();

          document.getElementById("listothers").style.display = "block";
          document.getElementById("listothers").innerHTML = await listOthers(
            npd.trim(),
            others.trim(),
          );

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO PMK Lainnya");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const savetanaman = () => {
  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailtanaman`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const notanaman = getValue("notanaman");

  let data = "";
  tr.forEach((el) => {
    const rpreal = remove_comma_var(el.querySelector(`.rpreal`).innerHTML);
    const nilaibi = remove_comma_var(el.querySelector(`.sdbitanaman`).value);
    const noakun = remove_comma_var(el.querySelector(`.noakun`).value);
    const potbpjs = remove_comma_var(el.querySelector(`.potbpjs`).value);
    const potalatpnn = remove_comma_var(el.querySelector(`.potalatpnn`).value);
    const potpenalty = remove_comma_var(el.querySelector(`.potpenalty`).value);
    const potkontanan = remove_comma_var(
      el.querySelector(`.potkontanan`).value,
    );

    if (nilaibi != 0) {
      data += `${noakun}##${nilaibi}##${rpreal}##${potbpjs}##${potalatpnn}##${potpenalty}##${potkontanan}$$`;
    }
  });

  param =
    "method=savetanaman" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&notanaman=" +
    notanaman;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, tanaman] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("notanaman").value = tanaman.trim();

          document.getElementById("listtanaman").style.display = "block";
          document.getElementById("listtanaman").innerHTML = await listTanaman(
            npd.trim(),
            tanaman.trim(),
          );

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO PMK Tanaman");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const addRowsTraksi = async (button) => {
  var tr = button.closest("tr");
  var clone = tr.cloneNode(true);

  var lastIndex = parseInt(clone.querySelector("input").id.match(/\d+$/)[0]);

  var newIndex = lastIndex + 1;

  clone.querySelector("input").id = "sdbitraksi_" + newIndex;

  clone
    .querySelector("input")
    .setAttribute(
      "onkeyup",
      "z.numberFormat('sdbitraksi_" + newIndex + "', 2);",
    );

  clone.querySelector("input").value = "";

  var selectParent = clone.querySelector(".noakun").parentNode;
  selectParent.innerHTML = "";

  var newSelect = document.createElement("select");
  newSelect.classList.add("noakun");
  newSelect.style.width = "98%";

  // Populate Nomor AKun
  await getAkunTraksi(newSelect);

  selectParent.appendChild(newSelect);

  $(newSelect).select2();

  tr.parentNode.insertBefore(clone, tr.nextSibling);
};

const getAkunTraksi = (select) => {
  return new Promise((resolve, reject) => {
    const unit = document.querySelector("#unit").value;
    let param = `method=getAkunTraksi&unit=${unit}`;
    let tujuan = "keu_slave_pdo3.php";
    post_response_text(tujuan, param, respon);

    function respon() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert(
              "Informasi",
              "ERROR TRANSACTION,\n" + con.responseText,
            );
            reject("Rejected");
          } else {
            select.innerHTML = con.responseText;
            resolve("Success");
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  });
};

const deleteRowsTraksi = (button) => {
  var tr = button.closest("tr");
  var rowCount = document.querySelectorAll("tr.manual-rows3").length;
  if (rowCount > 1) {
    tr.parentNode.removeChild(tr);
  } else {
    alertify.alert("Informasi", "Tidak bisa hapus baris terakhir!");
  }
};

const savetraksi = () => {
  const per = getValue("per");
  const sesi = getValue("sesi");
  const unit = getValue("unit");
  const div = document.getElementById(`detailtraksi`);
  const tr = div.querySelectorAll(`tr.rowcontent.input`);
  const nopdo = getValue("nopdo");
  const notraksi = getValue("notraksi");

  let data = "";
  tr.forEach((el) => {
    const rpreal = remove_comma_var(el.querySelector(`.rpreal`).innerHTML);
    const nilaibi = remove_comma_var(el.querySelector(`.sdbitraksi`).value);
    const noakun = remove_comma_var(el.querySelector(`.noakun`).value);
    const rupiah = remove_comma_var(el.querySelector(`.plusminus`).value);

    if (nilaibi != 0) {
      data += `${noakun}##${nilaibi}##${rpreal}##${rupiah}$$`;
    }
  });

  param =
    "method=savetraksi" +
    "&data=" +
    data.slice(0, -2) +
    "&per=" +
    per +
    "&sesi=" +
    sesi +
    "&unit=" +
    unit +
    "&nopdo=" +
    nopdo +
    "&notraksi=" +
    notraksi;
  tujuan = "keu_slave_pdo3.php";
  if (data != "") {
    post_response_text(tujuan, param, respog);
  }
  async function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          const [npd, traksi] = con.responseText.split("##");
          document.getElementById("nopdo").value = npd.trim();
          document.getElementById("notraksi").value = traksi.trim();

          document.getElementById("listtraksi").style.display = "block";
          document.getElementById("listtraksi").innerHTML = await listTraksi(
            npd.trim(),
            traksi.trim(),
          );

          alertify.popup2().destroy();
          alertify.set("notifier", "position", "top-right");
          alertify.set("notifier", "delay", 2);
          alertify.success("Berhasil Simpan PDO PMK Traksi");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
};

const toggleCheck = (headerCheckbox, elementClass) => {
  const checkboxes = document.querySelectorAll(`.${elementClass}`);

  checkboxes.forEach((checkbox) => {
    checkbox.checked = headerCheckbox.checked;
  });
};

const calcBIKas = () => {
  const container = document.querySelector(`#detailkas`);
  const nilaiBI = container.querySelectorAll(`.sdbikas`);
  const labelTotal = container.querySelector(`.totalnilaibi`);

  let totalBI = 0;
  nilaiBI.forEach((el) => {
    const valBI = remove_comma_var(el.value);
    totalBI += parseFloat(valBI || 0);
  });

  labelTotal.textContent = numberFormat(totalBI, 2);
};

const calcBIOthers = () => {
  const container = document.querySelector(`#detailothers`);
  const nilaiBI = container.querySelectorAll(`.sdbiothers`);
  const labelTotal = container.querySelector(`.totalnilaibi`);

  let totalBI = 0;
  nilaiBI.forEach((el) => {
    const valBI = remove_comma_var(el.value);
    totalBI += parseFloat(valBI || 0);
  });

  labelTotal.textContent = numberFormat(totalBI, 2);
};

const calcBITraksi = () => {
  const container = document.querySelector(`#detailtraksi`);
  const nilaiBI = container.querySelectorAll(`.sdbitraksi`);
  const labelTotal = container.querySelector(`.totalnilaibi`);

  let totalBI = 0;
  nilaiBI.forEach((el) => {
    const valBI = remove_comma_var(el.value);
    totalBI += parseFloat(valBI || 0);
  });

  labelTotal.textContent = numberFormat(totalBI, 2);
};

const calcBIHutangkas = () => {
  const container = document.querySelector(`#detailhutangkas`);
  const nilaiBI = container.querySelectorAll(`.sdbihutangkas`);
  const labelTotal = container.querySelector(`.totalnilaibi`);

  let totalBI = 0;
  nilaiBI.forEach((el) => {
    const valBI = remove_comma_var(el.value);
    totalBI += parseFloat(valBI || 0);
  });

  labelTotal.textContent = numberFormat(totalBI, 2);
};

const calcBIUpah = (subbagian, tipekaryawan) => {
  const container = document.querySelector(`#detailupah`);
  const row = container.querySelector(
    `tr.rowcontent.input[data-subbagian='${subbagian}'][data-tipekaryawan='${tipekaryawan}']`,
  );
  const nilaiBI = row.querySelectorAll(`.komponengaji_rp`);
  const labelTotal = row.querySelector(`.nilaibi`);

  let totalBI = 0;
  nilaiBI.forEach((el) => {
    const valBI = remove_comma_var(el.value);
    totalBI += parseFloat(valBI || 0);
  });

  labelTotal.textContent = numberFormat(totalBI, 2);
};

const autoChecked = (el) => {
  const checkPemby = el.closest("tr").querySelector(`.checkPemby`);
  const nilaibi = parseFloat(el.value);

  if (nilaibi > 0) {
    checkPemby.checked = true;
  } else {
    checkPemby.checked = false;
  }
};

function numberOnly(input) {
  if (input.value === "") {
    input.value = "0";
    return;
  }

  input.value = input.value.replace(/[^-?\d]/g, "");

  if (
    input.value.indexOf("-") > 0 ||
    (input.value.indexOf("-") === 0 && input.value.indexOf("-", 1) > 0)
  ) {
    input.value = input.value.slice(0, -1);
  }

  if (
    input.value.length > 1 &&
    input.value[0] === "0" &&
    input.value[1] !== "."
  ) {
    input.value = input.value.slice(1);
  }
}

const calcPlusMinus = (el, sdbi) => {
  const parent = el.closest("tr");
  const checkPengurang = parent.querySelector(`.checkPengurang`);
  const rupiah = remove_comma_var(parent.querySelector(`.rpreal`).innerHTML);
  var plusminus =
    parent.querySelector(`.plusminus`).value == ""
      ? 0
      : remove_comma_var(parent.querySelector(`.plusminus`).value);
  if (checkPengurang.checked) plusminus = plusminus * -1;

  const total = parseFloat(rupiah) + parseFloat(plusminus);
  parent.querySelector(`.${sdbi}`).value = numberFormat(total, 2);
};

const calcBITanaman = (el) => {
  const parent = el.closest("tr");
  const rupiah = remove_comma_var(parent.querySelector(`.rpreal`).innerHTML);
  const potbpjs = remove_comma_var(parent.querySelector(`.potbpjs`).value);
  const potalatpnn = remove_comma_var(
    parent.querySelector(`.potalatpnn`).value,
  );
  const potpenalty = remove_comma_var(
    parent.querySelector(`.potpenalty`).value,
  );
  const potkontanan = remove_comma_var(
    parent.querySelector(`.potkontanan`).value,
  );

  const total =
    parseFloat(rupiah) -
    parseFloat(potbpjs) -
    parseFloat(potalatpnn) -
    parseFloat(potpenalty) -
    parseFloat(potkontanan);
  parent.querySelector(`.sdbitanaman`).value = numberFormat(total, 2);

  // Grand Total Potongan
  const potbpjsEl = document.querySelectorAll(`.potbpjs`);
  const potalatpnnEl = document.querySelectorAll(`.potalatpnn`);
  const potpenaltyEl = document.querySelectorAll(`.potpenalty`);
  const potkontananEl = document.querySelectorAll(`.potkontanan`);

  var potbpjsTtl = 0,
    potalatpnnTtl = 0,
    potpenaltyTtl = 0,
    potkontananTtl = 0;
  potbpjsEl.forEach((item) => {
    value = parseFloat(remove_comma_var(item.value));
    potbpjsTtl += value;
  });
  potalatpnnEl.forEach((item) => {
    value = parseFloat(remove_comma_var(item.value));
    potalatpnnTtl += parseFloat(value);
  });
  potpenaltyEl.forEach((item) => {
    value = parseFloat(remove_comma_var(item.value));
    potpenaltyTtl += parseFloat(value);
  });
  potkontananEl.forEach((item) => {
    value = parseFloat(remove_comma_var(item.value));
    potkontananTtl += parseFloat(value);
  });
  document.querySelector(`.totalnilaipotbpjs`).innerHTML = numberFormat(
    potbpjsTtl,
    2,
  );
  document.querySelector(`.totalnilaipotalatpnn`).innerHTML = numberFormat(
    potalatpnnTtl,
    2,
  );
  document.querySelector(`.totalnilaipotpenalty`).innerHTML = numberFormat(
    potpenaltyTtl,
    2,
  );
  document.querySelector(`.totalnilaipotkontanan`).innerHTML = numberFormat(
    potkontananTtl,
    2,
  );

  calcGrandBITanaman();
};

const calcGrandBITanaman = () => {
  const container = document.querySelector(`#detailtanaman`);
  const sdbi = container.querySelectorAll(`.sdbitanaman`);

  var grandBi = 0;
  sdbi.forEach((item) => {
    grandBi += parseFloat(remove_comma_var(item.value));
  });

  container.querySelector(`.totalnilaibi`).innerHTML = numberFormat(grandBi, 2);
};

const valueRupiahReal = (el) => {
  const parent = el.closest("tr");
  const rupiahreal = parent.querySelector(`.rpreal`);
  rupiahreal.innerHTML = el.value;
};

const generateExcel = (tipepdo, ev) => {
  nopdo = document.querySelector(`#nopdo`).value;
  let param = `nopdo=${nopdo}&tipex=excel`;
  let countInvalid = 0;
  switch (tipepdo) {
    case "UPAH":
      method = "detailupahv2";
      unit = document.querySelector(`#unit`).value;
      periode = document.querySelector(`#per`).value;
      param += `&unit=${unit}&per=${periode}`;

      detailupah = document.querySelector(`#detailupah`);
      listupah = document.querySelector(`#listupah`);

      if (
        detailupah.childElementCount == 0 &&
        unit == "" &&
        periode == "" &&
        nopdo == ""
      ) {
        countInvalid += 1;
      }
      break;
    case "KAS":
      method = "listkas";
      notransaksi = document.querySelector(`#nokas`).value;
      param += `&nokas=${notransaksi}`;

      detailkas = document.querySelector(`#detailkas`);
      listkas = document.querySelector(`#listkas`);

      if (
        detailkas.childElementCount == 0 &&
        listkas.childElementCount == 0 &&
        nopdo == "" &&
        notransaksi == ""
      ) {
        countInvalid += 1;
      }
      break;
    case "HTGKAS":
      method = "listhutangkas";
      notransaksi = document.querySelector(`#nohutangkas`).value;
      param += `&nohutangkas=${notransaksi}`;

      detailhutangkas = document.querySelector(`#detailhutangkas`);
      listhutangkas = document.querySelector(`#listhutangkas`);

      if (
        detailhutangkas.childElementCount == 0 &&
        listhutangkas.childElementCount == 0 &&
        nopdo == "" &&
        notransaksi == ""
      ) {
        countInvalid += 1;
      }
      break;
    case "KTRK":
      method = "listkontraktor";
      notransaksi = document.querySelector(`#nokontraktor`).value;
      param += `&nokontraktor=${notransaksi}`;

      detailkontraktor = document.querySelector(`#detailkontraktor`);
      listkontraktor = document.querySelector(`#listkontraktor`);

      if (
        detailkontraktor.childElementCount == 0 &&
        listkontraktor.childElementCount == 0 &&
        nopdo == "" &&
        notransaksi == ""
      ) {
        countInvalid += 1;
      }
      break;
    case "PJD":
      method = "listpjd";
      notransaksi = document.querySelector(`#nopjd`).value;
      param += `&nopjd=${notransaksi}`;

      detailpjd = document.querySelector(`#detailpjd`);
      listpjd = document.querySelector(`#listpjd`);

      if (
        detailpjd.childElementCount == 0 &&
        listpjd.childElementCount == 0 &&
        nopdo == "" &&
        notransaksi == ""
      ) {
        countInvalid += 1;
      }
      break;
    case "OTH":
      method = "listothers";
      notransaksi = document.querySelector(`#noothers`).value;
      param += `&noothers=${notransaksi}`;

      detailothers = document.querySelector(`#detailothers`);
      listothers = document.querySelector(`#listothers`);

      if (
        detailothers.childElementCount == 0 &&
        listothers.childElementCount == 0 &&
        nopdo == "" &&
        notransaksi == ""
      ) {
        countInvalid += 1;
      }
      break;
  }
  param += `&method=${method}`;

  let tujuan = "keu_slave_pdo3.php";
  title = "Report Ms.Excel";
  if (countInvalid > 0) {
    alertify.alert("Informasi", "Generate dan Simpan data terlebih dahulu");
    return;
  }
  printnopopup(tujuan + "?" + param);
};
