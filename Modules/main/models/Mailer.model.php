<?php
/**
 * Mailer.model.php
 * A very simple mailer class that uses php default mail function.
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Mailer
{
    protected $to, $from, $subject, $body, $mailer;
    protected $headers = array();

    /**
     * Construct
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param bool $inHtml Send the Html or plain text headers.
     * @return void
     */
    public function __construct($from, $to, $subject, $body, $inHtml = true)
    {
        $this->from = $from;
        $this->addMailHeader('From', $this->from);
        $this->addMailHeader('Reply-To', $this->from);
        $this->addMailHeader('Return-Path', $this->from);
        $this->addMailHeader('X-mailer', 'PHP/BolidoMailer ' . (defined('BOLIDOVERSION') ? BOLIDOVERSION : 'Unknown'));
        $this->addMailHeader('MIME-Version', '1.0');

        if ($inHtml)
            $this->addMailHeader('Content-type', 'text/html; charset=UTF-8');
        else
            $this->addMailHeader('Content-type', 'text/plain; charset=UTF-8');

        $this->addMailHeader('Content-Transfer-Encoding', '8bit');

        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Appends stuff to the mail header
     *
     * @param string $header The name of the header
     * @param string $value
     * @return void
     */
    public function addMailHeader($header, $value) { $this->headers[$header] = $value; }

    /**
     * Sends the mail.
     *
     * @return bool or throws an exception if the operation was not successful.
     */
    public function send()
    {
        if (!filter_var($this->from, FILTER_VALIDATE_EMAIL))
            throw new Exception('The address ' . $this->from . ' does not appear to be valid!');
        else if (!filter_var($this->to, FILTER_VALIDATE_EMAIL))
            throw new Exception('The address ' . $this->to . ' does not appear to be valid!');

        $headers = '';
        if (!empty($this->headers))
        {
            foreach ($this->headers as $key => $value)
                $headers .= $key . ": " . $value . "\r\n";
        }

        if (!mail($this->to, '=?utf-8?B?' . base64_encode($this->subject) . '?=', $this->body, $headers))
            throw new Exception('Error sending mail');

        return true;
    }
}
?>
