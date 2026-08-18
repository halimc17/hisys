function detelemakan (maxRow)
{
    
    tipe=document.getElementById('tipe').value;
    per=document.getElementById('per').value;
    unit=document.getElementById('unit').value;
   
    param='proses=delete'+'&tipe='+tipe+'&per='+per+'&unit='+unit;
    tujuan='sdm_slave_3uangmakan.php';
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
                    saveAll(maxRow);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }	
}


maxf=0
sekarang=1;
function saveAll(maxRow)
{     

      	 maxf=maxRow;
	    loopsave(1,maxRow);
}


function batal()
{
    document.getElementById('per').value='';	
    document.getElementById('unit').value='';
    document.getElementById('tipe').value='';
    document.getElementById('rupiah').value='';
    document.getElementById('printContainer').innerHTML='';	
}


function loopsave(currRow,maxRow)
{
    periode=trim(document.getElementById('periode'+currRow).innerHTML);
    karyawanid=trim(document.getElementById('karyawanid'+currRow).innerHTML);
    premi=trim(document.getElementById('premi'+currRow).innerHTML);
    unt=document.getElementById('unit');
    unt=unt.options[unt.selectedIndex].value;

    if(periode=='' || karyawanid=='' || premi=='')
    {
            alert("Data tidak lengkap");return;
    }	
    else
    {  
        param='periode='+periode+'&karyawanid='+karyawanid+'&premi='+premi;
        param+="&proses=savedata"+'&unit='+unt;

            //alert(param);
            tujuan = 'sdm_slave_3uangmakan.php';
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

/////////////////
/////tj absensi
///////////////



function deteletjabsen(maxRowtjabsen)
{
    
    tipetjabsen=document.getElementById('tipetjabsen').value;
    pertjabsen=document.getElementById('pertjabsen').value;
    unittjabsen=document.getElementById('unittjabsen').value;
   
    param='proses=delete'+'&tipetjabsen='+tipetjabsen+'&pertjabsen='+pertjabsen+'&unittjabsen='+unittjabsen;
    tujuan='sdm_slave_3tjabsen.php';
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
                    saveAlltjabsen(maxRowtjabsen);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }	
}


maxftjabsen=0
sekarangtjabsen=1;

function saveAlltjabsen(maxRowtjabsen)
{     

      	 maxftjabsen=maxRowtjabsen;
	    loopsavetjabsen(1,maxRowtjabsen);
}


function bataltjabsen()
{
    document.getElementById('pertjabsen').value='';	
    document.getElementById('unittjabsen').value='';
    document.getElementById('tipetjabsen').value='';
    document.getElementById('printContainertjabsen').innerHTML='';	
}

function loopsavetjabsen(currRowtjabsen,maxRowtjabsen)
{
    tipetjabsen=document.getElementById('tipetjabsen');
    tipetjabsen=tipetjabsen.options[tipetjabsen.selectedIndex].value;       
            
    periode=trim(document.getElementById('periode'+currRowtjabsen).innerHTML);
    karyawanid=trim(document.getElementById('karyawanid'+currRowtjabsen).innerHTML);
    premi=trim(document.getElementById('premi'+currRowtjabsen).innerHTML);
    unt=document.getElementById('unittjabsen');
    unt=unt.options[unt.selectedIndex].value;
   
    if(periode=='' || karyawanid=='' || premi=='')
    {
            alert("Data tidak lengkap");return;
    }	
    else
    {  
        param='periode='+periode+'&karyawanid='+karyawanid+'&premi='+premi;
        param+="&proses=savedata"+'&unittjabsen='+unt+'&tipetjabsen='+tipetjabsen;
        tujuan = 'sdm_slave_3tjabsen.php';
        post_response_text(tujuan, param, respog);
        document.getElementById('row'+currRowtjabsen).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                        document.getElementById('row'+currRowtjabsen).style.backgroundColor='red';
                        unlockScreen();
                }
                else {
                    document.getElementById('row'+currRowtjabsen).style.display='none';
                    currRowtjabsen+=1;
                    sekarangtjabsen=currRowtjabsen;
                    if(currRowtjabsen>maxRowtjabsen)
                    {
                            alert('Done');
                            bataltjabsen();
                            //document.location.reload();
                            //document.getElementById('infoDisplay').innerHTML='';
                    }  
                    else
                    {
                            loopsavetjabsen(currRowtjabsen,maxRowtjabsen);
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




///////////////////////////
////////////////////////////


/////////////////
/////premi tetap
///////////////



function deletpremitetap(maxRowpremitetap)
{
    
    tipepremitetap=document.getElementById('tipepremitetap').value;
    perpremitetap=document.getElementById('perpremitetap').value;
    unitpremitetap=document.getElementById('unitpremitetap').value;
   
    param='proses=delete'+'&tipepremitetap='+tipepremitetap+'&perpremitetap='+perpremitetap+'&unitpremitetap='+unitpremitetap;
    tujuan='sdm_slave_3premitetap.php';
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
                    saveAllpremitetap(maxRowpremitetap);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }	
}


maxfpremitetap=0
sekarangpremitetap=1;

function saveAllpremitetap(maxRowpremitetap)
{     

      	 maxfpremitetap=maxRowpremitetap;
	    loopsavepremitetap(1,maxRowpremitetap);
}


function batalpremitetap()
{
    document.getElementById('perpremitetap').value='';	
    document.getElementById('unitpremitetap').value='';
    document.getElementById('tipepremitetap').value='';
    document.getElementById('printContainerpremitetap').innerHTML='';	
}

function loopsavepremitetap(currRowpremitetap,maxRowpremitetap)
{
    tipepremitetap=document.getElementById('tipepremitetap');
    tipepremitetap=tipepremitetap.options[tipepremitetap.selectedIndex].value;       
            
    periode=trim(document.getElementById('periode'+currRowpremitetap).innerHTML);
    karyawanid=trim(document.getElementById('karyawanid'+currRowpremitetap).innerHTML);
    premi=trim(document.getElementById('premi'+currRowpremitetap).innerHTML);
    unt=document.getElementById('unitpremitetap');
    unt=unt.options[unt.selectedIndex].value;
   
    if(periode=='' || karyawanid=='' || premi=='')
    {
            alert("Data tidak lengkap");return;
    }	
    else
    {  
        param='periode='+periode+'&karyawanid='+karyawanid+'&premi='+premi;
        param+="&proses=savedata"+'&unitpremitetap='+unt+'&tipepremitetap='+tipepremitetap;
        tujuan = 'sdm_slave_3premitetap.php';
        post_response_text(tujuan, param, respog);
        document.getElementById('row'+currRowpremitetap).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                        document.getElementById('row'+currRowpremitetap).style.backgroundColor='red';
                        unlockScreen();
                }
                else {
                    document.getElementById('row'+currRowpremitetap).style.display='none';
                    currRowpremitetap+=1;
                    sekarangpremitetap=currRowpremitetap;
                    if(currRowpremitetap>maxRowpremitetap)
                    {
                            alert('Done');
                            batalpremitetap();
                            //document.location.reload();
                            //document.getElementById('infoDisplay').innerHTML='';
                    }  
                    else
                    {
                            loopsavepremitetap(currRowpremitetap,maxRowpremitetap);
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





/////////////////////////
///////////////////////////

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
                    document.getElementById('rupiah').value=con.responseText;   
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

