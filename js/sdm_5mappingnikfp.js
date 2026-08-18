function addserverfp(ev){
	title = "Upload datakaryawan dari server Fingerprint";
	
	param = 'method=addserverfp';
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contDetail').innerHTML = con.responseText;
					alertify.popup().destroy();
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('900px','500px');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					loaddtdata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview(ev,tipe){
	unit=document.getElementById('unit').value;
	divisi=document.getElementById('divisi').value;
	
	param='method=preview&tipe='+tipe+'&unit='+unit+'&divisi='+divisi;
	tujuan='sdm_slave_5mappingnikfp.php';
	if(tipe=='html'){
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState == 4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert(con.responseText);
					}else{
						document.getElementById('container').innerHTML=con.responseText;
						// $(document).ready(function() {
							// $('.select2').select2({
								// dropdownAutoWidth:true
							// });
						// });
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}else if(tipe=='pdf'){
		title='PDF';
		tujuan=tujuan+"?"+param;  
		width = 1024;
		height = 400;
		content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
	}else if(tipe=='excel'){
		tujuan=tujuan+"?"+param;  
		printnopopup(tujuan);
	}
}

function clearcontainer(){
	document.getElementById('container').innerHTML="";
	
	unit=document.getElementById('unit').value;
	
	param='method=getdivisi&unit='+unit;
	tujuan='sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('divisi').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getpin(nourut){
	fp=document.getElementById('fp_'+nourut).value;
	totalkar=document.getElementById('totalkar').value;
	
	 for(i=1;i<=totalkar;i++){
		if(i!=nourut){
			document.getElementById('divfp_'+i).style.display='none';
			document.getElementById('fp_'+i).selectedIndex=0;
		}
	}
	
	param='method=getpin&fp='+fp;
	tujuan='sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					if(con.responseText==''){
						document.getElementById('divfp_'+nourut).style.display='none';
					}else{
						document.getElementById('divfp_'+nourut).style.display='block';
						document.getElementById('fppin_'+nourut).innerHTML=con.responseText;						
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function insattpeg(nourut){
	fp=document.getElementById('fp_'+nourut).value;
	fppin=document.getElementById('fppin_'+nourut).value;
	nik=trim(document.getElementById('nik_'+nourut).innerHTML);
	karyawanid=document.getElementById('karyawanid_'+nourut).value;
	
	param='method=insattpeg&fp='+fp+'&fppin='+fppin+'&nik='+nik+'&karyawanid='+karyawanid;
	tujuan='sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadattpeg(nourut);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefp(nourut,sn,pin){
	karyawanid=document.getElementById('karyawanid_'+nourut).value;
	
	param='method=deletefp&fp='+sn+'&fppin='+pin+'&karyawanid='+karyawanid;
	tujuan='sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadattpeg(nourut);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadattpeg(nourut){
	karyawanid=document.getElementById('karyawanid_'+nourut).value;
	
	param='method=loadattpeg&karyawanid='+karyawanid+'&nourut='+nourut;
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listfp_'+nourut).innerHTML=con.responseText;
					getpin(nourut);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function changesnfp(ev){
	title = "Ganti SN Fingerprint";
	param = 'method=changesnfp';
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('500px','250px');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpanchangesn(ev){
	snlama = document.getElementById('snlama').value;
	snbaru = document.getElementById('snbaru').value;
	
	param='method=simpanchangesn&snlama='+snlama+'&snbaru='+snbaru;
	
	tujuan = 'sdm_slave_5mappingnikfp.php';
	if(confirm("Anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.alert("Data telah diupdate.");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function adddtfp(ev){
	title = "Tambah Karyawan Finger";
	// width = '';
	// height = '';
	// content = "<fieldset><legend>" + title + "</legend><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	// showDialogx(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamicx').style.top = pos[1] + 'px';
	// document.getElementById('dynamicx').style.left = (pos[0] - 100) + 'px';
	
	param = 'method=adddtfp';
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contDetail').innerHTML = con.responseText;
					alertify.popup().destroy();
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('900px','500px');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					loaddtdata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedt(no){
	mesin       =document.getElementById('mesin'+no).innerHTML;
	pin         =document.getElementById('pin'+no).innerHTML;
	karyawan    =document.getElementById('karyawan'+no).value;
	dtorganisasi=document.getElementById('dtorganisasi').value;
	
	param='method=savedt&mesin='+mesin+'&pin='+pin+'&karyawan='+karyawan+'&dtorganisasi='+dtorganisasi;
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					data = con.responseText.split("####");
					document.getElementById('contkaryawan'+no).colSpan='2';
					document.getElementById('contkaryawan'+no).innerHTML=data[0];
					document.getElementById('contsimpan'+no).style.display="none";
					document.getElementById('edit'+no).innerHTML="";
					document.getElementById('delete'+no).innerHTML="";
					
					
					kary = document.getElementsByName('karyawan[]');
					for(i=0;i<=kary.length;i++){						
						kary[i].innerHTML=data[1];
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddtdata(){
	dtmesin=document.getElementById('dtmesin').value;
	dtorganisasi=document.getElementById('dtorganisasi').value;
	
	param='method=loaddtdata&dtmesin='+dtmesin+'&dtorganisasi='+dtorganisasi;
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('dtcotainer').innerHTML=con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function bataldt(){
	document.getElementById('dtmesin').selectedIndex=0;
	document.getElementById('dtmesin').disabled=false;
	document.getElementById('dtpin').value='';
	document.getElementById('dtpin').disabled=false;
	document.getElementById('dtnamakaryawan').value='';
	document.getElementById('dtmethod').value='insertdt';
	document.getElementById('dtcotainer').innerHTML='';
}

function dtfillfield(dtmesin,dtpin,dtnamakaryawan){
	document.getElementById('dtmesin').value=dtmesin;
	document.getElementById('dtmesin').disabled=true;
	document.getElementById('dtpin').value=dtpin;
	document.getElementById('dtpin').disabled=true;
	document.getElementById('dtnamakaryawan').value=dtnamakaryawan;
	document.getElementById('dtmethod').value='updatedt';
	showontop();
}

function dtdelete(dtmesin,dtpin){
	param='method=dtdelete&dtmesin='+dtmesin+'&dtpin='+dtpin;
	tujuan = 'sdm_slave_5mappingnikfp.php';
	
	if (confirm('Anda yakin hapus data di mesin '+dtmesin+' dengan pin '+dtpin+'??')){
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaddtdata();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpandt(){
	dtmesin=document.getElementById('dtmesin').value;
	dtpin=document.getElementById('dtpin').value;
	dtnamakaryawan=document.getElementById('dtnamakaryawan').value;
	dtmethod=document.getElementById('dtmethod').value;
	
	param='method=simpandt&dttipe='+dtmethod+'&dtmesin='+dtmesin+'&dtpin='+dtpin+'&dtnamakaryawan='+dtnamakaryawan;
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Berhasil disimpan");
					document.getElementById('dtmesin').disabled=false;
					document.getElementById('dtpin').value='';
					document.getElementById('dtpin').disabled=false;
					document.getElementById('dtnamakaryawan').value='';
					document.getElementById('dtmethod').value='insertdt';
					loaddtdata();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ambilkary(){
	dtmesin =document.getElementById('dtmesin').value;
	dtserver=document.getElementById('serverfpdt').value;
	
	
	param='method=ambilkary&dtmesin='+dtmesin+'&dtserver='+dtserver;
	tujuan = 'sdm_slave_5mappingnikfp.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('dtcotainerx').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}