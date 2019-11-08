<?php

error_reporting(0);

$token = "659463677:AAH55rr1RUrvzNU8MrKSCWrfjTKpAdvnsHg";
$website = "https://api.telegram.org/bot".$token;

$update = file_get_contents("php://input");
$update = json_decode($update);

$chatID = $update->message->chat->id;
$messageID = $update->message->message_id;

if(isset($_GET['source'])){
    if($_GET['source'] == 'cronjob'){
        $chatID = "@diskusirizubot";
        $messageID = "";
        printcandle();
    }
}

if ($update->message->chat->type == "private") {
    $text = "Layanan private chat tidak tersedia. Silahkan masuk ke group dengan mengklik link berikut : https://t.me/joinchat/IHAabUgazGBSrd1ZSVdtgQ";
} else {
    $key = explode("@",$update->message->text ,2);
    if($key[1]=="candle_coinex_bot") {
        $text = $key[0];
        $text = carikey($key[0],$chatID);
    }
}

if ($text != "error") {
    sendMessage($chatID, $messageID, $text);
}

function carikey($key,$chatID){
    switch(true){
        case $key == "/help" :
            $hasil = printhelp();
            break;
        case $key == "/candle" :
            $hasil = printcandle();
            break;
        default :
            $hasil = "error";
            break;
    }
    return $hasil;
}

function printtime(){
    $data = file_get_contents("http://api.timezonedb.com/v2.1/get-time-zone?key=GA80UOIX0ALF&format=json&by=zone&zone=Asia/Jakarta");
    $data = json_decode($data);
    $hasil = "Pesan ini dikirim pada ".$data->formatted;
    return $hasil;
}

function printhelp(){
    $hasil = "\xE2\x9C\x85 Daftar Perintah%0A%0A";
    $hasil .= "/help Lihat daftar perintah%0A";
    $hasil .= "/candle Lihat candle MA dan MAE%0A%0A";
    $hasil .= printtime();
    return $hasil;
}

function printcandle(){
    $url = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=https://11api-botlist.000webhostapp.com/candle_coinex/candle.php&screenshot=true';
    file_get_contents($url);

    $chatID = $GLOBALS['chatID'];
    $messageID = $GLOBALS['messageID'];
    $url = $GLOBALS['website']."/sendPhoto?chat_id=".$chatID;
    $img = curl_file_create(realpath('image.png'), 'image/png');
    $caption = "\xF0\x9F\x92\xB9 Coinex Market Depth Candle BTC/USDT \xF0\x9F\x92\xB9\n\n";
    $caption .= printtime();

    sendPhoto($chatID, $img, $caption, $messageID, $url);
    return "error";
}

function sendPhoto($chatID, $img, $caption, $messageID, $url){
    $post_fields = array(
        'chat_id'   => $chat_id,
        'photo'     => $img,
        'caption'   => $caption,
        'reply_to_message_id' => $messageID
    );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type:multipart/form-data"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_exec($ch);
    curl_close($ch);
}    

function sendMessage($chatID, $messageID, $message) {
    $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatID."&text=".$message."&reply_to_message_id=".$messageID."&parse_mode=HTML"; 
    file_get_contents($url);
}

?>