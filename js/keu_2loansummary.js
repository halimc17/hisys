function getperiode(nour){
    //alert('notransaksi'+nour);
    notrans=document.getElementById('notransaksi'+nour);
    notrans=notrans.options[notrans.selectedIndex].value;
    param = 'method=getperiode'+'&notransaksi='+notrans+'&formke='+nour;
    tujuan = 'keu_slave_2loansummary.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('periode'+nour).innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function preview(nomr){
    notrans=document.getElementById('notransaksi'+nomr);
    notrans=notrans.options[notrans.selectedIndex].value;
    prd=document.getElementById('periode'+nomr);
    prd=prd.options[prd.selectedIndex].value;
    param = 'method=preview'+'&notransaksi='+notrans+'&periode='+prd;
   switch(nomr){
        case'1':
        tujuan = 'keu_slave_2loansummary.php';    
        break;
        case'2':
        tujuan = 'keu_slave_2loansummary2.php';    
        break;
        case'3':
        tujuan = 'keu_slave_2loansummary3.php';    
        break;
    }
    
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('printContainer'+nomr).innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function excel(nomr,ev){
    notrans=document.getElementById('notransaksi'+nomr);
    notrans=notrans.options[notrans.selectedIndex].value;
    prd=document.getElementById('periode'+nomr);
    prd=prd.options[prd.selectedIndex].value;
    param = 'method=preview'+'&notransaksi='+notrans+'&periode='+prd+'&tipe=excel';
   switch(nomr){
        case'1':
        tujuan = 'keu_slave_2loansummary.php';    
        break;
        case'2':
        tujuan = 'keu_slave_2loansummary2.php';    
        break;
        case'3':
        tujuan = 'keu_slave_2loansummary3.php';    
        break;
    }
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev)    
}
function pdf(nomr,ev){
   notrans=document.getElementById('notransaksi'+nomr);
    notrans=notrans.options[notrans.selectedIndex].value;
    prd=document.getElementById('periode'+nomr);
    prd=prd.options[prd.selectedIndex].value;
    param = 'method=preview'+'&notransaksi='+notrans+'&periode='+prd+'&tipe=pdf';
   switch(nomr){
        case'1':
        tujuan = 'keu_slave_2loansummary.php';    
        break;
        case'2':
        tujuan = 'keu_slave_2loansummary2.php';    
        break;
        case'3':
        tujuan = 'keu_slave_2loansummary3.php';    
        break;
    }
    judul='Report PDF';        
    printFile(param,tujuan,judul,ev)    
}
    



























// function cancel(){
// 	closeDialog();
// 	document.getElementById('unit').value = '';
// 	document.getElementById('noakun').value = '';
// 	document.getElementById('bank').value = '';
// 	document.getElementById('tgl1').value = '';
// 	document.getElementById('tgl2').value = '';
// 	document.getElementById('printContainer').innerHTML = '';
// }

// function clearopt(){
// 	document.getElementById('noakun').value = '';
// 	document.getElementById('bank').value = '';
// 	getbank();
// }

// function preview(){
	
//     unit=document.getElementById('unit').value;
//     noakun=document.getElementById('noakun').value;
//     bank=document.getElementById('bank').value;
//     tgl1=document.getElementById('tgl1').value;
//     tgl2=document.getElementById('tgl2').value;
	
// 	if(unit=='' || noakun=='' || tgl1=='' || tgl1=='' || tgl2==''){
// 		alert('Lengkapi pengisian');return;
// 	}
	
	
// 	// if(noakun=='1110101' || noakun=='1111101'){
// 	// 	if(bank==''){
// 	// 		alert('bank harus diisi');return;
// 	// 	}
// 	// }		
	
//     param = 'method=preview';
//     param += '&unit=' + unit+'&noakun=' + noakun+'&bank=' + bank;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2;
//     tujuan = 'keu_2kasharianv2_slave.php';
//     post_response_text(tujuan, param, respog);
//     function respog(){
//         if (con.readyState == 4){
//             if (con.status == 200){
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)){
//                     alert(con.responseText);
//                 } else {
//                     document.getElementById('printContainer').innerHTML = con.responseText;
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }

