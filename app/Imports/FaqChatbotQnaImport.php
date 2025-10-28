<?php

namespace App\Imports;

use App\Models\FaqChatbotQna;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FaqChatbotQnaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new FaqChatbotQna([
            'question' => $row['question'] ?? null,
            'answer' => $row['answer'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.question' => ['required', 'string', 'max:1000'],
            '*.answer' => ['required', 'string', 'max:5000'],
        ];
    }
}


