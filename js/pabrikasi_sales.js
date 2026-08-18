function tambahBarang(title,ev){
    content= "<div id=formBarang style=\"max-height:250px;max-width:100%;overflow:auto;\"></div>";
    title='Add Material';
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}

function getListBarang(){
    param='method=getListBarang';
    tujuan = 'pabrikasi_slave_sales.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}

function cariListBarang()
{
    namaBarangCari=document.getElementById('namaBarangCari').value;
    param='method=getListBarang'+'&namaBarangCari='+namaBarangCari;
  
    tujuan = 'pabrikasi_slave_sales.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}



function moveDataBarang(kodebarang,namabarang,satuanbarang,hargabarang)
{
    document.getElementById('kdbrg').value=kodebarang;
    document.getElementById('nmbrg').value=namabarang;
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
	
}



function newdata()
{
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
    //document.getElementById('nopdo').value='';
    //document.getElementById('per').value='';
	cancel();
}


function cancel()
{
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('kdpt').value='';
	document.getElementById('kdso').value='';
	document.getElementById('kdcus').value='';
    document.getElementById('tglorder').value='';
	document.getElementById('nopo').value='';
	document.getElementById('tglmulai').value='';
	document.getElementById('tglselesai').value='';
	document.getElementById('salesid').value='';
	document.getElementById('savehead').disabled=false;
	document.getElementById('kdpt').disabled=false;
    document.getElementById('kdso').disabled=false;
    document.getElementById('kdcus').disabled=false;
    document.getElementById('method').value='savehead';
	
}








function deletedetail(kdso,kdbrg)
{
	param = 'method=deletedetail' + '&kdso=' + kdso+ '&kdbrg=' + kdbrg;
    tujuan = 'pabrikasi_slave_sales.php';
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
                    alert(con.responseText);
                }
                else
                {
                    listdetail();
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


function deletehead(kdpt,kdso)
{
	param = 'method=deletehead' + '&kdso=' + kdso + '&kdpt=' + kdpt;
    tujuan = 'pabrikasi_slave_sales.php';
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
                    alert(con.responseText);
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



function listdetail()
{
    kdso=document.getElementById('kdso').value;
    param = 'method=listdetail' + '&kdso=' + kdso;
    tujuan = 'pabrikasi_slave_sales.php';
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
                    alert(con.responseText);
                }
                else
                {
                    document.getElementById('listdetail').style.display = 'block';
                    document.getElementById('listdetail').innerHTML=con.responseText;
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


function editdetail(kdbrg,nmbrg,jum,ket,stat)
{
    document.getElementById('kdbrg').value=kdbrg;
	document.getElementById('nmbrg').value=nmbrg;
    document.getElementById('jum').value=jum;
    document.getElementById('ket').value=ket;
	document.getElementById('stat').value=stat;
    document.getElementById('methoddetail').value='updatedetail';
}

function canceldt()
{
	document.getElementById('kdbrg').value='';
	document.getElementById('nmbrg').value='';
    document.getElementById('jum').value='';
    document.getElementById('ket').value='';
	document.getElementById('stat').value='0';
	document.getElementById('methoddetail').value='savedetail';
	
}


function savedetail()
{
	kdso=document.getElementById('kdso').value;
	kdbrg=document.getElementById('kdbrg').value;
	jum=document.getElementById('jum').value;
    ket=document.getElementById('ket').value;
	stat=document.getElementById('stat').value;
	method=document.getElementById('methoddetail').value;
	
    param='kdbrg='+kdbrg+ '&jum=' + jum + '&ket=' + ket + '&stat=' + stat+ '&kdso=' + kdso;
	param+='&method='+method;	
    tujuan = 'pabrikasi_slave_sales.php';
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
                    alert(con.responseText);
                }
                else
                {
                    listdetail();
					canceldt();
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

function edit(kdpt,kdso,kdcus,tglorder,nopo,tglmulai,tglselesai,salesid)
{
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
    document.getElementById('kdpt').value=kdpt;
    document.getElementById('kdso').value=kdso;
	document.getElementById('kdpt').disabled=true;
    document.getElementById('kdso').disabled=true;
    document.getElementById('kdcus').disabled=true;
    document.getElementById('kdcus').value=kdcus;
    document.getElementById('tglorder').value=tglorder;
    document.getElementById('nopo').value=nopo;
    document.getElementById('tglmulai').value=tglmulai;
	document.getElementById('tglselesai').value=tglselesai;
	document.getElementById('salesid').value=salesid;
    document.getElementById('method').value='updatehead';
	document.getElementById('detail').style.display='block';
	listdetail();
}


function displaylist()
{
	document.getElementById('schkdso').value='';
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

function loaddata(num)
{
	// thnsch = document.getElementById('thnsch');
    // thnsch = thnsch.options[thnsch.selectedIndex].value;
	schkdso=document.getElementById('schkdso').value;
    param = 'method=loaddata&page=' + num;
	if (schkdso != '') 
	{
        param += '&schkdso=' + schkdso;
    }
    tujuan = 'pabrikasi_slave_sales.php';
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

function savehead(){
    kdpt=document.getElementById('kdpt').value;
    kdso=document.getElementById('kdso').value;
    kdcus=document.getElementById('kdcus').value;
	method=document.getElementById('method').value;
	tglorder=document.getElementById('tglorder').value;
	nopo=document.getElementById('nopo').value;
	tglmulai=document.getElementById('tglmulai').value;
	salesid=document.getElementById('salesid').value;
	tglselesai=document.getElementById('tglselesai').value;
	
	if(kdpt=='' || kdso=='' || kdcus=='' || tglorder=='' || salesid=='' || tglmulai=='' || tglselesai=='')
	{
		 alert('Lengkapi pengisian form diatas.');
		 return;
	}
	
	
	param='kdpt='+kdpt+ '&kdso=' + kdso + '&kdcus=' + kdcus+'&salesid='+salesid;
	param+='&tglorder='+tglorder+'&nopo='+nopo+'&tglmulai='+tglmulai+'&tglselesai='+tglselesai;
	param+='&method='+method;
    tujuan = 'pabrikasi_slave_sales.php';
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
                    alert(con.responseText);
                }
                else
                {
                    //document.getElementById('savehead').disabled=true;
                    // document.getElementById('kdpab').value=con.responseText;
                    
					document.getElementById('detail').style.display='block';
					document.getElementById('kdso').disabled=true;
					document.getElementById('kdpt').disabled=true;
					document.getElementById('kdcus').disabled=true;
					
					listdetail();
                    //listupah(nopdo,unit,per);
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



















//########################################################
//#################  T A B   R E K A P  ##################
//########################################################

/*
function showDialog1v(title,content,width,height,ev)
{
	if (document.getElementById('dynamic2')) {
			c = document.createElement('div');
		c.style.width = width+'px';
	} else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic2');
	   	c.setAttribute('class', 'drag');
	   	c.style.position = 'absolute';
	   	c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 2000;
	   	document.body.appendChild(c);
	}
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeDialog2() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\"><br><br>";
	cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic2').innerHTML=cont;
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = '75px';
	document.getElementById('dynamic2').style.display='';
}




function printFile1v(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='250';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1v(title,content,width,height,ev); 	
}
*/

function pdfrekap() {
    
	fileTarget='keu_slave_pdo';
	var cont = document.getElementById('listrekap');
	nopdo=document.getElementById('nopdo').value;
    unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
	param = 'method=pdfrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	//alert(param);
    cont.innerHTML = "<iframe frameborder=0 style='width:100%;height:500px' src='"+fileTarget+".php?"+param+"'></iframe>";
}

function excelrekap(tiperekap,ev){
	nopdo=document.getElementById('nopdo').value;
    unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
	param = 'method=htmlexcelrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per+ '&tiperekap=' + tiperekap;
    tujuan = 'keu_slave_pdo.php';
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev);	
}


function htmlrekap(tiperekap)
{
	nopdo=document.getElementById('nopdo').value;
    unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
	
	param = 'method=htmlexcelrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&tiperekap=' + tiperekap;
   
    tujuan = 'keu_slave_pdo.php';
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
                    alert(con.responseText);
                }
                else
                {
                    document.getElementById('listrekap').style.display = 'block';
                    document.getElementById('listrekap').innerHTML=con.responseText;
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
