/**
 * @author repindra.ginting
 */
function setSloc(x)
{
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;

	if (gudang != '') {
        if (x == 'simpan') {
			document.getElementById('sloc').disabled = true;
			document.getElementById('btnsloc').disabled = true;
			tujuan = 'log_slave_getTutupBukuFisik.php';
			param='gudang='+gudang;
			post_response_text(tujuan, param, respog);
        }
        else {
			document.getElementById('sloc').disabled = false;
			document.getElementById('sloc').options[0].selected=true;
			document.getElementById('btnsloc').disabled = false;
			document.getElementById('infoDisplay').innerHTML='';
        }	
	}
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						document.getElementById('infoDisplay').innerHTML=con.responseText;
						alertify.alert(con.responseText);
				}
				else {
						document.getElementById('infoDisplay').innerHTML=con.responseText;
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
function saveSaldoFisik(maxRow,btn)
{
       btn.disabled=true;
       maxf=maxRow;
       if(confirm('Tutup buku gudang, anda yakin..?'))
          loopClosingFisik(1,maxRow);
}

function lanjut()
{
    loopClosingFisik(sekarang,maxf);
}
function loopClosingFisik(currRow,maxRow)
{
	if(document.getElementById('pilihan'+currRow).checked==false){//jika tidak dipilih maka lanjut
		currRow+=1;
		if(currRow>maxRow)
		{
			alertify.alert('Berhasil tutup buku gudang');
                        //alertify.alert('Done'); 
			unlockScreen();
			logout();
		}else{    
			loopClosingFisik(currRow,maxRow);
		}
	} else {
		periode	=trim(document.getElementById('periode'+currRow).innerHTML);
		gudang	=trim(document.getElementById('kodeorg'+currRow).innerHTML);
		tanggalmulai=trim(document.getElementById('tanggalmulai'+currRow).innerHTML);
		tanggalsampai=trim(document.getElementById('tanggalsampai'+currRow).innerHTML);
		
        param='periode='+periode+'&gudang='+gudang+'&tanggalmulai='+tanggalmulai+'&tanggalsampai='+tanggalsampai;
        tujuan = 'log_slave_saveTutupBukuFisik.php';
        post_response_text(tujuan, param, respog);
        document.getElementById('row'+currRow).style.backgroundColor='orange';
        lockScreen('wait');
	}
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
						document.getElementById('row'+currRow).style.backgroundColor='red';
						unlockScreen();
					//    document.getElementById('lanjut').style.display='';
					}
					else {
							document.getElementById('row'+currRow).style.display='none';
							currRow+=1;
							sekarang=currRow;
							if(currRow>maxRow)
							{
								 //tutupProses('simpan');//tutup periode dan pindah periode						
							   // document.getElementById('infoDisplay').innerHTML='';
							   // alertify.alert('Berhasil tutup buku gudang');
							  // unlockScreen();
                                                          // //alertify.alert('Berhasil tutup buku gudang');
							   // logout();
							    saldoact(periode);
							}  
							else
							{
									loopClosingFisik(currRow,maxRow);
							}
					}
			}
			else {
					busy_off();
					error_catch(con.status);
		   //         document.getElementById('lanjut').style.display='';
					unlockScreen();
			}
		}
	}
}

function saldoact(periode){
	unit = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	param = 'proses=saldoact&periode=' + periode+'&unit='+unit;
    tujuan = 'log_slave_saveTutupBukuFisik_saldoact.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                }  else {
					alertify.alert('Berhasil tutup buku gudang');
					unlockScreen();                
					logout();
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
	
}