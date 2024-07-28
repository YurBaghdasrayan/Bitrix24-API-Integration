<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * Class Bitrix24Service
 */
class Bitrix24Service
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string Base URL for Bitrix24 API
     */
    protected $baseUrl = 'https://b24-u8i9wx.bitrix24.ru/rest/1/0oqwm1oftg1fmgf3/';

    /**
     * Bitrix24Service constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
        ]);
    }

    /**
     * Get the count of contacts with comments.
     *
     * @return int|string Number of contacts with comments or error message
     */
    public function getContactsCountWithComments(): int|string
    {
        try {
            $allDeals = [];
            $start = 0;
            $loopCase = true;
            while ($loopCase) {

                $response = $this->client->get('crm.contact.list', [
                    'query' => [
                        'filter' => [
                            '!=COMMENTS' => ''
                        ],
                        'start' => $start
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                $allDeals = [...$allDeals, ...$data['result']];
                $start = $data['next'] ?? false;

                if (!$start) {
                    $loopCase = false;
                }
            }

            return count($allDeals);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the list of deal categories.
     *
     * @return array|string List of deal categories or error message
     */
    public function getDealCategories(): array|string
    {
        try {
            $response = $this->client->get('crm.category.list', [
                'query' => [
                    'entityTypeId' => 2
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['result'];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Count deals by a specific category ID.
     *
     * @param int $categoryId
     * @return int|string Number of deals in the category or error message
     */
    public function countDealsByCategory(int $categoryId): int|string
    {
        try {
            $response = $this->client->get('crm.deal.list', [
                'query' => [
                    'filter' => [
                        'CATEGORY_ID' => $categoryId
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['total'];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Sum the 'ufCrm6_1721814262' field from smart processes items.
     *
     * @return int|string Sum of scores or an error message
     */
    public function sumScoreFieldSmartProcesses(): int|string
    {
        try {
            $sumScore = 0;
            $start = 0;
            $limit = 1000;

            do {
                $response = $this->client->get('crm.item.list', [
                    'query' => [
                        'entityTypeId' => 1038,
                        'select' => ['ufCrm6_1721814262'],
                        'start' => $start,
                        'limit' => $limit
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                foreach ($data['result']['items'] as $item) {
                    $sumScore += (int)($item['ufCrm6_1721814262'] ?? 0);
                }

                $start = $data['next'] ?? 0;
            } while (!empty($data['next']));

            return $sumScore;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
