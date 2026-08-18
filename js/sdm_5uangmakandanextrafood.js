function simpan(){
	unit=document.getElementById('unit').value;
	tahun=document.getElementById('tahun').value;
	namakary=document.getElementById('namakary').value;
	jenis=document.getElementById('jenis').value;
	
	rpnya=document.getElementById('rpnya').value;
	method=document.getElementById('method').value;
	param='unit='+unit+'&tahun='+tahun+'&method='+method+'&rpnya='+rpnya;
	param+='&namakary='+namakary;
	param+='&jenis='+jenis;
	tujuan='sdm_slave_5uangmakandanextrafood.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
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
	document.getElementById('namakary').value='';
	document.getElementById('jenis').value='';
	document.getElementById('method').value='insert';		
}

function loadData(num){
	unit    =document.getElementById('unitcr').value;
	tahun   =document.getElementById('tahuncr').value;
	namakary=document.getElementById('namakarysc').value;
	jenis   =document.getElementById('jeniscr').value;
	
    param='method=loadData';
	param+='&unit='+unit+'&tahun='+tahun;
	param+='&namakary='+namakary;
	param+='&jenis='+jenis;
	param+='&page='+num;
	tujuan = 'sdm_slave_5uangmakandanextrafood.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					isdt = con.responseText.split("####");
                    document.getElementById('container').innerHTML = isdt[0];
                    //document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
				}
  			}else{
  				busy_off();
          error_catch(con.status);
  			}
  		}	
  	}
}

function fillfield(thn,unit,rpd,tipe,jenis){
	document.getElementById('tahun').value=thn;
	document.getElementById('unit').value=unit;
	document.getElementById('rpnya').value=rpd;	 
	document.getElementById('namakary').value=tipe;	 
	document.getElementById('jenis').value=jenis;	 
	// document.getElementById('method').value='update';
}
