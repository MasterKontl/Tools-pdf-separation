<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertPdfRequest;
use App\Services\PdfConverterService;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PdfConverterController extends Controller
{
    /**
     * Display the converter interface.
     */
    public function index()
    {
        return view('converter', [
            'allowedDpis' => config('converter.allowed_dpis', [150, 300, 600]),
            'allowedFormats' => config('converter.allowed_formats', ['png', 'jpg']),
        ]);
    }

    /**
     * Handle the PDF conversion process and return the converted file as download.
     */
    public function convert(ConvertPdfRequest $request, PdfConverterService $converter)
    {
        try {
            $pdfFile = $request->file('pdf');
            $format = $request->input('format', 'png');
            $dpi = (int) $request->input('dpi', 300);

            $result = $converter->convert($pdfFile, $format, $dpi);

            return response()->download(
                $result['filePath'],
                $result['fileName'],
                [
                    'Content-Type' => $result['mimeType'],
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]
            )->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses file PDF: ' . $e->getMessage());
        }
    }
}
