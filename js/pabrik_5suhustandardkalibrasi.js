function cancel()
{
    document.getElementById('method').value='insert';
	kodeorg=document.getElementById('kodeorg');
	kodeorg.disabled=false;
	kodeorg=kodeorg.options[0].selected=true;
	kodetangki=document.getElementById('kodetangki');
	kodetangki.disabled=false;
	kodetangki=kodetangki.options[0].selected=true;
	periode=document.getElementById('periode');
	periode.disabled=false;
	periode=periode.options[0].selected=true;
    document.getElementById('suhu').value='0';
}

function loadData(){
	kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	optPeriode=document.getElementById('optPeriode').options[document.getElementById('optPeriode').selectedIndex].value;
	param='kodeorg='+kodeorg+'&optPeriode='+optPeriode+'&proses=loadData';
	tujuan='pabrik_slave_5suhustandardkalibrasi.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function simpan(){
	kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	kodetangki=document.getElementById('kodetangki').options[document.getElementById('kodetangki').selectedIndex].value;
	periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	suhu=document.getElementById('suhu').value;
	method=document.getElementById('method').value;
	
	param='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&periode='+periode+'&suhu='+suhu+'&proses='+method;
	tujuan='pabrik_slave_5suhustandardkalibrasi.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
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

function fillfield(kodeorg,kodetangki,periode,suhu){
	x=document.getElementById('kodeorg');
	for(a=0;a<x.length;a++){
		if(x.options[a].value==kodeorg){
			x.options[a].selected=true;
		}
	}
	x.disabled=true;
	y=document.getElementById('kodetangki');
	for(a=0;a<y.length;a++){
		if(y.options[a].value==kodetangki){
			y.options[a].selected=true;
		}
	}
	y.disabled=true;
	z=document.getElementById('periode');
	for(a=0;a<z.length;a++){
		if(z.options[a].value==periode){
			z.options[a].selected=true;
		}
	}
	z.disabled=true;
	document.getElementById('suhu').value=suhu;
	document.getElementById('method').value='update';
}

function deletefield(kodeorg,kodetangki,periode){
	param='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&periode='+periode+'&proses=delete';
	tujuan='pabrik_slave_5suhustandardkalibrasi.php';
	if(confirm("Anda yakin hapus item ini?"))
    {
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
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

function printPDF(ev)
{
	kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	optPeriode=document.getElementById('optPeriode').options[document.getElementById('optPeriode').selectedIndex].value;
	param='kodeorg='+kodeorg+'&optPeriode='+optPeriode+'&proses=pdf';
	
	showDialog1('Print PDF',"<iframe frameborder=0 style='width:595px;height:400px'"+
        " src='pabrik_slave_5suhustandardkalibrasi.php?"+param+"'></iframe>",'600','400',ev);
}