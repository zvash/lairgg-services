<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\VerificationException;
use App\Http\Controllers\Controller;
use App\Notifications\CustomVerifyEmail;
use App\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends Controller
{

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Contracts\View\Factory
     */
    public function verify(Request $request, User $user)
    {
        if ($request->hasValidSignature(false)) {
            if ($user->hasVerifiedEmail()) {
                return view('auth.verifications.verified');
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return view('auth.verifications.success')->withUser($user);
        }
        return view('auth.verifications.failed');
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Throwable
     */
    public function resend(Request $request)
    {
        $this->checkVerification($request)->notify($this->notification());

        return response(null)->setStatusCode(202);
    }

    /**
     * Create verification email notification.
     *
     * @return \App\Notifications\CustomVerifyEmail
     */
    protected function notification()
    {
        return (new CustomVerifyEmail);
    }

    /**
     * Check and validate verification process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\User
     *
     * @throws \Throwable
     * @throws \App\Exceptions\VerificationException
     */
    protected function checkVerification(Request $request)
    {
        $user = $request->user();

        throw_if(
            ! $user->email,
            VerificationException::class,
            __('errors.exceptions.verification.invalid.message'),
            'invalid'
        );

        throw_if(
            $user->hasVerifiedEmail(),
            VerificationException::class,
            __('errors.exceptions.verification.verified.message'),
            'verified'
        );

        return $user;
    }
}
