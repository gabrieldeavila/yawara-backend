<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Models\Image;
use App\Models\User;
use App\Models\UserTag;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mail;
use Validator;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('yawara')->plainTextToken;

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->admin) {
                return $this->sendError('Unauthorised.', ['error' => 'Usuário não possui acesso']);
            }

            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;
            $success['nickname'] = $user->nickname;

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Senha ou email incorretos']);
        }
    }

    public function adminLogin(Request $request)
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if (!$user->admin) {
                return $this->sendError('Unauthorised.', ['error' => 'Usuário não possui acesso']);
            }

            $admin['nickname'] = $user->nickname;
            $admin['token'] = $user->createToken('MyApp')->plainTextToken;
            $admin['name'] = $user->name;
            $admin['isAdmin'] = true;

            return $this->sendResponse($admin, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Senha ou email incorretos']);
        }
    }

    /**
     * Finishes the registration of an user.
     *
     * @param  object  $values
     * @return \Illuminate\Http\Response
     */
    public function finishRegistration(Request $values)
    {
        // salvando imagem no storage, caso haja uma imagem
        if ($values->img) {
            $converted_img = explode('base64', $values->img)[1];
            $img_name = rand(0, 99999) . rand(0, 99999) . rand(0, 99999)  . '.jpg';
            Storage::disk('public')->put($img_name, base64_decode($converted_img));

            // salvando path da imagem
            $image = new Image();
            $image->path = $img_name;
            $image->save();
        }

        // adicionando novos dados ao usuário
        $user = User::find(Auth::user()->id);
        if ($values->img) {
            $user->image_id = $image->id;
        }
        $user->nickname = $values->nickname;
        $user->save();

        // salvando dados das tags
        if (count($values->tags) > 0) {
            foreach ($values->tags as $tag) {
                $new_tag = new UserTag();
                $new_tag->tag_id = $tag['id'];
                $new_tag->user_id = $user->id;
                $new_tag->save();
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function hasFinished()
    {
        $user = User::find(Auth::user()->id);
        if ($user->nickname == null) {
            return response()->json([
                'success' => false,
            ]);
        }
        return response()->json([
            'success' => true,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            return $this->sendError('Usuário não encontrado.', ['error' => 'Usuário não encontrado']);
        }

        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        Mail::send('email.forgetPassword', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Recuperação de senha');
        });

    }

    public function updatePassword(Request $request)
    {
        $updatePassword = DB::table('password_resets')
            ->where([
                'token' => $request->token,
            ])
            ->first();
        if ($updatePassword === null) {
            return $this->sendError('Token inválido.', ['error' => 'Token inválido']);
        }
        $user = User::where('email', $updatePassword->email)
            ->update(['password' => bcrypt($request->new_password)]);

        DB::table('password_resets')->where(['email' => $updatePassword->email])->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
