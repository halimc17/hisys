function getlaporan(ev,tipelaporan){
	kebun=document.getElementById('kebun').value;
	periode=document.getElementById('periode').value;
	periodebayar=document.getElementById('periodebayar').value;
	
	param='tipelaporan='+tipelaporan+'&kebun='+kebun+'&periode='+periode+'&periodebayar='+periodebayar+'&method=getlaporan';
	tujuan='kebun_slave_2byrangkuttbs.php';
    
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
		// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		// showDialog1(title, content, width, height, ev);
		alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}else{
		title='Report Ms.Excel';
        tujuan=tujuan+"?"+param;  
		// width='200';
		// height='50';
		// content="<iframe frameborder=0 src='"+tujuan+"'></iframe>"
		// showDialog1(title,content,width,height,ev); 
		
		printnopopup(tujuan);
	}    
}

function printreport(ev,periode,kebun,kontraktor,tipelaporan){
	param='tipelaporan='+tipelaporan+'&periode='+periode+'&kebun='+kebun+'&kontraktor='+kontraktor+'&method=printreport';
	tujuan='kebun_slave_2byrangkuttbs.php';
    
	if(tipelaporan=='html'){
		title='Report HTML';
		//showdetaillink(ev);
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						// document.getElementById('contDetail').innerHTML = con.responseText;
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
				} else {
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
		// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>";
		// showDialog2(title, content, width, height, ev);
		
		alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}else{
		title='Report Ms.Excel';
        tujuan=tujuan+"?"+param;  
		width='200';
		height='50';
		// content="<iframe frameborder=0 src='"+tujuan+"'></iframe>";
		// showDialog1(title,content,width,height,ev); 
		
		printnopopup(tujuan);
	}
}

function showdetaillink(ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>Pembayaran Angkutan TBS</legend><div id=contDetail style='overflow:auto;width:auto;height:auto;'></div></fieldset>";
	showDialog1(title, content, width, height, ev);
}