function add_new_data()
{
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cancel();  
}

function cancel()
{
    document.getElementById('detail').style.display = 'none';
    document.getElementById('tomboldetail').disabled=false;
    document.getElementById('tgl').disabled=false;
    document.getElementById('tgl').value='';
    document.getElementById('div').disabled=false;
    document.getElementById('div').value='';
}

function detail()
{
    div=document.getElementById('div').value;
    tgl=document.getElementById('tgl').value;
    if(div=='' || tgl=='')
    {
        alert('Lengkapi Pengisian');
        return;
    }
    param = 'method=detail';
    param += '&tgl=' + tgl+'&div=' + div;
    tujuan = 'kebun_slave_mutubuah.php';
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
                    
                    loaddatadetail(div,tgl);
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

function loaddatadetail(div,tgl)
{
    
    document.getElementById('tomboldetail').disabled=true;
    document.getElementById('div').disabled=true;
    document.getElementById('tgl').disabled=true;
   
    param = 'method=loaddatadetail';
    param += '&tgl=' + tgl+'&div=' + div;
    tujuan = 'kebun_slave_mutubuah.php';
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

function savedetail()
{
	if(blok=='' || (totaljjg=='' || totaljjg=='0'))
    {
        alert('Blok dan Total Jjg harus diisi');
        return;
    }
	
    method=document.getElementById('method').value;
    kriteria = document.getElementById('kriteria').value;
	
	param='method='+method+'&kriteria='+kriteria;
	
	var arrlist = new Array();
	arrlist = JSON.parse(kriteria);
	
	for(var key in arrlist){
		param+='&kriteria_'+key+'='+document.getElementById('kriteria_'+key).value;
	}
	
	div=document.getElementById('div').value;
    tgl=document.getElementById('tgl').value;
    blok=document.getElementById('blok').value;
    totaljjg=document.getElementById('totaljjg').value;

    param+='&blok='+blok+'&totaljjg='+totaljjg;
    param+='&div='+div+'&tgl='+tgl;    

    tujuan='kebun_slave_mutubuah.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    cleardetail();
                    loaddatadetail(div,tgl);
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
    document.getElementById('blok').value='';
    document.getElementById('totaljjg').value='';
	
	kriteria = document.getElementById('kriteria').value;
	var arrlist = new Array();
	arrlist = JSON.parse(kriteria);
	
	for(var key in arrlist)
	{
		document.getElementById('kriteria_'+key).value = '';
	}
}

function deletedetail(div,tgl,blok)
{
    param='method=deletedetail'+'&div='+div+'&tgl='+tgl+'&blok='+blok;
 
    tujuan='kebun_slave_mutubuah.php';
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
                                       loaddatadetail(div,tgl); 
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              } 
    }
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
 
    tujuan = 'kebun_slave_mutubuah.php';
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

function edit(div,tgl)
{
    document.getElementById('div').value=div;
    document.getElementById('tgl').value=tgl;
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
    //document.getElementById('detail').style.display='block';
    detail(div,tgl);
}

function del(div,tgl)
{
    param='method=delete'+'&div='+div+'&tgl='+tgl;
    tujuan='kebun_slave_mutubuah.php';
    if(confirm(' Anda yakin ingin menghapus data ini ?'))
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

function posting(div,tgl,numrow)
{
    param='method=posting'+'&div='+div+'&tgl='+tgl;
    tujuan='kebun_slave_mutubuah.php';
    if(confirm('Anda yakin ingin memposting transaksi unit '+div+' pada tanggal '+tgl+' ??'))
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
                                        //x.cells[9].innerHTML = '';
                                        // x.cells[11].innerHTML = '';
                                        // x.cells[12].innerHTML = '';
                                        // x.cells[13].innerHTML = '';
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

function unposting(div,tgl,numrow)
{
    param='method=unposting'+'&div='+div+'&tgl='+tgl;
    tujuan='kebun_slave_mutubuah.php';
    if(confirm('Anda yakin ingin unposting transaksi unit '+div+' pada tanggal '+tgl+' ??'))
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
                                        //x.cells[9].innerHTML = '';
                                        // x.cells[12].innerHTML = '';
                                        // x.cells[13].innerHTML = '';
                                        // x.cells[14].innerHTML = '';
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

function htmldt(div,tgl,tipe,ev)
{
    param = 'method=html' + '&div=' + div + '&tgl=' + tgl+ '&tipe=' + tipe;
    tujuan = 'kebun_slave_mutubuah.php'+"?"+param;
    width = '700';
    height = '300';
    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
    showDialog1('Detail Mutu Buah Divisi '+div,content,width,height,ev);     
}

function excel(ev,tujuan)
{
    unitexp = document.getElementById('unitexp').value;
    perexp = document.getElementById('perexp').value;
    judul='Report Ms.Excel';    
    param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);   
}