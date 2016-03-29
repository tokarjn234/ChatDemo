<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Demo_chat extends CI_Controller {

    public function __construct() {
        parent::__construct();
        session_start();
        $this->load->helper('url');
    }
    
    public function index() {
        
        $this->load->model('demo_mchat');

        // Vô database múc cái user ID của cái thằng vừa đăng nhập rồi lưu vào session
        $condition = array(
            'username' => mysql_real_escape_string($_SESSION['username']) ,
            'password' => mysql_real_escape_string($_SESSION['password']) 
            );
        
        $userID = $this->demo_mchat->getUsers($condition , 'id');
        $userID = $userID->row_array();
        $_SESSION['id'] = $userID['id'];
        
        // Nếu ko có User ID tức là chưa login, redirect về trang login
        // Nếu đã login thành công thì change trạng thái thành online
        if(!$userID) {
            redirect('user');
        }else{
            $this->demo_mchat->changeStatus();
        }

        // Lấy ra danh sách tất cả các user
        $outputData['listOfUsers'] = $this->demo_mchat->getUsers();
        $outputData['listOfUsers'] = $outputData['listOfUsers']->result_array();
        $this->load->view('chat/demo_chat',$outputData);
    }

    // Được gọi tới trong demo_chat.js
    public function successChat(){

        if ($_GET['action'] == "chatheartbeat") { $this->chatHeartbeat(); } 
        if ($_GET['action'] == "sendchat") { $this->sendChat(); } 
        if ($_GET['action'] == "closechat") { $this->closeChat(); } 
        if ($_GET['action'] == "startchatsession") { $this->startChatSession(); } 

    }

    // Request từ ajax gửi đến function này sẽ bị treo cho đến khi function này thực hiện xong
    public function chatHeartbeat(){
        
        $this->load->model('demo_mchat');
        $query = $this->demo_mchat->chatHeartbeat();

        $items = '';

        $chatBoxes = array();
        
        if ( !empty($query) ) {
            while ( $chat = mysql_fetch_array($query) ) {
                if (!isset($_SESSION['openChatBoxes'][$chat['username']]) && isset($_SESSION['chatHistory'][$chat['username']])) {
                    $items = $_SESSION['chatHistory'][$chat['username']];
                }
                
                $chat['content'] = $this->sanitize($chat['content']);

                $items .= <<<EOD
                    {
                        "s": "0",
                        "f": "{$chat['username']}",
                        "m": "{$chat['content']}"
                    },
EOD;

                if (!isset($_SESSION['chatHistory'][$chat['username']])) {
                    $_SESSION['chatHistory'][$chat['username']] = '';
                }

                $_SESSION['chatHistory'][$chat['username']] .= <<<EOD
                    {
                        "s": "0",
                        "f": "{$chat['username']}",
                        "m": "{$chat['content']}"
                    },
EOD;
                unset($_SESSION['tsChatBoxes'][$chat['username']]);
                $_SESSION['openChatBoxes'][$chat['username']] = $chat['time'];
            } // end while line 69

            if (!empty($_SESSION['openChatBoxes'])) {
                foreach ($_SESSION['openChatBoxes'] as $chatbox => $time) {
                    if (!isset($_SESSION['tsChatBoxes'][$chatbox])) {
                        $now = time()-strtotime($time);
                        $time = date('g:iA M dS', strtotime($time));

                        $message = "Gửi lúc $time";
                        if ($now > 180) {
                            $items .= <<<EOD
                                {
                                    "s": "2",
                                    "f": "$chatbox",
                                    "m": "{$message}"
                                },
EOD;

                            if (!isset($_SESSION['chatHistory'][$chatbox])) {
                                $_SESSION['chatHistory'][$chatbox] = '';
                            }

                            $_SESSION['chatHistory'][$chatbox] .= <<<EOD
                                {
                                    "s": "2",
                                    "f": "$chatbox",
                                    "m": "{$message}"
                                },
EOD;
                            $_SESSION['tsChatBoxes'][$chatbox] = 1;

                        } 
                    } 
                } 
            } 

        }
        
        // Thực hiện update trạng thái từ 'chưa đọc' -> 'đã đọc' tin nhắn
        $this->demo_mchat->updateConversation();

        if ( $items != '' ) {
            $items = substr($items, 0, -1);
        }
        
        header('Content-type: application/json');
        ?>
        {
            "items": [ 
                <?php echo $items;?> 
            ]
        }
        <?php

        exit(0);
    }

    public function chatBoxSession($chatbox) {
        
        $items = '';
        
        if (isset($_SESSION['chatHistory'][$chatbox])) {
            $items = $_SESSION['chatHistory'][$chatbox];
        }

        return $items;
    }

    public function startChatSession() {
        
        $items = '';

        if (!empty($_SESSION['openChatBoxes'])) {
            foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
                $items .= $this->chatBoxSession($chatbox);
            }
        }

        if ($items != '') {
            $items = substr($items, 0, -1);
        }

        header('Content-type: application/json');
        ?>
        {
            "username": "<?php echo $_SESSION['username'];?>",
            "items": [
                <?php echo $items;?>
            ]
        }
        <?php

        exit(0);
    }

    public function sendChat() {
        // $from = $_SESSION['username']; // username người gửi
        // $to = $_POST['to']; // username của người nhận
        // $message = $_POST['message'];

        $_SESSION['openChatBoxes'][$_POST['to']] = date('Y-m-d H:i:s', time());
        
        $messagesan = $this->sanitize($_POST['message']);

        if (!isset($_SESSION['chatHistory'][$_POST['to']])) {
            $_SESSION['chatHistory'][$_POST['to']] = '';
        }

        $_SESSION['chatHistory'][$_POST['to']] .= <<<EOD
            {
                "s": "1",
                "f": "{$_POST['to']}",
                "m": "{$messagesan}"
            },
EOD;

        unset($_SESSION['tsChatBoxes'][$_POST['to']]);

        $this->load->model('demo_mchat');
        $this->demo_mchat->insertConversation();
        
        echo "1";
        exit(0);
    }

    public function closeChat() {

        unset($_SESSION['openChatBoxes'][$_POST['chatbox']]);
        
        echo "1";
        exit(0);
    }

    // Filter chuỗi
    public function sanitize($text) {
        $text = htmlspecialchars($text, ENT_QUOTES);
        $text = str_replace("\n\r","\n",$text);
        $text = str_replace("\r\n","\n",$text);
        $text = str_replace("\n","<br>",$text);
        return $text;
    }

}