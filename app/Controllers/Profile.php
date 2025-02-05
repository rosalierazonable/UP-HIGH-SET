<?php
namespace App\Controllers;

use \App\Entities\Admin;
use \App\Entities\Student;
use \App\Models\UserModel;

class Profile extends BaseController
{
    protected $userModel;

    function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function student()
    {
        $role = $_SESSION['logged_user']['role'];

        $sessionStudent = new Student();

        $sessionStudent = $this->userModel->where('is_deleted', 0)->where('email', $_SESSION['logged_user']['email'])->first();

        $data = $this->setDefaultData($role, $sessionStudent->id);

        $css = ['custom/profileUpdate/pUpdate.css', 'custom/alert.css', 'custom/avatar.css'];
        $js = ['custom/profileUpdate/pUpdate.js', 'custom/alert.js', 'custom/avatar.js'];
        $data['js'] = addExternal($js, 'javascript');
        $data['css'] = addExternal($css, 'css');

        $data['validation'] = null;
        $data['status'] = null;
        $data['role'] = $role;
        // $data['id'] = $id;

        if($this->request->getMethod() == 'post') {
            if($this->validate($this->setRules($role, $sessionStudent->id))) {
                $values = [
                    'contact_num' => $this->request->getPost('mobile'),
                    'username'    => $this->request->getPost('username'),
                    'avatar_url'  => $this->request->getPost('avatar')
                ];
                $data['status'] = ($this->userModel->update($sessionStudent->id, $values)) ? true : false;
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view("account_updates/profileUpdate", $data);
    }

    public function admin()
    {
        $role = $_SESSION['logged_user']['role'];

        $sessionAdmin = new Admin();

        $sessionAdmin = $this->userModel->asObject('App\Entities\Admin')->where('is_deleted', 0)->where('role', $role)->where('email', $_SESSION['logged_user']['email'])->first();

        $data = $this->setDefaultData($role, $sessionAdmin->id);

        $css = ['custom/profileUpdate/pUpdate.css', 'custom/alert.css', 'custom/avatar.css'];
        $js = ['custom/profileUpdate/pUpdate.js', 'custom/alert.js', 'custom/avatar.js'];
        $data['js'] = addExternal($js, 'javascript');
        $data['css'] = addExternal($css, 'css');

        $data['validation'] = null;
        $data['status'] = null;
        $data['role'] = $role;
        // $data['id'] = $id;

        if($this->request->getMethod() == 'post') {
            if($this->validate($this->setRules($role, $sessionAdmin->id))) {
                $email = $this->request->getPost('email') . '@up.edu.ph';
                $values = [
                    'avatar_url'  => $this->request->getPost('avatar'),
                    'contact_num' => $this->request->getPost('mobile'),
                    'username'    => $this->request->getPost('username'),
                    'first_name'  => $this->request->getPost('first_name'),
                    'last_name'   => $this->request->getPost('last_name'),
                    'email'       => $email
                ];
                $data['status'] = ($this->userModel->update($sessionAdmin->id, $values)) ? true : false;
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view("account_updates/adminProfileUpdate", $data);
    }

    /**
     * AUXILIARY FUNCTIONS
     */

    protected function setDefaultData($role = null, $id = null) {
        if($role == '2') {
            $student = new Student();
            if(isset($id)) {
                $id = (int)$id;
                $student = $this->userModel->find($id);
            }
            $data['sNo'] = $student->student_num;
            $data['fName'] = $student->first_name;
            $data['lName'] = $student->last_name;
            $data['uName'] = $student->username;
            $data['cn'] = $student->contact_num;
            $data['glevel'] = $student->grade_level;
            $data['avatar_url'] = $student->avatar_url;
            $data['email'] = $student->email;
        } else {
            $adminUpdate = new Admin();
            if(isset($id)) {
                $id = (int)$id;
                $adminUpdate = $this->userModel->asObject('App\Entities\Admin')->find($id);
            }
            $data['fN'] = $adminUpdate->first_name;
            $data['lN'] = $adminUpdate->last_name;
            $data['uN'] = $adminUpdate->username;
            $data['cN'] = $adminUpdate->contact_num;
            $data['eml'] = $adminUpdate->email;
            $data['avatar_url'] = $adminUpdate->avatar_url;
        }

        return $data;
    }

    protected function setRules($role = null, $id = null) {
        if($role == '2') {
            $rules['username'] = [
                'rules'     => 'uniqueUsername['. $id .']',
                'errors'    => [
                    'uniqueUsername'  => 'Username already taken'
                ]
            ];
            $rules['mobile'] = [
                'rules'     => 'valid_number',
                'errors'    => [
                    'valid_number' => 'Contact number format: 09xxxxxxxxx'
                ]
            ];
        } else {
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required'
            ];
            $rules['mobile'] = [
                'rules'     => 'required|min_length[11]|is_natural|valid_number|owned_contact['.$id.']',
                'errors'    => [
                    'owned_contact' => 'Contact number already exists',
                    'is_natural'   => 'Contact number format: 09xxxxxxxxx',
                    'valid_number' => 'This is not a valid number'
                ]
            ];
            $rules['email'] = [
                'rules'     => 'required|owned_email['.$id.']',
                'errors'    => [
                    'owned_email' => 'Email is already taken'
                ]
            ];
        }

        return $rules;
    }

    protected function hasSession() {
        // redirect to login if no session found
        // redirect to verifyAccount page if session not yet verified
        if (!$this->session->has('logged_user')) {
            return redirect()->to(base_url('login'));
        } elseif (!$_SESSION['logged_user']['emailVerified']) {
            return redirect()->to(base_url('verifyAccount'));
        }
    }
}