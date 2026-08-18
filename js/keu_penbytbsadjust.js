function detail(){
    
    unit=document.getElementById('unit').value;
    supplier=document.getElementById('supplier').value;
    tgl1=document.getElementById('tgl1').value;
    tgl2=document.getElementById('tgl2').value;
	
	param = 'method=detail' + '&unit=' + unit + '&tgl1=' + tgl1+ '&tgl2=' + tgl2+ '&supplier=' + supplier;
    // param+='&x='+x;

    tujuan = 'keu_penbytbsadjust_slave.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('detailform').style.display='block';
                    document.getElementById('detaildata').innerHTML=con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


maxf=0
sekarang=1;
function saveall(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}


function loopsave(currRow,maxRow) {
    notransaksi=trim(document.getElementById('notransaksi'+currRow).innerHTML);
    jurnal=trim(document.getElementById('jurnal'+currRow).innerHTML);
    kodesupplier=trim(document.getElementById('kodesupplier'+currRow).innerHTML);
    tanggal=trim(document.getElementById('tanggal'+currRow).innerHTML);
	
    hargabudget=trim(document.getElementById('hargabudget'+currRow).innerHTML);
	hargabudget=remove_comma_var(hargabudget);
	
	hargarealisasi=trim(document.getElementById('hargarealisasi'+currRow).innerHTML);
	hargarealisasi=remove_comma_var(hargarealisasi);
	
	hargasatuanrealisasi=trim(document.getElementById('hargasatuanrealisasi'+currRow).innerHTML);
	hargasatuanrealisasi=remove_comma_var(hargasatuanrealisasi);
	
	selisih=trim(document.getElementById('selisih'+currRow).innerHTML);
	selisih=remove_comma_var(selisih);
	
	// document.getElementById('preview').disabled=true;
	// document.getElementById('batal').disabled=true;
	
	unit=trim(document.getElementById('unit').value);
		
	param='notransaksi='+notransaksi+'&kodesupplier='+kodesupplier+'&tanggal='+tanggal+'&hargabudget='+hargabudget;
	param+="&method=savedata"+'&hargarealisasi='+hargarealisasi+'&unit='+unit+'&hargasatuanrealisasi='+hargasatuanrealisasi+'&selisih='+selisih;

	tujuan = 'keu_penbytbsadjust_slave.php';
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
						document.getElementById('preview').disabled=false;
						document.getElementById('batal').disabled=false;
						document.getElementById('batal').disabled=false;
						document.getElementById('detailform').style.display='none';
						loaddata(0);
                           // document.location.reload();
                            //document.getElementById('infoDisplay').innerHTML='';
                    } else {
						loopsave(currRow,maxRow);
                    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
               // document.getElementById('lanjut').style.display='';
                //unlockScreen();
            }
        }
    }		
	
}




function loaddata(num) {	
	unit = document.getElementById('unitcari').value;
	supplier = document.getElementById('suppliercari').value;
	tgl1 = document.getElementById('tgl1cari').value;
	tgl2 = document.getElementById('tgl2cari').value;
	jurnal = document.getElementById('jurnalcari').value;
	jurnalbalik = document.getElementById('jurnalbalikcari').value;
	
	if(tgl1=='' && tgl2!=''){
		alert('Tanggal tidak boleh salah satunya kosong');
	}
	
	if(tgl2=='' && tgl1!=''){
		alert('Tanggal tidak boleh salah satunya kosong');
	}
	
	param = 'method=loaddata' + '&unit=' + unit + '&tgl1=' + tgl1+ '&tgl2=' + tgl2+ '&supplier=' + supplier;
    param+='&jurnal='+jurnal+'&jurnalbalik='+jurnalbalik;
    param+='&page='+num;
    tujuan='keu_penbytbsadjust_slave.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			} else  {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function batal(){
	document.getElementById('unitcari').value='';
	document.getElementById('suppliercari').value='';
	document.getElementById('tgl1cari').value='';
	document.getElementById('tgl2cari').value='';
	document.getElementById('jurnalcari').value='';
	document.getElementById('jurnalbalikcari').value='';
	document.getElementById('unit').value='';
	document.getElementById('supplier').value='';
	document.getElementById('tgl1').value='';
	document.getElementById('tgl2').value='';
	document.getElementById('detailform').style.display='none';
	loaddata(0);
	
}


















