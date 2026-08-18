function getpemanen()
{
	kemandoran = document.getElementById('kemandoran').options[document.getElementById('kemandoran').selectedIndex].value;
    param='kemandoran='+kemandoran+'&method=getpemanen';
    tujuan='kebun_slave_mutuancaktransport.php';  
    function respon() 
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
					//=== Success Response
                    document.getElementById('pemanen').innerHTML = con.responseText;
                }
            } 
			else 
			{
				busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function add_new_data()
{
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cancel();  
}

function displayList()
{
    document.getElementById('divsch').value='';
    document.getElementById('tglsch').value='';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    loaddata(0);
}

function cancel()
{
    document.getElementById('detail').style.display = 'none';
    document.getElementById('tomboldetail').disabled=false;
    document.getElementById('tgl').disabled=false;
    document.getElementById('tgl').value='';
    document.getElementById('divisi').disabled=false;
    document.getElementById('divisi').value='';
}

function detail()
{
    divisi=document.getElementById('divisi').value;
    tgl=document.getElementById('tgl').value;
   // document.getElementById('kemandoran').disabled=true;

    if(tgl=='' || divisi=='')
    {
        alert('Lengkapi Pengisian');
        return;
    }
    param = 'method=detail';
    param += '&tgl=' + tgl+'&divisi=' + divisi;
    tujuan = 'kebun_slave_mutuancaktransport.php';
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
                else {
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
                    
                    // loaddatadetail(divisi,tgl);
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

function loaddatadetail(divisi,tgl)
{
    
    document.getElementById('tomboldetail').disabled=true;
    document.getElementById('tgl').disabled=true;
    document.getElementById('divisi').disabled=true;
   
    param = 'method=loaddatadetail';
    param += '&tgl=' + tgl+'&divisi=' + divisi;
    tujuan = 'kebun_slave_mutuancaktransport.php';
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
                else {
                    
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
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

function getsph(){
    blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
    param='blok='+blok+'&method=getsph';
    tujuan='kebun_slave_mutuancaktransport.php';  
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('sph').value = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function getkemandoran(divisi,kemandoran){
    if (divisi==0){
        divisi=document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
    }
    param='divisi='+divisi+'&method=getkemandoran';
    if (kemandoran!=0){
        param+='&kemandoran='+kemandoran;
    }
    tujuan='kebun_slave_mutuancaktransport.php';  
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('kemandoran').innerHTML = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function savedetail()
{
    divisi=document.getElementById('divisi').value;
    tgl=document.getElementById('tgl').value;
    kemandoran=document.getElementById('kemandoran').value;
	document.getElementById('kemandoran').disabled=true;
    pemanen=document.getElementById('pemanen').value;
    blok=document.getElementById('blok').value;
    sph=document.getElementById('sph').value;
    pokoksample=document.getElementById('pokoksample').value;
    pokokpanen=document.getElementById('pokokpanen').value;
    jjgpanen=document.getElementById('jjgpanen').value;
    jlhtph=document.getElementById('jlhtph').value;
    method=document.getElementById('method').value;
    kriteria = document.getElementById('kriteria').value;
    
    if(pokoksample=='' || pokokpanen=='' || jjgpanen=='')
    {
        alert('Semua data wajib di isi !');
        return;
    }

    param='&method='+method;
	
	var arrlist = new Array();
	arrlist = JSON.parse(kriteria);
	
	for(var key in arrlist){
		param+='&kriteria_'+key+'='+document.getElementById('kriteria_'+key).value;
	}
	
    
    param+='&divisi='+divisi+'&tgl='+tgl+'&kemandoran='+kemandoran+'&pemanen='+pemanen+'&blok='+blok+'&sph='+sph;
    param+='&pokoksample='+pokoksample+'&pokokpanen='+pokokpanen+'&jjgpanen='+jjgpanen+'&jlhtph='+jlhtph+'&kriteria='+kriteria;
    

    tujuan='kebun_slave_mutuancaktransport.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    cleardetail();
                    loaddatadetail(divisi,tgl);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardetail()
{
    document.getElementById('pemanen').value='';
    document.getElementById('blok').value='';
    document.getElementById('sph').value='';
    document.getElementById('pokoksample').value='';
    document.getElementById('pokokpanen').value='';
    document.getElementById('jjgpanen').value='';
    document.getElementById('jlhtph').value='';
    
	kriteria = document.getElementById('kriteria').value;
	var arrlist = new Array();
	arrlist = JSON.parse(kriteria);
	
	for(var key in arrlist)
	{
		document.getElementById('kriteria_'+key).value = '';
	}
}

function deletedetail(kemandoran,tgl,blok,pemanen,divisi)
{
    param='method=deletedetail'+'&kemandoran='+kemandoran+'&tgl='+tgl+'&blok='+blok+'&pemanen='+pemanen;
 
    tujuan='kebun_slave_mutuancaktransport.php';
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
                                       loaddatadetail(divisi,tgl); 
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              } 
    }
}

function loaddata(page)
{
    divsch=document.getElementById('divsch').value;
    tglsch=document.getElementById('tglsch').value;
param = 'method=loaddata&page=' + page;
    if (divsch != '') {
        param += '&divsch=' + divsch;
    }
    if (tglsch != '') {
        param += '&tglsch=' + tglsch;
    }
 
    tujuan = 'kebun_slave_mutuancaktransport.php';
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
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
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


/*
function form()
{
    width = '1020';
    height = '';
    content = "<fieldset><div id=containerd align=center style=\"width:1000px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}*/

function htmldt(divisi,tgl,kemandoran,tipe,ev)
{
    param = 'method=html' + '&divisi=' + divisi + '&tgl=' + tgl+ '&tipe=' + tipe+'&kemandoran='+kemandoran;
    tujuan = 'kebun_slave_mutuancaktransport.php'+"?"+param;
    width = '1020';
    height = '300';
    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
    showDialog1('Detail Transaksi',content,width,height,ev);
}

function posting(divisi,tgl,numrow)
{
    param='method=posting'+'&divisi='+divisi+'&tgl='+tgl;
    tujuan='kebun_slave_mutuancaktransport.php';
    if(confirm('Anda yakin ingin memposting data unit '+divisi+' pada tanggal '+tgl+' ??'))
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
                                        x = document.getElementById('tr_' + numrow);
                                        x.cells[12].innerHTML = '';
                                        x.cells[13].innerHTML = '';
                                        x.cells[14].innerHTML = '';
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

function del(divisi,tgl,kemandoran)
{
    param='method=delete'+'&divisi='+divisi+'&tgl='+tgl+'&kemandoran='+kemandoran;
    tujuan='kebun_slave_mutuancaktransport.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
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
                                        document.getElementById('contain').innerHTML=con.responseText;
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

function edit(divisi,kemandoran,tgl)
{
    document.getElementById('divisi').value=divisi;
    document.getElementById('tgl').value=tgl;
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
   // document.getElementById('kemandoran').value=kemandoran;
    detailedit(divisi,kemandoran,tgl);
    //document.getElementById('detail').style.display='block';
    //loaddatadetail(divisi,tgl);
    
}

function detailedit(divisi,kemandoran,tgl)
{
    // divisi=document.getElementById('divisi').value;
    // tgl=document.getElementById('tgl').value;
   // document.getElementById('kemandoran').disabled=true;
	
	if(tgl=='' || divisi=='')
    {
        alert('Lengkapi Pengisian');
        return;
    }
    param = 'method=detail';
    param += '&tgl=' + tgl+'&divisi=' + divisi;
    tujuan = 'kebun_slave_mutuancaktransport.php';
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
                else {
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
                    document.getElementById('kemandoran').value=kemandoran;
                    document.getElementById('kemandoran').disabled=true;
                    getpemanen2(divisi,kemandoran,tgl);
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

function getpemanen2(divisi,kemandoran,tgl)
{
	param='kemandoran='+kemandoran+'&method=getpemanen';
    tujuan='kebun_slave_mutuancaktransport.php';  
    function respon() 
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
					//=== Success Response
                    document.getElementById('pemanen').innerHTML = con.responseText;
					loaddatadetail(divisi,tgl);
                }
            } 
			else 
			{
				busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}


function unposting(divisi,tgl,numrow)
{
    param='method=unposting'+'&divisi='+divisi+'&tgl='+tgl;
    tujuan='kebun_slave_mutuancaktransport.php';
    if(confirm('Anda yakin ingin unposting data unit '+divisi+' pada tanggal '+tgl+' ??'))
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
                                        x = document.getElementById('tr_' + numrow);
                                        x.cells[12].innerHTML = '';
                                        x.cells[13].innerHTML = '';
                                        x.cells[14].innerHTML = '';
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

function excel(ev,tujuan)
{
    unitexp = document.getElementById('unitexp').value;
    perexp = document.getElementById('perexp').value;
    judul='Report Ms.Excel';    
    param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);   
}

function excelpreview(ev,tujuan)
{
    unitexp = document.getElementById('unitexp').value;
    perexp = document.getElementById('perexp').value;
    judul='Report Ms.Excel';    
    param = 'method=excelpreview' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);   
}