<?php
// ใช้ลง library แล้วมาตั้งค่าในไฟล์นี้

//กำหนดการ connect database ที่เป้น pdo โดยจะทำผ่าน container

use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    //connect database
    $container['db'] = function ($c) { // เรียก parameter ชื่อ db จากหน้า setting
        $settings = $c->get('settings')['db']; // ส่งค่า parameter จากหน้า setting มา
        $pdo= new PDO (
            "mysql:host=".$settings['host'].";dbname=".$settings['dbname'],
             $settings['user'],
             $settings['pass']
        );
        $pdo->exec("SET NAMES 'utf8' ");// แก้ฟ้อนภาษาไทย
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // โชว์ error
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch ข้อมูลแบบ assoc โดยใช้โหมด default

        return $pdo; // ส่งค่ากลับ

    };

};
