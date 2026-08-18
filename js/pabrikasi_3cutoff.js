function gethitunght(){

	persen=document.getElementById('persen').value;
	if(persen>100){
		alert('Persen > 100');
		document.getElementById('persen').value='';
		return;
	}
	rptampung=document.getElementById('rptampung').value;	
	hargatotal=(parseFloat(persen)/100)*parseFloat(rptampung);
	document.getElementById('total').value=hargatotal;
}


function gethitungdt(){
	total=document.getElementById('total').value;
	jumlahdt=document.getElementById('jumlahdt').value;
	persendt=document.getElementById('persendt').value;
	
	hargatotal=(parseFloat(persendt)/100)*parseFloat(total);
	document.getElementById('hargadt').value=hargatotal;
	
	hargasatdt=parseFloat(hargatotal)/parseFloat(jumlahdt);
	document.getElementById('hargasatdt').value=hargasatdt;
	
	
}




function tambahBarang(title,ev){
    content= "<div id=formBarang style=\"max-height:250px;width:100%;overflow:auto;\"></div>";
    title='Add Material';
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}

function getListBarang()
{
    param='method=getListBarang';
    tujuan = 'pabrikasi_slave_3cutoff.php';
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

function cariListBarang(){
	kdso=document.getElementById('kdso').value;
    namaBarangCari=document.getElementById('namaBarangCari').value;
    param='method=getListBarang'+'&namaBarangCari='+namaBarangCari+'&kdso='+kdso;
    tujuan = 'pabrikasi_slave_3cutoff.php';
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

function moveDataBarang(kodebarang,namabarang,qty)
{
    document.getElementById('kdbrgdt').value=kodebarang;
    document.getElementById('nmbrgdt').value=namabarang;
	document.getElementById('jumlahdt').value=qty;
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
}
















function crkdpab(title,ev)
{
    content= "<div id=formkdso style=\"max-height:250px;width:100%;overflow:auto;\"></div>";
    title='Find Fabrication Code';
    height='';
    width='';
    showDialog2(title,content,width,height,ev);	
    getkdso();
}

function getkdso()
{
    param='method=getkdso';
    tujuan = 'pabrikasi_slave_3cutoff.php';
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
  
    tujuan = 'pabrikasi_slave_3cutoff.php';
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


function movedatakdso(kdpab,nmpab,kdso,total){
    document.getElementById('kdpab').value=kdpab;
	document.getElementById('nmpab').value=nmpab;
	document.getElementById('kdso').value=kdso;
	document.getElementById('rptampung').value=total;
    document.getElementById('listkdso').style.display='none';
    closeDialog();	
}
function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
	cancel();
}


function cancel(){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('kdpab').value='';
    document.getElementById('nmpab').value='';
	document.getElementById('tgl1').value='';
	document.getElementById('persen').value='';
	document.getElementById('kdso').value='';
	document.getElementById('total').value='';
	document.getElementById('savehead').disabled=false;
	document.getElementById('tgl1').disabled=false;
	document.getElementById('persen').disabled=false;
	//document.getElementById('total').disabled=false;
	document.getElementById('method').value='savehead';
	
	
}



function postingdt(kdpab,kdbrgdt)
{
	param = 'method=postingdt' + '&kdpab=' + kdpab+ '&kdbrgdt=' + kdbrgdt;
    tujuan = 'pabrikasi_slave_3cutoff.php';
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




function deletedetail(kdpab,kdbrgdt)
{
	param = 'method=deletedetail' + '&kdpab=' + kdpab+ '&kdbrgdt=' + kdbrgdt;
    tujuan = 'pabrikasi_slave_3cutoff.php';
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


function deletehead(kdpab){
	
	
	param = 'method=deletehead' + '&kdpab=' + kdpab;
    tujuan = 'pabrikasi_slave_3cutoff.php';
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
    tujuan = 'pabrikasi_slave_3cutoff.php';
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


function editdetail(kdbrgdt,nmbrgdt,jumlahdt,persendt,hargadt,hargasatdt){
	document.getElementById('kdbrgdt').value=kdbrgdt;
	document.getElementById('nmbrgdt').value=nmbrgdt;
	document.getElementById('jumlahdt').value=jumlahdt;
	document.getElementById('persendt').value=persendt;
	document.getElementById('hargadt').value=hargadt;
	document.getElementById('hargasatdt').value=hargasatdt;
    document.getElementById('methoddetail').value='updatedetail';
}


function canceldt(){
	document.getElementById('kdbrgdt').value='';
	document.getElementById('nmbrgdt').value='';
	document.getElementById('jumlahdt').value='';
	document.getElementById('persendt').value='';
	document.getElementById('hargadt').value='';
	document.getElementById('hargasatdt').value='';
	document.getElementById('methoddetail').value='savedetail';
}

function savedetail(){
    kdpab=document.getElementById('kdpab').value;
	kdbrgdt=document.getElementById('kdbrgdt').value;
	jumlahdt=document.getElementById('jumlahdt').value;
    persendt=document.getElementById('persendt').value;
    hargadt=document.getElementById('hargadt').value;
	hargasatdt=document.getElementById('hargasatdt').value;
	method=document.getElementById('methoddetail').value;
	
	
	if(kdbrgdt=='' || jumlahdt=='' || persendt=='' || hargadt=='' || hargasatdt==''){
		alert('Lengkapi pengisian');return;
	}
	
    param='kdpab='+kdpab+ '&kdbrgdt=' + kdbrgdt + '&jumlahdt=' + jumlahdt + '&persendt=' + persendt;
	param+='&hargadt='+hargadt+'&hargasatdt='+hargasatdt;
	param+='&method='+method;	
    tujuan = 'pabrikasi_slave_3cutoff.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }
                else{
					canceldt();
                    listdetail();
                }
            }
            else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function edit(kdpab,nmpab,tgl1,persen,total,kdso)
{
	
	document.getElementById('savehead').disabled=true;
	document.getElementById('tgl1').disabled=true;
	document.getElementById('persen').disabled=true;
	document.getElementById('total').disabled=true;
	
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('kdso').value=kdso;
    document.getElementById('kdpab').value=kdpab;
    document.getElementById('nmpab').value=nmpab;
    document.getElementById('tgl1').value=tgl1;
    document.getElementById('persen').value=persen;
    document.getElementById('total').value=total;
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
    param = 'method=loaddata&page=' + num;
	if (schkdpab != '') 
	{
        param += '&schkdpab=' + schkdpab;
    }
    tujuan = 'pabrikasi_slave_3cutoff.php';
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

function savehead()
{
    kdpab=document.getElementById('kdpab').value;
	kdso=document.getElementById('kdso').value;
    nmpab=document.getElementById('nmpab').value;
	method=document.getElementById('method').value;
	tgl1=document.getElementById('tgl1').value;
	persen=document.getElementById('persen').value;
	total=document.getElementById('total').value;
	param='kdpab='+kdpab+ '&nmpab=' + nmpab + '&persen=' + persen;
	param+='&tgl1='+tgl1+'&total='+total+'&kdso='+kdso;
	param+='&method='+method;
	
    tujuan = 'pabrikasi_slave_3cutoff.php';
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
                    document.getElementById('detail').style.display='block';
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
