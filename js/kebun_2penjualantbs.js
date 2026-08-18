
function getunit() {
	kodept=document.getElementById('kodept').value;
	method = 'getunit';
	param='';
	param += '&kodept=' + kodept + '&method=' + method;
	tujuan = 'kebun_2penjualantbs_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
                } else {
                    document.getElementById('kodeunit').innerHTML = con.responseText;
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancel(){
	document.getElementById('kodept').value='';
	document.getElementById('kodecustomer').value='';
	document.getElementById('tanggal2').value='4';
	document.getElementById('kodeunit').value='';
	document.getElementById('tanggal1').value='';
	document.getElementById('container').innerHTML='';
	setValue2('kodept',null);
	setValue2('kodecustomer',null);
	setValue2('kodeunit',null);
}

function preview(tipe){
    kodept=document.getElementById('kodept').value;
    kodeunit=document.getElementById('kodeunit').value;
    kodecustomer=document.getElementById('kodecustomer').value;
    tanggal1=document.getElementById('tanggal1').value;
    tanggal2=document.getElementById('tanggal2').value;
	method='preview';
    param='kodecustomer='+kodecustomer+'&kodept='+kodept+'&tipe='+tipe;
	param += '&method=' + method+'&kodeunit='+kodeunit+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2;
    tujuan='kebun_2penjualantbs_slave.php';
	if(tipe=='excel'){
		printnopopup(tujuan+'?'+param);
	} else if(tipe=='pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_2penjualantbs_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');		
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
