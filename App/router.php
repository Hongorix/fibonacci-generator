<?php

use Controller\Controller;
use Helper\Helper;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_SERVER['REQUEST_URI'] === '/') {
        include 'view/index.html';
    } else {
        Helper::error(404);
    }
} else {
    $controller = new Controller();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_URI'] === '/' && isset($_POST['method'])) {

            $method = $_POST['method'];

            switch ($method) {
                case 'getData':
                    if (
                        isset($_POST['draw']) && isset($_POST['start']) &&
                        isset($_POST['length']) && isset($_POST['search']['value'])
                    ) {
                        $controller->show(

                            $_POST['draw'],
                            $_POST['start'],
                            $_POST['length'],
                            $_POST['search']['value'],
                            $_POST['order'] ?? null
                        );
                    } else {

                        Helper::error(400);
                    }
                    break;

                case 'createRow':
                    if (isset($_POST['username']) && isset($_POST['user_input'])) {
                        $controller->setRow(
                            $_POST['username'],
                            $_POST['user_input'],
                        );
                        break;
                    } else {
                        Helper::error(400);
                    }
                default:
                    Helper::error(400);
                    break;
            }
        } else {
            Helper::error(404);
        }

    } else {
        Helper::error(400);
    }
}