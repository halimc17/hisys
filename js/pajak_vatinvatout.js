function displayFormInput(){
    cancel();
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    document.getElementById('formdetaildata').style.display='none';
    document.getElementById('formdetail').style.display='none';
}

function displaylist(){
    cancel();
    document.getElementById('notranscr').value='';
    document.getElementById('listData').style.display='block';
    document.getElementById('formInput').style.display='none';
    document.getElementById('formdetaildata').style.display='none';
    document.getElementById('formdetail').style.display='none';
    loadData(0);
}
function getForm(){
    tipe=document.getElementById('tipe');
    tipe=tipe.options[tipe.selectedIndex].value;
    if(tipe>2){
        // document.getElementById('supplierId').innerHTML="<option value=''>"+bahasa+"</option>";
        document.getElementById('noinvoiceId').value='';
        document.getElementById('noinvoiceId').disabled=false;
        document.getElementById('supplierId').disabled=false;
    }else{
        // document.getElementById('supplierId').innerHTML="<option value=''>"+bahasa+"</option>";
        document.getElementById('supplierId').disabled=true;
        document.getElementById('supplierId').value='';
        document.getElementById('noinvoiceId').value='';
        document.getElementById('noinvoiceId').disabled=true;
    }
}



function getperiode(){
    unit=trim(document.getElementById('unit').value);

    param='unit='+unit+'&method=getperiode';
    tujuan='pajak_slave_vatinvatout.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data = con.responseText.split("####");
                    document.getElementById('periode').innerHTML=data[0];
                    document.getElementById('npwp').innerHTML=data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
    unit=document.getElementById('unit').value;
    npwp=document.getElementById('npwp').value;
    periode=trim(document.getElementById('periode').value);
    tipe=trim(document.getElementById('tipe').value);
    tanggaldari=trim(document.getElementById('tanggaldari').value);
    tanggalsampai=trim(document.getElementById('tanggalsampai').value);

    if(periode=='' || tipe=='' || unit==''|| npwp=='')
    {
        alert('Field Was Empty');
        return;
    }
    if(tipe>2){
        supplierId=document.getElementById('supplierId').value;
        noinvoice=document.getElementById('noinvoiceId').value;
        param='unit='+unit+'&periode='+periode+'&tipe='+tipe+'&tanggaldari='+tanggaldari+'&tanggalsampai='+tanggalsampai+'&npwp='+npwp+'&method=showlistdata2';
        param+='&supplierId='+supplierId+'&noinvoice='+noinvoice;
    }else{
        param='unit='+unit+'&periode='+periode+'&tipe='+tipe+'&tanggaldari='+tanggaldari+'&tanggalsampai='+tanggalsampai+'&npwp='+npwp+'&method=showlistdata';    
    }
    
    tujuan='pajak_slave_vatinvatout.php';
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
                    document.getElementById('formdetail').style.display='block';
                    document.getElementById('formdetaildata').style.display='block';
                    document.getElementById('formdetaildata').innerHTML = con.responseText;
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
    document.getElementById('periode').value='';
    document.getElementById('tanggaldari').value='';
    document.getElementById('tanggalsampai').value='';
    document.getElementById('tipe').value='';
    document.getElementById('noinvoiceId').value='';
    document.getElementById('supplierId').value='';
    document.getElementById('supplierId').disabled=true;
    document.getElementById('noinvoiceId').disabled=true;
}

function loadData(num){
    notranscr=document.getElementById('notranscr').value;

    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notransaksi=' + notranscr;
    }

    tujuan='pajak_slave_vatinvatout.php';
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

function checkfp(nsfp)
{   
    totrow = document.getElementById('totrow').value;
    console.log(nsfp);
    for (i=1; i <= totrow; i++)
    {
        if (document.getElementById('nsfp_'+i).innerHTML==nsfp) {
            if (document.getElementById('no_'+i).checked==false) {
                document.getElementById('no_'+i).checked = true;
            }else{
                document.getElementById('no_'+i).checked = false;
            }
        }
    }
}

function checkAll()
{   
    totrow = document.getElementById('totrow').value;
    btn = document.getElementById('btnall');
    if (btn.checked == true){
        chk = true;
    } else {
        chk = false;
    }

    for (i=1; i <= totrow; i++)
    {
        document.getElementById('no_'+i).checked = chk;
    }
}

