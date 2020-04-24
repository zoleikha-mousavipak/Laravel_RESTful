<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistrationController extends Controller
{

    public function store(Request $request)
    {
        $this->validate($request, [
            'meeting_id' => 'required',
            'user_id' => 'required'
        ]);

        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

        $meeting = [
            'title' => 'title',
            'description' => 'description',
            'time' => 'time',
            'user_id' => 'user_id',
            'view_meeting' => [
                'href' => 'amp/v1/meeting/1',
                'method' => 'GET',
            ]
        ];

        $user = [
            'name' => 'name',
        ];

        $response = [
            'msg' => 'User registered for the meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => 'api/v1/meeting/1',
                'method' => 'DELETE',
            ]
        ];
        return response()->json($response, 200);
    }



    public function destroy($id)
    {
        $meeting = [
            'title' => 'title',
            'description' => 'description',
            'time' => 'time',
            'user_id' => 'user_id',
            'view_meeting' => [
                'href' => 'amp/v1/meeting/1',
                'method' => 'GET',
            ]
        ];

        $user = [
            'name' => 'name',
        ];

        $response = [
            'msg' => 'User unregistered for the meeting',
            'user' => $user,
            'meeting' => $meeting,
            'register' => [
                'href' => 'api/v1/meeting/1',
                'method' => 'POST',
                'params' => 'user_id', 'meeting_id',
            ]
        ];
        return response()->json($response, 200);
    }    }
}
