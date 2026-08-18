function previewData(mode) {
    var pt = document.getElementById('pt').value;
    var periode = document.getElementById('periode').value;
    // var periode2 = document.getElementById('periode2').value;
    var intiplasma = document.getElementById("intiplasma").value;

    if (pt == '') {
        alert('Perusahaan harus dipilih');
        return;
    }
    if (periode == '') {
        alert('Periode harus dipilih');
        return;
    }

    var param = "proses=preview" +
        "&pt=" + pt +
        "&periode=" + periode +
        // "&periode2=" + periode2 +
        "&intiplasma=" + intiplasma;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    post_response_text(
        'lm_slave_2statisikproduksitbs.php',
        param,
        function () {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'statisikproduksitbs.xls');
                    } else {
                        document.getElementById('printContainer').innerHTML = con.responseText;
                    }
                } else {
                    alert('Error loading data');
                }
            }
        }
    );
}

function downloadExcel(data, filename) {
    var blob = new Blob([data], { type: 'application/vnd.ms-excel' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}
