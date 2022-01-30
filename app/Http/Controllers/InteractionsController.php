<?php

namespace App\Http\Controllers;

use App\Models\HistoryAnswers;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $oldInteraction = Interaction::where([['user_id', Auth::user()->id], ['history_answer_id', $request->history_answer_id], ['image_id', $request->image_id]])->first();

        $oldType = 9999;

        if ($oldInteraction) {
            $oldType = $oldInteraction->interaction;
            $oldInteraction->delete();
        }

        if ($request->interaction != $oldType) {
            $interaction = new Interaction();
            $interaction->user_id = Auth::user()->id;
            $interaction->image_id = $request->image_id;
            $interaction->history_answer_id = $request->history_answer_id;
            $interaction->interaction = $request->interaction;
            $interaction->save();
        }

        $answer = HistoryAnswers::where([['id', $request->history_answer_history_id], ['user_id', $request->creator_id], ['image_id', $request->image_id]])->first();

        $creator = User::where('id', $request->creator_id)->first();
        $answer->profilePic = $creator->image_id;
        $answer->author = $creator->nickname;
        $answer->interaction = $request->interaction;
        $answer->image = Image::where('id', $answer->image_id)->select('path')->first()->path;

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

        return ['success' => $answer];

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Interaction  $interaction
     * @return \Illuminate\Http\Response
     */
    public function show(Interaction $interaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Interaction  $interaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Interaction $interaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Interaction  $interaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Interaction $interaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Interaction  $interaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Interaction $interaction)
    {
        //
    }
}
