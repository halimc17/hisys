function delet(kode,file) {
	param='method=delete';
	param+='&kode='+kode+'&file='+file;
    tujuan='pmn_5jenisspak_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function simpan() {
	
	param="";
    kode	= document.getElementById('kode').value;
    nama	= document.getElementById('nama').value;
    akundebet	= document.getElementById('akundebet').value;
    akunkredit	= document.getElementById('akunkredit').value;
    keterangan	= document.getElementById('keterangan').value;
   
    file	= document.getElementById('file').value;
    filenonpenjualan	= document.getElementById('filenonpenjualan').value;
	
		
	
	if (document.getElementById('penjualan').checked == true) {
		penjualan = 1;
	} else {
		penjualan = 0;
	}
	
	if (document.getElementById('nonpenjualan').checked == true) {
		nonpenjualan = 1;
	} else {
		nonpenjualan = 0;
	}
	
    method=document.getElementById('method').value;
    if(kode==''||nama==''){
		alert('Field Was Empty');
        return false;
    }
	param+='kode='+kode+'&nama='+nama+'&penjualan='+penjualan+'&nonpenjualan='+nonpenjualan+'&method='+method;
	param+='&akundebet='+akundebet+'&akunkredit='+akunkredit+'&keterangan='+keterangan;
	param+='&file='+file+'&filenonpenjualan='+filenonpenjualan;
    tujuan='pmn_5jenisspak_slave.php';
    post_response_text(tujuan, param, respog);      
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else  {
					batal();
                    loaddata(0);
				}
			} else  {
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}



function batal()
{
	document.getElementById('kode').value = '';
	document.getElementById('kode').disabled = false;
	document.getElementById('nama').value = '';
	document.getElementById('nonpenjualan').checked = false;	
	document.getElementById('penjualan').checked = false;
	document.getElementById('file').value = '';
	document.getElementById('filenonpenjualan').value = '';
	document.getElementById('akundebet').value = '';
	document.getElementById('akunkredit').value = '';
	document.getElementById('keterangan').value = '';
    document.getElementById('method').value='insert';
}

function loaddata(num) {	
	param='method=loaddata';
    tujuan='pmn_5jenisspak_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
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

function edit(kode,nama,akundebet,akunkredit,keterangan,file,filenonpenjualan,penjualan,nonpenjualan) {
	
	if (penjualan==1) {
		document.getElementById('penjualan').checked = true;
	} else {
		document.getElementById('penjualan').checked = false;
	}
	
	if (nonpenjualan==1) {
		document.getElementById('nonpenjualan').checked = true;
	} else {
		document.getElementById('nonpenjualan').checked = false;
	}
	
	
	
	document.getElementById('kode').disabled = true;
	document.getElementById('kode').value=kode;
    document.getElementById('nama').value=nama;
    document.getElementById('file').value=file;
    document.getElementById('filenonpenjualan').value=filenonpenjualan;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('akunkredit').value=akunkredit;
    document.getElementById('akundebet').value=akundebet;
	document.getElementById('method').value='update';
}
