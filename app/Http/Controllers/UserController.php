<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return "oi";
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $user->nickname = $request->nickname;

        $user->save();

        return response()->json([
            'success' => $request->all(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = User::find(Auth::user()->id);
        $img = Image::find($user->image_id);
        $user_tags = UserTag::where('user_id', $user->id)->get(['tag_id']);
        $tags = Tag::all('name', 'id');
        $returnTags = [];

        foreach ($tags as $tag) {
            foreach ($user_tags as $user_tag) {
                if ($tag->id == $user_tag->tag_id) {
                    $tag['selected'] = true;
                }
            }
            $returnTags[] = $tag;
        }
        // get some avatar img from imgur api

        if (!$img) {
            $img = null;
        } else {
            $img = "http://localhost:8000/storage/" . $img->path;
        }
        return response()->json([
            'success' => ['user' => $user, 'img' => $img, 'tags' => $returnTags],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if ($request->img) {
            // salvando imagem no storage
            $converted_img = explode('base64', $request->img)[1];
            $img_name = rand(0, 99999) . $request->nickname . '.jpg';
            Storage::disk('public')->put($img_name, base64_decode($converted_img));

            // salvando path da imagem
            $image = new Image();
            $image->path = $img_name;
            $image->save();
        }

        $user_tag = new UserTag();
        $user_tag->removeTags();

        // adicionando novos dados ao usuário
        $user = User::find(Auth::user()->id);
        if ($request->img) {
            $user->image_id = $image->id;
        }
        $user->nickname = $request->nickname;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        // salvando dados das tags
        foreach ($request->tags as $tag) {
            $new_tag = new UserTag();
            $new_tag->tag_id = $tag['id'];
            $new_tag->user_id = $user->id;
            $new_tag->save();

        }
        return response()->json([
            'success' => ['user' => $request->all()],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}