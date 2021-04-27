<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\SocialMediaAccount;
use App\Traits\Passport\PassportToken;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\User;

class LoginController extends Controller
{
    use PassportToken;
    use ResponseMaker;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Call the view
     * @param string $provider
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(string $provider)
    {
        return view('auth.providers.' . $provider);
    }

    /**
     * @param LoginRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $loginResponse = $this->makeInternalLoginRequest($request);
        $statusCode = $loginResponse->getStatusCode();
        $content = json_decode($loginResponse->getContent(), 1);
        if ($statusCode == 200) {
            $user = User::findByUserName($request->get('username'));
            $content['email_is_provided'] = !!$user->email;
        }
        return $this->response($content, $statusCode);
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @param $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @param $provider
     * @return void
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->stateless()->user();
        $authUser = $this->findOrCreateUser($user, $provider);
        $this->verifyEmail($provider, $authUser);
        $response = $this->logUserInWithoutPassword($authUser);
        dd($response);
//        return redirect($this->redirectTo);
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  User
     */
    public function findOrCreateUser($user, $provider)
    {
        $socialMediaAccount = SocialMediaAccount::where('provider_user_id', $user->id)
            ->where('provider', $provider)
            ->first();
        if ($socialMediaAccount) {
            $authUser = User::find($socialMediaAccount->user_id);
            return $authUser;
        }
        $allAttributes = $this->getAttributesByProvider($provider, $user);
        $attributes = array_filter($allAttributes, function($key) {
            return $key != 'provider_user_id';
        }, ARRAY_FILTER_USE_KEY);
        $user = User::create($attributes);
        SocialMediaAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $allAttributes['provider_user_id']
        ]);
        return $user;
    }

    private function getAttributesByProvider(string $provider, $user)
    {
        if ($provider == 'google') {
            $user = $this->getGoogleAttributes($user);
        }
        return $user;
    }

    private function getGoogleAttributes($user)
    {
        $user = $user->user;
        return [
            'first_name' => $user['given_name'],
            'last_name' => $user['family_name'],
            'provider_user_id' => $user['sub'],
            'email' => $user['email'],
            'username' => $this->createUsername(),
            'password' => make_random_hash(),
        ];
    }

    /**
     * @return string
     */
    private function createUsername()
    {
        $random = 'user_' . mt_rand(1000000000, 9999999999) ;
        $user = User::where('username', $random)->first();
        if ($user) {
            return $this->createUsername();
        }
        return $random;
    }

    private function verifyEmail($provider, User $user)
    {
        if ($provider == 'google') {
            $user->setAttribute('email_verified_at', date('Y-m-d H:i:s'))
                ->save();
        }
    }

    /**
     * @param LoginRequest $request
     * @return mixed
     */
    private function makeInternalLoginRequest(LoginRequest $request)
    {
        $inputs = $request->all();

        $token = Request::create(
            'oauth/token',
            'POST',
            [
                'grant_type' => 'password',
                'client_id' => $inputs['client_id'],
                'client_secret' => $inputs['client_secret'],
                'username' => $inputs['username'],
                'password' => $inputs['password'],
                'scope' => $inputs['scope'],
            ]
        );

        $loginResponse = Route::dispatch($token);
        return $loginResponse;
    }
}