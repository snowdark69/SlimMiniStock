<?php
// ใช้เขียนโค้ดทั้งหมด สำหรับทำหน้า url api

use SebastianBergmann\CodeUnit\FunctionUnit;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

//เรียกใช้ JWT
use \Firebase\JWT\JWT;

return function (App $app) {
    $container = $app->getContainer(); // เรียกใช้ container เพื่อให้ ทำ routing ได้

    //การสร้าง Routing

    //Root

    //$app->get('/',function(Request $request,Response $response,array $args) use ($container)  //array ใช้รับค่าหลัง url /
    //{
    //    echo "hello first page";
    //});

    //About
    //$app->get('/about',function(Request $request,Response $response,array $args) use ($container)  //array ใช้รับค่าหลัง url /
    //{
    //    echo "hello About page";
    //});

    $app->get('/', function (Request $request, Response $response, array $args) use ($container)  //array ใช้รับค่าหลัง url /
    {
        echo "hello first page";
    });

    // Login และ รับ Token
    $app->post('/login', function (Request $request, Response $response, array $args) use ($container){
 
        $input = $request->getParsedBody();

        $password = sha1($input['password']);

        $sql = "SELECT * FROM users WHERE username=:username and password=:password";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("username", $input['username']);
        $sth->bindParam("password", $password);
        $sth->execute();

        $count = $sth->rowCount();
        if($count){
            $user = $sth->fetchObject();
            $settings = $this->get('settings'); // get settings array.
            $token = JWT::encode(['id' => $user->id, 'username' => $user->username], $settings['jwt']['secret'], "HS256");
            return $this->response->withJson(['token' => $token]);
        }else{
            return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match our records.']);
        }
    });




    //Routing group เพื่อสร้าง path ที่ต้องการ
    $app->group('/api', function () use ($app) {
        $container = $app->getContainer(); // เรียกใช้ container เพื่อให้ ทำ routing ได้ ต้องเรียกซ้ำเพราะแยก group ออกมา

        // สามารถเขียน edit delete โดยใช้ post ก็ได้ แต่ต้องเขียน url เพิ่ม เพื่อแก้ไขตัว sql ข้างใน
        //$app->post('/data',function(Request $request,Response $response,array $args) use ($container)  //array ใช้รับค่าหลัง url /
        //{
        //    echo "This is post data route";
        //});

        //$app->put('/data',function(Request $request,Response $response,array $args) use ($container)  //array ใช้รับค่าหลัง url /
        //{
        //    echo "This is edit data route";
        //});

        //$app->delete('/data',function(Request $request,Response $response,array $args) use ($container)  //array ใช้รับค่าหลัง url /
        //{
        //    echo "This is delete data route";
        //});

        // Get all product (Method GET)
        $app->get('/products', function (Request $request, Response $response, array $args) use ($container)  //array ใช้รับค่าหลัง url /
        {
            //Read Products
            $sql = "SELECT * FROM products";

            $stmt = $this->db->prepare($sql); //db จาก dependencise
            $stmt->execute();
            $product = $stmt->fetchALL(); //Fetch ข้อมูลทั้งหมด (arroy)

            // ตัวอย่างการสร้าง array เสริม กรณีต้องการการแทรกค่า
            //$option = array(
            //    'status' => 'success',
            //);

            // สร้าง array ใหม่ เพื่อแทรก status ก่อนโชว์ค่าที่เรียก โดยรวมกับค่า ที่เรียกด้วย 
            if (count($product)) {
                $input = [
                    'status' => 'Success',
                    'message' => 'Read Product Success',
                    'data' => $product
                ];
            } else {
                $input = [
                    'status' => 'Fail',
                    'message' => 'Empty Product Data',
                    'data' => $product
                ];
            }

            //return $this->response->withJson($product); // แสดง reponse โดยแปลง $product เป็น json
            return $this->response->withJson($input);
        });

        // Get product By ID(Method GET)
        $app->get('/products/{id}', function (Request $request, Response $response, array $args) use ($container)  //array ใช้รับค่าหลัง url 
        //$app->get('/products/{id}/{product_date}/{product_category}',function(Request $request,Response $response,array $args) use ($container)  // กรณีมีหลายตัวแปร
        //$app->get('/products/{id}?a=20',function(Request $request,Response $response,array $args) use ($container)  // กรณีมีหลายตัวแปร แบบใช้ php get ธรรมดา
        {
            //$GET['a']; // กรณีมีหลายตัวแปร แบบใช้ php get ธรรมดา และรับ สงค่าด้วย get

            //Read Products
            $sql = "SELECT * FROM products WHERE id = '$args[id]' ";
            //$sql = "SELECT * FROM products WHERE product_date = '$args[product_date]' and product_category = '$args[product_category]' "; // กรณีมีหลายตัวแปร
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $product = $stmt->fetchALL();

            if (count($product)) {
                $input = [
                    'status' => 'Success',
                    'message' => 'Read Product Success',
                    'data' => $product
                ];
            } else {
                $input = [
                    'status' => 'Fail',
                    'message' => 'Empty Product Data',
                    'data' => $product
                ];
            }

            return $this->response->withJson($input);
        });


        // Add new product (Method Post)  // นาทีที่ 3:45:00 ไฟล์ 2
        $app->post('/products', function (Request $request, Response $response, array $args) use ($container) {
            //รับจาก Client
            $body = $this->request->getParsedBody(); //รับจาก แบบ Form , input , json ต่างๆที่กรอกเข้ามา ต่างจาก getbody ที่รับจาก array , url
            //print_r($body); // แสดงข้อมูลที่รับจาก body ชนิด json โดยแสดงเป้น array (print_r)
            $img = "noimg.jpg";
            $sql = "INSERT INTO products(product_name,product_detail,product_barcode,product_price,product_qty,product_image) 
                       VALUES(:product_name,:product_detail,:product_barcode,:product_price,:product_qty,:product_image)";
            $sth = $this->db->prepare($sql);
            $sth->bindParam("product_name", $body['product_name']); //รับค่าจาก body -> raw (json) ที่ชื่อว่า product_name
            $sth->bindParam("product_detail", $body['product_detail']);
            $sth->bindParam("product_barcode", $body['product_barcode']);
            $sth->bindParam("product_price", $body['product_price']);
            $sth->bindParam("product_qty", $body['product_qty']);
            $sth->bindParam("product_image", $img);

            if ($sth->execute()) { // ถ้า insert id ได้ 1 แถว สำเร้จ
                $data = $this->db->lastInsertId(); // คืนค่า id ล่าสุดที่ insert ได้กลับมา แสดง (fuction ของ slim)
                $input = [
                    'id' => $data,
                    'status' => 'success'
                ];
            } else {
                $input = [
                    'id' => '',
                    'status' => 'fail'
                ];
            }

            return $this->response->withJson($input);
        });

        // Edit product (Method Post)  // นาทีที่ 4:08:00 ไฟล์ 2
        $app->put('/products/{id}', function (Request $request, Response $response, array $args) use ($container) {
            // หลัง /product/{xx} ต้องระบุชื่อ parameter เพื่อรับค่า id จากตัวแปร array ที่ชือว่า args ที่จะทำการ update ด้วย

            // รับจาก Client
            $body = $this->request->getParsedBody();

            $sql = "UPDATE  products SET 
                            product_name=:product_name, 
                            product_detail=:product_detail,
                            product_barcode=:product_barcode,
                            product_price=:product_price,
                            product_qty=:product_qty
                        WHERE id='$args[id]'"; // ใช้ค่า id ที่ได้จาก url เช่น /api/product/57

            $sth = $this->db->prepare($sql);
            $sth->bindParam("product_name", $body['product_name']); // รับค่าจาก body -> raw (json) ที่ชื่อว่า product_name
            $sth->bindParam("product_detail", $body['product_detail']);
            $sth->bindParam("product_barcode", $body['product_barcode']);
            $sth->bindParam("product_price", $body['product_price']);
            $sth->bindParam("product_qty", $body['product_qty']);


            if ($sth->execute()) {
                $data = $args['id']; // ใช้ข้อมุล id ที่ได้รับจาก args มาแสดงเมื่อ update สำเร็จ 
                $input = [
                    'id' => $data,
                    'status' => 'success'
                ];
            } else {
                $input = [
                    'id' => '',
                    'status' => 'fail'
                ];
            }

            return $this->response->withJson($input);
        });


        // Delete Product  (Method Delete) นาทีที่ 4:15:00
        $app->delete('/products/{id}', function (Request $request, Response $response, array $args) {
            // หลัง /product/{xx} ต้องระบุชื่อ parameter เพื่อรับค่า id จากตัวแปร array ที่ชือว่า args ที่จะทำการ delete ด้วย
            // รับจาก Client
            $body = $this->request->getParsedBody();
            $sql = "DELETE FROM products WHERE id='$args[id]'";

            $sth = $this->db->prepare($sql);

            if ($sth->execute()) {
                $data = $args['id'];
                $input = [
                    'id' => $data,
                    'status' => 'success'
                ];
            } else {
                $input = [
                    'id' => '',
                    'status' => 'fail'
                ];
            }

            return $this->response->withJson($input);
        });
    }); //End Group API
};
