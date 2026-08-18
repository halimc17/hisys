// JavaScript Document

function savePajakPesangon(fileTarget,passParam) {

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
   document.getElementById('penghasilan').value='';
   document.getElementById('persentase').value='';
   document.getElementById('method').value="insert";
}

function loadData(){
    carikodept=document.getElementById('carikodept').options[document.getElementById('carikodept').selectedIndex].value;
    param='method=loadData'+'&carikodept='+carikodept;
    tujuan='sdm_slave_5pajakpesangon';
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

function fillField(kodept,penghasilan,persentase)
{
    document.getElementById('kodept').value=kodept;
    document.getElementById('old_kodept').value=kodept;
    document.getElementById('penghasilan').value=penghasilan;
    document.getElementById('old_penghasilan').value=penghasilan;
    document.getElementById('persentase').value=persentase;
    document.getElementById('old_persentase').value=persentase;
    document.getElementById('method').value="update";
}

function del(kodept,penghasilan,persentase)
{
    param='kodept='+kodept+'&penghasilan='+penghasilan+'&persentase='+persentase+'&method=deletedata';
    tujuan='sdm_slave_5pajakpesangon.php';
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
                    document.getElementById('penghasilan').value='';
                    document.getElementById('persentase').value='';
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}