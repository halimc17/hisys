// JavaScript Document

function tampilhilang(idnya){
//    showById(idnya);
}

function getkud()
{
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	param='&kebun='+kebun;
	tujuan='kebun_2kavling_slave';
	post_response_text(tujuan+'.php?proses=getkud', param, respon);
//	alert(param);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    pisah=con.responseText.split('###');
					document.getElementById('divisi').innerHTML=pisah[0];
					// document.getElementById('mandor').innerHTML=pisah[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function getMandor()
// {
// 	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
// 	divisi=document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
// 	param='&kebun='+kebun+'&divisi='+divisi;
// 	tujuan='kebun_slave_getmandor';
// 	post_response_text(tujuan+'.php?proses=getMandor', param, respon);
// //	alert(param);
// 	function respon() {
//         if (con.readyState == 4) {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alert(con.responseText);
//                 } else {
//                     // Success Response
//                     document.getElementById('mandor').innerHTML=con.responseText;
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }

function Clear1()
{
	document.getElementById('kebun').value='';
    document.getElementById('divisi').innerHTML='<option value=""></option>';
	// document.getElementById('tanggal').value='';
	// document.getElementById('tanggal2').value='';
	document.getElementById('printContainer').innerHTML='';
	mandor();
}
