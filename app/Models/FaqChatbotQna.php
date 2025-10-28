<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqChatbotQna extends Model
{
    protected $table = 'faq_chatbot_qna';

    protected $fillable = [
        'question',
        'answer',
    ];
}
