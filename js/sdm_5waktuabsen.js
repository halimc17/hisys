function batal()
{
	document.getElementById('unit').selectedIndex = 0;
	document.getElementById('container').innerHTML='';
	document.getElementById('unit').disabled = false;
}

function getketerangan(karyawanid){
	stdabsen=document.getElementById('stdabsen_'+karyawanid).value;
	param='method=getketerangan&karyawanid='+karyawanid+'&stdabsen='+stdabsen;
    tujuan='sdm_slave_5waktuabsen.php';
    post_response_text(tujuan, param, respog);
    
	function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					isi = con.responseText.split("####");
					document.getElementById('jammasuk_'+karyawanid).innerHTML = isi[0];
					document.getElementById('jamistirahatdari_'+karyawanid).innerHTML = isi[1];
					document.getElementById('jamistirahatsampai_'+karyawanid).innerHTML = isi[2];
					document.getElementById('jamkeluar_'+karyawanid).innerHTML = isi[3];
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

function preview(){
	unit=document.getElementById('unit').value;
	param='method=preview&unit='+unit;
    tujuan='sdm_slave_5waktuabsen.php';
	
	if(unit==''){
		alert("Unit harus dipilih.");
		document.getElementById('container').innerHTML='';
		return;
	}
	
    post_response_text(tujuan, param, respog);
    
	function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('unit').disabled = true;
					document.getElementById('container').innerHTML=con.responseText;
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