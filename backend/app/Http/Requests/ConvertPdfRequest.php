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
        $user = $this->user();
        $canHighDpi = $user && $user->canAccessHighDpi();
        $allowedDpis = $canHighDpi ? [150, 300, 600] : [150, 300];
        $allowedFormats = config('converter.allowed_formats', ['png', 'jpg', 'jpeg']);

        $rules = [
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

        if ($this->filled('temp_file_id')) {
            $rules['temp_file_id'] = [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9]{40}$/',
                function ($attribute, $value, $fail) {
                    $path = storage_path('app/temp/url_import_' . $value . '.pdf');
                    if (!file_exists($path)) {
                        $fail('File PDF sementara tidak ditemukan atau sudah kadaluarsa. Silakan muat ulang URL.');
                    }
                },
            ];
            $rules['pdf'] = ['nullable'];
        } else {
            $rules['pdf'] = [
                'required',
                'file',
                'mimes:pdf',
                'max:' . $maxKb,
            ];
            $rules['temp_file_id'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $user = $this->user();
        $canHighDpi = $user && $user->canAccessHighDpi();

        return [
            'pdf.required' => 'Silakan pilih atau unggah file PDF yang ingin dikonversi.',
            'pdf.file' => 'File yang diunggah tidak valid.',
            'pdf.mimes' => 'Format file harus berupa dokumen PDF (.pdf).',
            'pdf.max' => 'Ukuran file PDF melebihi batas maksimum (:max KB).',
            'temp_file_id.required' => 'Identifikasi file sementara wajib ada.',
            'temp_file_id.regex' => 'Format token file sementara tidak valid.',
            'format.required' => 'Silakan pilih format gambar output (PNG atau JPG).',
            'format.in' => 'Format output harus berupa PNG atau JPG.',
            'dpi.required' => 'Silakan pilih resolusi DPI.',
            'dpi.in' => $canHighDpi
                ? 'Pilihan resolusi DPI harus 150, 300, atau 600 DPI.'
                : 'Resolusi 600 DPI tersedia khusus untuk Paket Pro, Unlimited, atau Administrator. Pilihan yang tersedia: 150 atau 300 DPI.',
        ];
    }
}
