// JavaScript Document
function displayFormInput(){
     
        document.getElementById('formInput').style.display='block';
        document.getElementById('formInputdetail').style.display='none';
        document.getElementById('formloaddata').style.display='none';
       
}



function simpanht(fileTarget,passParam) 
{
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
    gudang=document.getElementById(passP[1]).value;
    periode=document.getElementById(passP[3]).value;
    tipe='save';
    param+="&method=simpanht";
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } 
                else 
                {
                    // loadformdt(gudang,periode,tipe);
                    loaddatadt();
                  //document.getElementById('container').innerHTML = con.responseText;
                }
            } 
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

maxfitem=0
sekarangitem=1;
function saveAllitem(maxRow){     
    maxfitem=maxRow;
    loopsaveitem(1,maxRow);
}


function loopsaveitem(currRow,maxRow){
    unit=document.getElementById('unit').value;
    kodegudang=document.getElementById('kdgudang').value;
    periode=document.getElementById('periode').value;
    kodebarang=document.getElementById('kdbrg'+currRow).innerHTML;
    qtysys=document.getElementById('qtysys'+currRow).innerHTML;
    phsyqty=document.getElementById('phsyqty'+currRow).value;
    bincardqty=document.getElementById('bincardqty'+currRow).value;
    varian=document.getElementById('varian'+currRow).innerHTML; 
    remark=document.getElementById('remark'+currRow).value; 
   
   
        param='unit='+unit+'&kdgudang='+kodegudang+'&periode='+periode+'&kodebarang='+kodebarang+'&qtysys='+qtysys+'&phsyqty='+phsyqty+'&bincardqty='+bincardqty+'&varian='+varian+'&remark='+remark;
        param+="&method=savedata";
            tujuan = 'log_slave_stocktake.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('rowitem'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    unlockScreen();
                } else {
                    currRow+=1;
                    sekarangitem=currRow;
                    if(currRow>maxRow){
                        alert('Done');
                        //batalkerani();
                    } else {
                        loopsaveitem(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}



 function getmat(jenis,currRow)
{

    phsyqty= document.getElementById('phsyqty'+currRow).value;
    bincardqty= document.getElementById('bincardqty'+currRow).value;
    qtysys= document.getElementById('qtysys'+currRow).innerHTML;
    varian= parseInt(phsyqty)-parseInt(qtysys);
    document.getElementById('varian'+currRow).innerHTML=numberFormat(varian,2);
   
}  


function loadformdt(kodegudang,periode,tipe)
{
    param='method=loadformdt'+'&kdgudang='+kodegudang+'&periode='+periode+'&tipe='+tipe;
    tujuan='log_slave_stocktake';
    post_response_text(tujuan+'.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    document.getElementById('formInputdetail').style.display='block';
                    document.getElementById('formloaddata').style.display='none';
                    document.getElementById('container').innerHTML=con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function postingx(kdgudang, per) {
    param = 'kodegudang=' + kdgudang + '&periode='+ per + '&proses=postingx';
    tujuan = 'log_slave_stocktake.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   loaddatadt();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loaddatadt()
{
    param='method=loaddatadt';
    tujuan='log_slave_stocktake';
    post_response_text(tujuan+'.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                  document.getElementById('formInput').style.display='none';
                  document.getElementById('formInputdetail').style.display='none';
                  document.getElementById('formloaddata').style.display='block';
                  document.getElementById('container1').innerHTML=con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
}


function edit(kodegudang,kodeorg,periode) {
    document.getElementById('kdgudang').value = kodegudang;
    document.getElementById('unit').value = kodeorg;
    document.getElementById('periode').value = periode;
    document.getElementById('formInput').style.display='block';
    loadformdt(kodegudang,periode);
}

function del(kodegudang,kodeorg,periode)
{
    param='kdgudang='+kodegudang+'&unit='+kodeorg+'&periode='+periode+'&method=delete';
    tujuan = 'log_slave_stocktake.php';
    if (confirm('Anda yakin ???')) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    loaddatadt();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    
    }
 
function detailData(kodegudang,kodeorg,periode, numRow, ev, jenis) {
    param = "method=html"+'&kdgudang=' + kodegudang +'&unit='+kodeorg+'&periode='+periode+ "&jenis=" + jenis;
    title = "Data Detail";
    showDialog2(title, "<iframe frameborder=0 style='width:995px;height:490px'" +
        " src='log_slave_stocktake.php?" + param + "'></iframe>", '1000', '500', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
