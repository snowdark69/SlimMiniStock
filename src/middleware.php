<?php
// ใช้ทำ login และ ทำให้ระบบปลอดภัย jwt authen
use Slim\App;

$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "path" => "/api", /* or ["/api", "/admin"] */ 
    // url ทั้งหมดที่ลงท้ายด้วย api จะต้องใช้ token

    "attribute" => "decoded_token_data",
    "secret" => "ChangeBeforeUploadtoGitHub@2022", // จะต้องใส่ให้ตรงกับหน้า setting->jwt->secret
    "algorithm" => ["HS256"], //hash ด้วย
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);
};
