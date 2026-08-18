function batal()
{
	document.getElementById('notransaksi').value = '';
	document.getElementById('notransaksi').disabled = true;
	document.getElementById('tgl').value = '';
	document.getElementById('tgl').disabled = false;
	document.getElementById('pabrik').value = '';
	document.getElementById('pabrik').disabled = false;
	document.getElementById('supplier').value = '';
	document.getElementById('supplier').disabled = false;
	document.getElementById('harga').value = '';
	document.getElementById('find_tgl').value = '';
	document.getElementById('find_supplier').value = '';
	document.getElementById('find_notransaksi').value = '';
	document.getElementById('countapproval').value = '0';
	document.getElementById('trapproval').innerHTML = '';
    document.getElementById('method').value='insert';
}
function batalcari()
{
	document.getElementById('find_tgl').value = '';
	document.getElementById('find_supplier').value = '';
	document.getElementById('find_notransaksi').value = '';
	loaddata();
}
function loaddata(num) 
{	
	find_tgl = document.getElementById('find_tgl').value;
	find_supplier = document.getElementById('find_supplier').value;
	find_notransaksi = document.getElementById('find_notransaksi').value;
	param='method=loaddata';
    param+='&page='+num+'&find_tgl='+find_tgl+'&find_supplier='+find_supplier+'&find_notransaksi='+find_notransaksi;
    tujuan='log_slave_hargabelitbs.php';
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

function getNotransaksi()
{
	pabrik= document.getElementById('pabrik').options[document.getElementById('pabrik').selectedIndex].value;
	tgl	= document.getElementById('tgl').value;
	param='tgl='+tgl+'&pabrik='+pabrik+'&method=getNotransaksi';
    tujuan='log_slave_hargabelitbs.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('notransaksi').value = trim(con.responseText);
					getApproval();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

function getApproval(notransaksi){
	pabrik= document.getElementById('pabrik').options[document.getElementById('pabrik').selectedIndex].value;
	param='pabrik='+pabrik+'&method=getApproval&notransaksi='+notransaksi;
    tujuan='log_slave_hargabelitbs.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					split = con.responseText.split("####");
					document.getElementById('trapproval').innerHTML = split[0];
					document.getElementById('countapproval').value = split[1];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan()
{
	param="";
    notransaksi	= document.getElementById('notransaksi').value;
    tgl	= document.getElementById('tgl').value;
    pabrik= document.getElementById('pabrik').options[document.getElementById('pabrik').selectedIndex].value;
    supplier= document.getElementById('supplier').options[document.getElementById('supplier').selectedIndex].value;
    harga = document.getElementById('harga').value;
    countapproval = document.getElementById('countapproval').value;
    method=document.getElementById('method').value;
	strUrl='';
    if(tgl==''||supplier==''||harga==''||pabrik==''||notransaksi=='')
    {
		alert('Field Was Empty');
        return false;
    }
	
	if(countapproval=='0')
	{
		alert('Please contact administrator to setup Approval');
        return false;
	}
	else
	{
		for(i=1;i<=countapproval;i++)
		{
			persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value;
			if(persetujuan=='')
			{
				alert("Please compelete Approval");
				return;
			}
			strUrl += '&persetujuan['+i+']='+persetujuan;
		}
	}
	
	param+='tgl='+tgl+'&supplier='+supplier+'&harga='+harga+'&pabrik='+pabrik+'&notransaksi='+notransaksi+'&method='+method;
	param+=strUrl;
    tujuan='log_slave_hargabelitbs.php';
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

function edit(notransaksi,pabrik,tgl,supplier,harga)
{
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('notransaksi').disabled=true;
	document.getElementById('pabrik').value=pabrik;
	document.getElementById('pabrik').disabled=true;
	document.getElementById('tgl').value=tgl;
	document.getElementById('tgl').disabled=true;
	document.getElementById('supplier').value=supplier;
	document.getElementById('supplier').disabled=true;
	document.getElementById('harga').value=harga;
	document.getElementById('method').value='update';
	getApproval(notransaksi);
}

function del(notransaksi)
{
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='log_slave_hargabelitbs.php';
    if(confirm(' Anda yakin ???'))
    {
        post_response_text(tujuan, param, respog);	
    }
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

function posting(notransaksi,numrow,countrow)
{
    param='method=posting'+'&notransaksi='+notransaksi;
    tujuan='log_slave_hargabelitbs.php';
    if(confirm('Anda yakin ingin mengajukan item dengan nomor : '+notransaksi+' ???'))
    {
        post_response_text(tujuan, param, respog);	
    }
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
					//document.getElementById('contain').innerHTML=con.responseText;	
					x = document.getElementById('tr_' + numrow);
					x.cells[parseFloat(countrow)-3].innerHTML = 'Submitted';
					x.cells[parseFloat(countrow)-2].innerHTML = '';
					x.cells[parseFloat(countrow)-1].innerHTML = '';
					x.cells[countrow].innerHTML = "<img src=images/icons/04/16/04.png class=zImgOffBtn title='Submitted'>";
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
    if(confirm('Anda yakin ingin unposting transaksi nomor : '+notransaksi+' ???'))
    {
        post_response_text(tujuan, param, respog);	
    }
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
					getPage();
				}
		}
		else {
				busy_off();
				error_catch(con.status);
		}
	  }	
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}