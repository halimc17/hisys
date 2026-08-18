// JavaScript Document
function clearForm(){
    document.getElementById('volAir').value='';
    document.getElementById('tgl').value='';
    //document.getElementById('dataIsian').innerHTML='';
    //form cari
//    document.getElementById('jnsCr').value='';
    document.getElementById('tglCr').value='';
    document.getElementById('tglCr2').value='';
}
function displayList(){
        document.getElementById('listData').style.display='block';
        document.getElementById('headher').style.display='none';
        clearForm();
        loadData(0);
}
function lockForm(){
        document.getElementById('tgl').disabled=true;
        document.getElementById('tombolHeader').style.display="none";
}
function unlockForm(){
        document.getElementById('tgl').disabled=false;
        document.getElementById('tombolHeader').style.display="block";
        clearForm();
}

function loadData(num){
    tgl=document.getElementById('tglCr').value;
    tgl2=document.getElementById('tglCr2').value;
    
    param ='proses=loadNewData&page=' + num;
    param+='&tgl='+tgl+'&tgl2='+tgl2;
    tujuan = 'pabrik_slave_mr_wtp.php';
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
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
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

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);    
}

function getTable(){
    jns=document.getElementById('jenis');
    jns=jns.options[jns.selectedIndex].value;
    tujuan = 'pabrik_slave_mr_wtp.php';
    param='proses=getTable'+'&jenis='+jns;
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
                else {
                    document.getElementById('dataIsian').innerHTML = con.responseText;
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
function saveDt(){
    tanggal=document.getElementById('tgl').value;
    volAir=document.getElementById('volAir').value;
    tujuan ='pabrik_slave_mr_wtp.php';
    param='proses=saveDt'+'&volAir='+volAir+'&tanggal='+tanggal;
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                   displayList(0);
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
function deletehead(notrans){
    param='tanggal='+notrans+'&proses=deletehead';
    tujuan='pabrik_slave_mr_wtp.php';
    if(confirm("Anda Yakin Menghapus?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            //document.getElementById('tmbLheader').innerHTML='';
                            loadData(0);
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function detaildt(title,notransaksi){
    title=title+" "+notransaksi;
    var width='';
    var height='';
    formListPP(title,width,height);
        param='tgl='+notransaksi+'&proses=htmlDetail';
        tujuan='pabrik_slave_mr_wtp.php';
        post_response_text(tujuan, param, respog);
        function respog(){
              if(con.readyState==4)
              {
                if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                       document.getElementById('containerData').innerHTML=con.responseText;
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              } 
         }  
}
function formListPP(title,wdth,heig){
        //closeDialog();
        width='';
        height='';
        if(wdth!=''){
            width=wdth;
        }
        if(heig!=''){
            height=heig;
        }
        
        content="<div id=containerData></div>";
        ev='event';
        showDialog4(title,content,width,height,ev);
}
function upDt(){
    tanggal=document.getElementById('tgl2').value;
    volAir=document.getElementById('volAir2').value;
    tujuan ='pabrik_slave_mr_wtp.php';
    param='proses=update'+'&volAir='+volAir+'&tanggal='+tanggal;
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    closeDialog4();
                    loadData(0);
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

// function add_detail(){
//     notr=document.getElementById('notransaksi').value;
//     kdOrg=document.getElementById('kdOrg');
//     kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
//     jenis=document.getElementById('jenis');
//     jenis=jenis.options[jenis.selectedIndex].value;
//     tgl=document.getElementById('tgl').value;
//     nokontrak=document.getElementById('nokontrak').value;
//     noba=document.getElementById('noba').value;
//     lokasi=document.getElementById('lokasi').value;
//     param='kdOrg='+kdOrg+'&proses=createTable';
//     param+='&notransaksi='+notr+'&jenis='+jenis; 
//     param+='&tgl='+tgl+'&nokontrak='+nokontrak; 
//     param+='&noba='+noba+'&lokasi='+lokasi; 
//     tujuan='pabrik_slave_mr_wtp.php';
//     post_response_text(tujuan, param, respog);
//     function respog(){
//           if(con.readyState==4){
//             if (con.status == 200){
//                     busy_off();
//                     if (!isSaveResponse(con.responseText)) {
//                             alert(con.responseText);
//                     }
//                     else{
//                             document.getElementById('detailEntry').style.display='block';
//                             cor=con.responseText.split("####");
//                             document.getElementById('notransaksi').value=cor[0];
//                             document.getElementById('detailIsi').innerHTML=cor[1];
//                             //document.getElementById('detailIsi').innerHTML=con.responseText;
//                             //document.getElementById('tmbLheader').innerHTML='';
//                             lockForm();
//                         }
//                 }
//                 else{
//                         busy_off();
//                         error_catch(con.status);
//                 }
//           } 
//      }  
// }
// function edit(notrans,kdorg,jenis,tgl,nokont,noba,lokasi){
//     document.getElementById('notransaksi').value=notrans;
//     document.getElementById('kdOrg').value=kdorg;
//     document.getElementById('jenis').value=jenis;
//     document.getElementById('jenis').disabled=true;
//     document.getElementById('tgl').value=tgl;
//     document.getElementById('tgl').disabled=true;
//     document.getElementById('nokontrak').value=nokont;
//     document.getElementById('noba').value=noba;
//     document.getElementById('lokasi').value=lokasi;
//     showDetail(notrans,tgl);
// }
// function showDetail(notrans,tgl){
//     param='notransaksi='+notrans+'&proses=createTable'+'&tgl='+tgl;
//     tujuan='pabrik_slave_mr_wtp.php';
//     post_response_text(tujuan, param, respog);
//     function respog(){
//           if(con.readyState==4){
//             if (con.status == 200){
//                     busy_off();
//                     if (!isSaveResponse(con.responseText)) {
//                             alert(con.responseText);
//                     }
//                     else{
//                             document.getElementById('listData').style.display='none';
//                             document.getElementById('headher').style.display='block';
//                             document.getElementById('detailEntry').style.display='block';
//                             //document.getElementById('tmbLheader').innerHTML='';
//                             cor=con.responseText.split("####");
//                             document.getElementById('notransaksi').value=cor[0];
//                             document.getElementById('detailIsi').innerHTML=cor[1];
//                             loadDetail();
//                         }
//                 }
//                 else{
//                         busy_off();
//                         error_catch(con.status);
//                 }
//           } 
//      }
// }
// function deletehead(notrans){
//     param='notransaksi='+notrans+'&proses=deletehead';
//     tujuan='pabrik_slave_mr_wtp.php';
//     if(confirm("Anda Yakin Menghapus?")){
//         post_response_text(tujuan, param, respog);    
//     }
//     function respog(){
//           if(con.readyState==4){
//             if (con.status == 200){
//                     busy_off();
//                     if (!isSaveResponse(con.responseText)) {
//                             alert(con.responseText);
//                     }
//                     else{
//                             //document.getElementById('tmbLheader').innerHTML='';
//                             loadData(0);
//                         }
//                 }
//                 else{
//                         busy_off();
//                         error_catch(con.status);
//                 }
//           } 
//      }
// }
// function getData(){
//     not=document.getElementById('noTiket');
//     not=not.options[not.selectedIndex].value;
//     param='notransaksi='+not+'&proses=ambilDt';
//     tujuan='pabrik_slave_mr_wtp.php';
//     post_response_text(tujuan, param, respog);    
//     function respog(){
//           if(con.readyState==4){
//             if (con.status == 200){
//                     busy_off();
//                     if (!isSaveResponse(con.responseText)) {
//                             alert(con.responseText);
//                     }
//                     else{
//                             isi=con.responseText.split("####");
//                             document.getElementById('komoditiId').value=isi[0];
//                             document.getElementById('netto').value=isi[1];
//                         }
//                 }
//                 else{
//                         busy_off();
//                         error_catch(con.status);
//                 }
//           } 
//      }
// }
// status_inputan=0;
// function addDetail() {
//         if(status_inputan==0){
//                 if(confirm('Are you sure..?')){
//                         saveData(); 
//                 }
//         }
//         else if(status_inputan!=0){
//                 saveData(); 
//         }

// }
// function saveData(){
//         not=document.getElementById('noTiket');
//         not=not.options[not.selectedIndex].value;
//         komditi=document.getElementById('komoditiId');
//         komditi=komditi.options[komditi.selectedIndex].value;
//         netto=document.getElementById('netto').value;
//         notransaksi=document.getElementById('notransaksi').value;
//         tgl=document.getElementById('tgl').value;
//         pros=document.getElementById('proses').value;
//         if(pros!="updateDetail"){
//                 param = "proses=saveData";
//         }
//         else{
//                 param = "proses=updateDetail";
//         }
//         param+='&notransaksi='+notransaksi;
//         param+='&noTiket='+not+'&komoditiId='+komditi+'&netto='+netto; 
//         tujuan='pabrik_slave_mr_wtp.php';
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
//                                                         status_inputan=1;
//                                                         lockForm();
//                                                         showTmbl();
//                                                         bersihFormDet();
//                                                         showDetail(notransaksi,tgl);
//                                                 }
//                                         }
//                                         else {
//                                                 busy_off();
//                                                 error_catch(con.status);
//                                         }
//                       } 
//          }  
// }

// function posting(notransaksi,tgl,title){
//     param='notransaksi='+notransaksi+'&proses=postData';
//     tujuan='pabrik_slave_mr_wtp.php';
    
//     function respog(){
//           if(con.readyState==4)
//           {
//             if (con.status == 200) {
//                             busy_off();
//                             if (!isSaveResponse(con.responseText)) {
//                                     alert(con.responseText);
//                             }
//                             else {
//                                    loadData(0);
//                             }
//                     }
//                     else {
//                             busy_off();
//                             error_catch(con.status);
//                     }
//           } 
//      } 
//      if(confirm(title))
//      post_response_text(tujuan, param, respog);
// }







// function printFile(param,tujuan,title,ev)
// {
//    tujuan=tujuan+"?"+param;  
//     width='600';
//     height='400';
//    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
//    showDialog2(title,content,width,height,ev); 	
// }
// function excel(ev,kodeorg,periodegaji,tipepotongan)
// {
//         param='method=excel'+'&kodeorg='+kodeorg+'&periodegaji='+periodegaji+'&tipepotongan='+tipepotongan;
//         //alert(param);
//         tujuan='pabrik_slave_mr_wtpExcel.php';
//         judul='Print Excel';		
//         printFile(param,tujuan,judul,ev)	
// }

// function cariOrg(title,content,ev){
//         width='500';
//         height='400';
//         showDialog1(title,content,width,height,ev);
//         //alert('asdasd');
// }
// function findOrg(){
//         txt=trim(document.getElementById('fnOrg').value);
//         if(txt=='')
//         {
//                 alert('Text is obligatory');
//         }
//         else if(txt.length<3)
//         {
//                 alert('Text too short');
//         }
//         else
//         {
//                 param='txtfind='+txt+'&proses=cariOrg';
//                 tujuan='pabrik_slave_mr_wtp.php';
//                 post_response_text(tujuan, param, respog);
//         }
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
//                                                         document.getElementById('container').innerHTML=con.responseText;
//                                                 }
//                                         }
//                                         else {
//                                                 busy_off();
//                                                 error_catch(con.status);
//                                         }
//                       }	
//          }  	
// }
// function setOrg(kdOrg,nmOrg){
//         document.getElementById('kdOrg').value=kdOrg;
//         document.getElementById('nmOrg').value=nmOrg;
//         closeDialog();
// }

// function findOrg2(){
//         txt=trim(document.getElementById('crOrg').value);
//         if(txt==''){
//                 alert('Text is obligatory');
//         }
//         else if(txt.length<3){
//                 alert('Text too short');
//         }
//         else{
//                 param='txtfind='+txt+'&proses=cariOrg2';
//                 tujuan='pabrik_slave_mr_wtp.php';
//                 post_response_text(tujuan, param, respog);
//         }
//         function respog(){
//                       if(con.readyState==4)
//                       {
//                                 if (con.status == 200) {
//                                                 busy_off();
//                                                 if (!isSaveResponse(con.responseText)) {
//                                                         alert(con.responseText);
//                                                 }
//                                                 else {
//                                                         //alert(con.responseText);
//                                                         document.getElementById('container').innerHTML=con.responseText;
//                                                 }
//                                         }
//                                         else {
//                                                 busy_off();
//                                                 error_catch(con.status);
//                                         }
//                       }	
//          }  	
// }
// function setOrg2(kdOrg,nmOrg)
// {
//         document.getElementById('kdOrg').value=kdOrg;
//         document.getElementById('txtsearch').value=nmOrg;
//         closeDialog();
// }



// function editDetail(karyawn,rppot,ketrng) {
//     document.getElementById('krywnId').value=karyawn;
//     document.getElementById('krywnId').disabled=true;
//     document.getElementById('rpPot').value=rppot;
//     document.getElementById('ketPot').value=ketrng;
//     document.getElementById('proses').value="updateDetail";
// }
// statFrm=0;
// function showTmbl()
// {
//         if(statFrm==0)
//         {
//                 document.getElementById('tombol').innerHTML="<button class=mybutton onclick=frm_aju()>"+nmTmblDone+"</button>";
//         }
//         else if(statFrm==1)
//         {
//                 document.getElementById('tombol').innerHTML="<button class=mybutton onclick=frm_aju()>"+nmTmblDone+"</button>";
//         }
// }

// function bersihFormDet(){
//                 document.getElementById('netto').value='';
//                 document.getElementById('noTiket').value='';
//                 document.getElementById('komoditiId').value='';
//                 document.getElementById('proses').value="saveData";
// }

// function delDetail(notransaksi,noTiket,tgl){
//         param='&notransaksi='+notransaksi+'&noTiket='+noTiket;
//         param+='&proses=delDetail';
//         tujuan='pabrik_slave_mr_wtp.php';
//         function respog(){
//                 if (con.readyState == 4) {
//                         if (con.status == 200) {
//                                 busy_off();
//                                 if (!isSaveResponse(con.responseText)) {
//                                         alert(con.responseText);
//                                 }
//                                 else {
//                                         showDetail(notransaksi,tgl);
//                                 }
//                         }
//                         else {
//                                 busy_off();
//                                 error_catch(con.status);
//                         }
//                 }
//         }	
//         if(confirm("Deleting, are you sure..?"))
//         post_response_text(tujuan, param, respog);	
// }


// function loadDetail(){
//         notras=document.getElementById('notransaksi').value;
//         param='&proses=loadDetail'+'&notransaksi='+notras;
//         //alert(param);
//         tujuan='pabrik_slave_mr_wtp.php';
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
//                                                         document.getElementById('contentDetail').innerHTML=con.responseText;
//                                                 }
//                                         }
//                                         else {
//                                                 busy_off();
//                                                 error_catch(con.status);
//                                         }
//                       }	
//          } 	

// }
// function fillField(kdorg,prder,potong){
//        kdOrg=document.getElementById('kdOrg');
//        for(x=0;x<kdOrg.length;x++){
//                 if(kdOrg.options[x].value==kdorg)
//                 {
//                         kdOrg.options[x].selected=true;
//                 }
//        }
//        prd=document.getElementById('tglAbsen');
//        for(x=0;x<prd.length;x++){
//                 if(prd.options[x].value==prder)
//                 {
//                         prd.options[x].selected=true;
//                 }
//        }
//        tppot=document.getElementById('tpPotongan');
//        for(x=0;x<tppot.length;x++){
//         if(tppot.options[x].value==potong)
//         {
//                 tppot.options[x].selected=true;
//         }
//        }
//         param='kdOrg='+kdorg+'&periode='+prder+'&tipePotongan='+potong+'&statUpdate=1';
//         param+="&proses=createTable";
//         //alert(param);
//         tujuan='pabrik_slave_mr_wtp.php';
//         post_response_text(tujuan, param, respon);
//                 function respon(){
//                         if (con.readyState == 4) {
//                                 if (con.status == 200) {
//                                         busy_off();
//                                         if (!isSaveResponse(con.responseText)) {
//                                                 alert(con.responseText);
//                                         } else {
//                                                 // Success Response
//                                 lockForm();
//                                 document.getElementById('listData').style.display='none';
//                                 document.getElementById('headher').style.display='block';
//                                 document.getElementById('detailEntry').style.display='block';
//                                 var detailDiv = document.getElementById('detailIsi');
//                                 detailDiv.innerHTML = con.responseText;
//                                 status_inputan=1;
//                                 statFrm=1;
//                                 showTmbl();
//                                 loadDetail();
//                                         }
//                                 } else {
//                                         busy_off();
//                                         error_catch(con.status);
//                                 }
//                         }
//                 }


// }

// function delData(kdorg,prder,potong){
//         param+='&kdOrg='+kdorg;
//         param+='&periode='+prder+'&tipePot='+potong;
//         param+='&proses=delData';
//         tujuan='pabrik_slave_mr_wtp.php';
//         function respog(){
//                 if (con.readyState == 4) {
//                         if (con.status == 200) {
//                                 busy_off();
//                                 if (!isSaveResponse(con.responseText)) {
//                                         alert(con.responseText);
//                                 }
//                                 else {
//                                         displayList();
//                                 }
//                         }
//                         else {
//                                 busy_off();
//                                 error_catch(con.status);
//                         }
//                 }
//         }	
//         if(confirm("Deleteing, are you sure..?"))
//         post_response_text(tujuan, param, respog);	
// }
// function frm_aju()
// {

//         if(statFrm==0)
//         {
//                 if(confirm("Done, are you sure..?"))
//                 {
//                         displayList();
//                 }
//         }
//         else if(statFrm==1)
//         {		
//                 if(confirm("Done, are you sure..?"))
//                 {
//                         displayList();
//                 }
//         }
// }
// function reset_data()
// {
//         if(statFrm==0)
//         {
//                 if(confirm("Canceling, are you sure..?"))
//                 {
//                         kdorg=document.getElementById('kdOrg').value;
//                         tgl=document.getElementById('tglAbsen').value;
//                         delDataAll(kdorg,tgl);
//                 }
//         }

// }

 
// function getKary(title,pil,ev){
//         utkUnit=document.getElementById('kdOrg');
//         utkUnit=utkUnit.options[utkUnit.selectedIndex].value;
//         prd=document.getElementById('tglAbsen').value;
//         tpPot=document.getElementById('tpPotongan');
//         tpPot=tpPot.options[tpPot.selectedIndex].value;
        
//          if(pil==1){
//                 content= "<div style='width:100%;'>";
//                 content+="<fieldset>"+title+"<input type=hidden id=unit value="+utkUnit+" /><input type=hidden id=tppot value="+tpPot+" /><input type=hidden id=periode value="+prd+" /><input type=text placeholder='Nama Karyawan' id=txtnamabarang class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariKary("+pil+")>Go</button> </fieldset>";
//                 content+="<fieldset><legend><i>Result<i></legend><div id=containercari style='overflow:auto;max-height:300px'></div></div></fieldset>";                 
//          }

//      //display window
// 	   width='';
// 	   height='';
// 	   showDialog1(title,content,width,height,ev);		
// }
// function goCariKary(pil){
//     //keu_slave_2globalfungsi
//         lokTgs=document.getElementById('unit').value;
//         tppotongan=document.getElementById('tppot').value;
//         prd=document.getElementById('periode').value;
//         nmkary=document.getElementById('txtnamabarang').value;
//         param='unit='+lokTgs+'&tppot='+tppotongan+'&periode='+prd+'&nmkary='+nmkary;
       
//         if(pil==1){
//             param+='&proses=getKary';
//         }
//     tujuan = 'pabrik_slave_mr_wtp.php';
//     post_response_text(tujuan, param, respog);				
//     function respog(){
//             if (con.readyState == 4) {
//                     if (con.status == 200) {
//                             busy_off();
//                             if (!isSaveResponse(con.responseText)) {
//                                     alert(con.responseText);
//                             }
//                             else {
//                                     document.getElementById('containercari').innerHTML=con.responseText;
//                             }
//                     }
//                     else {
//                             busy_off();
//                             error_catch(con.status);
//                     }
//             }
//     }	
// }
// function setKary(karyid){
//       kar=document.getElementById('krywnId');
//       for(x=0;x<kar.length;x++){
//         if(kar.options[x].value==karyid){
//                 kar.options[x].selected=true;
//         }
//       }
//       closeDialog();
// }