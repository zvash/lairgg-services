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
use Illuminate\Support\Facades\URL;

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
        if (array_key_exists('error', $content)) {
            return $this->failMessage($content['message'], 401);
        }
        $user = User::findByUserName($request->get('username'));
        $content['email_is_provided'] = !!$user->email;
        $content['email_is_verified'] = !!$user->email_verified_at;
        $content['username_is_provided'] = !!$user->username;
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
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();
        $authUser = $this->findOrCreateUser($user, $provider);
        $this->verifyEmail($provider, $authUser);
        $signedUrl = config('app.url').URL::temporarySignedRoute(
            'login.single_url',
            now()->addSeconds(config('auth.single_login.expire', 120)),
            [
                'user' => $authUser->id
            ],
            false
        );
        $signedUrl = base64_encode($signedUrl);
        $redirectUrl = rtrim(route('login.social'), '/') . '?url=' . $signedUrl;

        return redirect($redirectUrl);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finishSocialLogin(Request $request)
    {
        return view('auth.verifications.loggedin');
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function singleUrlLogin(Request $request, User $user)
    {
        if ($request->hasValidSignature(false)) {
            $response = $this->logUserInWithoutPassword($user);
            return $this->response($response, 200);
        }
        return $this->failMessage("Authentication Failed.", 401);
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
        $user = $this->getUserBySocialAttributes($provider, $allAttributes);
        return $user;
    }

    private function getAttributesByProvider(string $provider, $user)
    {
        if ($provider == 'google') {
            $user = $this->getGoogleAttributes($user);
        } else if ($provider == 'discord') {
            $user = $this->getDiscordAttributes($user);
        } else if ($provider == 'twitter') {
            $user = $this->getTwitterAttributes($user);
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
            'username' => null,
            'password' => make_random_hash(),
        ];
    }

    private function getDiscordAttributes($user)
    {
        $user = $user->user;
        return [
            'first_name' => null,
            'last_name' => null,
            'provider_user_id' => $user['id'],
            'email' => $user['email'],
            'username' => null,
            'password' => make_random_hash(),
        ];
    }

    private function getTwitterAttributes($user)
    {
        dd($user);
        $user = $user->user;
        return [
            'first_name' => null,
            'last_name' => null,
            'provider_user_id' => $user['id_str'],
            'email' => null,
            'username' => null,
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

    /**
     * @param $provider
     * @param User $user
     */
    private function verifyEmail($provider, User $user)
    {
        if (in_array($provider, ['google', 'discord'])) {
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

    /**
     * @param string $provider
     * @param array $allAttributes
     * @return mixed
     */
    private function getUserBySocialAttributes(string $provider, array $allAttributes)
    {
        $attributes = array_filter($allAttributes, function ($key) {
            return $key != 'provider_user_id';
        }, ARRAY_FILTER_USE_KEY);
        $user = User::where('email', $attributes['email'])->first();
        if (!$user) {
            $user = User::create($attributes);
        }
        $socialAccount = SocialMediaAccount::where('provider', $provider)
            ->where('provider_user_id', $allAttributes['provider_user_id'])
            ->first();
        if (!$socialAccount) {
            SocialMediaAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $allAttributes['provider_user_id']
            ]);
        } else {
            $socialAccount->setAttribute('user_id', $user->id)->save();
        }

        return $user;
    }
}
