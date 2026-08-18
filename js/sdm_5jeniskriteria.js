function simpan(){
	kode=document.getElementById('kode').value;
	kriteria=document.getElementById('kriteria').value;
	method=document.getElementById('method').value;
	if((trim(kode)=='')&&(trim(kriteria)==''))
	{
		alert('Data tidak boleh kosong');
	}else{
		kode=trim(kode);
		kriteria=trim(kriteria);
		param='kode='+kode+'&kriteria='+kriteria+'&method='+method;
		tujuan='sdm_slave_5jeniskriteria.php';
        post_response_text(tujuan, param, respog);		
	}
	
	function respog(){
		if(con.readyState==4){
		    if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('container').innerHTML=con.responseText;
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
	document.getElementById('kriteria').value='';
	document.getElementById('method').value='insert';		
}

function loadData(num){

    param='method=loadData';
	param+='&page='+num;

	tujuan='sdm_slave_5jeniskriteria.php';
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


function fillfield(kode,kriteria){
	document.getElementById('kode').value=kode;
    document.getElementById('kode').disabled=true;
	document.getElementById('kriteria').value=kriteria;
	document.getElementById('method').value='update';
}

function del(kode){
    param='method=delete'+'&kode='+kode;
    tujuan='sdm_slave_5jeniskriteria.php';

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