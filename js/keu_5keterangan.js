function batal()
{
	document.getElementById('notransaksi').value = '';
	document.getElementById('notransaksi').disabled = true;
	document.getElementById('aruskas').value = '';
	document.getElementById('aruskas').disabled = false;
	document.getElementById('keterangan').value = '';
	document.getElementById('keterangan').disabled = false;
	document.getElementById('find_aruskas').value = '';
	document.getElementById('find_keterangan').value = '';
	document.getElementById('aktif').checked = true;
    document.getElementById('method').value='insert';
}
function batalcari()
{
	document.getElementById('find_aruskas').value = '';
	document.getElementById('find_keterangan').value = '';
	loaddata();
}
function loaddata(num) 
{	
	find_aruskas = document.getElementById('find_aruskas').value;
	find_keterangan = document.getElementById('find_keterangan').value;
	param='method=loaddata';
    param+='&page='+num+'&find_aruskas='+find_aruskas+'&find_keterangan='+find_keterangan;
    tujuan='keu_slave_5keterangan.php';
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
					alertify.alert("Informasi",con.responseText);
				}
				else 
				{
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

function simpan()
{
    aruskas= document.getElementById('aruskas').options[document.getElementById('aruskas').selectedIndex].value;
    keterangan = document.getElementById('keterangan').value;
    notransaksi = document.getElementById('notransaksi').value;
	aktif = document.getElementById('aktif');   
	if(aktif.checked==true){
		aktif=1;
	}
    else{
		aktif=0;
	}
    method=document.getElementById('method').value;
    if(aruskas==''||keterangan==''){
		alertify.alert("Informasi",'Field Was Empty');
        return false;
    }
	
	param='notransaksi='+notransaksi+'&aruskas='+aruskas+'&keterangan='+keterangan+'&aktif='+aktif+'&method='+method;
    tujuan='keu_slave_5keterangan.php';
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
					alertify.alert("Informasi",con.responseText);
				}
				else 
				{
					batal();
                    loaddata(0);
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

function edit(id_ket,noaruskas,keterangan,aktif)
{
	document.getElementById('notransaksi').value=id_ket;
	document.getElementById('notransaksi').disabled=true;
	document.getElementById('aruskas').value=noaruskas;
	document.getElementById('aruskas').disabled=true;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('keterangan').disabled=true;
	if(aktif=='1')
	{
		document.getElementById('aktif').checked=true;
	}
    else
	{
		document.getElementById('aktif').checked=false;
	}
	document.getElementById('method').value='update';
}

function del(notransaksi)
{
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='log_slave_hargabelitbs.php';
    // if(confirm(' Anda yakin ???'))
    // {
    //     post_response_text(tujuan, param, respog);	
    // }
	alertify.confirm("Infomation","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog()
    {
	  if(con.readyState==4)
	  {
		if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
			}
			else 
			{
				document.getElementById('container').innerHTML=con.responseText;
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

function posting(notransaksi,numrow)
{
    param='method=posting'+'&notransaksi='+notransaksi;
    tujuan='log_slave_hargabelitbs.php';
    // if(confirm('Anda yakin ingin memposting transaksi nomor : '+notransaksi+' ???'))
    // {
    //     post_response_text(tujuan, param, respog);	
    // }
	alertify.confirm("Infomation",'Anda yakin ingin memposting transaksi nomor : '+notransaksi+' ???',
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog()
    {
	  if(con.readyState==4)
	  {
		if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi",con.responseText);
				}
				else 
				{
					//document.getElementById('contain').innerHTML=con.responseText;	
					x = document.getElementById('tr_' + numrow);
					x.cells[8].innerHTML = '';
					x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
				}
		}
		else {
				busy_off();
				error_catch(con.status);
		}
	  }	
    }
}

function unposting(notransaksi,pabrik,tgl,numrow)
{
    param='method=unposting'+'&notransaksi='+notransaksi+'&pabrik='+pabrik+'&tgl='+tgl;
    tujuan='log_slave_hargabelitbs.php';
    // if(confirm('Anda yakin ingin unposting transaksi nomor : '+notransaksi+' ???'))
    // {
    //     post_response_text(tujuan, param, respog);	
    // }
	alertify.confirm("Infomation",'Anda yakin ingin unposting transaksi nomor : '+notransaksi+' ???',
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog()
    {
	  if(con.readyState==4)
	  {
		if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi",con.responseText);
				}
				else 
				{
					//document.getElementById('contain').innerHTML=con.responseText;	
					x = document.getElementById('tr_' + numrow);
					x.cells[8].innerHTML = '';
					x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
				}
		}
		else {
				busy_off();
				error_catch(con.status);
		}
	  }	
    }
}

