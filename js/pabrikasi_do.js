function getso(kdso){
	kdbuyer=document.getElementById('kdbuyer').value;
    param='method=getso'+'&kdbuyer='+kdbuyer+'&kdso='+kdso;
    tujuan = 'pabrikasi_slave_do.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					document.getElementById('kdso').innerHTML=con.responseText;
					loaddt();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}

function editht(nodo,tgldo,kdbuyer,kdout,kdso,ket,ttd1,ttd2,ttd3){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('detail').style.display='block';
	document.getElementById('nodo').value=nodo;
	document.getElementById('tgldo').value=tgldo;
	document.getElementById('kdbuyer').value=kdbuyer;
	document.getElementById('kdout').value=kdout;
	document.getElementById('kdso').value=kdso;
	document.getElementById('ket').value=ket;
	document.getElementById('ttd1').value=ttd1;
	document.getElementById('ttd2').value=ttd2;
	document.getElementById('ttd3').value=ttd3;
	document.getElementById('methodht').value='updatehead';
	
	getso(kdso);
	//loaddt();
}


function cancelht(){
	document.getElementById('methodht').value='savehead';
	document.getElementById('nodo').value='';
	document.getElementById('tgldo').value='';
	document.getElementById('kdbuyer').value='';
	document.getElementById('kdout').value='';
	document.getElementById('kdso').value='';
	document.getElementById('ket').value='';
	document.getElementById('ttd1').value='';
	document.getElementById('ttd2').value='';
	document.getElementById('ttd3').value='';
	document.getElementById('detail').style.display='none';
}


function saveht(){
	nodo=document.getElementById('nodo').value;
	tgldo=document.getElementById('tgldo').value;
	
	kdbuyer=document.getElementById('kdbuyer').value;
	kdout=document.getElementById('kdout').value;
	kdso=document.getElementById('kdso').value;
	ket=document.getElementById('ket').value;
	
	ttd1=document.getElementById('ttd1').value;
	ttd2=document.getElementById('ttd2').value;
	ttd3=document.getElementById('ttd3').value;
	
	method=document.getElementById('methodht').value;
    
	if(tgldo=='' || kdbuyer=='' || kdout==''){
		alert('Lengkapi pengisian data diatas.');return;
	}
	
	
	param='nodo='+nodo+'&tgldo='+tgldo;
	param+='&kdbuyer='+kdbuyer+'&kdout='+kdout+'&kdso='+kdso+'&ket='+ket;
	param+='&ttd1='+ttd1+'&ttd2='+ttd2+'&ttd3='+ttd3;
	param+='&method='+method;
	
	
	
	
    tujuan = 'pabrikasi_slave_do.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					
					document.getElementById('detail').style.display='block';
                    document.getElementById('nodo').value=con.responseText;
					loaddt();
					//document.getElementById('listdetail').innerHTML=arr[1];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}

function deleteht(nodo){
	param = 'method=deletehead' + '&nodo=' + nodo;
    tujuan = 'pabrikasi_slave_do.php';
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


function deletedt(nodo,nodok,kdbrg){
	param = 'method=deletedetail' + '&nodo=' + nodo+ '&nodok=' + nodok+ '&kdbrg=' + kdbrg;
    tujuan = 'pabrikasi_slave_do.php';
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
                    loaddt();
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


function editdt(nodok,kdbrg,nmbrg,qty,noseri,tglkad){
	document.getElementById('methoddt').value='updatedetail';
	document.getElementById('nodok').value=nodok;
	document.getElementById('kdbrg').value=kdbrg;
	document.getElementById('nmbrg').value=nmbrg;
	document.getElementById('qty').value=qty;
	document.getElementById('noseri').value=noseri;
	document.getElementById('tglkad').value=tglkad;
}


function canceldt(){
	document.getElementById('methoddt').value='savedetail';
	document.getElementById('nodok').value='';
	document.getElementById('kdbrg').value='';
	document.getElementById('nmbrg').value='';
	document.getElementById('qty').value='';
	document.getElementById('noseri').value='';
	document.getElementById('tglkad').value='';
}


function savedt(){
	nodo=document.getElementById('nodo').value;
	nodok=document.getElementById('nodok').value;
	kdbrg=document.getElementById('kdbrg').value;
	qty=document.getElementById('qty').value;
	noseri=document.getElementById('noseri').value;
	tglkad=document.getElementById('tglkad').value;
	method=document.getElementById('methoddt').value;
	param='nodo='+nodo+'&nodok='+nodok+'&kdbrg='+kdbrg+'&qty='+qty+'&noseri='+noseri+'&tglkad='+tglkad;
	param+='&method='+method;	
    tujuan = 'pabrikasi_slave_do.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					canceldt();
					loaddt();
					
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}




function loaddt(){
	nodo = document.getElementById('nodo').value;
    param = 'method=loaddetail&nodo=' + nodo;
    tujuan = 'pabrikasi_slave_do.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }
                else {
                    document.getElementById('listdetail').innerHTML = con.responseText;
                }
            }
            else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}




function crnodok(title,ev)
{
    content= "<div id=formnodok style=\"max-height:300px;width:100%;overflow:auto;\"></div>";
    title='Add Transaction from Warehouse';
    height='';
    width='';
    showDialog2(title,content,width,height,ev);	
    getnodok();
}

function getnodok(){
    param='method=getnodok';
    tujuan = 'pabrikasi_slave_do.php';
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
                    document.getElementById('formnodok').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}

function getlistnodok(){
    carilistnodok=document.getElementById('carilistnodok').value;
	kdso=document.getElementById('kdso').value;
    param='method=getnodok'+'&carilistnodok='+carilistnodok+'&kdso='+kdso;
    tujuan = 'pabrikasi_slave_do.php';
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
                    document.getElementById('formnodok').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}


function movedatadok(nodok,kdbrg,nmbrg,qty){
    document.getElementById('nodok').value=nodok;
	document.getElementById('kdbrg').value=kdbrg;
	document.getElementById('nmbrg').value=nmbrg;
	document.getElementById('qty').value=qty;
    document.getElementById('listnodok').style.display='none';
    closeDialog();	
}






function batalht(){
	document.getElementById('detail').style.display='none';
	document.getElementById('notran').value='';
	document.getElementById('gudang').value='';
	document.getElementById('tgl').value='';
	document.getElementById('kdpab').value='';
	document.getElementById('nmpab').value='';
	
}



function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
	cancelht();
}


function deletehead(notran){
	param = 'method=deletehead' + '&notran=' + notran;
    tujuan = 'pabrikasi_slave_do.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }
                else {
                    loaddata();
                }
            }
            else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
} 


function batalsch(){
	document.getElementById('tglsch').value='';
	document.getElementById('nodosch').value='';
}


function displaylist(){
	batalsch();
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


function loaddata(num){
	tglsch = document.getElementById('tglsch').value;
	nodosch = document.getElementById('nodosch').value;
	
    param = 'method=loaddata&page=' + num;
	if (tglsch != '') {
        param += '&tglsch=' + tglsch;
    }
	if (nodosch != '') {
        param += '&nodosch=' + nodosch;
    }
    tujuan = 'pabrikasi_slave_do.php';
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














