function getId(kodespel,idpel){
	if((kodespel==0)&&(kodespel==0)){
		getsppelanggaran=document.getElementById('sppelanggaran');
		getsppelanggaran=getsppelanggaran.options[getsppelanggaran.selectedIndex].value;	
	}else{
		getsppelanggaran=kodespel;
		document.getElementById('idpelanggaran').value=idpel;
	}
	document.getElementById('kodesp').value=getsppelanggaran;
}

function simpan(){
	jenispel=document.getElementById('jenispel').value;
	sppelanggaran=document.getElementById('sppelanggaran').options[document.getElementById('sppelanggaran').selectedIndex].value;
	kodesp=document.getElementById('kodesp').value;
	idpelanggaran=document.getElementById('idpelanggaran').value;
	method=document.getElementById('method').value;
	statusDt=document.getElementById('statusDt');
	param='jenispel='+jenispel+'&sppelanggaran='+sppelanggaran+'&kodesp='+kodesp;
	param+='&idpelanggaran='+idpelanggaran+'&proses='+method;
	if(statusDt.checked==true){
		param+='&stat=1';
	}else if(statusDt.checked==false){
		param+='&stat=0';
	}
	tujuan='sdm_slave_5jenispelanggaran.php';
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
    document.getElementById('method').value='insert';
    document.getElementById('jenispel').value='';
	sppelanggaran=document.getElementById('sppelanggaran');
	sppelanggaran.disabled=false;
	sppelanggaran=sppelanggaran.options[0].selected=true;
	kodesp=document.getElementById('kodesp');
	kodesp.value="";
	idpelanggaran=document.getElementById('idpelanggaran');
	idpelanggaran.value="";
	statusDt=document.getElementById('statusDt');
	statusDt.checked=false;
}

function loadData(num){
	param='proses=loadData';
	param+='&page='+num;
	tujuan='sdm_slave_5jenispelanggaran.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function fillfield(jenispel,kodesp,idpelanggaran,stat){
	x=document.getElementById('sppelanggaran');
	for(a=0;a<x.length;a++){
		if(x.options[a].value==kodesp){
			x.options[a].selected=true;
		}
	}
	x.disabled=true;
	document.getElementById('kodesp').value=kodesp;
	document.getElementById('idpelanggaran').value=idpelanggaran;
	document.getElementById('idpelanggaran').disabled=true;
	document.getElementById('jenispel').value=jenispel;
	document.getElementById('method').value='update';
	dtck=document.getElementById('statusDt');
	if(stat=='1'){
		dtck.checked=true;
	}else if(stat=='0'){
		dtck.checked=false;
	}
	
}