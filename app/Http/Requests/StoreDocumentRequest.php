<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a document upload to the library. The permission (47) is enforced
 * by the route middleware; section authorisation happens in the controller.
 */
class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $extensions = implode(',', config('documents.supported_extensions'));
        $maxKb = (int) config('documents.max_size_mb') * 1024;

        return [
            'section_id' => ['required', 'integer'],
            'folder_id' => ['nullable', 'integer'],
            'type' => ['required', 'string', 'exists:type_document,TD_CODE'],
            'userfile' => ['required', 'array', 'min:1'],
            'userfile.*' => ['file', 'extensions:'.$extensions, 'max:'.$maxKb],
        ];
    }

    /** @return array<string,string> */
    public function messages(): array
    {
        return [
            'userfile.*.extensions' => 'Type de fichier non autorisé.',
            'userfile.*.max' => 'Fichier trop volumineux (max '.config('documents.max_size_mb').' Mo).',
        ];
    }
}
