function displayList()
{
    document.getElementById('tglevaluasicr').value='';
    document.getElementById('listdata').style.display = 'block';
    loadData(0);
}


function loadData(num){
    tglevaluasicr=document.getElementById('tglevaluasicr').value;

    param='method=loadData';
    param+='&page='+num;
    
    if (tglevaluasicr != '') {
        param += '&tglevaluasicr=' + tglevaluasicr;
    }
    tujuan='sdm_slave_persetujuanpenilaian.php';
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
                    document.getElementById('container').innerHTML = isdt[0];
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
    width = '700';
    height = '';
    content = "<fieldset><div id=containerd align=center style=\"width:680px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(tglevaluasi,karyawan)
{
    form();
    param = 'method=viewdetail' + '&tglevaluasi=' + tglevaluasi + '&karyawan=' + karyawan;
    tujuan = 'sdm_slave_persetujuanpenilaian.php';
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

function agree()
{
        width='';
        height='';
        content="<div id=containerform></div>";
        ev='event';
        title="Approval Form";
        showDialog1(title,content,width,height,ev);   
}

function formalasan(tglevaluasi,karyawan){
        agree();
        param='method=formalasan'+ '&tglevaluasi=' + tglevaluasi + '&karyawan=' + karyawan;
        tujuan='sdm_slave_persetujuanpenilaian.php';
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
                    document.getElementById('containerform').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }  
        post_response_text(tujuan, param, respog);  

}

function ditolakpp(){
    alasan=document.getElementById('alasan').value;
    tgleva=document.getElementById('tglevaluasi').value;
    method=document.getElementById('method').value;
    karyawan=document.getElementById('karyawanid').value;
    param='alasan='+alasan+'&tgleva='+tgleva+'&karyawan='+karyawan+'&method='+method;
    tujuan='sdm_slave_persetujuanpenilaian.php';
    // alert(param);
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    cancelform();
                    loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function cancelform()
{
        closeDialog();
}

function disetujuisp(tglevaluasi,karyawan)
{
    param='method=disetujuisp'+ '&tgleva='+tglevaluasi + '&karyawan=' + karyawan;
    tujuan='sdm_slave_persetujuanpenilaian.php';
    if(confirm('Anda yakin ingin menyetujui pengajuan ini?'))
    {
        // alert(param);
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

function previewep(tglevaluasi,karyawan,ev)
{
    param='tglevaluasi='+tglevaluasi+'&karyawan='+karyawan;
    tujuan = 'sdm_slave_eppdf.php?'+param;   
    title='Detail Pengajuan';
    width='700';
    height='400';
    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
    showDialog1(title,content,width,height,ev);
}