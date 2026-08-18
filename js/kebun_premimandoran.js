function loaddata(page) {
	prdlist = document.getElementById('prdlist').value;
	unitlist = document.getElementById('unitlist').value;
	jabatanlist = document.getElementById('jabatanlist').value;
	afdlist = document.getElementById('afdlist').value;
	param = 'proses=preview&page=' + page;
	param += '&prdlist=' + prdlist;
	param += '&unitlist=' + unitlist;
	param += '&jabatanlist=' + jabatanlist;
	param += '&afdlist=' + afdlist;
	
	
	tujuan = 'kebun_slave_premimandoranlist.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('printContainerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function gettglmdr(jenis,id){
	if(jenis=='KONTAN'){
		document.getElementById(id).style.display='';
	}else{
		document.getElementById(id).style.display='none';
	}
}
function batallist(){
	document.location.reload();
}
function inputtglkirim(e,target){
	var val = e.value;
	var targetEle = document.getElementById(target);
	targetEle.value = val;
}
function batalmandor1(){
	document.getElementById('tglsatu').value='';	
    document.getElementById('unitsatu').value='';
    document.getElementById('afdsatu').value='';
    document.getElementById('printContainersatu').innerHTML='';	
}

function batalmandor(){
	// document.location.reload();
	
	document.getElementById('prd').value='';	
    document.getElementById('unit').value='';
    document.getElementById('afd').value='';
    document.getElementById('printContainer').innerHTML='';	
}

function batalkerani(){
	document.getElementById('tglkerani').value='';	
    document.getElementById('unitkerani').value='';
    document.getElementById('afdkerani').value='';
    document.getElementById('printContainerkerani').innerHTML='';	
}

function batalkeranitrk(){
	document.getElementById('prdkeranitrk').value='';	
    document.getElementById('unitkeranitrk').value='';
    document.getElementById('afdkeranitrk').value='';
    document.getElementById('printContainerkeranitrk').innerHTML='';	
}

function gettotal(no,idpremi,iddenda,idhasil){
	denda=document.getElementById(iddenda).value;
	premi=document.getElementById(idpremi).innerHTML;
	denda=remove_comma_var(denda);
	premi=remove_comma_var(premi);
	if(denda==''){
		denda=0;
	}
	premitotal=parseFloat(premi)-parseFloat(denda);
	document.getElementById(idhasil).innerHTML=numberFormat(premitotal);
}

function gettotaltrk(no){
	denda=document.getElementById('dendatrk'+no).value;
	premi=document.getElementById('premitrk'+no).innerHTML;
	denda=remove_comma_var(denda);
	premi=remove_comma_var(premi);
	if(denda==''){
		denda=0;
	}
	premitotal=parseFloat(premi)-parseFloat(denda);
	document.getElementById('premitotaltrk'+no).innerHTML=numberFormat(premitotal);
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
	prd=document.getElementById('prd').value;
	unit=document.getElementById('unit').value;
	tahap=document.getElementById('tahap').value;
	kontanan=document.getElementById('kontanan').value;
	tglmulai=document.getElementById('tglmulai').value;
	jabatan=document.getElementById('jabatan'+currRow).innerHTML;
	denda=document.getElementById('denda'+currRow).value;
	premi=document.getElementById('premi'+currRow).innerHTML;
	mandor=document.getElementById('mandor'+currRow).innerHTML;
	premitotal=document.getElementById('premitotal'+currRow).innerHTML;
	pembagi=document.getElementById('pembagi'+currRow).innerHTML;
	premiawal=document.getElementById('premiawal'+currRow).innerHTML;
    if(prd=='' || unit==''){
           alert("Data tidak lengkap");return;
    }  else{  
        param='prd='+prd+'&jabatan='+jabatan+'&unit='+unit+'&denda='+denda+'&premi='+premi+'&mandor='+mandor+'&premitotal='+premitotal+'&pembagi='+pembagi+'&premiawal='+premiawal+'&kontanan='+kontanan;
        param+='&tglmulai='+tglmulai;
        param+='&tahap='+tahap;
		
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
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
                } else {
					document.getElementById('row' + currRow).style.display = 'none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						document.getElementById('printContainer').innerHTML='';
						loaddata(0);
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
	prd=document.getElementById('prdsatu').value;
	unit=document.getElementById('unitsatu').value;
	tahap=document.getElementById('tahapsatu').value;
	kontanan=document.getElementById('kontanansatu').value;
	tglmulai=document.getElementById('tglmulaisatu').value;
	jabatan=document.getElementById('jabatansatu'+currRow).innerHTML;
	denda=document.getElementById('dendasatu'+currRow).value;
	premi=document.getElementById('premisatu'+currRow).innerHTML;
	mandor=document.getElementById('mandorsatu'+currRow).innerHTML;
	premitotal=document.getElementById('premitotalsatu'+currRow).innerHTML;
	pembagi=document.getElementById('pembagisatu'+currRow).innerHTML;
	premiawal=document.getElementById('premiawalsatu'+currRow).innerHTML;
    if(prd=='' || unit==''){
            alert("Data tidak lengkap");return;
    }  else{  
        param='prd='+prd+'&jabatan='+jabatan+'&unit='+unit+'&denda='+denda+'&premi='+premi+'&mandor='+mandor+'&premitotal='+premitotal+'&pembagi='+pembagi+'&premiawal='+premiawal+'&kontanan='+kontanan;
		param+='&tglmulai='+tglmulai;
		param+='&tahap='+tahap;
        param+="&proses=savedata";
            tujuan = 'kebun_slave_save_premimandoran.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('rowsatu'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('rowsatu' + currRow).style.backgroundColor = 'red';
					unlockScreen();
                } else {
					document.getElementById('rowsatu' + currRow).style.display = 'none';
                    currRow+=1;
                    sekarangsatu=currRow;
                    if(currRow>maxRow){
						alert('Done');
						document.getElementById('printContainersatu').innerHTML='';
						loaddata(0);
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
maxf=0
sekarang=1;
function saveAllKerani(maxRow){     
	maxf=maxRow;
	loopsaveKerani(1,maxRow);
}

function loopsaveKerani(currRow,maxRow){
	prd=document.getElementById('prdkerani').value;
	unit=document.getElementById('unitkerani').value;
	tahap=document.getElementById('tahapkerani').value;
	kontanan=document.getElementById('kontanankerani').value;
	tglmulai=document.getElementById('tglmulaikerani').value;
	jabatan=document.getElementById('jabatankerani'+currRow).innerHTML;
	denda=document.getElementById('dendakerani'+currRow).value;
	premi=document.getElementById('premikerani'+currRow).innerHTML;
	mandor=document.getElementById('mandorkerani'+currRow).innerHTML;
	premitotal=document.getElementById('premitotalkerani'+currRow).innerHTML;
	pembagi=document.getElementById('pembagikerani'+currRow).innerHTML;
	premiawal=document.getElementById('premiawalkerani'+currRow).innerHTML;
    if(prd=='' || unit==''){
            alert("Data tidak lengkap");return;
    }  else{  
        param='prd='+prd+'&jabatan='+jabatan+'&unit='+unit+'&denda='+denda+'&premi='+premi+'&mandor='+mandor+'&premitotal='+premitotal+'&pembagi='+pembagi+'&premiawal='+premiawal+'&kontanan='+kontanan;
		param+='&tglmulai='+tglmulai;
		param+='&tahap='+tahap;
        param+="&proses=savedata";
            tujuan = 'kebun_slave_save_premimandoran.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('rowkerani'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('rowkerani' + currRow).style.backgroundColor = 'red';
					unlockScreen();
                } else {
					document.getElementById('rowkerani' + currRow).style.display = 'none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						document.getElementById('printContainerkerani').innerHTML='';
						loaddata(0);
					} else {
						loopsaveKerani(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

//mandor traksi
maxf=0
sekarang=1;
function saveAllKeranitrk(maxRow){     
	maxf=maxRow;
	loopsaveKeranitrk(1,maxRow);
}

function loopsaveKeranitrk(currRow,maxRow){
	prd=document.getElementById('prdkeranitrk').value;
	unit=document.getElementById('unitkeranitrk').value;
	kontanan=document.getElementById('kontanantrk').value;
	tglmulai=document.getElementById('tglmulaitrk').value;
	jabatan=document.getElementById('jabatan'+currRow).innerHTML;
	denda=document.getElementById('dendatrk'+currRow).value;
	premi=document.getElementById('premitrk'+currRow).innerHTML;
	mandor=document.getElementById('mandor'+currRow).innerHTML;
	premitotal=document.getElementById('premitotaltrk'+currRow).innerHTML;
	pembagi=document.getElementById('pembagitrk'+currRow).innerHTML;
	premiawal=document.getElementById('premiawaltrk'+currRow).innerHTML;
    if(prd=='' || unit==''){
            alert("Data tidak lengkap");return;
    }  else{  
        param='prd='+prd+'&jabatan='+jabatan+'&unit='+unit+'&denda='+denda+'&premi='+premi+'&mandor='+mandor+'&premitotal='+premitotal+'&pembagi='+pembagi+'&premiawal='+premiawal;
		param+='&kontanan='+kontanan;
		param+='&tglmulai='+tglmulai;
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
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
                } else {
					document.getElementById('row' + currRow).style.display = 'none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						document.getElementById('printContainerkerani').innerHTML='';
					} else {
						loopsaveKeranitrk(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function del(tanggal,karyid,jabatanlist,unitlist,tglkontanan){
   	param='proses=delete'+'&tanggal='+tanggal+'&karyid='+karyid+'&jabatanlist='+jabatanlist+'&unitlist='+unitlist+'&tglkontanan='+tglkontanan;
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
					zPreview('kebun_slave_premimandoranlist','##prdlist##unitlist##jabatanlist##afdlist','printContainerlist');
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}
