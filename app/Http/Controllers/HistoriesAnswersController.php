<?php

namespace App\Http\Controllers;

use App\Models\HistoryAnswers;
use App\Models\Interaction;
use Illuminate\Http\Request;

class HistoriesAnswersController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HistoryAnswers  $historyAnswers
     * @return \Illuminate\Http\Response
     */
    public function show(HistoryAnswers $historyAnswers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HistoryAnswers  $historyAnswers
     * @return \Illuminate\Http\Response
     */
    public function edit(HistoryAnswers $historyAnswers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HistoryAnswers  $historyAnswers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HistoryAnswers $historyAnswers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HistoryAnswers  $historyAnswers
     * @return \Illuminate\Http\Response
     */
    public function destroy(HistoryAnswers $history)
    {
        $interactions = Interaction::where('image_id', $history->image_id)->get();

        foreach ($interactions as $interaction) {
            $interaction->delete();
        }

        $history->delete();
    }
}
