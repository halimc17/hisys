
function detailspk(notransaksi, kodeorg, koderekanan, divisi, ev) {
    param = 'proses=pdf';
    param += '&notransaksi=' + notransaksi + '&kodeorg=' + kodeorg + '&koderekanan=' + koderekanan + '&divisi=' + divisi;
    title = "Data Detail";
    showDialog1(title, "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='log_slave_realisasispk_print_detail.php?" + param + "'></iframe>", '800', '400', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detaildata(notransaksi, ev, tipe, blok) {
    if (tipe == '') {
        return;
    }
    if (tipe == 'PNN') {
        param = "method=panen&tipe=" + tipe + "&notransaksi=" + notransaksi + "&blok=" + blok;
        title = "Data Detail";
        showDialog1(title, "<iframe frameborder=0 style='width:795px;height:400px'" +
            " src='kebun_slave_2bpb_lv5.php?" + param + "'></iframe>", '800', '400', ev);
    } else {
        param = "proses=html&tipe=" + tipe + "&notransaksi=" + notransaksi;
        param += "&jenis=html&blok=" + blok;
        title = "Data Detail";
        //file='kebun_slave_operasional_print_detail';
        showDialog1(title, "<iframe frameborder=0 style='width:795px;height:400px'" +
            " src='kebun_slave_operasional_print_detailx.php?" + param + "'></iframe>", '800', '400', ev);
    }

    //showDialog1(title,"<iframe frameborder=0 style='width:795px;height:400px'"+
    //" src='kebun_slave_operasional_print_detail_panen.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

/****************************************************************************************************************************/

function detailvhc(notransaksi, ev) {
    param = 'method=detailvhc';
    param += '&notransaksi=' + notransaksi;
    title = "Data Detail";
    showDialog1(title, "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='kebun_slave_2bpb_lv5.php?" + param + "'></iframe>", '800', '400', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailjurnal(nojurnal, blok, kdkeg, ev, per2) {
    param = 'method=detailjurnal';
    param += '&blok=' + blok + '&nojurnal=' + nojurnal + '&kdkeg=' + kdkeg;
    if (per2) {
        param += '&per2=' + per2;
    }
    title = "Data Detail";
    showDialog1(title, "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='kebun_slave_2bpb_lv5.php?" + param + "'></iframe>", '800', '400', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailbarang(blok, per2, kdkeg, kdbrg, ev, notransaksi) {
    param = 'method=detailbarang';
    param += '&blok=' + blok + '&per2=' + per2 + '&kdkeg=' + kdkeg + '&kdbrg=' + kdbrg;
    if (notransaksi) {
        param += '&notransaksi=' + notransaksi;
    }

    //param = "method=detailjurnal&blok="+blok+"&nojurnal="+nojurnal;
    title = "Data Detail";
    showDialog1(title, "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='kebun_slave_2bpb_lv5.php?" + param + "'></iframe>", '800', '400', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';

}

/*
function detailjurnal(nojurnal,blok,ev){

//content= "<div id=detailjurnal style=\"height:400px;width:800;\"></div>";
content= "<div id=detailjurnal style=\"height:400px;width:800;overflow:scroll;\"></div>";
title='Detail Jurnal';
height='400';
width='800';
showDialog1(title,content,width,height,ev);
getdetailjurnal(nojurnal,blok);
}


function getdetailjurnal(nojurnal,blok){
param='method=getdetailjurnal';
param += '&blok=' + blok+'&nojurnal=' + nojurnal;
tujuan = 'kebun_slave_2bpb_lv5.php';
post_response_text(tujuan, param, respog);
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
document.getElementById('detailjurnal').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}

}
 */

/****************************************************************************************************************************/

/*
function detailbarang(blok,per2,kdkeg,kdbrg,ev)
{

//content= "<div id=detailbarang style=\"height:400px;width:800;\"></div>";
//content= "<div id=detailbarang style=\"height:400px;width:800;\"></div>";
content= "<div id=detailbarang style=\"height:400px;width:800;overflow:scroll;\"></div>";
title='Detail Material';
height='400';
width='800';
showDialog1(title,content,width,height,ev);
getdetailbarang(blok,per2,kdkeg,kdbrg);
}


function getdetailbarang(blok,per2,kdkeg,kdbrg)
{
param='method=getdetailbarang';
param += '&blok=' + blok+'&per2=' + per2+'&kdkeg=' + kdkeg+'&kdbrg=' + kdbrg;
tujuan = 'kebun_slave_2bpb_lv5.php';
post_response_text(tujuan, param, respog);
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
document.getElementById('detailbarang').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}

}
 */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function html1() {
    kdorg = document.getElementById('kdorg').value;
    per2 = document.getElementById('per2').value;
    divisi = document.getElementById('divisi').value;
    param = 'method=html1';
    param += '&kdorg=' + kdorg + '&per2=' + per2 + '&divisi=' + divisi;
    tujuan = 'kebun_slave_2bpb_lv1.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('atas').style.display = 'block';
                    document.getElementById('html1').style.display = 'block';
                    document.getElementById('both_report').style.display = 'block';
                    document.getElementById('html1').innerHTML = con.responseText;
                    leftFixedTable();
                    document.getElementById('html2').style.display = 'none';
                    document.getElementById('html3').style.display = 'none';
                    document.getElementById('html4').style.display = 'none';

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function excel1(ev) {
    kdorg = document.getElementById('kdorg').value;
    per2 = document.getElementById('per2').value;
    param = 'method=excel1';
    param += '&kdorg=' + kdorg + '&per2=' + per2;
    tujuan = 'kebun_slave_2bpb_lv1.php';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev)
}

/************************************************************/

function html2(blok, per2) {

    param = 'method=html2';
    param += '&blok=' + blok + '&per2=' + per2;
    tujuan = 'kebun_slave_2bpb_lv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    document.getElementById('atas').style.display = 'none';
                    document.getElementById('html1').style.display = 'none';
                    document.getElementById('both_report').style.display = 'none';
                    document.getElementById('html2').style.display = 'block';
                    document.getElementById('html2').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function excel2(ev, blok, per2) {
    param = 'method=excel2';
    param += '&blok=' + blok + '&per2=' + per2;
    tujuan = 'kebun_slave_2bpb_lv2.php';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev)
}

function kehtml1() {
    document.getElementById('atas').style.display = 'block';
    document.getElementById('html1').style.display = 'block';
    document.getElementById('both_report').style.display = 'block';
    document.getElementById('html2').style.display = 'none';
    document.getElementById('html3').style.display = 'none';
    document.getElementById('html4').style.display = 'none';
}

/*********************************************************************/

function html3(blok, per2, tipe) {

    param = 'method=html3';
    param += '&blok=' + blok + '&per2=' + per2 + '&tipe=' + tipe;
    tujuan = 'kebun_slave_2bpb_lv3.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    document.getElementById('atas').style.display = 'none';
                    document.getElementById('html1').style.display = 'none';
                    document.getElementById('both_report').style.display = 'none';
                    document.getElementById('html2').style.display = 'none';
                    document.getElementById('html3').style.display = 'block';
                    document.getElementById('html3').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function excel3(ev, blok, per2, tipe) {
    param = 'method=excel3';
    param += '&blok=' + blok + '&per2=' + per2 + '&tipe=' + tipe
    tujuan = 'kebun_slave_2bpb_lv3.php';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev)
}

function kehtml2() {
    document.getElementById('html1').style.display = 'none';
    document.getElementById('both_report').style.display = 'none';
    document.getElementById('html2').style.display = 'block';
    document.getElementById('html3').style.display = 'none';
    document.getElementById('html4').style.display = 'none';

}

/*********************************************************************/

function excel4(ev, blok, per2, tipeakun, tipe) {
    param = 'method=excel4';
    param += '&blok=' + blok + '&per2=' + per2 + '&tipeakun=' + tipeakun + '&tipe=' + tipe;
    tujuan = 'kebun_slave_2bpb_lv4' + tipe + '.php';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev)
}

function html4(blok, per2, tipeakun, tipe) {

    param = 'method=html4';
    param += '&blok=' + blok + '&per2=' + per2 + '&tipeakun=' + tipeakun + '&tipe=' + tipe;
    tujuan = 'kebun_slave_2bpb_lv4' + tipe + '.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('html1').style.display = 'none';
                    document.getElementById('both_report').style.display = 'none';
                    document.getElementById('html2').style.display = 'none';
                    document.getElementById('html3').style.display = 'none';
                    document.getElementById('html4').style.display = 'block';
                    document.getElementById('html4').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function kehtml3() {
    document.getElementById('html1').style.display = 'none';
    document.getElementById('both_report').style.display = 'none';
    document.getElementById('html2').style.display = 'none';
    document.getElementById('html3').style.display = 'block';
    document.getElementById('html4').style.display = 'none';

}

function getdivisi() {

    kdorg = document.getElementById('kdorg').options[document.getElementById('kdorg').selectedIndex].value;
    param = 'kdorg=' + kdorg + '&proses=getdivisi';
    tujuan = 'kebun_slave_2bpb_option.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
