<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\HistoryAnswers;
use App\Models\HistoryTag;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use Carbon\Carbon;
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
     *
     * Search for users with the given nickname
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        // tem q arrumar issos
        $users = User::where('nickname', 'like', '%' . $request->search . '%')->where('id', '<>', 1)->take($request->page)->get();

        foreach ($users as $user) {
            $user->path = Image::where('id', $user->image_id)->first();
            if (!$user->path) {
                $user->path = null;
            } else {
                $user->path = $user->path->path;
            }

        }

        $moreHistories = false;
        if ($request->page > count($users)) {
            $moreHistories = true;
        }

        return response()->json(['success' => $users, 'moreHistories' => $moreHistories]);
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
     * Display all the information about an user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */

    public function view(User $user)
    {
        $user->image = Image::find($user->image_id) ? Image::find($user->image_id)->path : null;

        $user->creation_date = Carbon::createFromFormat('Y-m-d H:i:s', $user->created_at)->format('d/m/Y');

        $user->last_update = Carbon::createFromFormat('Y-m-d H:i:s', $user->updated_at)->format('d/m/Y');

        $user->image = Image::find($user->image_id) ? Image::find($user->image_id)->path : null;

        $histories = History::where('creator_id', $user->id)->get();

        $histories_answers = [];

        foreach ($histories as $history) {
            $historyAll = HistoryAnswers::where('history_id', $history->id)->first();
            $history->image = Image::find($historyAll->image_id);
            if (!$history->image) {
                $history->image = null;
            } else {
                $history->image = $history->image->path;
            }

            $history->creation_date = Carbon::createFromFormat('Y-m-d H:i:s', $history->created_at)->format('d/m/Y');

            $allAnswers = HistoryAnswers::where('history_id', $history->id)->get();
            $likes = 0;
            $dislikes = 0;
            foreach ($allAnswers as $answer) {
                $interactions = Interaction::where('history_answer_id', $answer->id)->get();

                foreach ($interactions as $interaction) {
                    if ($interaction->interaction) {
                        $likes++;
                    } else {
                        $dislikes++;
                    }
                }

            }

            $history->likes = $likes;
            $history->dislikes = $dislikes;

            $histories_answers[] = $history;
        }

        $user->histories = $histories_answers;

        return response()->json([
            'success' => $user,
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
    public function destroy(User $user)
    {
        $userBackup = $user;

        $tags = UserTag::where('user_id', $user->id)->delete();
        $histories = History::where('creator_id', $user->id)->get();

        foreach ($histories as $history) {
            // Storage::disk('public')->delete($image->path);
            $histories_answers = HistoryAnswers::where('history_id', $history->id)->get();
            foreach ($histories_answers as $history_answer) {
                $interactions = Interaction::where('history_answer_id', $history_answer->id)->delete();
                $history_answer->delete();
            }

            $tags = HistoryTag::where('history_id', $history->id)->delete();
            $history->delete();
        }

        $history_answers = HistoryAnswers::where('user_id', $user->id)->get();

        foreach ($history_answers as $history_answer) {
            $interactions = Interaction::where('history_answer_id', $history_answer->id)->delete();
            $history_answer->delete();
        }

        $interactions = Interaction::where('user_id', $user->id)->delete();
        $user->delete();

        return response()->json([
            'success' => $user,
        ]);
    }
}
