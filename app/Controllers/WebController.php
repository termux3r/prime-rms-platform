<?php

namespace App\Controllers;

class WebController extends BaseController
{
    public function index()
    {
        return $this->login();
    }

    public function login()
    {
        return view('01-login');
    }

    public function dashboard()
    {
        return view('02-dashboard');
    }

    public function orders()
    {
        return view('03-order-pos');
    }

    public function menuItems()
    {
        return view('04-menu-items');
    }

    public function tables()
    {
        return view('05-tables');
    }

    public function reports()
    {
        return view('06-reports');
    }

    public function users()
    {
        return view('07-users');
    }
}
