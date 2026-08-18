function batal()
{
	document.getElementById('id').value = '';
	document.getElementById('id').disabled = false;
	document.getElementById('kriteria').value = '';
	document.getElementById('status').checked = true;
	document.getElementById('method').value='insert';
}

function loaddata(num) 
{
	param='method=loaddata';
    param+='&page='+num;
    tujuan='umm_slave_5mapingmodul.php';
	
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
    id = document.getElementById('id').value;
    kriteria = document.getElementById('kriteria').value;
	aktif = document.getElementById('status');
    if(aktif.checked==true){
		aktif=1;
	}else{
		aktif=0;
	}
    method=document.getElementById('method').value;

    if(kriteria==''){
		alert('Field Was Empty');
        return false;
    }
	
	param='id='+id+'&kriteria='+kriteria+'&status='+aktif+'&method='+method;
    tujuan='umm_slave_5mapingmodul.php';
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

function del(id,kriteria){
	param='id='+id+'&method=delete';
    tujuan='umm_slave_5mapingmodul.php';
	if(confirm('Are you sure delete this item '+kriteria+'?')) {
        post_response_text(tujuan, param, respog); 
    }
    
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

function edit(id,kriteria,aktif)
{
	document.getElementById('id').value=id;
	document.getElementById('id').disabled=true;
	document.getElementById('kriteria').value=kriteria;
	if(aktif=='1'){
		document.getElementById('status').checked=true;
	}else{
		document.getElementById('status').checked=false;
	}
	document.getElementById('method').value='update';
}

function addDetail(id,title,ev)
{
	showDetail(id,title,ev);
	param='id='+id+'&method=adddetail';
	tujuan='umm_slave_5mapingmodul.php';
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
	param='id='+kode+'&method=getdetail';
	tujuan='umm_slave_5mapingmodul.php';
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

function showDetail(id,title,ev)
{
	width='';
	height='';
    content="<fieldset><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset><input type=hidden id=idht name=idht value="+id+" />";
    showDialog1("Detail "+title,content,width,height,ev);

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
	document.getElementById('iddt').value = '';
	document.getElementById('subkriteria').value = '';
	document.getElementById('kelompok').value = '';
	document.getElementById('kode').value = '';
	document.getElementById('statusdet').checked = true;
	document.getElementById('methoddet').value='insertdet';
}

function bataldet2()
{
	document.getElementById('iddt').value = '';
	document.getElementById('filenamex').value = '';
	document.getElementById('statusdet').checked = true;
	document.getElementById('methoddet').value='insertdet2';
}

function simpandet()
{
    iddt = document.getElementById('iddt').value;
    idht = document.getElementById('idht').value;
    subkriteria = document.getElementById('subkriteria').value;
    kode = document.getElementById('kode').value;
    kelompok = document.getElementById('kelompok').value;
    aktifdet = document.getElementById('statusdet');
	if(aktifdet.checked==true){
		aktifdet=1;
	}else{
		aktifdet=0;
	}
	
    methoddet=document.getElementById('methoddet').value;

    if(subkriteria=='')
    {
		alert('Field Was Empty');
        return false;
    }
	
	param='iddt='+iddt+'&id='+idht+'&subkriteria='+subkriteria+'&kode='+kode+'&kelompok='+kelompok+'&statusdet='+aktifdet+'&method='+methoddet;
    tujuan='umm_slave_5mapingmodul.php';
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

function simpandet2()
{
    iddt = document.getElementById('iddt').value;
    idht = document.getElementById('idht').value;
    kodebarang = document.getElementById('kodebarang').value;
    filenamex = document.getElementById('filenamex').value;
    aktifdet = document.getElementById('statusdet');
	if(aktifdet.checked==true){
		aktifdet=1;
	}else{
		aktifdet=0;
	}
	
    methoddet=document.getElementById('methoddet').value;

    //alert(filenamex);
	
	param='iddt='+iddt+'&id='+idht+'&filenamex='+filenamex+'&kodebarang='+kodebarang+'&statusdet='+aktifdet+'&method='+methoddet;
    tujuan='umm_slave_5mapingmodul.php';
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
					bataldet2();
                    loaddatadet2(0);
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
function addDetail2(id,title,ev)
{
	showDetail(id,title,ev);
	param='id='+id+'&method=adddetail2';
	tujuan='umm_slave_5mapingmodul.php';
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
					loaddatadet2(0);
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

function loaddatadet2(num) 
{
	idht = document.getElementById('idht').value;
	param='method=loaddatadet2&id='+idht;
    param+='&page='+num;
    tujuan='umm_slave_5mapingmodul.php';
	
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

function loaddatadet(num) 
{
	idht = document.getElementById('idht').value;
	param='method=loaddatadet&id='+idht;
    param+='&page='+num;
    tujuan='umm_slave_5mapingmodul.php';
	
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

function editdet(iddt,subkriteria,kode,kelompok,aktif)
{
	document.getElementById('iddt').value=iddt;
	document.getElementById('subkriteria').value=subkriteria;
	document.getElementById('kelompok').value=kelompok;
	document.getElementById('kode').value=kode;
	if(aktif=='1'){
		document.getElementById('statusdet').checked=true;
	}else{
		document.getElementById('statusdet').checked=false;
	}
	document.getElementById('methoddet').value='updatedet';
}

function editdet2(iddt,filenamex,kodebarang,aktif)
{
	document.getElementById('iddt').value=iddt;
	document.getElementById('filenamex').value=filenamex;
	document.getElementById('kodebarang').value=kodebarang;
	if(aktif=='1'){
		document.getElementById('statusdet').checked=true;
	}else{
		document.getElementById('statusdet').checked=false;
	}
	document.getElementById('methoddet').value='updatedet2';
}

function addkeg(kodedet,deskripsi,ev)
{
	document.getElementById('divdet').style.display = 'none';
	document.getElementById('divkeg').style.display = '';
	
	param='kodedet='+kodedet+'&pekerjaandet='+deskripsi+'&method=addkeg';
	tujuan='umm_slave_5mapingmodul.php';
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
    tujuan='umm_slave_5mapingmodul.php';
	
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
    tujuan='umm_slave_5mapingmodul.php';
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
	tujuan = 'umm_slave_5mapingmodul.php';
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
	tujuan = 'umm_slave_5mapingmodul.php';
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
	tujuan = 'umm_slave_5mapingmodul.php';
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
	tujuan = 'umm_slave_5mapingmodul.php';
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
    tujuan='umm_slave_5mapingmodul.php';
	
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
    tujuan='umm_slave_5mapingmodul.php';
	
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