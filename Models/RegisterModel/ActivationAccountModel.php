<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\RegisterModel;

use Core\Helpers\HashEmailHelper;

/**
 * Description of ActivationAccountModel
 *
 * @author chris
 */
class ActivationAccountModel {

    private $hash;
    private $userId;
    private $errors = false;

    const ACTIVATION_SECONDS = 3600;
    
    CONST MAN_GENDER_ID = 1;
    CONST WOMAN_GENDER_ID = 2;

    public function __construct($hash) {
        $this->hash = $hash;
    }

    public function getUserId() {
        $this->userId = HashEmailHelper::verifyHash($this->hash, self::ACTIVATION_SECONDS);
        if ($this->userId == true) {
            return true;
        } else {
            $this->errors[] = 'The activation time has expired please contact the site administrator';
            return false;
        }
    }

    public function checkStatusAccount() {
        if ($this->getUserId() == true) {
            if (\User::checkUserAccountStatus($this->userId) == 1) {
                $this->errors[]='Invalid Input';
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    public function checkGenderUser() {
        if ($this->checkStatusAccount() == true) {
            $genderId = \User::getGenderIdByUserId($this->userId);
            if ($genderId == 1) {
                return \User::updateStatusAccount($this->userId);
            } else {
                return null;
            }
        }
        return false;
    }

    public function activation() {
        if ($this->checkGenderUser() == true) {
           return true;
        } else {
            $this->errors[] = "Please Wait Administrator for activation Your Account";
            return false;
        }
    }

    public function getErrors() {
        return $this->errors;
    }

}