function adddetail(periode,unit,noakun,npwp) {
    totrow=trim(document.getElementById('totrow').value);
    
    var allData='';
    var cekpil=0;
    for(dwc=1;dwc<=totrow;dwc++){
        if (document.getElementById('no_'+dwc).checked==true) {
            allData+="&tglinvoice[]="+document.getElementById('tglinvoice_'+dwc).innerHTML;
            allData+="&noinvoice[]="+document.getElementById('noinvoice_'+dwc).innerHTML;
            allData+="&tglgL[]="+document.getElementById('tglgL_'+dwc).innerHTML;
            allData+="&coagl[]="+document.getElementById('coagl_'+dwc).innerHTML;
            allData+="&tglnsfp[]="+document.getElementById('tglnsfp_'+dwc).innerHTML;
            allData+="&ekstglnsfp[]="+document.getElementById('ekstglnsfp_'+dwc).innerHTML;
            allData+="&nsfp[]="+document.getElementById('nsfp_'+dwc).innerHTML;
            allData+="&kodesupplier[]="+document.getElementById('kodesupplier_'+dwc).innerHTML;
            allData+="&dpp[]="+document.getElementById('dpp_'+dwc).innerHTML;
            allData+="&ppn[]="+document.getElementById('ppn_'+dwc).innerHTML;
            allData+="&jnspajak[]="+document.getElementById('jnspajak_'+dwc).innerHTML;
            allData+="&total[]="+document.getElementById('total_'+dwc).innerHTML;
            allData+="&status[]="+document.getElementById('status_'+dwc).value;
            cekpil+=1;
        }
    }

    if(cekpil==0){
        alert('Data belum terpilih.');
        return;
    }
    param='totrow='+cekpil+'&method=adddetail'+'&periode='+periode+'&unit='+unit+'&noakun='+noakun+'&npwp='+npwp;
    param+=allData;
    
    tujuan='pajak_slave_vatinvatout.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
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

function removedetail(notransaksi,noakun) {
    totrow=trim(document.getElementById('totrowdt').value);
    
    var allData='';
    var cekpil=0;
    for(dwc=1;dwc<=totrow;dwc++){
        if (document.getElementById('nodt_'+dwc).checked==true) {
            allData+="&noinvoice[]="+document.getElementById('noinvoicedt_'+dwc).innerHTML;
            allData+="&status[]="+document.getElementById('statusdt_'+dwc).value;
            cekpil+=1;
        }
    }

    if(cekpil==0){
        alert('Data belum terpilih.');
        return;
    }
    param='totrow='+cekpil+'&method=removedetail'+'&notransaksi='+notransaksi+'&noakun='+noakun;
    param+=allData;
    
    tujuan='pajak_slave_vatinvatout.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    viewdetail(notransaksi,noakun);
                    loadData();
                    closeDialog();
                }
            }
            else {
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

function del(notransaksi,npwp)
{
    param='method=delete'+'&notransaksi='+notransaksi+'&npwp='+npwp;
    tujuan='pajak_slave_vatinvatout.php';
    if(confirm(bahasa.notifandayakin)){
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

function form(){
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function formposting(notransaksi)
{
    form();
    param = 'method=formposting'+'&notransaksi='+notransaksi;
    tujuan = 'pajak_slave_vatinvatout.php';
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

function posting(notransaksi){
    tglposting=document.getElementById('tglposting').value;
    jumlahtax=document.getElementById('jumlahtax').value;
    param='method=posting'+'&notransaksi='+notransaksi+'&tglposting='+tglposting+'&jumlahtax='+jumlahtax;
    tujuan='pajak_slave_vatinvatout.php';
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
                    loadData();
                    closeDialog();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function viewdetail(notransaksi,npwp,noakun)
{
    form();
    param = 'method=viewdetail'+'&viewtipe=html'+'&notransaksi='+notransaksi+'&noakun='+noakun+'&npwp='+npwp;
    //alert(param);
    tujuan = 'pajak_slave_vatinvatout.php';
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
					data = con.responseText.split("####");
                    document.getElementById('containerd').innerHTML = data[0];
					loadfiles(notransaksi,noakun,npwp,data[1]);
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
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}
function dataKeExcel(ev,notransaksi,noakun, tujuan){
    if(noakun.substr(0,3)=='117'){
        met="ppnmasukanexcel"
    }
    if(noakun.substr(0,3)=='213'){
        met="ppnkeluaranexcel"
    }
    param = 'notransaksi='+notransaksi+'&method='+met+'&noakun='+noakun ;
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev);
}
function dataKeExcelLain(ev,notransaksi,noakun, tujuan){
    if(noakun.substr(0,3)=='117'){
        met="ppnmasukanexcellain"
    }
    if(noakun.substr(0,3)=='213'){
        met="ppnkeluaranexcel"
    }
    param = 'notransaksi='+notransaksi+'&method='+met+'&noakun='+noakun ;
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev);
}

function dataexceldetail(ev,notransaksi,noakun,npwp,tujuan){
    met="viewdetail";
    param = 'notransaksi='+notransaksi+'&method='+met+'&noakun='+noakun+'&viewtipe=excel'+'&npwp='+npwp;
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev);
}

function addfile(notransaksi,noakun,npwp,tipe) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById("upload").value);
	formdata.append("kriteriaefil", getValue('kriteriaefil'));
	formdata.append("notransaksi", notransaksi);
	formdata.append("noakun", noakun);
	formdata.append("npwp", npwp);
	formdata.append("tipe", tipe);
	if (document.getElementById("upload").value == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "pajak_slave_vatinvatout.php?method=submitfile", true);
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
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,noakun,npwp,tipe);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(notransaksi,noakun,npwp,tipe) {
	param = 'method=loadfiles&notransaksi='+notransaksi+'&noakun='+noakun+'&npwp='+npwp+'&tipe='+tipe;
	tujuan = 'pajak_slave_vatinvatout.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerupload').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

// function deletefile(notransaksi,noakun,npwp,tipe,namafile) {
function deletefile(notransaksi,namafile) {
	param = 'method=deletefile&namafile='+namafile+'&notransaksi='+notransaksi;
	tujuan = 'pajak_slave_vatinvatout.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// loadfiles(notransaksi,noakun,npwp,tipe);
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
