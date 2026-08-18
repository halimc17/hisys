// JavaScript Document
function batal()
{
	document.getElementById('method').value = 'insert';
	document.getElementById("kode").value = "";
	document.getElementById('namajenis').value = "";
	document.getElementById('sumber').value = "";
	
	document.getElementById('kode').disabled = false;
}

function loadData(){
	param='method=loaddata';
	tujuan='keu_slave_5jenisuangmuka.php';
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
				else 
				{
					document.getElementById('container').innerHTML=con.responseText;
					batal();
				}
			}
			else 
			{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function fillfield(kode,namajenis,sumber,noakun,sts){
	document.getElementById('kode').disabled=true;
	document.getElementById('kode').value=kode;
	document.getElementById('namajenis').value=namajenis;
	document.getElementById('sumber').value=sumber;
	document.getElementById('noakun').value=noakun;
	document.getElementById('method').value='edit';
	if(sts=='1'){
		document.getElementById('status').checked=true;
	}else{
		document.getElementById('status').checked=false;
	}


}

function simpan()
{	
	sts = document.getElementById('status');   
	if(sts.checked==true){
		sts=1;
	}
    else{
		sts=0;
	}

	kode=trim(document.getElementById('kode').value);
	namajenis=trim(document.getElementById('namajenis').value);
	
	noakun=document.getElementById('noakun');
	noakun=noakun.options[noakun.selectedIndex].value;
	sumber=trim(document.getElementById('sumber').value);
	method=trim(document.getElementById('method').value);
	


	// param='kode='+kode+'&namajenis='+namajenis+'&sumber='+sumber+'&tipesup='+tipesup+'&method='+method+'&sts='+sts+'&transaksirutin='+transaksirutin;
	// param+='&statJrn='+statJrn;

	param='kode='+kode+'&namajenis='+namajenis+'&noakun='+noakun+'&sumber='+sumber+'&sts='+sts+'&method='+method;
	//alert(param);
	tujuan='keu_slave_5jenisuangmuka.php';
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
					document.getElementById('container').innerHTML=con.responseText;
					batal();
				}
			}
			else 
			{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function deletefield(kode){
	param='kode='+kode+'&method=delete';
	tujuan='keu_slave_5jenisuangmuka.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}