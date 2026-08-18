function batallist(){
	document.location.reload();
}

function batalmandor1(){
	document.location.reload();
}

function batalmandor(){
	document.location.reload();
}



function gettotal(no){
	denda=document.getElementById('denda'+no).value;
	premi=document.getElementById('premi'+no).innerHTML;
	denda=remove_comma_var(denda);
	premi=remove_comma_var(premi);
	if(denda==''){
		denda=0;
	}
	premitotal=parseFloat(premi)-parseFloat(denda);
	document.getElementById('premitotal'+no).innerHTML=numberFormat(premitotal);
}

function gettotal(no){
	denda=document.getElementById('denda'+no).value;
	premi=document.getElementById('premi'+no).innerHTML;
	denda=remove_comma_var(denda);
	premi=remove_comma_var(premi);
	if(denda==''){
		denda=0;
	}
	premitotal=parseFloat(premi)-parseFloat(denda);
	document.getElementById('premitotal'+no).innerHTML=numberFormat(premitotal);
}

function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}



function batal(){
    document.getElementById('printContainer').innerHTML='';	
}

maxf=0
sekarang=1;
function saveAll(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}

function loopsave(currRow,maxRow){
	tgl=document.getElementById('tgl').value;
	unit=document.getElementById('unit').value;
	jabatan=document.getElementById('jabatan'+currRow).innerHTML;
	denda=document.getElementById('denda'+currRow).value;
	premi=document.getElementById('premi'+currRow).innerHTML;
	mandor=document.getElementById('mandor'+currRow).innerHTML;
	premitotal=document.getElementById('premitotal'+currRow).innerHTML;
	pembagi=document.getElementById('pembagi'+currRow).innerHTML;
	premiawal=document.getElementById('premiawal'+currRow).innerHTML;
    if(tgl=='' || unit==''){
            alert("Data tidak lengkap");return;
    }  else{  
        param='tgl='+tgl+'&jabatan='+jabatan+'&unit='+unit+'&denda='+denda+'&premi='+premi+'&mandor='+mandor+'&premitotal='+premitotal+'&pembagi='+pembagi+'&premiawal='+premiawal;
        param+="&proses=savedata";
            tujuan = 'kebun_slave_save_premimandoran.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('row'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					unlockScreen();
                } else {
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						document.location.reload();
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


/*******************************************************************
********************************************************************
********************************************************************/
maxfsatu=0
sekarangsatu=1;
function saveAllsatu(maxRow){     
	maxfsatu=maxRow;
	loopsavesatu(1,maxRow);
}

function loopsavesatu(currRow,maxRow){
	tgl=document.getElementById('tglsatu').value;
	unit=document.getElementById('unitsatu').value;
	jabatan=document.getElementById('jabatan'+currRow).innerHTML;
	denda=document.getElementById('denda'+currRow).value;
	premi=document.getElementById('premi'+currRow).innerHTML;
	mandor=document.getElementById('mandor'+currRow).innerHTML;
	premitotal=document.getElementById('premitotal'+currRow).innerHTML;
	pembagi=document.getElementById('pembagi'+currRow).innerHTML;
	premiawal=document.getElementById('premiawal'+currRow).innerHTML;
    if(tgl=='' || unit==''){
            alert("Data tidak lengkap");return;
    }  else{  
        param='tgl='+tgl+'&jabatan='+jabatan+'&unit='+unit+'&denda='+denda+'&premi='+premi+'&mandor='+mandor+'&premitotal='+premitotal+'&pembagi='+pembagi+'&premiawal='+premiawal;
        param+="&proses=savedata";
            tujuan = 'kebun_slave_save_premimandoran.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('row'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					unlockScreen();
                } else {
                    currRow+=1;
                    sekarangsatu=currRow;
                    if(currRow>maxRow){
						alert('Done');
						document.location.reload();
					} else {
						loopsavesatu(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}




//kerani


/*******************************************************************
********************************************************************
********************************************************************/
maxfkerani=0
sekarangkerani=1;
function saveAllkerani(maxRow){     
	maxfkerani=maxRow;
	loopsavekerani(1,maxRow);
}

function loopsavekerani(currRow,maxRow){
	tgl=document.getElementById('tglkerani').value;
	unit=document.getElementById('unitkerani').value;
	jabatan=document.getElementById('jabatan'+currRow).innerHTML;
	denda=document.getElementById('denda'+currRow).value;
	premi=document.getElementById('premi'+currRow).innerHTML;
	mandor=document.getElementById('mandor'+currRow).innerHTML;
	
	premitotal=document.getElementById('premitotal'+currRow).innerHTML;
	//pembagi=document.getElementById('pembagi'+currRow).innerHTML;
	//premiawal=document.getElementById('premiawal'+currRow).innerHTML;
    if(tgl=='' || unit==''){
            alert("Data tidak lengkap");return;
    }  else{  
        param='tgl='+tgl+'&jabatan='+jabatan+'&unit='+unit+'&denda='+denda+'&premi='+premi+'&mandor='+mandor+'&premitotal='+premitotal;
        param+="&proses=savedata";
            tujuan = 'kebun_slave_save_premimandoran.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('row'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					unlockScreen();
                } else {
                    currRow+=1;
                    sekarangkerani=currRow;
                    if(currRow>maxRow){
						alert('Done');
						document.location.reload();
					} else {
						loopsavekerani(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function del(tanggal,karyid,jabatanlist,unitlist){
   	param='proses=delete'+'&tanggal='+tanggal+'&karyid='+karyid+'&jabatanlist='+jabatanlist+'&unitlist='+unitlist;
    tujuan='kebun_slave_premimandoranlistdelete.php';
    if(confirm(' Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else{
					zPreview('kebun_slave_premimandoranlist','##tgl1list##tgl2list##unitlist##jabatanlist##afdlist','printContainerlist');
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}
