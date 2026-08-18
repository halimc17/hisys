
function preview(){
    pt=trim(document.getElementById('pt').value);
    per=trim(document.getElementById('per').value);
    kom=trim(document.getElementById('kom').value);
	param='method=preview'+'&pt='+pt+'&per='+per+'&kom='+kom;
	tujuan='sdm_slave_3ppho.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {	
					document.getElementById('printContainer').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}


function batal(){
    document.getElementById('pt').value='';	
    document.getElementById('per').value='';	
    document.getElementById('kom').value='';
    document.getElementById('printContainer').innerHTML='';	
}

maxf=0
sekarang=1;
function saveall(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}



function loopsave(currRow,maxRow){
	pt=trim(document.getElementById('pt').value);
    per=trim(document.getElementById('per').value);
    kom=trim(document.getElementById('kom').value);
	karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
	kodeunit=trim(document.getElementById('kodeunit'+currRow).innerHTML);
	
	rpawal=trim(document.getElementById('rpawal'+currRow).innerHTML);
		rpawal=remove_comma_var(rpawal);
	hrjumlah=trim(document.getElementById('hrjumlah'+currRow).innerHTML);
		hrjumlah=remove_comma_var(hrjumlah);
	
	rpjumlah=trim(document.getElementById('rpjumlah'+currRow).innerHTML);
		rpjumlah=remove_comma_var(rpjumlah);
	
	param='method=simpan'+'&pt='+pt+'&per='+per+'&kom='+kom;
	param+='&karyawanid='+karyawanid+'&kodeunit='+kodeunit+'&rpawal='+rpawal;
	param+='&hrjumlah='+hrjumlah+'&rpjumlah='+rpjumlah;
	
	
	tujuan = 'sdm_slave_3ppho.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
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
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow) {
                            alert('Done');
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









function previewv(){
    pt=trim(document.getElementById('ptv').value);
    per=trim(document.getElementById('perv').value);
    kom=trim(document.getElementById('komv').value);
	param='method=previewv'+'&pt='+pt+'&per='+per+'&kom='+kom;
	tujuan='sdm_slave_3ppho.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {	
					document.getElementById('printContainerv').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}



function batalv(){
    document.getElementById('ptv').value='';	
    document.getElementById('perv').value='';	
    document.getElementById('komv').value='';
    document.getElementById('printContainerv').innerHTML='';	
}



maxfv=0
sekarangv=1;
function saveallv(maxRow){     
	maxfv=maxRow;
	loopsavev(1,maxRow);
}



function loopsavev(currRow,maxRow){
	pt=trim(document.getElementById('ptv').value);
    per=trim(document.getElementById('perv').value);
    kom=trim(document.getElementById('komv').value);
	karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
	kodeunit=trim(document.getElementById('kodeunit'+currRow).innerHTML);
	
	rpawal=trim(document.getElementById('rpawal'+currRow).innerHTML);
		rpawal=remove_comma_var(rpawal);
	hrjumlah=trim(document.getElementById('hrjumlah'+currRow).innerHTML);
		hrjumlah=remove_comma_var(hrjumlah);
	
	rpjumlah=trim(document.getElementById('rpjumlah'+currRow).innerHTML);
		rpjumlah=remove_comma_var(rpjumlah);
	
	param='method=simpan'+'&pt='+pt+'&per='+per+'&kom='+kom;
	param+='&karyawanid='+karyawanid+'&kodeunit='+kodeunit+'&rpawal='+rpawal;
	param+='&hrjumlah='+hrjumlah+'&rpjumlah='+rpjumlah;
	
	tujuan = 'sdm_slave_3ppho.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
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
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarangv=currRow;
                    if(currRow>maxRow) {
                            alert('Done');
							batalv();
                    } else {
                            loopsavev(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}