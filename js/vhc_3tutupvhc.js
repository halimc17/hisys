

function cancel(){
	document.getElementById('kodetraksi').value='';
	document.getElementById('periode').value='';
	document.getElementById('container').innerHTML='';
	setValue2('kodetraksi',null);
	setValue2('periode',null);
}

function preview(){
    kodetraksi=document.getElementById('kodetraksi').value;
    periode=document.getElementById('periode').value;
	method='preview';
    param='periode='+periode+'&kodetraksi='+kodetraksi+'&method='+method;
    tujuan='vhc_3tutupvhc_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	} 
}

function savedt(){
    kodetraksi=document.getElementById('kodetraksi').value;
    periode=document.getElementById('periode').value;
	method='savedt';
    param='periode='+periode+'&kodetraksi='+kodetraksi+'&method='+method;
    tujuan='vhc_3tutupvhc_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('done');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	} 
}
