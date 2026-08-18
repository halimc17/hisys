function simpan(){
    method=document.getElementById('method').value;
    divisi=document.getElementById('divisi').value;
    tahuntanam=document.getElementById('tahuntanam').innerHTML;
	blok=document.getElementById('blok').value;
	seksi=document.getElementById('seksi').value;	
    if(divisi=='' || blok=='' || seksi==''){
        alert('Please complete the form');return;
    }
    param='divisi='+divisi+'&blok='+blok+'&seksi='+seksi+'&tahuntanam='+tahuntanam;
    param+='&method='+method;
	
    tujuan='kebun_slave_5seksipanen.php';
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
    document.getElementById('divisi').value='';
    document.getElementById('blok').value='';
    document.getElementById('tahuntanam').innerHTML='';
    document.getElementById('divisi').disabled=false;
	document.getElementById('blok').disabled=false;
    document.getElementById('seksi').value='';
}

function loadData(num) {
	param='method=loadData';
	param+='&page='+num;
	tujuan='kebun_slave_5seksipanen.php';
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

// function fillField(kodeorg,divisi,blok,seksi){
	// document.getElementById('kodeorg').value=kodeorg;
	// document.getElementById('kodeorg').disabled=false;
	// document.getElementById('divisi').value=divisi;
	// document.getElementById('divisi').disabled=false;
    // document.getElementById('blok').value=blok;
	// document.getElementById('blok').disabled=false;
	// document.getElementById('seksi').value=seksi;
    // document.getElementById('method').value='update';	
// }

function del(blok,seksi){
    param='method=delete'+'&blok='+blok+'&seksi='+seksi;
    tujuan='kebun_slave_5seksipanen.php';
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

function getdivisi()
{ 
	kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='kodeorg='+kodeorg+'&method=getdivisi';	
	tujuan='kebun_slave_5seksipanen.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					
						//alert("masuk");
						document.getElementById('divisi').innerHTML=con.responseText;
					
					
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}

function getblok()
{ 

	divisi= document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
	param='divisi='+divisi+'&method=getblok';	
	
	tujuan='kebun_slave_5seksipanen.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
						// alert("masuk");
						document.getElementById('blok').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}

function gettahuntanam()
{ 

	blok= document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	param='blok='+blok+'&method=gettahuntanam';	
	
	tujuan='kebun_slave_5seksipanen.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
						// alert("masuk");
						document.getElementById('tahuntanam').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}