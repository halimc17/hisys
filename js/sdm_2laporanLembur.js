function form(){
    width = '720';
    height = '';
    content = "<fieldset><div id=containerd style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail Lembur";
    showDialog5(title, content, width, height, ev); 
}

function detaillembur (periode,kary){
    
    param = 'proses=detaillembur' + '&periode=' + periode + '&kary=' + kary;
    tujuan = 'sdm_slave_2laporanLembur_rekap.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    // document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getTgl() {
    periode = document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
    kdOrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    //pilihan=document.getElementById('pilihan').options[document.getElementById('pilihan').selectedIndex].value;
    pilihan2 = document.getElementById('pilihan2').options[document.getElementById('pilihan2').selectedIndex].value;
    param = 'periode=' + periode + '&proses=getTgl' + '&kdUnit=' + kdOrg;
    if (pilihan2 != '') {
        param += '&pilihan2=' + pilihan2;
    }
    //    alert(param);
    tujuan = 'sdm_slave_2laporanLembur';
    post_response_text(tujuan + '.php?proses=getTgl', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    ar = con.responseText.split("###");
                    document.getElementById('tgl1').value = ar[0];
                    document.getElementById('tgl2').value = ar[1];
                    document.getElementById('tgl1').disabled = true;
                    document.getElementById('tgl2').disabled = true;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);
}
function getTgl2() {
    periode = document.getElementById('period').options[document.getElementById('period').selectedIndex].value;
    kdOrg = document.getElementById('kdeOrg').options[document.getElementById('kdeOrg').selectedIndex].value;
    //pilihan=document.getElementById('pilihan').options[document.getElementById('pilihan').selectedIndex].value;
    pilihan2 = document.getElementById('pilihan_2').options[document.getElementById('pilihan_2').selectedIndex].value;
    param = 'period=' + periode + '&proses=getTgl' + '&kdUnit=' + kdOrg;
    //alert(param);
    tujuan = 'sdm_slave_2laporanLembur_rekap';
    post_response_text(tujuan + '.php?proses=getTgl', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    ar = con.responseText.split("###");
                    document.getElementById('tgl_1').value = ar[0];
                    document.getElementById('tgl_2').value = ar[1];
                    document.getElementById('tgl_1').disabled = true;
                    document.getElementById('tgl_2').disabled = true;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);
}
function getPeriode() {
    kdOrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    //pilihan2=document.getElementById('pilihan2').options[document.getElementById('pilihan2').selectedIndex].value;
    tujuan = 'sdm_slave_2laporanLembur';
    param = 'kdOrg=' + kdOrg;
    post_response_text(tujuan + '.php?proses=getPeriode', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('periode').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getPeriode2() {
    kdOrg = document.getElementById('kdeOrg').options[document.getElementById('kdeOrg').selectedIndex].value;
    //pilihan2=document.getElementById('pilihan2').options[document.getElementById('pilihan2').selectedIndex].value;
    tujuan = 'sdm_slave_2laporanLembur_rekap';
    param = 'kdOrg=' + kdOrg;
    post_response_text(tujuan + '.php?proses=getPeriode', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('period').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getKry() {
    kdeOrg = document.getElementById('kdeOrg').options[document.getElementById('kdeOrg').selectedIndex].value;
    param = 'kdeOrg=' + kdeOrg;
    tujuan = 'sdm_slave_2laporanLembur';
    post_response_text(tujuan + '.php?proses=getKry', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('idKry').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function Clear1() {
    document.getElementById('tgl1').value = '';
    document.getElementById('tgl2').value = '';
    document.getElementById('tgl1').disabled = false;
    document.getElementById('tgl2').disabled = false;
    document.getElementById('kdOrg').value = '';
    document.getElementById('periode').value = '';
    document.getElementById('printContainer').innerHTML = '';
}
function Clear2() {
    document.getElementById('tgl_1').value = '';
    document.getElementById('tgl_2').value = '';
    document.getElementById('tgl_1').disabled = false;
    document.getElementById('tgl_2').disabled = false;
    document.getElementById('kdeOrg').value = '';
    document.getElementById('period').value = '';
    document.getElementById('idKry').innerHTML = "<option value''></option>";
    document.getElementById('printContainer').innerHTML = '';
}
function getTombol() {
    kdeOrg = document.getElementById('pilihan').options[document.getElementById('pilihan').selectedIndex].value;
    param = 'pilihan=' + kdeOrg;
    tujuan = 'sdm_slave_2laporanLembur';
    post_response_text(tujuan + '.php?proses=getTombol', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('tombolId').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printpdf() {
    kdOrg    = document.getElementById('kdOrg').value;
    periode  = document.getElementById('periode').value;
    tgl1    = document.getElementById('tgl1').value;
    tgl2    = document.getElementById('tgl2').value;
    pilihan = document.getElementById('pilihan').value;
    pilihan2 = document.getElementById('pilihan2').value;
    pilihan3 = document.getElementById('pilihan3').value;
    param   = 'proses=pdf' + '&kdOrg=' + kdOrg + '&periode=' + periode + '&tgl1=' + tgl1 + '&tgl2='+ tgl2 + '&pilihan2='+ pilihan2+ '&pilihan3='+ pilihan3+ '&pilihan='+ pilihan;
    tujuan='sdm_slave_2laporanLembur.php';
    tujuan = tujuan+'?' + param;
    content = "<iframe frameborder=0 style='width:100%;height:100%' src='" + tujuan + "'></iframe>";
    width = '1200';
    height = '5000';
    title = "";
    showDialog2(title, content, width, height, 'event');
    
}