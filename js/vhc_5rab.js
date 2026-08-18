function batal()
{
	document.getElementById('kode').value = '';
	document.getElementById('pekerjaan').selectedIndex = 0;
	document.getElementById('volume').value = '';
	document.getElementById('satuan').selectedIndex =0;
	document.getElementById('lokasi').value = '';
	
	document.getElementById('pekerjaan').disabled = false;
	document.getElementById('volume').disabled = false;
	document.getElementById('satuan').disabled = false;
	document.getElementById('lokasi').disabled = false;
	document.getElementById('status').checked = true;
    document.getElementById('method').value='insert';
}

function loaddata(num) 
{
	param='method=loaddata';
    param+='&page='+num;
    tujuan='vhc_slave_5rab.php';
	
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

function simpan()
{
    kode = document.getElementById('kode').value;
	pekerjaan = trim(document.getElementById('pekerjaan').options[document.getElementById('pekerjaan').selectedIndex].value);
    volume = document.getElementById('volume').value;
	satuan = trim(document.getElementById('satuan').options[document.getElementById('satuan').selectedIndex].value);
    lokasi = document.getElementById('lokasi').value;
	aktif = document.getElementById('status');
    
	if(aktif.checked==true)
	{
		aktif=1;
	}
    else
	{
		aktif=0;
	}
    method=document.getElementById('method').value;

    if(pekerjaan==''||volume==''||volume=='0')
    {
		alert('Field Was Empty');
        return false;
    }
	
	param='kode='+kode+'&pekerjaan='+pekerjaan+'&volume='+volume+'&satuan='+satuan+'&lokasi='+lokasi+'&status='+aktif+'&method='+method;
    tujuan='vhc_slave_5rab.php';
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
					alert("Success");
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

function edit(kode,pekerjaan,volume,satuan,lokasi,aktif)
{
	document.getElementById('kode').value=kode;
	document.getElementById('pekerjaan').value=pekerjaan;
	document.getElementById('volume').value=volume;
	document.getElementById('satuan').value=satuan;
	document.getElementById('lokasi').value=satuan;
	document.getElementById('pekerjaan').disabled=true;
	document.getElementById('volume').disabled=true;
	document.getElementById('satuan').disabled=true;
	document.getElementById('lokasi').disabled=true;
    
	if(aktif=='1')
	{
		document.getElementById('status').checked=true;
	}
    else
	{
		document.getElementById('status').checked=false;
	}
	document.getElementById('method').value='update';
}

function addDetail(kode,pekerjaan,ev)
{
	showDetail(kode,pekerjaan,ev);
	param='kode='+kode+'&method=adddetail';
	tujuan='vhc_slave_5rab.php';
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
					document.getElementById('contDetail').innerHTML=con.responseText;
					loaddatadet(0);
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

function previewDetail(kode,pekerjaan,ev)
{
	showDetail(kode,pekerjaan,ev);
	param='kode='+kode+'&method=getdetail';
	tujuan='vhc_slave_5rab.php';
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
					document.getElementById('contDetail').innerHTML=con.responseText;
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

function showDetail(kode,title,ev)
{
	width='';
	height='';
    content="<fieldset><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=koderab name=koderab value="+kode+" />";
    showDialog1(title,content,width,height,ev);

	var dialog = document.getElementById('dynamic1');
	clientWidth = document.getElementById("dynamic1").clientWidth;
	clientHeight = document.getElementById("dynamic1").clientHeight;
	pos = new Array();
	pos = getMouseP(ev);
	
	if((pos[1] + clientWidth) >= 600){
		dialog.style.top = (pos[1]-(clientWidth+10)) + 'px';
	}else{
		dialog.style.top = pos[1] + 'px';
	}
	// if((pos[0] - clientHeight) < 0){
		// dialog.style.left = (pos[0]) +'px';
	// }else{
		// dialog.style.left = (pos[0] - (clientHeight+100)) +'px';
	// }
	
	dialog.style.top = pos[1]+'px';
	// dialog.style.left = (pos[0]-400)+'px';
}







function bataldet()
{
	document.getElementById('kodedet').value = '';
	document.getElementById('pekerjaandet').value = '';
	document.getElementById('nourutdet').value = '';
	
	document.getElementById('pekerjaandet').disabled = false;
	document.getElementById('statusdet').checked = true;
    document.getElementById('methoddet').value='insertdet';
}

function simpandet()
{
    kodedet = document.getElementById('kodedet').value;
    kodedethid = document.getElementById('kodedethid').value;
    pekerjaandet = document.getElementById('pekerjaandet').value;
    nourutdet = document.getElementById('nourutdet').value;
    aktifdet = document.getElementById('statusdet');
    
	if(aktifdet.checked==true)
	{
		aktifdet=1;
	}
    else
	{
		aktifdet=0;
	}
    methoddet=document.getElementById('methoddet').value;

    if(pekerjaandet==''||nourutdet==''||nourutdet=='0')
    {
		alert('Field Was Empty');
        return false;
    }
	
	param='kodedet='+kodedet+'&kodedethid='+kodedethid+'&pekerjaandet='+pekerjaandet+'&nourutdet='+nourutdet+'&statusdet='+aktifdet+'&method='+methoddet;
    tujuan='vhc_slave_5rab.php';
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
					alert("Success");
					bataldet();
                    loaddatadet(0);
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

function loaddatadet(num) 
{
	kodedethid = document.getElementById('kodedethid').value;
	param='method=loaddatadet&kodedethid='+kodedethid;
    param+='&page='+num;
    tujuan='vhc_slave_5rab.php';
	
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
					document.getElementById('containerdet').innerHTML=con.responseText;
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

function editdet(kodedet,pekerjaandet,nourutdet,aktif)
{
	document.getElementById('kodedet').value=kodedet;
	document.getElementById('pekerjaandet').value=pekerjaandet;
	document.getElementById('nourutdet').value=nourutdet;
	
	document.getElementById('pekerjaandet').disabled=true;
    
	if(aktif=='1')
	{
		document.getElementById('statusdet').checked=true;
	}
    else
	{
		document.getElementById('statusdet').checked=false;
	}
	document.getElementById('methoddet').value='updatedet';
}

function addkeg(kodedet,deskripsi,ev)
{
	document.getElementById('divdet').style.display = 'none';
	document.getElementById('divkeg').style.display = '';
	
	param='kodedet='+kodedet+'&pekerjaandet='+deskripsi+'&method=addkeg';
	tujuan='vhc_slave_5rab.php';
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
					document.getElementById('divkeg').innerHTML=con.responseText;
					loaddatakeg(0);
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

function loaddatakeg(num) 
{
	kodekeghid = document.getElementById('kodekeghid').value;
	
	param='method=loaddatakeg&kodekeghid='+kodekeghid;
    param+='&page='+num;
    tujuan='vhc_slave_5rab.php';
	
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
					document.getElementById('containerkeg').innerHTML=con.responseText;
					loaddatamat();
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

function simpankeg()
{
    kodekeg = document.getElementById('kodekeg').value;
    kodekeghid = document.getElementById('kodekeghid').value;
    kegiatan = document.getElementById('kegiatan').value;
    volumekeg = document.getElementById('volumekeg').value;
    satuankeg = document.getElementById('satuankeg').value;
    nourutkeg = document.getElementById('nourutkeg').value;
    aktifkeg = document.getElementById('statuskeg');
    
	if(aktifkeg.checked==true)
	{
		aktifkeg=1;
	}
    else
	{
		aktifkeg=0;
	}
    methodkeg=document.getElementById('methodkeg').value;

    if(kegiatan==''||volumekeg==''||volumekeg=='0'||nourutkeg==''||nourutkeg=='0')
    {
		alert('Field Was Empty');
        return false;
    }
	
	param='kodekeg='+kodekeg+'&kodekeghid='+kodekeghid+'&kegiatan='+kegiatan+'&volumekeg='+volumekeg+'&satuankeg='+satuankeg+'&nourutkeg='+nourutkeg+'&statuskeg='+aktifkeg+'&method='+methodkeg;
    tujuan='vhc_slave_5rab.php';
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
					alert("Success");
					batalkeg();
                    loaddatakeg(0);
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

function batalkeg()
{
	document.getElementById('kodekeg').value = '';
	document.getElementById('kegiatan').value = '';
	document.getElementById('volumekeg').value = 0;
	document.getElementById('satuankeg').selectedIndex = 0;
	document.getElementById('nourutkeg').value = '';
	
	document.getElementById('kegiatan').disabled = false;
	document.getElementById('volumekeg').disabled = false;
	document.getElementById('satuankeg').disabled = false;
	document.getElementById('statuskeg').checked = true;
    document.getElementById('methodkeg').value='insertkeg';
	
	param = 'method=batalkeg';
	tujuan = 'vhc_slave_5rab.php';
	post_response_text(tujuan, param, respog);
			
	function respog()
	{
		if (con.readyState == 4)
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
					loaddatamat();
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

function editkeg(kodekeg,kegiatan,volumekeg,satuankeg,nourutkeg,aktif)
{
	document.getElementById('kodekeg').value=kodekeg;
	document.getElementById('kegiatan').value=kegiatan;
	document.getElementById('volumekeg').value=volumekeg;
	document.getElementById('satuankeg').value=satuankeg;
	document.getElementById('nourutkeg').value=nourutkeg;
	
	document.getElementById('kegiatan').disabled=true;
	document.getElementById('volumekeg').disabled=true;
	document.getElementById('satuankeg').disabled=true;
    
	if(aktif=='1')
	{
		document.getElementById('statuskeg').checked=true;
	}
    else
	{
		document.getElementById('statuskeg').checked=false;
	}
	document.getElementById('methodkeg').value='updatekeg';
	
	
	param = 'kodekeg='+kodekeg+'&method=editkeg';
	tujuan = 'vhc_slave_5rab.php';
	post_response_text(tujuan, param, respog);
			
	function respog()
	{
		if (con.readyState == 4)
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
					loaddatamat();
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

function back()
{
	document.getElementById('divdet').style.display = '';
	document.getElementById('divkeg').style.display = 'none';
	
	bataldet();
	batalkeg();
}

function addmat()
{
	kodemat = trim(document.getElementById('kodemat').value);
	namamat = trim(document.getElementById('namamat').value);
	
	if(kodemat=='')
	{
		alert("Material belum diisi.");
		return false;
	}
	
	param = 'kodemat='+kodemat+'&namamat='+namamat+'&method=addmat';
	tujuan = 'vhc_slave_5rab.php';
	post_response_text(tujuan, param, respog);
			
	function respog()
	{
		if (con.readyState == 4)
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
					// document.getElementById('containercari').innerHTML=con.responseText;
					loaddatamat();
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

function batalmat()
{
	document.getElementById('kodemat').value="";
	document.getElementById('namamat').value="";
}

function searchmat(title,ev)
{
	content= "<div style='width:100%;'>";
	content+="<fieldset>"+title+"<input type=text id=txtnamabarang class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariBarang()>Go</button> ";
	content+="<div id=containercari style='overflow:auto;max-height:317px;min-width:300px'></div></fieldset></div>";
    width='auto';
	height='auto';
	showDialog2(title,content,width,height,ev);
	
	var dialog = document.getElementById('dynamic2');
	clientWidth = document.getElementById("dynamic2").clientWidth;
	clientHeight = document.getElementById("dynamic2").clientHeight;
	pos = new Array();
	pos = getMouseP(ev);
	
	pos = new Array();
	pos = getMouseP(ev);
	
	dialog.style.top = pos[1]+'px';
	dialog.style.left = (pos[0]-clientWidth)+'px';
}

function goCariBarang()
{
	txtcari = trim(document.getElementById('txtnamabarang').value);
	
	param = 'txtcari='+txtcari+'&method=caribarang';
	tujuan = 'vhc_slave_5rab.php';
	post_response_text(tujuan, param, respog);
			
	function respog()
	{
		if (con.readyState == 4)
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
					document.getElementById('containercari').innerHTML=con.responseText;
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

function loadField(kode,nama)
{
	document.getElementById('kodemat').value=kode;
	document.getElementById('namamat').value=nama;
	closeDialog2();		
}

function loaddatamat() 
{
	param='method=loaddatamat';
    tujuan='vhc_slave_5rab.php';
	
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
					document.getElementById('listmaterial').innerHTML=con.responseText;
					batalmat();
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

function deletemat(kodemat)
{
	param='method=deletemat&kodemat='+kodemat;
    tujuan='vhc_slave_5rab.php';
	
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
					loaddatamat();
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