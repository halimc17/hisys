function simpan(){
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	tanggal=document.getElementById('tanggal').value;
	tipe=document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value;
	nourut=document.getElementById('nourut').value;
	
	jam=document.getElementById('jam').value;
	menit=document.getElementById('menit').value;
	kadar=document.getElementById('kadar').value;
	method=document.getElementById('method').value;
	param='unit='+unit+'&tipe='+tipe+'&tanggal='+tanggal+'&nourut='+nourut;
	param+='&jam='+jam+'&menit='+menit+'&kadar='+kadar+'&method='+method;
	tujuan='pabrik_slave_analisa_monitorkotoran.php';
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
 //    document.getElementById('unit').disabled=false;
	// document.getElementById('unit').value='';
	// document.getElementById('tanggal').disabled=false;
	// document.getElementById('tanggal').value='';
 //    document.getElementById('tipe').disabled=false;
	// document.getElementById('tipe').value='';
	// document.getElementById('nourut').disabled=false;
	// document.getElementById('nourut').value='';
	document.getElementById('jam').value='00';
	document.getElementById('menit').value='00';
	document.getElementById('kadar').value='0';
	document.getElementById('method').value='insert';		
}

function loadData(num){
    param='method=loadData';
	param+='&page='+num;
	tujuan='pabrik_slave_analisa_monitorkotoran.php';
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

function fillfield(unit,tanggal,tipe,nourut,jam,menit,kadar){
	document.getElementById('unit').value=unit;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('tipe').value=tipe;
	document.getElementById('nourut').value=nourut;
	document.getElementById('jam').value=jam;
	document.getElementById('menit').value=menit;
	document.getElementById('kadar').value=kadar;
	// document.getElementById('method').value='update';
}

function deletedata(unit,tanggal,tipe,nourut){
	param='unit='+unit+'&tipe='+tipe+'&tanggal='+tanggal+'&nourut='+nourut+'&method=deletedata';
	tujuan='pabrik_slave_analisa_monitorkotoran.php';
	
	if(confirm('Anda yakin hapus item ini???')){
        post_response_text(tujuan, param, respog);	
    }
	
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









