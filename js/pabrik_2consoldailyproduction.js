function preview(tanggal) {
    param = 'method=preview' + '&tanggal=' + tanggal;
    tujuan = 'pabrik_slave_2consoldailyproduction.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddata() {
    periode = document.getElementById('periode').value;
    param = 'method=loaddata' + '&periode=' + periode;
    tujuan = 'pabrik_slave_2consoldailyproduction.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    kembali();
                    document.getElementById('container').innerHTML = con.responseText;
                    document.getElementById('filterxxx').style.display = "none";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showfilter(){
	document.getElementById('filterxxx').style.display = "block";	
}
function kembali() {
    document.getElementById('tampil').style.display = "none";
    document.getElementById('tampil').innerHTML = "";
    document.getElementById('container').style.display = "";
}
function preview(tanggal) {
    param = 'tanggal=' + tanggal;
    param += '&tipe=preview';
    param += '&method=preview';

    tujuan = 'pabrik_slave_2consoldailyproduction.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('tampil').style.display = "";
                    document.getElementById('container').style.display = "none";
                    document.getElementById('tampil').innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    // //display window
    // title='CONSOL DAILY PROD REPORT KSP AGRO ('+tanggal+')';
    // content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
    // width='1150';
    // height='550';
    // ev = 'event';
    // showDialog1(title,content,width,height,ev);

    // var dialog = document.getElementById('dynamic1');

    // dialog.style.top = ((window.innerHeight/2) - (dialog.offsetHeight/2))+'px';
    // dialog.style.left = ((window.innerWidth/2) - (dialog.offsetWidth/2))+'px';
}

function excel(tanggal, ev) {
    param = 'tanggal=' + tanggal;
    param += '&tipe=excel';
    param += '&method=preview';
    tujuan = 'pabrik_slave_2consoldailyproduction.php?method=preview&' + param;
    //display window
    title = 'CONSOL_DAILY_PRODUCTION (' + tanggal + ')';
    width = '1150';
    height = '550';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);

    var dialog = document.getElementById('dynamic1');

    /*dialog.style.top = ((window.innerHeight/2) - (dialog.offsetHeight/2))+'px';
    dialog.style.left = ((window.innerWidth/2) - (dialog.offsetWidth/2))+'px';*/
}