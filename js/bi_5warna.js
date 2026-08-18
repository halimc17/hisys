
function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}



function del(tipe)
{
	//alert('masuk');
    param = 'method=delete' + '&tipe=' + tipe;
    tujuan = 'bi_slave_5warna.php';
    post_response_text(tujuan, param, respon);
    function respon()
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
                else
                {
                    loaddata();  
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
 
    
	//namasch = document.getElementById('namasch').value;
	
    param = 'method=loaddata&page=' + num;
	// if (thnsch != '') {
        // param += '&thnsch=' + thnsch;
    // }
    tujuan = 'bi_slave_5warna.php';
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


function edit(tipe,kodefill,kodeline,width)
{
	document.getElementById('tipe').value=tipe;
	document.getElementById('kodeline').value=kodeline;
	document.getElementById('kodefill').value=kodefill;
	document.getElementById('width').value=width;
	document.getElementById('method').value='update';
	document.getElementById('displaycolorfill').style.backgroundColor=kodefill;
	document.getElementById('displaycolorline').style.backgroundColor=kodeline;
	document.getElementById('tipe').disabled=true;
	
}

function save()
{
	tipe=document.getElementById('tipe').value;
	method=document.getElementById('method').value;
	
    kodefill=document.getElementById('kodefill').value;
	kodeline=document.getElementById('kodeline').value;
	width=document.getElementById('width').value;
	if(tipe=='')
	{
		alert('Lengkapi pengisian !');return;
	}
    param ='kodefill=' + kodefill + '&kodeline=' + kodeline + '&tipe=' + tipe + '&width=' + width + '&method=' + method;
    tujuan = 'bi_slave_5warna.php';
    post_response_text(tujuan, param, respon);
    function respon()
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
                else
                {
					loaddata();
					cancel();
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





function cancel()
{
	document.getElementById('method').value='insert';
	document.getElementById('tipe').value='';
    document.getElementById('kodeline').value='';
	document.getElementById('width').value='';
	document.getElementById('displaycolorline').style.backgroundColor='';	
    document.getElementById('kodefill').value='';
	document.getElementById('displaycolorfill').style.backgroundColor='';
	document.getElementById('tipe').disabled=false;
	
}



function cariwarna(jenis,ev)
{
    content = "<div id=listwarna style=\"height:400px;width:905px;\"></div>";
    title =' Tabel Warna :';
    width = '904';
    height = '377';
    showDialog1(title, content, width, height, ev);
	getwarna(jenis);
}


function movewarna(warna,jenis)
{
	document.getElementById('kode'+jenis).value=warna;
	document.getElementById('displaycolor'+jenis).style.backgroundColor=warna;
	closeDialog();
}



function getwarna(jenis)
{
    param = 'method=cariwarna&jenis=' + jenis;

    tujuan = 'bi_slave_5warna.php';
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
                else
                {
                    document.getElementById('listwarna').innerHTML = con.responseText;
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




















