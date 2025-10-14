<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
  /**
   * Display the admin dashboard.
   */
  public function dashboard()
  {
    // Check if this is staff route
    if (request()->is('staff/*')) {
      return view('staff.dashboard');
    }
    
    return view('admin.dashboard');
  }
}


