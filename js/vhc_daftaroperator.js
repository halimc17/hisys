function cari() {
    company_id = document.getElementById('company_id').options[document.getElementById('company_id').selectedIndex].value;
    jnsVhc = document.getElementById('jnsVhc').options[document.getElementById('jnsVhc').selectedIndex].value;

    param = 'company_id=' + company_id + '&jnsVhc=' + jnsVhc + '&proses=cari';
    tujuan = 'vhc_slave_daftaroperator.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contain').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function dataKeExcel(ev, tujuan) {
    company_id = document.getElementById('company_id').options[document.getElementById('company_id').selectedIndex].value;
    jnsVhc = document.getElementById('jnsVhc').options[document.getElementById('jnsVhc').selectedIndex].value;

    judul = 'Report Daftar Operator Ms.Excel';
    param = 'company_id=' + company_id + '&jnsVhc=' + jnsVhc + '&proses=getExcel';
    printFile(param, tujuan, judul, ev)
}
function dataKePDF(ev) {
    company_id = document.getElementById('company_id').options[document.getElementById('company_id').selectedIndex].value;
    company_name = document.getElementById('company_id').options[document.getElementById('company_id').selectedIndex].text;
    jnsVhc = document.getElementById('jnsVhc').options[document.getElementById('jnsVhc').selectedIndex].value;
    jnsVhc_name = document.getElementById('jnsVhc').options[document.getElementById('jnsVhc').selectedIndex].text;

    tujuan = 'vhc_slave_daftaroperator.php';
    judul = 'Report Daftar Operator PDF';
    param = 'company_id=' + company_id + '&company_name=' + company_name + '&jnsVhc=' + jnsVhc + '&jnsVhc_name=' + jnsVhc_name + '&proses=pdf';
    printFile(param, tujuan, judul, ev)
}
function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}
