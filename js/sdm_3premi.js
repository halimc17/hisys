function deletepremi (maxRow){
    per=document.getElementById('per').value;
    unit=document.getElementById('unit').value;
    kom=document.getElementById('kom').value;
    param='proses=delete'+'&per='+per+'&unit='+unit+'&kom='+kom;
    tujuan='sdm_slave_3premi.php';
    post_response_text(tujuan, param, respog);	
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					saveAll(maxRow);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}	
}


maxf=0
sekarang=1;
function saveAll(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}


function batal(){
    document.getElementById('per').value='';	
    document.getElementById('unit').value='';
    document.getElementById('printContainer').innerHTML='';	
}


function loopsave(currRow,maxRow){
    per=trim(document.getElementById('per'+currRow).innerHTML);
    karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
    premi=trim(document.getElementById('premi'+currRow).innerHTML);
    unit=document.getElementById('unit').value;
    kom=document.getElementById('kom').value;
    if(per=='' || karyawanid=='' || premi=='' || kom=='') {
            alert("Data tidak lengkap");return;
    } else {  
        param='per='+per+'&karyawanid='+karyawanid+'&premi='+premi+'&kom='+kom;
        param+="&proses=savedata"+'&unit='+unit;
		tujuan = 'sdm_slave_3premi.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('row'+currRow).style.backgroundColor='cyan';
           
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('row'+currRow).style.backgroundColor='red';
                   unlockScreen();
                }  else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow) {
						alert('Done');
						batal();
                    } else {
                            loopsave(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
	
}
