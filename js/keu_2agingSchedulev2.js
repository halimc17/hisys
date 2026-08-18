function getunit(){
    pt=document.getElementById('pt').value;
    param = 'method=getunit'+'&pt='+pt;
    tujuan = 'keu_slave_2agingschedulev2.php';
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
                    document.getElementById('unit').innerHTML = con.responseText;
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

function cancel(){
    document.getElementById('pt').value='';
    document.getElementById('unit').value='';
    document.getElementById('tipeform').value='';
    document.getElementById('jenis').value='';
    document.getElementById('printContainer').innerHTML='';
}

function excel(tipe,ev){
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    tanggal=document.getElementById('tanggal').value;
    tipeform=document.getElementById('tipeform').value;
    jenis=document.getElementById('jenis').value;

    param ='pt='+pt+'&unit='+unit+'&method=preview'+'&tanggal='+tanggal+'&tipeform='+tipeform+'&tipe='+tipe+'&jenis='+jenis;
    tujuan = 'keu_slave_2agingschedulev2.php';
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev);   
}

function html(tipe){
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    tanggal=document.getElementById('tanggal').value;
    tipeform=document.getElementById('tipeform').value;
    jenis=document.getElementById('jenis').value;

    param ='pt='+pt+'&unit='+unit+'&method=preview'+'&tanggal='+tanggal+'&tipeform='+tipeform+'&tipe='+tipe+'&jenis='+jenis;
    tujuan = 'keu_slave_2agingschedulev2.php';
    post_response_text(tujuan, param, respon);
    function respon()
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
                else{
                    document.getElementById('printContainer').innerHTML=con.responseText;
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

function form()
{
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(notrans)
{
    form();
    param = 'method=viewdetail'+'&notransaksi='+notrans;
    tujuan = 'keu_slave_2agingschedulev2.php';
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