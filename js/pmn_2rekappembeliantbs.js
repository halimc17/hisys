/*
function gettipe() {
    kodeunit = document.getElementById("kodeunit").value;
    param = "method=gettipe&kodeunit="+kodeunit;
    tujuan = "pmn_2rekappembeliantbs_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    // alert(con.responseText);
                    document.getElementById('tipetbs').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
*/

function cancel(){
	document.getElementById('tanggalmulai').value='';
	document.getElementById('tanggalsampai').value='';
	document.getElementById('container').innerHTML='';
	setValue2('kodeunit',null);
	setValue2('tipetbs',null);
}

function preview(tipe){
    tanggalmulai=document.getElementById('tanggalmulai').value;
    tanggalsampai=document.getElementById('tanggalsampai').value;
    kodeunit=document.getElementById('kodeunit').value;
    tipetbs=document.getElementById('tipetbs').value;
	method='preview';
    param='tanggalsampai='+tanggalsampai+'&tanggalmulai='+tanggalmulai+'&kodeunit='+kodeunit+'&tipetbs='+tipetbs+'&tipe='+tipe;
	param += '&method=' + method;
    tujuan='pmn_2rekappembeliantbs_slave.php';
	if(tipe=='excel'){
		printnopopup(tujuan+'?'+param);
	} else if(tipe=='pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_2rekappembeliantbs_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
	} else{
		post_response_text(tujuan, param, respog);
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						if(tipe=='html'){
							document.getElementById('container').innerHTML=con.responseText;
							leftFixedTable();
						}
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}	
}
