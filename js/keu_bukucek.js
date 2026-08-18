function getakuncr(){
	unit=trim(document.getElementById('unitcr').value);
	param='unit='+unit+'&method=getakun';
    tujuan='keu_slave_bukucek.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('noakuncr').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getakun(noakun){
	unit=trim(document.getElementById('unit').value);
	param='unit='+unit+'&method=getakun'+'&noakun='+noakun;
    tujuan='keu_slave_bukucek.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function saveData(){
    unit=trim(document.getElementById('unit').value);
	notrans_cek=trim(document.getElementById('notrans_cek').value);
	tipetransaksi=trim(document.getElementById('tipetransaksi').value);
	noakun=trim(document.getElementById('noakun').value);
	noawal=trim(document.getElementById('noawal').value);
	noakhir=trim(document.getElementById('noakhir').value);
	method=trim(document.getElementById('method').value);

    if (unit=='' || tipetransaksi=='' || noakun=='' || noawal=='' || noakhir=='') {
        alert('Field was Empty.');
        return;
    }
	
	param='unit='+unit+'&notrans_cek='+notrans_cek+'&tipetransaksi='+tipetransaksi+'&method='+method;
	param+='&noakun='+noakun+'&noawal='+noawal+'&noakhir='+noakhir;
    tujua='keu_slave_bukucek.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					clearData();
					displaylist();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deldt(notrans)
{
    param='method=deldt'+'&notrans_cek='+notrans;
    tujuan='keu_slave_bukucek.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
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
                }else{
                   displaylist();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
		} 
    }
}

function editdt(unit,notrans,noakun,tipe_buku,nocek_awal,nocek_akhir){
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('notrans_cek').value=notrans;
    document.getElementById('noakun').value=noakun;
    document.getElementById('tipetransaksi').value=tipe_buku;
    document.getElementById('tipetransaksi').disabled=true;
    document.getElementById('noawal').value=nocek_awal;
    document.getElementById('noakhir').value=nocek_akhir;
    document.getElementById('method').value='updatedt';
    getakun(noakun);
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
}

function clearData(){
    document.getElementById('unit').value='';
	document.getElementById('unit').disabled=false;
	document.getElementById('notrans_cek').value='';
	document.getElementById('tipetransaksi').value='';
    document.getElementById('tipetransaksi').disabled=false;
	document.getElementById('noakun').value='';
	document.getElementById('noawal').value='';
	document.getElementById('noakhir').value='';
	
	document.getElementById('noawalcr').value='';
	document.getElementById('noakhircr').value='';
	document.getElementById('notransaksicr').value='';
	document.getElementById('noakuncr').value='';
	document.getElementById('statuscr').value='';
	
	document.getElementById('method').value='insert';
}

function displayFormInput(){
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    clearData();
}

function displaylist(){
    document.getElementById('tipecr').value='';
	document.getElementById('listData').style.display='block';
	document.getElementById('formInput').style.display='none';
    clearData();
	loadData(0);
}

function loadData(num){
	
    noawalcr=document.getElementById('noawalcr').value;
    noakhircr=document.getElementById('noakhircr').value;
    tipecr=document.getElementById('tipecr').value;
    unitcr=document.getElementById('unitcr').value;
    notransaksicr=document.getElementById('notransaksicr').value;
    noakuncr=document.getElementById('noakuncr').value;
    statuscr=document.getElementById('statuscr').value;

    param='method=loadData';
    param+='&page='+num;

    if (tipecr != '') {
        param += '&tipecr=' + tipecr;
    }  
	if (unitcr != '') {
        param += '&unitcr=' + unitcr;
    }
	if (notransaksicr != '') {
        param += '&notransaksicr=' + notransaksicr;
    }
	if (noakuncr != '') {
        param += '&noakuncr=' + noakuncr;
    }if (statuscr != '') {
        param += '&statuscr=' + statuscr;
    }
	
	if (noawalcr != '') {
        param += '&noawalcr=' + noawalcr;
    } 
	
	if (noakhircr != '') {
        param += '&noakhircr=' + noakhircr;
    } 
	
	
// alert(param);
    tujuan='keu_slave_bukucek.php';
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

function form()
{
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(notrans,event)
{
    form();
    param = 'method=viewdetail' + '&notrans_cek=' + notrans;
    tujuan = 'keu_slave_bukucek.php';
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

function posting(notrans)
{
    param='method=posting'+'&notrans_cek='+notrans;
    tujuan='keu_slave_bukucek.php';
    if(confirm('Anda yakin ingin mengaktifkan ini ??'))
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

function formajukan(title)
{
        width='';
        height='';
        content="<div id=containervoid ></div>";
        ev='event';
        showDialog2(title,content,width,height,ev);   
}

function formajukanvoid(notrans,notransaksi,unit,nocek_awal)
{
    title="Ajukan Void";
    formajukan(title);
    param='method=formajukanvoid'+'&notrans_cek='+notrans+'&notransaksi='+notransaksi+'&unit='+unit+'&noawal='+nocek_awal;
    tujuan='keu_slave_bukucek.php';
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
                        document.getElementById('containervoid').innerHTML=con.responseText;
                        // return con.responseText;
                    }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            } 
        }  
    post_response_text(tujuan, param, respog);      
}

function ajukanvoid()
{
    noawalvoid=document.getElementById('noawalvoid').value;
	notransvoid=document.getElementById('notransvoid').value;
	notransaksi=document.getElementById('notransaksi').value;
    alasan=document.getElementById('alasan').value;
	persetujuan=document.getElementById('persetujuan').value;

    if (alasan=='' || persetujuan=='') {
        alert('Field was Empty.');
        return;
    }

    param='method=ajukanvoid'+'&notrans_cek='+notransvoid+'&notransaksi='+notransaksi+'&alasan='+alasan+'&persetujuan='+persetujuan;
    tujuan='keu_slave_bukucek.php';
    
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
                    simpanupload(notransvoid,notransaksi,2,noawalvoid)
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
		} 
    }
}

