function callafterSimpan(result){
    if(result.response.error){
        $.Alert(result.response.message);
    }else{
        $.refresh();
        result.element.close();
    }


}