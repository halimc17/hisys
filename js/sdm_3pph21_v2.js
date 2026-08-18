function preview(tipe) {
    unit = document.getElementById('unit').value;
    per = document.getElementById('per').value;
    istjpph21 = 0;

    if (document.getElementById('istjpph21').checked) {
        istjpph21 = 1;
    }

    nilaitjpph21 = document.getElementById('nilaitjpph21').value;
    param = 'proses=preview' + '&unit=' + unit + '&per=' + per + '&tipe=' + tipe + '&istjpph21=' + istjpph21 + '&nilaitjpph21=' + nilaitjpph21;
    tujuan = 'sdm_slave_3pph21_v2.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    arr = con.responseText.split("####");
                    document.getElementById('printContainer').innerHTML = arr[0];
                    //  if(tipe=='previewawal'){
                    // prosespph(arr[1]);
                    //  }
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function makeenabled() {
    if (document.getElementById('istjpph21').checked) {
        document.getElementById('nilaitjpph21').disabled = false;
    }
    else {

        document.getElementById('nilaitjpph21').disabled = true;
    }
}

maxf = 0
sekarang = 1;
function prosespph(maxRow) {
    maxf = maxRow;
    looppph(1, maxRow);
}

function looppph(currRow, maxRow) {
    karyawanid = trim(document.getElementById('karyawanid' + currRow).innerHTML);
    pph21x = trim(document.getElementById('pph21x' + currRow).innerHTML.replace(',', ''));
    tjpph21x = trim(document.getElementById('tjpph21x' + currRow).innerHTML.replace(',', ''));
    unit = document.getElementById('unit').value;
    per = document.getElementById('per').value;


    param = 'per=' + per + '&karyawanid=' + karyawanid + '&unit=' + unit + '&pph21x=' + pph21x + '&tjpph21x=' + tjpph21x + '&baris=' + currRow;
    param += "&proses=prosespph";
    tujuan = 'sdm_slave_3pph21_v2.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
    function respog() {
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                }
                else {

                    arr = con.responseText.split("####");

                    if (arr[2] != arr[3]) {
                        currRow = currRow;
                    } else {
                        document.getElementById('row' + currRow).style.display = 'none';
                        currRow += 1;
                    }
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alert('Done');
                        //preview();
                    } else {
                        looppph(currRow, maxRow);
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



function excel() {
    unit = document.getElementById('unit').value;
    per = document.getElementById('per').value;
    istjpph21 = 0;

    if (document.getElementById('istjpph21').checked) {
        istjpph21 = 1;
    }

    nilaitjpph21 = document.getElementById('nilaitjpph21').value;

    tipe = 'excel';
    tujuan = 'sdm_slave_3pph21_v2.php';
    ev = 'event';
    judul = 'Report Ms.Excel';
    param = 'proses=preview' + '&unit=' + unit + '&per=' + per + '&tipe=' + tipe + '&istjpph21=' + istjpph21 + '&nilaitjpph21=' + nilaitjpph21;
    printFile(param, tujuan, judul, ev);
}


function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '900';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
    showDialog1(title, content, width, height, ev);
}

