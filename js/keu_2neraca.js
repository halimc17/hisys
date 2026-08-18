function getLaporanNeraca()
{
        pt	=document.getElementById('pt');
        unit    =document.getElementById('gudang');
        periode =document.getElementById('periode');
        periode1=document.getElementById('periode1');       
        pt	=pt.options[pt.selectedIndex].value;
        unit	=unit.options[unit.selectedIndex].value;
        periode	=periode.options[periode.selectedIndex].value;
        periode1	=periode1.options[periode1.selectedIndex].value;
            revisi =document.getElementById('revisi');
            revisi=revisi.options[revisi.selectedIndex].value;        
        param='pt='+pt+'&unit='+unit+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
        tujuan='keu_slave_2neraca.php';
        post_response_text(tujuan, param, respog);
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert('ERROR TRANSACTION,\n' + con.responseText);
                                        }
                                        else {
                                                // showById('printPanel');
                                                document.getElementById('container').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }		
}


function lihatDetailNeraca(noakuna,noakuns,periode,tipe,pt,unit,ev,codeurut,kodelaporan){
    gudang=document.getElementById('gudang');
    gudang=gudang.options[gudang.selectedIndex].value;
    param = 'method=html' + '&noakuna=' + noakuna + '&noakuns=' + noakuns + '&periode=' + periode + '&tipe=' + tipe+ '&pt=' + pt+ '&unit=' + gudang+ '&codeurut=' + codeurut+ '&kodelaporan=' + kodelaporan;
    title="Data Detail";
     showDialog1(title,"<iframe frameborder=0 style='width:845px;height:395px'"+
    " src='keu_slave_2neraca_detail_v2.php?"+param+"'></iframe>",'850','400',ev);	
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';	
}


function getReg()
{
    pt=document.getElementById('pt').value;
    param='proses=getReg'+'&pt='+pt;
    
    tujuan='keu_slave_2jurnal_option.php';
    post_response_text(tujuan, param, respog);    
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
						
				}
				else {
					//alert(con.responseText);
					document.getElementById('regional').innerHTML=con.responseText;  
					getUnit();
				}
			} else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}

//get unit
function getUnit()
{
    regional=document.getElementById('regional').value;
    pt=document.getElementById('pt').value;
    param='proses=getUnit'+'&regional='+regional+'&pt='+pt;
    
    //alert(param);
    tujuan='keu_slave_2jurnal_option.php';
    post_response_text(tujuan, param, respog);    
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('gudang').innerHTML=con.responseText;  
				}
			} else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}