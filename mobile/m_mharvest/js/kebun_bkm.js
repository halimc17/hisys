
function postingAction(getPage){
    tujuan= $.options.slave+getPage;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin POSTING data ini? ',function(){
        $.get(ele,tujuan,function callback(Result){
            if(!Result.response.error){
                //Result.element.remove();
                $.refresh();
            }else{
                $.Alert(Result.response.message);
            }
        });
    });
}

function deleteActionHeader(notransaksi){
    param="?switcher=deleteAll&notransaksi="+notransaksi;
    tujuan= $.options.slave+param;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin menghapus data ini? ',function(){
        $.get(ele,tujuan,function callback(Result){
            console.log(Result);
            if(!Result.response.error){
                //Result.element.remove();
                $.refresh();
            }else{
                $.Alert(Result.response.message);
            }
        });
    });
}
function postingBkm(notransaksi){
    param="?switcher=posting&notransaksi="+notransaksi;
    tujuan= $.options.slave+param;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin memposting data ini? ',function(){
        $.get(ele,tujuan,function callback(Result){
            console.log(Result);
            if(!Result.response.error){
                //Result.element.remove();
                $.refresh();
            }else{
                $.Alert(Result.response.message);
            }
        });
    });
}
var winUpdate;
function listAction(getPage){
    tujuan= $.options.slave+getPage;
    let options = {
        url: tujuan,
	    title:'<strong>BUKU KEGIATAN MANDOR<strong>',
        success :function(arg){
            console.log(arg);
        } 
    };
	winUpdate = $.openWindow(options);
}
function pascaSubmit(Result){
    if(!Result.response.error){
        $.refresh();
        if(typeof winUpdate != 'undefined'){
            $.clearNewContainer();
        }else{
            $.buatbaru.close();
        }
        
    }else{
        $.Alert(Result.response.message);
    }
}