function preview(tipeprint,ev){
    regional = getValue('regional');
    periode = getValue('periode');
    
    param = 'method=preview&tipeprint='+tipeprint+'&periode='+periode+'&regional='+regional;
    
    tujuan = 'kebun_2forecastingcrop_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                    alertify.alert('Informasi',con.responseText);
                } else {
                    if(tipeprint=='html'){
                        document.getElementById('container').innerHTML=con.responseText;
                        document.getElementById('container').style.display='block';
                        // alert(con.responseText);
                    }else if(tipeprint=='excel'){
                        tujuan=tujuan+"?"+param;  
                        printnopopup(tujuan);
                    }
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printnopopup(url) {
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}