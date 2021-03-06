<?php

namespace Notify\Laravel;


use Notify\Laravel\Exception\NotifyException;


class Notify
{

    protected $adapter; // adapter that is going to be used to send message. (e.g. email or slack)
    protected $options; // array of options.


    /**
     * Notify constructor.
     * @param string $adapter name of adapter
     */
    function __construct($adapter = "")
    {
        // get info from server
        $serverInfo = request()->capture()->server;
        $userAgent = $serverInfo->get("HTTP_USER_AGENT");
        $requestUri = $serverInfo->get("REQUEST_URI");
        $fields = [$userAgent, $requestUri];
        $options['fields'] = $fields;

        // set adapter
        $adapter = $adapter ?: config('notify.default');
        $this->options = $options;
        $this->adapter = $this->createAdapter($adapter);

    }

    private function createAdapter($name) {
        $className = "Notify\\Laravel\\Adapters\\" . ucfirst($name) . "Adapter";
        $adapter = new $className($this->options);
        $this->adapter = $adapter;
        return $adapter;
    }

    /**
     * Send content to an adapter with options.
     * For SlackAdapter, keys of options = ['from', 'to', 'icon', 'endpoint', 'fields']
     * For MailAdapter, keys of options = ['from', 'to', 'subject, 'fields']
     * @param $content content that is going to be sent
     * @param array $options options for adapter
     * @param string $adapter name of adapter that is going to be used to sent a content
     */
    function send($content, $options = [], $adapter = "")
    {
        $adapter = $adapter ? $this->createAdapter($adapter) : $this->adapter;
        $this->adapter = $adapter;

        if($adapter->isOn()){
            $adapter->send($content, $options);
        }
    }

    /**
     * Set new address. Throws NotifyException if address is wrong format.
     * @param $address
     */
    function setTo($address) {
        $this->adapter->setTo($address);
    }

    /**
     * Set new name that is going to be displayed in the message.
     * @param $name
     */
    function setFrom($name) {
        $this->adapter->setFrom($name);
    }

    /**
     * Set new adapter. Current available adapter is 'slack' or 'mail'.
     * @param $adapter name of adapter.
     * @throws NotifyException
     */
    function setAdapter($adapter)
    {
        if(preg_match('/^(slack)|(mail)$/i', $adapter)) {
            $adapter = $this->createAdapter($adapter);
            $this->adapter = $adapter;
        } else {
            throw new NotifyException(new \Exception('Input adapter is in a wrong format.'));
        }
    }


}