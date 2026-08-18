function displayList()
{
    document.getElementById('nopengajuancr').value='';
    document.getElementById('tglcr').value='';
    document.getElementById('listdata').style.display = 'block';
    loadData(0);
}


function loadData(num){
	nopengajuancr=document.getElementById('nopengajuancr').value;
    tglcr=document.getElementById('tglcr').value;

    param='method=loadData';
	param+='&page='+num;

    if (nopengajuancr != '') {
        param += '&nopengajuancr=' + nopengajuancr;
    }
    if (tglcr != '') {
        param += '&tglcr=' + tglcr;
    }
	tujuan='sdm_slave_persetujuansp.php';
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

function disetujuisp1(nopengajuan)
{
    param='method=disetujuisp1'+'&nopengajuan='+nopengajuan;
    tujuan='sdm_slave_persetujuansp.php';
    if(confirm('Anda yakin ingin menyetujui pengajuan ini?'))
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
                                        //document.getElementById('contain').innerHTML=con.responseText;	
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

// function ditolaksp(nopengajuan,persetujuan)
// {
//     param='method=ditolaksp'+'&nopengajuan='+nopengajuan+'&persetujuan='+persetujuan;
//     tujuan='sdm_slave_persetujuansp.php';
//     if(confirm('Anda yakin ingin menolak pengajuan ini?'))
//     { 
//         post_response_text(tujuan, param, respog);
//     }  
//     function respog()
//     {
//               if(con.readyState==4)
//               {
//                             if (con.status == 200) {
//                                     busy_off();
//                                     if (!isSaveResponse(con.responseText)) {
//                                             alert(con.responseText);
//                                     }
//                                     else 
//                                     {
//                                         //document.getElementById('contain').innerHTML=con.responseText;    
//                                         loadData();
//                                     }
//                             }
//                             else {
//                                     busy_off();
//                                     error_catch(con.status);
//                             }
//               } 
//     }
// }

function agree()
{
        width='';
        height='';
        content="<div id=containerform></div>";
        ev='event';
        title="Approval Form";
        showDialog1(title,content,width,height,ev);   
}

function formalasan(nopengajuan,persetujuan){
        agree();
        param='method=formalasan'+'&nopengajuan='+nopengajuan+'&persetujuan='+persetujuan;
        //alert(param);
        tujuan='sdm_slave_persetujuansp.php';
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

function disetujuisp2(nopengajuan){
        agree();
        param='method=formpejabat'+'&nopengajuan='+nopengajuan;
        tujuan='sdm_slave_persetujuansp.php';
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

function ditolaksp(){
    alasan=document.getElementById('alasan').value;
    nopengajuan=document.getElementById('nopengajuan').value;
    method=document.getElementById('method').value;
    persetujuan=document.getElementById('persetujuan').value;
    param='alasan='+alasan+'&nopengajuan='+nopengajuan+'&persetujuan='+persetujuan+'&method='+method;
    tujuan='sdm_slave_persetujuansp.php';
    // alert(param);
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    //alert(con.responseText);
                    cancelverifikasi();
                    loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function verifikasihrd(){
    penandatangan1=document.getElementById('penandatangan1').options[document.getElementById('penandatangan1').selectedIndex].value;
    penandatangan2=document.getElementById('penandatangan2').options[document.getElementById('penandatangan2').selectedIndex].value;
    nopengajuan=document.getElementById('nopengajuan').value;
    //keterangan=document.getElementById('keterangan').value;
    method=document.getElementById('method').value;
    karyawanid=document.getElementById('karyawanid').value;
    param='penandatangan1='+penandatangan1+'&penandatangan2='+penandatangan2+'&nopengajuan='+nopengajuan+'&method='+method+'&karyawanid='+karyawanid;
    tujuan='sdm_slave_persetujuansp.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    //alert(con.responseText);
                    cancelverifikasi();
                    loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function cancelverifikasi()
{
        closeDialog();
}

function previewsp(nopengajuan,ev)
{
   param='nopengajuan='+nopengajuan;
   tujuan = 'sdm_slave_sppdf.php?'+param;   
   title=nopengajuan;
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);
   
}

function form()
{
    width = '720';
    height = '';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><div id=containerd align=center style=\"width:680px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog2(title, content, width, height, ev); 
}


function viewdetail(nopengajuan)
{
    form();
    param = 'method=viewdetail' + '&nopengajuan=' + nopengajuan;
    tujuan = 'sdm_slave_persetujuansp.php';
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