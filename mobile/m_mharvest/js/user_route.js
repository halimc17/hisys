function getUserRoute(val){
    let tanggal = '';
    if(document.getElementById('usertanggal')){
        tanggal = document.getElementById('usertanggal').value;
    }
    if(val == "ALL"){
        getRoute_all(tanggal);
    }else{
        getRoute_user(val+"||"+tanggal);
    }
}
