function cancel(){
	document.getElementById('unit').value='';
	document.getElementById('tipe').value='';
	document.getElementById('posting').value='';
	document.getElementById('tanggal1').value='';
	document.getElementById('tanggal2').value='';
	document.getElementById('nopo').value='';
	document.getElementById('container').innerHTML='';
}


function previewpdfgr(ev,notransaksi){	
	param='method=previewpdfgr&notransaksi='+notransaksi;
    tujuan = 'log_slave_noninventory.php';
    
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_noninventory.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
	// title='Report PDF';
	// tujuan=tujuan+"?"+param;  
	// width = 1024;
	// height = 500;
	// content = "<iframe frameborder=0 width=1024px height=500px src='" + tujuan + "'></iframe>"
	// showDialog5(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic5').style.top = pos[1] + 'px';
	// document.getElementById('dynamic5').style.left = (pos[0]-600) + 'px';
}

function previewpdfgrba(ev,notransaksi){	
	param='method=previewpdfgrba&notransaksi='+notransaksi;
    tujuan = 'log_slave_noninventory.php';
    
	title='Report PDF';
	tujuan=tujuan+"?"+param;  
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_noninventory.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
	// width = 1024;
	// height = 500;
	// content = "<iframe frameborder=0 width=1024px height=500px src='" + tujuan + "'></iframe>"
	// showDialog5(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic5').style.top = pos[1] + 'px';
	// document.getElementById('dynamic5').style.left = (pos[0]-600) + 'px';
}

function preview(tipelaporan){
    nopo=document.getElementById('nopo').value;
    unit=document.getElementById('unit').value;
    tipe=document.getElementById('tipe').value;
    posting=document.getElementById('posting').value;
    tanggal1=document.getElementById('tanggal1').value;
    tanggal2=document.getElementById('tanggal2').value;
	method='preview';
    param='tipe='+tipe+'&unit='+unit+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&tipelaporan='+tipelaporan+'&posting='+posting+'&nopo='+nopo;
	param += '&method=' + method;
    tujuan='log_2penerimaannoninv_slave.php';
	if(tipelaporan!='html'){
		judul=tipelaporan;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
	
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					if(tipelaporan=='html'){
						document.getElementById('container').innerHTML=con.responseText;
						leftFixedTable();
					}
					// leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}


function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
