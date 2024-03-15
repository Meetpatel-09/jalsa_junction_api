<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Friend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addPost(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $validator = Validator::make($request->all(), [
            'description' => 'required|String',
            'image' => 'mimes:png,jpg,jpeg,gif,mp4,mov,avi,wmv'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors(),
            ]);
        }

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $ext = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $img->move(public_path() . '/posts', $imageName);

            $post = new Post;
            $post->url = $imageName;
            $post->user_id = $id;
            $post->description = $request->description;
            if ($ext == 'mp4') {
                $post->type = "video";
            } else {
                $post->type = "image";
            }
            $post->save();

            return response()->json([
                'status' => true,
                'message' => 'Post Added Successfully'
            ]);
        }


        $post = new Post;
        $post->user_id = $id;
        $post->description = $request->description;
        $post->type = "none";
        $post->save();

        return response()->json([
            'status' => true,
            'message' => 'Post Added Successfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function viewFriendPost(Request $request)
    {
        $user_id = $request->user()->id;

        $friendIds = DB::table('friend')->where('user_id_1', $user_id)
            ->where('status', 'accepted')
            ->pluck('user_id_2')
            ->toArray();

        $friendIds = array_merge($friendIds, DB::table('friend')->where('user_id_2', $user_id)
            ->where('status', 'accepted')
            ->pluck('user_id_1')
            ->toArray());

        $posts = Post::whereIn('user_id', $friendIds)
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select('posts.*', 'users.name', 'users.profile_pic_url')
            ->get();
        return response()->json($posts);
    }

    /**
     * Update the specified resource in storage.
     */
    public function like(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }


    public function posts($filename) {


        $path = storage_path('..\\public\\posts\\' . $filename);
        return response()->file($path);

        // if (!Storage::disk('public')->exists('profile/' . $filename)) {
        //     abort(404);
        // }

        // return response()->file($path);
    }
}
