<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\HpsElektronik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HpsElektronikController extends BaseController
{
    /**
     * Check price and grade for electronic items
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPrice(Request $request)
    {
        // Validate input using base response format
        $validator = Validator::make($request->all(), [
            'jenis_barang' => 'required|string',
            'merek' => 'required|string',
            'nama_barang' => 'required|string',
            'kelengkapan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        // Normalize inputs
        $jenisBarang = Str::lower(trim($validated['jenis_barang']));
        $merek = Str::lower(trim($validated['merek']));
        $namaBarang = Str::lower(trim($validated['nama_barang']));
        $kelengkapan = Str::lower(trim($validated['kelengkapan']));

        // Map kelengkapan to kondisi/grade
        $kondisiMapping = $this->mapKelengkapanToKondisi($kelengkapan);

        // Start query with optimized indexing
        $query = HpsElektronik::where('active', true)
            ->select(['id', 'jenis_barang', 'merek', 'barang', 'tahun', 'grade', 'kondisi', 'harga']); // Select only needed fields

        // Apply filters with better performance
        $query->where(function ($q) use ($jenisBarang, $merek, $namaBarang) {
            $q->whereRaw('LOWER(jenis_barang) LIKE ?', ['%' . $jenisBarang . '%'])
              ->orWhereRaw('LOWER(merek) LIKE ?', ['%' . $merek . '%'])
              ->orWhereRaw('LOWER(barang) LIKE ?', ['%' . $namaBarang . '%']);
        });

        // Extract specs for optimization
        $specFromInput = $this->extractSpecs($namaBarang);
        $onlySpecQuery = $this->isMostlySpecQuery($namaBarang);

        // Optimized single query approach for better performance
        $results = collect();

        // Build comprehensive query with all conditions
        $finalQuery = $query->where(function ($q) use ($kondisiMapping, $kelengkapan) {
            // Add kondisi/grade conditions
            if ($kondisiMapping['kondisi']) {
                $q->whereRaw('LOWER(kondisi) = ?', [$kondisiMapping['kondisi']]);
            }
            if ($kondisiMapping['grade']) {
                $q->orWhereRaw('LOWER(grade) = ?', [$kondisiMapping['grade']]);
            }
            // Add fallback conditions
            $q->orWhereRaw('LOWER(kondisi) LIKE ?', ['%' . $kelengkapan . '%'])
              ->orWhereRaw('LOWER(grade) LIKE ?', ['%' . $kelengkapan . '%']);
        });

        // Add spec matching if applicable
        if ($specFromInput['ram'] || $specFromInput['storage']) {
            $finalQuery->where(function ($q) use ($specFromInput) {
                if ($specFromInput['ram']) {
                    $ram = (string) $specFromInput['ram'];
                    $q->where(function ($subQ) use ($ram) {
                        $subQ->whereRaw('LOWER(barang) LIKE ?', ['%' . $ram . '/%'])
                             ->orWhereRaw('LOWER(barang) LIKE ?', ['% ' . $ram . ' /%'])
                             ->orWhereRaw('LOWER(barang) LIKE ?', ['% ' . $ram . ' %'])
                             ->orWhereRaw('LOWER(barang) LIKE ?', ['%' . $ram . 'gb%']);
                    });
                }
                if ($specFromInput['storage']) {
                    $sto = (string) $specFromInput['storage'];
                    $q->orWhere(function ($subQ) use ($sto) {
                        $subQ->whereRaw('LOWER(barang) LIKE ?', ['%/' . $sto . '%'])
                             ->orWhereRaw('LOWER(barang) LIKE ?', ['% ' . $sto . ' %'])
                             ->orWhereRaw('LOWER(barang) LIKE ?', ['%' . $sto . 'gb%']);
                    });
                }
            });
        }

        // Execute single optimized query with limit
        $results = $finalQuery
            ->orderByRaw("
                CASE
                    WHEN LOWER(kondisi) = ? THEN 1
                    WHEN LOWER(grade) = ? THEN 2
                    WHEN LOWER(kondisi) LIKE ? THEN 3
                    WHEN LOWER(grade) LIKE ? THEN 4
                    ELSE 5
                END
            ", [
                $kondisiMapping['kondisi'] ?? '',
                $kondisiMapping['grade'] ?? '',
                '%' . $kelengkapan . '%',
                '%' . $kelengkapan . '%'
            ])
            ->limit(15) // Increased limit for more results
            ->get();

        if ($results->isEmpty()) {
            return $this->notFoundResponse('Tidak ditemukan data yang sesuai');
        }

        // Format response
        $response = [
            'request' => [
                'jenis_barang' => $validated['jenis_barang'],
                'merek' => $validated['merek'],
                'nama_barang' => $validated['nama_barang'],
                'kelengkapan' => $validated['kelengkapan'],
            ],
            'results' => $results->map(function ($item) use ($validated) {
                return [
                    'id' => $item->id,
                    'jenis_barang' => $item->jenis_barang,
                    'merek' => $item->merek,
                    'barang' => $item->barang,
                    'tahun' => $item->tahun,
                    'grade' => $item->grade,
                    'kondisi' => $item->kondisi,
                    'harga' => $item->harga,
                    'harga_formatted' => format_currency_id($item->harga),
                    'match_score' => $this->calculateMatchScore($item, [
                        'jenis_barang' => $validated['jenis_barang'],
                        'merek' => $validated['merek'],
                        'nama_barang' => $validated['nama_barang'],
                        'kelengkapan' => $validated['kelengkapan'],
                    ])
                ];
            })->sortByDesc('match_score')->values(),
            'best_match' => null,
            'price_range' => null,
        ];

        // Set best match
        if ($results->isNotEmpty()) {
            $response['best_match'] = $response['results'][0];

            // Calculate price range if multiple results
            if ($results->count() > 1) {
                $prices = $results->pluck('harga');
                $response['price_range'] = [
                    'min' => $prices->min(),
                    'max' => $prices->max(),
                    'min_formatted' => format_currency_id($prices->min()),
                    'max_formatted' => format_currency_id($prices->max()),
                ];
            }
        }

        return $this->successResponse($response, 'Data harga berhasil ditemukan');
    }

    /**
     * Map kelengkapan text to kondisi/grade
     *
     * @param string $kelengkapan
     * @return array
     */
    private function mapKelengkapanToKondisi($kelengkapan)
    {
        $kelengkapan = Str::lower($kelengkapan);

        // Define mappings
        $mappings = [
            // Fullset Like New
            ['keywords' => ['fullset like new', 'full set like new', 'fs like new', 'fsln'], 'kondisi' => 'fullset like new', 'grade' => 'a'],

            // Fullset
            ['keywords' => ['fullset', 'full set', 'fs', 'lengkap', 'full'], 'kondisi' => 'fullset', 'grade' => 'b'],

            // Unit Only / Unit Saja
            ['keywords' => ['unit only', 'unit saja', 'uo', 'unit aja'], 'kondisi' => 'unit only', 'grade' => 'c'],

            // Like New
            ['keywords' => ['like new', 'ln', 'seperti baru'], 'kondisi' => 'like new', 'grade' => 'a'],

            // Second / Bekas
            ['keywords' => ['second', 'bekas', 'used'], 'kondisi' => 'second', 'grade' => 'b'],

            // Grade specific
            ['keywords' => ['grade a', 'gradea'], 'kondisi' => null, 'grade' => 'a'],
            ['keywords' => ['grade b', 'gradeb'], 'kondisi' => null, 'grade' => 'b'],
            ['keywords' => ['grade c', 'gradec'], 'kondisi' => null, 'grade' => 'c'],
        ];

        // Check each mapping
        foreach ($mappings as $map) {
            foreach ($map['keywords'] as $keyword) {
                if (Str::contains($kelengkapan, $keyword)) {
                    return [
                        'kondisi' => $map['kondisi'],
                        'grade' => $map['grade']
                    ];
                }
            }
        }

        // Default fallback
        return [
            'kondisi' => $kelengkapan,
            'grade' => null
        ];
    }

    /**
     * Calculate intelligent match score using Levenshtein distance and weighted scoring
     *
     * @param HpsElektronik $item
     * @param array $searchParams
     * @return float
     */
    private function calculateMatchScore($item, $searchParams)
    {
        $totalScore = 0;
        $maxPossibleScore = 0;

        // Field weights
        $fieldWeights = [
            'jenis_barang' => 30,
            'merek' => 30,
            'barang' => 25,
            'kondisi' => 20,
            'grade' => 15
        ];

        // Calculate similarity for each field
        foreach ($fieldWeights as $field => $weight) {
            $maxPossibleScore += $weight;

            if ($field === 'barang') {
                $similarity = $this->calculateFieldSimilarity($item->$field, $searchParams['nama_barang'], $weight);
            } elseif ($field === 'kondisi' || $field === 'grade') {
                $kondisiMapping = $this->mapKelengkapanToKondisi($searchParams['kelengkapan']);
                $searchValue = $kondisiMapping[$field] ?? '';
                $similarity = $this->calculateFieldSimilarity($item->$field, $searchValue, $weight);
            } else {
                $similarity = $this->calculateFieldSimilarity($item->$field, $searchParams[$field], $weight);
            }

            $totalScore += $similarity;
        }

        // Add bonus for exact matches
        $bonus = $this->calculateBonusScore($item, $searchParams);
        $totalScore += $bonus;
        $maxPossibleScore += 20; // Max bonus

        // Return percentage score (0-100)
        return $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 2) : 0;
    }

    /**
     * Calculate field similarity using Levenshtein distance
     *
     * @param string $itemValue
     * @param string $searchValue
     * @param int $maxWeight
     * @return float
     */
    private function calculateFieldSimilarity(string $itemValue, string $searchValue, int $maxWeight): float
    {
        if (empty($searchValue)) {
            return 0;
        }

        $itemValue = Str::lower(trim($itemValue));
        $searchValue = Str::lower(trim($searchValue));

        // Exact match gets full weight
        if ($itemValue === $searchValue) {
            return $maxWeight;
        }

        // Partial match gets partial weight
        if (Str::contains($itemValue, $searchValue) || Str::contains($searchValue, $itemValue)) {
            return $maxWeight * 0.8;
        }

        // Calculate Levenshtein similarity
        $distance = $this->levenshteinDistance($itemValue, $searchValue);
        $maxLength = max(strlen($itemValue), strlen($searchValue));

        if ($maxLength === 0) {
            return 0;
        }

        // Calculate similarity percentage (0-1)
        $similarity = 1 - ($distance / $maxLength);

        // Apply weight based on similarity, with minimum threshold
        if ($similarity < 0.3) {
            return 0; // Too different
        }

        return $similarity * $maxWeight;
    }

    /**
     * Calculate Levenshtein distance between two strings
     *
     * @param string $str1
     * @param string $str2
     * @return int
     */
    private function levenshteinDistance(string $str1, string $str2): int
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);

        // Early return for empty strings
        if ($len1 === 0) return $len2;
        if ($len2 === 0) return $len1;

        // Create matrix
        $matrix = [];

        // Initialize first row and column
        for ($i = 0; $i <= $len1; $i++) {
            $matrix[$i][0] = $i;
        }
        for ($j = 0; $j <= $len2; $j++) {
            $matrix[0][$j] = $j;
        }

        // Fill the matrix
        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                $cost = ($str1[$i - 1] === $str2[$j - 1]) ? 0 : 1;
                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1,      // deletion
                    $matrix[$i][$j - 1] + 1,      // insertion
                    $matrix[$i - 1][$j - 1] + $cost // substitution
                );
            }
        }

        return $matrix[$len1][$len2];
    }

    /**
     * Calculate bonus score for special matches
     *
     * @param HpsElektronik $item
     * @param array $searchParams
     * @return float
     */
    private function calculateBonusScore($item, $searchParams): float
    {
        $bonus = 0;

        // Bonus for exact matches
        if (Str::lower($item->jenis_barang) === Str::lower($searchParams['jenis_barang'])) {
            $bonus += 5;
        }
        if (Str::lower($item->merek) === Str::lower($searchParams['merek'])) {
            $bonus += 5;
        }

        // Bonus for spec matching
        $specFromInput = $this->extractSpecs(Str::lower($searchParams['nama_barang']));
        $specFromItem = $this->extractSpecs(Str::lower($item->barang));

        if ($specFromInput['ram'] && $specFromItem['ram'] && $specFromInput['ram'] == $specFromItem['ram']) {
            $bonus += 5;
        }
        if ($specFromInput['storage'] && $specFromItem['storage'] && $specFromInput['storage'] == $specFromItem['storage']) {
            $bonus += 5;
        }

        // Bonus for year relevance (newer is better)
        $currentYear = date('Y');
        $yearDiff = $currentYear - $item->tahun;
        if ($yearDiff <= 2) {
            $bonus += 5;
        } elseif ($yearDiff <= 5) {
            $bonus += 3;
        }

        return $bonus;
    }

    /**
     * Extract RAM and storage spec from a free text like "4gb/64gb", "8/128", "4 64", "6+128",
     * returning normalized integers (e.g., ['ram' => 4, 'storage' => 64]).
     */
    private function extractSpecs(string $text): array
    {
        $text = Str::of($text)->lower()->replace(['gb', 'g b'], 'gb')->value();
        $ram = null;
        $storage = null;

        // Common patterns: 4gb/64gb, 4/64, 4 64, 4+64, 4-64, 4gb 64gb
        $patterns = [
            '/\b(\d{1,2})\s*gb\s*[\/\-\+\s]?\s*(\d{2,4})\s*gb\b/', // 4gb/64gb, 4gb 64gb
            '/\b(\d{1,2})\s*[\/\-\+\s]\s*(\d{2,4})\b/',             // 4/64, 4-64, 4 64, 4+64
            '/\b(\d{1,2})\s*gb\b/',                                      // 4gb
            '/\b(\d{2,4})\s*gb\b/',                                      // 64gb
        ];

        foreach ($patterns as $idx => $regex) {
            if (preg_match($regex, $text, $m)) {
                if ($idx === 0) {
                    $ram = isset($m[1]) ? (int) $m[1] : null;
                    $storage = isset($m[2]) ? (int) $m[2] : null;
                    break;
                } elseif ($idx === 1) {
                    $ram = isset($m[1]) ? (int) $m[1] : null;
                    $storage = isset($m[2]) ? (int) $m[2] : null;
                } elseif ($idx === 2 && $ram === null) {
                    $ram = (int) $m[1];
                } elseif ($idx === 3 && $storage === null) {
                    $storage = (int) $m[1];
                }
            }
        }

        return [
            'ram' => $ram,
            'storage' => $storage ?? $this->inferDefaultStorage($ram),
        ];
    }

    /** Determine if the query is mostly a specs-only search (e.g., "4/64", "8gb 128gb"). */
    private function isMostlySpecQuery(string $text): bool
    {
        $text = trim(Str::lower($text));
        // If after stripping non spec tokens the string becomes very short, consider it spec
        $stripped = preg_replace('/[^0-9\s\/\-\+a-z]/', ' ', $text);
        $spec = preg_replace('/[^0-9\s\/\-\+g b]/', '', $text);
        $strippedLen = strlen(preg_replace('/\s+/', '', $stripped));
        $specLen = strlen(preg_replace('/\s+/', '', $spec));
        return $specLen > 0 && ($strippedLen - $specLen) <= 2; // mostly spec
    }

    /**
     * Infer default storage when only RAM is provided (business rule):
     * - 8GB RAM → assume 128GB storage
     */
    private function inferDefaultStorage(?int $ram): ?int
    {
        if ($ram === null) {
            return null;
        }
        // Business defaults: map common RAM-only queries to typical storage
        // 4GB -> 64GB, 6GB -> 128GB, 8GB -> 128GB
        if ($ram === 4) return 64;
        if ($ram === 6) return 128;
        if ($ram === 8) return 128;
        return null;
    }
}
