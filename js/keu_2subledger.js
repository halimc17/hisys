/**
 * @author repindra.ginting
 */
 
function canceldetail(){
	document.getElementById('container').innerHTML='';
	document.getElementById('unitdt').value='';
	document.getElementById('tanggal1dt').value='';
}	

function cancel(){
	document.getElementById('container').innerHTML='';
	document.getElementById('unit').value='';
	document.getElementById('tanggal1').value='';
}	
 
 
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
//    width='900';
//    height='400';
//    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
//    showDialog1(title,content,width,height,ev); 	
   alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('50%','70%');
}


function laporan(tipe,method){
	unit	=document.getElementById('unit').value;
	noakun	=document.getElementById('noakun').value;
	tanggal1	=document.getElementById('tanggal1').value;
	tanggal2	=document.getElementById('tanggal2').value;
	method='preview';
	param='unit='+unit+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&method='+method+'&tipe='+tipe+'&noakun='+noakun;
	tujuan='keu_2subladger_slave.php';
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
				} else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

