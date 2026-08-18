function getunit(unit){
	pt=document.getElementById('pt').value;
	
	param='method=getunit&pt='+pt+'&unit='+unit;
    tujuan='sdm_slave_5stdwaktu.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('unit').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	} 
}

function getunitsearch(){
	spt=document.getElementById('spt').value;
	
	param='method=getunitsearch&spt='+spt;
    tujuan='sdm_slave_5stdwaktu.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('sunit').innerHTML=con.responseText;
					loaddata();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	} 
}

function updatejam(){
	jam = document.getElementById('jam10').value;
	
	document.getElementById('jam20').value = jam;
	document.getElementById('jam30').value = jam;
	document.getElementById('jam40').value = jam;
}

function updatemnt(){
	mnt = document.getElementById('mnt10').value;
	
	document.getElementById('mnt20').value = mnt;
	document.getElementById('mnt30').value = mnt;
	document.getElementById('mnt40').value = mnt;
}

function updatejamt(){
	jam = document.getElementById('jam11').value;
	
	document.getElementById('jam21').value = jam;
	document.getElementById('jam31').value = jam;
	document.getElementById('jam41').value = jam;
}

function updatemntt(){
	mnt = document.getElementById('mnt11').value;
	
	document.getElementById('mnt21').value = mnt;
	document.getElementById('mnt31').value = mnt;
	document.getElementById('mnt41').value = mnt;
}

function batal(){
	document.getElementById('kode').value = '';
    document.getElementById('pt').selectedIndex = 0;
	document.getElementById('pt').disabled=false;
	document.getElementById('unit').disabled=false;
    document.getElementById('keterangan').value = '';
    document.getElementById('jam10').selectedIndex = 0;
    document.getElementById('mnt10').selectedIndex = 0;
    document.getElementById('jam11').selectedIndex = 0;
    document.getElementById('mnt11').selectedIndex = 0;
	document.getElementById('jam20').selectedIndex = 0;
    document.getElementById('mnt20').selectedIndex = 0;
    document.getElementById('jam21').selectedIndex = 0;
    document.getElementById('mnt21').selectedIndex = 0;
	document.getElementById('jam30').selectedIndex = 0;
    document.getElementById('mnt30').selectedIndex = 0;
    document.getElementById('jam31').selectedIndex = 0;
    document.getElementById('mnt31').selectedIndex = 0;
	document.getElementById('jam40').selectedIndex = 0;
    document.getElementById('mnt40').selectedIndex = 0;
    document.getElementById('jam41').selectedIndex = 0;
    document.getElementById('mnt41').selectedIndex = 0;
    document.getElementById('stt').selectedIndex = 0;
    document.getElementById('method').value='insert';
	
	getunit();
}

function batalcari(){
    document.getElementById('spt').selectedIndex = 0;
    document.getElementById('sstt').selectedIndex = 0;
	
	getunitsearch();
}

function loaddata() {
	spt = document.getElementById('spt').value;
	sunit = document.getElementById('sunit').value;
	sstt = document.getElementById('sstt').value;
	
	param='method=loaddata&spt='+spt+'&sunit='+sunit+'&sstt='+sstt;
    tujuan='sdm_slave_5stdwaktu.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
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

function simpan(){
    kode = document.getElementById('kode').value;
    pt = document.getElementById('pt').value;
    unit = document.getElementById('unit').value;
    keterangan = document.getElementById('keterangan').value;
    jam10 = document.getElementById('jam10').value;
    mnt10 = document.getElementById('mnt10').value;
    jam11 = document.getElementById('jam11').value;
    mnt11 = document.getElementById('mnt11').value;
	jam20 = document.getElementById('jam20').value;
    mnt20 = document.getElementById('mnt20').value;
    jam21 = document.getElementById('jam21').value;
    mnt21 = document.getElementById('mnt21').value;
	jam30 = document.getElementById('jam30').value;
    mnt30 = document.getElementById('mnt30').value;
    jam31 = document.getElementById('jam31').value;
    mnt31 = document.getElementById('mnt31').value;
	jam40 = document.getElementById('jam40').value;
    mnt40 = document.getElementById('mnt40').value;
    jam41 = document.getElementById('jam41').value;
    mnt41 = document.getElementById('mnt41').value;
    stt = document.getElementById('stt').value;
    method=document.getElementById('method').value;

	param='kode='+kode+'&pt='+pt+'&unit='+unit+'&keterangan='+keterangan;
	param+='&jam10='+jam10+'&mnt10='+mnt10+'&jam11='+jam11+'&mnt11='+mnt11;
	param+='&jam20='+jam20+'&mnt20='+mnt20+'&jam21='+jam21+'&mnt21='+mnt21;
	param+='&jam30='+jam30+'&mnt30='+mnt30+'&jam31='+jam31+'&mnt31='+mnt31;
	param+='&jam40='+jam40+'&mnt40='+mnt40+'&jam41='+jam41+'&mnt41='+mnt41;
	param+='&stt='+stt+'&method='+method;
    tujuan='sdm_slave_5stdwaktu.php';
    post_response_text(tujuan, param, respog);      
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Success");
					batal();
                    loaddata(0);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}

function edit(kode,pt,unit,keterangan,jam10,mnt10,jam11,mnt11,jam20,mnt20,jam21,mnt21,jam30,mnt30,jam31,mnt31,jam40,mnt40,jam41,mnt41,stt){
	document.getElementById('kode').value=kode;
	document.getElementById('pt').value=pt;
	document.getElementById('pt').disabled=true;
	getunit(unit);
	document.getElementById('unit').disabled=true;
    document.getElementById('keterangan').value=keterangan;
	document.getElementById('jam10').value = jam10;
    document.getElementById('mnt10').value = mnt10;
    document.getElementById('jam11').value = jam11;
    document.getElementById('mnt11').value = mnt11;
	document.getElementById('jam20').value = jam20;
    document.getElementById('mnt20').value = mnt20;
    document.getElementById('jam21').value = jam21;
    document.getElementById('mnt21').value = mnt21;
	document.getElementById('jam30').value = jam30;
    document.getElementById('mnt30').value = mnt30;
    document.getElementById('jam31').value = jam31;
    document.getElementById('mnt31').value = mnt31;
	document.getElementById('jam40').value = jam40;
    document.getElementById('mnt40').value = mnt40;
    document.getElementById('jam41').value = jam41;
    document.getElementById('mnt41').value = mnt41;
    document.getElementById('stt').value = stt;
    
	document.getElementById('method').value='update';
}