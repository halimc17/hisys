function viewvar() {
  // alokasi=trim(document.getElementById('alokasi').value);
  unit = trim(document.getElementById("unit").value);
  per = trim(document.getElementById("per").value);
  method = "viewvar";
  param = "";
  param += "&method=" + method + "&per=" + per + "&unit=" + unit;
  tujuan = "keu_slave_3hpp.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify
            .popup()
            .set({
              resizable: true,
              maximizable: true,
              startMaximized: true,
              message: con.responseText,
            })
            .resizeTo("70%", "70%")
            .show();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function savehpp(parameter, nodata) {
  unit = trim(document.getElementById("unit").value);
  per = trim(document.getElementById("per").value);
  param = "method=savehpp" + "&unit=" + unit + "&per=" + per;
  var passP = parameter.split("###");

  // var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    param += "&" + passP[i] + "=" + getValue(passP[i]);
  }
  param += "&nodata=" + nodata;
  tujuan = "keu_slave_3hpp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          alert("Data tersimpan");
          // preview();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function preview() {
  unit = trim(document.getElementById("unit").value);
  per = trim(document.getElementById("per").value);
  tipe = "html";
  param = "method=preview" + "&unit=" + unit + "&per=" + per + "&tipe=" + tipe;
  tujuan = "keu_slave_3hpp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert("ERROR TRANSACTION,\n" + con.responseText);
        } else {
          document.getElementById("printContainer").innerHTML =
            con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function excel() {
  unit = trim(document.getElementById("unit").value);
  per = trim(document.getElementById("per").value);
  tipe = "excel";
  ev = "event";
  param = "method=preview" + "&unit=" + unit + "&per=" + per + "&tipe=" + tipe;
  ujuan = "keu_slave_3hpp.php";
  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "900";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function batal() {
  document.getElementById("unit").value = "";
  document.getElementById("per").value = "";
  document.getElementById("printContainer").innerHTML = "";
}
