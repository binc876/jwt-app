<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller{

    /**
     * UserController constructor.
     * 认证中间件,排除登录和注册
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function index() {
        $users = User::all();
        return $users;
    }

    public function register(Request $request){
        $this->validate($request, [
            'username' => 'required|min:6',
            'password' => 'required|min:6',
            'email' => 'required|email|unique:users'
        ]);
        $attributes = [
            'username' => $request->username,
            'password' => app('hash')->make($request->password),
            'email' =>$request->email
        ];
        $user = User::create($attributes);
        return $user;
    }

    public function login(Request $request) {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);
        $credentials = request(['username', 'password']);
        if(!$token = auth()->attempt($credentials)){
            return response()->json([
                'msg' => 'login gagal'], 401);
        } else{
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 401);
        }
    }

    public function logout() {
        auth()->logout();
        return response()->json([
            'msg' => 'logout berhasil'], 200);
    }

    public function me() {
        return response()->json([
            'date' => auth()->user()], 200);
    }

    /**
     * @param $token
     * @return array
     * 返回token信息
     */
    protected function responseWithToken($token) {
        return [
            'access_token' => $token,
            'token_type'    => 'bearer',
            'expires_in'    => auth()->factory()->getTTL() * env('JWT_TTL')
        ];
    }

    public function refresh(){
        return $this->responseWithToken(auth()->refresh());
    }
}