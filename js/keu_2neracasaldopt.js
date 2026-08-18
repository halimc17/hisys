
function preview(){

	periode=trim(document.getElementById('periode').value);
	periode1=trim(document.getElementById('periode1').value);
	akundari=trim(document.getElementById('akundari').value);
	akunsampai=trim(document.getElementById('akunsampai').value);
	tampilanId=trim(document.getElementById('tampilanId').value);
	tipe='html';
	param='method=preview'+'&periode='+periode+'&periode1='+periode1;
	param+='&akundari='+akundari+'&akunsampai='+akunsampai+'&tipe='+tipe;
	param+='&tampilanId='+tampilanId;
	tujuan='keu_2neracasaldopt_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {	
					document.getElementById('printContainer').innerHTML=con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}



function excel(){
	periode=trim(document.getElementById('periode').value);
	periode1=trim(document.getElementById('periode1').value);
	akundari=trim(document.getElementById('akundari').value);
	akunsampai=trim(document.getElementById('akunsampai').value);
	tampilanId=trim(document.getElementById('tampilanId').value);
	tipe='excel';
	param='method=preview'+'&periode='+periode+'&periode1='+periode1;
	param+='&akundari='+akundari+'&akunsampai='+akunsampai+'&tipe='+tipe;
	param+='&tampilanId='+tampilanId;
	ev='event';
	tujuan='keu_2neracasaldopt_slave.php';
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev);	
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}



function batal(){
    document.getElementById('tgl1').value='';	
    document.getElementById('tgl2').value='';
	document.getElementById('tgljurnal11').value='';	
    document.getElementById('tgljurnal12').value='';	
    document.getElementById('kodebarang').value='';	
    document.getElementById('kodecustomer').value='';	
    document.getElementById('noakun').value='';	
    document.getElementById('printContainer').innerHTML='';	
}


