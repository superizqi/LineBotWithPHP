<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

// use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
// use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
// use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
// use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
// use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
// use LINE\LINEBot\TemplateActionBuilder\CameraRollTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\CameraTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\LocationTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
// use LINE\LINEBot\Event\MessageEvent\TextMessage;
// use LINE\LINEBot\KitchenSink\EventHandler;
// use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Flex\FlexSampleRestaurant;
// use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Flex\FlexSampleShopping;
// use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\UrlBuilder;
// use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
// use LINE\LINEBot\MessageBuilder\Imagemap\VideoBuilder;
// use LINE\LINEBot\MessageBuilder\Imagemap\ExternalLinkBuilder;
// use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;

// set false for production
$pass_signature = true;

// =========== Start Notes ==============
// $userId adalah user id adders
// =========== End Notes ==============

// set LINE channel_access_token and channel_secret
$channel_access_token = "iRnmNNB7rAYWzh9WINOTi6XGITTzi2vygcHzzYUuyOGroTIGbZFZJVxUqyj8fB3mFj3qReRF10aZ5QijRddPAgVuBswM6l24c6rHVOdtjBJgAelEU6k8F1SvrrDoZDZa+jQqfBhxuoaVFthQ3qkBVwdB04t89/1O/w1cDnyilFU=";
$channel_secret = "6a27b7b5008443994b2efe0a06d800f2";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});

// buat route untuk webhook
// $app->post('/webhook', function (Request $request, Response $response) use ($bot, $httpClient)
// $app->post('/webhook', function (Request $request, Response $response) use ($bot, $pass_signature, $httpClient)
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);

    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    // kode aplikasi nanti disini
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {

                  $userMessage = $event['message']['text'];
                  $replyToken = $event['replyToken'];

                  if(
                     $event['source']['type'] == 'group' or
                     $event['source']['type'] == 'room'
                   ){
                    //message from group / room
                    if($event['source']['userId']){
                        $userId     = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile    = $getprofile->getJSONDecodedBody();
                        $greetings  = new TextMessageBuilder("Halo, ".$profile['displayName']);
                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                    } else {
                        // send same message as reply to user
                        $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                    }
                   } else {
                    //message from single user
                    if ($userMessage == 'rph') {
                      $result = $bot->replyText($event['replyToken'], 'Rizqi Prima Hariadhy');
                    }elseif ($userMessage == 'gambar') {
                      $imageMessageBuilder = new ImageMessageBuilder('https://instagram.fcgk9-2.fna.fbcdn.net/vp/8187b18fa20e78456949aaea762a3376/5D6D8F06/t51.2885-15/e35/58626673_2101538916804733_3643098245392813868_n.jpg?_nc_ht=instagram.fcgk9-2.fna.fbcdn.net&_nc_cat=101', 'https://instagram.fcgk9-2.fna.fbcdn.net/vp/8187b18fa20e78456949aaea762a3376/5D6D8F06/t51.2885-15/e35/58626673_2101538916804733_3643098245392813868_n.jpg?_nc_ht=instagram.fcgk9-2.fna.fbcdn.net&_nc_cat=101');
                      $bot->replyMessage($replyToken, $imageMessageBuilder);
                    }elseif ($userMessage == 'video') {
                      $videoMessageBuilder = new VideoMessageBuilder('https://scontent.cdninstagram.com/vp/928d55088d6d438bd1a11eae1bc33d4d/5CE36567/t50.2886-16/15828351_1801394970078318_4807595776198836224_n.mp4?_nc_ht=scontent.cdninstagram.com', 'https://scontent.cdninstagram.com/vp/928d55088d6d438bd1a11eae1bc33d4d/5CE36567/t50.2886-16/15828351_1801394970078318_4807595776198836224_n.mp4?_nc_ht=scontent.cdninstagram.com');
                      $bot->replyMessage($replyToken, $videoMessageBuilder);
                    }elseif ($userMessage == 'audio') {
                      $audioMessageBuilder = new AudioMessageBuilder('http://indo.kumandang.com/voice/09295541cade2e0eaa355818bd721aa6.mp3', 'http://indo.kumandang.com/voice/09295541cade2e0eaa355818bd721aa6.mp3');
                      $bot->replyMessage($replyToken, $audioMessageBuilder);
                    }elseif ($userMessage == 'stiker') {
                      $packageId = 1;
                      $stickerId = 3;
                      $stickerMessageBuilder = new StickerMessageBuilder($packageId, $stickerId);
                      $bot->replyMessage($replyToken, $stickerMessageBuilder);
                    }elseif ($userMessage == 'multi') {
                      $textMessageBuilder1 = new TextMessageBuilder('ini pesan balasan pertama');
                      $textMessageBuilder2 = new TextMessageBuilder('ini pesan balasan kedua');
                      $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
                      $multiMessageBuilder = new MultiMessageBuilder();
                      $multiMessageBuilder->add($textMessageBuilder1);
                      $multiMessageBuilder->add($textMessageBuilder2);
                      $multiMessageBuilder->add($stickerMessageBuilder);
                      $bot->replyMessage($replyToken, $multiMessageBuilder);
                    }
                    elseif ($userMessage == 'confirms') {
                      $replyMessageBot = new TemplateMessageBuilder(
                        'Confirm alt text',
                        new ConfirmTemplateBuilder('Do it?', [
                            new MessageTemplateActionBuilder('Yes', 'Yes!'),
                            new MessageTemplateActionBuilder('No', 'No!'),
                        ])
                    );
                    }
                    elseif ( strpos($userMessage, 'start') !== false) {
                      session_start();
                      // $result = $bot->replyText($event['replyToken'], 'Session Started');
                      $nama = str_replace("start"," ",$userMessage);
                      $_SESSION['nama'] = $nama;
                      $textMessageBuilder1 = new TextMessageBuilder('Session Started');
                      $textMessageBuilder2 = new TextMessageBuilder('Halo,'.$nama);
                      $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
                      $multiMessageBuilder = new MultiMessageBuilder();
                      $multiMessageBuilder->add($textMessageBuilder1);
                      $multiMessageBuilder->add($textMessageBuilder2);
                      $multiMessageBuilder->add($stickerMessageBuilder);
                      $bot->replyMessage($replyToken, $multiMessageBuilder);
                    }
                    elseif ($userMessage == 'nama') {
                      $result = $bot->replyText($event['replyToken'], "Halo,".$_SESSION['nama']);
                    }
                    elseif ($userMessage == 'clear') {
                      session_destroy();
                      $result = $bot->replyText($event['replyToken'],'Session Destroy');
                    }elseif ($userMessage == 'flex') {
                      $result = $bot->replyText($event['replyToken'],'Harusnya Flex');
                      $flexTemplate = file_get_contents("flex_message.json"); // template flex message
                           $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                               'replyToken' => $event['replyToken'],
                               'messages'   => [
                                   [
                                       'type'     => 'flex',
                                       'altText'  => 'Test Flex Message',
                                       'contents' => json_decode($flexTemplate)
                                   ]
                               ],
                           ]);
                    }
                    else {
                      $result = $bot->replyText($event['replyToken'], 'Orang Lain');
                    }
                   }

                    // send same message as reply to user
                    // $result = $bot->replyText($event['replyToken'], $event['message']['text'].'ini pesan balasan');
                    // $bot->replyText($replyToken, 'ini pesan balasan');
                    // edit balasan sesuai dengan masukkan




                    // atau
                    // $textMessageBuilder = new TextMessageBuilder('ini pesan balasan');
                    // $bot->replyMessage($replyToken, $textMessageBuilder);

                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);

                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());

                }


                if(
                    $event['message']['type'] == 'image' or
                    $event['message']['type'] == 'video' or
                    $event['message']['type'] == 'audio' or
                    $event['message']['type'] == 'file'
                ){
                    $basePath  = $request->getUri()->getBaseUrl();
                    $contentURL  = $basePath."/content/".$event['message']['id'];
                    $contentType = ucfirst($event['message']['type']);
                    $result = $bot->replyText($event['replyToken'],
                        $contentType. " yang Anda kirim bisa diakses dari link:\n " . $contentURL);
                    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }
            }
        }
    }

    return $response->withStatus(400, 'No event sent!');

});

