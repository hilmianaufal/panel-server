<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;

class SecurityLoginController extends Controller
{
    public function index()
    {
        $loginActivities = LoginActivity::latest()->paginate(20);

        return view('admin.security-login.index', compact('loginActivities'));
    }
}