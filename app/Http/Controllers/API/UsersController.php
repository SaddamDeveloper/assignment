<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Temp;
use App\Models\User;
use App\Notifications\SendCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UsersController extends Controller
{
    public function users()
    {
        $users = User::all();
        $response['status'] = 200;
        $response['users'] = $users;
        return response()->json($response);
    }
    public function registration($token)
    {
        $invite = Invite::where('token', $token)->first();
        if ($invite->status != 0) {
            $response['status'] = 400;
            $response['message'] = 'User Already Registered';
            return response()->json($response);
        }
        $response['status'] = 200;
        $response['invite'] = $invite;
        return response()->json($response);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:invites,email|unique:users,email',
            'user_name' => 'required|min:4|max:20|unique:users,user_name',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $token = $request->input('token');
        $email = $request->input('email');
        $invite = Invite::where('token', $token)->where('email', $email);
        if ($invite->count() < 1) {
            $response['status'] = 401;
            $response['message'] = 'Invalid token or email';
            return response()->json($response);
        }
        $invite = $invite->first();
        $temp = Temp::updateOrCreate(
            ['invite_id' => $invite->id],
            [
                'code_no' => rand(100000, 999999),
                'user_name' => $request->input('user_name'),
                'password' => bcrypt($request->input('password'))
            ]
        );
        if ($temp) {
            Notification::route('mail', $email)->notify(new SendCode($temp->code_no));
            $response['status'] = 200;
            $response['message'] = 'Code sent to ' . $invite->email;
            return response()->json($response);
        } else {
            $response['status'] = 500;
            $response['message'] = 'Something went wrong';
            return response()->json($response);
        }
    }
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_no' => 'required|numeric|digits:6|exists:temps,code_no',
            'email' => 'required|email|unique:users,email|exists:invites,email'
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => 401,
                'message' => $validator->errors()
            ];
            return response()->json($response, 200);
        }
        $invite = Invite::where('email', $request->input('email'))->first();
        $temp = Temp::where('code_no', $request->input('code_no'))->first();

        if ($invite->temp->code_no == $temp->code_no) {
            if ($temp->updated_at->toTimeString() < Carbon::now()->subMinutes(30)->toTimeString()) {
                $response['status'] = 401;
                $response['message'] = 'Code has been expired!';
                return response()->json($response);
            }
            if ($invite->email != $request->input('email')) {
                $response['status'] = 401;
                $response['message'] = 'Invalid email';
                return response()->json($response);
            }
            DB::beginTransaction();
            try {
                $user = new User();
                $user->user_name = $invite->temp->user_name;
                $user->password = $invite->temp->password;
                $user->email = $invite->email;
                $user->user_role = 1;
                $user->save();
                $invite->status = 1;
                $invite->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json($e->getMessage());
            }
            $response['status'] = 201;
            $response['message'] = 'User Created Successfully!';
            return response()->json($response);
        } else {
            $response['status'] = 401;
            $response['message'] = 'Invalid code no';
            return response()->json($response);
        }
    }

    public function profileUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string:255',
            'user_name' => 'required|min:4|max:20|unique:users,user_name,' . $request->user()->id,
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => 401,
                'message' => $validator->errors()
            ];
            return response()->json($response, 200);
        }
        $user = User::find($request->user()->id);
        $user->name = $request->input('name');
        $user->user_name = $request->input('user_name');
        $user->avatar = $this->avatar($request->file('avatar'));
        $user->save();
        $response['status'] = 200;
        $response['message'] = 'Profile Updated Successfully!';
        return response()->json($response);
    }

    private function avatar($avatar)
    {
        $destination = storage_path() . '/app/public/';
        $extension = $avatar->getClientOriginalExtension();
        $avatar_name = md5(date('now') . time()) . "." . $extension;
        $original_path = $destination . $avatar_name;
        Image::make($avatar)->save($original_path);
        Image::make($avatar)
            ->resize(256, 256)
            ->save($original_path);
        return $avatar_name;
    }
}
