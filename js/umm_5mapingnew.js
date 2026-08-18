function batal()
{
	document.getElementById('id').value = '';
	document.getElementById('tipe').selectedIndex = 0;
	document.getElementById('kriteria').value = '';
	document.getElementById('status').checked = true;
	document.getElementById('method').value='insert';
}

function enabkriteria()
{
	kriteria = document.getElementById('kriteriadet');
	if(document.getElementById('kelompok').value !='')
	{
		//alert(document.getElementById('kelompok').value);
		kriteria.value='';
		kriteria.disabled=true;
	}
	else
	{
		kriteria.disabled=false;
	}
}

function loaddata(num) 
{
	param='method=loaddata';
    param+='&page='+num;
    tujuan='umm_slave_5mapingnew.php';
	
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
    tipe = document.getElementById('tipe').value;
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
	
	param='id='+id+'&tipe='+tipe+'&kriteria='+kriteria+'&status='+aktif+'&method='+method;
    tujuan='umm_slave_5mapingnew.php';
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

function edit(id,tipe,kriteria,aktif)
{
	document.getElementById('id').value=id;
	document.getElementById('tipe').value=tipe;
	document.getElementById('kriteria').value=kriteria;
	if(aktif=='1'){
		document.getElementById('status').checked=true;
	}else{
		document.getElementById('status').checked=false;
	}
	document.getElementById('method').value='update';
}

function change(idht,nourut,tonourut)
{
	param='idht='+idht+'&nourut='+nourut+'&tonourut='+tonourut+'&method=changeurut';
	tujuan='umm_slave_5mapingnew.php';
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
					loaddatadet();
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

function changedet(idht,nourut,tonourut)
{
	param='idht='+idht+'&nourut='+nourut+'&tonourut='+tonourut+'&method=changedeturut';
	tujuan='umm_slave_5mapingnew.php';
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
					loaddatadet2();
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

function addDetail(id,title,ev)
{
	showDetail(id,title,ev);
	param='id='+id+'&method=adddetail';
	tujuan='umm_slave_5mapingnew.php';
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
	tujuan='umm_slave_5mapingnew.php';
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

function hidetouser(kode,pekerjaan,ev){
	showDetail(kode,"Hide to User",ev);
	param='id='+kode+'&method=hidetouser';
	tujuan='umm_slave_5mapingnew.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contDetail').innerHTML=con.responseText;
					loadlisthidetouser(kode);
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

function loadlisthidetouser(id){
	param='id='+id+'&method=loadlisthidetouser';
	tujuan='umm_slave_5mapingnew.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('containerdt').innerHTML=con.responseText;
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

function addhideuser(kode){
	userhide = document.getElementById('userhide').value;
	param='id='+kode+'&userhide='+userhide+'&method=addhideuser';
	tujuan='umm_slave_5mapingnew.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadlisthidetouser(kode);
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

function deletehideuser(id,userhide,namakaryawan) {
	param='id='+id+'&userhide='+userhide+'&method=deletehideuser';
	tujuan='umm_slave_5mapingnew.php';
	
	if(confirm('Anda yakin hapus karyawan '+namakaryawan+' dari list?')) {
		post_response_text(tujuan, param, respog);
    }
		
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadlisthidetouser(id);
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







function bataldet(xx)
{
	document.getElementById('iddt').value = '';
	document.getElementById('subkriteria').value = '';
	document.getElementById('kriteriadet').value = '';
	document.getElementById('kriteriadet').disabled = false;
	document.getElementById('kelompok').value = '';
	document.getElementById('kelompok').disabled = false;
	document.getElementById('kode').value = '';
	document.getElementById('modul').value = '';
	document.getElementById('statusdet').checked = true;
	document.getElementById('statuspay').checked = true;
	document.getElementById('methoddet').value='insertdet';
	if(xx==1)
	{
		document.getElementById('kelompok').disabled = true;
		document.getElementById('kriteriadet').disabled = false;
	}

	if(xx==2)
	{
		document.getElementById('kelompok').disabled = false;
		document.getElementById('kriteriadet').disabled = true;
	}
}

function bataldet2()
{
	document.getElementById('iddt').value = '';
	document.getElementById('moduldt').value = '';
	//document.getElementById('filenamex').value = '';
	document.getElementById('statusdet').checked = true;
	document.getElementById('methoddet').value='insertdet2';
}

function simpandet(xx)
{
    iddt = document.getElementById('iddt').value;
    idht = document.getElementById('idht').value;
    subkriteria = document.getElementById('subkriteria').value;
    kriteriadet = document.getElementById('kriteriadet').value;
    kode = document.getElementById('kode').value;
    kelompok = document.getElementById('kelompok').value;
    modul = document.getElementById('modul').value;
    statuspay = document.getElementById('statuspay');
	if(statuspay.checked==true){
		statuspay=1;
	}else{
		statuspay=0;
	}
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
	
	param='iddt='+iddt+'&id='+idht+'&subkriteria='+subkriteria+'&modul='+modul+'&kriteriadet='+kriteriadet+'&kode='+kode+'&kelompok='+kelompok+'&statuspay='+statuspay+'&statusdet='+aktifdet+'&method='+methoddet;
    tujuan='umm_slave_5mapingnew.php';
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

					if(xx>0){
					bataldet(xx);
                    loaddatadet(0);
					}
					else{
					 closeDialog();
					}

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
    kriteriadet = document.getElementById('kriteriadet').value;
    moduldt = document.getElementById('moduldt').value;
    aktifdet = document.getElementById('statusdet');
	if(aktifdet.checked==true){
		aktifdet=1;
	}else{
		aktifdet=0;
	}
	
    methoddet=document.getElementById('methoddet').value;

    //alert(filenamex);
	
	param='iddt='+iddt+'&id='+idht+'&kriteriadet='+kriteriadet+'&statusdet='+aktifdet+'&moduldt='+moduldt+'&method='+methoddet;
    tujuan='umm_slave_5mapingnew.php';
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
	tujuan='umm_slave_5mapingnew.php';
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
    tujuan='umm_slave_5mapingnew.php';
	
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
    tujuan='umm_slave_5mapingnew.php';
	
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

function editdet(iddt,subkriteria,kode,kelompok,kriteriadet,aktif,statuspay,modul)
{
	document.getElementById('iddt').value=iddt;
	document.getElementById('subkriteria').value=subkriteria;
	document.getElementById('kelompok').value=kelompok;
	document.getElementById('kelompok').disabled=true;
	document.getElementById('kriteriadet').value=kriteriadet;
	document.getElementById('modul').value=modul;
	document.getElementById('kriteriadet').disabled=true;
	
	document.getElementById('kode').value=kode;
	if(aktif=='1'){
		document.getElementById('statusdet').checked=true;
	}else{
		document.getElementById('statusdet').checked=false;
	}

	if(statuspay=='1'){
		document.getElementById('statuspay').checked=true;
	}else{
		document.getElementById('statuspay').checked=false;
	}
	
	document.getElementById('methoddet').value='updatedet';
}

function editdet2(iddt,kriteriadet,aktif,modul)
{
	document.getElementById('iddt').value=iddt;
	document.getElementById('kriteriadet').value=kriteriadet;
	document.getElementById('moduldt').value=modul;
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
	tujuan='umm_slave_5mapingnew.php';
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
    tujuan='umm_slave_5mapingnew.php';
	
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
    tujuan='umm_slave_5mapingnew.php';
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
	tujuan = 'umm_slave_5mapingnew.php';
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
	tujuan = 'umm_slave_5mapingnew.php';
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
	tujuan = 'umm_slave_5mapingnew.php';
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
	tujuan = 'umm_slave_5mapingnew.php';
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
    tujuan='umm_slave_5mapingnew.php';
	
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
    tujuan='umm_slave_5mapingnew.php';
	
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