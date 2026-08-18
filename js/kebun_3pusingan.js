maxf = 0
sekarang = 1;
function saveall(maxRow) 
{
    maxf = maxRow;
    loopsave(1, maxRow);
}
function loopsave(currRow, maxRow)
{
    bloks = trim(document.getElementById('bloks'+ currRow).innerHTML);
    tgls = trim(document.getElementById('tgls' + currRow).innerHTML);
    angkas = trim(document.getElementById('angkas' + currRow).innerHTML);
    kets = trim(document.getElementById('kets' + currRow).innerHTML);
    
    param = 'bloks=' + bloks + '&tgls=' + tgls + '&angkas=' + angkas + '&kets=' + kets;
    param += "&proses=savedata";

    tujuan = 'kebun_slave_3pusingan.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
    //lockScreen('wait');

    function respog() {
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                }
                else {
                    document.getElementById('row' + currRow).style.display = 'none';
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow)
                    {
                        alert('Done');
                    }
                    else
                    {
                        loopsave(currRow, maxRow);
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