<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invite;
use App\Notifications\SendInvite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    public function index(Request $request)
    {
        if($request->user()->user_role != 2){
            $response['status'] = 403;
            $response['message'] = 'You are not allowed to access';
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email'
        ]);
        if ($validator->fails()) {
            $response['status'] = 401;
            $response['message'] = $validator->errors();
            return response()->json($response, 200);
        }
        do {
            $token = Str::random(20);
        } while (Invite::where('token', $token)->first());
        $invite = Invite::updateOrCreate([
            'email' => $request->input('email'),
        ],[
            'token' => $token,
            'updated_at' => Carbon::now(),
        ]);
        if ($invite) {
            $url = URL::temporarySignedRoute(
                'registration',
                now()->addMinutes(300),
                ['token' => $token]
            );
            Notification::route('mail', $request->input('email'))->notify(new SendInvite($url));
            $response['status'] = 200;
            $response['message'] = $invite->email . ' conatins a link to your email to signup';
            return response()->json($response);
        } else {
            $response['status'] = 500;
            $response['message'] = 'Something went wrong';
            return response()->json($response);
        }
    }

    
}
