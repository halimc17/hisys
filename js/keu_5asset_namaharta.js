/**
 * @author repindra.ginting
 */
//=================================================sisi purchasing
function getkelompok(kelompok,jenisusaha)
{
    jenisharta=document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
    param='jenisharta='+jenisharta+'&method=getkelompok'+'&kelompok='+kelompok;
    tujuan='keu_slave_5asset_namaharta.php';
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
                                                    document.getElementById('kelompok').innerHTML=con.responseText;
                                                    // document.getElementById('id_klmpkharta').value='';
                                                    // if(document.getElementById('kelompok').disabled==true){
                                                    // document.getElementById('kelompok').disabled=false;    
                                                    // }
                                                    if(jenisusaha!=''){
                                                        getjenis(jenisusaha);
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

function getjenis(jenisusaha)
{
    //sesuaikan dengan id yg di form
     jenisharta=document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
    jenis_usaha=document.getElementById('kelompok').options[document.getElementById('kelompok').selectedIndex].value;
    param='jenisharta='+jenisharta+'&jenis_usaha='+jenis_usaha+'&method=getjenis'+'&jenisusaha='+jenisusaha;
    tujuan='keu_slave_5asset_namaharta.php';
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
                                                    document.getElementById('jenisusaha').innerHTML=con.responseText;
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
    tujuan='keu_slave_5asset_namaharta.php';
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
    jenis=document.getElementById('jenis').value;
    kelompok=document.getElementById('kelompok').value;
    //supplierid=supplierid.options[supplierid.selectedIndex].value;
    jenisusaha=document.getElementById('jenisusaha').value;
    namaid=document.getElementById('namaid').value;
    namaharta=document.getElementById('namaharta').value;
    jumlah=document.getElementById('jumlah').value;
    status1=document.getElementById('status1');
    if(status1.checked==true)
       status1=1;
    else
       status1=0;   
    method=document.getElementById('method').value;

    if(jenis=='' || kelompok==''|| jenisusaha==''|| namaharta==''|| jumlah=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='jenis='+jenis+'&kelompok='+kelompok+'&jenisusaha='+jenisusaha+'&namaid='+namaid+'&namaharta='+namaharta+'&jumlah='+jumlah+'&method='+method;
    param+='&status1='+status1;
    // alert(param);
    tujuan='keu_slave_5asset_namaharta.php';
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
    document.getElementById('jenis').value='';
    document.getElementById('jenis').disabled=false;
    document.getElementById('kelompok').value='';
    document.getElementById('kelompok').disabled=false;
    document.getElementById('jenisusaha').value='';
    document.getElementById('jenisusaha').disabled=false;
    document.getElementById('namaid').value='';
    document.getElementById('namaharta').value='';
    document.getElementById('jumlah').value='';    
    document.getElementById('status1').checked=true;
    document.getElementById('method').value='insert';
    getkelompok(kelompok,jenisusaha);
}


//==========EDIT FORM Alamat==================//
function edit(jenis,kelompok,jenisusaha,namaid,namaharta,jumlah,status1)
{
    document.getElementById('jenis').value=jenis;
    document.getElementById('jenis').disabled=true;
    document.getElementById('kelompok').value=kelompok;
    document.getElementById('kelompok').disabled=true;
    document.getElementById('jenisusaha').value=jenisusaha;
    document.getElementById('jenisusaha').disabled=true;
    document.getElementById('namaid').value=namaid;
    document.getElementById('namaid').disabled=true;
    document.getElementById('namaharta').value=namaharta;
    document.getElementById('jumlah').value=jumlah;
     if(status1=='1')
       document.getElementById('status1').checked=true;
    else
       document.getElementById('status1').checked=false;
    document.getElementById('method').value='update';

    
    getkelompok(kelompok,jenisusaha);

    
}

function delData(namaid)
{
    param='method=delData'+'&namaid='+namaid;
    tujuan='keu_slave_5asset_namaharta.php';
    if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
    // post_response_text(tujuan, param, respog);   
    function respog()
    {
          if(con.readyState==4)
          {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    }
                    else 
                    {
                        loadData();
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
          } 
    }

}


