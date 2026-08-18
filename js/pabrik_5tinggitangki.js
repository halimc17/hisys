function cancelTinggiTangki()
{
    document.getElementById('method').value='insert';
	kodeorg=document.getElementById('kodeorg');
	kodeorg.disabled=false;
	kodeorg=kodeorg.options[0].selected=true;
	kodetangki=document.getElementById('kodetangki');
	kodetangki.disabled=false;
	kodetangki=kodetangki.options[0].selected=true;
    document.getElementById('tinggi').value='0';
	document.getElementById('volume').value='0';
	document.getElementById('beda').value='0';
}

function loadData(num){

    param='proses=loadData';
    param+='&page='+num;

    tujuan='pabrik_slave_5tinggitangki.php';
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

function simpanTinggiTangki(){
	kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	kodetangki=document.getElementById('kodetangki').options[document.getElementById('kodetangki').selectedIndex].value;
	tinggi=document.getElementById('tinggi').value;
	volume=document.getElementById('volume').value;
	beda=document.getElementById('beda').value;
	method=document.getElementById('method').value;
	
	param='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&tinggi='+tinggi+'&volume='+volume+'&beda='+beda+'&proses='+method;
	tujuan='pabrik_slave_5tinggitangki.php';
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
					cancelTinggiTangki();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function fillfield(kodeorg,kodetangki,tinggi,volume,beda){
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
	document.getElementById('tinggi').value=tinggi;
	document.getElementById('volume').value=volume;
	document.getElementById('beda').value=beda;
	document.getElementById('method').value='update';
}

function deletefield(kodeorg,kodetangki,tinggi){
	param='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&tinggi='+tinggi+'&proses=delete';
	tujuan='pabrik_slave_5tinggitangki.php';
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
					cancelTinggiTangki();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function tinggiTangkiPDF(ev)
{
	kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='kodeorg='+kodeorg+'&proses=pdf';
	
	showDialog1('Print PDF',"<iframe frameborder=0 style='width:595px;height:400px'"+
        " src='pabrik_slave_5tinggitangki.php?"+param+"'></iframe>",'600','400',ev);
}