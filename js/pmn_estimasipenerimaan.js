function simpan(){
	periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	kodebarang=document.getElementById('kodebarang').value;
	qty=document.getElementById('qty').value;
	harga=document.getElementById('harga').value;
	method=document.getElementById('method').value;
	param='pt='+pt+'&periode='+periode+'&kodebarang='+kodebarang;
	param+='&qty='+qty+'&harga='+harga;
	param+='&method='+method;
	tujuan='pmn_slave_estimasipenerimaan.php';
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
    document.getElementById('pt').disabled=false;
	document.getElementById('pt').value='';
    document.getElementById('periode').disabled=false;
	document.getElementById('periode').value='';
	document.getElementById('kodebarang').disabled=false;
	document.getElementById('kodebarang').value='';
	document.getElementById('qty').value='';
	document.getElementById('harga').value='';
	document.getElementById('method').value='insert';		
}

function loadData(num){
    param='method=loadData';
	param+='&page='+num;
	tujuan='pmn_slave_estimasipenerimaan.php';
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

function fillfield(periode,pt,kodebarang,qty,harga){
	document.getElementById('pt').value=pt;
	document.getElementById('pt').disabled=true;
	document.getElementById('periode').value=periode;
	document.getElementById('periode').disabled=true;
	document.getElementById('kodebarang').value=kodebarang;
	document.getElementById('kodebarang').disabled=true;
	document.getElementById('qty').value=qty;
	document.getElementById('harga').value=harga;
}

function deldt(periode,pt,kodebarang)
{
    param='method=deldt'+'&periode='+periode+'&pt='+pt+'&kodebarang='+kodebarang;
    tujuan='pmn_slave_estimasipenerimaan.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                   loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function posting(periode,pt,kodebarang)
{
    param='method=posting'+'&periode='+periode+'&pt='+pt+'&kodebarang='+kodebarang;
    tujuan='pmn_slave_estimasipenerimaan.php';
    if(confirm('Anda yakin ingin memposting ini ??'))
    {
        post_response_text(tujuan, param, respog);  
    }
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
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}








