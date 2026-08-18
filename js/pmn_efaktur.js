function preview(){
	pt = document.getElementById('pt').value;
	tglawal = document.getElementById('tglawal').value;
	tglakhir = document.getElementById('tglakhir').value;
	
	param='method=preview'+'&pt='+pt+'&tglawal='+tglawal+'&tglakhir='+tglakhir;
	tujuan='pmn_slave_efaktur.php';

	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function excel(ev,tujuan){
	pt = document.getElementById('pt').value;
	tglawal = document.getElementById('tglawal').value;
	tglakhir = document.getElementById('tglakhir').value;
	
	judul='Report Ms.Excel';	
    param='method=excel'+'&pt='+pt+'&tglawal='+tglawal+'&tglakhir='+tglakhir;
	printFile(param,tujuan,judul,ev)	
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev); 	
}