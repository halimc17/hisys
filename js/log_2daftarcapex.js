function displayList()
{
    document.getElementById('tglcr').value='';
    document.getElementById('tglcarismp').value='';
    document.getElementById('notranscr').value='';
    loadData(0);
}

function loadData(num){
    notranscr=document.getElementById('notranscr').value;
    tglcr=document.getElementById('tglcr').value;
    tglcarismp=document.getElementById('tglcarismp').value;

    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notranscr=' + notranscr;
    }
    if (tglcr != '') {
        param += '&tglcr=' + tglcr;
    }
    if (tglcarismp != '') {
        param += '&tglcarismp=' + tglcarismp;
    }
    tujuan='log_slave_2daftarcapex.php';
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
    tujuan = 'log_slave_2daftarcapex.php';
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

function previewpdf(notransaksi,ev)
{
    param='notransaksi='+notransaksi;
    tujuan = 'log_slave_capexpdf.php?'+param;   
    title='';
    width='1000';
    height='400';
    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
    showDialog1(title,content,width,height,ev);
}

function cancel_asset()
{
        closeDialog();
}