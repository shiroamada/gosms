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
    protected $gateway;

    /** @var string */
    protected $mode;

    /** @var string */
    protected $type;

    /** @var string */
    protected $maskid;

    /** @var string */
    protected $charge;

    /** @var string */
    protected $convert;

    /** @var string */
    protected $url;

    /** @var string */
    protected $verifypwd;

    public function __construct($config)
    {
        $this->company   = $config['company'];
        $this->username  = $config['username'];
        $this->password  = $config['password'];
        $this->gateway   = $config['gateway'];
        $this->mode      = $config['mode'];
        $this->type      = $config['type'];
        $this->charge    = $config['charge'];
        $this->maskid    = $config['maskid'];
        $this->url       = $config['url'];
        $this->verifypwd = $config['verifypwd'];

        $this->httpClient = new HttpClient([
            'base_uri' =>  $this->apiUrl,
            'timeout' => 2.0,
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
        try {
            $sendsms_url = "?company={$this->company}&user={$this->username}&password={$this->password}&gateway={$this->gateway}";
            $sendsms_url += "&mode={$this->mode}&type={$this->type}&hp={$params['hp']}&mesg={$params['mesg']}&mesg_id={$params['mesg_id']}";
            $sendsms_url += "&charge={$this->charge}&maskid={$this->maskid}&convert={$this->convert}&url={$this->url}&verifypwd={$this->verifypwd}";

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
