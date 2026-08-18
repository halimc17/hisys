//JS 


// function numberFormat(number,digit) {
      // number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      // //Seperates the components of the number
      // var components = (parseFloat(number).toFixed(digit)).split(".");
      // //Comma-fies the first part
      // components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      // //Combines the two sections
      // return components.join(".");
// }

function changenumber(){
	 kg=document.getElementById('kg').value;
	 document.getElementById('kg').value=numberFormat(kg,2);
}


function simpan(){
    unit=document.getElementById('unit').value;
    kg=document.getElementById('kg').value;
	kg=remove_comma_var(kg);
    method=document.getElementById('method').value;

    if(unit=='' || kg=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='unit='+unit+'&kg='+kg+'&method='+method;
    tujuan='pabrik_5konversilori_slave.php';
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
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}
					


function cancel() {
    document.getElementById('unit').value='';
    document.getElementById('kg').value='';
    document.getElementById('method').value='insert';
    document.getElementById('unit').disabled=false;
}




function loadData () {
	param='method=loadData';
	tujuan='pabrik_5konversilori_slave.php';
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
									
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function edit(unit,kg) {
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
     document.getElementById('kg').value=numberFormat(kg,2);
    document.getElementById('method').value='update';
}



function del(unit)
{
	param='method=delete'+'&unit='+unit;
	tujuan='pabrik_5konversilori_slave.php';
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
						loadData();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}




