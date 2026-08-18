function simpan(){
	kodekelompok=document.getElementById('kodekelompok').value;
	keterangan=document.getElementById('keterangan').value;
	status=0;
	if(document.getElementById('status').checked==true){
		status=1;
	}
	method=document.getElementById('method').value;
	param='status='+status+'&kodekelompok='+kodekelompok+'&method='+method+'&keterangan='+keterangan;
	tujuan='sdm_slave_5kodebpjs.php';
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
	document.getElementById('kodekelompok').value='';    
	document.getElementById('keterangan').value='';
	document.getElementById('status').checked=false;
	document.getElementById('method').value='insert';	
	document.getElementById('kodekelompok').disabled=false;	
}

function loadData(num){
    param='method=loadData';
	param+='&page='+num;
	tujuan = 'sdm_slave_5kodebpjs.php';
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

function fillfield(kodekelompok,keterangan,status){
	document.getElementById('kodekelompok').value=kodekelompok;
	document.getElementById('kodekelompok').disabled=true;
	document.getElementById('keterangan').value=keterangan;
	if(status==1){
		document.getElementById('status').checked=true;
	}
	document.getElementById('method').value='update';
}









