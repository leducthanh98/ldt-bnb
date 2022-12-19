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
            console.log("Error getting");
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
            let data = await this.loadFile('./account.json');
            data = JSON.parse(data);
            let [start, end] = this.getStartEnd(req, data)
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
            let ctime = await this.getServerTime();
            let data = await this.loadFile('./account.json');
            data = JSON.parse(data);
            let [start, end] = this.getStartEnd(req, data);
            let response = [];
            for (let i = start; i <= end; i++) {
                let balance = await this.queryBalance(data[i]['apiKey'], data[i]['secretKey'], ctime)
                response.push({"mail" : data[i]['mail'], "balance": balance});
            }
            res.json(response);
        } catch (error) {
            console.log(error);
            res.json('Không có dữ liệu trùng khớp!');
        }
    }

    async queryBalance (apiKey, secretKey, ctime) {
        try {
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
            data = data.data.balances.filter(balance => parseFloat(balance["free"]) > 0.02);
            return data;
        } catch (err) {
            console.log(err);
            return err;
        }
    }

    getOrder = async (req, res, next) => {
        try {
            let ctime = await this.getServerTime();
            let data = await this.loadFile('./account.json');
            data = JSON.parse(data);
            let [start, end] = this.getStartEnd(req, data)
            let response = [];
            for (let i = start; i <= end; i++) {
                let orders = await this.queryOpen(data[i]['apiKey'], data[i]['secretKey'], ctime)
                response.push({"mail" : data[i]['mail'], "orders": orders});
            }
            res.json(response);
        } catch (error) {
            res.json('Không có dữ liệu trùng khớp!');
        }
    }

    async queryOpen (apiKey,secretKey, ctime) {
        try {
            let recvWindow = "50000";
            let query = "&timestamp=" + ctime + "&recvWindow=" + recvWindow;
            let sig = crypto.createHmac("sha256", secretKey).update(query).digest('hex');
            let headers = {
                headers: {
                    'X-MBX-APIKEY': apiKey
                }
            }
            var burl = "https://api.binance.com";
            var endPoint = "/api/v3/openOrders";
            var url = burl + endPoint + '?' + query + '&signature=' + sig;
            var data = await axios.get(url, headers);
            return data.data;
        } catch (err) {
            return err;
        }
    }

    getStartEnd = (req, data)=> {
        let start = req.query.start;
        let end = req.query.end ? req.query.end : data.length - 1;
        end = end == 'undefined' ? start : end;
        end = end > data.length - 1 || end == 'undefined' ? data.length - 1 : end;
        return [start, end];
    }
}

module.exports = new Controller 
