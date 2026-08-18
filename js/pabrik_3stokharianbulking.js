maxfpt=0
sekarangpt=1;
function savept(maxRow){     
	maxfpt=maxRow;
	loopsavept(1,maxRow);
}



function loopsavept(currRow,maxRow) {
    
	unit=trim(document.getElementById('unitpt').value);
    tanggal=trim(document.getElementById('tanggalpt').value);
    kodebarang=trim(document.getElementById('kodebarangpt').value);
    jumlah=trim(document.getElementById('jumlahstok'+currRow).innerHTML);
    kodept=trim(document.getElementById('kodeptstok'+currRow).innerHTML);
	
    jumlah=remove_comma_var(jumlah);

    if(unit=='' || tanggal=='' || kodebarang=='') {
            alert("Data tidak lengkap");return;
    } else {  
        param='unit='+unit+'&tanggal='+tanggal+'&kodebarang='+kodebarang+'&jumlah='+jumlah;
        param+="&method=savept"+'&kodept='+kodept;
		tujuan = 'pabrik_3stokharianbulking_slave.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('jumlahstok'+currRow).style.backgroundColor='cyan';
		document.getElementById('kodeptstok'+currRow).style.backgroundColor='cyan';
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('jumlahstok'+currRow).style.backgroundColor='red';
					document.getElementById('kodeptstok'+currRow).style.backgroundColor='red';
					unlockScreen();
                } else {
                    // document.getElementById('jumlahstok'+currRow).style.backgroundColor='cyan';
                    // document.getElementById('kodeptstok'+currRow).style.backgroundColor='cyan';
                    currRow+=1;
                    sekarangpt=currRow;
                    if(currRow>maxRow){
						alert('Done');
                    } else {
						loopsavept(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}


function previewpt(){
    unit=trim(document.getElementById('unitpt').value);
    tanggal=trim(document.getElementById('tanggalpt').value);
    kodebarang=trim(document.getElementById('kodebarangpt').value);
	tipe='html';
	param='method=previewpt'+'&unit='+unit+'&tanggal='+tanggal+'&tipe='+tipe+'&kodebarang='+kodebarang;
	tujuan='pabrik_3stokharianbulking_slave.php';
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


function preview(){
    unit=trim(document.getElementById('unit').value);
    tanggal=trim(document.getElementById('tanggal').value);
    // kodebarang=trim(document.getElementById('kodebarang').value);
    // kodetangki=trim(document.getElementById('kodetangki').value);
	tipe='html';
	// param='method=preview'+'&unit='+unit+'&tanggal='+tanggal+'&tipe='+tipe+'&kodebarang='+kodebarang+'&kodetangki='+kodetangki;
	// param='method=preview'+'&unit='+unit+'&tanggal='+tanggal+'&tipe='+tipe+'&kodetangki='+kodetangki;
	param='method=preview'+'&unit='+unit+'&tanggal='+tanggal+'&tipe='+tipe;
	tujuan='pabrik_3stokharianbulking_slave.php';
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


/*
function save(){
    unit=trim(document.getElementById('unit').value);
    tanggal=trim(document.getElementById('tanggal').value);
    kodetangki=trim(document.getElementById('kodetangki').value);

    tinggi=trim(document.getElementById('tinggistok').innerHTML);
    suhu=trim(document.getElementById('suhustok').innerHTML);
    jumlah=trim(document.getElementById('jumlahstok').innerHTML);
	
	tinggi=remove_comma_var(tinggi);
	suhu=remove_comma_var(suhu);
	jumlah=remove_comma_var(jumlah);

	param='method=save'+'&unit='+unit+'&tanggal='+tanggal+'&kodetangki='+kodetangki;
	param+='&tinggi='+tinggi+'&suhu='+suhu+'&jumlah='+jumlah;
	tujuan='pabrik_3stokharianbulking_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {	
					alert('Data tersimpan');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}
*/


maxf=0
sekarang=1;
function save(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}

function loopsave(currRow,maxRow) {
    
	unit=trim(document.getElementById('unit').value);
    tanggal=trim(document.getElementById('tanggal').value);
	
	kodetangki=trim(document.getElementById('kodetangki'+currRow).innerHTML);
	tinggi=trim(document.getElementById('tinggistok'+currRow).innerHTML);
	suhu=trim(document.getElementById('suhustok'+currRow).innerHTML);
	jumlah=trim(document.getElementById('jumlahstok'+currRow).innerHTML);
	
	tinggi=remove_comma_var(tinggi);
	suhu=remove_comma_var(suhu);
    jumlah=remove_comma_var(jumlah);

    if(unit=='' || tanggal=='') {
            alert("Data tidak lengkap");return;
    } else {  
       	param='method=save'+'&unit='+unit+'&tanggal='+tanggal+'&kodetangki='+kodetangki;
		param+='&tinggi='+tinggi+'&suhu='+suhu+'&jumlah='+jumlah;
		tujuan = 'pabrik_3stokharianbulking_slave.php';
		post_response_text(tujuan, param, respog);
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('jumlahstok'+currRow).style.backgroundColor='red';
					unlockScreen();
                } else {
					document.getElementById('jumlahstok'+currRow).style.backgroundColor='cyan';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
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


