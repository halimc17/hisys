function signout(){
    alertify.confirm("Apakah ingin signout ?",
    function(){
        //signout
        if(typeof sessionStorage.api_key != 'undefined'){
            sessionStorage.removeItem("api_key");
        }
        if(typeof sessionStorage.token != 'undefined'){
            sessionStorage.removeItem("token");
        }
        window.location = site_url('logout');
    },
    function(){
        //alertify.error('Cancel');
    });
}