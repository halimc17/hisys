
function html1()
{
    kdorg=document.getElementById('kdorg').value;
    per2=document.getElementById('per2').value;
    per1=document.getElementById('per1').value;
    divisi=document.getElementById('divisi').value;
    noakun=document.getElementById('noakun').value;
    tt=document.getElementById('tt').value;
    status=document.getElementById('status').value;
    param = 'method=html1';
    param += '&kdorg=' + kdorg+'&per2=' + per2+'&divisi=' + divisi + '&noakun=' +noakun+ '&per1=' +per1+'&tt='+tt+'&status='+status;
    tujuan = 'kebun_slave_2biaya.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('html1').style.display = 'block';
                    document.getElementById('html1').innerHTML = con.responseText;
					document.getElementById('html2').style.display = 'none';
					document.getElementById('html3').style.display = 'none';
					document.getElementById('html4').style.display = 'none';
					
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}