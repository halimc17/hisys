












maxf=0
sekarang=1;
function savedata(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}



function loopsave(currRow,maxRow) {
    param = "";
	kodeorg=trim(document.getElementById('kodeorg').value);
	jenis=trim(document.getElementById('jenis').value);
	kodeorgtujuan=trim(document.getElementById('kodeorgtujuan'+currRow).value);
	akunhutang=trim(document.getElementById('akunhutang'+currRow).value);
	akunpiutang=trim(document.getElementById('akunpiutang'+currRow).value);
	
	param+='&method=savedata'+'&kodeorg='+kodeorg+'&jenis='+jenis+'&kodeorgtujuan='+kodeorgtujuan;
	param+='&akunhutang='+akunhutang+'&akunpiutang='+akunpiutang;
	// alert(param);return;
	tujuan = 'keu_5caco_slave.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='';
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
	// document.getElementById('row'+currRow).style.backgroundColor='';
	// document.getElementById('row'+currRow).style.display='none';
  
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					 document.getElementById('row'+currRow).style.backgroundColor='red';
					unlockScreen();
                } else {
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						fillfield(kodeorg);
                    } else {
						loopsave(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}



function getPage(){
	pg      = document.getElementById('pages');
	pg      = pg.options[pg.selectedIndex].value;
	paged   = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(num){
    kodeorg      = document.getElementById('kodeorgsch').value;
    param   ='method=loaddata&page=' + num;
	param  +='&kodeorg=' + kodeorg;
    tujuan  ='keu_5caco_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    // document.getElementById('addNew').style.display ='none';
                    document.getElementById('listdata').style.display ='block';
                    document.getElementById('container').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  
}

function fillfield(kodeorg){
    // document.getElementById('addNew').style.display ='block';
    document.getElementById('listdata').style.display ='none';
    document.getElementById('input').style.display ='block';
	
	
	
    // document.getElementById('method').value='update';

	param ='method=input&kodeorg=' + kodeorg;
	jenis = document.getElementById('jenis');
	if (jenis) {
		param += '&jenis=' + jenis.value;
	}
	// alert(param);
	
    tujuan  ='keu_5caco_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					document.getElementById('input').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  
}


function displayList(){
    document.getElementById('listdata').style.display ='block';
    document.getElementById('input').style.display ='none';
    document.getElementById('kodeorgsch').value='';
    loaddata(0);
}
















function createNew(){
    // document.getElementById('addNew').style.display ='block';
    document.getElementById('listdata').style.display ='none';
    document.getElementById('method').value ='insert';
    batalcari();
    hapus();
}

function batalcari(){
	document.getElementById('kodesch').value='';
	document.getElementById('kodetujuansch').value=''; 
}

function hapus(){
	document.getElementById('kode').value='';
	document.getElementById('kodetujuan').value='';
	document.getElementById('akunpiutang').value=''; 
	document.getElementById('akunhutang').value=''; 
	document.getElementById('method').value='insert';
    document.getElementById('kode').disabled=false;
    document.getElementById('kodetujuan').disabled=false;
}


function cekdata(){
    kode        = document.getElementById('kode').value;
    kodetujuan  = document.getElementById('kodetujuan').value;

    param       ='kd='+kode+'&kdtujuan='+kodetujuan+'&method=cekdata';
    tujuan      ='keu_5caco_slave.php';
    post_response_text(tujuan,param,respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function simpan(){
    kode        =trim(document.getElementById('kode').value);
    kodetujuan 	=document.getElementById('kodetujuan').value;
    akunpiutang	=trim(document.getElementById('akunpiutang').value);
    akunhutang	=trim(document.getElementById('akunhutang').value);
    method      =document.getElementById('method').value;
    
    if(kode=='')
    {
        alert('Please fill Code');return;
	}
	else if(kodetujuan=='')
    {
        alert('Please choose Destination Code');return;
	}
	else if(akunpiutang=='')
    {
        alert('Please choose Accounts Receivable');return;
    }
    else if(akunhutang=='')
    {
        alert('Please choose Debt account');return;
    }
	
	param   ='kode='+trim(kode)+'&kodetujuan='+kodetujuan+'&akunpiutang='+trim(akunpiutang)+'&akunhutang='+trim(akunhutang)+'&method='+method;
    tujuan  ='keu_5caco_slave.php';
    post_response_text(tujuan, param, respog);		

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					displayList();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function del(kode,kodetujuan){
    param   ='method=delete'+'&kode='+kode+'&kdtujuan='+kodetujuan;
    tujuan  ='keu_5caco_slave.php';
    if(confirm("Delete data for Code "+kode+" and Destination Code "+kodetujuan+"?"))
    {
        post_response_text(tujuan, param, respog);	
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    hapus();
                    document.getElementById('container').innerHTML=con.responseText;
                    alert("Data has been deleted !!!");
                    loaddata(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
                hapus();
            }
        }	
    }	
}