// untuk mengakses content
$app->get('/content/{messageId}', function($req, $res) use ($bot)
{
    // get message content
    $route      = $req->getAttribute('route');
    $messageId = $route->getArgument('messageId');
    $result = $bot->getMessageContent($messageId);

    // set response
    $res->write($result->getRawBody());

    return $res->withHeader('Content-Type', $result->getHeader('Content-Type'));
});

// Push Message merupakan pesan broadcast yang akan diberikan kepada seluruh adders
$app->get('/pushmessage', function($req, $res) use ($bot)
{
    // send push message to user
    $userId = 'U4b0df6d7b2c16439c816a7e4e1320950';
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    $result = $bot->pushMessage($userId, $textMessageBuilder);
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});

// Push Message bisa Diedit
$app->get('/pushmessage/{message}', function($req, $res) use ($bot)
{
    // send push message to user
    $userId = 'U4b0df6d7b2c16439c816a7e4e1320950';
    $route  = $req->getAttribute('route');
    $thePushMessage = $route->getArgument('message');
    $textMessageBuilder = new TextMessageBuilder($thePushMessage);
    $result = $bot->pushMessage($userId, $textMessageBuilder);
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});

// mengambil user Id
// $bot->getProfile(userId);

$app->get('/profile', function($req, $res) use ($bot)
{
    // get user profile
    $userId = 'U4b0df6d7b2c16439c816a7e4e1320950';
    $result = $bot->getProfile($userId);

    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});

$app->get('/profile/{userId}', function($req, $res) use ($bot)
{
    // get user profile
    $route  = $req->getAttribute('route');
    $userId = $route->getArgument('userId');
    $result = $bot->getProfile($userId);

    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});

$app->run();
