function getunit(){
	pt=document.getElementById('pt').value;
	
	param='method=getunit&pt='+pt;
	tujuan='log_slave_2monitoringposo.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('unit').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		} 
	}
}

function batal(){
	document.getElementById('printContainer').innerHTML='';
	document.getElementById('pt').selectedIndex=0;
	document.getElementById('nopp').value='';
	document.getElementById('nopo').value='';
	document.getElementById('strategis').selectedIndex=0;
	document.getElementById('purchaser').selectedIndex=0;
	getunit();
}


function getlaporan(ev,tipelaporan){
	document.getElementById('printContainer').innerHTML='';
	
	pt=document.getElementById('pt').value;
	unit=document.getElementById('unit').value;
	tipeperiode=document.getElementById('tipeperiode').value;
	tgl1=document.getElementById('tgl1').value;
	tgl2=document.getElementById('tgl2').value;
	nopp=trim(document.getElementById('nopp').value);
	nopo=trim(document.getElementById('nopo').value);
	strategis=document.getElementById('strategis').value;
	purchaser=document.getElementById('purchaser').value;

	param='tipelaporan='+tipelaporan+'&pt='+pt+'&unit='+unit+'&tgl1='+tgl1+'&tgl2='+tgl2+'&method=getlaporan';
	param += '&nopp=' + nopp + '&nopo=' + nopo+'&strategis='+strategis+'&tipeperiode='+tipeperiode+'&purchaser='+purchaser;
	tujuan='log_slave_2monitoringposo.php';
    
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

function simpanstatus(nopp,kodebarang){
	ketstatus=document.getElementById('ketstatus_'+nopp+'_'+kodebarang).value;
	
	param='method=simpanstatus&ketstatus='+ketstatus+'&nopp='+nopp+'&kodebarang='+kodebarang;
	tujuan='log_slave_2monitoringposo.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Berhasil disimpan");
					loadstatus(nopp,kodebarang);
					document.getElementById('ketstatus_'+nopp+'_'+kodebarang).value="";
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		} 
	}
}

function loadstatus(nopp,kodebarang){
	param='method=loadstatus&nopp='+nopp+'&kodebarang='+kodebarang;
	tujuan='log_slave_2monitoringposo.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('tdketstatus_'+nopp+'_'+kodebarang).innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		} 
	}
}