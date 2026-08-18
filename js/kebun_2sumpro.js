function preview(tipeprint, ev) {
    // pt = getValue('pt');
    // periode2 = getValue('periode2');
    region = getValue('region');
    // kebun = getValue('kebun');
    tanggal = getValue('tanggal');

    param = 'method=preview&tipeprint=' + tipeprint + '&region=' + region +  '&tanggal=' + tanggal;
    // alert(param); 
    tujuan = 'kebun_slave_2sumpro.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                    alertify.alert('Informasi', con.responseText);
                } else {
                    if (tipeprint == 'html') {
                        document.getElementById('container').innerHTML = con.responseText;
                        // leftFixedTable();
                        document.getElementById('container').style.display = 'block';
                        // alert(con.responseText);
                    } else if (tipeprint == 'excel') {
                        tujuan = tujuan + "?" + param;
                        printnopopup(tujuan);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getkebun() {
    region = document.getElementById('region').value;
    param = 'method=getkebun' + '&region=' + region;
    tujuan = 'kebun_slave_2sumpro.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('kebun').innerHTML = con.responseText;
                }
            }
            else {
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