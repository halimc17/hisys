
function cancel(){
	document.getElementById('container').innerHTML='';
	setValue2('kodebarang',null);
	setValue2('tahun',null);
}

function detail(kodept,kodebarang,periode,tipe){
	method='detail';
    param='kodept='+kodept+'&periode='+periode+'&kodebarang='+kodebarang+'&tipe='+tipe;
	param += '&method=' + method;
	tujuan='pmn_2summarydelivery_slave.php';
	if(tipe=='excel'){
		printnopopup(tujuan+'?'+param);
	}else{
		post_response_text(tujuan, param, respog);
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','85%'); 
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}
}

function preview(tipe){
    kodebarang=document.getElementById('kodebarang').value;
    tahun=document.getElementById('tahun').value;
	method='preview';
    param='tahun='+tahun+'&kodebarang='+kodebarang+'&tipe='+tipe;
	param += '&method=' + method;
    tujuan='pmn_2summarydelivery_slave.php';
	if(tipe=='excel'){
		printnopopup(tujuan+'?'+param);
	} else if(tipe=='pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_2summarydelivery_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
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
