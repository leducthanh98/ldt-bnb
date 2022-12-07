const axios = require('axios');
const crypto = require("crypto");
const fs = require('fs').promises;
const { response } = require('express');

class Controller {

    async loadFile(file) {
        try {
            return await fs.readFile(file);
        } catch (err) {
            return (err.message);
        }
    }




    async getServerTime() {
        const url = "https://api.binance.com/api/v3/time";
        try {
            const response = await axios.get(url);
            console.log(response.data.serverTime)
            return response.data.serverTime
        } catch (error) {
            console.error(error);
        }
    }

    getPriceTicker = async (req, res, next) => {
        try {
            let symbol = req.params.symbol.toUpperCase();
            var burl = "https://api.binance.com";
            var endPoint = "/api/v3/ticker/price";
            var url = burl + endPoint + '?&symbol=' + symbol;
            console.log(url);
            var data = await axios.get(url);
            res.json(data.data);
        } catch (err) {
            res.status(401).send(err.message);
        }
    }

    getAccount = async (req, res, next) => {
        try {

            let data = await this.loadFile('../account.json');
            data = JSON.parse(data);
            let start = req.query.start;
            let end = req.query.end ? req.query.end : data.length - 1;
            end = end == 'undefined' ? start : end;
            end = end > data.length - 1 || end == 'undefined' ? data.length - 1 : end;
            let response = [];
            for (let i = start; i <= end; i++) {
                response.push(data[i]['mail']);
            }
            res.json(response);
        } catch (error) {
            res.json('Không có dữ liệu trùng khớp!');
        }
    }


    getBalance = async (req, res, next) => {
        try {
            let apiKey = 'ZCxCCB7VjhkYidHembmoGSlE6stRz6AvyyQMUucV9ZxqvTdHSDFeT6Yf3IINZwe0';
            let secretKey = 'LdLC8aCqVzz7chfORafhdwkUFRvQECvguljHzlDOhpJvJXdtW48uwk8fUkNjeloj';
            let recvWindow = "100000";
            let ctime = await this.getServerTime();
            let query = "&timestamp=" + ctime;
            let sig = crypto.createHmac("sha256", secretKey).update(query).digest('hex');
            let headers = {
                headers: {
                    'X-MBX-APIKEY': apiKey
                }
            }
            var burl = "https://api.binance.com";
            var endPoint = "/api/v3/account";
            var url = burl + endPoint + '?' + query + '&signature=' + sig;
            console.log(url);
            var data = await axios.get(url, headers);
            data = data.data.balances;
            res.json(data);
        } catch (err) {
            return res.status(401).send(err.message);
        }
    }
}

module.exports = new Controller 
