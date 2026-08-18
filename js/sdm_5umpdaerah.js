function simpan(){
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	tahun=document.getElementById('tahun').value;
	rpnya=document.getElementById('rpnya').value;
	method=document.getElementById('method').value;
	param='unit='+unit+'&tahun='+tahun+'&method='+method+'&rpnya='+rpnya;
	tujuan='sdm_slave_5umpdaerah.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    //alert(con.responseText);
					cancel();
					loadData();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function cancel(){
	document.getElementById('unit').value='';    
	document.getElementById('kompId').value='';
	document.getElementById('rpnya').value='';
	document.getElementById('method').value='insert';		
}

function loadData(num){
    param='method=loadData';
	param+='&page='+num;
	tujuan = 'sdm_slave_5umpdaerah.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					isdt = con.responseText.split("####");
                    document.getElementById('container').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
				}
  			}else{
  				busy_off();
          error_catch(con.status);
  			}
  		}	
  	}
}

function fillfield(thn,unit,rpd){
	document.getElementById('tahun').value=thn;
	document.getElementById('unit').value=unit;
	document.getElementById('rpnya').value=rpd;	 
	// document.getElementById('method').value='update';
}









