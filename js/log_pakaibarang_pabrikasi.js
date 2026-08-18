function batalht(){
	document.getElementById('detail').style.display='none';
	document.getElementById('notran').value='';
	document.getElementById('gudang').value='';
	document.getElementById('tgl').value='';
	document.getElementById('kdpab').value='';
	document.getElementById('nmpab').value='';
	
}

function editht(notran,gudang,tgl,kdpab,nmpab){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('gudang').value=gudang;
    document.getElementById('tgl').value=tgl;
    document.getElementById('kdpab').value=kdpab;
	document.getElementById('nmpab').value=nmpab;
	document.getElementById('notran').value=notran;
	list();
}



maxf=0
sekarang=1;
function saveall(maxRow){    
    maxf=maxRow;
    loopsave(1,maxRow);
}
function loopsave(currRow,maxRow){
    kdpab=document.getElementById('kdpab').value;
	tgl=document.getElementById('tgl').value;
    kdbrg=document.getElementById('kdbrg'+currRow).innerHTML;
    qty=document.getElementById('qty'+currRow).value;
	qtyawal=document.getElementById('qtyawal'+currRow).innerHTML;
	qtysave=document.getElementById('qtysave'+currRow).innerHTML;
	harsat=document.getElementById('harsat'+currRow).innerHTML;
	notran=document.getElementById('notran').value;
	gudang=document.getElementById('gudang').value;
	
	
    if (document.getElementById('cek'+currRow).checked == true){
        cek = 1;
    }
    else{
        cek = 0;
    }
	
	
	// if(cek==1){
		// qtycek=parseFloat(qty)+parseFloat(qtysave);
		// if(parseFloat(qtycek)>parseFloat(qtyawal)){
			// alert('Jumlah melebihi dari cutoff');return;
		// }
	// }
	
    param = 'method=savedata' + '&kdpab=' + kdpab + '&kdbrg=' + kdbrg + '&qty=' + qty+ '&cek=' + cek+ '&tgl=' + tgl+ '&harsat=' + harsat+ '&notran=' + notran;
    param+='&gudang=' + gudang + '&qtyawal=' + qtyawal + '&qtysave=' + qtysave;
	tujuan = 'log_slave_pakaibarang_pabrikasi.php';
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
                }
                else {
                    //document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow) {
                            alert('Done');
							batalht();
                            //list(nopdo,unit,per);  
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




// function getnotran(){
	
// }

function list(){
	notran=document.getElementById('notran').value;
	gudang=document.getElementById('gudang').value;
	tgl=document.getElementById('tgl').value;
	kdpab=document.getElementById('kdpab').value;
    param='method=list'+'&kdpab='+kdpab+'&notran='+notran+'&gudang='+gudang+'&tgl='+tgl;
    tujuan = 'log_slave_pakaibarang_pabrikasi.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					arr = con.responseText.split("###");
					document.getElementById('detail').style.display='block';
                    document.getElementById('notran').value=arr[0];
					document.getElementById('listdetail').innerHTML=arr[1];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}


function deletehead(notran){
	param = 'method=deletehead' + '&notran=' + notran;
    tujuan = 'log_slave_pakaibarang_pabrikasi.php';
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
	document.getElementById('notransch').value='';
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
    tujuan = 'log_slave_pakaibarang_pabrikasi.php';
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














function kdso(title,ev)
{
    content= "<div id=formkdso style=\"height:250px;width:350;overflow:scroll;\"></div>";
    title='Add Transaction';
    height='250';
    width='350';
    showDialog2(title,content,width,height,ev);	
    getkdso();
}

function getkdso()
{
    param='method=getkdso';
    tujuan = 'log_slave_pakaibarang_pabrikasi.php';
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
  
    tujuan = 'log_slave_pakaibarang_pabrikasi.php';
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


function movedatakdso(kdpab,nmpab,total){
    document.getElementById('kdpab').value=kdpab;
	document.getElementById('nmpab').value=nmpab;
    document.getElementById('listkdso').style.display='none';
    closeDialog();	
}
function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
	batalht();
}


