// JavaScript Document

function get_data_transaksi(notransaksi,kolom) 
{
	met='get_form_approval';
	param='method='+met+'&notransaksi='+notransaksi+'&kolom='+kolom;
	tujuan='log_slave_persetujuan_barangmasuk.php';
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
					alert("Persetujuan transaksi berhasil.");
                    refresh_data();
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

function agree_po()
{
	width='400';
    height='200';
    content="<div id=container></div>";
    ev='event';
    title="Disapproval Form";
    showDialog1(title,content,width,height,ev);
}

function rejected_transaksi(id,kolom)
{
    agree_po();
    
    met='get_form_rejected';
    param='method='+met+'&notransaksi='+id+'&kolom='+kolom;
    tujuan='log_slave_persetujuan_barangmasuk.php';
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
                        //alert(con.responseText);
                        document.getElementById('container').innerHTML=con.responseText;
                        //show_list();
                        //alert('Berhasil');
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

function ditolakpo()
{
    alasan=document.getElementById('alasan').value;
    notransaksi=document.getElementById('nopo').value;
    kolom=document.getElementById('kolom').value;
    param='alasan='+alasan+'&notransaksi='+notransaksi+'&kolom='+kolom+'&method=reject_po';
    tujuan='log_slave_persetujuan_barangmasuk.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					alert("Disapprove barang masuk berhasil");
                    cancel_po();
                    refresh_data();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function previewBapb(notransaksi,ev)
{
        param='notransaksi='+notransaksi;
        tujuan = 'log_slave_print_bapb_pdf.php?'+param;	
 //display window
   title=notransaksi;
   width='800';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);

}

// function rejected_po(nopo,kolom) {
//     met='get_form_rejected';
//     param='method='+met+'&nopo='+nopo+'&kolom='+kolom;
//     tujuan='log_persetujuan_po_get_data.php';
//     function respog()
//     {
//         if(con.readyState==4)
//         {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                         alert(con.responseText);
//                 }
//                 else 
//                 {
//                     refresh_data();
//                 }
//             }
//             else {
//                     busy_off();
//                     error_catch(con.status);
//             }
//         } 
//     }   
//     post_response_text(tujuan, param, respog);  
// }

// function ajukan(nopo)
// {
//     param='proses=ajukan'+'&nopo='+nopo;
//     tujuan='log_slave_save_po_lokal.php';
//     if(confirm('Anda yakin ingin mengajukan transaksi ini ??'))
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
//                                         load_new_data();
//                                     }
//                             }
//                             else {
//                                     busy_off();
//                                     error_catch(con.status);
//                             }
//               } 
//     }
// }


function forward_po()
{

        kolom=document.getElementById('kolom').value;
        nik=document.getElementById('id_user').value;
        rnopo=document.getElementById('nopo').value;
        met=document.getElementById('method');
        met=met.value='insert_forward_po';
        param='id_user_frwd='+nik+'&method='+met+'&nopo='+rnopo+'&kolom='+kolom;
        tujuan='log_slave_persetujuan_po.php';
        //alert(param);
        /*return;*/
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
                                                                //alert(con.responseText);
                                                                closeDialog();
                                                                refresh_data();
                                                                //document.getElementById('contain').innerHTML=con.responseText;

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
function cancel_po()
{
        closeDialog();
}
function close_form_po()
{
        document.getElementById('test').style.display='none';
        document.getElementById('approve').style.display='block';
}
function close_po()
{
        rnopo=trim(document.getElementById('rnopo').value);
        met=document.getElementById('method');
        met=met.value='insert_close_po';
        usr_id=document.getElementById('user_id').value;

                param='nopo='+rnopo+'&method='+met+'&id_user='+usr_id;
                tujuan='log_slave_persetujuan_po.php';
                //alert(param);
                /*alert(param);
                return;*/
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
                                                                        //alert(con.responseText);
                                                                        //document.getElementById('contain').innerHTML=con.responseText;
                                                                        closeDialog();
                                                                        refresh_data();
                                                                        //alert('Berhasil');
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



// function rejected_po(nopo,kolom)
// {
//     agree_po();
//     met=document.getElementById('method').value;
//     rnopo=nopo;
//     met='get_form_rejected';
//     param='method='+met+'&nopo='+rnopo;
//     tujuan='log_persetujuan_po_get_data.php';
//     function respog()
//         {
//             if(con.readyState==4)
//             {
//                 if (con.status == 200) {
//                     busy_off();
//                     if (!isSaveResponse(con.responseText)) {
//                             alert(con.responseText);
//                     }
//                     else {
//                             //alert(con.responseText);
//                             document.getElementById('container').innerHTML=con.responseText;
//                             //show_list();
//                             //alert('Berhasil');
//                             return con.responseText;
//                     }
//                 }
//                 else {
//                     busy_off();
//                     error_catch(con.status);
//                 }
//             }	
//          } 	
//     post_response_text(tujuan, param, respog);		
// }
function rejected_po_proses()
{
        rnopo=trim(document.getElementById('rnopo').value);
        met=document.getElementById('method');
        met=met.value='rejected_pp_ex';
        usr_id=document.getElementById('user_id').value;
        param='nopo='+rnopo+'&method='+met+'&id_user='+usr_id;
        tujuan='log_slave_persetujuan_po.php';
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
                                                                //alert(con.responseText);
                                                                //document.getElementById('contain').innerHTML=con.responseText;
                                                                closeDialog();
                                                                refresh_data();
                                                                //alert('Berhasil');
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
// function refresh_data()
// {
//         param='method=list_new_data';
//         tujuan='log_slave_persetujuan_po.php';
//         function respog()
//                 {
//                                   if(con.readyState==4)
//                                   {
//                                                 if (con.status == 200) {
//                                                         busy_off();
//                                                         if (!isSaveResponse(con.responseText)) {
//                                                                 alert(con.responseText);
//                                                         }
//                                                         else {
//                                                                 //alert(con.responseText);
//                                                                 document.getElementById('contain').innerHTML=con.responseText;
//                                                                 document.getElementById('txtsearch').value='';
//                                                                 document.getElementById('tgl_cari').value='';
//                                                                 //alert('Berhasil');
//                                                         }
//                                                 }
//                                                 else {
//                                                         busy_off();
//                                                         error_catch(con.status);
//                                                 }
//                                   }	
//                  } 	
//                  post_response_text(tujuan, param, respog);	
// }

function refresh_data(num)
{
	txtsearch=document.getElementById('txtsearch').value;
    tgl_cari=document.getElementById('tgl_cari').value;

    param='method=list_new_data';
    param+='&page='+num;

    if (txtsearch != '') {
        param += '&txtsearch=' + txtsearch;
    }
    if (tgl_cari != '') {
        param += '&tgl_cari=' + tgl_cari;
    }
    tujuan='log_slave_persetujuan_barangmasuk.php';
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

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    refresh_data(paged);  
}

function cariNopo()
{
        txtSearch=trim(document.getElementById('txtsearch').value);
        tglCari=trim(document.getElementById('tgl_cari').value);
        met=document.getElementById('method');
        met=met.value='list_new_data';
        param='txtSearch='+txtSearch+'&tglCari='+tglCari+'&method='+met;
        tujuan='log_slave_persetujuan_po.php';
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
                                                                //alert(con.responseText);
                                                                document.getElementById('contain').innerHTML=con.responseText;
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


function cariBast(num)
{
                param='method=list_new_data_release_po';
                param+='&page='+num;
                tujuan = 'log_slave_persetujuan_po.php';
                post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('contain').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}
