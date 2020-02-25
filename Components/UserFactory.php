<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components;

/**
 * Description of UserFactory
 *
 * @author chris
 */
class UserFactory {

    public static function createUser($gender) {
        //RegisterMan or RegisterWoman 
        $class = 'App\Model\RegisterModel\Register' . ucfirst($gender);
        if (class_exists($class)) {
            return new $class;
        } else {
            throw new \Exception("Invalid gender input and class Not Found");
        }
    }

}
