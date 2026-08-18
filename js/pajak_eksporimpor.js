function getnpwp(){
    unit=trim(document.getElementById('unit').value);
    param='unit='+unit+'&method=getnpwp';
    tujuan='pajak_slave_eksporimpor.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('npwp').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnoakun(){
    unit=trim(document.getElementById('unit').value);
    npwp=trim(document.getElementById('npwp').value);
    param='unit='+unit+'&npwp='+npwp+'+&method=getnoakun';
    tujuan='pajak_slave_eksporimpor.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('noakun').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function prosesdata(){
    unit=trim(document.getElementById('unit').value);
    npwp=trim(document.getElementById('npwp').value);
    noakun=trim(document.getElementById('noakun').value);
    tanggal1=trim(document.getElementById('tanggal1').value);
    tanggal2=trim(document.getElementById('tanggal2').value);
    param = 'tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&unit='+unit+'&npwp='+npwp+'&noakun='+noakun+'&method=prosesdata';
    tujuan = 'pajak_slave_eksporimpor.php';
    
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
                            document.getElementById('container').innerHTML=con.responseText;
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }  
     post_response_text(tujuan,param,respog);
}


function csv(){
    unit=trim(document.getElementById('unit').value);
    npwp=trim(document.getElementById('npwp').value);
    noakun=trim(document.getElementById('noakun').value);
    tanggal1=trim(document.getElementById('tanggal1').value);
    tanggal2=trim(document.getElementById('tanggal2').value);
    param = 'tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&unit='+unit+'&npwp='+npwp+'&noakun='+noakun+'&method=csv';
    tujuan = 'pajak_slave_eksporimpor.php';


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
                            printcsv();
                            // alert('sukses');

                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }  
     post_response_text(tujuan,param,respog);
}

function printcsv(tanggal1,tanggal2,unit,npwp,noakun,tujuan){
    unit=trim(document.getElementById('unit').value);
    npwp=trim(document.getElementById('npwp').value);
    noakun=trim(document.getElementById('noakun').value);
    tanggal1=trim(document.getElementById('tanggal1').value);
    tanggal2=trim(document.getElementById('tanggal2').value);
    ev='event';
    param = 'tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&unit='+unit+'&npwp='+npwp+'&noakun='+noakun+'&method=printcsv';
    tujuan = 'pajak_slave_eksporimpor.php';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev);
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}




