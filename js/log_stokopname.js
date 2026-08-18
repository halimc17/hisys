// JavaScript Document
function delStok(unit,gudang,periode){
        param='proses=deletestok'+'&unit='+unit+'&gudang='+gudang+'&periode='+periode;
        tujuan='log_slave_stokopname.php';
        if(confirm('Anda yakin ingin menghapus data??'))
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
                                            loadNData();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                  } 
        }
    }
    
    function posting(unit,gudang,periode){
        param='proses=posting'+'&unit='+unit+'&gudang='+gudang+'&periode='+periode;
        tujuan='log_slave_stokopname.php';
        if(confirm('Anda yakin ingin memposting ??'))
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
                                            loadNData();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                  } 
        }
    }
    
    
    function getklbrg()
    {
        gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
        periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
        param='gudang='+gudang+'&periode='+periode+'&proses=getklbrg';
        tujuan='log_slave_stokopname.php';
         function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        document.getElementById('klbrg').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }    
        post_response_text(tujuan, param, respon);    
    }
    
    function getperiode()
    {
        gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
        param='gudang='+gudang+'&proses=getperiode';
        tujuan='log_slave_stokopname.php';
         function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        document.getElementById('periode').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }    
        post_response_text(tujuan, param, respon);    
    }
    
    function getkebun()
    {
        pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
        param='pt='+pt+'&proses=getkebun';
        tujuan='log_slave_stokopname.php';
         function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        document.getElementById('unit').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }    
        post_response_text(tujuan, param, respon);    
    }
    
    function getgudang()
    {
        unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
        param='unit='+unit+'&proses=getgudang';
        tujuan='log_slave_stokopname.php';
         function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        document.getElementById('gudang').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }    
        post_response_text(tujuan, param, respon);    
    }
    
    function cancelForm()
    {
            document.getElementById('pt').disabled=false;
            document.getElementById('unit').disabled=false;
            document.getElementById('gudang').disabled=false;
            document.getElementById('periode').disabled=false;
            document.getElementById('klbrg').disabled=false;
            document.getElementById('dtlFormAtas').disabled=false;
            document.getElementById('formInputan').style.display='none';
            document.getElementById('formTampil').innerHTML='';
    }
    
    function getCustomer(kdbrg,kdrkn,kntrk,nodo)
    {
         if((kdbrg==0)||(kdrkn==0)||(kntrk==0))
         {
         kdBrg=document.getElementById('kdBrg').options[document.getElementById('kdBrg').selectedIndex].value;
         param='proses=getCustomer'+'&kdBrg='+kdBrg;
         }
         else
         {
             l=document.getElementById('kdBrg');
    
            for(a=0;a<l.length;a++)
            {
                if(l.options[a].value==kdbrg)
                    {
                        l.options[a].selected=true;
                    }
            }
             param='proses=getCustomer'+'&kdBrg='+kdbrg;
             param+='&custId='+kdrkn;
         }
         tujuan='pabrik_slave_timbangan_pembeli.php';
         post_response_text(tujuan, param, respog);
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
                                                            //return;				
                                                            document.getElementById('custId').innerHTML=con.responseText;
                                                            if(kntrk!=0)
                                                                {
                                                                   getKontrak(kdrkn,kntrk,nodo);
                                                                                                                               
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
    function getKontrak(kdrkn,kntrkno,nodo)
    {
         if((kntrkno==0)||(kdrkn==0))
         {
         custId=document.getElementById('custId').options[document.getElementById('custId').selectedIndex].value;
         param='proses=getKontrak'+'&custId='+custId;
         }
         else
         {
             param='proses=getKontrak'+'&custId='+kdrkn;
             param+='&noKontrak='+kntrkno;
         }
         tujuan='pabrik_slave_timbangan_pembeli.php';
         post_response_text(tujuan, param, respog);
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
                                                            //return;				
                                                            document.getElementById('noKontrak').innerHTML=con.responseText;
                                                                                                                    getnodo(nodo);
                                                            // if((kntrkno!=0)||(kdrkn!=0))
                                                            // {
                                                                // getForm();
                                                            // }
    
                                                    }
                                            }
                                            else {
                                                    busy_off();
                                                    error_catch(con.status);
                                            }
                          }	
             } 
    }
    
    
    function getnodo(nodo){
            noKontrak=document.getElementById('noKontrak').options[document.getElementById('noKontrak').selectedIndex].value;
            param='proses=getnodo'+'&noKontrak='+noKontrak+'&nodo='+nodo;
            tujuan='pabrik_slave_timbangan_pembeli.php';
        post_response_text(tujuan, param, respog);
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
                                                            //return;				
                                                            document.getElementById('nodo').innerHTML=con.responseText;
                                                            if((nodo!=0)||(nodo!='')) {
                                                                getForm();
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
    
    function getForm()
    {
      gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
      periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
      klbrg=document.getElementById('klbrg').options[document.getElementById('klbrg').selectedIndex].value;
           
    
            param="proses=getForm";
            param += "&gudang="+gudang;
            param += "&periode="+periode;
            param += "&klbrg="+klbrg;
    
            tujuan='log_slave_stokopname.php';
    
            post_response_text(tujuan, param, respog);
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
                                                            //return;
                                                            document.getElementById('pt').disabled=true;
                                                            document.getElementById('unit').disabled=true;
                                                            document.getElementById('gudang').disabled=true;
                                                            // document.getElementById('dtlFormAtas').disabled=true;
                                                            document.getElementById('formInputan').style.display='block';
                                                            document.getElementById('formTampil').innerHTML=con.responseText;
    
                                                    }
                                            }
                                            else {
                                                    busy_off();
                                                    error_catch(con.status);
                                            }
                          } 
             }  
    
    }
    
    
    function getForm2(kdorg,gdg,per)
    {
      gudang=gdg;
      periode=per;
    
            param="proses=getForm2";
            param += "&gudang="+gudang;
            param += "&periode="+periode;
    
            // alert(param);
    
            tujuan='log_slave_stokopname.php';
    
            post_response_text(tujuan, param, respog);
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
                                                            //return;
                                                            document.getElementById('pt').disabled=true;
                                                            document.getElementById('unit').disabled=true;
                                                            document.getElementById('gudang').disabled=true;
                                                            document.getElementById('dtlFormAtas').disabled=true;
                                                            document.getElementById('formInputan').style.display='block';
                                                            document.getElementById('formTampil').innerHTML=con.responseText;
    
                                                    }
                                            }
                                            else {
                                                    busy_off();
                                                    error_catch(con.status);
                                            }
                          } 
             }  
    
    }
    
    
    
    
    
    
    
    
    
    // function getForm()
    // {
    //         custId=document.getElementById('custId').options[document.getElementById('custId').selectedIndex].value;
    //         kdBrg=document.getElementById('kdBrg').options[document.getElementById('kdBrg').selectedIndex].value;
    //         noKontrak=document.getElementById('noKontrak').options[document.getElementById('noKontrak').selectedIndex].value;
    //         nodo=document.getElementById('nodo').options[document.getElementById('nodo').selectedIndex].value;
    
    //         param="proses=getForm";
    //         param += "&custId="+custId;
    //         param += "&kdBrg="+kdBrg;
    //         param += "&noKontrak="+noKontrak;
    //         param += "&nodo="+nodo;
    
    //         tujuan='pabrik_slave_timbangan_pembeli.php';
    //         //alert(param);
    // //	return;
    //         post_response_text(tujuan, param, respog);
    //         function respog()
    //         {
    //                       if(con.readyState==4)
    //                       {
    //                                 if (con.status == 200) {
    //                                                 busy_off();
    //                                                 if (!isSaveResponse(con.responseText)) {
    //                                                         alert(con.responseText);
    //                                                 }
    //                                                 else {
    //                                                         //alert(con.responseText);
    //                                                         //return;
    //                                                         document.getElementById('kdBrg').disabled=true;
    //                                                         document.getElementById('custId').disabled=true;
    //                                                         document.getElementById('noKontrak').disabled=true;
    //                                                         document.getElementById('dtlFormAtas').disabled=true;
    //                                                         document.getElementById('formInputan').style.display='block';
    //                                                         document.getElementById('formTampil').innerHTML=con.responseText;
    
    //                                                 }
    //                                         }
    //                                         else {
    //                                                 busy_off();
    //                                                 error_catch(con.status);
    //                                         }
    //                       }	
    //          } 	
    
    // }
    
    
    function loadNData()
    {
            param='proses=loadData';
            tujuan='log_slave_stokopname.php';
            //alert(tujuan);
            function respon(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                                            //alert(con.responseText);
                                            document.getElementById('contain').innerHTML=con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
            post_response_text(tujuan, param, respon);
    }
    function cariBast(num)
    {
                    param='proses=loadData';
                    param+='&page='+num;
                    tujuan = 'pabrik_slave_timbangan_pembeli.php';
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
    function fillField(kdorg,gdg,per) 
    {
       // if((koderekanan=='')||(nokontrak==''))
       //     {
       //         alert("Error: No sales contract registred for this transaction");
       //         return;
       //     }
           getForm2(kdorg,gdg,per);
       // getCustomer(kodebarang,koderekanan,nokontrak,nodo);
    }
    function displayList()
    {
        // document.getElementById('txtsearch').value='';
        // document.getElementById('tgl_cari').value='';
        // document.getElementById('txtsearchKntrk').value='';
        cancelForm();
        loadNData();
    }
    function cariTransaksi()
    {
            txtSearch=document.getElementById('txtsearch').value;
            txtTgl=document.getElementById('tgl_cari').value;
            txtKntrk=document.getElementById('txtsearchKntrk').value;
    
            param='txtSearch='+txtSearch+'&txtTgl='+txtTgl+'&proses=cariTransaksi';
            param+='&txtKntrk='+txtKntrk;
            //alert(param);
            tujuan='pabrik_slave_timbangan_pembeli.php';
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
    function cariBastTransaksi(num)
    {
                    txtKntrk=document.getElementById('txtsearchKntrk').value;
                    param='txtSearch='+txtSearch+'&txtTgl='+txtTgl+'&proses=cariTransaksi';
                    param+='&txtKntrk='+txtKntrk;
                    param+='&page='+num;
                    tujuan = 'pabrik_slave_timbangan_pembeli.php';
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
    function saveAll(x)
    {
        kdorg=document.getElementById('kdorg'+x).innerHTML;
        kdgudang=document.getElementById('kdgdg'+x).innerHTML;
        kdbarang=document.getElementById('kdbrg'+x).innerHTML;
        per=document.getElementById('periode'+x).innerHTML;
        stok=document.getElementById('stoksistem'+x).innerHTML;
        kuantitas=document.getElementById('kuantitas_'+x).value;
        totRow=document.getElementById('jmlhRow').value;
        param='proses=insert'+'&kdorg='+kdorg+'&kdgudang='+kdgudang+'&kdbarang='+kdbarang+'&per='+per+'&stok='+stok+'&kuantitas='+kuantitas;
        tujuan='log_slave_stokopname.php';
        if(x==1 && confirm('Are you sure ?'))
        post_response_text(tujuan, param, respog);
        else
        post_response_text(tujuan, param, respog);
                 document.getElementById('baris_'+x).style.backgroundColor='orange';
         function respog(){
                    if (con.readyState == 4) {
                            if (con.status == 200) {
                                    busy_off();
                                    if (!isSaveResponse(con.responseText)) {
                                            alert(con.responseText);
                                            document.getElementById('baris_'+x).style.backgroundColor='red';
                                    }
                                    else {
                                           // document.getElementById('contain').innerHTML=con.responseText;
                                             b=x;
                            row=x+1;
                            x=row;
                            if(x<=totRow)
                             {   
                                 document.getElementById('baris_'+b).style.backgroundColor='green';
                                 document.getElementById('kuantitas_'+b).disabled=true;
                                 document.getElementById('simTmbl2_'+b).disabled=true;
                                 saveAll(x);
                             }
                             else
                             {
                                 //displayList();
                                 document.getElementById('baris_'+b).style.backgroundColor='green';
                                 displayList();
                                // batal();
                               // alert('Done');
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
    function saveForm(unit,kdgdg,kdbrg,periode,saldoakhirqty,x)
    {
        kdorg=unit;
        kdgudang=kdgdg;
        kdbarang=kdbrg;
        per=periode;
        stok=saldoakhirqty;
        kuantitas=document.getElementById('kuantitas_'+x).value;
        param='proses=insert'+'&kdorg='+kdorg+'&kdgudang='+kdgudang+'&kdbarang='+kdbarang+'&per='+per+'&stok='+stok+'&kuantitas='+kuantitas;
        tujuan='log_slave_stokopname.php';
        post_response_text(tujuan, param, respog);
        document.getElementById('baris_'+x).style.backgroundColor='orange';
         function respog(){
                    if (con.readyState == 4) {
                            if (con.status == 200) {
                                    busy_off();
                                    if (!isSaveResponse(con.responseText)) {
                                            alert(con.responseText);
                                            document.getElementById('baris_'+x).style.backgroundColor='red';
                                    }
                                    else {
                                           // document.getElementById('contain').innerHTML=con.responseText;
                                 if(confirm("Continue input ?"))
                                 {
                                 b=x;
                                 document.getElementById('baris_'+b).style.backgroundColor='green';
                                 document.getElementById('kuantitas_'+b).disabled=true;
                                 document.getElementById('simTmbl2_'+b).disabled=true;
                                 }
                                 else
                                     {
                                         displayList();
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
    function printFile(param,tujuan,title,ev)
    {
       tujuan=tujuan+"?"+param;  
       width='700';
       height='400';
       content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
       showDialog1(title,content,width,height,ev); 	
    }
    function locoData(kodebarang,koderekanan,nokontrak,nodo)
    {
            param="proses=updateKgTimbangan";
            param += "&custId="+koderekanan;
            param += "&kdBrg="+kodebarang;
            param += "&noKontrak="+nokontrak;
            param += "&nodo="+nodo;
    
            tujuan='pabrik_slave_timbangan_pembeli.php';
            if(confirm("This uses Locco, are you sure?"))
            post_response_text(tujuan, param, respog);
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
                                                            //return;
                                                            loadNData();
                                                    }
                                            }
                                            else {
                                                    busy_off();
                                                    error_catch(con.status);
                                            }
                          }	
             } 	
    }
    