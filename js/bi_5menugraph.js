function saveht(){
	var iconup = document.getElementById("icon").files[0];
	var formdata = new FormData();
	formdata.append("iconup", iconup);
	formdata.append("menuht", getValue('menuht'));
	formdata.append("icon", getValue('icon'));
	var con = createXMLHttpRequest();
	con.open("POST", "bi_slave_5menugraph.php?method=saveht", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					//alert('Uploaded');
					//alert(con.responseText);
					//document.getElementById('icon').value = '';
					document.getElementById('detail').style.display='block';
					loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function edit(id,caption,icon)
{
	document.getElementById('listdata').style.display = 'none';
    document.getElementById('header').style.display = 'block';
	document.getElementById('detail').style.display = 'block';
	document.getElementById('menuht').value=caption;
	document.getElementById('saveht').disabled=true;
	loaddatadetail();
}


function editdt(id,caption,file,kel)
{
	document.getElementById('iddt').value=id;
	document.getElementById('menudt').value=caption;
	
	document.getElementById('file').value=file;
	document.getElementById('kel').value=kel;
	document.getElementById('methoddt').value='updatedt';
}



function savedt()
{
	method=document.getElementById('methoddt').value;
	menuht=document.getElementById('menuht').value;
	menudt=document.getElementById('menudt').value;
	kel=document.getElementById('kel').value;
	file=document.getElementById('file').value;
	iddt=document.getElementById('iddt').value;
	
	if(kel=='' || file=='' || menudt=='')
	{
		alert('Lengkapi Pengisian');return;
	}
	
	param ='menuht=' + menuht + '&menudt=' + menudt + '&file=' + file + '&kel=' + kel + '&iddt=' + iddt;
	param+='&method='+method;
    tujuan='bi_slave_5menugraph.php';
	post_response_text(tujuan, param, respog);	
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else 
                {
                   loaddatadetail();	
				   canceldt();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}



function del(idht)
{
    param='method=delete'+'&idht='+idht;
    tujuan='bi_slave_5menugraph.php';
    if(confirm('Anda yakin ingin menghapu'))
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
                        alert('ERROR TRANSACTION,\n' + con.responseText);
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



function deldt(iddt)
{
    param='method=deletedt'+'&iddt='+iddt;
    tujuan='bi_slave_5menugraph.php';
    if(confirm('Anda yakin ingin menghapus??'))
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
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else 
                {
                    document.getElementById('containdetail').innerHTML=con.responseText;
                   loaddatadetail();	
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}



function displaylist()
{
   
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display='none';
    loaddata(0);
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}


function canceldt()
{
	document.getElementById('file').value = '';
	document.getElementById('menudt').value = '';
	document.getElementById('kel').value = '';
	document.getElementById('methoddt').value='savedt';
}


function loaddatadetail()
{
	menuht=document.getElementById('menuht').value;
	param='method=loaddatadetail'+'&menuht='+menuht;
    tujuan = 'bi_slave_5menugraph.php';
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
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    document.getElementById('containdetail').innerHTML = con.responseText;
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



function loaddata(num)
{
    param = 'method=loaddata'+'&page=' + num;
    tujuan = 'bi_slave_5menugraph.php';
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
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //document.getElementById('contain').innerHTML=con.responseText;
			
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





function newdata()//indra
{
	document.getElementById('saveht').disabled=false;
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('menuht').value='';
	document.getElementById('icon').value='';
}

function cancelht()
{
	newdata();	
}









