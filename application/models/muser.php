<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Muser extends CI_Model {

	public function __construct() {
		parent::__construct();
		$this->load->database();
	}

	/**
	 * @param str $user
	 * @param str $pass
	 */
	public function login($user, $pass){
        $condition = array('username' => $user, 'password' => $pass);
        $this->db->where($condition);
        $get = $this->db->get('user');
        $result = $get->num_rows();
        if ($result == 1){
            return true;
        }else{
            return false;
        }
	}

}

/* End of file muser.php */
/* Location: ./application/models/muser.php */