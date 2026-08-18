function simpan(){
	kelbrg=document.getElementById('kelbrg').options[document.getElementById('kelbrg').selectedIndex].value;
	kdcapex=document.getElementById('kdcapex').options[document.getElementById('kdcapex').selectedIndex].value;
	method=document.getElementById('method').value;
	param='kelbrg='+kelbrg+'&kdcapex='+kdcapex;
	param+='&method='+method;
	// alert(param);
	// return;
	tujuan='bgt_slave_5capex.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    //alert(con.responseText);
					//cancel();
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
	kelbrg=document.getElementById('kelbrg');
	kelbrg.disabled=false;
	kelbrg=kelbrg.options[0].selected=true;
	kdcapex=document.getElementById('kdcapex');
	kdcapex.disabled=false;
	kdcapex=kdcapex.options[0].selected=true;
	document.getElementById('method').value='insert';		
}

function loadData(num){

    param='method=loadData';
	param+='&page='+num;

	tujuan='bgt_slave_5capex.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
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

// function fillfield(kelbrgkdcapex){
// 	x=document.getElementById('kelbrg');
// 	for(a=0;a<x.length;a++){
// 		if(x.options[a].value==kelbrg){
// 			x.options[a].selected=true;
// 		}
// 	}
// 	x.disabled=true;
// 	document.getElementById('kodekelbrg').value=kodekelbrg;
// 	document.getElementById('idnilai').value=idnilai;
// 	document.getElementById('kdcapex').value=kdcapex;
// 	document.getElementById('kodekelbrg').disabled=true;
// 	document.getElementById('method').value='update';
// }