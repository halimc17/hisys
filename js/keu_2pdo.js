//########################################################
//#################  T A B   R E K A P  ##################
//########################################################

function cancel()
{
	document.getElementById('unit').value='';
    document.getElementById('per').value='';
	document.getElementById('jenis').value='';
	document.getElementById('printContainer').innerHTML='';
}

function detail(spk,per,tipe,ev)
{
	//param = 'vhc=' + vhc + '&per=' + per+ '&tipe=' + tipe
	param='method=detail'+'&spk='+spk+'&tipe='+tipe+'&per='+per;
	tujuan = 'keu_slave_2pdo.php' + "?" + param;
	// width = '800';
	// height = '400';
	// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1('Detail Transaksi' + spk, content, width, height, ev);
    alertify.popup("Detail Transaksi"+spk,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}
    


function excel(tipe,ev){
	unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
	jenis=document.getElementById('jenis').value;
	if(jenis=='')
	{
		alertify.alert("Informasi",'lengkapi pengisian');return;
	}
	tujuan = 'keu_slave_2pdo.php';
	param ='unit='+unit+'&per='+per+'&tipe='+tipe+'&jenis='+jenis+'&method='+jenis;
    judul='Report Ms.Excel';
    alertify.popup(judul,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='keu_slave_2pdo.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');        
    // printFile(param,tujuan,judul,ev);	
}


function html(tipe)
{
	unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;
	jenis=document.getElementById('jenis').value;
	if(jenis=='')
	{
		alertify.alert("Informasi",'lengkapi pengisian');return;
	}
	param ='unit='+unit+'&per='+per+'&tipe='+tipe+'&jenis='+jenis+'&method='+jenis;
	tujuan = 'keu_slave_2pdo.php';
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
                    alertify.alert("Informasi",con.responseText);
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


