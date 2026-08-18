function displayFormInput(){
    cancel();
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
}

function displaylist(){
    cancel();
    document.getElementById('notranscr').value='';
    document.getElementById('listData').style.display='block';
    document.getElementById('formInput').style.display='none';
    loadData(0);
}

function getakun(norekpemberi,norekpenerima){
    unitpemberi=trim(document.getElementById('unitpemberi').value);
    unitpenerima=trim(document.getElementById('unitpenerima').value);
    norekpemberiin=trim(document.getElementById('norekpemberi').value);
    norekpenerimain=trim(document.getElementById('norekpenerima').value);

    if (norekpemberiin!=''){
        norekpemberi=norekpemberiin;
    }

    if (norekpenerimain!=''){
        norekpenerima=norekpenerimain;
    }

    param='unitpenerima='+unitpenerima+'&unitpemberi='+unitpemberi+'&method=getakun'+'&norekpenerima='+norekpenerima+'&norekpemberi='+norekpemberi;
    tujuan='keu_slave_modal.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data=con.responseText.split('####');
                    document.getElementById('norekpemberi').innerHTML=data[0];
                    document.getElementById('norekpenerima').innerHTML=data[1];
                    getmatauang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getmatauang(){
    norekpemberi=trim(document.getElementById('norekpemberi').value);
    norekpenerima=trim(document.getElementById('norekpenerima').value);

    param='method=getmatauang'+'&norekpenerima='+norekpenerima+'&norekpemberi='+norekpemberi;
    tujuan='keu_slave_modal.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data=con.responseText.split('####');
                    document.getElementById('matauangpemberi').value=data[0];
                    document.getElementById('matauangpenerima').value=data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function gettotpenerima(){
    totalpemberi=document.getElementById('totalpemberi').value;
    totalpemberi=totalpemberi.replace(new RegExp(/,/i, "gm"),'');
    document.getElementById('totalpenerima').value=numberFormat(totalpemberi,2);
}

function simpan(){
    notransaksi=document.getElementById('notransaksi').value;
    unitpemberi=trim(document.getElementById('unitpemberi').value);
    unitpenerima=trim(document.getElementById('unitpenerima').value);
    norekpemberi=document.getElementById('norekpemberi').value;
    norekpenerima=document.getElementById('norekpenerima').value;
    tanggalpemberi=document.getElementById('tanggalpemberi').value;
    totalpemberi=document.getElementById('totalpemberi').value;
    totalpenerima=document.getElementById('totalpenerima').value;
    method=document.getElementById('method').value;

    if(unitpemberi=='' || unitpenerima=='' || norekpemberi=='' || norekpenerima=='' || tanggalpemberi=='' || totalpemberi=='' || totalpenerima=='')
    {
        alert('Field Was Empty');
        return;
    }

    param='notransaksi='+notransaksi+'&unitpemberi='+unitpemberi+'&unitpenerima='+unitpenerima+'&norekpemberi='+norekpemberi+'&method='+method;
    param+='&norekpenerima='+norekpenerima+'&tanggalpemberi='+tanggalpemberi+'&totalpemberi='+totalpemberi+'&totalpenerima='+totalpenerima;
    tujuan='keu_slave_modal.php';
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
    document.getElementById('notransaksi').value='';
    document.getElementById('unitpemberi').value='';
    document.getElementById('unitpemberi').disabled=false;
    document.getElementById('unitpenerima').value='';
    document.getElementById('unitpenerima').disabled=false;
    document.getElementById('norekpemberi').value='';
    document.getElementById('norekpenerima').value='';
    document.getElementById('matauangpemberi').value='';
    document.getElementById('matauangpenerima').value='';
    document.getElementById('tanggalpemberi').value='';
    document.getElementById('totalpemberi').value='';
    document.getElementById('totalpenerima').value='';
    document.getElementById('method').value='insert';
}

function loadData(num){
    notranscr=document.getElementById('notranscr').value;

    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notransaksi=' + notranscr;
    }

    tujuan='keu_slave_modal.php';
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

function edit(notransaksi,unitpemberi,norekpemberi,tanggal,totalpemberi,unitpenerima,norekpenerima)
{
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('unitpemberi').value=unitpemberi;
    document.getElementById('unitpemberi').disabled=true;
    document.getElementById('norekpemberi').value=norekpemberi;
    document.getElementById('tanggalpemberi').value=tanggal;
    document.getElementById('unitpenerima').value=unitpenerima;
    document.getElementById('unitpenerima').disabled=true;
    document.getElementById('norekpenerima').value=norekpenerima;
    document.getElementById('totalpemberi').value=totalpemberi;
    document.getElementById('totalpenerima').value=totalpemberi;
    document.getElementById('method').value='update';
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    getakun(norekpemberi,norekpenerima);
}

function del(notransaksi)
{
	param='method=delete'+'&notransaksi='+notransaksi;
	tujuan='keu_slave_modal.php';
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

function posting(notransaksi)
{
    param='method=posting'+'&notransaksi='+notransaksi;
    tujuan='keu_slave_modal.php';
    if(confirm('Anda yakin ingin memposting transaksi ini ??'))
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

function form()
{
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(notransaksi,tipetransaksikasbank)
{
    form();
    param = 'method=viewdetail'+'&notransaksi='+notransaksi+'&tipetransaksikasbank='+tipetransaksikasbank;
    tujuan = 'keu_slave_modal.php';
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



