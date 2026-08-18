function load_unit_kpd()
{
    pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
    param='pt='+pt+'&proses=load_unit_kpd';
    tujuan='keu_slave_2transaksirutin.php';
    post_response_text(tujuan, param, respog);

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                }
                else {
                    pisah=con.responseText.split('###');
                    document.getElementById('unit').innerHTML=pisah[0];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function lihatDetail(notransaksi,ev)
{
   param='notransaksi='+notransaksi+'&proses=detail';
   tujuan='keu_slave_2transaksirutin.php'+"?"+param;  
//    width='950';
//    height='400';

//    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
//    showDialog1('Detail Transaksi'+notransaksi,content,width,height,ev); 

   alertify.popup('Detail Transaksi',"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('50%','70%');
}