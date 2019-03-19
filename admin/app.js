const path = require('path')
const express = require('express')
const WebSocket = require('express-ws')
const createError = require('http-errors')
const cookieParser = require('cookie-parser')
const hbs = require('express-handlebars')
const fileUpload = require('express-fileupload');
const fs = require('fs')
const childProcess = require('child_process')
const logger = require('morgan')

var app = express()
var webSocket = WebSocket(app)

app.use(logger('dev'))
app.use(express.json())
app.use(express.urlencoded({ extended: true }))
app.use(cookieParser())
app.use(express.static(path.join(__dirname, 'public')))
app.use(fileUpload())

app.post('/register/req', function(req, res, next){
    try{
      var number = req.body.number
      var regOutput = childProcess.spawnSync('php7.0', ['../Chat-API/registerTool.php', number, 'req'], {
      cwd: '../Chat-API/',
      encoding: 'utf8'
      })
      res.json({
        stdout: regOutput.stdout,
        stderr: regOutput.stderr
      })
    } catch(err){
      res.json({
        error: err
      })
    }
})

app.post('/register/vrfy', function(req, res, next){
    try{
      var number = req.body.number
      var otp = req.body.otp
      var regOutput = childProcess.spawnSync('php7.0', ['../Chat-API/registerTool.php', number, otp], {
        cwd: '../Chat-API/',
        encoding: 'utf8'
      })
      res.json({
        stdout: regOutput.stdout,
        stderr: regOutput.stderr
      })
    } catch(err){
      res.json({
        error: err
      })
    }
})

app.post('/bots/sync', function (req, res, next) {
  var number = req.body.number
  var token = req.body.token
  var outbot = childProcess.spawnSync('php7.0', ['../Chat-API/first_run.php', number, token],  {
    cwd: '../Chat-API/',
    encoding: 'utf8'
  })
  res.json({
    stdout: outbot.stdout,
    stderr: outbot.stderr,
  })
})

app.post('/bots/start', function(req,res,next) {
  var number = req.body.number
  var token = req.body.token
  var type = req.body.status
  var outbot = childProcess.spawnSync('php7.0', ['../Chat-API/sample.php', number, token, type],  {
    cwd: '../Chat-API/',
    encoding: 'utf8'
  })
  res.json({
    stdout: outbot.stdout,
    stderr: outbot.stderr,
  })
})

app.post('/bots/sendMessage', function(req,res,next) {
  var sendTo = req.body.sendTo
  var sendFrom = req.body.sendFrom
  var token = req.body.token
  var message = req.body.message
  var outbot = childProcess.spawnSync('php7.0',['../'])
})
app.listen(3000, () => {
    console.log("Server running on port 3000");
});
