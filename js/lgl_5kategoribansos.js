function loaddata(){
	param='method=loaddata';
    tujuan='lgl_slave_5kategoribansos.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('container').innerHTML=con.responseText;
					cancelKegiatan();
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function getKode(){
	noakun= document.getElementById('noakun').options[document.getElementById('noakun').selectedIndex].value;
	param='noakun='+noakun+'&method=getKode';
	tujuan='lgl_slave_5kategoribansos.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('kodekegiatan').value=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpanKegiatan(){
	kodekegiatan=document.getElementById('kodekegiatan').value;
	namakegiatan=document.getElementById('namakegiatan').value;
	// satuan=satuan.options[satuan.selectedIndex].value;
	satuan=document.getElementById('satuan').options[document.getElementById('satuan').selectedIndex].value;
	noakun       =document.getElementById('noakun');
	noakun=noakun.options[noakun.selectedIndex].value;
	tipe       =document.getElementById('tipe');
	tipe=tipe.options[tipe.selectedIndex].value;
	met=document.getElementById('method').value;
	if(namakegiatan=='' ||noakun==''||tipe==''){
		alert('Nama Kegiatan, Tipe dan Akun wajib di isi.');
		document.getElementById('kodekegiatan').focus();
	}else{
		kodekegiatan=trim(kodekegiatan);
		namakegiatan=trim(namakegiatan);
		param='kodekegiatan='+kodekegiatan+'&namakegiatan='+namakegiatan+'&satuan='+satuan+'&method='+met+'&noakun='+noakun+'&tipe='+tipe;
		tujuan='lgl_slave_5kategoribansos.php';
		post_response_text(tujuan, param, respog);		
	}

	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML=con.responseText;
					loaddata();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function fillField(kode,nama,satuan,noakun,tipe){
	document.getElementById('kodekegiatan').value=kode;
	document.getElementById('kodekegiatan').disabled=true;
	document.getElementById('noakun').disabled=true;
	document.getElementById('tipe').disabled=true;
	document.getElementById('noakun').disabled=true;
	document.getElementById('namakegiatan').value=nama;
	x=document.getElementById('noakun');
	for(y=0;y<x.length;y++){
		   if(x.options[y].value==noakun)
			   x.options[y].selected=true;
	   } 
	
	y=document.getElementById('satuan');
	for(z=0;z<y.length;z++){
		   if(y.options[z].value==satuan)
			  y.options[z].selected=true;
	   } 
	xy=document.getElementById('tipe');
	for(y=0;y<xy.length;y++){
		  if(xy.options[y].value==tipe)
			xy.options[y].selected=true;
	   } 
	document.getElementById('method').value='update';
}

function cancelKegiatan(){
	document.getElementById('kodekegiatan').disabled=true;
	document.getElementById('tipe').disabled=false;
	document.getElementById('noakun').disabled=false;
	document.getElementById('kodekegiatan').value='';
	document.getElementById('namakegiatan').value='';
	document.getElementById('satuan').selectedIndex=0;
	document.getElementById('noakun').selectedIndex=0;
	document.getElementById('method').value='insert';		
}
