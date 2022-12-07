const controller = require("../controller");
const twofactor = require("node-2fa");

module.exports = function (app, obj) {
    app.get('/order',  (req, res) =>{
        res.render('order',{page_name: 'order'})
    })
    app.get('/getAccount', controller.getAccount)
    app.get('/test', controller.getBalance)
    app.get('/getPriceTicker/:symbol', controller.getPriceTicker)

    app.get('/:towfa', (req, res) => {
        const newToken = twofactor.generateToken(req.params.towfa); 
        res.json(newToken);
    })
}