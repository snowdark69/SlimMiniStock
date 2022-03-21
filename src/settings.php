
<?php
// ใช้ตั้งค่าตัวแปรต่างๆ และจะเข้าถึงตัวแปรได้ทุกไฟล์ pdo
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        //Config connect database
        'db'=>[
            'host' => 'localhost',
            'dbname' => 'stockrestdb',
            'user' => 'root',
            'pass' => ''
            // เพิ่ม port ได้ กรณี port ไม่ใช่ 3306
        ],

        // jwt settings 
        "jwt" => [
            'secret' => 'ChangeBeforeUploadtoGitHub@2022' // ใช้ Key Secret ในการ Gen Token ออกมา // ต้องลบทุกครั้งที่มีการ commit to github
        ]
    ],
];
