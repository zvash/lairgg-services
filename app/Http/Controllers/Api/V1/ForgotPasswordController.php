<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Traits\Passport\PassportToken;
use App\Traits\Responses\ResponseMaker;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    use ResponseMaker;
    use PassportToken;

    use SendsPasswordResetEmails {
        sendResetLinkEmail as protected;
        showLinkRequestForm as protected;
    }

    use ResponseMaker;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function sendCode(Request $request)
    {
        $this->validateEmail($request);
        $email = $request->get('email');
        $code = $this->createPasswordResetCode($email);
        Mail::to($email)->queue((new ResetPasswordMail($code))->onConnection('sqs'));
        return $this->success(['message' => __('strings.password.token_was_sent')]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function verifyToken(Request $request)
    {
        $this->validateEmail($request);
        $email = $request->get('email');
        $code = $request->get('token');
        if ($this->resetRequestIsValid($email, $code)) {
            return $this->success(['valid_token' => true]);
        }
        return $this->failMessage(__('strings.password.invalid_token_or_email_address'), 400);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resetByCode(Request $request)
    {
        $this->validateResetPassword($request);
        $email = $request->get('email');
        $code = $request->get('token');
        $user = null;
        if ($this->resetRequestIsValid($email, $code)) {
            $password = $request->get('password');
            $this->removeInvalidResetRequests($email);
            $user = $this->resetPassword($email, $password);
        }
        if ($user) {
            return $this->response($this->logUserInWithoutPassword($user), 200);
        }
        return $this->failNotFound(__('strings.password.user_was_not_found'));
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        return $this->sendResetLinkEmail($request);
    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email:rfc,dns|exists:users,email']);
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function validateResetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email:rfc,dns|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'token' => 'required|filled'
        ]);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string $response
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $this->success(['message' => 'Check your email']);
        //return response(null)->setStatusCode(202);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string $response
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return $this->failData(['email' => trans($response)], '400');
        //return response()->withErrors(['email' => trans($response)]);
    }

    /**
     * @param string $email
     * @param int|null $expireMinutes
     * @return int
     */
    private function createPasswordResetCode(string $email, ?int $expireMinutes = null)
    {
        $code = mt_rand(0, 99999);
        $code = str_pad($code, 5, '0', STR_PAD_LEFT);
        $expireMinutes = $expireMinutes ?? config('auth.passwords.users.expire', 60);
        $lastAcceptableDate = \Carbon\Carbon::now()->subtract('minutes', $expireMinutes);
        $passwordReset = DB::table('password_resets')
            ->where('token', $code)
            ->where('created_at', '>=', $lastAcceptableDate)
            ->first();
        if ($passwordReset) {
            return $this->createPasswordResetCode($email, $expireMinutes);
        }
        $this->removeInvalidResetRequests($email);
        DB::table('password_resets')
            ->insert([
                'email' => $email,
                'token' => $code,
                'created_at' => \Carbon\Carbon::now()
            ]);
        return $code;
    }

    /**
     * @param string $email
     */
    private function removeInvalidResetRequests(string $email): void
    {
        DB::table('password_resets')
            ->where('email', $email)
            ->delete();
    }

    /**
     * @param $email
     * @param $code
     * @return bool
     */
    private function resetRequestIsValid($email, $code)
    {
        $lastResetPasswordRecord = DB::table('password_resets')
            ->where('email', $email)
            ->orderBy('created_at', 'desc')
            ->first();
        return $lastResetPasswordRecord && $code == $lastResetPasswordRecord->token;
    }

    /**
     * @param $email
     * @param $password
     * @return
     */
    private function resetPassword($email, $password)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->setAttribute('password', bcrypt($password))
                ->save();
        }
        return $user;
    }
}
