<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeparationRequest extends FormRequest
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

        return [
            'file' => [
                'required',
                'file',
                'max:' . $maxKb,
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'cdr'], true)) {
                        $fail('Format file yang didukung: PDF, PNG, JPG/JPEG, atau CorelDRAW (CDR).');
                    }
                },
            ],
            'dpi' => [
                'required',
                'integer',
                Rule::in($allowedDpis),
            ],
            'mode' => [
                'required',
                'string',
                Rule::in(['cmyk', 'grayscale', 'underbase', 'outline', 'rgb', 'spot']),
            ],
            'choke_mm' => [
                'nullable',
                'numeric',
                'min:0',
                'max:5.0',
            ],
            'trap_mm' => [
                'nullable',
                'numeric',
                'min:0',
                'max:5.0',
            ],
            'spot_colors' => [
                'nullable',
                'integer',
                'min:2',
                'max:8',
            ],
            'registration_marks' => [
                'nullable',
                'boolean',
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
        $user = $this->user();
        $canHighDpi = $user && $user->canAccessHighDpi();

        return [
            'file.required' => 'Silakan pilih atau unggah dokumen PDF atau file gambar (PNG/JPG).',
            'file.file' => 'File yang diunggah tidak valid.',
            'file.mimes' => 'Format file yang didukung: PDF, PNG, atau JPG/JPEG.',
            'file.max' => 'Ukuran file melebihi batas maksimum (:max KB).',
            'dpi.required' => 'Silakan pilih resolusi DPI.',
            'dpi.in' => $canHighDpi
                ? 'Pilihan resolusi DPI harus 150, 300, atau 600 DPI.'
                : 'Resolusi 600 DPI tersedia khusus untuk Paket Pro, Unlimited, atau Administrator. Pilihan yang tersedia: 150 atau 300 DPI.',
            'mode.required' => 'Silakan pilih mode separasi.',
            'mode.in' => 'Pilihan mode separasi harus CMYK, Grayscale, Underbase, Outline, RGB, atau Spot.',
        ];
    }
}
