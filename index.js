
const express = require('express');
const bodyParser = require('body-parser');
const Pusher = require('pusher');
const app = express();
const ip = require('ip')
const spawn = require('child_process').spawn
// Body parser middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

var intervalId= setInterval(function(){
    const bat = spawn('cmd.exe', ['/c', 'C:\\xampp\\htdocs\\auto_email\\email_auto_po.bat'])
    bat.stdout.on('data', (data) => {
    console.log(data.toString());
    });

    bat.stderr.on('data', (data) => {
    console.error(data.toString());
    });

    bat.on('exit', (code) => {
    console.log(`Child exited with code ${code}`);
    });
  }, 10000); 
 //stop interval
//  setTimeout(function(){
//      console.log('clear')
//     clearInterval(intervalId);
//  },3000);
// const bat = spawn('cmd.exe', ['/c', 'C:\\124_RECEIVING_PROGRAM.bat'])
// bat.stdout.on('data', (data) => {
// console.log(data.toString());
// });

// bat.stderr.on('data', (data) => {
// console.error(data.toString());
// });

// bat.on('exit', (code) => {
// console.log(`Child exited with code ${code}`);
// });

app.listen(1234, () => {
    return console.log('Server is up on 1234');
});
