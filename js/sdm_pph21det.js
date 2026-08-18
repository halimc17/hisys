// JavaScript Document
function batalpph21det()
{
	document.getElementById('method').value='insert';
	document.getElementById("jenis").value = 'regular';
	document.getElementById('jenis').disabled=false;
	document.getElementById('nilai').value=0;


	document.getElementById('unit').disabled=false;
	document.getElementById('periode').disabled=false;
	document.getElementById('karyawanid').disabled=false;
	document.getElementById('jenis').disabled=false;
	document.getElementById('periode').value='';
	document.getElementById('karyawanid').value='';
	document.getElementById('jenis').value='regular';
	document.getElementById('nilai').value=0;
	document.getElementById('method').value='insert';
}

function loadData(num){
	param='method=loaddata'+'&page='+num;
	tujuan='sdm_slave_pph21det.php';
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
							document.getElementById('container').innerHTML=con.responseText;
							batalpph21det();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(unit,periode,karyawanid,jenis,nilai){

	gantix(unit,periode,karyawanid,jenis,nilai);
}
function edit(unit,periode,karyawanid,jenis,nilai){
	document.getElementById('unit').disabled=true;
	document.getElementById('periode').disabled=true;
	document.getElementById('karyawanid').disabled=true;
	document.getElementById('jenis').disabled=true;
	document.getElementById('unit').value=unit;
	document.getElementById('periode').value=periode;
	document.getElementById('karyawanid').value=karyawanid;
	document.getElementById('jenis').value=jenis;
	document.getElementById('nilai').value=nilai;
	document.getElementById('method').value='edit';
}

function simpatpph21det()
{
	unit=document.getElementById('unit').value;
	periode=document.getElementById('periode').value;
	karyawanid=document.getElementById('karyawanid').value;
	jenis=document.getElementById('jenis').value;
	nilai=document.getElementById('nilai').value;
	method=trim(document.getElementById('method').value);
	
	param='jenis='+jenis+'&nilai='+nilai+'&unit='+unit+'&periode='+periode+'&karyawanid='+karyawanid+'&method='+method;
	tujuan='sdm_slave_pph21det.php';
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
							document.getElementById('container').innerHTML=con.responseText;
							batalpph21det();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function gantix(unit,periode,karyawanid,jenis,nilai)
{
	if(unit=='')
	{
	unit=document.getElementById('unit').value;	
	}
	method='gantix';
	
	param='unit='+unit+'&method='+method;
	tujuan='sdm_slave_pph21det.php';
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
							datax=con.responseText.split('###');
							document.getElementById('periode').innerHTML=datax[0];
							document.getElementById('karyawanid').innerHTML=datax[1];
							if(periode!='')
							{
								edit(unit,periode,karyawanid,jenis,nilai);
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



