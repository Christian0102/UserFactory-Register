<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components;

/**
 * Description of TokeParser
 *
 * @author chris
 */
class TokenParser {

    private $headers;
    private $headerBearer;
    private $token;

    public function __construct() {
        $this->headers = apache_request_headers();
    }

    private function getHeaderBearer() {
        if (isset($this->headers['Authorization'])) {
            $this->headerBearer = $this->headers['Authorization'];
            return true;
        }
        return false;
    }

    public function getToken() {
        if ($this->getHeaderBearer() == true) {
            $matches = array();
            preg_match('~Bearer\s(.*)~', $this->headerBearer, $matches);
            if (isset($matches)) {
                $this->token = $matches[1];
                return $this->token;
            }
        }
        return null;
    }

}
