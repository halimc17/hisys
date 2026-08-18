// JavaScript Document

function loadData(num)
{
    noinvoice=document.getElementById('noinvoice').value;
    param='proses=loadData&noinvoice='+noinvoice+'&page='+num;
    tujuan='keu_slave_tagihan_unpost';
    post_response_text(tujuan+'.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function unposting(noinvoice,num)
{
    param='proses=unposting&noinvoice='+noinvoice;
    tujuan='keu_slave_tagihan_unpost';
    if(confirm('Unposting '+noinvoice+'?'))
    post_response_text(tujuan+'.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData(num);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}