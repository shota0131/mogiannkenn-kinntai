<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        
        $staffs = User::all();

        return view('admin.staff_list', compact('staffs'));
    }
}

