<?php
namespace App\Library\CekatAI\Services;

use App\Library\CekatAI\CekatAbstract;

class CekatBoardingService extends CekatAbstract
{

    public function getLogString(): string {
        return $this->logString;
    }


    public function createBoardingItems(array $data)
    {
        $boardItems = [
            'item_name' => $data['item_name'] ?? null,
            'Email' => $data['email'] ?? "Default Email",
            'Phone' => $data['phone'] ?? "Default Phone",
            'Referral' => $data['referral'] ?? null,
            'UTM Source' => $data['utm_source'] ?? null,
            'UTM Medium' => $data['utm_medium'] ?? null,
            'UTM Campaign' => $data['utm_campaign'] ?? null,
            'UTM Term' => $data['utm_term'] ?? null,
            'UTM Content' => $data['utm_content'] ?? null,
            'Origin User' => $data['origin_user'] ?? null,
        ];

        $cekatOtpService = new CekatOtpRequest($this->apiConfig, $this->db);
        $boardItems['Phone'] = $cekatOtpService->normalizePhoneNumber($boardItems['Phone']);

        $this->log("Creating boarding items with data: ");
        foreach($boardItems as $key => $value) {
            $this->log(" - {$key}: {$value}");
        }

        $url = $this->apiConfig->apiBaseUrl . "/api/crm/boards/{$this->apiConfig->defaultBoardsId}/items";
        $createItem = $this->sendRequest($url, $boardItems, true);
        $this->log("Created boarding item response: " . json_encode($createItem));
        $this->saveLog();

        return [
            'status' => true,
            'message' => "Boarding processed successfully",
            'response' => $createItem
        ];
    }
}