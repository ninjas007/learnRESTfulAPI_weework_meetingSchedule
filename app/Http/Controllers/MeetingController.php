<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Meeting;

class MeetingController extends Controller
{

    public function __construct()
    {
        // panggil middleware jwt auth yg ada di meddleware VerifiyJWTToken yang sudah diregistrasikan di Kernel.php
        $this->middleware('jwt.auth', ['except' => ['index', 'show']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // melihat seluruh data meeting // requestnya
        $meetings = Meeting::all();
        foreach ($meetings as $meeting) {
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/',
                'method' => 'GET'
            ];
        }

        // responsenya 
        $response = [
           'msg' => 'List of all Meetings',
           'meeting' => $meetings
        ];

        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validasi
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required',
            'user_id' => 'required'
        ]);

        // menangkap inputan user
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        // buat objek meeting
        $meeting = new Meeting([
            'time' => $time,
            'title' => $title,
            'description' => $description
        ]);

        // ketika meeting berhasil disave maka kirimkan juga lamipiran(attach) pada user id untuk memberi tahu user mana yg membuat meeting
        // kemudian tampilkan meeting dengan url href dimana yg tampil berupa meeting idnya
        // buat variable pesan berupa response ke client
        if ($meeting->save()) {
            
            $meeting->users()->attach($user_id);
            $meeting->view_meetings = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];

            $message = [
                'msg' => 'Meeting Created',
                'data' => $meeting
            ]; 

            return response()->json($message, 201);
        }

        // response ketika data gagal dibuat
        $response = [
            'msg' => 'Error during creating'
        ];
        
        return response()->json($response, 404);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // buat variabel meeting dengan data" users yg berelasi dengan idnya
        $meeting = Meeting::with('users')->where('id', $id)->firstOrfail();
        $meeting->view_meetings = [
            'href' => 'api/v1/meeting/',
            'method' => 'GET'
        ];

        $response = [
           'msg' => 'Meeting Information',
           'data' => $meeting
        ];

        return response()->json($response, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // validasi
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required',
            'user_id' => 'required'
        ]);

        // menangkap inputan user
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        // membuat variable untuk mengambil data meeting
        $meeting = Meeting::with('users')->findOrFail($id);

        // ketika user_id tidak sama dengan user_id sebelumnya(ada didatabase)
        if(!$meeting->users()->where('users.id', $user_id)->first()){
            return response()->json(['msg' => 'user not registered for meeting, update not successful'], 401);
        }

        $meeting->time = $time;
        $meeting->title = $title;
        $meeting->description = $description;

        if(!$meeting->update()){
            return response()->json([
                'msg' => 'Error update'], 404);
        }

        $meeting->view_meteting = [
            'href' => 'api/v1/meeting/' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting Updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrfail($id);
        // mengambil data user berdasarkan id
        $users = $meeting->users;
        // melepas relasi users terhadap meeting
        $meeting->users()->detach();

        if(!$meeting->delete()){
            foreach ($users as $user) {
                $meeting->users()->attach($user);
            }

            return response()->json([
                'msg' => 'Deleting Failed'], 404);
        }

        $response = [
            'msg' => 'Meeting Deleted',
            'create' => [
                'href' => 'api/v1/meeting',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response, 200);
    }
}
