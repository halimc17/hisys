function getRatioKendaraan() {
    unit = document.getElementById('unit');
    jenisbbm = document.getElementById('jns_bbm').value;
    //periode =document.getElementById('periode');
    unit = unit.options[unit.selectedIndex].value;
    periode = getOptionsValue(document.getElementById('tahun'));

    param = 'unit=' + unit + '&tahun=' + periode;
    param += '&jenisbbm=' + jenisbbm;
	tujuan = 'vhc_slave_2ratio.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //showById('printPanel');
                    document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printFile(tujuan, ev) {
    unit = document.getElementById('unit');
    //periode =document.getElementById('periode');
    unit = unit.options[unit.selectedIndex].value;
    periode = getOptionsValue(document.getElementById('tahun'));
	jenisbbm = document.getElementById('jns_bbm').value;
	
    param = 'unit=' + unit + '&tahun=' + periode
	param += '&jenisbbm=' + jenisbbm;
        tujuan = tujuan + "?" + param;
    title = 'Print Execel';
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}