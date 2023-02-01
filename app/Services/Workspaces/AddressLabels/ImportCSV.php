<?php

namespace App\Services\Workspaces\AddressLabels;

use App\Exceptions\Workspace\AddressLabel\ImportCSV\DuplicateItem;
use App\Models\Workspace;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use League\Csv\Reader;

class ImportCSV
{
    private const REQUIRED_HEADERS = ['blockchain', 'address', 'label', 'tag', 'description'];
    private Reader $csvHandler;
    private Workspace $workspace;
    private array $headers;
    private bool $replaceDuplicates = false;
    private array $errors = [];
    private int $inserted = 0;
    private int $updated = 0;

    public function __construct(Workspace $workspace, UploadedFile $file)
    {
        $this->workspace = $workspace;
        $this->csvHandler = Reader::createFromPath($file->path(), 'r');
        $this->csvHandler->setDelimiter(';');
        $this->csvHandler->setHeaderOffset(0);
        $this->headers = $this->csvHandler->getHeader();

        if (!empty(array_diff($this->headers, self::REQUIRED_HEADERS))) {
            throw new \RuntimeException('Invalid headers detected: ' . implode(',', $this->headers));
        }
    }

    public function replaceDuplicates(bool $recreateDuplicates)
    {
        $this->replaceDuplicates = $recreateDuplicates;
    }

    public function save()
    {
        foreach ($this->csvHandler as $line => $record) {
            $errors = $this->validateRow($record);
            if (!$errors->isEmpty()) {
                $this->errors[$line] = $errors->toArray();
                continue;
            }

            try {
                $this->handleItem($record);
            } catch (DuplicateItem $exception) {
                $this->errors[$line] = ['address' => ['Duplicated']];
            }
        }
    }

    public function inserted(): int
    {
        return $this->inserted;
    }

    public function updated(): int
    {
        return $this->updated;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function handleitem($record)
    {
        try {
            $this->insertLabel($record);
        } catch (QueryException $exception) {
            if (strpos($exception->getMessage(), '1062 Duplicate entry') > 0) {
                if ($this->replaceDuplicates) {
                    $this->updateLabel($record);
                } else {
                    throw new DuplicateItem();
                }
            }
        }
    }

    private function insertLabel($data)
    {
        Workspace\AddressLabel::query()->insert([
            "blockchain" => $data['blockchain'],
            "address" => $data['address'],
            "label" => $data['label'],
            "description" => $data['description'],
            "tag" => $data['tag'],
            "workspace_id" => $this->workspace->id
        ]);

        $this->inserted++;
    }

    private function updateLabel($data)
    {
        Workspace\AddressLabel::query()
            ->where('address', $data['address'])
            ->where('blockchain', $data['blockchain'])
            ->where('workspace_id', $this->workspace->id)
            ->update([
                "label" => $data['label'],
                "description" => $data['description'],
                "tag" => $data['tag'],
            ]);

        $this->updated++;
    }

    private function validateRow($data)
    {
        $validator = \Validator::make($data, Workspace\AddressLabel::getValidationRules());

        return $validator->messages();
    }
}
