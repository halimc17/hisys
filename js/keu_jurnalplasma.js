/*
  sb_tot=document.getElementById('total_harga_po');
        sb_tot.value=remove_comma_var(sb_tot.value);
*/

maxf=0
sekarang=1;
function saveAll(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}


function batal(){
    document.getElementById('per').value='';	
    document.getElementById('unit').value='';
    document.getElementById('noakun').value='';
    document.getElementById('printContainer').innerHTML='';	
}


function del(maxRow){   
    nodok=trim(document.getElementById('nodok').value);
	unit=trim(document.getElementById('unit').value);
    per=document.getElementById('per').value;    	
    jumlah=document.getElementById('jumlah').value;    	
    nojurnal=document.getElementById('nojurnal').value;     
    nojurnalp=document.getElementById('nojurnalp').value;    	
	param='proses=delete'+'&unit='+unit+'&per='+per+'&nodok='+nodok+'&jumlah='+jumlah+'&nojurnal='+nojurnal+'&nojurnalp='+nojurnalp;
	tujuan='keu_slave_jurnalplasma.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					currRow=1;
					loopsave(currRow,maxRow);	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}



function loopsave(currRow,maxRow){

	
    nourutdt=trim(document.getElementById('nourutdt'+currRow).innerHTML);
    nojurnaldt=trim(document.getElementById('nojurnaldt'+currRow).innerHTML);
    noakundt=trim(document.getElementById('noakundt'+currRow).innerHTML);
    keterangandt=trim(document.getElementById('keterangandt'+currRow).innerHTML);
	
    jumlahdt=trim(document.getElementById('jumlahdt'+currRow).innerHTML);
    kodekegiatandt=trim(document.getElementById('kodekegiatandt'+currRow).innerHTML);
    kodebarangdt=trim(document.getElementById('kodebarangdt'+currRow).innerHTML);
    nikdt=trim(document.getElementById('nikdt'+currRow).innerHTML);
	
    kodesupplierdt=trim(document.getElementById('kodesupplierdt'+currRow).innerHTML);
    kodevhcdt=trim(document.getElementById('kodevhcdt'+currRow).innerHTML);
    kodeblokdt=trim(document.getElementById('kodeblokdt'+currRow).innerHTML);
    
	unit=trim(document.getElementById('unit').value);
	noakun=trim(document.getElementById('noakun').value);
    per=document.getElementById('per').value;    	
    nojurnal=document.getElementById('nojurnal').value;   
    nojurnalp=document.getElementById('nojurnalp').value;    	
    nodok=document.getElementById('nodok').value;    	
	
	param='proses=savedt'+'&unit='+unit+'&per='+per+'&noakun='+noakun+'&nojurnal='+nojurnal+'&nodok='+nodok+'&nojurnalp='+nojurnalp;
	
	param+='&nourutdt='+nourutdt+'&nojurnaldt='+nojurnaldt+'&noakundt='+noakundt+'&keterangandt='+keterangandt;
	param+='&jumlahdt='+jumlahdt+'&kodekegiatandt='+kodekegiatandt+'&kodebarangdt='+kodebarangdt+'&nikdt='+nikdt;
	param+='&kodesupplierdt='+kodesupplierdt+'&kodevhcdt='+kodevhcdt+'&kodeblokdt='+kodeblokdt;
       
            //alert(param);
            tujuan = 'keu_slave_jurnalplasma.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('row'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    
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
                    if(currRow>maxRow){
                            alert('Done');
                    }  
                    else
                    {
                            loopsave(currRow,maxRow);
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


function getakun() {
    var unit = document.getElementById('unit').value;
    var param = "unit="+unit+"&proses=getakun";
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    $data=con.responseText.split('####');
                    document.getElementById('noakun').innerHTML = $data[0];
                    document.getElementById('per').innerHTML = $data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_jurnalplasma.php', param, respon);
}