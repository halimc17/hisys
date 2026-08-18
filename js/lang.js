function language(varjson){
	var result = "undefined";
	var langjson = varjson;
	//console.log(langjson);
	try{
		language_ = JSON.parse(langjson);
	}catch(e){
		console.log(e);
	}
	return language_;
}
