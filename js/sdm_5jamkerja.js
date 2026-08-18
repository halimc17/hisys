//JS 

function cariBast(num){
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'sdm_slave_5jamkerja.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
								document.getElementById('container').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }
    }	
}


function simpan(){
	
	jmMulai=document.getElementById('jmMulai').value;
    mnMulai=document.getElementById('mnMulai').value;
    
    jmSelesai=document.getElementById('jmSelesai').value;
    mnSelesai=document.getElementById('mnSelesai').value;
	
	jamkerja=trim(document.getElementById('jamkerja').value);
	hari=trim(document.getElementById('hari').value);
	kodeunit=trim(document.getElementById('kodeunit').value);
	method=trim(document.getElementById('method').value);
	
	
	if(kodeunit=='' || hari=='' || jamkerja==''){
		alert('Please complete the form');return;
	}
	
	param='kodeunit='+kodeunit+'&hari='+hari+'&jamkerja='+jamkerja+'&method='+method;
	param+='&jmMulai='+jmMulai+'&mnMulai='+mnMulai;
	param+='&jmSelesai='+jmSelesai+'&mnSelesai='+mnSelesai;
	tujuan='sdm_slave_5jamkerja.php';
        post_response_text(tujuan, param, respog);		
	
	function respog() {
		if(con.readyState==4)  {
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

function hapus() {
	document.getElementById('kodeunit').value='';
	document.getElementById('hari').value='';
	document.getElementById('jamkerja').value=0;	
	document.getElementById('jmMulai').value='00';	
	document.getElementById('mnMulai').value='00';		
	document.getElementById('jmSelesai').value='00';	
	document.getElementById('mnSelesai').value='00';	
	document.getElementById('method').value='insert';
	document.getElementById('hari').disabled=false;
	document.getElementById('kodeunit').disabled=false;
}

function loadData () {
	param='method=loadData';
	tujuan='sdm_slave_5jamkerja.php';
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
                                   // alert(con.responseText);
                                    document.getElementById('container').innerHTML=con.responseText;	
									// getperiodesort();
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function fillField(hari,kodesupplier,kodeunit,vhc,jamkerja,jamkerjaperkg)
{
	document.getElementById('hari').value=hari;
	document.getElementById('kodesupplier').value=kodesupplier;
	document.getElementById('kodeunit').value=kodeunit;
	document.getElementById('vhc').value=vhc;
        
        document.getElementById('hari').disabled=true;
	document.getElementById('kodesupplier').disabled=true;
	document.getElementById('kodeunit').disabled=true;
	document.getElementById('vhc').disabled=true;
        
	document.getElementById('jamkerja').value=jamkerja;
	document.getElementById('jamkerjaperkg').value=jamkerjaperkg;
	document.getElementById('method').value='update';

	
}

function del(kodeunit,hari)
{
	param='method=delete'+'&kodeunit='+kodeunit+'&hari='+hari;
	tujuan='sdm_slave_5jamkerja.php';
	if(confirm("Delete data?")){
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
						 document.getElementById('container').innerHTML=con.responseText;
						loadData();	
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
	//alert("Data telah terhapus !!!");	
}

