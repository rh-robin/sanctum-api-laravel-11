<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();

        return response()->json([
            'status' => true,
            'message' => 'All post data',
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required', 
                'image' => 'required|mimes:png,jpg,jpeg,gif',
            ]
        );

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()->all()
            ],401);
        }

        $img = $request->image;
        $ext = $img->getClientOriginalExtension();
        $imageName = time().'.'.$ext;
        $img->move(public_path(). '/uploads',$imageName);

        $post = Post::create([ 
            'title' => $request->title, 
            'description' => $request->description, 
            'image' => $imageName
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post Created Successfully',
            'post' => $post
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::select(
            'title',
            'description',
            'image'
        )->where('id',$id)->get();

        return response()->json([
            'status' => true,
            'message' => 'Single Post',
            'data' => $data
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required', 
                'image' => 'required|mimes:png,jpg,jpeg,gif',
            ]
        );

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()->all()
            ],401);
        }

        $post = Post::select('id','image')->where('id', $id)->first();

        if($request->image !=''){
            $path = public_path(). '/uploads';
            if($post['image'] != '' && $post['image'] != null){
                $oldFile = $path.'/'.$post['image'];
                if(file_exists($oldFile)){
                    unlink($oldFile);
                }
            }
            $img = $request->image;
            $ext = $img->getClientOriginalExtension();
            $imageName = time().'.'.$ext;
            $img->move(public_path(). '/uploads',$imageName);
        }else{
            $imageName = $post['image'];
        }

        

        $post = Post::where(['id' => $id])->update([ 
            'title' => $request->title, 
            'description' => $request->description, 
            'image' => $imageName
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post Updated Successfully',
            'post' => $post
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $image = Post::select('image')->where('id',$id)->first();
        

        $filePath = public_path(). '/uploads'.'/'.$image['image'];
        unlink($filePath);

        $post = Post::where('id',$id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Post Deleted Successfully',
            'post' => $post
        ],200);
    }
}
