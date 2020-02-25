<?php

namespace App\Model\RegisterModel;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Model\StatisticModel\StatisticModel;

/**
 * Description of RegisterUserModel
 *
 * @author chris
 */
abstract class RegisterUserModel {

    const FETCH_ASSOC = 2;
    const FETCH_NUM = 3;
    const EMAIL_SUBJECT = 'Your Are Registered Confirm new user sign-ups';
    
    protected $emailBody;

    protected $username;
    protected $password;
    protected $email;
    protected $source;

    protected $userId;
    protected $hashActivation;
    
    protected $errors = false;
    
    protected function setEmailBody($hashActivation) {
        $this->emailBody =   'Please Click on the link to activate your account'
                . ' <a href="http://'. $_SERVER['HTTP_HOST'] .
                '/api/users/register/activation?account='. $hashActivation.
                ' ">Click</a>';
    }

    abstract public function registerUser();

    abstract public function getErrors();
    
    abstract protected function insertDataUserStepOne();
    
    
    public function setUsername($username) {
        $this->username = trim($username);
        return $this;
    }

    public function setEmail($email) {
        $this->email = trim($email);
        return $this;
    }

    public function setPassword($password) {
        $this->password = trim($password);
        return $this;
    }

    public function setSource($source) {
        $this->source = $source;
        return $this;
    }

    public function checkEmail() {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return $this->errors[] = 'Invalid Email';
        }
        return true;
    }

   protected function checkUsername(){
    if(!(strlen($this->username) > 3)){
      return $this->errors[] = 'Username min length 3 characters max 20';
    }
    return true;
   }

    protected function checkUsernameExist() {
        $conn = \Propel::getConnection();
        $query = "SELECT * from users "
                . "where user_name = '{$this->username}'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        //If exist records return true
        $result = $stmt->fetch(self::FETCH_ASSOC);
        if (!$result == false) {
            return $this->errors[] = 'Already Username exist';
        }
        return true;
    }

    protected function checkEmailExist() {
        $conn = \Propel::getConnection();
        $query = "SELECT * from users "
                . "where email = '{$this->email}'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        //If exist records return true
        $result = $stmt->fetch(self::FETCH_ASSOC);
        if (!$result == false) {
            return $this->errors[] = 'Already Email exist';
        }
        return true;
    }

    protected function isValid() {
        $this->checkEmail();
        $this->checkUsername();
        $this->checkEmailExist();
        $this->checkUsernameExist();
        if ($this->errors == false) {
            return true;
        }
        return false;
    }

    protected function saveSource() {
     parse_str($this->source, $source_params);
                            // If not set source in GET - set this visit in StatisticOther
        if (!isset($source_params['source_name'])) {
            $source_params['source_name'] = 'other';
            $source_params['source'] = $this->source;
        }
        $statistics = new StatisticModel($source_params['source_name']);
        $statistics->setUserId($this->userId);
        return $statistics->save($source_params);                   
    }
}
