function viewAction(getPage){
    tujuan= $.options.slave+getPage;
    let options = {
        url: tujuan,
	    title:'View Profile',
        success :function(arg){
            console.log(arg);
        } 
    };
	winUpdate = $.openWindow(options);
}