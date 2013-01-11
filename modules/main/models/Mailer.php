<?php
/**
 * Mailer.php
 * A very simple mailer class that uses php's default mail function.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Mailer
{
    protected $to, $from, $subject, $body;
    protected $headers = array();

    /**
     * Construct
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param bool $inHtml Send the content as html or plain text headers.
     * @return void
     */
    public function __construct($from, $to, $subject, $body, $inHtml = true)
    {
        $this->from = $from;
        $this->addMailHeader('From', $this->from);
        $this->addMailHeader('Reply-To', $this->from);
        $this->addMailHeader('Return-Path', $this->from);
        $this->addMailHeader('X-mailer', 'PHP/BolidoMailer ' . (defined('BOLIDO_VERSION') ? BOLIDO_VERSION : ''));
        $this->addMailHeader('MIME-Version', '1.0');

        if ($inHtml)
            $this->addMailHeader('Content-type', 'text/html; charset=UTF-8');
        else
            $this->addMailHeader('Content-type', 'text/plain; charset=UTF-8');

        // This is obsolete
        // $this->addMailHeader('Content-Transfer-Encoding', '8bit');

        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Appends stuff to the mail header
     *
     * @param string $header The name of the header
     * @param string $value  The content of the header
     * @return void
     */
    public function addMailHeader($header, $value) { $this->headers[$header] = $value; }

    /**
     * Sends the mail.
     *
     * @return bool
     *
     * @throws InvalidArgumentException when an invalid Email Address is given
     * @throws RuntimeException if the operation was not successful.
     *
     * @codeCoverageIgnore
     */
    public function send()
    {
        if (!filter_var($this->from, FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException('The address ' . $this->from . ' does not appear to be valid!');
        else if (!filter_var($this->to, FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException('The address ' . $this->to . ' does not appear to be valid!');

        $headers = '';
        foreach ($this->headers as $key => $value)
            $headers .= $key . ": " . $value . "\r\n";

        if (!mail($this->to, '=?utf-8?B?' . base64_encode($this->subject) . '?=', $this->body, $headers))
            throw new \RuntimeException('Error sending mail');

        return true;
    }

    /**
     * Gets the properties
     *
     * @param string $value
     * @return void
     */
    public function __get($value) { return $this->{$value}; }
}

?>
