function getUnitKebun() {
    pt = document.getElementById('pt').value;
    param = 'pt='+pt;
    param += '&method=getUnitKebun';
    
    tujuan='kebun_slave_2laporanmonograf.php';
    post_response_text(tujuan, param, respog);
    function respog() 
    {
        if (con.readyState == 4) 
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) 
                {
                    alertify.alert(con.responseText);
                } 
                else 
                {   
                    document.getElementById('idKebun').innerHTML = con.responseText;
                }
            } 
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getDivisiKebun() {
    idKebun = document.getElementById('idKebun').value;
    param = 'idKebun='+idKebun;
    param += '&method=getDivisiKebun';
    
    tujuan='kebun_slave_2laporanmonograf.php';
    post_response_text(tujuan, param, respog);
    function respog() 
    {
        if (con.readyState == 4) 
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) 
                {
                    alertify.alert(con.responseText);
                } 
                else 
                {   
                    document.getElementById('afdeling').innerHTML = con.responseText;
                }
            } 
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function preview(jenis) {
    pt          = document.getElementById('pt').value;
    idKebun     = document.getElementById('idKebun').value;
    afdeling    = document.getElementById('afdeling').value;
    idlaporan   = document.getElementById('idlaporan').value;
    tipelaporan = document.getElementById('tipelaporan').value;
    periode     = document.getElementById('periode').value;

    param='method=preview';
    param+='&jenis='+jenis;
    param+='&pt='+pt;
    param+='&idKebun='+idKebun;
    param+='&afdeling='+afdeling;
    param+='&idlaporan='+idlaporan;
    param+='&tipelaporan='+tipelaporan;
    param+='&periode='+periode;

    tujuan='kebun_slave_2laporanmonograf.php';
    post_response_text(tujuan, param, respog);
    function respog() 
    {
        if (con.readyState == 4) 
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) 
                {
                    alertify.alert(con.responseText);
                } 
                else 
                {   
                    if (jenis == 'html') {
                        document.getElementById('printContainer').innerHTML = con.responseText;
                        leftFixedTable();
                    } else {
                        tujuan=tujuan+"?"+param;  
                        printnopopup(tujuan);
                    }
                }
            }
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printnopopup(url) {
    // alert(url);
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}