// function pdf(ev){
//     unit=document.getElementById('unit').value;
//     noakun=document.getElementById('noakun').value;
//     bank=document.getElementById('bank').value;
//     tgl1=document.getElementById('tgl1').value;
//     tgl2=document.getElementById('tgl2').value;
//     param = 'method=preview';
//     param += '&unit=' + unit+'&noakun=' + noakun+'&bank=' + bank;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2+'&tipe=pdf';
//     tujuan = 'keu_2kasharianv2_slave.php';
//     judul='Report PDF';        
//     printFile(param,tujuan,judul,ev)	
// }

// function excel(ev){
//     unit=document.getElementById('unit').value;
//     noakun=document.getElementById('noakun').value;
//     bank=document.getElementById('bank').value;
//     tgl1=document.getElementById('tgl1').value;
//     tgl2=document.getElementById('tgl2').value;
//     param = 'method=preview';
//     param += '&unit=' + unit+'&noakun=' + noakun+'&bank=' + bank;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2+'&tipe=excel';
//     tujuan = 'keu_2kasharianv2_slave.php';
//     judul='Report Ms.Excel';        
//     printFile(param,tujuan,judul,ev)	
// }


// function getbank(){
// 	unit=document.getElementById('unit').value;
//     noakun=document.getElementById('noakun').value;
//     bank=document.getElementById('bank').value;
//     tgl1=document.getElementById('tgl1').value;
//     tgl2=document.getElementById('tgl2').value;
// 	param = 'method=getbank';
// 	param += '&unit=' + unit+'&noakun=' + noakun+'&bank=' + bank;
// 	param += '&tgl1=' + tgl1+'&tgl2=' + tgl2;
// 	tujuan='keu_2kasharianv2_slave.php';  
// 	post_response_text(tujuan, param, respog);
// 		function respog(){
// 			if (con.readyState == 4) {
// 				if (con.status == 200) {
// 					busy_off();
// 					if (!isSaveResponse(con.responseText)) {
// 						alert(con.responseText);
// 					}
// 					else {
// 						document.getElementById('bank').innerHTML=con.responseText;
// 					}
// 				}
// 				else {
// 					busy_off();
// 					error_catch(con.status);
// 				}
// 			}
// 		}	
// }

// function getrekening(){
//     unit=document.getElementById('unitsum').value;
//     tgl1=document.getElementById('tgl1sum').value;
//     tgl2=document.getElementById('tgl2sum').value;
//     param = 'method=getrekening';
//     param += '&unit=' + unit;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2;
//     tujuan='keu_2kasharianv2_slave.php';  
//     post_response_text(tujuan, param, respog);
//         function respog(){
//             if (con.readyState == 4) {
//                 if (con.status == 200) {
//                     busy_off();
//                     if (!isSaveResponse(con.responseText)) {
//                         alert(con.responseText);
//                     }
//                     else {
//                         document.getElementById('rek').innerHTML=con.responseText;
//                     }
//                 }
//                 else {
//                     busy_off();
//                     error_catch(con.status);
//                 }
//             }
//         }   
// }

// // #========================================================================================================================#


// function previewkk(){
//     unit=document.getElementById('unitkk').value;
//     noakun=document.getElementById('noakunkk').value;
//     tgl1=document.getElementById('tgl1kk').value;
//     tgl2=document.getElementById('tgl2kk').value;
	
// 	if(unitkk=='' || noakunkk=='' || tgl1kk=='' || tgl1kk=='' || tgl2kk==''){
// 		alert('Lengkapi pengisian');return;
// 	}
	
	
//     param = 'method=previewkk';
//     param += '&unit=' + unit+'&noakun=' + noakun;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2;
//     tujuan = 'keu_2kasharianv2_slave.php';
//     post_response_text(tujuan, param, respog);
//     function respog(){
//         if (con.readyState == 4){
//             if (con.status == 200){
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)){
//                     alert(con.responseText);
//                 } else {
//                     document.getElementById('printContainerkk').innerHTML = con.responseText;
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }


