<?php
namespace App\Core;

class Controller
{
    public function view($view, $data = [])
    {
        // Chuyển đổi mảng data thành các biến
        extract($data);
        
        // __DIR__ trả về đường dẫn đến folder 'app/core'
        // '../../views/' sẽ chuyển đến folder 'views' ở cấp gốc dự án
        require_once __DIR__ . '/../views/' . $view . '.php';

    }
}
