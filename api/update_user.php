<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'config/core.php';
include_once 'libs/php-jwt-master/src/BeforeValidException.php';
include_once 'libs/php-jwt-master/src/ExpiredException.php';
include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
include_once 'libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;

include_once 'config/Database.class.php';
include_once 'objects/User.class.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

$token = isset($data->token) ? $data->token : "";

if ($token) {
    try {
        $decoded = JWT::decode($token, $key, array("HS256"));

        $user->firstname = $data->firstname;
        $user->lastname = $data->lastname;
        $user->email = $data->email;
        $user->password = $data->password;
        $user->id = $decoded->data->id;

        if ($user->update()) {
            $token = array(
               "iss" => $iss,
               "aud" => $aud,
               "iat" => $iat,
               "nbf" => $nbf,
               "data" => $user->toArray()
            );
            $encoded_token = JWT::encode($token, $key);
            http_response_code(200);
            echo json_encode(array(
                "message" => "User was updated.",
                "token" => $encoded_token
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Unable to update user."));
        }
    } catch (Exception $exception) {
        http_response_code(401);
        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $exception->getMessage()
        ));
    }
} else {
    http_response_code(401);
    echo json_encode(array(
        "message" => "Access denied."
    ));
}

?>