// function pdfkk(ev){
//     unit=document.getElementById('unitkk').value;
//     noakun=document.getElementById('noakunkk').value;
//     tgl1=document.getElementById('tgl1kk').value;
//     tgl2=document.getElementById('tgl2kk').value;
//     param = 'method=previewkk';
//     param += '&unit=' + unit+'&noakun=' + noakun+'&bank=' + bank;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2+'&tipe=pdf';
//     tujuan = 'keu_2kasharianv2_slave.php';
//     judul='Report PDF';        
//     printFile(param,tujuan,judul,ev)	
// }

// function excelkk(ev){
//     unit=document.getElementById('unitkk').value;
//     noakun=document.getElementById('noakunkk').value;
//     tgl1=document.getElementById('tgl1kk').value;
//     tgl2=document.getElementById('tgl2kk').value;
//     param = 'method=previewkk';
//     param += '&unit=' + unit+'&noakun=' + noakun;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2+'&tipe=excel';
//     tujuan = 'keu_2kasharianv2_slave.php';
//     judul='Report Ms.Excel';        
//     printFile(param,tujuan,judul,ev)	
// }

// function cancelkk(){
// 	closeDialog();
// 	document.getElementById('unitkk').value = '';
// 	document.getElementById('noakunkk').value = '';
// 	document.getElementById('tgl1kk').value = '';
// 	document.getElementById('tgl2kk').value = '';
// 	document.getElementById('printContainerkk').innerHTML = '';
// }



// function previewsum(){
//     unit=document.getElementById('unitsum').value;
//     rek=document.getElementById('rek').value;
//     tgl1=document.getElementById('tgl1sum').value;
//     tgl2=document.getElementById('tgl2sum').value;
    
//     if(unitsum=='' || tgl1sum=='' || tgl2sum==''){
//         alert('Lengkapi pengisiannn');return;
//     }
    
//     param = 'method=previewsum';
//     param += '&unit=' + unit+'&rek=' + rek;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2;
//     tujuan = 'keu_2kasharianv2_slave.php';
//     post_response_text(tujuan, param, respog);
//     function respog(){
//         if (con.readyState == 4){
//             if (con.status == 200){
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)){
//                     alert(con.responseText);
//                 } else {
//                     document.getElementById('printContainersum').innerHTML = con.responseText;
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }

// function cancelsum(){
//     closeDialog();
//     document.getElementById('unitsum').value = '';
//     document.getElementById('rek').value = '';
//     document.getElementById('tgl1sum').value = '';
//     document.getElementById('tgl2sum').value = '';
//     document.getElementById('printContainersum').innerHTML = '';
// }

// function pdfsum(ev){
//     unit=document.getElementById('unitsum').value;
//     rek=document.getElementById('rek').value;
//     tgl1=document.getElementById('tgl1sum').value;
//     tgl2=document.getElementById('tgl2sum').value;
//     param = 'method=previewsum';
//     param += '&unit=' + unit+'&rek=' + rek;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2+'&tipe=pdf';
//     tujuan = 'keu_2kasharianv2_slave.php';
//     judul='Report PDF';        
//     printFile(param,tujuan,judul,ev)    
// }

// function excelsum(ev){
//     unit=document.getElementById('unitsum').value;
//     rek=document.getElementById('rek').value;
//     tgl1=document.getElementById('tgl1sum').value;
//     tgl2=document.getElementById('tgl2sum').value;
//     param = 'method=previewsum';
//     param += '&unit=' + unit+'&rek=' + rek;
//     param += '&tgl1=' + tgl1+'&tgl2=' + tgl2+'&tipe=excel';
//     tujuan = 'keu_2kasharianv2_slave.php';
//     judul='Report Ms.Excel';        
//     printFile(param,tujuan,judul,ev)    
// }




