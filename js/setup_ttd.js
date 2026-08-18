function savefile(){
	var fileup = document.getElementById('fileupload').files[0];
	var formdata = new FormData();
	formdata.append("fileup", fileup);
	formdata.append("kar",  getValue('kar'));
	formdata.append("kdpo",  getValue('kdpo'));
	formdata.append("fileupload", getValue('fileupload'));
	var con = createXMLHttpRequest();
	con.open("POST", "setup_slave_ttd.php?method=savefile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					
                    //=== Success Response
					//alert('Uploaded');
					alert(con.responseText);
					document.getElementById('fileupload').value = '';
					loaddata();
					// valSplit = con.responseText.split("####");
                    // document.getElementById('container').innerHTML = valSplit[0];
                    //document.getElementById('container').innerHTML = con.responseText;
                    // document.getElementById('id').value = valSplit[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loaddata () {
	param='method=loaddata';
	tujuan='setup_slave_ttd.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		  if(con.readyState==4){
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




function del (kar) {
	param='method=del'+'&kar='+kar;
	tujuan='setup_slave_ttd.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		  if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
					}
					else {
						loaddata();
					}
				}
				else {
						busy_off();
						error_catch(con.status);
				}
		  }	
	 }  
}







