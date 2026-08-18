function getUnit() {
  pt = document.getElementById("pt").value;

  document.getElementById("unit").innerHTML = "";
  document.getElementById("divisi").innerHTML = "<option value='all'>all</option>";
  
  if (typeof $ !== 'undefined') {
    $('#unit').val(null).trigger('change');
    $('#divisi').val(null).trigger('change');
  }

  param = "method=getUnit&pt=" + pt;
  tujuan = "lm_slave_2luasanarea.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("unit").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getDivisi() {
  var unitEl = document.getElementById("unit");
  var selectedUnits = [];
  for (var i = 0; i < unitEl.options.length; i++) {
    if (unitEl.options[i].selected) {
      selectedUnits.push(unitEl.options[i].value);
    }
  }
  unit = selectedUnits.join(',');

  param = "method=getDivisi&unit=" + unit;
  tujuan = "lm_slave_2luasanarea.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("divisi").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}


function preview(tipeprint, ev) {
  pt = document.getElementById("pt").value;

  var unitEl = document.getElementById("unit");
  var selectedUnits = [];
  for (var i = 0; i < unitEl.options.length; i++) {
    if (unitEl.options[i].selected) {
      selectedUnits.push(unitEl.options[i].value);
    }
  }
  unit = selectedUnits.join(',');

  var divisiEl = document.getElementById("divisi");
  var selectedDivisi = [];
  for (var i = 0; i < divisiEl.options.length; i++) {
    if (divisiEl.options[i].selected) {
      selectedDivisi.push(divisiEl.options[i].value);
    }
  }
  divisi = selectedDivisi.join(',');

  periode = document.getElementById("periode").value;

  param =
    "method=preview&tipeprint=" +
    tipeprint +
    "&pt=" +
    pt +
    "&unit=" +
    unit +
    "&divisi=" +
    divisi +
    "&periode=" +
    periode;
  tujuan = "lm_slave_2luasanarea.php";
  if (tipeprint != "html") {
    judul = tipeprint;
    ev = "event";
    printFile(param, tujuan, judul, ev);
  }
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("printContainer").innerHTML = con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "";
  height = "";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}