<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaqChatbotQna;
use App\Imports\FaqChatbotQnaImport;
use App\Exports\FaqChatbotQnaTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class FaqChatbotQnaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);

        $query = FaqChatbotQna::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'ILIKE', "%$search%")
                  ->orWhere('answer', 'ILIKE', "%$search%");
            });
        }

        $perPageOptions = [10, 25, 50, 100];
        if (!in_array($perPage, $perPageOptions, true)) { $perPage = 10; }

        $faqs = $query->orderByDesc('created_at')->paginate($perPage);

        return view('faq-chatbot-qna.index', compact('faqs', 'perPageOptions', 'perPage', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('faq-chatbot-qna.create');
    }

    public function importForm()
    {
        return view('faq-chatbot-qna.import');
    }

    public function downloadTemplate()
    {
        return Excel::download(new FaqChatbotQnaTemplateExport(), 'template-faq-chatbot-qna.xlsx');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        Excel::import(new FaqChatbotQnaImport(), $request->file('file'));
        return redirect()->route('faq-chatbot-qna.index')->with('success', 'FAQ import completed');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'answer' => ['required', 'string', 'max:5000'],
        ]);

        FaqChatbotQna::create($validated);
        return redirect()->route('faq-chatbot-qna.index')->with('success', 'FAQ created');
    }

    /**
     * Display the specified resource.
     */
    public function show(FaqChatbotQna $faq_chatbot_qna)
    {
        return view('faq-chatbot-qna.show', ['faq' => $faq_chatbot_qna]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FaqChatbotQna $faq_chatbot_qna)
    {
        return view('faq-chatbot-qna.edit', ['faq' => $faq_chatbot_qna]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FaqChatbotQna $faq_chatbot_qna)
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'answer' => ['required', 'string', 'max:5000'],
        ]);

        $faq_chatbot_qna->update($validated);
        return redirect()->route('faq-chatbot-qna.index')->with('success', 'FAQ updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FaqChatbotQna $faq_chatbot_qna)
    {
        $faq_chatbot_qna->delete();
        return redirect()->route('faq-chatbot-qna.index')->with('success', 'FAQ deleted');
    }
}
