/**
 * @author repindra.ginting
 */
//=================================================
function getkelompok(subkelompok,kodebarang)
{
    kodesub=document.getElementById('kelompok').options[document.getElementById('kelompok').selectedIndex].value;
    param='kodesub='+kodesub+'&method=getkelompok'+'&subkelompok='+subkelompok;
    tujuan='log_slave_5docassign.php';
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
                                                    // console.log('respon');
                                                    document.getElementById('subkelompok').innerHTML=con.responseText;
                                                    // document.getElementById('id_klmpkharta').value='';
                                                    // if(document.getElementById('kelompok').disabled==true){
                                                    // document.getElementById('kelompok').disabled=false;    
                                                    // }
                                                    if(kodebarang!=''){
                                                        getkode(kodebarang);
                                                    }
                                            }
                                    }
                                    else {
                                            busy_off();
                                            error_catch(con.status);
                                    }
                  } 
     }  
}

function getkode(kodebarang)
{
    //sesuaikan dengan id yg di form
     kodesub=document.getElementById('kelompok').options[document.getElementById('kelompok').selectedIndex].value;
    kodebar=document.getElementById('subkelompok').options[document.getElementById('subkelompok').selectedIndex].value;
    param='kodesub='+kodesub+'&kodebar='+kodebar+'&method=getkode'+'&kodebarang='+kodebarang;
    tujuan='log_slave_5docassign.php';
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
                                                    // console.log('respon');
                                                    document.getElementById('kodebarang').innerHTML=con.responseText;
                                                    // document.getElementById('id_klmpkharta').value='';
                                                    // if(document.getElementById('jenisusaha').disabled==true){
                                                    // document.getElementById('jenisusaha').disabled=false;    
                                                    // }
                                            }
                                    }
                                    else {
                                            busy_off();
                                            error_catch(con.status);
                                    }
                  } 
     }  
}


function loadData(num) 
{
    // alert('masukk');
    param='method=loadData';
    param+='&page='+num;
    // param+='&supplierid2='+idsupplier_detail;
    tujuan='log_slave_5docassign.php';
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
                                    // detaildt();

                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              } 
     }  
}

function getPage(pg){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);
    // cariBast(pg-1);    
}


//============ SIMPAN Alamat Supplier ============ BELUM SELESAI

function simpan()
{
     // alert ('masuk');
    
    idbar=document.getElementById('idbar').value;
    kodebarang=document.getElementById('kodebarang').value;
    status1=document.getElementById('status1');
    if(status1.checked==true)
       status1=1;
    else
       status1=0;   
    method=document.getElementById('method').value;

    if(kodebarang=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='idbar='+idbar+'&kodebarang='+kodebarang+'&method='+method;
    param+='&status1='+status1;
    // alert(param);
    tujuan='log_slave_5docassign.php';
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
                            
                            loadData();
                            // loadData1(supplierid2);
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }
}

//==========CANCEL / RESET FORM ==================//
function cancel()
{
   
    // document.getElementById('notrans').value='';
    document.getElementById('kelompok').value='';
    document.getElementById('kelompok').disabled=false;
    document.getElementById('subkelompok').value='';
    document.getElementById('subkelompok').disabled=false;
    document.getElementById('kodebarang').value='';
    document.getElementById('kodebarang').disabled=false;
    document.getElementById('status1').checked=true;
    document.getElementById('method').value='insert';
    getkelompok(subkelompok,kodebarang);
}


//==========EDIT FORM Alamat==================//
function edit(idbar,kelompok,subkelompok,kodebarang,status1)
{
    document.getElementById('idbar').value=idbar;
    document.getElementById('kelompok').value=kelompok;
    document.getElementById('kelompok').disabled=true;
    document.getElementById('subkelompok').value=subkelompok;
    document.getElementById('subkelompok').disabled=true;
    document.getElementById('kodebarang').value=kodebarang;
    document.getElementById('kodebarang').disabled=true;
     if(status1=='1')
       document.getElementById('status1').checked=true;
    else
       document.getElementById('status1').checked=false;
    document.getElementById('method').value='update';
    
    getkelompok(subkelompok,kodebarang);

    
}



