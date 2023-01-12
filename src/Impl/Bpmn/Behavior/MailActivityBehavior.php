<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Util\EnsureUtil;
use PHPMailer\PHPMailer\{
    PHPMailer,
    SMTP,
    Exception as EmailException
};
use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExpressionInterface
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class MailActivityBehavior extends AbstractBpmnActivityBehavior
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $to;
    protected $from;
    protected $cc;
    protected $bcc;
    protected $subject;
    protected $text;
    protected $html;
    protected $charset;

    public function execute(ActivityExecutionInterface $execution): void
    {
        $toStr = $this->getStringFromField($this->to, $execution);
        $fromStr = $this->getStringFromField($this->from, $execution);
        $ccStr = $this->getStringFromField($this->cc, $execution);
        $bccStr = $this->getStringFromField($this->bcc, $execution);
        $subjectStr = $this->getStringFromField($this->subject, $execution);
        $textStr = $this->getStringFromField($this->text, $execution);
        $htmlStr = $this->getStringFromField($this->html, $execution);
        $charSetStr = $this->getStringFromField($this->charset, $execution);

        $email = $this->createEmail($textStr, $htmlStr);

        $this->addTo($email, $toStr);
        $this->setFrom($email, $fromStr);
        $this->addCc($email, $ccStr);
        $this->addBcc($email, $bccStr);
        $this->setSubject($email, $subjectStr);
        $this->setMailServerProperties($email);
        $this->setCharset($email, $charSetStr);

        try {
            $email->send();
        } catch (EmailException $e) {
            //throw LOG.sendingEmailException(toStr, e);
        }
        $this->leave($execution);
    }

    protected function createEmail(?string $text, ?string $html): PHPMailer
    {
        if ($html !== null) {
            return $this->createHtmlEmail($text, $html);
        } elseif ($text !== null) {
            return $this->createTextOnlyEmail($text);
        } else {
            //throw LOG.emailFormatException();
        }
    }

    protected function createHtmlEmail(?string $text, ?string $html): PHPMailer
    {
        $email = new PHPMailer(true);
        try {
            $email->isHTML(true);
            $email->Body = $html;
            if ($text !== null) { // for email clients that don't support html
                $email->AltBody = $text;
            }
            return $email;
        } catch (EmailException $e) {
            //throw LOG.emailCreationException("HTML", e);
            throw $e;
        }
    }

    protected function createTextOnlyEmail(?string $text): PHPMailer
    {
        $email = new PHPMailer(true);
        try {
            $email->isHTML(false);
            $email->Body = $text;
            $email->AltBody = $text;
            return $email;
        } catch (EmailException $e) {
            //throw LOG.emailCreationException("text-only", e);
            throw $e;
        }
    }

    protected function addTo(PHPMailer $email, ?string $to): void
    {
        $tos = $this->splitAndTrim($to);
        if (!empty($tos)) {
            foreach ($tos as $t) {
                try {
                    $email->addAddress($t);
                } catch (EmailException $e) {
                    //throw LOG.addRecipientException(t, e);
                    throw $e;
                }
            }
        } else {
            //throw LOG.missingRecipientsException();
        }
    }

    protected function setFrom(PHPMailer $email, ?string $from): void
    {
        $fromAddress = null;

        if ($from !== null) {
            $fromAddress = $from;
        } else { // use default configured from address in process engine config
            $fromAddress = Context::getProcessEngineConfiguration()->getMailServerDefaultFrom();
        }

        try {
            $email->setFrom($fromAddress);
        } catch (EmailException $e) {
            //throw LOG.addSenderException(from, e);
            throw $e;
        }
    }

    protected function addCc(PHPMailer $email, ?string $cc): void
    {
        $ccs = $this->splitAndTrim($cc);
        if (!empty($ccs)) {
            foreach ($ccs as $c) {
                try {
                    $email->addCC($c);
                } catch (EmailException $e) {
                    //throw LOG.addCcException(c, e);
                    throw $e;
                }
            }
        }
    }

    protected function addBcc(PHPMailer $email, ?string $bcc): void
    {
        $bccs = $this->splitAndTrim($bcc);
        if (!empty($bcc)) {
            foreach ($bccs as $b) {
                try {
                    $email->addBCC($b);
                } catch (EmailException $e) {
                    //throw LOG.addBccException(b, e);
                    throw $e;
                }
            }
        }
    }

    protected function setSubject(PHPMailer $email, ?string $subject): void
    {
        $email->Subject = $subject ?? "";
    }

    protected function setMailServerProperties(PHPMailer $email): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        $host = $processEngineConfiguration->getMailServerHost();
        EnsureUtil::ensureNotNull("Could not send email: no SMTP host is configured", "host", $host);
        $email->isSMTP();
        $email->Host = $host;

        $port = $processEngineConfiguration->getMailServerPort();
        $email->Port = $port;

        $email->SMTPSecure = $processEngineConfiguration->getMailServerUseTLS();

        $user = $processEngineConfiguration->getMailServerUsername();
        $password = $processEngineConfiguration->getMailServerPassword();
        if ($user !== null && $password !== null) {
            $email->SMTPAuth = true;
            $email->Username = $user;
            $email->Password = $password;
        }
    }

    protected function setCharset(PHPMailer $email, ?string $charSetStr): void
    {
        /*if (charset !== null) {
            email.setCharset(charSetStr);
        }*/
    }

    protected function splitAndTrim(?string $str): array
    {
        if ($str !== null) {
            $splittedStrings = explode(',', $str);
            for ($i = 0; $i < strlen($splittedStrings); $i += 1) {
                $splittedStrings[$i] = trim($splittedStrings[$i]);
            }
            return $splittedStrings;
        }
        return [];
    }

    protected function getStringFromField(?ExpressionInterface $expression, DelegateExecutionInterface $execution): ?string
    {
        if ($expression !== null) {
            $value = $expression->getValue($execution);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }
}
