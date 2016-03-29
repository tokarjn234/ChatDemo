<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		session_start();
	}

	public function index()	{
		$this->load->view('user');

		if ( isset($_POST['username']) && isset($_POST['password']) ) {
			$user = $_POST['username'];
			$pass = $_POST['password'];
			$this->load->model('muser');
			$login = $this->muser->login($user,$pass);
			if ($login == true) {
				$_SESSION['username'] = $user;
				$_SESSION['password'] = $pass;
				redirect('demo_chat');
			}else{
				die('Tên đăng nhập hoặc mật khẩu không đúng. Quay lại và thử nhập lại !');
			}
		}

	}

	public function logout(){
		$this->load->model('demo_mchat');
        $this->demo_mchat->logout();
		session_destroy();
		redirect('user');
	}

}
//vi yeu em

/* End of file user.php */
/* Location: ./application/controllers/user.php */