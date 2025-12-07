<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(){
        $title = 'login';
        return view('admin.auth.login',compact('title'));
    }

    public function login(Request $request){
        // allow either email or username (stored in users.name)
        $this->validate($request ,[
            'email'=>'required|string',
            'password'=>'required',
        ]);

        $login = $request->input('email');
        $password = $request->input('password');

        // Determine if the login is an email address
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $credentials = ['email' => $login, 'password' => $password];
        } else {
            // fall back to 'name' field as username
            $credentials = ['name' => $login, 'password' => $password];
        }

        $authenticate = auth()->attempt($credentials);
        if (!$authenticate){
            return back()->with('login_error',"Invalid user credentials");
        }
        return redirect()->route('dashboard');

    }
}
