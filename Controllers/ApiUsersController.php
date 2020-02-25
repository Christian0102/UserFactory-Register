<?php

namespace App\Controllers;

use App\Components\UserFactory;
use App\Model\OnlineUser;
use App\Model\PackageAccessModel\PackageAccessModel;
use App\Model\Visit;
use Core\Helpers\Functions;
use Symfony\Component\HttpFoundation\Response;
use App\Components\ResponseBuilder;
use Profile;
use App\Model\UpdateProfileModel\UpdateProfilePreferences;
use Core\Helpers\HashEmailHelper;
use App\Model\RegisterModel\ActivationAccountModel;
use App\Model\RegisterModel\ChangeUserEmail;

class ApiUsersController extends \Core\Controller\AdminController {

    public function register() {
        $postdata = json_decode(file_get_contents('php://input'), true);
        $username = $postdata['username'];
        $password = $postdata['password'];
        $email = $postdata['email'];
        $gender = $postdata['gender'];
        $source = $postdata['source'] ?? '';
        $response = new ResponseBuilder();
        if (isset($username) && isset($password) && isset($email) && isset($gender) && isset($source)) {
            $user = UserFactory::createUser($gender);

            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setSource($source);
            if ($user->registerUser() == true) {
                $response->CreateResponse('Success Register Please Check Your Email for Activation Account');
            } else {
                $response->ResponseFailed($user->getErrors(), 401);
            }
        } else {
            $response->ResponseFailed('Invalid Input Object', 400);
        }
    }

    public function accountActivation() {
        $hash = $_GET['account'];
        $response = new ResponseBuilder();
        if ($hash) {
            $account = new ActivationAccountModel($hash);
            if ($account->activation()) {
                $response->CreateResponse("Your account has been successfully activated");
            } else {
                $response->CreateResponse($account->getErrors());
            }
        } else {
            $response->ResponseFailed('Bad Request!!!', 400);
        }
    }

    public function updateUser() {
        $profileId = $this->route_params['id'];
        $postdata = json_decode(file_get_contents('php://input'), true);
        $language = $postdata['languages'];
        $marital_status = $postdata['relationship'];
        $height = $postdata['height'];
        $age = $postdata['age'];
        $countryId = $postdata['countryId'];
        $cityId = $postdata['cityId'];
        $response = new ResponseBuilder();
        if (isset($language) && isset($marital_status) &&
                isset($height) && isset($age)) {
            $preferencesProfile = new UpdateProfilePreferences($profileId);
            $preferencesProfile->setUserLang($language);
            $preferencesProfile->setHeight($height);
            $preferencesProfile->setAge($age);
            $preferencesProfile->setMaritalStatus($marital_status);
            $preferencesProfile->setAddress($countryId, $cityId);
            if ($preferencesProfile->save() == true) {
                $response->CreateResponse('Update Personal Data Successfull');
            } else {
                $response->ResponseFailed($preferencesProfile->getErrors(), 400);
            }
        } else {
            $response->ResponseFailed('Invalid Input Data', 400);
        }
    }

    public function getUsers() {
        $postdata = json_decode(file_get_contents('php://input'), true);
        $user_id = $postdata['user_id'];
        $offset = $postdata['offset'];
        $offset = $offset ?? 10;
        $limit = 20;
        $user = \UserQuery::WhereId($user_id);
        $profiles = \Profile::getNeededProfiles($user, $offset, $limit);
        $found_profiles = count($profiles);
        // If profiles are finished - select random profiles
        if ($found_profiles < $limit) {
            $needed_profiles = $limit - $found_profiles;
            $profiles_new_circle = \Profile::getRandomProfiles($user, $needed_profiles);
            $profiles = array_merge($profiles, $profiles_new_circle);
        }
        $response = new ResponseBuilder();
        if (isset($profiles)) {
            $response->CreateResponse($profiles);
        } else {
            $response->ResponseFailed('Error', 400);
        }
    }

    public function searchProfiles() {
        $postdata = json_decode(file_get_contents('php://input'), true, JSON_UNESCAPED_UNICODE);
        $offset = $postdata['offset'];
        $response = new ResponseBuilder();
        if (isset($postdata) && is_array($postdata) && isset($offset)) {
            $profiles = Profile::searchProfiles($postdata, $offset);
            if ($profiles) {
                $response->CreateResponse($profiles);
            } else {
                $response->ResponseFailed('Not found ', 404);
            }
        }
    }
    public function changeEmail() {
        $profileId = $this->route_params['id'];
        $postdata = json_decode(file_get_contents('php://input'), true);
        $email = $postdata['email'];
        $userModel = new ChangeUserEmail($profileId, $email);
        $response = new ResponseBuilder();
        if($userModel->update()) {
            $response->CreateResponse('Email has Changed Successfully');
        } else {
            $response->ResponseFailed('Bad Request !!', 404);
        }
               
    }

		public function getProfilesInCountry() {
			$postdata = json_decode(file_get_contents('php://input'), true);
			$user_id = (int)$postdata['user_id'];
			$limit = $postdata['limit'] ?? 4;

			$user = \UserQuery::WhereId($user_id);
			if ($user) {
				$profiles = \Profile::getRandomProfilesByCountry($user, $limit);
			}

			$response = new ResponseBuilder();
			if (isset($profiles)) {
				$response->CreateResponse($profiles);
			} else {
				$response->ResponseFailed('An Error Occurred', 400);
			}
		}

	
}
