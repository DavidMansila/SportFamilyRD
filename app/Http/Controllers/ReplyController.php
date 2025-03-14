<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store(Request $request)
    {
        $reply = Reply::create($request->all());
        return response()->json($reply, 201);
    }
}