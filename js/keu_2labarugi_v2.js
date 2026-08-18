function getLaporanLabaRugiV2(tipe)
{
    pt = document.getElementById('pt');
    unit = document.getElementById('unit');
    periode = document.getElementById('periode');

    pt = pt.options[pt.selectedIndex].value;
    unit = unit.options[unit.selectedIndex].value;
    periode = periode.options[periode.selectedIndex].value;

    if (pt == '') {
        alertify.alert("Informasi", "Pilih PT terlebih dahulu");
        return;
    }

    param = 'pt=' + pt + '&unit=' + unit + '&periode=' + periode + '&tipe=' + tipe;
    tujuan = 'keu_slave_2labarugi_v2.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    if (tipe == 'excel') {
                        window.open(con.responseText, "_blank");
                    } else {
                        document.getElementById('container').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getUnitLabaRugiV2()
{
    pt = document.getElementById('pt').value;
    param = 'proses=getUnit2' + '&pt=' + pt;
    tujuan = 'keu_slave_2jurnal_option.php';
    post_response_text(tujuan, param, respog);

    function respog()
    {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById('unit').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
