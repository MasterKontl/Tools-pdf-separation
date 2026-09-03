<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertPdfRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxKb = config('converter.max_file_size_kb', 102400);
        $allowedDpis = config('converter.allowed_dpis', [150, 300, 600]);
        $allowedFormats = config('converter.allowed_formats', ['png', 'jpg', 'jpeg']);

        return [
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:' . $maxKb,
            ],
            'format' => [
                'required',
                'string',
                Rule::in($allowedFormats),
            ],
            'dpi' => [
                'required',
                'integer',
                Rule::in($allowedDpis),
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pdf.required' => 'Silakan pilih atau unggah file PDF yang ingin dikonversi.',
            'pdf.file' => 'File yang diunggah tidak valid.',
            'pdf.mimes' => 'Format file harus berupa dokumen PDF (.pdf).',
            'pdf.max' => 'Ukuran file PDF melebihi batas maksimum (:max KB).',
            'format.required' => 'Silakan pilih format gambar output (PNG atau JPG).',
            'format.in' => 'Format output harus berupa PNG atau JPG.',
            'dpi.required' => 'Silakan pilih resolusi DPI.',
            'dpi.in' => 'Pilihan resolusi DPI harus 150, 300, atau 600 DPI.',
        ];
    }
}
