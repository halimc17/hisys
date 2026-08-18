function getnamakud(jenis) {
	document.getElementById('listsupplier').innerHTML='';
    if(jenis=='detail' || jenis=='detail2'){		
		param = "method=getnamakud&jenis="+jenis;
		param += '&kodeunit=' + getValue('kodeunit');
		param += '&tanggalmulai=' + getValue('tanggalmulai');
		param += '&tanggalsampai=' + getValue('tanggalsampai');
		tujuan = "pmn_2rekappembeliandanabmintbs_slave.php";
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
					} else {
						document.getElementById('consupplier').style.display='';
						document.getElementById('conlistsupplier').style.display='';
						document.getElementById('supplier').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}else{
		document.getElementById('consupplier').style.display='none';
		document.getElementById('supplier').innerHTML = '';
	}
}


function cancel(){
	document.getElementById('tanggalmulai').value='';
	document.getElementById('tanggalsampai').value='';
	document.getElementById('container').innerHTML='';
	setValue2('kodeunit',null);
	setValue2('tipetbs',null);
}

function preview(tipe){
	tanggalmulai =document.getElementById('tanggalmulai').value;
	tanggalsampai=document.getElementById('tanggalsampai').value;
	kodeunit     =document.getElementById('kodeunit').value;
	tipetbs      =document.getElementById('tipetbs').value;
	jenis        =document.getElementById('jenis').value;
	petani        =document.getElementById('petani').value;
	supplier        =document.getElementById('supplier').value;
	method='preview';
    param='tanggalsampai='+tanggalsampai+'&tanggalmulai='+tanggalmulai+'&kodeunit='+kodeunit+'&tipetbs='+tipetbs+'&tipe='+tipe+'&petani='+petani;
	param += '&method=' + method;
	param += '&jenis=' + jenis;
	param += '&supplier=' + supplier;
    tujuan='pmn_2rekappembeliandanabmintbs_slave.php';
	if(tipe=='excel'){
		printnopopup(tujuan+'?'+param);
	} else if(tipe=='pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_2rekappembeliandanabmintbs_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
	} else{
		post_response_text(tujuan, param, respog);
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						if(tipe=='html'){
							document.getElementById('container').innerHTML=con.responseText;
							leftFixedTable();
						}
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}	
}

function chooseTarget(supplier){
	nsupplier=document.getElementById("supplier").options[document.getElementById('supplier').selectedIndex].text;
	
	param='method=chooseTarget&supplier='+supplier+'&nsupplier='+nsupplier;
	tujuan = 'pmn_2rekappembeliandanabmintbs_slave.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert('Informasi',con.responseText);
				}else{
					loadlistsupplier();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadlistsupplier(){
	param='method=loadlistsupplier';
	tujuan = 'pmn_2rekappembeliandanabmintbs_slave.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert('Informasi',con.responseText);
				}else{
					document.getElementById('listsupplier').innerHTML=con.responseText;
					loadoptsupplier();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadoptsupplier(){
	param='method=loadoptsupplier';
	param += '&kodeunit=' + getValue('kodeunit');
	param += '&tanggalmulai=' + getValue('tanggalmulai');
	param += '&tanggalsampai=' + getValue('tanggalsampai');
	tujuan = 'pmn_2rekappembeliandanabmintbs_slave.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert('Informasi',con.responseText);
				}else{
					document.getElementById('supplier').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletelistsupplier(supplier){
	param='method=deletelistsupplier&supplier='+supplier;
	tujuan = 'pmn_2rekappembeliandanabmintbs_slave.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert('Informasi',con.responseText);
				}else{
					loadlistsupplier();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}