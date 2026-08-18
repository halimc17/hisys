function gethitung(){
	sawal=document.getElementById('sawal').value;
	masuk=document.getElementById('masuk').value;
	keluar=document.getElementById('keluar').value;
	salak=parseFloat(sawal)+parseFloat(masuk)-parseFloat(keluar);
	document.getElementById('salak').value=salak;
}


function getdata(){  
    mesin=document.getElementById('mesin').value;
    tgl=document.getElementById('tgl').value;
    param='mesin='+mesin+'&method=getdata'+'&tgl='+tgl;
    tujuan='pabrik_slave_pemakaianbbm.php';
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
					   //alert(con.responseText);
						arr=con.responseText.split("##");
						document.getElementById('sawal').value=arr[0];
						document.getElementById('masuk').value=arr[1];
						gethitung();
						//document.getElementById('sisatbskemarin').value=con.responseText;
				}
			}
			else {
					busy_off();
					error_catch(con.status);
			}
		}	
     }  

}



function getmesin(station,mesin){
    station=document.getElementById('station').value; 
    param='method=getmesin'+'&station='+station+'&mesin='+mesin;
    tujuan='pabrik_slave_pemakaianbbm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }  else {
                    //alert(con.responseText);
                    document.getElementById('mesin').innerHTML=con.responseText;
                    //.value=trim(con.responseText);
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}





function simpan(){
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	station=document.getElementById('station').options[document.getElementById('station').selectedIndex].value;
	mesin=document.getElementById('mesin').options[document.getElementById('mesin').selectedIndex].value;
	method=document.getElementById('method').value;
	tgl=document.getElementById('tgl').value;
	sawal=document.getElementById('sawal').value;
	masuk=document.getElementById('masuk').value;
	keluar=document.getElementById('keluar').value;
	salak=document.getElementById('salak').value;
	param='unit='+unit+'&station='+station+'&mesin='+mesin;
	param+='&tgl='+tgl+'&method='+method;
	param+='&sawal='+sawal+'&masuk='+masuk;
	param+='&keluar='+keluar+'&salak='+salak;
	tujuan='pabrik_slave_pemakaianbbm.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    //alert(con.responseText);
					cancel();
					loadData();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function cancel(){
    document.getElementById('station').disabled=false;
	document.getElementById('station').value='';
	document.getElementById('mesin').disabled=false;
	document.getElementById('mesin').value='';	
	document.getElementById('tgl').disabled=false;
	document.getElementById('tgl').value='';
	document.getElementById('sawal').value='0';
	document.getElementById('masuk').value='0';
	document.getElementById('keluar').value='0';
	document.getElementById('salak').value='0';
	document.getElementById('method').value='insert';		
}

function loadData(num){
    param='method=loadData';
	param+='&page='+num;
	tujuan='pabrik_slave_pemakaianbbm.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
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

function fillfield(unit,station,mesin,tgl,sawal,masuk,keluar,salak){

	document.getElementById('unit').value=unit;
	document.getElementById('station').value=station;
	document.getElementById('station').disabled=true;
	document.getElementById('mesin').value=mesin;
	document.getElementById('mesin').disabled=true;
	document.getElementById('tgl').value=tgl;
	document.getElementById('tgl').disabled=true;
	document.getElementById('sawal').value=sawal;
	document.getElementById('masuk').value=masuk;
	document.getElementById('keluar').value=keluar;
	document.getElementById('salak').value=salak;
	document.getElementById('method').value='update';
	getmesin(station,mesin);
}