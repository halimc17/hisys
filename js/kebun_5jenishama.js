function batal(){
	document.getElementById('method').value = 'insert';
	document.getElementById('kodehama').value = '';
	document.getElementById('kodehama').disabled = false;
	document.getElementById('namahama').value = '';
	document.getElementById("satuan").selectedIndex = "0";
}

function loadData(){
	param='method=loaddata';
	tujuan='kebun_slave_5jenishama.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							document.getElementById('container').innerHTML=con.responseText;
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(kodehama,namahama,satuan){
	document.getElementById('kodehama').disabled=true;
	document.getElementById('kodehama').value=kodehama;
	vSatuan = document.getElementById('satuan');
    for(ard=0;ard<vSatuan.length;ard++)
    {
        if(vSatuan.options[ard].value==satuan)
            {
                vSatuan.options[ard].selected=true;
            }
    }
	document.getElementById('namahama').value=namahama;
	document.getElementById('method').value='update';
}

function simpan(){
	method = trim(document.getElementById('method').value);
	kodehama = trim(document.getElementById('kodehama').value);
	namahama = trim(document.getElementById('namahama').value);
	satuan = document.getElementById('satuan').options[document.getElementById('satuan').selectedIndex].value;
	
	param='method='+method+'&kodehama='+kodehama+'&namahama='+namahama+'&satuan='+satuan;
	tujuan='kebun_slave_5jenishama.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}