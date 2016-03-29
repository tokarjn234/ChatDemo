<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Demo_mchat extends CI_Model {
     
    function __construct() {
        parent::__construct();
        $this->load->database();
        if (!isset($_SESSION['chatHistory'])) {
            $_SESSION['chatHistory'] = array(); 
        }

        if (!isset($_SESSION['openChatBoxes'])) {
            $_SESSION['openChatBoxes'] = array();   
        }
    }

    /**
     * Lấy thông tin của user
     */
    public function getUsers( $conditions = array() , $fields = '' ) {
        
        if(count($conditions)>0) {
            $this->db->order_by('id', 'desc');
            $this->db->where($conditions);
            $this->db->from('user');
        }
        
        if($fields!='') {
            $this->db->select($fields);
        }else {
            $this->db->select('id , username, status');
            $this->db->from('user');
        }
        
        $result = $this->db->get();
        
        return $result;

    }

    /**
     * Trung tâm xử lý dữ liệu chat
     */
    public function chatHeartbeat() {
        // Lấy ra lịch sử tin nhắn đến ( chưa đọc ) của user
        $sql = "  SELECT  `user`.`username`, 
                            `conversation`.`time` , 
                            `conversation`.`content` 
                    FROM    `conversation` 
                    LEFT JOIN `user` 
                    ON        `user`.`id` = `conversation`.`id_user_from` 
                    WHERE     `conversation`.`id_to` = '".$_SESSION['id']."' 
                    AND       `conversation`.`type`  = '0' 
                    ORDER BY  `conversation`.`id` ASC 
                ;";
        $query = mysql_query($sql);
        return $query;

    } // end function

    /**
     * Thực hiện update trạng thái từ 'chưa đọc' -> 'đã đọc' tin nhắn
     * '0' : chưa đọc ; '1' : đã đọc
     */
    public function updateConversation(){
        $condition = array( 
            'id_to' => $_SESSION['id'] , 
            'type' => 0 
            );
        $data = array( 'type' => 1 );
        $this->db->update('conversation', $data, $condition);
    }

    // Lưu dữ liệu chat vào database
    public function insertConversation(){
        
        $this->load->model('demo_mchat');
        $to = $this->demo_mchat->getUsers( array('username' => mysql_real_escape_string($_POST['to'])) , 'id');
        $to = $to->row_array();

        $data = array(
            'id_user_from' => $_SESSION['id'] ,
            'id_to' => $to['id'] ,
            'content' => mysql_real_escape_string(trim($_POST['message'])) 
            );
        $this->db->insert('conversation',$data);
    }


    /**
     * Thay đổi trạng thái user thành online
     * @param int $status
     */
    public function changeStatus(){
        
        if ( isset($_SESSION['id']) 
            && !empty($_SESSION['id']) 
            && isset($_SESSION['username']) 
            && !empty($_SESSION['username']) ) {
            $condition = array( 
                'id' => $_SESSION['id'] , 
                'username' => $_SESSION['username'] 
                );
            $data = array( 'status' => 1 ); // online
        }
        
        $this->db->update('user', $data, $condition);
    }

    /**
     * Thay đổi trạng thái user thành offline
     * Sau đó mới unset cookie và destroy session
     */
    public function logout() {
        $condition = array( 
                'id' => $_SESSION['id'] , 
                'username' => $_SESSION['username'] 
                );
        $data = array( 'status' => 0 ); // offline
        $this->db->update('user', $data, $condition);
    }

}