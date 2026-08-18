function getlaporan(ev,tipelaporan){
	unit=document.getElementById('unit').value;
	divisi=document.getElementById('divisi').value;
	tgl1=document.getElementById('tgl1').value;
	tgl2=document.getElementById('tgl2').value;
	sistemgaji=document.getElementById('sistemgaji').value;
	idkomponen=document.getElementById('idkomponen').value;

	param='tipelaporan='+tipelaporan+'&unit='+unit+'&divisi='+divisi+'&tgl1='+tgl1+'&tgl2='+tgl2+'&sistemgaji='+sistemgaji+'&idkomponen='+idkomponen+'&method=getlaporan';
	tujuan='sdm_slave_2perkomponengaji.php';
    
	if(tipelaporan=='html'){
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState == 4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert(con.responseText);
					}else{
						document.getElementById('printContainer').innerHTML=con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}else if(tipelaporan=='pdf'){
		title='Report PDF';
        tujuan=tujuan+"?"+param;  
		width = 1024;
		height = 400;
		content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
	}else{
		title='Report Ms.Excel';
        tujuan=tujuan+"?"+param;  
		width='200';
		height='50';
		content="<iframe frameborder=0 src='"+tujuan+"'></iframe>"
		showDialog1(title,content,width,height,ev); 
	}    
}