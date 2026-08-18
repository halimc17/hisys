maxf=0
sekarang=1;
function editAll(maxRow)
{     
	maxf=maxRow;
	loopsave(1,maxRow);
}

function loopsave(currRow,maxRow)
{
	notranDet=trim(document.getElementById('notranDet'+currRow).innerHTML);
	nobpbDet=trim(document.getElementById('nobpbDet'+currRow).innerHTML);
	nopoDet=trim(document.getElementById('nopoDet'+currRow).innerHTML);
	kodebarangDet=trim(document.getElementById('kodebarangDet'+currRow).innerHTML);
	jumlah=trim(document.getElementById('jumlah'+currRow).value);
	
	param='notranDet='+notranDet+'&nobpbDet='+nobpbDet+'&nopoDet='+nopoDet+'&kodebarangDet='+kodebarangDet+'&jumlah='+jumlah;
	param+="&method=updateAll";
	
	tujuan = 'log_slave_packing.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
		//lockScreen('wait');
	
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
					//document.getElementById('row'+currRow).style.display='none';//ini untuk menghilangkan/memunculkan data telah tersimpan
                    currRow+=1;
					sekarang=currRow;
                    if(currRow>maxRow)
					{
						alert('Done');
						loadDataDetail();
						//document.location.reload();
						//document.getElementById('infoDisplay').innerHTML='';
					}  
					else
					{
						loopsave(currRow,maxRow);
					}
				}
			}
			else {
				busy_off();
				error_catch(con.status);
                               // document.getElementById('lanjut').style.display='';
				//unlockScreen();
			}
		}
	}		
	
}


function saveHeader(){
	notransaksi=document.getElementById('notran').value;
	unit=document.getElementById('unit').value;
	tgl=document.getElementById('tgl').value;
	ket=document.getElementById('ket').value;
	peti=document.getElementById('peti').value;
	serah=document.getElementById('serah').value;
	terima=document.getElementById('terima').value;
	method=document.getElementById('method').value;
	
	param='&notransaksi='+notransaksi+'&unit='+unit+'&tgl='+tgl;
	param+='&ket='+ket+'&peti='+peti+'&serah='+serah+'&terima='+terima+'&method='+method;	
	
	tujuan='log_slave_packing.php';
	post_response_text(tujuan, param, respog);	
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('detailForm').style.display='block';
					document.getElementById('notran').value=con.responseText;
				}
			}else{
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
	loadData(paged);
}

function loadData(pg){
	srcunit=document.getElementById('srcunit').value;
	srcperiode=document.getElementById('srcperiode').value;
	srcnotrans=document.getElementById('srcnotrans').value;
	srcnopr=document.getElementById('srcnopr').value;
	srcnopo=document.getElementById('srcnopo').value;
	
	param='method=loadData'+'&srcunit='+srcunit+'&srcperiode='+srcperiode+'&srcnotrans='+srcnotrans+'&srcnopr='+srcnopr+'&srcnopo='+srcnopo+'&page='+pg;
	tujuan='log_slave_packing.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}  
}

