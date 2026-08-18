//########################################################
//###################  T A B   P N N  ####################
//########################################################


function prevpnn(){
	
	norkb=document.getElementById('norkb').value;
    unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
    
    //noupah=document.getElementById('noupah').value;
    // divisiupah=document.getElementById('divisiupah').value;
    // tkupah=document.getElementById('tkupah').value;
    // tglupah=document.getElementById('tglupah').value;
    
    // if(tglupah=='' || tkupah=='' || divisiupah==''){
        // alert('Lengkapi Pengisian : Divisi, Tipe Karyawan dan Tanggal.');return;
    // }
    param = 'method=nopnn' + '&norkb=' + norkb + '&unit=' + unit + '&per=' + per;
    tujuan = 'kebun_slave_rkb.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    //document.getElementById('noupah').value=con.responseText;
                    detailpnn();    
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}




function detailpnn(){
    norkb=document.getElementById('norkb').value;
    unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
    
    divpnn=document.getElementById('divpnn').value;
    ttpnn=document.getElementById('ttpnn').value;
    // noupah=document.getElementById('noupah').value;
    // divisiupah=document.getElementById('divisiupah').value;
    // tkupah=document.getElementById('tkupah').value;
    // tglupah=document.getElementById('tglupah').value;
    
    param = 'method=detailpnn' + '&norkb=' + norkb + '&unit=' + unit + '&per=' + per;
	param +='&divpnn=' + divpnn + '&ttpnn=' + ttpnn;
	//param +='&noupah=' + noupah + '&tkupah=' + tkupah + '&tglupah=' + tglupah+ '&divisiupah=' + divisiupah;

    tujuan = 'kebun_slave_rkb.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }  else  {
                    document.getElementById('detailpnn').style.display = 'block';
                    document.getElementById('detailpnn').innerHTML=con.responseText;
					
					// document.getElementById('divisiupah').disabled=true;
					// document.getElementById('prevupah').disabled=true;
					// document.getElementById('tkupah').disabled=true;
					// document.getElementById('tglupah').disabled=true;
					
                   // listupah();
                }
            }  else  {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function totkarpnn(no){
	a=document.getElementById('pkwtpnn'+no).value;
	b=document.getElementById('pkwttpnn'+no).value;
	c=document.getElementById('khlpnn'+no).value;
	d=document.getElementById('borpnn'+no).value;
	e=parseFloat(a)+parseFloat(b)+parseFloat(c)+parseFloat(d);
    document.getElementById('tkarpnn'+no).innerHTML=parseFloat(e);	
	
}



//########################################################
//#################  T A B   R E K A P  ##################
//########################################################



function savehead(norkb,unit,per){
    norkb=document.getElementById('norkb').value;
    unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
  
    if(per=='')  {
        alert('Periode masih kosong');return;
    }
	
    param = 'method=norkb' + '&norkb=' + norkb + '&unit=' + unit + '&per=' + per;
    tujuan = 'kebun_slave_rkb.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                  
                    document.getElementById('savehead').disabled=true;
					document.getElementById('per').disabled=true;
                    document.getElementById('norkb').value=con.responseText;
                    document.getElementById('detail').style.display='block';
                    //listupah(norkb,unit,per);//ini nanti yg di ubah dibuat list2 untuk menampung load awal
                    
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function displaylist(){
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
	
	
    param = 'method=loaddata&page=' + num;
	// if (thnsch != '') 
	// {
        // param += '&thnsch=' + thnsch;
    // }
    tujuan = 'kebun_slave_rkb.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4){
            if (con.status == 200)  {
                busy_off();
                if (!isSaveResponse(con.responseText))  {
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



function edit(norkb,unit,per){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('norkb').value=norkb;
    document.getElementById('unit').value=unit;
    document.getElementById('per').value=per;
	savehead();
}


function cancel(){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('norkb').value='';
    document.getElementById('per').value='';
	document.getElementById('savehead').disabled=false;
	document.getElementById('per').disabled=false;
	
}

function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
    //document.getElementById('norkb').value='';
    //document.getElementById('per').value='';
	cancel();
}



