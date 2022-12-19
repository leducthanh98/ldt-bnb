<?php

function getSVtime()
{
	$url= "https://api.binance.com/api/v3/time";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	$obj = json_decode($result );
	return $obj->{'serverTime'};
}

function order($apikey,$secretkey,$symbol,$type,$side,$quantity,$price,$stopPrice)
{
	$recvWindow = "50000";
	$ctime = getSVtime();
	$url= "https://api.binance.com/api/v3/order?";
	
	if($type==1) //"LIMIT"
	{
		$type = "LIMIT";
		$query = "timestamp=".$ctime."&symbol=".$symbol."&side=".$side."&type=".$type."&timeInForce=GTC&quantity=".$quantity."&price=".$price."&recvWindow=".$recvWindow;
	}
	else if($type==2) //"MARKET"
	{
		$type = "MARKET";
		$query = "timestamp=".$ctime."&symbol=".$symbol."&side=".$side."&type=".$type."&quantity=".$quantity;
	}
	else if($type==3 || $type==4) //3: "STOP_LOSS_LIMIT"  - 4:"TAKE_PROFIT_LIMIT"
	{
		if($type==3)
			$type = "STOP_LOSS_LIMIT";
		else
			$type = "TAKE_PROFIT_LIMIT";
		
		$query = "timestamp=".$ctime."&symbol=".$symbol."&side=".$side."&type=".$type."&quantity=".$quantity."&price=".$price."&stopPrice=".$stopPrice;
	}
	else if($type==5 || $type==6) //5: "STOP_LOSS" -  6: "TAKE_PROFIT" , Lenh Market
	{
		if($type==5)
			$type = "STOP_LOSS";
		else
			$type = "TAKE_PROFIT";

		$query = "timestamp=".$ctime."&symbol=".$symbol."&side=".$side."&type=".$type."&quantity=".$quantity."&stopPrice=".$stopPrice;
	}
	else if($type==7) //"DELETE"
	{
		$type = "DELETE";
		$query = "timestamp=".$ctime."&symbol=".$symbol;
		$url = "https://api.binance.com/api/v3/openOrders?";
	}
	else if($type==8) //"VIEWORDER"
	{
		$type = "OPENORDER";
		$query = "timestamp=".$ctime."&symbol=".$symbol;
		$url = "https://api.binance.com/api/v3/openOrders?";
	}
	else
		return "Ma lenh yeu cau khong hop le:".$type;

	$sig = hash_hmac('sha256', $query, $secretkey);

	$url = $url.$query."&signature=".$sig;
	//return $url;

	$ch = curl_init(); 

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if($type == "DELETE"){
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	}
	else if($type == "OPENORDER"){
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
	}
	else{	
		curl_setopt($ch, CURLOPT_POST, 1);
	};
	$headers = array(); $headers[] = 'X-Mbx-Apikey: '.$apikey; curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);

	if (curl_errno($ch)) 
	{
	    return 'Error:' . curl_error($ch);
	}
	else
		return $result;

	curl_close ($ch);

}

function balance($apikey,$secretkey)
{
	$recvWindow = "100000";
	$ctime = getSVtime();
	$query = "&timestamp=".$ctime."&recvWindow=".$recvWindow;
	$sig = hash_hmac('sha256', $query, $secretkey);
	$url= "https://fapi.binance.com/fapi/v2/balance?".$query."&signature=".$sig;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	$headers = array(); $headers[] = 'X-Mbx-Apikey: '.$apikey; curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);

	if (curl_errno($ch)) {
	    return 'Error:' . curl_error($ch);
	}
	else
		return $result;

	curl_close ($ch);
}

function tranfer($apikey,$secretkey,$amount,$type)
{
		$asset = "USDT";
		$ctime = getSVtime();
		
		if($type==2) // Neu rut tien thi lay so du toi da duoc phep rut thiet lap cho $amount
		{
			$balance = json_decode(balance($apikey,$secretkey),true);
			$amount = $balance[0]["maxWithdrawAmount"]; // Rut het so tien duoc phep rut
		}
		
		$query = "timestamp=".$ctime."&asset=".$asset."&amount=".$amount."&type=".$type;

		$sig = hash_hmac('sha256', $query, $secretkey);

		$url= "https://api.binance.com/sapi/v2/futures/transfer?".$query."&signature=".$sig;
		//return $url;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$headers = array(); $headers[] = 'X-Mbx-Apikey: '.$apikey; curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);

		if (curl_errno($ch)) {
		    return 'Error:' . curl_error($ch);
		}
		else
			return $result;
}

function initLevel($apikey,$secretkey,$symbol,$level)
{
	$ctime = getSVtime();
	$query = "timestamp=".$ctime."&symbol=".$symbol."&level=".$level;
	$sig = hash_hmac('sha256', $query, $secretkey);
	$url= "https://api.binance.com/fapi/v2/leverage?".$query."&signature=".$sig;
	//return $url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	$headers = array(); $headers[] = 'X-Mbx-Apikey: '.$apikey; curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);

	if (curl_errno($ch)) {
		return 'Error:' . curl_error($ch);
	}
	else
		return $result;
}
function change($symbol)
{
	$query = "symbol=".$symbol;
	$url= "https://fapi.binance.com/fapi/v1/ticker/24hr?".$query;
	//return $url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, 1);
	$result = curl_exec($ch);

	if (curl_errno($ch)) {
		return 'Error:' . curl_error($ch);
	}
	else
		return $result;
}
function mainBalance($apikey,$secretkey)	
{
	$ctime = getSVtime();
	$query = "&timestamp=".$ctime;

	$sig = hash_hmac('sha256', $query, $secretkey);

	$url= "https://api.binance.com/api/v3/account?".$query."&signature=".$sig;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$headers = array(); $headers[] = 'X-Mbx-Apikey: '.$apikey; curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);

	if (curl_errno($ch)) {
	    return 'Error:' . curl_error($ch);
	}
	else
		return $result;

	curl_close ($ch);
}
?>