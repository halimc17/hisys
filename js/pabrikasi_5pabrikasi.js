function deletedetailbarang(kdpab,kdso,kdbrg){
	param = 'method=deletedetailbarang' + '&kdpab=' + kdpab+ '&kdso=' + kdso+ '&kdbrg=' + kdbrg;
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    listdetailbarang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function listdetailbarang(){
    kdso=document.getElementById('kdso').value;
	kdpab=document.getElementById('kdpab').value;
    param = 'method=listdetailbarang' + '&kdso=' + kdso+ '&kdpab=' + kdpab;
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else{
                    document.getElementById('listdetailbarang').style.display = 'block';
                    document.getElementById('listdetailbarang').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function canceldtbarang(){
	document.getElementById('kdbrg').value='';
	document.getElementById('nmbrg').value='';
	document.getElementById('jum').value='';
	document.getElementById('methoddetailbarang').value='savedetailbarang';
}

function savedetailbarang(){
	kdso=document.getElementById('kdso').value;
	kdbrg=document.getElementById('kdbrg').value;
	jum=document.getElementById('jum').value;
    kdpab=document.getElementById('kdpab').value;
	method=document.getElementById('methoddetailbarang').value;
	
	if(kdbrg=='' || jum==''){
		alert('Lengkapi pengisian');return;
	}
	
    param='kdbrg='+kdbrg+ '&jum=' + jum + '&kdpab=' + kdpab + '&stat=' + stat+ '&kdso=' + kdso;
	param+='&method='+method;	
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    listdetailbarang();
					canceldtbarang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

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


function moveDataBarang(kodebarang,namabarang,satuanbarang,hargabarang){
    document.getElementById('kdbrg').value=kodebarang;
    document.getElementById('nmbrg').value=namabarang;
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
}


function kdsosch(title,ev){
    content= "<div id=formkdso style=\"max-height:250px;width:100%;overflow:auto;\"></div>";
    title='Add Transaction';
    height='';
    width='350';
    showDialog2(title,content,width,height,ev);	
    getkdso();
}

function getkdso(){
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
function newdata()//indra
{
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
    //document.getElementById('nopdo').value='';
    //document.getElementById('per').value='';
	cancel1();
}

function cancel1(){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('kdpab').value='';
	document.getElementById('kdkel').value='';
	document.getElementById('stat').value='1';
    document.getElementById('nmpab').value='';
	document.getElementById('tgl1').value='';
	document.getElementById('tgl2').value='';
	document.getElementById('kdso').value='';
	document.getElementById('savehead').disabled=false;
}

function cancel(){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('kdpab').value='';
	document.getElementById('kdkel').value='';
	document.getElementById('stat').value='1';
    document.getElementById('nmpab').value='';
	document.getElementById('tgl1').value='';
	document.getElementById('tgl2').value='';
	document.getElementById('kdso').value='';
	document.getElementById('savehead').disabled=false;
	displaylist()
}






function deletedetail(kdpab,idtahapan){
	param = 'method=deletedetail' + '&kdpab=' + kdpab+ '&idtahapan=' + idtahapan;
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					canceldt();
                    listdetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletehead(kdpab){
	
	
	param = 'method=deletehead' + '&kdpab=' + kdpab;
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
	if(confirm("Anda yakin ingin menghapus ??")){
		post_response_text(tujuan, param, respon);	
	}
    //post_response_text(tujuan, param, respon);
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



function listdetail(){
    kdpab=document.getElementById('kdpab').value;
    param = 'method=listdetail' + '&kdpab=' + kdpab;
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
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
					listdetailbarang();
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


function editdetail(idtahapan,tahapan,tgldt1,tgldt2,ketdt)
{
	//document.getElementById('tahapan').disabled=true;
    document.getElementById('tahapan').value=tahapan;
	document.getElementById('idtahapan').value=idtahapan;
    document.getElementById('tgldt1').value=tgldt1;
    document.getElementById('tgldt2').value=tgldt2;
	 document.getElementById('ketdt').value=ketdt;
    document.getElementById('methoddetail').value='updatedetail';
}


function canceldt(){
	document.getElementById('idtahapan').value='';
	document.getElementById('tgldt1').value='';
	document.getElementById('tahapan').value='';
	document.getElementById('tgldt2').value='';
	document.getElementById('ketdt').value='';
	document.getElementById('tahapan').disabled=false;
	document.getElementById('methoddetail').value='savedetail';
}

function savedetail()
{
	tgl1=document.getElementById('tgl1').value;
	tgl2=document.getElementById('tgl2').value;
    kdpab=document.getElementById('kdpab').value;
	tahapan=document.getElementById('tahapan').value;
    tgldt1=document.getElementById('tgldt1').value;
    tgldt2=document.getElementById('tgldt2').value;
	ketdt=document.getElementById('ketdt').value;
	idtahapan=document.getElementById('idtahapan').value;
	method=document.getElementById('methoddetail').value;
	
	
	if(tahapan=='' || tgldt1=='' || tgldt2=='' || ketdt=='')
	{
		 alert('Lengkapi pengisian form diatas.');
		 return;
	}
	
    param='kdpab='+kdpab+ '&tgldt1=' + tgldt1 + '&tgldt2=' + tgldt2 + '&tahapan=' + tahapan;
	param+='&tgl1='+tgl1+'&tgl2='+tgl2 + '&ketdt=' + ketdt+ '&idtahapan=' + idtahapan;
	param+='&method='+method;	
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
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
					document.getElementById('tahapan').disabled=false;
					document.getElementById('tahapan').value='';
					canceldt();
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

function edit(kdpab,nmpab,kdkel,tgl1,tgl2,kdso,stat)
{
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
    document.getElementById('kdpab').value=kdpab;
    document.getElementById('nmpab').value=nmpab;
    document.getElementById('kdkel').value=kdkel;
    document.getElementById('tgl1').value=tgl1;
    document.getElementById('tgl2').value=tgl2;
    document.getElementById('kdso').value=kdso;
	document.getElementById('stat').value=stat;
    document.getElementById('method').value='updatehead';
	document.getElementById('detail').style.display='block';
	if(kdso!=''){
		document.getElementById('savedetailbarang').disabled=true;
		document.getElementById('canceldtbarang').disabled=true;
	}else{
		document.getElementById('savedetailbarang').disabled=false;
		document.getElementById('canceldtbarang').disabled=false;
	}
	listdetail();
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

function loaddata(num)
{
	// thnsch = document.getElementById('thnsch');
    // thnsch = thnsch.options[thnsch.selectedIndex].value;
	schkdpab=document.getElementById('schkdpab').value;
	schnmpab=document.getElementById('schnmpab').value;
	schkdkel=document.getElementById('schkdkel').value;
	schkdso=document.getElementById('schkdso').value;
	schstat=document.getElementById('schstat').value;
	
    param = 'method=loaddata&page=' + num;
	
	param += '&schkdpab=' + schkdpab+'&schnmpab=' + schnmpab+'&schkdkel=' + schkdkel+'&schkdso=' + schkdso+'&schstat=' + schstat;
    
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
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
    nmpab=document.getElementById('nmpab').value;
    kdkel=document.getElementById('kdkel').value;
	method=document.getElementById('method').value;
	tgl1=document.getElementById('tgl1').value;
	tgl2=document.getElementById('tgl2').value;
	kdso=document.getElementById('kdso').value;
	stat=document.getElementById('stat').value;
	
	if(nmpab=='' || kdkel=='' || tgl1=='' || tgl2=='')
	{
		 alert('Lengkapi pengisian form diatas.');
		 return;
	}
	
	param='kdpab='+kdpab+ '&nmpab=' + nmpab + '&kdkel=' + kdkel;
	param+='&tgl1='+tgl1+'&tgl2='+tgl2+'&kdso='+kdso+'&stat='+stat;
	param+='&method='+method;
    tujuan = 'pabrikasi_slave_5pabrikasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else{
                    //document.getElementById('savehead').disabled=true;
                    document.getElementById('kdpab').value=con.responseText;
                    document.getElementById('detail').style.display='block';
					listdetail();
					if(kdso==''){
						document.getElementById('savedetailbarang').disabled=true;
						document.getElementById('canceldtbarang').disabled=true;
					}else{
						document.getElementById('savedetailbarang').disabled=false;
						document.getElementById('canceldtbarang').disabled=false;
					}
                    //listupah(nopdo,unit,per);
                }
            } else {
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
