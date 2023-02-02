<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class Authcontroller extends Controller
{
    use HasApiTokens,Notifiable,HasFactory;
    /**
     * create user
    *@param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
     */
    // public function __construct() {
    //     $this->middleware('auth:api', ['except' => ['login', 'register']]);
    // }
    public function createuser(Request $request)
    {

        try{
            $uservalidator=Validator::make($request->all(),[
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required'],
            ]);
            if($uservalidator->fails()){
                return response()->json([
                    "status"=>false,
                    "message"=>"valitor error",
                    "error"=>$uservalidator->errors()
                ],401);
            }
            $user=User::create(
                [
                    'name'=>$request->name,
                    'email'=>$request->email,
                    'password'=>Hash::make($request->password)
                ]
                );

            return response()->json([
                "status"=>true,
                "message"=>"user created succesfully",
                "token"=>$user->createtoken("user_token")->plainTextToken
            ] ,200);
        }
        catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage()
            ],500);

        }


    }
    public function loginuser(Request $request)
    {
        try{
            $uservalidator=Validator::make($request->all(),[
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required']
              ]);
              if(!Auth::attempt($request->only('email','password'))){

                return response()->json([
                    "status"=>false,
                    "message"=>"Email or password error"
                ],401);
              }
        $user=User::where("email",$request->email)->first();

        return response()->json([
            "status"=>true,
            "message"=>"user logined succesfully",
            "token"=>$user->createtoken("API TOKEN")->plainTextToken
        ] ,200);

        }catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage()
            ],500);

        }

    }
    public function foregotpwd(Request $request)
    {
        $uservalidator=Validator::make($request->only($request->email),[
           'email'=>['required','string','email','max:250','unique:user']
        ]);
        // $user=User::where("email",$request->email)->first();
        //    return response()->json([
        //     "status"=>true,
        //     "message"=>"email sent succesfully",
        //     "token"=>$user->createtoken("API TOKEN")->plainTextToken
        //    ],200);
       $status=Password::sendResetLink($request->email);
       if($status==Password::RESET_LINK_SENT){
        return [
            "status"=>__($status),
        ];
       }

       throw ValidationException::withMessages([
        "email"=>[trans($status)],
       ]);
    }
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            // 'password' => ['required', 'confirmed',RulesPassword::default()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    // 'remember_token' => Str::random(60)
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response([
                'message'=> 'Password reset successfully'
            ]);
        }

        return response([
            'message'=> __($status)
        ], 500);
    }
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }
}
