function preview(){
    var param='kebun='+getValue('kebun')+'&periode='+getValue('periode')+
            '&sumber='+getValue('sumber'),
        tujuan='kebun_slave_pengakuanbybibit.php?proses=preview';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    getById('previewCont').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function process(){
    var param='kebun='+getValue('kebunHid')+'&periode='+getValue('periodeHid')+
            '&sumber='+getValue('sumberHid'),
        tujuan='kebun_slave_pengakuanbybibit.php?proses=proses';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    res = JSON.parse(con.responseText);
                    alert(res.msg);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}