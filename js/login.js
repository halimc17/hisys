function login()
{
        var file = "";
        let uname   =document.getElementById('name').value;
        let password=document.getElementById('pwd').value;
        var lang=document.getElementById('language').options[document.getElementById('language').selectedIndex].value;
        var theme=document.getElementById('theme').options[document.getElementById('theme').selectedIndex].value;

        if (uname == '' || password == '') {
                alert('Your UserName and Password are required');
                document.getElementById('name').focus();
        }else {
                param = 'uname=' + uname + '&password=' + password +'&language='+lang+'&theme='+theme;
                if(lang == 'dashboard'){
                        file = 'lab/login_slave.php'; 
                }else{
                        file = 'slave_login.php';
                }
                 post_response_text(file, param, respog);
           }

        function respog(){
                if (con.readyState == 4) {
                        if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                        if (con.responseText.lastIndexOf('Wrong') > -1) {
                                                document.getElementById('msg').innerHTML = con.responseText;
                                        }else {
                                                if(lang == 'dashboard'){
                                                        //window.location = 'lab/index.html';
                                                        window.top.location.href = 'lab/index.html';
                                                }else{
                                                        sessionStorage.menuid = "0";
                                                        sessionStorage.menuActive = "0";
                                                        window.location = 'master.php';
                                                        sessionStorage.menuActive = 'master';
                                                        sessionStorage.menuid = 'home';

                                                }
                                                        
                                                //alert(con.responseText);
                                        }
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
                        resetf();//clear from	
                }
        }	   
}

function resetf()//clear from	
{
        document.getElementById('name').value='';
        document.getElementById('pwd').value='';	
}

function enter(e)
{
  key=getKey(e);
  if(key==13)
    {
                login();
            return true;
        }	
  else
        {
                return tanpa_kutip_dan_sepasi(e);
        }	
}
//disable right click====================================
document.oncontextmenu=new Function('return false')
