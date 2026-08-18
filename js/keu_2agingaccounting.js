
function getunit() {
	kodept=document.getElementById('kodept').value;
	method = 'getunit';
	param='';
	param += '&kodept=' + kodept + '&method=' + method;
	tujuan = 'keu_2agingaccounting_slave.php';
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
	document.getElementById('subledger').value='';
	document.getElementById('kodept').value='';
	document.getElementById('noakun').value='';
	document.getElementById('periode').value='';
	document.getElementById('kodesupplier').value='';
	document.getElementById('bulan').value='4';
	document.getElementById('kodeunit').value='';
	
	document.getElementById('nodok').value='';
	document.getElementById('container').innerHTML='';
	setValue2('kodept',null);
	setValue2('noakun',null);
	setValue2('kodesupplier',null);
	setValue2('subledger',null);
	setValue2('periode',null);
	setValue2('kodeunit',null);
}

function preview(tipe){
    kodept=document.getElementById('kodept').value;
    noakun=document.getElementById('noakun').value;
    periode=document.getElementById('periode').value;
    subledger=document.getElementById('subledger').value;
    nodok=document.getElementById('nodok').value;
    bulan=document.getElementById('bulan').value;
    kodeunit=document.getElementById('kodeunit').value;
    kodesupplier=document.getElementById('kodesupplier').value;
	method='preview';
    param='noakun='+noakun+'&kodept='+kodept+'&periode='+periode+'&tipe='+tipe+'&kodesupplier='+kodesupplier;
	param += '&method=' + method+'&nodok='+nodok+'&bulan='+bulan+'&kodeunit='+kodeunit+'&subledger='+subledger;
	// alert(param);
    tujuan='keu_2agingaccounting_slave.php';
	// if(tipe!='html'){
		// judul=tipe;
		// ev='event';
		// printFile(param,tujuan,judul,ev);
	// }
	
	if(tipe=='excel'){
		printnopopup(tujuan+'?'+param);
	} else if(tipe=='pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_2agingaccounting_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
		// judul=tipe;
		// ev='event';
		// printFile(param,tujuan,judul,ev);
		// printnopopup(tujuan+'?'+param);
		
		/*
		showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+" src='keu_2agingaccounting_slave.php?"+param+"'></iframe>",'800','400','event');
		var dialog = document.getElementById('dynamic1');
		dialog.style.top = '50px';
		dialog.style.left = '15%';
		*/
		
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

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   // showDialog1(title,content,width,height,ev); 	
   alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_2agingaccounting_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}
