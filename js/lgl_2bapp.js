function preview(tipe){
    pt	=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    supp=document.getElementById('kontraktor').value;
    notr=document.getElementById('notransaksi').value;
    tgl1=document.getElementById('tgl1').value;
    tgl2=document.getElementById('tgl2').value;
   
	if(pt==''){
		alertify.alert('Informasi','Perusahaan belum dipilih');return;
	}
	if(tgl1=='' || tgl2==''){
		alertify.alert('Informasi','Tanggal harus dipilih');return;
	}
	
	param='method=preview'+'&unit='+unit+'&tgl1='+tgl1+'&tgl2='+tgl2+'&pt='+pt+'&kontraktor='+supp+'&notransaksi='+notr.trim()+'&tipe='+tipe;
	tujuan='lgl_slave_2bapp.php';
	if(tipe == 'excel'){
		printnopopup(tujuan+'?'+param);
		return
	}
	if(tipe == 'pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
		return
	}
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(con.responseText == 'kosong'){
						alertify.alert('Informasi','Data Kosong');
						document.getElementById('printContainer').innerHTML='';
					}else{
						document.getElementById('printContainer').innerHTML=con.responseText;
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

function getunit(){
    pt		=document.getElementById('pt').value;
	param	='method=getunit'+'&pt='+pt;
	tujuan	='lgl_slave_2bapp.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', + con.responseText);
				} else {	
					document.getElementById('unit').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function batal(){
    setValue2('pt',null);
    setValue2('unit','%%');
    setValue2('kontraktor','%%');
    setValue2('kontraktor','');
    setValue2('tgl1','');	
    setValue2('tgl2','');
    document.getElementById('printContainer').innerHTML='';	
}


