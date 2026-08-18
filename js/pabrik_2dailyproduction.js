

function cancel(){
	document.getElementById('container').innerHTML='';
	setValue2('kodeunit',null);
	setValue2('periode',null);
	setValue2('satuan',null);
}

function preview(tipe){
    periode=document.getElementById('periode').value;
    kodeunit=document.getElementById('kodeunit').value;
    satuan=document.getElementById('satuan').value;
	method='preview';
    param='kodeunit='+kodeunit+'&periode='+periode+'&tipe='+tipe+'&satuan='+satuan;
	param += '&method=' + method;
    tujuan='pabrik_2dailyproduction_slave.php';
	if(tipe=='excel'){
		printnopopup(tujuan+'?'+param);
	} else if(tipe=='pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pabrik_2dailyproduction_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
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
							// leftFixedTable();
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
