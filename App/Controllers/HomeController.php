<?php

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        // Prepare some data to pass to the view
        $data = [
            'title' => 'Home Page - eTutoring System'
        ];

        // Render the view with data
        $this->view('home/index', $data);
    }
}