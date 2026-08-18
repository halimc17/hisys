
function getdivisi(){
    kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
    param='&kebun='+kebun;
    tujuan='sdm_slave_2rekapborong';
    post_response_text(tujuan+'.php?proses=getAfdeling', param, respon);
//  alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('kdUnit').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getMandor()
{
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	divisi=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	param='&kebun='+kebun+'&divisi='+divisi;
	tujuan='kebun_slave_getmandor';
	post_response_text(tujuan+'.php?proses=getMandor', param, respon);
//	alert(param);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('mandor').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPeriode()
{
    kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
    divisi=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
    param='&kebun='+kebun+'&divisi='+divisi;
    tujuan='kebun_slave_getmandor';
    post_response_text(tujuan+'.php?proses=getMandor', param, respon);
//  alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('mandor').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function Clear1()
{
	document.getElementById('kebun').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggal2').value='';
	document.getElementById('printContainer').innerHTML='';
	mandor();
}
