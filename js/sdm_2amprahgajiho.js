function preview(tipe){
	pt=document.getElementById('pt').value;
	per=document.getElementById('per').value;
    param='proses=preview'+'&pt='+pt + '&per=' + per+ '&tipe=' + tipe; 
    tujuan = 'sdm_slave_2amprahgajiho.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					arr = con.responseText.split("####");
                   document.getElementById('printContainer').innerHTML=arr[0];
				   if(tipe=='previewawal'){
						prosespph(arr[1]);
				   }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


maxf=0
sekarang=1;
function prosespph(maxRow){     
	maxf=maxRow;
	looppph(1,maxRow);
}

function looppph(currRow,maxRow){
    karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
	pt=document.getElementById('pt').value;
	per=document.getElementById('per').value;
	param='per='+per+'&karyawanid='+karyawanid+'&pt='+pt;
	param+="&proses=prosespph";
	tujuan = 'sdm_slave_2amprahgajiho.php';
	post_response_text(tujuan, param, respog);
	// document.getElementById('row'+currRow).style.backgroundColor='cyan';
    function respog(){
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row'+currRow).style.backgroundColor='red';
					unlockScreen();
                }
                else {
					
					arr = con.responseText.split("####");
					
					if(arr[2]!=arr[3]){
						currRow=currRow;
					}else{
						// document.getElementById('row'+currRow).style.display='none';
						currRow+=1;
					}
                    sekarang=currRow;
                    if(currRow>maxRow) {
						// alert('Done');
						preview();
                    }  else {
						looppph(currRow,maxRow);
                    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
	
}



function excel(){
	pt=document.getElementById('pt').value;
	per=document.getElementById('per').value;
	tipe='excel';
	tujuan = 'sdm_slave_2amprahgajiho.php';
	ev='event';
	judul='Report Ms.Excel';	
	param='proses=preview'+'&pt='+pt + '&per=' + per+ '&tipe=' + tipe; 
    printFile(param,tujuan,judul,ev);	
}


function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

