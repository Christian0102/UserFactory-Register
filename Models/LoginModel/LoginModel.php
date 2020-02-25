<?php
namespace App\Model\LoginModel;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LoginModel
 *
 * @author chris*/

class LoginModel {

    private $username;
    private $password;

    const FETCH_ASSOC = 2;
    const FETCH_NUM = 3;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    public function  activationStatus() {
        $con = \Propel::getConnection();
        $query = 'SELECT '
                . 'users.complete_data AS complete_status, users.hash_email_activation AS activation_status '
                . 'from users '
                . 'where users.user_name = "'.$this->username
                .'" AND users.password = SHA1('.$this->password.')';
        $stmt = $con->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(self::FETCH_ASSOC);
        return $result;
        
    }

    public function getProfileData() {
        $con = \Propel::getConnection();
        $query = "SELECT
                       users.id AS user_id, profiles.id AS profile_id ,gender.name AS gender, role_id from  users  
                  LEFT JOIN 
                        profiles ON users.id = profiles.user_id
                    LEFT JOIN 
                        user_roles ON users.id = user_roles.user_id
                    LEFT JOIN gender ON gender.id = users.gender_id                        
                  WHERE 
                       users.status_account_id = '1' 
                  AND 
                       users.user_name =  '{$this->username}'
                  AND 
                      users.password = SHA1('{$this->password}')";
        $stmt = $con->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(self::FETCH_ASSOC);
        if ($result == false) {
            return null;
        } else {
            return $result;
        }
    }

}