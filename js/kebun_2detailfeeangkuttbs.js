function getlaporan(ev,tipelaporan){
	kebun=document.getElementById('kebun').value;
	// periode=document.getElementById('periode').value;
	tgl1=document.getElementById('tgl1').value;
	tgl2=document.getElementById('tgl2').value;
	penerimafee=document.getElementById('penerimafee').value;

	param='tipelaporan='+tipelaporan+'&kebun='+kebun+'&tgl1='+tgl1+'&tgl2='+tgl2+'&penerimafee='+penerimafee+'&method=getlaporan';
	tujuan='kebun_slave_2detailfeeangkuttbs.php';
    
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
						leftFixedTable();
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