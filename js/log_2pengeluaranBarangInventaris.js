function setAll()
{
    setValue('kodebarang','');
    setValue('nopo','');
	document.getElementById('container').innerHTML='';
	document.getElementById('container').innerHTML='';
}

function showWindowBarang(title,ev)
{
	content= "<div style='width:100%;'>";
	content+="<fieldset>Search <input type=text id=txtnamabarang  class=myinputtext size=25 onkeypress=\"return eventEnter(event);\" maxlength=35><button class=mybutton onclick=goCariBarang()>Search</button>";
	content+="<div id=containercari style='overflow:auto;max-height:250px;max-width:450px'></div></div></fieldset>";
	//display window
	width='';
	height='';
	showDialog1(title,content,width,height,ev);	
	document.getElementById('txtnamabarang').focus();
}

function eventEnter(evt)
{
	key=getKey(evt);
	if(key==13){
		goCariBarang();
	}else{
		return tanpa_kutip(evt);
	}
}

function goCariBarang(){
	txtcari = trim(document.getElementById('txtnamabarang').value);
    if (txtcari.length < 2){
		alert('material name min. 2 char');
	}else{
		param = 'txtcari=' + txtcari;
        tujuan = 'log_slave_2transaksigudangcari.php';
        post_response_text(tujuan, param, respog);
    }
    
    function respog(){
		if (con.readyState == 4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('containercari').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

function loadField(kode)
{
	setValue('kodebarang',kode);
	closeDialog();		
}

function proses(){ 
	kodebarang = getValue('kodebarang');
	nopo = getValue('nopo');
	periode = getValue('periode');
	param='kodebarang='+kodebarang+'&nopo='+nopo+'&periode='+periode+'&proses=showdata';
	tujuan='log_slave_2pengeluaranBarangInventaris.php';
	post_response_text(tujuan, param, respog);
	
	
	function respog(){
		if (con.readyState == 4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					showById('printPanel');
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}