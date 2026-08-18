function getkode(kodekriteria,idnilai){
	if((kodekriteria==0)&&(kodekriteria==0)){
		getdt=document.getElementById('kriteria');
		getdt=getdt.options[getdt.selectedIndex].value;	
	}else{
		getdt=kodekriteria;
		document.getElementById('idnilai').value=idnilai;
	}
	document.getElementById('kodekriteria').value=getdt;
}

function simpan(){
	kriteria=document.getElementById('kriteria').options[document.getElementById('kriteria').selectedIndex].value;
	kodekriteria=document.getElementById('kodekriteria').value;
	idnilai=document.getElementById('idnilai').value;
	penilaian=document.getElementById('penilaian').value;
	method=document.getElementById('method').value;
	param='kriteria='+kriteria+'&kodekriteria='+kodekriteria+'&penilaian='+penilaian;
	param+='&idnilai='+idnilai+'&method='+method;
	tujuan='sdm_slave_5kriteriapenilaian.php';
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
	kriteria=document.getElementById('kriteria');
	kriteria.disabled=false;
	kriteria=kriteria.options[0].selected=true;
    document.getElementById('kriteria').disabled=false;
	document.getElementById('kodekriteria').value='';
	document.getElementById('idnilai').value='';
	document.getElementById('penilaian').value='';
	document.getElementById('method').value='insert';		
}

function loadData(num){

    param='method=loadData';
	param+='&page='+num;

	tujuan='sdm_slave_5kriteriapenilaian.php';
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

function fillfield(kriteria,kodekriteria,idnilai,penilaian){
	x=document.getElementById('kriteria');
	for(a=0;a<x.length;a++){
		if(x.options[a].value==kriteria){
			x.options[a].selected=true;
		}
	}
	x.disabled=true;
	document.getElementById('kodekriteria').value=kodekriteria;
	document.getElementById('idnilai').value=idnilai;
	document.getElementById('penilaian').value=penilaian;
	document.getElementById('kodekriteria').disabled=true;
	document.getElementById('method').value='update';
}