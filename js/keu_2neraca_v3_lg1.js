function getLaporanNeraca(tipe)
{
    pt=document.getElementById('pt');
    unit=document.getElementById('unit');
    periode=document.getElementById('periode');
    // tipelaporan =document.getElementById('tipelaporan');
    pt=pt.options[pt.selectedIndex].value;
    unit=unit.options[unit.selectedIndex].value;
    periode=periode.options[periode.selectedIndex].value;
    // tipelaporan =tipelaporan.options[tipelaporan.selectedIndex].value;
    param='pt='+pt+'&unit='+unit+'&periode='+periode+'&tipe='+tipe;
    tujuan='keu_slave_2neraca_v3_lg1.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    lertify.alert("Informasi", con.responseText);
                }
                else {
                    // showById('printPanel');
                    // document.getElementById('container').innerHTML=con.responseText;
                    if(tipe=='excel'){
                        window.open(con.responseText, "_blank");
                    }else{
                        document.getElementById('container').innerHTML=con.responseText;
                    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}


function lihatDetailNeraca(nourut,periode,pt,unit,ev){
    // gudang=document.getElementById('gudang');
    // gudang=gudang.options[gudang.selectedIndex].value;
    param = 'method=html' + '&nourut=' + nourut + '&periode=' + periode + '&pt=' + pt+ '&unit=' + unit;
    title="Data Detail "+nourut+ " "+periode+ " "+pt+" "+unit;
//      showDialog1(title,"<iframe frameborder=0 style='width:845px;height:395px'"+
//     " src='keu_slave_2neraca_detail_v3.php?"+param+"'></iframe>",'850','400',ev);	
//     var dialog = document.getElementById('dynamic1');
//     dialog.style.top = '50px';
//     dialog.style.left = '15%';	

    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='keu_slave_2neraca_detail_v3.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('40%','70%');
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
						alertify.alert("Informasi", con.responseText);
						
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
    pt=document.getElementById('pt').value;
    param='proses=getUnit2'+'&pt='+pt;
    
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
					alertify.alert("Informasi", con.responseText);
				} else {
					document.getElementById('unit').innerHTML=con.responseText;  
				}
			} else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
} 