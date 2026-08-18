/**
 * @author repindra.ginting
 */
//=================================================sisi purchasing

//===================== LOAD DATA SUP KELOMPOK ======================

function loadData() 
{
    // alert('masukk');
    param='method=loadData';
    // param+='&supplierid2='+idsupplier_detail;
    tujuan='keu_slave_hutangbank.php';
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
                                   // alert(con.responseText);
                                    
                                    document.getElementById('container').innerHTML=con.responseText;
                                    // getPage();
                                    // detaildt();

                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              } 
     }  
}


// //================= Load Data Awal ==========================
// function loadData1 (num) 
// {
//     // alert('masukk');
//     param='method=loadData';
//     param+='&page='+num;
//     // param+='&page='+num;
//     txtsearch=trim(document.getElementById('txtsearch').value);
//     txtNoakun=trim(document.getElementById('txtNoakun').value);
//         if(txtsearch != ''){
//             param += '&txtsearch=' + txtsearch;

//         }
//         if(txtNoakun != ''){
//             param += '&txtNoakun=' + txtNoakun;

//         }
//     tujuan='log_slave_save_supplier.php';
//     post_response_text(tujuan, param, respog);
//     function respog()
//     {
//               if(con.readyState==4)
//               {
//                     if (con.status == 200) {
//                                 busy_off();
//                                 if (!isSaveResponse(con.responseText)) {
//                                         alert(con.responseText);
//                                 }
//                                 else {
//                                    // alert(con.responseText);
                                    
//                                      document.getElementById('container').innerHTML=con.responseText;
//                                     // getPage();
//                                     // detaildt();
//                                 }
//                         }
//                         else {
//                                 busy_off();
//                                 error_catch(con.status);
//                         }
//               } 
//      }  
// }

// function getPage(pg){
//     pg=document.getElementById('pages');
//     pg=pg.options[pg.selectedIndex].value;
//     paged=parseFloat(pg)-1;
//     loadData1(paged);
//     // cariBast(pg-1);    
// }

function pilihjenis(jenis){
    jenis=document.getElementById('jenis').value;
    if(jenis == 'NONLEASING'){
        document.getElementById('nilaibunga').disabled=true;
        document.getElementById('jumlahbulan').disabled=true;
        document.getElementById('tglmulai').disabled=true;
        document.getElementById('tglselesai').disabled=true;

    }else{
        document.getElementById('nilaibunga').disabled=false;
        document.getElementById('jumlahbulan').disabled=false;
        document.getElementById('tglmulai').disabled=false;
        document.getElementById('tglselesai').disabled=false;
       // loadData();
    }

}

//============ SIMPAN Alamat Supplier ============ BELUM SELESAI

function save()
{
     // alert ('masuk');
    notrans=document.getElementById('notrans').value;
    kodeorg=document.getElementById('kodeorg').value;
    //supplierid=supplierid.options[supplierid.selectedIndex].value;
    noakun=document.getElementById('noakun').value;
    jenis=document.getElementById('jenis').value;
    namahutang=document.getElementById('namahutang').value;
    nilaipokok=document.getElementById('nilaipokok').value;
    nilaibunga=document.getElementById('nilaibunga').value;
    jumlahbulan=document.getElementById('jumlahbulan').value;
    tglmulai=document.getElementById('tglmulai').value;
    tglselesai=document.getElementById('tglselesai').value;
    
    method=document.getElementById('methodHutang').value;

    if(kodeorg=='' || noakun==''|| jenis=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='notrans='+notrans+'&kodeorg='+kodeorg+'&noakun='+noakun+'&jenis='+jenis+'&namahutang='+namahutang+'&nilaipokok='+nilaipokok+'&nilaibunga='+nilaibunga+'&jumlahbulan='+jumlahbulan+'&tglmulai='+tglmulai+'&tglselesai='+tglselesai+'&method='+method;
    alert(param);
    tujuan='keu_slave_hutangbank.php';
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
                            cancel();
                            
                            loadData();
                            // loadData1(supplierid2);
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }
}

//==========CANCEL / RESET FORM ==================//
function cancel()
{
   
    // document.getElementById('notrans').value='';
    document.getElementById('kodeorg').value='';
    document.getElementById('noakun').value='';
    document.getElementById('jenis').value='';
    document.getElementById('namahutang').value='';
    document.getElementById('nilaipokok').value='';
    document.getElementById('nilaibunga').value='';    
    document.getElementById('jumlahbulan').value='';
    document.getElementById('tglmulai').value='';
    document.getElementById('tglselesai').value='';
    // document.getElementById('statusalamat').checked=false;            
    document.getElementById('methodHutang').value='insert';
    document.getElementById('kodeorg').disabled=false;
    // document.getElementById('namahutang').disabled=false;
    // document.getElementById('nilaipokok').disabled=false;
    document.getElementById('nilaibunga').disabled=false;
    document.getElementById('jumlahbulan').disabled=false;
    document.getElementById('tglmulai').disabled=false;
    document.getElementById('tglselesai').disabled=false;
}


//==========EDIT FORM Alamat==================//
function edit(kodeorg,noakun,jenis,namahutang,nilaipokok,nilaibunga,jumlahbulan,tglmulai,tglselesai,notrans)
{
    document.getElementById('notrans').value=notrans;
    document.getElementById('notrans').disabled=true;
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('noakun').value=noakun;
    // document.getElementById('noakun').disabled=true;
    document.getElementById('jenis').value=jenis;
    // document.getElementById('jenis').disabled=true;
    // document.getElementById('namasupplier').value=namasupplier;
    document.getElementById('namahutang').value=namahutang;
    document.getElementById('nilaipokok').value=nilaipokok;
    document.getElementById('methodHutang').value='update';
    
    if(jenis == 'NONLEASING'){
        document.getElementById('nilaibunga').value='';    
        document.getElementById('jumlahbulan').value='';
        document.getElementById('tglmulai').value='';
        document.getElementById('tglselesai').value='';

    }else{
        document.getElementById('nilaibunga').value=nilaibunga;
        document.getElementById('jumlahbulan').value=jumlahbulan;
        document.getElementById('tglmulai').value=tglmulai;
        document.getElementById('tglselesai').value=tglselesai;
       // loadData();
    }
    
    pilihjenis();
}


