<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\LoginModel;

/**
 * Description of LoginModelResponse
 *
 * @author chris
 */
use Core\Helpers\SessionManager;
use App\Components\ResponseBuilder;

class LoginModelResponse {

    private $id;
    private $loginModel;
    private $roleId;
    
    private $userData = false;
    
    public function __construct(LoginModel $loginModel) {
        $this->loginModel = $loginModel;
    }

    public function getLoginId() {
        $data = $this->loginModel->getProfileData();
        if (isset($data['user_id']) && isset($data['role_id'])) {
            $this->id = $data['user_id'];
            $this->roleId = $data['role_id'];
            return $this->id;
        }
        return false;
    }

    public function Login() {
        $id = self::getLoginId();
        $data = false;
        if ($id) {
            SessionManager::setCurrentUserId($id);
            SessionManager::setUserRole($this->roleId);
            $token = SessionManager::getSessionId($id);
            if ($token) {
                \User::setOnlineUser($this->id);
                $this->userData['id'] = $id;
                $this->userData['token'] = $token;
                $this->userData['gender'] = $this->loginModel->getProfileData()['gender'];
                /*Merged data from two array */
                $data = array_merge($this->userData, $this->loginModel->activationStatus());
                return $data;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getUserData() {

        return $this->loginModel->getProfileData();
    }

}
