function simpan(){
    method=document.getElementById('method').value;
    pt=document.getElementById('pt').value;
	jenis=document.getElementById('jenis').value;
	posisi=document.getElementById('posisi').value;
	basis=document.getElementById('basis').value;
	premibasis=document.getElementById('premibasis').value;
	premilebihbasis=document.getElementById('premilebihbasis').value;
    if(pt=='' || jenis=='' ||posisi=='' ||basis=='' ||premibasis=='' ||premilebihbasis==''){
        alert('Please complete the form');return;
    }
    param='pt='+pt+'&jenis='+jenis+'&posisi='+posisi;
	param+='&basis='+basis+'&premibasis='+premibasis+'&premilebihbasis='+premilebihbasis;
    param+='&method='+method;
    tujuan='vhc_slave_5premialatberat.php';
    post_response_text(tujuan, param, respog);		
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    hapus();							
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }
}

function hapus(){
    document.getElementById('method').value='insert';
    document.getElementById('pt').value='';
    document.getElementById('jenis').value='';
	document.getElementById('posisi').value='';
    document.getElementById('pt').disabled=false;
	document.getElementById('jenis').disabled=false;
	document.getElementById('posisi').disabled=false;
	document.getElementById('basis').value='0';
	document.getElementById('premibasis').value='0';
	document.getElementById('premilebihbasis').value='0';		
}

function loadData(num) {
	param='method=loadData';
	param+='&page='+num;
	tujuan='vhc_slave_5premialatberat.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function fillField(pt,jenis,posisi,basis,premibasis,premilebihbasis){
	document.getElementById('pt').value=pt;
	document.getElementById('pt').disabled=true;
    document.getElementById('jenis').value=jenis;
	document.getElementById('jenis').disabled=true;
	document.getElementById('posisi').value=posisi;
	document.getElementById('posisi').disabled=true;
    document.getElementById('method').value='update';	
	document.getElementById('basis').value=basis;
	document.getElementById('premilebihbasis').value=premilebihbasis;
	document.getElementById('premibasis').value=premibasis;
}

function del(pt,jenis){
    param='method=delete'+'&pt='+pt+'&jenis='+jenis;
    tujuan='vhc_slave_5premialatberat.php';
    if(confirm("Delete data?")){
            post_response_text(tujuan, param, respog);	
    }
    function respog(){
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                    loadData();	
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}