<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stories;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;

class StoryController extends Controller
{

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addStory(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $validator = Validator::make($request->all(), [
            'image' => 'required|mimes:png,jpg,jpeg,gif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors(),
            ]);
        }

        $img = $request->file('image');
        $ext = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;
        $img->move(public_path() . '/stories', $imageName);

        $image = new Stories;
        $image->url = $imageName;
        $image->user_id = $id;
        $image->save();

        return response()->json([
            'status' => true,
            'message' => 'Story Added Successfully'
        ]);
    }

    public function viweFriendStory(Request $request, Stories $story)
    {
        $currentUser = $request->user();
        $id = $currentUser->id;

        $results = DB::table('stories as s')
            ->select('s.url as story_url', 'u.name as friend_name', 'u.profile_pic_url as profile')
            ->join(DB::raw('(SELECT
                    CASE
                        WHEN user_id_1 = ' . $id . ' THEN user_id_2
                        ELSE user_id_1
                    END AS friend_id
                FROM
                    friend
                WHERE
                    (user_id_1 = ' . $id . ' OR user_id_2 = ' . $id . ')
                    AND status = "accepted") as f'), function ($join) {
                $join->on('s.user_id', '=', 'f.friend_id');
            })
            ->join('users as u', 'f.friend_id', '=', 'u.id')
            ->get();

        $myStroies = DB::table('stories as s')
            ->select('s.url as story_url', 'u.name as friend_name', 'u.profile_pic_url as profile')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('s.user_id', $id)
            ->get();

        // $myStroies = array($myStroies);
        // $results = array($results);
        $newResult = array();

        foreach ($myStroies as $result) {
            array_push($newResult, $result);
        }

        foreach ($results as $result) {
            array_push($newResult, $result);
        }

        return response()->json($newResult);

        // return response()->json([
        //     "myStroies" => $myStroies,
        //     "results" => $results,
        // ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stories $story)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stories $story)
    {
        //
    }


    public function stories($filename) {

        $path = storage_path('..\\public\\stories\\' . $filename);
        return response()->file($path);

    }
}
