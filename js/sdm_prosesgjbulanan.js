function getValue(id) {
    var tmp = document.getElementById(id);

    if (tmp) {
        if (tmp.options) {
            return tmp.options[tmp.selectedIndex].value;
        } else if (tmp.nodeType == 'checkbox') {
            if (tmp.checked == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return tmp.value;
        }
    } else {
        return false;
    }
}

/* Show Data List */
function list() {

    var listCon = document.getElementById('listContainer');
    var postBtn = document.getElementById('postBtn');
    //var postBtn = document.getElementById('postBtn');
    var param = "periodegaji=" + getValue('periodegaji');
    param += "&kodeorg=" + getValue('kodeorg');


    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    //postBtn.setAttribute('disabled','disabled');
                    // alertify.alert(con.responseText);
                    alertify.popup2("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('40%', '80%');
                } else {
                    //=== Success Response
                    listCon.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    post_response_text('sdm_slave_prosesgjbulanan.php?proses=list', param, respon);
}

function excel() {
    var listCon = document.getElementById('listContainer');
    var postBtn = document.getElementById('postBtn');
    //var postBtn = document.getElementById('postBtn');
    var param = "periodegaji=" + getValue('periodegaji');
    param += "&kodeorg=" + getValue('kodeorg');
    param += "&proses=excel";

    tipe = 'excel';
    tujuan = 'sdm_slave_prosesgjbulanan.php';
    ev = 'event';
    judul = 'Report Ms.Excel';

    printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '900';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
    showDialog1(title, content, width, height, ev);
}

/* Post */
function post() {
    var param = "periodegaji=" + getValue('periodegaji');
    param += "&kodeorg=" + getValue('kodeorg');

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //=== Success Response
                    alertify.alert('Proses Gaji Success........');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    post_response_text('sdm_slave_prosesgjbulanan.php?proses=post', param, respon);
}