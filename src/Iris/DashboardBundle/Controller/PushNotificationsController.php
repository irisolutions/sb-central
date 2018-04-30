<?php
/**
 * Created by PhpStorm.
 * User: Khaled
 * Date: 2/18/2018
 * Time: 2:55 PM
 */

namespace Iris\DashboardBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use PDO;

class PushNotificationsController extends Controller
{
    // (Android)API access key from Google API's Console.
    //new api key
    private static $TABLET_API_ACCESS_KEY = 'AAAAMwr0Syo:APA91bHVHHBnH-S6GYbII46ux6rrD5Osy6AF3My_XN2OYqe3BO6PrHctFTThQX4-R8satvVdp_ZANn9n9Jr8eQYOp_dMM3_pU7KWP9OJ9Ai7BfemyD5u5PcDjonK-pIDtiUQb-RUxKlh';
    private static $DONGLE_API_ACCESS_KEY = 'AAAA45uo5ZE:APA91bHmS3bb3baBixg0-GIWYBsfQhSPCLP7G-bO4T4QUrMiwuVRmePQNpqUSgzzu7Y_jahZvPO4CVOrFYdPW5WztX96FLs42n_aVydZO6RzeyqZtJNFInDGj-l_bRAOoEWxovWIDfmq';
    // (iOS) Private key's passphrase.
    private static $passphrase = 'joashp';
    // (Windows Phone 8) The name of our push channel.
    private static $channelName = "joashp";

    // Change the above three vriables as per your app.
    public function __construct()
    {
//        exit('Init function is not allowed');
    }

    // Sends Push notification for Android users
    public function android($data, $reg_id)
    {
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
            'Authorization: key=' . self::$API_ACCESS_KEY,
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
    public function WP($data, $uri)
    {
        $delay = 2;
        $msg = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<wp:Notification xmlns:wp=\"WPNotification\">" .
            "<wp:Toast>" .
            "<wp:Text1>" . htmlspecialchars($data['mtitle']) . "</wp:Text1>" .
            "<wp:Text2>" . htmlspecialchars($data['mdesc']) . "</wp:Text2>" .
            "</wp:Toast>" .
            "</wp:Notification>";

        $sendedheaders = array(
            'Content-Type: text/xml',
            'Accept: application/*',
            'X-WindowsPhone-Target: toast',
            "X-NotificationClass: $delay"
        );

        $response = $this->useCurl($uri, $sendedheaders, $msg);

        $result = array();
        foreach (explode("\n", $response) as $line) {
            $tab = explode(":", $line, 2);
            if (count($tab) == 2)
                $result[$tab[0]] = trim($tab[1]);
        }

        return $result;
    }

    // Sends Push notification for iOS users
    public function iOS($data, $devicetoken)
    {
        $deviceToken = $devicetoken;
        $ctx = stream_context_create();
        // ck.pem is your certificate file
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);
        // Open a connection to the APNS server
        $fp = stream_socket_client(
            'ssl://gateway.sandbox.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
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
    private function useCurl($url, $headers, $fields = null)
    {
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

//            return $result;
            return new JsonResponse(array('token' => $token, 'type' => $type,'results'=>$result));
//            return new JsonResponse($result);
        }
    }

    public function pushDownloadNotificationAction()
    {
        $request = $this->get('request');
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $appID = $request->request->get('AppID');
            $type = $request->request->get('Type');
            $userName = $request->request->get('UserName');
//            $token = $request->request->get('Token');

            $stmt1 = $conn->prepare('Select Token from Tokens where UID = ? AND Device = ?');
            try {
                $stmt1->execute([$userName, $type]);
            } catch (\PDOException $e) {
                $error = 'Operation Aborted ..' . $e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
            }
            $token = $stmt1->fetchAll()[0][0];

            $data = array(
                'AppID' => $appID,
                'Type' => $type
            );

            if ($type == "dongle") {
                return $this->sendToDongle($data, $token);
            } elseif ($type == "tablet") {
                return $this->sendToTablet($data, $token);
            } else {
                return new JsonResponse(array('token' => $token, 'type' => $type));
            }
        }
        return new JsonResponse(array('token' => "noToken", 'type' => "noType"));
    }

    // Sends Push notification for Android dongle users
    public function sendToDongle($data, $token)
    {
        $url = 'https://android.googleapis.com/gcm/send';
        $message = array(
            'title' => $data['AppID'],
            'message' => $data['Type'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1
        );

        $headers = array(
            'Authorization: key=' . self::$DONGLE_API_ACCESS_KEY,
            'Content-Type: application/json'
        );

//       Send to controller by topic by token
        $fields = array(
            'to' => $token,
            'data' => $message,
        );

        return $this->useCurl($url, $headers, json_encode($fields));
    }

    // Sends Push notification for Android dongle users
    public function sendToTablet($data, $token)
    {
        $url = 'https://android.googleapis.com/gcm/send';
        $message = array(
            'title' => $data['AppID'],
            'message' => $data['Type'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1
        );

        $headers = array(
            'Authorization: key=' . self::$TABLET_API_ACCESS_KEY,
            'Content-Type: application/json'
        );

//       Send to controller by topic by token
        $fields = array(
            'to' => $token,
            'data' => $message,
        );

        return $this->useCurl($url, $headers, json_encode($fields));
    }

}
