<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Bitrix24Service;
use Illuminate\Http\JsonResponse;

class Bitrix24Controller extends Controller
{
    /**
     * @var Bitrix24Service
     */
    protected Bitrix24Service $bitrix24Service;

    /**
     * Bitrix24Controller constructor.
     *
     * @param Bitrix24Service $bitrix24Service
     */
    public function __construct(Bitrix24Service $bitrix24Service)
    {
        $this->bitrix24Service = $bitrix24Service;
    }

    /**
     * Execute the task to fetch Bitrix24 data and return as JSON response.
     *
     * @return JsonResponse
     */
    public function executeTask(): JsonResponse
    {
        $contactsCountWithComments = $this->bitrix24Service->getContactsCountWithComments();
        $dealsByCategories = $this->getDealsByCategories();
        $sumScoreSmartProcess = $this->bitrix24Service->sumScoreFieldSmartProcesses();

        return response()->json([
            'count_with_comments' => $contactsCountWithComments,
            'count_0_hopper' => $dealsByCategories['Общая'] ?? 0,
            'count_1_hopper' => $dealsByCategories['Первая'] ?? 0,
            'count_2_hopper' => $dealsByCategories['Вторая'] ?? 0,
            'points_sum' => $sumScoreSmartProcess,
        ]);
    }

    /**
     * Fetch the count of deals by each category.
     *
     * @return array
     */
    private function getDealsByCategories(): array
    {
        $dealCategories = $this->bitrix24Service->getDealCategories();
        $dealsByCategories = [];
        foreach ($dealCategories['categories'] as $category) {
            $categoryId = $category['id'];
            $dealsCount = $this->bitrix24Service->countDealsByCategory($categoryId);
            $dealsByCategories[$category['name']] = $dealsCount;
        }

        return $dealsByCategories;
    }
}
