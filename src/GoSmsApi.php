<?php

namespace NotificationChannels\GoSms;

use DomainException;
use GuzzleHttp\Client as HttpClient;
use NotificationChannels\GoSms\Exceptions\CouldNotSendNotification;

class GoSmsApi
{
    const FORMAT_JSON = 3;

    /** @var string */
    protected $apiUrl = 'http://api.gosms.com.my/eapi/sms.aspx';

    /** @var HttpClient */
    protected $httpClient;

    /** @var string */
    protected $login;

    /** @var string */
    protected $secret;

    /** @var string */
    protected $sender;

    public function __construct($login, $secret, $sender)
    {
        $this->login = $login;
        $this->secret = $secret;
        $this->sender = $sender;

        $this->httpClient = new HttpClient([
            'timeout' => 5,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * @param  array  $params
     *
     * @return array
     *
     * @throws CouldNotSendNotification
     */
    public function send($params)
    {
        $base = [
            'charset' => 'utf-8',
            'login'   => $this->login,
            'psw'     => $this->secret,
            'sender'  => $this->sender,
            'fmt'     => self::FORMAT_JSON,
        ];

        $params = array_merge($params, $base);

        try {

            //gosms api
            $message = bin2hex(iconv('UTF-8', 'UTF-16BE', $message));

            $sendsms_url = "?company={$company}&user={$username}&password={$password}&gateway=L&mode=BUK&type=TX&hp={$valid_mobile}&mesg={$message}&charge=0&maskid=1&convert=0";

            //$response = $client->request('GET', config('sms.credit_url').$checksms_url);
            $response = $client->request('GET', config('sms.send_url').$sendsms_url);

            $stream = $response->getBody();

            $data['sms_returnstatus'] = $stream->getContents();
            //.gosms api

            $response = $this->httpClient->get($this->apiUrl, ['form_params' => $params]);

            $response = json_decode((string) $response->getBody(), true);

            if (isset($response['error'])) {
                throw new DomainException($response['error'], $response['error_code']);
            }

            return $response;
        } catch (DomainException $exception) {
            throw CouldNotSendNotification::smscRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithSmsc($exception);
        }
    }
}
