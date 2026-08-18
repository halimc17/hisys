function cancel(){
	document.getElementById('periode1').disabled=false;
	document.getElementById('periode2').disabled=false;
	document.getElementById('kelaslahan').disabled=false;
	document.getElementById('kelaslahan').selectedIndex=0;
	document.getElementById('tahuntanam').disabled=false;
	document.getElementById('tahuntanam').selectedIndex=0;
	document.getElementById('nilai').value='0';
	document.getElementById('proses').value='insert';
}

function simpan(){
	proses = document.getElementById('proses').value;
	periode1 = document.getElementById('periode1').options[document.getElementById('periode1').selectedIndex].value;
	periode2 = document.getElementById('periode2').options[document.getElementById('periode2').selectedIndex].value;
	kelaslahan = document.getElementById('kelaslahan').options[document.getElementById('kelaslahan').selectedIndex].value;
	tahuntanam = document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	nilai = document.getElementById('nilai').value;
	
	param='proses='+proses+'&kelaslahan='+kelaslahan+'&tahuntanam='+tahuntanam+'&nilai='+nilai+'&periode1='+periode1+'&periode2='+periode2;
	tujuan='kebun_slave_5tandanhathn';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('container').innerHTML=con.responseText;
					cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddata(num){
	param='proses=loaddata&page='+num;
	tujuan='kebun_slave_5tandanhathn';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
				  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(periode1,periode2,kelaslahan,tahuntanam,nilai){
	param='proses=delete&kelaslahan='+kelaslahan+'&tahuntanam='+tahuntanam+'&nilai='+nilai+'&periode1='+periode1+'&periode2='+periode2;
	tujuan='kebun_slave_5tandanhathn';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
				  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(periode1,periode2,kodelahan,tahuntanam,nilai){
	document.getElementById('kelaslahan').disabled=true;	
	kelaslahan=document.getElementById('kelaslahan');
	for(z=0;z<kelaslahan.length;z++)
	{
		if(kelaslahan.options[z].value==kodelahan){
            kelaslahan.options[z].selected=true;
        }
	}
    document.getElementById('tahuntanam').disabled=true;	
    document.getElementById('periode1').disabled=true;	
	document.getElementById('periode1').value = periode1;

    document.getElementById('periode2').disabled=true;	
    document.getElementById('periode2').value = periode2;


	thntnm=document.getElementById('tahuntanam');
    for (let k = 0; k < thntnm.length; k++) {
        if (thntnm.options[k].value==tahuntanam) {
            thntnm.options[k].selected = true;
        }
    }

	document.getElementById('nilai').value = nilai;
	document.getElementById('proses').value = 'edit';
}