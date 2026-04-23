<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\ProductKey;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class BulkImportKeysForm extends Form
{
    public ?TemporaryUploadedFile $file = null;

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload',
            'file.file'     => 'Please upload a valid file',
            'file.mimes'    => 'File must be CSV or XLSX format',
            'file.max'      => 'File size must not exceed 10MB',
        ];
    }

    public function getFilePath(): string
    {
        return $this->file->getRealPath();
    }

    public function getFileExtension(): string
    {
        return $this->file->getClientOriginalExtension();
    }
}
