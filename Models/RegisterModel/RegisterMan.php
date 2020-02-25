<?php

namespace App\Model\RegisterModel;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RegisterMan
 *
 * @author chris
 */
use Core\Helpers\Encrypt;
use User;
use Core\Helpers\HashEmailHelper;
use App\Components\MailBuilder;

class RegisterMan extends RegisterUserModel {

    private $gender = 1;
    /* status_Account = 2 Account is disabled */
    private $status_account = 1;

    public function getErrors() {
        return $this->errors;
    }

    /* Register User StepOne insert data users */

    protected function insertDataUserStepOne() {
        if ($this->isValid()) {
            $roleStmt = \RoleQuery::create();
            $user = new \User();
            $user->setHashId(Encrypt::Hash($this->email));
            $user->setEmail($this->email);
            $user->setUserName($this->username);
            $user->setPassword(sha1($this->password));
            $user->setGenderId($this->gender);
            $user->setStatusAccountId($this->status_account);
            $user->setGdprAccepted(true);
            $user->setHashActivation($this->hashActivation);
            $rowAffected = $user->save();
            $this->userId = $user->getId();
            $roleStmt = \RoleQuery::create();
            $roleID = $roleStmt->filterByName('user')->findOne()->getId();
            $userRole = new \UserRoles();
            $userRole->setRoleId($roleID);
            $userRole->setUserId($this->userId)->save();
            return $rowAffected;
        }
        return false;
    }

    /* Complete Register User with Update HashActivation column */

    public function registerUser() {
        if ($this->insertDataUserStepOne() == true && is_int($this->userId)) {
            $hashActivation = HashEmailHelper::makeHash($this->userId);
            $rowAffectedUser = User::updateHashActivation($this->userId, $hashActivation);
            if ($rowAffectedUser == true) {
		$this->saveSource();
		parent::setEmailBody($hashActivation);
                $mail = new MailBuilder($this->email, self::EMAIL_SUBJECT, $this->emailBody);
                $mail->sendEmail();
                $profile = new \Profile();
                $profile->setUserId($this->userId);
                $profile->setHashId(Encrypt::Hash($this->email));
                return $profile->save();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
