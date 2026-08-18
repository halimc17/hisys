function simpan(){
    method=document.getElementById('method').value;
    pabrik=document.getElementById('pabrik').value;
	tangki=document.getElementById('tangki').value;
    nilai=document.getElementById('nilai').value;   
	nilaiangka=document.getElementById('nilaiangka').value;	
    if(pabrik=='' || tangki=='' || nilai=='' || nilai=='0'){
        alert('Please complete the form');return;
    }
    param='pabrik='+pabrik+'&tangki='+tangki+'&nilai='+nilai+'&nilaiangka='+nilaiangka;
    param+='&method='+method;
    tujuan='pabrik_slave_5faktorkoreksi.php';
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
    document.getElementById('pabrik').value='';
    document.getElementById('tangki').value='';
    document.getElementById('pabrik').disabled=false;
	document.getElementById('tangki').disabled=false;
    document.getElementById('nilai').value='0';
    document.getElementById('nilaiangka').value='0';
}

function loadData(num) {
	param='method=loadData';
	param+='&page='+num;
	tujuan='pabrik_slave_5faktorkoreksi.php';
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

function fillField(pabrik,tangki,nilai,nilaiangka){
	document.getElementById('pabrik').value=pabrik;
	document.getElementById('pabrik').disabled=true;
    document.getElementById('tangki').value=tangki;
	document.getElementById('tangki').disabled=true;
    document.getElementById('nilai').value=nilai;
	document.getElementById('nilaiangka').value=nilaiangka;
    document.getElementById('method').value='update';	
}

function del(pabrik,tangki){
    param='method=delete'+'&pabrik='+pabrik+'&tangki='+tangki;
    tujuan='pabrik_slave_5faktorkoreksi.php';
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


function gettangki()
{

       pabrik=document.getElementById('pabrik').value;
       param='pabrik='+pabrik;
      
       tujuan='pabrik_slave_5faktorkoreksi.php';
        post_response_text(tujuan+'?method=gettangki', param, respog);
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

                        document.getElementById('tangki').innerHTML=con.responseText;
                        document.getElementById('tangki').disabled=false;
                       
                       
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
          } 
     }  
}