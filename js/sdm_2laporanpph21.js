function preview(tipe){
	unit=document.getElementById('unit').value;
	per=document.getElementById('per').value;
    istjpph21=0;

    if(document.getElementById('istjpph21').checked){
    istjpph21=1;
    }

    nilaitjpph21=document.getElementById('nilaitjpph21').value;
    param='proses=preview'+'&unit='+unit + '&per=' + per+ '&tipe=' + tipe+ '&istjpph21=' + istjpph21+ '&nilaitjpph21=' + nilaitjpph21; 
    tujuan = 'sdm_slave_2laporanpph21.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
					arr = con.responseText.split("####");
                    document.getElementById('printContainer').innerHTML=arr[0];
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
	unit=document.getElementById('unit').value;
	per=document.getElementById('per').value;
    istjpph21=0;

    if(document.getElementById('istjpph21').checked)
    {
    istjpph21=1;
    }

    nilaitjpph21=document.getElementById('nilaitjpph21').value;
    
	tipe='excel';
	tujuan = 'sdm_slave_2laporanpph21.php';
	ev='event';
	judul='Report Ms.Excel';	
	param='proses=preview'+'&unit='+unit + '&per=' + per+ '&tipe=' + tipe+ '&istjpph21=' + istjpph21+ '&nilaitjpph21=' + nilaitjpph21; 
    printFile(param,tujuan,judul,ev);	
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

