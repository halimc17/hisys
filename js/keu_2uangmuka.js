function cancel(){
    closeDialog();
    document.getElementById('unit').value = '';
    document.getElementById('noakun').value = '';
    document.getElementById('tgl1').value = '';
    document.getElementById('tgl2').value = '';
    document.getElementById('printContainer').innerHTML = '';
}
function getUnit(){
    pt = getValue('pt');
    // periode = getValue('periode');
    // periode2 = getValue('periode2');
    // tipe = getValue('tipe');
    
    param = 'method=getUnit&pt='+pt;
    // alertify.alert("Informasi",param);
    
    tujuan = 'keu_slave_2uangmuka.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
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

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0  width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);  
}

function preview(tipe){
    
    unit=document.getElementById('unit').value;
    pt=document.getElementById('pt').value;
    noakun=document.getElementById('noakun').value;
    tgl1=document.getElementById('tgl1').value;
    tgl2=document.getElementById('tgl2').value;
    
    if(pt=='' || noakun=='' || tgl1==''|| tgl2==''){
        alertify.alert("Informasi",'Lengkapi pengisian');return;
    }
    if(tgl2 < tgl1){
        alertify.alert("Informasi",'Tanggal tidak boleh kecil dari tanggal awal');return;
    }
    // if(tipe!='html'){
    //     judul=tipe;
    //     ev='event';
    //     printFile(param,tujuan,judul,ev);
    // }

    
    param = 'method=preview';
    param += '&unit=' + unit+'&noakun=' + noakun+'&pt=' + pt;
    param += '&tgl1=' + tgl1+'&tgl2=' + tgl2+'&tipe='+tipe;
    tujuan = 'keu_slave_2uangmuka.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert("Informasi",con.responseText);
                } else {
                    if(tipe=='html'){
                        document.getElementById('printContainer').innerHTML = con.responseText;
                        leftFixedTable();
                    }else if (tipe == 'excel') {
                        tujuan=tujuan+"?"+param;  
                        printnopopup(tujuan);
                    } else if (tipe == 'pdf') {
                        title = 'Report PDF';
                        tujuan = tujuan + "?" + param;
                        // width = 1024;
                        // height = 400;
                        // content = "<iframe frameborder=0 width=100% height=100% src="+tujuan+"></iframe>";
                        // showDialog4(title, content, width, height, 'event');
                        alertify.popuppdf(title,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printnopopup(url) {
    // alertify.alert("Informasi",url);
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}


function popup_ap(nodoc,dari1,dari2,org,akun){
    // width = '';
    // height = '';
    // content = "<fieldset style=width:280px><legend>List Detail</legend><div id=containeraju align=center style=\"width:100%;max-height:200px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // title = "Form Dokumen: "+nodoc;
    // showDialog5(title, content, width, height, ev);
    param = 'method=popup_ap' + '&nodoc=' + nodoc+ '&dari1=' + dari1+ '&dari2=' + dari2+ '&org=' + org+ '&akun=' + akun;
    // alertify.alert("Informasi",param);
    tujuan = 'keu_slave_2uangmuka.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    // document.getElementById('containeraju').innerHTML = con.responseText;
                    title = "Form Dokumen: "+nodoc;
                    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='keu_slave_2uangmuka.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function printnopopup(url) {
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}
