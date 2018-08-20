<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;

use App\User;
use App\Meeting;

class RegisterController extends Controller
{

    public function __construct()
    {
        // panggil middleware jwt auth yg ada di meddleware VerifiyJWTToken yang sudah diregistrasikan di Kernel.php
        $this->middleware('jwt.auth');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // cek validasi dulu
        $this->validate($request, [
            'meeting_id' => 'required',
            'user_id' => 'required',
        ]);

        // masukkan inputan user ke dalam variable
        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

        // panggil objek yang dimasukkan kedalam variabel dgn argument dari variable diatas
        $meeting = Meeting::findOrFail($meeting_id);
        $user = User::findOrFail($user_id);

        // response atau server mengeluarkan pesan apa setelah direquest
        $message = [
            'msg' => 'User is already registered for meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/' . $meeting->id,
                'method' => 'DELETE',
            ]
        ];

        // kondisi ketika data meeting dgn user_id ditemukan di database
        if($meeting->users()->where('users.id', $user->id)->first()){
            return response()->json($message, 200);
        } 

        // membuat attachment
        $user->meetings()->attach($meeting);

        $response = [
            'msg' => 'User registered for meeting',
            'meeting' => $meeting,
            'user' => $user,
            'unregister' => [
                'href' => 'api/v1/meeting/regsitration/' . $meeting->id,
                'method' => 'DELETE'
            ]
        ];

        return response()->json($response, 404);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       // panggil objek yang dimasukkan kedalam variabel dgn argument dari variable diatas
       $meeting = Meeting::findOrFail($id);
       $meeting->users()->detach();

       $response = [
            'msg' => 'User unregistered for meeting',
            'meeting' => $meeting,
            'user' => 'tbd',
            'register' => [
                'href' => 'api/v1/meeting//registration',
                'method' => 'POST',
                'params' => 'user_id', 'meeting_id'
            ]
        ];

        return response()->json($response, 200);
    }
}
