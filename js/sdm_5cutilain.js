/*
  sb_tot=document.getElementById('total_harga_po');
        sb_tot.value=remove_comma_var(sb_tot.value);
*/
function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}

maxf=0
sekarang=1;
function saveAll(maxRow)
{     

      	 maxf=maxRow;
	    loopsave(1,maxRow);
}


function batal(){
    document.getElementById('printContainer').innerHTML='';	
}



function del(maxRow){
    unit=trim(document.getElementById('unit').value);
	jeniscuti=trim(document.getElementById('jeniscuti').value);
    tahun=document.getElementById('tahun').value;
    tipekar=document.getElementById('tipekar').value;
	golkar=document.getElementById('golkar').value;
    	
	param='proses=del'+'&unit='+unit+'&jeniscuti='+jeniscuti+'&tahun='+tahun+'&tipekar='+tipekar+'&golkar='+golkar;

	tujuan='sdm_slave_save_5cutilain.php';
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
					else 
					{
						// document.getElementById('container').innerHTML=con.responseText;
						//saveAll(maxRow);
                                                currRow=1;
                                            loopsave(currRow,maxRow);	
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
	//alert("Data telah terhapus !!!");	
}




function loopsave(currRow,maxRow){
	tahun=document.getElementById('tahun').value;
	unit=document.getElementById('unit').value;
	jeniscuti=document.getElementById('jeniscuti').value;
	hakcuti=document.getElementById('hakcuti'+currRow).value;
	karidsave=document.getElementById('karidsave'+currRow).innerHTML;
	dari=document.getElementById('dari'+currRow).innerHTML;
	sampai=document.getElementById('sampai'+currRow).innerHTML;
    if(tahun=='' || unit=='' || jeniscuti=='' || hakcuti==''){
            alert("Data tidak lengkap");return;
    }	
    else{  
        param='tahun='+tahun+'&unit='+unit+'&jeniscuti='+jeniscuti+'&hakcuti='+hakcuti+'&karidsave='+karidsave+'&dari='+dari+'&sampai='+sampai;
        param+="&proses=savedata";

            //alert(param);
            tujuan = 'sdm_slave_save_5cutilain.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('row'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
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
                    if(currRow>maxRow)
                    {
                            alert('Done');
                           // document.location.reload();
                            //document.getElementById('infoDisplay').innerHTML='';
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
               // document.getElementById('lanjut').style.display='';
                //unlockScreen();
            }
        }
    }		
	
}


function getPerhitungan(no)
{
     x=trim(document.getElementById('tanpapengali'+no).innerHTML);
     y=trim(document.getElementById('pengalibawah'+no).value);
		x=remove_comma_var(x);
		y=remove_comma_var(y);
     z=x*y;
     document.getElementById('jumlahsave'+no).value=numberFormat(z);
     
}

function hide()
{
    jenis=trim(document.getElementById('jenis').value);
    if(jenis!=26)
    {
        document.getElementById('pengali').value=1;
       document.getElementById('pengali').disabled=true;
	   	document.getElementById('bulanawal').disabled=false;
	   document.getElementById('bulanawal').value=1;
	    document.getElementById('bulanakhir').disabled=false;
	   document.getElementById('bulanakhir').value=12;
    }
    else
    {
       document.getElementById('pengali').disabled=false;
	   document.getElementById('pengali').value=1;
	   	    document.getElementById('bulanawal').value=1;
       document.getElementById('bulanawal').disabled=true;
	    document.getElementById('bulanakhir').value=1;
       document.getElementById('bulanakhir').disabled=true; 
    }
}





function uang()
{
    unit=document.getElementById('unit').value; 
    param='unit='+unit;
    param+='&proses=uang';
    tujuan='sdm_slave_3uangmakan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('makan').value=con.responseText;   
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

