/*
  sb_tot=document.getElementById('total_harga_po');
        sb_tot.value=remove_comma_var(sb_tot.value);
*/








function cekall()
{
    drt = document.getElementById('cekall');
    if (drt.checked == true)
    {
        chk = true;
    }
    else
    {
        chk = false;
    }
    var tbl = document.getElementById("content");
    var row = tbl.rows.length;
    //row = row - 1;
    for (i = 1; i <= row; i++)
    {
        document.getElementById('cek' + i).checked = chk;
    }
}


maxf=0
sekarang=1;
function saveall(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}


function batal(){
    document.getElementById('per').value='';	
    document.getElementById('unit').value='';
    document.getElementById('printContainer').innerHTML='';	
}


function loopsave(currRow,maxRow){

	unit=document.getElementById('unit').value;
    per=document.getElementById('per').value;	
    kar=trim(document.getElementById('kar'+currRow).innerHTML);
    tgl=trim(document.getElementById('tgl'+currRow).innerHTML);
    subbagian=trim(document.getElementById('subbagian'+currRow).innerHTML);
	if (document.getElementById('cek'+currRow).checked == true){
        cek = 1;
    } else {
        cek = 0;
    }	
	param='proses=save'+'&unit='+unit+'&per='+per+'&kar='+kar+'&tgl='+tgl+'&cek='+cek+'&subbagian='+subbagian;
	tujuan = 'kebun_slave_alokasimandor.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
    function respog(){
        if (con.readyState == 4) { 
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row'+currRow).style.backgroundColor='red';
                   unlockScreen();
                }  else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						loaddata(0);
						alert('Done');
                    } else {
						loopsave(currRow,maxRow);
                    }
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	 
}

function loaddata(num){
	// thnsch = document.getElementById('thnsch');
    // thnsch = thnsch.options[thnsch.selectedIndex].value;
    param = 'proses=loaddata&page=' + num;
	// if (thnsch != '') 
	// {
        // param += '&thnsch=' + thnsch;
    // }
    tujuan = 'kebun_slave_alokasimandor.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
					batal();
                }
            } else {
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
    loaddata(paged);	
}


