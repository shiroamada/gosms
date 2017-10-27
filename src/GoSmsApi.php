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

            $sendsms_url = "?company={$this->company}&user={$this->username}&password={$this->password}&gateway=L&mode=BUK&type=TX&hp={$params['hp']}&mesg={$params['mesg']}&charge=0&maskid=1&convert=0";

        
            $response = $this->httpClient->request('GET', $this->apiUrl.$sendsms_url);
            
            $stream = $response->getBody();
            
            $content = $stream->getContents();

            $response = json_decode((string) $response->getBody(), true);

            if ($content == 'E01') {
                throw new \Exception('E01');
            }

            return $response;
        } catch (DomainException $exception) {
            throw CouldNotSendNotification::exceptionGoSmsRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithGoSms($exception);
        }
    }
}
