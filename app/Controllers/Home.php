<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserlogModel;
use App\Models\UserModel;
use App\Models\EmailModel;

use \App\Entities\Userlog;

class Home extends BaseController
{
  protected $is_change_pass = false;

	public function index() {
		return redirect()->to(base_url('login'));
	}

	public function login()
	{
		if ($this->session->has('logged_user') && $_SESSION['logged_user']['emailVerified']) {
			return redirect()->to(base_url('dashboard'));
		}

		$data['validation'] = null;
    $data['error'] = null;
		$css = ['custom/login/login.css'];
		$data['css'] = addExternal($css, 'css');

		if($this->request->getMethod() == 'post')
		{
			$rules = [
				'email' => 'required|valid_email',
				'password' => 'required|validateUser[email, password]'
			];

			$errors = [
				'password' => [
					'validateUser' => "Email and Password don't match",
				],
			];

			if($this->validate($rules, $errors)){

				$model = new UserModel();
				$user = $model->asArray()->where('email', $this->request->getVar('email'))->first();

				if($user['is_active'] != 1 || $user['is_deleted'] != 0) {
					$data['error'] = 'The account your trying to access is either inactive or deleted. <br> Please contact your school clerk if you wish to reactivate it.';
					return view('user_mgt/login', $data);

				} else {
					$userToken = $this->updateUserlog($user['id']);
					$this->setSession($user, $userToken);

					// To turn this off, fetch the data from database that represents the toggle for two step verification. Simply put an if statement and when 2f verification is turned off, make sure to set $_SESSION['logged_user']['emailVerified'] to true automatically. Also unset $_SESSION loginDate and $_SESSION userToken
          if($_SESSION['logged_user']['emailVerified']) {
            return redirect()->to(base_url('dashboard'));
          } elseif(!$this->checkPasswordLastUpdate()) {
					  // $this->sendVerification();

            // To be changed for a page that notifies the email verification was sent
					  return redirect()->to(base_url('verifyAccount'));
            // return redirect()->to(base_url('dashboard'));

          } else {
            $_SESSION['logged_user']['emailVerified'] = true;
            unset($_SESSION['logged_user']['userToken'], $_SESSION['logged_user']['loginDate']);
            return redirect()->to(base_url('dashboard'));
          }
				}
			} else {
				$data['validation'] = $this->validator;
			}
		}

		return view('user_mgt/login', $data);
	}

  protected function checkPasswordLastUpdate()
  {
    $student = new \App\Entities\Student();
    $model = new UserModel();

    $student = $model->where('email', $_SESSION['logged_user']['email'])->first();
    // Check if last password update was less than or equal to 30 minutes
    return ((strtotime(date('Y-m-d H:i:s')) - strtotime($student->updated_on)) <= 1800);
  }

  public function forgot_password()
  {

    $data = [];
		$data['validation'] = null;
    $data['validate_error'] = null;
    $data['success'] = null;

    $css = ['custom/login/login.css'];
    $data['css'] = addExternal($css, 'css');

    if($this->request->getMethod() == 'post')
    {

      $rules = ['email_fpass' => 'required|valid_email|is_UP_mail'];
      $errors = [
				'email_fpass' => [
          'is_UP_mail'  => 'The email you entered is an invalid UP mail',
          'valid_email' => 'You have entered an invalid email'
        ]
      ];

      if($this->validate($rules, $errors)) {
        $email = $this->request->getVar('email_fpass', FILTER_SANITIZE_EMAIL);
        $model = new UserModel();
				$user = $model->asArray()->where('email', $email)->first();

        if(!empty($user)) {
          $userToken = $this->updateUserlog($user['id']);

					$this->setSession($user, $userToken);
          // $data['userToken'] = $userToken; //for testing purposes

					$this->resetPasswordEmail();

        } else {
          $data['validate_error'] = 'Email does not exist.';
          return view('user_mgt/forgot_password', $data);
        }
      } else {
        $data['validation'] = $this->validator;
      }
    }
    return view('user_mgt/forgot_password', $data);
  }