function delHead(notransaksi){
	param='method=delHead'+'&notransaksi='+notransaksi;
	tujuan='log_slave_packing.php';
	post_response_text(tujuan, param, respog);	
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData(0);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function loadDataDetail(){
	notran=document.getElementById('notran').value;
	param='method=loadDetail'+'&notran='+notran;
	tujuan='log_slave_packing.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('containList').style.display='block';
					document.getElementById('containList').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function edit(notran,unit,tgl,peti,ket,serah,terima){
	tabAction(document.getElementById('tabFRM0'),0,'FRM',1);	
	
	document.getElementById('header').style.display='block';
	document.getElementById('notran').value=notran;
	document.getElementById('unit').value=unit;
	document.getElementById('unit').disabled=true;
	document.getElementById('tgl').value=tgl;
	document.getElementById('tgl').disabled=true;
	document.getElementById('peti').value=peti;
	document.getElementById('ket').value=ket;
	document.getElementById('serah').value=serah;
	document.getElementById('terima').value=terima;
	document.getElementById('method').value='update';
	document.getElementById('detailForm').style.display='block';
	loadDataDetail();	
}

function goCariPo(){
	notransaksi=document.getElementById('notran').value;
	nopo=trim(document.getElementById('noPo').value);
	unit=document.getElementById('unit').value;
	if(noPo.length<2){   
		alert('Text too short');
		return;
	}else{   
		param='method=goCariPo'+'&nopo='+nopo+'&unit='+unit+'&notransaksi='+notransaksi;
		tujuan = 'log_slave_packing.php';
		post_response_text(tujuan, param, respog);			
	}
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('containercari').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function posting(notransaksi){
	
	param='method=posting'+'&notransaksi='+notransaksi;
	tujuan='log_slave_packing.php';
	if(confirm('Are you sure posting this transaction,'+notransaksi+'??'))
		post_response_text(tujuan, param, respog);
		
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					loadData(0);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function DelDetail(notran,nobpb,nopo,kodebarang,norefrensi,jumlah){
	param='method=deleteDetail'+'&notran='+notran+'&nobpb='+nobpb+'&nopo='+nopo+'&kodebarang='+kodebarang+'&norefrensi='+norefrensi+'&jumlah='+jumlah;
	tujuan='log_slave_packing.php';
	post_response_text(tujuan, param, respog);	
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadDataDetail();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}


function saveDetail(notransaksi,nopo){
	if(notransaksi==''){
		alert('Some field was empty');return;
	}
	param='method=saveDetail'+'&notransaksi='+notransaksi+'&nopo='+nopo;
	tujuan='log_slave_packing.php';
	post_response_text(tujuan, param, respog);	
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadDataDetail();
					closeDialog();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}



/////////////////////////////////find barang di input barang


function cariBarang(title,ev)
{
	 // kosongkan();
	  //setSloc('simpan');
	  content= "<div>";
	  content+="<fieldset style=width:95%>Search : <input type=text id=txtBarang class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariBarang()>Search</button> </fieldset><fieldset><legend><i>Result</i></legend>";
	  content+="<div id=containercari style=\"max-height:250px;max-width:470px;overflow:auto;\"></div></fieldset></div>";
 //display window
	 title=title+'';
	   width='';
	   height='';
	   showDialog5(title,content,width,height,ev);	
}


function goCariBarang()
{

	txtBarang=trim(document.getElementById('txtBarang').value);
	if(txtBarang.length<1)
	   alert('Text too short');
	else
	{   
	param='method=goCariBarang'+'&txtBarang='+txtBarang;
   
	tujuan = 'log_slave_packing.php';
	post_response_text(tujuan, param, respog);			
	}
	function respog(){
			if (con.readyState == 4) {
					if (con.status == 200) {
							busy_off();
							if (!isSaveResponse(con.responseText)) {
									alert(con.responseText);
							}
							else {
									document.getElementById('containercari').innerHTML=con.responseText;
							}
					}
					else {
							busy_off();
							error_catch(con.status);
					}
			}
	}	
}


function goPickBarang(kodebarang,namabarang,satuan)
{
	document.getElementById('kodebarang').value=kodebarang;
	document.getElementById('namabarang').value=namabarang;
	document.getElementById('satuan').value=satuan;
	closeDialog5();
	//document.getElementById('').innerHTML=con.responseText;
	//document.getElementById('listCariBarang').style.display='none';
}





function cancelFormBarang()
{
	document.getElementById('nobpb').value='';
	document.getElementById('nopo').value='';
	document.getElementById('nopp').value='';
	document.getElementById('kodebarang').value='';
	document.getElementById('kurs').value='';
	document.getElementById('namabarang').value='';
	document.getElementById('jumlah').value='';
	document.getElementById('satuan').value='';
	document.getElementById('matauang').value='IDR';
	document.getElementById('hargasatuan').value='';
	
}


function saveFormBarang()
{

	//alert('MASUK');
	notran=document.getElementById('notran').value;
	
	nobpb=document.getElementById('nobpb').value;
	nopo=document.getElementById('nopo').value;
	nopp=document.getElementById('nopp').value;
	kodebarang=document.getElementById('kodebarang').value;
	jumlah=document.getElementById('jumlah').value;
	satuan=document.getElementById('satuan').value;
	matauang=document.getElementById('matauang').value;
	hargasatuan=document.getElementById('hargasatuan').value;
                    kurs=document.getElementById('kurs').value;
	method=document.getElementById('method').value;

	//param='kodeproject='+kodeproject+'&kodekegiatan='+kodekegiatan+'&kodeBarangForm='+kodeBarangForm+'&jumlahBarangForm='+jumlahBarangForm+'&method='+saveFormBarang;
	param='method=saveFormBarang'+'&notran='+notran+'&nobpb='+nobpb+'&nopo='+nopo+'&nopp='+nopp;
	param+='&kodebarang='+kodebarang+'&jumlah='+jumlah+'&satuan='+satuan+'&matauang='+matauang;		
	param+='&hargasatuan='+hargasatuan+'&kodebarang='+kodebarang+'&jumlah='+jumlah+'&kurs='+kurs;		
	
	tujuan = 'log_slave_packing.php';
	
	//alert(tujuan);
    post_response_text(tujuan, param, respog);		




	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							//alert(con.responseText
							cancelFormBarang();
							loadDataDetail();
							
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
	
}

//////////////////////////////




//////////////////////////////////////////////////////////////INPUT BARANG

function inputBarang(title,ev)
{
	notran=document.getElementById('notran').value;
	content= "<div id=formBarang style=\";max-width:800px;\">";
	content+="<input type=hidden id=tampung  value="+ notran +" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25>";
	//content+="<div id=formCariBarang></div>";
	title='No_Transaksi : '+notran;
	width='';
	height='';
	showDialog1(title,content,width,height,ev);	
	getFormBarang(notran);
}

function getFormBarang(notran)
{
	param='method=getFormBarang'+'&notran='+notran;
	//alert(param);
	tujuan = 'log_slave_packing.php';
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


/*function cariBarang(title,ev)
{
	notran=document.getElementById('notran').value;
	content= "<div>";
	content+="<fieldset>Barang:<input type=text id=txtBarang class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariBarang()>Go</button>";
	content+="<input type=hidden id=tampung  value="+ notran +" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25> </fieldset>";
	content+="<div id=containercari style=\"height:300px;width:735px;overflow:scroll;\"></div></div>";
	title=title+' Barang:';
	width='750';
	height='350';
	showDialog1(title,content,width,height,ev);	
}

function goCariBarang()
{
	//alert('a');
	tampung=document.getElementById('tampung').value;
	txtBarang=trim(document.getElementById('txtBarang').value);
	kdOrg=document.getElementById('kdOrg').value;
	pt=document.getElementById('pt').value;
	if(txtBarang.length<4)
	{  
		alert('Text too short');
		return;
	}
	else
	{   
		param='method=goCariBarang'+'&pt='+pt+'&tampung='+tampung+'&kdOrg='+kdOrg+'&txtBarang='+txtBarang;
		tujuan = 'log_slave_packing.php';
		post_response_text(tujuan, param, respog);			
	}
	//alert(param);
	function respog(){
			if (con.readyState == 4) {
					if (con.status == 200) {
							busy_off();
							if (!isSaveResponse(con.responseText)) {
									alert(con.responseText);
							}
							else {
									
									document.getElementById('containercari').innerHTML=con.responseText;
							}
					}
					else {
							busy_off();
							error_catch(con.status);
					}
			}
	}	
}*/




//////////////////////////////////////////////////////////////PO


function cariNoPo(title,ev)
{
	notran=document.getElementById('notran').value;
	content= "<div>";
	content+="<fieldset style=float:left;height:50px><legend><b>Find No. PO</b></legend>Search : <input type=text id=noPo class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariPo()>Go</button>";
	content+="<input type=hidden id=tampung  value="+ notran +" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25> ";
	content+="</fieldset><fieldset style=height:50px><legend>Note</legend>Jika field jumlah berwarna orange, maka barang tersebut tidak bisa diinput dikarenakan jumlah barang tersebut sudah dikirim melalui PL lain</fieldset>";
	content+="<input type=hidden id=tampung  value="+ notran +" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25> </fieldset>";
	content+="<fieldset><legend><b>List Data</b></legend><div id=containercari style=\"max-height:350px;max-width:750;overflow:auto;\"></div></div>";
	title=title+' PO:';// style=\"height:300px;width:1175px;overflow:scroll;\"
	width='';
	height='';
	showDialog1(title,content,width,height,ev);	
}


function updateDetail(notran,nobpb,nopo,kodebarang,no,jumbpb,jumterkirim)
{
	
	jumlah=parseFloat(document.getElementById('jumlah'+no).value);
	jbpb=parseFloat(jumbpb);
	jkirim=parseFloat(jumterkirim);
	
	
	
	if((jumlah+jkirim)>jbpb)
	{
		alert('jumlah melebihi');return;
	}
	
	param='method=updateDetail'+'&notran='+notran+'&nobpb='+nobpb+'&nopo='+nopo+'&kodebarang='+kodebarang+'&jumlah='+jumlah;
	//alert(param);
	tujuan='log_slave_packing.php';
	post_response_text(tujuan, param, respog);	
	function respog()
	{
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else 
					{
						
						//lockHeader();
						//document.getElementById('detailForm').style.display='block';
						loadDataDetail();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}





function cancel()
{
	document.location.reload();
}


/*function cancelDetail()
{
	tabAction(document.getElementById('tabFRM0'),0,'FRM',1);	
}
*/
function getlistgudang(e){
	var sloc = document.getElementById('kodeorg');
	var val = e.options[e.selectedIndex].value;
	param='method=getlistgudang'+'&pt='+val;
	tujuan='log_slave_packing.php';
	post_response_text(tujuan, param, respog);	
	function respog()
	{
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else 
					{
						sloc.innerHTML = con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
}
























function cariBast(num)
{
	kdPtSch=document.getElementById('kdPtSch').value;
	perSch=document.getElementById('perSch').value;
	notrn=document.getElementById('notransCari').value;
	param='method=loadData'+'&kdPtSch='+kdPtSch+'&perSch='+perSch+'&page='+num;
        param+='&notransCari='+notrn;
	tujuan = 'log_slave_packing.php';
	post_response_text(tujuan, param, respog);			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					//displayList();
					
					document.getElementById('container').innerHTML=con.responseText;
					//loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}






function lockHeader()
{
	document.getElementById('saveHeader').disabled=true;
	document.getElementById('cancelHeader').disabled=true;
	document.getElementById('notran').disabled=true;
	document.getElementById('pt').disabled=true;
	document.getElementById('kodeorg').disabled=true;
	document.getElementById('tgl').disabled=true;
	document.getElementById('ket').disabled=true;
	document.getElementById('peti').disabled=true;
	document.getElementById('serah').disabled=true;
	document.getElementById('terima').disabled=true;
	
}



function clearDetail()
{	
	document.getElementById('nopokok').value='';
	document.getElementById('jjgpanen').value='';
	document.getElementById('jjgtdkpanen').value='';
	document.getElementById('jjgtdkkumpul').value='';
	document.getElementById('jjgmentah').value='';
	document.getElementById('jjggantung').value='';
	document.getElementById('brdtdkdikutip').value='';
	
	document.getElementById('rumpukan').value='';
	document.getElementById('piringan').value='';
	document.getElementById('jalurpanen').value='';
	document.getElementById('tukulan').value='';
	//document.getElementById('rumpukan').checked==false;
}


