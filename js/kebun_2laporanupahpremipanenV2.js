function getDivisi() {
  kodeorg = document.getElementById("unit").value;

  param = "proses=getDivisi";
  param += "&unit=" + encodeURIComponent(kodeorg);
  tujuan = "kebun_2laporanupahpremipanenV2_slave.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("div").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadUpahPremiPanen() {
  var unit = document.getElementById("unit").value;
  var div = document.getElementById("div").value;
  var tgl = document.getElementById("tgl").value;
  var tglx = document.getElementById("tglx").value;
  var container = document.getElementById("printContainer");

  if (unit == "") {
    alert("Warning: Unit usaha harus dipilih");
    return;
  }

  if (tgl == "" || tglx == "") {
    alert("Warning: Tanggal wajib diisi");
    return;
  }

  var param = "proses=preview";
  param += "&unit=" + encodeURIComponent(unit);
  param += "&div=" + encodeURIComponent(div);
  param += "&tgl=" + encodeURIComponent(tgl);
  param += "&tglx=" + encodeURIComponent(tglx);

  post_response_text(
    "kebun_2laporanupahpremipanenV2_slave.php",
    param,
    responseUpahPremiPanen,
  );

  function responseUpahPremiPanen() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();

        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
          return;
        }

        if (typeof unfreezeTable == "function") {
          unfreezeTable("#printContainer table.z-freeze-target");
        }

        container.innerHTML = con.responseText;

        if (typeof freezeTable == "function") {
          freezeTable("#printContainer table.z-freeze-target", "header");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
