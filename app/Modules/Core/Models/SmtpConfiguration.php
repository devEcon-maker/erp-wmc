<?php

namespace App\Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SmtpConfiguration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'is_default',
        'is_active',
        'last_tested_at',
        'last_test_successful',
        'last_test_error',
        'created_by',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
        'last_test_successful' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    // Encryptions disponibles
    public const ENCRYPTIONS = [
        'tls' => 'TLS',
        'ssl' => 'SSL',
        '' => 'Aucune',
    ];

    // Ports courants
    public const COMMON_PORTS = [
        25 => 'Port 25 (Standard)',
        465 => 'Port 465 (SSL)',
        587 => 'Port 587 (TLS)',
        2525 => 'Port 2525 (Alternative)',
    ];

    // Mutateur pour chiffrer le mot de passe
    public function setPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    // Accesseur pour dechiffrer le mot de passe
    public function getDecryptedPasswordAttribute(): ?string
    {
        if (!$this->password) {
            return null;
        }
        try {
            return Crypt::decryptString($this->password);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getEncryptionLabelAttribute(): string
    {
        return self::ENCRYPTIONS[$this->encryption] ?? $this->encryption;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactif';
        }
        if ($this->is_default) {
            return 'Par defaut';
        }
        return 'Actif';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Definir comme configuration par defaut
    public function setAsDefault(): void
    {
        // Retirer le flag default de tous les autres
        self::where('id', '!=', $this->id)->update(['is_default' => false]);

        $this->update(['is_default' => true, 'is_active' => true]);
    }

    // Tester la connexion SMTP
    public function testConnection(): array
    {
        try {
            // Utiliser fsockopen pour un test de connexion rapide
            $timeout = 10;
            $port = $this->port;
            $host = $this->host;

            // Pour SSL, ajouter le prefixe ssl://
            if ($this->encryption === 'ssl') {
                $host = 'ssl://' . $host;
            }

            $errno = 0;
            $errstr = '';
            $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

            if (!$connection) {
                throw new \Exception("Impossible de se connecter au serveur: $errstr ($errno)");
            }

            // Lire la reponse initiale du serveur
            $response = fgets($connection, 512);
            if (strpos($response, '220') === false) {
                fclose($connection);
                throw new \Exception("Reponse inattendue du serveur: $response");
            }

            // Envoyer EHLO
            fwrite($connection, "EHLO " . gethostname() . "\r\n");
            $response = '';
            while ($line = fgets($connection, 512)) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') break;
            }

            // Pour TLS, negocier STARTTLS
            if ($this->encryption === 'tls') {
                fwrite($connection, "STARTTLS\r\n");
                $tlsResponse = fgets($connection, 512);
                if (strpos($tlsResponse, '220') === false) {
                    fclose($connection);
                    throw new \Exception("STARTTLS echoue: $tlsResponse");
                }

                // Activer le chiffrement TLS
                if (!stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($connection);
                    throw new \Exception("Echec de l'activation TLS");
                }

                // Re-envoyer EHLO apres TLS
                fwrite($connection, "EHLO " . gethostname() . "\r\n");
                $response = '';
                while ($line = fgets($connection, 512)) {
                    $response .= $line;
                    if (substr($line, 3, 1) === ' ') break;
                }
            }

            // Tester l'authentification
            fwrite($connection, "AUTH LOGIN\r\n");
            $authResponse = fgets($connection, 512);
            if (strpos($authResponse, '334') !== false) {
                // Envoyer username
                fwrite($connection, base64_encode($this->username) . "\r\n");
                $userResponse = fgets($connection, 512);

                if (strpos($userResponse, '334') !== false) {
                    // Envoyer password
                    fwrite($connection, base64_encode($this->decrypted_password) . "\r\n");
                    $passResponse = fgets($connection, 512);

                    if (strpos($passResponse, '235') === false && strpos($passResponse, '2') !== 0) {
                        fclose($connection);
                        throw new \Exception("Authentification echouee: identifiants incorrects");
                    }
                }
            }

            // Fermer proprement
            fwrite($connection, "QUIT\r\n");
            fclose($connection);

            $this->update([
                'last_tested_at' => now(),
                'last_test_successful' => true,
                'last_test_error' => null,
            ]);

            return [
                'success' => true,
                'message' => 'Connexion SMTP reussie !',
            ];
        } catch (\Exception $e) {
            $this->update([
                'last_tested_at' => now(),
                'last_test_successful' => false,
                'last_test_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage(),
            ];
        }
    }

    // Envoyer un email de test
    public function sendTestEmail(string $toEmail): array
    {
        try {
            // Créer un transport SMTP personnalisé pour ce test
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $this->host,
                $this->port,
                $this->encryption === 'tls'
            );

            // Pour SSL sur le port 465, utiliser une connexion sécurisée native
            if ($this->encryption === 'ssl') {
                $dsn = sprintf(
                    'smtps://%s:%s@%s:%d',
                    urlencode($this->username),
                    urlencode($this->decrypted_password),
                    $this->host,
                    $this->port
                );
                $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            } else {
                // Pour TLS ou sans encryption
                $transport->setUsername($this->username);
                $transport->setPassword($this->decrypted_password);
            }

            // Créer le mailer Symfony
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            // Créer l'email
            $email = (new \Symfony\Component\Mime\Email())
                ->from(new \Symfony\Component\Mime\Address($this->from_address, $this->from_name))
                ->to($toEmail)
                ->subject('Test SMTP - ' . $this->name . ' - ' . now()->format('d/m/Y H:i'))
                ->text('Ceci est un email de test depuis ERP WMC. Configuration: ' . $this->name . "\n\nEnvoyé le: " . now()->format('d/m/Y à H:i:s'));

            // Envoyer l'email
            $mailer->send($email);

            $this->update([
                'last_tested_at' => now(),
                'last_test_successful' => true,
                'last_test_error' => null,
            ]);

            return [
                'success' => true,
                'message' => 'Email de test envoye avec succes a ' . $toEmail,
            ];
        } catch (\Exception $e) {
            $this->update([
                'last_tested_at' => now(),
                'last_test_successful' => false,
                'last_test_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi : ' . $e->getMessage(),
            ];
        }
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Obtenir la configuration par defaut
    public static function getDefault(): ?self
    {
        return self::active()->default()->first();
    }

    // Appliquer cette configuration au mailer
    public function applyToMailer(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $this->host,
            'mail.mailers.smtp.port' => $this->port,
            'mail.mailers.smtp.encryption' => $this->encryption ?: null,
            'mail.mailers.smtp.username' => $this->username,
            'mail.mailers.smtp.password' => $this->decrypted_password,
            'mail.from.address' => $this->from_address,
            'mail.from.name' => $this->from_name,
        ]);
    }
}
