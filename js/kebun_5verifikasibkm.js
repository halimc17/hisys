function cancel(){
	document.getElementById('unit').disabled=false;
	setValue2('unit',null)
	document.getElementById('karyawanid').disabled=false;
	setValue2('karyawanid',null)
	document.getElementById('status').checked = true;
	document.getElementById('proses').value='insert';
}

function simpan(){
	proses = document.getElementById('proses').value;
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	karyawanid = document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
	stts = document.getElementById('status');

    if(stts.checked==true)
    {
        stts=1;
    }
    else
    {
        stts=0;
    }
	
	param='proses='+proses+'&unit='+unit+'&karyawanid='+karyawanid+'&status='+stts;
	tujuan='kebun_slave_5verifikasibkm';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert("Success");
					cancel();
                    loaddata(0);
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
	tujuan='kebun_slave_5verifikasibkm';
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

function deletefield(unit,karyawanid){
	param='proses=delete&unit='+unit+'&karyawanid='+karyawanid;
	tujuan='kebun_slave_5verifikasibkm';
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

function fillfield(unit,karyawanid,status){
    setValue2('unit',unit);
    setValue2('karyawanid',karyawanid);

	document.getElementById('unit').disabled=true;
	document.getElementById('karyawanid').disabled=true;

	if(status=='1')
    {
        document.getElementById('status').checked=true;
    }
    else
    {
        document.getElementById('status').checked=false;
    }

	document.getElementById('proses').value = 'edit';
}