function cancel(){
	document.getElementById('regional').disabled=false;
	document.getElementById('status').disabled=false;
	document.getElementById('periode').disabled=false;
	document.getElementById('regional').selectedIndex=0;
	document.getElementById('status').selectedIndex=0;
	setValue('periode',getValue('currPeriod'));
	document.getElementById('hargasatuan').value='0';
	document.getElementById('proses').value='insert';
}

function simpan(){
	proses = document.getElementById('proses').value;
	regional = document.getElementById('regional').options[document.getElementById('regional').selectedIndex].value;
	status = document.getElementById('status').options[document.getElementById('status').selectedIndex].value;
	periode = getValue('periode');
	hargasatuan = document.getElementById('hargasatuan').value;
	
	param='proses='+proses+'&regional='+regional+'&status='+status+'&periode='+periode+'&hargasatuan='+hargasatuan;
	tujuan='kebun_slave_5hargabibit';
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
	tujuan='kebun_slave_5hargabibit';
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

function deletefield(regional,status,periode){
	param='proses=delete&regional='+regional+'&status='+status+'&periode='+periode;
	tujuan='kebun_slave_5hargabibit';
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

function fillfield(regional,status,periode,hargasatuan){
	document.getElementById('regional').disabled=true;
	document.getElementById('status').disabled=true;
	document.getElementById('periode').disabled=true;
	
	x=document.getElementById('regional');
	for(z=0;z<x.length;z++)
	{
		if(x.options[z].value==regional)
		x.options[z].selected=true;
	}
	
	x=document.getElementById('status');
	for(z=0;z<x.length;z++)
	{
		if(x.options[z].value==status)
		x.options[z].selected=true;
	}
	// document.getElementById('regional').selected.value = regional;
	// document.getElementById('status').selected.value = status;
	setValue('periode',periode);
	document.getElementById('hargasatuan').value = hargasatuan;
	document.getElementById('proses').value = 'edit';
}