function getdata(vhc,kelbrg) {
	
	param='';
    unit = document.getElementById('unit').value;
    tipe = document.getElementById('tipe').value;
    
	method='getdata';
	
	if(unit==''){
		alert('Unit tidak boleh kosong');
		document.getElementById('unit').value='';
		return false;
	}
	
	param+='unit='+unit+'&tipe='+tipe+'&method='+method;
	param+='&vhc='+vhc+'&kelbrg='+kelbrg;
	tujuan='setup_varianharga_slave.php';
	post_response_text(tujuan, param, respog);      
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else  {
					data = con.responseText.split("####");
					document.getElementById('vhc').innerHTML=data[0];
					document.getElementById('kelbrg').innerHTML=data[1];
					if(tipe=='vhc'){
						document.getElementById('persen').value='0';
						document.getElementById('persen').disabled=true;
						document.getElementById('rupiah').disabled=false;
					}
					if(tipe=='inv'){
						document.getElementById('rupiah').value='0';
						document.getElementById('rupiah').disabled=true;
						document.getElementById('persen').disabled=false;
					}
					
					
				}
			} else  {
				busy_off();
				error_catch(con.status);
			}
		} 
	}	
}






function simpan() {
	
	param='';
    unit = document.getElementById('unit').value;
    tgl = document.getElementById('tgl').value;
    tipe = document.getElementById('tipe').value;
    vhc = document.getElementById('vhc').value;
    kelbrg = document.getElementById('kelbrg').value;
    persen = document.getElementById('persen').value;
    rupiah = document.getElementById('rupiah').value;
    method=document.getElementById('method').value;
	
    if(unit==''||tipe==''||tgl==''){
		alert('Field Was Empty');
        return false;
    }
	
	param+='unit='+unit+'&tipe='+tipe+'&tgl='+tgl+'&vhc='+vhc+'&kelbrg='+kelbrg;
	param+='&persen='+persen+'&rupiah='+rupiah+'&method='+method;
    tujuan='setup_varianharga_slave.php';
    post_response_text(tujuan, param, respog);      
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else  {
					batal();
                    loaddata(0);
				}
			} else  {
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}



function edit(unit,tgl,tipe,vhc,kelbrg,persen,rupiah) {
	
	document.getElementById('unit').disabled = true;
	document.getElementById('tgl').disabled = true;
	document.getElementById('tipe').disabled = true;
	document.getElementById('vhc').disabled = true;
	document.getElementById('kelbrg').disabled = true;
	
	document.getElementById('unit').value=unit;
	document.getElementById('tgl').value=tgl;
	document.getElementById('tipe').value=tipe;
	document.getElementById('vhc').value=vhc;
	document.getElementById('kelbrg').value=kelbrg;
	document.getElementById('persen').value=persen;
	document.getElementById('rupiah').value=rupiah;
	
	document.getElementById('method').value='update';
	getdata(vhc,kelbrg);
}





function loaddata(num) {	
	unit = document.getElementById('unitcari').value;
	tipe = document.getElementById('tipecari').value;
	param = 'method=loaddata' + '&unit=' + unit + '&tipe=' + tipe;
    // param+='&jurnal='+jurnal+'&jurnalbalik='+jurnalbalik;
    param+='&page='+num;
    tujuan='setup_varianharga_slave.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			} else  {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function batal(){
	
	document.getElementById('unitcari').value='';
	document.getElementById('tipecari').value='';
	document.getElementById('unit').value='';
	document.getElementById('tipe').value='';
	document.getElementById('tgl').value='';
	document.getElementById('tipe').value='';
	document.getElementById('vhc').value='';
	document.getElementById('kelbrg').value='';
	document.getElementById('persen').value='0';
	document.getElementById('rupiah').value='0';
	document.getElementById('method').value='insert';
	document.getElementById('persen').disabled=false;
	document.getElementById('rupiah').disabled=false;
	document.getElementById('unit').disabled = false;
	document.getElementById('tgl').disabled = false;
	document.getElementById('tipe').disabled = false;
	document.getElementById('vhc').disabled = false;
	document.getElementById('kelbrg').disabled = false;
	loaddata(0);
}
