  public function change_password()
  {

    $data = [];
    $data['error'] = null;
    $data['is_changed'] = false;
    $data['validation'] = null;

    if(!$this->session->has('logged_user')) {
      $data['error'] = 'You need to login to change your password. </br> Otherwise, request to reset your password instead.';
      return view('user_mgt/change_password', $data);
    } else {
      $data['is_changed'] = true;
      if($this->request->getMethod() == 'post') {

        $rules = [
          'new_pass' => [
            'label' => 'New Password',
            'rules' => 'required|min_length[8]|max_length[16]|regex_match[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$]'
          ],
          'confirm_pass' => [
            'label' => 'Confirm Password',
            'rules' => 'required|matches[new_pass]'
          ]
        ];

        $errors = [
          'new_pass' => [
            'regex_match' => 'Weak password! <br>Password must have at least one digit, lowercase and uppercase letter and a special character.'
          ]
        ];

        if($this->validate($rules, $errors)) {

            $old_password = $this->request->getVar('old_pass', FILTER_SANITIZE_EMAIL); //get old password

            $password = password_hash($this->request->getVar('new_pass', FILTER_SANITIZE_EMAIL), PASSWORD_BCRYPT); //get new password
            $datum = ['password' => $password];

            $model = new UserModel();
            $user = $model->asArray()->where('email', $_SESSION['logged_user']['email'])->first();

            if(password_verify($old_password, $user['password'])) {
              $model->asArray()->where('email', $_SESSION['logged_user']['email'])->set($datum)->update();
              $this->changePasswordEmail(); //let the user know that his/her password has been changed

              return redirect()->to('dashboard');
            } else {
              $data['error'] = "Old Password incorrect. Please review your input.";
              return view('user_mgt/change_password', $data);
            }

        } else {
            $data['validation'] = $this->validator;
            return view('user_mgt/change_password', $data);
        }
      }
      return view('user_mgt/change_password', $data);
    }
  }


  public function reset_password($userToken = null)
  {
    $data = [];
    $data['error'] = null;
    $data['validation'] = null;

    if(!empty($userToken)) {
      $timeElapsed = strtotime(date('Y-m-d H:i:s')) - strtotime($_SESSION['logged_user']['loginDate']); //in seconds
    }

    if(empty($userToken)) {
      $data['error'] = 'Unauthorized access.'; //when trying to manually access the forgot_password page

    } elseif($userToken === $_SESSION['logged_user']['userToken']) {

      if($timeElapsed <= 1800) {
        // $_SESSION['logged_user']['passwordReset'] = true;

        if($this->request->getMethod() == 'post') {

          $rules = [
            'new_pass' => [
              'label' => 'New Password',
              'rules' => 'required|min_length[8]|max_length[16]|regex_match[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$]'
            ],
            'confirm_pass' => [
              'label' => 'Confirm Password',
              'rules' => 'required|matches[new_pass]'
            ]
          ];

          $errors = [
            'new_pass' => [
              'regex_match' => 'Weak password! <br>Password must have at least one digit, lowercase and uppercase letter and a special character.'
            ]
          ];


          if($this->validate($rules, $errors)) {

            $password = password_hash($this->request->getVar('new_pass', FILTER_SANITIZE_EMAIL), PASSWORD_BCRYPT);
            $datum = ['password' => $password];

            $model = new UserModel();
            $model->asArray()->where('email', $_SESSION['logged_user']['email'])->set($datum)->update();

            $_SESSION['logged_user']['passwordReset'] = true;

            unset($_SESSION['logged_user']['userToken'], $_SESSION['logged_user']['loginDate']);

              //destroy session after password is updated
            return redirect()->to(base_url('dashboard/logout'));
          } else {

            $data['validation'] = $this->validator;
            return view('user_mgt/reset_password', $data);
          }
        }
      } else {
        $data['error'] = 'Sorry. Reset password link has expired.';
      }
    } else {
      $data['error'] = 'You are not authorized to access this page.'; //When incorrect usertoken
    }

    return view('user_mgt/reset_password', $data);
  }

