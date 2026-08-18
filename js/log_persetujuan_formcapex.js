function displayList()
{
    document.getElementById('tglcr').value='';
    document.getElementById('notranscr').value='';
    loadData(0);
}


function loadData(num){
    notranscr=document.getElementById('notranscr').value;
    tglcr=document.getElementById('tglcr').value;

    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notranscr=' + notranscr;
    }
    if (tglcr != '') {
        param += '&tglcr=' + tglcr;
    }
    tujuan='log_slave_persetujuan_formcapex.php';
    // alert(param);
    // return;
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    //alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
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
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><div id=containerd align=center style=\"width:680px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(notransaksi)
{
    form();
    param = 'method=viewdetail' + '&notransaksi=' + notransaksi;
    tujuan = 'log_slave_persetujuan_formcapex.php';
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

function approved_capex(notransaksi,karyawanid)
{
	agree_po();
    param='method=approved_capex'+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid;
    tujuan='log_slave_persetujuan_formcapex.php';
    
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
                    document.getElementById('container').innerHTML=con.responseText;
					return con.responseText;
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

function disetejuicapex()
{
	alasan=document.getElementById('alasan').value;
    notransaksi=document.getElementById('notransaksi').value;
    karyawanid=document.getElementById('karyawanid').value;
    param='alasan='+alasan+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&method=disetejuicapex';
    tujuan='log_slave_persetujuan_formcapex.php';
    if(confirm('Anda yakin ingin menyetujui pengajuan ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
		cancel_asset();
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

function agree_po()
{
        width='';
        height='';
        //nopp=document.getElementById('nopp_'+id).value;
        content="<div id=container></div>";
        ev='event';
        title="Approval Form";
        showDialog1(title,content,width,height,ev);
        //get_data_pp();    
}

function get_data_asset(notransaksi,karyawanid)
{
    agree_po();
    param='method=get_data_asset'+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid;
    tujuan='log_slave_persetujuan_formcapex.php';
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
                        return con.responseText;
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

function simpanasset(){
    notransaksi=document.getElementById('notransaksi').value;
    karyawanid=document.getElementById('karyawanid').value;
    totRow=document.getElementById('totrows').value;
    var allData='';
    for(dwc=0;dwc<totRow;dwc++){
        allData+="&kdbrg["+dwc+"]="+document.getElementById('kdbrg_'+dwc).value;
        allData+="&kdasset["+dwc+"]="+document.getElementById('kdasset_'+dwc).value;
        allData+="&subasset["+dwc+"]="+document.getElementById('subasset_'+dwc).value;
        allData+="&nama["+dwc+"]="+document.getElementById('nama_'+dwc).value;
        allData+="&jbiaya["+dwc+"]="+document.getElementById('jbiaya_'+dwc).value;
		if(document.getElementById('subasset_'+dwc).value==''){
			alert("Warning : Sub Tipe Asset tidak boleh Kosong !!!"); return;
		}
    }
    param='notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&totRow='+totRow+'&method=simpanasset';
    param+=allData;
    // alert(param);
    // return;
    tujuan='log_slave_persetujuan_formcapex.php';
    // alert(param);
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    cancel_asset();
                    loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function rejected_capex(notransaksi,karyawanid)
{
    agree_po();
    param='method=rejected_capex'+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid;
    tujuan='log_slave_persetujuan_formcapex.php';
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
                        return con.responseText;
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

function ditolakcapex(){
    alasan=document.getElementById('alasan').value;
    notransaksi=document.getElementById('notransaksi').value;
    karyawanid=document.getElementById('karyawanid').value;
    param='alasan='+alasan+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&method=ditolakcapex';
    // alert(param);
    // return;
    tujuan='log_slave_persetujuan_formcapex.php';
    // alert(param);
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    cancel_asset();
                    loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function cancel_asset()
{
        closeDialog();
}