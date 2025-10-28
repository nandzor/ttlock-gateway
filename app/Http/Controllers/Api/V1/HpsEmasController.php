<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\HpsEmas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HpsEmasController extends BaseController
{
    /**
     * Check price for gold items based on jenis_barang, karat, and berat
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPrice(Request $request)
    {
        // Validate input using base response format
        $validator = Validator::make($request->all(), [
            'jenis_barang' => 'required|string',
            'karat' => 'required|integer|min:1|max:24',
            'berat' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        // Normalize inputs
        $jenisBarang = Str::lower(trim($validated['jenis_barang']));
        $karat = (int) $validated['karat'];
        $berat = (float) $validated['berat'];

        // Start query
        $query = HpsEmas::where('active', true);

        // Filter by jenis_barang with semantic search
        $this->applySemanticSearch($query, $jenisBarang);

        // Filter by karat (kadar_karat)
        $query->where('kadar_karat', $karat);

        // Get matching records
        $results = $query->get();

        if ($results->isEmpty()) {
            return $this->notFoundResponse('Tidak ditemukan data emas yang sesuai dengan kriteria');
        }

        // Calculate total price based on weight
        $results = $results->map(function ($item) use ($berat, $jenisBarang, $karat) {
            // Calculate price per gram
            $hargaPerGram = $item->stle_rp;

            // Calculate total price based on requested weight
            $totalHarga = $hargaPerGram * $berat;

            // Calculate nilai taksiran per gram
            $nilaiTaksiranPerGram = $item->nilai_taksiran_rp;
            $totalNilaiTaksiran = $nilaiTaksiranPerGram * $berat;

            // Calculate uang pinjaman per gram
            $uangPinjamanPerGram = $item->uang_pinjaman_rp;
            $totalUangPinjaman = $uangPinjamanPerGram * $berat;

            return [
                'id' => $item->id,
                'jenis_barang' => $item->jenis_barang,
                'kadar_karat' => $item->kadar_karat,
                'berat_gram' => $item->berat_gram,
                'ltv' => $item->ltv,

                // Price per gram (from database)
                'harga_per_gram' => $hargaPerGram,
                'harga_per_gram_formatted' => format_currency_id((float) $hargaPerGram),
                'nilai_taksiran_per_gram' => $nilaiTaksiranPerGram,
                'nilai_taksiran_per_gram_formatted' => format_currency_id((float) $nilaiTaksiranPerGram),
                'uang_pinjaman_per_gram' => $uangPinjamanPerGram,
                'uang_pinjaman_per_gram_formatted' => format_currency_id((float) $uangPinjamanPerGram),

                // Total price for requested weight
                'berat_diminta' => $berat,
                'total_harga' => $totalHarga,
                'total_harga_formatted' => format_currency_id($totalHarga),
                'total_nilai_taksiran' => $totalNilaiTaksiran,
                'total_nilai_taksiran_formatted' => format_currency_id($totalNilaiTaksiran),
                'total_uang_pinjaman' => $totalUangPinjaman,
                'total_uang_pinjaman_formatted' => format_currency_id($totalUangPinjaman),

                // Match score based on how close the database weight is to requested weight
                'match_score' => $this->calculateMatchScore($item, $berat, $jenisBarang, $karat)
            ];
        })->sortByDesc('match_score')->values();

        // Format response
        $response = [
            'request' => [
                'jenis_barang' => $validated['jenis_barang'],
                'karat' => $validated['karat'],
                'berat' => $validated['berat'],
            ],
            'results' => $results,
            'best_match' => $results->isNotEmpty() ? $results->first() : null,
            'price_summary' => $this->calculatePriceSummary($results, $berat),
        ];

        return $this->successResponse($response, 'Data harga emas berhasil ditemukan');
    }

    /**
     * Calculate match score for sorting results
     *
     * @param HpsEmas $item
     * @param float $requestedWeight
     * @param string $jenisBarang
     * @param int $karat
     * @return float
     */
    private function calculateMatchScore($item, $requestedWeight, $jenisBarang, $karat)
    {
        $score = 0;
        $itemJenisBarang = Str::lower($item->jenis_barang);
        $searchTerm = Str::lower($jenisBarang);

        // Exact jenis_barang match (highest priority)
        if ($itemJenisBarang === $searchTerm) {
            $score += 100;
        } else {
            // Semantic search scoring
            $score += $this->calculateSemanticScore($itemJenisBarang, $searchTerm);
        }

        // Exact karat match
        if ($item->kadar_karat == $karat) {
            $score += 40;
        } else {
            // Penalty for different karat
            $karatDiff = abs($item->kadar_karat - $karat);
            $score += max(0, 40 - ($karatDiff * 5));
        }

        // Weight similarity bonus (closer weight = higher score)
        if ($item->berat_gram > 0) {
            $weightRatio = min($requestedWeight, $item->berat_gram) / max($requestedWeight, $item->berat_gram);
            $score += $weightRatio * 10;
        }

        return $score;
    }

    /**
     * Calculate semantic search score using Levenshtein distance algorithm
     *
     * @param string $itemJenisBarang
     * @param string $searchTerm
     * @return int
     */
    private function calculateSemanticScore(string $itemJenisBarang, string $searchTerm): int
    {
        // Handle special patterns first for better accuracy
        if (strpos($searchTerm, 'emas') !== false && strpos($searchTerm, 'antam') !== false) {
            return $this->calculateEmasAntamScore($itemJenisBarang, $searchTerm);
        }

        // Use Levenshtein distance for general semantic matching
        return $this->calculateLevenshteinScore($itemJenisBarang, $searchTerm);
    }

    /**
     * Calculate score for emas/antam specific patterns
     *
     * @param string $itemJenisBarang
     * @param string $searchTerm
     * @return int
     */
    private function calculateEmasAntamScore(string $itemJenisBarang, string $searchTerm): int
    {
        $hasNon = strpos($searchTerm, 'non') !== false;

        if ($hasNon) {
            // Searching for "emas non antam" - prioritize "LM Non Antam"
            if (strpos($itemJenisBarang, 'non') !== false && strpos($itemJenisBarang, 'antam') !== false) {
                return 90; // High score for exact pattern match
            }
            if (strpos($itemJenisBarang, 'antam') !== false) {
                return 30; // Lower score for partial match
            }
        } else {
            // Searching for "emas antam" - prioritize "LM Antam"
            if (strpos($itemJenisBarang, 'antam') !== false && strpos($itemJenisBarang, 'non') === false) {
                return 90; // High score for exact pattern match
            }
            if (strpos($itemJenisBarang, 'antam') !== false) {
                return 50; // Medium score for partial match
            }
        }

        return 0;
    }

    /**
     * Calculate score using Levenshtein distance algorithm
     *
     * @param string $itemJenisBarang
     * @param string $searchTerm
     * @return int
     */
    private function calculateLevenshteinScore(string $itemJenisBarang, string $searchTerm): int
    {
        // Exact match gets highest score
        if ($itemJenisBarang === $searchTerm) {
            return 100;
        }

        // Calculate Levenshtein distance
        $distance = $this->levenshteinDistance($itemJenisBarang, $searchTerm);
        $maxLength = max(strlen($itemJenisBarang), strlen($searchTerm));

        // Avoid division by zero
        if ($maxLength === 0) {
            return 0;
        }

        // Calculate similarity percentage (0-100)
        $similarity = (1 - ($distance / $maxLength)) * 100;

        // Convert to score with minimum threshold
        if ($similarity < 30) {
            return 0; // Too different, no match
        }

        return (int) $similarity;
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
     * Calculate price summary for all results
     *
     * @param \Illuminate\Support\Collection $results
     * @param float $requestedWeight
     * @return array|null
     */
    private function calculatePriceSummary($results, $requestedWeight)
    {
        if ($results->isEmpty()) {
            return null;
        }

        $totalHarga = $results->pluck('total_harga');
        $totalNilaiTaksiran = $results->pluck('total_nilai_taksiran');
        $totalUangPinjaman = $results->pluck('total_uang_pinjaman');

        return [
            'berat_diminta' => $requestedWeight,
            'jumlah_data' => $results->count(),
            'harga_range' => [
                'min' => $totalHarga->min(),
                'max' => $totalHarga->max(),
                'min_formatted' => format_currency_id($totalHarga->min()),
                'max_formatted' => format_currency_id($totalHarga->max()),
                'rata_rata' => $totalHarga->avg(),
                'rata_rata_formatted' => format_currency_id($totalHarga->avg()),
            ],
            'nilai_taksiran_range' => [
                'min' => $totalNilaiTaksiran->min(),
                'max' => $totalNilaiTaksiran->max(),
                'min_formatted' => format_currency_id($totalNilaiTaksiran->min()),
                'max_formatted' => format_currency_id($totalNilaiTaksiran->max()),
                'rata_rata' => $totalNilaiTaksiran->avg(),
                'rata_rata_formatted' => format_currency_id($totalNilaiTaksiran->avg()),
            ],
            'uang_pinjaman_range' => [
                'min' => $totalUangPinjaman->min(),
                'max' => $totalUangPinjaman->max(),
                'min_formatted' => format_currency_id($totalUangPinjaman->min()),
                'max_formatted' => format_currency_id($totalUangPinjaman->max()),
                'rata_rata' => $totalUangPinjaman->avg(),
                'rata_rata_formatted' => format_currency_id($totalUangPinjaman->avg()),
            ],
        ];
    }

    /**
     * Apply semantic search using Levenshtein distance for fuzzy matching
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $query
     * @param string $searchTerm
     * @return void
     */
    private function applySemanticSearch($query, $searchTerm)
    {
        $normalizedTerm = $this->normalizeSearchTerm($searchTerm);

        // For emas/antam patterns, use specific matching
        if (strpos($normalizedTerm, 'emas') !== false && strpos($normalizedTerm, 'antam') !== false) {
            $this->applyEmasAntamSearch($query, $normalizedTerm);
            return;
        }

        // For general terms, use broad LIKE search with Levenshtein scoring
        $query->where(function ($q) use ($normalizedTerm) {
            $q->whereRaw('LOWER(jenis_barang) LIKE ?', ['%' . $normalizedTerm . '%'])
              ->orWhereRaw('LOWER(jenis_barang) LIKE ?', ['%' . str_replace(' ', '', $normalizedTerm) . '%'])
              ->orWhereRaw('LOWER(jenis_barang) LIKE ?', ['%' . str_replace(' ', '-', $normalizedTerm) . '%']);
        });
    }

    /**
     * Apply specific search for emas/antam patterns
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $query
     * @param string $searchTerm
     * @return void
     */
    private function applyEmasAntamSearch($query, $searchTerm)
    {
        $hasNon = strpos($searchTerm, 'non') !== false;

        if ($hasNon) {
            // Search for "LM Non Antam" variations
            $query->where(function ($q) {
                $q->whereRaw('LOWER(jenis_barang) LIKE ?', ['%non%antam%'])
                  ->orWhereRaw('LOWER(jenis_barang) LIKE ?', ['%nonantam%'])
                  ->orWhereRaw('LOWER(jenis_barang) LIKE ?', ['%non-antam%']);
            });
        } else {
            // Search for "LM Antam" (without "non")
            $query->where(function ($q) {
                $q->whereRaw('LOWER(jenis_barang) LIKE ?', ['%antam%'])
                  ->whereRaw('LOWER(jenis_barang) NOT LIKE ?', ['%non%']);
            });
        }
    }

    /**
     * Normalize search term by removing special characters and standardizing format
     *
     * @param string $term
     * @return string
     */
    private function normalizeSearchTerm($term)
    {
        // Remove special characters and extra spaces
        $normalized = preg_replace('/[^a-z0-9\s]/', ' ', $term);
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        return $normalized;
    }

}
