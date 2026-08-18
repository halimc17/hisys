
function preview(){
    pt=trim(document.getElementById('pt').value);
    per=trim(document.getElementById('per').value);
	param='method=preview'+'&pt='+pt+'&per='+per;
	tujuan='sdm_slave_3rekapabsenho.php';
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
    document.getElementById('printContainer').innerHTML='';	
}

maxf=0
sekarang=1;
function saveall(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}



function loopsave(currRow,maxRow){
	colom=1;
	loopsavecol(currRow,maxRow,colom);
}



function loopsavecol(currRow,maxRow,colom){
	
	jumhari=trim(document.getElementById('jumhari').value);
	tglurut=colom;
		pt=trim(document.getElementById('pt').value);
		per=trim(document.getElementById('per').value);
		karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
		kodeunit=trim(document.getElementById('kodeunit'+currRow).innerHTML);

		jambayar = trim(document.getElementById('jambayar'+currRow+'#'+tglurut).innerHTML);
		jampotong = trim(document.getElementById('jampotong'+currRow+'#'+tglurut).innerHTML);
		persenpotong = trim(document.getElementById('persenpotong'+currRow+'#'+tglurut).innerHTML);
		
		param='method=simpan'+'&pt='+pt+'&per='+per;
		param+='&karyawanid='+karyawanid+'&kodeunit='+kodeunit;
		param+='&jambayar='+jambayar+'&jampotong='+jampotong+'&persenpotong='+persenpotong;
		param+='&tglurut='+tglurut;
		
		tujuan = 'sdm_slave_3rekapabsenho.php';
		// alert(tujuan);return;
		post_response_text(tujuan, param, respog);
		document.getElementById('row'+currRow).style.backgroundColor='#00CED1';
		// document.getElementById('jambayar'+currRow+'#'+tglurut).style.display='none';
		// document.getElementById('jampotong'+currRow+'#'+tglurut).style.display='none';
		// document.getElementById('persenpotong'+currRow+'#'+tglurut).style.display='none';
		document.getElementById('waktuabsen'+currRow+'#'+tglurut).style.backgroundColor='cyan';
		document.getElementById('waktubayar'+currRow+'#'+tglurut).style.backgroundColor='cyan';
		document.getElementById('jambayar'+currRow+'#'+tglurut).style.backgroundColor='cyan';
		document.getElementById('jampotong'+currRow+'#'+tglurut).style.backgroundColor='cyan';
		document.getElementById('persenpotong'+currRow+'#'+tglurut).style.backgroundColor='cyan';
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
						tglurut+=1;
						if(tglurut>jumhari){
							document.getElementById('row'+currRow).style.display='none';
							currRow+=1;
							sekarang=currRow;
							if(currRow>maxRow) {
								alert('Done');
							} else {
								loopsave(currRow,maxRow);
							}
						}else{
							loopsavecol(currRow,maxRow,tglurut);
						}
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
	

}



/*
################################################################################################################################
################################################################################################################################
################################################################################################################################
*/




function previewv(){
    pt=trim(document.getElementById('ptv').value);
    per=trim(document.getElementById('perv').value);
	param='method=previewv'+'&pt='+pt+'&per='+per;
	tujuan='sdm_slave_3rekapabsenho.php';
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
	karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
	kodeunit=trim(document.getElementById('kodeunit'+currRow).innerHTML);
	
	jumlahhari=trim(document.getElementById('jumlahhari'+currRow).innerHTML);
	potongan=trim(document.getElementById('potongan'+currRow).innerHTML);
	cutiawal=trim(document.getElementById('cutiawal'+currRow).innerHTML);
	potongancuti=trim(document.getElementById('potongancuti'+currRow).innerHTML);
	pengali=trim(document.getElementById('pengali'+currRow).innerHTML);
	sisacuti=trim(document.getElementById('sisacuti'+currRow).innerHTML);
	
	param='method=simpanv'+'&pt='+pt+'&per='+per;
	param+='&karyawanid='+karyawanid+'&kodeunit='+kodeunit;
	param+='&jumlahhari='+jumlahhari+'&potongan='+potongan;
	param+='&cutiawal='+cutiawal+'&potongancuti='+potongancuti;
	param+='&pengali='+pengali+'&sisacuti='+sisacuti;
	
	tujuan = 'sdm_slave_3rekapabsenho.php';
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














