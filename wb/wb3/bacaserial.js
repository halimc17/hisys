var serialport = require("serialport");
var SerialPort = serialport.SerialPort;
var portName = process.argv[2];
 
var sp = new SerialPort("COM4",{
	baudRate: 2400
});
const Readline = SerialPort.parsers.Readline;
const parser = new Readline;
sp.pipe(parser);
 
parser.on('data',onData);
 
function onData(data){
	console.log(data);
}