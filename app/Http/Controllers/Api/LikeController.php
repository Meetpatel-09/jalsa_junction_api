<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function likePost(Request $request, $postId) {
        $user = $request->user();
        $post = Post::find($postId);
    
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
    
        $existingLike = $user->likes()->where('post_id', $post->id)->first();
    
        if ($existingLike) {
            return response()->json(['message' => 'You already liked this post'], 400);
        }
    
        $like = new Like();
        $like->user_id = $user->id;
        $like->post_id = $post->id;
        $like->save();
    
        // Increment like count in the post
        $post->increment('like_count');
    
        return response()->json(['message' => 'Post liked successfully'], 200);
    }
    
    public function unlikePost(Request $request, $postId) {
        $user = $request->user();
        $post = Post::find($postId);
    
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
    
        $existingLike = $user->likes()->where('post_id', $post->id)->first();
    
        if (!$existingLike) {
            return response()->json(['message' => 'You have not liked this post'], 400);
        }
    
        $existingLike->delete();
    
        // Decrement like count in the post
        $post->decrement('like_count');
    
        return response()->json(['message' => 'Post unliked successfully'], 200);
    }
}
