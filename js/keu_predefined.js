maxf=0
sekarang=1;
function saveAll(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}

function changeflag(arr,tglmulai,tglselesai,pt,unit,status){

    var arrsplit = arr.split('###');
    var arnotrans='';

    nox=1;
    while(document.getElementById('input_'+nox))
    {
        if(document.getElementById('input_'+nox).checked==true)
        {
            if(arnotrans=='')
            {
                arnotrans=arrsplit[(nox-1)];
            }
            else
            {
                arnotrans+='###'+arrsplit[(nox-1)];
            }
        }
        nox++;
    }

    var param = 'proses=changeflag&arr='+arnotrans+'&tanggalmulai='+tglmulai+'&tanggalselesai='+tglselesai+'&pt='+pt+'&unit='+unit+'&status='+status;
    //alert(param);
    if(arnotrans=='')
    {
        alert('Data harus ada minimal satu untuk proses');
    }
    else
    {
       var tujuan = 'keu_slave_predefined.php';
       post_response_text(tujuan, param, respog); 
    }
      
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    loadpredefined();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function batal(){
    document.getElementById('pt').value='';	
    document.getElementById('unit').value='';
    document.getElementById('tanggalmulai').value='';
    document.getElementById('tanggalselesai').value='';
    document.getElementById('printContainer').innerHTML='';	
}


function del(maxRow){   
    nodok=trim(document.getElementById('nodok').value);
	unit=trim(document.getElementById('unit').value);
    per=document.getElementById('per').value;    	
    jumlah=document.getElementById('jumlah').value;    	
    nojurnal=document.getElementById('nojurnal').value;     
    nojurnalp=document.getElementById('nojurnalp').value;    	
	param='proses=delete'+'&unit='+unit+'&per='+per+'&nodok='+nodok+'&jumlah='+jumlah+'&nojurnal='+nojurnal+'&nojurnalp='+nojurnalp;
	tujuan='keu_slave_predefined.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					currRow=1;
					loopsave(currRow,maxRow);	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function cariBast(num)
{
    var param ='page='+num;
    //alert(param);
    post_response_text('keu_slave_predefined.php?proses=loadpredefined',param,respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('listdata').innerHTML=con.responseText;
                    document.getElementById('pages').value=num;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}



function loadpredefined(){   
    var param='proses=loadpredefined';
    var tujuan='keu_slave_predefined.php';
    post_response_text(tujuan, param, respog);  
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listdata').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}


function loopsave(currRow,maxRow){

	
    nourutdt=trim(document.getElementById('nourutdt'+currRow).innerHTML);
    nojurnaldt=trim(document.getElementById('nojurnaldt'+currRow).innerHTML);
    noakundt=trim(document.getElementById('noakundt'+currRow).innerHTML);
    keterangandt=trim(document.getElementById('keterangandt'+currRow).innerHTML);
	
    jumlahdt=trim(document.getElementById('jumlahdt'+currRow).innerHTML);
    kodekegiatandt=trim(document.getElementById('kodekegiatandt'+currRow).innerHTML);
    kodebarangdt=trim(document.getElementById('kodebarangdt'+currRow).innerHTML);
    nikdt=trim(document.getElementById('nikdt'+currRow).innerHTML);
	
    kodesupplierdt=trim(document.getElementById('kodesupplierdt'+currRow).innerHTML);
    kodevhcdt=trim(document.getElementById('kodevhcdt'+currRow).innerHTML);
    kodeblokdt=trim(document.getElementById('kodeblokdt'+currRow).innerHTML);
    
	unit=trim(document.getElementById('unit').value);
	noakun=trim(document.getElementById('noakun').value);
    per=document.getElementById('per').value;    	
    nojurnal=document.getElementById('nojurnal').value;   
    nojurnalp=document.getElementById('nojurnalp').value;    	
    nodok=document.getElementById('nodok').value;    	
	
	param='proses=savedt'+'&unit='+unit+'&per='+per+'&noakun='+noakun+'&nojurnal='+nojurnal+'&nodok='+nodok+'&nojurnalp='+nojurnalp;
	
	param+='&nourutdt='+nourutdt+'&nojurnaldt='+nojurnaldt+'&noakundt='+noakundt+'&keterangandt='+keterangandt;
	param+='&jumlahdt='+jumlahdt+'&kodekegiatandt='+kodekegiatandt+'&kodebarangdt='+kodebarangdt+'&nikdt='+nikdt;
	param+='&kodesupplierdt='+kodesupplierdt+'&kodevhcdt='+kodevhcdt+'&kodeblokdt='+kodeblokdt;
       
            //alert(param);
            tujuan = 'keu_slave_jurnalplasma.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('row'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    
    function respog(){
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                        document.getElementById('row'+currRow).style.backgroundColor='red';
                   unlockScreen();
                }
                else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
                            alert('Done');
                    }  
                    else
                    {
                            loopsave(currRow,maxRow);
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


function getunit() {
    var pt = document.getElementById('pt').value;
    var param = "pt="+pt+"&proses=getunit";
    
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
    
    post_response_text('keu_slave_predefined.php', param, respon);
}

function dataKeExcel(ev,tujuan,arr,no){
    judul='Report Ms.Excel';    
    param='arr='+arr+'&tanggalkirim='+document.getElementById('tanggalkirim_'+no).value+'&status='+document.getElementById('status_'+no).innerHTML+'&proses=excel';
    //alert(param);
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev);  
}