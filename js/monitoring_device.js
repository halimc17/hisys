const today = new Date();

function formatDate(date, format) {
    const map = {
        mm: date.getMonth() + 1,
        dd: date.getDate(),
        yy: date.getFullYear()
    }

    //const monthNames = {"Januari", "Februari", "Maret", "April", "Mei", "Juni","Juli", "Agustus", "September", "Oktober", "November", "Desember"};
    return format.replace(/mm|dd|yy|YYYY/gi, matched => map[matched])
}

function loadtableatt(){
    let param   = 'method=loadtableatt';
	let tujuan  = 'monitoring_slave_device.php';
	post_response_text(tujuan, param, function(){
        if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info", con.responseText);
				} else {
                    document.getElementById('table1').innerHTML = con.responseText;

                    loadtablecrh();
                }
            }
        }
    });
}

function loadtablecrh(){
    let param   = 'method=loadtablecrh';
	let tujuan  = 'monitoring_slave_device.php';
	post_response_text(tujuan, param, function(){
        if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info", con.responseText);
				} else {
                    document.getElementById('table2').innerHTML = con.responseText;

                    loadtablesounding();
                }
            }
        }
    });
}

function loadtablesounding(){
    let param   = 'method=loadtablesounding';
	let tujuan  = 'monitoring_slave_device.php';
	post_response_text(tujuan, param, function(){
        if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info", con.responseText);
				} else {
                    document.getElementById('table3').innerHTML = con.responseText;

                    previewcanvas1();
                }
            }
        }
    });
}

function prveiewcanvas(){
	let param   = 'method=prveiewcanvas';
	let tujuan  = 'monitoring_slave_device.php';
	post_response_text(tujuan, param, function(){
        if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info",con.responseText);
				} else {
					vsplt = con.responseText.split("####");
					
					let unit 	= JSON.parse(vsplt[0]);
					let unit2 	= JSON.parse(vsplt[3]);
					let list 	= JSON.parse(vsplt[1]);
					let dt 		= JSON.parse(vsplt[2]);
					let dt2 	= JSON.parse(vsplt[4]);
					let dt3 	= JSON.parse(vsplt[5]);
					let dt4 	= JSON.parse(vsplt[6]);
					let dt5 	= JSON.parse(vsplt[7]);
					let list2 	= JSON.parse(vsplt[8]);
					let list3 	= JSON.parse(vsplt[9]);
					let dt6 	= JSON.parse(vsplt[10]);
					let list4 	= JSON.parse(vsplt[11]);
					let list5 	= JSON.parse(vsplt[12]);
					let dt7 	= JSON.parse(vsplt[13]);
					let dt8 	= JSON.parse(vsplt[14]);
					let list6 	= JSON.parse(vsplt[15]);
					let dt9 	= JSON.parse(vsplt[16]);
					let list7 	= JSON.parse(vsplt[17]);			
					
					//###############
					let arrunit = new Array();
					for(let key in unit2){
						arrunit.push(key);
					}
					
					let arrDatasets = new Array();
					let valcolor    = new Array();

					for(let key2 in list5){
						let Datasets     = {};
						let valdata      = new Array();
						let randomcolorx = randomcolor();
						
						for(let keydt in unit2){
							valdata.push(dt3[key2][keydt]);
						}

						Datasets['label'] = list5[key2];
						Datasets['backgroundColor'] = randomcolorx[0];
						Datasets['borderColor'] = randomcolorx[1];
						Datasets['data'] = valdata;
						Datasets['borderWidth'] = "1";
						valcolor.push(randomcolorx[0]);
						arrDatasets.push(Datasets);
					}
					
					let ctx = document.getElementById("canvas1").getContext('2d');
					let data = {
						labels: arrunit,
						datasets: arrDatasets
					};
					
					let myBarChart = new Chart(ctx,{
						type: 'bar',
						data: data,
						options: {
							title:{
								display: true,
								text: 'Perhitungan Tangki : ' + formatDate(today, 'mm/yy')
							},
							scales: {
								yAxes: [{
									ticks: {
										beginAtZero:true,
										
										userCallback: function(value, index, values) {
											value = value.toString();
											value = value.split(/(?=(?:...)*$)/);
											value = value.join('.');
											return value;
										}
									}
								}]
							},
							tooltips: {
								callbacks: {
									label: function(tooltipItem, data) {
										let label = data.datasets[tooltipItem.datasetIndex].label;
										let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
										value = value.toString();
										value = value.split(/(?=(?:...)*$)/);
										value = value.join('.');
										return ' ' + label + ' : ' + value;
									}
								}
							}
						}
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
    });
}

function previewcanvas1(){
    let param   = 'method=previewcanvas1';
	let tujuan  = 'monitoring_slave_device.php';
	post_response_text(tujuan, param, function(){
        if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info", con.responseText);
				} else {
                    let myObj = JSON.parse(con.responseText);

                    let waktu   = myObj.waktu;
                    let volume  = myObj.volume;

                    let arrwaktu = new Array();
					for(let key in waktu){
						arrwaktu.push(waktu[key]);
					}

                    let arrDatasets = new Array();
					let valcolor    = new Array();

                    let Datasets     = {};
                    let valdata      = new Array();
                    let randomcolorx = randomcolor();
                    
                    for(let key in volume){
                        valdata.push(volume[key]);
                    }

                    Datasets['label'] 			= 'Volume';
                    Datasets['backgroundColor'] = randomcolorx[0];
                    Datasets['borderColor'] 	= randomcolorx[1];
                    Datasets['data'] 			= valdata;
                    Datasets['borderWidth'] 	= "1";

                    valcolor.push(randomcolorx[0]);
                    arrDatasets.push(Datasets);

                    let ctx = document.getElementById("canvas").getContext('2d');

					let data = {
						labels: arrwaktu,
						datasets: arrDatasets
					};
					
					var myBarChart = new Chart(ctx,{
						type: 'bar',
						data: data,
						options: {
							title:{
								display: true,
								text: 'Sounding : ' + formatDate(today, 'dd/mm/yy')
							},
							scales: {
								yAxes: [{
									ticks: {
										beginAtZero:true,
										
										userCallback: function(value, index, values) {
											value = value.toString();
											value = value.split(/(?=(?:...)*$)/);
											value = value.join('.');
											return value;
										}
									}
								}]
							},
							tooltips: {
								callbacks: {
									label: function(tooltipItem, data) {
										let label = data.datasets[tooltipItem.datasetIndex].label;
										let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
										value = value.toString();
										value = value.split(/(?=(?:...)*$)/);
										value = value.join('.');
										return ' ' + label + ' : ' + value;
									}
								}
							}
						}
					});
                }

				setTimeout(loadtableatt, 20000);
            }
        }
    });
}

function randomcolor(){
    // const randomColor = Math.floor(Math.random()*16777215).toString(16);
    // let hasil = "#"+randomColor;
    
    hasil = new Array();
    let rgb = [];
    for(let i = 0; i < 3; i++){
        rgb.push(Math.floor(Math.random() * 255));
    }
    
    hasil[0] = 'rgb('+ rgb.join(',') + ',0.2)';
    hasil[1] = 'rgb('+ rgb.join(',') + ',1)';
    
    return hasil;
}

function randomcolor2(){
    const randomColor = Math.floor(Math.random()*16777215).toString(16);
    let hasil = "#"+randomColor;
    
    return hasil;
}

// function runLoop() {
// 	loadtableatt;
// 	setTimeout(runLoop, 5000);
// }