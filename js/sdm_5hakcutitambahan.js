//JS 

function simpan()
{
    kodeorg=document.getElementById('kodeorg').value;
    kodegolongan=document.getElementById('kodegolongan').value;
    levelkaryawan=document.getElementById('levelkaryawan').value;
    tipekaryawan=document.getElementById('tipekaryawan').value;
    masakerja=document.getElementById('masakerja').value;
    masaaktif=document.getElementById('masaaktif').value;
    hakcuti=document.getElementById('hakcuti').value;
    method=document.getElementById('method').value;

    param='kodeorg='+kodeorg+'&kodegolongan='+kodegolongan+'&levelkaryawan='+levelkaryawan+'&tipekaryawan='+tipekaryawan+'&masakerja='+masakerja+'&masaaktif='+masaaktif+'&hakcuti='+hakcuti+'&method='+method;
    tujuan='sdm_slave_5hakcutitambahan.php';
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
					


function cancel()
{
   
    document.getElementById('kodeorg').value='';
    document.getElementById('kodegolongan').value='';
    document.getElementById('levelkaryawan').value='';
    document.getElementById('tipekaryawan').value='';
    document.getElementById('masakerja').value='';
    document.getElementById('masaaktif').value='';
    document.getElementById('hakcuti').value='';
    document.getElementById('method').value='insert';
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('kodegolongan').disabled=false;
    document.getElementById('levelkaryawan').disabled=false;
    document.getElementById('tipekaryawan').disabled=false;

}

function loadData(num){
	param='method=loadData';
    param+='&page='+num;

    tujuan='sdm_slave_5hakcutitambahan.php';
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

function edit(kodeorg,kodegolongan,levelkaryawan,tipekaryawan,masaaktif,masakerja,hakcuti)
{
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('kodegolongan').value=kodegolongan;
    document.getElementById('kodegolongan').disabled=true;
    document.getElementById('levelkaryawan').value=levelkaryawan;
    document.getElementById('levelkaryawan').disabled=true;
    document.getElementById('tipekaryawan').value=tipekaryawan;
    document.getElementById('tipekaryawan').disabled=true;
    document.getElementById('masaaktif').value=masaaktif;
    document.getElementById('masakerja').value=masakerja;
    document.getElementById('hakcuti').value=hakcuti;
    document.getElementById('method').value='update';
}



function del(kodegolongan)
{
	param='method=delete'+'&kodegolongan='+kodegolongan;
	tujuan='sdm_slave_5hakcutitambahan.php';
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




