
function detail(kodeurut,periode,kodept,regional,kodeunit,tipe,ev) {
	param = 'method=preview' + '&kodeurut=' + kodeurut + '&periode=' + periode + '&kodept=' + kodept + '&regional=' + regional + '&kodeunit=' + kodeunit + '&tipe=' + tipe;
	tujuan	='keu_2laporankeuangankonsolcashflow_slave_detail.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
				}
				else {
					alertify.popup2().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			}
			else {
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


function getlaporan(tipe){
	kodept	=document.getElementById('kodept').value;
	periode =document.getElementById('periode').value;
	param='kodept='+kodept+'&periode='+periode+'&tipe='+tipe;
	tujuan='keu_2group2_slave.php';
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
						alert('ERROR TRANSACTION,\n' + con.responseText);
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