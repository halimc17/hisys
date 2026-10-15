// js/kebun_5laporanProduksiTahunTanam.js
// JS khusus Laporan Produksi Per Tahun Tanam

function getThnTanam() {
    var unit = document.getElementById('unitId').options[document.getElementById('unitId').selectedIndex].value;
    var intiplasma = document.getElementById('intiplasma').options[document.getElementById('intiplasma').selectedIndex].value;
    var param = 'unitId=' + unit + '&intiplasma=' + intiplasma + '&proses=getThnTanam';
    var tujuan = "kebun_slave_5laporanProduksiTahunTanam.php";

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('tahuntanam').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function cekPreview(tipe) {
    var periode = document.getElementById('periodeId').options[document.getElementById('periodeId').selectedIndex].value;
    var unit = document.getElementById('unitId').options[document.getElementById('unitId').selectedIndex].value;
    if (periode == '') {
        alertify.alert("Informasi", "Periode wajib dipilih");
        return;
    }
    if (unit == '') {
        alertify.alert("Informasi", "Unit wajib dipilih");
        return;
    }
    if (tipe == 'preview') {
        zPreview('kebun_slave_5laporanProduksiTahunTanam', '##periodeId##unitId##intiplasma##tahuntanam', 'printContainer2');
    } else {
        zExcel(event, 'kebun_slave_5laporanProduksiTahunTanam.php', '##periodeId##unitId##intiplasma##tahuntanam');
    }
}

function batal() {
    document.getElementById('periodeId').value = '';
    document.getElementById('unitId').value = '';
    document.getElementById('intiplasma').value = '';
    document.getElementById('tahuntanam').value = '';
    document.getElementById('printContainer2').innerHTML = '';
}

function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
	}	
}
