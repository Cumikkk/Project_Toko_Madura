<?php
namespace App\Models;
use Config\Core\SystemInfo;
use Kreait\Firebase\Factory;
use Exception;

class Firebase {
    public static function getDatabase() {
        try {
            $factory = (new Factory)
                ->withServiceAccount(__DIR__.'/../../config/firebase-service-account.json')
                ->withDatabaseUri('https://first-state-ca146-default-rtdb.asia-southeast1.firebasedatabase.app'); // ganti project-id

            return $factory->createDatabase();
            
        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return false;
        }
    }

    public static function pushOpsEvent(string $type, string $userId, string $comment, string $device) {
        try {
            $db = self::getDatabase();

            // 2. Ambil semua data yang ada
            $snapshot = $db->getReference('ops/events')
                ->orderByKey()
                ->getSnapshot();

            $allData = $snapshot->getValue();

            // 3. Jika data lebih dari 10, hapus yang paling awal
            if ($allData && count($allData) > 10) {
                $keys = array_keys($allData);
                $keyToDelete = $keys[0]; // ambil key pertama
                $db->getReference('ops/events/' . $keyToDelete)->remove();
            }

            $payload = array_merge([
                'type'      => $type, // 'deposit'|'withdraw'|'internal_transfer'
                'userId'    => $userId,
                'comment'   => $comment,
                'createdAt' => (int) round(microtime(true) * 1000),
                'by'        => $device,
            ]);

            // push ke /ops/events
            $db->getReference('ops/events')->push($payload);
            return true;
        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return false;
        }
    }
}