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

        $search = $request->query->get(
            'search',
            ''
        );

        $language = $request->query->get(
            'language',
            ''
        );

        $categories = $request->query->get(
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
        ";

        $params = [
            'annee' => $annee,
            'search' => '%' . $search . '%'
        ];

        /* =====================================
           FILTRE LANGUE
        ===================================== */

        if ($language !== '') {

            $sql .= "
                AND langue = :language
            ";
            $params['language'] = $language;
        }

        /* =====================================
           FILTRE CATEGORIES MULTIPLES
        ===================================== */

        if ($categories !== '') {

            $categoriesArray = explode(
                ',',
                $categories
            );

            $placeholders = [];

            foreach ($categoriesArray as $index => $category) {
                $key = 'cat' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $category;
            }

            $sql .= "
                AND doc_type IN (
                    " . implode(',', $placeholders) . "
                )
            ";
        }

        $sql .= "
            ORDER BY
                doc_type,
                date_publication DESC
        ";

        $publications = $connection
            ->executeQuery(
                $sql,
                $params
            )
            ->fetchAllAssociative();

        return $this->json(
            $publications
        );
    }
}