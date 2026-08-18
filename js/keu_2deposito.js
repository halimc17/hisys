function cancel(){
    document.getElementById('unit').value='';
    document.getElementById('jenis').value='';
    document.getElementById('printContainer').innerHTML='';
}

function excel(tipe,ev){
    unit=document.getElementById('unit').value;
    jenis=document.getElementById('jenis').value;

    tujuan = 'keu_slave_2deposito.php';
    param ='unit='+unit+'&method=preview'+'&tipe='+tipe+'&jenis='+jenis;
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev);   
}

function html(tipe){
    unit=document.getElementById('unit').value;
    jenis=document.getElementById('jenis').value;

    param ='unit='+unit+'&method=preview'+'&tipe='+tipe+'&jenis='+jenis;
    tujuan = 'keu_slave_2deposito.php';
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
    tujuan = 'keu_slave_2deposito.php';
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