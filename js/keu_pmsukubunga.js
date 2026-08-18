function bungaloadData(num){
	notransaksi=document.getElementById('notransaksipm').value;
    bank=document.getElementById('namabank');
    bank=bank.options[bank.selectedIndex].value;
    param='kodebank='+bank+'&notransaksi='+notransaksi;
    param+='&method=loadData';
	param+='&page='+num;
	tujuan='keu_slave_pmsukubunga.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					isdt = con.responseText.split("####");
                    document.getElementById('pmssukubungacontainer').innerHTML = isdt[0];
                    document.getElementById('pmssukubungafootData').innerHTML = isdt[1];
				}
  			}else{
  				busy_off();
				error_catch(con.status);
  			}
  		}	
  	}
}
function bungaloadForm(){
    param='';
	tujuan='keu_slave_pmsukubunga.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					responseText = con.responseText;
                    document.getElementById('pmssukubunga').innerHTML = responseText;
					bungaloadData(0);
				}
  			}else{
  				busy_off();
				error_catch(con.status);
  			}
  		}	
  	}
}
function bungafillfield(periode,kodebank,nilai){
	document.getElementById('pmssukubungaperiode').value=periode;
	document.getElementById('sukubungaperiodelama').value=periode;
	document.getElementById('pmssukubungakodebank').value=kodebank;
	document.getElementById('pmssukubunganilai').value=nilai;
	// document.getElementById('method').value='update';
}
function bungacancel(){
	document.getElementById('pmssukubungaperiode').disabled=false;
	document.getElementById('pmssukubungaperiode').value='';
	document.getElementById('sukubungaperiodelama').value='';
 	// document.getElementById('pmssukubungakodebank').disabled=false;
	// document.getElementById('pmssukubungakodebank').value='';
	document.getElementById('pmssukubunganilai').value='0';
	document.getElementById('pmssukubungamethod').value='insert';	
}
function bungasimpan(){
	notransaksi=document.getElementById('notransaksipm').value;
	periode=document.getElementById('pmssukubungaperiode').value;
	periodelama=document.getElementById('sukubungaperiodelama').value;
	kodebank=document.getElementById('pmssukubungakodebank').options[document.getElementById('pmssukubungakodebank').selectedIndex].value;
	nilai=document.getElementById('pmssukubunganilai').value;
	method=document.getElementById('pmssukubungamethod').value;
	sumberform=document.getElementById('sumberform').value;
	param='kodebank='+kodebank+'&notransaksi='+notransaksi+'&periode='+periode+'&periodelama='+periodelama+'&nilai='+nilai+'&method='+method+'&sumberform='+sumberform;
	tujuan='keu_slave_pmsukubunga.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    //alert(con.responseText);
					bungacancel();
					bungaloadData(0);
					if(sumberform=="daripinjaman"){
						document.getElementById('sukubungaangsuran').value=con.responseText;	
					}
					
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}








