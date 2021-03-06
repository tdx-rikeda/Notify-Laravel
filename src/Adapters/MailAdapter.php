<?php

namespace Notify\Laravel\Adapters;

use Illuminate\Support\Facades\Mail;
use Notify\Laravel\Exception\NotifyException;


class MailAdapter implements AdapterInterface
{

    protected $options; // array of options. keys = ['from', 'to', 'subject, 'fields']

    /**
     * MailAdapter constructor.
     * Initialize values from config file.
     * @param $options
     */
    function __construct($options)
    {
        $options['to'] = config('notify.mail.address');
        $options['from'] = config('notify.mail.name');
        $options['subject'] = config('notify.mail.subject');
        $this->options = $options;
    }


    /**
     * Send content with specified options via email.
     * If there is no options specified, use one that is already specified. (at least default)
     * @param $content
     * @param $options
     * @throws NotifyException
     */
    function send($content, $options)
    {
        if (!$options) {
            $options = $this->options;
        } else {
            foreach ($this->options as $key => $value) {
                if (!key_exists($key, $options)) {
                    $options[$key] = $this->options[$key];
                }
            }
        }

        if($content instanceof \Exception) {
            // exception
            $data = $this->exceptionMessage($content);
            $data['userAgent'] = $options['fields'][0];
            $data['requestUri'] = $options['fields'][1];

        } else {
            // text message
            // if text is greater than 3000 chars, cut them at 3000 chars.
            if (strlen($content) > 3500) {
                $content = substr($content, 0, 3500);
                $content = $content . " ... ----- TEXT IS LIMITED TO 3500 CHARS-----";
            }
            $content = explode("\n", $content);
            $data['text'] = $content;
        }

        try {
            // send email
            Mail::send('notify.mail', $data, function ($message) use ($options) {
                $message->from(env('MAIL_USERNAME'), $options['from'])->to($options['to'])->subject($options['subject']);
            });
        } catch (\Exception $exception) {
            throw new NotifyException($exception);
        }




    }

    /**
     * Handles an exception object and returns as a message array.
     * @param $exception
     * @return array
     */
    private function exceptionMessage(\Exception $exception) {


        $errorName = get_class($exception) . " in " . $exception->getFile() . " line: " . $exception->getLine() . "\n";
        $errorTitle = $exception->getMessage();
        $trace = $exception->getTraceAsString();
        if (strlen($trace) > 1000) {
            $trace = substr($exception, 0, 1000);
            $trace = $trace . " ... ----- TRACE IS LIMITED TO 1000 CHARS -----";
        }
        $trace = explode("\n", $trace);
        $data = ['errorName' => $errorName,
        'errorTitle' => $errorTitle,
        'trace' => $trace];

        return $data;

    }

    /**
     * Returns true if $to is in a correct format, false if it is not.
     * @param $to
     * @return bool
     */
    private function isCorrectTo($to)
    {
        if (preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $to)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Set new address
     * @param $address
     * @throws NotifyException
     */
    function setTo($address)
    {
        if ($this->isCorrectTo($address)) {
            $this->options['to'] = $address;
        } else {
            throw new NotifyException(new \Exception("Input address is in a wrong format."));
        }
    }

    /**
     * Set new name
     * @param $name
     */
    function setFrom($name)
    {
        $this->options['from'] = $name;
    }

    /**
     * Set new subject
     * @param $subject
     */
    function setSubject($subject)
    {
        $this->options['subject'] = $subject;
    }


    /**
     * print out current status of this MailAdapter
     */
    function status()
    {
        echo "To: " . $this->options['to'] . "\n";
        echo "From: " . $this->options['from'] . "\n";
        echo "Subject: " . $this->options['subject'] . "\n";
    }

    /**
     * return bool values according to env file.
     * @return bool
     */
    function isOn()
    {
        if (config('notify.env.mail') == 1) {
            return true;
        } else {
            return false;
        }
    }
}