// JavaScript Document

function saveData()
{
	
	kdOrg=document.getElementById('idKbn').value;
	Tahun=document.getElementById('Tahun').value;
	param='kdOrg='+kdOrg+'&Tahun='+Tahun+'&proses=getData';
	//alert(param);
	tujuan='kebun_slave_3tutuparesta.php';
	post_response_text(tujuan, param, respon);
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					//alert(con.responseText);
					document.getElementById('result').style.display='block';
					document.getElementById('list_ganti').innerHTML=con.responseText;
					document.getElementById('idKbn').disabled=true;
					document.getElementById('Tahun').disabled=true;
					document.getElementById('dtl_pem').disabled=true;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function processData()
{
	
	kdOrg=document.getElementById('idKbn').value;
	Tahun=document.getElementById('Tahun').value;
	param='kdOrg='+kdOrg+'&Tahun='+Tahun+'&proses=processData';
	//alert(param);
	tujuan='kebun_slave_3tutuparesta.php';
	post_response_text(tujuan, param, respon);
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					//alert(con.responseText);
                                        alert('Done...');
                                        cancelSave();
                } 
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function cancelSave()
{
	document.getElementById('list_ganti').innerHTML='';
	document.getElementById('idKbn').disabled=false;
	document.getElementById('Tahun').disabled=false;
	document.getElementById('dtl_pem').disabled=false;
	document.getElementById('idKbn').selectedIndex=0;
	document.getElementById('Tahun').selectedIndex=0;
	document.getElementById('result').style.display='none';
	
}
function viewData(param,title,content,ev)
{
	width='600';
	height='400';
	showDialog1(title,content,width,height,ev);
	ar=param.split("###");
	dataDetail(ar[0],ar[1]);
}

function dataDetail(kodeorg,tahun)
{
	param='kdOrg='+kodeorg+'&Tahun='+tahun+'&proses=ShowData';
	tujuan='kebun_slave_3tutuparesta.php';
	post_response_text(tujuan, param, respon);
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function getPrd(){
	kdOrg= document.getElementById('idKbn').options[document.getElementById('idKbn').selectedIndex].value;
	param='kdOrg='+kdOrg+'&proses=getPrd';
	tujuan='kebun_slave_3tutuparesta.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('Tahun').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}