function uploaddata(notransvoid,notransaksi,nocek_awal)
{
    title="Upload Data";
    formajukan(title);
    param = 'method=uploaddata' + '&notrans_cek='+notransvoid+'&notransaksi='+notransaksi+'&noawal='+nocek_awal;
    tujuan = 'keu_slave_bukucek.php';
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
                    document.getElementById('containervoid').innerHTML = con.responseText;
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

function simpanupload(notransvoid,notransaksi,tipe,nocek_awal){
    var formdata = new FormData();
    if (tipe==1) {
        var fileup = document.getElementById('fileupload1').files[0];
        formdata.append("fileup", fileup);
        formdata.append("fileupload", getValue('fileupload1'));
    }else{
        var fileup = document.getElementById('fileupload').files[0];
        formdata.append("fileup", fileup);
        formdata.append("fileupload", getValue('fileupload'));  
    }
    

    var con = createXMLHttpRequest();
    con.open("POST", "keu_slave_bukucek.php?method=simpanupload&notrans_cek="+notransvoid+"&notransaksi="+notransaksi+"&tipe="+tipe+"&noawal="+nocek_awal, true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    viewdetail(notransvoid);
                    closeDialog2();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancelvoid()
{
        closeDialog2();
}

function formpilihnocek(notrans,notransaksi,nocek)
{
    title="No.Cek";
    formajukan(title);
    param='method=formpilihnocek'+'&notrans_cek='+notrans+'&notransaksi='+notransaksi+'&noawal='+nocek;
    tujuan='keu_slave_bukucek.php';
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
                        document.getElementById('containervoid').innerHTML=con.responseText;
                        // return con.responseText;
                    }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            } 
        }  
    post_response_text(tujuan, param, respog);      
}

function simpannocekvoid(notrans,notransaksi,nocek)
{
    nocekvoid=document.getElementById('nocekvoid').value;
    param='method=simpannocekvoid'+'&notrans_cek='+notrans+'&notransaksi='+notransaksi+'&nocekvoid='+nocekvoid+'&noawal='+nocek;
    tujuan='keu_slave_bukucek.php';
    
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
                    viewdetail(notrans);
                    closeDialog2();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}


function displayfile(doc,ev)
{
    param = 'method=displayfile' + '&doc=' + doc;
    title="Data Detail";
     showDialog4(title,"<iframe frameborder=0 style='width:795px;height:395px'"+
    " src='keu_slave_bukucek.php?"+param+"'></iframe>",'800','400',ev); 
    var dialog = document.getElementById('dynamic4');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function printFile(param,tujuan,ev)
{
   tujuan=tujuan+"?notrans_cek="+param+"&method=viewdetail"+"&proses=excel";  
   width='700';
   height='400';
   title=param;
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);  
}

function formgrtt(notrans,nocek)
{
    title="Form GRTT";
    formajukan(title);
    param='method=formgrtt'+'&notrans_cek='+notrans+'&noawal='+nocek;
    tujuan='keu_slave_bukucek.php';
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
                        document.getElementById('containervoid').innerHTML=con.responseText;
                        // return con.responseText;
                    }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            } 
        }  
    post_response_text(tujuan, param, respog);      
}

function simpangrtt(notrans,nocek)
{
    tujuan=document.getElementById('tujuan').value;
    tglcair=document.getElementById('tglcair').value;
    penerima=document.getElementById('penerima').value;
    param='method=simpangrtt'+'&notrans_cek='+notrans+'&noawal='+nocek+'&tujuan='+tujuan+'&tglcair='+tglcair+'&penerima='+penerima;
    tujuan='keu_slave_bukucek.php';
    post_response_text(tujuan, param, respog);  
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                    viewdetail(notrans);
                    closeDialog2();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}


function unclosed(notrans)
{
    param='method=unclosed'+'&notrans_cek='+notrans;
    tujuan='keu_slave_bukucek.php';
    if(confirm(bahasa.notifandayakin))
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

function closed(notrans)
{
    param='method=closed'+'&notrans_cek='+notrans;
    tujuan='keu_slave_bukucek.php';
    if(confirm(bahasa.notifandayakin))
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