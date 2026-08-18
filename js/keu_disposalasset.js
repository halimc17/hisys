function simpan(){

    unit=trim(document.getElementById('unit').value);
    notransaksi=document.getElementById('notransaksi').value;
    kodeasset=document.getElementById('kodeasset').value;
    jenis=document.getElementById('jenis').value;
    jenisket=document.getElementById('jenisket').value;
    ket=document.getElementById('ket').value;
    persetujuan1=document.getElementById('persetujuan1').value;
    persetujuan2=document.getElementById('persetujuan2').value;
    persetujuan3=document.getElementById('persetujuan3').value;
    persetujuan4=document.getElementById('persetujuan4').value;
    nilaibuku=document.getElementById('nilaibuku').value;
    akumulasipenyusutan=document.getElementById('akumulasipenyusutan').value;
    method=document.getElementById('method').value;

    if(unit=='' || kodeasset=='' || jenis=='' || jenisket=='' || ket=='' || persetujuan1=='' || persetujuan2=='' || persetujuan3=='' || persetujuan4=='' || nilaibuku=='' || akumulasipenyusutan=='')
    {
        alert('Field Was Empty');
        return;
    }

    param='notransaksi='+notransaksi+'&jenis='+jenis+'&jenisket='+jenisket+'&ket='+ket+'&method='+method+'&unit='+unit+'&kodeasset='+kodeasset;
    param+='&persetujuan1='+persetujuan1+'&persetujuan2='+persetujuan2+'&persetujuan3='+persetujuan3+'&persetujuan4='+persetujuan4+'&nilaibuku='+nilaibuku+'&akumulasipenyusutan='+akumulasipenyusutan;
    tujuan='keu_slave_disposalasset.php';
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
					cancel();
		            displaylist();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}
					
function cancel(){
    document.getElementById('unit').value='';
    document.getElementById('notransaksi').value='';
    document.getElementById('jenis').value='';
    document.getElementById('kodeasset').value='';
    document.getElementById('jenisket').value='';
    document.getElementById('ket').value='';
    document.getElementById('method').value='insert';
    document.getElementById('unit').disabled=false;
    document.getElementById('kodeasset').disabled=false;
    document.getElementById('persetujuan1').value='';
    document.getElementById('persetujuan2').value='';
    document.getElementById('persetujuan3').value='';
    document.getElementById('persetujuan4').value='';
    document.getElementById('nilaibuku').value='';
    document.getElementById('akumulasipenyusutan').value='';
    document.getElementById('nilaibuku').disabled=true;
    document.getElementById('akumulasipenyusutan').disabled=true;
    document.getElementById('tombasset').style.display='block';
}


function displayFormInput(){
    cancel();
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
}

function displaylist(){
    cancel();
    document.getElementById('notranscr').value='';
    document.getElementById('jeniscr').value='';
    document.getElementById('listData').style.display='block';
    document.getElementById('formInput').style.display='none';
    loadData(0);
}

function loadData(num){
    notranscr=document.getElementById('notranscr').value;
    jeniscr=document.getElementById('jeniscr').value;

    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notransaksi=' + notranscr;
    }
    if (jeniscr != '') {
        param += '&jeniscr=' + jeniscr;
    }
    tujuan='keu_slave_disposalasset.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt = con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);  
}

function edit(notransaksi,unit,kodeasset,jenis,jenisket,ket,persetujuan1,persetujuan2,persetujuan3,persetujuan4,nilaibuku,akumulasipenyusutan)
{
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('kodeasset').value=kodeasset;
    document.getElementById('kodeasset').disabled=true;
    document.getElementById('jenis').value=jenis;
    document.getElementById('ket').value=ket;
    document.getElementById('persetujuan1').value=persetujuan1;
    document.getElementById('persetujuan2').value=persetujuan2;
    document.getElementById('persetujuan3').value=persetujuan3;
    document.getElementById('persetujuan4').value=persetujuan4;
    document.getElementById('nilaibuku').value=nilaibuku;
    document.getElementById('akumulasipenyusutan').value=akumulasipenyusutan;
    document.getElementById('method').value='update';
    document.getElementById('tombasset').style.display='none';
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    getjenisket(jenisket);
}


function del(notransaksi)
{
	param='method=delete'+'&notransaksi='+notransaksi;
	tujuan='keu_slave_disposalasset.php';
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
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getjenisket(jenisket)
{
    jenis=document.getElementById('jenis').value;
    param='jenisket='+jenisket+'&jenis='+jenis+'&method=getjenisket';
    tujuan='keu_slave_disposalasset.php';
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
					document.getElementById('jenisket').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function ajukan(notransaksi)
{
    param='method=ajukan'+'&notransaksi='+notransaksi;
    tujuan='keu_slave_disposalasset.php';
    if(confirm('Anda yakin ingin mengajukan transaksi ini ??'))
    {
        post_response_text(tujuan, param, respog);  
    }
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
                    displaylist();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function checkdata(){
    jenisket=trim(document.getElementById('jenisket').value);
    
    if (jenisket==11) {
        document.getElementById('nilaibuku').disabled=false;
        document.getElementById('nilaibuku').value='';
        document.getElementById('akumulasipenyusutan').disabled=true;
        // document.getElementById('akumulasipenyusutan').disabled=false;
        // document.getElementById('akumulasipenyusutan').value='';
    }else{
        document.getElementById('nilaibuku').disabled=true;
        document.getElementById('akumulasipenyusutan').disabled=true;

    }
}

function getasset(){
    unit=trim(document.getElementById('unit').value);
    param='method=getasset'+'&unit='+unit;
    tujuan='keu_slave_disposalasset.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kodeasset').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdata(){
    kodeasset=trim(document.getElementById('kodeasset').value);
    param='method=getdata'+'&kodeasset='+kodeasset;
    tujuan='keu_slave_disposalasset.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('nilaibuku').value=isdt[0];
                    document.getElementById('akumulasipenyusutan').value=isdt[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function searchkodeasset(title,content,ev)
{
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    getformkodeasset();
}

function getformkodeasset(){
    unit=trim(document.getElementById('unit').value);
    param='method=getformkodeasset'+'&unit='+unit;
    tujuan='keu_slave_disposalasset.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formPencariandata').innerHTML=con.responseText;
                    findkodeasset();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findkodeasset(){
    unit=trim(document.getElementById('unit').value);
    param='method=getdatakodeasset'+'&unit='+unit;
    fkodeasset=trim(document.getElementById('fkodeasset').value);
    param+='&kodeasset='+fkodeasset;
    
    tujuan='keu_slave_disposalasset.php';
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
                    document.getElementById('container2').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setdata(kodeasset,nilaibuku,akumulasipenyusutan) {
    document.getElementById('kodeasset').value=kodeasset;
    document.getElementById('nilaibuku').value=nilaibuku;
    document.getElementById('akumulasipenyusutan').value=akumulasipenyusutan;
    closeDialog();
}

function form()
{
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(notransaksi,event)
{
    form();
    param = 'method=viewdetail'+'&notransaksi='+notransaksi;
    tujuan = 'keu_slave_disposalasset.php';
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
                    alert(con.responseText);
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




