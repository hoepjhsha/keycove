<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\ProductKey;

use App\Livewire\Admin\Form\ProductKey\BulkImportKeysForm;
use App\Services\BulkImportService;
use League\Csv\Writer;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Title('Bulk Import Keys')]
class BulkImportIndex extends Component
{
    use WithFileUploads;

    public bool $showImportModal = false;

    public ?array $importResult = null;

    public bool $isProcessing = false;

    public int $progressPercentage = 0;

    public BulkImportKeysForm $importForm;

    public function openImportModal(): void
    {
        $this->showImportModal = true;
    }

    public function processImport(): void
    {
        $this->validate([
            'importForm.file' => ['required', 'file', 'mimes:csv,xlsx', 'max:10240'],
        ]);

        $this->isProcessing = true;
        $this->progressPercentage = 0;

        try {
            $filePath = $this->importForm->getFilePath();
            $fileExtension = $this->importForm->getFileExtension();

            $bulkImportService = app(BulkImportService::class);
            $result = $bulkImportService->import($filePath, $fileExtension);

            $this->importResult = $result->toArray();
            $this->progressPercentage = 100;
        } catch (\Throwable $e) {
            $this->importResult = [
                'totalRows' => 0,
                'successCount' => 0,
                'failureCount' => 0,
                'errors' => [
                    [
                        'rowNumber' => 0,
                        'field' => 'import',
                        'message' => 'Import failed: '.$e->getMessage(),
                        'rowData' => [],
                    ],
                ],
                'hasErrors' => true,
            ];
        } finally {
            $this->isProcessing = false;
        }
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $csv = Writer::createFromString();
        $csv->insertOne(['listing_id', 'key_code', 'status']);
        $csv->insertOne([1, 'XXXX-YYYY-ZZZZ-AAAA', 'Available']);
        $csv->insertOne([1, 'BBBB-CCCC-DDDD-EEEE', 'Available']);

        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        file_put_contents($tempFile, $csv->getContent());

        return response()->download($tempFile, 'import_keys_template.csv')->deleteFileAfterSend(true);
    }

    public function downloadErrorReport(): BinaryFileResponse
    {
        if (! $this->importResult || empty($this->importResult['errors'])) {
            abort(400, 'No errors to download');
        }

        $csv = Writer::createFromString();
        $csv->insertOne(['Row', 'Field', 'Error', 'Listing ID', 'Key Code', 'Status']);

        foreach ($this->importResult['errors'] as $error) {
            $csv->insertOne([
                $error['rowNumber'],
                $error['field'],
                $error['message'],
                $error['rowData']['listing_id'] ?? '',
                $error['rowData']['key_code'] ?? '',
                $error['rowData']['status'] ?? '',
            ]);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'errors_');
        file_put_contents($tempFile, $csv->getContent());

        return response()->download($tempFile, 'import_errors.csv')->deleteFileAfterSend(true);
    }

    public function resetImport(): void
    {
        $this->importForm->reset();
        $this->importResult = null;
        $this->progressPercentage = 0;
    }

    public function render()
    {
        return view('pages.admin.product-key.bulk-import-index')->layout('components.layouts.dashboard');
    }
}
