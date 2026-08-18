var tujuan = "kebun_slave_2panenspbdma.php";
var param  = "";

function previewData(tipe = 'html') {
    param = "";
    param += "method=previewData";
    param += "&tipe="+tipe;
    param += "&unit=" + getValue('unit');
    param += "&divisi=" + getValue('divisi');
    param += "&tanggalawal=" + getValue('tanggalawal');
    param += "&tanggalakhir=" + getValue('tanggalakhir');
   
    if (getValue('unit') == '') {
        alertify.alert("Info", "Filter unit harus dipilih.");
        return;
    }
	
	function response(){
        if (con.readyState == 4) {
            busy_off();
            if (con.status == 200) {
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (tipe == 'html') {
                        document.getElementById('listForm').innerHTML = con.responseText;
						leftFixedTable();
                    } 
                }
            } else {
                error_catch(con.status);
            }
        }
    }

    if (tipe == 'html') {
        post_response_text(tujuan, param, response);
    } else {
		tujuan=tujuan+"?"+param;
        printnopopup(tujuan);
    }
}

function getDivisi(unit){
    param = "";
    param += "method=getDivisi";
    param += "&unit=" + unit;

    post_response_text(tujuan, param, function() {
        if (con.readyState == 4) {
            busy_off();
            if (con.status == 200) {
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                error_catch(con.status);
            }
        }
    });
}

//First Time Load
window.addEventListener('DOMContentLoaded', (event) => {
    console.log('DOM fully loaded and parsed');

    document.getElementById('previewButton').addEventListener("click", function() {
        previewData();
    });

    document.getElementById('excelButton').addEventListener("click", function() {
        previewData('excel');
    });

    document.getElementById('cancelButton').addEventListener("click", function() {
        clearField();
    });
});