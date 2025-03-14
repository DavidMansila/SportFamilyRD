<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reply;
use App\Models\Comment;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return Post::with('comments.replies')->get();
    }

    public function store(Request $request)
    {
        $post = Post::create($request->all());
        return response()->json($post, 201);
    }

    public function show($id)
    {
        return Post::with('comments.replies')->findOrFail($id);
    }
}


class CommentController extends Controller
{
    public function store(Request $request)
    {
        $comment = Comment::create($request->all());
        return response()->json($comment, 201);
    }
}



class ReplyController extends Controller
{
    public function store(Request $request)
    {
        $reply = Reply::create($request->all());
        return response()->json($reply, 201);
    }
}











