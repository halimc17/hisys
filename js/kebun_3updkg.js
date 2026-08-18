maxf=0
sekarang=1;
function saveAll(maxRow)
{     

      	 maxf=maxRow;
	    loopsave(1,maxRow);
}


function batal()
{
    document.getElementById('tgl1').value='';	
    document.getElementById('tgl2').value='';
    document.getElementById('unit').value='';
    document.getElementById('printContainer').innerHTML='';	
}


function loopsave(currRow,maxRow)
{
    notran=trim(document.getElementById('notran'+currRow).innerHTML);
    karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
    kodeorg=trim(document.getElementById('kodeorg'+currRow).innerHTML);
    hasilkerjakg=trim(document.getElementById('hasilkerjakg'+currRow).innerHTML);
    

    if(notran=='' || karyawanid=='' || kodeorg==''  || hasilkerjakg=='')
    {
            alert("Data tidak lengkap");return;
    }	
    else
    {  
        param='notran='+notran+'&karyawanid='+karyawanid+'&kodeorg='+kodeorg;
        param+="&proses=updatedata"+'&hasilkerjakg='+hasilkerjakg;

            //alert(param);return;
            tujuan = 'kebun_slave_3updkg.php';
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
                            batal();
                            //document.location.reload();
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
