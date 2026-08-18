// JavaScript Document
function saveFranco(fileTarget,passParam) {
	statFr=document.getElementById('statFr');
    var passP = passParam.split('##');
    var param = "";
	if(statFr.checked==true)
	{
		statFr.value=1;
	}
	else
	{
		statFr.value=0;
	}
	param='statFr='+statFr.value+'&';
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	//alert(param);
  //alert(param);
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
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(fileTarget+'.php', param, respon);

}
function loadData()
{
	param='method=loadData';
	tujuan='pmn_slave_5franco';
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
	tujuan='pmn_slave_5franco';
    post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    
                    //alert(con.responseText);
                    
					ar=con.responseText.split("###");
					document.getElementById('idFranco').value=ar[0];
					document.getElementById('nmFranco').value=ar[1];
					document.getElementById('almtFranco').value=ar[2];
					document.getElementById('jual').value=ar[3];
					if(ar[4]==1)
					{
						document.getElementById('statFr').checked=true;
					}
					document.getElementById('aslbrg').value=ar[5];
					document.getElementById('dsrtim').value=ar[6];
					document.getElementById('method').value="update";
					//document.getElementById('nmFranco').disabled=true;
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
	document.getElementById('aslbrg').value='';
	document.getElementById('dsrtim').value='';
	document.getElementById('idFranco').value='';
	document.getElementById('nmFranco').value='';
	document.getElementById('almtFranco').value='';
	document.getElementById('jual').value='';
	document.getElementById('method').value="insert";
	document.getElementById('statFr').checked=false;
	document.getElementById('nmFranco').disabled=false;
}
function delData(idFr)
{
	param='method=delData'+'&idFranco='+idFr;
	tujuan='pmn_slave_5franco';
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