// JavaScript Document

function savePensiun(fileTarget,passParam) {

    var passP = passParam.split('##');
    var param = "";

    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                        loadData();
                        cancelIsi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(fileTarget+'.php', param, respon);

}

function cancelIsi(passParam){
   document.getElementById('kodept').value='';
   document.getElementById('masakerja').value='';
   document.getElementById('jenis').value='';
   document.getElementById('banyaknya').value='';
   document.getElementById('method').value="insert";
}

function loadData(){
    carikodept=document.getElementById('carikodept').options[document.getElementById('carikodept').selectedIndex].value;
    carijenis=document.getElementById('carijenis').options[document.getElementById('carijenis').selectedIndex].value;
    param='method=loadData'+'&carikodept='+carikodept+'&carijenis='+carijenis;
    tujuan='sdm_slave_5pensiun';
    post_response_text(tujuan+'.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function fillField(kodept,masakerja,jenis,banyaknya)
{
    document.getElementById('kodept').value=kodept;
    document.getElementById('old_kodept').value=kodept;
    document.getElementById('masakerja').value=masakerja;
    document.getElementById('old_masakerja').value=masakerja;
    document.getElementById('jenis').value=jenis;
    document.getElementById('old_jenis').value=jenis;
    document.getElementById('banyaknya').value=banyaknya;
    document.getElementById('old_banyaknya').value=banyaknya;
    document.getElementById('method').value="update";
}

function del(kodept,masakerja,jenis,banyaknya)
{
    param='kodept='+kodept+'&masakerja='+masakerja+'&jenis='+jenis+'&banyaknya='+banyaknya+'&method=deletedata';
    tujuan='sdm_slave_5pensiun.php';
    if(confirm("Are You Sure Want Delete Data?"))
        post_response_text(tujuan, param, respog);
				
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    loadData();
                    document.getElementById('kodept').value='';
                    document.getElementById('masakerja').value='';
                    document.getElementById('jenis').value='';
                    document.getElementById('banyaknya').value='';
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

 
 