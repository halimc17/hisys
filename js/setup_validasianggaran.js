function batal(){
	document.getElementById('kodeorg').selectedIndex=0;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('jenispersetujuan').selectedIndex=0;
	document.getElementById('jenispersetujuan').disabled = false;
	document.getElementById('toleransi').value='';
	document.getElementById('status').checked = true;
	document.getElementById('method').value="insert";
	document.getElementById('myid').value="";
}

function batalcari()
{
	document.getElementById('crkodeorg').selectedIndex=0;
	document.getElementById('crjenispersetujuan').selectedIndex=0;
}

function loaddata()
{
	kodeunit=document.getElementById('crkodeorg').options[document.getElementById('crkodeorg').selectedIndex].value;
	jenispersetujuan=document.getElementById('crjenispersetujuan').options[document.getElementById('crjenispersetujuan').selectedIndex].value;
	
	param='method=loaddata&kodeunit='+kodeunit+'&jenispersetujuan='+jenispersetujuan;
    tujuan='setup_slave_validasianggaran.php';
    post_response_text(tujuan, param, respog);		
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('container').innerHTML=con.responseText;
					leftFixedTable();
					batal();
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

function editfield(myid,kodeunit,jenispersetujuan,toleransi,stt,digit){
	document.getElementById('myid').value=myid;
	document.getElementById('kodeorg').value=kodeunit;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('jenispersetujuan').value=jenispersetujuan;
	document.getElementById('jenispersetujuan').disabled = true;
	document.getElementById('toleransi').value=toleransi;
	document.getElementById('digit').value=digit;
	if(stt=='1'){
		document.getElementById('status').checked=true;
	}else{
		document.getElementById('status').checked=false;
	}
	document.getElementById('method').value="update";
}

function simpan(){
	myid=document.getElementById('myid').value;
	method=document.getElementById('method').value;
	kodeunit=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	jenispersetujuan=document.getElementById('jenispersetujuan').options[document.getElementById('jenispersetujuan').selectedIndex].value;
	toleransi=document.getElementById('toleransi').value;
	digit=document.getElementById('digit').value;
	stt = document.getElementById('status');
    
	if(stt.checked==true){
		stt=1;
	}else{
		stt=0;
	}


	param='method='+method+'&kodeunit='+kodeunit+'&jenispersetujuan='+jenispersetujuan+'&toleransi='+toleransi+'&status='+stt+'&myid='+myid+'&digit='+digit;
    tujuan='setup_slave_validasianggaran.php';
	
	if(kodeunit==''||jenispersetujuan==''){
		alert('Warning : Lengkapi Pengisian.');
		return;
	}
	
	if(confirm('Are You Sure Save This Data?')){
		post_response_text(tujuan, param, respog);			
	}	
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Success");
					loaddata();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}