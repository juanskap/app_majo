<?php

namespace App\Core;

/**
 * Mailer: cliente SMTP mínimo (STARTTLS + AUTH LOGIN).
 * Sin dependencias externas; compatible con Gmail y servidores SMTP comunes.
 */
class Mailer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $from;
    private string $fromName;
    private bool $encryption;

    public function __construct(
        string $host = '',
        int $port = 587,
        string $username = '',
        string $password = '',
        string $from = '',
        string $fromName = '',
        bool $encryption = true
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->from = $from;
        $this->fromName = $fromName;
        $this->encryption = $encryption;
    }

    /** Envía un correo en HTML. Devuelve true si fue aceptado por el servidor. */
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        if ($this->host === '') {
            return false;
        }

        $socket = @stream_socket_client(
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            15
        );
        if (!$socket) {
            return false;
        }

        if (!$this->expect($socket, 220)) {
            fclose($socket);
            return false;
        }

        $this->sendCommand($socket, "EHLO localhost\r\n");
        if ($this->expect($socket, 250) === false) {
            fclose($socket);
            return false;
        }

        // STARTTLS
        if ($this->encryption) {
            $this->sendCommand($socket, "STARTTLS\r\n");
            if ($this->expect($socket, 220) === false) {
                fclose($socket);
                return false;
            }
            $ok = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$ok) {
                fclose($socket);
                return false;
            }
            $this->sendCommand($socket, "EHLO localhost\r\n");
            if ($this->expect($socket, 250) === false) {
                fclose($socket);
                return false;
            }
        }

        // AUTH LOGIN
        $this->sendCommand($socket, "AUTH LOGIN\r\n");
        if ($this->expect($socket, 334) === false) {
            fclose($socket);
            return false;
        }
        $this->sendCommand($socket, base64_encode($this->username) . "\r\n");
        if ($this->expect($socket, 334) === false) {
            fclose($socket);
            return false;
        }
        $this->sendCommand($socket, base64_encode($this->password) . "\r\n");
        if ($this->expect($socket, 235) === false) {
            fclose($socket);
            return false;
        }

        // Remitente y destinatario
        $this->sendCommand($socket, "MAIL FROM:<{$this->from}>\r\n");
        if ($this->expect($socket, 250) === false) {
            fclose($socket);
            return false;
        }
        $this->sendCommand($socket, "RCPT TO:<{$to}>\r\n");
        if ($this->expect($socket, 250) === false) {
            fclose($socket);
            return false;
        }

        // Datos del mensaje
        $this->sendCommand($socket, "DATA\r\n");
        if ($this->expect($socket, 354) === false) {
            fclose($socket);
            return false;
        }

        $headers = "From: {$this->fromName} <{$this->from}>\r\n"
            . "To: <{$to}>\r\n"
            . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n";

        $body = $headers . "\r\n" . $htmlBody;

        // Terminar con punto en línea propia
        $body = preg_replace('/^\./m', '..', $body);
        $this->sendCommand($socket, $body . "\r\n.\r\n");

        if ($this->expect($socket, 250) === false) {
            fclose($socket);
            return false;
        }

        $this->sendCommand($socket, "QUIT\r\n");
        fclose($socket);
        return true;
    }

    private function sendCommand($socket, string $command): void
    {
        fwrite($socket, $command);
    }

    private function expect($socket, int $code): bool
    {
        $line = '';
        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) {
                return false;
            }
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return (int) substr($line, 0, 3) === $code;
    }
}
