<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SSH2;
use RuntimeException;

class SshKeyProvisioner
{
    /**
     * @return array{key_path: string, fingerprint: string}
     */
    public function provision(string $host, int $port, string $username, string $password): array
    {
        $privateKey = RSA::createKey(3072);
        $publicKey = $privateKey->getPublicKey()->toString('OpenSSH').' deploy-center';
        $ssh = new SSH2($host, $port, 15);

        if (! $ssh->login($username, $password)) {
            throw new RuntimeException('Connexion refusée. Vérifiez la commande SSH et le mot de passe.');
        }

        $hostKey = $ssh->getServerPublicHostKey();
        $quotedKey = escapeshellarg($publicKey);
        $command = 'umask 077; mkdir -p "$HOME/.ssh"; touch "$HOME/.ssh/authorized_keys"; '
            .'grep -qxF '.$quotedKey.' "$HOME/.ssh/authorized_keys" || printf "%s\\n" '.$quotedKey.' >> "$HOME/.ssh/authorized_keys"; '
            .'chmod 700 "$HOME/.ssh"; chmod 600 "$HOME/.ssh/authorized_keys"';

        $ssh->exec($command);

        $exitStatus = $ssh->getExitStatus();

        if ($exitStatus !== null && $exitStatus !== 0) {
            throw new RuntimeException('La clé de déploiement n’a pas pu être installée sur le serveur.');
        }

        $testConnection = new SSH2($host, $port, 15);

        if (! $testConnection->login($username, $privateKey)) {
            throw new RuntimeException('La vérification de la nouvelle clé SSH a échoué.');
        }

        $keyPath = 'deployment-keys/'.hash('sha256', $username.'@'.$host.':'.$port).'.key';
        $encryptedKey = Crypt::encryptString($privateKey->toString('PKCS8'));

        if (! Storage::disk('local')->put($keyPath, $encryptedKey)) {
            throw new RuntimeException('La clé SSH sécurisée n’a pas pu être enregistrée.');
        }

        @chmod(Storage::disk('local')->path($keyPath), 0600);

        return [
            'key_path' => $keyPath,
            'fingerprint' => $hostKey ? 'SHA256:'.base64_encode(hash('sha256', $hostKey, true)) : '',
        ];
    }
}
