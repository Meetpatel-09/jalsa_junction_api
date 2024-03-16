<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function chatDashboard(Request $request)
    {
        $id = $request->user()->id;

        $friends = DB::table('users')
            ->select('id', 'name', 'profile_pic_url')
            ->whereIn('id', function ($query) use ($id) {
                $query->select(DB::raw('CASE
                    WHEN user_id_1 = ' . $id . ' THEN user_id_2
                    WHEN user_id_2 = ' . $id . ' THEN user_id_1
                END AS friend_id'))
                    ->from('messages')
                    ->where(function ($subquery) use ($id) {
                        $subquery->where('user_id_1', $id)
                            ->orWhere('user_id_2', $id);
                    });
            })
            ->groupBy('id')
            ->distinct()
            ->get();

        $data = [
            "users" => $friends,
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function sendMessage(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $user_id_2 = $request->input('user_id_2');
        $message = $request->input('message');

        DB::table('messages')->insert([
            'user_id_1' => $id,
            'user_id_2' => $user_id_2,
            'message' => $message,
        ]);

        $data = [
            "message" => "message Sent Successfully",
        ];

        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function getMessage($user_id_2, Request $request)
    {
        $user_id_1 = $request->user()->id;

        $messages = DB::table('messages')
            ->select(
                'messages.id',
                'messages.user_id_1',
                'messages.user_id_2',
                'messages.message',
                'messages.created_at',
                'user1.name as user1_name',
                'user1.profile_pic_url as user1_profile_pic',
                'user2.name as user2_name',
                'user2.profile_pic_url as user2_profile_pic'
            )
            ->join('users as user1', 'messages.user_id_1', '=', 'user1.id')
            ->join('users as user2', 'messages.user_id_2', '=', 'user2.id')
            ->where(function ($query) use ($user_id_1, $user_id_2) {
                $query->where('messages.user_id_1', $user_id_1)
                    ->where('messages.user_id_2', $user_id_2);
            })
            ->orWhere(function ($query) use ($user_id_1, $user_id_2) {
                $query->where('messages.user_id_1', $user_id_2)
                    ->where('messages.user_id_2', $user_id_1);
            })
            ->orderBy('messages.created_at', 'asc')
            ->get();


        return response()->json($messages, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}
