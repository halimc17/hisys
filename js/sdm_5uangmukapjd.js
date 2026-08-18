// JavaScript Document
function cancel(){
	document.getElementById('regional').selectedIndex=0;
	document.getElementById('regional').disabled = false;
	document.getElementById('kodegolongan').selectedIndex=0;
	document.getElementById('kodegolongan').disabled = false;
	document.getElementById('jenis').selectedIndex=0;
	document.getElementById('sekali').value='0';
	document.getElementById('perhari').value='0';
	document.getElementById('hariketiga').value='0';
	document.getElementById('kode').value='';
	document.getElementById('method').value='insert';
}

function loadData(){
	param='method=loadData';
	tujuan='sdm_slave_5uangmukapjd';
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

function fillfield(kode,regional,kodegolongan,jenis,sekali,perhari,hariketiga){
	hregional = document.getElementById('regional');
	for(x=0;x<hregional.length;x++){
		if(hregional.options[x].value==regional){
			hregional.options[x].selected=true;
		}
	}
	hkodegolongan = document.getElementById('kodegolongan');
	setValue('kodegolongan',kodegolongan);
	hregional.disabled=true;
	hkodegolongan.disabled=true;
	document.getElementById('kode').value=kode;
	setValue('jenis',jenis);
	document.getElementById('sekali').value=sekali;
	document.getElementById('perhari').value=perhari;
	document.getElementById('hariketiga').value=hariketiga;
	document.getElementById('method').value='update';
}

function deleteData(kode){
	param='kode='+kode+'&method=delete';
	tujuan='sdm_slave_5uangmukapjd.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData();
					cancel();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function save(){
	regional=document.getElementById('regional').options[document.getElementById('regional').selectedIndex].value;
	kodegolongan=document.getElementById('kodegolongan').options[document.getElementById('kodegolongan').selectedIndex].value;
	kode=trim(document.getElementById('kode').value);
	jenis=getValue('jenis');
	sekali=trim(document.getElementById('sekali').value);
	perhari=trim(document.getElementById('perhari').value);
	hariketiga=trim(document.getElementById('hariketiga').value);
	method=trim(document.getElementById('method').value);
	
	param='kode='+kode+'&regional='+regional+'&kodegolongan='+kodegolongan+'&jenis='+jenis+'&sekali='+sekali+'&perhari='+perhari+'&hariketiga='+hariketiga+'&method='+method;
	tujuan='sdm_slave_5uangmukapjd.php';
	post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('container').innerHTML=con.responseText;
					cancel();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 
}