// JavaScript Document
function btldendapanen()
{
	document.getElementById('method').value='insert';
	document.getElementById("kd_org").selectedIndex = "0";
	document.getElementById('kd_org').disabled=false;
	document.getElementById('kd_denda').value='';
	document.getElementById('kd_denda').disabled=false;
	document.getElementById("jenisdenda").selectedIndex = "0";
	document.getElementById('nilaidenda').value='0';
	document.getElementById('ketdenda').value='';
}

function loadData(){
	param='method=loaddata';
	tujuan='kebun_slave_5dendapanen.php';
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
							btldendapanen();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(kd_org,kd_denda,jenisdenda,nilaidenda,ketdenda){
	Lkd_org=document.getElementById('kd_org');
    for(ard=0;ard<Lkd_org.length;ard++)
    {
        if(Lkd_org.options[ard].value==kd_org)
            {
                Lkd_org.options[ard].selected=true;
            }
    }
	document.getElementById('kd_org').disabled=true;
	document.getElementById('kd_denda').value=kd_denda;
	Ljenisdenda=document.getElementById('jenisdenda');
    for(ard=0;ard<Ljenisdenda.length;ard++)
    {
        if(Ljenisdenda.options[ard].value==jenisdenda)
            {
                Ljenisdenda.options[ard].selected=true;
            }
    }
	document.getElementById('kd_denda').disabled=true;
	document.getElementById('nilaidenda').value=nilaidenda;
	document.getElementById('ketdenda').value=ketdenda;
	document.getElementById('method').value='edit';
}

function simpadendapanen()
{
	kd_org=document.getElementById('kd_org').options[document.getElementById('kd_org').selectedIndex].value;
	kd_denda=trim(document.getElementById('kd_denda').value);
	jenisdenda=document.getElementById('jenisdenda').options[document.getElementById('jenisdenda').selectedIndex].value;
	nilaidenda=trim(document.getElementById('nilaidenda').value);
	ketdenda=trim(document.getElementById('ketdenda').value);
	method=trim(document.getElementById('method').value);
	
	param='kd_org='+kd_org+'&kd_denda='+kd_denda+'&jenisdenda='+jenisdenda+'&nilaidenda='+nilaidenda+'&ketdenda='+ketdenda+'&method='+method;
	tujuan='kebun_slave_5dendapanen.php';
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
							btldendapanen();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function deletefield(kd_org,kd_denda){
	param='kd_org='+kd_org+'&kd_denda='+kd_denda+'&method=delete';
	tujuan='kebun_slave_5dendapanen.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}