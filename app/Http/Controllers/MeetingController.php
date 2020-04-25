<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;

class MeetingController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => [
            'update', 'store', 'destroy'
        ]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meetings = Meeting::all();
        foreach ($meetings as $meeting) {
            $meeting->view_meeting = [
                'href' => 'amp/v1/meeting/' . $meeting->id,
                'method' => 'GET',
            ];
        }

        // api without db
        // $meeting = [
        //     'title' => 'title',
        //     'description' => 'description',
        //     'time' => 'time',
        //     'user_id' => 'user_id',
        //     'view_meeting' => [
        //         'href' => 'amp/v1/meeting/1',
        //         'method' => 'GET',
        //     ]
        // ];

        // $response = [
        //     'msg' => 'List of all Meetings',
        //     $meeting,
        //     $meeting
        // ];

        $response = [
            'msg' => 'List of all Meetings',
            'meetings' => $meetings
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
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
            // 'user_id' => 'required'
        ]);

        // using user extracted from token
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');

        // using user id extracted from token
        $user_id = $user->id;
        // $user_id = $request->input('user_id');

        $meeting = new Meeting([
            'title' => $title,
            'description' => $description,
            'time' => Carbon::createFromFormat('YmdHie', $time)
        ]);
        if ($meeting->save()) {
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href' => 'api/v1/meetings/' . $meeting->id,
                'method' => 'GET',
            ];
            $response = [
                'msg' => 'Meeting created',
                'meeting' => $meeting,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'msg' => 'An error accurred',
            'meeting' => $meeting,
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
        $meeting = Meeting::with('user')->where('id', $id)->firstOrFail();
        $meeting->view_meetings = [
            'href' => 'api/v1/meeting',
            'method' => 'GET'
        ];

        $response = [
            'mgs' => 'Meeting information',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);;
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
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
        ]);

        // using user extracted from token
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        // using user id extracted from token
        $user_id = $user->id;
        $meeting = [
            'title' => $title,
            'description' => $description,
            'time' => $time,
            'user_id' => $user_id,
            'view_meeting' => [
                'href' => 'amp/v1/meeting/1',
                'method' => 'GET',
            ]
        ];
        $meeting = Meeting::with('user')->firstOrFail($id);

        if (!$meeting->users()->where('user_id', $user_id)->first()) {
            return response()->json(['msg' => 'User not registered for meeting,
            update not successful'], 401);
        }

        $meeting->time = Carbon::createFromFormat('YmdHie', $time);
        $meeting->title = $title;
        $meeting->description = $description;

        if (!$meeting->update()) {
            return response()->json(['msg' => 'Error during updating'], 404);
        }

        $meeting->view_meeting = [
            'href' => 'api/v1/meeting' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting updated',
            'meeting' => $meeting,
        ];

        return response()->json($response, 201);
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
        // using user extracted from token
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }
        if (!$meeting->users()->where('user_id', $user->id)->first()) {
            return response()->json(['msg' => 'User not registered for meeting,
            delete not successful'], 401);
        }

        $users = $meeting->users;
        $meeting->users()->detach();

        if (!$meeting->delete()) {
            foreach ($users as $user) {
                $meeting->users()->attach();
            }
            return response()->json(['msg' => 'Deletion failed'], 404);
        }

        $response = [
            'msg' => 'Meeting deleted',
            'create' => [
                'href' => 'api/v1/meeting/1',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];
        return response()->json($response, 200);
    }
}
