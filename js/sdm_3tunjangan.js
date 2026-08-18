/*
sb_tot=document.getElementById('total_harga_po');
sb_tot.value=remove_comma_var(sb_tot.value);
 */
function numberFormat(number, digit) {
    number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
    //Seperates the components of the number
    var components = (parseFloat(number).toFixed(digit)).split(".");
    //Comma-fies the first part
    components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //Combines the two sections
    return components.join(".");
}

maxf = 0
    sekarang = 1;
function saveAll(maxRow) {

    maxf = maxRow;
    loopsave(1, maxRow);
}

function batal() {
    document.getElementById('per').value = '';
    document.getElementById('makan').value = 0;
    document.getElementById('printContainer').innerHTML = '';
}

function del(maxRow) {

    //jenissave=trim(document.getElementById('jenissave'+currRow).innerHTML);
    //per=document.getElementById('per').value;
    //karyawanidsave=trim(document.getElementById('karyawanidsave'+currRow).innerHTML);
    //jumlahsave=trim(document.getElementById('jumlahsave'+currRow).innerHTML);
    //kdorgsave=trim(document.getElementById('kdorgsave'+currRow).innerHTML);

    unit = trim(document.getElementById('unit').value);
    per = document.getElementById('per').value;
    jenis = trim(document.getElementById('jenis').value);
    tgl = trim(document.getElementById('tgl').value);
    tipe = document.getElementById('tipe');
    tipe = tipe.options[tipe.selectedIndex].value;

    param = 'proses=del' + '&unit=' + unit + '&per=' + per + '&jenis=' + jenis + '&tgl=' + tgl;
    param += '&tipe=' + tipe;
    tujuan = 'sdm_slave_save_3tunjangan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('container').innerHTML=con.responseText;
                    //saveAll(maxRow);
                    currRow = 1;
                    loopsave(currRow, maxRow);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //alert("Data telah terhapus !!!");
}

function loopsave(currRow, maxRow) {
    jenissave = trim(document.getElementById('jenissave' + currRow).innerHTML);
    per = document.getElementById('per').value;
    karyawanidsave = trim(document.getElementById('karyawanidsave' + currRow).innerHTML);
    jumlahsave = document.getElementById('jumlahsave' + currRow).value;
    kdorgsave = trim(document.getElementById('kdorgsave' + currRow).innerHTML);
    pengalisave = trim(document.getElementById('pengalisave' + currRow).innerHTML);
    jumlahsave = remove_comma_var(jumlahsave);

    if (per == '' || karyawanidsave == '' || jumlahsave == '' || kdorgsave == '') {
        alert("Data tidak lengkap");
        return;
    } else {
        param = 'jenissave=' + jenissave + '&per=' + per + '&karyawanidsave=' + karyawanidsave + '&jumlahsave=' + jumlahsave;
        param += "&proses=savedata" + '&kdorgsave=' + kdorgsave+ '&pengalisave=' + pengalisave;
        tujuan = 'sdm_slave_save_3tunjangan.php';
        post_response_text(tujuan, param, respog);
        document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
    }
    function respog() {
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                } else {
                    document.getElementById('row' + currRow).style.display = 'none';
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alert('Done...');
                    } else {
                        loopsave(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
                // document.getElementById('lanjut').style.display='';
                //unlockScreen();
            }
        }
    }

}

// function getPerhitungan(no) {
//     x = trim(document.getElementById('tanpapengali' + no).innerHTML);
//     y = trim(document.getElementById('pengalibawah' + no).value);
//     x = remove_comma_var(x);
//     y = remove_comma_var(y);
//     z = x * y;
//     document.getElementById('jumlahsave' + no).value = numberFormat(z);
// }

function hide() {
    jenis = trim(document.getElementById('jenis').value);
    if (jenis != 26) {
        document.getElementById('pengali').value = 1;
        document.getElementById('pengali').disabled = true;
        document.getElementById('bulanawal').disabled = false;
        document.getElementById('bulanawal').value = 1;
        document.getElementById('bulanakhir').disabled = false;
        document.getElementById('bulanakhir').value = 12;
    } else {
        document.getElementById('pengali').disabled = false;
        document.getElementById('pengali').value = 1;
        document.getElementById('bulanawal').value = 1;
        document.getElementById('bulanawal').disabled = true;
        document.getElementById('bulanakhir').value = 1;
        document.getElementById('bulanakhir').disabled = true;
    }
}

// function uang() {
//     unit = document.getElementById('unit').value;
//     param = 'unit=' + unit;
//     param += '&proses=uang';
//     tujuan = 'sdm_slave_3tunjangan.php';
//     post_response_text(tujuan, param, respog);
//     function respog() {
//         if (con.readyState == 4) {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alert(con.responseText);
//                 } else {
//                     data = con.responseText.split("####");
//                     document.getElementById('makan').value = data[0];
//                     document.getElementById('per').innerHTML = data[1];
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }