<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class PublicationController extends AbstractController
{
    #[Route('/api/publications', name: 'api_publications')]
    public function publications(
        Connection $connection,
        Request $request
    ): JsonResponse {
        $annee = $request->query->get('annee', 2026);
        $search = $request->query->get('search', '');
        $category = $request->query->get(
            'category',
            ''
        );

        $sql = "
            SELECT
                title,
                citation,
                url,
                doc_type,
                date_publication
            FROM publication
            WHERE EXTRACT(YEAR FROM date_publication) = :annee
            AND (
                LOWER(title) LIKE LOWER(:search)
                OR LOWER(citation) LIKE LOWER(:search)
            )
            AND (
                :category = ''
                OR doc_type = :category
            )
            ORDER BY doc_type, date_publication DESC
        ";

        $publications = $connection
            ->executeQuery(
                $sql,
                [
                    'annee' => $annee,
                    'search' => '%' . $search . '%',
                    'category' => $category
                ]
            )
            ->fetchAllAssociative();
        return $this->json($publications);
    }

    #[Route('/api/stats/categories', name: 'api_stats_categories')]
    public function statsCategories(
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
                COUNT(*) as total
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