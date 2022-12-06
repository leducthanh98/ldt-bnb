const express = require('express')
const fs = require('fs')
const path = require('path')
const app = express()

app.set('view engine', 'ejs')
app.set('views', path.join(__dirname, 'views'));

app.use(express.static(path.join(__dirname , 'public')))
app.use(express.urlencoded({
  extended: true
}))
app.use(express.json())

const route = require('./routes')
route(app, )



app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname + '/index.html'))
})

const port = 3050
app.listen(port, () => {}) 