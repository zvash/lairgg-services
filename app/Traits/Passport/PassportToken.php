<?php


namespace App\Traits\Passport;

use App\User;
use DateTime;
use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Illuminate\Events\Dispatcher;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Client as ClientModel;
use Laravel\Passport\ClientRepository as ClientModelRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;


/**
 * Trait PassportToken
 *
 * @package App\Traits
 */
trait PassportToken
{
    /**
     * Generate a new unique identifier.
     *
     * @param int $length
     *
     * @throws OAuthServerException
     *
     * @return string
     */
    private function generateUniqueIdentifier($length = 40)
    {
        try {
            return bin2hex(random_bytes($length));
            // @codeCoverageIgnoreStart
        } catch (\TypeError $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Error $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Exception $e) {
            // If you get this message, the CSPRNG failed hard.
            throw OAuthServerException::serverError('Could not generate a random string');
        }
        // @codeCoverageIgnoreEnd
    }

    private function issueRefreshToken(AccessTokenEntityInterface $accessToken)
    {
        $maxGenerationAttempts = 10;
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $refreshToken = $refreshTokenRepository->getNewRefreshToken();
        $refreshToken->setExpiryDateTime((new DateTimeImmutable())->add(Passport::refreshTokensExpireIn()));
        $refreshToken->setAccessToken($accessToken);

        while ($maxGenerationAttempts-- > 0) {
            $refreshToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $refreshTokenRepository->persistNewRefreshToken($refreshToken);

                return $refreshToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }
    }

    protected function createPassportTokenByUser(User $user)
    {
        $passportClient = ClientModel::where('password_client', 1)->first();
        $clientModelRepository = new ClientModelRepository();
        $clientRepository = new ClientRepository($clientModelRepository);
        $client = $clientRepository->getClientEntity($passportClient->id);
        $accessToken = new AccessToken($user->id, [], $client);
        $accessToken->setIdentifier($this->generateUniqueIdentifier());
        $accessToken->setClient(new Client($passportClient->id, null, null));
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->add(Passport::tokensExpireIn()));

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository(), new Dispatcher());
        $accessTokenRepository->persistNewAccessToken($accessToken);
        $refreshToken = $this->issueRefreshToken($accessToken);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    protected function sendBearerTokenResponse($accessToken, $refreshToken)
    {
        $privateKey = new CryptKey('file://'.Passport::keyPath('oauth-private.key'));
        $response = new BearerTokenResponse();
        $accessToken->setPrivateKey($privateKey);
        $response->setAccessToken($accessToken);
        $response->setRefreshToken($refreshToken);


        $response->setPrivateKey($privateKey);
        $response->setEncryptionKey(app('encrypter')->getKey());

        return $response->generateHttpResponse(new Response);
    }

    /**
     * @param \App\User $user
     * @param bool $output default = true
     * @return array | \League\OAuth2\Server\ResponseTypes\BearerTokenResponse
     */
    protected function getBearerTokenByUser(User $user, $output = true)
    {
        $passportToken = $this->createPassportTokenByUser($user);
        $bearerToken = $this->sendBearerTokenResponse($passportToken['access_token'], $passportToken['refresh_token']);

        if (! $output) {
            $bearerToken = json_decode($bearerToken->getBody()->__toString(), true);
        }

        return $bearerToken;
    }

    /**
     * @param User $user
     * @return array|BearerTokenResponse
     */
    protected function logUserInWithoutPassword(User $user)
    {
        $response = $this->getBearerTokenByUser($user, false);
        $response['email_is_provided'] = !!$user->email;
        $response['email_is_verified'] = !!$user->email_verified_at;
        $response['username_is_provided'] = !!$user->username;
        return $response;
    }
}