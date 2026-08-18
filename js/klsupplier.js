/**
 * @author repindra.ginting
 */
function saveKelSup(){
	tipe = trim(document.getElementById('tipe').value);
    tipe2 = trim(document.getElementById('tipe2').value);
	nama = trim(document.getElementById('nama').value);
	noakun = trim(document.getElementById('akun').options[document.getElementById('akun').selectedIndex].value);
    method = document.getElementById('method').value;
    kelompok = document.getElementById('kelompok').value;
	
	param='tipe='+tipe+'&noakun='+noakun+'&tipe2='+tipe2+'&nama='+nama;
    param+='&method='+method;
    param+='&kelompok='+kelompok;
    tujuan='log_slave_save_klsupplier.php';
    
	if (tipe == '' || noakun == ''){
       alert('Field Was Empty');
        return; 
    } 
		
	
	if(confirm('Saving '+tipe+', Are you sure..?')){
        post_response_text(tujuan, param, respog);
    }
		

	
	function respog()
	{
		if(con.readyState==4)
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
					//alert(con.responseText);
                    document.getElementById('container').innerHTML=con.responseText;
                    //clear form
                    //cancelKelSup();
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

function cancelKelSup()
{
    document.getElementById('tipe').value='';
    document.getElementById('nama').value='';
    document.getElementById('akun').value='';
    document.getElementById('method').value='insert';	
    document.getElementById('tipe').disabled = false;
    document.getElementById('tipe2').disabled = false;
}

function delKlSupplier(tipe)
{
    param='tipe='+tipe+'&method=delete';
        tujuan='log_slave_save_klsupplier.php';
        if(confirm('Deleting '+tipe+', Are you sure..?'))
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
                                                        document.getElementById('container').innerHTML=con.responseText;
                                                    //get list
                                                        //getCodeNumber(trim(document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value));
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
}

function editKlSupplier(tipe,nama,akun)
{
	document.getElementById('tipe').value=tipe;
    document.getElementById('tipe2').value=tipe;
    document.getElementById('nama').value=nama;
    document.getElementById('akun').value=akun;
	document.getElementById('method').value='update';	
    document.getElementById('tipe').disabled = true;
    document.getElementById('tipe2').disabled = true;
}