	public function verification($userToken)
	{
		// In seconds
		$timeDifference = strtotime(date('Y-m-d H:i:s')) - strtotime($_SESSION['logged_user']['loginDate']);

		if($_SESSION['logged_user']['emailVerified']) {
			return redirect()->to(base_url('dashboard'));
		} elseif($userToken === $_SESSION['logged_user']['userToken']) {

			if($timeDifference <= 1800) {
				$_SESSION['logged_user']['emailVerified'] = true;
				unset($_SESSION['logged_user']['userToken'], $_SESSION['logged_user']['loginDate']);

				return redirect()->to(base_url('dashboard'));
			} else {
				// Redirect to a page that notifies the link has expired
				echo 'Sorry. Verification link has expired';
        return redirect()->to(base_url('dashboard/logout'));
			}
		}
  }

	public function verifyAccount()
	{
		return view('verification/verification');
	}

	protected function setSession($user, $userToken)
	{
		$session_data = [
			'name'			=> $user['first_name'],
			'email'			=> $user['email'],
			'password' 		=> $user['password'],
			'role'			=> $user['role'],
      'isLoggedIn' 	=> true,
      'passwordReset' => false,
      // 'emailVerified' => false,
      'emailVerified' => true,
			'userToken'		=> $userToken,
			'loginDate'		=> date('Y-m-d H:i:s')
		];

		$this->session->set('logged_user', $session_data);
		return true;
	}

	protected function updateUserlog($user_id)
	{
		$userlog = new Userlog($this->request);
		$userlogModel = new UserlogModel();

		$userToken = $userlog->fillUserlogData($user_id);

		$userlogModel->insert($userlog);

		return $userToken;
	}

	protected function sendVerification()
	{
		// For sending 2f verification
    $emailModel = new EmailModel();

    $emailContent = $emailModel->where('is_deleted', '0')->where('purpose','verification')->orderBy('created_on', 'desc')->first();

    $search = ['-content-', '-student-', '-website_link-'];
    $subject = $emailContent['title'];

    $message = file_get_contents(base_url() . '/app/Views/verification.html');
		$replace = [$emailContent['message'], $_SESSION['logged_user']['name'], base_url().'/verification'.'/'.$_SESSION['logged_user']['userToken']];

		$message = str_replace($search, $replace, $message);
		$status = send_acc_notice($_SESSION['logged_user']['email'], $subject, $message);
		// For sending 2f verification
	}

  protected function resetPasswordEmail()
  {
    $emailModel = new EmailModel();

    $emailContent = $emailModel->where('is_deleted', '0')->where('purpose','forgot_pass')->orderBy('created_on', 'desc')->first();

    $search = ['-content-', '-student-', '-website_link-'];
    $subject = $emailContent['title'];

    $message = file_get_contents(base_url() . '/app/Views/verification.html');
		$replace = [$emailContent['message'], $_SESSION['logged_user']['name'], base_url().'/reset_password'.'/'.$_SESSION['logged_user']['userToken']];

		$message = str_replace($search, $replace, $message);
		$status = send_acc_notice($_SESSION['logged_user']['email'], $subject, $message);
  }

  protected function changePasswordEmail()
  {
    $emailModel = new EmailModel();

    $emailContent = $emailModel->where('is_deleted', '0')->where('purpose','change_pass')->orderBy('created_on', 'desc')->first();

    //alert user that his/her account's password changed if you did not do it blahblah --
    $search = ['-content-', '-student-', '-website_link-'];
    $subject = $emailContent['title'];

    $message = file_get_contents(base_url() . '/app/Views/verification.html');
		$replace = [$emailContent['message'], $_SESSION['logged_user']['name'], base_url()]; //redirect to login page

		$message = str_replace($search, $replace, $message);
		$status = send_acc_notice($_SESSION['logged_user']['email'], $subject, $message);
  }
}
