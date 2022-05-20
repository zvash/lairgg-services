<?php


namespace App\Services;


use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Response\DownstreamResponse;

class NotificationSender
{
    protected $title;
    protected $body;
    protected $tokens = [];
    protected $payload = [];
    protected $sound = null;

    /**
     * NotificationSender constructor.
     * @param $title
     * @param $body
     */
    public function __construct($title, $body)
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function addToken(string $token)
    {
        $this->tokens[] = $token;
        $this->tokens = array_unique($this->tokens);
        return $this;
    }

    public function addTokens(array $tokens)
    {
        $this->tokens += $tokens;
        $this->tokens = array_unique($this->tokens);
        return $this;
    }

    public function send()
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $option = $optionBuilder->build();
        $notificationBuilder = new PayloadNotificationBuilder($this->title);
        $notificationBuilder->setBody($this->body);
        $data = null;
        if ($this->payload) {
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData($this->payload);
            $data = $dataBuilder->build();
        }
        $notification = $notificationBuilder->build();
        try {
            $downstreamResponse = FCM::sendTo($this->tokens, $option, $notification, $data);
            $this->processDownstreamResponse($downstreamResponse);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

    }

    private function processDownstreamResponse(DownstreamResponse $response)
    {

    }
}
