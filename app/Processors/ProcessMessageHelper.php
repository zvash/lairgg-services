<?php

namespace App\Processors;

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

class ProcessMessageHelper
{
    protected $queueUrl;
    protected $client;

    function __construct(){
        $this->queueUrl = 'https://sqs.us-east-1.amazonaws.com/255655933314/lairgg-main-queue';

        $this->client = new SqsClient([
            //'profile' => 'default',
            'region' => env("AWS_DEFAULT_REGION", 'us-east-1'),
            'version' => '2012-11-05'
        ]);
    }
    public function handle()
    {
        try {
            $result = $this->client->receiveMessage(array(
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => 1,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $this->queueUrl, // REQUIRED
                'WaitTimeSeconds' => 0,
            ));
            if (count($result->get('Messages')) > 0) {
                var_dump($result->get('Messages')[0]);
                $result = $this->client->deleteMessage([
                    'QueueUrl' => $this->queueUrl, // REQUIRED
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle'] // REQUIRED
                ]);
                return true;
            } else {
                return true;
                echo "No messages in queue. \n";
            }
        } catch (AwsException $e) {
            return false;
            // output error message if fails
            error_log($e->getMessage());
        }
    }
}