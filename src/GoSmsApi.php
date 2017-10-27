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
    protected $company;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $sender;

    public function __construct($company, $username, $password, $sender)
    {
        $this->company = $company;
        $this->username = $username;
        $this->password = $password;
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
            'company'   => $this->company,
            'user'      => $this->username,
            'password'  => $this->password,
            'gateway'   => 'L',
            'mode'      => 'BUK',
            'type'      => 'TX',
            'charge'    => '0',
            'maskid'    => '1',
            'convert'   => '0'
        ];

        $params = array_merge($params, $base);

        try {

            //gosms api
            
            // $sendsms_url = "?company={$company}&user={$username}&password={$password}&gateway=L&mode=BUK&type=TX&hp={$valid_mobile}&mesg={$message}&charge=0&maskid=1&convert=0";

            // //$response = $client->request('GET', config('sms.credit_url').$checksms_url);
            // $response = $client->request('GET', config('sms.send_url').$sendsms_url);

            // $stream = $response->getBody();

            // $data['sms_returnstatus'] = $stream->getContents();
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
