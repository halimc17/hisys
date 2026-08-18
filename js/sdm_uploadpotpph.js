function getperiode()
{
    tmplkodeorg=document.getElementById('tmplkodeorg').value;
    param='tmplkodeorg='+tmplkodeorg+'&proses=getperiode';
    fileTarget='sdm_slave_uploadpotpph.php';
    post_response_text(fileTarget, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                   document.getElementById('tmplperiode').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}


function download() 
{

    tmplkodeorg=document.getElementById('tmplkodeorg');
    tmplkodeorg=tmplkodeorg.options[tmplkodeorg.selectedIndex].value;
    tmplperiode=document.getElementById('tmplperiode').value;
    
    param='proses=download&tmplkodeorg='+tmplkodeorg+'&tmplperiode='+tmplperiode;
    tujuan='sdm_slave_uploadpotpph.php';
    
    if (tmplkodeorg == '') 
    {
            alert('Data inconsistent');
    }
    else
    {
        window.location.href = "sdm_slave_uploadpotpph.php?"+param; 
    }

}

function cancelData(){
    document.getElementById('tmplperiode').value='';
    document.getElementById('frm').reset();
}

function submitFile()
{
    if(confirm('Are you sure..?'))
    {
        document.getElementById('frm').submit();
    }
}