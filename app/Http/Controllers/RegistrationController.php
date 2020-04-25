<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;

class RegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'meeting_id' => 'required',
            'user_id' => 'required'
        ]);

        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

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

        // $user = [
        //     'name' => 'name',
        // ];

        $meeting = Meeting::findOrFail($meeting_id);
        $user = User::findOrFail($user_id);

        $message = [
            'msg' => 'User is already registered for meeting.',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => 'api/v1/meeting/registration' . $meeting->id,
                'method' => 'DELETE',
            ]
        ];

        if ($meeting->users()->where('user_id', $user->id)->first()) {
            return response()->json($message, 404);
        };

        $user->meetings()->attach($meeting);

        $response = [
            'msg' => 'User registered for the meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => 'api/v1/meeting/registration' . $meeting->id,
                'method' => 'DELETE',
            ]
        ];
        return response()->json($response, 201);
    }

    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);

        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }
        if (!$meeting->users()->where('user_id', $user->id)->first()) {
            return response()->json(['msg' => 'User not registered for meeting,
            delete not successful'], 401);
        }
        // $meeting->users()->detach();
        $meeting->users()->detach($user->id);

        $response = [
            'msg' => 'User unregistered for meeting',
            'meeting' => $meeting,
            // 'user' => 'tdb',
            'user' => $user,
            'register' => [
                'href' => 'api/v1/meeting/registration',
                'method' => 'POST',
                'param' => 'user_id', 'meeting_id',
            ]
        ];
        return response()->json($response, 200);










        //     $meeting = [
        //         'title' => 'title',
        //         'description' => 'description',
        //         'time' => 'time',
        //         'user_id' => 'user_id',
        //         'view_meeting' => [
        //             'href' => 'amp/v1/meeting/1',
        //             'method' => 'GET',
        //         ]
        //     ];

        //     $user = [
        //         'name' => 'name',
        //     ];

        //     $response = [
        //         'msg' => 'User unregistered for the meeting',
        //         'user' => $user,
        //         'meeting' => $meeting,
        //         'register' => [
        //             'href' => 'api/v1/meeting/1',
        //             'method' => 'POST',
        //             'params' => 'user_id', 'meeting_id',
        //         ]
        //     ];
        //     return response()->json($response, 200);
        // }


    }
}
