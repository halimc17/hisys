function showheader() {
  if (document.getElementById("tableheader").style.display == "none") {
    document.getElementById("tableheader").style.display = "block";
    document.getElementById("showhead").innerHTML = "Hide Filter";
    document.getElementById("tombolexport").style.display = "none";
  } else {
    document.getElementById("tableheader").style.display = "none";
    document.getElementById("tombolexport").style.display = "block";
    document.getElementById("showhead").innerHTML = "Show Filter";
  }
}

function detailExcel(notransaksi, numRow, ev) {
  param = "proses=excel&tipe=PNN" + "&notransaksi=" + notransaksi;
  showDialog1(
    "Print PDF",
    "<iframe frameborder=0 style='width:895px;height:400px'" +
      " src='kebun_slave_operasional_print_detail_panen.php?" +
      param +
      "'></iframe>",
    "900",
    "400",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}
function detailData(notransaksi, numRow, ev, tipe) {
  param = "proses=html&tipe=" + tipe + "&notransaksi=" + notransaksi;
  title = "Data Detail";
  showDialog1(
    title,
    "<iframe frameborder=0 style='width:895px;height:400px'" +
      " src='kebun_slave_operasional_print_detail_panen.php?" +
      param +
      "'></iframe>",
    "",
    "",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}

function form() {
  width = "";
  height = "";
  content =
    '<fieldset><div id=containerd style="max-height:450px;max-width:100%;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "Detail HTML";
  showDialog1(title, content, width, height, ev);
}
function getdetail(
  pt,
  kdorg,
  tt,
  ip,
  divisi,
  prd,
  tipe,
  akun,
  jenis,
  bi,
  real
) {
  // form();
  param = "method=html";
  param += "&pt=" + pt;
  param += "&kdorg=" + kdorg;
  param += "&tt=" + tt;
  param += "&ip=" + ip;
  param += "&divisi=" + divisi;
  param += "&prd=" + prd;
  param += "&tipe=" + tipe;
  param += "&akun=" + akun;
  param += "&jenis=" + jenis;
  param += "&bi=" + bi;
  param += "&real=" + real;
  tujuan = "kebun_slave_2analisabyynursery_popup.php";
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
function getdetailexcel(
  pt,
  kdorg,
  tt,
  ip,
  divisi,
  prd,
  tipe,
  akun,
  jenis,
  bi,
  real
) {
  ev = "event";
  param = "method=excel";
  param += "&pt=" + pt;
  param += "&kdorg=" + kdorg;
  param += "&tt=" + tt;
  param += "&ip=" + ip;
  param += "&divisi=" + divisi;
  param += "&prd=" + prd;
  param += "&tipe=" + tipe;
  param += "&akun=" + akun;
  param += "&jenis=" + jenis;
  param += "&bi=" + bi;
  param += "&real=" + real;

  printnopopup("kebun_slave_2analisabyynursery_popup.php?" + param);

  //showDialog1('Report Ms.Excel', "<iframe frameborder=0 style='width:895px;height:400px'" +" src='kebun_slave_2analisabyytm_popup.php?" + param + "'></iframe>", '900', '400', ev);
}
