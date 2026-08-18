const { SerialPort } = require('serialport')
const express = require('express')
require('dotenv').config();
const app = express()
const fs = require('fs')
const portt = 300;
let serv =  app.listen(portt)
const fetch = require('node-fetch');
const chalk = require('chalk');
const readlineSync = require('readline-sync');
const waitPort = require('wait-port');

(async () => {
  const params = {
    host: process.env.SERVER_TIMBANGAN || 'localhost',
    port: 80,
  };
  const mysql = {
    host: process.env.SERVER_DATABASE,
    port: 3306,
  };
  console.log(chalk.bgCyan('Menunggu website timbangan berjalan'));
  await waitPort(params)
  .then(({ open, ipVersion }) => {
    if (open) console.log(chalk.green('website timbangan sudah berjalan'));
    else console.log(chalk.red('website timbangan gagal berjalan!!'));
  })
  .catch((err) => {
    console.err(`An unknown error occured while waiting for the port: ${err}`);
  });
  console.log(chalk.bgCyan('Menunggu server database berjalan'));
  await waitPort(mysql)
  .then(({ open, ipVersion }) => {
    if (open) console.log(chalk.green('Server database sudah berjalan'));
    else console.log(chalk.red('Server database gagal berjalan!!'));
  })
  .catch((err) => {
    console.err(`An unknown error occured while waiting for the port: ${err}`);
  });
  const timbangan = await fetch(`http://${process.env.SERVER_TIMBANGAN}/wb/api.php?method=getport`);
  const dataTimbangan = await timbangan.json();
  const {path,databit,baudRate,parity} = {
    path : dataTimbangan.data.port,
    databit: dataTimbangan.data.databit,
    baudRate: dataTimbangan.data.baudrate,
    parity: dataTimbangan.data.parity
  };
  await console.log(chalk.bgMagenta(`Menghubungkan ke port ${path} dengan baudrate: ${baudRate}`));
  const port = new SerialPort({ 
    path: path, 
    baudRate: parseInt(baudRate),
    parity: parity,
    dataBits: databit,
    stopBits: parseInt(1),
  }).setEncoding('utf8')
  // console.log(datas.port);
  port.on('readable', () => {
    let data = port.read().toString('utf8');
    let datas = data.replace("\n \u0002",'').replace('K','').replace('    ','').replace(')','').replace('*','').replace('M','').replace('','').replace("\u0002",'').replace(' ','').replace('  ','').replace(' ','').replace('K','').replace('.','').replace('g','').replace(',','').replace('T','').replace('G','').replace('S','').replace('+','');
    if(data != null){
      fs.appendFile('indikator.txt',datas, 'utf8',() => {});
      console.log(chalk.bgGreen('Aplikasi sudah berjalan, mohon jangan diclose'));
      const stats = fs.statSync('indikator.txt');
      const fileSizeInBytes = stats.size;
      if(fileSizeInBytes > 10000){
         fs.truncate('indikator.txt', 0, (err) => {});
      }
      // fs.writeFileSync('qr.txt',data, 'utf8');
      // fs.writeFile('qr.txt',data,'utf8',(err) => {
      //   if(err) console.error('errror');
      // });
    }
  
  });
  app.get('/', (req, res) => {
      fs.readFile('indikator.txt', 'utf8', (err, data) => {
        let respon;
        if(!data || err){
          res.statusCode = 404;
          respon  = {
            status: false,
            msg: 'Indikator tidak terbaca'
          }
        }else{
          res.statusCode = 200;
          let datas = data.split("\r");
	  //edit bagian sini untuk pembacaan indikator
          let beratIndikator = 0;
          if(datas.length > 0){
            beratIndikator = datas[datas.length - 2].replace("\n \u0002",'').replace('kg','').replace(')','').replace('*','').replace('M','').replace('    ','').replace('','').replace("\u0002",'').replace(' ','').replace('  ','').replace(' ','');
		//beratIndikator = datas[datas.length - 2];
          }
          respon  = {
            status: true,
            msg: beratIndikator
          }
        }
        res.json(respon);
        fs.truncate('indikator.txt', 0, (err) => {})
      });
      // await serv.close();
      // await app.listen(portt)
    });
  
  
  // Switches the port into "flowing mode"
  // port.on('data', function (data) {
  //   console.log('Data:', data)
  // })
  
  // Open errors will be emitted as an error event
  port.on('error', function(err) {
    console.log(chalk.bgRed('Error'), chalk.redBright(err.message));
    console.log(chalk.bgYellow('Silahkan setting port di parameter aplikasi dan  restart aplikasi owl.bat atau restart komputer setelah melakukan setting port di aplikasi weighbridge'))
  })
})();