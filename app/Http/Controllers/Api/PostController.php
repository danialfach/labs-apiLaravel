<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index() 
    {
        $post = Post::latest()->paginate(5);

        return new PostResource(true, 'List Data Posts', $post);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('/public/posts', $image->hashName());

        $post = Post::create([
            'image'=> $image->hashName(),
            'title'=> $image->title(),
            'content'=> $image->content(),
        ]);

        return new PostResource(true,'Data Posts Added Successfully!', $post);
    }

    public function show($id)
    {
        $post = Post::find($id);

        return new PostResource(true,'Detail Data Post!', $post);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content'=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post = Post::find($id);

        if ($post->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/posts/'. $image->hashName());

            $post->update([
                'image'=> $image->hashName(),
                'title'=> $image->title(),
                'content'=> $image->content(),
            ]);
        } else {
            $post->update([
                'image'=> $request->hashName(),
                'title'=> $request->title(),
            ]);
        }
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        Storage::delete('public/posts'. $post->id);

        $post->delete();

        return new PostResource(true,'Data Post Deleted Successfully!', $post);
    }
}
