function getunit(){
    pt = getValue('pt');
    param = 'method=getunit&pt='+pt;
    tujuan = 'kebun_2rabtmtbm_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                    alertify.alert('Informasi',con.responseText);
                } else {
                   document.getElementById('unit').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function preview(tipeprint,ev){
    pt = getValue('pt');
    unit = getValue('unit');
    tahunprd = getValue('tahunprd');
    
    param = 'method=preview&tipeprint='+tipeprint+'&tahunprd='+tahunprd+'&pt='+pt+'&unit='+unit;
    
    tujuan = 'kebun_2rabtmtbm_slave.php';
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
                        leftFixedTable();
                        // document.getElementById('container').style.display='block';
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