<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StatsController extends AbstractController
{
    #[Route(
        '/api/stats/categories',
        name: 'api_stats_categories'
    )]
    public function categories(
        Connection $connection,
        Request $request
    ): JsonResponse {

        $annee = $request->query->get(
            'annee',
            2026
        );

        $sql = "

            SELECT
                doc_type,
                COUNT(*) AS total
            FROM publication
            WHERE EXTRACT(
                YEAR FROM date_publication
            ) = :annee
            GROUP BY doc_type
            ORDER BY total DESC
        ";

        $stats = $connection
            ->executeQuery(
                $sql,
                [
                    'annee' => $annee
                ]
            )
            ->fetchAllAssociative();

        return $this->json($stats);
    }
}