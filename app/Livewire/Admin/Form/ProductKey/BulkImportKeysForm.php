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
            'file.required' => __('admin.validation.file_required'),
            'file.file'     => __('admin.validation.file_valid'),
            'file.mimes'    => __('admin.validation.file_mimes_csv_xlsx'),
            'file.max'      => __('admin.validation.file_max_10mb'),
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
