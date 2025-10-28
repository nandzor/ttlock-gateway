<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FaqChatbotQnaTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            ['Apa itu Gadai Mulia?', 'Gadai Mulia adalah layanan gadai emas dengan proses mudah.'],
        ];
    }

    public function headings(): array
    {
        return ['question', 'answer'];
    }

    public function title(): string
    {
        return 'Template FAQ Chatbot QnA';
    }
}


