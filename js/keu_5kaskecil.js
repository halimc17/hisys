function getbank(rekening)
{
	unit=document.getElementById('unit').value;
	noakun2=document.getElementById('noakun2').value;
	
	param = 'noakun2='+noakun2+'&method=getbank'+'&unit='+unit+'&rekening='+rekening;
	post_response_text('keu_slave_5kaskecil.php', param, respon);
	
	function respon() 
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
					// === Success Response
                    document.getElementById('rekening').innerHTML = con.responseText;
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

function simpan(){
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	rekening=document.getElementById('rekening').options[document.getElementById('rekening').selectedIndex].value;
	noakun=document.getElementById('noakun').options[document.getElementById('noakun').selectedIndex].value;
	noakun2=document.getElementById('noakun2').options[document.getElementById('noakun2').selectedIndex].value;
	periode=document.getElementById('periode').value;
	tanggalmulai=document.getElementById('tanggalmulai').value;
	tanggalselesai=document.getElementById('tanggalselesai').value;
	tanggaltopup=document.getElementById('tanggaltopup').value;
	plafon=document.getElementById('plafon').value;
	saldoberjalan=document.getElementById('saldoberjalan').value;
	batasbawah=document.getElementById('batasbawah').value;
	method=document.getElementById('method').value;
	param='unit='+unit+'&noakun='+noakun+'&noakun2='+noakun2+'&periode='+periode+'&rekening='+rekening;
	param+='&tanggalmulai='+tanggalmulai+'&tanggalselesai='+tanggalselesai+'&tanggaltopup='+tanggaltopup;
	param+='&plafon='+remove_comma_var(plafon)+'&saldoberjalan='+remove_comma_var(saldoberjalan)+'&batasbawah='+remove_comma_var(batasbawah)+'&method='+method;
	tujuan='keu_slave_5kaskecil.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    //alert(con.responseText);
					cancel();
					loadData();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function cancel(){
    document.getElementById('unit').disabled=false;
	document.getElementById('unit').value='';
    document.getElementById('noakun').disabled=false;
	document.getElementById('noakun').value='';
	document.getElementById('noakun2').disabled=false;
	document.getElementById('noakun2').value='';
	document.getElementById('rekening').disabled=false;
	document.getElementById('rekening').value='';
	document.getElementById('periode').disabled=false;
	document.getElementById('periode').value='';
	document.getElementById('tanggalmulai').value='';
	document.getElementById('tanggalselesai').value='';
	document.getElementById('tanggaltopup').value='';
	document.getElementById('plafon').value='0';
	document.getElementById('saldoberjalan').value='0';
	document.getElementById('batasbawah').value='0';
	document.getElementById('method').value='insert';		
}

function loadData(num){
    param='method=loadData';
	param+='&page='+num;
	tujuan='keu_slave_5kaskecil.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
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

function fillfield(unit,noakun,noakun2,periode,tanggalmulai,tanggalselesai,tanggaltopup,plafon,saldoberjalan,batasbawah,rekening){
	document.getElementById('unit').value=unit;
	document.getElementById('noakun').value=noakun;
	document.getElementById('noakun2').value=noakun2;
	// document.getElementById('rekening').value=rekening;
	document.getElementById('periode').value=periode;
	document.getElementById('tanggalmulai').value=tanggalmulai;
	document.getElementById('tanggalselesai').value=tanggalselesai;
	document.getElementById('tanggaltopup').value=tanggaltopup;
	document.getElementById('plafon').value=plafon;
	document.getElementById('saldoberjalan').value=saldoberjalan;
	document.getElementById('batasbawah').value=batasbawah;
	getbank(rekening);
	// document.getElementById('method').value='update';
}









