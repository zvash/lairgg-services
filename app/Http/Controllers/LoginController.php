<?php

namespace App\Http\Controllers;

use App\SocialMediaAccount;
use App\Traits\Passport\PassportToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\User;

class LoginController extends Controller
{
    use PassportToken;
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
        dd($this->getBearerTokenByUser($authUser, false));
//        Auth::login($authUser, true);
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
}
