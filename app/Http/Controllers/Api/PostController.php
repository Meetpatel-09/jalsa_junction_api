<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class PostController extends Controller
{
    public function index()
    {
        //
    }

    public function addPost(Request $request)
    {
        $currentUser = $request->user();

        $id = $currentUser->id;

        $validator = Validator::make($request->all(), [
            'description' => 'required|String',
            'file' => 'mimes:png,jpg,jpeg,gif,mp4,mov,avi,wmv'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors(),
            ]);
        }

        if ($request->hasFile('file')) {
            $f = $request->file('file');
            $ext = $f->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $f->move(public_path() . '/posts', $fileName);

            $post = new Post;
            $post->url = $fileName;
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

    public function viewFriendPost(Request $request)
    {
        $user_id = $request->user()->id;

        // Retrieve IDs of friends
        $friendIds = DB::table('friend')->where('user_id_1', $user_id)
            ->where('status', 'accepted')
            ->pluck('user_id_2')
            ->toArray();

        $friendIds = array_merge($friendIds, DB::table('friend')->where('user_id_2', $user_id)
            ->where('status', 'accepted')
            ->pluck('user_id_1')
            ->toArray());

        // Retrieve liked posts by the authenticated user
        $likedPostIds = DB::table('likes')
            ->where('user_id', $user_id)
            ->pluck('post_id')
            ->toArray();

        // Retrieve posts of friends and add information about whether the authenticated user has liked each post
        $posts = Post::whereIn('user_id', $friendIds)
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select('posts.*', 'users.name', 'users.profile_pic_url')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($post) use ($likedPostIds) {
                $post->liked_by_user = in_array($post->id, $likedPostIds);
                return $post;
            });

        return response()->json($posts);

    }

    public function viewFriendPostVideo(Request $request)
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

        $posts = Post::whereIn('user_id', $friendIds)->where('type', 'video')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select('posts.*', 'users.name', 'users.profile_pic_url')->orderBy('id', 'desc')
            ->get();

        return response()->json($posts);
    }

    public function viewUserPost(Request $request) {
        
    }

    public function like(Request $request, Post $post)
    {
        //
    }

    public function destroy(Post $post)
    {
        //
    }

    public function posts($filename)
    {
        $path = storage_path('..\\public\\posts\\' . $filename);
        return response()->file($path);
    }
}
