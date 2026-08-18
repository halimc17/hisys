function kdsocr(title,ev)
{
    content= "<div id=formkdso style=\"max-height:250px;width:100%;overflow:auto;\"></div>";
    title='Find Sales Order';
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    getkdso();
}

function getkdso()
{
    param='method=getkdso';
    tujuan = 'pabrikasi_slave_rab.php';
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
                    document.getElementById('formkdso').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}

function getlistkdso()
{
    carilistkdso=document.getElementById('carilistkdso').value;
    param='method=getkdso'+'&carilistkdso='+carilistkdso;
  
    tujuan = 'pabrikasi_slave_rab.php';
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
                    document.getElementById('formkdso').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}


function movedatakdso(kdso)
{
    document.getElementById('kdso').value=kdso;
    document.getElementById('listkdso').style.display='none';
    closeDialog();	
}













function tambahBarang(title,ev)
{
    
    content= "<div id=formBarang style=\"max-height:250px;width:100%;overflow:auto;\"></div>";
    title='Find Material';
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}

function getListBarang()
{
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

function moveDataBarang(kodebarang,namabarang,harga)
{
    document.getElementById('kdbrg').value=kodebarang;
    document.getElementById('nmbrg').value=namabarang;
	document.getElementById('biaya').value=harga;
	
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
}



function carikdpab(title,ev)
{
    content= "<div id=formkdpab style=\"max-height:250px;width:100%;overflow:auto;\"></div>";
    title='Find';
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    getkdpab();
}

function getkdpab()
{
    param='method=getkdpab';
    tujuan = 'pabrikasi_slave_rab.php';
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
                    document.getElementById('formkdpab').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}

function getlistkdpab()
{
    carilistkdpab=document.getElementById('carilistkdpab').value;
    param='method=getkdpab'+'&carilistkdpab='+carilistkdpab;
  
    tujuan = 'pabrikasi_slave_rab.php';
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
                    document.getElementById('formkdpab').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}



function movedata(kdpab)
{
    document.getElementById('kdpab').value=kdpab;
    document.getElementById('listkdpab').style.display='none';
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
	document.getElementById('tmblkdpab').style.display = 'block';
	document.getElementById('tmblkdso').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('kdso').value='';
	document.getElementById('stat').value='0';
    document.getElementById('kdpab').value='';
	document.getElementById('savehead').disabled=false;
	document.getElementById('method').value='savehead';
	
}








function deletedetail(kdpab,tahapan,kelby,kdbrg){
	param = 'method=deletedetail' + '&kdpab=' + kdpab+ '&tahapan=' + tahapan+ '&kelby=' + kelby+ '&kdbrg=' + kdbrg;
	
    tujuan = 'pabrikasi_slave_rab.php';
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


function deletehead(kdpab)
{
	param = 'method=deletehead' + '&kdpab=' + kdpab;
    tujuan = 'pabrikasi_slave_rab.php';
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
    kdpab=document.getElementById('kdpab').value;
    param = 'method=listdetail' + '&kdpab=' + kdpab;
    tujuan = 'pabrikasi_slave_rab.php';
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
					gettahapan();
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



function gettahapan()
{
	kdpab=document.getElementById('kdpab').value;
	param = 'method=gettahapan' + '&kdpab=' + kdpab;
    tujuan = 'pabrikasi_slave_rab.php';
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
                    document.getElementById('tahapan').innerHTML=con.responseText;
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



function editdetail(tahapan,kelby,kdbrg,nmbrg,jumlah,biaya)
{
	document.getElementById('tahapan').value=tahapan;
	document.getElementById('kelby').value=kelby;
    document.getElementById('kdbrg').value=kdbrg;
	document.getElementById('nmbrg').value=nmbrg;
	document.getElementById('tahapan').disabled=true;
	document.getElementById('kelby').disabled=true;
    document.getElementById('kdbrg').disabled=true;
	document.getElementById('nmbrg').disabled=true;
	
	
	//document.getElementById('tmblCariNoGudang').disabled=true;
	document.getElementById('tmblCariNoGudang').style.display = 'none';
    document.getElementById('jumlah').value=jumlah;
    document.getElementById('biaya').value=biaya;
    document.getElementById('methoddetail').value='updatedetail';
}

function canceldt()
{
	
	document.getElementById('tmblCariNoGudang').style.display = 'block';
	document.getElementById('tahapan').disabled=false;
	document.getElementById('kelby').disabled=false;
    document.getElementById('kdbrg').disabled=false;
	document.getElementById('nmbrg').value='';
	document.getElementById('tahapan').value='';
    document.getElementById('kdbrg').value='';
    document.getElementById('jumlah').value='';
	document.getElementById('biaya').value='';
	document.getElementById('methoddetail').value='savedetail';
}


function savedetail()
{
	kdpab=document.getElementById('kdpab').value;
	tahapan=document.getElementById('tahapan').value;
	kelby=document.getElementById('kelby').value;
	kdbrg=document.getElementById('kdbrg').value;
    jumlah=document.getElementById('jumlah').value;
	biaya=document.getElementById('biaya').value;
	method=document.getElementById('methoddetail').value;
    param='tahapan='+tahapan+ '&kelby=' + kelby + '&kdbrg=' + kdbrg + '&jumlah=' + jumlah+ '&biaya=' + biaya+ '&kdpab=' + kdpab;
	param+='&method='+method;	
    tujuan = 'pabrikasi_slave_rab.php';
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

function edit(kdpab,kdso,stat)
{
	document.getElementById('tmblkdpab').style.display = 'none';
	document.getElementById('tmblkdso').style.display = 'none';
	document.getElementById('savehead').disabled=true;
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
    document.getElementById('kdpab').value=kdpab;
    document.getElementById('kdso').value=kdso;
    document.getElementById('stat').value=stat;
    document.getElementById('method').value='updatehead';
	document.getElementById('detail').style.display='block';
	listdetail();
}


function displaylist()
{
	document.getElementById('schkdpab').value='';
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
	schkdpab=document.getElementById('schkdpab').value;
	
	schstat=document.getElementById('schstat').value;
	schkdso=document.getElementById('schkdso').value;
    param = 'method=loaddata&page=' + num;
	param += '&schkdpab=' + schkdpab+'&schkdso=' + schkdso+'&schstat=' + schstat;
 
    tujuan = 'pabrikasi_slave_rab.php';
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
    kdpab=document.getElementById('kdpab').value;
    kdso=document.getElementById('kdso').value;
	stat=document.getElementById('stat').value;
	method=document.getElementById('method').value;
	
	if(kdpab=='')
	{
		 alert('Kode Pabrikasi Wajib diisi !');
		 return;
	}
	
	
	param='kdpab='+kdpab+ '&kdso=' + kdso + '&stat=' + stat;
	param+='&method='+method;
    tujuan = 'pabrikasi_slave_rab.php';
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
					document.getElementById('tmblkdpab').style.display = 'none';
					document.getElementById('tmblkdso').style.display = 'none';
					
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
