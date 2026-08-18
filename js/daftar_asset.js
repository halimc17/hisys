function loadData1(mode) {
    var pt = getValue('statCari2');
    var param = 'proses=loadData1&pt=' + pt;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    var tujuan = 'daftar_asset_slave.php';

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();

                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'daftar_asset.xls');
                    } else {
                        document.getElementById('containData1').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData2(mode) {
    var pt = getValue('statCari3');
    var param = 'proses=loadData2' + '&pt=' + pt;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    var tujuan = 'daftar_asset_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'daftar_asset.xls');
                    } else {
                        document.getElementById('containData2').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData3(mode) {
    var pt = getValue('statCari4');
    var param = 'proses=loadData3' + '&pt=' + pt;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    var tujuan = 'daftar_asset_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'daftar_asset.xls');
                    } else {
                        document.getElementById('containData3').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData4(mode) {
    var pt = getValue('statCari5');
    var param = 'proses=loadData4' + '&pt=' + pt;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    var tujuan = 'daftar_asset_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'daftar_asset.xls');
                    } else {
                        document.getElementById('containData4').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData5(mode) {
    var pt = getValue('statCari6');
    var param = 'proses=loadData5' + '&pt=' + pt;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    var tujuan = 'daftar_asset_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'daftar_asset.xls');
                    } else {
                        document.getElementById('containData5').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData6(mode) {
    var pt = getValue('statCari7');
    var param = 'proses=loadData6' + '&pt=' + pt;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    var tujuan = 'daftar_asset_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'daftar_asset.xls');
                    } else {
                        document.getElementById('containData6').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData7(mode) {
    var pt = getValue('statCari8');
    var param = 'proses=loadData7' + '&pt=' + pt;

    if (mode == 'excel') {
        param += '&mode=excel';
    }

    var tujuan = 'daftar_asset_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    if (mode == 'excel') {
                        downloadExcel(con.responseText, 'daftar_asset.xls');
                    } else {
                        document.getElementById('containData7').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function downloadExcel(data, filename) {
    var blob = new Blob([data], { type: 'application/vnd.ms-excel' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

var originalGetValue = getValue;
getValue = function(id) {
    var tmp = document.getElementById(id);
    if(tmp && tmp.multiple) {
        var vals = [];
        for(var i=0; i<tmp.options.length; i++) {
            if(tmp.options[i].selected) vals.push(tmp.options[i].value);
        }
        return vals.join(',');
    }
    return originalGetValue(id);
}

