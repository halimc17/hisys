function simpan(){
	kode=document.getElementById('kode').value;
	nama=document.getElementById('nama').value;
	method=document.getElementById('method').value;
	if((trim(kode)=='')&&(trim(nama)==''))
	{
		alert('Data tidak boleh kosong');
	}else{
		kode=trim(kode);
		nama=trim(nama);
		param='kode='+kode+'&nama='+nama+'&method='+method;
		tujuan='pabrik_slave_5db_ksf.php';
        post_response_text(tujuan, param, respog);		
	}
	
	function respog(){
		if(con.readyState==4){
		    if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancel();
 					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}	
}

function cancel(){
    document.getElementById('kode').disabled=false;
	document.getElementById('kode').value='';
	document.getElementById('nama').value='';
	document.getElementById('method').value='insert';		
}

function loadData(num){

    param='method=loadData';
	param+='&page='+num;

	tujuan='pabrik_slave_5db_ksf.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
                    isdt = con.responseText.split("####");
                    document.getElementById('container').innerHTML = isdt[0];
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


function fillfield(kode,nama){
	document.getElementById('kode').value=kode;
    document.getElementById('kode').disabled=true;
	document.getElementById('nama').value=nama;
	document.getElementById('method').value='update';
}

function del(kode){
    param='method=delete'+'&kode='+kode;
    tujuan='pabrik_slave_5db_ksf.php';

    post_response_text(tujuan, param, respog);

    function respog(){
            if(con.readyState==4){
                if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    } else {
                       loadData();  
                    }
                }else{
                    busy_off();
                    error_catch(con.status);
                }
            } 
    }
}