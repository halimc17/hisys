/*Header*/
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

function getakun(noakun){
	pt=trim(document.getElementById('pt').value);
	param='pt='+pt+'&method=getakun'+'&noakun='+noakun;
    tujuan='keu_slave_deposito.php';
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

function getstatus(tipetransaksi,status){
    tipetransaksi=trim(document.getElementById('tipetransaksi').value);
    if(tipetransaksi==1){
        document.getElementById('status').value=1;
        document.getElementById('status').disabled=true;
    }else if(tipetransaksi==2){
        document.getElementById('status').value=0;
        document.getElementById('status').disabled=true;
    }else{
        if(status!=''){
            document.getElementById('status').value=status;
        }else{
        document.getElementById('status').value='';
        }
        document.getElementById('status').disabled=false;
    }
}

function getBulan(){
    tgl=document.getElementById('tglvaluta').value;
    tgltempo=document.getElementById('tgltempo').value;
    param='method=getBulan'+'&tglvaluta='+tgl+'&tgltempo='+tgltempo;
    tujuan='keu_slave_deposito.php';
    post_response_text(tujuan, param, respog); 
    function respog(){
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else 
                {
                    document.getElementById('jangkawaktu').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function loadData(num){
    tipecr=document.getElementById('tipecr').value;

    param='method=loadData';
    param+='&page='+num;

    if (tipecr != '') {
        param += '&tipecr=' + tipecr;
    }

    tujuan='keu_slave_deposito.php';
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

function saveData(){
	pt=trim(document.getElementById('pt').value);
	notransaksi=trim(document.getElementById('notransaksi').value);
	tipetransaksi=trim(document.getElementById('tipetransaksi').value);
	noakun=trim(document.getElementById('noakun').value);
	nobilyet=trim(document.getElementById('nobilyet').value);
    nodeposito=trim(document.getElementById('nodeposito').value);
    tglvaluta=trim(document.getElementById('tglvaluta').value);
    tgltempo=trim(document.getElementById('tgltempo').value);
    jangkawaktu=trim(document.getElementById('jangkawaktu').value);
    status=trim(document.getElementById('status').value);
    sukubunga=trim(document.getElementById('sukubunga').value);
	jumlahdeposito=trim(document.getElementById('jumlahdeposito').value);
	method=trim(document.getElementById('method').value);


	param='pt='+pt+'&notransaksi='+notransaksi+'&tipetransaksi='+tipetransaksi+'&method='+method+'&jumlahdeposito='+jumlahdeposito;
    param+='&noakun='+noakun+'&nobilyet='+nobilyet+'&nodeposito='+nodeposito+'&tglvaluta='+tglvaluta;
	param+='&tgltempo='+tgltempo+'&jangkawaktu='+jangkawaktu+'&status='+status+'&sukubunga='+sukubunga;
    tujuan='keu_slave_deposito.php';
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

function editdt(pt,notrans,noakun,tipetransaksi,nobilyet,nodeposito,tglvaluta,tgltempo,jangkawaktu,sukubunga,jumlahdeposito,status){
    document.getElementById('pt').value=pt;
    document.getElementById('pt').disabled=true;
    document.getElementById('notransaksi').value=notrans;
    document.getElementById('noakun').value=noakun;
    document.getElementById('tipetransaksi').value=tipetransaksi;
    document.getElementById('tipetransaksi').disabled=true;
    document.getElementById('nobilyet').value=nobilyet;
    document.getElementById('nodeposito').value=nodeposito;
    document.getElementById('tglvaluta').value=tglvaluta;
    document.getElementById('tgltempo').value=tgltempo;
    document.getElementById('jangkawaktu').value=jangkawaktu;
    document.getElementById('status').value=status;
    document.getElementById('sukubunga').value=sukubunga;
    document.getElementById('jumlahdeposito').value=jumlahdeposito;
    document.getElementById('method').value='updatedt';
    getakun(noakun);
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    getstatus(tipetransaksi,status);
}

function clearData(){
	document.getElementById('pt').value='';
	document.getElementById('notransaksi').value='';
	document.getElementById('tipetransaksi').value='';
    document.getElementById('pt').disabled=false;
    document.getElementById('tipetransaksi').disabled=false;
    document.getElementById('status').disabled=false;
	document.getElementById('noakun').value='';
	document.getElementById('nobilyet').value='';
    document.getElementById('nodeposito').value='';
    document.getElementById('tglvaluta').value='';
    document.getElementById('tgltempo').value='';
    document.getElementById('jangkawaktu').value='';
    document.getElementById('status').value='';
    document.getElementById('sukubunga').value='';
	document.getElementById('jumlahdeposito').value='';
	document.getElementById('method').value='insert';
}

function deldt(notrans)
{
    param='method=deldt'+'&notransaksi='+notrans;
    tujuan='keu_slave_deposito.php';
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

function posting(notrans)
{
    param='method=posting'+'&notransaksi='+notrans;
    tujuan='keu_slave_deposito.php';
    if(confirm('Anda yakin ingin memposting data ini ??'))
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
                else {
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
    param='method=closed'+'&notransaksi='+notrans;
    tujuan='keu_slave_deposito.php';
    if(confirm('Are you sure to close this transaction ??'))
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
/*End Header*/


/*Detail*/
function form()
{
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(notrans,tglcair)
{
    form();
    param = 'method=viewdetail'+'&notransaksi='+notrans+'&tglcair='+tglcair;
    tujuan = 'keu_slave_deposito.php';
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
function getjumlah(notransaksi){
    tglvaluta=trim(document.getElementById('tglvalutadetail').value);
    tglcair=trim(document.getElementById('tglcair').value);
    tglterima=trim(document.getElementById('tglterima').value);
    jumlahpenalti=trim(document.getElementById('jumlahpenalti').value);
    /*realisasi=trim(document.getElementById('realisasi').value);
    notranskasbank=trim(document.getElementById('notranskasbank').value);*/
    param='tglvaluta='+tglvaluta+'&tglcair='+tglcair+'&tglterima='+tglterima+'&jumlahpenalti='+jumlahpenalti+'&notransaksi='+notransaksi+'&method=getjumlah';
    // param+='&jumlahpenalti='+jumlahpenalti+'&realisasi='+realisasi+'&notranskasbank='+notranskasbank;
    tujuan='keu_slave_deposito.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('jumlahbunga').value=isdt[0];
                    document.getElementById('jumlahpajak').value=isdt[1];
                    document.getElementById('total').value=isdt[2];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function saveDatadt(notransaksi){
    tglvaluta=trim(document.getElementById('tglvalutadetail').value);
    tglcair=trim(document.getElementById('tglcair').value);
    tglterima=trim(document.getElementById('tglterima').value);
    jumlahbunga=trim(document.getElementById('jumlahbunga').value);
    jumlahpajak=trim(document.getElementById('jumlahpajak').value);
    jumlahpenalti=trim(document.getElementById('jumlahpenalti').value);
    statusclose=trim(document.getElementById('statusclose').value);
    methoddt=trim(document.getElementById('methoddt').value);

    param='notransaksi='+notransaksi+'&tglvaluta='+tglvaluta+'&tglcair='+tglcair+'&tglterima='+tglterima+'&method='+methoddt;
    param+='&jumlahbunga='+jumlahbunga+'&jumlahpajak='+jumlahpajak+'&jumlahpenalti='+jumlahpenalti+'&statusclose='+statusclose;
    tujua='keu_slave_deposito.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    clearDatadt();
                    viewdetail(notransaksi,'');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editdetail(notransaksi,tglvaluta,tglcair,tglterima,jumlahbunga,jumlahpajak,jumlahpenalti,total,statusclose){
    document.getElementById('tglcair').value=tglcair;
    document.getElementById('tglcair').disabled=false;
    document.getElementById('tglvalutadetail').value=tglvaluta;
    document.getElementById('tglterima').value=tglterima;
    document.getElementById('tglterima').disabled=false;
    document.getElementById('jumlahbunga').value=jumlahbunga;
    document.getElementById('jumlahpajak').value=jumlahpajak;
    document.getElementById('jumlahpenalti').value=jumlahpenalti;
    document.getElementById('jumlahpenalti').disabled=false;
    document.getElementById('total').value=total;
    document.getElementById('statusclose').value=statusclose;
    document.getElementById('statusclose').disabled=false;
    document.getElementById('methoddt').value='updatedetail';
}

function deldetail(notrans,tglvaluta)
{
    param='method=deldetail'+'&notransaksi='+notrans+'&tglvaluta='+tglvaluta;
    tujuan='keu_slave_deposito.php';
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
                   viewdetail(notrans,'');
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function postingdetail(notrans,tglvaluta)
{
    param='method=postingdetail'+'&notransaksi='+notrans+'&tglvaluta='+tglvaluta;
    tujuan='keu_slave_deposito.php';
    if(confirm('Anda yakin ingin memposting data ini ??'))
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
                    viewdetail(notrans,'');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function clearDatadt(status){
    document.getElementById('tglcair').value='';
    document.getElementById('tglvalutadetail').value='';
    document.getElementById('tglcair').disabled=true;
    document.getElementById('tglterima').disabled=true;
    document.getElementById('jumlahpenalti').disabled=true;
    document.getElementById('statusclose').disabled=true;
    document.getElementById('tglterima').value='';
    document.getElementById('jumlahbunga').value='';
    document.getElementById('jumlahpajak').value='';
    document.getElementById('jumlahpenalti').value='';
    document.getElementById('statusclose').value='0';
    document.getElementById('total').value='';
    document.getElementById('methoddt').value='';
}
/*End Detail*/


/*Upload File*/
function showupload(notransaksi,ev) {
    showformupload(ev);
    param = 'method=showupload&notransaksi=' + notransaksi;
    tujuan = 'keu_slave_deposito.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadfiles(notransaksi) {
    param = 'method=loadfiles&notransaksi=' + notransaksi;
    tujuan = 'keu_slave_deposito.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfilestop') !== null) {
                        document.getElementById('listfilestop').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfilesview') !== null) {
                        document.getElementById('listfilesview').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile(notransaksi) {
    var notransaksi = document.getElementById("notransupload").innerHTML;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    formdata.append("notransaksi", notransaksi);
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    document.getElementsByClassName("mybutton").disabled=true;
    busy_on();
    var con = createXMLHttpRequest();
    con.open("POST", "keu_slave_deposito.php?method=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementsByClassName("mybutton").disabled=false;
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(notransaksi, namafile) {
    param = 'method=deletefile&notransaksi=' + notransaksi + '&namafile=' + namafile;
    tujuan = 'keu_slave_deposito.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function downloadfile(path, filename) {
    param = 'path=' + path + '&filename=' + filename;
    tujuan = 'download.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showformupload(ev) {
    title = "UPLOAD FILES";
    width = '';
    height = '';
    content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
    document.getElementById('dynamic2').style.display = '';
}
/*End Upload File*/
