<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HistoryTag;
use App\Models\Tag;
use App\Models\UserTag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = Tag::all('id', 'name');

        return response()->json([
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tag = new Tag;

        $tag->name = $request->name;

        $tag->save();

        return response()->json([
            'success' => "Tag criada com sucesso",
            'tag' => (object) ['id' => $tag->id, 'name' => $tag->name],
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        foreach ($request->all() as $tag) {

            $changeTag = Tag::find($tag['id']);

            $changeTag->name = $tag['name'];
            $changeTag->save();
        }

        return response()->json([
            'success' => count($request->all()) . " tag(s) alteradas(s) com sucesso",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $tags)
    {

        // foreach ($tags->all() as $tag) {
        //     $deleteTag = Tag::find($tag);

        //     $deleteTag->delete();
        // }

        HistoryTag::whereIn('tag_id', $tags->all())->delete();
        UserTag::whereIn('tag_id', $tags->all())->delete();
        Tag::whereIn('id', $tags->all())->delete();
        return response()->json([
            'success' => count($tags->all()) . " tag(s) deletada(s) com sucesso",
        ]);
    }
}
