<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\HistoryAnswers;
use App\Models\HistoryTag;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use App\Models\UserTag;
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
        $histories = History::where('creator_id', $user->id)->join('users', 'histories.creator_id', '=', 'users.id')->take($request->page)->select('users.nickname', 'histories.name', 'histories.created_at', 'histories.id')->get();

        foreach ($histories as $history) {
            $history->time_ago = Carbon::parse($history->created_at)->diffForHumans();

            $history->path = HistoryAnswers::where('history_id', $history->id)->join('images', 'histories_answers.image_id', '=', 'images.id')->select('images.path')->first()->path;
        }

        if ($request->page > count($histories)) {
            return response()->json(['message' => 'No more histories'], 200);
        }
        return response()->json([
            'success' => $histories,
        ]);
    }

    /**
     * Add a new answer to a history
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $user = Auth::user();
        $history = History::find($request->history_id);
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
        $history = $history->where('histories.creator_id', $history->creator_id)->where('histories.id', $history->id)->join('users', 'histories.creator_id', '=', 'users.id')->select('histories.name AS title', 'histories.id', 'histories.public', 'histories.created_at', 'users.nickname AS author', 'users.id AS creator_id')->first();

        if ($history->creator_id === Auth::user()->id) {
            $history->is_creator = true;
        } else {
            $history->is_creator = false;
        }
        $history->time_ago = Carbon::parse($history->created_at)->diffForHumans();

        $answers = HistoryAnswers::where('history_id', $history->id)->join('users', 'histories_answers.user_id', '=', 'users.id')->join('images', 'histories_answers.image_id', '=', 'images.id')->select('users.nickname AS author', 'histories_answers.id', 'histories_answers.created_at', 'images.path AS image', 'users.image_id AS profilePic', 'histories_answers.image_id', 'histories_answers.history_id', 'users.id AS user_id')->get();

        foreach ($answers as $answer) {
            $answer->time_ago = Carbon::parse($answer->created_at)->diffForHumans();
            $answer->profilePic = Image::where('id', $answer->profilePic)->select('path')->first()->path;

            $interactions = Interaction::where([['history_answer_id', $answer->history_id], ['image_id', $answer->image_id]])->get();

            $likes = 0;
            $dislikes = 0;

            foreach ($interactions as $interaction) {
                if ($interaction->interaction) {
                    $likes++;
                } else {
                    $dislikes++;
                }
            }

            $answer->likes = $likes;
            $answer->dislikes = $dislikes;

            $didInteract = Interaction::where([['user_id', Auth::user()->id], ['history_answer_id', $answer->history_id], ['image_id', $answer->image_id]])->first();

            if ($didInteract) {
                $answer->interaction = $didInteract->interaction;
                $answer->didInteract = true;
            } else {
                $answer->didInteract = false;
            }

        }

        $history->answers = $answers;

        return response()->json(['success' => $history]);
    }

    /**
     * Show the histories with base in the tags of a user
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function explore(Request $request)
    {
        $user = Auth::user();
        $user_tags = UserTag::where('user_id', $user->id)->take($request->page)->get();

        if ($request->page > count($user_tags)) {
            return response()->json(['message' => 'No more histories'], 200);
        }

        $histories = [];

        foreach ($user_tags as $user_tag) {
            $history = HistoryTag::where('tag_id', $user_tag->tag_id)->get();

            foreach ($history as $h) {
                if (History::find($h->history_id)) {
                    $histories[] = HistoryAnswers::where('history_id', $h->history_id)->join('histories', 'histories_answers.history_id', '=', 'histories.id')->join('users', 'histories_answers.user_id', '=', 'users.id')->join('images', 'histories_answers.image_id', '=', 'images.id')->select('users.nickname', 'histories_answers.created_at', 'histories_answers.id', "histories.name", 'images.path')->first();
                }
            }
        }

        foreach ($histories as $history) {
            $history->time_ago = Carbon::parse($history->created_at)->diffForHumans();
        }
        return response()->json([
            'success' => $histories,
        ]);
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
