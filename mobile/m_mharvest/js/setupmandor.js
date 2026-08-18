function listAction(getPage){
    tujuan= $.options.slave+getPage;
    let options = {
        url: tujuan,
	    title:'Detail Kemandoran',
        success :function(arg){
            console.log(arg);
        } 
    };
	winUpdate = $.openWindow(options);
}