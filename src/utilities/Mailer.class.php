<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 2:18 PM
 */


namespace utilities;

require_once('mail.php');

use models\User;

class Mailer
{
    private $recipients;
    private $subject;
    private $body;

    /**
     * Mailer constructor.
     * @param string $subject
     * @param string $body
     * @param User[] $recipients
     */
    public function __construct(string $subject, string $body, array $recipients)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->recipients = $recipients;
    }

    /**
     * @return array
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @param array $recipients
     */
    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function send()
    {
        // Build e-mail message
        $headers = array(
            'From' => \Config::OPTIONS['emailFromName'] . ' <' . \Config::OPTIONS['emailFromAddress'] . '>',
            'Subject' => $this->subject,
            'MIME-Version' => 1,
            'Content-type' => 'text/html;charset=iso-8859-1'
        );

        /** @noinspection PhpUndefinedClassInspection */
        $smtp = \Mail::factory('smtp', array(
            'host' => \Config::OPTIONS['emailHost'],
            'port' => \Config::OPTIONS['emailPort'],
            'auth' => \Config::OPTIONS['emailAuth'],
            'username' => \Config::OPTIONS['emailUsername'],
            'password' => \Config::OPTIONS['emailPassword']
        ));

        foreach($this->recipients as $recipient)
        {
            if(!filter_var($recipient->getEmail(), FILTER_VALIDATE_EMAIL))
                continue;

            $headers['To'] = $recipient->getEmail();

            /** @noinspection PhpUndefinedMethodInspection */
            $smtp->send($recipient->getEmail(), $headers, $this->body);
        }
    }
}