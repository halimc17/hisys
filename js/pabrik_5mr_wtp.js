function simpan(){
	kode=document.getElementById('kdTrans');
	kode=kode.options[kode.selectedIndex].value;
	stationId=document.getElementById('stationId');
	stationId=stationId.options[stationId.selectedIndex].value;
	kdBrg=document.getElementById('kdBrg');
	kdBrg=kdBrg.options[kdBrg.selectedIndex].value;
    method=document.getElementById('method').value;
	primId=document.getElementById('primId').value;
	param='kdTrans='+kode+'&kdBrg='+kdBrg+'&method='+method+'&stationId='+stationId+'&primId='+primId;
	tujuan='pabrik_slave_5mr_wtp.php';
    post_response_text(tujuan, param, respog);		
	 
	
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
    document.getElementById('kdTrans').disabled=false;
    document.getElementById('stationId').disabled=false;
    document.getElementById("kdTrans").selectedIndex = "0";
    document.getElementById("stationId").selectedIndex = "0";
    document.getElementById("kdBrg").selectedIndex = "0";
	document.getElementById('primId').value='';
	document.getElementById('method').value='insert';		
}

function loadData(num){

    param='method=loadData';
	param+='&page='+num;

	tujuan='pabrik_slave_5mr_wtp.php';
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


function fillfield(primid,kdtrans,station,kdbrg){
	document.getElementById('primId').value=primid;
	l=document.getElementById('kdTrans');
    for(a=0;a<l.length;a++){
        if(l.options[a].value==kdtrans)
            {
                l.options[a].selected=true;
            }
    }
    //document.getElementById('kdTrans').disabled=true;
    l2=document.getElementById('stationId');
    for(a=0;a<l2.length;a++){
        if(l2.options[a].value==station)
            {
                l2.options[a].selected=true;
            }
    }
    //document.getElementById('stationId').disabled=true;kdbrg
    barang=document.getElementById('kdBrg');
    for(a=0;a<barang.length;a++){
        if(barang.options[a].value==kdbrg){
                barang.options[a].selected=true;
        }
    }
	document.getElementById('method').value='update';
}

function del(kode){
    param='method=delete'+'&kode='+kode;
    tujuan='pabrik_slave_5mr_wtp.php';

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