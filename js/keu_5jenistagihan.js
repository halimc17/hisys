// JavaScript Document
function batal()
{
	document.getElementById('method').value = 'insert';
	document.getElementById("kode").value = "";
	document.getElementById('namajenis').value = "";
	document.getElementById('sumber').value = "";
	document.getElementById('transaksirutin').checked=false;
	document.getElementById('kode').disabled = false;
}

function loadData(){
	param='method=loaddata';
	tujuan='keu_slave_5jenistagihan.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				}
				else 
				{
					document.getElementById('container').innerHTML=con.responseText;
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

function fillfield(kode,namajenis,sumber,jrndt,sts,transaksirutin,tipesup){
	document.getElementById('kode').disabled=true;
	document.getElementById('kode').value=kode;
	document.getElementById('namajenis').value=namajenis;
	document.getElementById('sumber').value=sumber;
	document.getElementById('statJrn').value=jrndt;
	document.getElementById('tipesup').value=tipesup;
	document.getElementById('method').value='edit';
	if(sts=='1'){
		document.getElementById('status').checked=true;
	}else{
		document.getElementById('status').checked=false;
	}

	if(transaksirutin=='1'){
		document.getElementById('transaksirutin').checked=true;
	}else{
		document.getElementById('transaksirutin').checked=false;
	}
}

function simpan()
{	
	sts = document.getElementById('status');   
	if(sts.checked==true){
		sts=1;
	}
    else{
		sts=0;
	}

	transaksirutin = document.getElementById('transaksirutin');   
	if(transaksirutin.checked==true){
		transaksirutin=1;
	}
    else{
		transaksirutin=0;
	}
	kode=trim(document.getElementById('kode').value);
	namajenis=trim(document.getElementById('namajenis').value);
	sumber=trim(document.getElementById('sumber').value);
	method=trim(document.getElementById('method').value);
	tipesup=trim(document.getElementById('tipesup').value);
	statJrn=document.getElementById('statJrn');
	statJrn=statJrn.options[statJrn.selectedIndex].value;
	param='kode='+kode+'&namajenis='+namajenis+'&sumber='+sumber+'&tipesup='+tipesup+'&method='+method+'&sts='+sts+'&transaksirutin='+transaksirutin;
	param+='&statJrn='+statJrn;
	tujuan='keu_slave_5jenistagihan.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				}
				else {
					document.getElementById('container').innerHTML=con.responseText;
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

function deletefield(kode){
	param='kode='+kode+'&method=delete';
	tujuan='keu_slave_5jenistagihan.php';
	// if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	alertify.confirm("Infomation",'Anda yakin hapus item ini?',
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alertify.alert("Informasi",con.responseText);
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

// MENU DETAIL

function getAruskas(){
    tipedt=document.getElementById('tipedt').value;
    
    param = 'method=getAruskas&tipedt='+tipedt;
    // alert(param);
    tujuan = 'keu_slave_5jenistagihan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                        document.getElementById('aruskasdt').innerHTML=con.responseText;
                        
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function viewdetailbaru(kodedt){
    // alert('masuk');
    titl="Kode : "+kodedt
    form(titl);
    param='method=viewdetailbaru'+'&kodedt='+kodedt;
    tujuan = 'keu_slave_5jenistagihan.php';
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
                    alertify.alert("Informasi",con.responseText);
                }
                else
                {
                    document.getElementById('containerd').innerHTML = con.responseText;
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


function batalviewdtb(){
    document.getElementById('tipedt').value='';   
    document.getElementById('aruskasdt').value='';    
    document.getElementById('noakundt').value='';    
}

function deldt(kodedt,tipedt){
   
    param='method=deldt'+'&kodedt='+kodedt+'&tipedt='+tipedt;
    
    tujuan='keu_slave_5jenistagihan.php';
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    viewdetailbaru(kodedt);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    // if (confirm("Apakah anda yakin menghapusnya?")) {
    //     post_response_text(tujuan, param, respog);
    // }
    alertify.confirm("Infomation","Apakah anda yakin menghapusnya?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
}

function form(titledt){
    width = '450px';
    height = 'auto';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><div id=containerd style=width:450px></div></fieldset>";
    ev = 'event';
    title = titledt;//"Detail HTML";
    showDialog4(title, content, width, height, ev); 
}

function getakun(){
    aruskas = document.getElementById('aruskas').value;
    
    param = 'method=getNoakun&aruskas='+aruskas;
    tujuan = 'keu_slave_5jenistagihan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                        document.getElementById('noakun').innerHTML=con.responseText;
                        
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function previewfield(kode,method) {
	param = 'kode=' + kode+'&method='+method;
	tujuan = 'keu_slave_5jenistagihan.php';

 	content = "<div id=formbayar style=\"height:100%;width:100%;\"></div>";
    title = 'Kode ' + kode;
    height = '';
    width = 600;
    showDialog4(title, content, width, height, 'event');

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    document.getElementById('formbayar').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savedt(){
    kodedt = document.getElementById('kodedt').value;
    tipepajak = document.getElementById('tipepajak').value;
    aruskas = document.getElementById('aruskas').value;
    noakun = document.getElementById('noakun').value;
    method = document.getElementById('methoddt').value;
   
    param = 'method='+method+'&kode='+kodedt+'&tipepajak='+tipepajak+'&aruskas='+aruskas+'&noakun='+noakun;
    tujuan = 'keu_slave_5jenistagihan.php';
    
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert("Informasi",con.responseText);
                } else {
                	alertify.alert("Done");
                    closeDialog4();
                    previewfield(kodedt, 'previewfield');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if (confirm("Apakah anda yakin menyimpannya?")) {
        post_response_text(tujuan, param, respog);
    }
}

function fillfielddt(kode,tipepajak,aruskas,noakun){
	document.getElementById('kodedt').disabled = true;
	document.getElementById('tipepajak').disabled = true;
	document.getElementById('tipepajak').value = tipepajak;
	document.getElementById('aruskas').value = aruskas;
	document.getElementById('methoddt').value = 'updatedt';

	param = 'method=getNoakun&aruskas='+aruskas;
    tujuan = 'keu_slave_5jenistagihan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                   	document.getElementById('noakun').innerHTML=con.responseText;
					document.getElementById('noakun').value = noakun;
                        
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletedt(kode,tipepajak){   
    param = 'method=deletedt&kode='+kode+'&tipepajak='+tipepajak;
    tujuan = 'keu_slave_5jenistagihan.php';
    
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert("Informasi",con.responseText);
                } else {
                	alertify.alert("Done");
                    closeDialog4();
                    previewfield(kode, 'previewfield');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if (confirm("Apakah anda yakin menghapus data?")) {
        post_response_text(tujuan, param, respog);
    }
}

function cleardt(){
	document.getElementById('kodedt').disabled = false;
	document.getElementById('tipepajak').disabled = false;
	document.getElementById('tipepajak').value = '';
	document.getElementById('aruskas').value = '';
	document.getElementById('noakun').value = '';
	document.getElementById('methoddt').value = 'insertdt';
}