// JavaScript Document


function saveFranco()
{
    
    idFranco=document.getElementById('idFranco').value;
    nmFranco=document.getElementById('nmFranco').value;
    almtFranco=document.getElementById('almtFranco').value;
    cntcPerson=document.getElementById('cntcPerson').value;
    hdnPhn=document.getElementById('hdnPhn').value;
    email=document.getElementById('email').value;
    kodeunit=document.getElementById('kodeunit').value;
    statFr=document.getElementById('statFr');
    if(statFr.checked==true)
       statFr=1;
    else
       statFr=0;   
    method=document.getElementById('method').value;

    if(nmFranco=='' || almtFranco==''|| cntcPerson==''|| hdnPhn=='')
    {
            alert('Field Was Empty');
            return;
    }
	
	if(emailCheck(email)==false)
	{
		return false;
	}

   
    param='idFranco='+idFranco+'&nmFranco='+nmFranco+'&almtFranco='+almtFranco+'&cntcPerson='+cntcPerson+'&hdnPhn='+hdnPhn+'&email='+email+'&kodeunit='+kodeunit+'&method='+method;
    param+='&statFr='+statFr;
    // alert(param);
    //alert(param);
    tujuan='log_slave_5masterfranco.php';
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
                            loadData();
                            cancelIsi();
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }
}

function loadData()
{
	param='method=loadData';
    // alert(param);
	tujuan='log_slave_5masterfranco';
    post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //var res = document.getElementById(idCont);
//                    res.innerHTML = con.responseText;
					  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}
function fillField(idFr)
{
	
	param='method=getData'+'&idFranco='+idFr;
	tujuan='log_slave_5masterfranco';
    post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					ar=con.responseText.split("###");
					document.getElementById('idFranco').value=ar[0];
					document.getElementById('nmFranco').value=ar[1];
					document.getElementById('almtFranco').value=ar[2];
					document.getElementById('cntcPerson').value=ar[3];
					document.getElementById('hdnPhn').value=ar[4];
                    document.getElementById('email').value=ar[5];
                    document.getElementById('kodeunit').value=ar[6];
					if(ar[7]==1)
						document.getElementById('statFr').checked=true;
					else
                       document.getElementById('statFr').checked=false;

					document.getElementById('method').value="update";
					document.getElementById('nmFranco').disabled=true;
					 // document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function cancelIsi()
{
	document.getElementById('idFranco').value='';
	document.getElementById('nmFranco').value='';
	document.getElementById('almtFranco').value='';
	document.getElementById('cntcPerson').value='';
	document.getElementById('hdnPhn').value='';
    document.getElementById('email').value='';
    document.getElementById('kodeunit').value='';
	document.getElementById('method').value="insert";
	document.getElementById('statFr').checked=false;
	document.getElementById('nmFranco').disabled=false;
}
function delData(idFr)
{
	param='method=delData'+'&idFranco='+idFr;
	tujuan='log_slave_5masterfranco';
	if(confirm("Anda yakin ingin menghapus"))
    {
		post_response_text(tujuan+'.php', param, respon);
	}
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //var res = document.getElementById(idCont);
//                    res.innerHTML = con.responseText;
					  loadData();
					  cancelIsi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}