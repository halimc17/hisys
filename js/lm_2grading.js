function preview(tipeprint, ev) {
  var pt = document.getElementById("pt").value;
  var tahuntanam = document.getElementById("tt").value;

  if (pt == "" ) {
    alert("PT wajib dipilih");
    return;
  }

  var param =
    "method=preview" +
    "&pt=" + pt +
    "&tahuntanam=" + tahuntanam +
    "&tipeprint=" + tipeprint;

  var tujuan = "lm_slave_2grading.php";

  post_response_text(tujuan, param, function () {
    if (con.readyState == 4) {
        if (con.status == 200) {
            busy_off();
            if (!isSaveResponse(con.responseText)) {
                alert(con.responseText);
            } else {
                document.getElementById("printContainer").innerHTML = con.responseText;
                renderChart();
            }
        } else {
            busy_off();
            error_catch(con.status);
        }
    }
  });
}

function renderChart() {
  const jsonEl = document.getElementById("chartData");
  if (!jsonEl) return;

  const parsed = JSON.parse(jsonEl.textContent);
  const bulanLabel = parsed.bulan;
  const rawData = parsed.data;

  const colors = [
    "#1f77b4", "#ff7f0e", "#2ca02c",
    "#d62728", "#9467bd", "#8c564b"
  ];

  const datasets = [];
  let i = 0;

  for (const tahun in rawData) {
    const dataBulanan = [];
    for (let b = 1; b <= 12; b++) {
      dataBulanan.push(rawData[tahun][b] ?? 0);
    }

    datasets.push({
      label: tahun,
      data: dataBulanan,
      borderColor: colors[i % colors.length],
      fill: false
    });
    i++;
  }

  new Chart(document.getElementById("grafikGrading"), {
    type: "line",
    data: {
      labels: bulanLabel,
      datasets: datasets
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: "Grafik Persentase Grading per Tahun Tanam"
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: v => v + "%"
          }
        }
      }
    }
  });
}




function printexcel(ev) {
  var pt = document.getElementById("pt").value;
  var tahuntanam = document.getElementById("tt").value;

  if (pt == "") {
    alert("PT wajib dipilih");
    return;
  }

  var param =
    "method=excel" +
    "&pt=" + pt +
    "&tahuntanam=" + tahuntanam;

  var tujuan = "lm_slave_2grading.php?" + param;
  printnopopup(tujuan);
}



