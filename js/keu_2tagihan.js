
function cancellaporan(){
	document.getElementById('container').innerHTML='';
	document.getElementById('noinv').value='';
	document.getElementById('noinvsupp').value='';
	document.getElementById('nopodt').value='';
	
		// var d = new Date();
	// var curr_date = d.getDate();
	// var curr_month = d.getMonth() + 1; //Months are zero based
	// var curr_year = d.getFullYear();
	// if (curr_date.length == 1) {
		// curr_date = '0' + curr_date;
	// }

	// d1 = curr_month + "-" + curr_year;
	
	setValue2('kdOrg',null);
	setValue2('updateby',null);
	setValue2('kodesupplier',null);
	// setValue2('periode',null);
	// setValue2('periode2',null);
	setValue2('jenis',null);
	setValue2('statTagihan',null);
}


function previewlaporan(parameter,tipe) {
	proses='preview';
	tujuan='keu_slave_2tagihan.php';
    var passP = parameter.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
		param += "&"+passP[i]+"="+getValue(passP[i]);
    }
	param += '&proses=' + proses;
	param += '&tipe=' + tipe;
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
	// alert(param);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
                } else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
						leftFixedTable();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}





function previewlaporanx(tipe){
	
    kdOrg=document.getElementById('kdOrg').value;
    updateby=document.getElementById('updateby').value;
    statTagihan=document.getElementById('statTagihan').value;
    periode2=document.getElementById('periode2').value;
    periode=document.getElementById('periode').value;
    noinv=document.getElementById('noinv').value;
    noinvsupp=document.getElementById('noinvsupp').value;
    nopodt=document.getElementById('nopodt').value;
    jenis=document.getElementById('jenis').value;
    kodesupplier=document.getElementById('kodesupplier').value;
 
	method='preview';
    param='kodebarang='+kodebarang+'&unit='+unit+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&tipe='+tipe;
	param += '&method=' + method;
    tujuan='pabrik_2stokbulkingpt_slave.php';
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
						leftFixedTable();
					}
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
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}




function Clear1()
{
    document.getElementById('kdOrg').value='';
    document.getElementById('periode').value='';
    document.getElementById('periode2').value='';
    document.getElementById('statTagihan').value='';
    document.getElementById('printContainer').innerHTML='';
}
function detailPDF2(numRow,ev) {
    // Prep Param
    var notran = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    notran=notran.split("####");
    var notransaksi=notran[0];
    var noakun =notran[2];
    var tipetransaksi =notran[1];
    var kodeorg  =notran[3];
    param = "proses=pdf2&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
function postingDatalaporan(row){
    noinvoice=document.getElementById('noinvoice_'+row).innerHTML;
    param='noinvoice='+noinvoice;
        tujuan='keu_slave_tagihanPosting.php';
        if(confirm(notifpostingpenagihan))
             post_response_text(tujuan+'?'+'proses=getPo', param, respog);
    
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
                                   loaddata();
                            }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
            }
     }    
}


function loaddata(){
        kdOrg=document.getElementById('kdOrg').value;
        updateby=document.getElementById('updateby').value;
        statTagihan=document.getElementById('statTagihan').value;
        periode2=document.getElementById('periode2').value;
        periode=document.getElementById('periode').value;
        noinv=document.getElementById('noinv').value;
        noinvsupp=document.getElementById('noinvsupp').value;
        param='proses=preview';
        param+='&kdOrg='+kdOrg+'&updateby='+updateby+'&statTagihan='+statTagihan+'&periode2='+periode2+'&periode='+periode;
        param+='&noinv='+noinv+'&noinvsupp='+noinvsupp;
        tujuan = 'keu_slave_2tagihan.php';
                //alert(param);
        post_response_text(tujuan, param, respog);          
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    }
                    else {
                        document.getElementById('printContainer').innerHTML=con.responseText;
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }   
}