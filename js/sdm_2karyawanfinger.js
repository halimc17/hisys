function getsubunit() {
    unit = document.getElementById("unit").value;

    param = "method=getsubunit&unit=" + unit;
    tujuan = "sdm_slave_2karyawanfinger.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("subunit").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function preview(tipeprint, ev) {
    unit = document.getElementById("unit").value;
    subunit = document.getElementById("subunit").value;
    periode = document.getElementById("periode").value;
    shiftkar = document.getElementById("shiftkar").value;

    param = "method=preview&tipeprint=" + tipeprint + "&unit=" + unit + "&subunit=" + subunit + "&periode=" + periode + "&shiftkar=" + shiftkar;

    tujuan = "sdm_slave_2karyawanfinger.php";
    if (tipeprint != "html") {
        judul = tipeprint;
        ev = "event";
        printFile(param, tujuan, judul, ev);
    }
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("printContainer").innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = "";
    height = "";
    content =
        "<iframe frameborder=0 width=100% height=100% src='" +
        tujuan +
        "'></iframe>";
    showDialog1(title, content, width, height, ev);
}
