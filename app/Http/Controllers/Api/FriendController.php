<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FriendController extends Controller
{

    public function suggestFriends(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $suggestFriends = DB::table('users')
            ->select('id', 'name', 'profile_pic_url')
            ->whereNotIn('id', function ($query) use ($id) {
                $query->select(DB::raw('CASE
                    WHEN user_id_1 = ' .$id. ' THEN user_id_2
                    WHEN user_id_2 = ' .$id. ' THEN user_id_1
                END AS friend_id'))
                ->from('friend')
                ->where(function ($subquery) use ($id) {
                    $subquery->where('user_id_1', $id)
                        ->orWhere('user_id_2', $id);
                });
            })
            ->where('id', '!=', $id)
            ->get();

        $data = [
            "users" => $suggestFriends,
        ];
        return response()->json($data, 200);
    }

    public function getFriends(Request $request)
    {
        $id = $request->user()->id;

        $friends = DB::table('users')
            ->select('id', 'name', 'profile_pic_url')
            ->whereIn('id', function ($query) use ($id) {
                $query->select(DB::raw('CASE
                    WHEN user_id_1 = ' .$id. ' THEN user_id_2
                    WHEN user_id_2 = ' .$id. ' THEN user_id_1
                END AS friend_id'))
                ->from('friend')
                ->where(function ($subquery) use ($id) {
                    $subquery->where('user_id_1', $id)
                        ->orWhere('user_id_2', $id);
                })
                ->where('status', 'accepted');
            })
            ->get();


        $data = [
            "users" => $friends,
        ];
        return response()->json($data, 200);
    }

    public function getFriendRequest(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $users = DB::table('users')
            ->select('id', 'name', 'profile_pic_url')
            ->whereIn('id', function ($query) use ($id) {
                $query->select('user_id_1')
                    ->from('friend')
                    ->where('user_id_2', $id)
                    ->where('status', 'pending');
            })
            ->get();

        $data = [
            "users" => $users,
        ];
        return response()->json($data, 200);
    }

    public function getSendFriendRequest(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $users = DB::table('users')
            ->select('id', 'name', 'profile_pic_url')
            ->whereIn('id', function ($query) use ($id) {
                $query->select('user_id_2')
                    ->from('friend')
                    ->where('user_id_1', $id)
                    ->where('status', 'pending');
            })
            ->get();

        $data = [
            "users" => $users,
        ];
        return response()->json($data, 200);
    }

    public function sendRequest(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $user_id_2 = $request->input('user_id_2');

        DB::table('friend')->insert([
            'user_id_1' => $id,
            'user_id_2' => $user_id_2,
        ]);

        $data = [
            "message" => "Request Send Successfully",
        ];

        return response()->json($data, 201);
    }

    public function acceptRequest(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $user_id_2 = $request->input('user_id_2');

        DB::table('friend')->where('user_id_1', $user_id_2)
            ->where('user_id_2', $id)
            ->update(['status' => 'accepted']);

        $data = [
            "message" => "Request accepted Successfully",
        ];

        return response()->json($data, 201);
    }
    public function deleteRequest(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $user_id_2 = $request->input('user_id_2');

        DB::table('friend')->where('user_id_1', $id)
            ->where('user_id_2', $user_id_2)
            ->delete();
        DB::table('friend')->where('user_id_2', $id)
            ->where('user_id_1', $user_id_2)
            ->delete();
        $data = [
            "message" => "Request deleted Successfully",
        ];

        return response()->json($data, 201);
    }
}
