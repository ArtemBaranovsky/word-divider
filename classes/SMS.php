<?php

namespace Classes;

use Dotenv\Dotenv;
use InvalidArgumentException;
use Vonage\Client;
use Vonage\SMS\Webhook\Factory;

class SMS extends Notification
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var string
     */
    private string $to;

    /**
     * @var string
     */
    private string $message;

    /**
     * @var string
     */
    public string $confirmName = 'code';

    /**
     * @var
     */
    private \Throwable $error;

    /**
     * SMS constructor.
     */
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $this->client = new Client(new Client\Credentials\Basic($_ENV['VONAGE_KEY'], $_ENV['VONAGE_SECRET']));
    }

    /**
     * @param string|null $to
     * @param string|null $subject
     * @param string|null $message
     * @return bool
     */
    public function send(string $to = null, string $subject = null, string $message = null): bool
    {
        $to = $to ?? $this->to;
        $message = $message ?? $this->message;

        try {
            $text = new \Vonage\SMS\Message\SMS($to, $_ENV['VONAGE_FROM'], $message);
            $text->setTtl(20000);
            $text->setClientRef($message);
            $response = $this->client->sms()->send($text);

            return $response;
        } catch (ConnectException $ex) {
            $this->set_error($ex->getMessage());
        } catch (FromException $ex) {
            $this->set_error($ex->getMessage());
        } catch (DataException $ex) {
            $this->set_error($ex->getMessage());
        } catch (RecipientException $ex) {
            $this->set_error($ex->getMessage());
        }

        return false;
    }

    /**
     * @param string $answer
     * @return bool
     */
    public function check(string $answer): bool
    {
        if (!empty($answer) && $_SESSION['digit']) {

            if ($answer != $_SESSION['digit']) {
                $_SESSION['error'][] = "Sorry, you entered invalid code.";

                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getRandomCode(): string
    {
        if (empty($_SESSION['digit'])) {

            $digit = '';

            for ($x = 15; $x <= 95; $x += 20) {
                $digit .= (rand(0, 9));
            }

            $_SESSION['digit'] = $digit;

            return $digit;
        }

        return $_SESSION['digit'];
    }

    public function html()
    {
        echo '<div class="form-group">
                    <label for="phone">Enter your phone number for sms confirmation</label>
                    <input type="text" class="form-control"  placeholder="Enter your phone number" name="confirm" value="" >
                </div>
                
                    <input id="sendcode"  class="btn btn-primary" value="Send SMS with code" data-code="' . $this->getRandomCode() . '">
                
                <div class="form-group">
                    <label for="' . $this->confirmName . '">Enter response code from SMS</label>
                    <input type="text" class="form-control"  placeholder="Enter SMS response code" name="' . $this->confirmName . '" value="" >
                </div>';
    }


    public function receive()
    {
        try {
            $inbound = Factory::createFromGlobals();
            error_log($inbound->getText());
        } catch (InvalidArgumentException $e) {
            error_log('invalid message');
        }
    }

    public function get_error()
    {
        return $this->error;
    }

    public function set_error($mes)
    {
        $this->error = $mes;
        $file = fopen('log.txt', 'a');
        $file == fwrite($file, "\r\n" . gmdate('Y-m-d H:i:s') . ' ' . $mes);
        fclose($file);
    }
}