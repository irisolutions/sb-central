<?php
/**
 * Created by PhpStorm.
 * User: Khaled
 * Date: 2/18/2018
 * Time: 2:55 PM
 */

namespace Iris\DashboardBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PushNotificationsController extends Controller {
    // (Android)API access key from Google API's Console.
    //new api key
    private static $API_ACCESS_KEY = 'AAAAChyhmPI:APA91bHP89zd5cq-0E2Nhti0Ua9kKibkskRqqky9Vg-KG71pGLtA_-Yk76LJ1QCVd-Bmp5krcxJ10Hrtbx13-392No4n5jcsZlgg6UwilGlUFOWQz8DBjHol0c_su5R-xcG2y-Y3Z81p';
    // (iOS) Private key's passphrase.
    private static $passphrase = 'joashp';
    // (Windows Phone 8) The name of our push channel.
    private static $channelName = "joashp";

    // Change the above three vriables as per your app.
    public function __construct() {
//        exit('Init function is not allowed');
    }

    // Sends Push notification for Android users
    public function android($data, $reg_id) {
        $url = 'https://android.googleapis.com/gcm/send';
        $message = array(
            'title' => $data['mtitle'],
            'message' => $data['mdesc'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1
        );

        $headers = array(
            'Authorization: key=' .self::$API_ACCESS_KEY,
            'Content-Type: application/json'
        );

//        $fields = array(
//            'registration_ids' => array($reg_id),
//            'data' => $message,
//        );

//        to send to controller by topic
        $topic = 'global';
        $fields = array(
            'to' => '/topics/' . $topic,
            'data' => $message,
        );


//       Send to controller by topic by token
//        $firebase_token ='dOX4qHew9Po:APA91bHQE41QgTvJZFtaS71YlhGVBRLAzxUmba7BmGBPItXi-NlC65Z37EvWX0TGfbnROWecURseTQE5yCieQ_qitsYZaP4vGLiHTsyVcZJisqO0N4tzHELgNAe9HFsUbRK_F7xNV4TC';
//        $fields = array(
//            'to' => $firebase_token,
//            'data' => $message,
//        );
        return $this->useCurl($url, $headers, json_encode($fields));
    }

    // Sends Push's toast notification for Windows Phone 8 users
    public function WP($data, $uri) {
        $delay = 2;
        $msg =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<wp:Notification xmlns:wp=\"WPNotification\">" .
            "<wp:Toast>" .
            "<wp:Text1>".htmlspecialchars($data['mtitle'])."</wp:Text1>" .
            "<wp:Text2>".htmlspecialchars($data['mdesc'])."</wp:Text2>" .
            "</wp:Toast>" .
            "</wp:Notification>";

        $sendedheaders =  array(
            'Content-Type: text/xml',
            'Accept: application/*',
            'X-WindowsPhone-Target: toast',
            "X-NotificationClass: $delay"
        );

        $response = $this->useCurl($uri, $sendedheaders, $msg);

        $result = array();
        foreach(explode("\n", $response) as $line) {
            $tab = explode(":", $line, 2);
            if (count($tab) == 2)
                $result[$tab[0]] = trim($tab[1]);
        }

        return $result;
    }

    // Sends Push notification for iOS users
    public function iOS($data, $devicetoken) {
        $deviceToken = $devicetoken;
        $ctx = stream_context_create();
        // ck.pem is your certificate file
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);
        // Open a connection to the APNS server
        $fp = stream_socket_client(
            'ssl://gateway.sandbox.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);
        // Create the payload body
        $body['aps'] = array(
            'alert' => array(
                'title' => $data['mtitle'],
                'body' => $data['mdesc'],
            ),
            'sound' => 'default'
        );
        // Encode the payload as JSON
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        // Close the connection to the server
        fclose($fp);
        if (!$result)
            return 'Message not delivered' . PHP_EOL;
        else
            return 'Message successfully delivered' . PHP_EOL;
    }

    public function pushNotificationAction($slug)
    {
//        $data = $slug;
        $data = array(
            'mtitle' => "title",
            'mdesc' => "body",
            'mdesc' => $slug
        );
        $reg_id = "43430025458";
        echo "call android function";
        $this->android($data, $reg_id);

        return 'Message successfully delivered' . PHP_EOL;
    }

    // Curl
//    private function useCurl(&$model, $url, $headers, $fields = null) {
    private function useCurl($url, $headers, $fields = null) {
        // Open connection
        $ch = curl_init();
        if ($url) {
            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($fields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            }

            // Execute post
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }

            // Close connection
            curl_close($ch);

            return $result;
        }
    }

}
