function preview(){
	unit=document.getElementById('unit').value;
	per=document.getElementById('per').value;
    param='proses=preview'+'&unit='+unit + '&per=' + per+ '&tipe=' + ''; 
    tujuan = 'sdm_slave_2amprahgaji.php';
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
				  //  if(tipe=='previewawal'){
						// prosespph(arr[1]);
				  //  }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillblank(){
   document.getElementById('printContainer').innerHTML='';
}

maxf=0
sekarang=1;
function prosespph(maxRow){     
	maxf=maxRow;
    //alert(maxRow);
	looppph(1,maxRow);
}

function looppph(currRow,maxRow){
    karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
    pph=document.getElementById('pph21_'+currRow).innerHTML.replace(',','');
	unit=document.getElementById('unit').value;
	per=document.getElementById('per').value;
	param='per='+per+'&karyawanid='+karyawanid+'&unit='+unit+'&pph='+pph;
	param+="&proses=prosespph";
	tujuan = 'sdm_slave_2amprahgaji.php';
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
					
					//arr = con.responseText.split("####");
					//alert(currRow);
					if(currRow<=maxRow){
                        document.getElementById('row'+currRow).style.backgroundColor='green';
						currRow+=1;
                        if(currRow>maxRow)
                        {
                        alert('Done');
                        }
                        else
                        {
                        looppph(currRow,maxRow);    
                        }

					}
                    else {
						alert('Done');
                        //document.getElementById('printContainer').innerHTML='';
						//preview();
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
	unit=document.getElementById('unit').value;
	per=document.getElementById('per').value;
	tipe='excel';
	tujuan = 'sdm_slave_2amprahgaji.php';
	ev='event';
	judul='Report Ms.Excel';	
	param='proses=preview'+'&unit='+unit + '&per=' + per+ '&tipe=' + tipe; 
    printFile(param,tujuan,judul,ev);	
}


function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

