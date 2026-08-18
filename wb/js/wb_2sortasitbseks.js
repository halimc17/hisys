const btnpreview = document.getElementById('btnpreview');
if (btnpreview) {
	btnpreview.addEventListener('click', function () {
	  loaddata();
	});
}

const btnexcel = document.getElementById('btnexcel');
if (btnexcel) {
    btnexcel.addEventListener('click', function () {
      exportTableToExcel('mytable','sortasitbs');
    });
}

function loaddata() {
	supplier = getValue('supplier');
	tanggal = getValue('tanggal');
	tanggal2 = getValue('tanggal2');

    param = 'method=loaddata&supplier='+supplier+'&tanggal='+tanggal+'&tanggal2='+tanggal2;
    tujuan = 'wb_2sortasitbseks_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('output').innerHTML = con.responseText;
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function exportTableToExcel(tableID, filename = ''){
    
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
        tableSelect.border='0';
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    filename = filename?filename+'.xls':'excel_data.xls';
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);

    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}