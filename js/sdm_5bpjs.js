function simpan(){
	lokasibpjs=document.getElementById('lokasibpjs').options[document.getElementById('lokasibpjs').selectedIndex].value;
	jenisbpjs=document.getElementById('jenisbpjs').options[document.getElementById('jenisbpjs').selectedIndex].value;
	jenisbpjsplus=document.getElementById('jenisbpjsplus').options[document.getElementById('jenisbpjsplus').selectedIndex].value;
	bebankaryawan=document.getElementById('bebankaryawan').value;
	bebanperusahaan=document.getElementById('bebanperusahaan').value;
	bebankaryawantpdiskon=document.getElementById('bebankaryawantpdiskon').value;
	bebanperusahaantpdiskon=document.getElementById('bebanperusahaantpdiskon').value;
	maxgaji=document.getElementById('maxgaji').value;
	method=document.getElementById('method').value;
	param='jenisbpjsplus='+jenisbpjsplus+'&jenisbpjs='+jenisbpjs+'&lokasibpjs='+lokasibpjs+'&maxgaji='+maxgaji+'&method='+method;
	param+='&bebankaryawan='+bebankaryawan+'&bebanperusahaan='+bebanperusahaan;
	param+='&bebankaryawantpdiskon='+bebankaryawantpdiskon+'&bebanperusahaantpdiskon='+bebanperusahaantpdiskon;
	tujuan='sdm_slave_5bpjs.php';
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
	document.getElementById('lokasibpjs').disabled=false;
	document.getElementById('lokasibpjs').value='';
    document.getElementById('jenisbpjs').disabled=false;
	document.getElementById('jenisbpjs').value='';    
	document.getElementById('jenisbpjsplus').disabled=false;
	document.getElementById('jenisbpjsplus').value='';
	
	document.getElementById('bebanperusahaan').value='0';
	document.getElementById('bebankaryawan').value='0';
	document.getElementById('bebanperusahaantpdiskon').value='0';
	document.getElementById('bebankaryawantpdiskon').value='0';
	document.getElementById('maxgaji').value='0';
	document.getElementById('method').value='insert';		
}

function loadData(num){
    param='method=loadData';
	param+='&page='+num;
	tujuan = 'sdm_slave_5bpjs.php';
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

function fillfield(lokasibpjs,jenisbpjs,jenisbpjsplus,bebankaryawan,bebanperusahaan,maxgaji,bebankaryawantpdiskon,bebanperusahaantpdiskon){
	document.getElementById('maxgaji').value=maxgaji;
	document.getElementById('jenisbpjs').value=jenisbpjs;
	document.getElementById('jenisbpjs').disabled=true;	
	document.getElementById('jenisbpjsplus').value=jenisbpjsplus;
	document.getElementById('jenisbpjsplus').disabled=true;
	document.getElementById('bebankaryawan').value=bebankaryawan;
	document.getElementById('bebanperusahaan').value=bebanperusahaan;
	document.getElementById('bebankaryawantpdiskon').value=bebankaryawantpdiskon;
	document.getElementById('bebanperusahaantpdiskon').value=bebanperusahaantpdiskon;
	document.getElementById('lokasibpjs').value=lokasibpjs;
	document.getElementById('lokasibpjs').disabled=true;
	// document.getElementById('method').value='update';
}









