function cekall(){
    drt = document.getElementById('cekall');
    if (drt.checked == true){
        chk = true;
    }
    else{
        chk = false;
	}
    var tbl = document.getElementById("contentdetail");
    var row = tbl.rows.length;
    for (i = 1; i <= row; i++){
        document.getElementById('cek' + i).checked = chk;
    }
}



function gettotal(no){
	a=document.getElementById('qty'+no).innerHTML;
	b=document.getElementById('harsat'+no).value;
	c=parseFloat(a)*parseFloat(b);
	document.getElementById('total'+no).innerHTML=c;
	getppn(no);
}

function getppn(no){
	a=document.getElementById('total'+no).innerHTML;
	b=(10/100*parseFloat(a));
	document.getElementById('ppn'+no).innerHTML=b;
	
}


function cancelht(){
	document.getElementById('notran').value='';
	document.getElementById('tglpen').value='';
	document.getElementById('tgljth').value='';
	document.getElementById('kdbuyer').value='';
	document.getElementById('kdso').value='';
	document.getElementById('kdbuyer').disabled=false;
	document.getElementById('kdso').disabled=false;
	document.getElementById('ttd1').value='';
	document.getElementById('ttd2').value='';
	document.getElementById('ttd3').value='';
	document.getElementById('methodht').value='savehead';
}


function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
	document.getElementById('detaildata').innerHTML = '';
	document.getElementById('listdetail').innerHTML = '';
	cancelht();
}


function saveht(){
	method=document.getElementById('methodht').value;
	notran=document.getElementById('notran').value;
	tglpen=document.getElementById('tglpen').value;
	tgljth=document.getElementById('tgljth').value;
	kdbuyer=document.getElementById('kdbuyer').value;
	kdso=document.getElementById('kdso').value;
	ttd1=document.getElementById('ttd1').value;
	ttd2=document.getElementById('ttd2').value;
	ttd3=document.getElementById('ttd3').value;
	
	param='notran='+notran+'&tglpen='+tglpen+'&tgljth='+tgljth;
	param+='&kdbuyer='+kdbuyer+'&kdso='+kdso;
	param+='&ttd1='+ttd1+'&ttd2='+ttd2+'&ttd3='+ttd3;
	param+='&method='+method;
	
    tujuan = 'pabrikasi_slave_penagihan.php';
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
                    document.getElementById('notran').value=con.responseText;
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




function listdo(){
	nodo=document.getElementById('nodo').value;
	kdbuyer=document.getElementById('kdbuyer').value;
	kdso=document.getElementById('kdso').value;
    param='method=listdo'+'&nodo='+nodo+'&kdbuyer='+kdbuyer+'&kdso='+kdso;
    tujuan = 'pabrikasi_slave_penagihan.php';
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
					document.getElementById('detaildata').style.display='block';
                    document.getElementById('detaildata').innerHTML=con.responseText;
                    
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}


maxf=0
sekarang=1;
function saveall(maxRow){    
    maxf=maxRow;
    loopsave(1,maxRow);
}

function loopsave(currRow,maxRow){
	
    nodo=document.getElementById('nodo').value;
    kdbrg=document.getElementById('kdbrg'+currRow).innerHTML;
    qty=document.getElementById('qty'+currRow).innerHTML;
	harsat=document.getElementById('harsat'+currRow).value;
	total=document.getElementById('total'+currRow).innerHTML;
	ppn=document.getElementById('ppn'+currRow).innerHTML;
	notran=document.getElementById('notran').value;
    if (document.getElementById('cek'+currRow).checked == true){
        cek = 1;
    }
    else{
        cek = 0;
    }
	
    param = 'method=savedetail' + '&nodo=' + nodo + '&kdbrg=' + kdbrg + '&qty=' + qty+ '&harsat=' + harsat+ '&total=' + total;
    param+='&ppn=' + ppn + '&cek=' + cek+ '&notran=' + notran;;
	tujuan = 'pabrikasi_slave_penagihan.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row'+currRow).style.backgroundColor='cyan';
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                        document.getElementById('row'+currRow).style.backgroundColor='red';
                        unlockScreen();
                } else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow) {
						alert('Done');
						document.getElementById('detaildata').style.display='none';
						loaddt();
						document.getElementById('nodo').value='';
                    }  
                    else{
						loopsave(currRow,maxRow);
                    }
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
	notran=document.getElementById('notran').value;
    param = 'method=loaddetail&notran=' + notran;
    tujuan = 'pabrikasi_slave_penagihan.php';
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

function cancelsch(){
	document.getElementById('tglsch').value='';
	document.getElementById('notransch').value='';
}

function displaylist(){
	cancelsch();
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
	tglsch = document.getElementById('tglsch').value;
	notransch = document.getElementById('notransch').value;
	
    param = 'method=loaddata&page=' + num;
	if (tglsch != '') {
        param += '&tglsch=' + tglsch;
    }
	if (notransch != '') {
        param += '&notransch=' + notransch;
    }
    tujuan = 'pabrikasi_slave_penagihan.php';
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


function posting(notran){
	param = 'method=posting' + '&notran=' + notran;
    tujuan = 'pabrikasi_slave_penagihan.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
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





function deletedt(notran,nodo,kdbrg){
	param = 'method=deletedetail' + '&notran=' + notran+ '&nodo=' + nodo+ '&kdbrg=' + kdbrg;
    tujuan = 'pabrikasi_slave_penagihan.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    loaddt();
                }
            }
            else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteht(notran){
	param = 'method=deletehead' + '&notran=' + notran;
    tujuan = 'pabrikasi_slave_penagihan.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
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


function editht(notran,tglpen,tgljth,kdbuyer,kdso,ttd1,ttd2,ttd3){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('notran').value=notran;
	document.getElementById('tglpen').value=tglpen;
	document.getElementById('tgljth').value=tgljth;
	document.getElementById('kdbuyer').value=kdbuyer;
	document.getElementById('kdso').value=kdso;
	document.getElementById('kdbuyer').disabled=true;
	document.getElementById('kdso').disabled=true;
	document.getElementById('ttd1').value=ttd1;
	document.getElementById('ttd2').value=ttd2;
	document.getElementById('ttd3').value=ttd3;
	document.getElementById('methodht').value='updatehead';
	document.getElementById('detail').style.display='block';
	loaddt();
	
}

function crnodok(title,ev)
{
    content= "<div id=formnodok style=\"max-height:300px;width:100%;overflow:auto;\"></div>";
    title='Find';
    height='';
    width='';
    showDialog2(title,content,width,height,ev);	
    getnodok();
}

function getnodok(){
    param='method=getnodok';
    tujuan = 'pabrikasi_slave_penagihan.php';
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
	kdbuyer=document.getElementById('kdbuyer').value;
    param='method=getnodok'+'&carilistnodok='+carilistnodok+'&kdso='+kdso+'&kdbuyer='+kdbuyer;
    tujuan = 'pabrikasi_slave_penagihan.php';
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

function movedatadok(nodo){
    document.getElementById('nodo').value=nodo;
    closeDialog();
	listdo();
}