function cancelnpwp(){
	document.getElementById('org').selectedIndex=0;
	document.getElementById('org').disabled=false;
	document.getElementById('npwp').value='';
	document.getElementById('npwp').disabled=false;
	document.getElementById('alamatnpwp').value='';
	document.getElementById('alamatdomisili').value='';	
	document.getElementById('nopkp').value='';	
	document.getElementById('inisial').value='';	
	document.getElementById('statuss').checked=false;
	document.getElementById('namakpp').checked='';
	document.getElementById('method').value='insert';	
}

function delnpwp(org,npwp){
	param='kodeorg='+org+'&switch=delete'+'&npwp='+npwp;
	
	tujuan='slave_save_org_npwp.php';
	if(confirm('Delete/hapus ..?')){
        post_response_text(tujuan, param, respog);			
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaddata();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}	
}

function editnpwp(kodeorg,npwp,inisial,alamatnpwp,alamatdomisili,no_pkp,stt,defaults){
	showontop();
	document.getElementById('method').value='update';
	document.getElementById('org').value=kodeorg;
	document.getElementById('org').disabled=true;
	document.getElementById('npwp').value=npwp;
	document.getElementById('npwp').disabled=true;
	document.getElementById('inisial').value=inisial;
	document.getElementById('alamatnpwp').value=alamatnpwp;
	document.getElementById('alamatdomisili').value=alamatdomisili;
	document.getElementById('nopkp').value=no_pkp;
	statuss=document.getElementById('statuss');
	defaultyo=document.getElementById('defaultyo');
	if(stt==1){
		statuss.checked=true;
    }else{
		statuss.checked=false;
    }
	if(defaults==1){
		defaultyo.checked=true;
    }else{
		defaultyo.checked=false;
    }
}

function savenpwp(){
	method=document.getElementById('method').value;
	org=document.getElementById('org').options[document.getElementById('org').selectedIndex].value;
	npwp=document.getElementById('npwp').value;
	alamatnpwp=document.getElementById('alamatnpwp').value;
	alamatdomisili =document.getElementById('alamatdomisili').value;
	nopkp=document.getElementById('nopkp').value;
	statuss=document.getElementById('statuss');
	defaultyo=document.getElementById('defaultyo');
	inisial=document.getElementById('inisial').value;
	namakpp=document.getElementById('namakpp').value;

    if(statuss.checked==true){
		statuss=1;
    }else{
		statuss=0;
    }

    if(defaultyo.checked==true){
		defaultyo=1;
    }else{
		defaultyo=0;
    }

    if(npwp==''){
		alert('No. NPWP harus diisi');
		return;
    }
	if(alamatnpwp==''){
		alert('Alamat NPWP harus diisi');
		return;
    }
	if(alamatdomisili==''){
		alert('Alamat Domisili harus diisi');
		return;
    }
   
	param='kodeorg='+org+'&npwp='+npwp+'&alamatnpwp='+alamatnpwp+'&switch='+method;
	param+='&alamatdom='+alamatdomisili+'&nopkp='+nopkp+'&statuss='+statuss+'&defaultyo='+defaultyo+'&inisial='+inisial+'&namakpp='+namakpp;

	tujuan='slave_save_org_npwp.php';
	if(confirm('Saving/Simpan ..?')){
	   post_response_text(tujuan, param, respog);			
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaddata();
					cancelnpwp();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}	
}

function loaddata(){
	param='switch=loaddata';
	tujuan='slave_save_org_npwp.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}  
}


