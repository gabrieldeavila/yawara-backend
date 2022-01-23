<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\HistoryAnswers;
use App\Models\HistoryTag;
use App\Models\Image;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HistoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $histories = HistoryAnswers::where('user_id', $user->id)->join('histories', 'histories_answers.history_id', '=', 'histories.id')->join('users', 'histories_answers.user_id', '=', 'users.id')->join('images', 'histories_answers.image_id', '=', 'images.id')->take($request->page)->select('users.nickname', 'histories.name', 'histories_answers.created_at', 'histories_answers.id', 'images.path')->get();

        foreach ($histories as $history) {
            $history->time_ago = Carbon::parse($history->created_at)->diffForHumans();
            // $history->image = Image::find($history->history->image_id);
        }
        if ($request->page > count($histories)) {
            return response()->json(['message' => 'No more histories'], 200);
        }
        return response()->json([
            'success' => $histories,
        ]);
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
        // salvando dados básicos
        $history = new History();
        $history->name = $request->name;
        $history->creator_id = Auth::user()->id;
        $history->public = $request->public;
        $history->save();

        // salvando as imagens
        $converted_img = explode('base64', $request->img)[1];
        $img_name = rand(0, 99999) . $request->name . '.jpg';
        Storage::disk('public')->put($img_name, base64_decode($converted_img));

        // salvando path da imagem
        $image = new Image();
        $image->path = $img_name;
        $image->save();

        // salvando resposta
        $historyAnswers = new HistoryAnswers();
        $historyAnswers->history_id = $history->id;
        $historyAnswers->user_id = Auth::user()->id;
        $historyAnswers->image_id = $image->id;
        $historyAnswers->save();

        // salvando dados das tags
        foreach ($request->tags as $tag) {
            $new_tag = new HistoryTag();
            $new_tag->tag_id = $tag['id'];
            $new_tag->history_id = $history->id;
            $new_tag->save();
        }

        return response()->json([
            'success' => $request,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function show(History $history)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function edit(History $history)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, History $history)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function destroy(History $history)
    {
        //
    }
}
