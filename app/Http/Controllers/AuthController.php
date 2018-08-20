<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use JWTAuthException;
// use App\Http\Middleware\VerifyJWTToken;
use App\User;

class AuthController extends Controller
{
    public function store(Request $request) 
    {
    	// buat validasi setiap inputan request
    	$this->validate($request, [
    		'name' => 'required',
    		'email' => 'required|email',
    		'password' => 'required|min:5'
    	]);

    	// buat variable setiap inputan
    	// ini sama dengan $_POST['name'] di native PHP
    	// kenapa tidak pakai $request->all() karena password akan di hash terlebih dahulu dan akan dibuatkan objek user
    	$name = $request->input('name');
    	$email = $request->input('email');
    	$password = $request->input('password');

    	// buat objek user untuk menyimpan data yg diinput ke dalam database
    	$user = new User([
    		'name' => $name,
    		'email' => $email,
    		'password' => bcrypt($password)
    	]);

        $credentials = [
            'email' => $email,
            'password' => $password
        ];

    	// membuat response berupa pesan berhasil/gagal ketika user berhasil dibuat
    	if ($user->save()) {

            // ketika user behasil memasukkan data ke database kita berikan data token jadi ketika gagal mendapatkan token maka dia akan memberi response juga
            $token = null;
            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect'
                    ], 404);
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                    'msg' => 'failed_to_create_token'
                ], 400);
            }

            // memberikan response
    		$user->signin = [
    			'href' => 'api/v1/user/signin',
    			'method' => 'POST',
    			'params' => 'email, password'
    		];

    		$response = [
    			'msg' => 'User Created',
    			'user' => $user,
                'token' => $token
    		];
    		return response()->json($response, 201);
    	}

    	// ketika data gagal ditambahkan maka responsenya seperti ini
    	$response = [
    		'msg' => 'An error occurred'
    	];

    	return response()->json($response, 404);

    }

    public function signin(Request $request)
    {
    	$this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        if ($user = User::where('email', $email)->first()) {

            $credentials = [
                'email' => $email,
                'password' => $password
            ];

            // ketika user behasil memasukkan data ke database kita berikan data token jadi ketika gagal mendapatkan token maka dia akan memberi response juga
            $token = null;

            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect'
                    ], 404);
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                    'msg' => 'failed_to_create_token'
                ], 400);
            }

            $response = [
                'msg' => 'User Sign',
                'user' => $user,
                'token' => $token
            ];

            return response()->json($response, 201);
        }

        // ketika data gagal ditambahkan maka responsenya seperti ini
        $response = [
            'msg' => 'An error occurred'
        ];

        return response()->json($response, 404);
    }
}
