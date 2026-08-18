// JavaScript Document

function add_new_data()
{
	param='proses=CekData';
	//alert(param);
	tujuan='kebun_slave_defisitair.php';
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
						//	alert(con.responseText);
						document.getElementById('headher').style.display='block';
						document.getElementById('listData').style.display='none';
						bersih();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	 	
}
	function displayList()
	{
		document.getElementById('headher').style.display='none';
		document.getElementById('listData').style.display='block';
		document.getElementById('unitOrg').value='';
		loadData();
	}

function bersih()
{
	document.getElementById('kodeOrg').value='';
	setValue2('kodeOrg',null);
	document.getElementById('tgl').value='';
	document.getElementById('cttn').value='';
	document.getElementById('kodeOrg').disabled=false;
	document.getElementById('tgl').disabled=false;
	document.getElementById('pg').value='0';
	document.getElementById('sg').value='0';
	document.getElementById('sr').value='0';
	document.getElementById('ml').value='0';
}

function cancelSave()
{
	bersih();
	displayList();
}
function loadData()
{
	param='proses=LoadData';
	tujuan='kebun_slave_defisitair.php';
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
							//alert(con.responseText);
							document.getElementById('contain').innerHTML=con.responseText;
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
		param='proses=LoadData';
		param+='&page='+num;
		tujuan = 'kebun_slave_defisitair.php';
		post_response_text(tujuan, param, respog);			
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('contain').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}
function saveData()
{
	kdorg=document.getElementById('kodeOrg').value;
	daTpagi=document.getElementById('pg').value;
	daTsiang=document.getElementById('sg').value;
	daTsore=document.getElementById('sr').value;
	daTmalam=document.getElementById('ml').value;
	note=document.getElementById('cttn').value;
	daTtgl=document.getElementById('tgl').value;
	pros=document.getElementById('proses').value;
	param='kdOrg='+kdorg+'&daTpagi='+daTpagi+'&daTsiang='+daTsiang+'&daTsore='+daTsore+'&daTmalam='+daTmalam+'&note='+note+'&daTtgl='+daTtgl+'&proses='+pros;
	// alert(param);
	tujuan='kebun_slave_defisitair.php';
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
							//alert(con.responseText);
							displayList();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
}
function fillField(kdorg,tgl)
{
	kdORg=kdorg;
	daTtgl=tgl;
	document.getElementById('proses').value='update';
	param='kdOrg='+kdORg+'&daTtgl='+daTtgl+'&proses=showData';
	//alert(param);
	tujuan='kebun_slave_defisitair.php';
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
						//	alert(con.responseText);
							document.getElementById('headher').style.display='block';
							document.getElementById('listData').style.display='none';
							ar=con.responseText.split("###");
							//alert(ar[0]+ar[1]+ar[2]);
							document.getElementById('kodeOrg').value=kdorg;
							setValue2('kodeOrg',kdorg);
							document.getElementById('tgl').value=daTtgl;
							setValue2('tgl',tgl);

							document.getElementById('cttn').value=ar[0];
							document.getElementById('kodeOrg').disabled=true;
							document.getElementById('tgl').disabled=true;
							document.getElementById('pg').value=ar[1];
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	
}
function deldata(kdorg,tgl)
{
	kdORg=kdorg;
	daTtgl=tgl;
	param='kdOrg='+kdORg+'&daTtgl='+daTtgl+'&proses=delData';
	//alert(param);
	tujuan='kebun_slave_defisitair.php';
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
						//	alert(con.responseText);
							displayList();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	 if(confirm("Are You Sure Want Delete This Data"))
	 	post_response_text(tujuan, param, respog);
}
// function printPDF(kdorg,tgl,ev) {
//     // Prep Param
// 	kdORg=kdorg;
// 	daTtgl=tgl;
// 	param='kdOrg='+kdORg+'&daTtgl='+daTtgl;
//     param += "&proses=pdf";
    
//     showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
//         " src='kebun_curahHujanPdf.php?"+param+"'></iframe>",'800','400',ev);
//     var dialog = document.getElementById('dynamic1');
//     dialog.style.top = '50px';
//     dialog.style.left = '15%';
// }
function cariCurah()
{
	kdOrg=document.getElementById('unitOrg').value;
	daTtgl=document.getElementById('tgl_cari').value;
	param='kdOrg='+kdOrg+'&daTtgl='+daTtgl+'&proses=cariData';
	//alert(param);
	tujuan='kebun_slave_defisitair.php';
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
						//	alert(con.responseText);
							document.getElementById('contain').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	
}