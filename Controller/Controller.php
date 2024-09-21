<?php
namespace Controller;

use Model\Model;
use Helper\Helper;

class Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function show($draw, $start, $length, $search, $order)
    {
        $data = $this->model->get($start, $length, $search, $order);

        $totalRecords = $this->model->getTotalRecords();
        $filteredRecords = $this->model->getFilteredRecords($search);

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);

    }

    public function setRow($username, $user_input)
    {

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_ip_proxy = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;

        if (!is_numeric($user_input)) {
            Helper::clientError("User input is not a number");
            return;
        }

        if ((int) $user_input > 10000 || (int) $user_input < 0) {
            Helper::clientError("User input is too big or too small");
            return;
        }

        if (empty($username)) {
            Helper::clientError("Username is empty");
            return;
        }

        $fibonacci_num = $this->fibonacci_calculate((string) $user_input);


        $this->model->create(
            $user_ip,
            $user_ip_proxy,
            $username,
            $user_input,
            $fibonacci_num
        );

        Helper::success("Row created successfully");
    }

    public function fibonacci_calculate($n)
    {
        if ($n <= 1) {
            return (string) $n;
        }

        $a = '0';  // First Fibonacci number as string
        $b = '1';  // Second Fibonacci number as string

        for ($i = 2; $i <= $n; $i++) {
            $temp = bcadd($a, $b);  // Sum previous two Fibonacci numbers
            $a = $b;  // Move the sequence forward
            $b = $temp;
        }

        return $b;
    }

}