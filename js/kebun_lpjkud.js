function cancel()
{
    document.getElementById('periode').value='';
    document.getElementById('periode').disabled=false;
    document.getElementById('namakud').value='';
    document.getElementById('namakud').disabled=false;
    document.getElementById('upah').value='0';
    document.getElementById('material').value='0';
    document.getElementById('transport').value='0';
    document.getElementById('lain').value='0';
    document.getElementById('method').value='insert';
}

function simpan()
{
    periode=document.getElementById('periode').value;
    namakud=document.getElementById('namakud').value;
    upah=document.getElementById('upah').value;
    material=document.getElementById('material').value;
    transport=document.getElementById('transport').value;
    lain=document.getElementById('lain').value;
    method=document.getElementById('method').value;

    if(periode=='' || namakud=='' || upah=='' || material=='' || transport=='' || lain=='')
    {
		alert('Semua field harus diisi.');
		return;
    }

    param='periode='+periode+'&namakud='+namakud+'&upah='+upah+'&material='+material+'&transport='+transport+'&lain='+lain+'&method='+method;
    tujuan='kebun_slave_lpjkud.php';
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
					cancel();
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	 }
}

function loadData () 
{
	param='method=loadData';
	tujuan='kebun_slave_lpjkud.php';
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
					   // alert(con.responseText);
						document.getElementById('container').innerHTML=con.responseText;
						
					}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'kebun_slave_lpjkud.php';
    post_response_text(tujuan, param, respog);
	
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}
				else {
						//displayList();

						document.getElementById('container').innerHTML=con.responseText;
						//loadData();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
    }	
}

function del(periode,namakud)
{
	param='method=delete'+'&periode='+periode+'&namakud='+namakud;
	tujuan='kebun_slave_lpjkud.php';
	
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
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
					cancel();
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}

}

function edit(periode,namakud,upah,material,transport,lain)
{
    document.getElementById('periode').value=periode;
    document.getElementById('periode').disabled=true;
    document.getElementById('namakud').value=namakud;
    document.getElementById('namakud').disabled=true;
    document.getElementById('upah').value=upah;
    document.getElementById('material').value=material;
    document.getElementById('transport').value=transport;
    document.getElementById('lain').value=lain;
    document.getElementById('method').value='update';
}








