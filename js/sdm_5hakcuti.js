//JS 

function simpan()
{
    kodeorg=document.getElementById('kodeorg').value;
    kodegolongan=document.getElementById('kodegolongan').value;
    levelkaryawan=document.getElementById('levelkaryawan').value;
    hakcuti=document.getElementById('hakcuti').value;
    type=document.getElementById('type').value;
    bulanmulai=document.getElementById('bulanmulai').value;
    masaberlaku=document.getElementById('masaberlaku').value;
    method=document.getElementById('method').value;

    // if(kodeorg == ''){
    //     alertify.alert('Kode organisasi wajib diisi !');\
    //     return false;
    // }

    param='kodeorg='+kodeorg+'&kodegolongan='+kodegolongan+'&levelkaryawan='+levelkaryawan+'&hakcuti='+hakcuti+'&type='+type+'&bulanmulai='+bulanmulai+'&masaberlaku='+masaberlaku+'&method='+method;
    tujuan='sdm_slave_5hakcuti.php';
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
							cancel();
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
					

function cekcronjob(){

    tujuan='cronjob_hakcuti.php';
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
					else 
					{
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

function cancel()
{
   
    document.getElementById('kodeorg').value='';
    document.getElementById('kodegolongan').value='';
    document.getElementById('levelkaryawan').value='';
    document.getElementById('hakcuti').value='';
    document.getElementById('method').value='insert';
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('kodegolongan').disabled=false;
    document.getElementById('levelkaryawan').disabled=false;

}

function loadData(num){
	param='method=loadData';
    param+='&page='+num;

    tujuan='sdm_slave_5hakcuti.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt = con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);  
}

function edit(kodeorg,kodegolongan,levelkaryawan,type,bulanmulai,hakcuti,masaberlaku)
{
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('kodegolongan').value=kodegolongan;
    document.getElementById('kodegolongan').disabled=true;
    document.getElementById('levelkaryawan').value=levelkaryawan;
    document.getElementById('levelkaryawan').disabled=true;
    document.getElementById('type').value=type;
    document.getElementById('bulanmulai').value=bulanmulai;
    document.getElementById('hakcuti').value=hakcuti;
    document.getElementById('masaberlaku').value=masaberlaku;
    document.getElementById('method').value='update';
}



function del(kodegolongan)
{
	param='method=delete'+'&kodegolongan='+kodegolongan;
	tujuan='sdm_slave_5hakcuti.php';
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
					else 
					{
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




