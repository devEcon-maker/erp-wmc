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

    // Créer le transport SMTP
    protected function createSmtpTransport(): \Symfony\Component\Mailer\Transport\TransportInterface
    {
        // Construire le DSN selon le type d'encryption
        if ($this->encryption === 'ssl') {
            // Pour SSL (port 465), utiliser smtps://
            $dsn = sprintf(
                'smtps://%s:%s@%s:%d?verify_peer=0',
                urlencode($this->username),
                urlencode($this->decrypted_password),
                $this->host,
                $this->port
            );
        } else {
            // Pour TLS (port 587) ou sans encryption
            $dsn = sprintf(
                'smtp://%s:%s@%s:%d',
                urlencode($this->username),
                urlencode($this->decrypted_password),
                $this->host,
                $this->port
            );
        }

        return \Symfony\Component\Mailer\Transport::fromDsn($dsn);
    }

    // Tester la connexion SMTP
    public function testConnection(): array
    {
        try {
            $transport = $this->createSmtpTransport();

            // Tester la connexion en démarrant le transport
            if ($transport instanceof \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport) {
                $transport->start();
                $transport->stop();
            }

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
            // Créer le transport via la méthode centralisée
            $transport = $this->createSmtpTransport();

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
