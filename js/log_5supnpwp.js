//JS 

function getPage(pg){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);
    // cariBast(pg-1);    
}

function simpan()
{
    supplierid=document.getElementById('supplierid').value;
    //supplierid=supplierid.options[supplierid.selectedIndex].value;
    npwp=document.getElementById('npwp').value;
    alamat=document.getElementById('alamat').value;
    aktif=document.getElementById('aktif');
    if(aktif.checked==true)
       aktif=1;
    else
       aktif=0;   
    method=document.getElementById('method').value;

    if(supplierid=='' || npwp=='' || alamat=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='supplierid='+supplierid+'&npwp='+npwp+'&alamat='+alamat+'&method='+method;
    param+='&aktif='+aktif;
    // alert(param);
    tujuan='log_slave_save_5supnpwp.php';
    post_response_text(tujuan, param, respog);      
    
    function respog()
    {
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                        }
                        else {
                            cancel();
                            loadData(0);
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }
}
                    
function cancel()
{
   
    document.getElementById('supplierid').value='';
    document.getElementById('npwp').value='';
    document.getElementById('alamat').value='';
    document.getElementById('aktif').checked=false;
    document.getElementById('method').value='insert';
    document.getElementById('supplierid').disabled=false;
}


function loadData (num) 
{
    // alert('masukk');
    param='method=loadData';
    param+='&page='+num;
    tujuan='log_slave_save_5supnpwp.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                   // alert(con.responseText);
                                    
                                    document.getElementById('container').innerHTML=con.responseText;
                                    // getPage();

                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              } 
     }  
}

function edit(supplierid,npwp,alamat,aktif)
{
    document.getElementById('supplierid').value=supplierid;
    document.getElementById('supplierid').disabled=true;
    document.getElementById('npwp').value=npwp;
     document.getElementById('alamat').value=alamat;
     
     if(aktif=='1')
       document.getElementById('aktif').checked=true;
    else
       document.getElementById('aktif').checked=false;
    document.getElementById('method').value='update';
}





