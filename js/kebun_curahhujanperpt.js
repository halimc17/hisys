function getsubunit() {
  unit = document.getElementById("unit").value;

  param = "method=getsubunit&unit=" + unit;
  tujuan = "kebun_slave_2newcurahhujan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("subunit").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getsubunit2() {
  unit2 = document.getElementById("unit2").value;

  param = "method=getsubunit2&unit2=" + unit2;
  tujuan = "kebun_slave_2newcurahhujan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("subunit2").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function preview(tipeprint, ev) {
  var pt_el = document.getElementById("pt");
  var pt_vals = [];
  for (var i = 0; i < pt_el.options.length; i++) {
    if (pt_el.options[i].selected) {
      pt_vals.push(pt_el.options[i].value);
    }
  }
  var pt = pt_vals.join(',');

  var periode = document.getElementById("periode").value;
  var intiplasma = document.getElementById("intiplasma").value;

  param =
    "method=preview&tipeprint=" +
    tipeprint +
    "&periode=" +
    periode +
    "&pt=" +
    pt +
    "&intiplasma=" +
    intiplasma;
  tujuan = "kebun_slave_curahhujanperpt.php";
  if (tipeprint != "html") {
    judul = tipeprint;
    ev = "event";
    printFile(param, tujuan, judul, ev);
  } else {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          console.log("Ini proses1");
          alert(con.responseText);
        } else {
          console.log("Ini proses2");
          document.getElementById("printContainer0").innerHTML = con.responseText;
          if (document.getElementById("chart-container")) {
            document.getElementById("chart-container").style.display = 'none';
          }
          if (typeof leftFixedTable === "function") leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function preview2(tipeprint, ev) {
  var pt_el2 = document.getElementById("pt2");
  var pt_vals2 = [];
  for (var i = 0; i < pt_el2.options.length; i++) {
    if (pt_el2.options[i].selected) {
      pt_vals2.push(pt_el2.options[i].value);
    }
  }
  var pt2 = pt_vals2.join(',');
  var intiplasma2 = document.getElementById("intiplasma2").value;

  var param = "method=preview2&tipeprint=" + tipeprint + "&pt2=" + pt2 + "&intiplasma=" + intiplasma2;
  var tujuan = "kebun_slave_curahhujanperpt.php";

  if (tipeprint != "html") {
    var judul = tipeprint;
    var ev2 = "event";
    printFile(param, tujuan, judul, ev2);
  } else {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("printContainer1").innerHTML = con.responseText;

          if (document.getElementById("chart-container")) {
            document.getElementById("chart-container").style.display = 'block';
          }

          if (typeof leftFixedTable === "function") leftFixedTable();
          renderChart();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function renderChart() {
  var elData = document.getElementById('dataGrafik');
  var elLabel = document.getElementById('labelGrafik');

  if (!elData || !elLabel) return;

  var datasetsHujan = JSON.parse(elData.value);
  var labelsBulan = JSON.parse(elLabel.value);

  var ctx = document.getElementById('myChartCanvas').getContext('2d');

  if (window.myChartHujan) {
    window.myChartHujan.destroy();
  }

  window.myChartHujan = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labelsBulan,
      datasets: datasetsHujan
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          },
          scaleLabel: {
            display: true,
            labelString: 'Curah Hujan (mm)'
          }
        }]
      }
    }
  });
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
