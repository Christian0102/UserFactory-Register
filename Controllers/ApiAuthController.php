<?php

namespace App\Controllers;

use Core\Helpers\SessionManager;
use App\Model\LoginModel\LoginModel;
use App\Components\TokenParser;
use App\Components\ResponseBuilder;
use App\Model\LoginModel\LoginModelResponse;

class ApiAuthController extends \Core\Controller\AdminController {

    public function login() {
        $postdata = json_decode(file_get_contents('php://input'), true, JSON_UNESCAPED_UNICODE);
        $response = new ResponseBuilder();
        $username = htmlspecialchars($postdata['username']);
        $password = htmlspecialchars($postdata['password']);
        if (!empty($password) && !empty($username)) {
            $loginModel = new LoginModel($username, $password);
            $LoginResponse = new LoginModelResponse($loginModel);
            $data = $LoginResponse->Login();
            if (is_array($data)) {
                $response->succesAuth($data);
            } else {
                $response->ResponseFailed('Invalid Password or Name', 403);
            }
        } else {
            $response->ResponseFailed('Invalid Input Object', 400);
        }
    }

    public function logout() {
        $postdata = json_decode(file_get_contents('php://input'), true);
        $tokenParser = new TokenParser();
        $user_id = htmlspecialchars(intval($postdata['id']));
        $token = $tokenParser->getToken();
        $response = new ResponseBuilder();
        if (isset($user_id, $token) && !empty($user_id) && !empty($token)) {
						$this->setOffline($user_id);

            $result = SessionManager::sessionDestroy($user_id, $token);
            if ($result == true) {
                $response->CreateResponse('Success Logout', 201);
            } else {
                $response->ResponseFailed('Fatal Error invalid User Id or token', 409);
            }
        }
    }

    private function setOffline($user_id) {
			\ProfileQuery::create()
					->filterByUserId($user_id)
					->update(array(
							'IsOnline' => 0,
					));
		}

}
