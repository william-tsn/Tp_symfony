<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecipeApiService
{
    private string $baseUrl = 'https://www.themealdb.com/api/json/v1/1/';

    public function __construct(
        private HttpClientInterface $client
    ) {}

    // 🔹 Toutes les recettes
    public function getRecipes(): array
    {
        $response = $this->client->request(
            'GET',
            $this->baseUrl . 'search.php?s='
        );

        $data = $response->toArray();

        return $data['meals'] ?? [];
    }

    // 🔍 Recherche par nom
    public function searchRecipes(string $query): array
    {
        $response = $this->client->request(
            'GET',
            $this->baseUrl . 'search.php?s=' . urlencode($query)
        );

        $data = $response->toArray();

        return $data['meals'] ?? [];
    }

    // 📖 Détail recette
    public function getRecipeById(string $id): ?array
    {
        $response = $this->client->request(
            'GET',
            $this->baseUrl . 'lookup.php?i=' . $id
        );

        $data = $response->toArray();

        return $data['meals'][0] ?? null;
    }
}
