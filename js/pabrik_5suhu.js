function cancel()
{
    document.getElementById('method').value='insert';
	kodeorg=document.getElementById('kodeorg');
	kodeorg.disabled=false;
	kodeorg=kodeorg.options[0].selected=true;
	kodetangki=document.getElementById('kodetangki');
	kodetangki.disabled=false;
	kodetangki=kodetangki.options[0].selected=true;
    document.getElementById('suhu').value='0';
    document.getElementById('suhu').disabled=false;
	document.getElementById('beratjenis').value='0';
	document.getElementById('varian').value='0';
}

function loadData(){
	kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='kodeorg='+kodeorg+'&proses=loadData';
	tujuan='pabrik_slave_5suhu.php';
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
	suhu=document.getElementById('suhu').value;
	beratjenis=document.getElementById('beratjenis').value;
	varian=document.getElementById('varian').value;
	method=document.getElementById('method').value;
	
	param='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&suhu='+suhu+'&beratjenis='+beratjenis+'&varian='+varian+'&proses='+method;
	tujuan='pabrik_slave_5suhu.php';
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

function fillfield(kodeorg,kodetangki,suhu,beratjenis,varian){
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
	document.getElementById('suhu').value=suhu;
	document.getElementById('suhu').disabled=true;
	document.getElementById('beratjenis').value=beratjenis;
	document.getElementById('varian').value=varian;
	document.getElementById('method').value='update';
}

function deletefield(kodeorg,kodetangki,suhu){
	param='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&suhu='+suhu+'&proses=delete';
	tujuan='pabrik_slave_5suhu.php';
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
	param='kodeorg='+kodeorg+'&proses=pdf';
	
	showDialog1('Print PDF',"<iframe frameborder=0 style='width:595px;height:400px'"+
        " src='pabrik_slave_5suhu.php?"+param+"'></iframe>",'600','400',ev);
}